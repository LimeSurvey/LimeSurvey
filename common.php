<?php
/*
    #############################################################
    # >>> PHPSurveyor                                           #
    #############################################################
    # > Author:  Jason Cleeland                                 #
    # > E-mail:  jason@cleeland.org                             #
    # > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
    # >          CARLTON SOUTH 3053, AUSTRALIA                  #
    # > Date:    20 February 2003                               #
    #                                                           #
    # This set of scripts allows you to develop, publish and    #
    # perform data-entry on surveys.                            #
    #############################################################
    #                                                           #
    #   Copyright (C) 2003  Jason Cleeland                      #
    #                                                           #
    # This program is free software; you can redistribute       #
    # it and/or modify it under the terms of the GNU General    #
    # Public License as published by the Free Software          #
    # Foundation; either version 2 of the License, or (at your  #
    # option) any later version.                                #
    #                                                           #
    # This program is distributed in the hope that it will be   #
    # useful, but WITHOUT ANY WARRANTY; without even the        #
    # implied warranty of MERCHANTABILITY or FITNESS FOR A      #
    # PARTICULAR PURPOSE.  See the GNU General Public License   #
    # for more details.                                         #
    #                                                           #
    # You should have received a copy of the GNU General        #
    # Public License along with this program; if not, write to  #
    # the Free Software Foundation, Inc., 59 Temple Place -     #
    # Suite 330, Boston, MA  02111-1307, USA.                   #
    #############################################################
*/

//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix)) {die("Cannot run this script directly");}

require_once(dirname(__FILE__).'/classes/adodb/adodb.inc.php');

$versionnumber = "1.01alpha";
$dbprefix=strtolower($dbprefix);
define("_PHPVERSION", phpversion());
if ($mutemailerrors==1) {define('PRINT_ERROR', false);}

##################################################################################
## DO NOT EDIT BELOW HERE
##################################################################################

require_once (dirname(__FILE__).'/classes/phpmailer/class.phpmailer.php');
require_once (dirname(__FILE__).'/classes/php-gettext/gettext.inc');
require_once (dirname(__FILE__).'/classes/core/surveytranslator.php');


if($_SERVER['SERVER_SOFTWARE'] == "Xitami") //Deal with Xitami Issue
    {
    $_SERVER['PHP_SELF'] = substr($_SERVER['SERVER_URL'], 0, -1) .$_SERVER['SCRIPT_NAME'];
    }

/*
* $sourcefrom variable checks the location of the current script against
* the administration directory, and if the current script is running
* in the administration directory, it is set to "admin". Otherwise it is set
* to "public". When $sourcefrom is "admin" certain administration only functions
* are loaded.
*/

if ((!isset($rootsymlinked)) || $rootsymlinked==0 ) {$scriptlocation=realpath(".");}
elseif ($rootsymlinked==1) {$scriptlocation = dirname($_SERVER['SCRIPT_FILENAME']);}
else {
        echo "ERROR: Cannot locate path - the PHP server does not have a setting for realpath or SCRIPT_FILENAME. Contact our support at phpsurveyor.org for assistance!";
        exit;
}

$slashlesspath=str_replace(array("\\", "/"), "", $scriptlocation);
$slashlesshome=str_replace(array("\\", "/"), "", $homedir);

// Uncomment the following line for debug purposes
// echo $slashlesspath." - ".$slashlesshome;

if (eregi($slashlesshome, $slashlesspath) || eregi("dump", $_SERVER['PHP_SELF'])) {
    $sourcefrom="admin";
} else {
    $sourcefrom="public";
}

//IF THIS IS AN ADMIN SCRIPT, RUN THE SESSIONCONTROL SCRIPT
if ($sourcefrom == "admin")
    {
    include("sessioncontrol.php");
    }

$btstyle = "class='btstyle' ";
$slstyle = "class='slstyle' ";
$slstyle2= "class='slstyle2' ";

//TURN OFF OPTIONS THAT DON'T WORK IN SAFE MODE IF NECESSARY
if (!ini_get('safe_mode') && (!eregi('shell_exec',ini_get('disable_functions'))))
    {
    // Only do this if safe_mode is OFF
    if (isset($mysqldir)) {$mysqlbin=$mysqldir;}
    if (isset($apachedir)) {$htpasswddir=$apachedir;}
	if ((substr($OS, 0, 3) != "WIN") && (substr($OS, 0, 4) != "OS/2") )
	   	{
        //USING LINUX: Find the location of various files and put that in the appropriate variables!
        if (!isset($mysqlbin) || !$mysqlbin)
            {
            $temp=shell_exec('which mysqldump');
            list($mysqlbin, $discard)=explode(" ", $temp);
            $mysqlbin=substr($mysqlbin, 0, strlen($mysqlbin)-11);
            }
        if (!isset($apachedir) || !$apachedir)
            {
            $temp=shell_exec('which htpasswd');
            list($htpasswddir, $discard)=explode(" ", $temp);
            $htpasswddir=substr($htpasswddir, 0, strlen($htpasswddir)-10);
            }
        else
            {
            $htpasswddir=$apachedir;
            }
        }
    }
else
    {
    // Safe Mode is ON - turn off security
    $accesscontrol = 0;
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
$localtimedate=(strftime("%Y-%m-%d %H:%M", mktime(date("H")+$timeadjust)));

// SITE STYLES
$setfont = "<font size='2' face='verdana'>";

$singleborderstyle = "style='border: 1px solid #111111'";

//CACHE DATA
$connect=&ADONewConnection($databasetype);
$database_exists = FALSE;
if ($connect->Connect("$databaselocation:$databaseport", $databaseuser, $databasepass, $databasename))
   { $database_exists = TRUE;}
    else {
         $connect->database = '';
         $connect->Connect("$databaselocation:$databaseport", $databaseuser, $databasepass);
         }


// The following line is for debug purposes
//$tmpresult=@mysql_query("SET SESSION SQL_MODE='STRICT_ALL_TABLES'");

$connect->Execute("SET CHARACTER SET 'utf8'");


//Admin menus and standards
if ($sourcefrom == "admin")
    {
    /**
    * @param string $htmlheader
    * This is the html header text for all administration pages
    *
    */
    $htmlheader = getAdminHeader();
    
    }
    /**
     * showadminmenu() function returns html text for the administration button bar
     * @global string $accesscontrol
     * @global string $homedir
     * @global string $scriptname
     * @global string $surveyid
     * @global string $setfont
     * @global string $imagefiles
     * @return string $adminmenu
     */
    function showadminmenu()
        {
        global $accesscontrol, $homedir, $scriptname, $surveyid, $setfont, $imagefiles;
        $adminmenu  = "<table width='100%' border='0' bgcolor='#DDDDDD'>\n"
                    . "\t<tr>\n"
                    . "\t\t<td>\n"
                    . "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
                    . "\t\t\t<tr bgcolor='#555555'>\n"
                    . "\t\t\t\t<td colspan='2' height='8'>\n"
                    . "\t\t\t\t\t$setfont<font size='1' color='white'><strong>"._("Administration")."</strong>\n"
                    . "\t\t\t\t</font></font></td>\n"
                    . "\t\t\t</tr>\n"
                    . "\t\t\t<tr bgcolor='#999999'>\n"
                    . "\t\t\t\t<td>\n"
                    . "\t\t\t\t\t<input type='image' src='$imagefiles/home.png' name='HomeButton' alt='"._("Default Administration Page")."' " 
                    ."title=''" 
                    ."align='left' onClick=\"window.open('$scriptname', '_top')\" onmouseout=\"hideTooltip()\"" 
                    ."onmouseover=\"showTooltip(event,'"._("Default Administration Page")."');return false\">\n"
                    . "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='11'  align='left'>\n"
                    . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt=''  align='left'>\n";
        if ($accesscontrol == 1)
            {
            $fhtaccess = "$homedir/.htaccess";
            if (!file_exists($fhtaccess))
                {
                $adminmenu .= "\t\t\t\t\t<input type='image' src='$imagefiles/badsecurity.png' name='AdminSecurity'"
                            . " title='' alt='"._("Activate Security")."'  align='left' "
                            . "onClick=\"window.open('$scriptname?action=editusers', '_top')\"" 
                            . "onmouseout=\"hideTooltip()\"" 
                    		. "onmouseover=\"showTooltip(event,'"._("Activate Security")."');return false\">";
                }
            else
                {
                $securityok = checksecurity();
                $adminmenu .= "\t\t\t\t\t<input type='image' src='$imagefiles/security.png' name='AdminSecurity' title='"
                            . _("Modify Security Settings")."' alt='". _("Modify Security Settings")."' align='left' "
                            . "onClick=\"window.open('$scriptname?action=editusers', '_top')\"" 
                            . "onmouseout=\"hideTooltip()\"" 
                    		. "onmouseover=\"showTooltip(event,'"._("Modify Security Settings")."');return false\">";
                }
            }
        else
            {
            $adminmenu .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20'  align='left'>\n";
            }
        $adminmenu .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20'  align='left'>\n"
                    . "\t\t\t\t\t<input type='image' src='$imagefiles/summary.png' name='CheckSettings' title='"
                    ."' alt='". _("Check Settings")."' align='left' "
                    . "onClick=\"window.open('$scriptname?action=checksettings', '_top')\"" 
                    . "onmouseout=\"hideTooltip()\"" 
                    . "onmouseover=\"showTooltip(event,'". _("Check Settings")."');return false\">"
                    //. "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20'  align='left'>\n"
                    . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' >\n";

        $adminmenu .= "\t\t\t\t\t<input type='image' src='$imagefiles/checkdb.png' name='CheckDatabase' title='"
                    ."'  alt='"._("Check Database")."' align='left' onClick=\"window.open('checkfields.php', '_top')\"" 
                    ."onmouseout=\"hideTooltip()\"" 
                    ."onmouseover=\"showTooltip(event,'"._("Check Database")."');return false\">\n";

        if ($surveyid)
            {
            $adminmenu  .="\t\t\t\t\t<input type='image' src='$imagefiles/delete.png' name='DeleteSurvey' alt='". _("Delete Entire Survey")." ($surveyid)' title='' align='left' "
                        . "onClick=\"window.open('deletesurvey.php?sid=$surveyid', '_top')\"" 
                        . "onmouseout=\"hideTooltip()\"" 
                        . "onmouseover=\"showTooltip(event,'"._("Delete Entire Survey")."');return false\">\n";
            }
        else
            {
              $adminmenu .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20'  align='left'>\n";
            }
        $adminmenu  .= "\t\t\t\t\t<input type='image' src='$imagefiles/backup.png' name='ExportDB' title='' "
        			. "alt='". _("Backup Entire Database")."($surveyid)' align='left' "
                    . "onClick=\"window.open('dumpdb.php', '_top')\""
                    . "onmouseout=\"hideTooltip()\"" 
                    . "onmouseover=\"showTooltip(event,'"._("Backup Entire Database")."');return false\">\n"
                    . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' >\n"
                    . "\t\t\t\t\t<input type='image' src='$imagefiles/labels.png' align='left' name='LabelsEditor' title='"
                    . _("Edit/Add Label Sets")."' alt='". _("Edit/Add Label Sets")."'onClick=\"window.open('labels.php', '_top')\""
                    . "onmouseout=\"hideTooltip()\"" 
                    . "onmouseover=\"showTooltip(event,'"._("Edit/Add Label Sets")."');return false\">\n"
                    . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' >\n"
                    . "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20'  align='left'>\n"
                    . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' >\n";
        $adminmenu .= "\t\t\t\t\t<input type='image' src='$imagefiles/templates.png' name='EditTemplates' title='' " 
        		    . "alt='". _("Template Editor")."' align='left' "
                    . "onClick=\"window.open('templates.php', '_top')\""
                    . "onmouseout=\"hideTooltip()\"" 
                    . "onmouseover=\"showTooltip(event,'"._("Template Editor")."');return false\">\n"
                    . "\t\t\t\t</td>\n";
        $adminmenu .= "\t\t\t\t<td align='right' width='400'>\n"
                    . "\t\t\t\t\t<input type='image' src='$imagefiles/showhelp.png' name='ShowHelp' title=''" 
                    . "alt='". _("Show Help")."' align='right' onClick=\"showhelp('show')\"" 
                    . "onmouseout=\"hideTooltip()\"" 
                    . "onmouseover=\"showTooltip(event,'"._("Show Help")."');return false\">\n"
                    . "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='42' height='20' align='right' >\n"
                    . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='right' >\n"
                    . "\t\t\t\t\t<input type='image' src='$imagefiles/add.png' align='right' name='AddSurvey' title=''" 
                    . " alt='". _("Create or Import New Survey")."' onClick=\"window.open('$scriptname?action=newsurvey', '_top')\""
                    . "onmouseout=\"hideTooltip()\"" 
                    . "onmouseover=\"showTooltip(event,'"._("Create or Import New Survey")."');return false\">\n"
                    . "\t\t\t\t\t$setfont<font size='2'><strong>"._("Surveys").":</strong> "
                    . "\t\t\t\t\t<select class=\"listboxsurveys\""
                    . "onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n"
                    //. $surveyselect
                    . getsurveylist()
                    . "\t\t\t\t\t</select>\n"
                    . "\t\t\t\t</font></font></td>\n"
                    . "\t\t\t</tr>\n"
                    . "\t\t</table>\n"
                    . "\t</td>\n"
                    . "</tr>\n"
                    . "</table>\n";
        return $adminmenu;
        }


//DATA TYPES
$qtypeselect = getqtypelist();

//SECURITY SETUP STUFF
$htaccess =<<<EOF
AuthType Basic
AuthName "SURVEYOR: Authorised Users only"
AuthUserFile "$homedir/.htpasswd"
AuthGroupFile /dev/null
Require valid-user
EOF;

function &db_execute_num($sql,$inputarr=false)
{
	global $connect;

	$connect->SetFetchMode(ADODB_FETCH_NUM);
	return $connect->Execute($sql,$inputarr);
}

function &db_execute_assoc($sql,$inputarr=false)
{
	global $connect;

	$connect->SetFetchMode(ADODB_FETCH_ASSOC);
	return $connect->Execute($sql,$inputarr);
}

function db_quote_id($id)
{
	global $connect;
	$quote = $connect->nameQuote;
	return $quote.str_replace($quote,$quote.$quote,$id).$quote;
}

function db_table_name($name)
{
	global $dbprefix;
	return db_quote_id($dbprefix.$name);
}

/**
* getsurveylist() Queries the database (survey table) for a list of existing surveys
* @global string $surveyid
* @global string $dbprefix
* @global string $scriptname
* @return string This string is returned containing <option></option> formatted list of existing surveys
*
*/
function getsurveylist()
    {
    global $surveyid, $dbprefix, $scriptname, $connect;
    $surveyidquery = 'SELECT sid, short_title FROM '.db_table_name('surveys').' ORDER BY short_title';
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    $surveyselecter = "";
    $surveynames = $surveyidresult->GetRows();
    if ($surveynames)
        {
        foreach($surveynames as $sv)
            {
            $surveyselecter .= "\t\t\t<option";
            if ($sv[0] == $surveyid) {$surveyselecter .= " selected"; $svexist = 1;}
            $surveyselecter .=" value='$scriptname?sid=$sv[0]'>$sv[1]</option>\n";
            }
        }
    if (!isset($svexist)) {$surveyselecter = "\t\t\t<option selected>"._("Please Choose...")."</option>\n".$surveyselecter;}
    else {$surveyselecter = "\t\t\t<option value='$scriptname?sid='>"._("None")."</option>\n".$surveyselecter;}
    return $surveyselecter;
    }

/**
* getquestions() queries the database for a list of all questions matching the current survey sid
* @global string $surveyid
* @global string $gid
* @global string $qid
* @global string $dbprefix
* @global string $scriptname
* @return This string is returned containing <option></option> formatted list of questions to current survey
*/
function getquestions()
    {
    global $surveyid, $gid, $qid, $dbprefix, $scriptname, $connect;
    $qquery = 'SELECT * FROM '.db_table_name('questions')." WHERE sid=$surveyid AND gid=$gid";
    $qresult = db_execute_assoc($qquery);
    $qrows = $qresult->GetRows();

    // Perform a case insensitive natural sort on group name then question title of a multidimensional array
    usort($qrows, 'CompareGroupThenTitle');
    if (!isset($questionselecter)) {$questionselecter="";}
    foreach ($qrows as $qrow)
        {
        $qrow['title'] = htmlspecialchars($qrow['title']);
        $questionselecter .= "\t\t<option value='$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid={$qrow['qid']}'";
        if ($qid == $qrow['qid']) {$questionselecter .= " selected"; $qexists="Y";}
        $questionselecter .=">{$qrow['title']}:";
        $questionselecter .= " ";
        $question=strip_tags($qrow['question']);
        if (strlen($question)<35)
            {
            $questionselecter .= htmlspecialchars($question);
            }
        else
            {
            $questionselecter .= htmlspecialchars(substr($question, 0, 35))."..";
            }
        $questionselecter .= "</option>\n";
        }

    if (!isset($qexists))
        {
        $questionselecter = "\t\t<option selected>"._("Please Choose...")."</option>\n".$questionselecter;
        }
    return $questionselecter;
    }

/**
* getanswers() queries the database for a list of all answers matching the current question qid
* @global string $surveyid
* @global string $gid
* @global string $qid
* @global string $dbprefix
* @global string $code
* @return This string is returned containing <option></option> formatted list of answers matching current qid
*/
function getanswers()
    {
    global $surveyid, $gid, $qid, $code, $dbprefix, $connect;
    $aquery = 'SELECT code, answer FROM '.db_table_name('answers')." WHERE qid=$qid ORDER BY sortorder, answer";
    $aresult = db_execute_assoc($aquery);
    $answerselecter = "";
    while ($arow = $aresult->FetchRow())
        {
        $answerselecter .= "\t\t<option value='$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;code={$arow['code']}'";
        if ($code == $arow['code']) {$answerselecter .= " selected"; $aexists="Y";}
        $answerselecter .= ">{$arow['code']}: {$arow['answer']}</option>\n";
        }
    if (!$aexists) {$answerselecter = "\t\t<option selected>"._("Please Choose...")."</option>\n".$answerselecter;}
    return $answerselecter;
    }

/**
* getqtypelist() Returnst list of question types available in PHPSurveyor. Edit this if you are adding a new
*    question type
* @global string $publicurl
* @global string $sourcefrom
* @param string $SelectedCode Value of the Question Type (defaults to "T")
* @param string $ReturnType Type of output from this function (defaults to selector)
* @return depending on $ReturnType param, returns a straight "array" of question types, or an <option></option> list
*/
function getqtypelist($SelectedCode = "T", $ReturnType = "selector")
    {
    global $publicurl;
    global $sourcefrom;
    if ($sourcefrom == "admin")
        {
        $qtypes = array(
            "5"=>_("5 Point Choice"),
            "D"=>_("Date"),
            //"X"=>_("Email Address"),
            "G"=>_("Gender"),
            "!"=>_("List (Dropdown)"),
            "L"=>_("List (Radio)"),
            "O"=>_("List With Comment"),
            "M"=>_("Multiple Options"),
            "P"=>_("Multiple Options With Comments"),
            "Q"=>_("Multiple Short Text"),
            "N"=>_("Numerical Input"),
            "R"=>_("Ranking"),
            "S"=>_("Short free text"),
            "T"=>_("Long free text"),
            "U"=>_("Huge Free Text"),
            "Y"=>_("Yes/No"),
            "A"=>_("Array (5 Point Choice)"),
            "B"=>_("Array (10 Point Choice)"),
            "C"=>_("Array (Yes/No/Uncertain)"),
            "E"=>_("Array (Increase, Same, Decrease)"),
            "F"=>_("Array (Flexible Labels)"),
            "H"=>_("Array (Flexible Labels) by Column"),
//			"J"=>_("Multiple Choice Question CSV-File based"),
//			"I"=>_("Single Choice Question CSV-File based"),

            //"V"=>_JSVALIDATEDTEXT,
            "X"=>_("Boilerplate Question"),
            "W"=>_("List (Flexible Labels) (Dropdown)"),
            "Z"=>_("List (Flexible Labels) (Radio)")
//            "^"=>_("Slider"),
			);

        if ($ReturnType == "array") {return $qtypes;}
        $qtypeselecter = "";
        foreach($qtypes as $TypeCode=>$TypeDescription)
            {
            $qtypeselecter .= "\t\t<option value='$TypeCode'";
            if ($SelectedCode == $TypeCode) {$qtypeselecter .= " selected";}
            $qtypeselecter .= ">$TypeDescription</option>\n";
            }
        return $qtypeselecter;
        }
    }

/**
* getNotificationlist() returns different options for notifications
* @param string $notificationcode - the currently selected one
* @return This string is returned containing <option></option> formatted list of notification methods for current survey
*/
function getNotificationlist($notificationcode)
    {
    $ntypes = array(
        "0"=>_("No email notification"),
        "1"=>_("Basic email notification"),
        "2"=>_("Detailed email notification with result codes")
    );
    if (!isset($ntypeselector)) {$ntypeselector="";}
    foreach($ntypes as $ntcode=>$ntdescription)
        {
        $ntypeselector .= "\t\t<option value='$ntcode'";
        if ($notificationcode == $ntcode) {$ntypeselector .= " selected";}
        $ntypeselector .= ">$ntdescription</option>\n";
        }
    return $ntypeselector;
    }

/**
* getgrouplist() queries the database for a list of all groups matching the current survey sid
* @global string $surveyid
* @global string $dbprefix
* @global string $scriptname
* @param string $gid - the currently selected gid/group
* @return This string is returned containing <option></option> formatted list of groups to current survey
*/
function getgrouplist($gid)
    {
    global $surveyid, $dbprefix, $scriptname, $connect;
    $groupselecter="";
    if (!$surveyid) {$surveyid=$_POST['sid'];}
    $gidquery = "SELECT gid, group_name FROM ".db_table_name('groups')." WHERE sid=$surveyid ORDER BY group_name";
    $gidresult = db_execute_num($gidquery) or die("Couldn't get group list in common.php<br />$gidquery<br />".htmlspecialchars($connect->ErrorMsg()));
    while($gv = $gidresult->FetchRow())
            {
            $groupselecter .= "\t\t<option";
            if ($gv[0] == $gid) {$groupselecter .= " selected"; $gvexist = 1;}
            $groupselecter .= " value='$scriptname?sid=$surveyid&amp;gid=$gv[0]'>".htmlspecialchars($gv[1])."</option>\n";
            }
    if ($groupselecter)
	{
        if (!isset($gvexist)) {$groupselecter = "\t\t<option selected>"._("Please Choose...")."</option>\n".$groupselecter;}
        else {$groupselecter .= "\t\t<option value='$scriptname?sid=$surveyid&amp;gid='>"._("None")."</option>\n";}
        }
    return $groupselecter;
    }

function getgrouplist2($gid)
    {
    global $surveyid, $dbprefix, $connect;
    $groupselecter = "";
    if (!$surveyid) {$surveyid=$_POST['sid'];}
    $gidquery = "SELECT gid, group_name FROM ".db_table_name('groups')." WHERE sid=$surveyid ORDER BY group_name";
    $gidresult = db_execute_num($gidquery) or die("Plain old did not work!");
    while ($gv = $gidresult->FetchRow())
            {
            $groupselecter .= "\t\t<option";
            if ($gv[0] == $gid) {$groupselecter .= " selected"; $gvexist = 1;}
            $groupselecter .= " value='$gv[0]'>".htmlspecialchars($gv[1])."</option>\n";
            }
    if ($groupselecter)
 	{
        if (!$gvexist) {$groupselecter = "\t\t<option selected>"._("Please Choose...")."</option>\n".$groupselecter;}
        else {$groupselecter .= "\t\t<option value=''>"._("None")."</option>\n";}
        }
    return $groupselecter;
    }

function getgrouplist3($gid)
    {
    global $surveyid, $dbprefix, $connect;
    if (!$surveyid) {$surveyid=$_POST['sid'];}
        $groupselecter = "";
    $gidquery = "SELECT gid, group_name FROM ".db_table_name('groups')." WHERE sid=$surveyid ORDER BY group_name";
    $gidresult = db_execute_num($gidquery) or die("Plain old did not work!");
    while ($gv = $gidresult->FetchRow())
            {
            $groupselecter .= "\t\t<option";
            if ($gv[0] == $gid) {$groupselecter .= " selected"; $gvexist = 1;}
            $groupselecter .= " value='$gv[0]'>".htmlspecialchars($gv[1])."</option>\n";
            }
    return $groupselecter;
    }

function getuserlist()
    {
    global $dbprefix, $connect;
    $uquery = "SELECT * FROM ".db_table_name('users')." ORDER BY user";
    $uresult = db_execute_assoc($uquery);
    while ($urow = $uresult->FetchRow())
        {
        $userlist[] = array("user"=>$urow['user'], "password"=>$urow['password'], "security"=>$urow['security']);
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
            if (!is_file("$tloc/$file") && $file != "." && $file != "..")
                {
                $list_of_files[] = $file;
                }
            }
        closedir($handle);
        }
    usort($list_of_files, 'StandardSort');
    return $list_of_files;
    }


function getSurveyInfo($surveyid)
    {
    global $dbprefix, $siteadminname, $siteadminemail, $connect;
    $query="SELECT * FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
    $result=db_execute_assoc($query) or die ("Couldn't access surveys<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
    while ($row=$result->FetchRow())
        {
        $thissurvey=array("name"=>$row['short_title'],
                        "description"=>$row['description'],
                        "welcome"=>$row['welcome'],
                        "templatedir"=>$row['template'],
                        "adminname"=>$row['admin'],
                        "adminemail"=>$row['adminemail'],
                        "active"=>$row['active'],
                        "useexpiry"=>$row['useexpiry'],
                        "expiry"=>$row['expires'],
                        "private"=>$row['private'],
                        "faxto"=>$row['faxto'],
                        "template"=>$row['template'],
                        "tablename"=>$dbprefix."survey_".$row['sid'],
                        "url"=>$row['url'],
                        "urldescrip"=>$row['urldescrip'],
                        "format"=>$row['format'],
                        "language"=>$row['language'],
                        "datestamp"=>$row['datestamp'],
                        "ipaddr"=>$row['ipaddr'],
                        "refurl"=>$row['refurl'],
                        "usecookie"=>$row['usecookie'],
                        "sendnotification"=>$row['notification'],
                        "allowregister"=>$row['allowregister'],
                        "attribute1"=>$row['attribute1'],
                        "attribute2"=>$row['attribute2'],
                        "email_invite_subj"=>$row['email_invite_subj'],
                        "email_invite"=>$row['email_invite'],
                        "email_remind_subj"=>$row['email_remind_subj'],
                        "email_remind"=>$row['email_remind'],
                        "email_confirm_subj"=>$row['email_confirm_subj'],
                        "email_confirm"=>$row['email_confirm'],
                        "email_register_subj"=>$row['email_register_subj'],
                        "email_register"=>$row['email_register'],
                        "allowsave"=>$row['allowsave'],
                        "autonumber_start"=>$row['autonumber_start'],
                        "autoredirect"=>$row['autoredirect'],
                        "allowprev"=>$row['allowprev']);
        if (!$thissurvey['adminname']) {$thissurvey['adminname']=$siteadminname;}
        if (!$thissurvey['adminemail']) {$thissurvey['adminemail']=$siteadminemail;}
        if (!$thissurvey['urldescrip']) {$thissurvey['urldescrip']=$thissurvey['url'];}
        }
    return $thissurvey;
    }


function getlabelsets()
    {
    global $dbprefix, $connect;
    $query = "SELECT * FROM ".db_table_name('labelsets')." ORDER BY label_name";
    $result = db_execute_assoc($query) or die ("Couldn't get list of label sets<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
    $labelsets=array();
    while ($row=$result->FetchRow())
        {
        $labelsets[] = array($row['lid'], $row['lid'].": ".$row['label_name']);
        }
    return $labelsets;
    }

function checkactivations()
    {
    global $dbprefix, $connect;
    $tablelist = $connect->MetaTables();
    $tablenames[] = "ListofTables"; //dummy entry because in_array never finds the first one!
    foreach ($tablelist as $tbl)
        {
        $tablenames[] = $tbl;
        }
    $caquery = "SELECT sid FROM ".db_table_name('surveys')." WHERE active='Y'";
    $caresult = db_execute_assoc($caquery);
    if (!$caresult) {return "Database Error";}
    while ($carow = $caresult->FetchRow())
        {
        $surveyname = "{$dbprefix}survey_{$carow['sid']}";
        if (!in_array($surveyname, $tablenames))
            {
            $udquery = "UPDATE ".db_table_name('surveys')." SET active='N' WHERE sid={$carow['sid']}";
            $udresult = $connect->Execute($udquery);
            }
        }
    }

function checksecurity()
    {
    // Check that the names in the htpasswd file match up with the names in the database.
    // Any names missing in htpasswd file should be removed from users table
    global $homedir, $dbprefix, $connect;
    $fname = "$homedir/.htpasswd";
    $htpasswds = file($fname);
    $realusers[] = "DUMMYRECORD"; //dummy entry because in_array never finds the first one!
    foreach ($htpasswds as $htp)
        {
        list ($fuser, $fpass) = split(":", $htp);
        $realusers[] = $fuser;
        }
    $usquery = "SELECT user FROM ".db_table_name('users');
    $usresult = db_execute_assoc($usquery);
    if (!$usresult) {return "Database Error";}
    while ($usrow = $usresult->FetchRow())
        {
        if (!array_search($usrow['user'], $realusers))
            {
            $dlusquery = "DELETE FROM ".db_table_name('users')." WHERE user='{$usrow['user']}'";
            $dlusresult = $connect->Execute($dlusquery);
            }
        }
    }

function checkfortables()
    {
    global $scriptname, $btstyle, $dbprefix, $setfont, $connect;
    $alltables=array("{$dbprefix}surveys",
                     "{$dbprefix}groups",
                     "{$dbprefix}questions",
                     "{$dbprefix}answers",
                     "{$dbprefix}conditions",
                     "{$dbprefix}users",
                     "{$dbprefix}labelsets",
                     "{$dbprefix}labels");
    $tables = $connect->MetaTables();

    foreach($alltables as $at)
        {
        if (!mysql_table_exists($at, $tables))
            {
            $checkfields="Y";
            }
        }
    if (!isset($checkfields)) {$checkfields="";}
    if ($checkfields=="Y")
        {
        echo "<br />\n"
            ."<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
            ."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
            ._("PHPSurveyor Setup")."</strong></td></tr>\n"
            ."\t<tr bgcolor='#CCCCCC'><td align='center'>$setfont\n"
            ."\t\t<font color='red'><strong>"
            ._("Error")."</strong></font><br />\n"
            ."\t\t"
            ._("It appears as if some tables or fields are missing from your database.")."<br /><br />\n"
            ."\t\t<input $btstyle type='submit' value='"
            ._("Check Database Fields")."' onClick=\"window.open('checkfields.php', '_top')\">\n"
            ."\t</td></tr>\n"
            ."</table>\n"
            ."</body></html>\n";
        exit;
        }
    }

function mysql_table_exists($tableName, $tables)
    {
    return(in_array($tableName, $tables));
    }

################################################################################
# Compares two elements from an array (passed by the usort function)
# and returns -1, 0 or 1 depending on the result of the comparison of
# group_name and then title.
function CompareGroupThenTitle($a, $b)
    {
    if (isset($a["group_name"]) && isset($b["group_name"]))
        {
        $GroupResult = strnatcasecmp($a["group_name"], $b["group_name"]);
        }
    else
        {
        $GroupResult = "";
        }
    if ($GroupResult == 0)
        {
        $TitleResult = strnatcasecmp($a["title"], $b["title"]);
        return $TitleResult;
        }
    return $GroupResult;
    }

function StandardSort($a, $b)
    {
    return strnatcasecmp($a, $b);
    }

function conditionscount($qid)
    {
    global $dbprefix, $connect;
    $query="SELECT COUNT(*) FROM ".db_table_name('conditions')." WHERE qid=$qid";
    $result=db_execute_num($query) or die ("Couldn't get conditions<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
    list($count) = $result->FetchRow();
    return $count;
    }

function keycontroljs()
    {
    $kcjs=<<<EOF
<SCRIPT TYPE="text/javascript">
<!--

function getkey(e)
    {
    if (window.event) return window.event.keyCode;
    else if (e) return e.which; else return null;
    }

function goodchars(e, goods)
    {
    var key, keychar;
    key = getkey(e);
    if (key == null) return true;

    // get character
    keychar = String.fromCharCode(key);
    keychar = keychar.toLowerCase();
    goods = goods.toLowerCase();

    // check goodkeys
    if (goods.indexOf(keychar) != -1)
        return true;

    // control keys
    if ( key==null || key==0 || key==8 || key==9 || key==13 || key==27 )
       return true;

    // else return false
    return false;
    }
//-->
</SCRIPT>
EOF;
    return $kcjs;
    }


function fixsortorder($qid) //Function rewrites the sortorder for a group of answers
    {
    global $dbprefix, $connect;
    $cdresult = db_execute_num("SELECT qid, code, answer FROM ".db_table_name('answers')." WHERE qid=? ORDER BY sortorder, answer", $qid);
    $position=0;
    while ($cdrow=$cdresult->FetchRow())
        {
        $position=sprintf("%05d", $position);
        $cd2query="UPDATE ".db_table_name('answers')." SET sortorder=? WHERE qid=? AND code=? AND answer=?";
        $cd2result=$connect->Execute($cd2query, $position, $cdrow[0], $cdrow[1], $cdrow[2]) or die ("Couldn't update sortorder<br />$cd2query<br />".htmlspecialchars($connect->ErrorMsg()));
        $position++;
        }
    }

function browsemenubar()
    {
    global $surveyid, $scriptname, $imagefiles, $homeurl;
    //BROWSE MENU BAR
    if (!isset($surveyoptions)) {$surveyoptions="";}
    $surveyoptions .= "\t<tr bgcolor='#999999'>\n"
                    . "\t\t<td>\n"
                    . "\t\t\t<input type='image' name='Administration' src='$imagefiles/home.png' title='"
                    . _("Return to Survey Administration")."' alt='". _("Return to Survey Administration")."' align='left' onClick=\"window.open('$scriptname?sid=$surveyid', '_top')\">\n"
                    . "\t\t\t<img src='$imagefiles/blank.gif' alt='' width='11'  align='left'>\n"
                    . "\t\t\t<img src='$imagefiles/seperator.gif' alt=''  align='left'>\n"
                    . "\t\t\t<input type='image' name='SurveySummary' src='$imagefiles/summary.png' title='"
                    . _("Show summary information")."' align='left' onClick=\"window.open('browse.php?sid=$surveyid', '_top')\">\n"
                    . "\t\t\t<input type='image' name='ViewAll' src='$imagefiles/document.png' title='"
                    . _("Display Responses")."' align='left' onClick=\"window.open('browse.php?sid=$surveyid&amp;action=all', '_top')\">\n"
                    . "\t\t\t<input type='image' name='ViewLast' src='$imagefiles/viewlast.png' title='"
                    . _("Display Last 50 Responses")."' align='left' onClick=\"window.open('browse.php?sid=$surveyid&amp;action=all&amp;limit=50&amp;order=desc', '_top')\">\n"
                    . "\t\t\t<input type='image' name='DataEntry' src='$imagefiles/dataentry.png' title='"
                    . _("Dataentry Screen for Survey")."' align='left'  onclick=\"window.open('dataentry.php?sid=$surveyid', '_top')\">\n"
                    . "\t\t\t<input type='image' name='Printable' src='$imagefiles/print.png' title='"
                    . _("Printable Version of Survey")."' align='left'  onclick=\"window.open('printablesurvey.php?sid=$surveyid', '_blank')\">\n"
                    . "\t\t\t<input type='image' name='Statistics' src='$imagefiles/statistics.png' title='"
                    . _("Get statistics from these responses")."' align='left'  onclick=\"window.open('statistics.php?sid=$surveyid', '_top')\">\n"
                    . "\t\t\t<img src='$imagefiles/seperator.gif' alt=''  align='left'>\n"
                    . "\t\t\t<input type='image' name='Export' src='$imagefiles/export.png' title='"
                    . _("Export Results to Application")."' alt='". _("Export Results to Application")."'align='left'  onclick=\"window.open('export.php?sid=$surveyid', '_blank')\">\n"
                    . "\t\t\t<a href='spss.php?sid=$surveyid'><img src='$imagefiles/exportspss.png' align='left' title='"
                    . _("Export result to a SPSS command file")."' border='0' alt='". _("Export result to a SPSS command file")."'></a>\n"
                    . "\t\t\t<input type='image' name='Export' src='$imagefiles/importold.png' title='"
                    . _("Import answers from a deactivated survey table")."' alt='". _("Import answers from a deactivated survey table")."'align='left'  onclick=\"window.open('importoldresponses.php?sid=$surveyid', '_blank')\">\n"
                    . "\t\t\t<img src='$imagefiles/seperator.gif' alt=''  align='left'>\n"
                    . "\t\t\t<input type='image' name='SaveDump' src='$imagefiles/exportsql.png' title='"
                    . _("Backup results table as SQL file")."' align='left'  onclick=\"window.open('resultsdump.php?sid=$surveyid', '_top')\">\n"
                    . "\t\t\t<img src='$imagefiles/seperator.gif' alt=''  align='left'>\n"
                    . "\t\t\t<input type='image' src='$imagefiles/saved.png' title='"._("View Saved but not submitted Responses")."' "
                    . "align='left'  name='BrowseSaved' "
                    . "onclick=\"window.open('".$homeurl."/saved.php?sid=$surveyid', '_top')\">\n"
                    . "\t\t\t<a href='vvexport.php?sid=$surveyid'>\n"
                    . "\t\t\t<a href='vvimport.php?sid=$surveyid'><img src='$imagefiles/importvv.png' align='left' title='"
                    . _("Import a VV survey file")."' border='0' alt='". _("Import a VV survey file")."'></a>\n"
                    . "\t\t\t<a href='vvexport.php?sid=$surveyid'><img src='$imagefiles/exportvv.png' align='left' title='"
                    . _("Export a VV survey file")."' alt='". _("Export a VV survey file")."' border='0'></a>\n"
                    . "\t\t</td>\n"
                    . "\t</tr>\n";
    return $surveyoptions;
    }

function returnglobal($stringname)
    {
    if (_PHPVERSION < "4.1.0")
        {
            if (isset($HTTP_GET_VARS[$stringname]))
            {
                if ($stringname == "sid" || $stringname == "gid" || $stringname == "qid")
                {
                    return intval($HTTP_GET_VARS[$stringname]);
                }
                return $HTTP_GET_VARS[$stringname];
            }
            elseif (isset($HTTP_POST_VARS[$stringname]))
            {
                if ($stringname == "sid" || $stringname == "gid" || $stringname == "qid")
                {
                    return intval($HTTP_POST_VARS[$stringname]);
                }
                return $HTTP_POST_VARS[$stringname];
            }
        }
    else
        {
        if (isset($_GET[$stringname]))
        {
                if ($stringname == "sid" || $stringname == "gid" || $stringname == "qid")
                {
                    return intval($_GET[$stringname]);
                }
            return $_GET[$stringname];
        }
        elseif (isset($_POST[$stringname]))
        {
                if ($stringname == "sid" || $stringname == "gid" || $stringname == "qid")
                {
                    return intval($_POST[$stringname]);
                }
            return $_POST[$stringname];
        }
        }
    }

function sendcacheheaders()
    {
    global $embedded;
    if ( $embedded ) return;
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // always modified
    header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header('Content-Type: text/html; charset=utf-8');
    }

function getLegitQids($surveyid)
    {
    global $dbprefix;
    $lq = "SELECT DISTINCT qid FROM ".db_table_name('questions')." WHERE sid=$surveyid"; //GET LIST OF LEGIT QIDs FOR TESTING LATER
    $lr = db_execute_num($lq);
    return array_merge(array("DUMMY ENTRY"), $lr->GetRows());
    }

function returnquestiontitlefromfieldcode($fieldcode)
    {
    if (!isset($fieldcode)) {return "Preset";}
    if ($fieldcode == "token") {return "Token";}
    if ($fieldcode == "datestamp") {return "Date Stamp";}
    if ($fieldcode == "ipaddr") {return "IP Address";}
    if ($fieldcode == "refurl") {return "Referring URL";}
    global $dbprefix, $surveyid, $connect;

    //Find matching information;
    $detailsarray=arraySearchByKey($fieldcode, createFieldMap($surveyid), "fieldname");
    foreach ($detailsarray as $dt) {
        $details=$dt;
    }

    $fqid=$details['qid'];
    $qq = "SELECT question, other FROM ".db_table_name('questions')." WHERE qid=$fqid";

    $qr = db_execute_assoc($qq);
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
    if (isset($details['aid']) && $details['aid']) //Add answer if necessary (array type questions)
        {
        $qq = "SELECT answer FROM ".db_table_name('answers')." WHERE qid=$fqid AND code='{$details['aid']}'";
        $qr = db_execute_assoc($qq) or die ("ERROR: ".htmlspecialchars($connect->ErrorMsg())."<br />$qq");
        while($qrow=$qr->FetchRow())
            {
            $qname.=" [".$qrow['answer']."]";
            }
        }
    if (substr($fieldcode, -5) == "other")
        {
        $qname .= " [Other]";
        }
    return $qname;
    }

function getsidgidqid($fieldcode)
    {
    list($fsid, $fgid, $fqid) = split("X", $fieldcode);
    $legitqs=getLegitQids($fsid);
    if (!$fqid) {$fqid=0;}
    $oldfqid=$fqid;
    while(!in_array($fqid, $legitqs))
        {
        $fqid=substr($fqid, 0, strlen($fqid)-1);
        }
    if (strlen($fqid) < strlen($oldfqid))
        {
        $faid=substr($oldfqid, strlen($fqid), strlen($oldfqid)-strlen($fqid));
        $oldfqid="";
        }
    else
        {
        $faid="";
        }
    return array("sid"=>$fsid, "gid"=>$fgid, "qid"=>$fqid, "aid"=>$faid);
    }

/*
*
*
*/
function getextendedanswer($fieldcode, $value)
    {
    global $dbprefix, $surveyid, $connect;
    //Fieldcode used to determine question, $value used to match against answer code
    //Returns NULL if question type does not suit
    if (substr_count($fieldcode, "X") > 1) //Only check if it looks like a real fieldcode
        {
        $detailsarray=arraySearchByKey($fieldcode, createFieldMap($surveyid), "fieldname");
		foreach ($detailsarray as $dt) {
            $fields=$dt;
        }
        //Find out the question type
        $query = "SELECT type, lid FROM ".db_table_name('questions')." WHERE qid={$fields['qid']}";
        $result = db_execute_assoc($query) or die ("Couldn't get question type - getextendedanswer() in common.php<br />".htmlspecialchars($connect->ErrorMsg()));
        while($row=$result->FetchRow())
            {
            $this_type=$row['type'];
            $this_lid=$row['lid'];
            } // while
        switch($this_type)
            {
            case "L":
            case "!":
            case "O":
			case "^":
			case "I":
            case "R":
                $query = "SELECT code, answer FROM ".db_table_name('answers')." WHERE qid={$fields['qid']} AND code='".mysql_escape_string($value)."'";
                $result = db_execute_assoc($query, $fields['qid'], $value) or die ("Couldn't get answer type L - getextendedanswer() in common.php<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
                while($row=$result->FetchRow())
                    {
                    $this_answer=$row['answer'];
                    } // while
                if ($value == "-oth-")
                    {
                    $this_answer=_("Other");
                    }
                break;
            case "M":
			case "J":
            case "P":
                switch($value)
                    {
                    case "Y": $this_answer=_("Yes"); break;
                    }
                break;
            case "Y":
                switch($value)
                    {
                    case "Y": $this_answer=_("Yes"); break;
                    case "N": $this_answer=_("No"); break;
                    default: $this_answer=_NOANSWER;
                    }
                break;
            case "G":
                switch($value)
                    {
                    case "M": $this_answer=_MALE; break;
                    case "F": $this_answer=_FEMALE; break;
                    default: $this_answer=_NOANSWER;
                    }
                break;
            case "C":
                switch($value)
                    {
                    case "Y": $this_answer=_("Yes"); break;
                    case "N": $this_answer=_("No"); break;
                    case "U": $this_answer=_("Uncertain"); break;
                    }
                break;
            case "E":
                switch($value)
                    {
                    case "I": $this_answer=_INCREASE; break;
                    case "D": $this_answer=_DECREASE; break;
                    case "S": $this_answer=_SAME; break;
                    }
                break;
            case "F":
            case "H":
            case "W":
            case "Z":
                $query = "SELECT title FROM ".db_table_name('labels')." WHERE lid=$this_lid AND code='$value'";
                $result = db_execute_assoc($query) or die ("Couldn't get answer type F/H - getextendedanswer() in common.php");
                while($row=$result->FetchRow())
                    {
                    $this_answer=$row['title'];
                    } // while
                break;
            default:
                ;
            } // switch
        }
    if (isset($this_answer))
        {
        return $this_answer." [$value]";
        }
    else
        {
        return $value;
        }
    }

function validate_email($email)
    {
    // Create the syntactical validation regular expression
    $regexp = "^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";
    // Validate the syntax
    return (eregi($regexp, $email));
    }
    
function crlf_lineendings($text)
    {
    $text=str_replace("\r\n", "~~~~", $text); //First replace any good line endings with ~~~~
    $text=str_replace("\n", "~~~~", $text); //Then replace any solitary \n's with ~~~~
    $text=str_replace("\r", "~~~~", $text); //Then replace any solitary \r's with ~~~~
    $text=str_replace("~~~~", "\r\n", $text); //Finally replace all ~~~~'s with \r\n
    return $text;
    }

function createFieldMap($surveyid, $style="null") {
    //This function generates an array containing the fieldcode, and matching data in the same
    //order as the activate script
    global $dbprefix, $connect;
    //Check for any additional fields for this survey and create necessary fields (token and datestamp and ipaddr)
    $pquery = "SELECT private, datestamp, ipaddr, refurl FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
    $presult=db_execute_assoc($pquery);
    $counter=0;
    while($prow=$presult->FetchRow())
        {
        if ($prow['private'] == "N")
            {
            $fieldmap[]=array("fieldname"=>"token", "type"=>"", "sid"=>$surveyid, "gid"=>"", "qid"=>"", "aid"=>"");
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
            $fieldmap[]=array("fieldname"=>"datestamp", "type"=>"", "sid"=>$surveyid, "gid"=>"", "qid"=>"", "aid"=>"");
            if ($style == "full")
                {
                $fieldmap[$counter]['title']="";
                $fieldmap[$counter]['question']="datestamp";
                $fieldmap[$counter]['group_name']="";
                }
            $counter++;
            }
	if ($prow['ipaddr'] == "Y")
            {
            $fieldmap[]=array("fieldname"=>"ipaddr", "type"=>"", "sid"=>$surveyid, "gid"=>"", "qid"=>"", "aid"=>"");
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
            $fieldmap[]=array("fieldname"=>"refurl", "type"=>"", "sid"=>$surveyid, "gid"=>"", "qid"=>"", "aid"=>"");
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
    $aquery = "SELECT * FROM ".db_table_name('questions').", ".db_table_name('groups')." WHERE ".db_table_name('questions').".gid=".db_table_name('groups').".gid AND ".db_table_name('questions').".sid=$surveyid ORDER BY group_name, title";
    $aresult = db_execute_assoc($aquery) or die ("Couldn't get list of questions in createFieldMap function.<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
    while ($arow=$aresult->FetchRow()) //With each question, create the appropriate field(s)
        {
        if ($arow['type'] != "M" && $arow['type'] != "A" && $arow['type'] != "B" && 
			$arow['type'] !="C" && $arow['type'] != "E" && $arow['type'] != "F" && 
			$arow['type'] != "H" && $arow['type'] !="P" && $arow['type'] != "R" && 
			$arow['type'] != "Q" && $arow['type'] != "J" && $arow['type'] != "^")
            {
            $fieldmap[]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}", "type"=>"{$arow['type']}", "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>"");
            if ($style == "full")
                {
                $fieldmap[$counter]['title']=$arow['title'];
                $fieldmap[$counter]['question']=$arow['question'];
                $fieldmap[$counter]['group_name']=$arow['group_name'];
                }
            $counter++;
            switch($arow['type'])
                {
                case "L":  //DROPDOWN LIST
                case "!":
                    if ($arow['other'] == "Y")
                        {
                        $fieldmap[]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other",
                                          "type"=>$arow['type'],
                                          "sid"=>$surveyid,
                                          "gid"=>$arow['gid'],
                                          "qid"=>$arow['qid'],
                                          "aid"=>"other");
                        // dgk bug fix line above. aid should be set to "other" for export to append to the field name in the header line.
                        if ($style == "full")
                            {
                            $fieldmap[$counter]['title']=$arow['title'];
                            $fieldmap[$counter]['question']=$arow['question']."["._("Other")."]";
                            $fieldmap[$counter]['group_name']=$arow['group_name'];
                            }
                        $counter++;
                        }
                    break;
                case "O": //DROPDOWN LIST WITH COMMENT
                    $fieldmap[]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}comment",
                                      "type"=>$arow['type'],
                                      "sid"=>$surveyid,
                                      "gid"=>$arow['gid'],
                                      "qid"=>$arow['qid'],
                                      "aid"=>"comment");
                    // dgk bug fix line below. aid should be set to "comment" for export to append to the field name in the header line. Also needed set the type element correctly.
                    if ($style == "full")
                        {
                        $fieldmap[$counter]['title']=$arow['title'];
                        $fieldmap[$counter]['question']=$arow['question']."["._("Comment")."]";
                        $fieldmap[$counter]['group_name']=$arow['group_name'];
                        }
                    $counter++;
                    break;
                }
            }
        elseif ($arow['type'] == "M" || $arow['type'] == "A" || $arow['type'] == "B" || 
				$arow['type'] == "C" || $arow['type'] == "E" || $arow['type'] == "F" || 
				$arow['type'] == "H" || $arow['type'] == "P" || $arow['type'] == "^" || $arow['type'] == "J")
            {
            //MULTI ENTRY
            $abquery = "SELECT ".db_table_name('answers').".*, ".db_table_name('questions').".other FROM ".db_table_name('answers').", ".db_table_name('questions')." WHERE ".db_table_name('answers').".qid=".db_table_name('questions').".qid AND sid=$surveyid AND ".db_table_name('questions').".qid={$arow['qid']} ORDER BY ".db_table_name('answers').".sortorder, ".db_table_name('answers').".answer";
            $abresult=db_execute_assoc($abquery) or die ("Couldn't get list of answers in createFieldMap function (case M/A/B/C/E/F/H/P)<br />$abquery<br />".htmlspecialchars($connect->ErrorMsg()));
            while ($abrow=$abresult->FetchRow())
                {
                $fieldmap[]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}", "type"=>$arow['type'], "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$abrow['code']);
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
                    $fieldmap[]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}comment", "type"=>$arow['type'], "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>"comment");
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
                $fieldmap[]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other", "type"=>$arow['type'], "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>"other");
                if ($style == "full")
                    {
                    $fieldmap[$counter]['title']=$arow['title'];
                    $fieldmap[$counter]['question']=$arow['question']."["._("Other")."]";
                    $fieldmap[$counter]['group_name']=$arow['group_name'];
                    }
                $counter++;
                if ($arow['type']=="P")
                    {
                    $fieldmap[]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}othercomment", "type"=>$arow['type'], "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>"othercomment");
                    if ($style == "full")
                        {
                        $fieldmap[$counter]['title']=$arow['title'];
                        $fieldmap[$counter]['question']=$arow['question']."["._("Other")."comment]";
                        $fieldmap[$counter]['group_name']=$arow['group_name'];
                        }
                    $counter++;
                    }
                }
            }
        elseif ($arow['type'] == "Q")
            {
            $abquery = "SELECT ".db_table_name('answers').".*, ".db_table_name('questions').".other FROM ".db_table_name('answers').", ".db_table_name('questions')." WHERE ".db_table_name('answers').".qid=".db_table_name('questions').".qid AND sid=$surveyid AND ".db_table_name('questions').".qid={$arow['qid']} ORDER BY ".db_table_name('answers').".sortorder, ".db_table_name('answers').".answer";
            $abresult=db_execute_assoc($abquery) or die ("Couldn't get list of answers in createFieldMap function (type Q)<br />$abquery<br />".htmlspecialchars($connect->ErrorMsg()));
            while ($abrow=$abresult->FetchRow())
                {
                $fieldmap[]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}", "type"=>$arow['type'], "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$abrow['code']);
                if ($style == "full")
                    {
                    $fieldmap[$counter]['title']=$arow['title'];
                    $fieldmap[$counter]['question']=$arow['question']."[".$abrow['answer']."]";
                    $fieldmap[$counter]['group_name']=$arow['group_name'];
                    }
                $counter++;
                }
            }
        elseif ($arow['type'] == "R")
            {
            //MULTI ENTRY
            $abquery = "SELECT ".db_table_name('answers').".*, ".db_table_name('questions').".other FROM ".db_table_name('answers').", ".db_table_name('questions')." WHERE ".db_table_name('answers').".qid=".db_table_name('questions').".qid AND sid=$surveyid AND ".db_table_name('questions').".qid={$arow['qid']} ORDER BY ".db_table_name('answers').".sortorder, ".db_table_name('answers').".answer";
            $abresult=$connect->Execute($abquery) or die ("Couldn't get list of answers in createFieldMap function (type R)<br />$abquery<br />".htmlspecialchars($connect->ErrorMsg()));
            $abcount=$abresult->RecordCount();
            for ($i=1; $i<=$abcount; $i++)
                {
                $fieldmap[]=array("fieldname"=>"{$arow['sid']}X{$arow['gid']}X{$arow['qid']}$i", "type"=>$arow['type'], "sid"=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$i);
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
    if (isset($fieldmap)) {return $fieldmap;}
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

function templatereplace($line)
    {
    global $thissurvey;

    global $percentcomplete;

    global $groupname, $groupdescription, $question;
    global $questioncode, $answer, $navigator;
    global $help, $totalquestions, $surveyformat;
    global $completed, $register_errormsg;
    global $notanswered, $privacy, $surveyid;
    global $publicurl, $templatedir, $token;
    global $assessments;
    global $errormsg;

    //Set up save/load feature
    if ($thissurvey['allowsave'] == "Y")
        {
            if (!isset($_SESSION['step']) || !$_SESSION['step'])
                {
                $saveall = "<input type='submit' name='loadall' value='"._LOAD_SAVED."' class='saveall'>";
                }
            elseif ($_SESSION['step'] <= $_SESSION['totalsteps'])  //Also modified so Save So Far shows up on last page.
                {
               	$saveall = "<input type='submit' name='saveall' value='"._SAVE_AND_RETURN."' class='saveall'>";
                }
            else
                {
                $saveall="";
                }
        }
    else
        {
        $saveall="";
        }

    if ($thissurvey['templatedir']) {$templateurl="$publicurl/templates/{$thissurvey['templatedir']}/";}
    else {$templateurl="$publicurl/templates/default/";}

    $clearall = "\t\t\t\t\t<div class='clearall'>"
              . "<a href='{$_SERVER['PHP_SELF']}?sid=$surveyid&amp;move=clearall";
    if (returnglobal('token'))
        {
        $clearall .= "&amp;token=".returnglobal('token');
        }
    $clearall .="' onClick='return confirm(\""
              . _CONFIRMCLEAR."\")'>["
              . _EXITCLEAR."]</a></div>\n";

	if (ereg("^</head>", $line))
        {
            $line=str_replace("</head>",
 				 "<script type=\"text/javascript\"><!-- \n"
				."var DOM1;\n"
				."window.onload=function() {\n"
				."  DOM1 = (typeof document.getElementsByTagName!='undefined');\n"
				."  if (typeof checkconditions!='undefined') checkconditions();\n"
				."  if (typeof template_onload!='undefined') template_onload();\n"
				."}\n"
				."//--></script>\n"
				."</head>"
			,$line);
        }

    $line=str_replace("{SURVEYNAME}", $thissurvey['name'], $line);
    $line=str_replace("{SURVEYDESCRIPTION}", $thissurvey['description'], $line);
    $line=str_replace("{WELCOME}", $thissurvey['welcome'], $line);
    $line=str_replace("{PERCENTCOMPLETE}", $percentcomplete, $line);
    $line=str_replace("{GROUPNAME}", $groupname, $line);
    $line=str_replace("{GROUPDESCRIPTION}", $groupdescription, $line);
    $line=str_replace("{QUESTION}", $question, $line);
    $line=str_replace("{QUESTION_CODE}", $questioncode, $line);
    $line=str_replace("{ANSWER}", $answer, $line);
    if ($totalquestions < 2)
        {
        $line=str_replace("{THEREAREXQUESTIONS}", _THEREAREXQUESTIONS_SINGLE, $line); //Singular
        }
    else
        {
        $line=str_replace("{THEREAREXQUESTIONS}", _THEREAREXQUESTIONS, $line); //Note this line MUST be before {NUMBEROFQUESTIONS}
        }
    $line=str_replace("{NUMBEROFQUESTIONS}", $totalquestions, $line);
    if (isset($token)) {
    	$line=str_replace("{TOKEN}", $token, $line);
		}
	elseif (isset($_POST['token'])) {
    		$line=str_replace("{TOKEN}", $_POST['token'], $line);
		 }
	else {
    		$line=str_replace("{TOKEN}",'', $line);
		 }
    $line=str_replace("{SID}", $surveyid, $line);
    if ($help) {
        $line=str_replace("{QUESTIONHELP}", "<img src='".$publicurl."/help.gif' alt='Help' align='left'>".$help, $line);
        $line=str_replace("{QUESTIONHELPPLAINTEXT}", strip_tags(addslashes($help)), $line);
    }
    else
        {
        $line=str_replace("{QUESTIONHELP}", $help, $line);
        $line=str_replace("{QUESTIONHELPPLAINTEXT}", strip_tags(addslashes($help)), $line);
        }
    $line=str_replace("{NAVIGATOR}", $navigator, $line);
    $submitbutton="<input class='submit' type='submit' value=' "._SUBMIT." ' name='move2' onclick=\"javascript:document.phpsurveyor.move.value = this.value;\">";
    $line=str_replace("{SUBMITBUTTON}", $submitbutton, $line);
    $line=str_replace("{COMPLETED}", $completed, $line);
    if ($thissurvey['url']!=""){$linkreplace="<a href='{$thissurvey['url']}'>{$thissurvey['urldescrip']}</a>";}
	else {$linkreplace="";}
    $line=str_replace("{URL}", $linkreplace, $line);
    $line=str_replace("{PRIVACY}", $privacy, $line);
    $line=str_replace("{PRIVACYMESSAGE}", _PRIVACY_MESSAGE, $line);
    $line=str_replace("{CLEARALL}", $clearall, $line);

// --> START NEW FEATURE - SAVE
    if (isset($_SESSION['datestamp']))
    	{
    	$line=str_replace("{DATESTAMP}", $_SESSION['datestamp'], $line);
    	}
    else
    	{
    	$line=str_replace("{DATESTAMP}", "-", $line);
    	}
// <-- END NEW FEATURE - SAVE

    $line=str_replace("{SAVE}", $saveall, $line);
    $line=str_replace("{TEMPLATEURL}", $templateurl, $line);
    $line=str_replace("{SUBMITCOMPLETE}", _SM_COMPLETED, $line);
    $strreview=_SM_REVIEW;
    if (isset($thissurvey['allowprev']) && $thissurvey['allowprev'] == "N") {$strreview = "";}
    $line=str_replace("{SUBMITREVIEW}", $strreview, $line);

    if (isset($_SESSION['thistoken']))
        {
        $line=str_replace("{TOKEN:FIRSTNAME}", $_SESSION['thistoken']['firstname'], $line);
        $line=str_replace("{TOKEN:LASTNAME}", $_SESSION['thistoken']['lastname'], $line);
        $line=str_replace("{TOKEN:EMAIL}", $_SESSION['thistoken']['email'], $line);
        $line=str_replace("{TOKEN:ATTRIBUTE_1}", $_SESSION['thistoken']['attribute_1'], $line);
        $line=str_replace("{TOKEN:ATTRIBUTE_2}", $_SESSION['thistoken']['attribute_2'], $line);
        }
        else
        {
        $line=str_replace("{TOKEN:FIRSTNAME}", "", $line);
        $line=str_replace("{TOKEN:LASTNAME}", "", $line);
        $line=str_replace("{TOKEN:EMAIL}", "", $line);
        $line=str_replace("{TOKEN:ATTRIBUTE_1}", "", $line);
        $line=str_replace("{TOKEN:ATTRIBUTE_2}", "", $line);
        }


    $line=str_replace("{ANSWERSCLEARED}", _ANSCLEAR, $line);
    $line=str_replace("{RESTART}",  "<a href='{$_SERVER['PHP_SELF']}?sid=$surveyid&amp;token=".returnglobal('token')."'>"._RESTART."</a>", $line);
    $line=str_replace("{CLOSEWINDOW}", "<a href='javascript:%20self.close()'>"._CLOSEWIN_PS."</a>", $line);

    //SAVE SURVEY DETAILS
    $saveform = "<table><tr><td align='right'>"._SAVENAME.":</td><td><input type='text' name='savename' value='";
    if (isset($_POST['savename'])) {$saveform .= html_escape(auto_unescape($_POST['savename']));}
    $saveform .= "'></td></tr>\n"
           . "<tr><td align='right'>"._SAVEPASSWORD."</td><td><input type='password' name='savepass' value='";
    if (isset($_POST['savepass'])) {$saveform .= html_escape(auto_unescape($_POST['savepass']));}
    $saveform .= "'></td></tr>\n"
           . "<tr><td align='right'>"._SAVEPASSWORDRPT."</td><td><input type='password' name='savepass2' value='";
    if (isset($_POST['savepass2'])) {$saveform .= html_escape(auto_unescape($_POST['savepass2']));}
    $saveform .= "'></td></tr>\n"
           . "<tr><td align='right'>"._SAVE_EMAIL."</td><td><input type='text' name='saveemail' value='";
    if (isset($_POST['saveemail'])) {$saveform .= html_escape(auto_unescape($_POST['saveemail']));}
    $saveform .= "'></td></tr>\n"
           . "<tr><td></td><td><input type='submit' name='savesubmit' value='"._SAVESUBMIT."'></td></tr>\n"
           . "</table>";
    $savereturn = "<a href='index.php?sid=$surveyid";
    if (returnglobal('token'))
        {
        $savereturn.= "&amp;token=".returnglobal('token');
        }
    $savereturn .= "'>"._RETURNTOSURVEY."</a>";
    $line=str_replace("{SAVEERROR}", $errormsg, $line);
    $line=str_replace("{SAVEHEADING}", _SAVEHEADING, $line);
    $line=str_replace("{SAVEMESSAGE}", _SAVEEXPLANATION, $line);
    $line=str_replace("{RETURNTOSURVEY}", $savereturn, $line);
    $line=str_replace("{SAVEFORM}", $saveform, $line);

    //LOAD SURVEY DETAILS
    $loadform = "<table><tr><td align='right'>"._LOADNAME.":</td><td><input type='text' name='loadname' value='";
    if (isset($_POST['loadname'])) {$loadform .= html_escape(auto_unescape($_POST['loadname']));}
    $loadform .= "'></td></tr>\n"
           . "<tr><td align='right'>"._LOADPASSWORD.":</td><td><input type='password' name='loadpass' value='";
    if (isset($_POST['loadpass'])) {$loadform .= html_escape(auto_unescape($_POST['loadpass']));}
    $loadform .= "'></td></tr>\n"
           . "<tr><td></td><td><input type='submit' value='"._LOADSUBMIT."'></td></tr></table>\n";
    $line=str_replace("{LOADERROR}", $errormsg, $line);
    $line=str_replace("{LOADHEADING}", _LOADHEADING, $line);
    $line=str_replace("{LOADMESSAGE}", _LOADEXPLANATION, $line);
    $line=str_replace("{LOADFORM}", $loadform, $line);

    //REGISTER SURVEY DETAILS
    $line=str_replace("{REGISTERERROR}", $register_errormsg, $line);
    $line=str_replace("{REGISTERMESSAGE1}", _RG_REGISTER1, $line);
    $line=str_replace("{REGISTERMESSAGE2}", _RG_REGISTER2, $line);
    if (strpos($line, "{REGISTERFORM}") !== false)
        {
        $registerform="<table class='register'>\n"
            ."<form method='post' action='register.php'>\n"
            ."<input type='hidden' name='sid' value='$surveyid' id='sid'>\n"
            ."<tr><td align='right'>"
            ._RG_FIRSTNAME.":</td>"
            ."<td align='left'><input class='text' type='text' name='register_firstname'";
        if (isset($_POST['register_firstname']))
            {
            $registerform .= " value='".returnglobal('register_firstname')."'";
                }
        $registerform .= "></td></tr>"
            ."<tr><td align='right'>"._RG_LASTNAME.":</td>\n"
            ."<td align='left'><input class='text' type='text' name='register_lastname'";
        if (isset($_POST['register_lastname']))
            {
            $registerform .= " value='".returnglobal('register_lastname')."'";
            }
        $registerform .= "></td></tr>\n"
            ."<tr><td align='right'>"._RG_EMAIL.":</td>\n"
            ."<td align='left'><input class='text' type='text' name='register_email'";
        if (isset($_POST['register_email']))
            {
            $registerform .= " value='".returnglobal('register_email')."'";
            }
        $registerform .= "></td></tr>\n";
        if(isset($thissurvey['attribute1']) && $thissurvey['attribute1'])
            {
            $registerform .= "<tr><td align='right'>".$thissurvey['attribute1'].":</td>\n"
                ."<td align='left'><input class='text' type='text' name='register_attribute1'";
            if (isset($_POST['register_attribute1']))
                {
                $registerform .= " value='".returnglobal('register_attribute1')."'";
                }
            $registerform .= "></td></tr>\n";
            }
        if(isset($thissurvey['attribute2']) && $thissurvey['attribute2'])
            {
            $registerform .= "<tr><td align='right'>".$thissurvey['attribute2'].":</td>\n"
                ."<td align='left'><input class='text' type='text' name='register_attribute2'";
            if (isset($_POST['register_attribute2']))
                {
                $registerform .= " value='".returnglobal('register_attribute2')."'";
                }
            $registerform .= "></td></tr>\n";
            }
        $registerform .= "<tr><td></td><td><input class='submit' type='submit' value='"._CONTINUE_PS."'>"
            ."</td></tr>\n"
            ."</form>\n"
            ."</table>\n";
        $line=str_replace("{REGISTERFORM}", $registerform, $line);
        }
    $line=str_replace("{ASSESSMENTS}", $assessments, $line);
    $line=str_replace("{ASSESSMENT_HEADING}", _ASSESSMENT_HEADING, $line);
    return $line;
    }

function getSavedCount($surveyid)
    {
    //This function returns a count of the number of saved responses to a survey
    global $dbprefix, $connect;
    $query = "SELECT COUNT(*) FROM ".db_table_name('saved_control')." WHERE sid=$surveyid";
    $result=db_execute_num($query) or die ("Couldn't get saved summary<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
    list($count) = $result->FetchRow();
    return $count;
    }

function GetLanguageFromSurveyID($surveyid)
    {
    global $connect;
    //This function loads the local language file applicable to a survey
        $query = "SELECT language FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
        $result = db_execute_num($query) or die ("Couldn't get language file");
        while ($row=$result->FetchRow()) {$surveylanguage = $row[0];}
        return $surveylanguage;
    }

function SetInterfaceLanguage($languagetoset)
    {
    @$locale = (isset($_GET['lang']))? $_GET['lang'] : DEFAULT_LOCALE;
    if (function_exists('T_setlocale')) T_setlocale(LC_MESSAGES, $locale);
    @setlocale(LC_ALL, $languagetoset);
    @putenv("LANG=$languagetoset");
    @putenv("LANGUAGE=$languagetoset");

     bindtextdomain($languagetoset, dirname(__FILE__).'/locale');
     if (!function_exists('bind_textdomain_codeset')) echo "You need at least PHP 4.2.x to run PHPSurveyor." and die;
     bind_textdomain_codeset($languagetoset,'UTF-8');
     return textdomain($languagetoset);
    }


function buildLabelsetCSArray()
    {
    global $dbprefix, $connect;
    // BUILD CHECKSUMS FOR ALL EXISTING LABEL SETS
    $query = "SELECT lid
              FROM ".db_table_name('labelsets')."
              ORDER BY lid";
    $result = db_execute_assoc($query) or die("Died collecting labelset ids<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
    while ($row=$result->FetchRow())
        {
        $thisset="";
        $query2 = "SELECT code, title, sortorder
                   FROM ".db_table_name('labels')."
                   WHERE lid={$row['lid']}
                   ORDER BY sortorder, code";
        $result2 = db_execute_num($query2) or die("Died querying labelset $lid<br />$query2<br />".htmlspecialchars($connect->ErrorMsg()));
        while($row2=$result2->FetchRow())
            {
	    $thisset .= implode('', $row2);
            } // while
        $csarray[$row['lid']]=dechex(crc32($thisset)*1);
        }
    return $csarray;
    }

function getQuestionAttributes($qid)
    {
    global $dbprefix, $connect;
    $query = "SELECT * FROM ".db_table_name('question_attributes')." WHERE qid=$qid";
    $result = db_execute_assoc($query) or die("Error finding question attributes");
    $qid_attributes=array();
    while ($row=$result->FetchRow())
        {
        $qid_attributes[]=$row;
        }
    //echo "<pre>";print_r($qid_attributes);echo "</pre>";
    return $qid_attributes;
    }

function questionAttributes()
    {
    //For each question attribute include a key:
    // name - the display name
    // types - a string with one character representing each question typ to which the attribute applies
    // help - a short explanation
    $qattributes[]=array("name"=>"display_columns",
                         "types"=>"LMZ",
                         "help"=>"Number of columns to display");
    $qattributes[]=array("name"=>"maximum_chars",
                         "types"=>"STUN",
                         "help"=>"Maximum Characters Allowed");
    $qattributes[]=array("name"=>"permission",
                         "types"=>"5DGL!OMPQNRSTUYABCEFHWZ",
                         "help"=>"Flexible attribute for permissions");
// --> START ENHANCEMENT - TEXT INPUT WIDTH
//    --> START ORIGINAL
//    $qattributes[]=array("name"=>"text_input_width",
//                         "types"=>"SN",
//                         "help"=>"Width of text input box");
//    --> END ORIGINAL
    $qattributes[]=array("name"=>"text_input_width",
                         "types"=>"SNTU",
                         "help"=>"Width of text input box");
// --> END ENHANCEMENT - TEXT INPUT WIDTH
    $qattributes[]=array("name"=>"hide_tip",
                         "types"=>"L!OMPWZ",
                         "help"=>"Hide the tip that is normally supplied with question");
    $qattributes[]=array("name"=>"random_order",
                         "types"=>"L!OMPRQWZ^",
                         "help"=>"Present Answers in random order");
    $qattributes[]=array("name"=>"code_filter",
                         "types"=>"FGWZ",
                         "help"=>"Filter the available answers by this value");
    $qattributes[]=array("name"=>"array_filter",
                         "types"=>"ABF",
                         "help"=>"Filter an Array's Answers from a Multiple Options Question");

// --> START ENHANCEMENT - DISPLAY ROWS
    $qattributes[]=array("name"=>"display_rows",
             "types"=>"TU",
             "help"=>"How many rows to display");
// --> END ENHANCEMENT - DISPLAY ROWS

// --> START ENHANCEMENT - SLIDER ATTRIBUTES
	$qattributes[]=array("name"=>"default_value",
				"types"=>"^",
				"help"=>"What value to use as the default");
	$qattributes[]=array("name"=>"minimum_value",
				"types"=>"^",
				"help"=>"The lowest value on the slider");
	$qattributes[]=array("name"=>"maximum_value",
				"types"=>"^",
				"help"=>"The highest value on the slider");
	$qattributes[]=array("name"=>"answer_width",
				"types"=>"^ABCEF",
				"help"=>"The percentage width of the answer column");
//	$qattributes[]=array("name"=>"left_label",
//				"types"=>"^",
//				"help"=>"The label to the left of the slider");
//	$qattributes[]=array("name"=>"centre_label",
//				"types"=>"^"
//				"help"=>"The centre label on the slider");
//	$qattributes[]=array("name"=>"right_label",
//				"types"=>"^",
//				"help"=>"The ")
				
// --> END ENHANCEMENT - SLIDER ATTRIBUTES

    //This builds a more useful array (don't modify)
    foreach($qattributes as $qa)
        {
        for ($i=0; $i<=strlen($qa['types'])-1; $i++)
            {
            $qat[substr($qa['types'], $i, 1)][]=array("name"=>$qa['name'],
                                                      "help"=>$qa['help']);
            }
        }
    return $qat;
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

// This function returns the header as result string
// If you want to echo the header use doHeader() !
function getHeader()
{
    global $embedded;

    if ( !$embedded )
    {
      return "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n"
        	."<head>\n"
			."<link type=\"text/css\" rel=\"StyleSheet\" href=\"classes/slider/swing.css\">\n"
			."<script type=\"text/javascript\" src=\"classes/slider/range.js\"></script>\n"
			."<script type=\"text/javascript\" src=\"classes/slider/timer.js\"></script>\n"
			."<script type=\"text/javascript\" src=\"classes/slider/slider.js\"></script>\n";
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
    global $versionnumber, $setfont, $imagefiles;
    $strHTMLFooter = "<div style='color: #FFFFFF; text-align:center; background-color: #000000; padding:3px; '>\n"
                . "\t\t\t<img  align='right' alt='Donate to PHPSurveyor' src='$imagefiles/donate.gif' "
                . "onClick=\"window.open('http://sourceforge.net/project/project_donations.php?group_id=74605')\" onMouseOver=\"document.body.style.cursor='pointer'\" "
                . "onMouseOut=\"document.body.style.cursor='auto'\">\n"
                . "\t\t\t<img style='padding-right:87px' align='left' alt='Help - $explanation' src='$imagefiles/help.gif' "
                . "onClick=\"window.open('$url')\" onMouseOver=\"document.body.style.cursor='pointer'\" "
                . "onMouseOut=\"document.body.style.cursor='auto'\">\n"
                . "\t\t\t<a href='http://www.phpsurveyor.org' class='subtitle' target='_blank'>"
                . "PHPSurveyor</a><div class='version'>Version $versionnumber</div> \n"
                . "</div>\n"
                . "</body>\n</html>";
    return $strHTMLFooter;
}


function doAdminHeader()
{
    echo getAdminHeader();
}

function getAdminHeader()
{
 global $sitename, $sitename, $homeurl;
 $strAdminHeader="<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n"
                ."<html>\n<head>\n"
                . "<!--[if lt IE 7]>\n"
                . "<script defer type=\"text/javascript\" src=\"scripts/pngfix.js\"></script>\n"
                . "<![endif]-->\n"
                . "<title>$sitename</title>\n"
                . "<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\">\n"
				. "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/default/adminstyle.css\">\n"
				. "<script type=\"text/javascript\" src=\"scripts/tooltips.js\"></script>"
                . "</head>\n<body>\n"
                . "<div class=\"maintitle\">\n"
                . "\t\t$sitename\n" 
                . "</div>\n";
  return $strAdminHeader;              
}



// This function returns the Footer as result string
// If you want to echo the Footer use doFooter() !
function getFooter()
{
    global $embedded;

    if ( !$embedded )
    {
        return "</html>\n";
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

function MailTextMessage($body, $subject, $to, $from, $sitename)
{
    global $emailmethod, $emailsmtphost, $emailsmtpuser, $emailsmtppassword;


    $mail = new PHPMailer;
    $mail->CharSet = "UTF-8";


	$fromname='';
	$fromemail=$from;
	if (strpos($from,'<'))
		{
         	$fromemail=substr($from,strpos($from,'<')+1,strpos($from,'>')-1-strpos($from,'<'));
         	$fromname=trim(substr($from,0, strpos($from,'<')-1));
		}
    $mail->Mailer = $emailmethod;
    if ($emailmethod=="smtp")
        { $mail->Host = $emailsmtphost;
          $mail->Username =$emailsmtpuser;
          $mail->Password =$emailsmtppassword;
          if ($emailsmtpuser!="")
            {$mail->SMTPAuth = true;}
        }
	$mail->From = $fromemail;
	$mail->AddAddress($to);
    	$mail->FromName = $fromname;
	$mail->AddCustomHeader("X-Surveymailer=$sitename:Emailer (PHPSurveyor.sourceforge.net)");
	$body = strip_tags($body);
	$body = str_replace("&quot;", '"', $body);
	if (get_magic_quotes_gpc() != "0")	{$body = stripcslashes($body);}
	$mail->Body = $body;
	$mail->Subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
	return $mail->Send();
}

// This functions removes all tags, CRs, linefeeds and other strange chars from a given text
function FlattenText($texttoflatten)
{
    $nicetext = strip_tags($texttoflatten);
    $nicetext = str_replace("\"", "`", $nicetext);
    $nicetext = str_replace("'", "`", $nicetext);
    $nicetext = str_replace("\r", "", $nicetext);
    $nicetext = trim(str_replace("\n", "", $nicetext));
	return  $nicetext;
}
/**
* getreferringurl() returns the reffering URL 
*/
function getreferringurl()
    {
    if (!isset($_SESSION['refurl']))
    {
    	if (!ereg(getenv("SERVER_NAME"), getenv("HTTP_REFERER"))) { $_SESSION['refurl'] = getenv("HTTP_REFERER"); }
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
function getArrayFiltersForGroup()
{
	// TODO: Check list_filter values to make sure questions are previous?
	global $surveyid, $gid, $dbprefix;
	// Get All Questions in Current Group
	$qquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid=$gid";
	$qresult = mysql_query($qquery);
	$grows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
	// Store each result as an array with in the $grows array
	while ($qrow = mysql_fetch_array($qresult)) {
		$grows[$qrow['qid']] = array('qid' => $qrow['qid'],'type' => $qrow['type'], 'mandatory' => $qrow['mandatory'], 'title' => $qrow['title']);
	}
	$attrmach = array(); // Stores Matches of filters that have their values as questions with in current group
	$grows2 = $grows;
	foreach ($grows as $qrow) // Cycle through questions to see if any have list_filter attributes
	{
		$qquery = "SELECT value FROM {$dbprefix}question_attributes WHERE attribute='array_filter' AND qid='".$qrow['qid']."'";
		$qresult = mysql_query($qquery);
		if (mysql_numrows($qresult) == 1) // We Found a array_filter attribute
		{
			$val = mysql_fetch_row($qresult); // Get the Value of the Attribute ( should be a previous question's title in same group )
			$qafound=0;
			while (list($key,$value) = each($grows2))
			{
				if ($value['title'] == $val[0])
				{
					$qafound = $value['qid']; // If array_filter value is a question in the group store it
					break;
				}

			}
			reset($grows2);
			if ($qafound > 1) // If not found remove that array element
			{ // If found we add it to our new array_filter matches array
				$filter = array('qid' => $qrow['qid'], 'mandatory' => $qrow['mandatory'], 'type' => $value['type'], 'fid' => $value['qid']);
				array_push($attrmach,$filter);
			}

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
	$query = "SELECT value FROM {$dbprefix}question_attributes WHERE attribute='array_filter' AND qid='".(int)$qid."'";
	$qresult = mysql_query($query);
	if (mysql_numrows($qresult) == 1) // We Found a array_filter attribute
	{
		$val = mysql_fetch_row($qresult); // Get the Value of the Attribute ( should be a previous question's title in same group )
		foreach ($_SESSION['fieldarray'] as $fields)
		{
			if ($fields[2] == $val[0])
			{
				// we found the target question, now we need to know what the answers where, we know its a multi!
				$query = "SELECT code FROM {$dbprefix}answers where qid='{$fields[0]}' order by sortorder";
				$qresult = mysql_query($query);
				$selected = array();
				while ($code = mysql_fetch_array($qresult))
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

?>
