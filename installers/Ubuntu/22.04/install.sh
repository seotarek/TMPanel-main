#!/bin/bash
INSTALL_DIR="/TM/install"

apt-get update && apt-get install ca-certificates

mkdir -p $INSTALL_DIR

cd $INSTALL_DIR

DEPENDENCIES_LIST=(
    "jq"
    "curl"
    "wget"
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
)
# Check if the dependencies are installed
for DEPENDENCY in "${DEPENDENCIES_LIST[@]}"; do
    apt install -y $DEPENDENCY
done

# Start MySQL
service mysql start

# Install TM PHP
wget https://github.com/seotarek/TMPanelPHPDist/raw/main/debian/php/dist/TM-php-8.2.0.deb
dpkg -i TM-php-8.2.0.deb

# Install TM NGINX
wget https://github.com/seotarek/TMPanelNginxDist/raw/main/debian/nginx/dist/TM-nginx-1.24.0.deb
dpkg -i TM-nginx-1.24.0.deb

service TM start

TM_PHP=/usr/local/TM/php/bin/php

wget https://github.com/seotarek/TMPanelWebDist/raw/main/TM-web-panel.zip
unzip -qq -o TM-web-panel.zip -d /usr/local/TM/web
rm -rf TM-web-panel.zip

chmod 711 /home
chmod -R 750 /usr/local/TM

# Go to web directory
cd /usr/local/TM/web


# Update mysql root password
MYSQL_ROOT_USERNAME="TM"
MYSQL_ROOT_PASSWORD="$(tr -dc a-za-z0-9 </dev/urandom | head -c 32; echo)"

mysql -uroot -proot <<MYSQL_SCRIPT
  CREATE USER '$MYSQL_ROOT_USERNAME'@'%' IDENTIFIED BY '$MYSQL_ROOT_PASSWORD';
  GRANT ALL PRIVILEGES ON *.* TO '$MYSQL_ROOT_USERNAME'@'%' WITH GRANT OPTION;
  FLUSH PRIVILEGES;
MYSQL_SCRIPT


# Create database
PANEL_DB_PASSWORD="$(tr -dc a-za-z0-9 </dev/urandom | head -c 32; echo)"
PANEL_DB_NAME="TM$(tr -dc a-za-z0-9 </dev/urandom | head -c 13; echo)"
PANEL_DB_USER="TM$(tr -dc a-za-z0-9 </dev/urandom | head -c 13; echo)"

mysql -uroot -proot <<MYSQL_SCRIPT
  CREATE DATABASE $PANEL_DB_NAME;
  CREATE USER '$PANEL_DB_USER'@'localhost' IDENTIFIED BY '$PANEL_DB_PASSWORD';
  GRANT ALL PRIVILEGES ON $PANEL_DB_NAME.* TO '$PANEL_DB_USER'@'localhost';
  FLUSH PRIVILEGES;
MYSQL_SCRIPT


# Configure the application
cp .env.example .env

sed -i "s/^APP_NAME=.*/APP_NAME=TM_PANEL/" .env
sed -i "s/^DB_DATABASE=.*/DB_DATABASE=$PANEL_DB_NAME/" .env
sed -i "s/^DB_USERNAME=.*/DB_USERNAME=$PANEL_DB_USER/" .env
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$PANEL_DB_PASSWORD/" .env
sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env

sed -i "s/^MYSQl_ROOT_USERNAME=.*/MYSQl_ROOT_USERNAME=$MYSQL_ROOT_USERNAME/" .env
sed -i "s/^MYSQL_ROOT_PASSWORD=.*/MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD/" .env

$TM_PHP artisan key:generate
$TM_PHP artisan migrate
$TM_PHP artisan db:seed

chmod -R o+w /usr/local/TM/web/storage/
chmod -R o+w /usr/local/TM/web/bootstrap/cache/

CURRENT_IP=$(curl ipinfo.io/ip)

echo "TMPanel downloaded successfully."
echo "Please visit http://$CURRENT_IP:8443 to continue installation of the panel."
