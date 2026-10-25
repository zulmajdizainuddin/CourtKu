#!/bin/sh
# Runs database migrations each time the container starts.
#
# Unlike the image's built-in migration step, this one never stops the API from
# starting: if the database is down (e.g. the free Aiven plan powered it off),
# it logs a warning and carries on. Pages that need no database keep working,
# and /api/health reports the database as down so the uptime monitor alerts us.

if [ "${COURTKU_MIGRATE_ON_START:-true}" != "true" ]; then
    exit 0
fi

echo "🗄️  CourtKu: running database migrations..."

if timeout "${COURTKU_MIGRATE_TIMEOUT:-60}" php "${APP_BASE_DIR:-/var/www/html}/artisan" migrate --force --no-interaction; then
    echo "✅ CourtKu: migrations done."
else
    echo "⚠️  CourtKu: migrations skipped, the database is not reachable. Starting the API anyway."
fi

exit 0
