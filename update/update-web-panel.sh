rm -rf /usr/local/TM/update/web-panel-latest
rm -rf /usr/local/TM/update/TM-web-panel.zip

wget https://github.com/seotarek/TMPanelWebDist/raw/main/TM-web-panel.zip
ls -la
unzip -o TM-web-panel.zip -d /usr/local/TM/update/web-panel-latest

rm -rf /usr/local/TM/web/vendor
rm -rf /usr/local/TM/web/composer.lock
rm -rf /usr/local/TM/web/routes
rm -rf /usr/local/TM/web/public
rm -rf /usr/local/TM/web/resources
rm -rf /usr/local/TM/web/database
rm -rf /usr/local/TM/web/config
rm -rf /usr/local/TM/web/app
rm -rf /usr/local/TM/web/bootstrap
rm -rf /usr/local/TM/web/lang
rm -rf /usr/local/TM/web/Modules
rm -rf /usr/local/TM/web/thirdparty

cp -r /usr/local/TM/update/web-panel-latest/vendor /usr/local/TM/web/vendor
cp /usr/local/TM/update/web-panel-latest/composer.lock /usr/local/TM/web/composer.lock
cp -r /usr/local/TM/update/web-panel-latest/routes /usr/local/TM/web/routes
cp -r /usr/local/TM/update/web-panel-latest/public /usr/local/TM/web/public
cp -r /usr/local/TM/update/web-panel-latest/resources /usr/local/TM/web/resources
cp -r /usr/local/TM/update/web-panel-latest/database /usr/local/TM/web/database
cp -r /usr/local/TM/update/web-panel-latest/config /usr/local/TM/web/config
cp -r /usr/local/TM/update/web-panel-latest/app /usr/local/TM/web/app
cp -r /usr/local/TM/update/web-panel-latest/bootstrap /usr/local/TM/web/bootstrap
cp -r /usr/local/TM/update/web-panel-latest/lang /usr/local/TM/web/lang
cp -r /usr/local/TM/update/web-panel-latest/Modules /usr/local/TM/web/Modules
cp -r /usr/local/TM/update/web-panel-latest/thirdparty /usr/local/TM/web/thirdparty

cp -r /usr/local/TM/update/web-panel-latest/db-migrate.sh /usr/local/TM/web/db-migrate.sh
chmod +x /usr/local/TM/web/db-migrate.sh
#
cd /usr/local/TM/web
#
#
#
#TM_PHP=/usr/local/TM/php/bin/php
##
#$TM_PHP -v
#$TM_PHP -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
#$TM_PHP ./composer-setup.php
#$TM_PHP -r "unlink('composer-setup.php');"

#rm -rf composer.lock
#COMPOSER_ALLOW_SUPERUSER=1 $TM_PHP composer.phar i --no-interaction --no-progress
#COMPOSER_ALLOW_SUPERUSER=1 $TM_PHP composer.phar dump-autoload --no-interaction

./db-migrate.sh

service TM restart
