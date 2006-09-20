<?php
/*
	#############################################################
	# >>> PHPSurveyor  								    		#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA					#
 	# > Date: 	 20 February 2003								#
	#															#
	# This set of scripts allows you to develop, publish and	#
	# perform data-entry on surveys.							#
	#############################################################
	#															#
	#	Copyright (C) 2003  Jason Cleeland						#
	#															#
	# This program is free software; you can redistribute 		#
	# it and/or modify it under the terms of the GNU General 	#
	# Public License as published by the Free Software 			#
	# Foundation; either version 2 of the License, or (at your 	#
	# option) any later version.								#
	#															#
	# This program is distributed in the hope that it will be 	#
	# useful, but WITHOUT ANY WARRANTY; without even the 		#
	# implied warranty of MERCHANTABILITY or FITNESS FOR A 		#
	# PARTICULAR PURPOSE.  See the GNU General Public License 	#
	# for more details.											#
	#															#
	# You should have received a copy of the GNU General 		#
	# Public License along with this program; if not, write to 	#
	# the Free Software Foundation, Inc., 59 Temple Place - 	#
	# Suite 330, Boston, MA  02111-1307, USA.					#
	#############################################################	
*/

if (empty($homedir)) {die("Cannot run this script directly (access_denied)");}
if ($accesscontrol <> 1) {exit;}

if (isset($_SESSION['loginID']))
	{
	
	$accesssummary = "<br /><strong>"._("Access denied!")."</strong><br />\n";
	/*if(returnglobal('action') == "edituser")
		{
		$accesssummary .= _("You are not allowed to change User Data!");
		$accesssummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
		} 
	else
	*/
	if(returnglobal('action') == "newsurvey")
		{		
		$accesssummary .= _("<p>You are not allowed to create new surveys!</p>");
		$accesssummary .= "<a href='$scriptname'>"._("Continue")."</a><br />&nbsp;\n";
		}	
	elseif(returnglobal('action') == "delsurvey" || $action == "delsurvey")
		{		
		$accesssummary .= _("<p>You are not allowed to delete this survey!</p>");
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>"._("Continue")."</a><br />&nbsp;\n";
		}
	elseif(returnglobal('action') == "addquestion")
		{		
		$accesssummary .= _("<p>You are not allowed to add new questions for this survey!</p>");
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>"._("Continue")."</a><br />&nbsp;\n";
		}
	elseif(returnglobal('action') == "activate")
		{		
		$accesssummary .= _("<p>You are not allowed to activate this survey!</p>");
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>"._("Continue")."</a><br />&nbsp;\n";
		}
	elseif(returnglobal('action') == "deactivate")
		{		
		$accesssummary .= _("<p>You are not allowed to deactivate this survey!</p>");
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>"._("Continue")."</a><br />&nbsp;\n";
		}
	
	
	
	elseif(returnglobal('action') == "addgroup")
		{		
		$accesssummary .= _("<p>You are not allowed to add a group to this survey!</p>");
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>"._("Continue")."</a><br />&nbsp;\n";
		}
	elseif(returnglobal('action') == "ordergroups")
		{		
		$accesssummary .= _("<p>You are not allowed to order groups in this survey!</p>");
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>"._("Continue")."</a><br />&nbsp;\n";
		}
	elseif(returnglobal('action') == "editsurvey")
		{		
		$accesssummary .= _("<p>You are not allowed to edit this survey!</p>");
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>"._("Continue")."</a><br />&nbsp;\n";
		}
	elseif(returnglobal('action') == "editgroup")
		{		
		$accesssummary .= _("<p>You are not allowed to edit groups in this survey!</p>");
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>"._("Continue")."</a><br />&nbsp;\n";
		}
	
	
	elseif($action == "browse_response")
		{		
		$accesssummary .= _("<p>You are not allowed to browse response!</p>");
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>"._("Continue")."</a><br />&nbsp;\n";
		}
	elseif($action == "assessment")
		{		
		$accesssummary .= _("<p>You are not allowed to set assessment rules!</p>");
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>"._("Continue")."</a><br />&nbsp;\n";
		}


	elseif($action == "delusergroup")
		{		
		$accesssummary .= _("<p>You are not allowed to delete this group!</p>");
		$accesssummary .= "<a href='$scriptname?action=editusergroups'>"._("Continue")."</a><br />&nbsp;\n";
		}

	
	/*elseif(returnglobal('action') == "importsurvey")
		{		
		$accesssummary .= _("<p>You are not allowed to import a survey!</p>");
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>"._("Continue")."</a><br />&nbsp;\n";
		}	
	elseif(returnglobal('action') == "importgroup")
		{		
		$accesssummary .= _("<p>You are not allowed to import a group!</p>");
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>"._("Continue")."</a><br />&nbsp;\n";
		}
	elseif(returnglobal('action') == "importquestion")
		{		
		$accesssummary .= _("<p>You are not allowed to to import a question!</p>");
		$accesssummary .= "<a href='$scriptname?sid={$sid}'>"._("Continue")."</a><br />&nbsp;\n";
		}*/
		
	else
		{
		$accesssummary .= "<br />"._("You are not allowed to perform this operation!")."<br />\n";		
		if($sid)
			$accesssummary .= "<br /><br /><a href='$scriptname?sid=$sid&action=surveysecurity'>"._("Continue")."</a><br />&nbsp;\n";
		elseif($ugid)
		//elseif(isset($_GET['ugid']))
			{
			$accesssummary .= "<br /><br /><a href='$scriptname?action=editusergroups&ugid={$ugid}'>"._("Continue")."</a><br />&nbsp;\n";
					}
		else 
			$accesssummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
		}
		
	}
?>