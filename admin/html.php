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
include_once("login_check.php");
if (isset($_POST['uid'])) {$postuserid=sanitize_int($_POST['uid']);}
if (isset($_POST['ugid'])) {$postusergroupid=sanitize_int($_POST['ugid']);}

if ($action == "listsurveys")
{
    $js_admin_includes[]='../scripts/jquery/jquery.tablesorter.min.js';
    $js_admin_includes[]='scripts/listsurvey.js';
    $query = " SELECT a.*, c.*, u.users_name FROM ".db_table_name('surveys')." as a "
    ." INNER JOIN ".db_table_name('surveys_languagesettings')." as c ON ( surveyls_survey_id = a.sid AND surveyls_language = a.language ) AND surveyls_survey_id=a.sid and surveyls_language=a.language "
    ." INNER JOIN ".db_table_name('users')." as u ON (u.uid=a.owner_id) ";

    if ($_SESSION['USER_RIGHT_SUPERADMIN'] != 1)
    {
        $query .= "WHERE a.sid in (select sid from ".db_table_name('survey_permissions')." where uid={$_SESSION['loginID']} and permission='survey' and read_p=1) ";
    }

    $query .= " ORDER BY surveyls_title";

    $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked

    if($result->RecordCount() > 0) {
        $listsurveys= "<br /><table class='listsurveys'><thead>
                  <tr>
                    <th colspan='7'>&nbsp;</th>
                    <th colspan='3'>".$clang->gT("Responses")."</th>
                    <th colspan='2'>&nbsp;</th>
                  </tr>
				  <tr>
				    <th>".$clang->gT("Status")."</th>
                    <th>".$clang->gT("SID")."</th>
				    <th>".$clang->gT("Survey")."</th>
				    <th>".$clang->gT("Date created")."</th>
				    <th>".$clang->gT("Owner") ."</th>
				    <th>".$clang->gT("Access")."</th>
				    <th>".$clang->gT("Anonymized responses")."</th>
				    <th>".$clang->gT("Full")."</th>
                    <th>".$clang->gT("Partial")."</th>
                    <th>".$clang->gT("Total")."</th>
                    <th>".$clang->gT("Tokens available")."</th>
                    <th>".$clang->gT("Response rate")."</th>
				  </tr></thead>
				  <tfoot><tr class='header ui-widget-header'>
		<td colspan=\"12\">&nbsp;</td>".
		"</tr></tfoot>
		<tbody>";
        $gbc = "evenrow";
        $dateformatdetails=getDateFormatData($_SESSION['dateformat']);

        while($rows = $result->FetchRow())
        {
            if($rows['private']=="Y")
            {
                $privacy=$clang->gT("Yes") ;
            }
            else $privacy =$clang->gT("No") ;


            if (tableExists('tokens_'.$rows['sid']))
            {
                $visibility = $clang->gT("Closed");
            }
            else
            {
                $visibility = $clang->gT("Open");
            }

            if($rows['active']=="Y")
            {
                if ($rows['expires']!='' && $rows['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust))
                {
                    $status=$clang->gT("Expired") ;
                }
                elseif ($rows['startdate']!='' && $rows['startdate'] > date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust))
                {
                    $status=$clang->gT("Not yet active") ;
                }
                else {
                    $status=$clang->gT("Active") ;
                }
                // Complete Survey Responses - added by DLR
                $gnquery = "SELECT count(id) FROM ".db_table_name("survey_".$rows['sid'])." WHERE submitdate IS NULL";
                $gnresult = db_execute_num($gnquery); //Checked
                while ($gnrow = $gnresult->FetchRow())
                {
                    $partial_responses=$gnrow[0];
                }
                $gnquery = "SELECT count(id) FROM ".db_table_name("survey_".$rows['sid']);
                $gnresult = db_execute_num($gnquery); //Checked
                while ($gnrow = $gnresult->FetchRow())
                {
                    $responses=$gnrow[0];
                }

            }
            else $status =$clang->gT("Inactive") ;


            $datetimeobj = new Date_Time_Converter($rows['datecreated'] , "Y-m-d H:i:s");
            $datecreated=$datetimeobj->convert($dateformatdetails['phpdate']);

            if (in_array($rows['owner_id'],getuserlist('onlyuidarray')))
            {
                $ownername=$rows['users_name'] ;
            }
            else
            {
                $ownername="---";
            }

            $questionsCount = 0;
            $questionsCountQuery = "SELECT * FROM ".db_table_name('questions')." WHERE sid={$rows['sid']} AND language='".$rows['language']."'"; //Getting a count of questions for this survey
            $questionsCountResult = $connect->Execute($questionsCountQuery); //Checked
            $questionsCount = $questionsCountResult->RecordCount();

            $listsurveys.="<tr>";

            if ($rows['active']=="Y")
            {
                if ($rows['expires']!='' && $rows['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust))
                {
                    $listsurveys .= "<td><img src='$imageurl/expired.png' "
                    . "alt='".$clang->gT("This survey is active but expired.")."' /></td>";
                }
                else
                {
                    if (bHasSurveyPermission($rows['sid'],'surveyactivation','update'))
                    {
                        $listsurveys .= "<td><a href=\"#\" onclick=\"window.open('$scriptname?action=deactivate&amp;sid={$rows['sid']}', '_top')\""
                        . " title=\"".$clang->gTview("This survey is active - click here to deactivate this survey.")."\" >"
                        . "<img src='$imageurl/active.png' alt='".$clang->gT("This survey is active - click here to deactivate this survey.")."' /></a></td>\n";
                    } else
                    {
                        $listsurveys .= "<td><img src='$imageurl/active.png' "
                        . "alt='".$clang->gT("This survey is currently active.")."' /></td>\n";
                    }
                }
            } else {
                if ( $questionsCount > 0 && bHasSurveyPermission($rows['sid'],'surveyactivation','update') )
                {
                    $listsurveys .= "<td><a href=\"#\" onclick=\"window.open('$scriptname?action=activate&amp;sid={$rows['sid']}', '_top')\""
                    . " title=\"".$clang->gTview("This survey is currently not active - click here to activate this survey.")."\" >"
                    . "<img src='$imageurl/inactive.png' title='' alt='".$clang->gT("This survey is currently not active - click here to activate this survey.")."' /></a></td>\n" ;
                } else
                {
                    $listsurveys .= "<td><img src='$imageurl/inactive.png'"
                    . " title='".$clang->gT("This survey is currently not active.")."' alt='".$clang->gT("This survey is currently not active.")."' />"
                    . "</td>\n";
                }
            }

            $listsurveys.="<td align='center'><a href='".$scriptname."?sid=".$rows['sid']."'>{$rows['sid']}</a></td>";
            $listsurveys.="<td align='left'><a href='".$scriptname."?sid=".$rows['sid']."'>{$rows['surveyls_title']}</a></td>".
					    "<td>".$datecreated."</td>".
					    "<td>".$ownername."</td>".
					    "<td>".$visibility."</td>" .
					    "<td>".$privacy."</td>";

            if ($rows['active']=="Y")
            {
                $complete = $responses - $partial_responses;
                $listsurveys .= "<td>".$complete."</td>";
                $listsurveys .= "<td>".$partial_responses."</td>";
                $listsurveys .= "<td>".$responses."</td>";
            }else{
                $listsurveys .= "<td>&nbsp;</td>";
                $listsurveys .= "<td>&nbsp;</td>";
                $listsurveys .= "<td>&nbsp;</td>";
            }
            
            if ($rows['active']=="Y" && tableExists("tokens_".$rows['sid']))
		    {
		    	//get the number of tokens for each survey
		    	$tokencountquery = "SELECT count(tid) FROM ".db_table_name("tokens_".$rows['sid']);
                            $tokencountresult = db_execute_num($tokencountquery); //Checked
                            while ($tokenrow = $tokencountresult->FetchRow())
                            {
                                $tokencount = $tokenrow[0];
                            }
                            
		    	//get the number of COMLETED tokens for each survey
		    	$tokencompletedquery = "SELECT count(tid) FROM ".db_table_name("tokens_".$rows['sid'])." WHERE completed!='N'";
                            $tokencompletedresult = db_execute_num($tokencompletedquery); //Checked
                            while ($tokencompletedrow = $tokencompletedresult->FetchRow())
                            {
                                $tokencompleted = $tokencompletedrow[0];
                            }
                            
                            //calculate percentage
                            
                            //prevent division by zero problems
                            if($tokencompleted != 0 && $tokencount != 0)
                            {
                            $tokenpercentage = round(($tokencompleted / $tokencount) * 100, 1);
                            }
                            else
                            {
                            $tokenpercentage = 0;
                            }
                            
                            $listsurveys .= "<td>".$tokencount."</td>";
                            $listsurveys .= "<td>".$tokenpercentage."%</td>";
		    }
		    else
		    {
				$listsurveys .= "<td>&nbsp;</td>";
				$listsurveys .= "<td>&nbsp;</td>";
		    }
		    
		    $listsurveys .= "</tr>" ;
        }

		$listsurveys.="</tbody>";
		$listsurveys.="</table><br />" ;
    }
    else $listsurveys="<p><strong> ".$clang->gT("No Surveys available - please create one.")." </strong><br /><br />" ;
}

if ($action == "personalsettings")
{

    // prepare data for the htmleditormode preference
    $edmod1='';
    $edmod2='';
    $edmod3='';
    $edmod4='';
    switch ($_SESSION['htmleditormode'])
    {
        case 'none':
            $edmod2="selected='selected'";
            break;
        case 'inline':
            $edmod3="selected='selected'";
            break;
        case 'popup':
            $edmod4="selected='selected'";
            break;
        default:
            $edmod1="selected='selected'";
            break;
    }

    $cssummary = "<div class='formheader'>"
    . "<strong>".$clang->gT("Your personal settings")."</strong>\n"
    . "</div>\n"
    . "<div>\n"
    . "<form action='{$scriptname}' id='personalsettings' method='post'>"
    . "<ul>\n";

    // Current language
    $cssummary .=  "<li>\n"
    . "<label for='lang'>".$clang->gT("Interface language").":</label>\n"
    . "<select id='lang' name='lang'>\n";
    foreach (getlanguagedata(true) as $langkey=>$languagekind)
    {
        $cssummary .= "<option value='$langkey'";
        if ($langkey == $_SESSION['adminlang']) {$cssummary .= " selected='selected'";}
        $cssummary .= ">".$languagekind['nativedescription']." - ".$languagekind['description']."</option>\n";
    }
    $cssummary .= "</select>\n"
    . "</li>\n";

    // Current htmleditormode
    $cssummary .=  "<li>\n"
    . "<label for='htmleditormode'>".$clang->gT("HTML editor mode").":</label>\n"
    . "<select id='htmleditormode' name='htmleditormode'>\n"
    . "<option value='default' {$edmod1}>".$clang->gT("Default")."</option>\n"
    . "<option value='inline' {$edmod3}>".$clang->gT("Inline HTML editor")."</option>\n"
    . "<option value='popup' {$edmod4}>".$clang->gT("Popup HTML editor")."</option>\n"
    . "<option value='none' {$edmod2}>".$clang->gT("No HTML editor")."</option>\n";
    $cssummary .= "</select>\n"
    . "</li>\n";

    // Date format
    $cssummary .=  "<li>\n"
    . "<label for='dateformat'>".$clang->gT("Date format").":</label>\n"
    . "<select name='dateformat' id='dateformat'>\n";
    foreach (getDateFormatData() as $index=>$dateformatdata)
    {
        $cssummary.= "<option value='{$index}'";
        if ($index==$_SESSION['dateformat'])
        {
            $cssummary.= "selected='selected'";
        }
         
        $cssummary.= ">".$dateformatdata['dateformat'].'</option>';
    }
    $cssummary .= "</select>\n"
    . "</li>\n"
    . "</ul>\n"
    . "<p><input type='hidden' name='action' value='savepersonalsettings' /><input class='submit' type='submit' value='".$clang->gT("Save settings")
    ."' /></p></form></div>";
}



if (isset($surveyid) && $surveyid &&
$action!='dataentry' && $action!='browse' && $action!='exportspss' &&
$action!='statistics' && $action!='importoldresponses' && $action!='exportr' &&
$action!='vvimport' && $action!='vvexport' && $action!='exportresults')
{
    if(bHasSurveyPermission($surveyid,'survey','read'))
    {
        $js_admin_includes[]='../scripts/jquery/jquery.cookie.js';
        $js_admin_includes[]='../scripts/jquery/superfish.js';
        $js_admin_includes[]='../scripts/jquery/hoverIntent.js';
        $js_admin_includes[]='scripts/surveytoolbar.js';
        $css_admin_includes[]= $homeurl."/styles/default/superfish.css";        
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $sumquery3 = "SELECT * FROM ".db_table_name('questions')." WHERE sid={$surveyid} AND parent_qid=0 AND language='".$baselang."'"; //Getting a count of questions for this survey
        $sumresult3 = $connect->Execute($sumquery3); //Checked
        $sumcount3 = $sumresult3->RecordCount();
        $sumquery6 = "SELECT count(*) FROM ".db_table_name('conditions')." as c, ".db_table_name('questions')." as q WHERE c.qid = q.qid AND q.sid=$surveyid"; //Getting a count of conditions for this survey
        $sumcount6 = $connect->GetOne($sumquery6); //Checked
        $sumquery2 = "SELECT * FROM ".db_table_name('groups')." WHERE sid={$surveyid} AND language='".$baselang."'"; //Getting a count of groups for this survey
        $sumresult2 = $connect->Execute($sumquery2); //Checked
        $sumcount2 = $sumresult2->RecordCount();
        $sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
        $sumresult1 = db_select_limit_assoc($sumquery1, 1) ; //Checked
        if ($sumresult1->RecordCount()==0){die('Invalid survey id');} //  if surveyid is invalid then die to prevent errors at a later time
        // Output starts here...
        $surveysummary = "";

        $surveyinfo = $sumresult1->FetchRow();

        $surveyinfo = array_map('FlattenText', $surveyinfo);
        //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
        $activated = $surveyinfo['active'];

        ////////////////////////////////////////////////////////////////////////
        // SURVEY MENU BAR
        ////////////////////////////////////////////////////////////////////////

        $surveysummary .= ""  //"<tr><td colspan=2>\n"
        . "<div class='menubar surveybar'>\n"
        . "<div class='menubar-title ui-widget-header'>\n"
        . "<strong>".$clang->gT("Survey")."</strong> "
        . "<span class='basic'>{$surveyinfo['surveyls_title']} (".$clang->gT("ID").":{$surveyid})</span></div>\n"
        . "<div class='menubar-main'>\n"
        . "<div class='menubar-left'>\n";


        // ACTIVATE SURVEY BUTTON

        if ($activated == "N" )
        {
            $surveysummary .= "<img src='{$imageurl}/inactive.png' "
            . "alt='".$clang->gT("This survey is not currently active")."' />\n";
            if($sumcount3>0 && bHasSurveyPermission($surveyid,'surveyactivation','update'))
            {
                $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=activate&amp;sid=$surveyid', '_top')\""
                . " title=\"".$clang->gTview("Activate this Survey")."\" >"
                . "<img src='{$imageurl}/activate.png' name='ActivateSurvey' alt='".$clang->gT("Activate this Survey")."'/></a>\n" ;
            }
            else
            {
                $surveysummary .= "<img src='{$imageurl}/activate_disabled.png' alt='"
                . $clang->gT("Survey cannot be activated. Either you have no permission or there are no questions.")."' />\n" ;
            }
        }
        elseif ($activated == "Y")
        {
            if ($surveyinfo['expires']!='' && ($surveyinfo['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust)))
            {
                $surveysummary .= "<img src='{$imageurl}/expired.png' "
                . "alt='".$clang->gT("This survey is active but expired.")."' />\n";
            }
            elseif (($surveyinfo['startdate']!='') && ($surveyinfo['startdate'] > date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust)))
            {
                $surveysummary .= "<img src='{$imageurl}/notyetstarted.png' "
                . "alt='".$clang->gT("This survey is active but has a start date.")."' />\n";
            }
            else
            {
                $surveysummary .= "<img src='{$imageurl}/active.png' title='' "
                . "alt='".$clang->gT("This survey is currently active")."' />\n";
            }
            if(bHasSurveyPermission($surveyid,'surveyactivation','update'))
            {
                $surveysummary .= "<a href=\"#\" onclick=\"window.open('{$scriptname}?action=deactivate&amp;sid=$surveyid', '_top')\""
                . " title=\"".$clang->gTview("Deactivate this Survey")."\" >"
                . "<img src='{$imageurl}/deactivate.png' alt='".$clang->gT("Deactivate this Survey")."' /></a>\n" ;
            }
            else
            {
                $surveysummary .= "<img src='{$imageurl}/blank.gif' alt='' width='14' />\n";
            }
        }

        $surveysummary .= "<img src='{$imageurl}/seperator.gif' alt=''  />\n";


        // ACTIVATE SURVEY BUTTON

        if ($activated == "N")
        {
            $icontext=$clang->gT("Test This Survey");
            $icontext2=$clang->gTview("Test This Survey");
        } else
        {
            $icontext=$clang->gT("Execute This Survey");
            $icontext2=$clang->gTview("Execute This Survey");
        }
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
        {
            $surveysummary .= "<a href='#' accesskey='d' onclick=\"window.open('"
            . $publicurl."/index.php?sid={$surveyid}&amp;newtest=Y&amp;lang={$baselang}', '_blank')\" title=\"{$icontext2}\" >"
            . "<img src='{$imageurl}/do.png' alt='{$icontext}' />"
            . "</a>\n";

        } else {
            $surveysummary .= "<a href='#' id='dosurvey' class='dosurvey'"
            . "title=\"{$icontext2}\" accesskey='d'>"
            . "<img  src='{$imageurl}/do.png' alt='{$icontext}' />"
            . "</a>\n";

            $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $tmp_survlangs[] = $baselang;
            rsort($tmp_survlangs);
            // Test Survey Language Selection Popup
            $surveysummary .="<div class=\"langpopup\" id=\"dosurveylangpopup\">".$clang->gT("Please select a language:")."<ul>";
            foreach ($tmp_survlangs as $tmp_lang)
            {
                $surveysummary .= "<li><a accesskey='d' onclick=\"$('.dosurvey').qtip('hide');\" target='_blank' href='{$publicurl}/index.php?sid=$surveyid&amp;newtest=Y&amp;lang={$tmp_lang}'>".getLanguageNameFromCode($tmp_lang,false)."</a></li>";
            }
            $surveysummary .= "</ul></div>";
        }

        // SEPARATOR
        $surveysummary .= "<img src='{$imageurl}/seperator.gif' alt=''  />\n"
        . "</div>\n";

        
        // Start of suckerfish menu
        $surveysummary .= "<ul class='sf-menu'>\n"
        . "<li><a href='#'>"
        . "<img src='$imageurl/edit.png' name='EditSurveyProperties' alt='".$clang->gT("Survey properties")."' /></a><ul>\n";

        // EDIT SURVEY TEXT ELEMENTS BUTTON
        if(bHasSurveyPermission($surveyid,'surveylocale','read'))
        {
            $surveysummary .= "<li><a href='{$scriptname}?action=editsurveylocalesettings&amp;sid={$surveyid}' >"
            . "<img src='{$imageurl}/edit_30.png' name='EditTextElements' /> ".$clang->gT("Edit text elements")."</a></li>\n";
        }

        // EDIT SURVEY SETTINGS BUTTON
        if(bHasSurveyPermission($surveyid,'surveysettings','read'))
        {
            $surveysummary .= "<li><a href='{$scriptname}?action=editsurveysettings&amp;sid={$surveyid}' >"
            . "<img src='{$imageurl}/token_manage_30.png' name='EditGeneralSettings' /> ".$clang->gT("General settings")."</a></li>\n";
        }
        
        // Survey permission item
        if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $surveyinfo['owner_id'] == $_SESSION['loginID'])
        {
            $surveysummary .= "<li><a href='{$scriptname}?action=surveysecurity&amp;sid={$surveyid}'>"
            . "<img src='{$imageurl}/survey_security_30.png' name='SurveySecurity'/> ".$clang->gT("Survey permissions")."</a></li>\n";
        }        
        
        // CHANGE QUESTION GROUP ORDER BUTTON
        if (bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            if($activated=="Y")
            {
                $surveysummary .= "<li><a href=\"#\" onclick=\"alert('".$clang->gT("You can't reorder question groups if the survey is active.", "js")."');\" >"
                . "<img src='$imageurl/reorder_disabled_30.png' name='translate'/> ".$clang->gT("Reorder question groups")."</a></li>\n";
            }
            elseif (getGroupSum($surveyid,$surveyinfo['language'])>1)
            {
                $surveysummary .= "<li><a href='{$scriptname}?action=ordergroups&amp;sid={$surveyid}'>"
                . "<img src='{$imageurl}/reorder_30.png' /> ".$clang->gT("Reorder question groups")."</a></li>\n";
            }       
            else{
                $surveysummary .= "<li><a href=\"#\" onclick=\"alert('".$clang->gT("You can't reorder question groups if there is only one group.", "js")."');\" >"
                . "<img src='$imageurl/reorder_disabled_30.png' name='translate'/> ".$clang->gT("Reorder question groups")."</a></li>\n";
            } 
            
        }

        // SET SURVEY QUOTAS BUTTON
        if (bHasSurveyPermission($surveyid,'quotas','read'))
        {
            $surveysummary .= "<li><a href='{$scriptname}?action=quotas&amp;sid={$surveyid}'>"
            . "<img src='{$imageurl}/quota_30.png' /> ".$clang->gT("Quotas")."</a></li>\n" ;
        }
        
        // Assessment menu item
        if (bHasSurveyPermission($surveyid,'assessments','read'))
        {
            $surveysummary .= "<li><a href='{$scriptname}?action=assessments&amp;sid={$surveyid}'>"
            . "<img src='{$imageurl}/assessments_30.png' /> ".$clang->gT("Assessments")."</a></li>\n" ;
        }

        // EDIT SURVEY TEXT ELEMENTS BUTTON
        if(bHasSurveyPermission($surveyid,'surveylocale','read'))
        {
            $surveysummary .= "<li><a href='{$scriptname}?action=emailtemplates&amp;sid={$surveyid}' >"
            . "<img src='{$imageurl}/emailtemplates_30.png' name='EditEmailTemplates' /> ".$clang->gT("Email templates")."</a></li>\n";
        }        
        
        $surveysummary .='</ul></li>'; // End if survey properties


        // Tools menu item     
        $surveysummary .= "<li><a href=\"#\">"
        . "<img src='{$imageurl}/tools.png' name='SorveyTools' alt='".$clang->gT("Tools")."' /></a><ul>\n";
      
      
        // Delete survey item
        if (bHasSurveyPermission($surveyid,'survey','delete'))
        {
            //            $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=deletesurvey&amp;sid=$surveyid', '_top')\""
            $surveysummary .= "<li><a href=\"#\" onclick=\"".get2post("{$scriptname}?action=deletesurvey&amp;sid={$surveyid}")."\">"
            . "<img src='{$imageurl}/delete_30.png' name='DeleteSurvey' /> ".$clang->gT("Delete survey")."</a></li>\n" ;
        }
            
            
        // Translate survey item
        if (bHasSurveyPermission($surveyid,'translations','read'))
        {
          // Check if multiple languages have been activated
          $supportedLanguages = getLanguageData(false);
          if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) > 0)
          {
            $surveysummary .= "<li><a href='{$scriptname}?action=translate&amp;sid={$surveyid}'>"
            . "<img src='{$imageurl}/translate_30.png' name='translate' /> ".$clang->gT("Quick-translation")."</a></li>\n";
          }
          else
          {
            $surveysummary .= "<li><a href=\"#\" onclick=\"alert('".$clang->gT("Currently there are no additional languages configured for this survey.", "js")."');\" >"
            . "<img src='$imageurl/translate_disabled_30.png' name='translate'/> ".$clang->gT("Quick-translation")."</a></li>\n";
          }
        }            
         
        // RESET SURVEY LOGIC BUTTON

        if (bHasSurveyPermission($surveyid,'surveycontent','update'))
        {
            if ($sumcount6 > 0) {
                $surveysummary .= "<li><a href=\"#\" onclick=\"".get2post("{$scriptname}?action=resetsurveylogic&amp;sid=$surveyid")."\">"
                . "<img src='{$imageurl}/resetsurveylogic_30.png' name='ResetSurveyLogic'> ".$clang->gT("Reset conditions")."</a></li>\n";
            }
            else
            {
                $surveysummary .= "<li><a href=\"#\" onclick=\"alert('".$clang->gT("Currently there are no conditions configured for this survey.", "js")."');\" >"
                . "<img src='{$imageurl}/resetsurveylogic_disabled_30.png' name='ResetSurveyLogic'/> ".$clang->gT("Reset Survey Logic")."</a></li>\n";
            }
        }         
        $surveysummary .='</ul></li>' ;
            

        
        // Display/Export main menu item     
        $surveysummary .= "<li><a href='#'>"
        . "<img src='{$imageurl}/display_export.png' name='DisplayExport' alt='".$clang->gT("Display / Export")."' /></a><ul>\n";
        
        // Eport menu item
        if (bHasSurveyPermission($surveyid,'surveycontent','export'))
        {
            $surveysummary .= "<li><a href='{$scriptname}?action=exportstructure&amp;sid={$surveyid}'>"
            . "<img src='{$imageurl}/export_30.png' /> ". $clang->gT("Export survey")."</a></li>\n" ;
        }

        // PRINTABLE VERSION OF SURVEY BUTTON

        if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
        {
            $surveysummary .= "<li><a href='{$scriptname}?action=showprintablesurvey&amp;sid={$surveyid}'>"
            . "<img src='{$imageurl}/print_30.png' name='ShowPrintableSurvey' /> ".$clang->gT("Printable version")."</a></li>";
        }
        else
        {
            $surveysummary .= "<li><a href='{$scriptname}?action=showprintablesurvey&amp;sid={$surveyid}'>"
            . "<img src='{$imageurl}/print_30.png' name='ShowPrintableSurvey' /> ".$clang->gT("Printable version")."</a><ul>";
            $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            $tmp_survlangs[] = $baselang;
            rsort($tmp_survlangs);
            foreach ($tmp_survlangs as $tmp_lang)
            {
                $surveysummary .= "<li><a href='{$scriptname}?action=showprintablesurvey&amp;sid={$surveyid}&amp;lang={$tmp_lang}'><img src='{$imageurl}/print_30.png' /> ".getLanguageNameFromCode($tmp_lang,false)."</a></li>";
            }
            $surveysummary.='</ul></li>';
        }
        
        
        // SHOW PRINTABLE AND SCANNABLE VERSION OF SURVEY BUTTON

        if(bHasSurveyPermission($surveyid,'surveycontent','export'))
        {
            if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
            {

                $surveysummary .= "<li><a href='{$scriptname}?action=showquexmlsurvey&amp;sid={$surveyid}'>"
                . "<img src='{$imageurl}/scanner_30.png' name='ShowPrintableScannableSurvey' /> ".$clang->gT("QueXML export")."</a></li>";

            } else {

                $surveysummary .= "<li><a href='{$scriptname}?action=showquexmlsurvey&amp;sid={$surveyid}'>"
                . "<img src='{$imageurl}/scanner_30.png' name='ShowPrintableScannableSurvey' /> ".$clang->gT("QueXML export")."</a><ul>";

                $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                $baselang = GetBaseLanguageFromSurveyID($surveyid);
                $tmp_survlangs[] = $baselang;
                rsort($tmp_survlangs);

                // Test Survey Language Selection Popup
                foreach ($tmp_survlangs as $tmp_lang)
                {
                    $surveysummary .= "<li><a href='{$scriptname}?action=showquexmlsurvey&amp;sid={$surveyid}&amp;lang={$tmp_lang}'>
                    <img src='{$imageurl}/scanner_30.png' /> ".getLanguageNameFromCode($tmp_lang,false)."</a></li>";
                }
                $surveysummary .= "</ul></li>";
            }
        }      
        $surveysummary .='</ul></li>' ;
        
        
        
        // Display/Export main menu item     
        $surveysummary .= "<li><a href='#'><img src='{$imageurl}/responses.png' name='Responses' alt='".$clang->gT("Responses")."' /></a><ul>\n";

        //browse responses menu item
        if (bHasSurveyPermission($surveyid,'responses','read') || bHasSurveyPermission($surveyid,'statistics','read'))
        {
            if ($activated == "Y")
            {
                $surveysummary .= "<li><a href='{$scriptname}?action=browse&amp;sid={$surveyid}'>"
                . "<img src='{$imageurl}/browse_30.png' name='BrowseSurveyResults' /> ".$clang->gT("Responses & statistics")."</a></li>\n";
            }
            else
            {
                $surveysummary .= "<li><a href='#' onclick=\"alert('".$clang->gT("This survey is not active - no responses are available.","js")."')\">"
                . "<img src='{$imageurl}/browse_disabled_30.png' name='BrowseSurveyResults' /> ".$clang->gT("Responses & statistics")."</a></li>\n";
            }
            
        }
        
        // Data entry screen menu item
        if (bHasSurveyPermission($surveyid,'responses','create'))
        {
            if($activated == "Y")
            {
                $surveysummary .= "<li><a href='{$scriptname}?action=dataentry&amp;sid={$surveyid}'>"
                . "<img src='{$imageurl}/dataentry_30.png' /> ".$clang->gT("Data entry screen")."</a></li>\n";
            }
            else {
                $surveysummary .= "<li><a href='#' onclick=\"alert('".$clang->gT("This survey is not active, data entry is not allowed","js")."')\">"
                . "<img src='{$imageurl}/dataentry_disabled_30.png'/> ".$clang->gT("Data entry screen")."</a></li>\n";
            }        
        }        
        
        
        
        if (bHasSurveyPermission($surveyid,'responses','read'))
        {
            if ($activated == "Y")
            {
                $surveysummary .= "<li><a href='#' onclick=\"window.open('{$scriptname}?action=saved&amp;sid=$surveyid', '_top')\" >"
                . "<img src='{$imageurl}/saved_30.png' name='BrowseSaved' /> ".$clang->gT("Partial (saved) responses")."</a></li>\n";
            }
            else
            {
                $surveysummary .= "<li><a href='#' onclick=\"alert('".$clang->gT("This survey is not active - no responses are available.","js")."')\">"
                . "<img src='{$imageurl}/saved_disabled_30.png' name='PartialResponses' /> ".$clang->gT("Partial (saved) responses")."</a></li>\n";
            }
            
        }

        $surveysummary .='</ul></li>' ;

                              
        // TOKEN MANAGEMENT BUTTON

        if (bHasSurveyPermission($surveyid,'surveysettings','update') || bHasSurveyPermission($surveyid,'tokens','read'))
        {
         //   $surveysummary .= "<img src='$imageurl/seperator.gif' alt=''  />\n";
            $surveysummary .="<li><a href='#' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\""
            . " title=\"".$clang->gTview("Token management")."\" >"
            . "<img src='$imageurl/tokens.png' name='TokensControl' alt='".$clang->gT("Token management")."' /></a></li>\n" ;
        }

 
        $surveysummary .= "</ul>";
        
        // End of survey toolbat 2nd page

        ////////////////////////////////////////////////////////////////////////
        // QUESTION GROUP TOOLBAR
        ////////////////////////////////////////////////////////////////////////

        $surveysummary.= "<div class='menubar-right'>\n";
        if (bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            $surveysummary .= "<span class=\"boxcaption\">".$clang->gT("Question groups").":</span>"
            . "<select name='groupselect' onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";

            if (getgrouplistlang($gid, $baselang))
            {
                $surveysummary .= getgrouplistlang($gid, $baselang);
            }
            else
            {
                $surveysummary .= "<option>".$clang->gT("None")."</option>\n";
            }
            $surveysummary .= "</select>\n";
        }
        else
        {
            $gid=null;
            $qid=null;
        }
            

        // ADD NEW GROUP TO SURVEY BUTTON

        if(bHasSurveyPermission($surveyid,'surveycontent','create'))
        {
            if ($activated == "Y")
            {
                $surveysummary .= "<a href='#'>"
                ."<img src='$imageurl/add_disabled.png' title='' alt='".$clang->gT("Disabled").' - '.$clang->gT("This survey is currently active.")."' " .
                " name='AddNewGroup' /></a>\n";
            }
            else
            {
                $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=addgroup&amp;sid=$surveyid', '_top')\""
                . " title=\"".$clang->gTview("Add new group to survey")."\">"
                . "<img src='$imageurl/add.png' alt='".$clang->gT("Add new group to survey")."' name='AddNewGroup' /></a>\n";
            }
        }
        $surveysummary .= "<img src='$imageurl/seperator.gif' alt='' />\n"
        . "<img src='$imageurl/blank.gif' width='15' alt='' />"
        . "<input type='image' src='$imageurl/minus.gif' title='". $clang->gT("Hide details of this Survey")."' "
        . "alt='". $clang->gT("Hide details of this Survey")."' name='MinimiseSurveyWindow' "
        . "onclick='document.getElementById(\"surveydetails\").style.display=\"none\";' />\n";

        $surveysummary .= "<input type='image' src='$imageurl/plus.gif' title='". $clang->gT("Show details of this survey")."' "
        . "alt='". $clang->gT("Show details of this survey")."' name='MaximiseSurveyWindow' "
        . "onclick='document.getElementById(\"surveydetails\").style.display=\"\";' />\n";

        if (!$gid)
        {
            $surveysummary .= "<input type='image' src='$imageurl/close.gif' title='". $clang->gT("Close this survey")."' "
            . "alt='".$clang->gT("Close this survey")."' name='CloseSurveyWindow' "
            . "onclick=\"window.open('$scriptname', '_top')\" />\n";
        }
        else
        {
            $surveysummary .= "<img src='$imageurl/blank.gif' width='18' alt='' />\n";
        }



        $surveysummary .= "</div>\n"
        . "</div>\n"
        . "</div>\n";

        //SURVEY SUMMARY
        if ($gid || $qid || $action=="deactivate"|| $action=="activate" || $action=="surveysecurity"
        || $action=="surveyrights" || $action=="addsurveysecurity" || $action=="addusergroupsurveysecurity"
        || $action=="setsurveysecurity" ||  $action=="setusergroupsurveysecurity" || $action=="delsurveysecurity"
        || $action=="editsurveysettings"|| $action=="editsurveylocalesettings" || $action=="updatesurveysettingsandeditlocalesettings" || $action=="addgroup" || $action=="importgroup"
        || $action=="ordergroups" || $action=="deletesurvey" || $action=="resetsurveylogic"
        || $action=="importsurveyresources" || $action=="translate"  || $action=="emailtemplates" 
        || $action=="exportstructure" || $action=="quotas" || $action=="copysurvey") {$showstyle="style='display: none'";}
        if (!isset($showstyle)) {$showstyle="";}
        $aAdditionalLanguages = GetAdditionalLanguagesFromSurveyID($surveyid);
        $surveysummary .= "<table $showstyle id='surveydetails'><tr><td align='right' valign='top' width='15%'>"
        . "<strong>".$clang->gT("Title").":</strong></td>\n"
        . "<td align='left' class='settingentryhighlight'><strong>{$surveyinfo['surveyls_title']} "
        . "(".$clang->gT("ID")." {$surveyinfo['sid']})</strong></td></tr>\n";
        $surveysummary2 = "";
        if ($surveyinfo['private'] != "N") {$surveysummary2 .= $clang->gT("Answers to this survey are anonymized.")."<br />\n";}
        else {$surveysummary2 .= $clang->gT("This survey is NOT anonymous.")."<br />\n";}
        if ($surveyinfo['format'] == "S") {$surveysummary2 .= $clang->gT("It is presented question by question.")."<br />\n";}
        elseif ($surveyinfo['format'] == "G") {$surveysummary2 .= $clang->gT("It is presented group by group.")."<br />\n";}
        else {$surveysummary2 .= $clang->gT("It is presented on one single page.")."<br />\n";}
        if ($surveyinfo['datestamp'] == "Y") {$surveysummary2 .= $clang->gT("Responses will be date stamped")."<br />\n";}
        if ($surveyinfo['ipaddr'] == "Y") {$surveysummary2 .= $clang->gT("IP Addresses will be logged")."<br />\n";}
        if ($surveyinfo['refurl'] == "Y") {$surveysummary2 .= $clang->gT("Referer-URL will be saved")."<br />\n";}
        if ($surveyinfo['usecookie'] == "Y") {$surveysummary2 .= $clang->gT("It uses cookies for access control.")."<br />\n";}
        if ($surveyinfo['allowregister'] == "Y") {$surveysummary2 .= $clang->gT("If tokens are used, the public may register for this survey")."<br />\n";}
        if ($surveyinfo['allowsave'] == "Y" && $surveyinfo['tokenanswerspersistence'] == 'N') {$surveysummary2 .= $clang->gT("Participants can save partially finished surveys")."<br />\n";}
        if ($surveyinfo['emailnotificationto'] != '') 
        {
            $surveysummary2 .= $clang->gT("Basic email notification is sent to:")." {$surveyinfo['emailnotificationto']}<br />\n";
        }
        if ($surveyinfo['emailresponseto'] != '') 
        {
            $surveysummary2 .= $clang->gT("Detailed email notification with response data is sent to:")." {$surveyinfo['emailresponseto']}<br />\n";
        }

        if(bHasSurveyPermission($surveyid,'surveycontent','update'))
        {
            $surveysummary2 .= $clang->gT("Regenerate question codes:")
            . " [<a href='#' "
            . "onclick=\"if (confirm('".$clang->gT("Are you sure you want regenerate the question codes?","js")."')) {".get2post("$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=straight")."}\" "
            . ">".$clang->gT("Straight")."</a>] "
            . " [<a href='#' "
            . "onclick=\"if (confirm('".$clang->gT("Are you sure you want regenerate the question codes?","js")."')) {".get2post("$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=bygroup")."}\" "
            . ">".$clang->gT("By Group")."</a>]";
            $surveysummary2 .= "</td></tr>\n";
        }
        $surveysummary .= "<tr>"
        . "<td align='right' valign='top'><strong>"
        . $clang->gT("Survey URL") ." (".getLanguageNameFromCode($surveyinfo['language'],false)."):</strong></td>\n";
        if ( $modrewrite ) {
            $tmp_url = $GLOBALS['publicurl'] . '/' . $surveyinfo['sid'];
            $surveysummary .= "<td align='left'> <a href='$tmp_url/lang-".$surveyinfo['language']."' target='_blank'>$tmp_url/lang-".$surveyinfo['language']."</a>";
            foreach ($aAdditionalLanguages as $langname)
            {
                $surveysummary .= "&nbsp;<a href='$tmp_url/lang-$langname' target='_blank'><img title='".$clang->gT("Survey URL for language:")." ".getLanguageNameFromCode($langname,false)."' alt='".getLanguageNameFromCode($langname,false)." ".$clang->gT("Flag")."' src='../images/flags/$langname.png' /></a>";
            }
        } else {
            $tmp_url = $GLOBALS['publicurl'] . '/index.php?sid=' . $surveyinfo['sid'];
            $surveysummary .= "<td align='left'> <a href='$tmp_url&amp;lang=".$surveyinfo['language']."' target='_blank'>$tmp_url&amp;lang=".$surveyinfo['language']."</a>";
            foreach ($aAdditionalLanguages as $langname)
            {
                $surveysummary .= "&nbsp;<a href='$tmp_url&amp;lang=$langname' target='_blank'><img title='".$clang->gT("Survey URL for language:")." ".getLanguageNameFromCode($langname,false)."' alt='".getLanguageNameFromCode($langname,false)." ".$clang->gT("Flag")."' src='../images/flags/$langname.png' /></a>";
            }
        }

        $surveysummary .= "</td></tr>\n"
        . "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Description:")."</strong></td>\n<td align='left'>";
        if (trim($surveyinfo['surveyls_description'])!='') {$surveysummary .= " {$surveyinfo['surveyls_description']}";}
        $surveysummary .= "</td></tr>\n"
        . "<tr >\n"
        . "<td align='right' valign='top'><strong>"
        . $clang->gT("Welcome:")."</strong></td>\n"
        . "<td align='left'> {$surveyinfo['surveyls_welcometext']}</td></tr>\n"
        . "<tr ><td align='right' valign='top'><strong>"
        . $clang->gT("Administrator:")."</strong></td>\n"
        . "<td align='left'> {$surveyinfo['admin']} ({$surveyinfo['adminemail']})</td></tr>\n"
        . "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Fax To:")."</strong></td>\n<td align='left'>";
        if (trim($surveyinfo['faxto'])!='') {$surveysummary .= " {$surveyinfo['faxto']}";}
        $surveysummary .= "</td></tr>\n"
        . "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Start date/time:")."</strong></td>\n";
        $dateformatdetails=getDateFormatData($_SESSION['dateformat']);
        if (trim($surveyinfo['startdate'])!= '')
        {
            $datetimeobj = new Date_Time_Converter($surveyinfo['startdate'] , "Y-m-d H:i:s");
            $startdate=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
        }
        else
        {
            $startdate="-";
        }
        $surveysummary .= "<td align='left'>$startdate</td></tr>\n"
        . "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Expiry date/time:")."</strong></td>\n";
        if (trim($surveyinfo['expires'])!= '')
        {
            $datetimeobj = new Date_Time_Converter($surveyinfo['expires'] , "Y-m-d H:i:s");
            $expdate=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
        }
        else
        {
            $expdate="-";
        }
        $surveysummary .= "<td align='left'>$expdate</td></tr>\n"
        . "<tr ><td align='right' valign='top'><strong>"
        . $clang->gT("Template:")."</strong></td>\n"
        . "<td align='left'> {$surveyinfo['template']}</td></tr>\n"

        . "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Base Language:")."</strong></td>\n";
        if (!$surveyinfo['language']) {$language=getLanguageNameFromCode($currentadminlang,false);} else {$language=getLanguageNameFromCode($surveyinfo['language'],false);}
        $surveysummary .= "<td align='left'>$language</td></tr>\n";

        // get the rowspan of the Additionnal languages row
        // is at least 1 even if no additionnal language is present
        $additionnalLanguagesCount = count($aAdditionalLanguages);
        if ($additionnalLanguagesCount == 0) $additionnalLanguagesCount = 1;
        $surveysummary .= "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Additional Languages").":</strong></td>\n";

        $first=true;
        foreach ($aAdditionalLanguages as $langname)
        {
            if ($langname)
            {
                if (!$first) {$surveysummary .= "<tr><td>&nbsp;</td>";}
                $first=false;
                $surveysummary .= "<td align='left'>".getLanguageNameFromCode($langname,false)."</td></tr>\n";
            }
        }
        if ($first) $surveysummary .= "</tr>";

        if ($surveyinfo['surveyls_urldescription']==""){$surveyinfo['surveyls_urldescription']=htmlspecialchars($surveyinfo['surveyls_url']);}
        $surveysummary .= "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Exit Link").":</strong></td>\n"
        . "<td align='left'>";                                             
        if ($surveyinfo['surveyls_url']!="") {$surveysummary .=" <a href=\"".htmlspecialchars($surveyinfo['surveyls_url'])."\" title=\"".htmlspecialchars($surveyinfo['surveyls_url'])."\">{$surveyinfo['surveyls_urldescription']}</a>";}
        $surveysummary .="</td></tr>\n";
        $surveysummary .= "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Number of questions/groups").":</strong></td><td>$sumcount3/$sumcount2</td></tr>\n";
        $surveysummary .= "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Survey currently active").":</strong></td><td>";
        if ($activated == "N")
        {
            $surveysummary .= $clang->gT("No");
        }
        else
        {
            $surveysummary .= $clang->gT("Yes");
        }
        $surveysummary .="</td></tr>\n";
         
        if ($activated == "Y")
        {
            $surveysummary .= "<tr><td align='right' valign='top'><strong>"
            . $clang->gT("Survey table name").":</strong></td><td>".$dbprefix."survey_$surveyid</td></tr>\n";
        }
        $surveysummary .= "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Hints").":</strong></td><td>\n";

        if ($activated == "N" && $sumcount3 == 0)
        {
            $surveysummary .= $clang->gT("Survey cannot be activated yet.")."<br />\n";
            if ($sumcount2 == 0 && bHasSurveyPermission($surveyid,'surveycontent','create'))
            {
                $surveysummary .= "<span class='statusentryhighlight'>[".$clang->gT("You need to add question groups")."]</span><br />";
            }
            if ($sumcount3 == 0 && bHasSurveyPermission($surveyid,'surveycontent','create'))
            {
                $surveysummary .= "<span class='statusentryhighlight'>[".$clang->gT("You need to add questions")."]</span><br />";
            }
        }
        $surveysummary .=  $surveysummary2
        . "</table>\n";
    }
    else
    {
        include("access_denied.php");
    }
}


if (isset($surveyid) && $surveyid && $gid )   // Show the group toolbar
{
    // TODO: check that surveyid and thus baselang are always set here
    $sumquery4 = "SELECT * FROM ".db_table_name('questions')." WHERE sid=$surveyid AND
	gid=$gid AND language='".$baselang."'"; //Getting a count of questions for this survey
    $sumresult4 = $connect->Execute($sumquery4); //Checked
    $sumcount4 = $sumresult4->RecordCount();
    $grpquery ="SELECT * FROM ".db_table_name('groups')." WHERE gid=$gid AND
	language='".$baselang."' ORDER BY ".db_table_name('groups').".group_order";
    $grpresult = db_execute_assoc($grpquery); //Checked

    // Check if other questions/groups are dependent upon this group
    $condarray=GetGroupDepsForConditions($surveyid,"all",$gid,"by-targgid");

    $groupsummary = "<div class='menubar'>\n"
    . "<div class='menubar-title ui-widget-header'>\n";

    while ($grow = $grpresult->FetchRow())
    {
        $grow = array_map('FlattenText', $grow);
        $groupsummary .= '<strong>'.$clang->gT("Question group").'</strong>&nbsp;'
        . "<span class='basic'>{$grow['group_name']} (".$clang->gT("ID").":$gid)</span>\n"
        . "</div>\n"
        . "<div class='menubar-main'>\n"
        . "<div class='menubar-left'>\n";


//        // CREATE BLANK SPACE FOR IMAGINARY BUTTONS
//
//
      $groupsummary .= ""
        . "<img src='$imageurl/blank.gif' alt='' width='54' height='20'  />\n"
        . "<img src='$imageurl/seperator.gif' alt=''  />"
        . "<img src='$imageurl/blank.gif' alt='' width='50' height='20'  />";


        // EDIT CURRENT QUESTION GROUP BUTTON

        if(bHasSurveyPermission($surveyid,'surveycontent','update'))
        {
            $groupsummary .=  "<img src='$imageurl/seperator.gif' alt=''  />\n"
            . "<a href=\"#\" onclick=\"window.open('$scriptname?action=editgroup&amp;sid=$surveyid&amp;gid=$gid','_top')\""
            . " title=\"".$clang->gTview("Edit current question group")."\">"
            . "<img src='$imageurl/edit.png' alt='".$clang->gT("Edit current question group")."' name='EditGroup' /></a>\n" ;
        }


        // DELETE CURRENT QUESTION GROUP BUTTON

        if (bHasSurveyPermission($surveyid,'surveycontent','delete'))
        {
            if ((($sumcount4 == 0 && $activated != "Y") || $activated != "Y"))
            {
                if (is_null($condarray))
                {
                    //				$groupsummary .= "<a href='$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid' onclick=\"return confirm('".$clang->gT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js")."')\""
                    $groupsummary .= "<a href='#' onclick=\"if (confirm('".$clang->gT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js")."')) {".get2post("$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid")."}\""
                    . " title=\"".$clang->gTview("Delete current question group")."\">"
                    . "<img src='$imageurl/delete.png' alt='".$clang->gT("Delete current question group")."' name='DeleteWholeGroup' title=''  /></a>\n";
                    //get2post("$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid");
                }
                else
                {
                    $groupsummary .= "<a href='$scriptname?sid=$surveyid&amp;gid=$gid' onclick=\"alert('".$clang->gT("Impossible to delete this group because there is at least one question having a condition on its content","js")."')\""
                    . " title=\"".$clang->gTview("Delete current question group")."\">"
                    . "<img src='$imageurl/delete_disabled.png' alt='".$clang->gT("Delete current question group")."' name='DeleteWholeGroup' /></a>\n";
                }
            }
            else
            {
                $groupsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";
            }
        }


        // EXPORT QUESTION GROUP BUTTON

        if(bHasSurveyPermission($surveyid,'surveycontent','export'))
        {

            $groupsummary .="<a href='$scriptname?action=exportstructureGroup&amp;sid=$surveyid&amp;gid=$gid' title=\"".$clang->gTview("Export this question group")."\" >"
            . "<img src='$imageurl/dumpgroup.png' title='' alt='".$clang->gT("Export this question group")."' name='ExportGroup'  /></a>\n";
        }


        // CHANGE QUESTION ORDER BUTTON

        if(bHasSurveyPermission($surveyid,'surveycontent','update'))
        {
            $groupsummary .= "<img src='$imageurl/seperator.gif' alt='' />\n";
            if($activated!="Y" && getQuestionSum($surveyid, $gid)>1)
            {
//                $groupsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";
//                $groupsummary .= "<img src='$imageurl/seperator.gif' alt='' />\n";
                $groupsummary .= "<a href='$scriptname?action=orderquestions&amp;sid=$surveyid&amp;gid=$gid' title=\"".$clang->gTview("Change Question Order")."\" >"
                . "<img src='$imageurl/reorder.png' alt='".$clang->gT("Change Question Order")."' name='updatequestionorder' /></a>\n" ;
            }
            else
            {
                $groupsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";
            }
        }

        $groupsummary.= "</div>\n"
        . "<div class='menubar-right'>\n"
        . "<span class=\"boxcaption\">".$clang->gT("Questions").":</span><select class=\"listboxquestions\" name='qid' "
        . "onchange=\"window.open(this.options[this.selectedIndex].value, '_top')\">"
        . getQuestions($surveyid,$gid,$qid)
        . "</select>\n";


        // ADD NEW QUESTION TO GROUP BUTTON

        if ($activated == "Y")
        {
            $groupsummary .= "<a href='#'"
            ."<img src='$imageurl/add_disabled.png' title='' alt='".$clang->gT("Disabled").' - '.$clang->gT("This survey is currently active.")."' " .
            " name='AddNewQuestion' onclick=\"window.open('', '_top')\" /></a>\n";
        }
        elseif(bHasSurveyPermission($surveyid,'surveycontent','create'))
        {
            $groupsummary .= "<a href='$scriptname?action=addquestion&amp;sid=$surveyid&amp;gid=$gid'"
            ." title=\"".$clang->gTview("Add New Question to Group")."\" >"
            ."<img src='$imageurl/add.png' title='' alt='".$clang->gT("Add New Question to Group")."' " .
            " name='AddNewQuestion' onclick=\"window.open('', '_top')\" /></a>\n";
        }

        $groupsummary .= "<img src='$imageurl/seperator.gif' alt=''  />";

        $groupsummary.= "<img src='$imageurl/blank.gif' width='18' alt='' />"
        . "<input id='MinimizeGroupWindow' type='image' src='$imageurl/minus.gif' title='"
        . $clang->gT("Hide Details of this Group")."' alt='". $clang->gT("Hide Details of this Group")."' name='MinimizeGroupWindow' />\n";
        $groupsummary .= "<input type='image' id='MaximizeGroupWindow' src='$imageurl/plus.gif' title='"
        . $clang->gT("Show Details of this Group")."' alt='". $clang->gT("Show Details of this Group")."' name='MaximizeGroupWindow' />\n";
        if (!$qid)
        {
            $groupsummary .= "<input type='image' src='$imageurl/close.gif' title='"
            . $clang->gT("Close this Group")."' alt='". $clang->gT("Close this Group")."'  name='CloseSurveyWindow' "
            . "onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" />\n";
        }
        else
        {
            $groupsummary .= "<img src='$imageurl/blank.gif' alt='' width='18' />\n";
        }
        $groupsummary .="</div></div>\n"
        . "</div>\n";
        //  $groupsummary .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix

        if ($qid || $action=='editgroup'|| $action=='addquestion') {$gshowstyle="style='display: none'";}
        else	  {$gshowstyle="";}

        $groupsummary .= "<table id='groupdetails' $gshowstyle ><tr ><td width='20%' align='right'><strong>"
        . $clang->gT("Title").":</strong></td>\n"
        . "<td align='left'>"
        . "{$grow['group_name']} ({$grow['gid']})</td></tr>\n"
        . "<tr><td valign='top' align='right'><strong>"
        . $clang->gT("Description:")."</strong></td>\n<td align='left'>";
        if (trim($grow['description'])!='') {$groupsummary .=$grow['description'];}
        $groupsummary .= "</td></tr>\n";

        if (!is_null($condarray))
        {
            $groupsummary .= "<tr><td align='right'><strong>"
            . $clang->gT("Questions with conditions to this group").":</strong></td>\n"
            . "<td valign='bottom' align='left'>";
            foreach ($condarray[$gid] as $depgid => $deprow)
            {
                foreach ($deprow['conditions'] as $depqid => $depcid)
                {
                    //$groupsummary .= "[QID: ".$depqid."]";
                    $listcid=implode("-",$depcid);
                    $groupsummary .= " <a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$depgid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."','_top')\">[QID: ".$depqid."]</a>";
                }
            }
            $groupsummary .= "</td></tr>";
        }
    }
    $groupsummary .= "\n</table>\n";
}

if (isset($surveyid) && $surveyid && $gid && $qid)  // Show the question toolbar
{
    // TODO: check that surveyid is set and that so is $baselang
    //Show Question Details
	//Count answer-options for this question
    $qrq = "SELECT * FROM ".db_table_name('answers')." WHERE qid=$qid AND language='".$baselang."' ORDER BY sortorder, answer";
    $qrr = $connect->Execute($qrq); //Checked
    $qct = $qrr->RecordCount();
	//Count sub-questions for this question
	$sqrq= "SELECT * FROM ".db_table_name('questions')." WHERE parent_qid=$qid AND language='".$baselang."'";
	$sqrr= $connect->Execute($sqrq); //Checked
	$sqct = $sqrr->RecordCount();
	
    $qrquery = "SELECT * FROM ".db_table_name('questions')." WHERE gid=$gid AND sid=$surveyid AND qid=$qid AND language='".$baselang."'";
    $qrresult = db_execute_assoc($qrquery) or safe_die($qrquery."<br />".$connect->ErrorMsg()); //Checked
    $questionsummary = "<div class='menubar'>\n";

    // Check if other questions in the Survey are dependent upon this question
    $condarray=GetQuestDepsForConditions($surveyid,"all","all",$qid,"by-targqid","outsidegroup");


    // PREVIEW THIS QUESTION BUTTON

    while ($qrrow = $qrresult->FetchRow())
    {
        $qrrow = array_map('FlattenText', $qrrow);
        //$qrrow = array_map('htmlspecialchars', $qrrow);
        $questionsummary .= "<div class='menubar-title ui-widget-header'>\n"
        . "<strong>". $clang->gT("Question")."</strong> <span class='basic'>{$qrrow['question']} (".$clang->gT("ID").":$qid)</span>\n"
        . "</div>\n"
        . "<div class='menubar-main'>\n"
        . "<div class='menubar-left'>\n"
        . "<img src='$imageurl/blank.gif' alt='' width='55' height='20' />\n"
        . "<img src='$imageurl/seperator.gif' alt='' />\n";
        if(bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
            {
                $questionsummary .= "<a href=\"#\" accesskey='q' onclick=\"window.open('$scriptname?action=previewquestion&amp;sid=$surveyid&amp;qid=$qid', '_blank')\""
                . "title=\"".$clang->gTview("Preview This Question")."\">"
                . "<img src='$imageurl/preview.png' alt='".$clang->gT("Preview This Question")."' name='previewquestionimg' /></a>\n"
                . "<img src='$imageurl/seperator.gif' alt='' />\n";
            } else {
                $questionsummary .= "<a href=\"#\" accesskey='q' id='previewquestion'"
                . "title=\"".$clang->gTview("Preview This Question")."\">"
                . "<img src='$imageurl/preview.png' title='' alt='".$clang->gT("Preview This Question")."' name='previewquestionimg' /></a>\n"
                . "<img src='$imageurl/seperator.gif' alt=''  />\n";

                $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                $baselang = GetBaseLanguageFromSurveyID($surveyid);
                $tmp_survlangs[] = $baselang;
                rsort($tmp_survlangs);

                // Test question Language Selection Popup
                $surveysummary .="<div class=\"langpopup\" id=\"previewquestionpopup\">".$clang->gT("Please select a language:")."<ul>";
                foreach ($tmp_survlangs as $tmp_lang)
                {
                    $surveysummary .= "<li><a target='_blank' onclick=\"$('#previewquestion').qtip('hide');\" href='{$scriptname}?action=previewquestion&amp;sid={$surveyid}&amp;qid={$qid}&amp;lang={$tmp_lang}' accesskey='d'>".getLanguageNameFromCode($tmp_lang,false)."</a></li>";
                }
                $surveysummary .= "</ul></div>";
            }
        }

        // SEPARATOR

//        $questionsummary .= "<img src='$imageurl/blank.gif' alt='' width='117' height='20'  />\n";


        // EDIT CURRENT QUESTION BUTTON

        if(bHasSurveyPermission($surveyid,'surveycontent','update'))
        {
            $questionsummary .= ""
//            ."<img src='$imageurl/seperator.gif' alt='' />\n"
            . "<a href='$scriptname?action=editquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
            . " title=\"".$clang->gTview("Edit current question")."\">"
            . "<img src='$imageurl/edit.png' alt='".$clang->gT("Edit Current Question")."' name='EditQuestion' /></a>\n" ;
        }


        // DELETE CURRENT QUESTION BUTTON

        if ((($qct == 0 && $activated != "Y") || $activated != "Y") && bHasSurveyPermission($surveyid,'surveycontent','delete'))
        {
            if (is_null($condarray))
            {
                $questionsummary .= "<a href='#'" .
				"onclick=\"if (confirm('".$clang->gT("Deleting this question will also delete any answer options and subquestions it includes. Are you sure you want to continue?","js")."')) {".get2post("$scriptname?action=delquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid")."}\">"
				. "<img src='$imageurl/delete.png' name='DeleteWholeQuestion' alt='".$clang->gT("Delete current question")."' "
				. "border='0' hspace='0' /></a>\n";
            }
            else
            {
                $questionsummary .= "<a href='$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid'" .
				"onclick=\"alert('".$clang->gT("It's impossible to delete this question because there is at least one question having a condition on it.","js")."')\""
				. "title=\"".$clang->gTview("Disabled - Delete current question")."\">"
				. "<img src='$imageurl/delete_disabled.png' name='DeleteWholeQuestion' alt='".$clang->gT("Disabled - Delete current question")."' /></a>\n";
            }
        }
        else {$questionsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";}


        // EXPORT CURRENT QUESTION BUTTON

        if(bHasSurveyPermission($surveyid,'surveycontent','export'))
        {
            $questionsummary .= "<a href='$scriptname?action=exportstructureQuestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
            . " title=\"".$clang->gTview("Export this question")."\" >"
            . "<img src='$imageurl/dumpquestion.png' alt='".$clang->gT("Export this question")."' name='ExportQuestion' /></a>\n";
        }

        $questionsummary .= "<img src='$imageurl/seperator.gif' alt='' />\n";


        // COPY CURRENT QUESTION BUTTON

        if(bHasSurveyPermission($surveyid,'surveycontent','create'))
        {
            if ($activated != "Y")
            {
                $questionsummary .= "<a href='$scriptname?action=copyquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
                . " title=\"".$clang->gTview("Copy Current Question")."\" >"
                . "<img src='$imageurl/copy.png'  alt='".$clang->gT("Copy Current Question")."' name='CopyQuestion' /></a>\n"
                . "<img src='$imageurl/seperator.gif' alt='' />\n";
            }
            else
            {
                $questionsummary .= "<a href='#' title=\"".$clang->gTview("Copy Current Question")."\" "
                . "onclick=\"alert('".$clang->gT("You can't copy a question if the survey is active.","js")."')\">"
                . "<img src='$imageurl/copy_disabled.png' alt='".$clang->gT("Copy Current Question")."' name='CopyQuestion' /></a>\n"
                . "<img src='$imageurl/seperator.gif' alt='' />\n";
            }
        }
        else
        {
            $questionsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";
        }


        // SET EXTENDED CONDITIONS FOR QUESTION BUTTON

        if(bHasSurveyPermission($surveyid,'surveycontent','update'))
        {
            $questionsummary .= "<a href='#' onclick=\"window.open('$scriptname?action=conditions&amp;sid=$surveyid&amp;qid=$qid&amp;gid=$gid&amp;subaction=editconditionsform', '_top')\""
            . " title=\"".$clang->gTview("Set/view conditions for this question")."\">"
            . "<img src='$imageurl/conditions.png' alt='".$clang->gT("Set conditions for this question")."'  name='SetQuestionConditions' /></a>\n"
            . "<img src='$imageurl/seperator.gif' alt='' />\n"
			. "<a href='#' onclick=\"window.open('$scriptname?action=extendedconditions&amp;sid=$surveyid&amp;qid=$qid&amp;gid=$gid&amp;question=$qid', '_top')\""
            . " title=\"".$clang->gTview("Set/view extended conditions for this question")."\">"
            . "<img src='$imageurl/extendedconditions.png' alt='".$clang->gT("Set extended conditions for this question")."'  name='SetQuestionExtendedConditions' /></a>\n"
            . "<img src='$imageurl/seperator.gif' alt='' />\n";
        }
        else
        {
            $questionsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";
        }


        // EDIT SUBQUESTIONS FOR THIS QUESTION BUTTON

        $qtypes=getqtypelist('','array');
        if(bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            if ($qtypes[$qrrow['type']]['subquestions'] >0)
            {
                $questionsummary .=  "<a href='".$scriptname."?action=editsubquestions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
                ."title=\"".$clang->gTview("Edit subquestions for this question")."\">"
                ."<img src='$imageurl/subquestions.png' alt='".$clang->gT("Edit subquestions for this question")."' name='EditSubquestions' /></a>\n" ;
            }
        }
        else
        {
            $questionsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";
        }


        // EDIT ANSWER OPTIONS FOR THIS QUESTION BUTTON

        if(bHasSurveyPermission($surveyid,'surveycontent','read') && $qtypes[$qrrow['type']]['answerscales'] >0)
        {
            $questionsummary .=  "<a href='".$scriptname."?action=editansweroptions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
            ."title=\"".$clang->gTview("Edit answer options for this question")."\">"
            ."<img src='$imageurl/answers.png' alt='".$clang->gT("Edit answer options for this question")."' name='EdtAnswerOptions' /></a>\n" ;
        }
        else
        {
            $questionsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";
        }


        // EDIT DEFAULT ANSWERS FOR THIS QUESTION BUTTON

        if(bHasSurveyPermission($surveyid,'surveycontent','read') && $qtypes[$qrrow['type']]['hasdefaultvalues'] >0)
        {
            $questionsummary .=  "<a href='".$scriptname."?action=editdefaultvalues&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
            ."title=\"".$clang->gTview("Edit default answers for this question")."\">"
            ."<img src='$imageurl/defaultanswers.png' alt='".$clang->gT("Edit default answers for this question")."' name='EdtAnswerOptions' /></a>\n" ;
        }
        $questionsummary .= "</div>\n"
        . "<div class='menubar-right'>\n"
        . "<input type='image' src='$imageurl/minus.gif' title='"
        . $clang->gT("Hide Details of this Question")."'  alt='". $clang->gT("Hide Details of this Question")."' name='MinimiseQuestionWindow' "
        . "onclick='document.getElementById(\"questiondetails\").style.display=\"none\";' />\n"
        . "<input type='image' src='$imageurl/plus.gif' title='"
        . $clang->gT("Show Details of this Question")."'  alt='". $clang->gT("Show Details of this Question")."' name='MaximiseQuestionWindow' "
        . "onclick='document.getElementById(\"questiondetails\").style.display=\"\";' />\n"
        . "<input type='image' src='$imageurl/close.gif' title='"
        . $clang->gT("Close this Question")."' alt='". $clang->gT("Close this Question")."' name='CloseQuestionWindow' "
        . "onclick=\"window.open('$scriptname?sid=$surveyid&amp;gid=$gid', '_top')\" />\n"
        . "</div>\n"
        . "</div>\n"
        . "</div>\n";
        $questionsummary .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix

        if ($action=='editansweroptions' || $action =="editsubquestions" || $action =="editquestion" || $action =="editdefaultvalues" || $action =="copyquestion")
        {
            $qshowstyle = "style='display: none'";
        }
        else
        {
            $qshowstyle = "";
        }
        $questionsummary .= "<table  id='questiondetails' $qshowstyle><tr><td width='20%' align='right'><strong>"
        . $clang->gT("Code:")."</strong></td>\n"
        . "<td align='left'>{$qrrow['title']}";
        if ($qrrow['type'] != "X")
        {
            if ($qrrow['mandatory'] == "Y") {$questionsummary .= ": (<i>".$clang->gT("Mandatory Question")."</i>)";}
            else {$questionsummary .= ": (<i>".$clang->gT("Optional Question")."</i>)";}
        }
        $questionsummary .= "</td></tr>\n"
        . "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Question:")."</strong></td>\n<td align='left'>".$qrrow['question']."</td></tr>\n"
        . "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Help:")."</strong></td>\n<td align='left'>";
        if (trim($qrrow['help'])!=''){$questionsummary .= $qrrow['help'];}
        $questionsummary .= "</td></tr>\n";
        if ($qrrow['preg'])
        {
            $questionsummary .= "<tr ><td align='right' valign='top'><strong>"
            . $clang->gT("Validation:")."</strong></td>\n<td align='left'>{$qrrow['preg']}"
            . "</td></tr>\n";
        }
        $qtypes = getqtypelist("", "array"); //qtypes = array(type code=>type description)
        $questionsummary .= "<tr><td align='right' valign='top'><strong>"
        .$clang->gT("Type:")."</strong></td>\n<td align='left'>{$qtypes[$qrrow['type']]['description']}";
        $questionsummary .="</td></tr>\n";
        if ($qct == 0 && $qtypes[$qrrow['type']]['answerscales'] >0)
        {
            $questionsummary .= "<tr ><td></td><td align='left'>"
            . "<font face='verdana' size='1' color='red'>"
            . $clang->gT("Warning").": ". $clang->gT("You need to add answer options to this question")." "
            . "<input align='top' type='image' src='$imageurl/answers_20.png' title='"
            . $clang->gT("Edit answer options for this question")."' name='EditThisQuestionAnswers'"
            . "onclick=\"window.open('".$scriptname."?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;action=editansweroptions', '_top')\" /></font></td></tr>\n";
        }


        // EDIT SUBQEUSTIONS FOR THIS QUESTION BUTTON

        if($sqct == 0 && $qtypes[$qrrow['type']]['subquestions'] >0)
        {
           $questionsummary .= "<tr ><td></td><td align='left'>"
            . "<font face='verdana' size='1' color='red'>"
            . $clang->gT("Warning").": ". $clang->gT("You need to add subquestions to this question")." "
            . "<input align='top' type='image' src='$imageurl/answers_20.png' title='"
            . $clang->gT("Edit subquestions for this question")."' name='EditThisQuestionAnswers'"
            . "onclick=\"window.open('".$scriptname."?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;action=editsubquestions', '_top')\" /></font></td></tr>\n";
        }

        if ($qrrow['type'] == "M" or $qrrow['type'] == "P")
        {
            $questionsummary .= "<tr>"
            . "<td align='right' valign='top'><strong>"
            . $clang->gT("Option 'Other':")."</strong></td>\n"
            . "<td align='left'>";
            $questionsummary .= ($qrrow['other'] == "Y") ? ($clang->gT("Yes")) : ($clang->gT("No")) ;
            $questionsummary .= "</td></tr>\n";
        }
        if (isset($qrrow['mandatory']) and ($qrrow['type'] != "X") and ($qrrow['type'] != "|"))
        {
            $questionsummary .= "<tr>"
            . "<td align='right' valign='top'><strong>"
            . $clang->gT("Mandatory:")."</strong></td>\n"
            . "<td align='left'>";
            $questionsummary .= ($qrrow['mandatory'] == "Y") ? ($clang->gT("Yes")) : ($clang->gT("No")) ;
            $questionsummary .= "</td></tr>\n";
        }
        if (!is_null($condarray))
        {
            $questionsummary .= "<tr>"
            . "<td align='right' valign='top'><strong>"
            . $clang->gT("Other questions having conditions on this question:")
            . "</strong></td>\n<td align='left' valign='bottom'>\n";
            foreach ($condarray[$qid] as $depqid => $depcid)
            {
                $listcid=implode("-",$depcid);
                $questionsummary .= " <a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."','_top')\">[QID: ".$depqid."]</a>";
            }
            $questionsummary .= "</td></tr>";
        }
        $questionsummary .= "</table>";
    }
}

if ($action=='editansweroptions')
{
    $js_admin_includes[]='scripts/answers.js';
    $js_admin_includes[]='../scripts/jquery/jquery.blockUI.js';
    $js_admin_includes[]='../scripts/jquery/jquery.selectboxes.min.js';

    $_SESSION['FileManagerContext']="edit:answer:$surveyid";
    // Get languages select on survey.
    $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);

    $qquery = "SELECT type FROM ".db_table_name('questions')." WHERE qid=$qid AND language='".$baselang."'";
    $qrow = $connect->GetRow($qquery);
    $qtype = $qrow['type'];
    $scalecount=$qtypes[$qtype]['answerscales'];
    //Check if there is at least one answer
    for ($i = 0; $i < $scalecount; $i++)
    {
        $qquery = "SELECT count(*) as num_ans  FROM ".db_table_name('answers')." WHERE qid=$qid AND scale_id=$i AND language='".$baselang."'";
        $qresult = $connect->GetOne($qquery); //Checked
        if ($qresult==0)
        {
            $query="INSERT into ".db_table_name('answers')." (qid,code,answer,language,sortorder,scale_id) VALUES ($qid,'A1',".db_quoteall($clang->gT("Some example answer option")).",'$baselang',0,$i)";
            $connect->execute($query);
        }
    }


    // check that there are answers for every language supported by the survey
    for ($i = 0; $i < $scalecount; $i++)
    {
        foreach ($anslangs as $language)
        {
            $iAnswerCount = $connect->GetOne("SELECT count(*) as num_ans  FROM ".db_table_name('answers')." WHERE qid=$qid AND scale_id=$i AND language='".$language."'");
            if ($iAnswerCount == 0)   // means that no record for the language exists in the answers table
            {
                $qquery = "INSERT INTO ".db_table_name('answers')." (qid,code,answer,sortorder,language,scale_id, assessment_value) (SELECT qid,code,answer,sortorder, '".$language."','$i', assessment_value FROM ".db_table_name('answers')." WHERE qid=$qid AND scale_id=$i AND language='".$baselang."')";
                $connect->Execute($qquery); //Checked
            }
        }
    }

    array_unshift($anslangs,$baselang);      // makes an array with ALL the languages supported by the survey -> $anslangs

    //delete the answers in languages not supported by the survey
    $languagequery = "SELECT DISTINCT language FROM ".db_table_name('answers')." WHERE (qid = $qid) AND (language NOT IN ('".implode("','",$anslangs)."'))";
    $languageresult = db_execute_assoc($languagequery); //Checked
    while ($qrow = $languageresult->FetchRow())
    {
        $deleteanswerquery = "DELETE FROM ".db_table_name('answers')." WHERE (qid = $qid) AND (language = '".$qrow["language"]."')";
        $connect->Execute($deleteanswerquery); //Checked
    }

    if (!isset($_POST['ansaction']))
    {
        //check if any nulls exist. If they do, redo the sortorders
        $caquery="SELECT * FROM ".db_table_name('answers')." WHERE qid=$qid AND sortorder is null AND language='".$baselang."'";
        $caresult=$connect->Execute($caquery); //Checked
        $cacount=$caresult->RecordCount();
        if ($cacount)
        {
            fixsortorderAnswers($qid);
        }
    }

    // Print Key Control JavaScript
    $vasummary = PrepareEditorScript("editanswer");

    $query = "SELECT sortorder FROM ".db_table_name('answers')." WHERE qid='{$qid}' AND language='".GetBaseLanguageFromSurveyID($surveyid)."' ORDER BY sortorder desc";
    $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked
    $anscount = $result->RecordCount();
    $row=$result->FetchRow();
    $maxsortorder=$row['sortorder']+1;
    $vasummary .= "<div class='header ui-widget-header'>\n"
    .$clang->gT("Edit answer options")
    ."</div>\n"
    ."<form id='editanswersform' name='editanswersform' method='post' action='$scriptname'>\n"
    . "<input type='hidden' name='sid' value='$surveyid' />\n"
    . "<input type='hidden' name='gid' value='$gid' />\n"
    . "<input type='hidden' name='qid' value='$qid' />\n"
    . "<input type='hidden' name='action' value='updateansweroptions' />\n"
    . "<input type='hidden' name='sortorder' value='' />\n";
    $vasummary .= "<div class='tab-pane' id='tab-pane-answers-$surveyid'>";
    $first=true;

    $vasummary .= "<div id='xToolbar'></div>\n";

    // the following line decides if the assessment input fields are visible or not
    $assessmentvisible=($surveyinfo['assessments']=='Y' && $qtypes[$qtype]['assessable']==1);

    // Insert some Javascript variables
    $surveysummary .= "\n<script type='text/javascript'>
                          var languagecount=".count($anslangs).";\n
                          var scalecount=".$scalecount."; 
                          var assessmentvisible=".($assessmentvisible?'true':'false')."; 
                          var newansweroption_text='".$clang->gT('New answer option','js')."'; 
                          var strcode='".$clang->gT('Code','js')."'; 
                          var strlabel='".$clang->gT('Label','js')."'; 
                          var strCantDeleteLastAnswer='".$clang->gT('You cannot delete the last answer option.','js')."'; 
                          var lsbrowsertitle='".$clang->gT('Label set browser','js')."'; 
                          var quickaddtitle='".$clang->gT('Quick-add subquestions','js')."'; 
                          var duplicateanswercode='".$clang->gT('Error: You are trying to use duplicate answer codes.','js')."'; 
                          var langs='".implode(';',$anslangs)."';</script>\n";
                          
    foreach ($anslangs as $anslang)
    {
        $vasummary .= "<div class='tab-page' id='tabpage_$anslang'>"
        ."<h2 class='tab'>".getLanguageNameFromCode($anslang, false);
        if ($anslang==GetBaseLanguageFromSurveyID($surveyid)) {$vasummary .= '('.$clang->gT("Base Language").')';}

        $vasummary .= "</h2>";

        for ($scale_id = 0; $scale_id < $scalecount; $scale_id++)
        {
            $position=0;
            if ($scalecount>1)
            {
                $vasummary.="<div class='header ui-widget-header' style='margin-top:5px;'>".sprintf($clang->gT("Answer scale %s"),$scale_id+1)."</div>";
            }


            $vasummary .= "<table class='answertable' id='answers_{$anslang}_$scale_id' align='center' >\n"
            ."<thead>"
            ."<tr>\n"
            ."<th align='right'>&nbsp;</th>\n"
            ."<th align='center'>".$clang->gT("Code")."</th>\n";
            if ($assessmentvisible)
            {
                $vasummary .="<th align='center'>".$clang->gT("Assessment value");
            }
            else
            {
                $vasummary .="<th style='display:none;'>&nbsp;";
            }

            $vasummary .= "</th>\n"
            ."<th align='center'>".$clang->gT("Answer option")."</th>\n"
            ."<th align='center'>".$clang->gT("Actions")."</th>\n"
            ."</tr></thead>"
            ."<tbody align='center'>";
            $alternate=true;

            $query = "SELECT * FROM ".db_table_name('answers')." WHERE qid='{$qid}' AND language='{$anslang}' and scale_id=$scale_id ORDER BY sortorder, code";
            $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked
            $anscount = $result->RecordCount();
            while ($row=$result->FetchRow())
            {
                $row['code'] = htmlspecialchars($row['code']);
                $row['answer']=htmlspecialchars($row['answer']);
                 
                $vasummary .= "<tr class='row_$position ";
                if ($alternate==true)
                {
                    $vasummary.='highlight';
                }
                $alternate=!$alternate;

                $vasummary .=" '><td align='right'>\n";

                if ($first)
                {
                    $vasummary .= "<img class='handle' src='$imageurl/handle.png' /></td><td><input type='text' class='code' id='code_{$position}_{$scale_id}' name='code_{$position}_{$scale_id}' value=\"{$row['code']}\" maxlength='5' size='5'"
                    ." onkeypress=\"return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_')\""
                    ." />";
                }
                else
                {
                    $vasummary .= "&nbsp;</td><td>{$row['code']}";

                }

                $vasummary .= "</td>\n"
                ."<td\n";

                if ($assessmentvisible && $first)
                {
                    $vasummary .= "><input type='text' class='assessment' id='assessment_{$position}_{$scale_id}' name='assessment_{$position}_{$scale_id}' value=\"{$row['assessment_value']}\" maxlength='5' size='5'"
                    ." onkeypress=\"return goodchars(event,'-1234567890')\""
                    ." />";
                }
                elseif ( $first)
                {
                    $vasummary .= " style='display:none;'><input type='hidden' class='assessment' id='assessment_{$position}_{$scale_id}' name='assessment_{$position}_{$scale_id}' value=\"{$row['assessment_value']}\" maxlength='5' size='5'"
                    ." onkeypress=\"return goodchars(event,'-1234567890')\""
                    ." />";
                }
                elseif ($assessmentvisible)
                {
                    $vasummary .= '>'.$row['assessment_value'];
                }
                else
                {
                    $vasummary .= " style='display:none;'>";
                }

                $vasummary .= "</td><td>\n"
                ."<input type='text' class='answer' id='answer_{$row['language']}_{$row['sortorder']}_{$scale_id}' name='answer_{$row['language']}_{$row['sortorder']}_{$scale_id}' size='100' value=\"{$row['answer']}\" />\n"
                . getEditor("editanswer","answer_".$row['language']."_{$row['sortorder']}_{$scale_id}", "[".$clang->gT("Answer:", "js")."](".$row['language'].")",$surveyid,$gid,$qid,'editanswer');
                 
                // Deactivate delete button for active surveys
                $vasummary.="</td><td><img src='$imageurl/addanswer.png' class='btnaddanswer' />";
                $vasummary.="<img src='$imageurl/deleteanswer.png' class='btndelanswer' />";

                $vasummary .= "</td></tr>\n";
                $position++;
            }
            $vasummary .='</table><br />';
            if ($first)
            {
                $vasummary .=  "<input type='hidden' id='answercount_{$scale_id}' name='answercount_{$scale_id}' value='$anscount' />\n";
            }
            $vasummary .= "<button id='btnlsbrowser_{$scale_id}' class='btnlsbrowser' type='button'>".$clang->gT('Predefined label sets...')."</button>";
            $vasummary .= "<button id='btnquickadd_{$scale_id}' class='btnquickadd' type='button'>".$clang->gT('Quick add...')."</button>";

        }

        $position=sprintf("%05d", $position);

        $first=false;
        $vasummary .= "</div>";
    }
    // Label set browser
//                      <br/><input type='checkbox' checked='checked' id='languagefilter' /><label for='languagefilter'>".$clang->gT('Match language')."</label>
    $vasummary .= "<div id='labelsetbrowser' style='display:none;'><div style='float:left;width:260px;'>
                      <label for='labelsets'>".$clang->gT('Available label sets:')."</label>
                      <br /><select id='labelsets' size='10' style='width:250px;'><option>&nbsp;</option></select>
                      <br /><button id='btnlsreplace' type='button'>".$clang->gT('Replace')."</button>
                      <button id='btnlsinsert' type='button'>".$clang->gT('Add')."</button>
                      <button id='btncancel' type='button'>".$clang->gT('Cancel')."</button></div>
                      
                   <div id='labelsetpreview' style='float:right;width:500px;'></div></div> ";
    $vasummary .= "<div id='quickadd' style='display:none;'><div style='float:left;'>
                      <label for='quickadd'>".$clang->gT('Enter your subquestions:')."</label>
                      <br /><textarea id='quickaddarea' class='tipme' title='".$clang->gT('Enter one answer per line. You can provide a code by separating code and answer text with a semikolon or tab.')."' rows='30' style='width:570px;'></textarea>
                      <br /><button id='btnqareplace' type='button'>".$clang->gT('Replace')."</button>
                      <button id='btnqainsert' type='button'>".$clang->gT('Add')."</button>
                      <button id='btnqacancel' type='button'>".$clang->gT('Cancel')."</button></div>
                   </div> ";    
    // Save button
    $vasummary .= "<p><input type='submit' id='saveallbtn_$anslang' name='method' value='".$clang->gT("Save changes")."' />\n";
    $vasummary .= "</div></form>";


}



// ============= EDIT SUBQUESTIONS ======================================

if ($action=='editsubquestions')
{

    $js_admin_includes[]='scripts/subquestions.js';
    $js_admin_includes[]='../scripts/jquery/jquery.blockUI.js';
    $js_admin_includes[]='../scripts/jquery/jquery.selectboxes.min.js';

    $_SESSION['FileManagerContext']="edit:answer:{$surveyid}";
    // Get languages select on survey.
    $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);

    $sQuery = "SELECT type FROM ".db_table_name('questions')." WHERE qid={$qid} AND language='{$baselang}'";
    $sQuestiontype=$connect->GetOne($sQuery);
    $aQuestiontypeInfo=getqtypelist($sQuestiontype,'array');
    $iScaleCount=$aQuestiontypeInfo[$sQuestiontype]['subquestions'];
    
    for ($iScale = 0; $iScale < $iScaleCount; $iScale++)
    {
        $sQuery = "SELECT * FROM ".db_table_name('questions')." WHERE parent_qid={$qid} AND language='{$baselang}' and scale_id={$iScale}";
        $subquestiondata=$connect->GetArray($sQuery);
    if (count($subquestiondata)==0)
    {
            $sQuery = "INSERT INTO ".db_table_name('questions')." (sid,gid,parent_qid,title,question,question_order,language,scale_id)
                       VALUES($surveyid,$gid,$qid,'SQ001',".db_quoteall($clang->gT('Some example subquestion')).",1,".db_quoteall($baselang).",{$iScale})";
            $connect->Execute($sQuery); //Checked
            $sQuery = "SELECT * FROM ".db_table_name('questions')." WHERE parent_qid={$qid} AND language='{$baselang}' and scale_id={$iScale}"; 
            $subquestiondata=$connect->GetArray($sQuery);
    }
    // check that there are subquestions for every language supported by the survey
    foreach ($anslangs as $language)
    {
        foreach ($subquestiondata as $row)
        {
                $sQuery = "SELECT count(*) FROM ".db_table_name('questions')." WHERE parent_qid={$qid} AND language='{$language}' AND qid={$row['qid']} and scale_id={$iScale}";  
                $qrow = $connect->GetOne($sQuery); //Checked
            if ($qrow == 0)   // means that no record for the language exists in the questions table
            {
                    db_switchIDInsert('questions',true);
                    $sQuery = "INSERT INTO ".db_table_name('questions')." (qid,sid,gid,parent_qid,title,question,question_order,language, scale_id)
                               VALUES({$row['qid']},$surveyid,{$row['gid']},$qid,".db_quoteall($row['title']).",".db_quoteall($row['question']).",{$row['question_order']},".db_quoteall($language).",{$iScale})";
                    $connect->Execute($sQuery); //Checked
                    db_switchIDInsert('questions',false);
            }
        }
    }
    }


    array_unshift($anslangs,$baselang);      // makes an array with ALL the languages supported by the survey -> $anslangs

    $vasummary = "\n<script type='text/javascript'>
                      var languagecount=".count($anslangs).";\n
                      var newansweroption_text='".$clang->gT('New answer option','js')."'; 
                      var strcode='".$clang->gT('Code','js')."'; 
                      var strlabel='".$clang->gT('Label','js')."'; 
                      var strCantDeleteLastAnswer='".$clang->gT('You cannot delete the last subquestion.','js')."'; 
                      var lsbrowsertitle='".$clang->gT('Label set browser','js')."'; 
                      var quickaddtitle='".$clang->gT('Quick-add subquestions','js')."'; 
                      var duplicateanswercode='".$clang->gT('Error: You are trying to use duplicate subquestion codes.','js')."'; 
                      var langs='".implode(';',$anslangs)."';</script>\n";


    //delete the subquestions in languages not supported by the survey
    $qquery = "SELECT DISTINCT language FROM ".db_table_name('questions')." WHERE (parent_qid = $qid) AND (language NOT IN ('".implode("','",$anslangs)."'))";
    $qresult = db_execute_assoc($qquery); //Checked
    while ($qrow = $qresult->FetchRow())
    {
        $qquery = "DELETE FROM ".db_table_name('questions')." WHERE (parent_qid = $qid) AND (language = '".$qrow["language"]."')";
        $connect->Execute($qquery); //Checked
    }


    // Check sort order for subquestions
    $qquery = "SELECT type FROM ".db_table_name('questions')." WHERE qid=$qid AND language='".$baselang."'";
    $qresult = db_execute_assoc($qquery); //Checked
    while ($qrow=$qresult->FetchRow()) {$qtype=$qrow['type'];}
    if (!isset($_POST['ansaction']))
    {
        //check if any nulls exist. If they do, redo the sortorders
        $caquery="SELECT * FROM ".db_table_name('questions')." WHERE parent_qid=$qid AND question_order is null AND language='".$baselang."'";
        $caresult=$connect->Execute($caquery); //Checked
        $cacount=$caresult->RecordCount();
        if ($cacount)
        {
            fixsortorderAnswers($qid); // !!Adjust this!!
        }
    }

    // Print Key Control JavaScript
    $vasummary .= PrepareEditorScript("editanswer");

    $query = "SELECT question_order FROM ".db_table_name('questions')." WHERE parent_qid='{$qid}' AND language='".GetBaseLanguageFromSurveyID($surveyid)."' ORDER BY question_order desc";
    $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked
    $anscount = $result->RecordCount();
    $row=$result->FetchRow();
    $maxsortorder=$row['question_order']+1;
    $vasummary .= "<div class='header ui-widget-header'>\n"
    .$clang->gT("Edit subquestions")
    ."</div>\n"
    ."<form id='editsubquestionsform' name='editsubquestionsform' method='post' action='$scriptname'onsubmit=\"return codeCheck('code_',$maxsortorder,'".$clang->gT("Error: You are trying to use duplicate answer codes.",'js')."','".$clang->gT("Error: 'other' is a reserved keyword.",'js')."');\">\n"
    . "<input type='hidden' name='sid' value='$surveyid' />\n"
    . "<input type='hidden' name='gid' value='$gid' />\n"
    . "<input type='hidden' name='qid' value='$qid' />\n"
    . "<input type='hidden' id='action' name='action' value='updatesubquestions' />\n"
    . "<input type='hidden' id='sortorder' name='sortorder' value='' />\n"
    . "<input type='hidden' id='deletedqids' name='deletedqids' value='' />\n";
    $vasummary .= "<div class='tab-pane' id='tab-pane-assessments-$surveyid'>";
    $first=true;
    $sortorderids='';
    $codeids='';

    $vasummary .= "<div id='xToolbar'></div>\n";

    // the following line decides if the assessment input fields are visible or not
    // for some question types the assessment values is set in the label set instead of the answers
    $qtypes=getqtypelist('','array');

    $scalecount=$qtypes[$qtype]['subquestions'];
    foreach ($anslangs as $anslang)
    {
        $vasummary .= "<div class='tab-page' id='tabpage_$anslang'>"
        ."<h2 class='tab'>".getLanguageNameFromCode($anslang, false);
        if ($anslang==GetBaseLanguageFromSurveyID($surveyid)) {$vasummary .= '('.$clang->gT("Base Language").')';}
        $vasummary .= "</h2>";

        for ($scale_id = 0; $scale_id < $scalecount; $scale_id++)
        {
            $position=0;
            if ($scalecount>1)
            {
                if ($scale_id==0)
                {
                    $vasummary .="<div class='header ui-widget-header'>\n".$clang->gT("Y-Scale")."</div>";
                }
                else
                {
                    $vasummary .="<div class='header ui-widget-header'>\n".$clang->gT("X-Scale")."</div>";
                }
            }
            $query = "SELECT * FROM ".db_table_name('questions')." WHERE parent_qid='{$qid}' AND language='{$anslang}' AND scale_id={$scale_id} ORDER BY question_order, title";
            $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked
            $anscount = $result->RecordCount();
            $vasummary .="<table class='answertable' id='answertable_{$anslang}_{$scale_id}' align='center'>\n"
            ."<thead>"
            ."<tr><th>&nbsp;</th>\n"
            ."<th align='right'>".$clang->gT("Code")."</th>\n"
            ."<th align='center'>".$clang->gT("Subquestion")."</th>\n";
            if ($activated != 'Y' && $first)
            {
                $vasummary .="<th align='center'>".$clang->gT("Action")."</th>\n";
            }
            $vasummary .="</tr></thead>"
            ."<tbody align='center'>";
            $alternate=false;
            while ($row=$result->FetchRow())
            {
                $row['title'] = htmlspecialchars($row['title']);
                $row['question']=htmlspecialchars($row['question']);
                 
                if ($first) {$codeids=$codeids.' '.$row['question_order'];}

                $vasummary .= "<tr id='row_{$row['language']}_{$row['qid']}_{$row['scale_id']}'";
                if ($alternate==true)
                {
                    $vasummary.=' class="highlight" ';
                    $alternate=false;
                }
                else
                {
                    $alternate=true;
                }

                $vasummary .=" ><td align='right'>\n";

                if ($activated == 'Y' ) // if activated
                {
                    $vasummary .= "&nbsp;</td><td><input type='hidden' name='code_{$row['qid']}_{$row['scale_id']}' value=\"{$row['title']}\" maxlength='5' size='5'"
                    ." />{$row['title']}";
                }
                elseif ($activated != 'Y' && $first) // If survey is decactivated
                {
                    $vasummary .= "<img class='handle' src='$imageurl/handle.png' /></td><td><input type='text' id='code_{$row['qid']}_{$row['scale_id']}' class='code' name='code_{$row['qid']}_{$row['scale_id']}' value=\"{$row['title']}\" maxlength='5' size='5'"
                    ." onkeypress=\" if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_$anslang').click(); return false;} return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_')\""
                    ." />";

                }
                else
                {
                    $vasummary .= "</td><td>{$row['title']}";

                }
                //      <img class='handle' src='$imageurl/handle.png' /></td><td>
                $vasummary .= "</td><td>\n"
                ."<input type='text' size='100' id='answer_{$row['language']}_{$row['qid']}_{$row['scale_id']}' name='answer_{$row['language']}_{$row['qid']}_{$row['scale_id']}' value=\"{$row['question']}\" onkeypress=\" if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_$anslang').click(); return false;}\" />\n"
                . getEditor("editanswer","answer_".$row['language']."_".$row['qid']."_{$row['scale_id']}", "[".$clang->gT("Subquestion:", "js")."](".$row['language'].")",$surveyid,$gid,$qid,'editanswer')
                ."</td>\n"
                ."<td>\n";

                // Deactivate delete button for active surveys
                if ($activated != 'Y' && $first)
                {
                    $vasummary.="<img src='$imageurl/addanswer.png' class='btnaddanswer' />";
                    $vasummary.="<img src='$imageurl/deleteanswer.png' class='btndelanswer' />";
                }

                $vasummary .= "</td></tr>\n";
                $position++;
            }
            ++$anscount;
            $vasummary .= "</tbody></table>\n";
            $disabled='';
            if ($activated == 'Y')
            {
                $disabled="disabled='disabled'";
            }
            $vasummary .= "<button class='btnlsbrowser' id='btnlsbrowser_{$scale_id}' $disabled type='button'>".$clang->gT('Predefined label sets...')."</button>";
            $vasummary .= "<button class='btnquickadd' id='btnquickadd_{$scale_id}' $disabled type='button'>".$clang->gT('Quick add...')."</button>";
        }

        $first=false;
        $vasummary .= "</div>";
    }

    // Label set browser
//                      <br/><input type='checkbox' checked='checked' id='languagefilter' /><label for='languagefilter'>".$clang->gT('Match language')."</label>
    $vasummary .= "<div id='labelsetbrowser' style='display:none;'><div style='float:left; width:260px;'>
                      <label for='labelsets'>".$clang->gT('Available label sets:')."</label>
                      <br /><select id='labelsets' size='10' style='width:250px;'><option>&nbsp;</option></select>
                      <br /><button id='btnlsreplace' type='button'>".$clang->gT('Replace')."</button>
                      <button id='btnlsinsert' type='button'>".$clang->gT('Add')."</button>
                      <button id='btncancel' type='button'>".$clang->gT('Cancel')."</button></div>
                   <div id='labelsetpreview' style='float:right;width:500px;'></div></div> ";    
    $vasummary .= "<div id='quickadd' style='display:none;'><div style='float:left;'>
                      <label for='quickadd'>".$clang->gT('Enter your subquestions:')."</label>
                      <br /><textarea id='quickaddarea' class='tipme' title='".$clang->gT('Enter one subquestion per line. You can provide a code by separating code and subquestion text with a semikolon or tab.')."' rows='30' style='width:570px;'></textarea>
                      <br /><button id='btnqareplace' type='button'>".$clang->gT('Replace')."</button>
                      <button id='btnqainsert' type='button'>".$clang->gT('Add')."</button>
                      <button id='btnqacancel' type='button'>".$clang->gT('Cancel')."</button></div>
                   </div> ";    
    $vasummary .= "<p>"
    ."<input type='submit' id='saveallbtn_$anslang' name='method' value='".$clang->gT("Save changes")."' />\n";
    $position=sprintf("%05d", $position);
    if ($activated == 'Y')
    {
        $vasummary .= "<p>\n"
        ."<font color='red' size='1'><i><strong>"
        .$clang->gT("Warning")."</strong>: ".$clang->gT("You cannot add/remove subquestions or edit their codes because the survey is active.")."</i></font>\n"
        ."</td>\n"
        ."</tr>\n";
    }

    $vasummary .= "</div></form>";


}



// *************************************************
// Survey Rights Start	****************************
// *************************************************

if($action == "addsurveysecurity")
{
    $addsummary = "<div class='header ui-widget-header'>".$clang->gT("Add User")."</div>\n";
    $addsummary .= "<div class=\"messagebox\">\n";

    $query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID']." AND owner_id != ".$postuserid;
    $result = db_execute_assoc($query); //Checked
    if( ($result->RecordCount() > 0 && in_array($postuserid,getuserlist('onlyuidarray'))) ||
    $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {

        if($postuserid > 0){

            $isrquery = "INSERT INTO {$dbprefix}survey_permissions (sid,uid,permission,read_p) VALUES ({$surveyid},{$postuserid},'survey',1)";
            $isrresult = $connect->Execute($isrquery); //Checked

            if($isrresult)
            {
                $addsummary .= "<div class=\"successheader\">".$clang->gT("User added.")."</div>\n";
                $addsummary .= "<br /><form method='post' action='$scriptname?sid={$surveyid}'>"
                ."<input type='submit' value='".$clang->gT("Set survey permissions")."' />"
                ."<input type='hidden' name='action' value='setsurveysecurity' />"
                ."<input type='hidden' name='uid' value='{$postuserid}' />"
                ."</form>\n";
            }
            else
            {
                // Username already exists.
                $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add user.")."</div>\n"
                . "<br />" . $clang->gT("Username already exists.")."<br />\n";
                $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?sid={$surveyid}&amp;action=surveysecurity', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
            }
        }
        else
        {
            $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add User.")."</div>\n"
            . "<br />" . $clang->gT("No Username selected.")."<br />\n";
            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?sid={$surveyid}&amp;action=surveysecurity', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
        }
    }
    else
    {
        include("access_denied.php");
    }
    $addsummary .= "</div>\n";
}


if($action == "addusergroupsurveysecurity")
{
    $addsummary = "<div class=\"header\">".$clang->gT("Add user group")."</div>\n";
    $addsummary .= "<div class=\"messagebox\">\n";

    $query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID'];
    $result = db_execute_assoc($query); //Checked
    if( ($result->RecordCount() > 0 && in_array($postusergroupid,getsurveyusergrouplist('simpleugidarray')) ) ||
    $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        if($postusergroupid > 0){
            $query2 = "SELECT b.uid FROM (SELECT uid FROM ".db_table_name('survey_permissions')." WHERE sid = {$surveyid}) AS c RIGHT JOIN ".db_table_name('user_in_groups')." AS b ON b.uid = c.uid WHERE c.uid IS NULL AND b.ugid = {$postusergroupid}";
            $result2 = db_execute_assoc($query2); //Checked
            if($result2->RecordCount() > 0)
            {
                while ($row2 = $result2->FetchRow())
                {
                    $uid_arr[] = $row2['uid'];
                    $isrquery = "INSERT INTO {$dbprefix}surveys_permissions (sid,uid,permission,read_p) VALUES ({$surveyid}, {$row2['uid']},'survey',1) ";
                    $isrresult = $connect->Execute($isrquery); //Checked
                    if (!$isrresult) break;
                }

                if($isrresult)
                {
                    $addsummary .= "<div class=\"successheader\">".$clang->gT("User Group added.")."</div>\n";
                    $_SESSION['uids'] = $uid_arr;
                    $addsummary .= "<br /><form method='post' action='$scriptname?sid={$surveyid}'>"
                    ."<input type='submit' value='".$clang->gT("Set Survey Rights")."' />"
                    ."<input type='hidden' name='action' value='setusergroupsurveysecurity' />"
                    ."<input type='hidden' name='ugid' value='{$postusergroupid}' />"
                    ."</form>\n";
                }
                else
                {
                    // Error while adding user to the database
                    $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add User Group.")."</div>\n";
                    $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=surveysecurity&amp;sid={$surveyid}', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
                }
            }
            else
            {
                // no user to add
                $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add User Group.")."</div>\n";
                $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=surveysecurity&amp;sid={$surveyid}', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
            }
        }
        else
        {
            $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add User.")."</div>\n"
            . "<br />" . $clang->gT("No Username selected.")."<br />\n";
            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?action=surveysecurity&amp;sid={$surveyid}', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
        }
    }
    else
    {
        include("access_denied.php");
    }
    $addsummary .= "</div>\n";
}

if($action == "delsurveysecurity")
{
    $addsummary = "<div class=\"header\">".$clang->gT("Deleting User")."</div>\n";
    $addsummary .= "<div class=\"messagebox\">\n";

    $query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID']." AND owner_id != ".$postuserid;
    $result = db_execute_assoc($query); //Checked
    if($result->RecordCount() > 0 || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        if (isset($postuserid))
        {
            $dquery="DELETE FROM".db_table_name('survey_permissions')." WHERE uid={$postuserid} AND sid={$surveyid}";	//	added by Dennis
            $dresult=$connect->Execute($dquery); //Checked

            $addsummary .= "<br />".$clang->gT("Username").": ".sanitize_xss_string($_POST['user'])."<br /><br />\n";
            $addsummary .= "<div class=\"successheader\">".$clang->gT("Success!")."</div>\n";
        }
        else
        {
            $addsummary .= "<div class=\"warningheader\">".$clang->gT("Could not delete user. User was not supplied.")."</div>\n";
        }
        $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?sid={$surveyid}&amp;action=surveysecurity', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
    }
    else
    {
        include("access_denied.php");
    }
    $addsummary .= "</div>\n";
}

if($action == "setsurveysecurity")
{
    $query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID']." AND owner_id != ".$postuserid;
    $result = db_execute_assoc($query); //Checked
    if($result->RecordCount() > 0 || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        $js_admin_includes[]='../scripts/jquery/jquery.tablesorter.min.js';
        $js_admin_includes[]='scripts/surveysecurity.js';
        $sUsername=$connect->GetOne("select users_name from ".db_table_name('users')." where uid={$postuserid}");
        $usersummary = "<div class='header ui-widget-header'>".sprintf($clang->gT("Edit survey permissions for user %s"),"<span style='font-style:italic'>".$sUsername."</span>")."</div><br />
        <form action='$scriptname?sid={$surveyid}' method='post'>\n"
        . "<table style='margin:0 auto;' border='0' class='usersurveypermissions'><thead>\n";

        $usersummary .= ""
        . "<tr><th></th><th align='center'>".$clang->gT("Permission")."</th>\n"
        . "<th align='center'><input type='button' id='btnToggleAdvanced' value='&gt;&gt;' /></th>\n"
        . "<th align='center' class='extended'>".$clang->gT("Create")."</th>\n"
        . "<th align='center' class='extended'>".$clang->gT("View/read")."</th>\n"
        . "<th align='center' class='extended'>".$clang->gT("Update")."</th>\n"
        . "<th align='center' class='extended'>".$clang->gT("Delete")."</th>\n"
        . "<th align='center' class='extended'>".$clang->gT("Import")."</th>\n"
        . "<th align='center' class='extended'>".$clang->gT("Export")."</th>\n"
        . "</tr></thead>\n";

        //content

        $aBasePermissions=aGetBaseSurveyPermissions();
        $oddcolumn=false;
        foreach($aBasePermissions as $sPermissionKey=>$aCRUDPermissions)
        {
            $oddcolumn=!$oddcolumn;
            $usersummary .= "<tr><td align='center'><img src='{$imageurl}/{$aCRUDPermissions['img']}_30.png' /></td>";
            $usersummary .= "<td align='right'>{$aCRUDPermissions['title']}</td>";
            $usersummary .= "<td  align='center'><input type=\"checkbox\"  class=\"markrow\" name='all_{$sPermissionKey}' /></td>";
            foreach ($aCRUDPermissions as $sCRUDKey=>$CRUDValue)
            {
                if (!in_array($sCRUDKey,array('create','read','update','delete','import','export'))) continue;
                $usersummary .= "<td class='extended' align='center'>";
                
                if ($CRUDValue)
                {
                    if (!($sPermissionKey=='survey' && $sCRUDKey=='read')) 
                    {
                        $usersummary .= "<input type=\"checkbox\"  class=\"checkboxbtn\" name='perm_{$sPermissionKey}_{$sCRUDKey}' ";
                        if(bHasSurveyPermission( $surveyid,$sPermissionKey,$sCRUDKey,$postuserid)) {
                            $usersummary .= ' checked="checked" ';
                        }
                        $usersummary .=" />";                    
                    }
                }
                $usersummary .= "</td>";
            }
            $usersummary .= "</tr>";
        }

        $usersummary .= "\n</table>"
        ."<p><input type='submit' value='".$clang->gT("Save Now")."' />"
        ."<input type='hidden' name='perm_survey_read' value='1' />"
        ."<input type='hidden' name='action' value='surveyrights' />"
        ."<input type='hidden' name='uid' value='{$postuserid}' />"
        . "</form>\n";
    }
    else
    {
        include("access_denied.php");
    }
}


if($action == "setusergroupsurveysecurity")
{
    $query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID'];//." AND owner_id != ".$postuserid;
    $result = db_execute_assoc($query); //Checked
    if($result->RecordCount() > 0 || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        $usersummary = "<table width='100%' border='0'>\n<tr><td colspan='7'>\n"
        . "".$clang->gT("Set Survey Rights")."</td></tr>\n";

        $usersummary .= ""
        . "<th align='center'>".$clang->gT("Edit Survey Property")."</th>\n"
        . "<th align='center'>".$clang->gT("Define Questions")."</th>\n"
        . "<th align='center'>".$clang->gT("Browse Response")."</th>\n"
        . "<th align='center'>".$clang->gT("Export")."</th>\n"
        . "<th align='center'>".$clang->gT("Delete Survey")."</th>\n"
        . "<th align='center'>".$clang->gT("Activate Survey")."</th>\n"
        . "<th align='center'>".$clang->gT("Translate Survey")."</th>\n"
        . "</tr>\n"
        . "<form action='$scriptname?sid={$surveyid}' method='post'>\n";

        //content
        $usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"edit_survey_property\" value=\"edit_survey_property\"";

        $usersummary .=" /></td>\n";
        $usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"define_questions\" value=\"define_questions\"";

        $usersummary .=" /></td>\n";
        $usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"browse_response\" value=\"browse_response\"";

        $usersummary .=" /></td>\n";
        $usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"export\" value=\"export\"";

        $usersummary .=" /></td>\n";
        $usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"delete_survey\" value=\"delete_survey\"";

        $usersummary .=" /></td>\n";
        $usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"activate_survey\" value=\"activate_survey\"";

        $usersummary .=" /></td>\n";
        $usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"translate_survey\" value=\"translate_survey\"";

        $usersummary .=" /></td>\n";

        $usersummary .= "\n<tr><td colspan='7' align='center'>"
        ."<input type='submit' value='".$clang->gT("Save Now")."' />"
        ."<input type='hidden' name='action' value='surveyrights' />"
        ."<input type='hidden' name='ugid' value='{$postusergroupid}' /></td></tr>"
        ."</form>"
        . "</table>\n";
    }
    else
    {
        include("access_denied.php");
    }
}

// This is the action to export the structure of a complete survey
if($action == "exportstructure")
{
    if(bHasSurveyPermission($surveyid,'surveycontent','export'))
    {
        $exportstructure = "<form id='exportstructure' name='exportstructure' action='$scriptname' method='post'>\n"
        ."<div class='header ui-widget-header'>"
        .$clang->gT("Export Survey Structure")."\n</div><br />\n"
        ."<ul style='margin-left:35%;'>\n"
        ."<li><input type='radio' class='radiobtn' name='action' value='exportstructurexml' checked='checked' id='surveyxml'"
        ."<label for='surveycsv'>"
        .$clang->gT("LimeSurvey XML survey file (*.lss)")."</label></li>\n";

	    $exportstructure.="<li><input type='radio' class='radiobtn' name='action' value='exportstructurequexml'  id='queXML'"
	    ."<label for='queXML'>"
	    .str_replace('queXML','<a href="http://quexml.sourceforge.net/" target="_blank">queXML</a>',$clang->gT("queXML Survey XML Format (*.xml)"))." "
	    ."</label></li>\n";

	    // XXX
	    //include("../config.php");

	    //echo $export4lsrc;
	    if($export4lsrc)
	    {
	        $exportstructure.="<li><input type='radio' class='radiobtn' name='type' value='structureLsrcCsv'  id='LsrcCsv'
		    onclick=\"this.form.action.value='exportstructureLsrcCsv'\" />"
		    ."<label for='LsrcCsv'>"
		    .$clang->gT("Save for Lsrc (*.csv)")." "
		    ."</label></li>";
	    }
	    $exportstructure.="</ul>\n";

	    $exportstructure.="<p>\n"
	    ."<input type='submit' value='"
	    .$clang->gT("Export To File")."' />\n"
	    ."<input type='hidden' name='sid' value='$surveyid' />\n";
	    $exportstructure.="</form>\n";
    }
}

// This is the action to export the structure of a group
if($action == "exportstructureGroup")
{
    if($export4lsrc === true && bHasSurveyPermission($surveyid,'export'))
    {
        $exportstructure = "<form id='exportstructureGroup' name='exportstructureGroup' action='$scriptname' method='post'>\n"
        ."<div class='header ui-widget-header'>".$clang->gT("Export group structure")."\n</div>\n"
        ."<ul>\n"
        ."<li>\n";
        $exportstructure.="<input type='radio' class='radiobtn' name='type' value='structurecsvGroup' checked='checked' id='surveycsv'
	    onclick=\"this.form.action.value='exportstructurecsvGroup'\"/>"
	    ."<label for='surveycsv'>"
	    .$clang->gT("LimeSurvey group file (*.csv)")."</label></li>\n";

	    //	    $exportstructure.="<input type='radio' class='radiobtn' name='type' value='structurequeXMLGroup'  id='queXML' onclick=\"this.form.action.value='exportstructurequexml'\" />"
	    //	    ."<label for='queXML'>"
	    //	    .$clang->gT("queXML Survey XML Format (*.xml)")." "
	    //	    ."</label>\n";

	    // XXX
	    //include("../config.php");

	    //echo $export4lsrc;
	    if($export4lsrc)
	    {
	        $exportstructure.="<li><input type='radio' class='radiobtn' name='type' value='structureLsrcCsvGroup'  id='LsrcCsv'
		    onclick=\"this.form.action.value='exportstructureLsrcCsvGroup'\" />"
		    ."<label for='LsrcCsv'>"
		    .$clang->gT("Save for Lsrc (*.csv)")." "
		    ."</label></li>\n";
	    }

	    $exportstructure.="</ul>\n"
	    ."<p>\n"
	    ."<input type='submit' value='"
	    .$clang->gT("Export to file")."' />\n"
	    ."<input type='hidden' name='sid' value='$surveyid' />\n"
	    ."<input type='hidden' name='gid' value='$gid' />\n"
	    ."<input type='hidden' name='action' value='exportstructurecsvGroup' />\n";
	    $exportstructure.="</form>\n";
    }
    else
    {
        include('dumpgroup.php');
    }
}

// This is the action to export the structure of a question
if($action == "exportstructureQuestion")
{
    if($export4lsrc === true && bHasSurveyPermission($surveyid,'export'))
    {
        $exportstructure = "<form id='exportstructureQuestion' name='exportstructureQuestion' action='$scriptname' method='post'>\n"
        ."<div class='header ui-widget-header'>".$clang->gT("Export question structure")."\n</div>\n"
        ."<ul>\n"
        ."<li>\n";
        $exportstructure.="<input type='radio' class='radiobtn' name='type' value='structurecsvQuestion' checked='checked' id='surveycsv'
	    onclick=\"this.form.action.value='exportstructurecsvQuestion'\"/>"
	    ."<label for='surveycsv'>"
	    .$clang->gT("LimeSurvey group file (*.csv)")."</label></li>\n";

	    //	    $exportstructure.="<input type='radio' class='radiobtn' name='type' value='structurequeXMLGroup'  id='queXML' onclick=\"this.form.action.value='exportstructurequexml'\" />"
	    //	    ."<label for='queXML'>"
	    //	    .$clang->gT("queXML Survey XML Format (*.xml)")." "
	    //	    ."</label>\n";

	    // XXX
	    //include("../config.php");

	    //echo $export4lsrc;
	    if($export4lsrc)
	    {
	        $exportstructure.="<li><input type='radio' class='radiobtn' name='type' value='structureLsrcCsvQuestion'  id='LsrcCsv'
		    onclick=\"this.form.action.value='exportstructureLsrcCsvQuestion'\" />"
		    ."<label for='LsrcCsv'>"
		    .$clang->gT("Save for Lsrc (*.csv)")." "
		    ."</label></li>\n";
	    }

	    $exportstructure.="</ul>\n"
	    ."<p>\n"
	    ."<input type='submit' value='"
	    .$clang->gT("Export to file")."' />\n"
	    ."<input type='hidden' name='sid' value='$surveyid' />\n"
	    ."<input type='hidden' name='gid' value='$gid' />\n"
	    ."<input type='hidden' name='qid' value='$qid' />\n"
	    ."<input type='hidden' name='action' value='exportstructurecsvQuestion' />\n";
	    $exportstructure.="</form>\n";
    }
    else
    {
        include('dumpquestion.php');
    }
}

if($action == "surveysecurity")
{
    if(bHasSurveyPermission($surveyid,'survey','read'))
    {
        $aBaseSurveyPermissions=aGetBaseSurveyPermissions();
        $js_admin_includes[]='../scripts/jquery/jquery.tablesorter.min.js';
        $js_admin_includes[]='scripts/surveysecurity.js';

        $query2 = "SELECT p.*, u.users_name, u.full_name FROM ".db_table_name('survey_permissions')." AS p INNER JOIN ".db_table_name('users')."  AS u ON p.uid = u.uid 
                   WHERE p.sid = {$surveyid} AND u.uid != ".$_SESSION['loginID'] ." 
                   group by uid, users_name, full_name 
                   ORDER BY u.users_name";
        $result2 = db_execute_assoc($query2); //Checked

        $surveysecurity ="<div class='header ui-widget-header'>".$clang->gT("Survey permissions")."</div>\n"
        . "<table class='surveysecurity'><thead>"
        . "<tr>\n"
        . "<th>".$clang->gT("Action")."</th>\n"
        . "<th>".$clang->gT("Username")."</th>\n"
        . "<th>".$clang->gT("User Group")."</th>\n"
        . "<th>".$clang->gT("Full name")."</th>\n";
        foreach ($aBaseSurveyPermissions as $sPermission=>$aSubPermissions )
        {
            $surveysecurity.="<th align=\"center\"><img src=\"{$imageurl}/{$aSubPermissions['img']}_30.png\" alt=\"<span style='font-weight:bold;'>".$aSubPermissions['title']."</span><br />".$aSubPermissions['description']."\" /></th>\n";
        }
        $surveysecurity .= "</tr></thead>\n";

        // Foot first

        if (isset($usercontrolSameGroupPolicy) &&
        $usercontrolSameGroupPolicy == true)
        {
            $authorizedGroupsList=getusergrouplist('simplegidarray');
        }

        $surveysecurity .= "<tbody>\n";
        if($result2->RecordCount() > 0)
        {
            //	output users
            $row = 0;
            while ($PermissionRow = $result2->FetchRow())
            {
                
                $query3 = "SELECT a.ugid FROM ".db_table_name('user_in_groups')." AS a RIGHT OUTER JOIN ".db_table_name('users')." AS b ON a.uid = b.uid WHERE b.uid = ".$PermissionRow['uid'];
                $result3 = db_execute_assoc($query3); //Checked
                while ($resul3row = $result3->FetchRow())
                {
                    if (!isset($usercontrolSameGroupPolicy) ||
                    $usercontrolSameGroupPolicy == false ||
                    in_array($resul3row['ugid'],$authorizedGroupsList))
                    {
                        $group_ids[] = $resul3row['ugid'];
                    }
                }

                if(isset($group_ids) && $group_ids[0] != NULL)
                {
                    $group_ids_query = implode(" OR ugid=", $group_ids);
                    unset($group_ids);

                    $query4 = "SELECT name FROM ".db_table_name('user_groups')." WHERE ugid = ".$group_ids_query;
                    $result4 = db_execute_assoc($query4); //Checked

                    while ($resul4row = $result4->FetchRow())
                    {
                        $group_names[] = $resul4row['name'];
                    }
                    if(count($group_names) > 0)
                    $group_names_query = implode(", ", $group_names);
                }
                //                  else {break;} //TODO Commented by lemeur
                $surveysecurity .= "<tr>\n";

                $surveysecurity .= "<td>\n";
                $surveysecurity .= "<form style='display:inline;' method='post' action='$scriptname?sid={$surveyid}'>"
                ."<input type='image' src='{$imageurl}/token_edit.png' title='".$clang->gT("Edit permissions")."' />"
                ."<input type='hidden' name='action' value='setsurveysecurity' />"
                ."<input type='hidden' name='user' value='{$PermissionRow['users_name']}' />"
                ."<input type='hidden' name='uid' value='{$PermissionRow['uid']}' />"
                ."</form>\n";
                $surveysecurity .= "<form style='display:inline;' method='post' action='$scriptname?sid={$surveyid}'>"
                ."<input type='image' src='{$imageurl}/token_delete.png' title='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")' />"
                ."<input type='hidden' name='action' value='delsurveysecurity' />"
                ."<input type='hidden' name='user' value='{$PermissionRow['users_name']}' />"
                ."<input type='hidden' name='uid' value='{$PermissionRow['uid']}' />"
                ."</form>";

                
                $surveysecurity .= "</td>\n";
                $surveysecurity .= "<td>{$PermissionRow['users_name']}</td>\n"
                . "<td>";
                 
                if(isset($group_names) > 0)
                {
                    $surveysecurity .= $group_names_query;
                }
                else
                {
                    $surveysecurity .= "---";
                }
                unset($group_names);

                $surveysecurity .= "</td>\n"
                . "<td>\n{$PermissionRow['full_name']}</td>\n";

                //Now show the permissions
                foreach ($aBaseSurveyPermissions as $sPKey=>$aPDetails) {
                    unset($aPDetails['img']);
                    unset($aPDetails['description']);
                    unset($aPDetails['title']);
                    $iCount=0;
                    $iPermissionCount=0;
                    foreach ($aPDetails as $sPDetailKey=>$sPDetailValue)
                    {
                        if ($sPDetailValue && bHasSurveyPermission($surveyid,$sPKey,$sPDetailKey,$PermissionRow['uid']) && !($sPKey=='survey' && $sPDetailKey=='read')) $iCount++;
                        if ($sPDetailValue) $iPermissionCount++; 
                    }
                    if ($sPKey=='survey')  $iPermissionCount--;
                    if ($iCount==$iPermissionCount) {
                        $insert = "<div class=\"ui-icon ui-icon-check\">&nbsp;</div>";
                    } 
                    elseif ($iCount>0){
                        $insert = "<div class=\"ui-icon ui-icon-check mixed\">&nbsp;</div>";
                    }
                    else
                    {
                        $insert = "<div>&nbsp;</div>";
                    }
                    $surveysecurity .= "<td align=\"center\">\n$insert\n</td>\n";
                }

                $surveysecurity .= "</tr>\n";
                $row++;
            }
        } else {
            $surveysecurity .= "<tr><td colspan='18'></td></tr>"; //fix error on empty table
        }
        $surveysecurity .= "</tbody>\n"
        . "</table>\n"
        . "<form class='form44' action='$scriptname?sid={$surveyid}' method='post'><ul>\n"
        . "<li><label for='uidselect'>".$clang->gT("User").": </label><select id='uidselect' name='uid'>\n"
        . sGetSurveyUserlist(false,false)
        . "</select>\n"
        . "<input style='width: 15em;' type='submit' value='".$clang->gT("Add User")."'  onclick=\"if (document.getElementById('uidselect').value == -1) {alert('".$clang->gT("Please select a user first","js")."'); return false;}\"/>"
        . "<input type='hidden' name='action' value='addsurveysecurity' />"
        . "</li></ul></form>\n"
        . "<form class='form44' action='$scriptname?sid={$surveyid}' method='post'><ul><li>\n"
        . "<label for='ugidselect'>".$clang->gT("Groups").": </label><select id='ugidselect' name='ugid'>\n"
        . getsurveyusergrouplist()
        . "</select>\n"
        . "<input style='width: 15em;' type='submit' value='".$clang->gT("Add User Group")."' onclick=\"if (document.getElementById('ugidselect').value == -1) {alert('".$clang->gT("Please select a user group first","js")."'); return false;}\" />"
        . "<input type='hidden' name='action' value='addusergroupsurveysecurity' />\n"
        . "</li></ul></form>";
        
    }
    else
    {
        include("access_denied.php");
    }
}

elseif ($action == "surveyrights")
{
    $addsummary = "<div class='header ui-widget-header'>".$clang->gT("Edit survey permissions")."</div>\n";
    $addsummary .= "<div class='messagebox ui-corner-all'>\n";

    if(isset($postuserid)){
        $query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} ";
        if ($_SESSION['USER_RIGHT_SUPERADMIN'] != 1)
        {
            $query.=" AND owner_id != ".$postuserid." AND owner_id = ".$_SESSION['loginID'];
        }
    }
    else{
        $query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID'];
    }
    
    $aBaseSurveyPermissions=aGetBaseSurveyPermissions();
    $aPermissions=array();
    foreach ($aBaseSurveyPermissions as $sPermissionKey=>$aCRUDPermissions)
    {
        foreach ($aCRUDPermissions as $sCRUDKey=>$CRUDValue)
        {
            if (!in_array($sCRUDKey,array('create','read','update','delete','import','export'))) continue;
            
            if ($CRUDValue)
            {
                if(isset($_POST["perm_{$sPermissionKey}_{$sCRUDKey}"])){
                    $aPermissions[$sPermissionKey][$sCRUDKey]=1;
                }
                else
                {
                    $aPermissions[$sPermissionKey][$sCRUDKey]=0;
                }
            }
        }        
    }
    if(SetSurveyPermissions($postuserid, $surveyid, $aPermissions))
    {
        $addsummary .= "<div class=\"successheader\">".$clang->gT("Survey permissions were successfully updated.")."</div>\n";
    }
    else
    {
        $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to update survey permissions!")."</div>\n";
    }
    $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?sid={$surveyid}&amp;action=surveysecurity', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
    $addsummary .= "</div>\n";
}

// *************************************************
// Survey Rights End	****************************
// *************************************************


// Editing the survey
if ($action == "editsurveysettings" || $action == "newsurvey")
{
    if(!bHasSurveyPermission($surveyid,'surveysettings','read') && !bHasGlobalPermission('USER_RIGHT_CREATE_SURVEY'))
    {
        include("access_denied.php");
    }
    else
    {
        $js_admin_includes[]='scripts/surveysettings.js';

        if ($action == "newsurvey") {
            //New survey, set the defaults
            $esrow = array();
            $esrow['active']                    = 'N';
            $esrow['format']                    = 'G'; //Group-by-group mode
            $esrow['template']                  = $defaulttemplate;
            $esrow['allowsave']                 = 'Y';
            $esrow['allowprev']                 = 'N';
            $esrow['printanswers']              = 'N';
            $esrow['publicstatistics']          = 'N';
            $esrow['publicgraphs']              = 'N';
            $esrow['public']                    = 'Y';
            $esrow['autoredirect']              = 'N';
            $esrow['tokenlength']               = 15;
            $esrow['allowregister']             = 'N';
            $esrow['usecookie']                 = 'N';
            $esrow['usecaptcha']                = 'D';
            $esrow['htmlemail']                 = 'Y';
            $esrow['emailnotificationto']       = '';
            $esrow['private']                   = 'Y';
            $esrow['datestamp']                 = 'N';
            $esrow['ipaddr']                    = 'N';
            $esrow['refurl']                    = 'N';
            $esrow['tokenanswerspersistence']   = 'N';
            $esrow['assesments']                = 'N';
            $esrow['startdate']                 = '';
            $esrow['expires']                   = '';
            $esrow['showwelcome']               = 'Y';
            $esrow['emailresponseto']           = '';
            $esrow['assessments']               = 'Y';

            $dateformatdetails=getDateFormatData($_SESSION['dateformat']);

            $editsurvey = PrepareEditorScript();

            $editsurvey .="<script type=\"text/javascript\">
                        standardtemplaterooturl='$standardtemplaterooturl';
                        templaterooturl='$usertemplaterooturl'; \n";
            $editsurvey .= "</script>\n";

            // header
            $editsurvey .= "<div class='header ui-widget-header'>" . $clang->gT("Create, import or copy survey") . "</div>\n";
        } elseif ($action == "editsurveysettings") {
            //Fetch survey info
        $esquery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid";
        $esresult = db_execute_assoc($esquery); //Checked
            if ($esrow = $esresult->FetchRow()) {
            $esrow = array_map('htmlspecialchars', $esrow);
            }

            // header
            $editsurvey = "<div class='header ui-widget-header'>".$clang->gT("Edit survey settings")."</div>\n";
        }

            // beginning TABs section - create tab pane
        if ($action == "newsurvey") {
            $editsurvey .= "<div id='tabs'><ul>
            <li><a href='#general'>".$clang->gT("General")."</a></li>
            <li><a href='#presentation'>".$clang->gT("Presentation & navigation")."</a></li>
            <li><a href='#publication'>".$clang->gT("Publication & access control")."</a></li>
            <li><a href='#notification'>".$clang->gT("Notification & data management")."</a></li>
            <li><a href='#tokens'>".$clang->gT("Tokens")."</a></li>
            <li><a href='#import'>".$clang->gT("Import survey")."</a></li>
            <li><a href='#copy'>".$clang->gT("Copy survey")."</a></li>
            </ul>
            \n";
            $editsurvey .= "<form class='form30' name='addnewsurvey' id='addnewsurvey' action='$scriptname' method='post' onsubmit=\"alert('hi');return isEmpty(document.getElementById('surveyls_title'), '" . $clang->gT("Error: You have to enter a title for this survey.", 'js') . "');\" >\n";

            // General & Contact TAB
            $editsurvey .= "<div id='general'>\n";

            // Survey Language
            $editsurvey .= "<ul><li><label for='language' title='" . $clang->gT("This is the base language of your survey and it can't be changed later. You can add more languages after you have created the survey.") . "'><span class='annotationasterisk'>*</span>" . $clang->gT("Base Language:") . "</label>\n"
                    . "<select id='language' name='language'>\n";

            foreach (getLanguageData () as $langkey2 => $langname) {
                $editsurvey .= "<option value='" . $langkey2 . "'";
                if ($defaultlang == $langkey2) {
                    $editsurvey .= " selected='selected'";
                }
                $editsurvey .= ">" . $langname['description'] . "</option>\n";
            }
            $editsurvey .= "</select>\n";
            
            //Use the current user details for the default administrator name and email for this survey
            $query = "SELECT full_name, email FROM " . db_table_name('users') . " WHERE users_name = " . db_quoteall($_SESSION['user']);
            $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
            $owner = $result->FetchRow();
            //Degrade gracefully to $siteadmin details if anything is missing.
            if (empty($owner['full_name']))
                $owner['full_name'] = $siteadminname;
            if (empty($owner['email']))
                $owner['email'] = $siteadminemail;

            $editsurvey .= "<span class='annotation'> " . $clang->gT("*This setting cannot be changed later!") . "</span></li>\n";

            $editsurvey .= ""
                    . "<li><label for='surveyls_title'><span class='annotationasterisk'>*</span>" . $clang->gT("Title") . ":</label>\n"
                    . "<input type='text' size='82' maxlength='200' id='surveyls_title' name='surveyls_title' /> <span class='annotation'>" . $clang->gT("*Required") . "</span></li>\n"
                    . "<li><label for='description'>" . $clang->gT("Description:") . "</label>\n"
                    . "<textarea cols='80' rows='10' id='description' name='description'></textarea>"
                    . getEditor("survey-desc", "description", "[" . $clang->gT("Description:", "js") . "]", '', '', '', $action)
                    . "</li>\n"
                    . "<li><label for='welcome'>" . $clang->gT("Welcome message:") . "</label>\n"
                    . "<textarea cols='80' rows='10' id='welcome' name='welcome'></textarea>"
                    . getEditor("survey-welc", "welcome", "[" . $clang->gT("Welcome message:", "js") . "]", '', '', '', $action)
                    . "</li>\n"
                    . "<li><label for='endtext'>" . $clang->gT("End message:") . "</label>\n"
                    . "<textarea cols='80' id='endtext' rows='10' name='endtext'></textarea>"
                    . getEditor("survey-endtext", "endtext", "[" . $clang->gT("End message:", "js") . "]", '', '', '', $action)
                    . "</li>\n";

            // End URL
            $editsurvey .= "<li><label for='url'>" . $clang->gT("End URL:") . "</label>\n"
                    . "<input type='text' size='50' id='url' name='url' value='http://";
            $editsurvey .= "' /></li>\n";

            // URL description
            $editsurvey.= "<li><label for='urldescrip'>" . $clang->gT("URL description:") . "</label>\n"
                    . "<input type='text' maxlength='255' size='50' id='urldescrip' name='urldescrip' value='";
            $editsurvey .= "' /></li>\n"

                    //Default date format
                    . "<li><label for='dateformat'>" . $clang->gT("Date format:") . "</label>\n"
                    . "<select size='1' id='dateformat' name='dateformat'>\n";
            foreach (getDateFormatData () as $index => $dateformatdata) {
                $editsurvey.= "<option value='{$index}'";
                $editsurvey.= ">" . $dateformatdata['dateformat'] . '</option>';
            }
            $editsurvey.= "</select></li>"
            
                    . "<li><label for='admin'>" . $clang->gT("Administrator:") . "</label>\n"
                    . "<input type='text' size='50' id='admin' name='admin' value='" . $owner['full_name'] . "' /></li>\n"
                    . "<li><label for='adminemail'>" . $clang->gT("Admin Email:") . "</label>\n"
                    . "<input type='text' size='50' id='adminemail' name='adminemail' value='" . $owner['email'] . "' /></li>\n"
                    . "<li><label for='bounce_email'>" . $clang->gT("Bounce Email:") . "</label>\n"
                    . "<input type='text' size='50' id='bounce_email' name='bounce_email' value='" . $owner['email'] . "' /></li>\n"
                    . "<li><label for='faxto'>" . $clang->gT("Fax To:") . "</label>\n"
                    . "<input type='text' size='50' id='faxto' name='faxto' /></li>\n";

            $editsurvey.= "</ul>";


            // End General TAB
            $editsurvey .= "</div>\n";
        } elseif ($action == "editsurveysettings") {
            $editsurvey .= "<div id='tabs'><ul>
            <li><a href='#general'>".$clang->gT("General")."</a></li>
            <li><a href='#presentation'>".$clang->gT("Presentation & navigation")."</a></li>
            <li><a href='#publication'>".$clang->gT("Publication & access control")."</a></li>
            <li><a href='#notification'>".$clang->gT("Notification & data management")."</a></li>
            <li><a href='#tokens'>".$clang->gT("Tokens")."</a></li>
            <li><a href='#resources'>".$clang->gT("Resources")."</a></li>
            </ul>
            \n";
            $editsurvey .= "<form class='form30' name='addnewsurvey' id='addnewsurvey' action='$scriptname' method='post' onsubmit=\"alert('hi');return isEmpty(document.getElementById('surveyls_title'), '" . $clang->gT("Error: You have to enter a title for this survey.", 'js') . "');\" >\n";

            // General & Contact TAB
            $editsurvey .= "<div id='general'>\n";

            // Base Language
            $editsurvey .= "<ul><li><label>" . $clang->gT("Base Language:") . "</label>\n"
            .GetLanguageNameFromCode($esrow['language'])
            . "</li>\n"

            // Additional languages listbox
            . "<li><label for='additional_languages'>".$clang->gT("Additional Languages").":</label>\n"
            . "<table><tr><td align='left'><select style='min-width:220px;' size='5' id='additional_languages' name='additional_languages'>";
            $jsX=0;
            $jsRemLang ="<script type=\"text/javascript\">
                            var mylangs = new Array();
                            standardtemplaterooturl='$standardtemplaterooturl';
                            templaterooturl='$usertemplaterooturl'; \n";

            foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname) {
                if ($langname && $langname != $esrow['language']) { // base languag must not be shown here
                    $jsRemLang .="mylangs[$jsX] = \"$langname\"\n";
                    $editsurvey .= "<option id='".$langname."' value='".$langname."'";
                    $editsurvey .= ">".getLanguageNameFromCode($langname,false)."</option>\n";
                    $jsX++;
                }
            }
            $jsRemLang .= "</script>\n";
            $editsurvey .= $jsRemLang;
            //  Add/Remove Buttons
            $editsurvey .= "</select></td>"
            . "<td align='left'><input type=\"button\" value=\"<< ".$clang->gT("Add")."\" onclick=\"DoAdd()\" id=\"AddBtn\" /><br /> <input type=\"button\" value=\"".$clang->gT("Remove")." >>\" onclick=\"DoRemove(0,'')\" id=\"RemoveBtn\"  /></td>\n"

            // Available languages listbox
            . "<td align='left'><select size='5' style='min-width:220px;' id='available_languages' name='available_languages'>";
            $tempLang=GetAdditionalLanguagesFromSurveyID($surveyid);
            foreach (getLanguageData () as $langkey2 => $langname) {
                if ($langkey2 != $esrow['language'] && in_array($langkey2, $tempLang) == false) {  // base languag must not be shown here
                    $editsurvey .= "<option id='".$langkey2."' value='".$langkey2."'";
                    $editsurvey .= ">".$langname['description']."</option>\n";
                }
            }
            $editsurvey .= "</select></td>"
            . " </tr></table></li>\n";
            // Administrator...
            $editsurvey .= ""
            . "<li><label for='admin'>".$clang->gT("Administrator:")."</label>\n"
            . "<input type='text' size='50' id='admin' name='admin' value=\"{$esrow['admin']}\" /></li>\n"
            . "<li><label for='adminemail'>".$clang->gT("Admin Email:")."</label>\n"
            . "<input type='text' size='50' id='adminemail' name='adminemail' value=\"{$esrow['adminemail']}\" /></li>\n"
            . "<li><label for='bounce_email'>".$clang->gT("Bounce Email:")."</label>\n"
            . "<input type='text' size='50' id='bounce_email' name='bounce_email' value=\"{$esrow['bounce_email']}\" /></li>\n"
            . "<li><label for='faxto'>".$clang->gT("Fax To:")."</label>\n"
                    . "<input type='text' size='50' id='faxto' name='faxto' value=\"{$esrow['faxto']}\" /></li></ul>\n";

            // End General TAB
            $editsurvey .= "</div>\n";
        }

            // Presentation and navigation TAB
            $editsurvey .= "<div id='presentation'><ul>\n";

            //Format
            $editsurvey .= "<li><label for='format'>".$clang->gT("Format:")."</label>\n"
            . "<select id='format' name='format'>\n"
            . "<option value='S'";
        if ($esrow['format'] == "S" || !$esrow['format']) {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Question by Question")."</option>\n"
            . "<option value='G'";
        if ($esrow['format'] == "G") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Group by Group")."</option>\n"
            . "<option value='A'";
        if ($esrow['format'] == "A") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("All in one")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //TEMPLATES
            $editsurvey .= "<li><label for='template'>".$clang->gT("Template:")."</label>\n"
            . "<select id='template' name='template'>\n";
        foreach (array_keys(gettemplatelist()) as $tname) {

            if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] == 1 || hasTemplateManageRights($_SESSION["loginID"], $tname) == 1) {
                    $editsurvey .= "<option value='$tname'";
                if ($esrow['template'] && htmlspecialchars($tname) == $esrow['template']) {
                    $editsurvey .= " selected='selected'";
                } elseif (!$esrow['template'] && $tname == "default") {
                    $editsurvey .= " selected='selected'";
                }
                    $editsurvey .= ">$tname</option>\n";
                }
            }
            $editsurvey .= "</select>\n"
            . "</li>\n";

            $editsurvey .= "<li><label for='preview'>".$clang->gT("Template Preview:")."</label>\n"
            . "<img alt='".$clang->gT("Template preview image")."' id='preview' src='".sGetTemplateURL($esrow['template'])."/preview.png' />\n"
            . "</li>\n" ;

        //SHOW WELCOMESCRN
        $editsurvey .= "<li><label for='showwelcome'>" . $clang->gT("Show welcome screen?") . "</label>\n"
                . "<select id='showwelcome' name='showwelcome'>\n"
                . "<option value='Y'";
        if (!$esrow['showwelcome'] || $esrow['showwelcome'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">" . $clang->gT("Yes") . "</option>\n"
                . "<option value='N'";
        if ($esrow['showwelcome'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">" . $clang->gT("No") . "</option>\n"
                . "</select></li>\n";



        //Show Prev Button
        $editsurvey .= "<li><label for='allowprev'>".$clang->gT("Show [<< Prev] button")."</label>\n"
        . "<select id='allowprev' name='allowprev'>\n"
        . "<option value='Y'";
        if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

	//Show Progress
	$editsurvey .= "<li><label for='showprogress'>".$clang->gT("Show progress bar")."</label>\n"
                . "<select id='showprogress' name='showprogress'>\n"
                . "<option value='Y'";
	if (!isset($esrow['showprogress']) || !$esrow['showprogress'] || $esrow['showprogress'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
	$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
	if (isset($esrow['showprogress']) && $esrow['showprogress'] == "N") {
            $editsurvey .= " selected='selected'";
        }
	$editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select></li>\n";

            //Result printing
            $editsurvey .= "<li><label for='printanswers'>".$clang->gT("Participants may print answers?")."</label>\n"
            . "<select id='printanswers' name='printanswers'>\n"
            . "<option value='Y'";
        if (!isset($esrow['printanswers']) || !$esrow['printanswers'] || $esrow['printanswers'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['printanswers']) && $esrow['printanswers'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //Public statistics
            $editsurvey .= "<li><label for='publicstatistics'>".$clang->gT("Public statistics?")."</label>\n"
            . "<select id='publicstatistics' name='publicstatistics'>\n"
            . "<option value='Y'";
        if (!isset($esrow['publicstatistics']) || !$esrow['publicstatistics'] || $esrow['publicstatistics'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['publicstatistics']) && $esrow['publicstatistics'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //Public statistics
            $editsurvey .= "<li><label for='publicgraphs'>".$clang->gT("Show graphs in public statistics?")."</label>\n"
            . "<select id='publicgraphs' name='publicgraphs'>\n"
            . "<option value='Y'";
        if (!isset($esrow['publicgraphs']) || !$esrow['publicgraphs'] || $esrow['publicgraphs'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['publicgraphs']) && $esrow['publicgraphs'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

           
            // End URL block
            $editsurvey .= "<li><label for='autoredirect'>".$clang->gT("Automatically load URL when survey complete?")."</label>\n"
            . "<select id='autoredirect' name='autoredirect'>";
            $editsurvey .= "<option value='Y'";
        if (isset($esrow['autoredirect']) && $esrow['autoredirect'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n";
            $editsurvey .= "<option value='N'";
        if (!isset($esrow['autoredirect']) || $esrow['autoredirect'] != "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>";

            // Show {THEREAREXQUESTIONS} block
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_showXquestions\">".$clang->gT('Show "There are X questions in this survey"')."</label>\n\t\t".'<input type="hidden" name="showXquestions" id="" value="';
	    $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_showXquestions" id="dis_showXquestions" disabled="disabled" value="';
	    $show_dis_post = "\" size=\"70\" />\n\t</li>\n";
        switch ($showXquestions) {
		case 'show':
		    $editsurvey .= $show_dis_pre.'Y'.$show_dis_mid.$clang->gT('Yes (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'hide':
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('No (Forced by the system administrator)').$show_dis_post;
		    break;
	    	case 'choose':
		default:
		    $sel_showxq = array( 'Y' => '' , 'N' => '' );
                if (isset($esrow['showXquestions'])) {
		    	$set_showxq = $esrow['showXquestions'];
			$sel_showxq[$set_showxq] = ' selected="selected"';
		    }
                if (empty($sel_showxq['Y']) && empty($sel_showxq['N'])) {
		    	$sel_showxq['Y'] = ' selected="selected"';
		    };
		    $editsurvey .= "\n\t<li>\n\t\t<label for=\"showXquestions\">".$clang->gT('Show "There are X questions in this survey"')."</label>\n\t\t"
		    . "<select id=\"showXquestions\" name=\"showXquestions\">\n\t\t\t"
		    . '<option value="Y"'.$sel_showxq['Y'].'>'.$clang->gT('Yes')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showxq['N'].'>'.$clang->gT('No')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    unset($sel_showxq,$set_showxq);
		    break;
	    };

            // Show {GROUPNAME} and/or {GROUPDESCRIPTION} block
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_showgroupinfo\">".$clang->gT('Show Group Name and/or Group Description')."</label>\n\t\t".'<input type="hidden" name="showgroupinfo" id="showgroupinfo" value="';
            $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_showgroupinfo" id="dis_showgroupinfo" disabled="disabled" value="';
        switch ($showgroupinfo) {
		case 'both':
		    $editsurvey .= $show_dis_pre.'B'.$show_dis_mid.$clang->gT('Show both (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'name':
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('Show group name only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'description':
		    $editsurvey .= $show_dis_pre.'D'.$show_dis_mid.$clang->gT('Show group description only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'none':
		    $editsurvey .= $show_dis_pre.'X'.$show_dis_mid.$clang->gT('Hide both (Forced by the system administrator)').$show_dis_post;
		    break;
	    	case 'choose':
		default:
		    $sel_showgri = array( 'B' => '' , 'D' => '' , 'N' => '' , 'X' => '' );
                if (isset($esrow['showgroupinfo'])) {
		    	$set_showgri = $esrow['showgroupinfo'];
			$sel_showgri[$set_showgri] = ' selected="selected"';
		    }
                if (empty($sel_showgri['B']) && empty($sel_showgri['D']) && empty($sel_showgri['N']) && empty($sel_showgri['X'])) {
		    	$sel_showgri['C'] = ' selected="selected"';
		    };
		    $editsurvey .= "\n\t<li>\n\t\t<label for=\"showgroupinfo\">".$clang->gT('Show Group Name and/or Group Description')."</label>\n\t\t"
		    . "<select id=\"showgroupinfo\" name=\"showgroupinfo\">\n\t\t\t"
		    . '<option value="B"'.$sel_showgri['B'].'>'.$clang->gT('Show both')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showgri['N'].'>'.$clang->gT('Show group name only')."</option>\n\t\t\t"
		    . '<option value="D"'.$sel_showgri['D'].'>'.$clang->gT('Show group description only')."</option>\n\t\t\t"
		    . '<option value="X"'.$sel_showgri['X'].'>'.$clang->gT('Hide both')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    unset($sel_showgri,$set_showgri);
		    break;
	    };

            // Show {QUESTION_CODE} and/or {QUESTION_NUMBER} block
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_showqnumcode\">".$clang->gT('Show Question Number and/or Question Code')."</label>\n\t\t".'<input type="hidden" name="showqnumcode" id="showqnumcode" value="';
            $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_showqnumcode" id="dis_showqnumcode" disabled="disabled" value="';
        switch ($showqnumcode) {
		case 'none':
		    $editsurvey .= $show_dis_pre.'X'.$show_dis_mid.$clang->gT('Hide both (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'number':
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('Show question number only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'code':
		    $editsurvey .= $show_dis_pre.'C'.$show_dis_mid.$clang->gT('Show question code only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'both':
		    $editsurvey .= $show_dis_pre.'B'.$show_dis_mid.$clang->gT('Show both (Forced by the system administrator)').$show_dis_post;
		    break;
	    	case 'choose':
		default:
		    $sel_showqnc = array( 'B' => '' , 'C' => '' , 'N' => '' , 'X' => '' );
                if (isset($esrow['showqnumcode'])) {
		    	$set_showqnc = $esrow['showqnumcode'];
			$sel_showqnc[$set_showqnc] = ' selected="selected"';
		    }
                if (empty($sel_showqnc['B']) && empty($sel_showqnc['C']) && empty($sel_showqnc['N']) && empty($sel_showqnc['X'])) {
		    	$sel_showqnc['C'] = ' selected="selected"';
		    };
		    $editsurvey .= "\n\t<li>\n\t\t<label for=\"showqnumcode\">".$clang->gT('Show Question Number and/or Question Code')."</label>\n\t\t"
		    . "<select id=\"showqnumcode\" name=\"showqnumcode\">\n\t\t\t"
		    . '<option value="B"'.$sel_showqnc['B'].'>'.$clang->gT('Show both')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showqnc['N'].'>'.$clang->gT('Show question number only')."</option>\n\t\t\t"
		    . '<option value="C"'.$sel_showqnc['C'].'>'.$clang->gT('Show question Code only')."</option>\n\t\t\t"
		    . '<option value="X"'.$sel_showqnc['X'].'>'.$clang->gT('Hide both')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    unset($sel_showqnc,$set_showqnc);
		    break;
	    };

            // Show "No Answer" block
	    $shownoanswer = isset($shownoanswer)?$shownoanswer:'Y';
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_shownoanswer\">".$clang->gT('Show no answer')."</label>\n\t\t".'<input type="hidden" name="shownoanswer" id="shownoanswer" value="';
            $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_shownoanswer" id="dis_shownoanswer" disabled="disabled" value="';
        switch ($shownoanswer) {
	    	case 0:
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('Off (Forced by the system administrator)').$show_dis_post;
		    break;
	        case 2:
		    $sel_showno = array( 'Y' => '' , 'N' => '' );
                if (isset($esrow['shownoanswer'])) {
		    	$set_showno = $esrow['shownoanswer'];
			$sel_showno[$set_showno] = ' selected="selected"';
		    };
                if (empty($sel_showno)) {
		    	$sel_showno['Y'] = ' selected="selected"';
		    };
	    	    $editsurvey .= "\n\t<li>\n\t\t<label for=\"shownoanswer\">".$clang->gT('Show No Answer')."</label>\n\t\t"
		    . "<select id=\"shownoanswer\" name=\"shownoanswer\">\n\t\t\t"
		    . '<option value="Y"'.$sel_showno['Y'].'>'.$clang->gT('Yes')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showno['N'].'>'.$clang->gT('No')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    break;
		default:
		    $editsurvey .= $show_dis_pre.'Y'.$show_dis_mid.$clang->gT('On (Forced by the system administrator)').$show_dis_post;
		    break;
	    };

            // End Presention and navigation TAB
            $editsurvey .= "</ul></div>\n";

            // Publication and access control TAB
            $editsurvey .= "<div id='publication'><ul>\n";

             //Public Surveys
            $editsurvey .= "<li><label for='public'>".$clang->gT("List survey publicly:")."</label>\n"
            . "<select id='public' name='public'>\n"
            . "<option value='Y'";
        if (!isset($esrow['listpublic']) || !$esrow['listpublic'] || $esrow['listpublic'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['listpublic']) && $esrow['listpublic'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            // Self registration
            $editsurvey .= "<li><label for='allowregister'>".$clang->gT("Allow public registration?")."</label>\n"
            . "<select id='allowregister' name='allowregister'>\n"
            . "<option value='Y'";
        if ($esrow['allowregister'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['allowregister'] != "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

            // Start date
            $dateformatdetails=getDateFormatData($_SESSION['dateformat']);
            $startdate='';
        if (trim($esrow['startdate']) != '') {
                $datetimeobj = new Date_Time_Converter($esrow['startdate'] , "Y-m-d H:i:s");
                $startdate=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
            }

            $editsurvey .= "<li><label for='startdate'>".$clang->gT("Start date/time:")."</label>\n"
            . "<input type='text' class='popupdatetime' id='startdate' size='20' name='startdate' value=\"{$startdate}\" /></li>\n";

            // Expiration date
            $expires='';
        if (trim($esrow['expires']) != '') {
                $datetimeobj = new Date_Time_Converter($esrow['expires'] , "Y-m-d H:i:s");
                $expires=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
            }
            $editsurvey .="<li><label for='expires'>".$clang->gT("Expiry date/time:")."</label>\n"
            . "<input type='text' class='popupdatetime' id='expires' size='20' name='expires' value=\"{$expires}\" /></li>\n";

            //COOKIES
            $editsurvey .= "<li><label for=''>".$clang->gT("Set cookie to prevent repeated participation?")."</label>\n"
            . "<select name='usecookie'>\n"
            . "<option value='Y'";
        if ($esrow['usecookie'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['usecookie'] != "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            // Use Captcha
            $editsurvey .= "<li><label for=''>".$clang->gT("Use CAPTCHA for").":</label>\n"
            . "<select name='usecaptcha'>\n"
            . "<option value='A'";
        if ($esrow['usecaptcha'] == "A") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='B'";
        if ($esrow['usecaptcha'] == "B") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ---------</option>\n"
            . "<option value='C'";
        if ($esrow['usecaptcha'] == "C") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("Survey Access")." / ------------ / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='D'";
        if ($esrow['usecaptcha'] == "D") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">------------- / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='X'";

        if ($esrow['usecaptcha'] == "X") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("Survey Access")." / ------------ / ---------</option>\n"
            . "<option value='R'";
        if ($esrow['usecaptcha'] == "R") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">------------- / ".$clang->gT("Registration")." / ---------</option>\n"
            . "<option value='S'";
        if ($esrow['usecaptcha'] == "S") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">------------- / ------------ / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='N'";
        if ($esrow['usecaptcha'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">------------- / ------------ / ---------</option>\n"
            . "</select>\n</li>\n";

            // Email format
            $editsurvey .= "<li><label for=''>".$clang->gT("Use HTML format for token emails?")."</label>\n"
            . "<select name='htmlemail' onchange=\"alert('".$clang->gT("If you switch email mode, you'll have to review your email templates to fit the new format","js")."');\">\n"
            . "<option value='Y'";
        if ($esrow['htmlemail'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['htmlemail'] == "N") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

            // End Publication and access control TAB
            $editsurvey .= "</ul></div>\n";

            
            // Notification and Data management TAB
            $editsurvey .= "<div id='notification'><ul>\n";


            //NOTIFICATION
            $editsurvey .= "<li><label for='emailnotificationto'>".$clang->gT("Send basic admin notification email to:")."</label>\n"
            . "<input size='70' type='text' value=\"{$esrow['emailnotificationto']}\" id='emailnotificationto' name='emailnotificationto' />\n"
            . "</li>\n";

            //EMAIL SURVEY RESPONSES TO
            $editsurvey .= "<li><label for='emailresponseto'>".$clang->gT("Send detailed admin notification email to:")."</label>\n"
            . "<input size='70' type='text' value=\"{$esrow['emailresponseto']}\" id='emailresponseto' name='emailresponseto' />\n"
            . "</li>\n";

            //ANONYMOUS
            $editsurvey .= "<li><label for=''>".$clang->gT("Anonymized responses?")."\n";
            // warning message if anonymous + tokens used
            $editsurvey .= "\n"
            . "<script type=\"text/javascript\"><!-- \n"
            . "function alertPrivacy()\n"
            . "{\n"
            . "if (document.getElementById('tokenanswerspersistence').value == 'Y')\n"
            . "{\n"
            . "alert('".$clang->gT("You can't use Anonymized responses when Token-based answers persistence is enabled.","js")."');\n"
            . "document.getElementById('private').value = 'N';\n"
            . "}\n"
            . "else if (document.getElementById('private').value == 'Y')\n"
            . "{\n"
            . "alert('".$clang->gT("Warning").": ".$clang->gT("If you turn on the -Anonymized responses- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.","js")."');\n"
            . "}\n"
            . "}"
            . "//--></script></label>\n";

        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['private'] == "N") {
                $editsurvey .= " " . $clang->gT("This survey is NOT anonymous.");
            } else {
                $editsurvey .= $clang->gT("Answers to this survey are anonymized.");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='private' value=\"{$esrow['private']}\" />\n";
        } else {
                $editsurvey .= "<select id='private' name='private' onchange='alertPrivacy();'>\n"
                . "<option value='Y'";
            if ($esrow['private'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['private'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";

            // date stamp
            $editsurvey .= "<li><label for='datestamp'>".$clang->gT("Date Stamp?")."</label>\n";
        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['datestamp'] != "Y") {
                $editsurvey .= " " . $clang->gT("Responses will not be date stamped.");
            } else {
                $editsurvey .= $clang->gT("Responses will be date stamped.");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='datestamp' value=\"{$esrow['datestamp']}\" />\n";
        } else {
                $editsurvey .= "<select id='datestamp' name='datestamp' onchange='alertPrivacy();'>\n"
                . "<option value='Y'";
            if ($esrow['datestamp'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['datestamp'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";

            // Ip Addr
            $editsurvey .= "<li><label for=''>".$clang->gT("Save IP Address?")."</label>\n";

        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['ipaddr'] != "Y") {
                $editsurvey .= " " . $clang->gT("Responses will not have the IP address logged.");
            } else {
                $editsurvey .= $clang->gT("Responses will have the IP address logged");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='ipaddr' value='".$esrow['ipaddr']."' />\n";
        } else {
                $editsurvey .= "<select name='ipaddr'>\n"
                . "<option value='Y'";
            if ($esrow['ipaddr'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['ipaddr'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }

            $editsurvey .= "</li>\n";

            // begin REF URL Block
            $editsurvey .= "<li><label for=''>".$clang->gT("Save Referring URL?")."</label>\n";

        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['refurl'] != "Y") {
                $editsurvey .= " " . $clang->gT("Responses will not have their referring URL logged.");
            } else {
                $editsurvey .= $clang->gT("Responses will have their referring URL logged.");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='refurl' value='".$esrow['refurl']."' />\n";
        } else {
                $editsurvey .= "<select name='refurl'>\n"
                . "<option value='Y'";
            if ($esrow['refurl'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['refurl'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";
            // BENBUN - END REF URL Block

            // Enable assessments
            $editsurvey .= "<li><label for=''>".$clang->gT("Enable assessment mode?")."</label>\n"
            . "<select id='assessments' name='assessments'>\n"
            . "<option value='Y'";
        if ($esrow['assessments'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['assessments'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></li>\n";


        // Save timings
        $editsurvey .= "<li><label for='savetimings'>".$clang->gT("Save timings?")."</label>\n";
        if ($esrow['active']=="Y")
        {
            $editsurvey .= "\n";
            if ($esrow['savetimings'] != "Y") {$editsurvey .= " ".$clang->gT("Timings will not be saved.");}
            else {$editsurvey .= $clang->gT("Timings will be saved.");}
            $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
            . "</font>\n";
            $editsurvey .= "<input type='hidden' name='savetimings' value='".$esrow['savetimings']."' />\n";
		}
		else
        {
			$editsurvey .= "<select id='savetimings' name='savetimings'>\n"
			. "<option value='Y'";
			if (!isset($esrow['savetimings']) || !$esrow['savetimings'] || $esrow['savetimings'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "<option value='N'";
			if (isset($esrow['savetimings']) && $esrow['savetimings'] == "N") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "</select>\n"
			. "</li>\n";
		}
        //ALLOW SAVES
        $editsurvey .= "<li><label for='allowsave'>".$clang->gT("Participant may save and resume later?")."</label>\n"
        . "<select id='allowsave' name='allowsave'>\n"
        . "<option value='Y'";
        if (!$esrow['allowsave'] || $esrow['allowsave'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['allowsave'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></li>\n";        


        // End Notification and Data management TAB
        $editsurvey .= "</ul></div>\n";

        // Tokens TAB
        $editsurvey .= "<div id='tokens'><ul>\n";
        // Token answers persistence
        $editsurvey .= "<li><label for=''>".$clang->gT("Enable token-based response persistence?")."</label>\n"
        . "<select id='tokenanswerspersistence' name='tokenanswerspersistence' onchange=\"javascript: if (document.getElementById('private').value == 'Y') {alert('".$clang->gT("This option can't be set if Anonymized responses are used","js")."'); this.value='N';}\">\n"
        . "<option value='Y'";
        if ($esrow['tokenanswerspersistence'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
        . "<option value='N'";
        if ($esrow['tokenanswerspersistence'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        //Set token length
        $editsurvey .= "<li><label for='tokenlength'>".$clang->gT("Set token length to:")."</label>\n"
        . "<input type='text' value=\"{$esrow['tokenlength']}\" name='tokenlength' id='tokenlength' size='12' maxlength='2' onkeypress=\"return goodchars(event,'0123456789')\" />\n"
        . "</li>\n";
        
        // End Tokens TAB
        $editsurvey .= "</ul></div>\n";
            
            
            // Ending First TABs Form
        if ($action == "newsurvey") {
            $editsurvey .= "<input type='hidden' id='surveysettingsaction' name='action' value='insertsurvey' />\n";
        } elseif ($action == "editsurveysettings") {
        $editsurvey .= "<input type='hidden' id='surveysettingsaction' name='action' value='updatesurveysettings' />\n"
            . "<input type='hidden' name='sid' value=\"{$esrow['sid']}\" />\n"
            . "<input type='hidden' name='languageids' id='languageids' value=\"{$esrow['additional_languages']}\" />\n"
                . "<input type='hidden' name='language' value=\"{$esrow['language']}\" />\n";
        }
        $editsurvey .= "</form>";

        if ($action == "newsurvey" ) {
            // Import TAB
            $editsurvey .= "<div id='import'>\n";

            // Import Survey
            $editsurvey .= "<form enctype='multipart/form-data' class='form30' id='importsurvey' name='importsurvey' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"" . $clang->gT('Please select a file to import!', 'js') . "\");'>\n"
                    . "<ul>\n"
                    . "<li><label for='the_file'>" . $clang->gT("Select survey structure file (*.lss, *.csv):") . "</label>\n"
                    . "<input id='the_file' name=\"the_file\" type=\"file\" size=\"50\" /></li>\n"
                    . "<li><label for='translinksfields'>" . $clang->gT("Convert resource links and INSERTANS fields?") . "</label>\n"
                    . "<input id='translinksfields' name=\"translinksfields\" type=\"checkbox\" checked='checked'/></li></ul>\n"
                    . "<p><input type='submit' value='" . $clang->gT("Import survey") . "' />\n"
                    . "<input type='hidden' name='action' value='importsurvey' /></p></form>\n";

            // End Import TAB
            $editsurvey .= "</div>\n";

            // Copy survey TAB
            $editsurvey .= "<div id='copy'>\n";

            // Copy survey
            $editsurvey .= "<form class='form30' action='$scriptname' id='copysurveyform' method='post'>\n"
                    . "<ul>\n"
                    . "<li><label for='copysurveylist'><span class='annotationasterisk'>*</span>" . $clang->gT("Select survey to copy:") . "</label>\n"
                    . "<select id='copysurveylist' name='copysurveylist'>\n"
                    . getsurveylist(false, true) . "</select> <span class='annotation'>" . $clang->gT("*Required") . "</span></li>\n"
                    . "<li><label for='copysurveyname'><span class='annotationasterisk'>*</span>" . $clang->gT("New survey title:") . "</label>\n"
                    . "<input type='text' id='copysurveyname' size='82' maxlength='200' name='copysurveyname' value='' />"
                    . "<span class='annotation'>" . $clang->gT("*Required") . "</span></li>\n"
                    . "<li><label for='copysurveytranslinksfields'>" . $clang->gT("Convert resource links and INSERTANS fields?") . "</label>\n"
                    . "<input id='copysurveytranslinksfields' name=\"copysurveytranslinksfields\" type=\"checkbox\" checked='checked'/></li>\n"
                    . "<li><label for='copysurveyexcludequotas'>" . $clang->gT("Exclude quotas?") . "</label>\n"
                    . "<input id='copysurveyexcludequotas' name=\"copysurveyexcludequotas\" type=\"checkbox\" /></li>\n"
                    . "<li><label for='copysurveyexcludeanswers'>" . $clang->gT("Exclude answers?") . "</label>\n"
                    . "<input id='copysurveyexcludeanswers' name=\"copysurveyexcludeanswers\" type=\"checkbox\" /></li>\n"
                    . "<li><label for='copysurveyresetconditions'>" . $clang->gT("Reset conditions?") . "</label>\n"
                    . "<input id='copysurveyresetconditions' name=\"copysurveyresetconditions\" type=\"checkbox\" /></li></ul>\n"
                    . "<p><input type='submit' value='" . $clang->gT("Copy survey") . "' />\n"
                    . "<input type='hidden' name='action' value='copysurvey' /></p></form>\n";

            // End Copy survey TAB
            $editsurvey .= "</div>\n";
        } elseif ($action = "editsurveysettings") {
            // TAB Uploaded Resources Management
            $ZIPimportAction = " onclick='if (validatefilename(this.form,\"".$clang->gT('Please select a file to import!','js')."\")) {this.form.submit();}'";
            if (!function_exists("zip_open")) {
                $ZIPimportAction = " onclick='alert(\"".$clang->gT("zip library not supported by PHP, Import ZIP Disabled","js")."\");'";
            }

            $disabledIfNoResources = '';
            if (hasResources($surveyid, 'survey') === false) {
                $disabledIfNoResources = " disabled='disabled'";
            }

            $editsurvey .= "<div id='resources'>\n"
            . "<form enctype='multipart/form-data'  class='form30' id='importsurveyresources' name='importsurveyresources' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
            . "<input type='hidden' name='sid' value='$surveyid' />\n"
            . "<input type='hidden' name='action' value='importsurveyresources' />\n"
            . "<ul>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' onclick='window.open(\"$sFCKEditorURL/editor/filemanager/browser/default/browser.html?Connector=../../connectors/php/connector.php\", \"_blank\")' value=\"".$clang->gT("Browse Uploaded Resources")."\" $disabledIfNoResources /></li>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' onclick='window.open(\"$scriptname?action=exportsurvresources&amp;sid={$surveyid}\", \"_blank\")' value=\"".$clang->gT("Export Resources As ZIP Archive")."\" $disabledIfNoResources /></li>\n"
            . "<li><label for='the_file'>".$clang->gT("Select ZIP File:")."</label>\n"
            . "<input id='the_file' name='the_file' type='file' size='50' /></li>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' value='".$clang->gT("Import Resources ZIP Archive")."' $ZIPimportAction /></li>\n"
            . "</ul></form>\n";

            // End TAB Uploaded Resources Management
            $editsurvey .= "</div>\n";
        }

            // End TAB pane
            $editsurvey .= "</div>\n";

            // The external button to sumbit Survey edit changes
        if ($action == "newsurvey") {
            $cond = "if (isEmpty(document.getElementById(\"surveyls_title\"), \"" . $clang->gT("Error: You have to enter a title for this survey.", 'js') . "\"))";
            $editsurvey .= "<p><button onclick='$cond {document.getElementById(\"addnewsurvey\").submit();}' class='standardbtn' >" . $clang->gT("Save") . "</button></p>\n";
        } elseif ($action == "editsurveysettings") {
            $cond = "if (UpdateLanguageIDs(mylangs,\"" . $clang->gT("All questions, answers, etc for removed languages will be lost. Are you sure?", "js") . "\"))";
            if (bHasSurveyPermission($surveyid,'surveysettings','update'))
            {
                $editsurvey .= "<p><button onclick='$cond {document.getElementById(\"addnewsurvey\").submit();}' class='standardbtn' >" . $clang->gT("Save") . "</button></p>\n";
            }
            if (bHasSurveyPermission($surveyid,'surveylocale','read'))
            {
                $editsurvey .= "<p><button onclick='$cond {document.getElementById(\"surveysettingsaction\").value = \"updatesurveysettingsandeditlocalesettings\"; document.getElementById(\"addnewsurvey\").submit();}' class='standardbtn' >" . $clang->gT("Save & edit survey text elements") . " >></button></p>\n";
            }
        }
    }
    }

if ($action == "updatesurveysettingsandeditlocalesettings" || $action == "editsurveylocalesettings")  // Edit survey step 2  - editing language dependent settings
{
    if(bHasSurveyPermission($surveyid,'surveylocale','read'))
    {

        $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($grplangs,$baselang);

        $editsurvey = PrepareEditorScript();


        $editsurvey .="<div class='header ui-widget-header'>".$clang->gT("Edit survey text elements")."</div>\n";
        $editsurvey .= "<form id='addnewsurvey' class='form30' name='addnewsurvey' action='$scriptname' method='post'>\n"
        . '<div class="tab-pane" id="tab-pane-surveyls-'.$surveyid.'">';
        foreach ($grplangs as $grouplang)
        {
            // this one is created to get the right default texts fo each language
            $bplang = new limesurvey_lang($grouplang);
            $esquery = "SELECT * FROM ".db_table_name("surveys_languagesettings")." WHERE surveyls_survey_id=$surveyid and surveyls_language='$grouplang'";
            $esresult = db_execute_assoc($esquery); //Checked
            $esrow = $esresult->FetchRow();
            $editsurvey .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($esrow['surveyls_language'],false);
            if ($esrow['surveyls_language']==GetBaseLanguageFromSurveyID($surveyid)) {$editsurvey .= '('.$clang->gT("Base Language").')';}
            $editsurvey .= '</h2><ul>';
            $esrow = array_map('htmlspecialchars', $esrow);
            $editsurvey .= "<li><label for=''>".$clang->gT("Survey title").":</label>\n"
            . "<input type='text' size='80' name='short_title_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_title']}\" /></li>\n"
            . "<li><label for=''>".$clang->gT("Description:")."</label>\n"
            . "<textarea cols='80' rows='15' name='description_".$esrow['surveyls_language']."'>{$esrow['surveyls_description']}</textarea>\n"
            . getEditor("survey-desc","description_".$esrow['surveyls_language'], "[".$clang->gT("Description:", "js")."](".$esrow['surveyls_language'].")",'','','',$action)
            . "</li>\n"
            . "<li><label for=''>".$clang->gT("Welcome message:")."</label>\n"
            . "<textarea cols='80' rows='15' name='welcome_".$esrow['surveyls_language']."'>{$esrow['surveyls_welcometext']}</textarea>\n"
            . getEditor("survey-welc","welcome_".$esrow['surveyls_language'], "[".$clang->gT("Welcome:", "js")."](".$esrow['surveyls_language'].")",'','','',$action)
            . "</li>\n"
            . "<li><label for=''>".$clang->gT("End message:")."</label>\n"
            . "<textarea cols='80' rows='15' name='endtext_".$esrow['surveyls_language']."'>{$esrow['surveyls_endtext']}</textarea>\n"
            . getEditor("survey-endtext","endtext_".$esrow['surveyls_language'], "[".$clang->gT("End message:", "js")."](".$esrow['surveyls_language'].")",'','','',$action)
            . "</li>\n"
            . "<li><label for=''>".$clang->gT("End URL:")."</label>\n"
            . "<input type='text' size='80' name='url_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_url']}\" />\n"
            . "</li>"
            . "<li><label for=''>".$clang->gT("URL description:")."</label>\n"
            . "<input type='text' size='80' name='urldescrip_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_urldescription']}\" />\n"
            . "</li>"
            . "<li><label for=''>".$clang->gT("Date format:")."</label>\n"
            . "<select size='1' name='dateformat_".$esrow['surveyls_language']."'>\n";
            foreach (getDateFormatData() as $index=>$dateformatdata)
            {
                $editsurvey.= "<option value='{$index}'";
                if ($esrow['surveyls_dateformat']==$index) {
                    $editsurvey.=" selected='selected'";
                }
                $editsurvey.= ">".$dateformatdata['dateformat'].'</option>';
            }
            $editsurvey.= "</select></li></ul>"
            . "</div>";
        }
        $editsurvey .= '</div>';
        if(bHasSurveyPermission($surveyid,'surveylocale','update'))
        {
            $editsurvey .= "<p><input type='submit' class='standardbtn' value='".$clang->gT("Save")."' />\n"
            . "<input type='hidden' name='action' value='updatesurveylocalesettings' />\n"
            . "<input type='hidden' name='sid' value=\"{$surveyid}\" />\n"
            . "<input type='hidden' name='language' value=\"{$esrow['surveyls_language']}\" />\n"
            . "</p>\n"
            . "</form>\n";
        }

    }
    else
    {
        include("access_denied.php");
    }
}

if ($action == "translate")  // Translate survey
{
    if(bHasSurveyPermission($surveyid,'translation','read'))
    {
        $translateoutput .="<div class='header ui-widget-header'>".$clang->gT("Quick-translate survey")."</div>\n";
    }
    else
    {
        include("access_denied.php");
    }

}

if ($action == "emailtemplates")
{
    $js_admin_includes[]='scripts/emailtemplates.js';
    if(isset($surveyid) && getEmailFormat($surveyid) == 'html')
    {
        $ishtml=true;
    }
    else
    {
        $ishtml=false;
    }    
    $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    array_unshift($grplangs,$baselang);

    $sHTMLOutput = PrepareEditorScript();
    // Inject necessary strings for Javascript functions
    $sHTMLOutput .= "<script type='text/javascript'>
                          var sReplaceTextConfirmation='".$clang->gT("This will replace the existing text. Continue?","js")."'
                       </script>\n";
    $sHTMLOutput .="<div class='header ui-widget-header'>\n".$clang->gT("Edit email templates")."</div>\n"
    . "<form class='form30newtabs' id='emailtemplates' action='$scriptname' method='post'>\n"
    . "<div id='tabs'><ul>";
    $surveyinfo=getSurveyInfo($surveyid);

    foreach ($grplangs as $grouplang)
    {
        $sHTMLOutput.="<li><a href='#tab-{$grouplang}'>".getLanguageNameFromCode($grouplang,false);
        if ($grouplang==GetBaseLanguageFromSurveyID($surveyid)) {$sHTMLOutput .= ' ('.$clang->gT("Base language").')';}          
        $sHTMLOutput.="</a></li>";
    }
    $sHTMLOutput.="</ul>";
    foreach ($grplangs as $grouplang)
    {
        // this one is created to get the right default texts fo each language
        $bplang = new limesurvey_lang($grouplang);
        $esquery = "SELECT * FROM ".db_table_name("surveys_languagesettings")." WHERE surveyls_survey_id=$surveyid and surveyls_language='$grouplang'";
        $esresult = db_execute_assoc($esquery);
        $esrow = $esresult->FetchRow();
        $aDefaultTexts=aTemplateDefaultTexts($bplang);
        if ($ishtml==true){
            $aDefaultTexts['admin_detailed_notification']=$aDefaultTexts['admin_detailed_notification_css'].conditional_nl2br($aDefaultTexts['admin_detailed_notification'],$ishtml);            
        }
        
        $sHTMLOutput .= "<div id='tab-{$grouplang}'>";
        $sHTMLOutput .= "<div class='tabsinner' id='tabsinner-{$grouplang}'>"
        ."<ul>"
        ."<li><a href='#tab-{$grouplang}-invitation'>".$clang->gT("Invitation")."</a></li>"
        ."<li><a href='#tab-{$grouplang}-reminder'>".$clang->gT("Reminder")."</a></li>"
        ."<li><a href='#tab-{$grouplang}-confirmation'>".$clang->gT("Confirmation")."</a></li>"
        ."<li><a href='#tab-{$grouplang}-registration'>".$clang->gT("Registration")."</a></li>"
        ."<li><a href='#tab-{$grouplang}-admin-confirmation'>".$clang->gT("Basic admin notification")."</a></li>"
        ."<li><a href='#tab-{$grouplang}-admin-responses'>".$clang->gT("Detailed admin notification")."</a></li>"
        ."</ul>"
        
        ."<div id='tab-{$grouplang}-admin-confirmation'>";
        $sHTMLOutput .= "<ul><li><label for='email_admin_confirmation_subj_{$grouplang}'>".$clang->gT("Admin confirmation email subject:")."</label>\n"
        . "<input type='text' size='80' name='email_admin_confirmation_subj_{$grouplang}' id='email_admin_confirmation_subj_{$grouplang}' value=\"{$esrow['email_admin_confirmation_subj']}\" />\n"
        . "<input type='hidden' name='email_admin_confirmation_subj_default_{$grouplang}' id='email_admin_confirmation_subj_default_{$grouplang}' value='".$aDefaultTexts['admin_notification_subject']."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_admin_confirmation_subj_{$grouplang}\",\"email_admin_confirmation_subj_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .= "<li><label for='email_admin_confirmation_{$grouplang}'>".$clang->gT("Admin confirmation email body:")."</label>\n"
        . "<textarea cols='80' rows='20' name='email_admin_confirmation_{$grouplang}' id='email_admin_confirmation_{$grouplang}'>".htmlspecialchars($esrow['email_admin_confirmation'])."</textarea>\n"
        . getEditor("email-admin-conf","email_admin_confirmation_{$grouplang}", "[".$clang->gT("Invitation email:", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_admin_confirmation_default_{$grouplang}' id='email_admin_confirmation_default_{$grouplang}' value='".htmlspecialchars(conditional_nl2br($aDefaultTexts['admin_notification'],$ishtml),ENT_QUOTES)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_admin_confirmation_{$grouplang}\",\"email_admin_confirmation_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .="</ul></div>"
        
        ."<div id='tab-{$grouplang}-admin-responses'>";
        $sHTMLOutput .= "<ul><li><label for='email_admin_responses_subj_{$grouplang}'>".$clang->gT("Invitation email subject:")."</label>\n"
        . "<input type='text' size='80' name='email_admin_responses_subj_{$grouplang}' id='email_admin_responses_subj_{$grouplang}' value=\"{$esrow['email_admin_responses_subj']}\" />\n"
        . "<input type='hidden' name='email_admin_responses_subj_default_{$grouplang}' id='email_admin_responses_subj_default_{$grouplang}' value='{$aDefaultTexts['admin_detailed_notification_subject']}' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_admin_responses_subj_{$grouplang}\",\"email_admin_responses_subj_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .= "<li><label for='email_admin_responses_{$grouplang}'>".$clang->gT("Invitation email:")."</label>\n"
        . "<textarea cols='80' rows='20' name='email_admin_responses_{$grouplang}' id='email_admin_responses_{$grouplang}'>".htmlspecialchars($esrow['email_admin_responses'])."</textarea>\n"
        . getEditor("email-admin-resp","email_admin_responses_{$grouplang}", "[".$clang->gT("Invitation email:", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_admin_responses_default_{$grouplang}' id='email_admin_responses_default_{$grouplang}' value='".htmlspecialchars($aDefaultTexts['admin_detailed_notification'],ENT_QUOTES)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_admin_responses_{$grouplang}\",\"email_admin_responses_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .="</ul></div>"  
                      
        ."<div id='tab-{$grouplang}-invitation'>";
        $sHTMLOutput .= "<ul><li><label for='email_invite_subj_{$grouplang}'>".$clang->gT("Invitation email subject:")."</label>\n"
        . "<input type='text' size='80' name='email_invite_subj_{$grouplang}' id='email_invite_subj_{$grouplang}' value=\"{$esrow['surveyls_email_invite_subj']}\" />\n"
        . "<input type='hidden' name='email_invite_subj_default_{$grouplang}' id='email_invite_subj_default_{$grouplang}' value='{$aDefaultTexts['invitation_subject']}' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_invite_subj_{$grouplang}\",\"email_invite_subj_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .= "<li><label for='email_invite_{$grouplang}'>".$clang->gT("Invitation email:")."</label>\n"
        . "<textarea cols='80' rows='20' name='email_invite_".$esrow['surveyls_language']."' id='email_invite_{$grouplang}'>".htmlspecialchars($esrow['surveyls_email_invite'])."</textarea>\n"
        . getEditor("email-inv","email_invite_{$grouplang}", "[".$clang->gT("Invitation email:", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_invite_default_".$esrow['surveyls_language']."' id='email_invite_default_{$grouplang}' value='".htmlspecialchars(conditional_nl2br($aDefaultTexts['invitation'],$ishtml),ENT_QUOTES)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_invite_{$grouplang}\",\"email_invite_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .="</ul></div>"
        
        ."<div id='tab-{$grouplang}-reminder'>";
        $sHTMLOutput .= "<ul><li><label for='email_remind_subj_{$grouplang}'>".$clang->gT("Reminder email subject:")."</label>\n"
        . "<input type='text' size='80' name='email_remind_subj_".$esrow['surveyls_language']."' id='email_remind_subj_{$grouplang}' value=\"{$esrow['surveyls_email_remind_subj']}\" />\n"
        . "<input type='hidden' name='email_remind_subj_default_".$esrow['surveyls_language']."' id='email_remind_subj_default_{$grouplang}' value='{$aDefaultTexts['reminder_subject']}' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_remind_subj_{$grouplang}\",\"email_remind_subj_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .= "<li><label for='email_remind_{$grouplang}'>".$clang->gT("Email reminder:")."</label>\n"
        . "<textarea cols='80' rows='20' name='email_remind_".$esrow['surveyls_language']."' id='email_remind_{$grouplang}'>".htmlspecialchars($esrow['surveyls_email_remind'])."</textarea>\n"
        . getEditor("email-rem","email_remind_{$grouplang}", "[".$clang->gT("Email reminder:", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_remind_default_".$esrow['surveyls_language']."' id='email_remind_default_{$grouplang}' value='".htmlspecialchars(conditional_nl2br($aDefaultTexts['reminder'],$ishtml),ENT_QUOTES)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_remind_{$grouplang}\",\"email_remind_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .="</ul></div>"
        
        ."<div id='tab-{$grouplang}-confirmation'>";
        $sHTMLOutput .= "<ul><li><label for='email_confirm_subj_{$grouplang}'>".$clang->gT("Confirmation email subject:")."</label>\n"
        . "<input type='text' size='80' name='email_confirm_subj_".$esrow['surveyls_language']."' id='email_confirm_subj_{$grouplang}' value=\"{$esrow['surveyls_email_confirm_subj']}\" />\n"
        . "<input type='hidden' name='email_confirm_subj_default_".$esrow['surveyls_language']."' id='email_confirm_subj_default_{$grouplang}' value='{$aDefaultTexts['confirmation_subject']}' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_confirm_subj_{$grouplang}\",\"email_confirm_subj_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .= "<li><label for='email_confirm_{$grouplang}'>".$clang->gT("Confirmation email:")."</label>\n"
        . "<textarea cols='80' rows='20' name='email_confirm_".$esrow['surveyls_language']."' id='email_confirm_{$grouplang}'>".htmlspecialchars($esrow['surveyls_email_confirm'])."</textarea>\n"
        . getEditor("email-conf","email_confirm_{$grouplang}", "[".$clang->gT("Confirmation email", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_confirm_default_".$esrow['surveyls_language']."' id='email_confirm_default_{$grouplang}' value='".htmlspecialchars(conditional_nl2br($aDefaultTexts['confirmation'],$ishtml),ENT_QUOTES)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_confirm_{$grouplang}\",\"email_confirm_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .="</ul></div>"
        
        ."<div id='tab-{$grouplang}-registration'>";
        $sHTMLOutput .= "<ul><li><label for='email_register_subj_{$grouplang}'>".$clang->gT("Public registration email subject:")."</label>\n"
        . "<input type='text' size='80' name='email_register_subj_".$esrow['surveyls_language']."' id='email_register_subj_{$grouplang}' value=\"{$esrow['surveyls_email_register_subj']}\" />\n"
        . "<input type='hidden' name='email_register_subj_default_".$esrow['surveyls_language']."' id='email_register_subj_default_{$grouplang}' value='{$aDefaultTexts['registration_subject']}' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript:  fillin(\"email_register_subj_{$grouplang}\",\"email_register_subj_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .= "<li><label for='email_register_{$grouplang}'>".$clang->gT("Public registration email:")."</label>\n"
        . "<textarea cols='80' rows='20' name='email_register_{$grouplang}' id='email_register_{$grouplang}'>".htmlspecialchars($esrow['surveyls_email_register'])."</textarea>\n"
        . getEditor("email-reg","email_register_{$grouplang}", "[".$clang->gT("Public registration email:", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_register_default_".$esrow['surveyls_language']."' id='email_register_default_{$grouplang}' value='".htmlspecialchars(conditional_nl2br($aDefaultTexts['registration'],$ishtml),ENT_QUOTES)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript:  fillin(\"email_register_{$grouplang}\",\"email_register_default_{$grouplang}\")' />\n"
        . "\t</li></ul>";
        $sHTMLOutput .="</div>" // tab
        
        ."</div>" // tabinner
        ."</div>"; // language tab
    }
    $sHTMLOutput .= '</div>';
    $sHTMLOutput .= "\t<p><input type='submit' class='standardbtn' value='".$clang->gT("Save")."' />\n"
    . "\t<input type='hidden' name='action' value='tokens' />\n"
    . "\t<input type='hidden' name='action' value='updateemailtemplates' />\n"
    . "\t<input type='hidden' name='sid' value=\"{$surveyid}\" />\n"
    . "\t<input type='hidden' name='language' value=\"{$esrow['surveyls_language']}\" />\n"
    . "</form>";
}




if($action == "quotas")
        {
    include("quota.php");
        }

function replacenewline ($texttoreplace)
{
    $texttoreplace = str_replace( "\n", '<br />', $texttoreplace);
    //  $texttoreplace = htmlentities( $texttoreplace, ENT_QUOTES, UTF-8);
    $new_str = '';

    for($i = 0; $i < strlen($texttoreplace); $i++) {
        $new_str .= '\x' . dechex(ord(substr($texttoreplace, $i, 1)));
    }

    return $new_str;
}

/**
 * showadminmenu() function returns html text for the administration button bar
 *
 * @global string $homedir
 * @global string $scriptname
 * @global string $surveyid
 * @global string $setfont
 * @global string $imageurl
 * @return string $adminmenu
 */
function showadminmenu()
{
    global $homedir, $scriptname, $surveyid, $setfont, $imageurl, $clang, $debug, $action, $updateavailable, $updatebuild, $updateversion, $updatelastcheck;

    $adminmenu  = "<div class='menubar'>\n";
    if  ($_SESSION['pw_notify'] && $debug<2)  {$adminmenu .="<div class='alert'>".$clang->gT("Warning: You are still using the default password ('password'). Please change your password and re-login again.")."</div>";}
    $adminmenu  .="<div class='menubar-title ui-widget-header'>\n"
    . "<div class='menubar-title-left'>\n"
    . "<strong>".$clang->gT("Administration")."</strong>";
    if(isset($_SESSION['loginID']))
    {
        $adminmenu  .= " --  ".$clang->gT("Logged in as:"). " <strong>"
        . "<a href=\"#\" onclick=\"window.open('{$scriptname}?action=personalsettings', '_top')\" title=\"".$clang->gTview("Edit your personal preferences")."\" >"
        . $_SESSION['user']." <img src='{$imageurl}/profile_edit.png' name='ProfileEdit' alt='".$clang->gT("Edit your personal preferences")."' /></a>"
        . "</strong>\n";
    }
    $adminmenu  .="</div>\n";
    if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 && isset($updatelastcheck) && $updatelastcheck>0 && isset($updateavailable) && $updateavailable==1)
    {
        $adminmenu  .="<div class='menubar-title-right'><a href='{$scriptname}?action=globalsettings'>".sprintf($clang->gT('Update available: %s'),$updateversion."($updatebuild)").'</a></div>';
    }
    $adminmenu .= "</div>\n"
    . "<div class='menubar-main'>\n"
    . "<div class='menubar-left'>\n"
    . "<a href=\"#\" onclick=\"window.open('{$scriptname}', '_top')\" title=\"".$clang->gTview("Default Administration Page")."\">"
    . "<img src='{$imageurl}/home.png' name='HomeButton' alt='".$clang->gT("Default Administration Page")."' /></a>\n";

    $adminmenu .= "<img src='{$imageurl}/blank.gif' alt='' width='11' />\n"
    . "<img src='{$imageurl}/seperator.gif' alt='' />\n";

    // Edit users
    $adminmenu .="<a href=\"#\" onclick=\"window.open('{$scriptname}?action=editusers', '_top')\" title=\"".$clang->gTview("Create/Edit Users")."\" >"
    ."<img src='{$imageurl}/security.png' name='AdminSecurity' alt='".$clang->gT("Create/Edit Users")."' /></a>";

    $adminmenu .="<a href=\"#\" onclick=\"window.open('{$scriptname}?action=editusergroups', '_top')\" title=\"".$clang->gTview("Create/Edit Groups")."\" >"
    ."<img src='{$imageurl}/usergroup.png' alt='".$clang->gT("Create/Edit Groups")."' /></a>\n" ;

    if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        $adminmenu .= "<a href=\"#\" onclick=\"window.open('{$scriptname}?action=globalsettings', '_top')\" title=\"".$clang->gTview("Global settings")."\" >"
        . "<img src='{$imageurl}/global.png' name='GlobalSettings' alt='". $clang->gT("Global settings")."' /></a>"
        . "<img src='{$imageurl}/seperator.gif' alt='' border='0' hspace='0' />\n";
    }
    // Check data integrity
    if($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1)
    {
        $adminmenu .= "<a href=\"#\" onclick=\"window.open('{$scriptname}?action=checkintegrity', '_top')\" title=\"".$clang->gTview("Check Data Integrity")."\">".
                      "<img src='{$imageurl}/checkdb.png' name='CheckDataIntegrity' alt='".$clang->gT("Check Data Integrity")."' /></a>\n";
    }

    // list surveys
    $adminmenu .= "<a href=\"#\" onclick=\"window.open('{$scriptname}?action=listsurveys', '_top')\" title=\"".$clang->gTview("List Surveys")."\" >\n"
    ."<img src='{$imageurl}/surveylist.png' name='ListSurveys' alt='".$clang->gT("List Surveys")."' onclick=\"window.open('$scriptname?action=listsurveys', '_top')\" />"
    ."</a>" ;

    // db backup & label editor
    if($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1)
    {
        $adminmenu  .= "<a href=\"#\" onclick=\"window.open('{$scriptname}?action=dumpdb', '_top')\" title=\"".$clang->gTview("Backup Entire Database")."\">\n"
        ."<img src='{$imageurl}/backup.png' name='ExportDB' alt='". $clang->gT("Backup Entire Database")."' />"
        ."</a>\n"
        ."<img src='{$imageurl}/seperator.gif' alt=''  border='0' hspace='0' />\n";
    }

    if($_SESSION['USER_RIGHT_MANAGE_LABEL'] == 1)
    {
        $adminmenu  .= "<a href=\"#\" onclick=\"window.open('{$scriptname}?action=labels', '_top')\" title=\"".$clang->gTview("Edit label sets")."\">\n"
        ."<img src='{$imageurl}/labels.png'  name='LabelsEditor' alt='". $clang->gT("Edit label sets")."' /></a>\n"
        ."<img src='{$imageurl}/seperator.gif' alt=''  border='0' hspace='0' />\n";
    }

    if($_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] == 1)
    {
        $adminmenu .= "<a href='{$scriptname}?action=templates' title=\"".$clang->gTview("Template Editor")."\" >"
        ."<img src='{$imageurl}/templates.png' name='EditTemplates' title='' alt='". $clang->gT("Template Editor")."' /></a>\n";
    }
    
    // survey select box
    $adminmenu .= "</div><div class='menubar-right'><span class=\"boxcaption\">".$clang->gT("Surveys").":</span>"
    . "<select onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n"
    . getsurveylist()
    . "</select>\n";

    if($_SESSION['USER_RIGHT_CREATE_SURVEY'] == 1)
    {
        $adminmenu .= "<a href=\"#\" onclick=\"window.open('{$scriptname}?action=newsurvey', '_top')\""
        ."title=\"".$clang->gTview("Create, import, or copy a survey")."\" >"
        ."<img src='{$imageurl}/add.png' name='AddSurvey' title='' alt='". $clang->gT("Create, import, or copy a survey")."' /></a>\n";
    }


    if(isset($_SESSION['loginID'])) //ADDED to prevent errors by reading db while not logged in.
    {
        // Logout
        $adminmenu .= "<img src='{$imageurl}/seperator.gif' alt='' border='0' hspace='0' />"
        . "<a href=\"#\" onclick=\"window.open('{$scriptname}?action=logout', '_top')\" title=\"".$clang->gTview("Logout")."\" >"
        . "<img src='{$imageurl}/logout.png' name='Logout' alt='".$clang->gT("Logout")."'/></a>";

        //Show help
        $adminmenu .= "<a href=\"http://docs.limesurvey.org\" target='_blank' title=\"".$clang->gTview("LimeSurvey Online manual")."\" >"
        . "<img src='{$imageurl}/showhelp.png' name='ShowHelp' alt='". $clang->gT("LimeSurvey Online manual")."'/></a>";

        $adminmenu .= "</div>"
        . "</div>\n"
        . "</div>\n";
        //  $adminmenu .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix
        if (!isset($action) && !isset($surveyid) && count(getsurveylist(true))==0)
        {
            $adminmenu.= '<div style="width:500px;margin:0 auto;">'
            .'<h2>'.sprintf($clang->gT("Welcome to %s!"),'LimeSurvey').'</h2>'
            .'<p>'.$clang->gT("Some piece-of-cake steps to create your very own first survey:").'<br/>'
            .'<ol>'
            .'<li>'.sprintf($clang->gT('Create a new survey clicking on the %s icon in the upper right.'),"<img src='$imageurl/add_20.png' name='ShowHelp' title='' alt='". $clang->gT("Add survey")."'/>").'</li>'
            .'<li>'.$clang->gT('Create a new question group inside your survey.').'</li>'
            .'<li>'.$clang->gT('Create one or more questions inside the new question group.').'</li>'
            .'<li>'.sprintf($clang->gT('Done. Test your survey using the %s icon.'),"<img src='$imageurl/do_20.png' name='ShowHelp' title='' alt='". $clang->gT("Test survey")."'/>").'</li>'
            .'</ol></p><br />&nbsp;</div>';
        }

    }
    return $adminmenu;
}
