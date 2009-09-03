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

$databasetype       =   'mysql';       // ADOdb database driver - valid values are mysql, odbc_mssql or postgres
$databaselocation   =   'localhost';   // Network location of your Database - for odbc_mssql use the mssql servername, not localhost or IP
$databasename       =   'limesurvey';  // The name of the database that we will create
$databaseuser       =   'root';        // The name of a user with rights to create db (or if db already exists, then rights within that db)
$databasepass       =   '';            // Password of db user
$dbprefix           =   'lime_';       // A global prefix that can be added to all LimeSurvey tables. Use this if you are sharing
                                       // a database with other applications. Suggested prefix is 'lime_'

// File Locations
$rooturl            =   "http://{$_SERVER['HTTP_HOST']}/limesurvey"; //The root web url for your limesurvey installation (without a trailing slash). The double quotes (") are important.

$rootdir            =   dirname(__FILE__); // This is the physical disk location for your limesurvey installation. Normally you don't have to touch this setting.
                                           // If you use IIS then you MUST enter the complete rootdir e.g. : $rootDir='C:\Inetpub\wwwroot\limesurvey'!
                                           // Some IIS installations also require to use forward slashes instead of backslashes, e.g.  $rootDir='C:/Inetpub/wwwroot/limesurvey'!
                                           // If you use OS/2 this must be the complete rootdir with FORWARD slashes e.g.: $rootDir='c:/limesurvey';!
// Site Setup
$sitename           =   'LimeSurvey';     // The official name of the site (appears in the Window title)

$defaultuser        =   'admin';          // This is the default username when LimeSurvey is installed
$defaultpass        =   'password';       // This is the default password for the default user when LimeSurvey is installed

// Email Settings

$siteadminemail     =   'your@email.org'; // The default email address of the site administrator
$siteadminbounce    =   'your@email.org'; // The default email address used for error notification of sent messages for the site administrator (Return-Path)
$siteadminname      =   'Your Name';      // The name of the site administrator

