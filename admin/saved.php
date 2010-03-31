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

include_once("login_check.php");

$surveyid=returnglobal('sid');
$srid=returnglobal('srid');
$scid=returnglobal('scid');
$subaction=returnglobal('subaction');

//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}

$thissurvey=getSurveyInfo($surveyid);
$savedsurveyoutput='';
$surveytable = db_table_name("survey_".$surveyid);

if ($subaction == "delete" && $surveyid && $scid)
{
    $query = "DELETE FROM {$dbprefix}saved_control
			  WHERE scid=$scid
			  AND sid=$surveyid
			  ";
    if ($result = $connect->Execute($query))
    {
        //If we were succesful deleting the saved_control entry,
        //then delete the rest
        $query = "DELETE FROM {$surveytable} WHERE id={$srid}";
        $result = $connect->Execute($query) or die("Couldn't delete");

    }
    else
    {
        $savedsurveyoutput .=  "Couldn't delete<br />$query<br />".$connect->ErrorMsg();
    }
}
$js_adminheader_includes[]='../scripts/jquery/jquery.tablesorter.min.js';
$js_adminheader_includes[]='scripts/saved.js';

$savedsurveyoutput .= "<div class='menubar'>\n"
. "<div class='menubar-title'><span style='font-weight:bold;'>\n";
$savedsurveyoutput .= $clang->gT("Saved Responses")."</span> ".$thissurvey['name']." (ID: $surveyid)</div>\n"
. "<div class='menubar-main'>\n"
. "<div class='menubar-left'>\n";
$savedsurveyoutput .= savedmenubar();
$savedsurveyoutput .= "</div></div></div>\n";
$savedsurveyoutput .= "<div class='header'>".$clang->gT("Saved Responses:") . " ". getSavedCount($surveyid)."</div><p>";

showSavedList($surveyid);



function showSavedList($surveyid)
{
    global $dbprefix, $connect, $clang, $savedsurveyoutput, $scriptname, $imagefiles, $surrows;
    $query = "SELECT scid, srid, identifier, ip, saved_date, email, access_code\n"
    ."FROM {$dbprefix}saved_control\n"
    ."WHERE sid=$surveyid\n"
    ."ORDER BY saved_date desc";
    $result = db_execute_assoc($query) or safe_die ("Couldn't summarise saved entries<br />$query<br />".$connect->ErrorMsg());
    if ($result->RecordCount() > 0)
    {
        $savedsurveyoutput .= "<table class='browsetable' align='center'>\n";
        $savedsurveyoutput .= "<thead><tr><th>SCID</th><th>"
        .$clang->gT("Actions")."</th><th>"
        .$clang->gT("Identifier")."</th><th>"
        .$clang->gT("IP Address")."</th><th>"
        .$clang->gT("Date Saved")."</th><th>"
        .$clang->gT("Email Address")."</th>"
        ."</tr></thead><tbody>\n";
        while($row=$result->FetchRow())
        {
            $savedsurveyoutput .= "<tr>
				<td>".$row['scid']."</td>
				<td align='center'>";

            if (($surrows['delete_survey'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1))
            {
                $savedsurveyoutput .="<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='$imagefiles/token_edit.png' title='"
                .$clang->gT("Edit entry")."' onclick=\"window.open('$scriptname?action=dataentry&amp;sid=$surveyid&amp;subaction=edit&amp;id={$row['srid']}', '_top')\" />"
                ."<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='$imagefiles/token_delete.png' title='"
                .$clang->gT("Delete entry")."' onclick=\"if (confirm('".$clang->gT("Are you sure you want to delete this entry?","js")."')) {".get2post("$scriptname?action=saved&amp;sid=$surveyid&amp;subaction=delete&amp;scid={$row['scid']}&amp;srid={$row['srid']}")."}\"  />";


                /*                    $savedsurveyoutput .=  "[<a href='$scriptname?action=saved&amp;sid=$surveyid&amp;subaction=delete&amp;scid={$row['scid']}&amp;srid={$row['srid']}'"
                 ." onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")'"
                 .">".$clang->gT("Delete")."</a>]";
                 $savedsurveyoutput .=  "[<a href='".$scriptname."?action=dataentry&amp;subaction=edit&amp;id=".$row['srid']."&amp;sid={$surveyid}&amp;surveytable={$surveytable}'>".$clang->gT("Edit")."</a>]";
                 */
            }
            else
            {
                $savedsurveyoutput .=  "[<a href='".$scriptname."?action=dataentry&amp;subaction=edit&amp;id=".$row['srid']."&amp;sid={$surveyid}'>".$clang->gT("View")."</a>]";

            }

            $savedsurveyoutput .="</td>
                <td>".$row['identifier']."</td>
                <td>".$row['ip']."</td>
                <td>".$row['saved_date']."</td>
                <td><a href='mailto:".$row['email']."'>".$row['email']."</td>
               
			   </tr>\n";
        } // while
        $savedsurveyoutput .= "</tbody></table><br />&nbsp\n";
    }
}

//				[<a href='saved.php?sid=$surveyid&amp;action=remind&amp;scid=".$row['scid']."'>".$clang->gT("Remind")."</a>]
//               c_schmitz: Since its without function at the moment i removed it from the above lines

function savedmenubar()
{
    global $surveyid, $scriptname, $imagefiles, $clang;
    //BROWSE MENU BAR
    if (!isset($surveyoptions)) {$surveyoptions="";}
    $surveyoptions .= "<a href='$scriptname?sid=$surveyid' title='".$clang->gTview("Return to survey administration")."' >" .
			"<img name='Administration' src='$imagefiles/home.png' alt='".$clang->gT("Return to survey administration")."' align='left'></a>\n";
    /*	. "\t\t\t<img src='$imagefiles/blank.gif' alt='' width='11' border='0' hspace='0' align='left'>\n"
     . "\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
     . "\t\t\t<a href='$scriptname?action=saved&amp;sid=$surveyid' " .
     "title='".$clang->gTview("Show summary information")."'>" .
     "<img name='SurveySummary' src='$imagefiles/summary.png' alt='".$clang->gT("Show summary information")."' align='left'></a>\n"
     . "\t\t\t<a href='$scriptname?action=saved&amp;sid=$surveyid&amp;subaction=all' title='".$clang->gTview("Display Responses")."'>"
     . "<img name='ViewAll' src='$imagefiles/document.png' alt='".$clang->gT("Display Responses")."' align='left'></a>\n"
     //. "\t\t\t<input type='image' name='ViewLast' src='$imagefiles/viewlast.png' title='"
     //. $clang->gT("Display Last 50 Responses")."'  align='left'  onclick=\"window.open('saved.php?sid=$surveyid&action=all&limit=50&order=desc', '_top')\">\n"
     . "\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt=''>\n";*/
    return $surveyoptions;
}
?>
