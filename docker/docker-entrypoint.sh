#!/bin/bash

apt-get update && apt-get install -y wget

#
#apt-get install libsodium-dev -y
#
#wget https://raw.githubusercontent.com/seotarek/TMPanel/main/installers/install.sh && chmod +x install.sh && ./install.sh
#
#ls -la

#curl http://localhost:8443

#tail -f /dev/null

cd e2e-tests


apt-get update && \
  apt-get install --no-install-recommends -y \
  libgtk2.0-0 \
  libgtk-3-0 \
  libnotify-dev \
  libgconf-2-4 \
  libgbm-dev \
  libnss3 \
  libxss1 \
  libasound2 \
  libxtst6 \
  xauth \
  xvfb \
  ttf-wqy-zenhei \
  ttf-wqy-microhei \
  xfonts-wqy \
  fonts-liberation \
  libgbm1 \
  libu2f-udev \
  libvulkan1

rm -rf /var/lib/apt/lists/*



add-apt-repository ppa:webupd8team/y-ppa-manager
apt-get update
apt-get install y-ppa-manager

# Get Chrome
wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | apt-key add -
sh -c 'echo "deb http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google.list'
apt-get update
apt-get install -y google-chrome-stable

/usr/bin/google-chrome

apt install -y curl

wget https://deb.nodesource.com/setup_20.x
mv setup_20.x /tmp/nodesource_setup.sh
bash /tmp/nodesource_setup.sh

apt --fix-broken install -y

apt install nodejs -y
apt install npm -y

npm install -g npm@latest --force
npm --version

npm install -g yarn@latest --force
yarn --version

npm install
npm run test


