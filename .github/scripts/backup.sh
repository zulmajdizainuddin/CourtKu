#!/usr/bin/env bash
# Nightly backup of the live database, and proof that it can be restored (ADR-029).
#
#   1. Dump the database: one consistent snapshot, without the rows of sessions and cache
#      (logins and throwaway copies; never needed again, and logins shouldn't sit in a file).
#   2. Compress it and lock it with BACKUP_PASSWORD (gpg, AES-256). Only the locked file is kept.
#   3. Unlock that file again and load it into an empty database, exactly as a real restore would.
#   4. Check the restored copy: the same tables, every migration the code knows about, and
#      people, venues and bookings in it. Only counts are printed, never data.
#
# Runs inside the mysql:8.4 Docker image (it has mysqldump, mysql, gzip and gpg), on GitHub
# Actions every night (.github/workflows/backup.yml) or on a laptop the same way (docs/deploy.md).
#
# Settings (environment variables):
#   SOURCE_HOST, SOURCE_PORT, SOURCE_USER, SOURCE_PASSWORD, SOURCE_DATABASE   the database to back up
#   SOURCE_CA_CERT    the server's CA certificate (Aiven needs encrypted connections); empty = no TLS
#   RESTORE_HOST, RESTORE_PORT, RESTORE_USER, RESTORE_PASSWORD               an empty MySQL to restore into
#   BACKUP_PASSWORD   at least 20 characters; needed to unlock the backup later
#   MIGRATIONS_DIR    apps/api/database/migrations, to check every migration is in the copy
#   OUT_DIR           where the locked file goes (default: backup-out)
set -euo pipefail

for name in SOURCE_HOST SOURCE_PORT SOURCE_USER SOURCE_PASSWORD SOURCE_DATABASE RESTORE_HOST RESTORE_PORT RESTORE_USER RESTORE_PASSWORD BACKUP_PASSWORD MIGRATIONS_DIR; do
  if [ -z "${!name:-}" ]; then
    echo "::error::$name is not set." >&2
    exit 1
  fi
done
if [ "${#BACKUP_PASSWORD}" -lt 20 ]; then
  echo "::error::BACKUP_PASSWORD must be at least 20 characters." >&2
  exit 1
fi

OUT_DIR="${OUT_DIR:-backup-out}"
CHECK_DB="courtku_restore_check"
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT
mkdir -p "$OUT_DIR"

# Passwords go in option files that only this process can read, never on the command line,
# where other programs on the machine could see them.
umask 077
{
  echo "[client]"
  echo "host=$SOURCE_HOST"
  echo "port=$SOURCE_PORT"
  echo "user=$SOURCE_USER"
  echo "password=$SOURCE_PASSWORD"
  if [ -n "${SOURCE_CA_CERT:-}" ]; then
    printf '%s\n' "$SOURCE_CA_CERT" > "$WORK/ca.pem"
    echo "ssl-mode=VERIFY_CA"
    echo "ssl-ca=$WORK/ca.pem"
  else
    echo "ssl-mode=DISABLED"
  fi
} > "$WORK/source.cnf"
{
  echo "[client]"
  echo "host=$RESTORE_HOST"
  echo "port=$RESTORE_PORT"
  echo "user=$RESTORE_USER"
  echo "password=$RESTORE_PASSWORD"
  echo "ssl-mode=DISABLED"
} > "$WORK/restore.cnf"

source_sql() { mysql --defaults-extra-file="$WORK/source.cnf" --batch --skip-column-names "$SOURCE_DATABASE" -e "$1"; }
restore_sql() { mysql --defaults-extra-file="$WORK/restore.cnf" --batch --skip-column-names "$@"; }

# --- 1. Dump ---------------------------------------------------------------------------------
# Named after the time in Malaysia (UTC+8, no daylight saving), e.g. courtku-2026-11-02-0300.sql.gz.gpg.
STAMP="$(date -u -d '+8 hours' +%Y-%m-%d-%H%M)"
FILE="courtku-$STAMP.sql.gz.gpg"
echo "Backing up $SOURCE_DATABASE..."

# Logins and throwaway copies: their tables are kept (empty), their rows are not.
SKIP=(sessions cache cache_locks)
ignore=()
for table in "${SKIP[@]}"; do ignore+=("--ignore-table=$SOURCE_DATABASE.$table"); done

# --single-transaction: one consistent snapshot without locking the live site.
# --set-gtid-purged=OFF and --no-tablespaces: the copy loads into any empty MySQL.
dump_options=(--single-transaction --quick --routines --triggers --no-tablespaces --set-gtid-purged=OFF --default-character-set=utf8mb4)
{
  mysqldump --defaults-extra-file="$WORK/source.cnf" "${dump_options[@]}" "${ignore[@]}" "$SOURCE_DATABASE"
  mysqldump --defaults-extra-file="$WORK/source.cnf" "${dump_options[@]}" --no-data "$SOURCE_DATABASE" "${SKIP[@]}"
} | gzip -9 > "$WORK/dump.sql.gz"

# --- 2. Lock ---------------------------------------------------------------------------------
gpg --batch --yes --quiet --pinentry-mode loopback --passphrase-fd 3 \
  --symmetric --cipher-algo AES256 --output "$OUT_DIR/$FILE" "$WORK/dump.sql.gz" 3<<<"$BACKUP_PASSWORD"
rm -f "$WORK/dump.sql.gz"
echo "Locked backup: $FILE ($(du -h "$OUT_DIR/$FILE" | cut -f1))"

# --- 3. Restore, from the locked file, into an empty database -------------------------------
restore_sql -e "DROP DATABASE IF EXISTS $CHECK_DB; CREATE DATABASE $CHECK_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
gpg --batch --quiet --pinentry-mode loopback --passphrase-fd 3 --decrypt "$OUT_DIR/$FILE" 3<<<"$BACKUP_PASSWORD" \
  | gunzip \
  | restore_sql "$CHECK_DB"
echo "Restored into an empty database ($CHECK_DB)."

# --- 4. Check the restored copy --------------------------------------------------------------
problems=0
fail() { echo "::error::$1" >&2; problems=$((problems + 1)); }

source_tables="$(source_sql "SHOW TABLES" | sort)"
restored_tables="$(restore_sql "$CHECK_DB" -e "SHOW TABLES" | sort)"
[ "$source_tables" = "$restored_tables" ] || fail "The restored copy doesn't have the same tables as the live database."

# Every migration file in the code must be recorded in the copy: the copy has the whole schema.
restored_migrations="$(restore_sql "$CHECK_DB" -e "SELECT migration FROM migrations")"
for file in "$MIGRATIONS_DIR"/*.php; do
  name="$(basename "$file" .php)"
  grep -qx "$name" <<<"$restored_migrations" || fail "Migration $name is missing from the copy."
done

# The tables that matter must have rows, and counts are printed for the record (counts only).
echo "Rows in the restored copy:"
for table in users venues courts bookings payments; do
  count="$(restore_sql "$CHECK_DB" -e "SELECT COUNT(*) FROM \`$table\`")"
  printf '  %-10s %s\n' "$table" "$count"
  if [ "$table" = users ] || [ "$table" = venues ]; then
    [ "$count" -gt 0 ] || fail "The restored copy has no $table."
  fi
done

restore_sql -e "DROP DATABASE $CHECK_DB;"

if [ "$problems" -gt 0 ]; then
  echo "The backup was made but did NOT restore correctly ($problems problem(s))." >&2
  exit 1
fi
echo "Backup OK: it unlocks with the password and restores into an empty database."
