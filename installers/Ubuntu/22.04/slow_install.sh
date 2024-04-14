#!/bin/bash
MAIN_DIR="/TM/raw-repo"

apt-get update && apt-get install ca-certificates

apt install -y git
cd /
mkdir -p $MAIN_DIR
git clone https://github.com/seotarek/TMPanel.git $MAIN_DIR

HELPERS_DIR=$MAIN_DIR"/shell/helpers/ubuntu"
. $HELPERS_DIR"/common.sh"
. $HELPERS_DIR"/create-mysql-db-and-user.sh"


# Create the new TMweb user

random_password="$(openssl rand -base64 32)"
email="admin@to2mor.com"

# Create the new TMweb user
/usr/sbin/useradd "TMweb" -c "$email" --no-create-home

# Add the TMweb user to the www-data group
sudo usermod -a -G www-data TMweb

# Add the root user to the www-data group
#sudo usermod -a -G www-data root


# do not allow login into TMweb user
echo TMweb:$random_password | sudo chpasswd -e

mkdir -p /etc/sudoers.d
cp -f $MAIN_DIR/installers/Ubuntu/22.04/sudo/TMweb /etc/sudoers.d/
chmod 440 /etc/sudoers.d/TMweb


# Update the system
apt update -y

REPOSITORIES_LIST=(
    "ppa:ondrej/php"
)

# Check if the repositories are installed
for REPOSITORY in "${REPOSITORIES_LIST[@]}"; do
  add-apt-repository -y $REPOSITORY
done

DEPENDENCIES_LIST=(
    "jq"
    "curl"
    "wget"
    "git"
    "apache2"
    "apache2-suexec-custom"
    "nodejs"
    "npm"
    "unzip"
    "zip"
    "tar"
    "mysql-common"
    "mysql-server"
    "mysql-client"
    "lsb-release"
    "gnupg2"
    "ca-certificates"
    "apt-transport-https"
    "software-properties-common"
    "supervisor"
    "libonig-dev"
    "libzip-dev"
    "libcurl4-openssl-dev"
    "libssl-dev"
    "zlib1g-dev"
    "libapache2-mod-ruid2"
#    "libapache2-mod-fcgid"
#    "libapache2-mod-php8.1"
    "libapache2-mod-php8.2"
#    "libapache2-mod-php8.3"
#    "php7.4"
#    "php7.4-fpm"
#    "php7.4-{bcmath,xml,bz2,intl,curl,dom,fileinfo,gd,intl,mbstring,mysql,opcache,sqlite3,xmlrpc,zip}"
#    "php8.1"
#    "php8.1-fpm"
#    "php8.1-{bcmath,xml,bz2,intl,curl,dom,fileinfo,gd,intl,mbstring,mysql,opcache,sqlite3,xmlrpc,zip}"
    "php8.2"
    "php8.2-fpm"
    "php8.2-{bcmath,xml,bz2,intl,curl,dom,fileinfo,gd,intl,mbstring,mysql,opcache,sqlite3,xmlrpc,zip}"
#    "php8.3"
#    "php8.3-fpm"
#    "php8.3-{bcmath,xml,bz2,intl,curl,dom,fileinfo,gd,intl,mbstring,mysql,opcache,sqlite3,xmlrpc,zip}"
)
# Check if the dependencies are installed
for DEPENDENCY in "${DEPENDENCIES_LIST[@]}"; do
    if ! command_is_installed $DEPENDENCY; then
        echo "Dependency $DEPENDENCY is not installed."
        echo "Installing $DEPENDENCY..."
        apt install -y $DEPENDENCY
    else
        echo "Dependency $DEPENDENCY is installed."
    fi
done

sudo a2enmod php8.2
sudo a2enmod rewrite
sudo a2enmod env
sudo a2enmod ssl
sudo a2enmod actions
sudo a2enmod headers
sudo a2enmod suexec
sudo a2enmod ruid2
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enconf ssl-params

#sudo a2enmod fcgid
#sudo a2enmod alias
#sudo a2enmod proxy_fcgi
#sudo a2enmod setenvif

sudo ufw allow in "Apache Full"

#sudo a2ensite default-ssl
#sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/apache-selfsigned.key -out /etc/ssl/certs/apache-selfsigned.crt

systemctl restart apache2

#DEPENDENCIES_FOR_REMOVE_LIST=(
#    "apache2"
#)
## Check if the dependencies are installed
#for DEPENDENCY in "${DEPENDENCIES_FOR_REMOVE_LIST[@]}"; do
#    if command_is_installed $DEPENDENCY; then
#        echo "Dependency $DEPENDENCY is installed."
#        echo "Removing $DEPENDENCY..."
#        apt purge -y $DEPENDENCY
#        apt autoremove -y
#    fi
#done

# Install TM PHP
wget https://github.com/seotarek/TMPanelPHPDist/raw/main/debian/php/dist/TM-php-8.2.0.deb
sudo dpkg -i TM-php-8.2.0.deb

# Install TM NGINX
wget https://github.com/seotarek/TMPanelNginxDist/raw/main/debian/nginx/dist/TM-nginx-1.24.0.deb
sudo dpkg -i TM-nginx-1.24.0.deb

# sudo ufw allow proto tcp from any to any port 80,443

# Run Nginx
#systemctl start nginx
#systemctl enable nginx

service TM start

# Change NGINX index.html
rm -rf /var/www/html/*
cp $MAIN_DIR/samples/sample-index.html /var/www/html/index.html

# Restart NGINX
#systemctl restart nginx

TM_PHP=/usr/local/TM/php/bin/php

mkdir -p /usr/local/TM/web
cp -r $MAIN_DIR/web/* /usr/local/TM/web
cp $MAIN_DIR/web/.env.example /usr/local/TM/web/.env.example

mkdir -p /usr/local/TM/bin
cp -r $MAIN_DIR/bin/* /usr/local/TM/bin
cp -r $MAIN_DIR/samples/* /usr/local/TM/samples

mkdir -p /usr/local/TM/update
cp -r $MAIN_DIR/update/* /usr/local/TM/update
chmod +x /usr/local/TM/update/*

# Install Composer
cd /usr/local/TM/web

$TM_PHP -v
$TM_PHP -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
$TM_PHP ./composer-setup.php
$TM_PHP -r "unlink('composer-setup.php');"

COMPOSER_ALLOW_SUPERUSER=1 $TM_PHP ./composer.phar install --no-interaction

# Create database
PANEL_DB_NAME="TMdb"
PANEL_DB_USER="TMuser"
PANEL_DB_PASSWORD="TMpass"
create_mysql_db_and_user $PANEL_DB_NAME $PANEL_DB_USER $PANEL_DB_PASSWORD

# Configure the application
cp .env.example .env

sed -i "s/^APP_NAME=.*/APP_NAME=TMPanel/" .env
sed -i "s/^DB_DATABASE=.*/DB_DATABASE=$PANEL_DB_NAME/" .env
sed -i "s/^DB_USERNAME=.*/DB_USERNAME=$PANEL_DB_USER/" .env
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$PANEL_DB_PASSWORD/" .env
sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env

$TM_PHP artisan key:generate
$TM_PHP artisan migrate
$TM_PHP artisan db:seed

sudo chmod -R o+w /usr/local/TM/web/storage/
sudo chmod -R o+w /usr/local/TM/web/bootstrap/cache/
