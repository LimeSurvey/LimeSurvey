<?php

include_once("login_check.php");

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
            if($rows['anonymized']=="Y")
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
					    "<td>".$ownername." (<a href='#' class='ownername_edit' translate_to='".$clang->gT("Update")."' id='ownername_edit_{$rows['sid']}'>".$clang->gT("Edit")."</a>)</td>".
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
elseif ($action == "ajaxowneredit"){

    header('Content-type: application/json');
    
    if (isset($_REQUEST['newowner'])) {$intNewOwner=sanitize_int($_REQUEST['newowner']);}
    if (isset($_REQUEST['survey_id'])) {$intSurveyId=sanitize_int($_REQUEST['survey_id']);}
    $owner_id = $_SESSION['loginID'];

    header('Content-type: application/json');
    
    $query = "UPDATE ".db_table_name('surveys')." SET owner_id = $intNewOwner WHERE sid=$intSurveyId";
    if (bHasGlobalPermission("USER_RIGHT_SUPERADMIN"))
        $query .=";";
    else
        $query .=" AND owner_id=$owner_id;";

    $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());

    $query = "SELECT b.users_name FROM ".db_table_name('surveys')." as a"
    ." INNER JOIN  ".db_table_name('users')." as b ON a.owner_id = b.uid   WHERE sid=$intSurveyId AND owner_id=$intNewOwner;";
    $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
    $intRecordCount = $result->RecordCount();
    
    $aUsers = array(
        'record_count' => $intRecordCount,
    );

    if($result->RecordCount() > 0) {
        while($rows = $result->FetchRow())
                $aUsers['newowner'] = $rows['users_name'];
    }
    $ajaxoutput = json_encode($aUsers) . "\n";
    
}

elseif ($action == "ajaxgetusers"){
    header('Content-type: application/json');

    $query = "SELECT users_name, uid FROM ".db_table_name('users').";";

    $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());

    $aUsers = array();
    if($result->RecordCount() > 0) {
        while($rows = $result->FetchRow())
                $aUsers[] = array($rows['uid'], $rows['users_name']);
    }
    
    $ajaxoutput = json_encode($aUsers) . "\n";
}

