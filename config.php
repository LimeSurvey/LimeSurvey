<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or is 
* derivative of works licensed under the GNU General Public License or other 
* free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* $Id: config-defaults.php 4334 2008-02-25 13:21:10Z c_schmitz $
*/

/* IMPORTANT NOTICE
*  With LimeSurvey v1.70+ the configuration of LimeSurvey was simplified,
*  Now config.php only contains the basic required settings.
*  Some optional settings are also set by default in config-defaults.php.
*  If you want to change an optional parameter, DON'T change values in config-defaults.php!!!
*  Just copy the parameter into your config.php-file and adjust the value!
*  All settings in config.php overwrite the default values from config-defaults.php
*/


// Basic Setup

/*$databasetype       =   'odbc_mssql';       // ADOdb database driver - valid values are mysql, odbc_mssql or postgres
$databaselocation   =   'NOBODY\SQLEXPRESS';   // Network location of your Database - for odbc_mssql use the mssql servername, not localhost or IP
$databasename       =   'limesurvey';  // The name of the database that we will create
$databaseuser       =   'sa';        // The name of a user with rights to create db (or if db already exists, then rights within that db)
$databasepass       =   'alster';            // Password of db user
$dbprefix           =   'lime_';       // A global prefix that can be added to all LimeSurvey tables. Use this if you are sharing
 */ 
  
$databasetype       =   'mysql';       // ADOdb database driver - valid values are mysql, odbc_mssql or postgres
$databaselocation   =   'localhost';   // Network location of your Database - for odbc_mssql use the mssql servername, not localhost or IP
$databasename       =   'limesurvey';  // The name of the database that we will create
$databaseuser       =   'root';        // The name of a user with rights to create db (or if db already exists, then rights within that db)
$databasepass       =   '';            // Password of db user
$dbprefix           =   'lime_';       // A global prefix that can be added to all LimeSurvey tables. Use this if you are sharing
                                       // a database with other applications. Suggested prefix is 'lime_'
                                       // a database with other applications. Suggested prefix is 'lime_'

// File Locations
$rooturl            =   "http://{$_SERVER['HTTP_HOST']}/limesurvey"; //The root web url for your limesurvey installation (without a trailing slash). The double quotes (") are important.

$rootdir            =   dirname(__FILE__); // This is the physical disk location for your limesurvey installation. Normally you don't have to touch this setting.
                                           // If you use IIS then you MUST enter the complete rootdir e.g. : $rootDir='C:\Inetpub\wwwroot\limesurvey'!
                                           // Some IIS installations also require to use forward slashes instead of backslashes, e.g.  $rootDir='C:/Inetpub/wwwroot/limesurvey'!
                                           // If you use OS/2 this must be the complete rootdir with FORWARD slashes e.g.: $rootDir='c:/limesurvey';!
// Site Setup
$sitename           =   'LimeSurvey - Die ist ein sehr sehr sehr langer Titel!';     // The official name of the site (appears in the Window title)

$defaultuser        =   'admin';          // This is the default username when LimeSurvey is installed
$defaultpass        =   'password';       // This is the default password for the default user when LimeSurvey is installed

// Email Settings

$siteadminemail     =   'your@email.org'; // The default email address of the site administrator
$siteadminbounce    =   'your@email.org'; // The default email address used for error notification of sent messages for the site administrator (Return-Path)
$siteadminname      =   'Your Name';      // The name of the site administrator
$debug=2;

$emailmethod        =   'mail';           // The following values can be used:
									    // mail      -  use internal PHP Mailer
                                          // sendmail  -  use Sendmail Mailer
                                          // qmail     -  use Qmail MTA
                                          // smtp      -  use SMTP relaying

$emailsmtphost      =   'smtp.gmail.com:465';      // Sets the SMTP host. You can also specify a different port than 25 by using
                                          // this format: [hostname:port] (e.g. 'smtp1.example.com:25').

$emailsmtpuser      =   'carsten.schmitz.hh@gmail.com';               // SMTP authorisation username - only set this if your server requires authorization - if you set it you HAVE to set a password too
$emailsmtppassword  =   'u7bmdg7dju';               // SMTP authorisation password - empty password is not allowed
$emailsmtpssl       =   'tls';               // Set this to 'ssl' or 'tls' to use SSL/TLS for SMTP connection 
$usejpgraph         =   1;                //Set to 1 to enable
$jpgraphdir         =   'D:\web\TechPlat\apache\htdocs\jpgraph\src'; //The location of the jpgraph class (where jpgraph.php is)
                                                      // If you use IIS then you MUST enter the complete rootdir e.g. : $rootDir='C:\\Inetpub\\wwwroot\\jpgraph'
$jpgraphfont        =   'FF_ARIAL'; //The font to use with graphs. A failsafe setting would be 'FF_FONT1'
$jpgraphfontdir     =   '';         //On debian based systems, the fonts aren't in the old font dir of XFree86 : (/usr/X11R6/lib/X11/fonts/truetype/)
                                    //To have beautiful fonts with JpGraph it might be necessary to set this to a new path , for example: /usr/share/fonts/truetype/msttcorefonts/
$jpgraph_antialiasing	=	'0';        // Set to 1 to enable AntiAliasing, this will make your graph's curves smoother, but will take more than twise aslong to generate statistics or might even timeout.

?>
