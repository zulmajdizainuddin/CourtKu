// Starts the API for the browser tests (ADR-034), called by playwright.config.ts:
// 1. writes apps/api/.env.e2e from .env.e2e.example (keeping last run's key), with the database
//    settings from E2E_DB_* when given (CI), so the API uses its own courtku_e2e database;
// 2. checks it really is that database, then empties it and loads the test data (E2eSeeder);
// 3. serves the API on http://127.0.0.1:8100.
// PHP is `php`, or the path in E2E_PHP (e.g. Laravel Herd's php.exe on Windows).
import { spawn, spawnSync } from "node:child_process";
import { existsSync, readFileSync, writeFileSync } from "node:fs";
import { join } from "node:path";
import { fileURLToPath } from "node:url";

const apiDir = fileURLToPath(new URL("../../api/", import.meta.url));
const php = process.env.E2E_PHP || "php";
// APP_ENV=e2e makes Laravel read .env.e2e instead of .env, here and in every request `serve` starts.
const env = { ...process.env, APP_ENV: "e2e" };

const envFile = join(apiDir, ".env.e2e");
let settings = readFileSync(join(apiDir, ".env.e2e.example"), "utf8");
for (const name of ["DB_HOST", "DB_PORT", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD"]) {
  const value = process.env[`E2E_${name}`];
  if (value) settings = settings.replace(new RegExp(`^${name}=.*$`, "m"), `${name}=${value}`);
}
const oldKey = existsSync(envFile) ? /^APP_KEY=(.+)$/m.exec(readFileSync(envFile, "utf8"))?.[1] : undefined;
if (oldKey) settings = settings.replace(/^APP_KEY=.*$/m, `APP_KEY=${oldKey}`);
writeFileSync(envFile, settings);

function artisan(...args) {
  const run = spawnSync(php, ["artisan", ...args], { cwd: apiDir, env, encoding: "utf8" });
  if (run.status !== 0) {
    console.error(run.stdout, run.stderr);
    throw new Error(`php artisan ${args.join(" ")} failed`);
  }
  return run.stdout.trim();
}

if (!oldKey) artisan("key:generate", "--force");

// Never empty any database but the browser tests' own.
const where = artisan("tinker", "--execute=echo app()->environment().'|'.config('database.connections.'.config('database.default').'.database');");
if (!/^e2e\|\w+_e2e$/m.test(where)) {
  throw new Error(`Refusing to reset the database: the API would use "${where}", not an *_e2e database.`);
}
artisan("migrate:fresh", "--seed", "--seeder=Database\\Seeders\\E2eSeeder", "--force");

const server = spawn(php, ["artisan", "serve", "--host=127.0.0.1", "--port=8100"], { cwd: apiDir, env, stdio: "inherit" });
for (const signal of ["SIGINT", "SIGTERM"]) process.on(signal, () => server.kill(signal));
server.on("exit", (code) => process.exit(code ?? 0));
