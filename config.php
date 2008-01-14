<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* $Id$
*/


// === Basic Setup

/*$databasetype       =   "odbc_mssql";       // ADOdb database driver - valid values are mysql, odbc_mssql or postgres
$databaselocation   =   "NOBODYS\SQLEXPRESS";   // Network location of your Database - for odbc_mssql use the mssql servername, not localhost or IP
$databaseport       =   "default";     // The port of your Database - if you use a standard port leave on default
$databasename       =   "limesurvey";  // The name of the database that we will create
$databaseuser       =   "sa";        // The name of a user with rights to create db (or if db already exists, then rights within that db)
$databasepass       =   "alster";            // Password of db user
*/

$databasetype       =   "mysql";       // ADOdb database driver - valid values are mysql, odbc_mssql or postgres
$databaselocation   =   "localhost";   // Network location of your Database - for odbc_mssql use the mssql servername, not localhost or IP
$databaseport       =   "default";     // The port of your Database - if you use a standard port leave on default
$databasename       =   "limesurvey";  // The name of the database that we will create
$databaseuser       =   "root";        // The name of a user with rights to create db (or if db already exists, then rights within that db)
$databasepass       =   "";            // Password of db user


$dbprefix           =   "lime_";       // A global prefix that can be added to all LimeSurvey tables. Use this if you are sharing
                                       // a database with other applications. Suggested prefix is "lime_"
$databasetabletype  =   "myISAM";	   // Storage engine mysql should use when creating survey results tables and token tables (if mysql is used). If available, InnoDB is recommended. Default is myISAM.

// FILE LOCATIONS
$rooturl            =   "http://{$_SERVER['SERVER_NAME']}/limesurvey"; //The root web url for your limesurvey installation.
//$rooturl            =   "http://localhost:81/file:/D:/web/xampp/htdocs/limesurvey";

$relativeurl        =   "/limesurvey"; // the url relative to you DocumentRoot where is installed LimeSurvey. Usually same as $rooturl without http://{$_SERVER['SERVER_NAME']}. Used by Fcked Filemanager

$rootdir            =   dirname(__FILE__); // This is the physical disk location for your limesurvey installation. Normally you don't have to touch this setting.
                                           // If you use IIS then you MUST enter the complete rootdir e.g. : $rootDir="C:\Inetpub\wwwroot\limesurvey"!
                                           // Some IIS installations also require to use forward slashes instead of backslashes, e.g.  $rootDir="C:/Inetpub/wwwroot/limesurvey"!
                                           // If you use OS/2 this must be the complete rootdir with FORWARD slashes e.g.: $rootDir="c:/limesurvey";!

// === Advanced Setup

    //The following url and dir locations do not need to be modified unless you have a non-standard
    //LimeSurvey installation. Do not change unless you know what you are doing.
    $homeurl        =   "$rooturl/admin"; // The website location (url) of the admin scripts
    $publicurl      =   "$rooturl";       // The public website location (url) of the public survey script
    $tempurl        =   "$rooturl/tmp";
    $imagefiles     =   "$rooturl/images"; //Location of button bar files for admin script
    $homedir        =   "$rootdir/admin"; // The physical disk location of the admin scripts
    $publicdir      =   "$rootdir";       // The physical disk location of the public scripts
    $tempdir        =   "$rootdir/tmp";   // The physical location where LimeSurvey can store temporary files
                                          // Note: For OS/2 the $tempdir may need to be defined as an actual directory
                                          // example: "x:/limesurvey/tmp". We don't know why.

// Site Info
$sitename           =   "LimeSurvey";     // The official name of the site (appears in the Window title)
$scriptname         =   "admin.php";      // The name of the admin script

$defaultuser        =   "admin";          // This is the default username when LimeSurvey is installed
$defaultpass        =   "password";       // This is the default password for the default user when LimeSurvey is installed

// Site Settings
$lwcdropdowns       =   "R";              // SHOW LISTS WITH COMMENT in Public Survey as Radio Buttons (R) or Dropdown List (L)
$dropdownthreshold  =   "25";             // The number of answers to a list type question before it switches from Radio Buttons to List
                                          // Only applicable, of course, if you have chosen "R" for $dropdowns and/or $lwcdropdowns
$repeatheadings     =   "25";             // The number of answers to show before repeating the headings in array (flexible) questions. Set to 0 to turn this feature off
$minrepeatheadings  =   3;                // The minimum number of remaing answers that are required before repeating the headings in array (flexible) questions.
$defaultlang        =   "en";             // The default language to use - the available languages are the directory names in the /locale dir - for example de = German

$timeadjust         =   0;                // Number of hours to adjust between your webserver local time and your own local time (for datestamping responses)
$allowexportalldb   =   1;                // 0 will only export prefixed tables when doing a database dump. If set to 1 ALL tables in the database will be exported
$allowmandbackwards =   1;                // Allow moving backwards (ie: << prev) through survey if a mandatory question
                                          // has not been answered. 1=Allow, 0=Deny
$deletenonvalues    =   1;                // By default, LimeSurvey does not save responses to conditional questions that haven't been answered/shown. To have LimeSurvey save these responses change this value to 0.
$shownoanswer       =   1;                // Show "no answer" for non mandatory questions
$admintheme         =  "default";         // This setting specifys the directory where the admin finds it theme/css style files, e.g. setting 'default' points to /admin/styles/default

$defaulttemplate    =  "default";         // This setting specifys the default theme used for the "public list" of surveys

$allowedtemplateuploads = "gif,jpg,png";   // File types allowed to be uploaded in the templates section.


$debug              =   2;      // Set this to 1 if you are looking for errors. If you still get no errors after enabling this
                                // then please check your error-logs - either in your hosting provider admin panel or in some /logs dir.
                                // LimeSurvey developers set this to 2.   
$memorylimit       =  "16M";    // This sets how much memory LimeSurvey can access. 16M is the minimum (M=mb) recommended.
$translationmode    =   0;      // If interface translations are not working this might be because of a bug in your PHP version. 
                                // Set this to '1' to activate a workaround for this bug
                                
$sessionlifetime    =   3600;   // How long until a survey session expires in seconds

// Email Settings
// These settings determine how LimeSurvey will send emails

$siteadminemail     =   "your@email.org"; // The default email address of the site administrator
$siteadminbounce     =   "your@email.org"; // The default email address used for error notification of sent messages for the site administrator (Return-Path)
$siteadminname      =   "Your Name";      // The name of the site administrator

$emailmethod        =   "mail";           // The following values can be used:
									      // mail      -  use internal PHP Mailer
                                          // sendmail  -  use Sendmail Mailer
                                          // smtp      -  use SMTP relaying

$emailsmtphost      =   "localhost";      // Sets the SMTP host. All hosts must be separated by a semicolon.
                                          // You can also specify a different port for each host by using
                                          // this format: [hostname:port] (e.g. "smtp1.example.com:25;smtp2.example.com").

$emailsmtpuser      =   "";               // SMTP authorisation username - only set this if your server requires authorization - if you set it you HAVE to set a password too
$emailsmtppassword  =   "";               // SMTP authorisation password - empty password is not allowed
$emailsmtpssl       =   0;                // Set this to 1 to use SSL for SMTP connection 

$maxemails          =   50;               // The maximum number of emails to send in one go (this is to prevent your mail server or script from timeouting when sending mass mail)

// JPGRAPH Settings
// To use jpgraph you must install and set up jpgraph, available from http://www.aditus.nu/jpgraph/
// LimeSurvey has been tested using version 1.13. Documentation for this is available at the
// jpgraph website. LimeSurvey cannot assist in the setting up of this system.
// To use JPGraph adjust the next two lines, and adjust the location as suites.
$usejpgraph         =   0; //Set to 1 to enable
$jpgraphdir         =   "/var/apache/htdocs/jpgraph"; //The location of the jpgraph class (where jpgraph.php is)
                                                      // If you use IIS then you MUST enter the complete rootdir e.g. : $rootDir="C:\\Inetpub\\wwwroot\\jpgraph"
$jpgraphfont        =   "FF_ARIAL"; //The font to use with graphs. A failsafe setting would be "FF_FONT1"
$jpgraphfontdir     =   "";         //On debian based systems, the fonts aren't in the old font dir of XFree86 : (/usr/X11R6/lib/X11/fonts/truetype/)
                                    //To have beautiful fonts with JpGraph it might be necessary to set this to a new path , for example: /usr/share/fonts/truetype/msttcorefonts/
$jpgrath_AntiAliasing	=	"0"; // Set to 1 to enable AntiAliasing, this will make your graph's curves smoother, but will take more than twise aslong to generate statistics or might even timeout.

// CMS Integration Settings
// Set $embedded to true and specify the header and footer functions if the survey is to be displayed embedded in a CMS
$embedded = false;
$embedded_inc = "";             // path to the header to include if any
$embedded_headerfunc = "";      // e.g. COM_siteHeader for geeklog
$embedded_footerfunc = "";      // e.g. COM_siteFooter for geeklog

// Enable or Disable Ldap feature
$enableLdap = false;

// Experimental parameters, only change if you know what you're doing
//
// $filterout_incomplete_answers
//  * default behaviour of LimeS regarding answer records with no submitdate
//  * can be overwritten by module parameters
//         ("Filter-Out incomplete answers" checkbox when implemented)
$filterout_incomplete_answers = true;
//
// $stripQueryFromRefurl (default is false)
//  * default behaviour is to record the full referer url when requested
//  * set to true in order to remove the parameter part of the referrer url
//  $stripQueryFromRefurl = false;

// $defaulthtmleditormode
//  * sets the default mode for htmleditor: none, inline, popup
//    users without specific preference inherit this setup
//  * inline: inline replacement of fields by an HTML editor: 
//     --> slow but convenient and user friendly
//  * popup: adds an icon that runs a popup with and html editor 
//     --> faster, but html code is displayed on the form
//  * none: no html editor
$defaulthtmleditormode = 'inline';

// $useWebserverAuth
// Enable delegation of authentication to the webserver.
// If you set this parameter to true and set your webserver to authenticate
// users accessing the /admin subdirectory, then the username returned by
// the webserver will be trusted by LimeSurvey and used for authentication
// unless a username mapping is used see $userArrayMap below
//
// The user still needs to be defined in the limesurvey database in order to
// login and get his permissions
$useWebserverAuth = false;

// $userArrayMap
// Enable username mapping
// This parameter is an array mapping username from the webserver to username
// defined in LimeSurvey
// Can be usefull if you have no way to add an 'admin' user to the database
// used by the webserver, then you could map your true loginame to admin with
// $userArrayMap = Array ('mylogin' => 'admin');

//$filterxsshtml
// Enables filtering of suspicious html tags in survey, group, questions
// and answer texts in the administration interface
// Only set this to false if you absolutely trust the users 
// you created for the administration of  LimeSurvey and if you want to 
// allow these users to be able to use Javascript etc. . 
$filterxsshtml = true;


// $usercontrolSameGroupPolicy
// If this option is set to true, then limesurvey operators will only 'see'
// users that belong to at least one of their groups
// Otherwise they can see all operators defines in LimeSurvey
$usercontrolSameGroupPolicy = true;

//DO NOT CHANGE BELOW HERE --------------------

?>
