-- Runs once, the first time the MySQL container starts with an empty volume.
CREATE DATABASE IF NOT EXISTS courtku_test;
GRANT ALL PRIVILEGES ON courtku_test.* TO 'courtku'@'%';
-- And courtku_e2e for the browser tests (apps/web/e2e), so they never touch local data.
CREATE DATABASE IF NOT EXISTS courtku_e2e;
GRANT ALL PRIVILEGES ON courtku_e2e.* TO 'courtku'@'%';
FLUSH PRIVILEGES;
