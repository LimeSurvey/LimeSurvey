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
* $Id: common.php 7566 2009-09-04 14:04:56Z c_schmitz $
*/


//Ensure script is not run directly, avoid path disclosure
if (!isset($homedir) || isset($_REQUEST['$homedir'])) {die("Cannot run this script directly");}            
injectglobalsettings();


function injectglobalsettings()
{
    
    global $connect,$emailmethod ;
    $emailmethod='smtp';
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
                       setGlobalSetting('sitename',strip_tags($_POST['sitename']));
                       setGlobalSetting('updatecheckperiod',(int)($_POST['updatecheckperiod']));   
                       setGlobalSetting('addTitleToLinks',sanitize_paranoid_string($_POST['addTitleToLinks']));
                       setGlobalSetting('defaultlang',sanitize_languagecode($_POST['defaultlang']));
                       setGlobalSetting('defaulthtmleditormode',sanitize_paranoid_string($_POST['defaulthtmleditormode']));
                       setGlobalSetting('defaulttemplate',sanitize_paranoid_string($_POST['defaulttemplate']));
                       setGlobalSetting('emailsmtphost',strip_tags($_POST['emailsmtphost']));
                       if ($_POST['emailsmtppassword']!='somepassword')
                       {
                            setGlobalSetting('emailsmtppassword',strip_tags($_POST['emailsmtppassword']));
                       }
                       setGlobalSetting('emailsmtpssl',sanitize_paranoid_string($_POST['emailsmtpssl']));
                       setGlobalSetting('emailsmtpdebug',sanitize_int($_POST['emailsmtpdebug']));
                       setGlobalSetting('emailsmtpuser',strip_tags($_POST['emailsmtpuser']));
                       setGlobalSetting('filterxsshtml',strip_tags($_POST['filterxsshtml']));
                       setGlobalSetting('siteadminbounce',strip_tags($_POST['siteadminbounce']));
                       setGlobalSetting('siteadminemail',strip_tags($_POST['siteadminemail']));
                       setGlobalSetting('siteadminname',strip_tags($_POST['siteadminname']));
                       setGlobalSetting('shownoanswer',sanitize_int($_POST['shownoanswer']));
                       setGlobalSetting('repeatheadings',sanitize_int($_POST['repeatheadings']));
                       setGlobalSetting('maxemails',sanitize_int($_POST['maxemails']));
                       setGlobalSetting('sessionlifetime',sanitize_int($_POST['sessionlifetime']));
                       setGlobalSetting('surveyPreview_require_Auth',strip_tags($_POST['surveyPreview_require_Auth']));
                       $savetime=trim(strip_tags($_POST['timeadjust']).' hours');
                       if ((substr($savetime,0,1)!='-') && (substr($savetime,0,1)!='+')) { $savetime = '+'.$savetime;}
                       setGlobalSetting('timeadjust',$savetime);
                       setGlobalSetting('usepdfexport',strip_tags($_POST['usepdfexport']));
                       setGlobalSetting('usercontrolSameGroupPolicy',strip_tags($_POST['usercontrolSameGroupPolicy']));
                       $editsurvey .= "<p>".$clang->gT("Global settings were saved.");
        }                                                                
    }
}

function globalsettingsdisplay() 
{
    global $action, $subaction, $editsurvey, $connect, $scriptname, $clang, $updateversion, $updatebuild, $updateavailable, $updatelastcheck;
    
    if (isset($subaction) && $subaction == "updatecheck")
    {
        $updateinfo=updatecheck();
    }  
    
    if (isset($action) && $action == "globalsettings")
    {
        if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
        {
            // header
            $editsurvey = "<table width='100%' border='0'>\n\t<tr><td colspan='4' class='settingcaption'>"
            . "\t\t".$clang->gT("Global settings")."</td></tr></table>\n";


            // beginning TABs section
            $editsurvey .= "\t<div class='tab-pane' id='tab-pane-1'>\n";
            $editsurvey .= "<form id='frmglobalsettings' name='frmglobalsettings' action='$scriptname' method='post'>\n";
            $editsurvey .= "\t<div class='tab-page'> <h2 class='tab'>".$clang->gT("Overview & Update")."</h2>\n";
            $editsurvey .= checksettings();
            $thisupdatecheckperiod=getGlobalSetting('updatecheckperiod');
            $editsurvey .= "<br /></p><div class='settingcaption'>Update settings</div><ul>"
            . "\t<li><label for='updatecheckperiod'>".$clang->gT("Check for updates:")."</label>\n"
            . "\t\t\t<select name='updatecheckperiod' id='updatecheckperiod'>\n"
            . "\t\t\t\t<option value='0'";
            if ($thisupdatecheckperiod==0) {$editsurvey .= "selected='selected'";}
            $editsurvey .=">".$clang->gT("Never")."</option>\n"
            . "\t\t\t\t<option value='1'";
            if ($thisupdatecheckperiod==1) {$editsurvey .= "selected='selected'";}
            $editsurvey .=">".$clang->gT("Every day")."</option>\n"
            . "\t\t\t\t<option value='7'";
            if ($thisupdatecheckperiod==7) {$editsurvey .= "selected='selected'";}
            $editsurvey .=">".$clang->gT("Every week")."</option>\n"
            . "<option value='14'"; 
            if ($thisupdatecheckperiod==14) {$editsurvey .= "selected='selected'";}
            $editsurvey .=">".$clang->gT("Every 2 weeks")."</option>\n"
            . "<option value='30'"; 
            if ($thisupdatecheckperiod==30) {$editsurvey .= "selected='selected'";}
            $editsurvey .=">".$clang->gT("Every month")."</option>\n"
            . "</select>&nbsp;<input type='button' onclick=\"window.open('$scriptname?action=globalsettings&amp;subaction=updatecheck', '_top')\" value='".$clang->gT("Check now")."' />&nbsp;<span id='lastupdatecheck'>".sprintf($clang->gT("Last check: %s"),$updatelastcheck)."</span></li></ul><p>\n"; 
            
            if (isset($updateavailable) && $updateavailable==1)
            {
              $editsurvey .=sprintf($clang->gT('There is a LimeSurvey update available: Version %s'),$updateversion."($updatebuild)").'<br />';
              $editsurvey .=sprintf($clang->gT('You can update manually or use the %s'),"<a href='$scriptname?action=update'>".$clang->gT('3-Click ComfortUpdate').'</a>').'.<br />';
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
            $editsurvey .= "</div>";
            


            // General TAB
            $editsurvey .= "\t<div class='tab-page'> <h2 class='tab'>".$clang->gT("General")."</h2>\n";
            // Administrator...
            $editsurvey .= "<ul>"
            . "\t<li><label for='sitename'>".$clang->gT("Site name:")."</label>\n"
            . "\t\t<input type='text' size='50' id='sitename' name='sitename' value=\"".htmlspecialchars(getGlobalSetting('sitename'))."\" /></li>\n"
            . "\t<li><label for='defaultlang'>".$clang->gT("Default site language:")."</label>\n"
            . "\t\t<select name='defaultlang' id='defaultlang'>\n";
            $actuallang=getGlobalSetting('defaultlang');
            foreach (getLanguageData() as  $langkey2=>$langname)
            {
                $editsurvey .= "\t\t\t<option value='".$langkey2."'";
                if ($actuallang == $langkey2) {$editsurvey .= " selected='selected'";}
                $editsurvey .= ">".$langname['description']." - ".$langname['nativedescription']."</option>\n";
            }

            $editsurvey .= "\t\t</select></li>";

            $thisdefaulttemplate=getGlobalSetting('defaulttemplate');
            $templatenames=gettemplatelist();
            $editsurvey .= ""
            . "\t<li><label for='defaulttemplate'>".$clang->gT("Default template:")."</label>\n"
            . "\t\t\t<select name='defaulttemplate' id='defaulttemplate'>\n";
            foreach ($templatenames as $templatename)
            {
                $editsurvey.= "\t\t\t\t<option value='$templatename'";
                if ($thisdefaulttemplate==$templatename) {$editsurvey .= "selected='selected'";}
                $editsurvey .=">$templatename</option>\n";
            }
            $editsurvey .="\t\t\t</select></li>\n";       


            $thisdefaulthtmleditormode=getGlobalSetting('defaulthtmleditormode');
            $editsurvey .= ""
            . "\t<li><label for='defaulthtmleditormode'>".$clang->gT("Default HTML editor mode:")."</label>\n"
            . "\t\t\t<select name='defaulthtmleditormode' id='defaulthtmleditormode'>\n"
            . "\t\t\t\t<option value='default'";
            if ($thisdefaulthtmleditormode=='default') {$editsurvey .= "selected='selected'";}
            $editsurvey .=">".$clang->gT("Default HTML editor mode")."</option>\n"
            . "\t\t\t\t<option value='none'";
            if ($thisdefaulthtmleditormode=='none') {$editsurvey .= "selected='selected'";}
            $editsurvey .=">".$clang->gT("No HTML editor")."</option>\n"
            . "<option value='inline'"; 
            if ($thisdefaulthtmleditormode=='inline') {$editsurvey .= "selected='selected'";}
            $editsurvey .=">".$clang->gT("Inline HTML editor")."</option>\n"
            . "<option value='popup'"; 
            if ($thisdefaulthtmleditormode=='popup') {$editsurvey .= "selected='selected'";}
            $editsurvey .=">".$clang->gT("Popup HTML editor")."</option>\n"
            . "</select></li>\n";       

            $editsurvey.= "\t<li><label for='timeadjust'>".$clang->gT("Time difference (in hours):")."</label>\n"
            . "\t\t<input type='text' size='10' id='timeadjust' name='timeadjust' value=\"".htmlspecialchars(str_replace(array('+',' hours'),array('',''),getGlobalSetting('timeadjust')))."\" /></li>\n";
            
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
            $editsurvey .= "\t<div class='tab-page'> <h2 class='tab'>".$clang->gT("Email settings")."</h2><ul>\n";

                //Format

                $editsurvey.= "\t<li><label for='siteadminemail'>".$clang->gT("Default site admin email:")."</label>\n"
                . "\t\t<input type='text' size='50' id='siteadminemail' name='siteadminemail' value=\"".htmlspecialchars(getGlobalSetting('siteadminemail'))."\" /></li>\n"
                . "\t<li><label for='siteadminbounce'>".$clang->gT("Default site bounce email:")."</label>\n"
                . "\t\t<input type='text' size='50' id='siteadminbounce' name='siteadminbounce' value=\"".htmlspecialchars(getGlobalSetting('siteadminbounce'))."\" /></li>\n"
                . "\t<li><label for='siteadminname'>".$clang->gT("Administrator name:")."</label>\n"
                . "\t\t<input type='text' size='50' id='siteadminname' name='siteadminname' value=\"".htmlspecialchars(getGlobalSetting('siteadminname'))."\" /><br /><br /></li>\n"
                . "\t<li><label for='emailsmtphost'>".$clang->gT("SMTP host:")."</label>\n"
                . "\t\t<input type='text' size='50' id='emailsmtphost' name='emailsmtphost' value=\"".htmlspecialchars(getGlobalSetting('emailsmtphost'))."\" />&nbsp;<font size=1>Enter your hostname and port, e.g.: my.smtp.com:25</font></li>\n"
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
                . "\t\t</select></li><br />\n"
                . "\t<li><label for='maxemails'>".$clang->gT("Email batch size:")."</label>\n"
                . "\t\t<input type='text' size='5' id='maxemails' name='maxemails' value=\"".htmlspecialchars(getGlobalSetting('maxemails'))."\" /></li>\n"
                . "\t</ul>\n";

            // End Email TAB
            $editsurvey .= "\t</div>\n";

            // Security Settings
            $editsurvey .= "\t<div class='tab-page'> <h2 class='tab'>".$clang->gT("Security")."</h2><ul>\n";


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
                $editsurvey .= "\t<li><label for='filterxsshtml'>".$clang->gT("Filter HTML for XSS:")."</label>\n"
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
            $editsurvey .= "\t</ul></div>\n";

            // Miscellaneous Settings
            $editsurvey .= "\t<div class='tab-page'> <h2 class='tab'>".$clang->gT("Miscellaneous")."</h2><ul>\n";

                // shownoanswer
                $shownoanswer=getGlobalSetting('shownoanswer');
                $editsurvey .= "\t<li><label for='shownoanswer'>".$clang->gT("Show 'no answer' option for non-mandatory questions:")."</label>\n"
                . "\t\t<select id='shownoanswer' name='shownoanswer'>\n"
                . "\t\t\t<option value='1'";
                if ($shownoanswer == 1) {$editsurvey .= " selected='selected'";}
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "\t\t\t<option value='0'";
                if ($shownoanswer == 0) {$editsurvey .= " selected='selected'";}
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "\t\t</select></li>\n";
                $thisrepeatheadings=getGlobalSetting('repeatheadings');
                $editsurvey .= "\t<li><label for='repeatheadings'>".$clang->gT("Number of answers to show before repeating the headings in array questions:")."</label>\n"
                . "\t\t<input id='repeatheadings' name='repeatheadings' value='$thisrepeatheadings' size='4' maxlength='4' /></li>\n";
                $editsurvey .= "\t</ul>\n";
            // End TAB page & form
            $editsurvey .= "\t</div><input type='hidden' name='action' value='globalsettingssave'/></form>\n";
            
            // End tabs
            $editsurvey .= "</div>\n";

            // The external button to sumbit Survey edit changes
            $editsurvey .= "\t<p><input type='button' onclick='$(\"#frmglobalsettings\").submit();' class='standardbtn' value='".$clang->gT("Save settings")."' /></p>\n";


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
    $usquery = "SELECT stg_value FROM ".db_table_name("settings_global")." where stg_name='$settingname'"; 
    $dbvalue=$connect->GetOne($usquery);
    if ($dbvalue===false)
    {
        if (isset($$settingname))
        {
            $dbvalue=$$settingname;
        }
    }
    return $dbvalue;
}
  
function setGlobalSetting($settingname,$settingvalue)
{
    global $connect, $$settingname;
    $usquery = "update ".db_table_name("settings_global")." set stg_value='".auto_escape($settingvalue)."' where stg_name='$settingname'"; 
    $connect->Execute($usquery);
    if ($connect->Affected_Rows()==0)
    {
        $usquery = "insert into  ".db_table_name("settings_global")." (stg_value,stg_name) values('".auto_escape($settingvalue)."','$settingname')"; 
        $connect->Execute($usquery);
    }
     
    $$settingname=$settingvalue;
}  


function checksettings()
{
    global $connect, $dbprefix, $clang, $databasename, $scriptname;
    //GET NUMBER OF SURVEYS
    $query = "SELECT count(sid) FROM ".db_table_name('surveys');
    $surveycount=$connect->GetOne($query);   //Checked  
    $query = "SELECT count(sid FROM ".db_table_name('surveys')." WHERE active='Y'";
    $activesurveycount=$connect->GetOne($query);  //Checked  
    $query = "SELECT count(users_name) FROM ".db_table_name('users');
    $usercount = $connect->GetOne($query);   //Checked    

    if ($activesurveycount==false) $activesurveycount=0;
    if ($surveycount==false) $surveycount=0;
    
    $tablelist = $connect->MetaTables();
    foreach ($tablelist as $table)
    {
        $stlength=strlen($dbprefix).strlen("old");
        if (substr($table, 0, $stlength+strlen("_tokens")) == $dbprefix."old_tokens")
        {
            $oldtokenlist[]=$table;
        }
        elseif (substr($table, 0, strlen($dbprefix) + strlen("tokens")) == $dbprefix."tokens")
        {
            $tokenlist[]=$table;
        }
        elseif (substr($table, 0, $stlength) == $dbprefix."old")
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
    $cssummary = "<div class='settingcaption'>".$clang->gT("System overview")."</div>\n";
    // Database name & default language
    $cssummary .= "<br /><table class='statisticssummary'><tr>\n"
    . "<td width='50%' align='right'>\n"
    . "<strong>".$clang->gT("Database name").":</strong>\n"
    . "</td><td>$databasename</td>\n"
    . "</tr>\n";
    // Other infos
    $cssummary .=  "<tr>\n"
    . "<td align='right'>\n"
    . "<strong>".$clang->gT("Users").":</strong>\n"
    . "</td><td>$usercount</td>\n"
    . "</tr>\n"
    . "<tr>\n"
    . "<td align='right'>\n"
    . "<strong>".$clang->gT("Surveys").":</strong>\n"
    . "</td><td>$surveycount</td>\n"
    . "</tr>\n"
    . "<tr>\n"
    . "<td align='right'>\n"
    . "<strong>".$clang->gT("Active surveys").":</strong>\n"
    . "</td><td>$activesurveycount</td>\n"
    . "</tr>\n"
    . "<tr>\n"
    . "<td align='right'>\n"
    . "<strong>".$clang->gT("Deactivated surveys").":</strong>\n"
    . "</td><td>$deactivatedsurveys</td>\n"
    . "</tr>\n"
    . "<tr>\n"
    . "<td align='right'>\n"
    . "<strong>".$clang->gT("Active token tables").":</strong>\n"
    . "</td><td>$activetokens</td>\n"
    . "</tr>\n"
    . "<tr>\n"
    . "<td align='right'>\n"
    . "<strong>".$clang->gT("Deactivated token tables").":</strong>\n"
    . "</td><td>$deactivatedtokens</td>\n"
    . "</tr>\n"
    . "</table>\n";
    
    if ($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1) 
    {
    $cssummary .= "<p><input type='button' onclick='window.open(\"$scriptname?action=showphpinfo\")'value='".$clang->gT("Show PHPInfo")."' />";
    }
    return $cssummary;
}

  
?>
