<?php
/*
	#############################################################
	# >>> PHPSurveyor       									#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA
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
//require_once(dirname(__FILE__).'/../config.php');

//Ensure script is not run directly, avoid path disclosure
if (empty($homedir)) {die ("Cannot run this script directly (navigator.php)");}

require_once("{$homedir}/classes/TreeMenu/TreeMenuXL.php");

$scriptname="index.php"; //REMOVE WHEN FINALLY INTEGRATED - THIS JUST OVERRIDES THE 'admin.php' setting

if (!isset($_SESSION['noquestions'])) {$_SESSION['noquestions']="noquestions";}
if (isset($_GET['noquestions'])) {$_SESSION['noquestions']=$_GET['noquestions'];} 
elseif (isset($_GET['changenavdisplay'])) {$_SESSION['noquestions']="";}

function surveyNavigator($surveyid, $gid, $qid) {
	global $homedir, $scriptname, $imagefiles, $navigation;
	
	if (!isset($_SESSION['java_exclude']) || $_SESSION['java_exclude'] != $surveyid) {
		unset ($_SESSION['java_allsurveys']);
	}

	if (isset($surveyid) && $surveyid) {
	    $menu01 = new HTML_TreeMenuXL();
		$nodeProperties = array("icon"=>"folder.gif", "cssClass"=>"auto", "autostyles"=>array("smalltextBold", "smallitalic", "smalltext"));
		$nodePropertiesActive = array("icon"=>"activefolder.gif", "cssClass"=>"auto", "autostyles"=>array("smalltextBold", "smallitalic", "smalltext"));
		$questionProperties = array("icon"=>"document.png", "cssClass"=>"auto", "autostyles"=>array("smalltextBold", "smallitalic", "smalltext"));
		foreach(getSurveysBrief(null, $surveyid) as $toplevel) {
			if ($toplevel['active']=="Y") { $nodeProp=$nodePropertiesActive;} else {$nodeProp=$nodeProperties;}
			$text=makeJavaSafe($toplevel['title'], 35);
			$node01=new HTML_TreeNodeXL($text, "$scriptname?sid=".$toplevel['sid'], $nodeProp);
			$m=0;
			foreach(getGroupsBrief($toplevel['sid']) as $grouplevel) {
				$nxgroups="node01_group_".$m;
				$text=makeJavaSafe($grouplevel['group_name'], 37);
				$$nxgroups = &$node01->addItem(new HTML_TreeNodeXL($text, "$scriptname?sid=$surveyid&amp;gid=".$grouplevel['gid'], $nodeProperties));
				$n=0;
				foreach(getQuestionsBrief($grouplevel['gid']) as $questionlevel) {
					$nxquestions=$nxgroups."_".$n;
					$text=makeJavaSafe($questionlevel['question'], 27);
					$$nxquestions = &${$nxgroups}->addItem(new HTML_TreeNodeXL($questionlevel['title'].": ".$text, "$scriptname?sid=$surveyid&amp;gid=".$grouplevel['gid']."&amp;qid=".$questionlevel['qid'], $questionProperties));
					$n++;
				}
			$m++;
			}
		$menu01->addItem($node01);
		}	
		$surveyidmenu= &new HTML_TreeMenu_DHTMLXL($menu01, array("images"=>"classes/TreeMenu/TMimages"));	
		//$surveyidmenu->printMenu();
		$_SESSION['java_thissurvey']=$surveyidmenu->toHTML();
	}
	
	if (!isset($_SESSION['java_allsurveys']) || isset($_GET['changenavdisplay'])) {
		$menu00  = new HTML_TreeMenuXL();
		$nodeProperties = array("icon"=>"folder.gif", "cssClass"=>"auto", "autostyles"=>array("smalltextBold", "smallitalic", "smalltext"));
		$nodePropertiesActive = array("icon"=>"activefolder.gif", "cssClass"=>"auto", "autostyles"=>array("smalltextBold", "smallitalic", "smalltext"));
		
		$i=0;
		//$node0 = new HTML_TreeNodeXL("Surveys", "#", $nodeProperties);
		foreach(getSurveysBrief(null, null, $surveyid) as $toplevel) {
			$nodename="node".$i;
			$text=makeJavaSafe($toplevel['title'], 35);
			$title=makeJavaSafe($toplevel['title'], 150);
			//echo "<pre>"; print_r($toplevel); echo "</pre>";
			if ($toplevel['active']=="Y") { $nodeProp=$nodePropertiesActive;} else {$nodeProp=$nodeProperties;}
			$$nodename = new HTML_TreeNodeXL($text, "$scriptname?sid=".$toplevel['sid'], $nodeProp);
			//$nx = &$node0->addItem(new HTML_TreeNodeXL($toplevel['title'], "#", $nodeProperties));
			$k=0;
			foreach(getGroupsBrief($toplevel['sid']) as $grouplevel) {
				$nxgroups=$nodename."_group_".$k;
				$text=makeJavaSafe($grouplevel['group_name'], 37);
				$$nxgroups = &${$nodename}->addItem(new HTML_TreeNodeXL($text, "$scriptname?sid=".$toplevel['sid']."&amp;gid=".$grouplevel['gid'], $nodeProperties));
				$l=0;
				if ($_SESSION['noquestions'] != "noquestions") {
					foreach(getQuestionsBrief($grouplevel['gid']) as $questionlevel){
						$nxquestions=$nxgroups."_".$l;
						$text=makeJavaSafe($questionlevel['question'], 27);
						if ($text == "") {$text="No title";}
						
						$$nxquestions = &${$nxgroups}->addItem(new HTML_TreeNodeXL($questionlevel['title'].": ".$text, "$scriptname?sid=".$toplevel['sid']."&amp;gid=".$grouplevel['gid']."&amp;qid=".$questionlevel['qid'], $nodeProperties));
						$l++;
					}
				}
				$k++;
			}
			$menu00->addItem($$nodename);
			$i++;
		}
	$surveymenu = &new HTML_TreeMenu_DHTMLXL($menu00, array("images"=>"classes/TreeMenu/TMimages"));
	$_SESSION['java_allsurveys']=$surveymenu->toHTML();
	$_SESSION['java_exclude']=$surveyid;
	}
	

	
	echo "<table width='100%' border='0' cellpadding='1'><tr><td width='250'>
	  <table width='250' border='0' cellpadding='0' cellspacing='0' class='menutable'>
		<tr>
		 <th nowrap>
		  <strong>"._SN_TITLE."</strong>
		 </th>
		</tr>
		<tr>
		 <td>
		  <a href='#' ".$navigation['dropdownaction']."=\"return dropdownmenu(this, event, createnew, '200px')\"><img src='$imagefiles/down.gif' border='0' hspace='0'  alt='"._CREATE."'>"._CREATE."</a>
		  <a href='#' ".$navigation['dropdownaction']."=\"return dropdownmenu(this, event, importnew, '200px')\"><img src='$imagefiles/down.gif' border='0' hspace='0' alt='"._IMPORT."'>"._IMPORT."</a>
		 </td>
		</tr>
	   </table>
	   <table cellspacing='0' cellpadding='0' width='250'><tr><td height='4'></td></tr></table>";

	$display="";
	if (isset($surveyid) && $surveyid) {
		echo "<table width='250' class='navtable' cellspacing='0'>
		 <tr><th nowrap>".windowControl('selectedsurvey', "$scriptname", 10, 10)."Selected Survey</th></tr>
		 <tr><td bgcolor='#BBBBBB' id='selectedsurvey'>";
		echo $_SESSION['java_thissurvey'];
		echo "</td></tr></table>
		<table cellspacing='0' cellpadding='0'><tr><td height='4'></td></tr></table>";
		$display="style='display: none'";
	}
	
	echo "<table width='250' class='navtable' cellspacing='0'>
	 <tr><th valign='top'>".windowControl('allsurveys', null, 10, 10)."All Surveys</th></tr>
	 <tr>
	  <td class='noborder'>
    	 <form method='get' action='index.php'>
	   "._SN_EXCLUDE.":
	   <input type='checkbox' id='noquestions' name='noquestions' value='noquestions'" 
	   . autoComparitor("noquestions", $_SESSION['noquestions'], " checked")." onClick='form.submit()'><label for='noquestions'>"._SN_QUESTIONS."</label>
	  	 <input type='hidden' name='sid' value='$surveyid'>
	  	 <input type='hidden' name='gid' value='$gid'>
	  	 <input type='hidden' name='qid' value='$qid'>
	  	 <input type='hidden' name='changenavdisplay'>
  	 </form>
	  </td>
	 </tr>
	 <tr><td bgcolor='#BBBBBB' id='allsurveys' $display>";
	//$surveymenu->printMenu();
	echo $_SESSION['java_allsurveys'];
	echo "</td></tr></table>
	<table cellspacing='0' cellpadding='0' width='250'><tr><td height='4'></td></tr></table>
	</td></tr></table>";
}

function labelsetDetails($lid = null) {
	global $publicurl, $homeurl, $imagefiles, $scriptname, $navigation;
	$theselabelsets=getLabelSetList();
	$thislabelset=getLabelSetInfo($lid);
	if (!empty($lid)) {
		$theselabels=getLabels($lid);
	}
	echo "<table width='100%' cellpadding='3' cellspacing='0' border='0' bgcolor='#CCCCCC' align='center'>
	 <tr>
	   <td>
	   <table width='100%' border='0' cellpadding='0' cellspacing='0' class='menutable' align='center'>
		<tr>
		 <th>
		  <strong>"._("Label Sets Administration")."</strong>
		 </th>
		</tr>
		<tr>
		 <td height='22' valign='top'>
		  ".windowControl('labelsummary', "$scriptname")."
		  <table align='right' border='0' cellspacing='0' cellpadding='0'><tr><td>
		  <select name='lid' onChange='window.open(\"$scriptname?action=showlabelsets&lid=\"+this.value, \"_top\")'>";
	echo "<option value=''>"._("Please Choose...")."</option>\n";
	foreach ($theselabelsets as $labelset) {
		echo "<option value='{$labelset['lid']}'".autoComparitor($lid, $labelset['lid'], " selected").">".$labelset['label_name']." (".$labelset['lid'].")</option>";
	}
	echo "		  </select></td></tr></table>
		  <a href='#' ".$navigation['dropdownaction']."=\"return dropdownmenu(this, event, labeloptions, '165px')\"><img src='$imagefiles/down.gif' border='0' hspace='0' alt='"._SN_OPTIONS."'>"._SN_OPTIONS."</a>
		 </td>
		</tr>
	   </table>";
	if (!empty($lid)) {
		$activeqids=labelsInActiveSurvey($lid);
		$otherqids=labelsInSurvey($lid);
		echo "	   <table id='labelsummary' class='outlinetable' width='100%'>";
//	    echo "<pre>";print_r($activeqids);echo "</pre>";
//	    echo "<pre>";print_r($otherqids);echo "</pre>";
		$noedit=0;
		$notes="";
		if (count($activeqids) > 0) {
		    $notes = "<tr><td colspan='4' align='center'><i><font color='red'>"._("Warning").":</font> "._("You cannot change codes, add or delete entries in this label set because it is being used by an active survey.")."</i></td></tr>\n";
			$noedit=1;
		} elseif (count($otherqids) > 0) {
			$notes = "<tr><td colspan='4' align='center'><i><font color='red'>"._("Warning").":</font> "._("Some surveys currently use this label set. Modifying the codes, adding or deleting entries to this label set may produce undesired results in other surveys.")."</i></td></tr>\n";
		}
		echo "			<tr>
			 <th>"._("Code")."</th>
			 <th>"._("Title")."</th>
			 <th>"._("Action")."</th>
			 <th>"._("Order")."</th>
			</tr>";
		$i=1;
		$max=count($theselabels);
		foreach ($theselabels as $label) {
			echo "		<tr>
						 <td align='center'><input type='text' name='code[]'".autoComparitor(1, $noedit, " readonly")." size='5' value='".$label['code']."'></td>
						 <td align='center'><input type='text' name='title[]' size='60' value='".$label['title']."'></td>
						 <td align='center'><input type='button' class='buttons' value='"._("Del")."'".autoComparitor(1, $noedit, " disabled")."></td>
						 <td align='center' nowrap>
						  <input type='button' class='buttons' value='"._("Up")."'".autoComparitor(1, $i, " style='display: none'")." onClick=\"window.open('$scriptname?lid=$lid&sortorder=".$label['sortorder']."&code=".$label['code']."&action=showlabelsets&dbaction=moveanswer&moveorder=-1', '_top')\">
						  <input type='button' class='buttons' value='"._("Dn")."'".autoComparitor($max, $i, " style='display: none'")." onClick=\"window.open('$scriptname?lid=$lid&sortorder=".$label['sortorder']."&code=".$label['code']."&action=showlabelsets&dbaction=moveanswer&moveorder=1', '_top')\">
						 </td>
						</tr>\n";
			$i++;
		}
		if (count($activeqids) < 1) {
		    echo "		<tr><td colspan='4' height='5'></td></tr>
						<tr>
						 <form method='post' action='index.php?action=showlabelsets&lid=$lid'>
						 <td align='center'><input type='text' name='code' size='5'></td>
						 <td align='center'><input type='text' name='title' size='60'></td>
						 <td align='center'><input type='submit' class='buttons' value='"._("Add")."'></td>
						 <td></td>
						 <input type='hidden' name='dbaction' value='addlabel'>
						 <input type='hidden' name='lid' value='$lid'>
						 <input type='hidden' name='sortorder' value='".(count($theselabels)+1)."'>
						 </form>
						</tr>\n";
		}
		echo $notes;
		echo "		   </table>
			  </td>
			 </tr>
			</table>";
		if (count($activeqids) > 0 || count($otherqids) > 0) {
			if (count($activeqids) > 0 && count($otherqids) > 0) {
			    $allqids = $activeqids;
				foreach ($otherqids as $key=>$qid) {
					$allqids[$key]=$qid;
				}
				//echo "<pre>"; print_r($allqids); echo "</pre>";
			} elseif (count($activeqids) >0 && count($otherqids) < 1) {
				$allqids=$activeqids;
			} elseif (count($activeqids) <1 && count($otherqids) > 0) {
				$allqids=$otherqids;
			}
		    echo "	   <br /><table id='labelsummary' class='outlinetable' align='center'>
				<tr><th colspan='".count($allqids)."'>Questions/Surveys Using this LabelSet</td></tr>
				<tr>\n";
			foreach ($allqids as $surveyid=>$detail) {
				echo "		<td align='center'>[<a href='$scriptname?sid=$surveyid'>Survey $surveyid</a>]<br />
					<select onChange=\"window.open(this.value, '_top')\">
					 <option>"._("Please Choose...")."</option>\n";
				foreach($detail as $dets) {
					echo "<option value='$scriptname?sid=$surveyid&amp;gid=".$dets['gid']."&amp;qid=".$dets['qid']."'>".$dets['qid']."</option>\n";
				}
				echo "		</select>
					</td>\n";
			}
			echo "</tr></table>";
		}
	}

}

function labelAdd() {
	global $publicurl, $imagefiles, $scriptname;
	$theselabelsets=getLabelSetList();

	echo "<table width='100%' cellpadding='3' cellspacing='0' border='0' bgcolor='#CCCCCC' align='center'>
	 <tr>
	   <td>
	   <table width='100%' border='0' cellpadding='0' cellspacing='0' class='menutable' align='center'>
		<tr>
		 <th>
		  &nbsp;
		 </th>
		</tr>
		<tr>
		 <td height='22' valign='top'>
		 <img src='$imagefiles/labels.png' align='left'>"._("Add new label set")."
		 </td>
		</tr>
	   </table>
	   <table cellspacing='0' cellpadding='0'><tr><td height='4'></td></tr></table>
	   <table width='100%' cellspacing='0'>
	    <form method='post' action='$scriptname'>
	    <tr>
		 <td align='right' class='rowheading'>"._("Set Name").":</td>
		 <td class='rowdata'><input type='text' name='label_name'></td>
		</tr>
		<tr><td colspan='2' height='4'></td></tr>
		<tr>
		 <td></td>
		 <td><input type='submit' class='buttons' value='"._("Add")."'></td>
		</tr>
		<input type='hidden' name='action' value='showlabelsets'>
		<input type='hidden' name='dbaction' value='addlabelset'>
		</form>
	   </table>\n";
	
}

function surveyDetails($surveyid, $gid, $qid) {
	global $publicurl, $homeurl, $imagefiles, $scriptname, $slstyle, $navigation;
	$thissurvey=getSurveyInfo($surveyid);

	list($ny, $nm, $nd)=explode("-", $thissurvey['expiry']);
	if ($ny < 1970) {
	    $nicedate=_NEVER;
	} else {
		$nicedate=date("D, d M Y", mktime(0,0,0,$nm,$nd,$ny));
	}
	
	if ((isset($gid) && $gid) || returnglobal('action') == "editsurvey" || returnglobal('action') =="ordergroups" || returnglobal('action') == "addsurvey" || returnglobal('action') == "editgroup" || returnglobal('action') == "addgroup" || returnglobal('action') == "showassessments") {$display="none";} else {$display="";}
	
	$fields=array(_("Title:")=>$thissurvey['name'],
				  _("Survey URL:")=>"<a href='".$publicurl."/index.php?sid=$surveyid' target='_blank'>".$publicurl."/index.php?sid=$surveyid</a>",
				  _("Description:")=>$thissurvey['description'],
				  _("Welcome:")=>$thissurvey['welcome'],
				  _("Administrator:")=>$thissurvey['adminname']." (<a href='mailto:".$thissurvey['adminemail']."'>".$thissurvey['adminemail']."</a>)",
				  _("Fax To:")=>$thissurvey['faxto'],
				  _("Expiry Date:")=>$nicedate,
				  _("End URL:")=>"<a href='".$thissurvey['url']."' target='_blank' title='".$thissurvey['url']."'>".$thissurvey['urldescrip']."</a>",
				  _("Automatically load URL when survey complete?")=>yesno($thissurvey['autoredirect']),
				  _("Language:")=>$thissurvey['language'],
				  _("Format:")=>formatName($thissurvey['format']),
				  _("Template:")=>$thissurvey['template'],
				  _("Allow Saves?")=>yesno($thissurvey['allowsave']),
				  _("Anonymous?")=>yesno($thissurvey['private']),
				  _("Date Stamp?")=>yesno($thissurvey['datestamp']),
				  _("IP Address")=>yesno($thissurvey['ipaddr']),
				  _("Invitation Email Subject:")=>$thissurvey['email_invite_subj'],
				  _("Invitation Email:")=>nl2br($thissurvey['email_invite']),
				  _("Email Reminder Subject:")=>$thissurvey['email_remind_subj'],
				  _("Email Reminder:")=>nl2br($thissurvey['email_remind']),
				  _("Confirmation Email Subject")=>$thissurvey['email_confirm_subj'],
				  _("Confirmation Email")=>nl2br($thissurvey['email_confirm']),
				  _("Public registration Email Subject:")=>$thissurvey['email_register_subj'],
				  _("Public registration Email:")=>nl2br($thissurvey['email_register']),
				  _("Token Attribute Names:")=>$thissurvey['attribute1'].autoComparitor(empty($thissurvey['attribute1']), false, " / ").$thissurvey['attribute2'],
				  _("Notification:")=>notifications($thissurvey['sendnotification']),
				  _("Start ID numbers at:")=>$thissurvey['autonumber_start'],
				  _("Show [<< Prev] button")=>yesno($thissurvey['allowprev']));

	$pages[_SN_SV_GENERAL]=array(_("Expiry Date:"), _("Title:"), _("Survey URL:"), _("Description:"), _("Welcome:"), _("Administrator:"), _("Fax To:"));
	$pages[_SN_SV_EXTRA]=array(_("Template:"), _("Language:"), _("End URL:"), _("Automatically load URL when survey complete?"), _("Format:"), _("Allow Saves?"), _("Anonymous?"), _("Date Stamp?"), _("IP Address"));
	$pages[_SN_SV_EMAIL]=array(_("Invitation Email Subject:"), _("Invitation Email:"), _("Email Reminder Subject:"), _("Email Reminder:"), _("Confirmation Email Subject"),_("Confirmation Email"), _("Public registration Email Subject:"), _("Public registration Email:"));
	$pages[_SN_SV_MISC]=array(_("Token Attribute Names:"), _("Notification:"), _("Start ID numbers at:"), _("Show [<< Prev] button"));
	
	if ($thissurvey['active'] == "Y") {
	    $surveystatus="<img src='$imagefiles/blank.gif' width='50' height='1' alt=''><a href='#' ".$navigation['dropdownaction']."=\"return dropdownmenu(this, event, surveyactivation, '180px')\"><img src='{$imagefiles}/active.png' border='0' hspace='0' alt='"._("This survey is currently active")."'>"._("This survey is currently active")."</a>";
	} else {
		$surveystatus="<img src='$imagefiles/blank.gif' width='195' height='1' alt=''><a href='#' ".$navigation['dropdownaction']."=\"return dropdownmenu(this, event, surveyactivation, '180px')\"><img src='{$imagefiles}/inactive.png' border='0' hspace='0' alt='"._("This survey is not currently active")."'>"._("This survey is not currently active")."</a>";
	}
	
	$contents = buildSummaryRows($fields, 'surveysummary', $display, $pages);
	
	echo "<table width='100%' cellpadding='3' cellspacing='0' border='0' bgcolor='#CCCCCC' align='center'>
	 <tr>
	   <td>
	   <table width='100%' border='0' cellpadding='0' cellspacing='0' class='menutable' align='center'>
		<tr>
		 <th>
		  <strong>"._("Survey").": ".$fields[_("Title:")]."</strong>
		 </th>
		</tr>
		<tr>
		 <td height='22' nowrap>".windowControl('surveysummary', "$scriptname")."
		  <a href='#' ".$navigation['dropdownaction']."=\"return dropdownmenu(this, event, surveyoptions, '170px')\"><img src='$imagefiles/down.gif' border='0' hspace='0' alt='"._SN_OPTIONS."'>"._SN_OPTIONS."</a>
		  <a href='#' ".$navigation['dropdownaction']."=\"return dropdownmenu(this, event, surveyactions, '215px')\"><img src='$imagefiles/down.gif' border='0' hspace='0' alt='"._SN_ACTIONS."'>"._SN_ACTIONS."</a>
		  ";
	if ($thissurvey['active'] == "Y") {
		echo "	  <a href='#' ".$navigation['dropdownaction']."=\"return dropdownmenu(this, event, surveyactive, '290px')\"><img src='$imagefiles/down.gif' border='0' hspace='0' alt='"._SN_ACTIVESURVEYOPTIONS."'>"._SN_ACTIVESURVEYOPTIONS."</a>";
	}
	echo "$surveystatus
		 </td>
		</tr>
	   </table>
	   $contents
	  </td>
	 </tr>
	</table>\n";
}

function surveyDel($surveyid) {
	global $dbprefix, $databasename;
	if (!is_numeric($surveyid)) { //make sure it's just a number!
	    return _("Error")." "._("You have not selected a survey to delete");
	} else {
		echo "<p align='center'>";
	    echo _("You are about to delete this survey")."<br />"._("This process will delete this survey, and all related groups, questions answers and conditions.")."<br />"._("We recommend that before you delete this survey you export the entire survey from the main administration screen.")."<br />";
		echo "<input type='submit' value='"._("Yes")."' onClick='window.open(\"index.php?sid=$surveyid&dbaction=delsurvey&ok=yes\", \"_top\")'>";
		echo "<input type='submit' value='"._("No")."' onClick='window.open(\"index.php?sid=$surveyid\", \"_top\")'>";
		echo "</p>";
	}
}

function surveyImport() {
    global $publicurl, $homeurl, $imagefiles, $scriptname, $slstyle, $action, $navigation;
	echo "<form enctype='multipart/form-data' name='importsurvey' action='$scriptname' method='post'>\n"
		. "<table width='100%' border='0'>\n"
		. "<tr><td>\n"
		. _("Import Survey")."</td></tr>\n\t<tr>"
		. "\t\t<td>"._("Select SQL File:")."</td>\n"
		. "\t\t<td><input name=\"the_file\" type=\"file\" size=\"35\"></td></tr>\n"
		. "\t<tr><td colspan='2' align='center'><input type='submit' value='"._("Import Survey")."'>\n"
		. "\t<input type='hidden' name='action' value='importsurvey'></td>\n"
		. "\t<input type='hidden' name='ok' value='yes'>\n"
		. "\t</tr>\n</table></form>\n";}

function surveyEdit($surveyid) {
	global $publicurl, $homeurl, $imagefiles, $scriptname, $slstyle, $action, $navigation;

	if ($action == "editsurvey") {
		$thissurvey=getSurveyInfo($surveyid);
		$title="<img src='$imagefiles/edit.png' align='left' border='0'>"._("Edit Current Survey");
		$button=_("Update");
	} elseif ($action == "addsurvey") {
		//Set defaults for a new survey
		$thissurvey=array("name"=>"",
						  "description"=>"",
						  "welcome"=>"",
						  "adminname"=>"",
						  "adminemail"=>"",
						  "faxto"=>"",
						  "useexpiry"=>"Y",
						  "expiry"=>"",
						  "url"=>"",
						  "active"=>"N",
						  "urldescrip"=>"",
						  "autoredirect"=>"N",
						  "usecookie"=>"N",
						  "language"=>"",
						  "format"=>"G",
						  "template"=>"default",
						  "allowsave"=>"N",
						  "private"=>"Y",
						  "datestamp"=>"N",
						  "ipaddr"=>"N",
						  "allowregister"=>"N",
						  "email_invite_subj"=>_("Invitation to participate in survey"),
						  "email_invite"=>_("Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}"),
						  "email_remind_subj"=>_("Reminder to participate in survey"),
						  "email_remind"=>_("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}"),
						  "email_confirm_subj"=>_("Confirmation of completed survey"),
						  "email_confirm"=>_("Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}"),
						  "email_register_subj"=>_("Survey Registration Confirmation"),
						  "email_register"=>_("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}."),
						  "attribute1"=>"",
						  "attribute2"=>"",
						  "sendnotification"=>"0",
						  "autonumber_start"=>"",
						  "allowprev"=>"Y");
		$title = "<img src='$imagefiles/add.png' align='left' border='0'>"._("Create or Import New Survey");
		$button = _("Add Survey");
	}

	$fields=array(_("Title:")=>textinput($thissurvey['name'], "short_title", "size='60'"),
				  _("Description:")=>textarea($thissurvey['description'], "description", "rows='6' cols='70'"),
				  _("Welcome:")=>textarea($thissurvey['welcome'], "welcome", "rows='6' cols='70'"),
				  _("Administrator:")=>textinput($thissurvey['adminname'], "admin", "size='30'"),
				  _("Admin Email:")=>textinput($thissurvey['adminemail'], "adminemail", "size='30'"),
				  _("Fax To:")=>textinput($thissurvey['faxto'], "faxto", "size='30'"),
				  _("Expires:")=>yesnoSelect($thissurvey['useexpiry'], "useexpiry"),
				  _("Expiry Date:")=>textinput($thissurvey['expiry'], "expires"),
				  _("End URL:")=>textinput($thissurvey['url'], "url", "size='60'"),
				  _("URL Description:")=>textinput($thissurvey['urldescrip'], "urldescrip", "size='60'"),
				  _("Automatically load URL when survey complete?")=>yesnoSelect($thissurvey['autoredirect'], "autoredirect"),
				  _("Use Cookies?")=>yesnoSelect($thissurvey['usecookie'], "usecookie"),
				  _("Language:")=>languages($thissurvey['language'], "language"),
				  _("Format:")=>formats($thissurvey['format'], "format"),
				  _("Template:")=>templates($thissurvey['template'], "template"),
				  _("Allow Saves?")=>yesnoSelect($thissurvey['allowsave'], "allowsave"),
				  _("Anonymous?")=>yesnoSelect($thissurvey['private'], "private", autoComparitor($thissurvey['active'], "Y", "disabled")).autoComparitor($thissurvey['active'], "Y", _SN_CANNOTCHANGE_SURVEYACTIVE),
				  _("Date Stamp?")=>yesnoSelect($thissurvey['datestamp'], "datestamp", autoComparitor($thissurvey['active'], "Y", "disabled")).autoComparitor($thissurvey['active'], "Y", _SN_CANNOTCHANGE_SURVEYACTIVE),
				  _("IP Address")=>yesnoSelect($thissurvey['ipaddr'], "ipaddr", autoComparitor($thissurvey['active'], "Y", "disabled")).autoComparitor($thissurvey['active'], "Y", _SN_CANNOTCHANGE_SURVEYACTIVE),
			      _("Allow public registration?")=>yesnoSelect($thissurvey['allowregister'], "allowregister"),
				  _("Invitation Email Subject:")=>textinput($thissurvey['email_invite_subj'], "email_invite_subj", "size='60'"),
				  _("Invitation Email:")=>textarea($thissurvey['email_invite'], "email_invite", "rows='6' cols='70'"),
				  _("Email Reminder Subject:")=>textinput($thissurvey['email_remind_subj'], "email_remind_subj", "size='60'"),
				  _("Email Reminder:")=>textarea($thissurvey['email_remind'], "email_remind", "rows='6' cols='70'"),
				  _("Confirmation Email Subject")=>textinput($thissurvey['email_confirm_subj'], "email_confirm_subj", "size='60'"),
				  _("Confirmation Email")=>textarea($thissurvey['email_confirm'], "email_confirm", "rows='6' cols='70'"),
				  _("Public registration Email Subject:")=>textinput($thissurvey['email_register_subj'], "email_register_subj", "size='60'"),
				  _("Public registration Email:")=>textarea($thissurvey['email_register'], "email_register", "rows='6' cols='70'"),
				  _("Attribute 1")=>textinput($thissurvey['attribute1'], "attribute1", "size='60'"),
				  _("Attribute 2")=>textinput($thissurvey['attribute2'], "attribute2", "size='60'"),
				  _("Notification:")=>notificationlist($thissurvey['sendnotification'], "notification"),
				  _("Start ID numbers at:")=>textinput($thissurvey['autonumber_start'], "autonumber_start", "size='60'"),
				  _("Show [<< Prev] button")=>yesnoSelect($thissurvey['allowprev'], "allowprev"));

  	echo "<table width='100%' cellpadding='3' cellspacing='0' border='0' bgcolor='#CCCCCC' align='center'>
		  <form method='post' action='$scriptname'>
		  <tr><td>
		   <table width='100%' border='0' cellpadding='0' cellspacing='0' class='menutable' align='center'>
			<tr>
			 <th height='15'>
			 &nbsp;
			 </th>
			</tr>
			<tr>
			 <td height='24' nowrap>
			  $title
			 </td>
			</tr>
		   </table>
		   <table cellspacing='0' cellpadding='0'><tr><td height='4'></td></tr></table>
		  \n";

	$contents = buildSummaryRows($fields, 'surveysummary', "");
	echo $contents;
	echo "		  </td></tr>
		  <tr>
		   <td>
		    <table width='100%' cellspacing='0' cellpadding='1'>
			 <tr>
			  <th width='165'>
			  </th>
			  <td>
		       <input type='submit' class='buttons' value='$button'>
		      </td>
			 </tr>
			</table>
		   </td>
		  </tr>
		  <input type='hidden' name='sid' value='$surveyid'>
		  <input type='hidden' name='action' value='showsurvey'>
		  <input type='hidden' name='dbaction' value='$action'>
		  </form>
		  </table>";
}

function groupDetails($surveyid, $gid, $qid) {
	global $publicurl, $imagefiles, $scriptname, $navigation;
	$thisgroup=getGroupInfo($surveyid, $gid);
	if (isset($qid) || multiStringSearch(array("editgroup", "addgroup"), returnglobal('action'))) {$display="none";} else {$display="";}
	$fields=array(_("Title:")=>$thisgroup['group_name'],
				  _("Description:")=>$thisgroup['description']);
	
	$contents = buildSummaryRows($fields, 'groupsummary', $display);
	
	echo "<table width='100%' cellpadding='3' cellspacing='0' border='0' bgcolor='#CCCCCC' align='center'>
	 <tr>
	   <td>
	   <table width='100%' border='0' cellpadding='0' cellspacing='0' class='menutable' align='center'>
		<tr>
		 <th>
		  <strong>"._("Group").": ".$thisgroup['group_name']."</strong>
		 </th>
		</tr>
		<tr>
		 <td height='22' nowrap>".windowControl('groupsummary', "$scriptname?sid=$surveyid")."
		  <a href='#' ".$navigation['dropdownaction']."=\"return dropdownmenu(this, event, groupoptions, '165px')\"><img src='$imagefiles/down.gif' border='0' hspace='0' alt='"._SN_OPTIONS."'>"._SN_OPTIONS."</a>
		  <a href='#' ".$navigation['dropdownaction']."=\"return dropdownmenu(this, event, groupactions, '200px')\"><img src='$imagefiles/down.gif' border='0' hspace='0' alt='"._SN_ACTIONS."'>"._SN_ACTIONS."</a>
		 </td>
		</tr>
	   </table>
	   $contents
	  </td>
	 </tr>
	</table>\n";
}

function questionDel($surveyid, $gid, $qid) {
	global $publicurl, $imagefiles, $scriptname, $action;
	echo "<p align='center'>";
    echo _("Deleting this question will also delete any answers it includes. Are you sure you want to continue?")."<br />";
	echo "<input type='submit' value='"._("Yes")."' onClick='window.open(\"index.php?sid=$surveyid&gid=$gid&amp;qid=$qid&dbaction=delquestion&ok=yes\", \"_top\")'>";
	echo "<input type='submit' value='"._("No")."' onClick='window.open(\"index.php?sid=$surveyid&gid=$gid&amp;qid=$qid\", \"_top\")'>";
	echo "</p>";
}

function groupDel($surveyid, $gid) {
    global $publicurl, $imagefiles, $scriptname, $action;
	echo "<p align='center'>";
    echo _("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?")."<br />";
	echo "<input type='submit' value='"._("Yes")."' onClick='window.open(\"index.php?sid=$surveyid&gid=$gid&dbaction=delgroup&ok=yes\", \"_top\")'>";
	echo "<input type='submit' value='"._("No")."' onClick='window.open(\"index.php?sid=$surveyid&gid=$gid\", \"_top\")'>";
	echo "</p>";	
}

function groupEdit($surveyid, $gid) {
	global $publicurl, $imagefiles, $scriptname, $action;
	if ($action == "editgroup") {
		$thisgroup=getGroupInfo($surveyid, $gid);
		$title="<img src='$imagefiles/edit.png' align='left' border='0'>"._("Edit Current Group");
		$button=_("Update");
	} elseif ($action == "addgroup") {
		//Set defaults for a new survey
		$thisgroup=array("group_name"=>"",
						  "description"=>"");
		$title = "<img src='$imagefiles/add.png' align='left' border='0'>"._("Add New Group to Survey");
		$button = _("Add Group");
	}

	$fields = array(_("Title:")=>textinput($thisgroup['group_name'], "group_name", "size='60'"),
				    _("Description:")=>textarea($thisgroup['description'], "description", "cols='60' rows='5'&nbsp;"));
	$contents = buildSummaryRows($fields, 'groupsummary');
	echo "<table width='100%' cellpadding='3' cellspacing='0' border='0' bgcolor='#CCCCCC' align='center'>
	 <form method='post' action='$scriptname'>
	 <tr>
	   <td>
	   <table width='100%' border='0' cellpadding='0' cellspacing='0' class='menutable' align='center'>
		<tr>
		 <th>&nbsp;
		</tr>
		<tr>
		 <td height='22' nowrap>
		  $title
		 </td>
		</tr>
	   </table>
	   $contents
	  </td>
	 </tr>
	  <tr>
	   <td>
	    <table width='100%' cellspacing='0' cellpadding='1'>
		 <tr>
		  <th width='165'>
		  </th>
		  <td>
	       <input type='submit' class='buttons' value='$button'>
	      </td>
		 </tr>
		</table>
	   </td>
	  </tr>
	  <input type='hidden' name='sid' value='$surveyid'>
	  <input type='hidden' name='gid' value='$gid'>
	  <input type='hidden' name='action' value='showgroup'>
	  <input type='hidden' name='dbaction' value='$action'>
	  </form>
	  </table>";
}

function questionDetails($surveyid, $gid, $qid, $action) {
	global $publicurl, $imagefiles, $scriptname, $navigation;
	$thissurvey=getSurveyInfo($surveyid);
	$thisquestion=getQuestionInfo($qid);
	$qtypes = getqtypelist("", "array");
	if (isset($action) && multiStringSearch(array("showanswers", "showattributes", "showsummary", "editquestion", "addquestion", "copyquestion"), $action)) {$display="none";} else {$display="";}
	$fields=array(_("Code:")=>$thisquestion['title'],
				  _("Question:")=>$thisquestion['question'],
				  _("Help:")=>$thisquestion['help'],
				  _("Type:")=>$qtypes[$thisquestion['type']]." (".$thisquestion['type'].")");
	
	$contents = buildSummaryRows($fields, 'questionsummary', $display);

	echo "<table width='100%' cellpadding='3' cellspacing='0' border='0' bgcolor='#CCCCCC' align='center'>
	 <tr>
	   <td>
	   <table width='100%' border='0' cellpadding='0' cellspacing='0' class='menutable' align='center'>
		<tr>
		 <th>
		  "._("Question").": [".$fields[_("Code:")]."] ".$fields[_("Question:")]."
		 </th>
		</tr>
		<tr>
		 <td height='22' nowrap>"
		 .windowControl('questionsummary', "$scriptname?sid=$surveyid&amp;gid=$gid")."
		  <a href='#' ".$navigation['dropdownaction']."=\"return dropdownmenu(this, event, questionoptions, '210px')\"><img src='$imagefiles/down.gif' border='0' hspace='0' alt='"._SN_OPTIONS."'>"._SN_OPTIONS."</a>
		  <a href='#' ".$navigation['dropdownaction']."=\"return dropdownmenu(this, event, questionactions, '230px')\"><img src='$imagefiles/down.gif' border='0' hspace='0' alt='"._SN_ACTIONS."'>"._SN_ACTIONS."</a>\n";
	if($thissurvey['active'] == "Y") {
		echo "		<a href='#' ".$navigation['dropdownaction']."=\"return dropdownmenu(this, event, activequestion, '230px')\"><img src='$imagefiles/down.gif' border='0' hspace='0' alt='"._SN_ACTIVEQUESTIONOPTIONS."'>"._SN_ACTIVEQUESTIONOPTIONS."</a>\n";
	}	 
	echo "		 </td>
		</tr>
	   </table>
	   $contents
	  </td>
	 </tr>
	</table>\n";
}

function questionEdit($surveyid, $gid, $qid) {
	global $publicurl, $homeurl, $imagefiles, $scriptname, $slstyle, $action, $navigation;
	
	if ($action == "editquestion") {
		$thissurvey = getSurveyInfo($surveyid);
	    $thisquestion = getQuestionInfo($qid);
		$title="<img src='$imagefiles/edit.png' align='left' border='0'>"._("Edit Current Question");
		$button = _("Update");
	} elseif ($action == "copyquestion") {
		$thissurvey = getSurveyInfo($surveyid);
	    $thisquestion = getQuestionInfo($qid);
		$thisquestion['title']="";
		$title="<img src='$imagefiles/copy.png' align='left' border='0' alt='". _("Copy Current Question")."' >"._("Copy Current Question");
		$button = _("Copy Question");
	} elseif ($action == "addquestion") {
		$thissurvey=getSurveyInfo($surveyid);
		$thisquestion = array("sid"=>$surveyid,
							  "gid"=>$gid,
							  "type"=>"S",
							  "title"=>"",
							  "question"=>"",
							  "help"=>"",
							  "other"=>"N",
							  "mandatory"=>"N",
							  "lid"=>"",
							  "preg"=>"");
		$title = "<img src='$imagefiles/add.png' align='left' border='0'>"._("Add New Question to Group");
		$button = _("Add Question");
	}

	$fields=array(_("Type:")=>questionTypeSelect($thisquestion['type'], "type", "onChange='otherSelection(this.value)'".autoComparitor($thissurvey['active'], "Y", " disabled")).autoComparitor($thissurvey['active'], "Y", _SN_CANNOTCHANGE_SURVEYACTIVE),
				  _("Code:")=>textinput($thisquestion['title'], "title", "size='5' maxlength='5'"),
				  _("Question:")=>textarea($thisquestion['question'], "question", "rows='4' cols='50'"),
				  _("Help:")=>textarea($thisquestion['help'], "help", "rows='3' cols='50'"),
				  _("Other:")=>yesnoSelect($thisquestion['other'], "other", "id='OtherSelection'".autoComparitor($thissurvey['active'], "Y", " disabled")).autoComparitor($thissurvey['active'], "Y", _SN_CANNOTCHANGE_SURVEYACTIVE),
				  _("Mandatory:")=>yesnoSelect($thisquestion['mandatory'], "mandatory", "id='Mandatory'"),
				  _("Label Set:")=>labelsetSelect($thisquestion['lid'], "lid", "id='LabelSets'".autoComparitor($thissurvey['active'], "Y", " disabled")).autoComparitor($thissurvey['active'], "Y", _SN_CANNOTCHANGE_SURVEYACTIVE),
				  _("Validation:")=>textinput($thisquestion['preg'], "preg", "id='Validation' size='40'"));

	if ($action == "editquestion" || $action == "copyquestion") {
		$fields[_("Group:")]=groupSelect($thisquestion['gid'], "gid", "");
	}	

	if ($action == "copyquestion") {
	    $fields[_("Copy Answers?")]=yesnoSelect("Y", "copyanswers");
		$fields[_("Copy Attributes?")]=yesnoSelect("Y", "copyattributes");
		//$qid="";
	}

   	echo "<table width='100%' cellpadding='3' cellspacing='0' border='0' bgcolor='#CCCCCC' align='center'>
		  <form method='post' action='$scriptname'>
		  <tr><td>
		   <table width='100%' border='0' cellpadding='0' cellspacing='0' class='menutable' align='center'>
			<tr>
			 <th height='15'>
			 &nbsp;
			 </th>
			</tr>
			<tr>
			 <td height='24' nowrap>
			  $title
			 </td>
			</tr>
		   </table>
		   <table cellspacing='0' cellpadding='0'><tr><td height='4'></td></tr></table>
		  \n";
	$contents = buildSummaryRows($fields, 'surveysummary', "");
	echo $contents;
	echo "		  </td></tr>
		  <tr>
		   <td>
		    <table width='100%' cellspacing='0' cellpadding='1'>
			 <tr>
			  <th width='165'>
			  </th>
			  <td>
		       <input type='submit' class='buttons' value='$button'>
		      </td>
			 </tr>
			</table>
		   </td>
		  </tr>
		  <input type='hidden' name='sid' value='$surveyid'>
		  <input type='hidden' name='qid' value='$qid'>
		  <input type='hidden' name='action' value='showquestion'>
		  <input type='hidden' name='dbaction' value='$action'>";
	if ($action == "addquestion") {
	  echo "    		  <input type='hidden' name='gid' value='$gid'>";
	}
	echo "			  </form>
		  </table>";
	echo "		<script type='text/javascript'>
		function otherSelection(QuestionType) {
			document.getElementById('Mandatory').style.display='';
			if (QuestionType == '') {QuestionType=document.getElementById('question_type').value;}
			if (QuestionType == 'M' || QuestionType == 'P' || QuestionType == 'L' || QuestionType == '!') {
				document.getElementById('OtherSelection').style.display = '';
				document.getElementById('LabelSets').style.display = 'none';
				document.getElementById('Validation').style.display = 'none';
			} else if (QuestionType == 'F' || QuestionType == 'H' || QuestionType == 'W' || QuestionType == 'Z' || QuestionType == '^') {
				document.getElementById('LabelSets').style.display = '';
				document.getElementById('OtherSelection').style.display = 'none';
				document.getElementById('Validation').style.display = 'none';
			} else if (QuestionType == 'S' || QuestionType == 'T' || QuestionType == 'U' || QuestionType == 'N' || QuestionType=='') {
				document.getElementById('Validation').style.display = '';
				document.getElementById('OtherSelection').style.display ='none';
				document.getElementById('LabelSets').style.display='none';
			} else if (QuestionType == 'X') {
				document.getElementById('Validation').style.display = 'none';
				document.getElementById('OtherSelection').style.dipslay = 'none';
				document.getElementById('LabelSets').style.display='none';
				document.getElementById('Mandatory').style.display='none';
				document.getElementById('Mandatory').value='N';
			} else {
				document.getElementById('LabelSets').style.display = 'none';
				document.getElementById('OtherSelection').style.display = 'none';
				document.getElementById('Validation').style.display = 'none';
			}
			buildQTlist(QuestionType);
		}
		otherSelection('".$thisquestion['type']."');
		-->
		</script>\n";
}

function questionResultSummary($surveyid, $gid, $qid) {
	global $publicurl, $imagefiles, $scriptname;

    $thisquestion=getQuestionInfo($qid);
	
	require_once("results.php");
	$results=giveMeRawDataFromFieldNames($surveyid, $gid, $qid, array(), "full");
	$total=count($results);
	echo "<table width='100%' cellpadding='3' cellspacing='0' border='0' bgcolor='#CCCCCC' align='center'>
	 <tr>
	   <td>
	   <table width='100%' border='0' cellpadding='0' cellspacing='0' class='menutable' align='center'>
		<tr>
		 <th>
		  <strong>"._SN_RESULTS.":</strong> [".$thisquestion['title']."]
		 </th>
		</tr>
		<tr>
		 <td height='22' nowrap>"
		 .windowControl('questionresults', "$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid")."
		 <img src='{$imagefiles}/browse.png' align='left'>$total "._SN_RECORDS."</td>
		</tr>
	   </table>";

	if ($summary=makeSummaryFromRawData($results, $surveyid, $gid, $qid)) {
		//echo "<pre>";print_r($summary);echo "</pre>";
		foreach ($summary as $sum) {
			echo "<table height='1'><tr><td></td></tr></table>\n";
			echo "<table id='questionresults' class='resultstable' align='center' width='100%' cellspacing='0'>\n";
			echo "<tr><th colspan='2'>".$sum['question']."</th></tr>\n";
			foreach ($sum['summary'] as $key=>$val) {
				$percentage=sprintf("%02.2f", ($val/$total)*100);
				echo "<tr><td align='right' valign='top' nowrap><strong>$key</strong></td><td class='result' nowrap>$percentage% ($val)</td></tr>\n";
			}
			echo "</table>";
		}
	}
	echo "</td></tr></table>\n";

}

function assessmentDetails($surveyid) {
	global $imagefiles, $scriptname, $homeurl;
	$thissurvey=getSurveyInfo($surveyid);
	$theseassessments=getAssessments($surveyid);
	$thesegroups=getGroupsBrief($surveyid);
	foreach($theseassessments as $assessments) {
		$fields[]=array("id"=>$assessments['id'],
						_("Scope")=>$assessments['scope'],
						_("Group")=>$assessments['gid'],
						_("Minimum")=>$assessments['minimum'],
						_("Maximum")=>$assessments['maximum'],
						_("Heading")=>$assessments['name'],
						_("Message")=>$assessments['message'],
						_("URL")=>$assessments['link']);
	}
	if (!empty($_GET['id'])) {
	    $thisassessment=getAssessmentInfo($_GET['id']);
		$actiontitle=_("Edit");
		$actionbutton=_("Edit");
		$dbaction = "editassessment";
	} else {
		$thisassessment=array("id"=>"",
							  "sid"=>$surveyid,
							  "scope"=>"G",
							  "gid"=>"",
							  "name"=>"",
							  "minimum"=>"",
							  "maximum"=>"",
							  "message"=>"",
							  "link"=>"");
		$actiontitle=_("Add");
		$actionbutton=_("Add");
		$dbaction = "addassessment";
	}
	echo "<table width='100%' cellpadding='3' cellspacing='0' border='0' bcolor='#CCCCCC' align='center'>
		<tr>
		 <td>
		 <table width='100%' border='0' cellpadding='0' cellspacing='0' class='menutable' align='center'>
		  <tr>
		   <th>
		    <strong>"._("Assessments").": ".$thissurvey['name']."</strong>
		   </th>
		  </tr>
		  <tr>
		   <td height='22' nowrap>"
		    .windowControl('assessmentsummary', "$scriptname?sid=$surveyid")."
		   </td>
		  </tr>
		 </table>
	   <table width='100%' align='center' border='0' cellpadding='0' cellspacing='1' class='outlinetable' id='assessmentsummary'>
	    <tr>
		 <td>
		 <table width='100%' align='center' border='0' cellpadding='0' cellspacing='1'>
		 <tr>
		 <th width='40'>"._("Scope")."</th>
		 <th>"._("Group")."</th>
		 <th width='40'>"._("Minimum")."</th>
		 <th width='80'>"._("Maximum")."</th>
		 <th width='80'>"._("Heading")."</th>
		 <th width='80'>"._("Message")."</th>
		 <th width='80'>"._("URL")."</th>
		 <th>"._("Action")."</th></tr>
	 ";
	 if (isset($fields) && is_array($fields) && count($fields) > 0) {
		 $max=count($fields);
		 foreach ($fields as $field) {
		 	echo "			 <tr>
					<td valign='top' align='center'>".$field[_("Scope")]."</td>
					<td valign='top' align='center'>".$field[_("Group")]."</td>
					<td valign='top' align='center'>".$field[_("Minimum")]."</td>
					<td valign='top' align='center'>".$field[_("Maximum")]."</td>
					<td valign='top' align='center'>".$field[_("Heading")]."</td>
					<td valign='top' align='center'>".$field[_("Message")]."</td>
					<td valign='top' align='center'>".$field[_("URL")]."</td>
					<form action='$scriptname' method='post'>
					<td  valign='top' align='center'>
					 <input type='button' class='buttons' value='"._("Edit")."' onClick='window.open(\"{$homeurl}/$scriptname?action=showassessments&amp;sid=$surveyid&amp;id=".$field['id']."\", \"_top\")'>
					 <input type='submit' class='buttons' value='"._("Delete")."' onClick='return confirm(\""._("Are you sure you want to delete this entry?")."\")'>
					 <input type='hidden' name='action' value='showassessments'>
					 <input type='hidden' name='sid' value='$surveyid'>
					 <input type='hidden' name='id' value='".$field['id']."'>
					 <input type='hidden' name='dbaction' value='deleteassessment'>
					</td>
					</form>
				   </tr>";
		 }
	}
	$groupselect="<select name='assessment_gid'>\n";
	foreach($thesegroups as $group) {
		$groupselect.="<option value='".$group['gid']."'".autoComparitor($thisassessment['gid'], $group['gid'], " selected").">".$group['group_name']."</option>\n";
	}
	$groupselect .="</select>\n";
	$headings=array(_("Scope"), _("Group"), _("Minimum"), _("Maximum"), _("Heading"), _("Message"), _("URL"));
	$inputs=array("<select name='scope'>
					<option value='T'".autoComparitor($thisassessment['scope'], "T", " selected").">"._("Total")."</option>
					<option value='G'".autoComparitor($thisassessment['scope'], "G", " selected").">"._("Group")."</option>
				   </select>",
				  $groupselect,
				  "<input type='text' name='minimum' value='".$thisassessment['minimum']."'>",
				  "<input type='text' name='maximum' value='".$thisassessment['maximum']."'>",
				  "<input type='text' name='name' value='".$thisassessment['name']."'>",
				  "<textarea name='message'>".$thisassessment['message']."</textarea>",
				  "<input type='text' name='link' value='".$thisassessment['link']."'>");

	echo "</table>\n\t</td></tr></table>\n<table height='10'><tr><td></td></tr></table>
		<table align='center' class='outlinetable'>
		<form action='$scriptname' method='post'>
		<tr><th colspan='2'>$actiontitle</th></tr>\n";
	$i=0;
	foreach ($headings as $head) {
		echo "<tr><th>$head</th><td>".$inputs[$i]."</td></tr>\n";
		$i++;
	 }
	echo "<tr><th colspan='2'>
		   <input type='submit' value='$actionbutton'>\n";
	if (!empty($_GET['id'])) {
	    echo "	 <input type='button' value='"._("Add")."' onClick=\"window.open('$scriptname?sid=$surveyid&action=showassessments', '_top')\">\n";
	}
	echo "
		</td></tr>
		<input type='hidden' name='sid' value='$surveyid'>
		<input type='hidden' name='action' value='showassessments'>
		<input type='hidden' name='dbaction' value='$dbaction'>
		<input type='hidden' name='id' value='".$thisassessment['id']."'>
		</form>
		</td></tr></table>\n</table>";

}

function answerDetails($surveyid, $gid, $qid) {
	global $publicurl, $imagefiles, $scriptname;
	$thissurvey=getSurveyInfo($surveyid);
	$thisquestion=getQuestionInfo($qid);
	$theseanswers=getAnswerInfo($qid);
	$fields=array();
	foreach($theseanswers as $answers) {
		$fields[]=array(_("Code")=>$answers['code'],
						_("Answer")=>$answers['answer'],
						_("Default")=>$answers['default_value'],
						"sortorder"=>$answers['sortorder'],
						"qid"=>$answers['qid']);
	}
	echo keycontroljs();
	echo "<table width='100%' cellpadding='3' cellspacing='0' border='0' bgcolor='#CCCCCC' align='center'>
	 <tr>
	   <td>
	   <table width='100%' border='0' cellpadding='0' cellspacing='0' class='menutable' align='center'>
		<tr>
		 <th>
		  <strong>"._("Answers").":</strong> [".$thisquestion['title']."]
		 </th>
		</tr>
		<tr>
		 <td height='22' nowrap>"
		 .windowControl('answersummary', "$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid")."
		 </td>
		</tr>
	   </table>
	   <table width='100%' align='center' border='0' cellpadding='0' cellspacing='1' class='outlinetable' id='answersummary'>
	    <tr>
		 <th width='40'>"._("Code")."</th><th>"._("Answer")."</th><th width='40'>"._("Default")."</th><th width='80'>"._("Action")."</th><th width='80'>"._("Move")."</th></tr>
	 	<form method='post' action='$scriptname'>
	 ";
	 $i=1;
	 if (isset($fields) && is_array($fields) && count($fields) > 0) {
		 $max=count($fields);
		 foreach ($fields as $field) {
		 	echo "			 <tr>
					<td><input type='text' name='code[]' value='".$field[_("Code")]."' size='4' maxlength='5' onKeyPress=\"return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_-')\"".autoComparitor($thissurvey['active'], "Y", " readonly")."></td>
					<td><input type='text' name='answer[]' value='".$field[_("Answer")]."' size='75'></td>
					<td align='center'><select name='default_value[]'>
						<option value='Y'".autoComparitor("Y", $field[_("Default")], " selected").">"._("Yes")."</option>
						<option value='N'".autoComparitor("N", $field[_("Default")], " selected").">"._("No")."</option>
					</select></td>
					<td align='center'><input type='button' class='buttons' value='"._("Del")."' onClick=\"window.open('$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&action=showanswers&dbaction=deleteanswer&code=".$field[_("Code")]."', '_top')\">
					</td>
					<td align='center'>
					 <input type='button' class='buttons' value='"._("Up")."'".autoComparitor(1, $i, " style='display: none'")." onClick=\"window.open('$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&sortorder=".$field['sortorder']."&code=".$field[_("Code")]."&action=showanswers&dbaction=moveanswer&moveorder=-1', '_top')\">
					 <input type='button' class='buttons' value='"._("Dn")."'".autoComparitor($max, $i, " style='display: none'")." onClick=\"window.open('$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&sortorder=".$field['sortorder']."&code=".$field[_("Code")]."&action=showanswers&dbaction=moveanswer&moveorder=1', '_top')\">
					</td>
				   </tr>
				   <input type='hidden' name='sortorder[]' value='".$field['sortorder']."'>\n";
				   $i++;
		 }
		echo "		<tr>
				 <td colspan='3'>
				 </td>
				 <td align='center'>
				  <input type='submit' class='buttons' value='"._("Save")."'>
				 </td>
				 <td>
				 </td>
				</tr>
			   <input type='hidden' name='sid' value='$surveyid'>
			   <input type='hidden' name='gid' value='$gid'>
			   <input type='hidden' name='qid' value='$qid'>
			   <input type='hidden' name='action' value='showanswers'>
			   <input type='hidden' name='dbaction' value='updateanswers'>
			   </form>\n";
	 }
	if ($thissurvey['active'] == "Y" && 
		($thisquestion['type'] == "M" 
		 || $thisquestion['type'] == "O"
		 || $thisquestion['type'] == "R")) {
	    
	} else {
		echo "		  <tr>
			  <td colspan='5'>
			  </td>
			 </tr>
			 <tr>
			 <form method='post' action='$scriptname'>
			  <td><input type='text' size='4' name='code' id='addanswercode' maxlength='5' onKeyPress=\"return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_-')\"></td>
			  <td><input type='text' size='75' name='answer'></td>
			  <td align='center'><select name='default_value'>
			    <option value='Y'>"._("Yes")."</option>
				<option value='N' selected>"._("No")."</option>
			  </select></td>
			  <td align='center'><input type='submit' class='buttons' value='"._("Add")."'></td>
			  <td></td>
			 <input type='hidden' name='sid' value='$surveyid'>
			 <input type='hidden' name='gid' value='$gid'>
			 <input type='hidden' name='qid' value='$qid'>
			 <input type='hidden' name='action' value='showanswers'>
			 <input type='hidden' name='dbaction' value='addanswer'>
			 <input type='hidden' name='sortorder' value='".sprintf("%0d", $i)."'>
			 </form>
			 </tr>
 		<script type='text/javascript' language='javascript'>
		<!--
		 document.getElementById('addanswercode').focus();
		//-->
		</script>";
	}
	echo "</table>
	   </td>
	  </tr>
	 </table>";
}

function attributeDetails($surveyid, $gid, $qid) {
	global $publicurl, $imagefiles, $scriptname, $homeurl;
	$theseattributes=getAttributeInfo($qid);
	$thisquestion=getQuestionInfo($qid);

	echo "<table width='100%' cellpadding='3' cellspacing='0' border='0' bgcolor='#CCCCCC' align='center'>
	 <tr>
	   <td>
	   <table width='100%' border='0' cellpadding='0' cellspacing='0' class='menutable' align='center'>
		<tr>
		 <th>
		  <strong>"._("Question Attributes:")."</strong> [".$thisquestion['title']."]
		 </th>
		</tr>
		<tr>
		 <td height='22' nowrap>"
		 .windowControl('attributesummary', "$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid")."
		 </td>
		</tr>
	   </table>
	   <table width='100%' align='center' border='0' cellpadding='0' cellspacing='1' id='attributesummary' class='outlinetable' id='answersummary'>
	    <tr><th>Attribute</th><th>Value</th><th>"._("Action")."</th></tr>
	 ";
	 foreach ($theseattributes as $attributes) {
	 	echo "		<tr><td align='center'>".$attributes['attribute']."</td>
		 <form method='post' action='$scriptname'>
		 <td align='center'>
		  <input type='text' name='value' value='".$attributes['value']."' size='10'>
		  <input type='submit' class='buttons' value='"._("Save")."'>
		 </td>
		 <input type='hidden' name='sid' value='$surveyid'>
		 <input type='hidden' name='qid' value='$qid'>
		 <input type='hidden' name='gid' value='$gid'>
		 <input type='hidden' name='action' value='showattributes'>
		 <input type='hidden' name='dbaction' value='editattribute'>
		 <input type='hidden' name='qaid' value='".$attributes['qaid']."'>
		 </form>
		 <td align='center'>
		  <input type='button' class='buttons' value='"._("Del")."' onClick=\"window.open('$homeurl/$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&action=showattributes&dbaction=deleteattribute&qaid=".$attributes['qaid']."', '_top')\">
		 </td>
		</tr>\n";
	 }
	echo "		<tr ><td colspan='3'></td></tr>
		  <tr><form method='post' action='$scriptname'>
		   <td align='center'><select name='attribute' id='QTlist'>";
	foreach(questionAttributes() as $qey=>$qat) {
		if ($qey == $thisquestion['type']) {
		    foreach($qat as $qut) {
				echo "<option>".$qut['name']."</option>\n";
			}
		}
	}
	echo "		   </select>
		   </td>
		   <td align='center'><input type='text' size='10' name='value'></td>
		   <td align='center'><input type='submit' class='buttons' value='"._("Add")."'></td>
		  </tr>
		  <input type='hidden' name='sid' value='$surveyid'>
		  <input type='hidden' name='gid' value='$gid'>
		  <input type='hidden' name='qid' value='$qid'>
		  <input type='hidden' name='action' value='showattributes'>
		  <input type='hidden' name='dbaction' value='addattribute'>
		  </form>
		  ";
	echo "</table>";
//echo "<pre>";print_r(questionAttributes());echo "</pre>".$thisquestion['type'];
echo "	  </td>
	 </tr>
	</table>";
	
}

function showPreview($surveyid, $gid, $qid=null) {
	global $publicdir, $publicurl, $tempdir, $tempurl, $imagefiles;
	//Show a preview of this question/group
	//Currently just for questions
	require_once("$publicdir/qanda.php");
	$thissurvey=getSurveyInfo($surveyid);
	$thisgroup=getGroupInfo($surveyid, $gid);
	if ($qid !== null) {
	    $thisquestion=getQuestionInfo($qid);
	}
	GetLanguageFromSurveyID($surveyid);
	$ia=array($qid, 
			  "DUMMY", 
			  $thisquestion['title'], 
			  $thisquestion['question'], 
			  $thisquestion['type'],
			  $gid,
			  $thisquestion['mandatory'],
			  "N");
	list($plus_qanda, $plus_inputnames)=retrieveAnswers($ia);
	$qanda[]=$plus_qanda;
	$inputnames[]=$plus_inputnames;
	
	$thistpl=$publicdir."/templates/".$thissurvey['template'];
	if (!is_dir($thistpl)) {$thistpl=$publicdir."/templates"."/default";}
	$GLOBALS['thistpl']=$thistpl;
	$GLOBALS['templatedir']=$publicurl."/templates/".$thissurvey['template'];
	$GLOBALS['thissurvey']=$thissurvey;
	
	$output=array();
	
    $output[] = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
   	$output[] = "<head><link type=\"text/css\" rel=\"StyleSheet\" href=\"../slider/swing.css\" />\n";
	$output[] = "<script type=\"text/javascript\" src=\"../slider/range.js\"></script>\n";
	$output[] = "<script type=\"text/javascript\" src=\"../slider/timer.js\"></script>\n";
	$output[] = "<script type=\"text/javascript\" src=\"../slider/slider.js\"></script>\n</head>\n";
	$output[] = "<html>\n";
	
	foreach(file("$thistpl/startpage.pstpl") as $op)
		{
		$output[]=templatereplace($op)."\n";
		}
	if (is_array($qanda))
		{
		foreach ($qanda as $qa)
			{
			//echo "<pre>";print_r($qa);echo"</pre>";
			$output[]= "\n\t<!-- NEW QUESTION -->\n";
			$output[]= "\t\t\t\t<div name='$qa[4]' id='$qa[4]'>";
			$GLOBALS['question']="<label for='$ia[7]'>" . $qa[0] . "</label>";
			$GLOBALS['answer']=$qa[1];
			$GLOBALS['help']=$qa[2];
			$GLOBALS['questioncode']=$qa[5];
			foreach(file("$thistpl/question.pstpl") as $op)
				{
				$output[]= "\t\t\t\t\t".templatereplace($op)."\n";
				}
			$output[]= "\t\t\t\t</div>\n";
			}
		}
	$output[]= "\n\n<!-- END THE GROUP -->\n";
	foreach(file("$thistpl/endpage.pstpl") as $op)
		{
		$output[]=templatereplace($op)."\n";
		}
	$output[] = "</html>";
	unlink_wc($tempdir, "preview_temp_*.html"); //Delete any older template files
	$time=date("ymdHis");
	$fnew=fopen("$tempdir/preview_temp_$time.html", "w+");
	foreach($output as $line) {
		fwrite($fnew, $line);
	}
	fclose($fnew);

	echo "<table height='4'><tr><td></td></tr></table>
		  <table width='99%' align='center' class='menutable' cellspacing='0' cellpadding='0'><tr><th>
		  "._PR_HEADING."
		  </th></tr>
		  <tr><td height='22'>
		  ".windowControl("preview", "?sid=$surveyid&amp;gid=$gid&amp;qid=$qid")."
		   <img src='$imagefiles/templates.png' align='left'>
		   "._Q_PREVIEWQUESTION."
		  </td></tr>
		  </table>
		  <table width='99%' align='center' class='outlinetable' id='preview' cellspacing='0' cellpadding='0'><tr><td>
		  <iframe src='$tempurl/preview_temp_$time.html' width='100%' height='250' name='sample' style='background-color: white'></iframe>
		  </td></tr></table>\n";

}


function checkSettings($dbprefix) {
	//GET NUMBER OF SURVEYS
	global $currentadminlang, $databasename, $currentadminlang, $scriptname, $homeurl, $imagefiles, $connect;
	
	$query = "SELECT sid FROM {$dbprefix}surveys";
	$result = $connect->Execute($query);
	$surveycount=$result->RecordCount();
	$query = "SELECT sid FROM {$dbprefix}surveys WHERE active='Y'";
	$result = $connect->Execute($query);
	$activesurveycount=$result->RecordCount();
	$query = "SELECT user FROM {$dbprefix}users";
	$result = $connect->Execute($query);
	$usercount = $result->RecordCount();
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
	echo  "		<table cellspacing='0'><tr><td height='3'></td></tr></table>
				<table align='center' class='menutable' width='100%' cellspacing='0' cellpadding='0'>
				<tr><th><strong>"._("PHPSurveyor System Summary")."</td></tr>
				<tr><td height='23'><img src='{$imagefiles}/summary.png' align='left' border='0'></td></tr>
				</table>
				<table cellspacing='0' cellpadding='0'><tr><td height='4'></td></tr></table>
				<table align='center' class='outlinetable' cellpadding='1' cellspacing='0' width='100%'>
				 <tr>
				  <td width='50%' align='right'><strong>"._("Database Name").":</strong></td>
				  <td>$databasename</td>
				 </tr>
				 <tr>
				  <td align='right'><strong>"._("Default Language").":</strong></td>
				  <td>$currentadminlang</td>
				 </tr>
				 <tr>
				  <td align='right'><strong>"._("Current Language").":</strong></td>
				  <form action='$scriptname'>
				  <td>
				   <select name='lang' onChange='form.submit()'>\n";
	foreach (getLanguageData() as $langkey=>$language)
		{
		echo "\t\t\t\t<option value='$langkey'".autoComparitor($currentadminlang, $language['description'], " selected").">".$language['description']."</option>\n";
		}
	echo  "			</select>
				<input type='hidden' name='action' value='changelang'></td>
				</form>
			   </tr>
			   <tr>
				<td align='right'><strong>"._("Users").":</strong></td>
				<td>$usercount</td>
			   </tr>
			   <tr>
				<td align='right'><strong>"._("Surveys").":</strong></td>
				<td>$surveycount</td>
			   </tr>
			   <tr>
			    <td align='right'><strong>"._("Active Surveys").":</strong></td>
				<td>$activesurveycount</td>
			   </tr>
			   <tr>
			    <td align='right'><strong>"._("De-activated Surveys").":</strong></td>
				<td>$deactivatedsurveys</td>
			   </tr>
			   <tr>
			    <td align='right'><strong>"._("Active Token Tables").":</strong></td>
				<td>$activetokens</td>
			   </tr>
			   <tr>
			    <td align='right'><strong>"._("De-activated Token Tables").":</strong></td>
				<td>$deactivatedtokens</td>
			   </tr>
			  </table>
			  <table><tr><td height='1'></td></tr></table>\n";

}





function getSurveysBrief($user=null, $surveyid=null, $notsid=null) {
    global $dbprefix, $connect;
	$surveyList=array();
	$query = "SELECT * FROM {$dbprefix}surveys ";
	if ($surveyid !== null) {
	    $query .= "WHERE sid=$surveyid ";
	}
	if ($notsid !== null && $surveyid!="") {
	    $query .= "WHERE sid != $notsid ";
	}
	$query .= "ORDER BY short_title";
	$result = db_execute_assoc($query) or die($query ."<br />".$connect->ErrorMsg());
	while($row=$result->FetchRow()) {
		$surveyList[]=array("title"=>$row['short_title'],
						  "sid"=>$row['sid'],
						  "active"=>$row['active']);
	} // while
	return $surveyList;	
}
function getGroupsBrief($surveyid) {
    global $dbprefix, $connect;
	$groupList=array();
	$query = "SELECT * FROM {$dbprefix}groups WHERE sid=$surveyid ORDER BY {$dbprefix}groups.sortorder";
	$result = db_execute_assoc($query) or die($query."<br />".$connect->ErrorMsg());
	while($row=$result->FetchRow()) {
		$groupList[]=array("group_name"=>$row['group_name'],
						 "gid"=>$row['gid']);
	} // while
	usort($groupList, 'CompareGroupThenTitle');
	return $groupList;
}

function getQuestionsBrief($gid) {
	global $dbprefix, $connect;
	$questionList=array();
	$query = "SELECT * FROM {$dbprefix}questions WHERE gid=$gid ORDER BY title";
	$result = db_execute_assoc($query);
	while($row=$result->FetchRow()) {
		$questionList[]=array("title"=>$row['title'],
							  "question"=>$row['question'],
							 "qid"=>$row['qid']);
	} // while
	return $questionList;
}

function windowControl($elementName, $closeLink="", $height=20, $width=20) {
	global $imagefiles;
	$windowcontrol = "";
	if ($closeLink !== null) {
		$windowcontrol .= "<input type='image' src='$imagefiles/close.gif' align='right' onClick=\"window.open('$closeLink', '_top')\" />\n";
	}
	$windowcontrol .= "<input type='image' src='$imagefiles/plus.gif' align='right' onClick=\"javascript: document.getElementById('$elementName').style.display=''\" />\n";
	$windowcontrol .= "<input type='image' src='$imagefiles/minus.gif' align='right' onClick=\"javascript: document.getElementById('$elementName').style.display='none'\" />\n";

	return $windowcontrol;
}

function buildSummaryRows($fields, $elementName, $display="", $pages=null) {
	$summary = "<table width='100%' id='$elementName' style='display: $display' cellpadding='0' cellspacing='0'>\n<tr><td colspan='2'>\n";
	if (is_array($pages)) {
		$summary .= "<script type='text/javascript'>
					<!--
					function showtab(name)
					 {
					 var tabhead='show'+name;\n";
		foreach (array_keys($pages) as $page) {
			$summary .= "			document.getElementById('tab_$page').style.display='none';
					document.getElementById('showtab_$page').className='tabselect';\n";
		}
					 
		$summary .="			document.getElementById(name).style.display='';
					document.getElementById(tabhead).className='tabselected';
					  }
					//-->
					</script>";
		$summary .= " <table cellspacing='1' cellpadding='0'><tr><td colspan='".count($pages)."'></td></tr>
					  <tr>";
		$headerno=1;
		foreach(array_keys($pages) as $page) {
			if ($headerno==1) {$class='tabselected';} else {$class='tabselect';}
			$summary .= "<td align='center' id='showtab_$page' class='$class'>
						 <a href='#' onclick='javascript: showtab(\"tab_$page\")'>$page</a></td>";
			$headerno++;
		}
		$summary .= "</tr></table></td></tr><tr><td>";
		$pageno=1;
		foreach ($pages as $key=>$val) {
			if ($pageno > 1) {$display='none';} else {$display='';}
			$summary .= "<table id='tab_$key' style='display: $display' width='100%' cellpadding='0' cellspacing='1'>\n";
			foreach ($fields as $fkey=>$fval) {
				if (in_array($fkey, $val))
					$summary .= "<tr><td class='rowheading' width='165' valign='top'>$fkey</td>
								 <td class='rowdata' valign='top'>$fval</td></tr>";
			}
			$summary .= "</table>\n";
			$pageno++;
		}
		$summary .= "</td></tr>";
	}
	else {
		foreach ($fields as $key=>$val) {
			$summary .= "<tr><td class='rowheading' width='165' valign='top'>$key</td>
						 <td class='rowdata' valign='top'>$val</td></tr>";
		}
	}
	$summary .= "</table>\n";
	return $summary;
}

function buildEditRows($fields, $elementName, $display="") {
	$summary = "<table width='100%' id='$elementName' style='display: $display'>\n";
	foreach ($fields as $key=>$val) {
		$summary .= "<tr><td class='rowheading' width='150'>$key</td>
					 <td class='rowdata'><input type='text' name='$key' value='$val'></td></tr>";
	}
	$summary .= "</table>\n";
	return $summary;
}

function getGroupInfo($surveyid, $gid) {
	global $dbprefix, $connect;
	$query = "SELECT * FROM {$dbprefix}groups WHERE sid=$surveyid AND gid=$gid";
	$result = db_execute_assoc($query) or die("Couldn't get info for group $gid<br />$query<br />".$connect->ErrorMsg());
	while($row=$result->FetchRow()) {
		return $row;
	} // while
}

function getQuestionInfo($qid) {
	global $dbprefix, $connect;
	$query = "SELECT * FROM {$dbprefix}questions where qid=$qid";
	$result = db_execute_assoc($query) or die("Couldn't get info for question $qid<br />$query<br />".$connect->ErrorMsg());
	while($row=$result->FetchRow()) {
		return $row;
	} // while
}

function getAnswerInfo($qid) {
	global $dbprefix, $connect;
	$query = "SELECT * FROM {$dbprefix}answers WHERE qid=$qid ORDER BY sortorder, code";
	$result = db_execute_assoc($query) or die("Couldn't get info for answers $qid<br />$query<br />".$connect->ErrorMsg());
	$output=array();
	while($row = $result->FetchRow()){
		$output[] = $row;
	} // while
	return $output;
}

function getAttributeInfo($qid) {
	global $dbprefix, $connect;
	$query = "SELECT * FROM {$dbprefix}question_attributes where qid=$qid";
	$result = db_execute_assoc($query) or die("Couldn't get info for question attributes $qid<br />$query<br />".$connect->ErrorMsg());
	$output=array();
	while($row = $result->FetchRow()){
		$output[] = $row;
	} // while
	return $output;

}

function getAssessments($surveyid) {
	global $dbprefix, $connect;
	$query = "SELECT id, sid, scope, gid, minimum, maximum, name, message, link
			  FROM {$dbprefix}assessments
			  WHERE sid=$surveyid
			  ORDER BY scope, gid";
	$result=db_execute_assoc($query) or die("Error getting assessments<br />$query<br />".$connect->ErrorMsg());
	$output=array();
	while($row=$result->FetchRow()) {
		$output[]=$row;
	}
	return $output;
}

function getLabelSetList() {
	global $dbprefix, $connect;
	$query = "SELECT * FROM {$dbprefix}labelsets ORDER BY label_name";
	$result = db_execute_assoc($query);
	$output=array();
	while($row=$result->FetchRow()) {$output[]=$row;}
	return $output;
}

function getLabels($lid) {
	global $dbprefix, $connect;
	$query = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid ORDER BY sortorder, title";
	$result = db_execute_assoc($query);
	$output=array();
	while($row=$result->FetchRow()) {$output[]=$row;}
	return $output;
}

function getLabelSetInfo($lid) {
	global $dbprefix, $connect;
	if ($lid) {
		$query = "SELECT * FROM {$dbprefix}labelsets WHERE lid=$lid";
		$result = db_execute_assoc($query) or die("Error getting labelset<br />$query<br />".$connect->ErrorMsg());
		while($row=$result->FetchRow()) {
			$output=$row;
		}
	} else {$output=array();}
	return $output;
}

function getAssessmentInfo($id) {
	global $dbprefix, $connect;
	$query = "SELECT * FROM {$dbprefix}assessments WHERE id=$id";
	$result = db_execute_assoc($query);
	while($row=$result->FetchRow()) {
		$output=$row;
	}
	return $output;
}
//function getGroupList($surveyid) {
//	global $dbprefix, $connect;
//	$query = "SELECT gid, group_name
//			  FROM {$dbprefix}groups
//			  WHERE sid=$surveyid
//			  ORDER BY group_name";
//	$result db_execute_assoc($query) or die("Error getting groups<br />$query<br />".$connect->ErrorMsg());
//	$output=array();
//	while($row=$result->FetchRow()) {
//		$output[]=$row;
//	}
//	return $output;
//}

function showPosition($surveyid, $gid, $qid) {
	global $dbprefix, $connect;
	$query = "SELECT {$dbprefix}surveys.short_title";
	$from = "\nFROM {$dbprefix}surveys";
	$join = "\n";
	$where = "\nWHERE {$dbprefix}surveys.sid = $surveyid";
	if (isset($gid) && $gid) {
	    $query .= ",\n{$dbprefix}groups.group_name";
		$join .= "LEFT JOIN {$dbprefix}groups ON {$dbprefix}surveys.sid={$dbprefix}groups.sid";
		$where .= "\nAND {$dbprefix}groups.gid = $gid";
	}
	if (isset($qid) && $qid) {
	    $query .= ",\n{$dbprefix}questions.title, {$dbprefix}questions.question";
		$join .= "\nLEFT JOIN {$dbprefix}questions ON {$dbprefix}surveys.sid={$dbprefix}questions.sid";
		$where .= "\nAND {$dbprefix}questions.qid=$qid";
	}
	$sql=$query.$from.$join.$where;
	$result=db_execute_assoc($sql) or die("Couldn't do good join for position display<br />$query<br />".$connect->ErrorMsg());
	while($row=$result->FetchRow()) {
		$output = "[".$row['short_title']."]";
		if (isset($gid) && $gid) {
		    $output .= "->[".$row['group_name']."]";
		}
		if (isset($qid) && $qid) {
		 	$output .= "->[".$row['title'].": ".$row['question']."]";
		}
	} // while
	return $output;
}

function makeJavaSafe($string, $maxlength) {
	$string=strip_tags($string);
//	$string=htmlentities($string, ENT_QUOTES);
	$string=trim($string);
	$string=str_replace(array("\n\r", "\n", "\r"), array(" ", " ", " "), $string);
	//Reduce to $maxlength
	if (strlen($string) > $maxlength) {
	    $string=substr($string, 0, $maxlength-2)."..";
	}
	return $string;
}

function autoComparitor($value, $comparitor, $returnvalue) {
	//returns $returnvalue if the $comparitor is equal to the value
	if ($value == $comparitor) {
	    return $returnvalue;
	}
}

function labelsets() {
	global $dbprefix, $connect;
	$query = "SELECT * FROM {$dbprefix}labelsets
			  ORDER BY label_name";
	$result=db_execute_assoc($query) or die($connect->ErrorMsg());
	$output=array();
	while ($row = $result->FetchRow()) {$output[]=$row;}
	return $output;
}

function questionTypes() {
	$qtypes = array(
			"5"=>_("5 Point Choice"),
			"D"=>_("Date"),
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
			"X"=>_("Boilerplate Question"),
			"W"=>_("List (Flexible Labels) (Dropdown)"),
			"Z"=>_("List (Flexible Labels) (Radio)"),
			"^"=>_("Slider"),
			);
		array_multisort($qtypes);
		return $qtypes;
}

function yesno($value) {
	switch($value){
		case "Y": 
			return _("Yes");
		case "N": 
			return _("No");
	} // switch
}

function notifications($value) {
	switch($value) {
		case "0":
			return _("No email notification");
		case "1":
			return _("Basic email notification");
		case "2":
			return _("Detailed email notification with result codes");
	}
}

function formatName($format) {
	switch($format){
		case "S": 
			return _("Question by Question");
		case "G": 
			return _("Group by Group");
		case "A":
			return _("All in one");
		default:
			return _("Question by Question");
	} // switch
}

function textinput($value, $name, $extras=null) {
	return "<input type='text' name='$name' value = '$value' $extras />";
}

function textarea($value, $name, $extras=null) {
	return "<textarea name='$name' $extras />$value</textarea>";
}

function yesnoSelect($value=null, $name, $extras=null) {
	return "<select name='$name' $extras>
		<option value='Y'".autoComparitor($value, "Y", " selected").">"._("Yes")."</option>
		<option value='N'".autoComparitor($value, "N", " selected").">"._("No")."</option>
		</select>";
}

function labelsetSelect($value=null, $name, $extras=null) {
	$output = "<select name='$name' $extras>\n";
	$labelsets=labelsets();
	foreach($labelsets as $label) {
		$output .= "<option value='".$label['lid']."'".autoComparitor($value, $label['lid'], " selected").">".$label['label_name']."</option>\n";
	}
	$output .= "</select>";
	return $output;
}

function groupSelect($value=null, $name="gid", $extras=null) {
	global $surveyid;
    $output = "<select name='$name' $extras>\n";
	$groups=getGroupsBrief($surveyid);
	foreach($groups as $group) {
	    $output .= "<option value='".$group['gid']."'".autoComparitor($value, $group['gid'], " selected").">".$group['group_name']."</option>\n";
	}
	$output .= "</select>";
	return $output;
}

function questionTypeSelect($value=null, $name, $extras=null) {
	$output = "<select name='$name' $extras>\n";
	$questiontypes=questionTypes();
	foreach($questiontypes as $type=>$question) {
		$output .= "<option value='".$type."'".autoComparitor($value, $type, " selected").">".$question."</option>\n";
	}
	$output .= "</select>";
	return $output;
}

function languages($value=null, $name, $extras=null) {
	$return = "<select name='$name' $extras>\n";
	foreach(getLanguageData() as $language) {
		$return .= "<option".autoComparitor($value, $language, " selected").">$language</option>\n";
	}
	$return .= "</select>\n";
	return $return;
}

function formats($value=null, $name, $extras=null) {
	return "	<select name='$name' $extras>
	<option value='S'".autoComparitor($value, "S", " selected").">"._("Question by Question")."</option>
	<option value='G'".autoComparitor($value, "G", " selected").">"._("Group by Group")."</option>
	<option value='A'".autoComparitor($value, "A", " selected").">"._("All in one")."</option>
	</select>\n";
	
}

function templates($value=null, $name, $extras=null) {
	$return = "<select name='$name' $extras>\n";
	foreach (gettemplatelist() as $template) {
		$return .= "<option ".autoComparitor($value, $template, " selected").">$template</option>\n";
	}
	$return .= "</selected>\n";
	return $return;
}

function notificationlist($value=null, $name, $extras=null) {
	return "	<select name='$name' $extras>
	<option value='0'".autoComparitor($value, "0", " selected").">"._("No email notification")."</option>
	<option value='1'".autoComparitor($value, "1", " selected").">"._("Basic email notification")."</option>
	<option value='2'".autoComparitor($value, "2", " selected").">"._("Detailed email notification with result codes")."</option>
	</select>\n";
}

function labelsInActiveSurvey($lid) {
	global $dbprefix, $connect;
	$query = "SELECT {$dbprefix}questions.sid, {$dbprefix}questions.gid, {$dbprefix}questions.qid 
			  FROM {$dbprefix}questions, {$dbprefix}surveys
			  WHERE {$dbprefix}questions.sid={$dbprefix}surveys.sid
			  AND {$dbprefix}questions.lid=$lid
			  AND {$dbprefix}surveys.active = 'Y'";
	$result = db_execute_assoc($query);
	$output=array();
	$surveyid=null;
	while($row=$result->FetchRow()) {
		if ($row['sid'] != $surveyid) {
		    $surveyid=$row['sid'];
		}
		$output[$surveyid][]=array('gid'=>$row['gid'], 'qid'=>$row['qid']);
	}
	return $output;
}

function labelsInSurvey($lid) {
	global $dbprefix, $connect;
	$query = "SELECT {$dbprefix}questions.sid, {$dbprefix}questions.gid, {$dbprefix}questions.qid 
			  FROM {$dbprefix}questions, {$dbprefix}surveys
			  WHERE {$dbprefix}questions.sid={$dbprefix}surveys.sid
			  AND {$dbprefix}questions.lid=$lid
			  AND {$dbprefix}surveys.active != 'Y'
			  ORDER BY {$dbprefix}questions.sid, {$dbprefix}questions.qid";
	$result = db_execute_assoc($query);
	$output=array();
	$surveyid=0;
	while($row=$result->FetchRow()) {
		if ($row['sid'] != $surveyid) {
		    $surveyid=$row['sid'];
		}
		$output[$surveyid][]=array('gid'=>$row['gid'], 'qid'=>$row['qid']);
	}
	return $output;
}

function javadropdown($surveyid, $gid, $qid) {
global $publicurl, $homeurl, $imagefiles, $scriptname;
global $lid;

//Menu entries go here
//CREATENEW
//$menu['system']["0"]="<a href='$homeurl/authentication.php'><img src='$imagefiles/security.png' align='left' border='0' height='15' width='15'>"._AUTHENTICATION_BT."<\/a><br />";
$menu['system']["1"]="<a href='$homeurl/$scriptname?action=checksettings'><img src='$imagefiles/summary.png' align='left' border='0' height='15' width='15'>"._SYSTEM_BT."<\/a><br />";

$menu['systemdb']["0"]="<a href='$homeurl/checkfields.php'><img src='$imagefiles/checkdb.png' align='left' border='0' height='15' width='15'>"._("Check Database Fields")."<\/a><br />";
$menu['systemdb']["1"]="<a href='$homeurl/dbchecker.php'><img src='$imagefiles/checkdb.png' align='left' border='0' height='15' width='15'>"._("Check PHPSurveyor Data Integrity")."<\/a><br />";
$menu['systemdb']["2"]="<a href='$homeurl/dumpdb.php'><img src='$imagefiles/backup.png' align='left' border='0' height='15' width='15'>"._("Backup Entire Database")."<\/a><br />";

$menu['systemother']["0"]="<a href='$homeurl/$scriptname?action=showlabelsets'><img src='$imagefiles/labels.png' align='left' border='0' height='15' width='15'>"._("Edit/Add Label Sets")."<\/a><br />";

$menu['createnew']["2"]="<a href='$homeurl/$scriptname?action=addsurvey'><img src='$imagefiles/add.png' align='left' border='0' height='15' width='15'>"._("Create or Import New Survey")."<\/a><br />";
$menu['importnew']["2"]="<a href='$homeurl/$scriptname?action=importsurvey'><img src='$imagefiles/import.gif' align='left' border='0' height='15' width='15'>"._("Import Survey")."<\/a><br />";

$menu['labeloptions']["0"]="<a href='$homeurl/$scriptname?action=addlabel'><img src='$imagefiles/add.png' align='left' border='0' height='15' width='15'>"._("Create New Label Set")."<\/a><br />";
if (!empty($lid)) {
	$activeqids=labelsInActiveSurvey($lid);
	if (count($activeqids) < 1) {
	    $menu['labeloptions']["1"]="<a href='$homeurl/$scriptname?action=dellabel&lid=$lid'><img src='$imagefiles/delete.png' align='left' border='0' height='15' width='15'>"._("Delete label set")."<\/a><br />";
	}
}
if(!empty($surveyid)) {
	$thissurvey=getSurveyInfo($surveyid);
	if ($thissurvey['active'] != "Y") {
		$menu['createnew']["1"]="<a href='$homeurl/$scriptname?action=addgroup&amp;sid=$surveyid'><img src='$imagefiles/add.png' align='left' border='0' height='15' width='15'>"._("Add New Group to Survey")."<\/a><br />";
		$menu['importnew']["1"]="<a href='$homeurl/$scriptname?action=importgroup&amp;sid=$surveyid'><img src='$imagefiles/import.gif' align='left' border='0' height='15' width='15'>"._("Import Group")."<\/a><br />";
	}

	if(!empty($gid)) {
		if ($thissurvey['active'] != "Y") {
			$menu['createnew']["0"]="<a href='$homeurl/$scriptname?action=addquestion&amp;sid=$surveyid&amp;gid=$gid'><img src='$imagefiles/add.png' align='left' border='0' height='15' width='15'>"._("Add New Question to Group")."<\/a><br />";
			$menu['importnew']["0"]="<a href='$homeurl/$scriptname?action=importquestion&amp;sid=$surveyid&amp;gid=$gid'><img src='$imagefiles/import.gif' align='left' border='0' height='15' width='15'>"._("Import Question")."<\/a><br />";
		}
	}

	
	$menu['surveyactions']["0"]="<a href='{$publicurl}/$scriptname?sid=$surveyid&amp;newtest=Y' target='_blank'><img src='$imagefiles/do.png' align='left' border='0' height='15' width='15'>"._("Do Survey")."<\/a><br />";
	$menu['surveyactions']["1"]="<a href='{$homeurl}/dataentry.php?sid=$surveyid' target='_blank'><img src='$imagefiles/dataentry.png' align='left' border='0' height='15' width='15'>"._("Dataentry Screen for Survey")."<\/a><br />";
	$menu['surveyactions']["2"]="<a href='{$homeurl}/printablesurvey.php?sid=$surveyid' target='_blank'><img src='$imagefiles/print.png' align='left' border='0' height='15' width='15'>"._("Printable Version of Survey")."<\/a><br />";
	$menu['surveyactions']["3"]="<a onClick='rusurelink(\\\""._S_RENUMBERSURVEYWARNING."\\\", \\\"$homeurl/$scriptname?sid=$surveyid&dbaction=renumbersurvey\\\")' href='#'><img src='$imagefiles/renumber.gif' align='left' border='0' height='15' width='15'>"._S_RENUMBER_BT."<\/a><br />";

	
	if ($thissurvey['active'] == "Y") {
	    $menu['surveyactivation']["0"]="<a href='{$homeurl}/$scriptname?action=deactivate&amp;sid=$surveyid'><img src='$imagefiles/deactivate.png' align='left' border='0' height='15' width='15'>"._("De-activate this Survey")."<\/a><br />";
	} else {
		$menu['surveyactivation']["0"]="<a href='{$homeurl}/$scriptname?action=activate&amp;sid=$surveyid'><img src='$imagefiles/activate.png' align='left' border='0' height='15' width='15'>"._("Activate this Survey")."<\/a><br />";
	}

	$menu['surveyoptions']["0"]="<a href='{$homeurl}/$scriptname?sid=$surveyid&action=editsurvey'><img src='$imagefiles/edit.png' align='left' border='0' height='15' width='15'>"._("Edit Current Survey")."<\/a><br />";
	$menu['surveyoptions']["2"]="<a href='{$homeurl}/dumpsurvey.php?sid=$surveyid'><img src='$imagefiles/exportsql.png' align='left' border='0' height='15' width='15'>"._("Export this Survey")."<\/a><br />";
	if ($thissurvey['active'] != "Y") {
		$menu['surveyoptions']["1"]="<a href='{$homeurl}/$scriptname?sid=$surveyid&action=delsurvey' onclick=\\\"return confirm('"._("You are about to delete this survey")." "._("This process will delete this survey, and all related groups, questions answers and conditions.")."')\\\"><img src='$imagefiles/delete.png' align='left' border='0' height='15' width='15'>"._("Delete Current Survey")."<\/a><br />";
	}
	$menu['surveyoptions']["3"]="<a href='{$homeurl}/$scriptname?sid=$surveyid&action=showassessments'><img src='$imagefiles/assessments.png' align='left' border='0' height='15' width='15'>"._("Set Assessment Rules")."<\/a><br />";
	
	if ($thissurvey['active'] == "Y") {
		$menu['surveyactive']["0"]="<a href='{$homeurl}/browse.php?sid=$surveyid'><img src='$imagefiles/browse.png' align='left' border='0' height='15' width='15'>"._("Browse Responses for this Survey")."<\/a><br />";
		$menu['surveyactive']["1"]="<a href='{$homeurl}/statistics.php?sid=$surveyid'><img src='$imagefiles/statistics.png' alignt='left' border='0' height='15' width='15'>"._("Get statistics from these responses")."<\/a><br />";
		$menu['surveyactive']["2"]="<a href='{$homeurl}/tokens.php?sid=$surveyid'><img src='$imagefiles/tokens.png' align='left' border='0' height='15' width='15'>"._("Activate/Edit Tokens for this Survey")."<\/a><br />";	
		if ($thissurvey['allowsave'] == "Y") {
			$menu['surveyactive']["3"]="<a href='{$homeurl}/saved.php?sid=$surveyid'><img src='$imagefiles/saved.png' alignt='left' border='0' height='15' width='15'>"._("View Saved but not submitted Responses")."<\/a><br />";
		}
	}
	
	if (!empty($gid)) {
	    $menu['groupoptions']["0"]="<a href='{$homeurl}/$scriptname?sid=$surveyid&amp;gid=$gid&action=editgroup'><img src='$imagefiles/edit.png' align='left' border='0' height='15' width='15'>"._("Edit Current Group")."<\/a><br />";
		if ($thissurvey['active'] != "Y") {
			$menu['groupoptions']["1"] = "<a href='{$homeurl}/$scriptname?sid=$surveyid&amp;gid=$gid&action=delgroup'><img src='$imagefiles/delete.png' align='left' border='0' height='15' width='15'>"._("Delete Current Group")."<\/a><br />";
		}
		$menu['groupoptions']["2"]="<a href='{$homeurl}/dumpgroup.php?sid=$surveyid&amp;gid=$gid'><img src='$imagefiles/exportsql.png' align='left' border='0' height='15' width='15'>"._("Export Current Group")."<\/a><br />";
	
		$menu['groupactions']["0"]="<a href='$homeurl/$scriptname?sid=$surveyid&amp;gid=$gid&dbaction=renumbergroup'><img src='$imagefiles/renumber.gif' align='left' border='0' height='15' width='15'>"._G_RENUMBER_BT."<\/a><br />";
	}

	if (!empty($qid)) {
	    $menu['questionoptions']["0"]="<a href='{$homeurl}/$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&action=editquestion'><img src='$imagefiles/edit.png' align='left' border='0' height='15' width='15'>"._("Edit Current Question")."<\/a><br />";
	    if ($thissurvey['active'] != "Y") {
			$menu['questionoptions']["1"]="<a href='{$homeurl}/$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&action=delquestion'><img src='$imagefiles/delete.png' align='left' border='0' height='15' width='15'>"._("Delete Current Question")."<\/a><br />";
	    }
	    $menu['questionoptions']["2"]="<a href='{$homeurl}/dumpgroup.php?sid=$surveyid&amp;gid=$gid&amp;qid=$qid'><img src='$imagefiles/exportsql.png' align='left' border='0' height='15' width='15'>"._("Export this Question")."<\/a><br />";
	    $menu['questionoptions']["3"]="<a href='{$homeurl}/$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&action=copyquestion'><img src='{$imagefiles}/copy.png' align='left' border='0' height='15' width='15' alt='". _("Copy Current Question")."'>"._("Copy Current Question")."<\/a><br />";

		$thisquestion=getQuestionInfo($qid);
		switch($thisquestion['type']){
			case "L": case "M": case "O": case "!": case "A": case "B": case "C": case "E": case "F":
			case "P": case "Q": case "R": case "H": case "^":
				$menu["questionactions"]["0"]="<a href='{$homeurl}/$scriptname?sid=$surveyid&amp;gid=$gid&qid=$qid&action=showanswers'><img src='{$imagefiles}/answers.png' align='left' border='0' height='15' width='15'>"._("Edit/Add Answers for this Question")."<\/a><br />";
				break;
		} // switch
		$menu["questionactions"]["1"]="<a href='{$homeurl}/$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&action=showattributes'><img src='{$imagefiles}/answers.png' align='left' border='0' height='15' width='15'>"._("Question Attributes:")."<\/a><br />";
		$menu["questionactions"]["2"]="<a href=\\\"#\\\" onclick=\\\"window.open('{$homeurl}/conditions.php?sid=$surveyid&amp;qid=$qid', 'conditions', 'menubar=no, location=no, status=no, height=475, width=560, scrollbars=yes, resizable=yes, left=50, top=50')\\\"><img src='{$imagefiles}/conditions.png' align='left' border='0' height='15' width='15'  alt='". _("Set Conditions for this Question")."'>"._("Set Conditions for this Question")."</a><br />";
		$menu["questionactions"]["3"]="<a href='{$homeurl}/$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&action=showpreview'><img src='{$imagefiles}/templates.png' align='left' border='0' height='15' width='15'>"._Q_PREVIEWQUESTION."<\/a><br />";	
		if($thissurvey['active'] == "Y") {
			$menu['activequestion']["0"]="<a href='{$homeurl}/$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&action=showsummary'><img src='{$imagefiles}/browse.png' align='left' border='0' height='15' width='15'>"._Q_VIEWSUMMARY."<\/a><br />";
		}
	}
}							   
						   
$java="<script type=\"text/javascript\">
<!--
/****************************************************************
* AnyLink Drop Down Menu- (C)Dynamic Drive (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit http://www.dynamicdrive.com/ for full source code
*****************************************************************/

//Contents for menus
";
foreach($menu as $menuname=>$menuitem) {
	$java .= "\nvar $menuname=new Array()\n";
	//echo "<pre>";print_r($menuitem);echo "</pre>";
	foreach($menuitem as $name=>$text) {
		$java .= "{$menuname}[$name]=\"$text\"\n";
	}
}




//No more menus below here!
$java .= "
var menuwidth='185px' //default menu width
var menubgcolor='#999999'  //menu bgcolor
var disappeardelay=250  //menu disappear speed onMouseout (in miliseconds)
var hidemenu_onclick=\"yes\" //hide menu when user clicks within menu?

/////No further editting needed

var ie4=document.all
var ns6=document.getElementById&&!document.all

if (ie4||ns6)
document.write('<div id=\"dropmenudiv\" style=\"visibility:hidden;width:'+menuwidth+';background-color:'+menubgcolor+'\" onMouseover=\"clearhidemenu()\" onMouseout=\"dynamichide(event)\"><\/div>')

function getposOffset(what, offsettype){
var totaloffset=(offsettype==\"left\")? what.offsetLeft : what.offsetTop;
var parentEl=what.offsetParent;
while (parentEl!=null){
totaloffset=(offsettype==\"left\")? totaloffset+parentEl.offsetLeft : totaloffset+parentEl.offsetTop;
parentEl=parentEl.offsetParent;
}
return totaloffset;
}


function showhide(obj, e, visible, hidden, menuwidth){
if (ie4||ns6)
dropmenuobj.style.left=dropmenuobj.style.top=-500
if (menuwidth!=\"\"){
dropmenuobj.widthobj=dropmenuobj.style
dropmenuobj.widthobj.width=menuwidth
}
if (e.type==\"click\" && obj.visibility==hidden || e.type==\"mouseover\")
obj.visibility=visible
else if (e.type==\"click\")
obj.visibility=hidden
}

function iecompattest(){
return (document.compatMode && document.compatMode!=\"BackCompat\")? document.documentElement : document.body
}

function clearbrowseredge(obj, whichedge){
var edgeoffset=0
if (whichedge==\"rightedge\"){
var windowedge=ie4 && !window.opera? iecompattest().scrollLeft+iecompattest().clientWidth-15 : window.pageXOffset+window.innerWidth-15
dropmenuobj.contentmeasure=dropmenuobj.offsetWidth
if (windowedge-dropmenuobj.x < dropmenuobj.contentmeasure)
edgeoffset=dropmenuobj.contentmeasure-obj.offsetWidth
}
else{
var windowedge=ie4 && !window.opera? iecompattest().scrollTop+iecompattest().clientHeight-15 : window.pageYOffset+window.innerHeight-18
dropmenuobj.contentmeasure=dropmenuobj.offsetHeight
if (windowedge-dropmenuobj.y < dropmenuobj.contentmeasure)
edgeoffset=dropmenuobj.contentmeasure+obj.offsetHeight
}
return edgeoffset
}

function populatemenu(what){
if (ie4||ns6)
dropmenuobj.innerHTML=what.join(\"\")
}


function dropdownmenu(obj, e, menucontents, menuwidth){
if (window.event) event.cancelBubble=true
else if (e.stopPropagation) e.stopPropagation()
clearhidemenu()
dropmenuobj=document.getElementById? document.getElementById(\"dropmenudiv\") : dropmenudiv
populatemenu(menucontents)

if (ie4||ns6){
showhide(dropmenuobj.style, e, \"visible\", \"hidden\", menuwidth)
dropmenuobj.x=getposOffset(obj, \"left\")
dropmenuobj.y=getposOffset(obj, \"top\")
dropmenuobj.style.left=dropmenuobj.x-clearbrowseredge(obj, \"rightedge\")+\"px\"
dropmenuobj.style.top=dropmenuobj.y-clearbrowseredge(obj, \"bottomedge\")+obj.offsetHeight+\"px\"
}

return clickreturnvalue()
}

function clickreturnvalue(){
if (ie4||ns6) return false
else return true
}

function contains_ns6(a, b) {
while (b.parentNode)
if ((b = b.parentNode) == a)
return true;
return false;
}

function dynamichide(e){
if (ie4&&!dropmenuobj.contains(e.toElement))
delayhidemenu()
else if (ns6&&e.currentTarget!= e.relatedTarget&& !contains_ns6(e.currentTarget, e.relatedTarget))
delayhidemenu()
}

function hidemenu(e){
if (typeof dropmenuobj!=\"undefined\"){
if (ie4||ns6)
dropmenuobj.style.visibility=\"hidden\"
}
}

function delayhidemenu(){
if (ie4||ns6)
delayhide=setTimeout(\"hidemenu()\",disappeardelay)
}

function clearhidemenu(){
if (typeof delayhide!=\"undefined\")
clearTimeout(delayhide)
}

if (hidemenu_onclick==\"yes\")
document.onclick=hidemenu

//-->
</script>\n";
return $java;
}

function unlink_wc($dir, $pattern){
   if ($dh = opendir($dir)) { 
       
       //List and put into an array all files
       while (false !== ($file = readdir($dh))){
           if ($file != "." && $file != "..") {
               $files[] = $file;
           }
       }
       closedir($dh);
       
       
       //Split file name and extenssion
       if(strpos($pattern,".")) {
           $baseexp=substr($pattern,0,strpos($pattern,"."));
           $typeexp=substr($pattern,strpos($pattern,".")+1,strlen($pattern));
       }else{ 
           $baseexp=$pattern;
           $typeexp="";
       } 
       
       //Escape all regexp Characters 
       $baseexp=preg_quote($baseexp); 
       $typeexp=preg_quote($typeexp); 
       
       // Allow ? and *
       $baseexp=str_replace(array("\*","\?"), array(".*","."), $baseexp);
       $typeexp=str_replace(array("\*","\?"), array(".*","."), $typeexp);
       
       //Search for pattern match
       $i=0;
       foreach($files as $file) {
           $filename=basename($file);
           if(strpos($filename,".")) {
               $base=substr($filename,0,strpos($filename,"."));
               $type=substr($filename,strpos($filename,".")+1,strlen($filename));
           }else{
               $base=$filename;
               $type="";
           }
       
           if(preg_match("/^".$baseexp."$/i",$base) && preg_match("/^".$typeexp."$/i",$type))  {
               $matches[$i]=$file;
               $i++;
           }
       }
       if (isset($matches)) {
	       while(list($idx,$val) = each($matches)){
	           if (substr($dir,-1) == "/"){
	               unlink($dir.$val);
	           }else{
	               unlink($dir."/".$val);
	           }
       		}
       }
       
   }
}
?>
