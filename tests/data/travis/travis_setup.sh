#!/bin/sh

# Use this script file if you have a Travis docker image setup on your
# local system, as described here: https://docs.travis-ci.com/user/common-build-problems/#Troubleshooting-Locally-in-a-Docker-Image
#
# After docker has been installed, run the following commands:
# docker run --name travis-debug -dit travisci/ci-garnet:packer-1512502276-986baf0 /sbin/init
# docker exec -it travis-debug bash -l
# su - travis
# git clone --depth=1 --branch=master https://github.com/LimeSurvey/LimeSurvey.git LimeSurvey/LimeSurvey
# cd LimeSurvey/LimeSurvey
# chmod +x tests/data/travis/travis_setup.sh
# ./tests/data/travis/travis_setup.sh
# find application/ -type function -name "*.php" -exec php -l {} \;  | grep -v 'No syntax errors'
# sudo -u <your-web-user> DOMAIN=localhost phpunit
# (You might want to use the switch --stop-on-failure.)

curl -s -o archive.tar.bz2 https://storage.googleapis.com/travis-ci-language-archives/php/binaries/ubuntu/14.04/x86_64/php-7.2.tar.bz2 && tar xjf archive.tar.bz2 --directory /
git clone git://github.com/phpenv/phpenv.git ~/.phpenv
echo 'export PATH="$HOME/.phpenv/bin:$PATH"' >> ~/.bash_profile
echo 'eval "$(phpenv init -)"' >> ~/.bash_profile
exec $SHELL -l

phpenv global 7.2

#phpenv config-rm xdebug.ini
#phpunit --version
touch enabletests
composer install
chmod -R 776 tmp
chmod -R 776 tmp/runtime
chmod -R 776 upload
chmod -R 776 themes
mkdir -p tests/tmp/runtime
chmod -R 776 tests/tmp
chmod -R 776 tests/tmp/runtime
DBENGINE=MyISAM php application/commands/console.php install admin password TravisLS no@email.com verbose
cp application/config/config-sample-mysql.php application/config/config.php
# sed -i '59s/.*/        "debug"=>2,/' application/config/config.php

sudo apt-get update > /dev/null
sudo apt-get -y --force-yes install apache2 libapache2-mod-fastcgi nodejs firefox
sudo cp /usr/bin/firefox /usr/local/bin/firefox
sudo cp /usr/bin/firefox /usr/local/bin/firefox-bin
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/www.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/www.conf
sudo a2enmod rewrite actions fastcgi alias
echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
sudo sed -i -e "s,www-data,travis,g" /etc/apache2/envvars
sudo chown -R travis:travis /var/lib/apache2/fastcgi
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm
sudo cp -f tests/travis/travis-ci-apache /etc/apache2/sites-available/000-default.conf
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/000-default.conf
sudo service apache2 restart

# Chromedriver does not work on Travis.
#wget https://chromedriver.storage.googleapis.com/2.33/chromedriver_linux64.zip
#unzip chromedriver_linux64.zip

# Firefox headless.
wget "https://selenium-release.storage.googleapis.com/3.7/selenium-server-standalone-3.7.1.jar"
wget "https://github.com/mozilla/geckodriver/releases/download/v0.23.0/geckodriver-v0.23.0-linux64.tar.gz"
tar xvzf geckodriver-v0.23.0-linux64.tar.gz
export MOZ_HEADLESS=1
java -jar selenium-server-standalone-3.7.1.jar -enablePassThrough false 2> /dev/null &
