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
        $query .= " INNER JOIN ".db_table_name('surveys_rights')." AS b ON a.sid = b.sid ";
        $query .= " WHERE b.uid =".$_SESSION['loginID'];
    }

    $query .= " ORDER BY surveyls_title";

    $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked

    if($result->RecordCount() > 0) {
        $listsurveys= "<br /><table class='listsurveys'><thead>
				  <tr>
				    <th>".$clang->gT("Status")."</th>
                    <th>".$clang->gT("Survey ID")."</th>
				    <th style='width:20%;'>".$clang->gT("Survey")."</th>
				    <th>".$clang->gT("Date Created")."</th>
				    <th>".$clang->gT("Owner") ."</th>
				    <th>".$clang->gT("Access")."</th>
				    <th>".$clang->gT("Anonymous answers")."</th>
				    <th>".$clang->gT("Full Responses")."</th>
                    <th>".$clang->gT("Partial Responses")."</th>
                    <th>".$clang->gT("Total Responses")."</th>
				  </tr></thead><tbody>";
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
                $visibility = $clang->gT("Closed-access");
            }
            else
            {
                $visibility = $clang->gT("Open-access");
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

            if ($gbc == "oddrow") {$gbc = "evenrow";}
            else {$gbc = "oddrow";}
            $listsurveys.="<tr class='$gbc'>";

            if ($rows['active']=="Y")
            {
                if ($rows['expires']!='' && $rows['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust))
                {
                    $listsurveys .= "<td><img src='$imagefiles/expired.png' "
                    . "alt='".$clang->gT("This survey is active but expired.")."' /></td>";
                }
                else
                {
                    if (hasRight($rows['sid'],'activate_survey'))
                    {
                        $listsurveys .= "<td><a href=\"#\" onclick=\"window.open('$scriptname?action=deactivate&amp;sid={$rows['sid']}', '_top')\""
                        . " title=\"".$clang->gTview("This survey is active - click here to deactivate this survey.")."\" >"
                        . "<img src='$imagefiles/active.png' alt='".$clang->gT("This survey is active - click here to deactivate this survey.")."' /></a></td>\n";
                    } else
                    {
                        $listsurveys .= "<td><img src='$imagefiles/active.png' "
                        . "alt='".$clang->gT("This survey is currently active.")."' /></td>\n";
                    }
                }
            } else {
                if ( $questionsCount > 0 && hasRight($rows['sid'],'activate_survey') )
                {
                    $listsurveys .= "<td><a href=\"#\" onclick=\"window.open('$scriptname?action=activate&amp;sid={$rows['sid']}', '_top')\""
                    . " title=\"".$clang->gTview("This survey is currently not active - click here to activate this survey.")."\" >"
                    . "<img src='$imagefiles/inactive.png' title='' alt='".$clang->gT("This survey is currently not active - click here to activate this survey.")."' /></a></td>\n" ;
                } else
                {
                    $listsurveys .= "<td><img src='$imagefiles/inactive.png'"
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
            $listsurveys .= "</tr>" ;
        }

        $listsurveys.="</tbody><tfoot><tr>
		<td colspan=\"11\">&nbsp;</td>".
		"</tr></tfoot>";
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
    . "<form action='$scriptname' id='personalsettings' method='post'>"
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
    . "<option value='default' $edmod1>".$clang->gT("Default")."</option>\n"
    . "<option value='inline' $edmod3>".$clang->gT("Inline HTML editor")."</option>\n"
    . "<option value='popup' $edmod4>".$clang->gT("Popup HTML editor")."</option>\n"
    . "<option value='none' $edmod2>".$clang->gT("No HTML editor")."</option>\n";
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
    if(hasRight($surveyid))
    {
        $js_admin_includes[]='../scripts/jquery/jquery.cookie.js';
        $js_admin_includes[]='scripts/surveytoolbar.js';
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $sumquery3 = "SELECT * FROM ".db_table_name('questions')." WHERE sid=$surveyid AND parent_qid=0 AND language='".$baselang."'"; //Getting a count of questions for this survey
        $sumresult3 = $connect->Execute($sumquery3); //Checked
        $sumcount3 = $sumresult3->RecordCount();
        $sumquery6 = "SELECT * FROM ".db_table_name('conditions')." as c, ".db_table_name('questions')."as q WHERE c.qid = q.qid AND q.sid=$surveyid"; //Getting a count of conditions for this survey
        $sumresult6 = $connect->Execute($sumquery6) or die("Can't coun't conditions"); //Checked
        $sumcount6 = $sumresult6->RecordCount();
        $sumquery2 = "SELECT * FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='".$baselang."'"; //Getting a count of groups for this survey
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
        //BUTTON BAR
        $surveysummary .= ""  //"<tr><td colspan=2>\n"
        . "<div class='menubar'>\n"
        . "<div class='menubar-title'>\n"
        . "<strong>".$clang->gT("Survey")."</strong> "
        . "<span class='basic'>{$surveyinfo['surveyls_title']} (".$clang->gT("ID").":$surveyid)</span></div>\n"
        . "<div class='menubar-main'>\n"
        . "<div class='menubar-left' id='basicsurveybar'>\n";
        if ($activated == "N" )
        {
            $surveysummary .= "<img src='$imagefiles/inactive.png' "
            . "alt='".$clang->gT("This survey is not currently active")."' />\n";
            if($sumcount3>0 && hasRight($surveyid,'activate_survey'))
            {
                $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=activate&amp;sid=$surveyid', '_top')\""
                . " title=\"".$clang->gTview("Activate this Survey")."\" >"
                . "<img src='$imagefiles/activate.png' name='ActivateSurvey' alt='".$clang->gT("Activate this Survey")."'/></a>\n" ;
            }
            else
            {
                $surveysummary .= "<img src='$imagefiles/activate_disabled.png' alt='"
                . $clang->gT("Survey cannot be activated. Either you have no permission or there are no questions.")."' />\n" ;
            }
        }
        elseif ($activated == "Y")
        {
            if ($surveyinfo['expires']!='' && ($surveyinfo['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust)))
            {
                $surveysummary .= "<img src='$imagefiles/expired.png' "
                . "alt='".$clang->gT("This survey is active but expired.")."' />\n";
            }
            elseif (($surveyinfo['startdate']!='') && ($surveyinfo['startdate'] > date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust)))
            {
                $surveysummary .= "<img src='$imagefiles/notyetstarted.png' "
                . "alt='".$clang->gT("This survey is active but has a start date.")."' />\n";
            }
            else
            {
                $surveysummary .= "<img src='$imagefiles/active.png' title='' "
                . "alt='".$clang->gT("This survey is currently active")."' />\n";
            }
            if(hasRight($surveyid,'activate_survey'))
            {
                $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=deactivate&amp;sid=$surveyid', '_top')\""
                . " title=\"".$clang->gTview("Deactivate this Survey")."\" >"
                . "<img src='$imagefiles/deactivate.png' alt='".$clang->gT("Deactivate this Survey")."' /></a>\n" ;
            }
            else
            {
                $surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='14' />\n";
            }
        }

        $surveysummary .= "<img src='$imagefiles/seperator.gif' alt=''  />\n";
        // survey rights

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
            $surveysummary .= "<a href=\"#\" accesskey='d' onclick=\"window.open('"
            . $publicurl."/index.php?sid=$surveyid&amp;newtest=Y&amp;lang=$baselang', '_blank')\" title=\"".$icontext2."\" >"
            . "<img src='$imagefiles/do.png' name='DoSurvey' alt='$icontext' />"
            . "</a>\n";

        } else {
            $surveysummary .= "<a href=\"#\" onclick=\"$('#printpopup').css('visibility','hidden'); $('#langpopup2').css('visibility','visible');\" "
            . "title=\"".$icontext2."\" accesskey='d'>"
            . "<img  src='$imagefiles/do.png' name='DoSurvey' alt='$icontext' />"
            . "</a>\n";

            $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $tmp_survlangs[] = $baselang;
            rsort($tmp_survlangs);
            // Test Survey Language Selection Popup
            $surveysummary .="<div class=\"langpopup2\" id=\"langpopup2\">".$clang->gT("Please select a language:")."<ul>";
            foreach ($tmp_survlangs as $tmp_lang)
            {
                $surveysummary .= "<li><a href=\"#\" accesskey='d' onclick=\"document.getElementById('langpopup2').style.visibility='hidden'; window.open('".$publicurl."/index.php?sid=$surveyid&amp;newtest=Y&amp;lang=".$tmp_lang."', '_blank')\"><font color=\"#097300\"><b>".getLanguageNameFromCode($tmp_lang,false)."</b></font></a></li>";
            }
            $surveysummary .= "<li class='cancellink'><a href=\"#\" accesskey='d' onclick=\"document.getElementById('langpopup2').style.visibility='hidden';\"><span style='color:#DF3030'>".$clang->gT("Cancel")."</span></a></li>"
            ."</ul></div>";
        }
        $surveysummary .= "<img src='$imagefiles/seperator.gif' alt=''  />\n";

        if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $surveyinfo['owner_id'] == $_SESSION['loginID'])
        {
            $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=surveysecurity&amp;sid=$surveyid', '_top')\""
            . " title='".$clang->gTview("Survey Security Settings")."'>"
            . "<img src='$imagefiles/survey_security.png' name='SurveySecurity' alt='".$clang->gT("Survey Security Settings")."' />"
            . "</a>\n";
        }
        else
        {
            $surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }

        if($activated == "Y" && hasRight($surveyid,'browse_response'))
        {
            $surveysummary .= "<a href=\"#\" onclick=\"window.open('".$homeurl."/".$scriptname."?action=dataentry&amp;sid=$surveyid', '_top')\""
            . " title=\"".$clang->gTview("Dataentry Screen for Survey")."\" >"
            . "<img src='$imagefiles/dataentry.png' alt='".$clang->gT("Dataentry Screen for Survey")."' name='DoDataentry' />"
            . "</a>\n";
        }
        else if (!hasRight($surveyid,'browse_response'))
        {
            $surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        } else {
            $surveysummary .= "<a href=\"#\" onclick=\"alert('".$clang->gT("This survey is not active, data entry is not allowed","js")."')\""
            . " title=\"".$clang->gTview("Dataentry Screen for Survey")."\">"
            . "<img src='$imagefiles/dataentry_disabled.png'  alt='".$clang->gT("Dataentry Screen for Survey")."' name='DoDataentry' />"
            . "</a>\n";
        }

        if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
        {

            $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=showprintablesurvey&amp;sid=$surveyid', '_blank')\""
            . " title=\"".$clang->gTview("Printable Version of Survey")."\" >"
            . "<img src='$imagefiles/print.png' name='ShowPrintableSurvey' alt='".$clang->gT("Printable Version of Survey")."' />"
            . "</a><img src='$imagefiles/seperator.gif' alt='' />\n";

        } else {

            $surveysummary .= "<a href=\"#\" onclick=\"document.getElementById('printpopup').style.visibility='visible'; "
            . "document.getElementById('langpopup2').style.visibility='hidden';\""
            . " title=\"".$clang->gTview("Printable Version of Survey")."\" >"
            . "<img src='$imagefiles/print.png' name='ShowPrintableSurvey' alt='".$clang->gT("Printable Version of Survey")."' />\n"
            . "</a><img src='$imagefiles/seperator.gif' alt='' />\n";

            $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            $tmp_survlangs[] = $baselang;
            rsort($tmp_survlangs);

            // Test Survey Language Selection Popup
            $surveysummary .="<div class=\"langpopup2\" id=\"printpopup\"><table width=\"100%\"><tr><td>".$clang->gT("Please select a language:")."</td></tr>";
            foreach ($tmp_survlangs as $tmp_lang)
            {
                $surveysummary .= "<tr><td><a href=\"#\" accesskey='d' onclick=\"document.getElementById('printpopup').style.visibility='hidden'; window.open('$scriptname?action=showprintablesurvey&amp;sid=$surveyid&amp;lang=".$tmp_lang."', '_blank')\"><font color=\"#097300\"><b>".getLanguageNameFromCode($tmp_lang,false)."</b></font></a></td></tr>";
            }
            $surveysummary .= "<tr><td align=\"center\"><a href=\"#\" accesskey='d' onclick=\"document.getElementById('printpopup').style.visibility='hidden';\"><font color=\"#DF3030\">".$clang->gT("Cancel")."</font></a></td></tr></table></div>";

            $surveysummary .= "<script type='text/javascript'>document.getElementById('printpopup').style.left='152px';</script>\n";


        }

        if(hasRight($surveyid,'edit_survey_property'))
        {
            $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=editsurvey&amp;sid=$surveyid', '_top')\""
            . " title=\"".$clang->gTview("Edit survey settings")."\" >"
            . "<img src='$imagefiles/edit.png' name='EditSurveySettings' alt='".$clang->gT("Edit survey settings")."' /></a>\n";
        }
        else
        {
            $surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }


        if (hasRight($surveyid,'delete_survey'))
        {
            //			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=deletesurvey&amp;sid=$surveyid', '_top')\""
            $surveysummary .= "<a href=\"#\" onclick=\"".get2post("$scriptname?action=deletesurvey&amp;sid=$surveyid")."\""
            . " title=\"".$clang->gTview("Delete Current Survey")."\" >"
            . "<img src='$imagefiles/delete.png' name='DeleteWholeSurvey' alt='".$clang->gT("Delete Current Survey")."' /></a>\n" ;
        }
        else
        {
            $surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40'  />\n";
        }

        if (hasRight($surveyid,'export'))
        {
            $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=exportstructure&amp;sid=$surveyid', '_top')\""
            . " title=\"".$clang->gTview("Export Survey Structure")."\">"
            . "<img src='$imagefiles/export.png' alt='". $clang->gT("Export Survey Structure")."' name='ExportSurvey' />"
            . "</a>\n" ;
        }
        else
        {
            $surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />";
        }

        if ($activated == "Y" && hasRight($surveyid,'browse_response'))
        {
            $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=browse&amp;sid=$surveyid', '_top')\""
            . " title=\"".$clang->gTview("Browse Responses For This Survey")."\" >"
            . "<img src='$imagefiles/browse.png' name='BrowseSurveyResults' alt='".$clang->gT("Browse Responses For This Survey")."' /></a>\n";
            if ($surveyinfo['allowsave'] == "Y")
            {
                $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=saved&amp;sid=$surveyid', '_top')\""
                . " title=\"".$clang->gTview("View Saved but not submitted Responses")."\" >"
                . "<img src='$imagefiles/saved.png' name='BrowseSaved' alt='".$clang->gT("View Saved but not submitted Responses")."' /></a>\n";
            }
        }
        if (hasRight($surveyid,'export') || hasRight($surveyid,'activate_survey'))
        {
            $surveysummary .= "<img src='$imagefiles/seperator.gif' alt=''  />\n";
            $surveysummary .="<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\""
            . " title=\"".$clang->gTview("Token management")."\" >"
            . "<img src='$imagefiles/tokens.png' name='TokensControl' alt='".$clang->gT("Token management")."' /></a>\n" ;
        }
        if($activated!="Y" && hasRight($surveyid,'define_questions') && getGroupSum($surveyid,$surveyinfo['language'])>1)
        {
            $surveysummary .= "<img src='$imagefiles/seperator.gif' alt=''  />\n";
            $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=ordergroups&amp;sid=$surveyid', '_top')\""
            . " title=\"".$clang->gTview("Change question group order")."\" >"
            . "<img src='$imagefiles/reorder.png' alt='".$clang->gT("Change question group order")."' name='ordergroups' />"
            . "</a>\n";
        }

         
        // Second toolsbar
        $surveysummary .= "</div>\n"
        . "<div class='menubar-left'>\n"
        . "<img id='surveyhandleright' class='btnsurveybar' alt=\"".$clang->gT("Standard survey options")."\"src='$imagefiles/handle-right.png' />\n"
        . "<img id='surveyhandleleft' class='btnsurveybar' alt=\"".$clang->gT("Advanced survey options")."\"src='$imagefiles/handle-left.png' />\n"
        ."</div>"
        . "<div class='menubar-left' id='advancedsurveybar' style='display:none'>\n";
        if ($activated == "N" )
        {
            $surveysummary .= "<img src='$imagefiles/inactive.png' "
            . "alt='".$clang->gT("This survey is not currently active")."' />\n";
            if($sumcount3>0 && hasRight($surveyid,'activate_survey'))
            {
                $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=activate&amp;sid=$surveyid', '_top')\""
                . "title=\"".$clang->gTview("Activate this Survey")."\" >"
                . "<img src='$imagefiles/activate.png' name='ActivateSurvey' alt='".$clang->gT("Activate this Survey")."'/></a>\n" ;
            }
            else
            {
                $surveysummary .= "<img src='$imagefiles/activate_disabled.png' alt='"
                . $clang->gT("Survey cannot be activated. Either you have no permission or there are no questions.")."' />\n" ;
            }
        }
        elseif ($activated == "Y")
        {
            if ($surveyinfo['expires']!='' && ($surveyinfo['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust)))
            {
                $surveysummary .= "<img src='$imagefiles/expired.png' "
                . "alt='".$clang->gT("This survey is active but expired.")."' />\n";
            }
            elseif (($surveyinfo['startdate']!='') && ($surveyinfo['startdate'] > date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust)))
            {
                $surveysummary .= "<img src='$imagefiles/notyetstarted.png' "
                . "alt='".$clang->gT("This survey is active but has a start date.")."' />\n";
            }
            else
            {
                $surveysummary .= "<img src='$imagefiles/active.png' title='' "
                . "alt='".$clang->gT("This survey is currently active")."' />\n";
            }
            if(hasRight($surveyid,'activate_survey'))
            {
                $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=deactivate&amp;sid=$surveyid', '_top')\""
                . "title=\"".$clang->gTview("Deactivate this Survey")."\" >"
                . "<img src='$imagefiles/deactivate.png' alt='".$clang->gT("Deactivate this Survey")."' /></a>\n" ;
            }
            else
            {
                $surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='14' />\n";
            }
        }

        $surveysummary .= "<img src='$imagefiles/seperator.gif' alt=''  />\n";
        // survey rights

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
            $surveysummary .= "<a href=\"#\" accesskey='d' onclick=\"window.open('"
            . $publicurl."/index.php?sid=$surveyid&amp;newtest=Y&amp;lang=$baselang', '_blank')\" title=\"".$icontext2."\" >"
            . "<img src='$imagefiles/do.png' name='DoSurvey2' alt='$icontext' />"
            . "</a>\n";

        } else {
            $surveysummary .= "<a href=\"#\" onclick=\"$('#printpopup').css('visibility','hidden'); $('#langpopup2').css('visibility','visible');\" "
            . "title=\"".$icontext2."\" accesskey='d'>"
            . "<img  src='$imagefiles/do.png' name='DoSurvey2' alt='$icontext' />"
            . "</a>\n";

            $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $tmp_survlangs[] = $baselang;
            rsort($tmp_survlangs);
            // Test Survey Language Selection Popup
            $surveysummary .="<div class=\"langpopup2\" id=\"langpopup2\">".$clang->gT("Please select a language:")."<ul>";
            foreach ($tmp_survlangs as $tmp_lang)
            {
                $surveysummary .= "<li><a href=\"#\" accesskey='d' onclick=\"document.getElementById('langpopup2').style.visibility='hidden'; window.open('".$publicurl."/index.php?sid=$surveyid&amp;newtest=Y&amp;lang=".$tmp_lang."', '_blank')\"><font color=\"#097300\"><b>".getLanguageNameFromCode($tmp_lang,false)."</b></font></a></li>";
            }
            $surveysummary .= "<li class='cancellink'><a href=\"#\" accesskey='d' onclick=\"document.getElementById('langpopup2').style.visibility='hidden';\"><span style='color:#DF3030'>".$clang->gT("Cancel")."</span></a></li>"
            ."</ul></div>";
        }
        $surveysummary .= "<img src='$imagefiles/seperator.gif' alt=''  />\n";

        if (hasRight($surveyid,'edit_survey_property'))
        {
            $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=assessments&amp;sid=$surveyid', '_top')\" "
            . "title=\"".$clang->gTview("Set Assessment Rules")."\" >"
            . "<img src='$imagefiles/assessments.png' alt='". $clang->gT("Set Assessment Rules")."' name='SurveyAssessment' /></a>\n";
        }
        else
        {
            $surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40'  />\n";
        }

        if (hasRight($surveyid,'edit_survey_property'))
        {
            $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=quotas&amp;sid=$surveyid', '_top')\" "
            . "title=\"".$clang->gTview("Set Survey Quotas")."\" >"
            . "<img src='$imagefiles/quota.png' alt='". $clang->gT("Set Survey Quotas")."' name='SurveyQuotas' /></a>\n" ;
        }
        else
        {
            $surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40'  />\n";
        }

        if (hasRight($surveyid,'define_questions'))
        {
            if ($sumcount6 > 0) {
                $surveysummary .= "<a href=\"#\" onclick=\"".get2post("$scriptname?action=resetsurveylogic&amp;sid=$surveyid")."\" "
                . "title=\"".$clang->gTview("Reset Survey Logic")."\" >"
                . "<img src='$imagefiles/resetsurveylogic.png' name='ResetSurveyLogic' alt='".$clang->gT("Reset Survey Logic")."' /></a>\n";
            }
            else
            {
                $surveysummary .= "<a href=\"#\" onclick=\"alert('".$clang->gT("Currently there are no conditions configured for this survey.", "js")."');\" "
                . "title=\"".$clang->gTview("Reset Survey Logic")."\" >"
                . "<img src='$imagefiles/resetsurveylogic_disabled.png' name='ResetSurveyLogic' alt='".$clang->gT("Reset Survey Logic")."' />"
                . "</a>\n";
            }
        }
        else
        {
            $surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }
        if (hasRight($surveyid,'define_questions'))
        {
            $surveysummary .= "<a href=\"#\" onclick=\"".get2post("$scriptname?action=quicktranslate&amp;sid=$surveyid")."\" "
            . "title=\"".$clang->gTview("Quick-translation")."\" >"
            . "<img src='$imagefiles/translate.png' name='QuickTranslation' alt='".$clang->gT("Quick-translation")."' /></a>\n";
        }
        else
        {
            $surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }

        $surveysummary .="</div>"
        
        // End of survey toolbat 2nd page


        . "<div class='menubar-right'>\n";
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
        if ($activated == "Y")
        {
            $surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }
        elseif(hasRight($surveyid,'define_questions'))
        {
            $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=addgroup&amp;sid=$surveyid', '_top')\""
            . " title=\"".$clang->gTview("Add new group to survey")."\">"
            . "<img src='$imagefiles/add.png' alt='".$clang->gT("Add new group to survey")."' name='AddNewGroup' /></a>\n";
        }
        $surveysummary .= "<img src='$imagefiles/seperator.gif' alt='' />\n"
        . "<img src='$imagefiles/blank.gif' width='15' alt='' />"
        . "<input type='image' src='$imagefiles/minus.gif' title='". $clang->gT("Hide details of this Survey")."' "
        . "alt='". $clang->gT("Hide details of this Survey")."' name='MinimiseSurveyWindow' "
        . "onclick='document.getElementById(\"surveydetails\").style.display=\"none\";' />\n";
        $surveysummary .= "<input type='image' src='$imagefiles/plus.gif' title='". $clang->gT("Show details of this survey")."' "
        . "alt='". $clang->gT("Show details of this survey")."' name='MaximiseSurveyWindow' "
        . "onclick='document.getElementById(\"surveydetails\").style.display=\"\";' />\n";
        if (!$gid)
        {
            $surveysummary .= "<input type='image' src='$imagefiles/close.gif' title='". $clang->gT("Close this survey")."' "
            . "alt='".$clang->gT("Close this survey")."' name='CloseSurveyWindow' "
            . "onclick=\"window.open('$scriptname', '_top')\" />\n";
        }
        else
        {
            $surveysummary .= "<img src='$imagefiles/blank.gif' width='18' alt='' />\n";
        }



        $surveysummary .= "</div>\n"
        . "</div>\n"
        . "</div>\n";

        //SURVEY SUMMARY
        if ($gid || $qid || $action=="deactivate"|| $action=="activate" || $action=="surveysecurity"
        || $action=="surveyrights" || $action=="addsurveysecurity" || $action=="addusergroupsurveysecurity"
        || $action=="setsurveysecurity" ||  $action=="setusergroupsurveysecurity" || $action=="delsurveysecurity"
        || $action=="editsurvey" || $action=="addgroup" || $action=="importgroup"
        || $action=="ordergroups" || $action=="updatesurvey" || $action=="deletesurvey" || $action=="resetsurveylogic"
        || $action=="importsurveyresources"
        || $action=="exportstructure" || $action=="quotas" ) {$showstyle="style='display: none'";}
        if (!isset($showstyle)) {$showstyle="";}
        $additionnalLanguagesArray = GetAdditionalLanguagesFromSurveyID($surveyid);
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
        if ($surveyinfo['allowsave'] == "Y") {$surveysummary2 .= $clang->gT("Participants can save partially finished surveys")."<br />\n";}
        switch ($surveyinfo['notification'])
        {
            case 0:
                $surveysummary2 .= $clang->gT("No email notification")."<br />\n";
                break;
            case 1:
                $surveysummary2 .= $clang->gT("Basic email notification")."<br />\n";
                break;
            case 2:
                $surveysummary2 .= $clang->gT("Detailed email notification with result codes")."<br />\n";
                break;
        }

        if(hasRight($surveyid,'edit_survey_property'))
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
            foreach ($additionnalLanguagesArray as $langname)
            {
                $surveysummary .= "&nbsp;<a href='$tmp_url/lang-$langname' target='_blank'><img title='".$clang->gT("Survey URL For Language:")." ".getLanguageNameFromCode($langname,false)."' alt='".getLanguageNameFromCode($langname,false)." ".$clang->gT("Flag")."' src='../images/flags/$langname.png' /></a>";
            }
        } else {
            $tmp_url = $GLOBALS['publicurl'] . '/index.php?sid=' . $surveyinfo['sid'];
            $surveysummary .= "<td align='left'> <a href='$tmp_url&amp;lang=".$surveyinfo['language']."' target='_blank'>$tmp_url&amp;lang=".$surveyinfo['language']."</a>";
            foreach ($additionnalLanguagesArray as $langname)
            {
                $surveysummary .= "&nbsp;<a href='$tmp_url&amp;lang=$langname' target='_blank'><img title='".$clang->gT("Survey URL For Language:")." ".getLanguageNameFromCode($langname,false)."' alt='".getLanguageNameFromCode($langname,false)." ".$clang->gT("Flag")."' src='../images/flags/$langname.png' /></a>";
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
        $additionnalLanguagesCount = count($additionnalLanguagesArray);
        if ($additionnalLanguagesCount == 0) $additionnalLanguagesCount = 1;
        $surveysummary .= "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Additional Languages").":</strong></td>\n";

        $first=true;
        foreach ($additionnalLanguagesArray as $langname)
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
            if ($sumcount2 == 0 && hasRight($surveyid,'define_questions'))
            {
                $surveysummary .= "<span class='statusentryhighlight'>[".$clang->gT("You need to add question groups")."]</span><br />";
            }
            if ($sumcount3 == 0 && hasRight($surveyid,'define_questions'))
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
    . "<div class='menubar-title'>\n";

    while ($grow = $grpresult->FetchRow())
    {
        $grow = array_map('FlattenText', $grow);
        //$grow = array_map('htmlspecialchars', $grow);
        $groupsummary .= '<strong>'.$clang->gT("Question group").'</strong>&nbsp;'
        . "<span class='basic'>{$grow['group_name']} (".$clang->gT("ID").":$gid)</span>\n"
        . "</div>\n"
        . "<div class='menubar-main'>\n"
        . "<div class='menubar-left'>\n"
        . "<img src='$imagefiles/blank.gif' alt='' width='54' height='20'  />\n"
        . "<img src='$imagefiles/seperator.gif' alt=''  />"
        . "<img src='$imagefiles/blank.gif' alt='' width='180' height='20'  />"
        . "<img src='$imagefiles/seperator.gif' alt=''  />\n";

        if(hasRight($surveyid,'define_questions'))
        {
            $groupsummary .=  "<a href=\"#\" onclick=\"window.open('$scriptname?action=editgroup&amp;sid=$surveyid&amp;gid=$gid','_top')\""
            . " title=\"".$clang->gTview("Edit current question group")."\">"
            . "<img src='$imagefiles/edit.png' alt='".$clang->gT("Edit current question group")."' name='EditGroup' /></a>\n" ;
        }
        else
        {
            $groupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }

        if ((($sumcount4 == 0 && $activated != "Y") || $activated != "Y") && hasRight($surveyid,'define_questions'))
        {
            if (is_null($condarray))
            {
                //				$groupsummary .= "<a href='$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid' onclick=\"return confirm('".$clang->gT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js")."')\""
                $groupsummary .= "<a href='#' onclick=\"if (confirm('".$clang->gT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js")."')) {".get2post("$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid")."}\""
                . " title=\"".$clang->gTview("Delete current question group")."\">"
                . "<img src='$imagefiles/delete.png' alt='".$clang->gT("Delete current question group")."' name='DeleteWholeGroup' title=''  /></a>\n";
                //get2post("$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid");
            }
            else
            {
                $groupsummary .= "<a href='$scriptname?sid=$surveyid&amp;gid=$gid' onclick=\"alert('".$clang->gT("Impossible to delete this group because there is at least one question having a condition on its content","js")."')\""
                . " title=\"".$clang->gTview("Delete current question group")."\">"
                . "<img src='$imagefiles/delete_disabled.png' alt='".$clang->gT("Delete current question group")."' name='DeleteWholeGroup' /></a>\n";
            }
        }
        else
        {
            $groupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }


        if(hasRight($surveyid,'export'))
        {

            $groupsummary .="<a href='$scriptname?action=exportstructureGroup&amp;sid=$surveyid&amp;gid=$gid' title=\"".$clang->gTview("Export this question group")."\" >"
            . "<img src='$imagefiles/dumpgroup.png' title='' alt='".$clang->gT("Export this question group")."' name='ExportGroup'  /></a>\n";
        }
        else
        {
            $groupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }
        $groupsummary .= "<img src='$imagefiles/seperator.gif' alt='' />\n";
        if($activated!="Y" && hasRight($surveyid,'define_questions') && getQuestionSum($surveyid, $gid)>1)
        {
            $groupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
            $groupsummary .= "<img src='$imagefiles/seperator.gif' alt='' />\n";
            $groupsummary .= "<a href='$scriptname?action=orderquestions&amp;sid=$surveyid&amp;gid=$gid' title=\"".$clang->gTview("Change Question Order")."\" >"
            . "<img src='$imagefiles/reorder.png' alt='".$clang->gT("Change Question Order")."' name='updatequestionorder' /></a>\n" ;
        }
        else
        {
            $groupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }

        $groupsummary.= "</div>\n"
        . "<div class='menubar-right'>\n"
        . "<span class=\"boxcaption\">".$clang->gT("Questions").":</span><select class=\"listboxquestions\" name='qid' "
        . "onchange=\"window.open(this.options[this.selectedIndex].value, '_top')\">"
        . getQuestions($surveyid,$gid,$qid)
        . "</select>\n";
        if ($activated == "Y")
        {
            $groupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }
        elseif(hasRight($surveyid,'define_questions'))
        {
            $groupsummary .= "<a href='$scriptname?action=addquestion&amp;sid=$surveyid&amp;gid=$gid'"
            ." title=\"".$clang->gTview("Add New Question to Group")."\" >"
            ."<img src='$imagefiles/add.png' title='' alt='".$clang->gT("Add New Question to Group")."' " .
            " name='AddNewQuestion' onclick=\"window.open('', '_top')\" /></a>\n";
        }

        $groupsummary .= "<img src='$imagefiles/seperator.gif' alt=''  />";
        $groupsummary.= "<img src='$imagefiles/blank.gif' width='18' alt='' />"
        . "<input id='MinimizeGroupWindow' type='image' src='$imagefiles/minus.gif' title='"
        . $clang->gT("Hide Details of this Group")."' alt='". $clang->gT("Hide Details of this Group")."' name='MinimizeGroupWindow' />\n";
        $groupsummary .= "<input type='image' id='MaximizeGroupWindow' src='$imagefiles/plus.gif' title='"
        . $clang->gT("Show Details of this Group")."' alt='". $clang->gT("Show Details of this Group")."' name='MaximizeGroupWindow' />\n";
        if (!$qid)
        {
            $groupsummary .= "<input type='image' src='$imagefiles/close.gif' title='"
            . $clang->gT("Close this Group")."' alt='". $clang->gT("Close this Group")."'  name='CloseSurveyWindow' "
            . "onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" />\n";
        }
        else
        {
            $groupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='18' />\n";
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
    $qrq = "SELECT * FROM ".db_table_name('answers')." WHERE qid=$qid AND language='".$baselang."' ORDER BY sortorder, answer";
    $qrr = $connect->Execute($qrq); //Checked
    $qct = $qrr->RecordCount();
    $qrquery = "SELECT * FROM ".db_table_name('questions')." WHERE gid=$gid AND sid=$surveyid AND qid=$qid AND language='".$baselang."'";
    $qrresult = db_execute_assoc($qrquery) or safe_die($qrquery."<br />".$connect->ErrorMsg()); //Checked
    $questionsummary = "<div class='menubar'>\n";

    // Check if other questions in the Survey are dependent upon this question
    $condarray=GetQuestDepsForConditions($surveyid,"all","all",$qid,"by-targqid","outsidegroup");

    while ($qrrow = $qrresult->FetchRow())
    {
        $qrrow = array_map('FlattenText', $qrrow);
        //$qrrow = array_map('htmlspecialchars', $qrrow);
        $questionsummary .= "<div class='menubar-title'>\n"
        . "<strong>". $clang->gT("Question")."</strong> <span class='basic'>{$qrrow['question']} (".$clang->gT("ID").":$qid)</span>\n"
        . "</div>\n"
        . "<div class='menubar-main'>\n"
        . "<div class='menubar-left'>\n"
        . "<img src='$imagefiles/blank.gif' alt='' width='55' height='20' />\n"
        . "<img src='$imagefiles/seperator.gif' alt='' />\n";
        if(hasRight($surveyid,'define_questions'))
        {
            if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
            {
                $questionsummary .= "<a href=\"#\" accesskey='d' onclick=\"window.open('$scriptname?action=previewquestion&amp;sid=$surveyid&amp;qid=$qid', '_blank')\""
                . "title=\"".$clang->gTview("Preview This Question")."\">"
                . "<img src='$imagefiles/preview.png' alt='".$clang->gT("Preview This Question")."' name='previewquestionimg' /></a>\n"
                . "<img src='$imagefiles/seperator.gif' alt='' />\n";
            } else {
                $questionsummary .= "<a href=\"#\" accesskey='d' onclick=\"document.getElementById('printpopup').style.visibility='hidden'; document.getElementById('langpopup2').style.visibility='hidden'; document.getElementById('previewquestion').style.visibility='visible';\""
                . "title=\"".$clang->gTview("Preview This Question")."\">"
                . "<img src='$imagefiles/preview.png' title='' alt='".$clang->gT("Preview This Question")."' name='previewquestionimg' /></a>\n"
                . "<img src='$imagefiles/seperator.gif' alt=''  />\n";

                $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                $baselang = GetBaseLanguageFromSurveyID($surveyid);
                $tmp_survlangs[] = $baselang;
                rsort($tmp_survlangs);

                // Test Survey Language Selection Popup
                $surveysummary .="<div class=\"previewpopup\" id=\"previewquestion\"><table width=\"100%\"><tr><td>".$clang->gT("Please select a language:")."</td></tr>";
                foreach ($tmp_survlangs as $tmp_lang)
                {
                    $surveysummary .= "<tr><td><a href=\"#\" accesskey='d' onclick=\"document.getElementById('previewquestion').style.visibility='hidden'; window.open('$scriptname?action=previewquestion&amp;sid=$surveyid&amp;qid=$qid&amp;lang=".$tmp_lang."', '_blank')\"><font color=\"#097300\"><b>".getLanguageNameFromCode($tmp_lang,false)."</b></font></a></td></tr>";
                }
                $surveysummary .= "<tr><td align=\"center\"><a href=\"#\" accesskey='d' onclick=\"document.getElementById('previewquestion').style.visibility='hidden';\"><font color=\"#DF3030\">".$clang->gT("Cancel")."</font></a></td></tr></table></div>";
            }
        }
        else
        {
            $questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }
        $questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='117' height='20'  />\n"
        . "<img src='$imagefiles/seperator.gif' alt='' />\n";

        if(hasRight($surveyid,'define_questions'))
        {
            $questionsummary .= "<a href='$scriptname?action=editquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
            . " title=\"".$clang->gTview("Edit Current Question")."\">"
            . "<img src='$imagefiles/edit.png' alt='".$clang->gT("Edit Current Question")."' name='EditQuestion' /></a>\n" ;
        }
        else
        {
            $questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }

        if ((($qct == 0 && $activated != "Y") || $activated != "Y") && hasRight($surveyid,'define_questions'))
        {
            if (is_null($condarray))
            {
                $questionsummary .= "<a href='#'" .
				"onclick=\"if (confirm('".$clang->gT("Deleting this question will also delete any answer options and subquestions it includes. Are you sure you want to continue?","js")."')) {".get2post("$scriptname?action=delquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid")."}\">"
				. "<img src='$imagefiles/delete.png' name='DeleteWholeQuestion' alt='".$clang->gT("Delete current question")."' "
				. "border='0' hspace='0' /></a>\n";
            }
            else
            {
                $questionsummary .= "<a href='$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid'" .
				"onclick=\"alert('".$clang->gT("It's impossible to delete this question because there is at least one question having a condition on it.","js")."')\""
				. "title=\"".$clang->gTview("Disabled - Delete current question")."\">"
				. "<img src='$imagefiles/delete_disabled.png' name='DeleteWholeQuestion' alt='".$clang->gT("Disabled - Delete current question")."' /></a>\n";
            }
        }
        else {$questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";}

        if(hasRight($surveyid,'export'))
        {
            $questionsummary .= "<a href='$scriptname?action=exportstructureQuestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
            . " title=\"".$clang->gTview("Export this Question")."\" >"
            . "<img src='$imagefiles/dumpquestion.png' alt='".$clang->gT("Export this Question")."' name='ExportQuestion' /></a>\n";
        }
        else
        {
            $questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }
        $questionsummary .= "<img src='$imagefiles/seperator.gif' alt='' />\n";

        if(hasRight($surveyid,'define_questions'))
        {
            if ($activated != "Y")
            {
                $questionsummary .= "<a href='$scriptname?action=copyquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
                . " title=\"".$clang->gTview("Copy Current Question")."\" >"
                . "<img src='$imagefiles/copy.png'  alt='".$clang->gT("Copy Current Question")."' name='CopyQuestion' /></a>\n"
                . "<img src='$imagefiles/seperator.gif' alt='' />\n";
            }
            else
            {
                $questionsummary .= "<a href='#' title=\"".$clang->gTview("Copy Current Question")."\" "
                . "onclick=\"alert('".$clang->gT("You can't copy a question if the survey is active.","js")."')\">"
                . "<img src='$imagefiles/copy_disabled.png' alt='".$clang->gT("Copy Current Question")."' name='CopyQuestion' /></a>\n"
                . "<img src='$imagefiles/seperator.gif' alt='' />\n";
            }
        }
        else
        {
            $questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }
        if(hasRight($surveyid,'define_questions'))
        {
            $questionsummary .= "<a href='#' onclick=\"window.open('$scriptname?action=conditions&amp;sid=$surveyid&amp;qid=$qid&amp;gid=$gid&amp;subaction=editconditionsform', '_top')\""
            . " title=\"".$clang->gTview("Set Conditions for this Question")."\">"
            . "<img src='$imagefiles/conditions.png' alt='".$clang->gT("Set Conditions for this Question")."'  name='SetQuestionConditions' /></a>\n"
            . "<img src='$imagefiles/seperator.gif' alt='' />\n";
        }
        else
        {
            $questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }

        $qtypes=getqtypelist('','array');
        if(hasRight($surveyid,'define_questions'))
        {
            if ($qtypes[$qrrow['type']]['subquestions'] >0)
            {
                $questionsummary .=  "<a href='".$scriptname."?action=editsubquestions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
                ."title=\"".$clang->gTview("Edit subquestions for this question")."\">"
                ."<img src='$imagefiles/subquestions.png' alt='".$clang->gT("Edit subquestions for this question")."' name='EditSubquestions' /></a>\n" ;
            }
        }
        else
        {
            $questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }
        if(hasRight($surveyid,'define_questions') && $qtypes[$qrrow['type']]['answerscales'] >0)
        {
            $questionsummary .=  "<a href='".$scriptname."?action=editansweroptions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
            ."title=\"".$clang->gTview("Edit answer options for this question")."\">"
            ."<img src='$imagefiles/answers.png' alt='".$clang->gT("Edit answer options for this question")."' name='EdtAnswerOptions' /></a>\n" ;
        }
        else
        {
            $questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }
        if(hasRight($surveyid,'define_questions') && $qtypes[$qrrow['type']]['hasdefaultvalues'] >0)
        {
            $questionsummary .=  "<a href='".$scriptname."?action=editdefaultvalues&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
            ."title=\"".$clang->gTview("Edit default answers for this question")."\">"
            ."<img src='$imagefiles/defaultanswers.png' alt='".$clang->gT("Edit default answers for this question")."' name='EdtAnswerOptions' /></a>\n" ;
        }
        $questionsummary .= "</div>\n"
        . "<div class='menubar-right'>\n"
        . "<input type='image' src='$imagefiles/minus.gif' title='"
        . $clang->gT("Hide Details of this Question")."'  alt='". $clang->gT("Hide Details of this Question")."' name='MinimiseQuestionWindow' "
        . "onclick='document.getElementById(\"questiondetails\").style.display=\"none\";' />\n"
        . "<input type='image' src='$imagefiles/plus.gif' title='"
        . $clang->gT("Show Details of this Question")."'  alt='". $clang->gT("Show Details of this Question")."' name='MaximiseQuestionWindow' "
        . "onclick='document.getElementById(\"questiondetails\").style.display=\"\";' />\n"
        . "<input type='image' src='$imagefiles/close.gif' title='"
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
            . "<input align='top' type='image' src='$imagefiles/answerssmall.png' title='"
            . $clang->gT("Edit/add answer options for this question")."' name='EditThisQuestionAnswers'"
            . "onclick=\"window.open('".$scriptname."?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;action=editansweroptions', '_top')\" /></font></td></tr>\n";
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
        if (isset($qrrow['mandatory']) and ($qrrow['type'] != "X"))
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
        $qid_attributes=getQuestionAttributes($qid);
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
            $qquery = "SELECT count(*) as num_ans  FROM ".db_table_name('answers')." WHERE qid=$qid AND scale_id=$i AND language='".$language."'";
            $qresult = db_execute_assoc($qquery); //Checked
            $qrow = $qresult->FetchRow();
            if ($qrow["num_ans"] == 0)   // means that no record for the language exists in the answers table
            {
                $qquery = "INSERT INTO ".db_table_name('answers')." (qid,code,answer,default_value,sortorder,language,scale_id, assessment_value) (SELECT qid,code,answer,default_value,sortorder, '".$language."','$i', assessment_value FROM ".db_table_name('answers')." WHERE qid=$qid AND scale_id=$i AND language='".$baselang."')";
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
    $vasummary .= "<div class='header'>\n"
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
                $vasummary.="<div class='header' style='margin-top:5px;'>".sprintf($clang->gT("Answer scale %s"),$scale_id+1)."</div>";
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
                    $vasummary .= "<img class='handle' src='$imagefiles/handle.png' /></td><td><input type='text' class='code' id='code_{$position}_{$scale_id}' name='code_{$position}_{$scale_id}' value=\"{$row['code']}\" maxlength='5' size='5'"
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
                $vasummary.="</td><td><img src='$imagefiles/addanswer.png' class='btnaddanswer' />";
                $vasummary.="<img src='$imagefiles/deleteanswer.png' class='btndelanswer' />";

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
    $vasummary .= "<div id='labelsetbrowser' style='display:none;'><div style='float:left;width:260px;'>
                      <label for='labelsets'>".$clang->gT('Available label sets:')."</label>
                      <br /><select id='labelsets' size='10' style='width:250px;'><option>A label set</option></select>
                      <br/><input type='checkbox' checked='checked' id='languagefilter' /><label for='languagefilter'>".$clang->gT('Match language')."</label>
                      <br /><button id='btnlsreplace' type='button'>".$clang->gT('Replace')."</button>
                      <button id='btnlsinsert' type='button'>".$clang->gT('Add')."</button>
                      <button id='btncancel' type='button'>".$clang->gT('Cancel')."</button></div>
                      
                   <div id='labelsetpreview' style='float:right;width:500px;'></div></div> ";
    $vasummary .= "<div id='quickadd' style='display:none;'><div style='float:left;'>
                      <label for='quickadd'>".$clang->gT('Enter your subquestions:')."</label>
                      <br /><textarea id='quickaddarea' class='tipme' title='Enter one subquestion per line. You can provide a code by separating code and subquestion text with a semikolon or tab.' rows='30' style='width:570px;'></textarea>
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



    $_SESSION['FileManagerContext']="edit:answer:$surveyid";
    // Get languages select on survey.
    $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);

    $qquery = "SELECT * FROM ".db_table_name('questions')." WHERE parent_qid=$qid AND language='".$baselang."'";
    $subquestiondata=$connect->GetArray($qquery);
    if (count($subquestiondata)==0)
    {
        $qquery = "INSERT INTO ".db_table_name('questions')." (sid,gid,parent_qid,title,question,question_order,language)
                   VALUES($surveyid,$gid,$qid,'SQ001',".db_quoteall($clang->gT('Some example subquestion')).",1,".db_quoteall($baselang).")";
        $connect->Execute($qquery); //Checked

        $qquery = "SELECT * FROM ".db_table_name('questions')." WHERE parent_qid=$qid AND language='".$baselang."'";
        $subquestiondata=$connect->GetArray($qquery);
    }
    // check that there are subquestions for every language supported by the survey
    foreach ($anslangs as $language)
    {
        foreach ($subquestiondata as $row)
        {
            $qquery = "SELECT count(*) FROM ".db_table_name('questions')." WHERE parent_qid=$qid AND language='".$language."' AND title=".db_quoteall($row['title']);
            $qrow = $connect->GetOne($qquery); //Checked
            if ($qrow == 0)   // means that no record for the language exists in the questions table
            {
                $qquery = "INSERT INTO ".db_table_name('questions')." (sid,gid,parent_qid,title,question,question_order,language)
                           VALUES($surveyid,{$row['gid']},$qid,".db_quoteall($row['title']).",".db_quoteall($row['question']).",{$row['question_order']},".db_quoteall($language).")";
                $connect->Execute($qquery); //Checked
            }
        }
    }

    array_unshift($anslangs,$baselang);      // makes an array with ALL the languages supported by the survey -> $anslangs

    $vasummary = "\n<script type='text/javascript'>
                      var languagecount=".count($anslangs).";\n
                      var newansweroption_text='".$clang->gT('New answer option','js')."'; 
                      var strcode='".$clang->gT('Code','js')."'; 
                      var strlabel='".$clang->gT('Label','js')."'; 
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
    $vasummary .= "<div class='header'>\n"
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
                    $vasummary .="<div class='header'>\n".$clang->gT("Y-Scale")."</div>";
                }
                else
                {
                    $vasummary .="<div class='header'>\n".$clang->gT("X-Scale")."</div>";
                }
            }
            $query = "SELECT * FROM ".db_table_name('questions')." WHERE parent_qid='{$qid}' AND language='{$anslang}' AND scale_id={$scale_id} ORDER BY question_order, title";
            $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked
            $anscount = $result->RecordCount();
            $vasummary .="<table class='answertable' id='answertable_{$anslang}_{$scale_id}' align='center'>\n"
            ."<thead>"
            ."<tr><th>&nbsp;</th>\n"
            ."<th align='right'>".$clang->gT("Code")."</th>\n"
            ."</th><th align='center'>".$clang->gT("Subquestion")."</th>\n";
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
                    $vasummary .= "<img class='handle' src='$imagefiles/handle.png' /></td><td><input type='text' id='code_{$row['qid']}_{$row['scale_id']}' class='code' name='code_{$row['qid']}_{$row['scale_id']}' value=\"{$row['title']}\" maxlength='5' size='5'"
                    ." onkeypress=\" if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_$anslang').click(); return false;} return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_')\""
                    ." />";

                }
                else
                {
                    $vasummary .= "</td><td>{$row['title']}";

                }
                //      <img class='handle' src='$imagefiles/handle.png' /></td><td>
                $vasummary .= "</td><td>\n"
                ."<input type='text' size='100' id='answer_{$row['language']}_{$row['qid']}_{$row['scale_id']}' name='answer_{$row['language']}_{$row['qid']}_{$row['scale_id']}' value=\"{$row['question']}\" onkeypress=\" if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_$anslang').click(); return false;}\" />\n"
                . getEditor("editanswer","answer_".$row['language']."_".$row['qid']."_{$row['scale_id']}", "[".$clang->gT("Subquestion:", "js")."](".$row['language'].")",$surveyid,$gid,$qid,'editanswer')
                ."</td>\n"
                ."<td>\n";

                // Deactivate delete button for active surveys
                if ($activated != 'Y' && $first)
                {
                    $vasummary.="<img src='$imagefiles/addanswer.png' class='btnaddanswer' />";
                    $vasummary.="<img src='$imagefiles/deleteanswer.png' class='btndelanswer' />";
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
    $vasummary .= "<div id='labelsetbrowser' style='display:none;'><div style='float:left; width:260px;'>
                      <label for='labelsets'>".$clang->gT('Available label sets:')."</label>
                      <br /><select id='labelsets' size='10' style='width:250px;'><option>A label set</option></select>
                      <br/><input type='checkbox' checked='checked' id='languagefilter' /><label for='languagefilter'>".$clang->gT('Match language')."</label>
                      <br /><button id='btnlsreplace' type='button'>".$clang->gT('Replace')."</button>
                      <button id='btnlsinsert' type='button'>".$clang->gT('Add')."</button>
                      <button id='btncancel' type='button'>".$clang->gT('Cancel')."</button></div>
                   <div id='labelsetpreview' style='float:right;width:500px;'></div></div> ";    
    $vasummary .= "<div id='quickadd' style='display:none;'><div style='float:left;'>
                      <label for='quickadd'>".$clang->gT('Enter your subquestions:')."</label>
                      <br /><textarea id='quickaddarea' class='tipme' title='Enter one subquestion per line. You can provide a code by separating code and subquestion text with a semikolon or tab.' rows='30' style='width:570px;'></textarea>
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
    $addsummary = "<div class='header'>".$clang->gT("Add User")."</div>\n";
    $addsummary .= "<div class=\"messagebox\">\n";

    $query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID']." AND owner_id != ".$postuserid;
    $result = db_execute_assoc($query); //Checked
    if( ($result->RecordCount() > 0 && in_array($postuserid,getuserlist('onlyuidarray'))) ||
    $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {

        if($postuserid > 0){

            $isrquery = "INSERT INTO {$dbprefix}surveys_rights VALUES($surveyid,". $postuserid.",0,0,0,0,0,0)";
            $isrresult = $connect->Execute($isrquery); //Checked

            if($isrresult)
            {
                $addsummary .= "<div class=\"successheader\">".$clang->gT("User added.")."</div>\n";
                $addsummary .= "<br /><form method='post' action='$scriptname?sid={$surveyid}'>"
                ."<input type='submit' value='".$clang->gT("Set Survey Rights")."' />"
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
    $addsummary = "<div class=\"header\">".$clang->gT("Add User Group")."</div>\n";
    $addsummary .= "<div class=\"messagebox\">\n";

    $query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID'];
    $result = db_execute_assoc($query); //Checked
    if( ($result->RecordCount() > 0 && in_array($postusergroupid,getsurveyusergrouplist('simpleugidarray')) ) ||
    $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        if($postusergroupid > 0){
            $query2 = "SELECT b.uid FROM (SELECT uid FROM ".db_table_name('surveys_rights')." WHERE sid = {$surveyid}) AS c RIGHT JOIN ".db_table_name('user_in_groups')." AS b ON b.uid = c.uid WHERE c.uid IS NULL AND b.ugid = {$postusergroupid}";
            $result2 = db_execute_assoc($query2); //Checked
            if($result2->RecordCount() > 0)
            {
                while ($row2 = $result2->FetchRow())
                {
                    $uid_arr[] = $row2['uid'];
                    $isrquery = "INSERT INTO {$dbprefix}surveys_rights VALUES ($surveyid, {$row2['uid']},0,0,0,0,0,0) ";
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
            $dquery="DELETE FROM {$dbprefix}surveys_rights WHERE uid={$postuserid} AND sid={$surveyid}";	//	added by Dennis
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
        $query2 = "SELECT uid, edit_survey_property, define_questions, browse_response, export, delete_survey, activate_survey FROM ".db_table_name('surveys_rights')." WHERE sid = {$surveyid} AND uid = ".$postuserid;
        $result2 = db_execute_assoc($query2); //Checked

        if($result2->RecordCount() > 0)
        {
            $resul2row = $result2->FetchRow();

            $usersummary = "<form action='$scriptname?sid={$surveyid}' method='post'>\n"
            . "<table width='100%' border='0'>\n<tr><td colspan='6' class='header'>\n"
            . "".$clang->gT("Set Survey Rights")."</td></tr>\n";

            $usersummary .= "<tr><th align='center'>".$clang->gT("Edit Survey Properties")."</th>\n"
            . "<th align='center'>".$clang->gT("Define Questions")."</th>\n"
            . "<th align='center'>".$clang->gT("Browse Responses")."</th>\n"
            . "<th align='center'>".$clang->gT("Export")."</th>\n"
            . "<th align='center'>".$clang->gT("Delete Survey")."</th>\n"
            . "<th align='center'>".$clang->gT("Activate Survey")."</th>\n"
            . "</tr>\n";

            //content
            $usersummary .= "<tr><td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"edit_survey_property\" value=\"edit_survey_property\"";
            if($resul2row['edit_survey_property']) {
                $usersummary .= ' checked="checked" ';
            }
            $usersummary .=" /></td>\n";
            $usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"define_questions\" value=\"define_questions\"";
            if($resul2row['define_questions']) {
                $usersummary .= ' checked="checked" ';
            }
            $usersummary .=" /></td>\n";
            $usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"browse_response\" value=\"browse_response\"";
            if($resul2row['browse_response']) {
                $usersummary .= ' checked="checked" ';
            }
            $usersummary .=" /></td>\n";
            $usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"export\" value=\"export\"";
            if($resul2row['export']) {
                $usersummary .= ' checked="checked" ';
            }
            $usersummary .=" /></td>\n";
            $usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"delete_survey\" value=\"delete_survey\"";
            if($resul2row['delete_survey']) {
                $usersummary .= ' checked="checked" ';
            }
            $usersummary .=" /></td>\n";
            $usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"activate_survey\" value=\"activate_survey\"";
            if($resul2row['activate_survey']) {
                $usersummary .= ' checked="checked" ';
            }
            $usersummary .=" /></td></tr>\n";

            $usersummary .= "\n<tr><td colspan='6' align='center'>"
            ."<input type='submit' value='".$clang->gT("Save Now")."' />"
            ."<input type='hidden' name='action' value='surveyrights' />"
            ."<input type='hidden' name='uid' value='{$postuserid}' /></td></tr>"
            . "</table></form>\n";
        }
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
        $usersummary = "<table width='100%' border='0'>\n<tr><td colspan='6'>\n"
        . "".$clang->gT("Set Survey Rights")."</td></tr>\n";

        $usersummary .= "<th align='center'>".$clang->gT("Edit Survey Property")."</th>\n"
        . "<th align='center'>".$clang->gT("Define Questions")."</th>\n"
        . "<th align='center'>".$clang->gT("Browse Response")."</th>\n"
        . "<th align='center'>".$clang->gT("Export")."</th>\n"
        . "<th align='center'>".$clang->gT("Delete Survey")."</th>\n"
        . "<th align='center'>".$clang->gT("Activate Survey")."</th>\n"
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

        $usersummary .= "\n<tr><td colspan='6' align='center'>"
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
    if(hasRight($surveyid,'export'))
    {
        $exportstructure = "<form id='exportstructure' name='exportstructure' action='$scriptname' method='post'>\n"
        ."<div class='header'>"
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
    if($export4lsrc === true && hasRight($surveyid,'export'))
    {
        $exportstructure = "<form id='exportstructureGroup' name='exportstructureGroup' action='$scriptname' method='post'>\n"
        ."<div class='header'>".$clang->gT("Export group structure")."\n</div>\n"
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
    if($export4lsrc === true && hasRight($surveyid,'export'))
    {
        $exportstructure = "<form id='exportstructureQuestion' name='exportstructureQuestion' action='$scriptname' method='post'>\n"
        ."<div class='header'>".$clang->gT("Export question structure")."\n</div>\n"
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
    if(hasRight($surveyid))
    {
        $js_admin_includes[]='../scripts/jquery/jquery.tablesorter.min.js';
        $js_admin_includes[]='scripts/surveysecurity.js';
        $query2 = "SELECT a.*, b.users_name, b.full_name FROM ".db_table_name('surveys_rights')." AS a INNER JOIN ".db_table_name('users')." AS b ON a.uid = b.uid WHERE a.sid = {$surveyid} AND b.uid != ".$_SESSION['loginID'] ." ORDER BY b.users_name";
        $result2 = db_execute_assoc($query2); //Checked
        $surveysecurity ="<div class='header'>".$clang->gT("Survey Security")."</div>\n";
        $surveysecurity .= "<table class='surveysecurity'><thead>"
        . "<tr>\n"
        . "<th>".$clang->gT("Username")."</th>\n"
        . "<th>".$clang->gT("User Group")."</th>\n"
        . "<th>".$clang->gT("Full name")."</th>\n"
        . "<th align=\"center\"><img src=\"$imagefiles/help.gif\" alt=\"".$clang->gT("Edit Survey Property")."\"></th>\n"
        . "<th align=\"center\"><img src=\"$imagefiles/help.gif\" alt=\"".$clang->gT("Define Questions")."\"></th>\n"
        . "<th align=\"center\"><img src=\"$imagefiles/help.gif\" alt=\"".$clang->gT("Browse Response")."\"></th>\n"
        . "<th align=\"center\"><img src=\"$imagefiles/help.gif\" alt=\"".$clang->gT("Export")."\"></th>\n"
        . "<th align=\"center\"><img src=\"$imagefiles/help.gif\" alt=\"".$clang->gT("Delete Survey")."\"></th>\n"
        . "<th align=\"center\"><img src=\"$imagefiles/help.gif\" alt=\"".$clang->gT("Activate Survey")."\"></th>\n"
        . "<th>".$clang->gT("Action")."</th>\n"
        . "</tr></thead>\n";

        $style="style='width: 15em;'";
        $surveysecurity .= "<tfoot>\n"
        . "<tr>\n"
        . "<td colspan='9' align='right'>"
        . "<form action='$scriptname?sid={$surveyid}' method='post'>\n"
        . "<strong>".$clang->gT("User").": </strong><select id='uidselect' name='uid'>\n"
        . getsurveyuserlist()
        . "</select>\n"
        . "<input $style type='submit' value='".$clang->gT("Add User")."'  onclick=\"if (document.getElementById('uidselect').value == -1) {alert('".$clang->gT("Please select a user first","js")."'); return false;}\"/>"
        . "<input type='hidden' name='action' value='addsurveysecurity' />"
        . "</form>\n"
        . "</td>\n"

        . "<td></td>\n"
        . "</tr>\n"

        . "<tr>\n"
        . "<td colspan='9' align='right'>"
        . "<form action='$scriptname?sid={$surveyid}' method='post'>\n"
        . "<strong>".$clang->gT("Groups").": </strong><select id='ugidselect' name='ugid'>\n"
        . getsurveyusergrouplist()
        . "</select>\n"
        . "<input $style type='submit' value='".$clang->gT("Add User Group")."' onclick=\"if (document.getElementById('ugidselect').value == -1) {alert('".$clang->gT("Please select a user group first","js")."'); return false;}\" />"
        . "<input type='hidden' name='action' value='addusergroupsurveysecurity' />\n"
        . "</form>\n"
        . "</td>\n"

        . "<td></td>\n"
        . "</tr></tfoot>\n";

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
            while ($resul2row = $result2->FetchRow())
            {
                $query3 = "SELECT a.ugid FROM ".db_table_name('user_in_groups')." AS a RIGHT OUTER JOIN ".db_table_name('users')." AS b ON a.uid = b.uid WHERE b.uid = ".$resul2row['uid'];
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
                if(($row % 2) == 0)
                $surveysecurity .= "<tr class=\"oddrow\">\n";
                else
                $surveysecurity .= "<tr class=\"evenrow\">\n";

                $surveysecurity .= "<td>{$resul2row['users_name']}</td>\n"
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
                . "<td>\n{$resul2row['full_name']}</td>\n";

                //Now insert the rights
                $rightsarr = array('edit_survey_property','define_questions','browse_response','export','delete_survey','activate_survey');
                foreach ($rightsarr as $right) {
                    if ($resul2row[$right]==1) {
                        $insert = "<div class=\"ui-icon ui-icon-check\"></div>";
                    } else {
                        $insert = "<div></div>";
                    }
                    $surveysecurity .= "<td align=\"center\">\n$insert\n</td>\n";
                }

                $surveysecurity .= "<td style='padding-top:10px;'>\n";

                $surveysecurity .= "<form method='post' action='$scriptname?sid={$surveyid}'>"
                ."<input type='submit' value='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")' />"
                ."<input type='hidden' name='action' value='delsurveysecurity' />"
                ."<input type='hidden' name='user' value='{$resul2row['users_name']}' />"
                ."<input type='hidden' name='uid' value='{$resul2row['uid']}' />"
                ."</form>";

                $surveysecurity .= "<form method='post' action='$scriptname?sid={$surveyid}'>"
                ."<input type='submit' value='".$clang->gT("Set Survey Rights")."' />"
                ."<input type='hidden' name='action' value='setsurveysecurity' />"
                ."<input type='hidden' name='user' value='{$resul2row['users_name']}' />"
                ."<input type='hidden' name='uid' value='{$resul2row['uid']}' />"
                ."</form>\n";

                $surveysecurity .= "</td>\n"
                . "</tr>\n";
                $row++;
            }
        } else {
            $surveysecurity .= "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>"; //fix error on empty table
        }
        $surveysecurity .= "</tbody>\n"
        . "</table>\n";
    }
    else
    {
        include("access_denied.php");
    }
}

elseif ($action == "surveyrights")
{
    $addsummary = "<div class=\"header\">".$clang->gT("Set Survey Rights")."</div>\n";
    $addsummary .= "<div class=\"messagebox\">\n";

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
    $result = db_execute_assoc($query); //Checked
    if($result->RecordCount() > 0)
    {
        $rights = array();

        if(isset($_POST['edit_survey_property']))$rights['edit_survey_property']=1;	else $rights['edit_survey_property']=0;
        if(isset($_POST['define_questions']))$rights['define_questions']=1;			else $rights['define_questions']=0;
        if(isset($_POST['browse_response']))$rights['browse_response']=1;			else $rights['browse_response']=0;
        if(isset($_POST['export']))$rights['export']=1;								else $rights['export']=0;
        if(isset($_POST['delete_survey']))$rights['delete_survey']=1;				else $rights['delete_survey']=0;
        if(isset($_POST['activate_survey']))$rights['activate_survey']=1;			else $rights['activate_survey']=0;

        if(isset($postuserid)){
            $uids[] = $postuserid;
        }
        else{
            $uids = $_SESSION['uids'];
            unset($_SESSION['uids']);
        }

        if(setsurveyrights($uids, $rights))
        {
            $addsummary .= "<div class=\"successheader\">".$clang->gT("Update survey rights successful.")."</div>\n";
        }
        else
        {
            $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to update survey rights!")."</div>\n";
        }
        $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('$scriptname?sid={$surveyid}&amp;action=surveysecurity', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
    }
    else
    {
        include("access_denied.php");
    }
    $addsummary .= "</div>\n";
}

// *************************************************
// Survey Rights End	****************************
// *************************************************


// Editing the survey
if ($action == "editsurvey")
{
    if(hasRight($surveyid,'edit_survey_property'))
    {
        $js_admin_includes[]='scripts/surveysettings.js';
        $esquery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid";
        $esresult = db_execute_assoc($esquery); //Checked
        while ($esrow = $esresult->FetchRow())
        {
            $esrow = array_map('htmlspecialchars', $esrow);

            // header
            $editsurvey = "<div class='header'>".$clang->gT("Edit survey settings - Step 1 of 2")."</div>\n";

            // beginning TABs section - create tab pane
            $editsurvey .= "<div class='tab-pane' id='tab-pane-survey-$surveyid'>\n";
            $editsurvey .= "<form id='addnewsurvey' class='form30' name='addnewsurvey' action='$scriptname' method='post'>\n";
            // General & Contact TAB
            $editsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("General")."</h2><ul>\n";

            // Base Language
            $editsurvey .= "<li><label>".$clang->gT("Base Language:")."</label>\n"
            .GetLanguageNameFromCode($esrow['language'])
            . "</li>\n"

            // Additional languages listbox
            . "<li><label for='additional_languages'>".$clang->gT("Additional Languages").":</label>\n"
            . "<table><tr><td align='left'><select style='min-width:220px;' size='5' id='additional_languages' name='additional_languages'>";
            $jsX=0;
            $jsRemLang ="<script type=\"text/javascript\">
                            var mylangs = new Array();
                            templaterooturl='$templaterooturl'; \n";

            foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname)
            {
                if ($langname && $langname!=$esrow['language']) // base languag must not be shown here
                {
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
            foreach (getLanguageData() as  $langkey2=>$langname)
            {
                if ($langkey2!=$esrow['language'] && in_array($langkey2,$tempLang)==false)  // base languag must not be shown here
                {
                    $editsurvey .= "<option id='".$langkey2."' value='".$langkey2."'";
                    $editsurvey .= ">".$langname['description']."</option>\n";
                }
            }
            $editsurvey .= "</select></td>"
            . " </tr></table></li>\n";

            $editsurvey .= "";


            // Administrator...
            $editsurvey .= ""
            . "<li><label for='admin'>".$clang->gT("Administrator:")."</label>\n"
            . "<input type='text' size='50' id='admin' name='admin' value=\"{$esrow['admin']}\" /></li>\n"
            . "<li><label for='adminemail'>".$clang->gT("Admin Email:")."</label>\n"
            . "<input type='text' size='50' id='adminemail' name='adminemail' value=\"{$esrow['adminemail']}\" /></li>\n"
            . "<li><label for='bounce_email'>".$clang->gT("Bounce Email:")."</label>\n"
            . "<input type='text' size='50' id='bounce_email' name='bounce_email' value=\"{$esrow['bounce_email']}\" /></li>\n"
            . "<li><label for='faxto'>".$clang->gT("Fax To:")."</label>\n"
            . "<input type='text' size='50' id='faxto' name='faxto' value=\"{$esrow['faxto']}\" /></li>\n";

            // End General TAB
            // Create Survey Button
            $editsurvey .= "</ul></div>\n";

            // Presentation and navigation TAB
            $editsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Presentation & Navigation")."</h2><ul>\n";

            //Format
            $editsurvey .= "<li><label for='format'>".$clang->gT("Format:")."</label>\n"
            . "<select id='format' name='format'>\n"
            . "<option value='S'";
            if ($esrow['format'] == "S" || !$esrow['format']) {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Question by Question")."</option>\n"
            . "<option value='G'";
            if ($esrow['format'] == "G") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Group by Group")."</option>\n"
            . "<option value='A'";
            if ($esrow['format'] == "A") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("All in one")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //TEMPLATES
            $editsurvey .= "<li><label for='template'>".$clang->gT("Template:")."</label>\n"
            . "<select id='template' name='template'>\n";
            foreach (gettemplatelist() as $tname)
            {

                if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] == 1 || hasTemplateManageRights($_SESSION["loginID"], $tname) == 1 )
                {
                    $editsurvey .= "<option value='$tname'";
                    if ($esrow['template'] && htmlspecialchars($tname) == $esrow['template']) {$editsurvey .= " selected='selected'";}
                    elseif (!$esrow['template'] && $tname == "default") {$editsurvey .= " selected='selected'";}
                    $editsurvey .= ">$tname</option>\n";
                }

            }
            $editsurvey .= "</select>\n"
            . "</li>\n";

            $editsurvey .= "<li><label for='preview'>".$clang->gT("Template Preview:")."</label>\n"
            . "<img alt='".$clang->gT("Template Preview:")."' id='preview' src='$publicurl/templates/{$esrow['template']}/preview.png' />\n"
            . "</li>\n" ;

            //ALLOW SAVES
            $editsurvey .= "<li><label for='allowsave'>".$clang->gT("Allow Saves?")."</label>\n"
            . "<select id='allowsave' name='allowsave'>\n"
            . "<option value='Y'";
            if (!$esrow['allowsave'] || $esrow['allowsave'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if ($esrow['allowsave'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

            //Show Prev Button
            $editsurvey .= "<li><label for='allowprev'>".$clang->gT("Show [<< Prev] button")."</label>\n"
            . "<select id='allowprev' name='allowprev'>\n"
            . "<option value='Y'";
            if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

            //Result printing
            $editsurvey .= "<li><label for='printanswers'>".$clang->gT("Participants may print answers?")."</label>\n"
            . "<select id='printanswers' name='printanswers'>\n"
            . "<option value='Y'";
            if (!isset($esrow['printanswers']) || !$esrow['printanswers'] || $esrow['printanswers'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if (isset($esrow['printanswers']) && $esrow['printanswers'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //Public statistics
            $editsurvey .= "<li><label for='publicstatistics'>".$clang->gT("Public statistics?")."</label>\n"
            . "<select id='publicstatistics' name='publicstatistics'>\n"
            . "<option value='Y'";
            if (!isset($esrow['publicstatistics']) || !$esrow['publicstatistics'] || $esrow['publicstatistics'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if (isset($esrow['publicstatistics']) && $esrow['publicstatistics'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //Public statistics
            $editsurvey .= "<li><label for='publicgraphs'>".$clang->gT("Show graphs in public statistics?")."</label>\n"
            . "<select id='publicgraphs' name='publicgraphs'>\n"
            . "<option value='Y'";
            if (!isset($esrow['publicgraphs']) || !$esrow['publicgraphs'] || $esrow['publicgraphs'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if (isset($esrow['publicgraphs']) && $esrow['publicgraphs'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //Public Surveys
            $editsurvey .= "<li><label for='public'>".$clang->gT("List survey publicly:")."</label>\n"
            . "<select id='public' name='public'>\n"
            . "<option value='Y'";
            if (!isset($esrow['listpublic']) || !$esrow['listpublic'] || $esrow['listpublic'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if (isset($esrow['listpublic']) && $esrow['listpublic'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";


            // End URL block
            $editsurvey .= "<li><label for='autoredirect'>".$clang->gT("Automatically load URL when survey complete?")."</label>\n"
            . "<select id='autoredirect' name='autoredirect'>";
            $editsurvey .= "<option value='Y'";
            if (isset($esrow['autoredirect']) && $esrow['autoredirect'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n";
            $editsurvey .= "<option value='N'";
            if (!isset($esrow['autoredirect']) || $esrow['autoredirect'] != "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>";


            // End Presention and navigation TAB
            $editsurvey .= "</ul></div>\n";

            // Publication and access control TAB
            $editsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Publication & Access control")."</h2><ul>\n";



            /* Token access
             $editsurvey .= "<li><label for='usetokens'>".$clang->gT("Only users with tokens may enter the survey?")."</label>\n"
             . "<select id='usetokens' name='usetokens'>\n"
             . "<option value='Y'";
             if ($esrow['usetokens'] == "Y") {$editsurvey .= " selected='selected'";}
             $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
             . "<option value='N'";
             if ($esrow['usetokens'] != "Y") {$editsurvey .= " selected='selected'";}
             $editsurvey .= ">".$clang->gT("No")."</option>\n"
             . "</select></li>\n"; */

            //Set token length
            $editsurvey .= "<li><label for='tokenlength'>".$clang->gT("Set token length to:")."</label>\n"
            . "<input type='text' value=\"{$esrow['tokenlength']}\" name='tokenlength' id='tokenlength' size='12' maxlength='2' onkeypress=\"return goodchars(event,'0123456789')\" />\n"
            . "</li>\n";

            // Self registration
            $editsurvey .= "<li><label for='allowregister'>".$clang->gT("Allow public registration?")."</label>\n"
            . "<select id='allowregister' name='allowregister'>\n"
            . "<option value='Y'";
            if ($esrow['allowregister'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if ($esrow['allowregister'] != "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";



            // Start date
            $dateformatdetails=getDateFormatData($_SESSION['dateformat']);
            $startdate='';
            if (trim($esrow['startdate'])!= '')
            {
                $datetimeobj = new Date_Time_Converter($esrow['startdate'] , "Y-m-d H:i:s");
                $startdate=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
            }

            $editsurvey .= "<li><label for='startdate'>".$clang->gT("Start date/time:")."</label>\n"
            . "<input type='text' class='popupdatetime' id='startdate' size='20' name='startdate' value=\"{$startdate}\" /></li>\n";

            // Expiration date
            $expires='';
            if (trim($esrow['expires'])!= '')
            {
                $datetimeobj = new Date_Time_Converter($esrow['expires'] , "Y-m-d H:i:s");
                $expires=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
            }
            $editsurvey .="<li><label for='expires'>".$clang->gT("Expiry date/time:")."</label>\n"
            . "<input type='text' class='popupdatetime' id='expires' size='20' name='expires' value=\"{$expires}\" /></li>\n";
            //COOKIES
            $editsurvey .= "<li><label for=''>".$clang->gT("Set cookie to prevent repeated participation?")."</label>\n"
            . "<select name='usecookie'>\n"
            . "<option value='Y'";
            if ($esrow['usecookie'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if ($esrow['usecookie'] != "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";


            // Use Captcha
            $editsurvey .= "<li><label for=''>".$clang->gT("Use CAPTCHA for").":</label>\n"
            . "<select name='usecaptcha'>\n"
            . "<option value='A'";
            if ($esrow['usecaptcha'] == "A") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='B'";
            if ($esrow['usecaptcha'] == "B") {$editsurvey .= " selected='selected'";}

            $editsurvey .= ">".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ---------</option>\n"
            . "<option value='C'";
            if ($esrow['usecaptcha'] == "C") {$editsurvey .= " selected='selected'";}

            $editsurvey .= ">".$clang->gT("Survey Access")." / ------------ / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='D'";
            if ($esrow['usecaptcha'] == "D") {$editsurvey .= " selected='selected'";}

            $editsurvey .= ">------------- / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='X'";

            if ($esrow['usecaptcha'] == "X") {$editsurvey .= " selected='selected'";}

            $editsurvey .= ">".$clang->gT("Survey Access")." / ------------ / ---------</option>\n"
            . "<option value='R'";
            if ($esrow['usecaptcha'] == "R") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">------------- / ".$clang->gT("Registration")." / ---------</option>\n"
            . "<option value='S'";
            if ($esrow['usecaptcha'] == "S") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">------------- / ------------ / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='N'";
            if ($esrow['usecaptcha'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">------------- / ------------ / ---------</option>\n"

            . "</select>\n</li>\n";

            // Email format
            $editsurvey .= "<li><label for=''>".$clang->gT("Use HTML format for token emails?")."</label>\n"
            . "<select name='htmlemail' onchange=\"alert('".$clang->gT("If you switch email mode, you'll have to review your email templates to fit the new format","js")."');\">\n"
            . "<option value='Y'";
            if ($esrow['htmlemail'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if ($esrow['htmlemail'] == "N") {$editsurvey .= " selected='selected'";}

            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

            // End Publication and access control TAB
            $editsurvey .= "</ul></div>\n";

            // Notification and Data management TAB
            $editsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Notification & Data Management")."</h2><ul>\n";


            //NOTIFICATION
            $editsurvey .= "<li><label for=''>".$clang->gT("Admin Notification:")."</label>\n"
            . "<select name='notification'>\n"
            . getNotificationlist($esrow['notification'])
            . "</select>\n"
            . "</li>\n";

            //EMAIL SURVEY RESPONSES TO
            $editsurvey .= "<li><label for=''>".$clang->gT("Email responses to:")."</label>\n"
            . "<input type='text' value=\"{$esrow['emailresponseto']}\" name='emailresponseto' />\n"
            . "</li>\n";

            //ANONYMOUS
            $editsurvey .= "<li><label for=''>".$clang->gT("Anonymous answers?")."\n";
            // warning message if anonymous + tokens used
            $editsurvey .= "\n"
            . "<script type=\"text/javascript\"><!-- \n"
            . "function alertPrivacy()\n"
            . "{\n"
            . "if (document.getElementById('tokenanswerspersistence').value == 'Y')\n"
            . "{\n"
            . "alert('".$clang->gT("You can't use Anonymous answers when Token-based answers persistence is enabled.","js")."');\n"
            . "document.getElementById('private').value = 'N';\n"
            . "}\n"
            . "else if (document.getElementById('private').value == 'Y')\n"
            . "{\n"
            . "alert('".$clang->gT("Warning").": ".$clang->gT("If you turn on the -Anonymous answers- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.","js")."');\n"
            . "}\n"
            . "}"
            . "//--></script></label>\n";

            if ($esrow['active'] == "Y")
            {
                $editsurvey .= "\n";
                if ($esrow['private'] == "N") {$editsurvey .= " ".$clang->gT("This survey is NOT anonymous.");}
                else {$editsurvey .= $clang->gT("Answers to this survey are anonymized.");}
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='private' value=\"{$esrow['private']}\" />\n";
            }
            else
            {
                $editsurvey .= "<select id='private' name='private' onchange='alertPrivacy();'>\n"
                . "<option value='Y'";
                if ($esrow['private'] == "Y") {$editsurvey .= " selected='selected'";}
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
                if ($esrow['private'] != "Y") {$editsurvey .= " selected='selected'";}
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";

            // date stamp
            $editsurvey .= "<li><label for=''>".$clang->gT("Date Stamp?")."</label>\n";
            if ($esrow['active'] == "Y")
            {
                $editsurvey .= "\n";
                if ($esrow['datestamp'] != "Y") {$editsurvey .= " ".$clang->gT("Responses will not be date stamped.");}
                else {$editsurvey .= $clang->gT("Responses will be date stamped.");}
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='datestamp' value=\"{$esrow['datestamp']}\" />\n";
            }
            else
            {
                $editsurvey .= "<select id='datestamp' name='datestamp' onchange='alertPrivacy();'>\n"
                . "<option value='Y'";
                if ($esrow['datestamp'] == "Y") {$editsurvey .= " selected='selected'";}
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
                if ($esrow['datestamp'] != "Y") {$editsurvey .= " selected='selected'";}
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";

            // Ip Addr
            $editsurvey .= "<li><label for=''>".$clang->gT("Save IP Address?")."</label>\n";

            if ($esrow['active'] == "Y")
            {
                $editsurvey .= "\n";
                if ($esrow['ipaddr'] != "Y") {$editsurvey .= " ".$clang->gT("Responses will not have the IP address logged.");}
                else {$editsurvey .= $clang->gT("Responses will have the IP address logged");}
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='ipaddr' value='".$esrow['ipaddr']."' />\n";
            }
            else
            {
                $editsurvey .= "<select name='ipaddr'>\n"
                . "<option value='Y'";
                if ($esrow['ipaddr'] == "Y") {$editsurvey .= " selected='selected'";}
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
                if ($esrow['ipaddr'] != "Y") {$editsurvey .= " selected='selected'";}
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }

            $editsurvey .= "</li>\n";

            // begin REF URL Block
            $editsurvey .= "<li><label for=''>".$clang->gT("Save Referring URL?")."</label>\n";

            if ($esrow['active'] == "Y")
            {
                $editsurvey .= "\n";
                if ($esrow['refurl'] != "Y") {$editsurvey .= " ".$clang->gT("Responses will not have their referring URL logged.");}
                else {$editsurvey .= $clang->gT("Responses will have their referring URL logged.");}
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='refurl' value='".$esrow['refurl']."' />\n";
            }
            else
            {
                $editsurvey .= "<select name='refurl'>\n"
                . "<option value='Y'";
                if ($esrow['refurl'] == "Y") {$editsurvey .= " selected='selected'";}
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
                if ($esrow['refurl'] != "Y") {$editsurvey .= " selected='selected'";}
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";
            // BENBUN - END REF URL Block

            // Token answers persistence
            $editsurvey .= "<li><label for=''>".$clang->gT("Enable token-based response persistence?")."</label>\n"
            . "<select id='tokenanswerspersistence' name='tokenanswerspersistence' onchange=\"javascript: if (document.getElementById('private').value == 'Y') {alert('".$clang->gT("This option can't be set if Anonymous answers are used","js")."'); this.value='N';}\">\n"
            . "<option value='Y'";
            if ($esrow['tokenanswerspersistence'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if ($esrow['tokenanswerspersistence'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

            // Enable assessments
            $editsurvey .= "<li><label for=''>".$clang->gT("Enable assessment mode?")."</label>\n"
            . "<select id='assessments' name='assessments'>\n"
            . "<option value='Y'";
            if ($esrow['assessments'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if ($esrow['assessments'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";



            // End Notification and Data management TAB
            $editsurvey .= "</ul></div>\n";

            // Ending First TABs Form
            $editsurvey .= ""
            . "<input type='hidden' name='action' value='updatesurvey' />\n"
            . "<input type='hidden' name='sid' value=\"{$esrow['sid']}\" />\n"
            . "<input type='hidden' name='languageids' id='languageids' value=\"{$esrow['additional_languages']}\" />\n"
            . "<input type='hidden' name='language' value=\"{$esrow['language']}\" />\n"
            ."</form>";


            // TAB Uploaded Resources Management

            $ZIPimportAction = " onclick='if (validatefilename(this.form,\"".$clang->gT('Please select a file to import!','js')."\")) {this.form.submit();}'";
            if (!function_exists("zip_open"))
            {
                $ZIPimportAction = " onclick='alert(\"".$clang->gT("zip library not supported by PHP, Import ZIP Disabled","js")."\");'";
            }

            $disabledIfNoResources = '';
            if (hasResources($surveyid,'survey') === false)
            {
                $disabledIfNoResources = " disabled='disabled'";
            }

            $editsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Uploaded Resources Management")."</h2>\n"
            . "<form enctype='multipart/form-data'  class='form30' id='importsurveyresources' name='importsurveyresources' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
            . "<input type='hidden' name='sid' value='$surveyid' />\n"
            . "<input type='hidden' name='action' value='importsurveyresources' />\n"
            . "<ul>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' onclick='window.open(\"$fckeditordir/editor/filemanager/browser/default/browser.html?Connector=../../connectors/php/connector.php\", \"_blank\")' value=\"".$clang->gT("Browse Uploaded Resources")."\" $disabledIfNoResources /></li>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' onclick='window.open(\"$scriptname?action=exportsurvresources&amp;sid={$surveyid}\", \"_blank\")' value=\"".$clang->gT("Export Resources As ZIP Archive")."\" $disabledIfNoResources /></li>\n"
            . "<li><label for='the_file'>".$clang->gT("Select ZIP File:")."</label>\n"
            . "<input id='the_file' name='the_file' type='file' size='50' /></li>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' value='".$clang->gT("Import Resources ZIP Archive")."' $ZIPimportAction /></li>\n"
            . "</ul></form>\n";

            // End TAB Uploaded Resources Management
            $editsurvey .= "</div>\n";

            // End TAB pane
            $editsurvey .= "</div>\n";


            // The external button to sumbit Survey edit changes
            $editsurvey .= "<p><button onclick='if (UpdateLanguageIDs(mylangs,\"".$clang->gT("All questions, answers, etc for removed languages will be lost. Are you sure?","js")."\")) {document.getElementById(\"addnewsurvey\").submit();}' class='standardbtn' >".$clang->gT("Save and Continue")." >></button>\n";
        }

    }
    else
    {
        include("access_denied.php");
    }

}


if ($action == "updatesurvey")  // Edit survey step 2  - editing language dependent settings
{
    if(hasRight($surveyid,'edit_survey_property'))
    {

        $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($grplangs,$baselang);

        $editsurvey = PrepareEditorScript();


        $editsurvey .="<script type='text/javascript'>\n"
        . "<!--\n"
        . "function fillin(tofield, fromfield)\n"
        . "{\n"
        . "if (confirm(\"".$clang->gT("This will replace the existing text. Continue?","js")."\")) {\n"
        . "document.getElementById(tofield).value = document.getElementById(fromfield).value\n"
        . "}\n"
        . "}\n"
        . "--></script>\n"
        . "<div class='header'>".$clang->gT("Edit survey settings - Step 2 of 2")."</div>\n";
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
            $editsurvey .= "<li><label for=''>".$clang->gT("Title").":</label>\n"
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
        $editsurvey .= "<p><input type='submit' class='standardbtn' value='".$clang->gT("Save")."' />\n"
        . "<input type='hidden' name='action' value='updatesurvey2' />\n"
        . "<input type='hidden' name='sid' value=\"{$surveyid}\" />\n"
        . "<input type='hidden' name='language' value=\"{$esrow['surveyls_language']}\" />\n"
        . "</p>\n"
        . "</form>\n";

    }
    else
    {
        include("access_denied.php");
    }

}

if($action == "quotas")
{
    include("quota.php");
}

// Show the screen to order groups

if ($action == "newsurvey")
{
    if($_SESSION['USER_RIGHT_CREATE_SURVEY'])
    {
        $js_admin_includes[]='scripts/surveysettings.js';
        $dateformatdetails=getDateFormatData($_SESSION['dateformat']);

        $newsurvey = PrepareEditorScript();

        // header
        $newsurvey .= "<div class='header'>"
        . "".$clang->gT("Create, import or copy survey")."</div>\n";

        // begin Tabs section
        $newsurvey .= "<script type=\"text/javascript\">
                           templaterooturl='$templaterooturl'; \n
                       </script>";

        $newsurvey .= "<div class='tab-pane' id='tab-pane-newsurvey'>\n";
        $newsurvey  .= "<form class='form30' name='addnewsurvey' id='addnewsurvey' action='$scriptname' method='post' onsubmit=\"alert('hi');return isEmpty(document.getElementById('surveyls_title'), '".$clang->gT("Error: You have to enter a title for this survey.",'js')."');\" >\n";

        // General and Contact TAB
        $newsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("General")."</h2>\n";

        // * Survey Language
        $newsurvey .= "<ul><li><label for='language' title='".$clang->gT("This is the base language of your survey and it can't be changed later. You can add more languages after you have created the survey.")."'><span class='annotationasterisk'>*</span>".$clang->gT("Base Language:")."</label>\n"
        . "<select id='language' name='language'>\n";


        foreach (getLanguageData() as  $langkey2=>$langname)
        {
            $newsurvey .= "<option value='".$langkey2."'";
            if ($defaultlang == $langkey2) {$newsurvey .= " selected='selected'";}
            $newsurvey .= ">".$langname['description']."</option>\n";
        }

        //Use the current user details for the default administrator name and email for this survey
        $query = "SELECT full_name, email FROM ".db_table_name('users')." WHERE users_name = ".db_quoteall($_SESSION['user']);
        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
        $owner=$result->FetchRow();
        //Degrade gracefully to $siteadmin details if anything is missing.
        if(empty($owner['full_name'])) $owner['full_name']=$siteadminname;
        if(empty($owner['email'])) $owner['email'] = $siteadminemail;

        $newsurvey .= "</select><span class='annotation'> ".$clang->gT("*This setting cannot be changed later!")."</span></li>\n";

        $newsurvey .= ""
        . "<li><label for='surveyls_title'><span class='annotationasterisk'>*</span>".$clang->gT("Title").":</label>\n"
        . "<input type='text' size='82' maxlength='200' id='surveyls_title' name='surveyls_title' /> <span class='annotation'>".$clang->gT("*Required")."</span></li>\n"
        . "<li><label for='description'>".$clang->gT("Description:")."</label>\n"
        . "<textarea cols='80' rows='10' id='description' name='description'></textarea>"
        . getEditor("survey-desc","description", "[".$clang->gT("Description:", "js")."]",'','','',$action)
        . "</li>\n"
        . "<li><label for='welcome'>".$clang->gT("Welcome message:")."</label>\n"
        . "<textarea cols='80' rows='10' id='welcome' name='welcome'></textarea>"
        . getEditor("survey-welc","welcome", "[".$clang->gT("Welcome message:", "js")."]",'','','',$action)
        . "</li>\n"
        . "<li><label for='endtext'>".$clang->gT("End message:")."</label>\n"
        . "<textarea cols='80' id='endtext' rows='10' name='endtext'></textarea>"
        . getEditor("survey-endtext","endtext", "[".$clang->gT("End message:", "js")."]",'','','',$action)
        . "</li>\n"
        . "<li><label for='admin'>".$clang->gT("Administrator:")."</label>\n"
        . "<input type='text' size='50' id='admin' name='admin' value='".$owner['full_name']."' /></li>\n"
        . "<li><label for='adminemail'>".$clang->gT("Admin Email:")."</label>\n"
        . "<input type='text' size='50' id='adminemail' name='adminemail' value='".$owner['email']."' /></li>\n"
        . "<li><label for='bounce_email'>".$clang->gT("Bounce Email:")."</label>\n"
        . "<input type='text' size='50' id='bounce_email' name='bounce_email' value='".$owner['email']."' /></li>\n";
        $newsurvey .= "<li><label for='faxto'>".$clang->gT("Fax To:")."</label>\n"
        . "<input type='text' size='50' id='faxto' name='faxto' /></li></ul>\n";

        // End General TAB
        // Create Survey Button
        $newsurvey .= "<p><input type='button' onclick=\"if (isEmpty(document.getElementById('surveyls_title'), '".$clang->gT("Error: You have to enter a title for this survey.",'js')."')) { document.getElementById('addnewsurvey').submit(); }; return false;\" value='".$clang->gT("Save survey")."' /></p>\n";

        $newsurvey .= "</div>\n";

        // Presentation and navigation TAB
        $newsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Presentation & Navigation")."</h2>\n";


        $newsurvey .= "<ul><li><label for='format'>".$clang->gT("Format:")."</label>\n"
        . "<select name='format' id='format'>\n"
        . "<option value='S'>".$clang->gT("Question by Question")."</option>\n"
        . "<option value='G' selected='selected'>".$clang->gT("Group by Group")."</option>\n"
        . "<option value='A'>".$clang->gT("All in one")."</option>\n"
        . "</select>\n"
        . "</li>\n";
        $newsurvey .= "<li><label for='template'>".$clang->gT("Template:")."</label>\n"
        . "<select id='template' name='template'>\n";
        foreach (gettemplatelist() as $tname)
        {

            if ($_SESSION["loginID"] == 1 || $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] == 1 || hasTemplateManageRights($_SESSION["loginID"], $tname) == 1 )  {
                $newsurvey .= "<option value='$tname'";
                if (isset($esrow) && $esrow['template'] && $tname == $esrow['template']) {$newsurvey .= " selected='selected'";}
                elseif ((!isset($esrow) || !$esrow['template']) && $tname == $defaulttemplate) {$newsurvey .= " selected='selected'";}
                $newsurvey .= ">$tname</option>\n";
            }

        }
        $newsurvey .= "</select>\n"
        . "</li>\n"
        . "<li><label for='preview'>".$clang->gT("Template Preview:")."</label>\n"
        . "<img alt='".$clang->gT("Template Preview:")."' id='preview' src='$publicurl/templates/{$defaulttemplate}/preview.png' />\n"
        . "</li>\n";

        //ALLOW SAVES
        $newsurvey .= "<li><label for='allowsave'>".$clang->gT("Allow Saves?")."</label>\n"
        . "<select id='allowsave' name='allowsave'>\n"
        . "<option value='Y'";
        if (!isset($esrow['allowsave']) || !$esrow['allowsave'] || $esrow['allowsave'] == "Y") {$newsurvey .= " selected='selected'";}
        $newsurvey .= ">".$clang->gT("Yes")."</option>\n"
        . "<option value='N'";
        if (isset($esrow['allowsave']) && $esrow['allowsave'] == "N") {$newsurvey .= " selected='selected'";}
        $newsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        //ALLOW PREV
        $newsurvey .= "<li><label for='allowprev'>".$clang->gT("Show [<< Prev] button")."</label>\n"
        . "<select id='allowprev' name='allowprev'>\n"
        . "<option value='Y' selected='selected'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N'>".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        //Result printing
        $newsurvey .= "<li><label for='printanswers'>".$clang->gT("Participants may print answers?")."</label>\n"
        . "<select id='printanswers' name='printanswers'>\n"
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        //Public statistics
        $newsurvey .= "<li><label for='publicstatistics'>".$clang->gT("Public statistics?")."</label>\n"
        . "<select id='publicstatistics' name='publicstatistics'>\n"
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        //Public statistics graphs
        $newsurvey .= "<li><label for='publicgraphs'>".$clang->gT("Show graphs in public statistics?")."</label>\n"
        . "<select id='publicgraphs' name='publicgraphs'>\n"
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        //List survey publicly
        $newsurvey .= "<li><label for='public'>".$clang->gT("List survey publicly:")."</label>\n"
        . "<select id='public' name='public'>\n"
        . "<option value='Y' selected='selected'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N'>".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        // End URL
        $newsurvey .= "<li><label for='url'>".$clang->gT("End URL:")."</label>\n"
        . "<input type='text' size='50' id='url' name='url' value='http://";
        $newsurvey .= "' /></li>\n";

        // URL description
        $newsurvey.= "<li><label for='urldescrip'>".$clang->gT("URL description:")."</label>\n"
        . "<input type='text' maxlength='255' size='50' id='urldescrip' name='urldescrip' value='";
        if (isset($esrow)) {$newsurvey .= $esrow['surveyls_urldescription'];}
        $newsurvey .= "' /></li>\n";
        $newsurvey .= "<li><label for='autoredirect'>".$clang->gT("Automatically load URL when survey complete?")."</label>\n"
        . "<select name='autoredirect' id='autoredirect'>\n"
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "</select></li>"

        //Default date format
        . "<li><label for='dateformat'>".$clang->gT("Date format:")."</label>\n"
        . "<select size='1' id='dateformat' name='dateformat'>\n";
        foreach (getDateFormatData() as $index=>$dateformatdata)
        {
            $newsurvey.= "<option value='{$index}'";
            $newsurvey.= ">".$dateformatdata['dateformat'].'</option>';
        }
        $newsurvey.= "</select></li></ul>";


        // End Presention and navigation TAB
        // Create Survey Button
        $newsurvey .= "<p><input type='button' onclick=\"if (isEmpty(document.getElementById('surveyls_title'), '".$clang->gT("Error: You have to enter a title for this survey.",'js')."')) { document.getElementById('addnewsurvey').submit(); }; return false;\" value='".$clang->gT("Save survey")."' /></p>\n";
        $newsurvey .= "</div>\n";

        // Publication and access control TAB
        $newsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Publication & Access control")."</h2><ul>\n";


        // Use tokens
        /*    $newsurvey .= "<li><label for='usetokens'>".$clang->gT("Only users with tokens may enter the survey?")."</label>\n"
        . "<select id='usetokens' name='usetokens'>\n"
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "</select></li>\n"; */

        // Set token length
        $newsurvey .= "<li><label for='tokenlength'>".$clang->gT("Set token length to:")."</label>\n"
        . "<input value='15' type='text' name='tokenlength' id='tokenlength' size='12' maxlength='2' onkeypress=\"return goodchars(event,'0123456789')\" />"
        . "</li>\n";

        // Public registration
        $newsurvey .= "<li><label for='allowregister'>".$clang->gT("Allow public registration?")."</label>\n"
        . "<select id='allowregister' name='allowregister'>\n"
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        // Timed Start
        $newsurvey .= "<li><label for='startdate'>".$clang->gT("Start date:")."</label>\n"
        . "<input type='text' class='popupdatetime' id='startdate' size='20' name='startdate' value='' />"
        . "<font size='1'> ".sprintf($clang->gT("Date format: %s"), $dateformatdetails['dateformat'].' hh:nn')."</font></li>\n";

        // Expiration
        $newsurvey .= "<li><label for='enddate'>".$clang->gT("Expiry Date:")."</label>\n"
        . "<input type='text' class='popupdatetime' id='enddate' size='20' name='expires' value='' />"
        . "<font size='1'> ".sprintf($clang->gT("Date format: %s"), $dateformatdetails['dateformat'].' hh:nn')."</font></li>\n";

        //COOKIES
        $newsurvey .= "<li><label for='usecookie'>".$clang->gT("Set cookie to prevent repeated participation?")."</label>\n"
        . "<select id='usecookie' name='usecookie'>\n"
        . "<option value='Y'";
        if (isset($esrow) && $esrow['usecookie'] == "Y") {$newsurvey .= " selected='selected'";}
        $newsurvey .= ">".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'";
        $newsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></li>\n";


        // Use Captcha
        $newsurvey .= "<li><label for='usecaptcha'>".$clang->gT("Use CAPTCHA for").":</label>\n"
        . "<select id='usecaptcha' name='usecaptcha'>\n"
        . "<option value='A'>".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
        . "<option value='B'>".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ---------</option>\n"
        . "<option value='C'>".$clang->gT("Survey Access")." / ------------ / ".$clang->gT("Save & Load")."</option>\n"
        . "<option value='D' selected='selected'>------------- / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
        . "<option value='X'>".$clang->gT("Survey Access")." / ------------ / ---------</option>\n"
        . "<option value='R'>------------- / ".$clang->gT("Registration")." / ---------</option>\n"
        . "<option value='S'>------------- / ------------ / ".$clang->gT("Save & Load")."</option>\n"
        . "<option value='N'>------------- / ------------ / ---------</option>\n"
        . "</select></li>\n";

        // Email format
        $newsurvey .= "<li><label for='htmlemail'>".$clang->gT("Use HTML format for token emails?")."</label>\n"
        . "<select id='htmlemail' name='htmlemail'>\n"
        . "<option value='Y' selected='selected'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N'>".$clang->gT("No")."</option>\n"
        . "</select></li></ul>\n";

        // End Publication and access control TAB
        // Create Survey Button
        $newsurvey .= "<p><input type='button' onclick=\"if (isEmpty(document.getElementById('surveyls_title'), '".$clang->gT("Error: You have to enter a title for this survey.",'js')."')) { document.getElementById('addnewsurvey').submit(); }; return false;\" value='".$clang->gT("Save survey")."' /></p>\n";
        $newsurvey .= "</div>\n";

        // Notification and Data management TAB
        $newsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Notification & Data Management")."</h2><ul>\n";

        //NOTIFICATIONS
        $newsurvey .= "<li><label for='notification'>".$clang->gT("Admin Notification:")."</label>\n"
        . "<select id='notification' name='notification'>\n"
        . getNotificationlist(0)
        . "</select></li>\n";

        //EMAIL SURVEY RESPONSES TO
        $newsurvey .= "<li><label for='emailresponseto'>".$clang->gT("Email responses to:")."</label>\n"
        . "<input type='text' id='emailresponseto' name='emailresponseto' />\n"
        . "</li>\n";

        // ANONYMOUS
        $newsurvey .= "<li><label for='private'>".$clang->gT("Anonymous answers?")."\n";
        // warning message if anonymous + datestamped anwsers
        $newsurvey .= "\n"
        . "<script type=\"text/javascript\"><!-- \n"
        . "function alertPrivacy()\n"
        . "{"
        . "if (document.getElementById('private').value == 'Y')\n"
        . "{\n"
        . "alert('".$clang->gT("Warning").": ".$clang->gT("If you turn on the -Anonymous answers- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.","js")."');\n"
        . "}\n"
        . "}"
        . "//--></script></label>\n";
        $newsurvey .= "<select id='private' name='private' onchange='alertPrivacy();'>\n"
        . "<option value='Y' selected='selected'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N'>".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        // Datestamp
        $newsurvey .= "<li><label for='datestamp'>".$clang->gT("Date Stamp?")."</label>\n"
        . "<select id='datestamp' name='datestamp' onchange='alertPrivacy();'>\n"
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        // IP Address
        $newsurvey .= "<li><label for='ipaddr'>".$clang->gT("Save IP Address?")."</label>\n"
        . "<select id='ipaddr' name='ipaddr'>\n"
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        // Referring URL
        $newsurvey .= "<li><label for='refurl'>".$clang->gT("Save Referring URL?")."</label>\n"
        . "<select id='refurl' name='refurl'>\n"
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        // Token answers persistence
        $newsurvey .= "<li><label for='tokenanswerspersistence'>".$clang->gT("Enable token-based response persistence?")."</label>\n"
        . "<select id='tokenanswerspersistence' name='tokenanswerspersistence'>\n"
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        // enable assessment mote
        $newsurvey .= "<li><label for='assessments'>".$clang->gT("Enable assessment mode?")."</label>\n"
        . "<select id='assessments' name='assessments'>\n"
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "</select></li></ul>\n";


        // end of addnewsurvey form
        $newsurvey .= "<input type='hidden' name='action' value='insertnewsurvey' />\n";

        // End Notification and Data management TAB
        // Create Survey Button
        $newsurvey .= "<p><input type='button' onclick=\"if (isEmpty(document.getElementById('surveyls_title'), '".$clang->gT("Error: You have to enter a title for this survey.",'js')."')) { document.getElementById('addnewsurvey').submit(); }; return false;\" value='".$clang->gT("Save survey")."' /></p>\n";
        $newsurvey .= "</div>\n";
        $newsurvey .= "</form>\n";

        // Import TAB
        $newsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Import survey")."</h2>\n";

        // Import Survey
        $newsurvey .= "<form enctype='multipart/form-data' class='form30' id='importsurvey' name='importsurvey' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
        . "<ul>\n"
        . "<li><label for='the_file'>".$clang->gT("Select survey structure file (*.lss, *.csv):")."</label>\n"
        . "<input id='the_file' name=\"the_file\" type=\"file\" size=\"50\" /></li>\n"
        . "<li><label for='translinksfields'>".$clang->gT("Convert resource links and INSERTANS fields?")."</label>\n"
        . "<input id='translinksfields' name=\"translinksfields\" type=\"checkbox\" checked='checked'/></li></ul>\n"
        . "<p><input type='submit' value='".$clang->gT("Import survey")."' />\n"
        . "<input type='hidden' name='action' value='importsurvey' /></p></form>\n";

        // End Import TAB
        $newsurvey .= "</div>\n";

        // Copy survey TAB
        $newsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Copy survey")."</h2>\n";

        // Copy survey
        $newsurvey .= "<form class='form30' action='$scriptname' id='copysurveyform' method='post'>\n"
        . "<ul>\n"
        . "<li><label for='copysurveylist'><span class='annotationasterisk'>*</span>".$clang->gT("Select survey to copy:")."</label>\n"
        . "<select id='copysurveylist' name='copysurveylist'>\n"
        . getsurveylist(false,true)."</select> <span class='annotation'>".$clang->gT("*Required")."</span></li>\n"
        . "<li><label for='copysurveyname'><span class='annotationasterisk'>*</span>".$clang->gT("New survey title:")."</label>\n"
        . "<input type='text' id='copysurveyname' size='82' maxlength='200' name='copysurveyname' value='' />"
        . " <span class='annotation'>".$clang->gT("*Required")."</span></li>\n"
        . "<li><label for='copysurveyexcludequotas'>".$clang->gT("Exclude quotas?")."</label>\n"
        . "<input id='copysurveyexcludequotas' name=\"copysurveyexcludequotas\" type=\"checkbox\" /></li>\n"
        . "<li><label for='copysurveyexcludeanswers'>".$clang->gT("Exclude answers?")."</label>\n"
        . "<input id='copysurveyexcludeanswers' name=\"copysurveyexcludeanswers\" type=\"checkbox\" /></li>\n"
        . "<li><label for='copysurveyresetconditions'>".$clang->gT("Reset conditions?")."</label>\n"
        . "<input id='copysurveyresetconditions' name=\"copysurveyresetconditions\" type=\"checkbox\" /></li></ul>\n"
        . "<p><input type='submit' value='".$clang->gT("Copy survey")."' />\n"
        . "<input type='hidden' name='action' value='copysurvey' /></p></form>\n";

        // End Copy survey TAB
        $newsurvey .= "</div>\n";
        
        // End TAB pane
        $newsurvey .= "</div>\n";

    }
    else
    {
        include("access_denied.php");
    }
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
 * @global string $imagefiles
 * @return string $adminmenu
 */
function showadminmenu()
{
    global $homedir, $scriptname, $surveyid, $setfont, $imagefiles, $clang, $debug, $action, $updateavailable, $updatebuild, $updateversion, $updatelastcheck;

    $adminmenu  = "<div class='menubar'>\n";
    if  ($_SESSION['pw_notify'] && $debug<2)  {$adminmenu .="<div class='alert'>".$clang->gT("Warning: You are still using the default password ('password'). Please change your password and re-login again.")."</div>";}
    $adminmenu  .="<div class='menubar-title'>\n"
    . "<div class='menubar-title-left'>\n"
    . "<strong>".$clang->gT("Administration")."</strong>";
    if(isset($_SESSION['loginID']))
    {
        $adminmenu  .= " --  ".$clang->gT("Logged in as:"). " <strong>"
        . "<a href=\"#\" onclick=\"window.open('$scriptname?action=personalsettings', '_top')\" title=\"".$clang->gTview("Edit your personal preferences")."\" >"
        . $_SESSION['user']." <img src='$imagefiles/profile_edit.png' name='ProfileEdit' alt='".$clang->gT("Edit your personal preferences")."' /></a>"
        . "</strong>\n";
    }
    $adminmenu  .="</div>\n";
    if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 && isset($updatelastcheck) && $updatelastcheck>0 && isset($updateavailable) && $updateavailable==1)
    {
        $adminmenu  .="<div class='menubar-title-right'><a href='$scriptname?action=globalsettings'>".sprintf($clang->gT('Update available: %s'),$updateversion."($updatebuild)").'</a></div>';
    }
    $adminmenu .= "</div>\n"
    . "<div class='menubar-main'>\n"
    . "<div class='menubar-left'>\n"
    . "<a href=\"#\" onclick=\"window.open('$scriptname', '_top')\" title=\"".$clang->gTview("Default Administration Page")."\">"
    . "<img src='$imagefiles/home.png' name='HomeButton' alt='".$clang->gT("Default Administration Page")."' /></a>\n";

    $adminmenu .= "<img src='$imagefiles/blank.gif' alt='' width='11' />\n"
    . "<img src='$imagefiles/seperator.gif' alt='' />\n";

    // Edit users
    $adminmenu .="<a href=\"#\" onclick=\"window.open('$scriptname?action=editusers', '_top')\" title=\"".$clang->gTview("Create/Edit Users")."\" >"
    ."<img src='$imagefiles/security.png' name='AdminSecurity' alt='".$clang->gT("Create/Edit Users")."' /></a>";

    $adminmenu .="<a href=\"#\" onclick=\"window.open('$scriptname?action=editusergroups', '_top')\" title=\"".$clang->gTview("Create/Edit Groups")."\" >"
    ."<img src='$imagefiles/usergroup.png' alt='".$clang->gT("Create/Edit Groups")."' /></a>\n" ;

    if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        $adminmenu .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=globalsettings', '_top')\" title=\"".$clang->gTview("Global settings")."\" >"
        . "<img src='$imagefiles/global.png' name='GlobalSettings' alt='". $clang->gT("Global settings")."' /></a>"
        . "<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' />\n";
    }
    // Check data integrity
    if($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1)
    {
        $adminmenu .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=checkintegrity', '_top')\" title=\"".$clang->gTview("Check Data Integrity")."\">".
                      "<img src='$imagefiles/checkdb.png' name='CheckDataIntegrity' alt='".$clang->gT("Check Data Integrity")."' /></a>\n";
    }
    else
    {
        $adminmenu .= "<img src='$imagefiles/blank.gif' alt='' width='40'  />\n";
    }

    // list surveys
    $adminmenu .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=listsurveys', '_top')\" title=\"".$clang->gTview("List Surveys")."\" >\n"
    ."<img src='$imagefiles/surveylist.png' name='ListSurveys' alt='".$clang->gT("List Surveys")."' onclick=\"window.open('$scriptname?action=listsurveys', '_top')\" />"
    ."</a>" ;

    // db backup & label editor
    if($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1)
    {
        $adminmenu  .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=dumpdb', '_top')\" title=\"".$clang->gTview("Backup Entire Database")."\">\n"
        ."<img src='$imagefiles/backup.png' name='ExportDB' alt='". $clang->gT("Backup Entire Database")."' />"
        ."</a>\n"
        ."<img src='$imagefiles/seperator.gif' alt=''  border='0' hspace='0' />\n";
    }
    else
    {
        $adminmenu .= "<img src='$imagefiles/blank.gif' alt='' width='40'   />\n";
    }
    if($_SESSION['USER_RIGHT_MANAGE_LABEL'] == 1)
    {
        $adminmenu  .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=labels', '_top')\" title=\"".$clang->gTview("Edit label sets")."\">\n"
        ."<img src='$imagefiles/labels.png'  name='LabelsEditor' alt='". $clang->gT("Edit label sets")."' /></a>\n"
        ."<img src='$imagefiles/seperator.gif' alt=''  border='0' hspace='0' />\n";
    }
    else
    {
        $adminmenu .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
    }
    if($_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] == 1)
    {
        $adminmenu .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=templates', '_top')\" title=\"".$clang->gTview("Template Editor")."\" >"
        ."<img src='$imagefiles/templates.png' name='EditTemplates' title='' alt='". $clang->gT("Template Editor")."' /></a>\n";
    }
    // survey select box
    $adminmenu .= "</div><div class='menubar-right'><span class=\"boxcaption\">".$clang->gT("Surveys").":</span>"
    . "<select onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n"
    . getsurveylist()
    . "</select>\n";

    if($_SESSION['USER_RIGHT_CREATE_SURVEY'] == 1)
    {
        $adminmenu .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=newsurvey', '_top')\""
        ."title=\"".$clang->gTview("Create, import, or copy a survey")."\" >"
        ."<img src='$imagefiles/add.png' name='AddSurvey' title='' alt='". $clang->gT("Create, import, or copy a survey")."' /></a>\n";
    }


    if(isset($_SESSION['loginID'])) //ADDED to prevent errors by reading db while not logged in.
    {
        // Logout
        $adminmenu .= "<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' />"
        . "<a href=\"#\" onclick=\"window.open('$scriptname?action=logout', '_top')\" title=\"".$clang->gTview("Logout")."\" >"
        . "<img src='$imagefiles/logout.png' name='Logout' alt='".$clang->gT("Logout")."'/></a>";

        //Show help
        $adminmenu .= "<a href=\"http://docs.limesurvey.org\" target='_blank' title=\"".$clang->gTview("LimeSurvey Online manual")."\" >"
        . "<img src='$imagefiles/showhelp.png' name='ShowHelp' alt='". $clang->gT("LimeSurvey Online manual")."'/></a>";

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
            .'<li>'.sprintf($clang->gT('Create a new survey clicking on the %s icon in the upper right.'),"<img src='$imagefiles/add_small.png' name='ShowHelp' title='' alt='". $clang->gT("Add survey")."'/>").'</li>'
            .'<li>'.$clang->gT('Create a new question group inside your survey.').'</li>'
            .'<li>'.$clang->gT('Create one or more questions inside the new question group.').'</li>'
            .'<li>'.sprintf($clang->gT('Done. Test your survey using the %s icon.'),"<img src='$imagefiles/do_small.png' name='ShowHelp' title='' alt='". $clang->gT("Test survey")."'/>").'</li>'
            .'</ol></p><br />&nbsp;</div>';
        }

    }
    return $adminmenu;
}


function browsemenubar($title='')
{
    global $surveyid, $scriptname, $imagefiles, $homeurl, $clang, $sumrows5, $surrows;

    $thissurvey=getSurveyInfo($surveyid);
    //BROWSE MENU BAR
    $browsemenubar = "<div class='menubar'>\n"
    . "<div class='menubar-title'>\n"
    . "<strong>$title</strong>: ({$thissurvey['name']})"
    . "</div>"
    . "<div class='menubar-main'>\n"
    . "<div class='menubar-left'>\n"
    //Return to survey administration
    . "<a href='$scriptname?sid=$surveyid' title=\"".$clang->gTview("Return to survey administration")."\" >"
    . "<img name='Administration' src='$imagefiles/home.png' title='' alt='".$clang->gT("Return to survey administration")."' /></a>\n"
    . "<img src='$imagefiles/blank.gif' alt='' width='11' />\n"
    . "<img src='$imagefiles/seperator.gif' alt='' />\n"
    //Show summary information
    . "<a href='$scriptname?action=browse&amp;sid=$surveyid' title=\"".$clang->gTview("Show summary information")."\" >"
    . "<img name='SurveySummary' src='$imagefiles/summary.png' title='' alt='".$clang->gT("Show summary information")."' /></a>\n";

    //Display responses
    if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
    {
        $browsemenubar .="<a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=all' title=\"".$clang->gTview("Display Responses")."\" >" .
        "<img name='ViewAll' src='$imagefiles/document.png' title='' alt='".$clang->gT("Display Responses")."' /></a>\n";
    }
    else
    {
        $browsemenubar .= "<a href=\"#\" accesskey='b' onclick=\"document.getElementById('browsepopup').style.visibility='visible';\""
        . "title=\"".$clang->gTview("Display Responses")."\" >"
        ."<img src='$imagefiles/document.png' alt='".$clang->gT("Display Responses")."' name='ViewAll' /></a>";

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
    }

    // Display last 50 responses
    $browsemenubar .= "<a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=all&amp;limit=50&amp;order=desc'" .
                    " title=\"".$clang->gTview("Display Last 50 Responses")."\" >" .
                    "<img name='ViewLast' src='$imagefiles/viewlast.png' alt='".$clang->gT("Display Last 50 Responses")."' /></a>\n";
    // Data entry
    $browsemenubar .= "<a href='$scriptname?action=dataentry&amp;sid=$surveyid'".
                    " title=\"".$clang->gTview("Dataentry Screen for Survey")."\" >" .
                    "<img name='DataEntry' src='$imagefiles/dataentry.png' alt='".$clang->gT("Dataentry Screen for Survey")."' /></a>\n";
    // Statistics
    $browsemenubar .= "<a href='$scriptname?action=statistics&amp;sid=$surveyid' "
    ."title=\"".$clang->gTview("Get statistics from these responses")."\" >"
    ."<img name='Statistics' src='$imagefiles/statistics.png' alt='".$clang->gT("Get statistics from these responses")."' /></a>\n";

    $browsemenubar .= "<img src='$imagefiles/seperator.gif' alt='' />\n";

    if ($sumrows5['export'] == "1" || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        // Export to application
        $browsemenubar .= "<a href='$scriptname?action=exportresults&amp;sid=$surveyid' title=\"".$clang->gTview("Export Results to Application")."\" >"
        . "<img name='Export' src='$imagefiles/export.png' "
        . "alt='".$clang->gT("Export Results to Application")."' /></a>\n"
        
        // Export to SPSS
        . "<a href='$scriptname?action=exportspss&amp;sid=$surveyid' title=\"".$clang->gTview("Export results to a SPSS/PASW command file")."\" >"
        . "<img src='$imagefiles/exportspss.png' "
        . "alt='". $clang->gT("Export results to a SPSS/PASW command file")."' /></a>\n"
        
        // Export to R
        . "<a href='$scriptname?action=exportr&amp;sid=$surveyid' title=\"".$clang->gTview("Export results to a R data file")."\" >"
        . "<img src='$imagefiles/exportr.png' "
        . "alt='". $clang->gT("Export results to a R data file")."' /></a>\n";
    }
    //Import old response table
    $browsemenubar .= "<a href='$scriptname?action=importoldresponses&amp;sid=$surveyid' title=\"".$clang->gTview("Import answers from a deactivated survey table")."\" >"
    . "<img name='ImportOldResponses' src='$imagefiles/importold.png' alt='".$clang->gT("Import answers from a deactivated survey table")."' /></a>\n";

    $browsemenubar .= "<img src='$imagefiles/seperator.gif' alt='' />\n";

    //browse saved responses
    $browsemenubar .= "<a href='$scriptname?action=saved&amp;sid=$surveyid' title=\"".$clang->gTview("View Saved but not submitted Responses")."\" >"
    . "<img src='$imagefiles/saved.png' title='' alt='".$clang->gT("View Saved but not submitted Responses")."' name='BrowseSaved' /></a>\n"

    //Import VV
    . "<a href='$scriptname?action=vvimport&amp;sid=$surveyid' title=\"".$clang->gTview("Import a VV survey file")."\" >"
    . "<img src='$imagefiles/importvv.png' alt='".$clang->gT("Import a VV survey file")."' /></a>\n";

    //Export VV
    if ($sumrows5['export'] == "1" || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        $browsemenubar .= "<a href='$scriptname?action=vvexport&amp;sid=$surveyid' title=\"".$clang->gTview("Export a VV survey file")."\" >"
        ."<img src='$imagefiles/exportvv.png' title='' alt='".$clang->gT("Export a VV survey file")."' /></a>\n";
    }

    //Iterate survey
    if (( ($surrows['browse_response'] && $surrows['activate_survey']) ||
    $_SESSION['USER_RIGHT_SUPERADMIN'] == 1
    ) &&
    (
    $thissurvey['private'] == 'N' &&
    $thissurvey['tokenanswerspersistence'] == 'Y'
    ))
    {
        $browsemenubar .= "<a href='$scriptname?action=iteratesurvey&amp;sid=$surveyid' title=\"".$clang->gTview("Iterate surevey")."\" >"
        ."<img src='$imagefiles/iterate.png' title='' alt='".$clang->gT("Iterate surevey")."' /></a>\n";
    }
    $browsemenubar .= "</div>\n"
    . "\t</div>\n"
    . "</div>\n";

    return $browsemenubar;
}
