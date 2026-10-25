# Production image for the CourtKu API.
# The host (Render today) builds this file and runs the result.
#
# serversideup/php = PHP 8.4 + nginx, tuned for Laravel, runs as a normal user (not root),
# and works on both Intel/AMD and ARM servers.
FROM serversideup/php:8.4-fpm-nginx

ENV PHP_OPCACHE_ENABLE=1 \
    # On every start, cache config/routes/views for speed.
    AUTORUN_ENABLED=true \
    # The image's own migration step stops the container when the database is down,
    # so we use our own script instead (docker/45-courtku-migrate.sh).
    AUTORUN_LARAVEL_MIGRATION=false

# Our startup script runs before the image's Laravel step (files run in name order).
USER root
COPY docker/45-courtku-migrate.sh /etc/entrypoint.d/45-courtku-migrate.sh
RUN chmod 755 /etc/entrypoint.d/45-courtku-migrate.sh
USER www-data

WORKDIR /var/www/html

# 1. Install PHP packages first. Docker reuses this layer when only our own code
#    changed, so most deploys skip the slow download step.
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts --no-autoloader

# 2. Copy the app itself (.dockerignore keeps out .env, vendor, tests, logs).
COPY --chown=www-data:www-data . .
RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi

# nginx inside the image listens on 8080; the host's proxy adds HTTPS in front.
EXPOSE 8080
