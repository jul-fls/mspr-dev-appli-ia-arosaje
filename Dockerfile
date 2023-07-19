# Choisir l'image de base
FROM php:8.1-apache

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql

# Installer Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier le code source de l'application dans le conteneur
COPY . .

# Installer les dépendances
RUN composer install --no-scripts --no-autoloader --no-progress --no-interaction

# Créer l'autoload
RUN composer dump-autoload --optimize

# Permettre à Apache de réécrire les URLs pour Symfony
RUN a2enmod rewrite

# Modifier le propriétaire des fichiers pour éviter les problèmes de permissions
RUN chown -R www-data:www-data .

# Installer Symfony CLI
RUN apt-get update && apt-get install -y wget gnupg acl
RUN wget https://get.symfony.com/cli/installer -O - | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Créer un fichier .env vide
RUN touch .env

# Démarrer le serveur Apache
CMD ["/usr/local/bin/symfony", "server:start", "--no-tls", "--port=8000", "--dir=/var/www/html/public"]

# Exposer le port 8000
EXPOSE 8000