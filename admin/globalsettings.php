<?php
/*
 * LimeSurvey (tm)
 * Copyright (C) 2009 The LimeSurvey Project Team / Carsten Schmitz
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
require_once('classes/core/settingsstorage.php');

//Ensure script is not run directly, avoid path disclosure
if (!isset($homedir) || isset($_REQUEST['$homedir'])) {die("Cannot run this script directly");}
injectglobalsettings();


function injectglobalsettings()
{
    global $connect;

    $registry = SettingsStorage::getInstance();
    $usquery = "SELECT * FROM ".db_table_name("settings_global");
    $dbvaluearray=$connect->GetAll($usquery);
    if ($dbvaluearray!==false)
    {
        foreach  ($dbvaluearray as $setting)
        {
            global $$setting['stg_name'];
            if (isset($$setting['stg_name']))
            {
                $$setting['stg_name']=$setting['stg_value'];
            }
            $registry->set($setting['stg_name'],$setting['stg_value']);
        }
    }
}


function globalsettingssave()
{
    global $action, $editsurvey, $connect, $scriptname, $clang;
    if (isset($action) && $action == "globalsettingssave")
    {
        if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
        {
            if (sanitize_int($_POST['maxemails'])<1)
            {
                $_POST['maxemails']=1;
            }
            setGlobalSetting('sitename',strip_tags($_POST['sitename']));
            setGlobalSetting('updatecheckperiod',(int)($_POST['updatecheckperiod']));
            setGlobalSetting('addTitleToLinks',sanitize_paranoid_string($_POST['addTitleToLinks']));
            setGlobalSetting('defaultlang',sanitize_languagecode($_POST['defaultlang']));
            setGlobalSetting('defaulthtmleditormode',sanitize_paranoid_string($_POST['defaulthtmleditormode']));
            setGlobalSetting('defaulttemplate',sanitize_paranoid_string($_POST['defaulttemplate']));
            setGlobalSetting('emailmethod',strip_tags($_POST['emailmethod']));
            setGlobalSetting('emailsmtphost',strip_tags(returnglobal('emailsmtphost')));
            if (returnglobal('emailsmtppassword')!='somepassword')
            {
                setGlobalSetting('emailsmtppassword',strip_tags(returnglobal('emailsmtppassword')));
            }
            setGlobalSetting('bounceaccounthost',strip_tags(returnglobal('bounceaccounthost')));
      	    setGlobalSetting('bounceaccounttype',strip_tags(returnglobal('bounceaccounttype')));
            setGlobalSetting('bounceencryption',strip_tags(returnglobal('bounceencryption')));
            setGlobalSetting('bounceaccountuser',strip_tags(returnglobal('bounceaccountuser')));
       
	    if (returnglobal('bounceaccountpass')!='enteredpassword')
            {
                setGlobalSetting('bounceaccountpass',strip_tags(returnglobal('bounceaccountpass')));
            }
            setGlobalSetting('emailsmtpssl',sanitize_paranoid_string(returnglobal('emailsmtpssl')));
            setGlobalSetting('emailsmtpdebug',sanitize_int(returnglobal('emailsmtpdebug')));
            setGlobalSetting('emailsmtpuser',strip_tags(returnglobal('emailsmtpuser')));
            setGlobalSetting('filterxsshtml',strip_tags($_POST['filterxsshtml']));
            setGlobalSetting('siteadminbounce',strip_tags($_POST['siteadminbounce']));
            setGlobalSetting('siteadminemail',strip_tags($_POST['siteadminemail']));
            setGlobalSetting('siteadminname',strip_tags($_POST['siteadminname']));
            setGlobalSetting('shownoanswer',sanitize_int($_POST['shownoanswer']));
            setGlobalSetting('showXquestions',($_POST['showXquestions']));
            setGlobalSetting('showgroupinfo',($_POST['showgroupinfo']));
            setGlobalSetting('showqnumcode',($_POST['showqnumcode']));
            $repeatheadingstemp=(int)($_POST['repeatheadings']);
            if ($repeatheadingstemp==0)  $repeatheadingstemp=25;
            setGlobalSetting('repeatheadings',$repeatheadingstemp);

            setGlobalSetting('maxemails',sanitize_int($_POST['maxemails']));
            $sessionlifetimetemp=(int)($_POST['sessionlifetime']);
            if ($sessionlifetimetemp==0)  $sessionlifetimetemp=3600;
            setGlobalSetting('sessionlifetime',$sessionlifetimetemp);
            setGlobalSetting('force_ssl',$_POST['force_ssl']);
            setGlobalSetting('surveyPreview_require_Auth',strip_tags($_POST['surveyPreview_require_Auth']));
            $savetime=trim(strip_tags((float) $_POST['timeadjust']).' hours'); //makes sure it is a number, at least 0
            if ((substr($savetime,0,1)!='-') && (substr($savetime,0,1)!='+')) { $savetime = '+'.$savetime;}
            setGlobalSetting('timeadjust',$savetime);
            setGlobalSetting('usepdfexport',strip_tags($_POST['usepdfexport']));
            setGlobalSetting('usercontrolSameGroupPolicy',strip_tags($_POST['usercontrolSameGroupPolicy']));
            $editsurvey .= "<div class='header ui-widget-header'>".$clang->gT("Global settings")."</div>\n"
            . "<div class=\"messagebox\">\n"
            . "<br /><div class=\"successheader\">".$clang->gT("Global settings were saved.")."</div>\n"
            . "<br/><input type=\"submit\" onclick=\"window.open('admin.php', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n"
            . "</div>\n";
        }
    }
}

function globalsettingsdisplay()
{
    global $action, $connect, $js_admin_includes, $editsurvey, $subaction, $scriptname, $clang;
    global $updateversion, $updatebuild, $updateavailable, $updatelastcheck, $demoModeOnly;

    if (isset($subaction) && $subaction == "updatecheck")
    {
        $updateinfo=updatecheck();
    }

    if (isset($action) && $action == "globalsettings")
    {
        if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
        {
            $js_admin_includes[]='scripts/globalsettings.js';
            // header
            $editsurvey = "<div class='header ui-widget-header'>".$clang->gT("Global settings")."</div>\n";
            // beginning TABs section
            $editsurvey .= "\t<div id='tabs'>
            <ul>
            <li><a href='#overview'>".$clang->gT("Overview & update")."</a></li>
            <li><a href='#general'>".$clang->gT("General")."</a></li>
            <li><a href='#email'>".$clang->gT("Email settings")."</a></li>
            <li><a href='#bounce'>".$clang->gT("Bounce settings")."</a></li>
            <li><a href='#security'>".$clang->gT("Security")."</a></li>
            <li><a href='#presentation'>".$clang->gT("Presentation")."</a></li>
            </ul>\n";
            $editsurvey .= "<form class='form30' id='frmglobalsettings' name='frmglobalsettings' action='$scriptname' method='post'>\n";
            $editsurvey .= "<div id='overview'>\n";
            $editsurvey .= checksettings();
            $thisupdatecheckperiod=getGlobalSetting('updatecheckperiod');
            $editsurvey .= "<br /></p><div class='header ui-widget-header'>".$clang->gT("Updates")."</div><ul>"
            . "\t<li><label for='updatecheckperiod'>".$clang->gT("Check for updates:")."</label>\n"
            . "\t\t\t<select name='updatecheckperiod' id='updatecheckperiod'>\n"
            . "\t\t\t\t<option value='0'";
            if ($thisupdatecheckperiod==0) {$editsurvey .= " selected='selected'";}
            $editsurvey .=">".$clang->gT("Never")."</option>\n"
            . "\t\t\t\t<option value='1'";
            if ($thisupdatecheckperiod==1) {$editsurvey .= " selected='selected'";}
            $editsurvey .=">".$clang->gT("Every day")."</option>\n"
            . "\t\t\t\t<option value='7'";
            if ($thisupdatecheckperiod==7) {$editsurvey .= " selected='selected'";}
            $editsurvey .=">".$clang->gT("Every week")."</option>\n"
            . "<option value='14'";
            if ($thisupdatecheckperiod==14) {$editsurvey .= " selected='selected'";}
            $editsurvey .=">".$clang->gT("Every 2 weeks")."</option>\n"
            . "<option value='30'";
            if ($thisupdatecheckperiod==30) {$editsurvey .= " selected='selected'";}
            $editsurvey .=">".$clang->gT("Every month")."</option>\n"
            . "</select>&nbsp;<input type='button' onclick=\"window.open('$scriptname?action=globalsettings&amp;subaction=updatecheck', '_top')\" value='".$clang->gT("Check now")."' />&nbsp;<span id='lastupdatecheck'>".sprintf($clang->gT("Last check: %s"),$updatelastcheck)."</span></li></ul><p>\n";

            if (isset($updateavailable) && $updateavailable==1)
            {
                $editsurvey .='<span style="font-weight: bold;">'.sprintf($clang->gT('There is a LimeSurvey update available: Version %s'),$updateversion."($updatebuild)").'</span><br />';
                $editsurvey .=sprintf($clang->gT('You can update %smanually%s or use the %s'),"<a href='http://docs.limesurvey.org/tiki-index.php?page=Upgrading+from+a+previous+version'>","</a>","<a href='$scriptname?action=update'>".$clang->gT('3-Click ComfortUpdate').'</a>').'.<br />';
            }
            elseif (isset($updateinfo['errorcode']))
            {
                $editsurvey .=sprintf($clang->gT('There was an error on update check (%s)'),$updateinfo['errorcode']).'.<br />';
                $editsurvey .="<textarea readonly='readonly' style='width:35%; height:60px; overflow: auto;'>".strip_tags($updateinfo['errorhtml']).'</textarea>';

            }
            else
            {
                $editsurvey .=$clang->gT('There is currently no newer LimeSurvey version available.');
            }
            $editsurvey .= "</p></div>";



            // General TAB
            $editsurvey .= "\t<div id='general'>\n";
            // Administrator...
            $editsurvey .= "<ul>"
            . "\t<li><label for='sitename'>".$clang->gT("Site name:").(($demoModeOnly==true)?'*':'')."</label>\n"
            . "\t\t<input type='text' size='50' id='sitename' name='sitename' value=\"".htmlspecialchars(getGlobalSetting('sitename'))."\" /></li>\n"
            . "\t<li><label for='defaultlang'>".$clang->gT("Default site language:").(($demoModeOnly==true)?'*':'')."</label>\n"
            . "\t\t<select name='defaultlang' id='defaultlang'>\n";
            $actuallang=getGlobalSetting('defaultlang');
            foreach (getLanguageData(true) as  $langkey2=>$langname)
            {
                $editsurvey .= "\t\t\t<option value='".$langkey2."'";
                if ($actuallang == $langkey2) {$editsurvey .= " selected='selected'";}
                $editsurvey .= ">".$langname['nativedescription']." - ".$langname['description']."</option>\n";
            }

            $editsurvey .= "\t\t</select></li>";

            $thisdefaulttemplate=getGlobalSetting('defaulttemplate');
            $templatenames=array_keys(gettemplatelist());
            $editsurvey .= ""
            . "\t<li><label for='defaulttemplate'>".$clang->gT("Default template:")."</label>\n"
            . "\t\t\t<select name='defaulttemplate' id='defaulttemplate'>\n";
            foreach ($templatenames as $templatename)
            {
                $editsurvey.= "\t\t\t\t<option value='$templatename'";
                if ($thisdefaulttemplate==$templatename) {$editsurvey .= " selected='selected'";}
                $editsurvey .=">$templatename</option>\n";
            }
            $editsurvey .="\t\t\t</select></li>\n";


            $thisdefaulthtmleditormode=getGlobalSetting('defaulthtmleditormode');
            $editsurvey .= ""
            . "\t<li><label for='defaulthtmleditormode'>".$clang->gT("Default HTML editor mode:").(($demoModeOnly==true)?'*':'')."</label>\n"
            . "\t\t\t<select name='defaulthtmleditormode' id='defaulthtmleditormode'>\n"
            . "\t\t\t\t<option value='default'";
            if ($thisdefaulthtmleditormode=='default') {$editsurvey .= " selected='selected'";}
            $editsurvey .=">".$clang->gT("Default HTML editor mode")."</option>\n"
            . "\t\t\t\t<option value='none'";
            if ($thisdefaulthtmleditormode=='none') {$editsurvey .= " selected='selected'";}
            $editsurvey .=">".$clang->gT("No HTML editor")."</option>\n"
            . "<option value='inline'";
            if ($thisdefaulthtmleditormode=='inline') {$editsurvey .= " selected='selected'";}
            $editsurvey .=">".$clang->gT("Inline HTML editor")."</option>\n"
            . "<option value='popup'";
            if ($thisdefaulthtmleditormode=='popup') {$editsurvey .= " selected='selected'";}
            $editsurvey .=">".$clang->gT("Popup HTML editor")."</option>\n"
            . "</select></li>\n";

            $dateformatdata=getDateFormatData($_SESSION['dateformat']);
            $editsurvey.= "\t<li><label for='timeadjust'>".$clang->gT("Time difference (in hours):")."</label>\n"
            . "\t\t<span><input type='text' size='10' id='timeadjust' name='timeadjust' value=\"".htmlspecialchars(str_replace(array('+',' hours'),array('',''),getGlobalSetting('timeadjust')))."\" /> "
            . $clang->gT("Server time:").' '.convertDateTimeFormat(date('Y-m-d H:i:s'),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i')." - ".$clang->gT("Corrected time :").' '.convertDateTimeFormat(date_shift(date("Y-m-d H:i:s"), 'Y-m-d H:i:s', getGlobalSetting('timeadjust')),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i')."
            </span></li>\n";

            $thisusepdfexport=getGlobalSetting('usepdfexport');
            $editsurvey .= "\t<li><label for='usepdfexport'>".$clang->gT("PDF export available:")."</label>\n"
            . "<select name='usepdfexport' id='usepdfexport'>\n"
            . "<option value='1'";
            if ( $thisusepdfexport == true) {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("On")."</option>\n"
            . "<option value='0'";
            if ( $thisusepdfexport == false) {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Off")."</option>\n"
            . "\t\t</select>\n\t</li>\n";

            $thisaddTitleToLinks=getGlobalSetting('addTitleToLinks');
            $editsurvey .= "\t<li><label for='addTitleToLinks'>".$clang->gT("Screen reader compatibility mode:")."</label>\n"
            . "<select name='addTitleToLinks' id='addTitleToLinks'>\n"
            . "<option value='1'";
            if ( $thisaddTitleToLinks == true) {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("On")."</option>\n"
            . "<option value='0'";
            if ( $thisaddTitleToLinks == false) {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Off")."</option>\n"
            . "</select>\n</li>\n"
            . "<li><label for='sessionlifetime'>".$clang->gT("Session lifetime (seconds):")."</label>\n"
            . "<input type='text' size='10' id='sessionlifetime' name='sessionlifetime' value=\"".htmlspecialchars(getGlobalSetting('sessionlifetime'))."\" /></li>";

            // End General TAB

            $editsurvey .= "\t</ul></div>\n";

            // Email TAB
            $editsurvey .= "\t<div id='email'><ul>\n";
			 //Format
            $editsurvey.= "\t<li><label for='siteadminemail'>".$clang->gT("Default site admin email:")."</label>\n"
            . "\t\t<input type='text' size='50' id='siteadminemail' name='siteadminemail' value=\"".htmlspecialchars(getGlobalSetting('siteadminemail'))."\" /></li>\n"
 
            . "\t<li><label for='siteadminname'>".$clang->gT("Administrator name:")."</label>\n"
            . "\t\t<input type='text' size='50' id='siteadminname' name='siteadminname' value=\"".htmlspecialchars(getGlobalSetting('siteadminname'))."\" /><br /><br /></li>\n"
            . "\t<li><label for='emailmethod'>".$clang->gT("Email method:")."</label>\n"
            . "\t\t<select id='emailmethod' name='emailmethod'>\n"
            . "\t\t\t<option value='mail'";
            if (getGlobalSetting('emailmethod')=='mail') {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("PHP (default)")."</option>\n"
            . "\t\t\t<option value='smtp'";
            if (getGlobalSetting('emailmethod')=='smtp') {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("SMTP")."</option>\n"
            . "\t\t\t<option value='sendmail'";
            if (getGlobalSetting('emailmethod')=='sendmail') {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Sendmail")."</option>\n"
            . "\t\t\t<option value='qmail'";
            if (getGlobalSetting('emailmethod')=='qmail') {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Qmail")."</option>\n"
            . "\t\t</select></li>\n"
            . "\t<li>\n\t\t<label for=\"emailsmtphost\">".$clang->gT("SMTP host:")."</label>\n"
            . "\t\t<input type='text' size='50' id='emailsmtphost' name='emailsmtphost' value=\"".htmlspecialchars(getGlobalSetting('emailsmtphost'))."\" />&nbsp;<font size='1'>".$clang->gT("Enter your hostname and port, e.g.: my.smtp.com:25")."</font></li>\n"
            . "\t<li><label for='emailsmtpuser'>".$clang->gT("SMTP username:")."</label>\n"
            . "\t\t<input type='text' size='50' id='emailsmtpuser' name='emailsmtpuser' value=\"".htmlspecialchars(getGlobalSetting('emailsmtpuser'))."\" /></li>\n"
            . "\t<li><label for='emailsmtppassword'>".$clang->gT("SMTP password:")."</label>\n"
            . "\t\t<input type='password' size='50' id='emailsmtppassword' name='emailsmtppassword' value='somepassword' /></li>\n"
            . "\t<li><label for='emailsmtpssl'>".$clang->gT("SMTP SSL/TLS:")."</label>\n"
            . "\t\t<select id='emailsmtpssl' name='emailsmtpssl'>\n"
            . "\t\t\t<option value=''";
            if (getGlobalSetting('emailsmtpssl')=='') {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Off")."</option>\n"
            . "\t\t\t<option value='ssl'";
            if (getGlobalSetting('emailsmtpssl')=='ssl' || getGlobalSetting('emailsmtpssl')==1) {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("SSL")."</option>\n"
            . "\t\t\t<option value='tls'";
            if (getGlobalSetting('emailsmtpssl')=='tls') {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("TLS")."</option>\n"
            . "\t\t</select></li>\n"
            . "\t<li><label for='emailsmtpdebug'>".$clang->gT("SMTP debug mode:")."</label>\n"
            . "\t\t<select id='emailsmtpdebug' name='emailsmtpdebug'>\n"
            . "\t\t\t<option value=''";
            if (getGlobalSetting('emailsmtpdebug')=='0') {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Off")."</option>\n"
            . "\t\t\t<option value='1'";
            if (getGlobalSetting('emailsmtpdebug')=='1' || getGlobalSetting('emailsmtpssl')==1) {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("On errors")."</option>\n"
            . "\t\t\t<option value='2'";
            if (getGlobalSetting('emailsmtpdebug')=='2' || getGlobalSetting('emailsmtpssl')==1) {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Always")."</option>\n"
            . "\t\t</select><br />&nbsp;</li>\n"
            . "\t<li><label for='maxemails'>".$clang->gT("Email batch size:")."</label>\n"
            . "\t\t<input type='text' size='5' id='maxemails' name='maxemails' value=\"".htmlspecialchars(getGlobalSetting('maxemails'))."\" /></li>\n"
            . "\t</ul>\n";
            // End Email TAB
            $editsurvey .= "\t</div>\n";
            // Start bounce tab
            $editsurvey .= "\t<div id='bounce'><ul>\n"
            . "\t<li><label for='siteadminbounce'>".$clang->gT("Default site bounce email:")."</label>\n"
            . "\t\t<input type='text' size='50' id='siteadminbounce' name='siteadminbounce' value=\"".htmlspecialchars(getGlobalSetting('siteadminbounce'))."\" /></li>\n"
            . "\t<li><label for='bounceaccounttype'>".$clang->gT("Bounce account type:")."</label>\n"
	        . "\t\t<select id='bounceaccounttype' name='bounceaccounttype'>\n"
  	        . "\t\t\t<option value='off'";
            if (getGlobalSetting('bounceaccounttype')=='off') {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Off")."</option>\n"
            . "\t\t\t<option value='IMAP'";
            if (getGlobalSetting('bounceaccounttype')=='IMAP') {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("IMAP")."</option>\n"
            . "\t\t\t<option value='POP'";
            if (getGlobalSetting('bounceaccounttype')=='POP') {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("POP")."</option>\n"
            ."\t\t</select></li>\n"

            . "\t<li><label for='bounceaccounthost'>".$clang->gT("Bounce account host:")."</label>\n"
            . "\t\t<input type='text' size='50' id='bounceaccounthost' name='bounceaccounthost' value=\"".htmlspecialchars(getGlobalSetting('bounceaccounthost'))."\" />\n"."<font size='1'>".$clang->gT("Enter your hostname and port, e.g.: imap.gmail.com:995")."</font>\n"

            . "\t<li><label for='bounceaccountuser'>".$clang->gT("Bounce account user:")."</label>\n"
            . "\t\t<input type='text' size='50' id='bounceaccountuser' name='bounceaccountuser' value=\"".htmlspecialchars(getGlobalSetting('bounceaccountuser'))."\" /></li>\n"
            . "\t<li><label for='bounceaccountpass'>".$clang->gT("Bounce account password:")."</label>\n"
            . "\t\t<input type='password' size='50' id='bounceaccountpass' name='bounceaccountpass' value='enteredpassword' /></li>\n";
	    $editsurvey.= "\t<li><label for='bounceencryption'>".$clang->gT("Bounce account encryption type")."</label>\n"
	    . "\t\t<select id='bounceencryption' name='bounceencryption'>\n"
  	    . "\t\t\t<option value='off'";
            if (getGlobalSetting('bounceencryption')=='off') {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Off")."</option>\n"
            . "\t\t\t<option value='SSL'";
            if (getGlobalSetting('bounceencryption')=='SSL') {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("SSL")."</option>\n"
            . "\t\t\t<option value='TLS'";
            if (getGlobalSetting('bounceencryption')=='TLS') {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("TLS")."</option>\n"
            ."\t\t</select></li>\n</ul>";
            $editsurvey .= "\t</div>\n";
            // End of bounce tabs
            // Security Settings
            $editsurvey .= "\t<div id='security'><ul>\n";
            // Expiration
            $thissurveyPreview_require_Auth=getGlobalSetting('surveyPreview_require_Auth');
            $editsurvey .= "\t<li><label for='surveyPreview_require_Auth'>".$clang->gT("Survey preview only for administration users")."</label>\n"
            . "\t\t<select id='surveyPreview_require_Auth' name='surveyPreview_require_Auth'>\n"
            . "\t\t\t<option value='1'";
            if ($thissurveyPreview_require_Auth == true) {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "\t\t\t<option value='0'";
            if ($thissurveyPreview_require_Auth == false) {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "\t\t</select></li>\n";

            // Auto registration
            $thisfilterxsshtml=getGlobalSetting('filterxsshtml');
            $editsurvey .= "\t<li><label for='filterxsshtml'>".$clang->gT("Filter HTML for XSS:").(($demoModeOnly==true)?'*':'')."</label>\n"
            . "\t\t<select id='filterxsshtml' name='filterxsshtml'>\n"
            . "\t\t\t<option value='1'";
            if ( $thisfilterxsshtml == true) {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "\t\t\t<option value='0'";
            if ( $thisfilterxsshtml == false) {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "\t\t</select></li>\n";

            $thisusercontrolSameGroupPolicy=getGlobalSetting('usercontrolSameGroupPolicy');
            $editsurvey .= "\t<li><label for='usercontrolSameGroupPolicy'>".$clang->gT("Group member can only see own group:")."</label>\n"
            . "\t\t<select id='usercontrolSameGroupPolicy' name='usercontrolSameGroupPolicy'>\n"
            . "\t\t\t<option value='1'";
            if ( $thisusercontrolSameGroupPolicy == true) {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "\t\t\t<option value='0'";
            if ( $thisusercontrolSameGroupPolicy == false) {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "\t\t</select></li>\n";

            $thisforce_ssl = getGlobalSetting('force_ssl');
	    $opt_force_ssl_on = $opt_force_ssl_off = $opt_force_ssl_neither = '';
	    $warning_force_ssl = $clang->gT('Warning: Before turning on HTTPS, ')
	    . '<a href="https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'"title="'
	    . $clang->gT('Test if your server has SSL enabled by clicking on this link.').'">'
	    . $clang->gT('check if this link works.').'</a><br/> '
	    . $clang->gT("If the link does not work and you turn on HTTPS, LimeSurvey will break and you won't be able to access it.");
//	    $warning_force_ssl = ' Do <strong>NOT</strong> force "On" if you\'re <strong>not completely certain</strong> your server has a SSL enabled. <br />'
//	    . 'Before turning on HTTPS, <a href="https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'">See if this link works</a><br />'
//	    . 'If not, <strong>LimeSurvey will break</strong> if SSL is forced on but your server does not have a valid secure certificate installed and enabled.<br />';
	    switch($thisforce_ssl)
	    {
	    	case 'on':
		    $warning_force_ssl = '&nbsp;';                            
		    break;
		case 'off':
		case 'neither':
		    break;
	    	default:
		    $thisforce_ssl = 'neither';
	    };
	    $this_opt = 'opt_force_ssl_'.$thisforce_ssl;
	    $$this_opt = ' selected="selected"';
	    $editsurvey .= '<li><label for="force_ssl">'.$clang->gT('Force HTTPS:')."</label>\n"
	    . "<select name=\"force_ssl\" id=\"force_ssl\">\n\t"
            . '<option value="on" '.$opt_force_ssl_on.'>'.$clang->gT('On')."</option>\n\t"
            . '<option value="off" '.$opt_force_ssl_off.'>'.$clang->gT('Off')."</option>\n\t"
            . '<option value="neither" '.$opt_force_ssl_neither.'>'.$clang->gT('Don\'t force on or off')."</option>\n\t"
	    . "</select></li>\n"
	    . "<li><span style='font-size:0.7em;'>$warning_force_ssl\n</span></li>\n";
	    unset($thisforce_ssl,$opt_force_ssl_on,$opt_force_ssl_off,$opt_force_ssl_neither,$warning_force_ssl,$this_opt);


        $editsurvey .= "\t</ul></div>\n";

        // presentation settings tab
        $editsurvey .= "\t<div id='presentation'><ul>\n";
        // shownoanswer
        $shownoanswer=getGlobalSetting('shownoanswer');
	    $sel_na = array( 0 => '' , 1 => '' , 2 => '');
	    $sel_na[$shownoanswer] = ' selected="selected"';
        $editsurvey .= "\t<li><label for='shownoanswer'>".$clang->gT("Show 'no answer' option for non-mandatory questions:")."</label>\n"
        . "\t\t<select id='shownoanswer' name='shownoanswer'>\n"
        . "\t\t\t<option value=\"1\"{$sel_na[1]}>".$clang->gT('Yes')."</option>\n"
        . "\t\t\t<option value=\"0\"{$sel_na[0]}>".$clang->gT('No')."</option>\n"
        . "\t\t\t<option value=\"2\"{$sel_na[2]}>".$clang->gT('Survey admin can choose')."</option>\n"
        . "\t\t</select></li>\n";

        $thisrepeatheadings=getGlobalSetting('repeatheadings');
        $editsurvey .= "\t<li><label for='repeatheadings'>".$clang->gT("Repeating headings in array questions every X subquestions:")."</label>\n"
        . "\t\t<input id='repeatheadings' name='repeatheadings' value='$thisrepeatheadings' size='4' maxlength='4' /></li>\n";


        // showXquestions
        $set_xq=getGlobalSetting('showXquestions');
	    $sel_xq = array( 'hide' => '' , 'show' => '' , 'choose' => '');
	    $sel_xq[$set_xq] = ' selected="selected"';
	    if( empty($sel_xq['hide']) && empty($sel_xq['show']) && empty($sel_xq['choose']))
	    {
	    	$sel_xq['choose'] = ' selected="selected"';
	    };
            $editsurvey .= "\t<li><label for=\"showXquestions\">".$clang->gT('Show "There are X questions in this survey"')."</label>\n"
            . "\t\t<select id=\"showXquestions\" name=\"showXquestions\">\n"
            . "\t\t\t<option value=\"show\"{$sel_xq['show']}>".$clang->gT('Yes')."</option>\n"
            . "\t\t\t<option value=\"hide\"{$sel_xq['hide']}>".$clang->gT('No')."</option>\n"
            . "\t\t\t<option value=\"choose\"{$sel_xq['choose']}>".$clang->gT('Survey admin can choose')."</option>\n"
            . "\t\t</select></li>\n";
	    unset($set_xq,$sel_xq);






	    // showgroupinfo
            $set_gri=getGlobalSetting('showgroupinfo');
	    $sel_gri = array( 'both' => '' , 'choose' =>'' , 'description' => '' , 'name' => '' , 'none' => '' );
	    $sel_gri[$set_gri] = ' selected="selected"';
	    if( empty($sel_gri['both']) && empty($sel_gri['choose']) && empty($sel_gri['description']) && empty($sel_gri['name']) && empty($sel_gri['none']))
	    {
	    	$sel_gri['choose'] = ' selected="selected"';
	    };
            $editsurvey .= "\t<li><label for=\"showgroupinfo\">".$clang->gT('Show question group name and/or description')."</label>\n"
            . "\t\t<select id=\"showgroupinfo\" name=\"showgroupinfo\">\n"
            . "\t\t\t<option value=\"both\"{$sel_gri['both']}>".$clang->gT('Show both')."</option>\n"
            . "\t\t\t<option value=\"name\"{$sel_gri['name']}>".$clang->gT('Show group name only')."</option>\n"
            . "\t\t\t<option value=\"description\"{$sel_gri['description']}>".$clang->gT('Show group description only')."</option>\n"
            . "\t\t\t<option value=\"none\"{$sel_gri['none']}>".$clang->gT('Hide both')."</option>\n"
            . "\t\t\t<option value=\"choose\"{$sel_gri['choose']}>".$clang->gT('Survey admin can choose')."</option>\n"
            . "\t\t</select></li>\n";
	    unset($set_gri,$sel_gri);

	    // showqnumcode
            $set_qnc=getGlobalSetting('showqnumcode');
	    $sel_qnc = array( 'both' => '' , 'choose' =>'' , 'number' => '' , 'code' => '' , 'none' => '' );
	    $sel_qnc[$set_qnc] = ' selected="selected"';
	    if( empty($sel_qnc['both']) && empty($sel_qnc['choose']) && empty($sel_qnc['number']) && empty($sel_qnc['code']) && empty($sel_qnc['none']))
	    {
	    	$sel_qnc['choose'] = ' selected="selected"';
	    };
            $editsurvey .= "\t<li><label for=\"showqnumcode\">".$clang->gT('Show question number and/or question code')."</label>\n"
            . "\t\t<select id=\"showqnumcode\" name=\"showqnumcode\">\n"
            . "\t\t\t<option value=\"both\"{$sel_qnc['both']}>".$clang->gT('Show both')."</option>\n"
            . "\t\t\t<option value=\"number\"{$sel_qnc['number']}>".$clang->gT('Show question number only')."</option>\n"
            . "\t\t\t<option value=\"code\"{$sel_qnc['code']}>".$clang->gT('Show question code only')."</option>\n"
            . "\t\t\t<option value=\"none\"{$sel_qnc['none']}>".$clang->gT('Hide both')."</option>\n"
            . "\t\t\t<option value=\"choose\"{$sel_qnc['choose']}>".$clang->gT('Survey admin can choose')."</option>\n"
           . "\t\t</select></li>\n";
	    unset($set_qnc,$sel_qnc);

            $editsurvey .= "\t</ul>\n";
            // End TAB page & form
            $editsurvey .= "\t</div><input type='hidden' name='action' value='globalsettingssave'/></form>\n";

            // End tabs
            $editsurvey .= "</div>\n";

            // The external button to sumbit Survey edit changes
            $editsurvey .= "\t<p><input type='button' onclick='$(\"#frmglobalsettings\").submit();' class='standardbtn' value='".$clang->gT("Save settings")."' /><br /></p>\n";
            if ($demoModeOnly==true)
            {
                $editsurvey .= '<p>'.$clang->gT("Note: Demo mode is activated. Marked (*) settings won't be saved.").'</p>\n';
            }




        }
        else
        {
            include("access_denied.php");
        }
    }
}

function getGlobalSetting($settingname)
{
    global $connect, $$settingname;
    $registry = SettingsStorage::getInstance();
    if (!$registry->isRegistered($settingname)) {
        $usquery = "SELECT stg_value FfROM ".db_table_name("settings_global")." where stg_name='$settingname'";
        $dbvalue=$connect->GetOne($usquery);
        if (is_null($dbvalue))
        {
            $registry->set($settingname,$dbvalue);
        } elseif (isset($$settingname)) {
            // If the setting was not found in the setting table but exists as a variable (from config.php)
            // get it and save it to the table
            setGlobalSetting($settingname,$$settingname);
            $dbvalue=$$settingname;
        }
    } else {
        $dbvalue=$registry->get($settingname);
    }

    return $dbvalue;
}

function setGlobalSetting($settingname,$settingvalue)
{
    global $connect, $$settingname, $demoModeOnly;
    if ($demoModeOnly==true && ($settingname=='sitename' || $settingname=='defaultlang' || $settingname=='defaulthtmleditormode' || $settingname=='filterxsshtml'))
    {
        return; //don't save
    }
    $usquery = "update ".db_table_name("settings_global")." set stg_value='".auto_escape($settingvalue)."' where stg_name='$settingname'";
    $connect->Execute($usquery);
    if ($connect->Affected_Rows()==0)
    {
        $usquery = "insert into  ".db_table_name("settings_global")." (stg_value,stg_name) values('".auto_escape($settingvalue)."','$settingname')";
        $connect->Execute($usquery);
    }
    $registry = SettingsStorage::getInstance();
    $registry->set($settingname,$settingvalue);
    if (isset($$settingname)) $$settingname=$settingvalue;
}


function checksettings()
{
    global $connect, $dbprefix, $clang, $databasename, $scriptname;
    //GET NUMBER OF SURVEYS
    $query = "SELECT count(sid) FROM ".db_table_name('surveys');
    $surveycount=$connect->GetOne($query);   //Checked
    $query = "SELECT count(sid) FROM ".db_table_name('surveys')." WHERE active='Y'";
    $activesurveycount=$connect->GetOne($query);  //Checked
    $query = "SELECT count(users_name) FROM ".db_table_name('users');
    $usercount = $connect->GetOne($query);   //Checked

    if ($activesurveycount==false) $activesurveycount=0;
    if ($surveycount==false) $surveycount=0;

    $tablelist = $connect->MetaTables();
    foreach ($tablelist as $table)
    {
        if (strpos($table,$dbprefix."old_tokens_")!==false)
        {
            $oldtokenlist[]=$table;
        }
        elseif (strpos($table,$dbprefix."tokens_")!==false)
        {
            $tokenlist[]=$table;
        }
        elseif (strpos($table,$dbprefix."old_survey_")!==false)
        {
            $oldresultslist[]=$table;
        }
    }
    if(isset($oldresultslist) && is_array($oldresultslist))
    {$deactivatedsurveys=count($oldresultslist);} else {$deactivatedsurveys=0;}
    if(isset($oldtokenlist) && is_array($oldtokenlist))
    {$deactivatedtokens=count($oldtokenlist);} else {$deactivatedtokens=0;}
    if(isset($tokenlist) && is_array($tokenlist))
    {$activetokens=count($tokenlist);} else {$activetokens=0;}
    $cssummary = "<div class='header ui-widget-header'>".$clang->gT("System overview")."</div>\n";
    // Database name & default language
    $cssummary .= "<br /><table class='statisticssummary'><tr>\n"
    . "<th width='50%' align='right'>".$clang->gT("Database name").":</th><td>$databasename</td>\n"
    . "</tr>\n";
    // Other infos
    $cssummary .=  "<tr>\n"
    . "<th align='right'>".$clang->gT("Users").":</th><td>$usercount</td>\n"
    . "</tr>\n"
    . "<tr>\n"
    . "<th align='right'>".$clang->gT("Surveys").":</th><td>$surveycount</td>\n"
    . "</tr>\n"
    . "<tr>\n"                                               
    . "<th align='right'>".$clang->gT("Active surveys").":</th><td>$activesurveycount</td>\n"
    . "</tr>\n"
    . "<tr>\n"
    . "<th align='right'>".$clang->gT("Deactivated result tables").":</th><td>$deactivatedsurveys</td>\n"
    . "</tr>\n"
    . "<tr>\n"
    . "<th align='right'>".$clang->gT("Active token tables").":</th><td>$activetokens</td>\n"
    . "</tr>\n"
    . "<tr>\n"
    . "<th align='right'>".$clang->gT("Deactivated token tables").":</th><td>$deactivatedtokens</td>\n"
    . "</tr>\n"
    . "</table>\n";

    if ($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1)
    {
        $cssummary .= "<p><input type='button' onclick='window.open(\"$scriptname?action=showphpinfo\")' value='".$clang->gT("Show PHPInfo")."' />";
    }
    return $cssummary;
}
?>
