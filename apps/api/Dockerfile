# Production image for the CourtKu API.
# The server (Coolify on Oracle Cloud) builds this file and runs the result.
#
# serversideup/php = PHP 8.4 + nginx, tuned for Laravel, runs as a normal user (not root),
# and works on both Intel/AMD and ARM servers (Oracle's free servers are ARM).
FROM serversideup/php:8.4-fpm-nginx

ENV PHP_OPCACHE_ENABLE=1 \
    # On every start: run database migrations, then cache config/routes/views for speed.
    AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_MIGRATION=true

WORKDIR /var/www/html

# 1. Install PHP packages first. Docker reuses this layer when only our own code
#    changed, so most deploys skip the slow download step.
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts --no-autoloader

# 2. Copy the app itself (.dockerignore keeps out .env, vendor, tests, logs).
COPY --chown=www-data:www-data . .
RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi

# nginx inside the image listens on 8080; the server's proxy adds HTTPS in front.
EXPOSE 8080
