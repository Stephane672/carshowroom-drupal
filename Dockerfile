FROM php:8.3-apache AS base

# System packages + PHP extensions Drupal 11 needs
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libjpeg62-turbo-dev libwebp-dev \
        libfreetype6-dev libicu-dev libxml2-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mysqli gd zip intl opcache xml \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install dependencies first (cache-friendly): this reproduces vendor/, web/core,
# all contrib modules/themes and the web/ scaffold files from composer.json/lock.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist \
    && composer clear-cache

# Overlay our own custom code, exported config and the essential uploaded images.
# Nothing here collides with anything composer just generated.
COPY web/modules/custom/ web/modules/custom/
COPY config/sync/ config/sync/
COPY web/sites/default/files/ web/sites/default/files/
COPY docker/settings.php web/sites/default/settings.php
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/web#' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/web>\n    AllowOverride All\n    Require all granted\n</Directory>\n' >> /etc/apache2/sites-available/000-default.conf \
    && mkdir -p web/sites/default/files/styles web/sites/default/files/css web/sites/default/files/js web/sites/default/files/php \
    && chown -R www-data:www-data web/sites/default/files

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
