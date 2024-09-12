<img src="https://www.limesurvey.org/images/limesurvey/svg/logo_limesurvey_head.svg" width="600" alt='LimeSurvey Logo' />

# Sophisticated online survey software
![Build status](https://github.com/LimeSurvey/LimeSurvey/actions/workflows/main.yml/badge.svg)


## About
Limesurvey is the number one open-source survey software.

Advanced features like branching and multiple question types make it a valuable partner for survey-creation.

### Demo

See our [Administration Demo](http://demo.limesurvey.org/index.php?r=admin/authentication/sa/login).
The credentials are prefilled, just click **Log in**

Or try taking one of our [test surveys](https://survey.limesurvey.org/index.php?sid=78184&lang=en)


## How to install

### Release
We release early and often.
The latest stable release version is available for download at https://community.limesurvey.org/downloads/
We recommend using those.

### Repository
You may want to use the plain repository, which is also possible.

Please be advised, that we sometimes push development versions into the repository, which may not be working correctly.

## Self-Hosting Options

### Elestio

You can deploy LimeSurvey on Elestio using one-click deployment.

[![Deploy on Elestio](https://elest.io/images/logos/deploy-to-elestio-btn.png)](https://elest.io/open-source/limesurvey)

## Requirements

### Minimal
The absolute minimal requirements are:
 - Apache >= 2.4 | nginx >= 1.1 | any other php-ready webserver
 - php >= 7.2.5
    - with mbstring and pdo-database drivers
 - mysql >= 5.5.9 | pgsql >= 9 | mariadb >= 5.5  | mssql >= 2005

### Recommended
We recommend the following setup
 - Web server: nginx (most recent stable version)
 - PHP (most recent stable version)
    - with php-fpm, mbstring, gd2 with freetype, imap, ldap, zip, zlib and database drivers
 - MariaDB or MySQL (most recent stable version)

## Manual
for more information please refer to our [homepage](http://www.limesurvey.org), or have a look at the [manual](http://manual.limesurvey.org) 

## License
LimeSurvey software is licensed under the [GPL 2.0](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html), or at your choice any later version of the license.

Pictures and the LimeSurvey Logo are registered trademarks of LimeSurvey GmbH, Hamburg, Germany.

## Tools used
We are using BrowserStack to run manual tests on different browsers and devices

[![](https://raw.githubusercontent.com/LimeSurvey/LimeSurvey/master/docs/contributions/browserstack-logo.png "Checkout BrowserStack")](https://www.browserstack.com/)

We are using Github Actions to run automated tests before release.

![Main workflow](https://github.com/LimeSurvey/LimeSurvey/actions/workflows/main.yml/badge.svg)
