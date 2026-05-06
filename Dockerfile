FROM php:8.2-apache

# 1. Activer mod_rewrite
RUN a2enmod rewrite

# 2. Installer les dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    unzip \
    git \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        zip \
        intl \
        mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Configurer Apache directement avec le bon DocumentRoot
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# 4. Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Préparer le répertoire de travail
WORKDIR /var/www/html

# 6. Copier le projet
COPY . /var/www/html

# 7. Variables d'environnement pour le build
ENV APP_ENV=prod
ENV APP_DEBUG=0

# 8. Installer les dépendances
RUN git config --global --add safe.directory /var/www/html && \
    composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# 9. Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/var

# 10. Vider le cache
RUN php bin/console cache:clear --env=prod --no-debug

# 11. Finalisation
EXPOSE 80
CMD ["apache2-foreground"]