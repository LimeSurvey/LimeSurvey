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

//Security Checked: POST, GET, SESSION, DB, REQUEST, returnglobal

//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {safe_die("Cannot run this script directly");}

// Include version information
require($rootdir.'/version.php');
require($rootdir."/common_functions.php");
// Include dTexts
require_once(dirname(__FILE__).'/classes/dTexts/dTexts.php');

// Check for most necessary requirements
// Now check for PHP & db version
// Do not localize/translate this!
$ver = explode( '.', PHP_VERSION );
$ver_num = $ver[0] . $ver[1] . $ver[2];
$dieoutput='';
if ( $ver_num < 500 )
{
    $dieoutput .= 'This script can only be run on PHP version 5.x or later! Your version: '.phpversion().'<br />';
}
if (!function_exists('mb_convert_encoding'))
{
    $dieoutput .= "This script needs the PHP Multibyte String Functions library installed: See <a href='http://docs.limesurvey.org/tiki-index.php?page=Installation+FAQ'>FAQ</a> and <a href='http://de.php.net/manual/en/ref.mbstring.php'>PHP documentation</a><br />";
}
if ($dieoutput!='') die($dieoutput);

if (!isset($debug)) {$debug=0;}  // for some older config.php's

if ($debug>0) {//For debug purposes - switch on in config.php
    @ini_set("display_errors", 1);
    error_reporting(E_ALL);
}

if ($debug>2) {//For debug purposes - switch on in config.php
    error_reporting(E_ALL | E_STRICT);
}

if (ini_get("max_execution_time")<1200) @set_time_limit(1200); // Maximum execution time - works only if safe_mode is off
//@ini_set("memory_limit",$memorylimit); // Set Memory Limit for big surveys

$maildebug='';


// The following function (when called) includes FireBug Lite if true
define('FIREBUG' , $use_firebug_lite);

define('ADODB_ASSOC_CASE', 2); // needed to set proper upper/lower casing for mssql

##################################################################################

require_once ($rootdir.'/classes/adodb/adodb.inc.php');
require_once ($rootdir.'/classes/datetimeconverter/class.datetimeconverter.php');
require_once ($rootdir.'/classes/phpmailer/class.phpmailer.php');
require_once ($rootdir.'/classes/php-gettext/gettext.inc');
require_once ($rootdir.'/classes/core/surveytranslator.php');
require_once ($rootdir.'/classes/core/sanitize.php');

//  DB session handling
if ($sessionhandler=='db')
{
    require_once($rootdir."/classes/adodb/session/adodb-session2.php");
    $sessionoptions['table'] = $dbprefix.'sessions';
    ADOdb_Session::config($databasetype, $databaselocation, $databaseuser, $databasepass, $databasename, $sessionoptions);
}


$dbprefix=strtolower($dbprefix);
define("_PHPVERSION", phpversion()); // This is the same as the server defined 'PHP_VERSION'


// Deal with server systems having not set a default time zone
if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
@date_default_timezone_set(@date_default_timezone_get());


//Every 50th time clean up the temp directory of old files (older than 1 day)
//depending on the load the  probability might be set higher or lower
if (rand(1,50)==25)
{
    cleanTempDirectory();
}

// Array of JS and CSS scripts to include in client header
$js_header_includes = array();
$css_header_includes =  array();

// JS scripts and CSS to include in admin header
// updated by admin scripts
$js_admin_includes = array();
$css_admin_includes = array();

/*
 * $sourcefrom variable checks the location of the current script against
 * the administration directory, and if the current script is running
 * in the administration directory, it is set to "admin". Otherwise it is set
 * to "public". When $sourcefrom is "admin" certain administration only functions
 * are loaded.
 */

$scriptlocation=realpath(".");
$slashlesspath=str_replace(array("\\", "/"), "", $scriptlocation);
$slashlesshome=str_replace(array("\\", "/"), "", $homedir);

// Uncomment the following line for debug purposes
// echo $slashlesspath." - ".$slashlesshome;

if (strcasecmp($slashlesshome, $slashlesspath) == 0) {
    if (strcasecmp($slashlesshome."install", $slashlesspath) != 0)
    {
        $sourcefrom="admin";
    }
    else
    {
        $sourcefrom="install";
    }
} else {
    $sourcefrom="public";
}

// Set path for captcha verification.php
if ($sourcefrom == "admin")
{
    $captchapath='../';
}
else
{
    $captchapath=$rooturl.'/';  
}


//BEFORE SESSIONCONTOL BECAUSE OF THE CONNECTION
//CACHE DATA
$connect=ADONewConnection($databasetype);
$database_exists = FALSE;
switch ($databasetype)
{
    case "postgres":
    case "mysqli":
    case "mysql": if ($databaseport!="default") {$dbhost="$databaselocation:$databaseport";}
    else {$dbhost=$databaselocation;}
    break;
    case "mssql_n":
	case "mssqlnative":
    case "mssql": if ($databaseport!="default") {$dbhost="$databaselocation,$databaseport";}
    else {$dbhost=$databaselocation;}
    break;
    case "odbc_mssql": $dbhost="Driver={SQL Server};Server=$databaselocation;Database=".$databasename;
    break;

    default: safe_die("Unknown database type");
}
// Now try connecting to the database
if ($databasepersistent==true)
{
    if (@$connect->PConnect($dbhost, $databaseuser, $databasepass, $databasename))
    {
        $database_exists = TRUE;
    }
    else {
        // If that doesnt work try connection without database-name
        $connect->database = '';
        if (!@$connect->PConnect($dbhost, $databaseuser, $databasepass))
        {
            safe_die("Can't connect to LimeSurvey database. Reason: ".$connect->ErrorMsg());
        }
    }
}
else
{
    if (@$connect->Connect($dbhost, $databaseuser, $databasepass, $databasename))
    {
        $database_exists = TRUE;
    }
    else {
        // If that doesnt work try connection without database-name
        $connect->database = '';
        if (!@$connect->Connect($dbhost, $databaseuser, $databasepass))
        {
            safe_die("Can't connect to LimeSurvey database. Reason: ".$connect->ErrorMsg());
        }
    }
}

// AdoDB seems to be defaulting to ADODB_FETCH_NUM and we want to be sure that the right default mode is set

$connect->SetFetchMode(ADODB_FETCH_ASSOC);

$dbexistsbutempty=($database_exists && !tableExists('surveys'));



if ($databasetype=='mysql' || $databasetype=='mysqli') {
    if ($debug>1) { @$connect->Execute("SET SESSION SQL_MODE='STRICT_ALL_TABLES,ANSI'"); } //for development - use mysql in the strictest mode  //Checked
    $infoarray=$connect->ServerInfo();
    if (version_compare ($infoarray['version'],'4.1','<'))
    {
        safe_die ("<br />Error: You need at least MySQL version 4.1 to run LimeSurvey. Your version:".$infoarray['version']);
    }
    @$connect->Execute("SET CHARACTER SET 'utf8'");  //Checked
    @$connect->Execute("SET NAMES 'utf8'");  //Checked
}

// Setting dateformat for mssql driver. It seems if you don't do that the in- and output format could be different
if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n' || $databasetype=='mssqlnative') {
    @$connect->Execute('SET DATEFORMAT ymd;');     //Checked
    @$connect->Execute('SET QUOTED_IDENTIFIER ON;');     //Checked
}


// Check if the DB is up to date
If ($dbexistsbutempty && $sourcefrom=='admin') {
    die ("<br />The LimeSurvey database does exist but it seems to be empty. Please run the <a href='$homeurl/install/index.php'>install script</a> to create the necessary tables.");
}

// Default global values that should not appear in config-defaults.php
$updateavailable=0;
$updatebuild='';
$updateversion='';
$updatelastcheck='';
$updatekey='';
$updatekeyvaliduntil='';

require ($homedir.'/globalsettings.php');
SSL_mode();// This really should be at the top but for it to utilise getGlobalSetting() it has to be here

$showXquestions = getGlobalSetting('showXquestions');
$showgroupinfo = getGlobalSetting('showgroupinfo');
$showqnumcode = getGlobalSetting('showqnumcode');

if ($sourcefrom == "admin")
{
    require_once('admin_functions.php');
} 

// Check if the DB is up to date
If (!$dbexistsbutempty && $sourcefrom=='admin')
{
    $usrow = getGlobalSetting('DBVersion');
    if (intval($usrow)<$dbversionnumber)
    {
        $action='';
        require_once($rootdir.'/classes/core/language.php');
        $clang = new limesurvey_lang($defaultlang);
        include_once($homedir.'/update/updater.php');
        $output=CheckForDBUpgrades();
        echo $output;
        echo "<br /><a href='$homeurl'>".$clang->gT("Back to main menu")."</a>";
        updatecheck();
        die();
    }

      if (is_dir($homedir."/install") && $debug<2)
       {
        die ("<p style='text-align: center; margin-left: auto; margin-right: auto; width: 500px; margin-top: 50px;'><img src='../images/limecursor-handle.png' /><strong>Congratulations</strong><br /><br />Your installation is now complete. The final step is to remove or rename the LimeSurvey installation directory (admin/install) on your server since it may be a security risk.<br /><br />Once this directory has been removed or renamed you will be able to log in to your new LimeSurvey Installation.<br /><br /><a href='admin.php'>Try again</a></p>");
       }  
}

//Admin menus and standards
//IF THIS IS AN ADMIN SCRIPT, RUN THE SESSIONCONTROL SCRIPT
if ($sourcefrom == "admin")
{
    include($homedir."/sessioncontrol.php");
    /**
     * @param string $htmlheader
     * This is the html header text for all administration pages
     *
     */
    $htmlheader = getAdminHeader();
}

//SET LANGUAGE DIRECTORY
if ($sourcefrom == "admin")
{
    $langdir="$publicurl/locale/".$_SESSION['adminlang']."/help";
    $langdirlocal="$rootdir/locale/".$_SESSION['adminlang']."/help";

    if (!is_dir($langdirlocal))  // is_dir only works on local dirs
    {
        $langdir="$publicurl/locale/en/help"; //default to english if there is no matching language dir
    }
}

if ($sourcefrom == "admin" && $buildnumber != "" && $updatecheckperiod>0 && $updatelastcheck<date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", "-".$updatecheckperiod." days"))
{
    updatecheck();
}



//SET LOCAL TIME
if (substr($timeadjust,0,1)!='-' && substr($timeadjust,0,1)!='+') {$timeadjust='+'.$timeadjust;}
if (strpos($timeadjust,'hours')===false && strpos($timeadjust,'minutes')===false && strpos($timeadjust,'days')===false)
{
    $timeadjust=$timeadjust.' hours';
}

// SITE STYLES
$setfont = "<font size='2' face='verdana'>";
$singleborderstyle = "style='border: 1px solid #111111'";



// Closing PHP tag intentionally left out - yes, it is okay
