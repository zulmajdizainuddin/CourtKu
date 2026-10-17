-- Runs once, the first time the MySQL container starts with an empty volume.
CREATE DATABASE IF NOT EXISTS courtku_test;
GRANT ALL PRIVILEGES ON courtku_test.* TO 'courtku'@'%';
FLUSH PRIVILEGES;
