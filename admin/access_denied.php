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
*/


if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly (access_denied)");}
if ($accesscontrol <> 1) {exit;}

if (isset($_SESSION['loginID']))
	{
	
	$accesssummary = "<br /><strong>".$clang->gT("Access denied!")."</strong><br />\n";
	/*if(returnglobal('action') == "edituser")
		{
		$accesssummary .= $clang->gT("You are not allowed to change User Data!");
		$accesssummary .= "<br /><br /><a href='$scriptname?action=editusers'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		} 
	else
	*/
	if(returnglobal('action') == "newsurvey")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to create new surveys!")."</p>";
		$accesssummary .= "<a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}	
	elseif(returnglobal('action') == "delsurvey" || $action == "delsurvey")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to delete this survey!")."</p>";
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	elseif(returnglobal('action') == "addquestion")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to add new questions for this survey!")."</p>";
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	elseif(returnglobal('action') == "activate")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to activate this survey!")."</p>";
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	elseif(returnglobal('action') == "deactivate")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to deactivate this survey!")."</p>";
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	
	
	
	elseif(returnglobal('action') == "addgroup")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to add a group to this survey!")."</p>";
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	elseif(returnglobal('action') == "ordergroups")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to order groups in this survey!")."</p>";
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	elseif(returnglobal('action') == "editsurvey")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to edit this survey!")."</p>";
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	elseif(returnglobal('action') == "editgroup")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to edit groups in this survey!")."</p>";
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	
	
	elseif($action == "browse_response")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to browse response!")."</p>";
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	elseif($action == "assessment")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to set assessment rules!")."</p>";
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}


	elseif($action == "delusergroup")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to delete this group!")."</p>";
		$accesssummary .= "<a href='$scriptname?action=editusergroups'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	elseif($action == "importsurvey")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to import a survey!")."</p>";
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}	

	/*elseif(returnglobal('action') == "importgroup")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to import a group!")."</p>";
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	elseif(returnglobal('action') == "importquestion")
		{		
		$accesssummary .= "<p>".$clang->gT("You are not allowed to to import a question!")."</p>";
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}*/
		
	else
		{
		$accesssummary .= "<br />".$clang->gT("You are not allowed to perform this operation!")."<br />\n";		
		if(!empty($sid))
			$accesssummary .= "<br /><br /><a href='$scriptname?sid=$sid&amp;action=surveysecurity'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		elseif(!empty($ugid))
		//elseif(isset($_GET['ugid']))
			{
			$accesssummary .= "<br /><br /><a href='$scriptname?action=editusergroups&ugid={$ugid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
					}
		else 
			$accesssummary .= "<br /><br /><a href='$scriptname?action=editusers'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
		
	}
?>
