#!/bin/bash

MAIN_DIR=$(pwd)

sudo apt-get update -y && sudo apt-get upgrade -y

# Build Essentials
sudo apt-get install -y build-essential
sudo apt-get install -y libsodium-dev
sudo apt-get install -y libonig-dev

# Install Dependencies
DEPENDENCIES_LIST=(
     "re2c"
     "ccache"
     "mysql-server"
     "libbz2-dev"
     "libcurl4-gnutls-dev"
     "libenchant-dev"
     "libfreetype6-dev"
     "libgmp-dev"
     "libicu-dev"
     "libjpeg-dev"
     "libkrb5-dev"
     "libonig-dev"
     "libpng-dev"
     "libpq-dev"
     "libsasl2-dev"
     "libsqlite3-dev"
     "libsodium-dev"
     "libtidy-dev"
     "libwebp-dev"
     "libxml2-dev"
     "libxpm-dev"
     "libxslt1-dev"
     "libzip-dev"
     "autoconf"
     "re2c"
     "bison"
     "sqlite3"
     "libsqlite3-dev"
     "libpq-dev"
     "libfcgi-dev"
     "libfcgi0ldbl"
     "libjpeg-dev"
     "libpng-dev"
     "libssl-dev"
     "libcurl4-openssl-dev"
     "libxpm-dev"
     "libgd-dev"
     "libmysqlclient-dev"
     "libfreetype6-dev"
     "libxslt1-dev"
     "libpspell-dev"
     "libzip-dev"
     "libicu-dev"
     "libldap2-dev"
     "libxslt-dev"
     "libssl-dev"
     "libldb-dev"
     "libgccjit-10-dev"
     "libargon2-dev"
     "zlib1g-dev"
     "binutils"
     "libtool"
     "bison"
     "re2c"
     "pkg-config"
     "make"
     "autoconf"
     "automake"
)
# Check if the dependencies are installed
for DEPENDENCY in "${DEPENDENCIES_LIST[@]}"; do
    echo "Installing $DEPENDENCY..."
    sudo apt install -y $DEPENDENCY
done

# Download PHP source
wget http://de2.php.net/distributions/php-8.2.0.tar.gz
tar -zxvf php-8.2.0.tar.gz
cd php-8.2.0

# Configure PHP
sudo make LIBDIR=/usr/lib/$(arch)-linux-gnu install

./buildconf --force
./configure --prefix=/usr/local/TM/php \
				--with-libdir=lib/$(arch)-linux-gnu \
				--enable-fpm --with-fpm-user=TMweb --with-fpm-group=TMweb \
				--with-openssl \
				--with-mysqli \
        --with-pdo-mysql=mysqlnd \
        --with-mysqli=mysqlnd \
		    --with-pdo-sqlite \
				--with-gettext \
				--with-curl \
				--enable-intl \
				--with-zip \
				--with-zlib \
				--with-gmp \
				--with-sodium \
			  --enable-sockets \
				--enable-mbstring \
				--with-libdir=lib/$(arch)-linux-gnu

#sudo ./configure --prefix=/usr/local/TM/php \
#        --enable-fpm \
#        --with-fpm-user=admin \
#        --with-fpm-group=admin \
#        --enable-phpdbg \
#        --enable-fpm \
#        --with-pdo-mysql=mysqlnd \
#        --with-mysqli=mysqlnd \
#        --with-pgsql \
#        --with-pdo-pgsql \
#        --with-pdo-sqlite \
#        --enable-intl \
#        --without-pear \
#        --enable-gd \
#        --with-jpeg \
#        --with-webp \
#        --with-freetype \
#        --with-xpm \
#        --enable-exif \
#        --with-zip \
#        --with-zlib \
#        --enable-soap \
#        --enable-xmlreader \
#        --with-xsl \
#        --with-tidy \
#        --enable-sysvsem \
#        --enable-sysvshm \
#        --enable-shmop \
#        --enable-pcntl \
#        --with-readline \
#        --enable-mbstring \
#        --with-curl \
#        --with-gettext \
#        --enable-sockets \
#        --with-bz2 \
#        --with-openssl \
#        --with-gmp \
#        --enable-bcmath \
#        --enable-calendar \
#        --enable-ftp \
#        --enable-sysvmsg \
#        --with-sodium \
#        --enable-zend-test=shared \
#        --enable-dl-test=shared \
#        --enable-werror \
#        --with-pear \
#        --with-libdir=lib/$(arch)-linux-gnu

# Compile PHP
sudo make -j 4
#sudo make test
sudo make install

/usr/local/TM/php/bin/php -v

PACKAGE_MAIN_DIR=$MAIN_DIR/TM-php-8.2.0
sudo mkdir $PACKAGE_MAIN_DIR

# Create debian package directories
sudo mkdir -p $PACKAGE_MAIN_DIR/DEBIAN
sudo mkdir -p $PACKAGE_MAIN_DIR/usr/local/TM

# Copy php compiled files
sudo mv /usr/local/TM/php $PACKAGE_MAIN_DIR/usr/local/TM
sudo cp $MAIN_DIR/php-fpm.conf $PACKAGE_MAIN_DIR/usr/local/TM/php/etc
sudo mv $PACKAGE_MAIN_DIR/usr/local/TM/php/sbin/php-fpm $PACKAGE_MAIN_DIR/usr/local/TM/php/sbin/TM-php-fpm

# Copy debian package META file
sudo cp $MAIN_DIR/control $PACKAGE_MAIN_DIR/DEBIAN
sudo cp $MAIN_DIR/postinst $PACKAGE_MAIN_DIR/DEBIAN
sudo cp $MAIN_DIR/postrm $PACKAGE_MAIN_DIR/DEBIAN

# Set debian package post files permissions
sudo chmod +x $PACKAGE_MAIN_DIR/DEBIAN/postinst
sudo chmod +x $PACKAGE_MAIN_DIR/DEBIAN/postrm

# Make debian package
sudo dpkg-deb --build $PACKAGE_MAIN_DIR
sudo dpkg --info $MAIN_DIR/TM-php-8.2.0.deb
sudo dpkg --contents $MAIN_DIR/TM-php-8.2.0.deb

# Move debian package to dist folder
sudo mkdir -p $MAIN_DIR/dist
sudo mv $MAIN_DIR/TM-php-8.2.0.deb $MAIN_DIR/dist
