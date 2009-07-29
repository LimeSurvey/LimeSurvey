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

##################################################################################

$versionnumber = "1.85+";
$dbversionnumber = 138;
$buildnumber = "";

##################################################################################

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


if ($debug>0) {//For debug purposes - switch on in config.php
        @ini_set("display_errors", 1);
        error_reporting(E_ALL); 
}

if ($debug>2) {//For debug purposes - switch on in config.php
        error_reporting(E_ALL | E_STRICT); 
}

 


if (ini_get("max_execution_time")<600) @set_time_limit(600); // Maximum execution time - works only if safe_mode is off
@ini_set("memory_limit",$memorylimit); // Set Memory Limit for big surveys 

$maildebug='';
                  

// The following function (when called) includes FireBug Lite if true 
define('FIREBUG' , $use_firebug_lite);

define('ADODB_ASSOC_CASE', 2); // needed to set proper upper/lower casing for mssql

##################################################################################

require_once ($rootdir.'/classes/adodb/adodb.inc.php');
require_once ($rootdir.'/classes/datetimeconverter/class.datetimeconverter.php');
require_once ($rootdir.'/classes/phpmailer/class.phpmailer.php');
require_once ($rootdir.'/classes/php-gettext/gettextinc.php');
require_once ($rootdir.'/classes/core/surveytranslator.php');
require_once ($rootdir.'/classes/core/sanitize.php');


$dbprefix=strtolower($dbprefix);
define("_PHPVERSION", phpversion()); // This is the same as the server defined 'PHP_VERSION'


//Deal with Xitami server issues 
//Todo: find out if this still is an issue with the latest Xitami server version
if(isset($_SERVER['SERVER_SOFTWARE']) && $_SERVER['SERVER_SOFTWARE'] == "Xitami") 
{
	$_SERVER['PHP_SELF'] = substr($_SERVER['SERVER_URL'], 0, -1) .$_SERVER['SCRIPT_NAME'];
}

// Deal with server systems having not set a default time zone
if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
@date_default_timezone_set(@date_default_timezone_get());


//Every 50th time clean up the temp directory of old files (older than 1 day)
//depending on the load the  probability might be set higher or lower
if (rand(1,50)==1) 
{
    cleanTempDirectory();   
}

// Array of JS and CSS scripts to include in client header
$js_header_includes = array();
$css_header_includes =  array();
 
// JS scripts and CSS to include in admin header
// updated by admin scripts
$js_adminheader_includes = array();   
$css_adminheader_includes = array();   

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
        $captchapath='';
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

$dbexistsbutempty=($database_exists && checkifemptydb());



if ($databasetype=='mysql' || $databasetype=='mysqli') {
    if ($debug>1) { @$connect->Execute("SET SESSION SQL_MODE='STRICT_ALL_TABLES,ANSI'"); } //for development - use mysql in the strictest mode  //Checked
    $infoarray=$connect->ServerInfo();
    if (version_compare ($infoarray['version'],'4.1','<'))
    {
      safe_die ("<br />Error: You need at least MySQL version 4.1 to run LimeSurvey. Your version:".$infoarray['version']);
    }
    @$connect->Execute("SET CHARACTER SET 'utf8'");  //Checked    
}

// Setting dateformat for mssql driver. It seems if you don't do that the in- and output format could be different
if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n') {
   @$connect->Execute('SET DATEFORMAT ymd;');     //Checked   
   @$connect->Execute('SET QUOTED_IDENTIFIER ON;');     //Checked   
}


// Check if the DB is up to date
If ($dbexistsbutempty && $sourcefrom=='admin') {
     die ("<br />The LimeSurvey database does exist but it seems to be empty. Please run the <a href='$homeurl/install/index.php'>install script</a> to create the necessary tables.");
}



// Check if the DB is up to date
If (!$dbexistsbutempty && $sourcefrom=='admin')
{
    $usquery = "SELECT stg_value FROM ".db_table_name("settings_global")." where stg_name='DBVersion'"; 
    $usresult = db_execute_assoc($usquery,'',false); //checked
    if (!$usresult)
    {
     die ("<br />The configured LimeSurvey database does not seem to exist and the LimeSurvey tables weren't found. <br />Please check the <a href='http://docs.limesurvey.org'>online manual</a> for installation instructions.<br />If you already edited config.php please run the <a href='$homeurl/install/index.php'>installation script</a>.");
	}
    $usrow = $usresult->FetchRow();
    if (intval($usrow['stg_value'])<$dbversionnumber)
    {
     die ("<br />The LimeSurvey database is not up to date. <br />Please run the <a href='$homeurl/install/index.php'>installation script</a> to upgrade your database.");
    }

    if (is_dir($homedir."/install") && $debug<2)
    {
     die ("<br />Everything is fine - you just forgot to delete or rename your LimeSurvey installation directory (/admin/install). <br />Please do so since it may be a security risk.");
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

//SET LOCAL TIME
if (substr($timeadjust,0,1)!='-' && substr($timeadjust,0,1)!='+') {$timeadjust='+'.$timeadjust;}
if (strpos($timeadjust,'hours')===false && strpos($timeadjust,'minutes')===false && strpos($timeadjust,'days')===false)
{
  $timeadjust=$timeadjust.' hours';  
}

// SITE STYLES
$setfont = "<font size='2' face='verdana'>";
$singleborderstyle = "style='border: 1px solid #111111'";

/**
     * showadminmenu() function returns html text for the administration button bar
 * 
     * @global string $homedir
     * @global string $scriptname
     * @global string $surveyid
     * @global string $setfont
     * @global string $imagefiles
     * @return string $adminmenu
     */
    function showadminmenu()
        {
        global $homedir, $scriptname, $surveyid, $setfont, $imagefiles, $clang, $debug, $action;
    
        $adminmenu  = "<div class='menubar'>\n";
        if  ($_SESSION['pw_notify'] && $debug<2)  {$adminmenu .="<div class='alert'>".$clang->gT("Warning: You are still using the default password ('password'). Please change your password and re-login again.")."</div>";}
        $adminmenu  .="\t<div class='menubar-title'>\n"
                    . "\t\t<strong>".$clang->gT("Administration")."</strong>";
		if(isset($_SESSION['loginID']))
			{
			$adminmenu  .= " --  ".$clang->gT("Logged in as:"). " <strong>"
                        . "<a href=\"#\" onclick=\"window.open('$scriptname?action=personalsettings', '_top')\" title=\"".$clang->gTview("Edit your personal preferences")."\" "
                        . "onmouseout=\"hideTooltip()\""
                        . "onmouseover=\"showTooltip(event,'".$clang->gT("Edit your personal preferences", "js")."');return false\">"
                        . $_SESSION['user']." <img src='$imagefiles/profile_edit.png' name='ProfileEdit' alt='".$clang->gT("Edit your personal preferences")."' "
                        . "title='' /></a>"
                        . "</strong>\n";
			}
       	$adminmenu .= "\t\t</div>\n"
                    . "\t\t\t<div class='menubar-main'>\n"
                    . "\t\t\t\t<div class='menubar-left'>\n"
                    . "\t\t\t\t\t<a href=\"#\" onclick=\"window.open('$scriptname', '_top')\" title=\"".$clang->gTview("Default Administration Page")."\"" .
                     "onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Default Administration Page", "js")."');return false\">" .
                     "<img src='$imagefiles/home.png' name='HomeButton' alt='".$clang->gT("Default Administration Page")."' "
                    ."title=''" ." /></a>\n";

		$adminmenu .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='11'   />\n"
                    . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt=''  />\n";

		// edit users
		$adminmenu .= "\t\t\t\t\t<a href=\"#\" onclick=\"window.open('$scriptname?action=editusers', '_top')\" title=\"".$clang->gTview("Create/Edit Users")."\" " .
					"onmouseout=\"hideTooltip()\""
					. "onmouseover=\"showTooltip(event,'".$clang->gT("Create/Edit Users", "js")."');return false\">" .
					 "<img src='$imagefiles/security.png' name='AdminSecurity'"
					." title='' alt='".$clang->gT("Create/Edit Users")."' /></a>";

		$adminmenu .="<a href=\"#\" onclick=\"window.open('$scriptname?action=editusergroups', '_top')\" title=\"".$clang->gTview("Create/Edit Groups")."\" "
					. "onmouseout=\"hideTooltip()\""
					. "onmouseover=\"showTooltip(event,'".$clang->gT("Create/Edit Groups", "js")."');return false\">" .
					"<img src='$imagefiles/usergroup.png' title=''  alt='".$clang->gT("Create/Edit Groups")."' /></a>\n" ;

		// check settings
						$adminmenu .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=checksettings', '_top')\" title=\"".$clang->gTview("Show System Summary")."\" "
					    . "onmouseout=\"hideTooltip()\""
                      	. "onmouseover=\"showTooltip(event,'".$clang->gT("Show System Summary", "js")."');return false\">"
						. "\t\t\t\t\t<img src='$imagefiles/summary.png' name='CheckSettings' title='"
						. "' alt='". $clang->gT("Show System Summary")."'/></a>"
						. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt=''  border='0' hspace='0' />\n";

		// check data cosistency
        if($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1)
			{
			$adminmenu .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=checkintegrity', '_top')\" title=\"".$clang->gTview("Check Data Integrity")."\" ".
						   "onmouseout=\"hideTooltip()\""
						  ."onmouseover=\"showTooltip(event,'".$clang->gT("Check Data Integrity", "js")."');return false\">".
						"<img src='$imagefiles/checkdb.png' name='CheckDataINtegrity' title=''  alt='".$clang->gT("Check Data Integrity")."'  /></a>\n";
			}
		else
			{
			$adminmenu .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40'  />\n";
			}

		// list surveys
		$adminmenu .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=listsurveys', '_top')\" title=\"".$clang->gTview("List Surveys")."\" "
		 			."onmouseout=\"hideTooltip()\""
                    ."onmouseover=\"showTooltip(event,'".$clang->gT("List Surveys", "js")."');return false\">\n"
		 			."<img src='$imagefiles/surveylist.png' name='ListSurveys' title=''"
		 			."  alt='".$clang->gT("List Surveys")."'  onclick=\"window.open('$scriptname?action=listsurveys', '_top')\" />"
                    ."</a>" ;

		// db backup & label editor
		if($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1)
			{
			$adminmenu  .= "<a href=\"#\" title=\"".$clang->gTview("Backup Entire Database")."\" "
						. "onclick=\"window.open('$scriptname?action=dumpdb', '_top')\""
						. "onmouseout=\"hideTooltip()\""
						. "onmouseover=\"showTooltip(event,'".$clang->gT("Backup Entire Database", "js")."');return false\">"
						."<img src='$imagefiles/backup.png' name='ExportDB' title='' alt='". $clang->gT("Backup Entire Database")."($surveyid)'  />"
						."</a>\n"
						. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt=''  border='0' hspace='0' />\n";
			}
		else
			{
			  $adminmenu .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40'   />\n";
			}
		if($_SESSION['USER_RIGHT_MANAGE_LABEL'] == 1)
			{
			$adminmenu  .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=labels', '_top')\" title=\"".$clang->gTview("Edit/Add Label Sets")."\" "
						. "onmouseout=\"hideTooltip()\""
						. "onmouseover=\"showTooltip(event,'".$clang->gT("Edit/Add Label Sets", "js")."');return false\">" .
						 "<img src='$imagefiles/labels.png'  name='LabelsEditor' title='' alt='". $clang->gT("Edit/Add Label Sets")."' /></a>\n"
						. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt=''  border='0' hspace='0' />\n";
           	}
		else
			{
			  $adminmenu .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40'   />\n";
			}
        if($_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] == 1)
			{
	        $adminmenu .= "<a href=\"#\" " .
	        			  "onclick=\"window.open('$scriptname?action=templates', '_top')\" title=\"".$clang->gTview("Template Editor")."\" "
	                    . "onmouseout=\"hideTooltip()\""
	                    . "onmouseover=\"showTooltip(event,'".$clang->gT("Template Editor", "js")."');return false\">" .
	                    "<img src='$imagefiles/templates.png' name='EditTemplates' title='' alt='". $clang->gT("Template Editor")."'  /></a>\n";
            }
            // survey select box
            $adminmenu .= "\t\t\t\t\t</div><div class='menubar-right'><font class=\"boxcaption\">".$clang->gT("Surveys").":</font>"
                        . "\t\t\t\t\t<select onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n"
                        . getsurveylist()
                        . "\t\t\t\t\t</select>\n";
            
            if($_SESSION['USER_RIGHT_CREATE_SURVEY'] == 1)
                {
            $adminmenu .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=newsurvey', '_top')\""
                        . "title=\"".$clang->gTview("Create or Import New Survey")."\" "
                        . "onmouseout=\"hideTooltip()\""
                        . "onmouseover=\"showTooltip(event,'".$clang->gT("Create or Import New Survey", "js")."');return false\">" .
                        "<img src='$imagefiles/add.png' name='AddSurvey' title='' alt='". $clang->gT("Create or Import New Survey")."' /></a>\n";
                 }


        if(isset($_SESSION['loginID'])) //ADDED to prevent errors by reading db while not logged in.
	    {
	        $adminmenu .= "\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' />\n"
                        . "\t\t<a href=\"#\" onclick=\"window.open('$scriptname?action=logout', '_top')\""
                        . "title=\"".$clang->gTview("Logout")."\" "
                        . "onmouseout=\"hideTooltip()\""
					    . "onmouseover=\"showTooltip(event,'".$clang->gT("Logout", "js")."');return false\">"
                        . "<img src='$imagefiles/logout.png' name='Logout'"
					    . "title='' alt='".$clang->gT("Logout")."'/></a>"
	                    . "\t\t<a href=\"#\" onclick=\"showhelp('show')\""
                        . "title=\"".$clang->gTview("Show Help")."\" "
                        . "onmouseout=\"hideTooltip()\""
                        . "onmouseover=\"showTooltip(event,'".$clang->gT("Show Help", "js")."');return false\">"
                        . "<img src='$imagefiles/showhelp.png' name='ShowHelp' title=''"
                        . "alt='". $clang->gT("Show Help")."'/></a>";
                        
	        $adminmenu .= "\t\t\t\t</div>\n"
                        . "\t\t\t</div>\n"
                        . "\t\t</div>\n";
            $adminmenu .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix
            if (count(getsurveylist(true))==0 && !isset($action) && !isset($surveyid)) {
                $adminmenu.= '<div style="width:500px;margin:0 auto;">'
                             .'<h2>'.sprintf($clang->gT("Welcome to %s!"),'LimeSurvey').'</h2>'
                             .'<p>'.$clang->gT("Some piece-of-cake steps to create your very own first survey:").'<br/>'
                             .'<ol>'
                             .'<li>'.sprintf($clang->gT('Create a new survey clicking on the %s icon in the upper right.'),"<img src='$imagefiles/add_small.png' name='ShowHelp' title='' alt='". $clang->gT("Add survey")."'/>").'</li>'
                             .'<li>'.$clang->gT('Create a new group inside your survey.').'</li>'
                             .'<li>'.$clang->gT('Create one or more question inside the new group.').'</li>'
                             .'<li>'.sprintf($clang->gT('Done. Test your survey using the %s icon.'),"<img src='$imagefiles/do_small.png' name='ShowHelp' title='' alt='". $clang->gT("Test survey")."'/>").'</li>'
                             .'</ol></p><br />&nbsp;</div>';
            }
                        
        }                 
        return $adminmenu;
        }



//DATA TYPES
$qtypeselect = getqtypelist();

function &db_execute_num($sql,$inputarr=false)
{
	global $connect;

// Todo: Set fetchmode to previous state after changing
	//$oldfetchmode=
    $connect->SetFetchMode(ADODB_FETCH_NUM);
	$dataset=$connect->Execute($sql,$inputarr);  //Checked    
	//$connect->SetFetchMode($oldfetchmode);
	return $dataset;
}

function &db_select_limit_num($sql,$numrows=-1,$offset=-1,$inputarr=false)
{
	global $connect;

	$connect->SetFetchMode(ADODB_FETCH_NUM);
	$dataset=$connect->SelectLimit($sql,$numrows,$offset,$inputarr=false) or safe_die($sql);
	return $dataset;
}

function &db_execute_assoc($sql,$inputarr=false,$silent=false)
{
	global $connect;
// Todo: Set fetchmode to previous state after changing 
//	$oldfetchmode=
    $connect->SetFetchMode(ADODB_FETCH_ASSOC);
	$dataset=$connect->Execute($sql,$inputarr);    //Checked    
	if (!$silent && !$dataset)  {safe_die($connect->ErrorMsg().':'.$sql);}      
//	$connect->SetFetchMode($oldfetchmode);
	return $dataset;
}

function &db_select_limit_assoc($sql,$numrows=-1,$offset=-1,$inputarr=false,$dieonerror=true)
{
	global $connect;

	$connect->SetFetchMode(ADODB_FETCH_ASSOC);
	$dataset=$connect->SelectLimit($sql,$numrows,$offset,$inputarr=false);
    if (!$dataset && $dieonerror) {safe_die($connect->ErrorMsg().':'.$sql);}
	return $dataset;
}

function db_quote_id($id)
// This functions quotes fieldnames accordingly 
{
	global $databasetype;
    // WE DONT HAVE nor USE other thing that alfanumeric characters in the field names
//	$quote = $connect->nameQuote;
//	return $quote.str_replace($quote,$quote.$quote,$id).$quote;

    switch ($databasetype)
    {
        case "mysqli" : 
        case "mysql" : 
            return "`".$id."`";
            break;
        case "mssql_n" : 
        case "mssql" : 
        case "odbc_mssql" : 
            return "[".$id."]";
            break;
        case "postgres": 
            return "\"".$id."\"";
            break;
        default: 
            return "`".$id."`";
    }
}

function db_random()
{
    global $connect,$databasetype;
    if ($databasetype=='odbc_mssql' || $databasetype=='mssql_n' || $databasetype=='odbtp')  {$srandom='NEWID()';}
    else {$srandom=$connect->random;}
    return $srandom;
    
}

function db_quote($str,$ispostvar=false)
// This functions escapes the string only inside 
{
    global $connect;
    return $connect->escape($str, $ispostvar);
}

function db_quoteall($str,$ispostvar=false)  
// This functions escapes the string inside and puts quotes around the string according to the used db type
// IF you are quoting a variable from a POST/GET then set $ispostvar to true so it doesnt get quoted twice.
{
	global $connect;
	if ($ispostvar) { return $connect->qstr($str, get_magic_quotes_gpc());}
	  else {return $connect->qstr($str);}
	
}

function db_table_name($name)
{
	global $dbprefix;
	return db_quote_id($dbprefix.$name);
}

/**
* returns the table name without quotes 
* 
* @param mixed $name
*/
function db_table_name_nq($name)
{
    global $dbprefix;
    return $dbprefix.$name;
}

/**
 *  Return a sql statement for finding LIKE named tables
* 
* @param mixed $table
 */
function db_select_tables_like($table)
{
	global $databasetype;
	switch ($databasetype) {
        case 'mysqli': 
		case 'mysql' : 
			return "SHOW TABLES LIKE '$table'";
        case 'odbtp' : 
        case 'mssql_n' : 
		case 'odbc_mssql' : 
			return "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES where TABLE_TYPE='BASE TABLE' and TABLE_NAME LIKE '$table'";
		case 'postgres' : 
			return "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' and table_name like '$table'";
		default: safe_die ("Couldn't create 'select tables like' query for connection type 'databaseType'"); 
	}	
}

/**
 *  Return a boolean stating if the table(s) exist(s)
 *  Accepts '%' in names since it uses the 'like' statement
* 
* @param mixed $table
 */
function db_tables_exist($table)
{
	global $connect;

	$surveyHasTokensTblQ = db_select_tables_like("$table");
	$surveyHasTokensTblResult = db_execute_num($surveyHasTokensTblQ); //Checked

	if ($surveyHasTokensTblResult->RecordCount() >= 1)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

/**
* getsurveylist() Queries the database (survey table) for a list of existing surveys
* 
* @param mixed $returnarray   boolean - if set to true an array instead of an HTML option list is given back
* 
* @global string $surveyid
* @global string $dbprefix
* @global string $scriptname
* @global string $connect
* @global string $clang
* 
* @return string This string is returned containing <option></option> formatted list of existing surveys
*
*/
function getsurveylist($returnarray=false)
{
    global $surveyid, $dbprefix, $scriptname, $connect, $clang, $timeadjust;
    $surveyidquery = " SELECT a.*, surveyls_title, surveyls_description, surveyls_welcometext, surveyls_url "
					." FROM ".db_table_name('surveys')." AS a "
					. "INNER JOIN ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=a.sid and surveyls_language=a.language) ";

	if ($_SESSION['USER_RIGHT_SUPERADMIN'] != 1)
	{
		$surveyidquery .= " INNER JOIN ".db_table_name('surveys_rights')." AS b ON a.sid = b.sid ";
		$surveyidquery .= "WHERE b.uid =".$_SESSION['loginID'];
	}

	$surveyidquery .= " order by active DESC, surveyls_title";
    if ($returnarray===true)
    {
        $surveyidresult = $connect->GetAll($surveyidquery);  //Checked
        return $surveyidresult;
    }
    $surveyidresult = db_execute_assoc($surveyidquery);  //Checked
    if (!$surveyidresult) {return "Database Error";}
    $surveyselecter = "";
    $surveynames = $surveyidresult->GetRows();
    $activesurveys='';
    $inactivesurveys='';
    $expiredsurveys='';
    if ($surveynames)
    {
        foreach($surveynames as $sv)
        {
            $sv['surveyls_title']=htmlspecialchars(strip_tags($sv['surveyls_title']));
            if($sv['active']!='Y') 
            { 
              $inactivesurveys .= "\t\t\t<option ";
        			if($_SESSION['loginID'] == $sv['owner_id']) {$inactivesurveys .= " style=\"font-weight: bold;\"";}
        			if ($sv['sid'] == $surveyid) {$inactivesurveys .= " selected='selected'"; $svexist = 1;}
                    $inactivesurveys .=" value='$scriptname?sid={$sv['sid']}'>{$sv['surveyls_title']}</option>\n";
            }
            elseif($sv['expires']!='' && $sv['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust))
            {
			        $expiredsurveys .="\t\t\t<option ";
			        if ($_SESSION['loginID'] == $sv['owner_id']) {$expiredsurveys .= " style=\"font-weight: bold;\"";}
			        if ($sv['sid'] == $surveyid) {$expiredsurveys .= " selected='selected'"; $svexist = 1;}
			        $expiredsurveys .=" value='$scriptname?sid={$sv['sid']}'>{$sv['surveyls_title']}</option>\n";
			}
            else
            {
                $activesurveys .= "\t\t\t<option ";
        		if($_SESSION['loginID'] == $sv['owner_id']) {$activesurveys .= " style=\"font-weight: bold;\"";}
        		if ($sv['sid'] == $surveyid) {$activesurveys .= " selected='selected'"; $svexist = 1;}
                $activesurveys .=" value='$scriptname?sid={$sv['sid']}'>{$sv['surveyls_title']}</option>\n";
            }
        }
		}
    //Only show each activesurvey group if there are some 
    if ($activesurveys!='') 
    {  
      $surveyselecter .= "\t\t\t<optgroup label='".$clang->gT("Active")."' class='activesurveyselect'>\n";
      $surveyselecter .= $activesurveys . "\t\t\t</optgroup>";
    }
    if ($expiredsurveys!='')
    {
	  $surveyselecter .= "\t\t\t<optgroup label='".$clang->gT("Expired")."' class='expiredsurveyselect'>\n";
	  $surveyselecter .= $expiredsurveys . "\t\t\t</optgroup>";
	}
    if ($inactivesurveys!='') 
    {  
      $surveyselecter .= "\t\t\t<optgroup label='".$clang->gT("Inactive")."' class='inactivesurveyselect'>\n";
      $surveyselecter .= $inactivesurveys . "\t\t\t</optgroup>";
    }    
    if (!isset($svexist)) {$surveyselecter = "\t\t\t<option selected='selected'>".$clang->gT("Please Choose...")."</option>\n".$surveyselecter;}
    else {$surveyselecter = "\t\t\t<option value='$scriptname?sid='>".$clang->gT("None")."</option>\n".$surveyselecter;}
    return $surveyselecter;
}

/**
* getquestions() queries the database for a list of all questions matching the current survey sid
* 
* @global string $surveyid
* @global string $gid
* @global string $qid
* @global string $dbprefix
* @global string $scriptname
* 
* @return This string is returned containing <option></option> formatted list of questions to current survey
*/
function getquestions($surveyid,$gid,$selectedqid)
{
	global $dbprefix, $scriptname, $connect, $clang;
//MOD for multilanguage surveys
	$s_lang = GetBaseLanguageFromSurveyID($surveyid);
	$qquery = 'SELECT * FROM '.db_table_name('questions')." WHERE sid=$surveyid AND gid=$gid AND language='{$s_lang}' order by question_order";
	$qresult = db_execute_assoc($qquery); //checked
	$qrows = $qresult->GetRows();

	if (!isset($questionselecter)) {$questionselecter="";}
	foreach ($qrows as $qrow)
	{
		$qrow['title'] = strip_tags($qrow['title']);
		$questionselecter .= "\t\t<option value='$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid={$qrow['qid']}'";
		if ($selectedqid == $qrow['qid']) {$questionselecter .= " selected='selected'"; $qexists="Y";}
		$questionselecter .=">{$qrow['title']}:";
		$questionselecter .= " ";
		$question=strip_tags($qrow['question']);
		if (strlen($question)<35)
		{
			$questionselecter .= $question;
		}
		else
		{   
			$questionselecter .= htmlspecialchars(mb_strcut(html_entity_decode($question,ENT_QUOTES,'UTF-8'), 0, 35, 'UTF-8'))."...";          
		}
		$questionselecter .= "</option>\n";
	}

	if (!isset($qexists))
	{
		$questionselecter = "\t\t<option selected='selected'>".$clang->gT("Please Choose...")."</option>\n".$questionselecter;
	}
	return $questionselecter;
}


/**
* Gets number of groups inside a particular survey
* 
* @param string $surveyid
* @param mixed $lang
*/
function getGroupSum($surveyid, $lang)
{
	global $surveyid,$dbprefix ;
	$sumquery3 = "SELECT * FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='".$lang."'"; //Getting a count of questions for this survey

	$sumresult3 = db_execute_assoc($sumquery3); //Checked
	$groupscount = $sumresult3->RecordCount();

	return $groupscount ;
}


/**
* Gets number of questions inside a particular group
* 
* @param string $surveyid
* @param mixed $groupid
*/
function getQuestionSum($surveyid, $groupid)
{
	global $surveyid,$dbprefix ;
	$s_lang = GetBaseLanguageFromSurveyID($surveyid);
	$sumquery3 = "SELECT * FROM ".db_table_name('questions')." WHERE gid=$groupid and sid=$surveyid AND language='{$s_lang}'"; //Getting a count of questions for this survey
	$sumresult3 = db_execute_assoc($sumquery3); //Checked
	$questionscount = $sumresult3->RecordCount();
	return $questionscount ;
}


/**
* getMaxgrouporder($surveyid) queries the database for the maximum sortorder of a group.
* 
* @param mixed $surveyid     
* @global string $surveyid
*/
function getMaxgrouporder($surveyid)
{
	global $surveyid ;
	$s_lang = GetBaseLanguageFromSurveyID($surveyid);
	$max_sql = "SELECT max( group_order ) AS max FROM ".db_table_name('groups')." WHERE sid =$surveyid AND language='{$s_lang}'" ;
	$max_result =db_execute_assoc($max_sql) ; //Checked
	$maxrow = $max_result->FetchRow() ;
	$current_max = $maxrow['max'];
	if($current_max=="")
	{
		return "0" ;
	}
	else return ++$current_max ;
}


/**
* getGroupOrder($surveyid,$gid) queries the database for the sortorder of a group.
* 
* @param mixed $surveyid
* @param mixed $gid
* @return mixed
*/
function getGroupOrder($surveyid,$gid)
{
	$s_lang = GetBaseLanguageFromSurveyID($surveyid);
	$grporder_sql = "SELECT group_order FROM ".db_table_name('groups')." WHERE sid =$surveyid AND language='{$s_lang}' AND gid=$gid" ;
	$grporder_result =db_execute_assoc($grporder_sql); //Checked
	$grporder_row = $grporder_result->FetchRow() ;
	$group_order = $grporder_row['group_order'];
	if($group_order=="")
	{
		return "0" ;
	}
	else return $group_order ;
}

/**
* getMaxquestionorder($gid) queries the database for the maximum sortorder of a question.
* 
* @global string $surveyid
*/
function getMaxquestionorder($gid)
{
	global $surveyid ;
	$gid=sanitize_int($gid);
	$s_lang = GetBaseLanguageFromSurveyID($surveyid);
	$max_sql = "SELECT max( question_order ) AS max FROM ".db_table_name('questions')." WHERE gid='$gid' AND language='$s_lang'";

	$max_result =db_execute_assoc($max_sql) ; //Checked
	$maxrow = $max_result->FetchRow() ;
	$current_max = $maxrow['max'];
	if($current_max=="")
	{
		return "0" ;
	}
	else return $current_max ;
}


/**
* getqtypelist() Returns list of question types available in LimeSurvey. Edit this if you are adding a new
*    question type
* 
* @global string $publicurl
* @global string $sourcefrom
* 
* @param string $SelectedCode Value of the Question Type (defaults to "T")
* @param string $ReturnType Type of output from this function (defaults to selector)
* 
* @return depending on $ReturnType param, returns a straight "array" of question types, or an <option></option> list
*/
function getqtypelist($SelectedCode = "T", $ReturnType = "selector")
{
	global $publicurl;
	global $sourcefrom, $clang;
	if ($sourcefrom == "admin")
	{
		$qtypes = array(
		"1"=>$clang->gT("Array (Flexible Labels) Dual Scale"),
		"5"=>$clang->gT("5 Point Choice"),
		"A"=>$clang->gT("Array (5 Point Choice)"),
		"B"=>$clang->gT("Array (10 Point Choice)"),
		"C"=>$clang->gT("Array (Yes/No/Uncertain)"),
		"D"=>$clang->gT("Date"),
		"E"=>$clang->gT("Array (Increase, Same, Decrease)"),
		"F"=>$clang->gT("Array (Flexible Labels)"),
		"G"=>$clang->gT("Gender"),
		"H"=>$clang->gT("Array (Flexible Labels) by Column"),
		"I"=>$clang->gT("Language Switch"),
		"K"=>$clang->gT("Multiple Numerical Input"),
		"L"=>$clang->gT("List (Radio)"),
		"M"=>$clang->gT("Multiple Options"),
		"N"=>$clang->gT("Numerical Input"),
		"O"=>$clang->gT("List With Comment"),
		"P"=>$clang->gT("Multiple Options With Comments"),
		"Q"=>$clang->gT("Multiple Short Text"),
		"R"=>$clang->gT("Ranking"),
		"S"=>$clang->gT("Short Free Text"),
		"T"=>$clang->gT("Long Free Text"),
		"U"=>$clang->gT("Huge Free Text"),
		"W"=>$clang->gT("List (Flexible Labels) (Dropdown)"),
		"X"=>$clang->gT("Boilerplate Question"),
		"Y"=>$clang->gT("Yes/No"),
		"Z"=>$clang->gT("List (Flexible Labels) (Radio)"),
		"!"=>$clang->gT("List (Dropdown)"),
		":"=>$clang->gT("Array (Multi Flexible) (Numbers)"),
		";"=>$clang->gT("Array (Multi Flexible) (Text)"),
		);
        asort($qtypes);
		if ($ReturnType == "array") {return $qtypes;}
		$qtypeselecter = "";
		foreach($qtypes as $TypeCode=>$TypeDescription)
		{
			$qtypeselecter .= "\t\t<option value='$TypeCode'";
			if ($SelectedCode == $TypeCode) {$qtypeselecter .= " selected='selected'";}
			$qtypeselecter .= ">$TypeDescription</option>\n";
		}
		return $qtypeselecter;
	}
}


/**
 * question_class() returns a class name for a given question type to allow custom styling for each question type.
 *
 * @param string $input containing unique character representing each question type.
 * @return string containing the class name for a given question type.
 */
function question_class($input)
{

	switch($input)
	{	// I think this is a bad solution to adding classes to question
		// DIVs but I can't think of a better solution. (eric_t_cruiser)

		case 'X': return 'boilerplate';		//	BOILERPLATE QUESTION
		case '5': return 'choice-5-pt-radio';	//	5 POINT CHOICE radio-buttons
		case 'D': return 'date';		//	DATE
		case 'Z': return 'list-radio-flexible';	//	LIST Flexible radio-button
		case 'L': return 'list-radio';		//	LIST radio-button
		case 'W': return 'list-dropdown-flexible'; //	LIST drop-down (flexible label)
		case '!': return 'list-dropdown';	//	List - dropdown
		case 'O': return 'list-with-comment';	//	LIST radio-button + textarea
		case 'R': return 'ranking';		//	RANKING STYLE
		case 'M': return 'multiple-opt';	//	MULTIPLE OPTIONS checkbox
		case 'I': return 'language';		//	Language Question
		case 'P': return 'multiple-opt-comments'; //	MULTIPLE OPTIONS WITH COMMENTS checkbox + text
		case 'Q': return 'multiple-short-txt';	//	TEXT
		case 'K': return 'numeric-multi';	//	MULTIPLE NUMERICAL QUESTION
		case 'N': return 'numeric';		//	NUMERICAL QUESTION TYPE
		case 'S': return 'text-short';		//	SHORT FREE TEXT
		case 'T': return 'text-long';		//	LONG FREE TEXT
		case 'U': return 'text-huge';		//	HUGE FREE TEXT
		case 'Y': return 'yes-no';		//	YES/NO radio-buttons
		case 'G': return 'gender';		//	GENDER drop-down list
		case 'A': return 'array-5-pt';		//	ARRAY (5 POINT CHOICE) radio-buttons
		case 'B': return 'array-10-pt';		//	ARRAY (10 POINT CHOICE) radio-buttons
		case 'C': return 'array-yes-uncertain-no'; //	ARRAY (YES/UNCERTAIN/NO) radio-buttons
		case 'E': return 'array-increase-same-decrease'; // ARRAY (Increase/Same/Decrease) radio-buttons
		case 'F': return 'array-flexible-row';	//	ARRAY (Flexible) - Row Format
		case 'H': return 'array-flexible-column'; //	ARRAY (Flexible) - Column Format
//		case '^': return 'slider';			//	SLIDER CONTROL
		case ':': return 'array-multi-flexi';	//	ARRAY (Multi Flexi) 1 to 10
		case ";": return 'array-multi-flexi-text';
		case "1": return 'array-flexible-duel-scale'; //	Array (Flexible Labels) dual scale
		default:  return 'generic_question';	//	Should have a default fallback
	};
};

if(!defined('COLSTYLE'))
{
/**
 * The following prepares and defines the 'COLSTYLE' constant which
 * dictates how columns are to be marked up for list type questions.
 *
 * $column_style is initialised at the end of config-defaults.php or from within config.php
 */
	if(	!isset($column_style)	||
		$column_style  != 'css' ||
		$column_style  != 'ul'	||
		$column_style  != 'table' ||
		$column_style  != null )
	{
		$column_style = 'ul';
	};
	define('COLSTYLE' ,strtolower($column_style), true);
};
if(!defined('MAX_COLUMNS'))
{
/**
 * The following prepares and defines the 'MAX_COLUMNS' constant which
 * dictates the maximum number of columns allowed when using display columns.
 *
 * $column_style is initialised at the end of config-defaults.php or from within config.php
 */
	$max_columns = isset($max_columns)?$max_columns:8;
	define('MAX_COLUMNS' , $max_columns , true);
};

function setup_columns($columns, $answer_count)
{
/**
 * setup_columns() defines all the html tags to be wrapped around
 * various list type answers.
 *
 * @param integer $columns - the number of columns, usually supplied by $dcols
 * @param integer $answer_count - the number of answers to a question, usually supplied by $anscount
 * @return array with all the various opening and closing tags to generate a set of columns.
 *
 * It returns an array with the following items:
 *    $wrapper['whole-start']   = Opening wrapper for the whole list
 *    $wrapper['whole-end']     = closing wrapper for the whole list
 *    $wrapper['col-devide']    = normal column devider
 *    $wrapper['col-devide-last'] = the last column devider (to allow
 *                                for different styling of the last
 *                                column
 *    $wrapper['item-start']    = opening wrapper tag for individual
 *                                option
 *    $wrapper['item-start-other'] = opening wrapper tag for other
 *                                option
 *    $wrapper['item-end']      = closing wrapper tag for individual
 *                                option
 *    $wrapper['maxrows']       = maximum number of rows in each
 *                                column
 *    $wrapper['cols']          = Number of columns to be inserted
 *                                (and checked against)
 *
 * It also expect the constant COLSTYLE which sets how columns should
 * be rendered.
 *
 * COLSTYLE is defined 30 lines above.
 *
 * - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
 * Columns are a problem.
 * Really there is no perfect solution to columns at the moment.
 *
 * -  Using Tables is problematic semanticly.
 * -  Using inline or float to create columns, causes the answers
 *    flows horizontally, not vertically which is not ideal visually.
 * -  Using CSS3 columns is also a problem because of browser support
 *    and also because if you have answeres split across two or more
 *    lines, and those answeres happen to fall at the bottom of a
 *    column, the answer might be split across columns as well as
 *    lines.
 * -  Using nested unordered list with the first level of <LI>s
 *    floated is the same as using tables and so is bad semantically
 *    for the same reason tables are bad.
 * -  Breaking the unordered lists into consecutive floated unordered
 *    lists is not great semantically but probably not as bad as
 *    using tables.
 *
 * Because I haven't been able to decide which option is the least
 * bad, I have handed over that responsibility to the admin who sets
 * LimeSurvey up on their server.
 *
 * There are four options:
 *    'css'   using one of the various CSS only methods for
 *            rendering columns.
 *            (Check the CSS file for your chosen template to see
 *             how columns are defined.)
 *    'ul'    using multiple floated unordered lists. (DEFAULT)
 *    'table' using conventional tables based layout.
 *     NULL   blocks the use of columns
 *
 * 'ul' is the default because it's the best possible compromise
 * between semantic markup and visual layout.
 */


	$colstyle = COLSTYLE;

/*
	if(defined('PRINT_TEMPLATE')) // This forces tables based columns for printablesurvey
	{
		$colstyle = 'table';
	};
*/
	if($columns < 2)
	{
		$colstyle = null;
		$columns = 1;
	}
	elseif($columns > MAX_COLUMNS)
	{
		$columns = MAX_COLUMNS;
	};

	if($columns > $answer_count)
	{
		$columns = $answer_count;
	};

	$class_first = '';
	if($columns > 1 && $colstyle != null)
	{
		if($colstyle == 'ul')
		{
			$ul = '-ul';
		}
		else
		{
			$ul = '';
		}
		$class_first = ' class="cols-'.$columns . $ul.' first"';
		$class = ' class="cols-'.$columns . $ul.'"';
		$class_last_ul = ' class="cols-'.$columns . $ul.' last"';
		$class_last_table = ' class="cols-'.$columns.' last"';
	}
	else
	{
		$class = '';
		$class_last_ul = '';
		$class_last_table = '';
	};

	$wrapper = array(
			 'whole-start'	=> "\n<ul$class_first>\n"
			,'whole-end'	=> "</ul>\n"
			,'col-devide'	=> ''
			,'col-devide-last' => ''
			,'item-start'	=> "\t<li>\n"
			,'item-start-other' => "\t<li class=\"other\">\n"
			,'item-end'	=> "\t</li>\n"
			,'maxrows'	=> ceil($answer_count/$columns) //Always rounds up to nearest whole number
			,'cols'		=> $columns
	);

	switch($colstyle)
	{
		case 'ul':	if($columns > 1)
				{
					$wrapper['col-devide']	= "\n</ul>\n\n<ul$class>\n";
					$wrapper['col-devide-last']	= "\n</ul>\n\n<ul$class_last_ul>\n";
				}
				break;

		case 'table':	$table_cols = '';
				for($cols = $columns ; $cols > 0 ; --$cols)
				{
					switch($cols)
					{
						case $columns:	$table_cols .= "\t<col$class_first />\n";
								break;
						case 1:		$table_cols .= "\t<col$class_last_table />\n";
								break;
						default:	$table_cols .= "\t<col$class />\n";
					};
				};

				if($columns > 1)
				{
					$wrapper['col-devide']	= "\t\t\t\t</ul>\n\t\t\t</td>\n\n\t\t\t<td>\n\t\t\t\t<ul>\n";
					$wrapper['col-devide-last']	= "\t\t\t\t</ul>\n\t\t\t</td>\n\n\t\t\t<td class=\"last\">\n\t\t\t\t<ul>\n";
				};
				$wrapper['whole-start']	= "\n<table$class>\n$table_cols\n\t<tbody>\n\t\t<tr>\n\t\t\t<td>\n\t\t\t\t<ul>\n";
				$wrapper['whole-end']	= "\t\t\t\t</ul>\n\t\t\t</td>\n\t\t</tr>\n\t</tbody>\n</table>\n";
				$wrapper['item-start']	= "\t\t\t\t\t<li>\n";
				$wrapper['item-end']	= "\t\t\t\t\t</li>\n";
	};

	return $wrapper;
};

function alternation($alternate = '' , $type = 'col')
{
/**
 * alternation() Returns a class identifyer for alternating between
 * two options. Used to style alternate elements differently. creates
 * or alternates between the odd string and the even string used in
 * as column and row classes for array type questions.
 *
 * @param string $alternate = '' (empty) (default) , 'array2' ,  'array1' , 'odd' , 'even'
 * @param string  $type = 'col' (default) or 'row'
 *
 * @return string representing either the first alternation or the opposite alternation to the one supplied..
 */
 /*
// The following allows type to be left blank for row in subsequent
// function calls.
// It has been left out because 'row' must be defined the first time
// alternation() is called. Since it is only ever written once for each
// while statement within a function, 'row' is always defined.
	if(!empty($alternate) && $type != 'row')
	{	if($alternate == ('array2' || 'array1'))
		{
			$type = 'row';
		};
	};
// It has been left in case it becomes useful but probably should be
// removed.
*/
	if($type == 'row')
	{
		$odd  = 'array2'; // should be row_odd
		$even = 'array1'; // should be row_even
	}
	else
	{
		$odd  = 'odd';	// should be col_odd
		$even = 'even';	// should be col_even
	};
	if($alternate == $odd)
	{
		$alternate = $even;
	}
	else
	{
		$alternate = $odd;
	};
	return $alternate;
}


/**
 * longest_string() returns the length of the longest string past to it.
 * @peram string $new_string
 * @peram integer $longest_length length of the (previously) longest string passed to it.
 * @return integer representing the length of the longest string passed (updated if $new_string was longer than $longest_length)
 *
 * usage should look like this: $longest_length = longest_string( $new_string , $longest_length );
 *
 */
function longest_string( $new_string , $longest_length )
{
	if($longest_length < strlen(trim(strip_tags($new_string))))
	{
		$longest_length = strlen(trim(strip_tags($new_string)));
	};
	return $longest_length;	
};



/**
* getNotificationlist() returns different options for notifications
* 
* @param string $notificationcode - the currently selected one
* 
* @return This string is returned containing <option></option> formatted list of notification methods for current survey
*/
function getNotificationlist($notificationcode)
{
	global $clang;
	$ntypes = array(
	"0"=>$clang->gT("No email notification"),
	"1"=>$clang->gT("Basic email notification"),
	"2"=>$clang->gT("Detailed email notification with result codes")
	);
	if (!isset($ntypeselector)) {$ntypeselector="";}
	foreach($ntypes as $ntcode=>$ntdescription)
	{
		$ntypeselector .= "\t\t<option value='$ntcode'";
		if ($notificationcode == $ntcode) {$ntypeselector .= " selected='selected'";}
		$ntypeselector .= ">$ntdescription</option>\n";
	}
	return $ntypeselector;
}


/**
* getgrouplist() queries the database for a list of all groups matching the current survey sid
* 
* @global string $surveyid
* @global string $dbprefix
* @global string $scriptname
* 
* @param string $gid - the currently selected gid/group
* 
* @return This string is returned containing <option></option> formatted list of groups to current survey
*/
function getgrouplist($gid)
{
	global $surveyid, $dbprefix, $scriptname, $connect, $clang;
	$groupselecter="";
    $gid=sanitize_int($gid);
    $surveyid=sanitize_int($surveyid);
	if (!$surveyid) {$surveyid=returnglobal('sid');}
	$s_lang = GetBaseLanguageFromSurveyID($surveyid);
	$gidquery = "SELECT gid, group_name FROM ".db_table_name('groups')." WHERE sid='{$surveyid}' AND  language='{$s_lang}'  ORDER BY group_order";
	$gidresult = db_execute_num($gidquery) or safe_die("Couldn't get group list in common.php<br />$gidquery<br />".$connect->ErrorMsg()); //Checked
	while($gv = $gidresult->FetchRow())
	{
		$groupselecter .= "\t\t<option";
		if ($gv[0] == $gid) {$groupselecter .= " selected='selected'"; $gvexist = 1;}
		$groupselecter .= " value='$scriptname?sid=$surveyid&amp;gid=$gv[0]'>".htmlspecialchars($gv[1])."</option>\n";
	}
	if ($groupselecter)
	{
		if (!isset($gvexist)) {$groupselecter = "\t\t<option selected='selected'>".$clang->gT("Please Choose...")."</option>\n".$groupselecter;}
		else {$groupselecter .= "\t\t<option value='$scriptname?sid=$surveyid&amp;gid='>".$clang->gT("None")."</option>\n";}
	}
	return $groupselecter;
}


function getgrouplist2($gid)
{
	global $surveyid, $dbprefix, $connect, $clang;
	$groupselecter = "";
	if (!$surveyid) {$surveyid=returnglobal('sid');}
	$s_lang = GetBaseLanguageFromSurveyID($surveyid);
	$gidquery = "SELECT gid, group_name FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='{$s_lang}' ORDER BY group_order";


	$gidresult = db_execute_num($gidquery) or safe_die("Plain old did not work!");   //Checked
	while ($gv = $gidresult->FetchRow())
	{
		$groupselecter .= "\t\t<option";
		if ($gv[0] == $gid) {$groupselecter .= " selected='selected'"; $gvexist = 1;}
		$groupselecter .= " value='$gv[0]'>".htmlspecialchars($gv[1])."</option>\n";
	}
	if ($groupselecter)
	{
		if (!$gvexist) {$groupselecter = "\t\t<option selected='selected'>".$clang->gT("Please Choose...")."</option>\n".$groupselecter;}
		else {$groupselecter .= "\t\t<option value=''>".$clang->gT("None")."</option>\n";}
	}
	return $groupselecter;
}


function getgrouplist3($gid)
{
	global $surveyid, $dbprefix, $connect;
    if (!$surveyid) {$surveyid=returnglobal('sid');}
	$groupselecter = "";
	$s_lang = GetBaseLanguageFromSurveyID($surveyid);
	$gidquery = "SELECT gid, group_name FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='{$s_lang}' ORDER BY group_order";


	$gidresult = db_execute_num($gidquery) or safe_die("Plain old did not work!");      //Checked
	while ($gv = $gidresult->FetchRow())
	{
		$groupselecter .= "\t\t<option";
		if ($gv[0] == $gid) {$groupselecter .= " selected='selected'"; $gvexist = 1;}
		$groupselecter .= " value='$gv[0]'>".htmlspecialchars($gv[1])."</option>\n";
	}
	return $groupselecter;
}

function getgrouplist4($gid)
{
	global $surveyid, $dbprefix, $connecti,$clang;
    if (!$surveyid) {$surveyid=returnglobal('sid');}
	$groupselecter = "";
	$s_lang = GetBaseLanguageFromSurveyID($surveyid);
	$gidquery = "SELECT group_name FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='{$s_lang}' and gid=$gid";


	$gidresult = db_execute_num($gidquery) or safe_die("Plain old did not work!");      //Checked
	while ($gv = $gidresult->FetchRow())
	{
		$groupselecter .= "\t\t".htmlspecialchars($gv[0])." - ".$clang->gT("Cannot be modified (Survey is active)")."\n";
	}
	return $groupselecter;
}


function getgrouplistlang($gid, $language)
{
	global $surveyid, $scriptname, $connect, $clang;

	$groupselecter="";
    if (!$surveyid) {$surveyid=returnglobal('sid');}
	$gidquery = "SELECT gid, group_name FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='".$language."' ORDER BY group_order";
	$gidresult = db_execute_num($gidquery) or safe_die("Couldn't get group list in common.php<br />$gidquery<br />".$connect->ErrorMsg());   //Checked
	while($gv = $gidresult->FetchRow())
	{
		$groupselecter .= "\t\t<option";
		if ($gv[0] == $gid) {$groupselecter .= " selected='selected'"; $gvexist = 1;}
		$groupselecter .= " value='$scriptname?sid=$surveyid&amp;gid=$gv[0]'>";
        if (strip_tags($gv[1]))
        {
            $groupselecter .= htmlspecialchars(strip_tags($gv[1]));
		} else {
		    $groupselecter .= htmlspecialchars($gv[1]);
		}
		$groupselecter .= "</option>\n";
	}
	if ($groupselecter)
	{
		if (!isset($gvexist)) {$groupselecter = "\t\t<option selected='selected'>".$clang->gT("Please Choose...")."</option>\n".$groupselecter;}
		else {$groupselecter .= "\t\t<option value='$scriptname?sid=$surveyid&amp;gid='>".$clang->gT("None")."</option>\n";}
	}
	return $groupselecter;
}


function getuserlist($outputformat='fullinfoarray')
{
	global $dbprefix, $connect, $databasetype;
	global $usercontrolSameGroupPolicy;
	
    if (isset($_SESSION['loginID']))
		{
			$myuid=sanitize_int($_SESSION['loginID']);
		}

	if ($_SESSION['USER_RIGHT_SUPERADMIN'] != 1 && isset($usercontrolSameGroupPolicy) &&
		$usercontrolSameGroupPolicy == true)
	{
		if (isset($myuid))
		{
			// List users from same group as me + all my childs	
            // a subselect is used here because MSSQL does not like to group by text
            // also Postgres does like this one better
			 	$uquery = " SELECT * FROM ".db_table_name('users')." where uid in 
                        (SELECT u.uid FROM ".db_table_name('users')." AS u, 
			 			".db_table_name('user_in_groups')." AS ga ,".db_table_name('user_in_groups')." AS gb 
			 			WHERE u.uid=$myuid 
			 			OR (ga.ugid=gb.ugid AND ( (gb.uid=$myuid AND u.uid=ga.uid) OR (u.parent_id=$myuid) ) ) 
			 			GROUP BY u.uid)";
		}
		else
		{
			return Array(); // Or die maybe
		}

	}
	else
	{
		$uquery = "SELECT * FROM ".db_table_name('users')." ORDER BY uid";
	}

	$uresult = db_execute_assoc($uquery); //Checked
    
    if ($uresult->RecordCount()==0)  
    //user is not in a group and usercontrolSameGroupPolicy is activated - at least show his own userinfo
    {
            $uquery = "SELECT u.* FROM ".db_table_name('users')." AS u WHERE u.uid=".$myuid;
            $uresult = db_execute_assoc($uquery);//Checked
    }

	$userlist = array();
	$userlist[0] = "Reserved for logged in user";
	while ($srow = $uresult->FetchRow())
	{
		if ($outputformat != 'onlyuidarray')
		{
			if ($srow['uid'] != $_SESSION['loginID'])
			{
				$userlist[] = array("user"=>$srow['users_name'], "uid"=>$srow['uid'], "email"=>$srow['email'], "password"=>$srow['password'], "full_name"=>$srow['full_name'], "parent_id"=>$srow['parent_id'], "create_survey"=>$srow['create_survey'], "configurator"=>$srow['configurator'], "create_user"=>$srow['create_user'], "delete_user"=>$srow['delete_user'], "superadmin"=>$srow['superadmin'], "manage_template"=>$srow['manage_template'], "manage_label"=>$srow['manage_label']);			//added by Dennis modified by Moses
			}
			else
			{
				$userlist[0] = array("user"=>$srow['users_name'], "uid"=>$srow['uid'], "email"=>$srow['email'], "password"=>$srow['password'], "full_name"=>$srow['full_name'], "parent_id"=>$srow['parent_id'], "create_survey"=>$srow['create_survey'], "configurator"=>$srow['configurator'], "create_user"=>$srow['create_user'], "delete_user"=>$srow['delete_user'], "superadmin"=>$srow['superadmin'], "manage_template"=>$srow['manage_template'], "manage_label"=>$srow['manage_label']);
			}
		}
		else
		{
			if ($srow['uid'] != $_SESSION['loginID'])
			{
				$userlist[] = $srow['uid'];
			}
			else
			{
				$userlist[0] = $srow['uid'];
			}
		}

	}
	return $userlist;
}


function gettemplatelist()
{
	global $publicdir;
	if (!$publicdir) {$publicdir=dirname(getcwd());}
	$tloc="$publicdir/templates";
	if ($handle = opendir($tloc))
	{
		while (false !== ($file = readdir($handle)))
		{
			if (!is_file("$tloc/$file") && $file != "." && $file != ".." && $file!=".svn")
			{
				$list_of_files[] = $file;
			}
		}
		closedir($handle);
	}
	usort($list_of_files, 'StandardSort');
	return $list_of_files;
}


/**
* Gets all survey infos in one big array including the language specific settings
* 
* @param string $surveyid  The survey ID
* @param string $languagecode The language code - if not given the base language of the particular survey is used
* @return array Returns array with survey info or false, if survey does not exist
*/
function getSurveyInfo($surveyid, $languagecode='')
{
	global $dbprefix, $siteadminname, $siteadminemail, $connect, $languagechanger;
	$surveyid=sanitize_int($surveyid);
	$languagecode=sanitize_languagecode($languagecode);
	$thissurvey=false;
	// if no language code is set then get the base language one
    if (!isset($languagecode) || $languagecode=='')
	{
	   $languagecode=GetBaseLanguageFromSurveyID($surveyid);;
    }
	$query="SELECT * FROM ".db_table_name('surveys').",".db_table_name('surveys_languagesettings')." WHERE sid=$surveyid and surveyls_survey_id=$surveyid and surveyls_language='$languagecode'";
    $result=db_execute_assoc($query) or safe_die ("Couldn't access survey settings<br />$query<br />".$connect->ErrorMsg());   //Checked
	while ($row=$result->FetchRow())
	{
        $thissurvey=$row;
        // now create some stupid array translations - needed for backward compatibility
        // Newly added surveysettings don't have to be added specifically - these will be available by field name automatically
        $thissurvey["name"]=$thissurvey['surveyls_title'];
        $thissurvey["description"]=$thissurvey['surveyls_description'];
        $thissurvey["welcome"]=$thissurvey['surveyls_welcometext'];
        $thissurvey["templatedir"]=$thissurvey['template'];
        $thissurvey["adminname"]=$thissurvey['admin'];
        $thissurvey["tablename"]=$dbprefix."survey_".$thissurvey['sid'];
        $thissurvey["urldescrip"]=$thissurvey['surveyls_urldescription'];
        $thissurvey["url"]=$thissurvey['surveyls_url'];
        $thissurvey["sendnotification"]=$thissurvey['notification'];
        $thissurvey["expiry"]=$thissurvey['expires'];
        $thissurvey["email_invite_subj"]=$thissurvey['surveyls_email_invite_subj'];
        $thissurvey["email_invite"]=$thissurvey['surveyls_email_invite'];
        $thissurvey["email_remind_subj"]=$thissurvey['surveyls_email_remind_subj'];
        $thissurvey["email_remind"]=$thissurvey['surveyls_email_remind'];
        $thissurvey["email_confirm_subj"]=$thissurvey['surveyls_email_confirm_subj'];
        $thissurvey["email_confirm"]=$thissurvey['surveyls_email_confirm'];
        $thissurvey["email_register_subj"]=$thissurvey['surveyls_email_register_subj'];
        $thissurvey["email_register"]=$thissurvey['surveyls_email_register'];
	    if (!isset($thissurvey['adminname'])) {$thissurvey['adminname']=$siteadminname;}
	    if (!isset($thissurvey['adminemail'])) {$thissurvey['adminemail']=$siteadminemail;}
	    if (!isset($thissurvey['urldescrip']) ||
		$thissurvey['urldescrip'] == '' ) {$thissurvey['urldescrip']=$thissurvey['surveyls_url'];}
		$thissurvey['passthrulabel']=isset($_SESSION['passthrulabel']) ? $_SESSION['passthrulabel'] : "";
		$thissurvey['passthruvalue']=isset($_SESSION['passthruvalue']) ? $_SESSION['passthruvalue'] : "";
	}              
    
    //not sure this should be here... ToDo: Find a better place
    if (function_exists('makelanguagechanger')) $languagechanger = makelanguagechanger();
	return $thissurvey;
}


function getlabelsets($languages=null)
// Returns a list with label sets
// if the $languages paramter is provided then only labelset containing all of the languages in the paramter are provided
{
	global $dbprefix, $connect, $surveyid;
	if ($languages){
      $languages=sanitize_languagecodeS($languages);
	  $languagesarray=explode(' ',trim($languages));
    } 
	$query = "SELECT ".db_table_name('labelsets').".lid as lid, label_name FROM ".db_table_name('labelsets');
	if ($languages){
        $query .=" where ";
        foreach  ($languagesarray as $item)
        {
        $query .=" ((languages like '% $item %') or (languages='$item') or (languages like '% $item') or (languages like '$item %')) and ";
        }
        $query .=" 1=1 ";
    }
    $query .=" order by label_name";
	$result = db_execute_assoc($query) or safe_die ("Couldn't get list of label sets<br />$query<br />".$connect->ErrorMsg()); //Checked
	$labelsets=array();
	while ($row=$result->FetchRow())
	{
		$labelsets[] = array($row['lid'], $row['lid'].": ".$row['label_name']);
	}
	return $labelsets;
}


function checkifemptydb()
{
	global $connect, $dbprefix;
	$tablelist = $connect->MetaTables('TABLES');
	if ( in_array($dbprefix.'surveys',$tablelist) ) {Return(false);}
	else {Return(true);}
}


function sql_table_exists($tableName, $tables)
{
	return(in_array($tableName, $tables));
}


/**
* Compares two elements from an array (passed by the usort function) 
* and returns -1, 0 or 1 depending on the result of the comparison of 
* the sort order of the group_order and question_order field
* 
* @param mixed $a
* @param mixed $b
* @return int
*/
function CompareGroupThenTitle($a, $b)
{
	if (isset($a["group_order"]) && isset($b["group_order"]))
	{
		$GroupResult = strnatcasecmp($a["group_order"], $b["group_order"]);
	}
	else
	{
		$GroupResult = "";
	}
	if ($GroupResult == 0)
	{
		$TitleResult = strnatcasecmp($a["question_order"], $b["question_order"]);
		return $TitleResult;
	}
	return $GroupResult;
}


function StandardSort($a, $b)
{
	return strnatcasecmp($a, $b);
}


function fixsortorderAnswers($qid) //Function rewrites the sortorder for a group of answers
{
	global $dbprefix, $connect, $surveyid;
    $qid=sanitize_int($qid);
	$baselang = GetBaseLanguageFromSurveyID($surveyid);     
	$cdresult = db_execute_num("SELECT qid, code, sortorder FROM ".db_table_name('answers')." WHERE qid={$qid} and language='{$baselang}' ORDER BY sortorder"); //Checked    
	$position=0;
	while ($cdrow=$cdresult->FetchRow())
	{
		$cd2query="UPDATE ".db_table_name('answers')." SET sortorder={$position} WHERE qid={$cdrow[0]} AND code='{$cdrow[1]}' AND sortorder={$cdrow[2]} ";
		$cd2result=$connect->Execute($cd2query) or safe_die ("Couldn't update sortorder<br />$cd2query<br />".$connect->ErrorMsg()); //Checked    
		$position++;
	}
}

/**
* This function rewrites the sortorder for questions inside the named group
* 
* @param integer $groupid the group id
* @param integer $surveyid the survey id
*/
function fixsortorderQuestions($groupid, $surveyid) //Function rewrites the sortorder for questions
{
	global $connect;
    $gid = sanitize_int($groupid);
    $surveyid = sanitize_int($surveyid);
	$baselang = GetBaseLanguageFromSurveyID($surveyid);
	$cdresult = db_execute_assoc("SELECT qid FROM ".db_table_name('questions')." WHERE gid='{$gid}' and language='{$baselang}' ORDER BY question_order, title ASC");      //Checked    
	$position=0;
	while ($cdrow=$cdresult->FetchRow())
	{
		$cd2query="UPDATE ".db_table_name('questions')." SET question_order='{$position}' WHERE qid='{$cdrow['qid']}' ";
		$cd2result = $connect->Execute($cd2query) or safe_die ("Couldn't update question_order<br />$cd2query<br />".$connect->ErrorMsg());    //Checked    
		$position++;
	}
}


function shiftorderQuestions($sid,$gid,$shiftvalue) //Function shifts the sortorder for questions
{
	global $dbprefix, $connect, $surveyid;
    $sid=sanitize_int($sid);
    $gid=sanitize_int($gid);
    $shiftvalue=sanitize_int($shiftvalue);
    
	$baselang = GetBaseLanguageFromSurveyID($surveyid);
	$cdresult = db_execute_assoc("SELECT qid FROM ".db_table_name('questions')." WHERE gid='{$gid}' and language='{$baselang}' ORDER BY question_order, title ASC"); //Checked
	$position=$shiftvalue;
	while ($cdrow=$cdresult->FetchRow())
	{
		$cd2query="UPDATE ".db_table_name('questions')." SET question_order='{$position}' WHERE qid='{$cdrow['qid']}' ";
		$cd2result = $connect->Execute($cd2query) or safe_die ("Couldn't update question_order<br />$cd2query<br />".$connect->ErrorMsg()); //Checked 
		$position++;
	}
}

function fixsortorderGroups() //Function rewrites the sortorder for groups
{
	global $dbprefix, $connect, $surveyid;
	$baselang = GetBaseLanguageFromSurveyID($surveyid);
    $cdresult = db_execute_assoc("SELECT gid FROM ".db_table_name('groups')." WHERE sid='{$surveyid}' AND language='{$baselang}' ORDER BY group_order, group_name");    
	$position=0;
	while ($cdrow=$cdresult->FetchRow())
	{
		$cd2query="UPDATE ".db_table_name('groups')." SET group_order='{$position}' WHERE gid='{$cdrow['gid']}' ";
		$cd2result = $connect->Execute($cd2query) or safe_die ("Couldn't update group_order<br />$cd2query<br />".$connect->ErrorMsg());  //Checked   
		$position++;
	}
}

function fixmovedquestionConditions($qid,$oldgid,$newgid) //Function rewrites the cfieldname for a question after group change
{
	global $dbprefix, $connect, $surveyid;
    $qid=sanitize_int($qid);
    $oldgid=sanitize_int($oldgid);
    $newgid=sanitize_int($newgid);

	$cresult = db_execute_assoc("SELECT cid, cfieldname FROM ".db_table_name('conditions')." WHERE cqid={$qid}");  //Checked
	while ($crow=$cresult->FetchRow())
	{

		$mycid=$crow['cid'];
		$mycfieldname=$crow['cfieldname'];
		$cfnregs="";

		if (ereg($surveyid."X".$oldgid."X".$qid."(.*)", $mycfieldname, $cfnregs) > 0) 
		{
			$newcfn=$surveyid."X".$newgid."X".$qid.$cfnregs[1];
			$c2query="UPDATE ".db_table_name('conditions')
			." SET cfieldname='{$newcfn}' WHERE cid={$mycid}";

			$c2result=$connect->Execute($c2query)     //Checked   
			or safe_die ("Couldn't update conditions<br />$c2query<br />".$connect->ErrorMsg());
		}
	}
}

function browsemenubar($title='')
{
	global $surveyid, $scriptname, $imagefiles, $homeurl, $clang, $sumrows5, $surrows;

	$thissurvey=getSurveyInfo($surveyid);
	//BROWSE MENU BAR
	$browsemenubar = "<div class='menubar'>\n"
	. "\t<div class='menubar-title'>\n"
    . "\t<strong>$title</strong>: ({$thissurvey['name']})"
    . "\t</div>"
    . "\t<div class='menubar-main'>\n"
    . "\t<div class='menubar-left'>\n"
	. "\t\t\t<a href='$scriptname?sid=$surveyid' onmouseout=\"hideTooltip()\" " .
			"title=\"".$clang->gTview("Return to Survey Administration")."\" " .
			"onmouseover=\"showTooltip(event,'".$clang->gT("Return to Survey Administration", "js")."')\">" .
			"<img name='Administration' src='$imagefiles/home.png' title='' alt='' /></a>\n"
	. "\t\t\t<img src='$imagefiles/blank.gif' alt='' width='11' />\n"
	. "\t\t\t<img src='$imagefiles/seperator.gif' alt='' />\n"
	. "\t\t\t<a href='$scriptname?action=browse&amp;sid=$surveyid' onmouseout=\"hideTooltip()\"" .
			" title=\"".$clang->gTview("Show summary information")."\" " .
			" onmouseover=\"showTooltip(event,'".$clang->gT("Show summary information", "js")."')\"" .
			"><img name='SurveySummary' src='$imagefiles/summary.png' title='' alt='' /></a>\n";
    if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
    {
        $browsemenubar .="\t\t\t<a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=all' onmouseout=\"hideTooltip()\"" .
        "title=\"".$clang->gTview("Display Responses")."\" " .
        "onmouseover=\"showTooltip(event,'".$clang->gT("Display Responses", "js")."')\">" .
        "<img name='ViewAll' src='$imagefiles/document.png' title='' alt='' /></a>\n";
    
    } else {
            $browsemenubar .= "<a href=\"#\" accesskey='b' onclick=\"hideTooltip(); document.getElementById('browsepopup').style.visibility='visible';\""
            . "onmouseout=\"hideTooltip()\""
            . "title=\"".$clang->gTview("Display Responses")."\" " 
            . "onmouseover=\"showTooltip(event,'".$clang->gT("Display Responses", "js")."');return false\">"
            ."<img src='$imagefiles/document.png' title='".$clang->gTview("Display Responses")."' "
            . "name='ViewAll' alt='' /></a>";
            
            $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            $tmp_survlangs[] = $baselang;
            rsort($tmp_survlangs);
            
            $browsemenubar .="<div class=\"langpopup1\" id=\"browsepopup\"><table width=\"100%\"><tr><td>".$clang->gT("Please select a language:")."</td></tr>";
            foreach ($tmp_survlangs as $tmp_lang)
            {
                $browsemenubar .= "<tr><td><a href=\"$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=all&amp;browselang=".$tmp_lang."\" accesskey='d' onclick=\"document.getElementById('browsepopup').style.visibility='hidden';\"><font color=\"#097300\"><b>".getLanguageNameFromCode($tmp_lang,false)."</b></font></a></td></tr>";
            }
            $browsemenubar .= "<tr><td align=\"center\"><a href=\"#\" accesskey='d' onclick=\"document.getElementById('browsepopup').style.visibility='hidden';\"><font color=\"#DF3030\">".$clang->gT("Cancel")."</font></a></td></tr></table></div>";
            
            $tmp_pheight = getPopupHeight();
            $browsemenubar .= "<script type='text/javascript'>document.getElementById('browsepopup').style.height='".$tmp_pheight."px';</script>";

        }            
            
            
	$browsemenubar .= "\t\t\t<a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=all&amp;limit=50&amp;order=desc'" .
			" title=\"".$clang->gTview("Display Last 50 Responses")."\" " .
			"onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Display Last 50 Responses", "js")."')\">" .
			"<img name='ViewLast' src='$imagefiles/viewlast.png' title='' alt='' /></a>\n"
	. "\t\t\t<a href='$scriptname?action=dataentry&amp;sid=$surveyid' onmouseout=\"hideTooltip()\" ".
			" title=\"".$clang->gTview("Dataentry Screen for Survey")."\" " .
			" onmouseover=\"showTooltip(event,'".$clang->gT("Dataentry Screen for Survey", "js")."')\">" .
	  "<img name='DataEntry' src='$imagefiles/dataentry.png' title='' alt='' /></a>\n"
	. "\t\t\t<a href='$scriptname?action=statistics&amp;sid=$surveyid' "
	."title=\"".$clang->gTview("Get statistics from these responses")."\" "
	."onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Get statistics from these responses", "js")."')\">"
	."<img name='Statistics' src='$imagefiles/statistics.png' title='' alt='' /></a>\n"
	. "\t\t\t<img src='$imagefiles/seperator.gif' alt='' />\n";
	if ($sumrows5['export'] == "1" || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
		$browsemenubar .= "\t\t\t<a href='$scriptname?action=exportresults&amp;sid=$surveyid' onmouseout=\"hideTooltip()\" "
		. "title=\"".$clang->gTview("Export Results to Application")."\" "
		. "onmouseover=\"showTooltip(event,'".$clang->gT("Export Results to Application", "js")."')\">"
		. "<img name='Export' src='$imagefiles/export.png' "
		. "title='' alt='' /></a>\n"
		. "\t\t\t<a href='$scriptname?action=exportspss&amp;sid=$surveyid' onmouseout=\"hideTooltip()\" "
		. "title=\"".$clang->gTview("Export results to an SPSS command file")."\" "
		. "onmouseover=\"showTooltip(event,'".$clang->gT("Export results to a SPSS/PASW command file", "js")."')\">"
		. "<img src='$imagefiles/exportspss.png' "
		. "title='' border='0' alt='". $clang->gT("Export result to a SPSS/PASW command file")."' /></a>\n"
        . "\t\t\t<a href='$scriptname?action=exportr&amp;sid=$surveyid' onmouseout=\"hideTooltip()\" "
        . "title=\"".$clang->gTview("Export result to a SPSS command file")."\" "
        . "onmouseover=\"showTooltip(event,'".$clang->gT("Export results to a R data file", "js")."')\">"
        . "<img src='$imagefiles/exportr.png' "
        . "title='' border='0' alt='". $clang->gT("Export results to a R data file")."' /></a>\n";
	}
	$browsemenubar .= "\t\t\t<a href='$scriptname?action=importoldresponses&amp;sid=$surveyid' onmouseout=\"hideTooltip()\" "
	. " title=\"".$clang->gTview("Import answers from a deactivated survey table")."\" "
	. " onmouseover=\"showTooltip(event,'".$clang->gT("Import answers from a deactivated survey table", "js")."')\" >" .
			"<img name='ImportOld' src='$imagefiles/importold.png' title='' alt='' /></a>\n"
	. "\t\t\t<img src='$imagefiles/seperator.gif' alt='' />\n"
	. "\t\t\t<a href='$scriptname?action=saved&amp;sid=$surveyid' onmouseout=\"hideTooltip()\" "
	. " title=\"".$clang->gTview("View Saved but not submitted Responses")."\" "
	. " onmouseover=\"showTooltip(event,'".$clang->gT("View Saved but not submitted Responses", "js")."')\" >" .
		"<img src='$imagefiles/saved.png' title='' alt='' name='BrowseSaved' /></a>\n"
	. "\t\t\t<a href='$scriptname?action=vvimport&amp;sid=$surveyid' onmouseout=\"hideTooltip()\" "
	. " title=\"".$clang->gTview("Import a VV survey file")."\" "
	. " onmouseover=\"showTooltip(event,'".$clang->gT("Import a VV survey file", "js")."')\">\n"
	. "<img src='$imagefiles/importvv.png' alt='' /></a>\n";
	if ($sumrows5['export'] == "1" || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
		$browsemenubar .= "\t\t\t<a href='$scriptname?action=vvexport&amp;sid=$surveyid' onmouseout=\"hideTooltip()\" " .
		" title=\"".$clang->gTview("Export a VV survey file")."\" " .
		" onmouseover=\"showTooltip(event,'".$clang->gT("Export a VV survey file", "js")."')\">" .
		"<img src='$imagefiles/exportvv.png' title='' alt='' /></a>\n";
	}
	if (
		( ($surrows['browse_response'] && $surrows['activate_survey']) || 
			$_SESSION['USER_RIGHT_SUPERADMIN'] == 1
		) &&
		(
			$thissurvey['private'] == 'N' &&
			$thissurvey['tokenanswerspersistence'] == 'Y'
		)
	)
	{ //TIBO
		$browsemenubar .= "\t\t\t<a href='$scriptname?action=iteratesurvey&amp;sid=$surveyid' onmouseout=\"hideTooltip()\" " .
		" title=\"".$clang->gTview("Iterate surevey")."\" " .
		" onmouseover=\"showTooltip(event,'".$clang->gT("Iterate survey", "js")."')\">" .
		"<img src='$imagefiles/iterate.png' title='' alt='' /></a>\n";
	}
	$browsemenubar .= "\t\t</div>\n"
    . "\t</div>\n"
	. "</div>\n"
    ."<font style='size:12px;line-height:2px;'>&nbsp;&nbsp;</font>"; //CSS Firefox 2 transition fix
    
	return $browsemenubar;
}


function returnglobal($stringname)
{

	if (isset($_REQUEST[$stringname]))
		{
		if ($stringname == "sid" || $stringname == "gid" || 
			$stringname == "qid" || $stringname == "tid" || 
			$stringname == "lid" || $stringname == "ugid"|| 
			$stringname == "thisstep" || $stringname == "scenario" ||
			$stringname == "cqid" || $stringname == "cid" || 
			$stringname == "qaid" || $stringname == "scid")
		{
			return sanitize_int($_REQUEST[$stringname]);
		}
        elseif ($stringname =="lang" || $stringname =="adminlang")
        {
            return sanitize_languagecode($_REQUEST[$stringname]);
        }
        elseif ($stringname =="htmleditormode" || 
		$stringname =="subaction")
        {
            return sanitize_paranoid_string($_REQUEST[$stringname]);    
        }
        elseif ( $stringname =="cquestions")
        {
            return sanitize_cquestions($_REQUEST[$stringname]);    
        }
		return $_REQUEST[$stringname];
	}
    else return NULL;
}


function sendcacheheaders()
{
	global $embedded;
	if ( $embedded ) return;
    if (!headers_sent())
    {
        header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');  // this line lets IE7 run LimeSurvey in an iframe    
	    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
	    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // always modified
	    header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
	    header("Cache-Control: post-check=0, pre-check=0", false);
	    header("Pragma: no-cache");
	    header('Content-Type: text/html; charset=utf-8');
    }   
}


function returnquestiontitlefromfieldcode($fieldcode)
{
	// Performance optimized	: Nov 13, 2006
	// Performance Improvement	: 37%
	// Optimized By				: swales
    

	global $dbprefix, $surveyid, $connect, $clang;
	if (!isset($fieldcode)) {return $clang->gT("Preset");}
	if ($fieldcode == "token") {return $clang->gT("Token");}
	if ($fieldcode == "datestamp") {return $clang->gT("Date Last Action");}
	if ($fieldcode == "startdate") {return $clang->gT("Date Started");}
	if ($fieldcode == "ipaddr") {return $clang->gT("IP Address");}
	if ($fieldcode == "refurl") {return $clang->gT("Referring URL");}

	//Find matching information;
	$details=arraySearchByKey($fieldcode, createFieldMap($surveyid), "fieldname", 1);

	$fqid=$details['qid'];
	$qq = "SELECT question, other FROM ".db_table_name('questions')." WHERE qid=$fqid AND language='".$_SESSION['s_lang']."'";

	$qr = db_execute_assoc($qq);    //Checked   
	if (!$qr)
	{
		echo "<!-- ERROR Finding Question Name for qid $fqid - $qq - ".htmlspecialchars($connect->ErrorMsg())."! -->";
		$qname="[QID: $fqid]";
	}
	else
	{
		while($qrow=$qr->FetchRow())
		{
			$qname=strip_tags($qrow['question']);
		}
	}
    $aname='';
	if (isset($details['aid']) && $details['aid']) //Add answer if necessary (array type questions)
	{
	    if($details['type'] == ":" || $details['type'] == ";")
	    {
		    list($details['aid'], $lidcode) = explode("_", $details['aid']);
		}
		$qq = "SELECT answer FROM ".db_table_name('answers')." WHERE qid=$fqid AND code='{$details['aid']}' AND language='".$_SESSION['s_lang']."'";
		$qr = db_execute_assoc($qq) or safe_die ("ERROR: ".$connect->ErrorMsg()."<br />$qq"); //Checked
		while($qrow=$qr->FetchRow())
		{
			$aname=$qrow['answer'];
		}
		if (isset($lidcode) && isset($details['lid'])) 
		{
		   //Add the Labelset Title to the answer info
		   $qq = "SELECT title FROM ".db_table_name('labels')." WHERE lid = {$details['lid']} AND code='$lidcode' AND language='".$_SESSION['s_lang']."'";
		   $qr = db_execute_assoc($qq) or safe_die ("ERROR: ".$connect->ErrorMsg()."<br />$qq"); 
		   while ($qrow=$qr->FetchRow())
		   {
		     $aname .= "] [".$qrow['title'];
		   }
		}
		unset($lidcode);
	}
	if (substr($fieldcode, -5) == 'other')
	{
		$aname = $clang->gT('Other');
	}
	return array($qname,$aname);
}


function getsidgidqidaidtype($fieldcode)
{
	// use simple parsing to get {sid}, {gid}
	// and what may be {qid} or {qid}{aid} combination
	list($fsid, $fgid, $fqid) = split("X", $fieldcode);
	$fsid=sanitize_int($fsid);
	$fgid=sanitize_int($fgid);
	if (!$fqid) {$fqid=0;}
    $fqid=sanitize_int($fqid);                  
	// try a true parsing of fieldcode (can separate qid from aid)
	// but fails for type M and type P multiple choice
	// questions because the SESSION fieldcode is combined
	// and we want here to pass only the sidXgidXqid for type M and P
	$fields=arraySearchByKey($fieldcode, createFieldMap($fsid), "fieldname", 1);

	if (count($fields) != 0)
	{
		$aRef['sid']=$fields['sid'];
		$aRef['gid']=$fields['gid'];
		$aRef['qid']=$fields['qid'];
		$aRef['aid']=$fields['aid'];
		$aRef['type']=$fields['type'];
	}
	else
	{
		// either the fielcode doesn't match a question
		// or it is a type M or P question
		$aRef['sid']=$fsid;
		$aRef['gid']=$fgid;
		$aRef['qid']=sanitize_int($fqid);

		$s_lang = GetBaseLanguageFromSurveyID($fsid);
		$query = "SELECT type FROM ".db_table_name('questions')." WHERE qid=".$fqid." AND language='".$s_lang."'";  
		$result = db_execute_assoc($query) or safe_die ("Couldn't get question type - getsidgidqidaidtype() in common.php<br />".$connect->ErrorMsg()); //Checked   
		if ( $result->RecordCount() == 0 )
		{ // question doesn't exist
			return Array();
		}
		else
		{	// certainly is type M or P
			while($row=$result->FetchRow())
			{
				$aRef['type']=$row['type'];
			}		
		}

	}

	//return array("sid"=>$fsid, "gid"=>$fgid, "qid"=>$fqid);
	return $aRef;
}

/**
* put your comment there...
* 
* @param mixed $fieldcode
* @param mixed $value
* @param mixed $format
* @param mixed $dateformatid
* @return string
*/
function getextendedanswer($fieldcode, $value, $format='', $dateformatphp='d.m.Y')
{
	// Performance optimized	: Nov 13, 2006
	// Performance Improvement	: 36%
	// Optimized By				: swales

	global $dbprefix, $surveyid, $connect, $clang, $action;

	// use Survey base language if s_lang isn't set in _SESSION (when browsing answers)
	$s_lang = GetBaseLanguageFromSurveyID($surveyid);        
	if  (!isset($action) || (isset($action) && $action!='browse') ) 
    {
        if (isset($_SESSION['s_lang'])) $s_lang = $_SESSION['s_lang'];  //This one does not work in admin mode when you browse a particular answer
    }

	//Fieldcode used to determine question, $value used to match against answer code
	//Returns NULL if question type does not suit
	if (substr_count($fieldcode, "X") > 1) //Only check if it looks like a real fieldcode
	{
		$fields=arraySearchByKey($fieldcode, createFieldMap($surveyid), "fieldname", 1);
		//Find out the question type
		$query = "SELECT type, lid, lid1 FROM ".db_table_name('questions')." WHERE qid={$fields['qid']} AND language='".$s_lang."'";
		$result = db_execute_assoc($query) or safe_die ("Couldn't get question type - getextendedanswer() in common.php<br />".$connect->ErrorMsg()); //Checked   
		while($row=$result->FetchRow())
		{
			$this_type=$row['type'];
			$this_lid=$row['lid'];
			$this_lid1=$row['lid1'];
		} // while
		switch($this_type)
		{
            case 'D': if (trim($value)!='')
                {
                    $datetimeobj = new Date_Time_Converter($value , "Y-m-d H:i:s");
                    $value=$datetimeobj->convert($dateformatphp);                                      
                }
                break;                                   
			case "L":
			case "!":
			case "O":
			case "^":
			case "I":
			case "R":
			    $query = "SELECT code, answer FROM ".db_table_name('answers')." WHERE qid={$fields['qid']} AND code='".$connect->escape($value)."' AND language='".$s_lang."'";
			    $result = db_execute_assoc($query) or safe_die ("Couldn't get answer type L - getextendedanswer() in common.php<br />$query<br />".$connect->ErrorMsg()); //Checked   
			    while($row=$result->FetchRow())
			    {
				    $this_answer=$row['answer'];
			    } // while
			    if ($value == "-oth-")
			    {
				    $this_answer=$clang->gT("Other");
			    }
			    break;
			case "M":
			case "J":
			case "P":
			    switch($value)
			    {
				    case "Y": $this_answer=$clang->gT("Yes"); break;
			    }
			    break;
			case "Y":
			    switch($value)
			    {
				    case "Y": $this_answer=$clang->gT("Yes"); break;
				    case "N": $this_answer=$clang->gT("No"); break;
				    default: $this_answer=$clang->gT("No answer");
			    }
			    break;
			case "G":
			    switch($value)
			    {
				    case "M": $this_answer=$clang->gT("Male"); break;
				    case "F": $this_answer=$clang->gT("Female"); break;
				    default: $this_answer=$clang->gT("No answer");
			    }
			    break;
			case "C":
			    switch($value)
			    {
				    case "Y": $this_answer=$clang->gT("Yes"); break;
				    case "N": $this_answer=$clang->gT("No"); break;
				    case "U": $this_answer=$clang->gT("Uncertain"); break;
			    }
			    break;
			case "E":
			    switch($value)
			    {
				    case "I": $this_answer=$clang->gT("Increase"); break;
				    case "D": $this_answer=$clang->gT("Decrease"); break;
				    case "S": $this_answer=$clang->gT("Same"); break;
			    }
			    break;
			case "F":
			case "H":
			case "W":
			case "Z":
			case "1":
			    $query = "SELECT title FROM ".db_table_name('labels')." WHERE ((lid=$this_lid) OR (lid=$this_lid1)) AND code='".$connect->escape($value)."' AND language='".$s_lang."'";
			    $result = db_execute_assoc($query) or safe_die ("Couldn't get answer type F/H - getextendedanswer() in common.php");   //Checked
			    while($row=$result->FetchRow())
			    {
				    $this_answer=$row['title'];
			    } // while
			    if ($value == "-oth-")
			    {
				    $this_answer=$clang->gT("Other");
			    }
			    break;
			default:
			;
		} // switch
	}
	if (isset($this_answer))
	{
		if ($format != 'INSERTANS')
		{
			return $this_answer." [$value]";
		}
		else
		{
			 if (strip_tags($this_answer) == "") 
			 { 
				switch ($this_type)
				{// for questions with answers beeing
				// answer code, it is safe to return the
				// code instead of the blank stripped answer
					case "A":
					case "B":
					case "C":
					case "E":
					case "F":
					case "H":
					case "1":
					case "M":
					case "P":
					case "!":
					case "5":
					case "L":
					case "O":
					case "W":
					case "Z":
						return $value;
						break;
					default:
						return strip_tags($this_answer); 
						break;
				}
			 } 
			 else 
			 { 
			 	return strip_tags($this_answer); 
			 }
		}
	}
	else
	{
		return $value;
	}
}

function validate_email($email)
{
	// Create the syntactical validation regular expression
	// Validate the syntax

	// see http://data.iana.org/TLD/tlds-alpha-by-domain.txt
	$maxrootdomainlength = 6;
    return ( ! preg_match("/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,".$maxrootdomainlength."}))$/ix", $email)) ? FALSE : TRUE;  
}

function validate_templatedir($templatename)
{
    global $publicdir, $defaulttemplate;
    if (is_dir("$publicdir/templates/{$templatename}/"))
    {
         return $templatename;
    }
    elseif (is_dir("$publicdir/templates/{$defaulttemplate}/"))
    {
         return $defaulttemplate;
    }
    else 
    {
         return 'default';
    }     
}


/**
* This function generates an array containing the fieldcode, and matching data in the same order as the activate script
* 
* @param string $surveyid
* @param mixed $style
* @param mixed $force_refresh - Forces to really refresh the array, not just take the session copy 
* @return mixed
*/
function createFieldMap($surveyid, $style="null", $force_refresh=false) {

	global $dbprefix, $connect, $globalfieldmap, $clang;
    $surveyid=sanitize_int($surveyid);
	//checks to see if fieldmap has already been built for this page.
	if (isset($globalfieldmap) && $globalfieldmap[0] == $surveyid  && $force_refresh==false) {
		return $globalfieldmap[1];
	}

	//Check for any additional fields for this survey and create necessary fields (token and datestamp and ipaddr)
	$pquery = "SELECT private, datestamp, ipaddr, refurl FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
	$presult=db_execute_assoc($pquery); //Checked
	$counter=0;
	while($prow=$presult->FetchRow())
	{
		if ($prow['private'] == "N")
		{
			$fieldmap[$counter]=array("fieldname"=>"token", "type"=>"", "sid"=>$surveyid, "gid"=>"", "qid"=>"", "aid"=>"");
			if ($style == "full")
			{
				$fieldmap[$counter]['title']="";
				$fieldmap[$counter]['question']="token";
				$fieldmap[$counter]['group_name']="";
			}
			$counter++;
		}
		if ($prow['datestamp'] == "Y")
		{
			$fieldmap[$counter]=array("fieldname"=>"datestamp", "type"=>"", "sid"=>$surveyid, "gid"=>"", "qid"=>"", "aid"=>"");
			if ($style == "full")
			{
				$fieldmap[$counter]['title']="";
				$fieldmap[$counter]['question']="datestamp";
				$fieldmap[$counter]['group_name']="";
			}
			$counter++;
		}
		if ($prow['datestamp'] == "Y")
		{
			$fieldmap[$counter]=array("fieldname"=>"startdate", "type"=>"", "sid"=>$surveyid, "gid"=>"", "qid"=>"", "aid"=>"");
			if ($style == "full")
			{
				$fieldmap[$counter]['title']="";
				$fieldmap[$counter]['question']="startdate";
				$fieldmap[$counter]['group_name']="";
			}
			$counter++;
		}
		if ($prow['ipaddr'] == "Y")
		{
			$fieldmap[$counter]=array("fieldname"=>"ipaddr", "type"=>"", "sid"=>$surveyid, "gid"=>"", "qid"=>"", "aid"=>"");
			if ($style == "full")
			{
				$fieldmap[$counter]['title']="";
				$fieldmap[$counter]['question']="ipaddr";
				$fieldmap[$counter]['group_name']="";
			}
			$counter++;
		}
		// Add 'refurl' to fieldmap.
		if ($prow['refurl'] == "Y")
		{
			$fieldmap[$counter]=array("fieldname"=>"refurl", "type"=>"", "sid"=>$surveyid, "gid"=>"", "qid"=>"", "aid"=>"");
			if ($style == "full")
			{
				$fieldmap[$counter]['title']="";
				$fieldmap[$counter]['question']="refurl";
				$fieldmap[$counter]['group_name']="";
			}
			$counter++;
		}

	}
	//Get list of questions
	$s_lang = GetBaseLanguageFromSurveyID($surveyid);
	$aquery = "SELECT * FROM ".db_table_name('questions').", ".db_table_name('groups')
	." WHERE ".db_table_name('questions').".gid=".db_table_name('groups').".gid AND "
	.db_table_name('questions').".sid=$surveyid AND "
	.db_table_name('questions').".language='{$s_lang}' AND "
	.db_table_name('groups').".language='{$s_lang}' "
	." ORDER BY group_order, question_order";
	$aresult = db_execute_assoc($aquery) or safe_die ("Couldn't get list of questions in createFieldMap function.<br />$query<br />".$connect->ErrorMsg()); //Checked
	while ($arow=$aresult->FetchRow()) //With each question, create the appropriate field(s)
	{
		if ($arow['type'] != "M" && $arow['type'] != "A" && $arow['type'] != "B" &&
		$arow['type'] !="C" && $arow['type'] != "E" && $arow['type'] != "F" &&
		$arow['type'] != "H" && $arow['type'] !="P" && $arow['type'] != "R" &&
		$arow['type'] != "Q" && $arow['type'] != "J" && $arow['type'] != "K" && 
		$arow['type'] != "^" && $arow['type'] != ":" && $arow['type'] != "1" &&
		$arow['type'] != ";")
		{
			$fieldmap[$counter]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}", "type"=>"{$arow['type']}", "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>"");
			if ($style == "full")
			{
				$fieldmap[$counter]['title']=$arow['title'];
				$fieldmap[$counter]['question']=$arow['question'];
				$fieldmap[$counter]['group_name']=$arow['group_name'];
			}
			$counter++;
			switch($arow['type'])
			{
				case "L":  //RADIO LIST
				case "!":  //DROPDOWN LIST
				case "W":  //FLEXIBLE DROPDOWN LIST
				case "Z":  //FLEXIBLE RADIO LIST
				if ($arow['other'] == "Y")
				{
					$fieldmap[$counter]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other",
					"type"=>$arow['type'],
					"sid"=>$surveyid,
					"gid"=>$arow['gid'],
					"qid"=>$arow['qid'],
					"aid"=>"other");
					// dgk bug fix line above. aid should be set to "other" for export to append to the field name in the header line.
					if ($style == "full")
					{
						$fieldmap[$counter]['title']=$arow['title'];
						$fieldmap[$counter]['question']=$arow['question']."[".$clang->gT("Other")."]";
						$fieldmap[$counter]['group_name']=$arow['group_name'];
					}
					$counter++;
				}
				break;
				case "O": //DROPDOWN LIST WITH COMMENT
				$fieldmap[$counter]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}comment",
				"type"=>$arow['type'],
				"sid"=>$surveyid,
				"gid"=>$arow['gid'],
				"qid"=>$arow['qid'],
				"aid"=>"comment");
				// dgk bug fix line below. aid should be set to "comment" for export to append to the field name in the header line. Also needed set the type element correctly.
				if ($style == "full")
				{
					$fieldmap[$counter]['title']=$arow['title'];
					$fieldmap[$counter]['question']=$arow['question']."[".$clang->gT("Comment")."]";
					$fieldmap[$counter]['group_name']=$arow['group_name'];
				}
				$counter++;
				break;
			}
		}
		elseif ($arow['type'] == ":" || $arow['type'] == ";")
		{
		    //MULTI FLEXI
			$abquery = "SELECT ".db_table_name('answers').".*, ".db_table_name('questions').".other, ".db_table_name('questions').".lid\n"
			." FROM ".db_table_name('answers').", ".db_table_name('questions')
			." WHERE sid=$surveyid AND ".db_table_name('answers').".qid=".db_table_name('questions').".qid "
			. "AND ".db_table_name('questions').".language='".$s_lang."'"
			." AND ".db_table_name('answers').".language='".$s_lang."'"
			." AND ".db_table_name('questions').".qid={$arow['qid']} "
			." ORDER BY ".db_table_name('answers').".sortorder, ".db_table_name('answers').".answer";
			$abresult=db_execute_assoc($abquery) or die ("Couldn't get list of answers in createFieldMap function (case :)<br />$abquery<br />".htmlspecialchars($connect->ErrorMsg()));
			$ab2query = "SELECT ".db_table_name('labels').".*
			             FROM ".db_table_name('questions').", ".db_table_name('labels')."
			             WHERE sid=$surveyid 
						 AND ".db_table_name('labels').".lid=".db_table_name('questions').".lid
			             AND ".db_table_name('questions').".language='".$s_lang."'
			             AND ".db_table_name('labels').".language='".$s_lang."'
			             AND ".db_table_name('questions').".qid=".$arow['qid']."
			             ORDER BY ".db_table_name('labels').".sortorder, ".db_table_name('labels').".title";
			$ab2result=db_execute_assoc($ab2query) or die("Couldn't get list of labels in createFieldMap function (case :)<br />$ab2query<br />".htmlspecialchars($connection->ErrorMsg()));
			$lset=array();
			while($ab2row=$ab2result->FetchRow())
			{
			    $lset[]=$ab2row;
			}
			while ($abrow=$abresult->FetchRow())
			{
			    foreach($lset as $ls)
			    {
				  $fieldmap[$counter]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}_{$ls['code']}", 
				                    "type"=>$arow['type'], 
									"sid"=>$surveyid, 
									"gid"=>$arow['gid'], 
									"qid"=>$arow['qid'], 
									"aid"=>$abrow['code']."_".$ls['code'],
									"lid"=>$abrow['lid']);
				  if ($abrow['other']=="Y") {$alsoother="Y";}
				  if ($style == "full")
			  	  {
					$fieldmap[$counter]['title']=$arow['title'];
					$fieldmap[$counter]['question']=$arow['question']."[".$ls['title']."] [".$abrow['answer']."]";
					$fieldmap[$counter]['group_name']=$arow['group_name'];
				  }
				  $counter++;
			    }
			}
			unset($lset);
		}
		elseif ($arow['type'] == "M" || $arow['type'] == "A" || $arow['type'] == "B" ||
		$arow['type'] == "C" || $arow['type'] == "E" || $arow['type'] == "F" ||
		$arow['type'] == "H" || $arow['type'] == "P" || $arow['type'] == "^" || $arow['type'] == "J")
		{
			//MULTI ENTRY
			$abquery = "SELECT ".db_table_name('answers').".*, ".db_table_name('questions').".other\n"
			." FROM ".db_table_name('answers').", ".db_table_name('questions')
			." WHERE sid=$surveyid AND ".db_table_name('answers').".qid=".db_table_name('questions').".qid "
			. "AND ".db_table_name('questions').".language='".$s_lang."'"
			." AND ".db_table_name('answers').".language='".$s_lang."'"
			." AND ".db_table_name('questions').".qid={$arow['qid']} "
			." ORDER BY ".db_table_name('answers').".sortorder, ".db_table_name('answers').".answer";
			$abresult=db_execute_assoc($abquery) or safe_die ("Couldn't get list of answers in createFieldMap function (case M/A/B/C/E/F/H/P)<br />$abquery<br />".$connect->ErrorMsg());  //Checked
			while ($abrow=$abresult->FetchRow())
			{
				$fieldmap[$counter]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}", "type"=>$arow['type'], "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$abrow['code']);
				if ($abrow['other']=="Y") {$alsoother="Y";}
				if ($style == "full")
				{
					$fieldmap[$counter]['title']=$arow['title'];
					$fieldmap[$counter]['question']=$arow['question']."[".$abrow['answer']."]";
					$fieldmap[$counter]['group_name']=$arow['group_name'];
				}
				$counter++;
				if ($arow['type'] == "P")
				{
					$fieldmap[$counter]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}comment", "type"=>$arow['type'], "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>"comment");
					if ($style == "full")
					{
						$fieldmap[$counter]['title']=$arow['title'];
						$fieldmap[$counter]['question']=$arow['question']."[comment]";
						$fieldmap[$counter]['group_name']=$arow['group_name'];
					}
					$counter++;
				}
			}
			if ((isset($alsoother) && $alsoother=="Y") && ($arow['type']=="M" || $arow['type']=="P"))
			{
				$fieldmap[$counter]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other", "type"=>$arow['type'], "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>"other");
				if ($style == "full")
				{
					$fieldmap[$counter]['title']=$arow['title'];
					$fieldmap[$counter]['question']=$arow['question']."[".$clang->gT("Other")."]";
					$fieldmap[$counter]['group_name']=$arow['group_name'];
				}
				$counter++;
				if ($arow['type']=="P")
				{
					$fieldmap[$counter]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}othercomment", "type"=>$arow['type'], "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>"othercomment");
					if ($style == "full")
					{
						$fieldmap[$counter]['title']=$arow['title'];
						$fieldmap[$counter]['question']=$arow['question']."[".$clang->gT("Other")."comment]";
						$fieldmap[$counter]['group_name']=$arow['group_name'];
					}
					$counter++;
				}
			}
		}
		elseif ($arow['type'] == "Q" || $arow['type'] == "K")
		{
			$abquery = "SELECT ".db_table_name('answers').".*, ".db_table_name('questions').".other FROM "
			.db_table_name('answers').", ".db_table_name('questions')." WHERE sid=$surveyid AND "
			.db_table_name('answers').".qid=".db_table_name('questions').".qid AND "
			.db_table_name('answers').".language='".$s_lang."' AND "
			.db_table_name('questions').".language='".$s_lang."' AND "
			.db_table_name('questions').".qid={$arow['qid']} ORDER BY ".db_table_name('answers').".sortorder, ".db_table_name('answers').".answer";
			$abresult=db_execute_assoc($abquery) or safe_die ("Couldn't get list of answers in createFieldMap function (type Q)<br />$abquery<br />".$connect->ErrorMsg()); //Checked
			while ($abrow=$abresult->FetchRow())
			{
				$fieldmap[$counter]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}", "type"=>$arow['type'], "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$abrow['code']);
				if ($style == "full")
				{
					$fieldmap[$counter]['title']=$arow['title'];
					$fieldmap[$counter]['question']=$arow['question']."[".$abrow['answer']."]";
					$fieldmap[$counter]['group_name']=$arow['group_name'];
				}
				$counter++;
			}
		}
		elseif ($arow['type'] == "1")
		{
			$abquery = "SELECT a.*, q.other, q.lid, q.lid1 FROM {$dbprefix}answers as a, {$dbprefix}questions as q"
                       ." WHERE a.qid=q.qid AND sid=$surveyid AND q.qid={$arow['qid']} "
                       ." AND a.language='".$s_lang. "' "
                       ." AND q.language='".$s_lang. "' "
                       ." ORDER BY a.sortorder, a.answer";
			$abresult=db_execute_assoc($abquery) or safe_die ("Couldn't get perform answers query<br />$abquery<br />".$connect->ErrorMsg());    //Checked    
			$abcount=$abresult->RecordCount();
			while ($abrow=$abresult->FetchRow())
			{
				$abmultiscalequery = "SELECT l.* FROM {$dbprefix}questions as q, {$dbprefix}labels as l, {$dbprefix}answers as a"
					     ." WHERE a.qid=q.qid AND sid=$surveyid AND q.qid={$arow['qid']} "
	                     ." AND l.lid=q.lid AND sid=$surveyid AND q.qid={$arow['qid']}"
                         ." AND l.language='".$s_lang. "' "
                         ." AND a.language='".$s_lang. "' "
                         ." AND q.language='".$s_lang. "' ";
                        
				$abmultiscaleresult=db_execute_assoc($abmultiscalequery) or safe_die ("Couldn't get perform answers query<br />$abquery<br />".$connect->ErrorMsg()); //Checked
				$abmultiscalecount=$abmultiscaleresult->RecordCount();
				//if ($abmultiscalecount>0)
				if ($abmultiscaleresultrow=$abmultiscaleresult->FetchRow())
					{
 					$fieldmap[$counter]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}#0", "type"=>$arow['type'], "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$abrow['code'], "lid"=>$abmultiscaleresultrow["lid"], "lid1"=>$arow["lid1"]);
     					if ($style == "full")
						{
							$fieldmap[$counter]['title']=$arow['title'];
							$fieldmap[$counter]['question']=$arow['question']."[".$abrow['answer']."]";
							$fieldmap[$counter]['group_name']=$arow['group_name'];
						}
     					
     					$counter++;	
	
					} 
				// multi-scale
				$abmultiscalequery = "SELECT l.* FROM {$dbprefix}questions as q, {$dbprefix}labels as l, {$dbprefix}answers as a"
					     ." WHERE a.qid=q.qid AND sid=$surveyid AND q.qid={$arow['qid']} "
	                     ." AND l.lid=q.lid1 AND sid=$surveyid AND q.qid={$arow['qid']}"
                       ." AND l.language='".$s_lang. "' "
                       ." AND a.language='".$s_lang. "' "
                       ." AND q.language='".$s_lang. "' ";
                       
				$abmultiscaleresult=db_execute_assoc($abmultiscalequery) or safe_die ("Couldn't get perform answers query<br />$abquery<br />".$connect->ErrorMsg()); //Checked
				$abmultiscalecount=$abmultiscaleresult->RecordCount();
				if ($abmultiscaleresultrow=$abmultiscaleresult->FetchRow())
				{
 					$fieldmap[$counter]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}#1", "type"=>$arow['type'], "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$abrow['code'], "lid"=>$abmultiscaleresultrow["lid"], "lid1"=>$arow["lid1"]);
					if ($style == "full")
					{
						$fieldmap[$counter]['title']=$arow['title'];
						$fieldmap[$counter]['question']=$arow['question']."[".$abrow['answer']."]";
						$fieldmap[$counter]['group_name']=$arow['group_name'];
					}
					
				$counter++;				
			}
		}
		}
		
		elseif ($arow['type'] == "R")
		{
			//MULTI ENTRY
			$abquery = "SELECT ".db_table_name('answers').".*, ".db_table_name('questions').".other FROM "
			.db_table_name('answers').", ".db_table_name('questions')." WHERE "
			.db_table_name('answers').".qid=".db_table_name('questions').".qid AND sid=$surveyid AND "
			.db_table_name('answers').".language='".$s_lang."' AND "
			.db_table_name('questions').".language='".$s_lang."' AND"
			.db_table_name('questions').".qid={$arow['qid']} ORDER BY ".db_table_name('answers')
			.".sortorder, ".db_table_name('answers').".answer";
			$abresult=db_execute_assoc($abquery) or safe_die ("Couldn't get list of answers in createFieldMap function (type R)<br />$abquery<br />".$connect->ErrorMsg()); //Checked
			$abcount=$abresult->RecordCount();
			for ($i=1; $i<=$abcount; $i++)
			{
				$fieldmap[$counter]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}$i", "type"=>$arow['type'], "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$i);
				if ($style == "full")
				{
					$fieldmap[$counter]['title']=$arow['title'];
					$fieldmap[$counter]['question']=$arow['question']."[$i]";
					$fieldmap[$counter]['group_name']=$arow['group_name'];
				}
				$counter++;
			}
		}
	}
	if (isset($fieldmap)) {
		$globalfieldmap[0] = $surveyid;
		$globalfieldmap[1] = $fieldmap;

		return $fieldmap;
	}
}

function arraySearchByKey($needle, $haystack, $keyname, $maxanswers="") {
	$output=array();
	foreach($haystack as $hay) {
		if (array_key_exists($keyname, $hay)) {
			if ($hay[$keyname] == $needle) {
				if ($maxanswers == 1) {
					return $hay;
				} else {
					$output[]=$hay;
				}
			}
		}
	}
	return $output;
}

function templatereplace($line, $replacements=array())
{
	global $surveylist, $sitename, $clienttoken, $rooturl;
	global $thissurvey, $imagefiles, $defaulttemplate;
	global $percentcomplete, $move;
	global $groupname, $groupdescription, $question;
	global $questioncode, $answer, $navigator;
	global $help, $totalquestions, $surveyformat;
	global $completed, $register_errormsg;
	global $notanswered, $privacy, $surveyid;
	global $publicurl, $templatedir, $token;
	global $assessments, $s_lang;
	global $errormsg, $clang;
	global $saved_id, $templaterootdir;
	global $totalBoilerplatequestions, $relativeurl;
    global $languagechanger;    
    global $printoutput, $captchapath, $loadname;
                     
	if (stripos ($line,"</head>"))
	{
		$line=str_ireplace("</head>",
			"\t\t<script type=\"text/javascript\" src=\"$rooturl/scripts/survey_runtime.js\"></script>\n"
			.use_firebug()
			."\t</head>", $line);
	}


	// If there are non-bracketed replacements to be made do so above this line.
	// Only continue in this routine if there are bracketed items to replace {}
	if (strpos($line, "{") === false) {
		return $line;
	}

    foreach ($replacements as $replacementkey=>$replacementvalue)
    {
        if (strpos($line, '{'.$replacementkey.'}') !== false) $line=str_replace('{'.$replacementkey.'}', $replacementvalue, $line);
    }
    
	if (strpos($line, "{SURVEYLISTHEADING}") !== false) $line=str_replace("{SURVEYLISTHEADING}", $surveylist['listheading'], $line);
	if (strpos($line, "{SURVEYLIST}") !== false) $line=str_replace("{SURVEYLIST}", $surveylist['list'], $line);
	if (strpos($line, "{NOSURVEYID}") !== false) $line=str_replace("{NOSURVEYID}", $surveylist['nosid'], $line);
	if (strpos($line, "{SURVEYCONTACT}") !== false) $line=str_replace("{SURVEYCONTACT}", $surveylist['contact'], $line);
	
	if (strpos($line, "{SITENAME}") !== false) $line=str_replace("{SITENAME}", $sitename, $line);
	
	if (strpos($line, "{SURVEYLIST}") !== false) $line=str_replace("{SURVEYLIST}", $surveylist, $line);
	if (strpos($line, "{CHECKJAVASCRIPT}") !== false) $line=str_replace("{CHECKJAVASCRIPT}", "<noscript><span class='warningjs'>".$clang->gT("Caution: JavaScript execution is disabled in your browser. You may not be able to answer all questions in this survey. Please, verify your browser parameters.")."</span></noscript>", $line);
    if (strpos($line, "{ANSWERTABLE}") !== false) $line=str_replace("{ANSWERTABLE}", $printoutput, $line);
	if (strpos($line, "{SURVEYNAME}") !== false) $line=str_replace("{SURVEYNAME}", $thissurvey['name'], $line);
	if (strpos($line, "{SURVEYDESCRIPTION}") !== false) $line=str_replace("{SURVEYDESCRIPTION}", $thissurvey['description'], $line);
	if (strpos($line, "{WELCOME}") !== false) $line=str_replace("{WELCOME}", $thissurvey['welcome'], $line);
    if (strpos($line, "{LANGUAGECHANGER}") !== false) $line=str_replace("{LANGUAGECHANGER}", $languagechanger, $line);  
	if (strpos($line, "{PERCENTCOMPLETE}") !== false) $line=str_replace("{PERCENTCOMPLETE}", $percentcomplete, $line);
	if (strpos($line, "{GROUPNAME}") !== false) $line=str_replace("{GROUPNAME}", $groupname, $line);
	if (strpos($line, "{GROUPDESCRIPTION}") !== false) $line=str_replace("{GROUPDESCRIPTION}", $groupdescription, $line);
	if (strpos($line, "{QUESTION}") !== false) $line=str_replace("{QUESTION}", $question, $line);
	if (strpos($line, "{QUESTION_CODE}") !== false) $line=str_replace("{QUESTION_CODE}", $questioncode, $line);
	if (strpos($line, "{ANSWER}") !== false) $line=str_replace("{ANSWER}", $answer, $line);
	$totalquestionsAsked = $totalquestions - $totalBoilerplatequestions;
	if ($totalquestionsAsked < 1)
	{
		if (strpos($line, "{THEREAREXQUESTIONS}") !== false) $line=str_replace("{THEREAREXQUESTIONS}", $clang->gT("There are no questions in this survey"), $line); //Singular
	}
	if ($totalquestionsAsked == 1)
	{
		if (strpos($line, "{THEREAREXQUESTIONS}") !== false) $line=str_replace("{THEREAREXQUESTIONS}", $clang->gT("There is 1 question in this survey"), $line); //Singular
	}	
	else
	{
		if (strpos($line, "{THEREAREXQUESTIONS}") !== false) $line=str_replace("{THEREAREXQUESTIONS}", $clang->gT("There are {NUMBEROFQUESTIONS} questions in this survey."), $line); //Note this line MUST be before {NUMBEROFQUESTIONS}
	}
	if (strpos($line, "{NUMBEROFQUESTIONS}") !== false) $line=str_replace("{NUMBEROFQUESTIONS}", $totalquestionsAsked, $line);

	if (strpos($line, "{TOKEN}") !== false) {
		if (isset($token)) {
			$line=str_replace("{TOKEN}", $token, $line);
		}
		elseif (isset($clienttoken)) {
			$line=str_replace("{TOKEN}", htmlentities($clienttoken,ENT_QUOTES,'UTF-8'), $line);
		}
		else {
			$line=str_replace("{TOKEN}",'', $line);
		}
	}

	if (strpos($line, "{SID}") !== false) $line=str_replace("{SID}", $surveyid, $line);

	if (strpos($line, "{EXPIRY}") !== false) $line=str_replace("{EXPIRY}", $thissurvey['expiry'], $line);
	if (strpos($line, "{EXPIRY-DMY}") !== false) $line=str_replace("{EXPIRY-DMY}", date("d-m-Y",strtotime($thissurvey["expiry"])), $line);
	if (strpos($line, "{EXPIRY-MDY}") !== false) $line=str_replace("{EXPIRY-MDY}", date("m-d-Y",strtotime($thissurvey["expiry"])), $line);
	if (strpos($line, "{NAVIGATOR}") !== false) $line=str_replace("{NAVIGATOR}", $navigator, $line);
	if (strpos($line, "{SUBMITBUTTON}") !== false) {
		$submitbutton="<input class='submit' type='submit' value=' ".$clang->gT("Submit")." ' name='move2' onclick=\"javascript:document.limesurvey.move.value = 'movesubmit';\" />";
		$line=str_replace("{SUBMITBUTTON}", $submitbutton, $line);
	}
	if (strpos($line, "{COMPLETED}") !== false) $line=str_replace("{COMPLETED}", $completed, $line);
	if (strpos($line, "{URL}") !== false) {
		if ($thissurvey['surveyls_url']!=""){
            if (trim($thissurvey['surveyls_urldescription'])!=''){    
                $linkreplace="<a href='{$thissurvey['surveyls_url']}'>{$thissurvey['surveyls_urldescription']}</a>";
            }
		    else {
                $linkreplace="<a href='{$thissurvey['surveyls_url']}'>{$thissurvey['surveyls_url']}</a>";
            }
        }
        else $linkreplace='';
		$line=str_replace("{URL}", $linkreplace, $line);            
        $line=str_replace("{SAVEDID}",$saved_id, $line);     // to activate the SAVEDID in the END URL 
        if (isset($clienttoken)) {$token=$clienttoken;} else {$token='';}
		$line=str_replace("{TOKEN}",urlencode($token), $line);			// to activate the TOKEN in the END URL 
        $line=str_replace("{SID}", $surveyid, $line);       // to activate the SID in the RND URL
	}
	if (strpos($line, "{PRIVACY}") !== false) 
    {
        $line=str_replace("{PRIVACY}", $privacy, $line);
    }
	if (strpos($line, "{PRIVACYMESSAGE}") !== false) 
    {
        $line=str_replace("{PRIVACYMESSAGE}", "<span style='font-weight:bold; font-style: italic;'>".$clang->gT("A Note On Privacy")."</span><br />".$clang->gT("This survey is anonymous.")."<br />".$clang->gT("The record kept of your survey responses does not contain any identifying information about you unless a specific question in the survey has asked for this. If you have responded to a survey that used an identifying token to allow you to access the survey, you can rest assured that the identifying token is not kept with your responses. It is managed in a separate database, and will only be updated to indicate that you have (or haven't) completed this survey. There is no way of matching identification tokens with survey responses in this survey."), $line);
    }
	if (strpos($line, "{CLEARALL}") !== false) 	{
		$clearall = "\t\t\t\t\t<div class='clearall'>"
		. "<a href='{$_SERVER['PHP_SELF']}?sid=$surveyid&amp;move=clearall&amp;lang=".$_SESSION['s_lang'];
		if (returnglobal('token'))
		{
			$clearall .= "&amp;token=".urlencode(trim(sanitize_xss_string(strip_tags(returnglobal('token')))));
		}
		$clearall .="' onclick='return confirm(\""
		. $clang->gT("Are you sure you want to clear all your responses?")."\")'>["
		. $clang->gT("Exit and Clear Survey")."]</a></div>\n";


		$line=str_replace("{CLEARALL}", $clearall, $line);
	}
	// --> START NEW FEATURE - SAVE
	if (strpos($line, "{DATESTAMP}") !== false) {
		if (isset($_SESSION['datestamp'])) {
			$line=str_replace("{DATESTAMP}", $_SESSION['datestamp'], $line);
		}
		else {
			$line=str_replace("{DATESTAMP}", "-", $line);
		}
	}
	// <-- END NEW FEATURE - SAVE

	if (strpos($line, "{SAVE}") !== false)	{
		//Set up save/load feature
		if ($thissurvey['allowsave'] == "Y")
		{
			// Find out if the user has any saved data
			
            if ($thissurvey['format']=='A')
            {
                $saveall = "<input type='submit' name='loadall' value='".$clang->gT("Load Unfinished Survey")."' class='saveall' ". (($thissurvey['active'] != "Y")? "disabled='disabled'":"") ."/>"
                          ."<input type='button' name='saveallbtn' value='".$clang->gT("Resume Later")."' class='saveall' onclick=\"javascript:document.limesurvey.move.value = this.value;addHiddenField(document.getElementById('limesurvey'),'saveall',this.value);document.getElementById('limesurvey').submit();\" ". (($thissurvey['active'] != "Y")? "disabled='disabled'":"") ."/>";  // Show Save So Far button
                
            }
			elseif (!isset($_SESSION['step']) || !$_SESSION['step'])  //First page, show LOAD
			{
				$saveall = "<input type='submit' name='loadall' value='".$clang->gT("Load Unfinished Survey")."' class='saveall' ". (($thissurvey['active'] != "Y")? "disabled='disabled'":"") ."/>";
			}
			elseif (isset($_SESSION['scid']) && (isset($move) && $move == "movelast"))  //Already saved and on Submit Page, dont show Save So Far button
			{
				$saveall="";
			}
			else
			{
				$saveall= "<input type='button' name='saveallbtn' value='".$clang->gT("Resume Later")."' class='saveall' onclick=\"javascript:document.limesurvey.move.value = this.value;addHiddenField(document.getElementById('limesurvey'),'saveall',this.value);document.getElementById('limesurvey').submit();\" ". (($thissurvey['active'] != "Y")? "disabled='disabled'":"") ."/>";  // Show Save So Far button
			}
		}
		else
		{
			$saveall="";
		}
		$line=str_replace("{SAVE}", $saveall, $line);
	}
	if (strpos($line, "{TEMPLATEURL}") !== false) {
        
    
		if ($thissurvey['templatedir']) 
		{
			$templateurl="$publicurl/templates/".validate_templatedir($thissurvey['templatedir'])."/";
		}
        else  {
            $templateurl="$publicurl/templates/{$defaulttemplate}/";
        }
		$line=str_replace("{TEMPLATEURL}", $templateurl, $line);
	}
    if ($help) {
        if (strpos($line, "{QUESTIONHELP}") !== false) 
        {
           If (!isset($helpicon))
           {
              $templatedir="$templaterootdir/".$thissurvey['templatedir']."/";
               if ($thissurvey['templatedir']) 
               {
                   $templateurl="$publicurl/templates/".validate_templatedir($thissurvey['templatedir'])."/";
               }
               else  {
                   $templateurl="$publicurl/templates/{$defaulttemplate}/";
               }                        
               if (file_exists($templatedir.'/help.gif'))
              {
                
                  $helpicon=$templateurl.'/help.gif';    
              }
              elseif (file_exists($templatedir.'/help.png'))
              {
                
                  $helpicon=$templateurl.'/help.png';    
              }
              else 
              {
                  $helpicon=$imagefiles."/help.gif";
              }
           }
           $line=str_replace("{QUESTIONHELP}", "<img src='$helpicon' alt='Help' align='left' />".$help, $line);
           
        }
        if (strpos($line, "{QUESTIONHELPPLAINTEXT}") !== false) $line=str_replace("{QUESTIONHELPPLAINTEXT}", strip_tags(addslashes($help)), $line);
    }
    else
    {
        if (strpos($line, "{QUESTIONHELP}") !== false) $line=str_replace("{QUESTIONHELP}", $help, $line);
        if (strpos($line, "{QUESTIONHELPPLAINTEXT}") !== false) $line=str_replace("{QUESTIONHELPPLAINTEXT}", strip_tags(addslashes($help)), $line);
    }    

    $line=insertansReplace($line);

	if (strpos($line, "{SUBMITCOMPLETE}") !== false) $line=str_replace("{SUBMITCOMPLETE}", "<strong>".$clang->gT("Thank You!")."<br /><br />".$clang->gT("You have completed answering the questions in this survey.")."</strong><br /><br />".$clang->gT("Click on 'Submit' now to complete the process and save your answers."), $line);
	if (strpos($line, "{SUBMITREVIEW}") !== false) {
		if (isset($thissurvey['allowprev']) && $thissurvey['allowprev'] == "N") {
			$strreview = "";
		}
		else {
			$strreview=$clang->gT("If you want to check any of the answers you have made, and/or change them, you can do that now by clicking on the [<< prev] button and browsing through your responses.");
		}
		$line=str_replace("{SUBMITREVIEW}", $strreview, $line);
	}
	if (isset($_SESSION['thistoken']))
	{
		if (strpos($line, "{TOKEN:FIRSTNAME}") !== false) $line=str_replace("{TOKEN:FIRSTNAME}", $_SESSION['thistoken']['firstname'], $line);
		if (strpos($line, "{TOKEN:LASTNAME}") !== false) $line=str_replace("{TOKEN:LASTNAME}", $_SESSION['thistoken']['lastname'], $line);
		if (strpos($line, "{TOKEN:EMAIL}") !== false) $line=str_replace("{TOKEN:EMAIL}", $_SESSION['thistoken']['email'], $line);
	}
	else
	{
		if (strpos($line, "{TOKEN:FIRSTNAME}") !== false) $line=str_replace("{TOKEN:FIRSTNAME}", "", $line);
		if (strpos($line, "{TOKEN:LASTNAME}") !== false) $line=str_replace("{TOKEN:LASTNAME}", "", $line);
		if (strpos($line, "{TOKEN:EMAIL}") !== false) $line=str_replace("{TOKEN:EMAIL}", "", $line);
	}

    while (strpos($line, "{TOKEN:ATTRIBUTE_")!== false) 
    {
        $templine=substr($line,strpos($line, "{TOKEN:ATTRIBUTE_"));
        $templine=substr($templine,0,strpos($templine, "}")+1);
        $attr_no=(int)substr($templine,17,strpos($templine, "}")-17);
        $replacestr='';
        if (isset($_SESSION['thistoken']['attribute_'.$attr_no])) $replacestr=$_SESSION['thistoken']['attribute_'.$attr_no];
        $line=str_replace($templine, $replacestr, $line);
    }
    
    
    
	if (strpos($line, "{ANSWERSCLEARED}") !== false) $line=str_replace("{ANSWERSCLEARED}", $clang->gT("Answers Cleared"), $line);
	if (strpos($line, "{RESTART}") !== false)
	{
		if ($thissurvey['active'] == "N") 
		{
            $replacetext= "<a href='{$_SERVER['PHP_SELF']}?sid=$surveyid&amp;newtest=Y";
            if (isset($s_lang) && $s_lang!='') $replacetext.="&amp;lang=".$s_lang;
            $replacetext.="'>".$clang->gT("Restart this Survey")."</a>";
			$line=str_replace("{RESTART}", $replacetext, $line);
		} else {
			$restart_extra = "";
			$restart_token = returnglobal('token');
			if (!empty($restart_token)) $restart_extra .= "&amp;token=".urlencode($restart_token);
              else $restart_extra = "&amp;newtest=Y";
			if (!empty($_GET['lang'])) $restart_extra .= "&amp;lang=".returnglobal('lang');
			$line=str_replace("{RESTART}",  "<a href='{$_SERVER['PHP_SELF']}?sid=$surveyid".$restart_extra."'>".$clang->gT("Restart this Survey")."</a>", $line);
		}
	}
	if (strpos($line, "{CLOSEWINDOW}") !== false) $line=str_replace("{CLOSEWINDOW}", "<a href='javascript:%20self.close()'>".$clang->gT("Close this Window")."</a>", $line);
	if (strpos($line, "{SAVEERROR}") !== false) $line=str_replace("{SAVEERROR}", $errormsg, $line);
	if (strpos($line, "{SAVEHEADING}") !== false) $line=str_replace("{SAVEHEADING}", $clang->gT("Save Your Unfinished Survey"), $line);
	if (strpos($line, "{SAVEMESSAGE}") !== false) $line=str_replace("{SAVEMESSAGE}", $clang->gT("Enter a name and password for this survey and click save below.")."<br />\n".$clang->gT("Your survey will be saved using that name and password, and can be completed later by logging in with the same name and password.")."<br /><br />\n".$clang->gT("If you give an email address, an email containing the details will be sent to you."), $line);
	if (strpos($line, "{RETURNTOSURVEY}") !== false) 
	{
		$savereturn = "<a href='$relativeurl/index.php?sid=$surveyid";
		if (returnglobal('token'))
		{
			$savereturn.= "&amp;token=".urlencode(trim(sanitize_xss_string(strip_tags(returnglobal('token')))));
		}
 		$savereturn .= "'>".$clang->gT("Return To Survey")."</a>";
		$line=str_replace("{RETURNTOSURVEY}", $savereturn, $line);
	}	
	if (strpos($line, "{SAVEFORM}") !== false) {
		//SAVE SURVEY DETAILS
		$saveform = "<table><tr><td align='right'>".$clang->gT("Name").":</td><td><input type='text' name='savename' value='";
		if (isset($_POST['savename'])) {$saveform .= html_escape(auto_unescape($_POST['savename']));}
		$saveform .= "' /></td></tr>\n"
		. "<tr><td align='right'>".$clang->gT("Password").":</td><td><input type='password' name='savepass' value='";
		if (isset($_POST['savepass'])) {$saveform .= html_escape(auto_unescape($_POST['savepass']));}
		$saveform .= "' /></td></tr>\n"
		. "<tr><td align='right'>".$clang->gT("Repeat Password").":</td><td><input type='password' name='savepass2' value='";
		if (isset($_POST['savepass2'])) {$saveform .= html_escape(auto_unescape($_POST['savepass2']));}
		$saveform .= "' /></td></tr>\n"
		. "<tr><td align='right'>".$clang->gT("Your Email").":</td><td><input type='text' name='saveemail' value='";
		if (isset($_POST['saveemail'])) {$saveform .= html_escape(auto_unescape($_POST['saveemail']));}
		$saveform .= "' /></td></tr>\n";
        if (function_exists("ImageCreate") && captcha_enabled('saveandloadscreen',$thissurvey['usecaptcha']))
        {
		    $saveform .="<tr><td align='right'>".$clang->gT("Security Question").":</td><td><table><tr><td valign='middle'><img src='{$captchapath}verification.php' alt='' /></td><td valign='middle' style='text-align:left'><input type='text' size='5' maxlength='3' name='loadsecurity' value='' /></td></tr></table></td></tr>\n";
        }
		$saveform .= "<tr><td align='right'></td><td></td></tr>\n"
		. "<tr><td></td><td><input type='submit'  id='savebutton' name='savesubmit' value='".$clang->gT("Save Now")."' /></td></tr>\n"
		. "</table>";
		$line=str_replace("{SAVEFORM}", $saveform, $line);
	}
	if (strpos($line, "{LOADERROR}") !== false) $line=str_replace("{LOADERROR}", $errormsg, $line);
	if (strpos($line, "{LOADHEADING}") !== false) $line=str_replace("{LOADHEADING}", $clang->gT("Load A Previously Saved Survey"), $line);
	if (strpos($line, "{LOADMESSAGE}") !== false) $line=str_replace("{LOADMESSAGE}", $clang->gT("You can load a survey that you have previously saved from this screen.")."<br />".$clang->gT("Type in the 'name' you used to save the survey, and the password.")."<br />", $line);
	if (strpos($line, "{LOADFORM}") !== false) {
		//LOAD SURVEY DETAILS
		$loadform = "<table><tr><td align='right'>".$clang->gT("Saved name").":</td><td><input type='text' name='loadname' value='";
		if ($loadname) {$loadform .= html_escape(auto_unescape($loadname));}
		$loadform .= "' /></td></tr>\n"
		. "<tr><td align='right'>".$clang->gT("Password").":</td><td><input type='password' name='loadpass' value='";
		if (isset($loadpass)) { $loadform .= html_escape(auto_unescape($loadpass)); }
		$loadform .= "' /></td></tr>\n";
        if (function_exists("ImageCreate") && captcha_enabled('saveandloadscreen',$thissurvey['usecaptcha']))
        {
            $loadform .="<tr><td align='right'>".$clang->gT("Security Question").":</td><td><table><tr><td valign='middle'><img src='{$captchapath}verification.php' alt='' /></td><td valign='middle'><input type='text' size='5' maxlength='3' name='loadsecurity' value='' alt=''/></td></tr></table></td></tr>\n";
        }

        
		$loadform .="<tr><td align='right'></td><td></td></tr>\n"
		. "<tr><td></td><td><input type='submit' id='loadbutton' value='".$clang->gT("Load Now")."' /></td></tr></table>\n";
		$line=str_replace("{LOADFORM}", $loadform, $line);
	}
	//REGISTER SURVEY DETAILS
	if (strpos($line, "{REGISTERERROR}") !== false) $line=str_replace("{REGISTERERROR}", $register_errormsg, $line);
	if (strpos($line, "{REGISTERMESSAGE1}") !== false) $line=str_replace("{REGISTERMESSAGE1}", $clang->gT("You must be registered to complete this survey"), $line);
	if (strpos($line, "{REGISTERMESSAGE2}") !== false) $line=str_replace("{REGISTERMESSAGE2}", $clang->gT("You may register for this survey if you wish to take part.")."<br />\n".$clang->gT("Enter your details below, and an email containing the link to participate in this survey will be sent immediately."), $line);
	if (strpos($line, "{REGISTERFORM}") !== false)
	{
		$registerform="<form method='post' action='register.php'>\n"
		."<table class='register' summary='Registrationform'>\n"
		."<tr><td align='right'>"
		."<input type='hidden' name='sid' value='$surveyid' id='sid' />\n"
		.$clang->gT("First Name").":</td>"
		."<td align='left'><input class='text' type='text' name='register_firstname'";
		if (isset($_POST['register_firstname']))
		{
			$registerform .= " value='".htmlentities(returnglobal('register_firstname'),ENT_QUOTES,'UTF-8')."'";
		}
		$registerform .= " /></td></tr>"
		."<tr><td align='right'>".$clang->gT("Last Name").":</td>\n"
		."<td align='left'><input class='text' type='text' name='register_lastname'";
		if (isset($_POST['register_lastname']))
		{
			$registerform .= " value='".htmlentities(returnglobal('register_lastname'),ENT_QUOTES,'UTF-8')."'";
		}
		$registerform .= " /></td></tr>\n"
		."<tr><td align='right'>".$clang->gT("Email Address").":</td>\n"
		."<td align='left'><input class='text' type='text' name='register_email'";
		if (isset($_POST['register_email']))
		{
			$registerform .= " value='".htmlentities(returnglobal('register_email'),ENT_QUOTES,'UTF-8')."'";
		}
		$registerform .= " /></td></tr>\n";
        if (!isset($_REQUEST['lang']))
        {
		    $reglang = GetBaseLanguageFromSurveyID($surveyid);
        }
        else
            {
              $reglang = returnglobal('lang');    
            }
        

        if (function_exists("ImageCreate") && captcha_enabled('registrationscreen',$thissurvey['usecaptcha']))
        {
            $registerform .="<tr><td align='right'>".$clang->gT("Security Question").":</td><td><table><tr><td valign='middle'><img src='{$captchapath}verification.php' alt='' /></td><td valign='middle'><input type='text' size='5' maxlength='3' name='loadsecurity' value='' /></td></tr></table></td></tr>\n";
        }
      

		$registerform .= "<tr><td align='right'><input type='hidden' name='lang' value='".$reglang."' /></td><td></td></tr>\n";
/*		if(isset($thissurvey['attribute1']) && $thissurvey['attribute1'])
		{
			$registerform .= "<tr><td align='right'>".$thissurvey['attribute1'].":</td>\n"
			."<td align='left'><input class='text' type='text' name='register_attribute1'";
			if (isset($_POST['register_attribute1']))
			{
				$registerform .= " value='".htmlentities(returnglobal('register_attribute1'),ENT_QUOTES,'UTF-8')."'";
			}
			$registerform .= " /></td></tr>\n";
		}
		if(isset($thissurvey['attribute2']) && $thissurvey['attribute2'])
		{
			$registerform .= "<tr><td align='right'>".$thissurvey['attribute2'].":</td>\n"
			."<td align='left'><input class='text' type='text' name='register_attribute2'";
			if (isset($_POST['register_attribute2']))
			{
				$registerform .= " value='".htmlentities(returnglobal('register_attribute2'),ENT_QUOTES,'UTF-8')."'";
			}
			$registerform .= " /></td></tr>\n";
		}        */
		$registerform .= "<tr><td></td><td><input class='submit' type='submit' value='".$clang->gT("Continue")."' />"
		."</td></tr>\n"
		."</table>\n"
		."</form>\n";
		$line=str_replace("{REGISTERFORM}", $registerform, $line);
	}
	if (strpos($line, "{ASSESSMENTS}") !== false) $line=str_replace("{ASSESSMENTS}", $assessments, $line);
	if (strpos($line, "{ASSESSMENT_HEADING}") !== false) $line=str_replace("{ASSESSMENT_HEADING}", $clang->gT("Your Assessment"), $line);
	return $line;
}

/**
* insertAnsReplace() takes a string and looks for any {INSERTANS:xxxx} variables
*  which it then, one by one, substitutes the SGQA code with the relevant answer
*  from the session array containing responses
*
*  The operations of this function were previously in the templatereplace function
*  but have been moved to a function of their own to make it available
*  to other areas of the script.
* 
* @param mixed $line   string - the string to iterate, and then return
* 
* @return string This string is returned containing the substituted responses
*
*/
function insertansReplace($line)
{
	while (strpos($line, "{INSERTANS:") !== false)
	{
		$answreplace=substr($line, strpos($line, "{INSERTANS:"), strpos($line, "}", strpos($line, "{INSERTANS:"))-strpos($line, "{INSERTANS:")+1);
		$answreplace2=substr($answreplace, 11, strpos($answreplace, "}", strpos($answreplace, "{INSERTANS:"))-11);
		$answreplace3=strip_tags(retrieve_Answer($answreplace2));
		$line=str_replace($answreplace, $answreplace3, $line);
	}
	return $line;
}

/**
* passthruReplace() takes a string and looks for {PASSTHRULABEL} and {PASSTHRUVALUE} variables
*  which it then substitutes for passthru data sent in the initial URL and stored
*  in the session array containing responses
*
* @param mixed $line   string - the string to iterate, and then return
* @param mixed $thissurvey     string - the string containing the surveyinformation
* @return string This string is returned containing the substituted responses
*
*/
function passthruReplace($line, $thissurvey)
{
	$line=str_replace("{PASSTHRULABEL}", $thissurvey['passthrulabel'], $line);
	$line=str_replace("{PASSTHRUVALUE}", $thissurvey['passthruvalue'], $line);

	return $line;
}

function getSavedCount($surveyid)
{
	//This function returns a count of the number of saved responses to a survey
	global $dbprefix, $connect;
    $surveyid=sanitize_int($surveyid);
    
	$query = "SELECT COUNT(*) FROM ".db_table_name('saved_control')." WHERE sid=$surveyid";
	$result=db_execute_num($query) or safe_die ("Couldn't get saved summary<br />$query<br />".$connect->ErrorMsg());    //Checked
	list($count) = $result->FetchRow();
	return $count;
}

function GetBaseLanguageFromSurveyID($surveyid)
{
	global $connect;
	//This function loads the local language file applicable to a survey
	$surveylanguage='en';
    $surveyid=sanitize_int($surveyid);
	$query = "SELECT language FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
	$result = db_execute_num($query); //Checked
	while ($result && ($row=$result->FetchRow())) {$surveylanguage = $row[0];}
	return $surveylanguage;
}


function GetAdditionalLanguagesFromSurveyID($surveyid)
{
	global $connect;
    $surveyid=sanitize_int($surveyid);
	//This function loads the local language file applicable to a survey
	$query = "SELECT additional_languages FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
	$result = db_execute_num($query);
	while ($result && ($row=$result->FetchRow())) {$surveylanguage = $row[0];}
	if (isset($surveylanguage) && $surveylanguage !="") $additional_languages = explode(" ", trim($surveylanguage));
	if (!isset($additional_languages) || $additional_languages==false) { $additional_languages = array();}
	return $additional_languages;
}



//For multilanguage surveys
// If null or 0 is given for $surveyid then the default language from config-defaults.php is returned
function SetSurveyLanguage($surveyid, $language)// SetSurveyLanguage($surveyid)
{

		global $rootdir, $defaultlang;
        $surveyid=sanitize_int($surveyid);
		require_once($rootdir.'/classes/core/language.php');
		if (isset($surveyid) && $surveyid>0)
		{
	 		// see if language actually is present in survey
			$query = "SELECT language, additional_languages FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
			$result = db_execute_assoc($query); //Checked
			while ($result && ($row=$result->FetchRow())) {
				$additional_languages = $row['additional_languages'];
				$default_language = $row['language'];
			}
	
			if (!isset($language)
                 or ($language=='') 
                 or (isset($additional_languages) && strpos($additional_languages, $language) === false) 
                 or (isset($default_language) && $default_language == $language) 
             ) {
				// Language not supported, or default language for survey, fall back to survey's default language
				$_SESSION['s_lang'] = $default_language;
				//echo "Language not supported, resorting to ".$_SESSION['s_lang']."<br />";
			} else {
				$_SESSION['s_lang'] = $language;
				//echo "Language will be set to ".$_SESSION['s_lang']."<br />";
			}
		$clang = new limesurvey_lang($_SESSION['s_lang']);
		}
		else {
			 $clang = new limesurvey_lang($defaultlang);
			 }
		return $clang;
}


function buildLabelSetCheckSumArray()
{
	global $connect;
	// BUILD CHECKSUMS FOR ALL EXISTING LABEL SETS
	$query = "SELECT lid
              FROM ".db_table_name('labelsets')."
              ORDER BY lid";
	$result = db_execute_assoc($query) or safe_die("safe_died collecting labelset ids<br />$query<br />".$connect->ErrorMsg());  //Checked  
	$csarray=array();
	while ($row=$result->FetchRow())
	{
		$thisset="";
		$query2 = "SELECT code, title, sortorder, language
                   FROM ".db_table_name('labels')."
                   WHERE lid={$row['lid']}
                   ORDER BY language, sortorder, code";
		$result2 = db_execute_num($query2) or safe_die("safe_died querying labelset $lid<br />$query2<br />".$connect->ErrorMsg()); //Checked  
		while($row2=$result2->FetchRow())
		{
			$thisset .= implode('.', $row2);
		} // while
		$csarray[$row['lid']]=dechex(crc32($thisset)*1);
	}
	return $csarray;
}


/**
* Obsolete - please use getQAttributes instead
* 
* @param string $qid
*/
function getQuestionAttributes($qid)
{
	global $dbprefix, $connect;
    $qid=sanitize_int($qid);
	$query = "SELECT * FROM ".db_table_name('question_attributes')." WHERE qid=$qid";
	$result = db_execute_assoc($query) or safe_die("Error finding question attributes");  //Checked
	$qid_attributes=array();
	while ($row=$result->FetchRow())
	{
		$qid_attributes[]=$row;
	}
	//echo "<pre>";print_r($qid_attributes);echo "</pre>";
	return $qid_attributes;
}


/**
 * 
 * returns a flat array with all question attributes for the question only (and the qid we gave it)!
 * @author: wahrendorff
 * @param $qid
 * @return array{attribute=>value , attribute=>value}
 */
function getQAttributes($qid)
{
	$array = getQuestionAttributes($qid);
	//$return = array();
	$return["qid"]=$qid;
	foreach($array as $key=>$value)
	{
		foreach($value as $attribute=>$single)
		{
			if($attribute == "attribute")
			{
				$return[$single] =  $value["value"] ;
			}
		}
	}
	return $return;
}
/**
* Returns array of question type chars with attributes
* 
* @param mixed $returnByName If set to true the array will be by attribute name
*/
function questionAttributes($returnByName=false)
{
    global $clang;
	//For each question attribute include a key:
	// name - the display name
	// types - a string with one character representing each question typy to which the attribute applies
	// help - a short explanation

    $qattributes["alphasort"]=array(
    "types"=>"!LOWZ",
    "help"=>$clang->gT("Sort answers alphabetically"),
    "caption"=>$clang->gT('Sort answers alphabetically'));
    
    $qattributes["answer_width"]=array(
    "types"=>"ABCEF1:;",
    "help"=>$clang->gT('The percentage width of the answer column'),
    "caption"=>$clang->gT('Answer width'));

    $qattributes["array_filter"]=array(
    "types"=>"ABCEF:;R",
    "help"=>$clang->gT("Filter an array's answers from a Multiple Options Question"),
    "caption"=>$clang->gT('Array filter'));
    
    $qattributes["category_separator"]=array(
    "types"=>"!",
    "help"=>$clang->gT('Category Separator'),
    "caption"=>$clang->gT('Category Separator'));
        
    $qattributes["code_filter"]=array(
    "types"=>"WZ",
    "help"=>$clang->gT('Filter the available answers by this value'),
    "caption"=>$clang->gT('Code filter'));

	$qattributes["display_columns"]=array(
	"types"=>"LMZG",
	"help"=>$clang->gT('Number of columns to display'),
    "caption"=>$clang->gT('Display columns'));
    
    $qattributes["display_rows"]=array(
    "types"=>"TU",
    "help"=>$clang->gT('How many rows to display'),
    "caption"=>$clang->gT('Display rows'));
    
	$qattributes["hide_tip"]=array(
	"types"=>"!LMOPRWZK",
	"help"=>$clang->gT('Hide the tip that is normally shown with a question'),
    "caption"=>$clang->gT('Hide tip'));
  
	$qattributes["max_answers"]=array(
	"types"=>"MPR",
	"help"=>$clang->gT('Limit the number of possible answers'),
    "caption"=>$clang->gT('Maximum answers'));
   
    $qattributes["max_num_value"]=array(
    "types"=>"K",
    "help"=>$clang->gT('Maximum sum value of multiple numeric input'),
    "caption"=>$clang->gT('Maximum sum value'));

    $qattributes["maximum_chars"]=array(
    "types"=>"STUNQK",
    "help"=>$clang->gT('Maximum characters allowed'),
    "caption"=>$clang->gT('Maximum characters'));

	$qattributes["min_answers"]=array(
	"types"=>"MPR",
	"help"=>$clang->gT('Ensure a minimum number of possible answers'),
    "caption"=>$clang->gT('Minimum answers'));

	$qattributes["other_comment_mandatory"]=array(
	"types"=>"PLW!Z",
	"help"=>$clang->gT("Make the \"other comment\" field mandatory when the \"other\" field has been marked"),
    "caption"=>$clang->gT('Other comment mandatory'));
    
    $qattributes["numbers_only"]=array(
    "types"=>"Q;",
    "help"=>$clang->gT('Allow only numerical input'),
    "caption"=>$clang->gT('Numbers only'));


    $qattributes["other_numbers_only"]=array(
    "types"=>"LMP",
    "help"=>$clang->gT('Allow only numerical input for `Other` text'),
    "caption"=>$clang->gT('Numbers only for `Other`'));


    $qattributes["random_order"]=array(
    "types"=>"!LMOPQKRWZFHABCE1:;",
    "help"=>$clang->gT('Present answers in random order'),
    "caption"=>$clang->gT('Random answer order'));
    
    $qattributes["text_input_width"]=array(
    "types"=>"NSTUK;Q",
    "help"=>$clang->gT('Width of text input box'),
    "caption"=>$clang->gT('Input box width'));
    
	$qattributes["equals_num_value"]=array(
	"types"=>"K",
	"help"=>$clang->gT('Multiple numeric inputs sum must equal this value'),
    "caption"=>$clang->gT('Equals sum value'));
    
	$qattributes["min_num_value"]=array(
	"types"=>"K",
	"help"=>$clang->gT('Multiple numeric inputs must be greater than this value'),
    "caption"=>$clang->gT('Minimum sum value'));

	$qattributes["slider_layout"]=array(
	"types"=>"K",
	"help"=>$clang->gT('Use slider layout'),
    "caption"=>$clang->gT('Use slider layout'));
    
	$qattributes["slider_min"]=array(
	"types"=>"K",
	"help"=>$clang->gT('Slider minimum value'),
    "caption"=>$clang->gT('Slider minimum value'));
    
	$qattributes["slider_max"]=array(
	"types"=>"K",
	"help"=>$clang->gT('Slider maximum value'),
    "caption"=>$clang->gT('Slider maximum value'));
    
	$qattributes["slider_accuracy"]=array(
	"types"=>"K",
	"help"=>$clang->gT('Slider accuracy'),
    "caption"=>$clang->gT('Slider accuracy'));
    
	$qattributes["slider_default"]=array(
	"types"=>"K",
	"help"=>$clang->gT('Slider initial value'),
    "caption"=>$clang->gT('Slider initial value'));

	$qattributes["prefix"]=array(
	"types"=>"KNSQ",
	"help"=>$clang->gT('Add a prefix to the answer field'),
    "caption"=>$clang->gT('Answer prefix'));
    
	$qattributes["suffix"]=array(
	"types"=>"KNSQ",
	"help"=>$clang->gT('Add a suffix to the answer field'),
    "caption"=>$clang->gT('Answer suffix'));
	
	$qattributes["dropdown_dates"]=array(
	"types"=>"D",
	"help"=>$clang->gT('Use accessible select boxes instead of calendar popup'),
    "caption"=>$clang->gT('Display select boxes'));
    
	$qattributes["dropdown_dates_year_min"]=array(
	"types"=>"D",
	"help"=>$clang->gT('Minimum year value in calendar'),
    "caption"=>$clang->gT('Minimum dropdown year'));
    
	$qattributes["dropdown_dates_year_max"]=array(
	"types"=>"D",
	"help"=>$clang->gT('Maximum year value for calendar'),
    "caption"=>$clang->gT('Maximum dropdown year'));
	
	$qattributes["exclude_all_others"]=array(
	"types"=>"M",
	"help"=>$clang->gT('Excludes all other options if this is selected'),
    "caption"=>$clang->gT('Exclusive option'));
    
	$qattributes["multiflexible_max"]=array(
	"types"=>":",
	"help"=>$clang->gT('Maximum value for array(mult-flexible) question type'),
    "caption"=>$clang->gT('Maximum value'));
    
	$qattributes["multiflexible_min"]=array(
	"types"=>":",
	"help"=>$clang->gT('Minimum value for array(multi-flexible) question type'),
    "caption"=>$clang->gT('Minimum value'));
    
	$qattributes["multiflexible_step"]=array(
	"types"=>":",
	"help"=>$clang->gT('Step value for array (multi-flexible) question type'),
    "caption"=>$clang->gT('Step value'));
    
	$qattributes["multiflexible_checkbox"]=array(
	"types"=>":",
	"help"=>$clang->gT('Use Checkbox layout for array (multi-flexible) question type'),
    "caption"=>$clang->gT('Checkbox layout'));
    
	$qattributes["use_dropdown"]=array(
	"types"=>"1",
	"help"=>$clang->gT('Use Dual Dropdown instead of Dual Scale'),
    "caption"=>$clang->gT('Dual dropdown'));
    
	$qattributes["dropdown_prepostfix"]=array(
	"types"=>"1",
	"help"=>$clang->gT('Prefix|Suffix for dropdown lists'),
    "caption"=>$clang->gT('Prefix|Suffix'));
    
	$qattributes["dualscale_headerA"]=array(
	"types"=>"1",
	"help"=>$clang->gT('Header for scale A'),
    "caption"=>$clang->gT('Header scale A'));
    
	$qattributes["dualscale_headerB"]=array(
	"types"=>"1",
	"help"=>$clang->gT('Header for scale B'),
    "caption"=>$clang->gT('Header scale B'));
    
	$qattributes["dropdown_separators"]=array(
	"types"=>"1",
	"help"=>$clang->gT('Post-Answer-Separator|Inter-Dropdownlist-Separator for dropdown lists'),
    "caption"=>$clang->gT('Dropdown separators'));
    
	$qattributes["other_replace_text"]=array(
	"types"=>"LMPWZ!",
	"help"=>$clang->gT("Replaces the 'other' label with text"),
    "caption"=>$clang->gT('"Other" caption'));
	
	$qattributes["public_statistics"]=array(
	"types"=>"15ABCEFGHKLMNOPRWYZ!:",
	"help"=>$clang->gT('Show statistics of a certain question to the user'),
    "caption"=>$clang->gT('Show in public statistics'));
	
	$qattributes["max_num_value_sgqa"]=array(
	"types"=>"K",
	"help"=>$clang->gT('SGQA identifier to use total of previous question as maximum for this question'),
	"caption"=>$clang->gT('Max value from SGQA'));
	
	$qattributes["min_num_value_sgqa"]=array(
	"types"=>"K",
	"help"=>$clang->gT('SGQA identifier to use total of previous question as minimum for this question'),
	"caption"=>$clang->gT('Min value from SGQA'));

	$qattributes["num_value_equals_sgqa"]=array(
	"types"=>"K",
	"help"=>$clang->gT('SGQA identifier to use total of previous question as total for this question'),
	"caption"=>$clang->gT('Value equals SGQA'));

    $qattributes["page_break"]=array(
    "types"=>"15ABCEFGHKLMNOPRWYZ!:",
    "help"=>$clang->gT('Insert a page break before this question in printable view by setting this to 1.'),
    "caption"=>$clang->gT('Insert page break in printable view'));
    
    $qattributes["scale_export"]=array(
    "types"=>"!LOFWZWH1:MPOGYCE",
    "help"=>$clang->gT("1=nominal 2=ordinal 3=scale."),
    "caption"=>$clang->gT('Export scale type 1=nominal 2=ordinal 3=scale.'));
	//This builds a more useful array (don't modify)
    if ($returnByName!=true)
    {
	    foreach($qattributes as $qname=>$qvalue)
	    {
		    for ($i=0; $i<=strlen($qvalue['types'])-1; $i++)
		    {
			    $qat[substr($qvalue['types'], $i, 1)][]=array("name"=>$qname,
			                                                "help"=>$qvalue['help'],
                                                            "caption"=>$qvalue['caption']);
		    }
	    }
        return $qat;
    }
    else {
        return $qattributes;
    }
}

// make sure the given string (which comes from a POST or GET variable)
// is safe to use in MySQL.  This does nothing if gpc_magic_quotes is on.
function auto_escape($str) {
	global $connect;
	if (!get_magic_quotes_gpc()) {
		return $connect->escape($str);
	}
	return $str;
}
// the opposite of the above: takes a POST or GET variable which may or
// may not have been 'auto-quoted', and return the *unquoted* version.
// this is useful when the value is destined for a web page (eg) not
// a SQL query.
function auto_unescape($str) {
    if (!isset($str)) {return null;};
	if (!get_magic_quotes_gpc())
	return $str;
	return stripslashes($str);
}
// make a string safe to include in an HTML 'value' attribute.
function html_escape($str) {
	// escape newline characters, too, in case we put a value from
	// a TEXTAREA  into an <input type="hidden"> value attribute.
	return str_replace(array("\x0A","\x0D"),array("&#10;","&#13;"),
	htmlspecialchars( $str, ENT_QUOTES ));
}

// make a string safe to include in a JavaScript String parameter.
function javascript_escape($str, $strip_tags=false, $htmldecode=false) {
    $new_str ='';

    if ($htmldecode==true) {
        $str=html_entity_decode($str,ENT_QUOTES,'UTF-8');
    }
    if ($strip_tags==true)
    {
        $str=strip_tags($str);
    }
    return str_replace(array('\'','"', "\n"),
	                   array("\\'",'\u0022', "\\n"),
					   $str);
}

// This function returns the header as result string
// If you want to echo the header use doHeader() !
function getHeader()
{
	global $embedded, $surveyid, $rooturl,$defaultlang, $js_header_includes, $css_header_includes;

    $js_header_includes = array_unique($js_header_includes);
    $css_header_includes = array_unique($css_header_includes);
    
    if (isset($_SESSION['s_lang']) && $_SESSION['s_lang'])
    {
        $surveylanguage= $_SESSION['s_lang'];
    }
    elseif (isset($surveyid) && $surveyid) 
    {
        $surveylanguage=GetBaseLanguageFromSurveyID($surveyid);
    }
	else 
    {
        $surveylanguage=$defaultlang;
    }

	$js_header = ''; $css_header='';
	foreach ($js_header_includes as $jsinclude)
	{
		$js_header .= "<script type=\"text/javascript\" src=\"".$rooturl."$jsinclude\"></script>\n";
	}

    foreach ($css_header_includes as $cssinclude)
    {
        $css_header .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"".$rooturl.$cssinclude."\" />\n";
    }
    
    
	if ( !$embedded )
	{
		$header=  "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n"
        		. "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"".$surveylanguage."\" lang=\"".$surveylanguage."\"";
        if (getLanguageRTL($surveylanguage))
        {
            $header.=" dir=\"rtl\" ";
        }
        $header.= ">\n\t<head>\n"
                . $css_header
                . "\t\t<script type=\"text/javascript\" src=\"".$rooturl."/scripts/jquery/jquery.js\"></script>\n"
			    . $js_header;
			
        return $header;        
    }

	global $embedded_headerfunc;

	if ( function_exists( $embedded_headerfunc ) )
	return $embedded_headerfunc();
}

function doHeader()
{
	echo getHeader();
}

function doAdminFooter()
{
	echo getAdminFooter();
}

function getAdminFooter($url, $explanation)
{
	global $versionnumber, $buildnumber, $setfont, $imagefiles, $clang;

	if ($buildnumber != "")
	{
		$buildtext="($buildnumber)";
	}
	else
	{
		$buildtext="";
	}
   
   //If user is not logged in, don't print the version number information in the footer.
   $versiontitle=$clang->gT('Version');
   if(!isset($_SESSION['loginID'])) 
   {
     $versionnumber="";
     $buildtext="";
     $versiontitle="";
   }

	$strHTMLFooter = "<div class='footer'>\n"
	. "\t\t\t<div style='float:left;width:110px;text-align:left;'><img alt='LimeSurvey - ".$clang->gT("Online Manual")."' title='LimeSurvey - ".$clang->gT("Online Manual")."' src='$imagefiles/docs.png' "
	. "onclick=\"window.open('$url')\" onmouseover=\"document.body.style.cursor='pointer'\" "
	. "onmouseout=\"document.body.style.cursor='auto'\" /></div>\n"
	. "\t\t\t<div style='float:right;'><img alt='".$clang->gT("Support this project - Donate to ")."LimeSurvey' title='".$clang->gT("Support this project - Donate to ")."LimeSurvey!' src='$imagefiles/donate.png' "
	. "onclick=\"window.open('http://www.donate.limesurvey.org')\" "
	. "onmouseover=\"document.body.style.cursor='pointer'\" onmouseout=\"document.body.style.cursor='auto'\" /></div>\n"
	. "\t\t\t<div class='subtitle'><a class='subtitle' title='".$clang->gT("Visit our website!")."' href='http://www.limesurvey.org' target='_blank'>LimeSurvey</a><br />".$versiontitle." $versionnumber $buildtext</div>"
	. "</div></body>\n</html>";
	return $strHTMLFooter;
}


function doAdminHeader()
{
	echo getAdminHeader();
}

function getAdminHeader($meta=false)
{
	global $sitename, $admintheme, $rooturl, $defaultlang, $js_adminheader_includes, $css_adminheader_includes;
	if (!isset($_SESSION['adminlang']) || $_SESSION['adminlang']=='') {$_SESSION['adminlang']=$defaultlang;}
	$strAdminHeader="<?xml version=\"1.0\"?><!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n"
	."<html ";
    
    if (getLanguageRTL($_SESSION['adminlang']))
    {
        $strAdminHeader.=" dir=\"rtl\" ";
    }
    $strAdminHeader.=">\n<head>\n"
	. "<!--[if lt IE 7]>\n"
	. "<script defer type=\"text/javascript\" src=\"scripts/pngfix.js\"></script>\n"
	. "<![endif]-->\n"
	. "<title>$sitename</title>\n";
    
	if ($meta)
        {
        $strAdminHeader.=$meta;
        }
	$strAdminHeader.="\t\t<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />\n"
	. "<script type=\"text/javascript\" src=\"scripts/tabpane/js/tabpane.js\"></script>\n"
	. "<script type=\"text/javascript\" src=\"scripts/tooltips.js\"></script>\n"                    
    . "<script type=\"text/javascript\" src=\"../scripts/jquery/jquery.js\"></script>\n"
    . "<script type=\"text/javascript\" src=\"../scripts/jquery/jquery-ui.js\"></script>\n";
    if ($_SESSION['adminlang']!='en')
    {
        $strAdminHeader.= "<script type=\"text/javascript\" src=\"../scripts/jquery/locale/ui.datepicker-{$_SESSION['adminlang']}.js\"></script>\n";
    }
    $strAdminHeader.= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"styles/$admintheme/tab.webfx.css \" />\n"
    . "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"../scripts/jquery/css/start/jquery-ui-1.7.1.custom.css\" />\n"
    . "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/$admintheme/printablestyle.css\" media=\"print\" />\n"    
    . "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/$admintheme/adminstyle.css\" />\n";
    if (getLanguageRTL($_SESSION['adminlang']))
    {
        $strAdminHeader.="\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/$admintheme/adminstyle-rtl.css\" />\n";
    }

    $js_adminheader_includes = array_unique($js_adminheader_includes);
    $css_adminheader_includes = array_unique($css_adminheader_includes);
    
    foreach ($js_adminheader_includes as $jsinclude)
    {
        $strAdminHeader .= "<script type=\"text/javascript\" src=\"".$jsinclude."\"></script>\n";
    }    
    foreach ($css_adminheader_includes as $cssinclude)
    {
        $strAdminHeader .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"$cssinclude\" />\n";
    }    
	$strAdminHeader.= "\t\t<script type=\"text/javascript\" src=\"scripts/admin_core.js\"></script>"
	. use_firebug()
	. "\t</head>\n\t<body>\n";
    if (isset($_SESSION['dateformat']))
    {
        $formatdata=getDateFormatData($_SESSION['dateformat']);
        $strAdminHeader .= "<script type='text/javascript'>
                               var userdateformat='".$formatdata['jsdate']."';
                               var userlanguage='".$_SESSION['adminlang']."';
                           </script>";
    }
    
    $strAdminHeader .="\t\t<div class=\"maintitle\">\n"
	. "\t\t\t$sitename\n"
	. "\t\t</div>\n";
	return $strAdminHeader;
}


function getPrintableHeader()
{
    global $rooturl;
    $headelements = '
            <meta http-equiv="content-type" content="text/html; charset=UTF-8" />

    <!--[if lt IE 7]>
            <script defer type="text/javascript" src="'.$rooturl.'/scripts/pngfix.js"></script>
    <![endif]-->

            <script type="text/javascript" src="'.$rooturl.'/admin/scripts/tabpane/js/tabpane.js"></script>
            <script type="text/javascript" src="'.$rooturl.'/admin/scripts/tooltips.js"></script>

    '; 
    return $headelements;   
}




// This function returns the Footer as result string
// If you want to echo the Footer use doFooter() !
function getFooter()
{
	global $embedded;

	if ( !$embedded )
	{
		return "\n\n\t</body>\n</html>\n";
	}

	global $embedded_footerfunc;

	if ( function_exists( $embedded_footerfunc ) )
	return $embedded_footerfunc();
}


function doFooter()
{
	echo getFooter();
}



// This function replaces field names in a text with the related values
// (e.g. for email and template functions)
function ReplaceFields ($text,$fieldsarray)
{

	foreach ( $fieldsarray as $key => $value )
	{
		$text=str_replace($key, $value, $text);
	}
	return $text;
}

function MailTextMessage($body, $subject, $to, $from, $sitename, $ishtml=false, $bouncemail=null)
{
// This function mails a text $body to the recipient $to. YOu can use more than one 
// recipient when using a comma separated string with recipients.

	global $emailmethod, $emailsmtphost, $emailsmtpuser, $emailsmtppassword, $defaultlang;
    global $rootdir, $maildebug, $maildebugbody, $emailsmtpssl, $clang, $demoModeOnly, $emailcharset;

    //if ($ishtml) {$body=htmlwrap($body,110);}

     if ($demoModeOnly==true)
     {
         $maildebug=$clang->gT('Email was not sent because demo-mode is activated.');
         $maildebugbody='';
         return false;
     }    
    
	if (is_null($bouncemail) )
	{
		$sender=$from;
	}
	else
	{
		$sender=$bouncemail;
	}

	$mail = new PHPMailer;
    if (!$mail->SetLanguage($defaultlang,$rootdir.'/classes/phpmailer/language/')) 
    {
        $mail->SetLanguage('en',$rootdir.'/classes/phpmailer/language/');
    }
	$mail->CharSet = $emailcharset;
	if (isset($emailsmtpssl) && trim($emailsmtpssl)!=='' && $emailsmtpssl!==0) {
        if ($emailsmtpssl===1) {$mail->SMTPSecure = "ssl";}
    	 else {$mail->SMTPSecure = $emailsmtpssl;}
	 }

	$fromname='';
	$fromemail=$from;
	if (strpos($from,'<'))
	{
		$fromemail=substr($from,strpos($from,'<')+1,strpos($from,'>')-1-strpos($from,'<'));
		$fromname=trim(substr($from,0, strpos($from,'<')-1));
	}

	$sendername='';
	$senderemail=$sender;
	if (strpos($sender,'<'))
	{
		$senderemail=substr($sender,strpos($sender,'<')+1,strpos($sender,'>')-1-strpos($sender,'<'));
		$sendername=trim(substr($sender,0, strpos($sender,'<')-1));
	}

    if ($emailmethod=="qmail")
    {
        $mail->IsQmail();
    }
    else
    {
        $mail->Mailer = $emailmethod;
    }
	if ($emailmethod=="smtp")
	{ 
        if (strpos($emailsmtphost,':')>0)
        {
            $mail->Host = substr($emailsmtphost,0,strpos($emailsmtphost,':'));
            $mail->Port = substr($emailsmtphost,strpos($emailsmtphost,':')+1);
        }
        else {
            $mail->Host = $emailsmtphost;
        }
	    $mail->Username =$emailsmtpuser;
	    $mail->Password =$emailsmtppassword;
	    if ($emailsmtpuser!="")
	    {$mail->SMTPAuth = true;}
	}
	$mail->From = $fromemail;
	$mail->Sender = $senderemail; // Sets Return-Path for error notifications
    $toemails = explode(";", $to);
    foreach ($toemails as $singletoemail)
    {
        if (strpos($singletoemail, '<') )
        {
	       $toemail=substr($singletoemail,strpos($singletoemail,'<')+1,strpos($singletoemail,'>')-1-strpos($singletoemail,'<'));
           $toname=trim(substr($singletoemail,0, strpos($singletoemail,'<')-1));
           $mail->AddAddress($toemail,$toname);
        }
        else
        {
            $mail->AddAddress($singletoemail);
        }
    }	
	$mail->FromName = $fromname;
	$mail->AddCustomHeader("X-Surveymailer: $sitename:Emailer (LimeSurvey.sourceforge.net)");
	if (get_magic_quotes_gpc() != "0")	{$body = stripcslashes($body);}
	$textbody = strip_tags($body);
	$textbody = str_replace("&quot;", '"', $textbody);
    if ($ishtml) { 
        $mail->IsHTML(true);
    	$mail->Body = $body;
    	$mail->AltBody = strip_tags(br2nl(html_entity_decode($textbody,ENT_QUOTES,'UTF-8')));
    } else
        {
        $mail->IsHTML(false);
    	$mail->Body = $textbody;
        }

	if (trim($subject)!='') {$mail->Subject = "=?$emailcharset?B?" . base64_encode($subject) . "?=";}
    $sent=$mail->Send();
    $maildebug=$mail->ErrorInfo;
    $maildebugbody=$mail->Body;
	return $sent;
}

// This functions removes all tags, CRs, linefeeds and other strange chars from a given text
function FlattenText($texttoflatten)
{
    $nicetext = strip_javascript($texttoflatten);
	$nicetext = strip_tags($nicetext);
	$nicetext = str_replace("\r", "", $nicetext);
	$nicetext = trim(str_replace("\n", "", $nicetext));
	return  $nicetext;
}
/**
* getreferringurl() returns the reffering URL
*/
function getreferringurl()
{
  global $clang,$stripQueryFromRefurl;
  if (isset($_SESSION['refurl']))
  {
    return; // do not overwrite refurl
  }

  // refurl is not set in session, read it from server variable
  if(isset($_SERVER["HTTP_REFERER"]))
  {
    if(!ereg($_SERVER["SERVER_NAME"], $_SERVER["HTTP_REFERER"]))
    {
      if (!isset($stripQueryFromRefurl) || !$stripQueryFromRefurl)
      {
           $_SESSION['refurl'] = $_SERVER["HTTP_REFERER"];
      }
      else
      {
	   $aRefurl = explode("?",$_SERVER["HTTP_REFERER"]);
           $_SESSION['refurl'] = $aRefurl[0];
      }
    }
    else
    {
       $_SESSION['refurl'] = '-';
    }
  }
  else
  {
    $_SESSION['refurl'] = null;
  }
}

function getRandomID()
{        // Create a random survey ID - based on code from Ken Lyle
	// Random sid/ question ID generator...
	$totalChar = 5; // number of chars in the sid
	$salt = "123456789"; // This is the char. that is possible to use
	srand((double)microtime()*1000000); // start the random generator
	$sid=""; // set the inital variable
	for ($i=0;$i<$totalChar;$i++) // loop and create sid
	$sid = $sid . substr ($salt, rand() % strlen($salt), 1);
	return $sid;
}

/**
* getArrayFiltersForGroup() queries the database and produces a list of array_filter questions and targets with in the same group
* @global string $surveyid
* @global string $gid
* @global string $dbprefix
* @return returns an nested array which contains arrays with the keys: question id (qid), question manditory, target type (type), and list_filter id (fid)
*/
function getArrayFiltersForGroup($surveyid,$gid)
{
	// TODO: Check list_filter values to make sure questions are previous?
	global $dbprefix;
    $surveyid=sanitize_int($surveyid);
    $gid=sanitize_int($gid);
    // Get All Questions in Current Group
	$qquery = "SELECT * FROM ".db_table_name('questions')." WHERE sid='$surveyid'";
	if($gid != "") {$qquery .= " AND gid='$gid'";}
	$qquery .= " AND language='".$_SESSION['s_lang']."' ORDER BY qid";
	$qresult = db_execute_assoc($qquery);  //Checked
	$grows = array(); //Create an empty array in case query not return any rows
	// Store each result as an array with in the $grows array
	while ($qrow = $qresult->FetchRow()) {
		$grows[$qrow['qid']] = array('qid' => $qrow['qid'],'type' => $qrow['type'], 'mandatory' => $qrow['mandatory'], 'title' => $qrow['title'], 'gid' => $qrow['gid']);
	}
	$attrmach = array(); // Stores Matches of filters that have their values as questions with in current group
	$grows2 = $grows;
	foreach ($grows as $qrow) // Cycle through questions to see if any have list_filter attributes
	{
		$qquery = "SELECT value FROM ".db_table_name('question_attributes')." WHERE attribute='array_filter' AND qid='".$qrow['qid']."'";
		$qresult = db_execute_num($qquery);     //Checked
		if ($qresult->RecordCount() == 1) // We Found a array_filter attribute
		{
			$val = $qresult->FetchRow(); // Get the Value of the Attribute ( should be a previous question's title in same group )
			foreach ($grows2 as $avalue)
			{
				if ($avalue['title'] == $val[0])
				{
					$filter = array('qid' => $qrow['qid'], 'mandatory' => $qrow['mandatory'], 'type' => $avalue['type'], 'fid' => $avalue['qid'], 'gid' => $qrow['gid'], 'gid2'=>$avalue['gid']);
					array_push($attrmach,$filter);
				}
			}
			reset($grows2);
		}
	}
	return $attrmach;
}

/**
* getArrayFiltersForQuestion($qid) finds out if a question has an array_filter attribute and what codes where selected on target question
* @global string $surveyid
* @global string $gid
* @global string $dbprefix
* @return returns an array of codes that were selected else returns false
*/
function getArrayFiltersForQuestion($qid)
{
	// TODO: Check list_filter values to make sure questions are previous?
	global $surveyid, $dbprefix;
    $qid=sanitize_int($qid);
	$query = "SELECT value FROM ".db_table_name('question_attributes')." WHERE attribute='array_filter' AND qid='".$qid."'";
	$result = db_execute_assoc($query);  //Checked
	if ($result->RecordCount() == 1) // We Found a array_filter attribute
	{
		$val = $result->FetchRow(); // Get the Value of the Attribute ( should be a previous question's title in same group )
		foreach ($_SESSION['fieldarray'] as $fields)
		{
			if ($fields[2] == $val['value'])
			{
				// we found the target question, now we need to know what the answers where, we know its a multi!
                $fields[0]=sanitize_int($fields[0]);
				$query = "SELECT code FROM ".db_table_name('answers')." where qid='{$fields[0]}' AND language='".$_SESSION['s_lang']."' order by sortorder";
				$qresult = db_execute_assoc($query);  //Checked
				$selected = array();
				while ($code = $qresult->fetchRow())
				{
					if ($_SESSION[$fields[1].$code['code']] == "Y") array_push($selected,$code['code']);
				}
				return $selected;
			}
		}
		return false;
	}
	return false;
}

/**
* getArrayFiltersForQuestion($qid) finds out if a question is in the currect group or not for array filter
* @global string $surveyid
* @global string $gid
* @global string $dbprefix
* @return returns true if its not in currect group and false if it is..
*/
function getArrayFiltersOutGroup($qid)
{
	// TODO: Check list_filter values to make sure questions are previous?
	global $surveyid, $dbprefix, $gid;
    $qid=sanitize_int($qid);
	$query = "SELECT value FROM ".db_table_name('question_attributes')." WHERE attribute='array_filter' AND qid='".$qid."'";
	$result = db_execute_assoc($query); //Checked
	if ($result->RecordCount() == 1) // We Found a array_filter attribute
	{
		$val = $result->FetchRow(); // Get the Value of the Attribute ( should be a previous question's title in same group )

		// we found the target question, now we need to know what the answers where, we know its a multi!
		$query = "SELECT gid FROM ".db_table_name('questions')." where title='{$val['value']}' AND language='".$_SESSION['s_lang']."' AND sid = $surveyid";
		$qresult = db_execute_assoc($query); //Checked
		if ($qresult->RecordCount() == 1)
		{
			$val2 = $qresult->FetchRow();
			if ($val2['gid'] != $gid) return true;
			if ($val2['gid'] == $gid) return false;
		}
		return false;
	}
	return false;
}


/**
 * Run an arbitrary sequence of semicolon-delimited SQL commands
 *
 * Assumes that the input text (file or string) consists of
 * a number of SQL statements ENDING WITH SEMICOLONS.  The
 * semicolons MUST be the last character in a line.
 * Lines that are blank or that start with "#" or "--" (postgres) are ignored.
 * Only tested with mysql dump files (mysqldump -p -d limesurvey)
 * Function kindly borrowed by Moodle
 * @uses $dbprefix
 * @param string $sqlfile The path where a file with sql commands can be found on the server.
 * @param string $sqlstring If no path is supplied then a string with semicolon delimited sql
 * commands can be supplied in this argument.
 * @return bool Returns true if database was modified successfully.
 */
function modify_database($sqlfile='', $sqlstring='') 
{
	global $dbprefix;
	global $defaultuser;
	global $defaultpass;
	global $siteadminemail;
	global $siteadminname;
	global $defaultlang;
	global $codeString;
	global $rootdir, $homedir;
    global $connect;
    global $clang;
    global $modifyoutput;
    global $databasetabletype;

	require_once($homedir."/classes/core/sha256.php");

	$success = true;  // Let's be optimistic
    $modifyoutput='';

	if (!empty($sqlfile)) {
		if (!is_readable($sqlfile)) {
			$success = false;
			echo '<p>Tried to modify database, but "'. $sqlfile .'" doesn\'t exist!</p>';
			return $success;
		} else {
			$lines = file($sqlfile);
		}
	} else {
		$sqlstring = trim($sqlstring);
		if ($sqlstring{strlen($sqlstring)-1} != ";") {
			$sqlstring .= ";"; // add it in if it's not there.
		}
		$lines[] = $sqlstring;
	}

	$command = '';

	foreach ($lines as $line) {
		$line = rtrim($line);
		$length = strlen($line);

		if ($length and $line[0] <> '#' and substr($line,0,2) <> '--') {
			if (substr($line, $length-1, 1) == ';') {
  				$line = substr($line, 0, $length-1);   // strip ;
				$command .= $line;
				$command = str_replace('prefix_', $dbprefix, $command); // Table prefixes
				$command = str_replace('$defaultuser', $defaultuser, $command); // variables By Moses
				$command = str_replace('$defaultpass', SHA256::hashing($defaultpass), $command); // variables By Moses
				$command = str_replace('$siteadminname', $siteadminname, $command);
				$command = str_replace('$siteadminemail', $siteadminemail, $command); // variables By Moses
				$command = str_replace('$defaultlang', $defaultlang, $command); // variables By Moses
				$command = str_replace('$sessionname', 'ls'.getRandomID().getRandomID().getRandomID().getRandomID(), $command); // variables By Moses
				$command = str_replace('$databasetabletype', $databasetabletype, $command);

				if (! db_execute_num($command)) {  //Checked
                  $command=htmlspecialchars($command);
                  $modifyoutput .="<br />".$clang->gT("Executing").".....".$command."<font color='#FF0000'>...".$clang->gT("Failed! Reason: ").$connect->ErrorMsg()."</font>";
				  $success = false;
				}
                 else
                 {
                    $command=htmlspecialchars($command);
                    $modifyoutput .="<br />".$clang->gT("Executing").".....".$command."<font color='#00FF00'>...".$clang->gT("Success!")."</font>";
                 }

				$command = '';
			} else {
				$command .= $line;
			}
		}
	}

	return $success;

}



// unsets all Session variables to kill session
function killSession()	//added by Dennis
{
		// Delete the Session Cookie
		$CookieInfo = session_get_cookie_params();
		if ( (empty($CookieInfo['domain'])) && (empty($CookieInfo['secure'])) ) {
			setcookie(session_name(), '', time()-3600, $CookieInfo['path']);
		} elseif (empty($CookieInfo['secure'])) {
			setcookie(session_name(), '', time()-3600, $CookieInfo['path'], $CookieInfo['domain']);
		} else {
			setcookie(session_name(), '', time()-3600, $CookieInfo['path'], $CookieInfo['domain'], $CookieInfo['secure']);
		}
		unset($_COOKIE[session_name()]);
        foreach ($_SESSION as $key =>$value) 
        {
		  //echo $key." = ".$value."<br />";
		  unset($_SESSION[$key]);
		}
		$_SESSION = array(); // redundant with previous lines
		session_unset();
		session_destroy();
}







// set the rights of a user and his children
function setuserrights($uid, $rights)
{
	global $connect;
    $uid=sanitize_int($uid);
	$updates = "create_survey=".$rights['create_survey']
	. ", create_user=".$rights['create_user']
	. ", delete_user=".$rights['delete_user']
	. ", superadmin=".$rights['superadmin']
	. ", configurator=".$rights['configurator']
	. ", manage_template=".$rights['manage_template']
	. ", manage_label=".$rights['manage_label'];
	$uquery = "UPDATE ".db_table_name('users')." SET ".$updates." WHERE uid = ".$uid;
 	return $connect->Execute($uquery);     //Checked
}

// set the rights for a survey
function setsurveyrights($uids, $rights)
{
	global $connect, $surveyid;
    $uids=array_map('sanitize_int',$uids);  
	$uids_implode = implode(" OR uid = ", $uids);

	$updates = "edit_survey_property=".$rights['edit_survey_property']
	. ", define_questions=".$rights['define_questions']
	. ", browse_response=".$rights['browse_response']
	. ", export=".$rights['export']
	. ", delete_survey=".$rights['delete_survey']
	. ", activate_survey=".$rights['activate_survey'];
	$uquery = "UPDATE ".db_table_name('surveys_rights')." SET ".$updates." WHERE sid = {$surveyid} AND uid = ".$uids_implode;
	// TODO
	return $connect->Execute($uquery);   //Checked 
}

function createPassword()
{
	$pwchars = "abcdefhjmnpqrstuvwxyz23456789";
	$password_length = 8;
	$passwd = '';

	for ($i=0; $i<$password_length; $i++)
		{
		$passwd .= $pwchars[floor(rand(0,strlen($pwchars)-1))];
		}
	return $passwd;
}

function getgroupuserlist()
{
    global $ugid, $dbprefix, $scriptname, $connect, $clang;

    $ugid=sanitize_int($ugid);
	$surveyidquery = "SELECT a.uid, a.users_name FROM ".db_table_name('users')." AS a LEFT JOIN (SELECT uid AS id FROM ".db_table_name('user_in_groups')." WHERE ugid = {$ugid}) AS b ON a.uid = b.id WHERE id IS NULL ORDER BY a.users_name";

    $surveyidresult = db_execute_assoc($surveyidquery);  //Checked
    if (!$surveyidresult) {return "Database Error";}
    $surveyselecter = "";
    $surveynames = $surveyidresult->GetRows();
    if ($surveynames)
        {
        foreach($surveynames as $sv)
            {
			$surveyselecter .= "\t\t\t<option";
            $surveyselecter .=" value='{$sv['uid']}'>{$sv['users_name']}</option>\n";
            }
        }
    $surveyselecter = "\t\t\t<option value='-1' selected='selected'>".$clang->gT("Please Choose...")."</option>\n".$surveyselecter;
    return $surveyselecter;
}

function getsurveyuserlist()
{
    global $surveyid, $dbprefix, $scriptname, $connect, $clang, $usercontrolSameGroupPolicy;
    $surveyid=sanitize_int($surveyid);
	$surveyidquery = "SELECT a.uid, a.users_name FROM ".db_table_name('users')." AS a LEFT OUTER JOIN (SELECT uid AS id FROM ".db_table_name('surveys_rights')." WHERE sid = {$surveyid}) AS b ON a.uid = b.id WHERE id IS NULL ORDER BY a.users_name";

    $surveyidresult = db_execute_assoc($surveyidquery);  //Checked
    if (!$surveyidresult) {return "Database Error";}
    $surveyselecter = "";
    $surveynames = $surveyidresult->GetRows();

    if (isset($usercontrolSameGroupPolicy) &&
		$usercontrolSameGroupPolicy == true)
    {
	$authorizedUsersList = getuserlist('onlyuidarray');
    }

    if ($surveynames)
        {
        foreach($surveynames as $sv)
            {
		if (!isset($usercontrolSameGroupPolicy) ||
			$usercontrolSameGroupPolicy == false ||
			in_array($sv['uid'],$authorizedUsersList))
		{
			$surveyselecter .= "\t\t\t<option";
			$surveyselecter .=" value='{$sv['uid']}'>{$sv['users_name']}</option>\n";
		}
            }
        }
    if (!isset($svexist)) {$surveyselecter = "\t\t\t<option value='-1' selected='selected'>".$clang->gT("Please Choose...")."</option>\n".$surveyselecter;}
    else {$surveyselecter = "\t\t\t<option value='-1'>".$clang->gT("None")."</option>\n".$surveyselecter;}
    return $surveyselecter;
}

function getsurveyusergrouplist($outputformat='htmloptions')
{
    global $surveyid, $dbprefix, $scriptname, $connect, $clang, $usercontrolSameGroupPolicy;
    $surveyid=sanitize_int($surveyid);

	//$surveyidquery = "SELECT a.ugid, a.name, MAX(d.ugid) AS da FROM ".db_table_name('user_groups')." AS a LEFT JOIN (SELECT b.ugid FROM ".db_table_name('user_in_groups')." AS b LEFT JOIN (SELECT * FROM ".db_table_name('surveys_rights')." WHERE sid = {$surveyid}) AS c ON b.uid = c.uid WHERE c.uid IS NULL) AS d ON a.ugid = d.ugid GROUP BY a.ugid, a.name HAVING da IS NOT NULL";
	//n.b: the original query (above) uses 'da' in the HAVING clause. MS SQL Server doesn't like that, and forces you to redeclare the expression used in the select. Stupid, stupid, SQL Server.
	//     I'm hoping this will not bork MySQL. If it does, we'll need to drop a switch in here.
	$surveyidquery = "SELECT a.ugid, a.name, MAX(d.ugid) AS da FROM ".db_table_name('user_groups')." AS a LEFT JOIN (SELECT b.ugid FROM ".db_table_name('user_in_groups')." AS b LEFT JOIN (SELECT * FROM ".db_table_name('surveys_rights')." WHERE sid = {$surveyid}) AS c ON b.uid = c.uid WHERE c.uid IS NULL) AS d ON a.ugid = d.ugid GROUP BY a.ugid, a.name HAVING MAX(d.ugid) IS NOT NULL";
	$surveyidresult = db_execute_assoc($surveyidquery);  //Checked
    if (!$surveyidresult) {return "Database Error";}
    $surveyselecter = "";
    $surveynames = $surveyidresult->GetRows();

    if (isset($usercontrolSameGroupPolicy) &&
		$usercontrolSameGroupPolicy == true)
    {
	 $authorizedGroupsList=getusergrouplist('simplegidarray');
    }

    if ($surveynames)
        {
        foreach($surveynames as $sv)
            {
		if (!isset($usercontrolSameGroupPolicy) ||
			$usercontrolSameGroupPolicy == false ||
			in_array($sv['ugid'],$authorizedGroupsList))
		{
			$surveyselecter .= "\t\t\t<option";
			$surveyselecter .=" value='{$sv['ugid']}'>{$sv['name']}</option>\n";
			$simpleugidarray[] = $sv['ugid'];
		}
            }
        }
    if (!isset($svexist)) {$surveyselecter = "\t\t\t<option value='-1' selected='selected'>".$clang->gT("Please Choose...")."</option>\n".$surveyselecter;}
    else {$surveyselecter = "\t\t\t<option value='-1'>".$clang->gT("None")."</option>\n".$surveyselecter;}

    if ($outputformat == 'simpleugidarray')
    {
	return $simpleugidarray;
    }
    else
    {
    	return $surveyselecter;
    }
}

function getusergrouplist($outputformat='optionlist')
{
    global $dbprefix, $scriptname, $connect, $clang;

	//$squery = "SELECT ugid, name FROM ".db_table_name('user_groups') ." WHERE owner_id = {$_SESSION['loginID']} ORDER BY name";
	$squery = "SELECT a.ugid, a.name, a.owner_id, b.uid FROM ".db_table_name('user_groups') ." AS a LEFT JOIN ".db_table_name('user_in_groups') ." AS b ON a.ugid = b.ugid WHERE uid = {$_SESSION['loginID']} ORDER BY name";

    $sresult = db_execute_assoc($squery); //Checked
    if (!$sresult) {return "Database Error";}
    $selecter = "";
    $groupnames = $sresult->GetRows();
    $simplegidarray=array();
    if ($groupnames)
        {
        foreach($groupnames as $gn)
            {
		$selecter .= "\t\t\t<option ";
		if($_SESSION['loginID'] == $gn['owner_id']) {$selecter .= " style=\"font-weight: bold;\"";}
		if (isset($_GET['ugid']) && $gn['ugid'] == $_GET['ugid']) {$selecter .= " selected='selected'"; $svexist = 1;}
		$selecter .=" value='$scriptname?action=editusergroups&amp;ugid={$gn['ugid']}'>{$gn['name']}</option>\n";
		$simplegidarray[] = $gn['ugid'];
            }
        }
    if (!isset($svexist)) {$selecter = "\t\t\t<option value='-1' selected='selected'>".$clang->gT("Please Choose...")."</option>\n".$selecter;}
    //else {$selecter = "\t\t\t<option value='-1'>".$clang->gT("None")."</option>\n".$selecter;}

    if ($outputformat == 'simplegidarray')
    {
    	return $simplegidarray;
    }
    else
    {
    	return $selecter;
    }
}


function languageDropdown($surveyid,$selected)
{
	$slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
	$baselang = GetBaseLanguageFromSurveyID($surveyid);
	array_unshift($slangs,$baselang);
	$html = "<select class='listboxquestions' name='langselect' onchange=\"window.open(this.options[this.selectedIndex].value, '_top')\">\n";
	foreach ($slangs as $lang)
	{
		if ($lang == $selected) $html .= "\t<option value='{$_SERVER['PHP_SELF']}?action=dataentry&sid={$surveyid}&language={$lang}' selected='selected'>".getLanguageNameFromCode($lang,false)."</option>\n";
		if ($lang != $selected) $html .= "\t<option value='{$_SERVER['PHP_SELF']}?action=dataentry&sid={$surveyid}&language={$lang}'>".getLanguageNameFromCode($lang,false)."</option>\n";
	}
	$html .= "</select>";
	return $html;
}

function languageDropdownClean($surveyid,$selected)
{
	$slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
	$baselang = GetBaseLanguageFromSurveyID($surveyid);
	array_unshift($slangs,$baselang);
	$html = "<select class='listboxquestions' name='language'>\n";
	foreach ($slangs as $lang)
	{
		if ($lang == $selected) $html .= "\t<option value='$lang' selected='selected'>".getLanguageNameFromCode($lang,false)."</option>\n";
		if ($lang != $selected) $html .= "\t<option value='$lang'>".getLanguageNameFromCode($lang,false)."</option>\n";
	}
	$html .= "</select>";
	return $html;
}

function BuildCSVFromQuery($Query)
{
	global $dbprefix, $connect;
	$QueryResult = db_execute_assoc($Query) or safe_die ("ERROR: $QueryResult<br />".$connect->ErrorMsg()); //safe
	preg_match('/FROM (\w+)( |,)/i', $Query, $MatchResults);
	$TableName = $MatchResults[1];;
	if ($dbprefix)
	{
		$TableName = substr($TableName, strlen($dbprefix), strlen($TableName));
	}
	$Output = "\n#\n# " . strtoupper($TableName) . " TABLE\n#\n";
	$HeaderDone = false;	$ColumnNames = "";
	while ($Row = $QueryResult->FetchRow())
	{

       if (!$HeaderDone)
       {
    		foreach ($Row as $Key=>$Value)
    		{
    			$ColumnNames .= CSVEscape($Key).","; //Add all the column names together
    		}
			$ColumnNames = substr($ColumnNames, 0, -1); //strip off last comma space
     		$Output .= "$ColumnNames\n";
    		$HeaderDone=true;
       }
		$ColumnValues = "";
		foreach ($Row as $Key=>$Value)
		{
			$Value=str_replace("\r\n", "\n", $Value);
			$Value=str_replace("\r", "\n", $Value);
			$ColumnValues .= CSVEscape($Value) . ",";
		}
		$ColumnValues = substr($ColumnValues, 0, -1); //strip off last comma space
		$Output .= str_replace("\n","\\n","$ColumnValues")."\n";
	}
	return $Output;
}

function CSVEscape($str) 
{
   return '"' . str_replace('"','""', $str) . '"';
}

function convertCSVRowToArray($string, $seperator, $quotechar) 
{
	$fields=preg_split('/,(?=([^"]*"[^"]*")*(?![^"]*"))/',trim($string));
	$fields=array_map('CSVUnquote',$fields);
	return $fields;
}


/**
* This function removes surrounding and masking quotes from the CSV field        
* 
* @param mixed $field
* @return mixed
*/
function CSVUnquote($field)
{
	//print $field.":";
	$field = preg_replace ("/^\040*\"/", "", $field);
	$field = preg_replace ("/\"\040*$/", "", $field);
    $field=str_replace('""','"',$field);
    //print $field."\n";
    return $field;
}

/**
* CleanLanguagesFromSurvey() removes any languages from survey tables that are not in the passed list
* @param string $sid - the currently selected survey
* @param string $availlangs - space seperated list of additional languages in survey
* @return bool - always returns true
*/
function CleanLanguagesFromSurvey($sid, $availlangs)
{
	global $connect;
	$sid=sanitize_int($sid);
	$baselang = GetBaseLanguageFromSurveyID($sid);
	
	if (!empty($availlangs) && $availlangs != " ")
	{
		$availlangs=sanitize_languagecodeS($availlangs);
        $langs = explode(" ",$availlangs);
		if($langs[count($langs)-1] == "") array_pop($langs);
	}
	
	$sqllang = "language <> '".$baselang."' ";;
	
	if (!empty($availlangs) && $availlangs != " ")
	{
		foreach ($langs as $lang)
		{
			$sqllang .= "and language <> '".$lang."' ";
		}
	}
	
	// Remove From Answers Table
	$query = "SELECT qid FROM ".db_table_name('questions')." WHERE sid='{$sid}' and ($sqllang)";
	$qidresult = db_execute_assoc($query) or safe_die($connect->ErrorMsg());    //Checked
	while ($qrow =  $qidresult->FetchRow())
	{
		$myqid = $qrow['qid'];
		$query = "DELETE FROM ".db_table_name('answers')." WHERE qid='$myqid' and ($sqllang)";
		$connect->Execute($query) or safe_die($connect->ErrorMsg());    //Checked
	}
	
	// Remove From Questions Table
	$query = "DELETE FROM ".db_table_name('questions')." WHERE sid='{$sid}' and ($sqllang)";
	$connect->Execute($query) or safe_die($connect->ErrorMsg());   //Checked
	
	// Remove From Groups Table
	$query = "DELETE FROM ".db_table_name('groups')." WHERE sid='{$sid}' and ($sqllang)";
	$connect->Execute($query) or safe_die($connect->ErrorMsg());   //Checked
	
	return true;
}

/**
* FixLanguageConsistency() fixes missing groups,questions,answers & assessments for languages on a survey
* @param string $sid - the currently selected survey
* @param string $availlangs - space seperated list of additional languages in survey
* @return bool - always returns true
*/
function FixLanguageConsistency($sid, $availlangs)
{
	global $connect, $databasetype;
	
	if (!empty($availlangs) && $availlangs != " ")
	{
		$availlangs=sanitize_languagecodeS($availlangs);
        $langs = explode(" ",$availlangs);
		if($langs[count($langs)-1] == "") array_pop($langs);
	} else {
		return true;
	}
	
	$baselang = GetBaseLanguageFromSurveyID($sid);
	$sid=sanitize_int($sid);
	$query = "SELECT * FROM ".db_table_name('groups')." WHERE sid='{$sid}' AND language='{$baselang}'  ORDER BY group_order";
	$result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());  //Checked
	if ($result->RecordCount() > 0)
	{
		while($group = $result->FetchRow())
		{
			foreach ($langs as $lang)
			{
				$query = "SELECT gid FROM ".db_table_name('groups')." WHERE sid='{$sid}' AND gid='{$group['gid']}' AND language='{$lang}'";
				$gresult = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked
				if ($gresult->RecordCount() < 1)
				{
                    if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n') {$connect->Execute('SET IDENTITY_INSERT '.db_table_name('groups')." ON");}   //Checked
					$query = "INSERT INTO ".db_table_name('groups')." (gid,sid,group_name,group_order,description,language) VALUES('{$group['gid']}','{$group['sid']}',".db_quoteall($group['group_name']).",'{$group['group_order']}',".db_quoteall($group['description']).",'{$lang}')";  
					$connect->Execute($query) or safe_die($connect->ErrorMsg());  //Checked
                     if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n') {$connect->Execute('SET IDENTITY_INSERT '.db_table_name('groups')." OFF");}   //Checked
				}
			}
			reset($langs);
		}
	}
	
	$quests = array();
	$query = "SELECT * FROM ".db_table_name('questions')." WHERE sid='{$sid}' AND language='{$baselang}' ORDER BY question_order";
	$result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());  //Checked
	if ($result->RecordCount() > 0)
	{
		while($question = $result->FetchRow())
		{
			array_push($quests,$question['qid']);
			foreach ($langs as $lang)
			{
				$query = "SELECT qid FROM ".db_table_name('questions')." WHERE sid='{$sid}' AND qid='{$question['qid']}' AND language='{$lang}'";
				$gresult = db_execute_assoc($query) or safe_die($connect->ErrorMsg());   //Checked
				if ($gresult->RecordCount() < 1)
				{
                    if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n') {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('questions')." ON");}    //Checked
					$query = "INSERT INTO ".db_table_name('questions')." (qid,sid,gid,type,title,question,preg,help,other,mandatory,lid,question_order,language) VALUES('{$question['qid']}','{$question['sid']}','{$question['gid']}','{$question['type']}',".db_quoteall($question['title']).",".db_quoteall($question['question']).",".db_quoteall($question['preg']).",".db_quoteall($question['help']).",'{$question['other']}','{$question['mandatory']}','{$question['lid']}','{$question['question_order']}','{$lang}')";
					$connect->Execute($query) or safe_die($query."<br />".$connect->ErrorMsg());   //Checked
                    if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n') {$connect->Execute('SET IDENTITY_INSERT '.db_table_name('questions')." OFF");}      //Checked
				}
			}
			reset($langs);
		}

		$sqlans = "";
		foreach ($quests as $quest)
		{
			$sqlans .= " OR qid = '".$quest."' ";
		}

		$query = "SELECT * FROM ".db_table_name('answers')." WHERE language='{$baselang}' and (".trim($sqlans,' OR').") ORDER BY qid, code";
		$result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked
		if ($result->RecordCount() > 0)
		{
			while($answer = $result->FetchRow())
			{
				foreach ($langs as $lang)
				{
					$query = "SELECT qid FROM ".db_table_name('answers')." WHERE code='{$answer['code']}' AND qid='{$answer['qid']}' AND language='{$lang}'";
					$gresult = db_execute_assoc($query) or safe_die($connect->ErrorMsg());  //Checked
					if ($gresult->RecordCount() < 1)
					{
                        if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n') {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('answers')." ON");}    //Checked
						$query = "INSERT INTO ".db_table_name('answers')." (qid,code,answer,default_value,sortorder,language,assessment_value) VALUES('{$answer['qid']}',".db_quoteall($answer['code']).",".db_quoteall($answer['answer']).",".db_quoteall($answer['default_value']).",'{$answer['sortorder']}','{$lang}',{$answer['assessment_value']})";
						$connect->Execute($query) or safe_die($connect->ErrorMsg()); //Checked
                        if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n') {$connect->Execute('SET IDENTITY_INSERT '.db_table_name('answers')." OFF");}   //Checked
					}
				}
				reset($langs);
			}
		}
	}
    
    
    $query = "SELECT * FROM ".db_table_name('assessments')." WHERE sid='{$sid}' AND language='{$baselang}'";
    $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());  //Checked
    if ($result->RecordCount() > 0)
    {
        while($assessment = $result->FetchRow())
        {
            foreach ($langs as $lang)
            {
                $query = "SELECT id FROM ".db_table_name('assessments')." WHERE sid='{$sid}' AND id='{$assessment['id']}' AND language='{$lang}'";
                $gresult = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked
                if ($gresult->RecordCount() < 1)
                {
                    if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n') {$connect->Execute('SET IDENTITY_INSERT '.db_table_name('assessments')." ON");}   //Checked
                    $query = "INSERT INTO ".db_table_name('assessments')." (id,sid,scope,gid,name,minimum,maximum,message,language) "
                            ."VALUES('{$assessment['id']}','{$assessment['sid']}',".db_quoteall($assessment['scope']).",".db_quoteall($assessment['gid']).",".db_quoteall($assessment['name']).",".db_quoteall($assessment['minimum']).",".db_quoteall($assessment['maximum']).",".db_quoteall($assessment['message']).",'{$lang}')";  
                    $connect->Execute($query) or safe_die($connect->ErrorMsg());  //Checked
                    if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n') {$connect->Execute('SET IDENTITY_INSERT '.db_table_name('assessments')." OFF");}   //Checked
                }
            }
            reset($langs);
        }
    }

    
    
	return true;
}

/**
* GetGroupDepsForConditions() get Dependencies between groups caused by conditions 
* @param string $sid - the currently selected survey
* @param string $depgid - (optionnal) get only the dependencies applying to the group with gid depgid
* @param string $targgid - (optionnal) get only the dependencies for groups dependents on group targgid
* @param string $index-by - (optionnal) "by-depgid" for result indexed with $res[$depgid][$targgid]
* 					"by-targgid" for result indexed with $res[$targgid][$depgid]
* @return array - returns an array describing the conditions or NULL if no dependecy is found
*
* Example outupt assumin $index-by="by-depgid":
*Array 
*(
*    [125] => Array				// Group Id 125 is dependent on
*        (
*            [123] => Array			// Group Id 123
*                (
*                    [depgpname] => G3		// GID-125 has name G3
*                    [targetgpname] => G1	// GID-123 has name G1
*                    [conditions] => Array
*                        (
*                            [189] => Array	// Because Question Id 189
*                                (
*                                    [0] => 9	// Have condition 9 set
*                                    [1] => 10	// and condition 10 set
*                                    [2] => 14  // and condition 14 set
*                                )
*
*                        )
*
*                )
*
*            [124] => Array			// GID 125 is also dependent on GID 124
*                (
*                    [depgpname] => G3
*                    [targetgpname] => G2
*                    [conditions] => Array
*                        (
*                            [189] => Array	// Because Question Id 189 have conditions set
*                                (
*                                    [0] => 11
*                                )
*
*                            [215] => Array	// And because Question Id 215 have conditions set
*                                (
*                                    [0] => 12
*                                )
*
*                        )
*
*                )
*
*        )
*
*)
*
* Usage example:
*	* Get all group dependencies for SID $sid indexed by depgid:
*		$result=GetGroupDepsForConditions($sid);
*	* Get all group dependencies for GID $gid in survey $sid indexed by depgid:
*		$result=GetGroupDepsForConditions($sid,$gid);
*	* Get all group dependents on group $gid in survey $sid indexed by targgid:
*		$result=GetGroupDepsForConditions($sid,"all",$gid,"by-targgid");
*/
function GetGroupDepsForConditions($sid,$depgid="all",$targgid="all",$indexby="by-depgid")
{
	global $connect, $clang;
    $sid=sanitize_int($sid);
	$condarray = Array();

	$sqldepgid="";
	$sqltarggid="";
	if ($depgid != "all") { $depgid = sanitize_int($depgid); $sqldepgid="AND tq.gid=$depgid";}
	if ($targgid != "all") {$targgid = sanitize_int($targgid); $sqltarggid="AND tq2.gid=$targgid";}

	$baselang = GetBaseLanguageFromSurveyID($sid);
	$condquery = "SELECT tg.gid as depgid, tg.group_name as depgpname, "
		. "tg2.gid as targgid, tg2.group_name as targgpname, tq.qid as depqid, tc.cid FROM "
		. db_table_name('conditions')." AS tc, "
		. db_table_name('questions')." AS tq, "
		. db_table_name('questions')." AS tq2, "
		. db_table_name('groups')." AS tg ,"
		. db_table_name('groups')." AS tg2 "
		. "WHERE tq.language='{$baselang}' AND tq2.language='{$baselang}' AND tg.language='{$baselang}' AND tg2.language='{$baselang}' AND tc.qid = tq.qid AND tq.sid=$sid "
		. "AND tq.gid = tg.gid AND tg2.gid = tq2.gid "
		. "AND tq2.qid=tc.cqid AND tq.gid != tg2.gid $sqldepgid $sqltarggid";
	$condresult=db_execute_assoc($condquery) or safe_die($connect->ErrorMsg());   //Checked
	
	if ($condresult->RecordCount() > 0) {
		while ($condrow = $condresult->FetchRow())
		{

			switch ($indexby)
			{
				case "by-depgid":
				$depgid=$condrow['depgid'];
				$targetgid=$condrow['targgid'];
				$depqid=$condrow['depqid'];
				$cid=$condrow['cid'];
				$condarray[$depgid][$targetgid]['depgpname'] = $condrow['depgpname'];
				$condarray[$depgid][$targetgid]['targetgpname'] = $condrow['targgpname'];
				$condarray[$depgid][$targetgid]['conditions'][$depqid][]=$cid;
				break;

				case "by-targgid":
				$depgid=$condrow['depgid'];
				$targetgid=$condrow['targgid'];
				$depqid=$condrow['depqid'];
				$cid=$condrow['cid'];
				$condarray[$targetgid][$depgid]['depgpname'] = $condrow['depgpname'];
				$condarray[$targetgid][$depgid]['targetgpname'] = $condrow['targgpname'];
				$condarray[$targetgid][$depgid]['conditions'][$depqid][] = $cid;
				break;
			}
		}
		return $condarray;
	}
	return null;
}

/**
* GetQuestDepsForConditions() get Dependencies between groups caused by conditions 
* @param string $sid - the currently selected survey
* @param string $gid - (optionnal) only search dependecies inside the Group Id $gid
* @param string $depqid - (optionnal) get only the dependencies applying to the question with qid depqid
* @param string $targqid - (optionnal) get only the dependencies for questions dependents on question Id targqid
* @param string $index-by - (optionnal) "by-depqid" for result indexed with $res[$depqid][$targqid]
* 					"by-targqid" for result indexed with $res[$targqid][$depqid]
* @return array - returns an array describing the conditions or NULL if no dependecy is found
*
* Example outupt assumin $index-by="by-depqid":
*Array
*(
*    [184] => Array		// Question Id 184
*        (
*            [183] => Array	// Depends on Question Id 183
*                (
*                    [0] => 5	// Because of condition Id 5
*                )
*
*        )
*
*)
*
* Usage example:
*	* Get all questions dependencies for Survey $sid and group $gid indexed by depqid:
*		$result=GetQuestDepsForConditions($sid,$gid);
*	* Get all questions dependencies for question $qid in survey/group $sid/$gid indexed by depqid:
*		$result=GetGroupDepsForConditions($sid,$gid,$qid);
*	* Get all questions dependents on question $qid in survey/group $sid/$gid indexed by targqid:
*		$result=GetGroupDepsForConditions($sid,$gid,"all",$qid,"by-targgid");
*/
function GetQuestDepsForConditions($sid,$gid="all",$depqid="all",$targqid="all",$indexby="by-depqid", $searchscope="samegroup")
{
	global $connect, $clang;
	$condarray = Array();
	
	$baselang = GetBaseLanguageFromSurveyID($sid);
	$sqlgid="";
	$sqldepqid="";
	$sqltargqid="";
	$sqlsearchscope="";
	if ($gid != "all") {$gid = sanitize_int($gid); $sqlgid="AND tq.gid=$gid";}
	if ($depqid != "all") {$depqid = sanitize_int($depqid); $sqldepqid="AND tq.qid=$depqid";}
	if ($targqid != "all") {$targqid = sanitize_int($targqid); $sqltargqid="AND tq2.qid=$targqid";}
	if ($searchscope == "samegroup") {$sqlsearchscope="AND tq2.gid=tq.gid";}

	$condquery = "SELECT tq.qid as depqid, tq2.qid as targqid, tc.cid FROM "
		. db_table_name('conditions')." AS tc, "
		. db_table_name('questions')." AS tq, "
		. db_table_name('questions')." AS tq2 "
		. "WHERE tq.language='{$baselang}' AND tq2.language='{$baselang}' AND tc.qid = tq.qid AND tq.sid=$sid "
		. "AND  tq2.qid=tc.cqid $sqlsearchscope $sqlgid $sqldepqid $sqltargqid";

		$condresult=db_execute_assoc($condquery) or safe_die($connect->ErrorMsg());    //Checked

	if ($condresult->RecordCount() > 0) {
		while ($condrow = $condresult->FetchRow())
		{
			$depqid=$condrow['depqid'];
			$targetqid=$condrow['targqid'];
			$condid=$condrow['cid'];
			switch ($indexby)
			{
				case "by-depqid":
				$condarray[$depqid][$targetqid][] = $condid;
				break;

				case "by-targqid":
				$condarray[$targetqid][$depqid][] = $condid;
				break;
			}
		}
		return $condarray;
	}
	return null;
}


/**
* checkMovequestionConstraintsForConditions() 
* @param string $sid - the currently selected survey
* @param string $qid - qid of the question you want to check possible moves 
* @param string $newgid - (optionnal) get only constraints when trying to move to this particular GroupId
*                                     otherwise, get all moves constraints for this question
*
* @return array - returns an array describing the conditions
*                 Array
*                 (
*                   ['notAbove'] = null | Array
*						(
*						  Array ( gid1, group_order1, qid1, cid1 )
*						)
*                   ['notBelow'] = null | Array
*						(
*						  Array ( gid2, group_order2, qid2, cid2 )
*						)
*                 )
*
* This should be read as:
*    - this question can't be move above group gid1 in position group_order1 because of the condition cid1 on question qid1
*    - this question can't be move below group gid2 in position group_order2 because of the condition cid2 on question qid2
*
*/
function checkMovequestionConstraintsForConditions($sid,$qid,$newgid="all")
{
	global $connect;
	$resarray=Array();
	$resarray['notAbove']=null; // defaults to no constraint
	$resarray['notBelow']=null; // defaults to no constraint
    $sid=sanitize_int($sid);
    $qid=sanitize_int($qid);

	if ($newgid != "all")
	{
        $newgid=sanitize_int($newgid);
		$newgorder=getGroupOrder($sid,$newgid);
	}
	else
	{
		$neworder=""; // Not used in this case
	}

	$baselang = GetBaseLanguageFromSurveyID($sid);
	
	// First look for 'my dependencies': questions on which I have set conditions
	$condquery = "SELECT tq.qid as depqid, tq.gid as depgid, tg.group_order as depgorder, "
		. "tq2.qid as targqid, tq2.gid as targgid, tg2.group_order as targgorder, "
		. "tc.cid FROM "
		. db_table_name('conditions')." AS tc, "
		. db_table_name('questions')." AS tq, "
		. db_table_name('questions')." AS tq2, "
		. db_table_name('groups')." AS tg, "
		. db_table_name('groups')." AS tg2 "
		. "WHERE tq.language='{$baselang}' AND tq2.language='{$baselang}' AND tc.qid = tq.qid AND tq.sid=$sid "
		. "AND  tq2.qid=tc.cqid AND tg.gid=tq.gid AND tg2.gid=tq2.gid AND tq.qid=$qid ORDER BY tg2.group_order DESC";
	
	$condresult=db_execute_assoc($condquery) or safe_die($connect->ErrorMsg());    //Checked

	if ($condresult->RecordCount() > 0) {

		while ($condrow = $condresult->FetchRow() )
		{
			// This Question can go up to the minimum GID on the 1st row
			$depqid=$condrow['depqid'];
			$depgid=$condrow['depgid'];
			$depgorder=$condrow['depgorder'];
			$targetqid=$condrow['targqid'];
			$targetgid=$condrow['targgid'];
			$targetgorder=$condrow['targgorder'];
			$condid=$condrow['cid'];
			//echo "This question can't go above to GID=$targetgid/order=$targetgorder because of CID=$condid";
			if ($newgid != "all")
			{ // Get only constraints when trying to move to this group
				if ($newgorder < $targetgorder)
				{
					$resarray['notAbove'][]=Array($targetgid,$targetgorder,$depqid,$condid);
				}
			}
			else
			{ // get all moves constraints
				$resarray['notAbove'][]=Array($targetgid,$targetgorder,$depqid,$condid);	
			}
		}
	}

	// Secondly look for 'questions dependent on me': questions that have conditions on my answers
	$condquery = "SELECT tq.qid as depqid, tq.gid as depgid, tg.group_order as depgorder, "
		. "tq2.qid as targqid, tq2.gid as targgid, tg2.group_order as targgorder, "
		. "tc.cid FROM "
		. db_table_name('conditions')." AS tc, "
		. db_table_name('questions')." AS tq, "
		. db_table_name('questions')." AS tq2, "
		. db_table_name('groups')." AS tg, "
		. db_table_name('groups')." AS tg2 "
		. "WHERE tq.language='{$baselang}' AND tq2.language='{$baselang}' AND tc.qid = tq.qid AND tq.sid=$sid "
		. "AND  tq2.qid=tc.cqid AND tg.gid=tq.gid AND tg2.gid=tq2.gid AND tq2.qid=$qid ORDER BY tg.group_order";
	
	$condresult=db_execute_assoc($condquery) or safe_die($connect->ErrorMsg());        //Checked    

	if ($condresult->RecordCount() > 0) {

		while ($condrow = $condresult->FetchRow())
		{
			// This Question can go down to the maximum GID on the 1st row
			$depqid=$condrow['depqid'];
			$depgid=$condrow['depgid'];
			$depgorder=$condrow['depgorder'];
			$targetqid=$condrow['targqid'];
			$targetgid=$condrow['targgid'];
			$targetgorder=$condrow['targgorder'];
			$condid=$condrow['cid'];
			//echo "This question can't go below to GID=$depgid/order=$depgorder because of CID=$condid";
			if ($newgid != "all")
			{ // Get only constraints when trying to move to this group
				if ($newgorder > $depgorder)
				{
					$resarray['notBelow'][]=Array($depgid,$depgorder,$depqid,$condid);
				}
			}
			else
			{ // get all moves constraints
				$resarray['notBelow'][]=Array($depgid,$depgorder,$depqid,$condid);
			}
		}
	}
	return $resarray;
}

function incompleteAnsFilterstate()
{
	global $filterout_incomplete_answers;
	$letsfilter='';
	$letsfilter = returnglobal('filterinc'); //read get/post filterinc

	// first let's initialize the incompleteanswers session variable
	if ($letsfilter != '')
	{ // use the read value if not empty
		$_SESSION['incompleteanswers']=$letsfilter;
	}
	elseif (!isset($_SESSION['incompleteanswers']))
	{ // sets default variable value from config file
		$_SESSION['incompleteanswers'] = $filterout_incomplete_answers;
	}

	if  ($_SESSION['incompleteanswers']=='filter') {
		return "filter"; //COMPLETE ANSWERS ONLY
	}
	elseif ($_SESSION['incompleteanswers']=='show') {
		return false; //ALL ANSWERS
	}
	elseif ($_SESSION['incompleteanswers']=='incomplete') {
	    return "inc"; //INCOMPLETE ANSWERS ONLY
	}
	else
	{ // last resort is to prevent filtering
		return false;
	}
}

/**
* captcha_enabled($screen, $usecaptchamode) 
* @param string $screen - the screen name for which to test captcha activation
*
* @return boolean - returns true if captcha must be enabled
**/
function captcha_enabled($screen, $captchamode='')
{
	switch($screen)
	{
	case 'registrationscreen':
		if ($captchamode == 'A' ||
			$captchamode == 'B' ||
			$captchamode == 'D' ||
			$captchamode == 'R')
		{
			return true;
		}
		else
		{
			return false;
		}
		break;
	case 'surveyaccessscreen':
		if ($captchamode == 'A' ||
			$captchamode == 'B' ||
			$captchamode == 'C' ||
			$captchamode == 'X')
		{
			return true;
		}
		else
		{
			return false;
		}
		break;
	case 'saveandloadscreen':
		if ($captchamode == 'A' ||
			$captchamode == 'C' ||
			$captchamode == 'D' ||
			$captchamode == 'S')
		{
			return true;
		}
		else
		{
			return false;
		}
		return true;
	break;
	default:
		return true;
		break;
	}
}


/**
* used for import[survey|questions|groups]
* 
* @param mixed $string
* @return mixed
*/
function convertCsvreturn2return($string)
{
        return str_replace('\n', "\n", $string);
}



/**
*  Checks that each object from an array of CSV data [question-rows,answer-rows,labelsets-row] supports at least a given language
* 
* @param mixed $csvarray array with a line of csv data per row      
* @param mixed $idkeysarray  array of integers giving the csv-row numbers of the object keys  
* @param mixed $langfieldnum  integer giving the csv-row number of the language(s) filed
*        ==> the language field  can be a single language code or a 
*            space separated language code list
* @param mixed $langcode  the language code to be tested    
* @param mixed $hasheader  if we should strip off the first line (if it contains headers)   
*/
function  bDoesImportarraySupportsLanguage($csvarray,$idkeysarray,$langfieldnum,$langcode, $hasheader = false)
{
	// An array with one row per object id and langsupport status as value
	$objlangsupportarray=Array();
	if ($hasheader === true)
	{ // stripping first row to skip headers if any
		array_shift($csvarray);
	}

	foreach ($csvarray as $csvrow)
	{
		$rowcontents = convertCSVRowToArray($csvrow,',','"');		
		$rowid = "";
		foreach ($idkeysarray as $idfieldnum)
		{
			$rowid .= $rowcontents[$idfieldnum]."-";
		}
		$rowlangarray = split (" ", $rowcontents[$langfieldnum]);
		if (!isset($objlangsupportarray[$rowid]))
		{
			if (array_search($langcode,$rowlangarray)!== false)
			{
				$objlangsupportarray[$rowid] = "true";
			}
			else
			{
				$objlangsupportarray[$rowid] = "false";
			}
		}
		else
		{
			if ($objlangsupportarray[$rowid] == "false" && 
				array_search($langcode,$rowlangarray) !== false)
			{
				$objlangsupportarray[$rowid] = "true";
			}
		}
	} // end foreach rown

	// If any of the object doesn't support the given language, return false
	if (array_search("false",$objlangsupportarray) === false)
	{
		return true;
	}
	else
	{
		return false;
	}
}

// returns the answerText from session vraiable  corresponding to a question code
//
function retrieve_Answer($code)
{
	//This function checks to see if there is an answer saved in the survey session
	//data that matches the $code. If it does, it returns that data.
	//It is used when building a questions text to allow incorporating the answer
	//to an earlier question into the text of a later question.
	//IE: Q1: What is your name? [Jason]
	//    Q2: Hi [Jason] how are you ?
	//This function is called from the retriveAnswers function.
	global $dbprefix, $connect, $clang;
	//Find question details
	if (isset($_SESSION[$code]))
	{
		$questiondetails=getsidgidqidaidtype($code);
		//the getsidgidqidaidtype function is in common.php and returns
		//a SurveyID, GroupID, QuestionID and an Answer code
		//extracted from a "fieldname" - ie: 1X2X3a
		// also returns question type

		if ($questiondetails['type'] == "M" ||
			$questiondetails['type'] == "P")
		{
			$query="SELECT * FROM {$dbprefix}answers WHERE qid='".$questiondetails['qid']."' AND language='".$_SESSION['s_lang']."'";
			$result=db_execute_assoc($query) or safe_die("Error getting answer<br />$query<br />".$connect->ErrorMsg());  //Checked
			while($row=$result->FetchRow())
			{
				if (isset($_SESSION[$code.$row['code']]) && $_SESSION[$code.$row['code']] == "Y")
				{
					$returns[] = $row['answer'];
				}
			}
			if (isset($_SESSION[$code."other"]) && $_SESSION[$code."other"])
			{
				$returns[]=$_SESSION[$code."other"];
			}
			if (isset($returns))
			{
				$return=implode(", ", $returns);
				if (strpos($return, ","))
				{
					$return=substr_replace($return, " &", strrpos($return, ","), 1);
				}
			}
			else
			{
				$return=$clang->gT("No answer");
			}
		}
		elseif (!$_SESSION[$code])
		{
			$return=$clang->gT("No answer");
		}
		else
		{
			$return=getextendedanswer($code, $_SESSION[$code], 'INSERTANS');
		}
	}
	else
	{
		$return=$clang->gT("Error") . "($code)";
	}
	return html_escape($return);
}

/**
* Check if token table odes exist
* 
* @param mixed $sid  The survey id to check
* @return boolean true if thesurvey has a token table defined       
*/
function tokenTableExists($surveyid)
{
	global $connect;
	$tablelist = $connect->MetaTables() or safe_die ("Error getting tokens<br />".$connect->ErrorMsg());
	foreach ($tablelist as $tbl)
	{
		if (db_quote_id($tbl) == db_table_name('tokens_'.$surveyid)) 
		{
			return true;
		}
	}
	return false;
}

// Returns false if the survey is anonymous, 
// and a token table exists: in this case the completed field of a token
// will contain 'Y' instead of the submitted date to ensure privacy
// Returns true otherwise
function bIsTokenCompletedDatestamped($thesurvey)
{
	if ($thesurvey['private'] == 'Y' &&  tokenTableExists($thesurvey['sid']))
	{
		return false;
	}
	else
	{
		return true;
	}
}

/**
* example usage
* $date = "2006-12-31 21:00";
* $shift "+6 hours"; // could be days, weeks... see function strtotime() for usage
* 
* echo sql_date_shift($date, "Y-m-d H:i:s", $shift);
* 
* will output: 2007-01-01 03:00:00
* 
* @param mixed $date
* @param mixed $dformat
* @param mixed $shift
* @return string
*/
function date_shift($date, $dformat, $shift)
{
return date($dformat, strtotime($shift, strtotime($date)));
}


// getBounceEmail: returns email used to receive error notifications
function getBounceEmail($surveyid)
{
    $surveyInfo=getSurveyInfo($surveyid);
	
	if ($surveyInfo['bounce_email'] == '')
	{
		return null; // will be converted to from in MailText
	}
	else
	{
		return $surveyInfo['bounce_email'];
	}	
}

// getEmailFormat: returns email format for the survey
// returns 'text' or 'html'
function getEmailFormat($surveyid)
{

	$surveyInfo=getSurveyInfo($surveyid);
	if ($surveyInfo['htmlemail'] == 'Y')
	{
		return 'html';
	}
	else
	{
		return 'text';
	}	

}

// Check if user has manage rights for a template
function hasTemplateManageRights($userid, $templatefolder) {
      global $connect;
      global $dbprefix;
      $userid=sanitize_int($userid);
      $templatefolder=sanitize_paranoid_string($templatefolder);
      $query = "SELECT ".db_quote_id('use')." FROM {$dbprefix}templates_rights WHERE uid=".$userid." AND folder LIKE '".$templatefolder."'";

      $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());  //Safe

      if ($result->RecordCount() == 0)	return false;

      $row = $result->FetchRow();

      return $row["use"];
}

// This function creates an incrementing answer code based on the previous source-code
function getNextCode($sourcecode)
{
   $i=1; 
   $found=true;
   $foundnumber=-1;
   while ($i<=strlen($sourcecode) && $found)   
   {
     $found=is_numeric(substr($sourcecode,-$i));
     if ($found) 
        {
        $foundnumber=substr($sourcecode,-$i);
        $i++;
        }
   }
   if ($foundnumber==-1) 
    {
        return($sourcecode);
    }
    else 
    {
       $foundnumber++; 
       $result=substr($sourcecode,0,strlen($sourcecode)-$i+1).$foundnumber;
       return($result);
    }
    
}

// translink
function translink($type, $oldid,$newid,$text)
{
	if (!isset($_POST['translinksfields']))
	{
		return $text;
	}

		if ($type == 'survey')
		{
			$pattern = "upload/surveys/$oldid/";
			$replace = "upload/surveys/$newid/";
			return ereg_replace($pattern, $replace, $text);
		}
		elseif ($type == 'label')
		{
			$pattern = "upload/labels/$oldid/";
			$replace = "upload/labels/$newid/";
			return ereg_replace($pattern, $replace, $text);
		}
		else
		{
			return $text;
		}
}

function transInsertAns($newsid,$oldsid,$fieldnames)
{ 
	global $connect, $dbprefix;

	if (!isset($_POST['translinksfields']))
	{
		return;
	}

	$newsid=sanitize_int($newsid);
	$oldsid=sanitize_int($oldsid);

	# translate 'description' INSERTANS tags in groups 
	$sql = "SELECT gid, language, description from {$dbprefix}groups WHERE sid=".$newsid." AND description LIKE '%{INSERTANS:".$oldsid."X%' ";
	$res = db_execute_assoc($sql) or safe_die("Can't read groups table in transInsertAns ".$connect->ErrorMsg());     // Checked

	while ($qentry = $res->FetchRow())
	{
		$description = $qentry['description'];
		$gid = $qentry['gid'];
		$language = $qentry['language'];

		foreach ($fieldnames as $fnrow)
		{
			$pattern = "{INSERTANS:".$fnrow['oldfieldname']."}";
			$replacement = "{INSERTANS:".$fnrow['newfieldname']."}";
			$description=ereg_replace($pattern, $replacement, $description);
		}

		if (strcmp($description,$qentry['description']) !=0 )
		{
			// Update Field
			$sqlupdate = "UPDATE {$dbprefix}groups SET description='".auto_escape($description)."' WHERE gid=$gid AND language='$language'";
			$updateres=$connect->Execute($sqlupdate) or safe_die ("Couldn't update INSERTANS in groups<br />$sqlupdate<br />".$connect->ErrorMsg());    //Checked
		} // Enf if modified
	} // end while qentry

	# translate 'question' and 'help' INSERTANS tags in questions 
	$sql = "SELECT qid, language, question, help from {$dbprefix}questions WHERE sid=".$newsid." AND question LIKE '%{INSERTANS:".$oldsid."X%' OR help LIKE '%{INSERTANS:".$oldsid."X%'";
	$res = db_execute_assoc($sql) or safe_die("Can't read question table in transInsertAns ".$connect->ErrorMsg());     // Checked

	while ($qentry = $res->FetchRow())
	{
		$question = $qentry['question'];
		$help = $qentry['help'];
		$qid = $qentry['qid'];
		$language = $qentry['language'];

		foreach ($fieldnames as $fnrow)
		{
			$pattern = "{INSERTANS:".$fnrow['oldfieldname']."}";
			$replacement = "{INSERTANS:".$fnrow['newfieldname']."}";
			$question=ereg_replace($pattern, $replacement, $question);
			$help=ereg_replace($pattern, $replacement, $help);
		}

		if (strcmp($question,$qentry['question']) !=0 ||
				strcmp($help,$qentry['help']) !=0)
		{
			// Update Field
			$sqlupdate = "UPDATE {$dbprefix}questions SET question='".auto_escape($question)."', help='".auto_escape($help)."' WHERE qid=$qid AND language='$language'";
			$updateres=$connect->Execute($sqlupdate) or safe_die ("Couldn't update INSERTANS in question<br />$sqlupdate<br />".$connect->ErrorMsg());    //Checked
		} // Enf if modified
	} // end while qentry


	# translate 'answer' INSERTANS tags in answers 
	$sql = "SELECT a.qid, a.language, a.code, a.answer from {$dbprefix}answers as a INNER JOIN {$dbprefix}questions as b ON a.qid=b.qid WHERE b.sid=".$newsid." AND a.answer LIKE '%{INSERTANS:".$oldsid."X%'";
	$res = db_execute_assoc($sql) or safe_die("Can't read answers table in transInsertAns ".$connect->ErrorMsg());     // Checked

	while ($qentry = $res->FetchRow())
	{
		$answer = $qentry['answer'];
		$code = $qentry['code'];
		$qid = $qentry['qid'];
		$language = $qentry['language'];

		foreach ($fieldnames as $fnrow)
		{
			$pattern = "{INSERTANS:".$fnrow['oldfieldname']."}";
			$replacement = "{INSERTANS:".$fnrow['newfieldname']."}";
			$answer=ereg_replace($pattern, $replacement, $answer);
		}

		if (strcmp($answer,$qentry['answer']) !=0)
		{
			// Update Field
			$sqlupdate = "UPDATE {$dbprefix}answers SET answer='".auto_escape($answer)."' WHERE qid=$qid AND code='$code' AND language='$language'";
			$updateres=$connect->Execute($sqlupdate) or safe_die ("Couldn't update INSERTANS in answers<br />$sqlupdate<br />".$connect->ErrorMsg());    //Checked
		} // Enf if modified
	} // end while qentry
}

function hasResources($id,$type='survey')
{
	global $publicdir;
	$dirname = "$publicdir/upload";

	if ($type == 'survey')
	{
		$dirname .= "/surveys/$id";
	}
	elseif ($type == 'label')
	{
		$dirname .= "/labels/$id";
	}
	else
	{
		return false;
	}

	if (is_dir($dirname) && $dh=opendir($dirname))
	{
		while(($entry = readdir($dh)) !== false)
		{
			if($entry !== '.' && $entry !== '..')
			{
				return true;
				break;
			}
		}
		closedir($dh);
	}
	else
	{
		return false;
	}

	return false;
}


function randomkey($length)
{
	$pattern = "23456789abcdefghijkmnpqrstuvwxyz";
	$patternlength = strlen($pattern)-1; 
	for($i=0;$i<$length;$i++)
	{
		if(isset($key))
		$key .= $pattern{rand(0,$patternlength)};
		else
		$key = $pattern{rand(0,$patternlength)};
	}
	return $key;
}


                           

function conditional_nl2br($mytext,$ishtml)
{
	if ($ishtml === true)
	{
		// $mytext has been processed by clang->gT with html mode
		// and thus \n has already been translated to &#10;
		return str_replace('&#10;', '<br />',$mytext);
	}
	else
	{
		return $mytext;
	}
}

function conditional2_nl2br($mytext,$ishtml)
{
	if ($ishtml === true)
	{
		return str_replace("\n", '<br />',$mytext);
	}
	else
	{
		return $mytext;
	}
}

function br2nl( $data ) {
     return preg_replace( '!<br.*>!iU', "\n", $data );
}

function getPopupHeight() 
{
    global $clang, $surveyid;
    
    $rowheight = 20;
    $height = 0;
    $bottomPad = 15;
    
    // header text height
    $htext = ceil(strlen($clang->gT("Please select a language:")) / 17);
    $height += $rowheight * $htext;
        
    // language list height
    $survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    $survlangs[] = $baselang;
    
    foreach ($survlangs as $lang)
    {
        $ltext = ceil(strlen(getLanguageNameFromCode($lang,false)) / 17);
        $height += $rowheight * $ltext;
        if ($ltext > 1) $height += ($ltext * 3);
    }

    // footer height
    $ftext = ceil(count($clang->gT("Cancel")) / 17);
    $height += $rowheight * $ftext;
    
    $height += $bottomPad;
    
    return $height;
}

function safe_die($text)
{
    //Only allowed tag: <br />
    $textarray=explode('<br />',$text);
    array_map('htmlspecialchars',$textarray);
    die(implode( '<br />',$textarray));
}

/**
* getQuotaInformation() returns quota information for the current survey
* @param string $surveyid - Survey identification number
* @param string $quotaid - Optional quotaid that restricts the result to a given quota
* @return array - nested array, Quotas->Members->Fields
*/
function getQuotaInformation($surveyid,$quotaid='all')
{
	$baselang = GetBaseLanguageFromSurveyID($surveyid);
	$query = "SELECT * FROM ".db_table_name('quota').", ".db_table_name('quota_languagesettings')."
		   	  WHERE ".db_table_name('quota').".id = ".db_table_name('quota_languagesettings').".quotals_quota_id
			  AND sid='{$surveyid}'
			  AND quotals_language='$baselang'";
	if ($quotaid != 'all')
	{
		$query .= " AND id=$quotaid";
	}

	$result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());    //Checked 
	$quota_info = array();
	$x=0;
	
	// Check all quotas for the current survey
	if ($result->RecordCount() > 0)
	{
		while ($survey_quotas = $result->FetchRow())
		{
			array_push($quota_info,array('Name' => $survey_quotas['name'],
										 'Limit' => $survey_quotas['qlimit'],
										 'Action' => $survey_quotas['action'],
										 'Message' => $survey_quotas['quotals_message'],
										 'Url' => passthruReplace(insertansReplace($survey_quotas['quotals_url']), getSurveyInfo($surveyid)),
										 'UrlDescrip' => $survey_quotas['quotals_urldescrip'],
										 'AutoloadUrl' => $survey_quotas['autoload_url']));
			$query = "SELECT * FROM ".db_table_name('quota_members')." WHERE quota_id='{$survey_quotas['id']}'";
			$result_qe = db_execute_assoc($query) or safe_die($connect->ErrorMsg());      //Checked 
			$quota_info[$x]['members'] = array();
			if ($result_qe->RecordCount() > 0)
			{
				while ($quota_entry = $result_qe->FetchRow())
				{
					$query = "SELECT type, title,gid FROM ".db_table_name('questions')." WHERE qid='{$quota_entry['qid']}' AND language='{$baselang}'";
					$result_quest = db_execute_assoc($query) or safe_die($connect->ErrorMsg());     //Checked 
					$qtype = $result_quest->FetchRow();
					
					$fieldnames = "0";
					
					if ($qtype['type'] == "I" || $qtype['type'] == "G" || $qtype['type'] == "Y")
					{
						$fieldnames=array(0 => $surveyid.'X'.$qtype['gid'].'X'.$quota_entry['qid']);
						$value = $quota_entry['code'];
					}
					
					if($qtype['type'] == "L" || $qtype['type'] == "O" || $qtype['type'] =="!") 
					{
					    $fieldnames=array(0 => $surveyid.'X'.$qtype['gid'].'X'.$quota_entry['qid']);
					    $value = $quota_entry['code'];
					}
				
					if($qtype['type'] == "M")
					{
						$fieldnames=array(0 => $surveyid.'X'.$qtype['gid'].'X'.$quota_entry['qid'].$quota_entry['code']);
						$value = "Y";
					}
					
					if($qtype['type'] == "A" || $qtype['type'] == "B")
					{
						$temp = explode('-',$quota_entry['code']);
						$fieldnames=array(0 => $surveyid.'X'.$qtype['gid'].'X'.$quota_entry['qid'].$temp[0]);
						$value = $temp[1];
					}
					
					array_push($quota_info[$x]['members'],array('Title' => $qtype['title'],
																'type' => $qtype['type'],
																'code' => $quota_entry['code'],
																'value' => $value,
																'qid' => $quota_entry['qid'],
																'fieldnames' => $fieldnames));
				}
			}
			$x++;
		}
	}
	return $quota_info;
}

/**
* get_quotaCompletedCount() returns the number of answers matching the quota
* @param string $surveyid - Survey identification number
* @param string $quotaid - quota id for which you want to compute the completed field
* @return string - number of mathing entries in the result DB or 'N/A'
*/
function get_quotaCompletedCount($surveyid, $quotaid)
{
	$result ="N/A";
	$quota_info = getQuotaInformation($surveyid,$quotaid);	
	$quota = $quota_info[0];

	if ( db_tables_exist(db_table_name_nq('survey_'.$surveyid))  &&
			count($quota['members']) > 0)
	{
		$fields_list = array(); // Keep a list of fields for easy reference
		unset($querycond);

		foreach($quota['members'] as $member)
		{
			$fields_query = array();
			$select_query = " (";
			foreach($member['fieldnames'] as $fieldname)
			{
				$fields_list[] = $fieldname;
				$fields_query[] = db_quote_id($fieldname)." = '{$member['value']}'";
				// Incase of multiple fields for an answer - only needs to match once.
				$select_query.= implode(' OR ',$fields_query).' )';
				$querycond[] = $select_query;
				unset($fields_query);
			}

		}
		//FOR MYSQL?
		$querysel = "SELECT count(id) as count FROM ".db_table_name('survey_'.$surveyid)." WHERE ".implode(' AND ',$querycond)." "." AND submitdate !=''";
		//FOR POSTGRES?
		$querysel = "SELECT count(id) as count FROM ".db_table_name('survey_'.$surveyid)." WHERE ".implode(' AND ',$querycond)." "." AND submitdate IS NOT NULL";
		$result = db_execute_assoc($querysel) or safe_die($connect->ErrorMsg()); //Checked
		$quota_check = $result->FetchRow();
		$result = $quota_check['count'];
	}

	return $result;
}

function fix_FCKeditor_text($str)
{
	$str = str_replace('<br type="_moz" />','',$str);
	if ($str == "<br />" || $str == " ")
	{
		$str = "";
	}
	if (preg_match("/^[\s]+$/",$str))
	{
		$str='';
	}
	return $str;
}


function recursive_stripslashes($array_or_string)
{
	if (is_array($array_or_string))
	{
		return array_map('recursive_stripslashes', $array_or_string);
	}
	else
	{
		return stripslashes($array_or_string);
	}
}




/**
* This function checks if a given question should be displayed or not
* If the optionnal gid parameter is set, then we are in a group/group survey
* and thus we can't evaluate conditions using answers on the same page 
* (this will be done by javascript): in this case we disregard conditions on 
* answers from same page
* 
* @param mixed $qid
* @param mixed $gid
*/
function checkquestionfordisplay($qid, $gid=null)
{ 
	global $dbprefix, $connect,$surveyid,$thissurvey;

	if (!is_array($thissurvey))
	{
		$local_thissurvey=getSurveyInfo($surveyid);
	}
	else
	{
		$local_thissurvey=$thissurvey;
	}

	$scenarioquery = "SELECT DISTINCT scenario FROM ".db_table_name("conditions")
		." WHERE ".db_table_name("conditions").".qid=$qid ORDER BY scenario";
	$scenarioresult=db_execute_assoc($scenarioquery);

	if ($scenarioresult->RecordCount() == 0)
	{
		return true;
	}

	while ($scenariorow=$scenarioresult->FetchRow())
	{
		$scenario = $scenariorow['scenario'];
		$totalands=0;
		$query = "SELECT * FROM ".db_table_name('conditions')."\n"
			."WHERE qid=$qid AND scenario=$scenario ORDER BY cqid,cfieldname";
		$result = db_execute_assoc($query) or safe_die("Couldn't check conditions<br />$query<br />".$connect->ErrorMsg());   //Checked 

		$conditionsfoundforthisscenario=0;
		while($row=$result->FetchRow())
		{
			// Conditions on the same question are Anded together
			// (for instance conditions on several multiple-numerical lines)
			// However, if they are related to the same cfieldname
			// they are Ored. Conditions on the same cfieldname can be either:
			// * conditions on the same 'simple question': 
			//   - for instance several possible answers on the same radio-button question
			// * conditions on the same 'multiple choice question': 
			//   - this case is very specific. In fact each checkbox corresponds to a different
			//     cfieldname (1X1X1a, 1X1X1b, ...), but the condition uses only the base 
			//     'SGQ' cfieldname and the expected answers codes as values
			//   - then, in the following lines for questions M or P, we transform the
			//     condition SGQ='a' to SGQa='Y'
			//  ==> This explains why conditions on multiple choice answers are ORed even if
			//      in reality they use a different cfieldname for each checkbox
			//
			// In order to implement this we build an array storing the result
			// of condition evaluations for this group and scenario
			// This array is processed as follow:
			// * it is indexed by cfieldname,
			// * each 'cfieldname' row is added at the first condition eval on this fieldname
			// * each row is updated only if the condition evaluation is successful
			//   ==> this way if only 1 condition for a cfieldname is successful, the set of
			//       conditions for this cfieldname is assumed to be met (Ored conditions)

			$conditionsfoundforthisscenario++;
			$conditionCanBeEvaluated=true;
			//Iterate through each condition for this question and check if it is met.

			if (preg_match("/^\+(.*)$/",$row['cfieldname'],$cfieldnamematch))
			{ // this condition uses a single checkbox as source
				$conditionSourceType='question';
			$query2= "SELECT type, gid FROM ".db_table_name('questions')."\n"
				." WHERE qid={$row['cqid']} AND language='".$_SESSION['s_lang']."'";
			$result2=db_execute_assoc($query2) or safe_die ("Coudn't get type from questions<br />$ccquery<br />".$connect->ErrorMsg());   //Checked 
			while($row2=$result2->FetchRow())
			{
				$cq_gid=$row2['gid'];
					// set type to +M or +P in order to skip
					$thistype='+'.$row2['type']; 
				}

				$row['cfieldname']=$cfieldnamematch[1]; // remover the leading '+'
			}
			elseif (preg_match("/^{/",$row['cfieldname']))
			{ // this condition uses a token attr as source
				$conditionSourceType='tokenattr';
				$thistype="";
				$cq_gid=0;
			}
			else
			{ // this is a simple condition using a question as source
				$conditionSourceType='question';
				$query2= "SELECT type, gid FROM ".db_table_name('questions')."\n"
					." WHERE qid={$row['cqid']} AND language='".$_SESSION['s_lang']."'";
				$result2=db_execute_assoc($query2) or safe_die ("Coudn't get type from questions<br />$ccquery<br />".$connect->ErrorMsg());   //Checked 
				while($row2=$result2->FetchRow())
				{
					$cq_gid=$row2['gid'];
					//Find out the "type" of the question this condition uses
					$thistype=$row2['type'];
				}
			}


			
			// Fix the cfieldname and cvalue in case of type M or P questions
			if ($thistype == "M" || $thistype == "P")
			{
				// For multiple choice type questions, the "answer" value will be "Y"
				// if selected, the fieldname will have the answer code appended.
				$row['cfieldname']=$row['cfieldname'].$row['value'];
				$row['value']="Y";     
			}
			
			if ( !is_null($gid) && $gid == $cq_gid && $conditionSourceType == 'question')
			{
				//Don't do anything - this cq is in the current group
			}
			elseif (ereg('^@([0-9]+X[0-9]+X[^@]+)@',$row['value'],$targetconditionfieldname))
			{ 
				if (isset($_SESSION[$targetconditionfieldname[1]]) )
				{
					// If value uses @SIDXGIDXQID@ codes i
					// then try to replace them with a 
					// value recorded in SESSION if any
					$cvalue=$_SESSION[$targetconditionfieldname[1]];
					if ($conditionSourceType == 'question')
					{
					if (isset($_SESSION[$row['cfieldname']]))
					{ 
						$cfieldname=$_SESSION[$row['cfieldname']]; 
					} 
					else 
					{ 
						$conditionCanBeEvaluated=false;
						//$cfieldname=' ';
					}
				}
					elseif ($local_thissurvey['private'] == "N" && ereg('^{TOKEN:([^}]*)}$',$row['cfieldname'],$sourceconditiontokenattr))
					{
						if ( isset($_SESSION['token']) &&
							in_array(strtolower($sourceconditiontokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
						{
							$cfieldname=GetAttributeValue($surveyid,strtolower($sourceconditiontokenattr[1]),$_SESSION['token']);	
						}
				else
						{
							$conditionCanBeEvaluated=false;
						}
					
					}
					else
					{
						$conditionCanBeEvaluated=false;
					}
				}
				else
				{ // if _SESSION[$targetconditionfieldname[1]] is not set then evaluate condition as FALSE
					$conditionCanBeEvaluated=false;
					//$cfieldname=' ';
				}
			}
			elseif ($local_thissurvey['private'] == "N" && ereg('^{TOKEN:([^}]*)}$',$row['value'],$targetconditiontokenattr))
			{ //TIBO
				if ( isset($_SESSION['token']) && 
					in_array(strtolower($targetconditiontokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
				{
					// If value uses {TOKEN:XXX} placeholders
					// then try to replace them with a 
					// the value recorded in DB
					$cvalue=GetAttributeValue($surveyid,strtolower($targetconditiontokenattr[1]),$_SESSION['token']);
					if ($conditionSourceType == 'question')
					{
						if (isset($_SESSION[$row['cfieldname']]))
						{ 
							$cfieldname=$_SESSION[$row['cfieldname']]; 
						} 
			else
			{
							$conditionCanBeEvaluated=false;
						}
					}
					elseif ($local_thissurvey['private'] == "N" && ereg('^{TOKEN:([^}]*)}$',$row['cfieldname'],$sourceconditiontokenattr))
					{
						if ( isset($_SESSION['token']) &&
							in_array(strtolower($sourceconditiontokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
						{
							$cfieldname=GetAttributeValue($surveyid,strtolower($sourceconditiontokenattr[1]),$_SESSION['token']);	
						}
						else
						{
							$conditionCanBeEvaluated=false;
						}
					
					}
					else
					{
						$conditionCanBeEvaluated=false;
					}
				}
				else
				{ // if _SESSION[$targetconditionfieldname[1]] is not set then evaluate condition as FALSE
					$conditionCanBeEvaluated=false;
				}
			}
			else
			{
				$cvalue=$row['value'];
				if ($conditionSourceType == 'question')
				{
					if (isset($_SESSION[$row['cfieldname']]))
					{
						$cfieldname=$_SESSION[$row['cfieldname']];
					} 
					elseif ($thistype == "M" || $thistype == "P" || $thistype == "+M" || $thistype == "+P")
					{
						$cfieldname="";
					}
					else 
					{
						$conditionCanBeEvaluated=false;
					}
				}
				elseif ($local_thissurvey['private'] == "N" && ereg('^{TOKEN:([^}]*)}$',$row['cfieldname'],$sourceconditiontokenattr))
				{
					if ( isset($_SESSION['token']) &&
							in_array(strtolower($sourceconditiontokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
					{
						$cfieldname=GetAttributeValue($surveyid,strtolower($sourceconditiontokenattr[1]),$_SESSION['token']);	
					}
					else
					{
						$conditionCanBeEvaluated=false;
					}

				}
				else
				{
					$conditionCanBeEvaluated=false;
				}
			}

			if ( !is_null($gid) && $gid == $cq_gid && $conditionSourceType == 'question')
			{
				//Don't do anything - this cq is in the current group
				$conditionMatches=true;
			}
			elseif ($conditionCanBeEvaluated === false)
			{
				// condition can't be evaluated, so let's assume FALSE
				$conditionMatches=false;
			}
			else
			{
					if (trim($row['method'])=='') 
					{
						$row['method']='==';
					}
				if ($row['method'] != 'RX')
				{
					if (eval('if (trim($cfieldname)'. $row['method'].' trim($cvalue)) return true; else return false;'))
					{
						$conditionMatches=true;
						//This condition is met
					}
					else
					{
						$conditionMatches=false;
					}
				}
				else
				{
					if (ereg(trim($cvalue),trim($cfieldname)))
					{
						$conditionMatches=true;

					}
					else
					{
						$conditionMatches=false;
					}
				}
			}

			if ($conditionMatches === true)
			{
				// Let's store this positive result in the distinctcqids array
				// indexed by cfieldname so that conditions on theb same cfieldname ar Ored
				// while conditions on different cfieldnames (either different conditions
				// or conditions on different cfieldnames inside the same question)
				if (!isset($distinctcqids[$row['cfieldname']]) || $distinctcqids[$row['cfieldname']] == 0)
				{
					$distinctcqids[$row['cfieldname']] = 1;
				}
			}
			else
			{
				// Let's store this negative result in the distinctcqids array
				// indexed by cfieldname so that conditions on theb same cfieldname ar Ored
				// while conditions on different cfieldnames (either different conditions
				// or conditions on different cfieldnames inside the same question)
				if (!isset($distinctcqids[$row['cfieldname']]))
				{
					$distinctcqids[$row['cfieldname']] = 0;
				}
			}
		} // while
		if ($conditionsfoundforthisscenario > 0) {
			foreach($distinctcqids as $key=>$val)
			{
				// Let's count the number of conditions that are met, and then compare
				// it to the total number of stored results
				$totalands=$totalands+$val;
			}
			if ($totalands >= count($distinctcqids))
			{
				// if all stored results are positive then we MUST show the group
				// because at least this question is displayed
				return true;
			}
		}
		else
		{
			//Curious there is no condition for this question in this scenario
			// this is not a normal behaviour, but I propose to defaults to a
			// condition-matched state in this case
			return true;
		}
		unset($distinctcqids);
	} // end while scenario
	return false;
}

/**
* This is a helper function for GetAttributeFieldNames
* 
* @param mixed $fieldname
*/
function filterforattributes ($fieldname)
{
    if (strpos($fieldname,'attribute_')===false) return false; else return true;    
}


/**
* Retrieves the attribute field names from the related token table
* 
* @param mixed $surveyid  The survey ID
* @return array The fieldnames
*/
function GetAttributeFieldNames($surveyid)
{
    global $dbprefix, $connect;
    if (tokenTableExists($surveyid) === false)
    {
    return Array();
    }    
    $tokenfieldnames = array_values($connect->MetaColumnNames("{$dbprefix}tokens_$surveyid", true));
    return array_filter($tokenfieldnames,'filterforattributes');
}

/**
* Retrieves the token field names usable for conditions from the related token table
* 
* @param mixed $surveyid  The survey ID
* @return array The fieldnames
*/
function GetTokenConditionsFieldNames($surveyid)
{
    $extra_attrs=GetAttributeFieldNames($surveyid);
    $basic_attrs=Array('firstname','lastname','email','token','language','sent','remindersent','remindercount');
    return array_merge($basic_attrs,$extra_attrs);
}

/**
* Retrieves the attribute names from the related token table
* 
* @param mixed $surveyid  The survey ID
* @return array The fieldnames as key and names as value in an Array
*/
function GetTokenFieldsAndNames($surveyid, $onlyAttributes=false)
{
    global $dbprefix, $connect, $clang;
    if (tokenTableExists($surveyid) === false)
    {
	return Array();
    }
    $extra_attrs=GetAttributeFieldNames($surveyid);
    $basic_attrs=Array('firstname','lastname','email','token','language','sent','remindersent','remindercount');
    $basic_attrs_names=Array(
			$clang->gT('First Name'),
			$clang->gT('Last Name'),
			$clang->gT('Email address'),
			$clang->gT('Token code'),
			$clang->gT('Language code'),
			$clang->gT('Invitation sent date'),
			$clang->gT('Last Reminder sent date'),
			$clang->gT('Total numbers of sent reminders'));

    $thissurvey=getSurveyInfo($surveyid);               
    $attdescriptiondata=!empty($thissurvey['attributedescriptions']) ? $thissurvey['attributedescriptions'] : "";
    $attdescriptiondata=explode("\n",$attdescriptiondata);
    $attributedescriptions=array(); 
    $basic_attrs_and_names=array();
    $extra_attrs_and_names=array();
    foreach ($attdescriptiondata as $attdescription)
    {
        $attributedescriptions['attribute_'.substr($attdescription,10,strpos($attdescription,'=')-10)] = substr($attdescription,strpos($attdescription,'=')+1);
    }
    foreach ($extra_attrs as $fieldname)
    {
        if (isset($attributedescriptions[$fieldname]))
        {
            $extra_attrs_and_names[$fieldname]=$attributedescriptions[$fieldname];
        }
        else
        {
            $extra_attrs_and_names[$fieldname]=sprintf($clang->gT('Attribute %s'),substr($fieldname,10));
        }
    }
    if ($onlyAttributes===false)
    {
        $basic_attrs_and_names=array_combine($basic_attrs,$basic_attrs_names);
        return array_merge($basic_attrs_and_names,$extra_attrs_and_names);
    }
    else
    {
        return $extra_attrs_and_names;
    }
}

/**
* Retrieves the token attribute value from the related token table
* 
* @param mixed $surveyid  The survey ID
* @param mixed $attrName  The token-attribute field name
* @param mixed $token  The token code
* @return string The token attribute value (or null on error)
*/
function GetAttributeValue($surveyid,$attrName,$token)
{
    global $dbprefix, $connect;
    $attrName=strtolower($attrName);
    if (!in_array($attrName,GetTokenConditionsFieldNames($surveyid)))
    {
	return null;	
    }
    $sanitized_token=$connect->qstr($token,get_magic_quotes_gpc());
    $surveyid=sanitize_int($surveyid);

    $query="SELECT $attrName FROM {$dbprefix}tokens_$surveyid WHERE token=$sanitized_token"; 
    $result=db_execute_num($query);
    $count=$result->RecordCount();
    if ($count != 1)
    {
	return null;
    }
    else
    {
    	$row=$result->FetchRow();
	return $row[0];
    }
}

/**
* This function strips any content between and including <style>  & <javascript> tags
* 
* @param string $content String to clean
* @return string  Cleaned string
*/
function strip_javascript($content){
    $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
                   '@<style[^>]*?>.*?</style>@siU'    // Strip style tags properly
    /*               ,'@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
                   '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA
                   */
    );
    $text = preg_replace($search, '', $content);
    return $text;
} 


/**
 * 
 * formats a datestring (YY-MM-DD or YYYY-MM-DD or YY-M-D... to whatever)
 * @param $date Datestring, that should be formated normally it is in YYYY-MM-DD, but we take also YY-MM-DD or YY-M-D
 * @param $format Format you want your date in (DD.MM.YYYY or MM.DD.YYYY or MM/YY ? everything possible )
 * @return formated datestring

function dateFormat($date, $format="DD.MM.YYYY")
{
	if(preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/",$date))
	{
		$pieces = explode("-",$date);
		$yy = $pieces[0];
		$mm = $pieces[1];
		$dd = $pieces[2];
	}
	elseif(preg_match("/^([0-9]{2})-([0-9]{1,2})-([0-9]{1,2})/",$date))
	{
		$pieces = explode("-",$date);
		$yy = $pieces[0];
		$mm = $pieces[1];
		$dd = $pieces[2];
	}
	else
	{
		return "No valid Date";
	}
	// Format check
	$c['Y'] = substr_count($format,"Y" );
	$c['M'] = substr_count($format,"M" );
	$c['D'] = substr_count($format,"D" );
	
	foreach($c as $key => $value)
	{
		for($n=0;$n<$value;++$n)
		{
			$dFormat[$key] .= "".$key;
		}
	}
	
	if(strlen($yy)>$c['Y'])
	{$yy = substr($yy,-2,2);}
	if(strlen($yy)<4 && strlen($yy)<$c['Y'])
	{$yy = "20".$yy;}
	if(strlen($mm)<2 && strlen($mm)<$c['M'])
	{$mm = "0".$mm;}
	if(strlen($dd)>2 )
	{$dd = substr($dd,0,2);}
	if(strlen($dd)<2 && strlen($dd)<$c['D'])
	{$dd = "0".$dd;}
	
	$return = str_replace($dFormat['Y'],substr($yy,-$c['Y'], $c['Y']), $format);
	$return = str_replace($dFormat['M'],substr($mm,-$c['M'], $c['M']), $return);
	$return = str_replace($dFormat['D'],substr($dd,-$c['D'], $c['D']), $return);
	
	return $return;
}
 */
 
/**
* This function cleans files from the temporary directory being older than 1 day
* @todo Make the days configurable 
*/
function cleanTempDirectory()
{
    global $tempdir;
    $dir=  $tempdir.'/';
    $dp = opendir($dir) or die ('Could not open temporary directory');
    while ($file = readdir($dp)) {
        if ((filemtime($dir.$file)) < (strtotime('-1 days')) && $file!='index.html' && $file!='readme.txt' && $file!='..' && $file!='.' && $file!='.svn') {
            unlink($dir.$file);
        }
    }
    closedir($dp);    
}


function use_firebug()
{
    if(FIREBUG == true)
    {
        return '<script type="text/javascript" src="http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js"></script>';
    };
};

/**
* This is a convenience function for the coversion of datetime values
*  
* @param mixed $value
* @param mixed $fromdateformat
* @param mixed $todateformat
* @return string
*/
function convertDateTimeFormat($value, $fromdateformat, $todateformat)
{
    $datetimeobj = new Date_Time_Converter($value , $fromdateformat);
    return $datetimeobj->convert($todateformat);                
}


/**
* This function removes the UTF-8 Byte Order Mark from a string
* 
* @param string $str
* @return string
*/
function removeBOM($str=""){
        if(substr($str, 0,3) == pack("CCC",0xef,0xbb,0xbf)) {
                $str=substr($str, 3);
        }
        return $str;
} 
