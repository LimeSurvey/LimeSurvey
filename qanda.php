<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
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
/*
 * Let's explain what this strange $ia var means
 *
 * $ia[0] => question id
 * $ia[1] => fieldname
 * $ia[2] => title
 * $ia[3] => question text
 * $ia[4] => type --  text, radio, select, array, etc
 * $ia[5] => group id
 * $ia[6] => mandatory Y || N
 * $ia[7] => conditions ??
 *
 */
function retrieveConditionInfo($ia)
	{
	//This function returns an array containing all related conditions
	//for a question - the array contains the fields from the conditions table
	global $dbprefix;
	if ($ia[7] == "Y")
		{ //DEVELOP CONDITIONS ARRAY FOR THIS QUESTION
		$cquery = "SELECT {$dbprefix}conditions.qid, "
				."{$dbprefix}conditions.cqid, "
				."{$dbprefix}conditions.cfieldname, "
				."{$dbprefix}conditions.value, "
				."{$dbprefix}questions.type, "
				."{$dbprefix}questions.sid, "
				."{$dbprefix}questions.gid "
				."FROM {$dbprefix}conditions, "
				."{$dbprefix}questions "
				."WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid "
				."AND {$dbprefix}conditions.qid=$ia[0] "
				."ORDER BY {$dbprefix}conditions.cqid";
		$cresult = mysql_query($cquery) or die ("OOPS<BR />$cquery<br />".mysql_error());
		while ($crow = mysql_fetch_array($cresult))
			{
			$conditions[] = array ($crow['qid'], $crow['cqid'], $crow['cfieldname'], $crow['value'], $crow['type'], $crow['sid']."X".$crow['gid']."X".$crow['cqid']);
			}
		return $conditions;
		}
	else
		{
		return null;
		}
	}

function create_mandatorylist($ia)
	{
	//Checks current question and returns required mandatory arrays if required
	if ($ia[6] == "Y")
		{
		switch($ia[4])
			{
			case "R":
				$thismandatory=setman_ranking($ia);
				break;
			case "M":
			case "P":
			case "Q":
			case "A":
			case "B":
			case "C":
			case "E":
			case "F":
			case "H":
				$thismandatory=setman_questionandcode($ia);
				break;
			case "X":
				//Do nothing - boilerplate questions CANNOT be mandatory
				break;
			default:
				$thismandatory=setman_normal($ia);
			}
		if ($ia[7] != "Y" && isset($thismandatory)) //Question is not conditional - addto mandatory arrays
			{
			$mandatory=$thismandatory;
			}	
		if ($ia[7] == "Y" && isset($thismandatory)) //Question IS conditional - add to conmandatory arrays
			{
			$conmandatory=$thismandatory;
			}
		}
	
	if (isset($mandatory))
		{
	    return array($mandatory, null);
		}
	elseif (isset($conmandatory))
		{
	    return array(null, $conmandatory);
		}
	else
		{
		return array(null, null);
		}
	}

function setman_normal($ia)
	{
	$mandatorys[]=$ia[1];
	$mandatoryfns[]=$ia[1];
	return array($mandatorys, $mandatoryfns);
	}

function setman_ranking($ia)
	{
	global $dbprefix;
	$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
	$ansresult = mysql_query($ansquery);
	$anscount = mysql_num_rows($ansresult);
	for ($i=1; $i<=$anscount; $i++)
		{
		$mandatorys[]=$ia[1].$i;
		$mandatoryfns[]=$ia[1];
		}
	return array($mandatorys, $mandatoryfns);
	}

function setman_questionandcode($ia)
	{
	global $dbprefix;
	$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
	$qresult = mysql_query($qquery);
	while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
	$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
	$ansresult = mysql_query($ansquery);
	$anscount = mysql_num_rows($ansresult);
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$mandatorys[]=$ia[1].$ansrow['code'];
		$mandatoryfns[]=$ia[1];
		}
	if ($other == "Y")
		{
		$mandatorys[]=$ia[1]."other";
		$mandatoryfns[]=$ia[1];
		}
	return array($mandatorys, $mandatoryfns);
	}

function retrieveAnswers($ia, $notanswered=null, $notvalidated=null)
	{
	//This function returns an array containing the "question/answer" html display
	//and a list of the question/answer fieldnames associated. It is called from
	//question.php, group.php or survey.php

	//globalise required config variables
	global $dbprefix, $shownoanswer; //These are from the confir.php file
	//-----
	global $thissurvey, $gl; //These are set by index.php
	
	//DISPLAY
	$display = $ia[7];

	//QUESTION NAME
	$name = $ia[0];

	$qtitle=$ia[3];
	//Replace INSERTANS statements with previously provided answers;
	while (strpos($qtitle, "{INSERTANS:") !== false) 
		{
		$replace=substr($qtitle, strpos($qtitle, "{INSERTANS:"), strpos($qtitle, "}", strpos($qtitle, "{INSERTANS:"))-strpos($qtitle, "{INSERTANS:")+1);
		$replace2=substr($replace, 11, strpos($replace, "}", strpos($replace, "{INSERTANS:"))-11);
		$replace3=retrieve_Answer($replace2);
		$qtitle=str_replace($replace, $replace3, $qtitle);
		} //while
	
	//GET HELP
	$hquery="SELECT help FROM {$dbprefix}questions WHERE qid=$ia[0]";
	$hresult=mysql_query($hquery);
	$hcount=mysql_num_rows($hresult);
	if ($hcount > 0)
		{
		while ($hrow=mysql_fetch_array($hresult)) {$help=$hrow['help'];}
		}
	else
		{
		$help="";
		}

	//A bit of housekeeping to stop PHP Notices
	$answer = "";
	if (!isset($_SESSION[$ia[1]])) {$_SESSION[$ia[1]] = "";}
	$qidattributes=getQuestionAttributes($ia[0]);
	//echo "<pre>";print_r($qidattributes);echo "</pre>";
	//Create the question/answer html
	switch ($ia[4])
		{
		case "X": //BOILERPLATE QUESTION
			$values=do_boilerplate($ia);
			break;
		case "5": //5 POINT CHOICE radio-buttons
			$values=do_5pointchoice($ia);
			break;
		case "D": //DATE
			$qtitle = "<label for='{$ia[1]}'>$qtitle</label>";
			$values=do_date($ia);
			break;
		case "Z": //LIST Flexible drop-down/radio-button list
			$values=do_list_flexible_radio($ia);
			if (!$displaycols=arraySearchByKey("hide_tip", $qidattributes, "attribute", 1)) 
				{
				$qtitle .= "<br />\n</b><i><font size='1'>"
						 . _INSTRUCTION_LIST."</font></i><b>";
				}
			break;
		case "L": //LIST drop-down/radio-button list
			$values=do_list_radio($ia);
			if (!$displaycols=arraySearchByKey("hide_tip", $qidattributes, "attribute", 1)) 
				{
				$qtitle .= "<br />\n</b><i><font size='1'>"
						 . _INSTRUCTION_LIST."</font></i><b>";
				}
			break;
		case "W": //List - dropdown
			$values=do_list_flexible_dropdown($ia);
			if (!$displaycols=arraySearchByKey("hide_tip", $qidattributes, "attribute", 1)) 
				{
				$qtitle .= "<br />\n</b><i><font size='1'>"
						 . _INSTRUCTION_LIST."</font></i><b>";
				}
			break;
		case "!": //List - dropdown
			$values=do_list_dropdown($ia);
			if (!$displaycols=arraySearchByKey("hide_tip", $qidattributes, "attribute", 1)) 
				{
				$qtitle .= "<br />\n</b><i><font size='1'>"
						 . _INSTRUCTION_LIST."</font></i><b>";
				}
			break;
		case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
			$values=do_listwithcomment($ia);
			if (count($values[1]) > 1 && !$displaycols=arraySearchByKey("hide_tip", $qidattributes, "attribute", 1)) 
				{
				$qtitle .= "<br />\n</b><i><font size='1'>"
						 . _INSTRUCTION_LIST."</font></i><b>";
				}
			break;
		case "R": //RANKING STYLE
			$values=do_ranking($ia);
			break;
		case "M": //MULTIPLE OPTIONS checkbox
			$values=do_multiplechoice($ia);
			if (count($values[1]) > 1 && !$displaycols=arraySearchByKey("hide_tip", $qidattributes, "attribute", 1)) 
				{
					$qtitle .= "<br />\n</b><i><font size='1'>"
						 . _INSTRUCTION_MULTI."</font></i><b>";
				}
			break;
		case "P": //MULTIPLE OPTIONS WITH COMMENTS checkbox + text
			$values=do_multiplechoice_withcomments($ia);
			if (count($values[1]) > 1 && !$displaycols=arraySearchByKey("hide_tip", $qidattributes, "attribute", 1)) 
				{
				$qtitle .= "<br />\n</b><i><font size='1'>"
						 . _INSTRUCTION_MULTI."</font></i><b>";
				}
			break;
		case "Q": //MULTIPLE SHORT TEXT
			$values=do_multipleshorttext($ia);
			break;
		case "N": //NUMERICAL QUESTION TYPE
			$qtitle = "<label for='{$ia[1]}'>$qtitle</label>";
			$values=do_numerical($ia);
			break;
		case "S": //SHORT FREE TEXT
			$qtitle = "<label for='{$ia[1]}'>$qtitle</label>";
			$values=do_shortfreetext($ia);
			break;
		case "T": //LONG FREE TEXT
			$qtitle = "<label for='{$ia[1]}'>$qtitle</label>";
			$values=do_longfreetext($ia);
			break;
		case "U": //HUGE FREE TEXT
			$qtitle = "<label for='{$ia[1]}'>$qtitle</label>";
			$values=do_hugefreetext($ia);
			break;
		case "Y": //YES/NO radio-buttons
			$values=do_yesno($ia);
			break;
		case "G": //GENDER drop-down list
			$values=do_gender($ia);
			break;
		case "A": //ARRAY (5 POINT CHOICE) radio-buttons
			$values=do_array_5point($ia);
			break;
		case "B": //ARRAY (10 POINT CHOICE) radio-buttons
			$values=do_array_10point($ia);
			break;
		case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
			$values=do_array_yesnouncertain($ia);
			break;
		case "E": //ARRAY (Increase/Same/Decrease) radio-buttons
			$values=do_array_increasesamedecrease($ia);
			break;
		case "F": //ARRAY (Flexible) - Row Format
			$values=do_array_flexible($ia);
			break;
		case "H": //ARRAY (Flexible) - Column Format
			$values=do_array_flexiblecolumns($ia);
			break;		
		} //End Switch

	if (isset($values)) //Break apart $values array returned from switch
		{
		//$answer is the html code to be printed
		//$inputnames is an array containing the names of each input field
		list($answer, $inputnames)=$values;
		}

	$answer .= "\n\t\t\t<input type='hidden' name='display$ia[1]' id='display$ia[0]' value='";
	if ($thissurvey['format'] == "S")
		{
	    $answer .= "on"; //Ifthis is single format, then it must be showing. Needed for checking conditional mandatories
		}
	$answer .= "'>\n"; //for conditional mandatory questions
	
	if ($ia[6] == "Y")
		{
	    $qtitle = _REQUIRED.$qtitle;
		}
	//If this question is mandatory but wasn't answered in the last page
	//add a message HIGHLIGHTING the question
	$qtitle .= mandatory_message($ia);

	$qtitle .= validation_message($ia);
	
	$qanda=array($qtitle, $answer, $help, $display, $name, $ia[2], $gl[0], $ia[1]);
	//New Return
	return array($qanda, $inputnames);
	}

function validation_message($ia)
	{
	//This function checks to see if this question requires validation and
	//that validation has not been met.
	global $notvalidated, $dbprefix;
	$help="";
	$helpselect="SELECT help\n"
			   ."FROM {$dbprefix}questions\n"
			   ."WHERE qid={$ia[0]}";
	$helpresult=mysql_query($helpselect) or die("$helpselect<br />".mysql_error());
	while ($helprow=mysql_fetch_array($helpresult))
		{
		$help=" <i>(".$helprow['help'].")</i>";
		}
	$qtitle="";
	if (isset($notvalidated) && is_array($notvalidated)) //ADD WARNINGS TO QUESTIONS IF THEY ARE NOT VALID
		{
	    global $validationpopup, $popup;
		if (in_array($ia[1], $notvalidated))
			{
		    $qtitle .= "</b><br /><span class='errormandatory'>"._VALIDATION." $help</span><b><br />\n";
			}
		}
	return $qtitle;
	}

function mandatory_message($ia)
	{
	//This function checks to see if this question is mandatory and
	//is being re-displayed because it wasn't answered. It returns
	global $notanswered;
	$qtitle="";
	if (isset($notanswered) && is_array($notanswered)) //ADD WARNINGS TO QUESTIONS IF THEY WERE MANDATORY BUT NOT ANSWERED
		{
		global $mandatorypopup, $popup;
		if (in_array($ia[1], $notanswered))
			{
			$qtitle .= "</b><br /><span class='errormandatory'>"._MANDATORY.".";
			switch($ia[4])
				{
				case "A":
				case "B":
				case "C":
				case "Q":
				case "F":
				case "H":
					$qtitle .= "<br />\n"._MANDATORY_PARTS.".";
					break;
				case "R": 
					$qtitle .= "<br />\n"._MANDATORY_RANK.".";
					break;
				case "M": 
				case "P":
					$qtitle .= "<br />\n"._MANDATORY_CHECK.".";
					break;
				} // end switch
			$qtitle .= "</font></span><b><br />\n";
			}
		}
	return $qtitle;
	}
	
function mandatory_popup($ia, $notanswered=null)
	{
	//This sets the mandatory popup message to show if required
	//Called from question.php, group.php or surveyo.php
	if ($notanswered === null) {unset($notanswered);}
	$qtitle="";
	if (isset($notanswered) && is_array($notanswered)) //ADD WARNINGS TO QUESTIONS IF THEY WERE MANDATORY BUT NOT ANSWERED
		{
		global $mandatorypopup, $popup;
		//POPUP WARNING
		if (!isset($mandatorypopup))
			{
			$popup="<script type=\"text/javascript\">\n<!--\n alert(\""._MANDATORY_POPUP."\")\n //-->\n</script>\n";
			$mandatorypopup="Y";
			}
		return array($mandatorypopup, $popup);
		}
	else
		{
		return false;
		}
	}

function validation_popup($ia, $notvalidated=null)
	{
	//This sets the validation popup message to show if required
	//Called from question.php, group.php or survey.php
	if ($notvalidated === null) {unset($notvalidated);}
	$qtitle="";
	if (isset($notvalidated) && is_array($notvalidated))  //ADD WARNINGS TO QUESTIONS IF THEY ARE NOT VALID
		{
	    global $validationpopup, $vpopup;
		//POPUP WARNING
		if (!isset($validationpopup))
			{
		    $vpopup = "<script type\"text/javascript\">\n<!--\n alert(\""._VALIDATION_POPUP."\")\n //-->\n</script>\n";
			$validationpopup="Y";
			}
		return array($validationpopup, $vpopup);
		}
	else
		{
		return false;
		}
	}
//QUESTION METHODS
function do_boilerplate($ia)
	{
	$answer="";
	$inputnames[]="";
	return array($answer, $inputnames);
	}

function do_5pointchoice($ia)
	{
	$answer="";
	for ($fp=1; $fp<=5; $fp++)
		{
		$answer .= "\t\t\t<input class='radio' type='radio' name='$ia[1]' id='$ia[1]$fp' value='$fp'";
		if ($_SESSION[$ia[1]] == $fp) {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]$fp' class='answertext'>$fp</label>\n";
		}
	if ($ia[6] != "Y") // Add "No Answer" option if question is not mandatory
		{
		$answer .= "\t\t\t<input class='radio' type='radio' name='$ia[1]' id='NoAnswer' value=''";
		if (!$_SESSION[$ia[1]]) {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='NoAnswer'>"._NOTAPPLICABLE."</label>\n";
		}
	$answer .= "\t\t\t<input type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n";
	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
	}

function do_date($ia)
	{
	$answer = "\t\t\t<input class='text' type='text' size=10 name='$ia[1]' id='{$ia[1]}' value=\"".$_SESSION[$ia[1]]."\" />\n"
			 . "\t\t\t<table class='question'>\n"
			 . "\t\t\t\t<tr>\n"
			 . "\t\t\t\t\t<td>\n"
			 . "\t\t\t\t\t\t<font size='1'>"._DATEFORMAT."<br />\n"
			 . "\t\t\t\t\t\t"._DATEFORMATEG."\n"
			 . "\t\t\t\t\t</font></td>\n"
			 . "\t\t\t\t</tr>\n"
			 . "\t\t\t</table>\n";
	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
	}

function do_list_dropdown($ia)
	{
	global $dbprefix, $dropdowns, $dropdownthreshold, $lwcdropdowns;
	global $shownoanswer;
	$qidattributes=getQuestionAttributes($ia[0]);
	$answer="";
	if (isset($defexists)) {unset ($defexists);}
	$query = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
	$result = mysql_query($query);
	while($row = mysql_fetch_array($result)) {$other = $row['other'];}
	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY RAND()";
	} else {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY sortorder, answer";
	}
	$ansresult = mysql_query($ansquery) or die("Couldn't get answers<br />$ansquery<br />".mysql_error());
	$anscount = mysql_num_rows($ansresult);
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$answer .= "\t\t\t\t\t\t<option value='{$ansrow['code']}'";
		if ($_SESSION[$ia[1]] == $ansrow['code'])
			{
			$answer .= " selected"; 
			}
		elseif ($ansrow['default_value'] == "Y") {$answer .= " selected"; $defexists = "Y";}
		$answer .= ">{$ansrow['answer']}</option>\n";
		}
	if (!$_SESSION[$ia[1]] && (!isset($defexists) || !$defexists)) {$answer = "\t\t\t\t\t\t<option value='' selected>"._PLEASECHOOSE."..</option>\n".$answer;}
	if (isset($other) && $other=="Y") 
		{
		$answer .= "\t\t\t\t\t\t<option value='-oth-'";
		if ($_SESSION[$ia[1]] == "-oth-")
			{
			$answer .= " selected";
			}
		$answer .= ">"._OTHER."</option>\n";
		}
	if ($_SESSION[$ia[1]] && (!isset($defexists) || !$defexists) && $ia[6] != "Y" && $shownoanswer == 1) {$answer .= "\t\t\t\t\t\t<option value=' '>"._NOANSWER."</option>\n";}
	$answer .= "\t\t\t\t\t</select>\n";
	$sselect = "\n\t\t\t\t\t<select name='$ia[1]' id='$ia[1]' onChange='checkconditions(this.value, this.name, this.type)";
	if (isset($other) && $other=="Y")
		{
		$sselect .= "; showhideother(this.name, this.value)";
		}
	$sselect .= "'>\n";
	$answer = $sselect.$answer;
	if (isset($other) && $other=="Y") 
		{
		$answer = "\n<SCRIPT TYPE=\"text/javascript\">\n"
			."<!--\n"
			."function showhideother(name, value)\n"
			."\t{\n"
			."\tvar hiddenothername='othertext'+name;\n"
			."\tif (value == \"-oth-\")\n"
			."\t\t{\n"
			."\t\tdocument.getElementById(hiddenothername).style.display='';\n"
			."\t\tdocument.getElementById(hiddenothername).focus();\n"
			."\t\t}\n"
			."\telse\n"
			."\t\t{\n"
			."\t\tdocument.getElementById(hiddenothername).style.display='none';\n"
			."\t\t}\n"
			."\t}\n"
			."//--></SCRIPT>\n".$answer;
		$answer .= "<input type='text' id='othertext".$ia[1]."' name='$ia[1]other' style='display:";
		if ($_SESSION[$ia[1]] != "-oth-")
			{
			$answer .= " none";
			}
		$answer .= "'>";
		}

	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
	}

function do_list_flexible_dropdown($ia)
	{
	global $dbprefix, $dropdowns, $dropdownthreshold, $lwcdropdowns;
	global $shownoanswer;
	$qidattributes=getQuestionAttributes($ia[0]);
	$answer="";
	$qquery = "SELECT other, lid FROM {$dbprefix}questions WHERE qid=".$ia[0];
	$qresult = mysql_query($qquery);
	while($row = mysql_fetch_array($qresult)) {$other = $row['other']; $lid=$row['lid'];}
	$filter="";
	if ($code_filter=arraySearchByKey("code_filter", $qidattributes, "attribute", 1))
		{
		$filter=$code_filter['value'];
		if(in_array($filter, $_SESSION['insertarray']))
			{
			$filter=$_SESSION[$filter];
			}
		}
	$filter .= "%";
	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND code LIKE '$filter' ORDER BY RAND()";
	} else {
		$ansquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND code LIKE '$filter' ORDER BY sortorder, code";
	}
	$ansresult = mysql_query($ansquery) or die("Couldn't get answers<br />$ansquery<br />".mysql_error());
	$anscount = mysql_num_rows($ansresult);
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$answer .= "\t\t\t\t\t\t<option value='{$ansrow['code']}'";
		if ($_SESSION[$ia[1]] == $ansrow['code'])
			{
			$answer .= " selected"; 
			}
		$answer .= ">{$ansrow['title']}</option>\n";
		}
	if (!$_SESSION[$ia[1]] && (!isset($defexists) || !$defexists)) {$answer = "\t\t\t\t\t\t<option value='' selected>"._PLEASECHOOSE."..</option>\n".$answer;}
	if (isset($other) && $other=="Y") 
		{
		$answer .= "\t\t\t\t\t\t<option value='-oth-'";
		if ($_SESSION[$ia[1]] == "-oth-")
			{
			$answer .= " selected";
			}
		$answer .= ">"._OTHER."</option>\n";
		}
	if ($_SESSION[$ia[1]] && (!isset($defexists) || !$defexists) && $ia[6] != "Y" && $shownoanswer == 1) {$answer .= "\t\t\t\t\t\t<option value=' '>"._NOANSWER."</option>\n";}
	$answer .= "\t\t\t\t\t</select>\n";
	$sselect = "\n\t\t\t\t\t<select name='$ia[1]' id='$ia[1]' onChange='checkconditions(this.value, this.name, this.type)";
	if (isset($other) && $other=="Y")
		{
		$sselect .= "; showhideother(this.name, this.value)";
		}
	$sselect .= "'>\n";
	$answer = $sselect.$answer;
	if (isset($other) && $other=="Y") 
		{
		$answer = "\n<SCRIPT TYPE=\"text/javascript\">\n"
			."<!--\n"
			."function showhideother(name, value)\n"
			."\t{\n"
			."\tvar hiddenothername='othertext'+name;\n"
			."\tif (value == \"-oth-\")\n"
			."\t\t{\n"
			."\t\tdocument.getElementById(hiddenothername).style.display='';\n"
			."\t\tdocument.getElementById(hiddenothername).focus();\n"
			."\t\t}\n"
			."\telse\n"
			."\t\t{\n"
			."\t\tdocument.getElementById(hiddenothername).style.display='none';\n"
			."\t\t}\n"
			."\t}\n"
			."//--></SCRIPT>\n".$answer;
		$answer .= "<input type='text' id='othertext".$ia[1]."' name='$ia[1]other' style='display:";
		if ($_SESSION[$ia[1]] != "-oth-")
			{
			$answer .= " none";
			}
		$answer .= "'>";
		}

	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
	}

function do_list_radio($ia)
	{
	global $dbprefix, $dropdowns, $dropdownthreshold, $lwcdropdowns;
	global $shownoanswer;
	$answer="";
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($displaycols=arraySearchByKey("display_columns", $qidattributes, "attribute", 1))
		{
	    $dcols=$displaycols['value'];
		}
	else
		{
		$dcols=0;
		}
	if (isset($defexists)) {unset ($defexists);}
	$query = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
	$result = mysql_query($query);
	while($row = mysql_fetch_array($result)) {$other = $row['other'];}
	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY RAND()";
	} else {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY sortorder, answer";
	}
	$ansresult = mysql_query($ansquery) or die("Couldn't get answers<br />$ansquery<br />".mysql_error());
	$anscount = mysql_num_rows($ansresult);
	if ((isset($other) && $other=="Y") || ($ia[6] != "Y" && $shownoanswer == 1)) {$anscount++;} //Count "
	$divider="";
	$maxrows=0;
	if ($dcols >0 && $anscount > $dcols) //Break into columns
		{
	    $denominator=$dcols; //Change this to set the number of columns
		$width=sprintf("%0d", 100/$denominator);
		$maxrows=ceil(100*($anscount/$dcols)/100); //Always rounds up to nearest whole number
		$answer .= "<table class='question'><tr>\n <td valign='top' width='$width%' nowrap>";
		$divider=" </td>\n <td valign='top' width='$width%' nowrap>";
		}
	else 
		{
		$answer .= "\n\t\t\t\t\t<table class='question'>\n"
				 . "\t\t\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t\t\t<td>\n";
		}
	$rowcounter=0;
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$rowcounter++;
		$answer .= "\t\t\t\t\t\t\t\t  <input class='radio' type='radio' value='{$ansrow['code']}' name='$ia[1]' id='$ia[1]{$ansrow['code']}'";
		if ($_SESSION[$ia[1]] == $ansrow['code'])
			{
			$answer .= " checked";
			}
		elseif ($ansrow['default_value'] == "Y") {$answer .= " checked"; $defexists = "Y";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]{$ansrow['code']}' class='answertext'>{$ansrow['answer']}</label><br />\n";
		if ($rowcounter==$maxrows) {$answer .= $divider; $rowcounter=0;}
		}
	if (isset($other) && $other=="Y") 
		{
		$rowcounter++;
	    $answer .= "\t\t\t\t\t\t\t\t  <input class='radio' type='radio' value='-oth-' name='$ia[1]' id='SOTH$ia[1]'";
		if ($_SESSION[$ia[1]] == "-oth-") 
			{
		    $answer .= " checked";
			}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='SOTH$ia[1]' class='answertext'>"._OTHER."</label>\n";
		$answer .= "<label for='$ia[1]othertext'><input type='text' class='text' id='$ia[1]othertext' name='$ia[1]other' size='20' title='"._OTHER."' ";
		$thisfieldname=$ia[1]."other";
		if (isset($_SESSION[$thisfieldname])) { $answer .= "value='".$_SESSION[$thisfieldname]."' ";}
		$answer .= "onclick=\"javascript:document.getElementById('SOTH$ia[1]').checked=true; checkconditions(document.getElementById('SOTH$ia[1]').value, document.getElementById('SOTH$ia[1]').name, document.getElementById('SOTH$ia[1]').type)\"></label><br />\n";
		$inputnames[]=$thisfieldname;
		if ($rowcounter==$maxrows) {$answer .= $divider; $rowcounter=0;}
		}
	if ($ia[6] != "Y" && $shownoanswer == 1)
		{
		$rowcounter++;
		$answer .= "\t\t\t\t\t\t  <input class='radio' type='radio' name='$ia[1]' id='$ia[1]NANS' value=' ' ";
		if ((!isset($defexists) || $defexists != "Y") && (!isset($_SESSION[$ia[1]]) || !$_SESSION[$ia[1]]))
			{
			$answer .= " checked"; //Check the "no answer" radio button if there is no default, and user hasn't answered this.
			}
		$answer .=" onClick='checkconditions(this.value, this.name, this.type)' />"
				 . "<label for='$ia[1]NANS' class='answertext'>"._NOANSWER."</label>\n";
		if ($rowcounter==$maxrows) {$answer .= $divider; $rowcounter=0;}
		}
	$answer .= "\t\t\t\t\t\t\t</td>\n"
			 . "\t\t\t\t\t\t</tr>\n"
			 . "\t\t\t\t\t\t<input type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n"
			 . "\t\t\t\t\t</table>\n";
	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
	}

function do_list_flexible_radio($ia)
	{
	global $dbprefix, $dropdowns, $dropdownthreshold, $lwcdropdowns;
	global $shownoanswer;
	$answer="";
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($displaycols=arraySearchByKey("display_columns", $qidattributes, "attribute", 1))
		{
	    $dcols=$displaycols['value'];
		}
	else
		{
		$dcols=0;
		}
	if (isset($defexists)) {unset ($defexists);}
	$query = "SELECT other, lid FROM {$dbprefix}questions WHERE qid=".$ia[0];
	$result = mysql_query($query);
	while($row = mysql_fetch_array($result)) {$other = $row['other']; $lid = $row['lid'];}
	$filter="";
	if ($code_filter=arraySearchByKey("code_filter", $qidattributes, "attribute", 1))
		{
		$filter=$code_filter['value'];
		if(in_array($filter, $_SESSION['insertarray']))
			{
			$filter=$_SESSION[$filter];
			}
		}
	$filter .= "%";
	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND code LIKE '$filter' ORDER BY RAND()";
	} else {
		$ansquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND code LIKE '$filter' ORDER BY sortorder, code";
	}
	$ansresult = mysql_query($ansquery) or die("Couldn't get answers<br />$ansquery<br />".mysql_error());
	$anscount = mysql_num_rows($ansresult);
	if ((isset($other) && $other=="Y") || ($ia[6] != "Y" && $shownoanswer == 1)) {$anscount++;} //Count "
	$divider="";
	$maxrows=0;
	if ($dcols >0 && $anscount > $dcols) //Break into columns
		{
	    $denominator=$dcols; //Change this to set the number of columns
		$width=sprintf("%0d", 100/$denominator);
		$maxrows=ceil(100*($anscount/$dcols)/100); //Always rounds up to nearest whole number
		$answer .= "<table class='question'><tr>\n <td valign='top' width='$width%' nowrap>";
		$divider=" </td>\n <td valign='top' width='$width%' nowrap>";
		}
	else 
		{
		$answer .= "\n\t\t\t\t\t<table class='question'>\n"
				 . "\t\t\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t\t\t<td>\n";
		}
	$rowcounter=0;
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$rowcounter++;
		$answer .= "\t\t\t\t\t\t\t\t  <input class='radio' type='radio' value='{$ansrow['code']}' name='$ia[1]' id='$ia[1]{$ansrow['code']}'";
		if ($_SESSION[$ia[1]] == $ansrow['code'])
			{
			$answer .= " checked";
			}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]{$ansrow['code']}' class='answertext'>{$ansrow['title']}</label><br />\n";
		if ($rowcounter==$maxrows) {$answer .= $divider; $rowcounter=0;}
		}
	if (isset($other) && $other=="Y") 
		{
		$rowcounter++;
	    $answer .= "\t\t\t\t\t\t\t\t  <input class='radio' type='radio' value='-oth-' name='$ia[1]' id='SOTH$ia[1]'";
		if ($_SESSION[$ia[1]] == "-oth-") 
			{
		    $answer .= " checked";
			}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='SOTH$ia[1]' class='answertext'>"._OTHER."</label>\n";
		$answer .= "<label for='$ia[1]othertext'><input type='text' class='text' id='$ia[1]othertext' name='$ia[1]other' size='20' title='"._OTHER."' ";
		$thisfieldname=$ia[1]."other";
		if (isset($_SESSION[$thisfieldname])) { $answer .= "value='".$_SESSION[$thisfieldname]."' ";}
		$answer .= "onclick=\"javascript:document.getElementById('SOTH$ia[1]').checked=true; checkconditions(document.getElementById('SOTH$ia[1]').value, document.getElementById('SOTH$ia[1]').name, document.getElementById('SOTH$ia[1]').type)\"></label><br />\n";
		$inputnames[]=$thisfieldname;
		if ($rowcounter==$maxrows) {$answer .= $divider; $rowcounter=0;}
		}
	if ($ia[6] != "Y" && $shownoanswer == 1)
		{
		$rowcounter++;
		$answer .= "\t\t\t\t\t\t  <input class='radio' type='radio' name='$ia[1]' id='$ia[1]NANS' value=' ' ";
		if ((!isset($defexists) || $defexists != "Y") && (!isset($_SESSION[$ia[1]]) || !$_SESSION[$ia[1]]))
			{
			$answer .= " checked"; //Check the "no answer" radio button if there is no default, and user hasn't answered this.
			}
		$answer .=" onClick='checkconditions(this.value, this.name, this.type)' />"
				 . "<label for='$ia[1]NANS' class='answertext'>"._NOANSWER."</label>\n";
		if ($rowcounter==$maxrows) {$answer .= $divider; $rowcounter=0;}
		}
	$answer .= "\t\t\t\t\t\t\t</td>\n"
			 . "\t\t\t\t\t\t</tr>\n"
			 . "\t\t\t\t\t\t<input type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n"
			 . "\t\t\t\t\t</table>\n";
	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
	}

function do_listwithcomment($ia)
	{
	global $maxoptionsize, $dbprefix, $dropdowns, $dropdownthreshold, $lwcdropdowns;
	global $shownoanswer;
	$answer="";
	$qidattributes=getQuestionAttributes($ia[0]);
	if (!isset($maxoptionsize)) {$maxoptionsize=35;}
	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY RAND()";
	} else {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY sortorder, answer";
	}
	$ansresult = mysql_query($ansquery);
	$anscount = mysql_num_rows($ansresult);
	if ($lwcdropdowns == "R" && $anscount <= $dropdownthreshold)
		{
		$answer .= "\t\t\t<table class='question'>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td><u>"._CHOOSEONE.":</u></td>\n"
				 . "\t\t\t\t\t<td><u><label for='$ia[1]comment'>"._ENTERCOMMENT.":</label></td>\n"
				 . "\t\t\t\t</tr>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td valign='top'>\n";
		
		while ($ansrow=mysql_fetch_array($ansresult))
			{
			$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' value='{$ansrow['code']}' name='$ia[1]' id='$ia[1]{$ansrow['code']}'";
			if ($_SESSION[$ia[1]] == $ansrow['code'])
				{$answer .= " checked";}
			elseif ($ansrow['default_value'] == "Y") {$answer .= " checked"; $defexists = "Y";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]{$ansrow['code']}'>{$ansrow['answer']}</label><br />\n";
			}
		if ($ia[6] != "Y" && $shownoanswer == 1)
			{
			$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' id='$ia[1]' value=' ' onClick='checkconditions(this.value, this.name, this.type)' ";
			if ((!$_SESSION[$ia[1]] && (!isset($defexists) || !$defexists)) ||($_SESSION[$ia[1]] == ' ' && (!isset($defexists) || !$defexists))) 
				{
				$answer .= "checked />";
				}
			elseif ($_SESSION[$ia[1]] && (!isset($defexists) || !$defexists)) 
				{
				$answer .= " />";
				}
			$answer .= "<label for='$ia[1] ' class='answertext'>"._NOANSWER."</label>\n";
			}
		$answer .= "\t\t\t\t\t</td>\n";
		$fname2 = $ia[1]."comment";
		if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
		$answer .= "\t\t\t\t\t<td valign='top'>\n"
				 . "\t\t\t\t\t\t<textarea class='textarea' name='$ia[1]comment' id='$ia[1]comment' rows='$tarows' cols='30'>";
		if (isset($_SESSION[$fname2]) && $_SESSION[$fname2]) 
			{
			$answer .= str_replace("\\", "", $_SESSION[$fname2]);
			}
		$answer .= "</textarea>\n"
				 . "\t\t\t\t\t</td>\n"
				 . "\t\t\t\t</tr>\n"
				 . "\t\t\t\t<input class='radio' type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n"
				 . "\t\t\t</table>\n";
		$inputnames[]=$ia[1];
		$inputnames[]=$ia[1]."comment";
		}
	else //Dropdown list
		{
		$answer .= "\t\t\t<table class='question'>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td valign='top' align='center'>\n"
				 . "\t\t\t\t\t<select class='select' name='$ia[1]' id='$ia[1]' onClick='checkconditions(this.value, this.name, this.type)'>\n";
		while ($ansrow=mysql_fetch_array($ansresult))
			{
			$answer .= "\t\t\t\t\t\t<option value='{$ansrow['code']}'";
			if ($_SESSION[$ia[1]] == $ansrow['code'])
				{$answer .= " selected";}
			elseif ($ansrow['default_value'] == "Y") {$answer .= " selected"; $defexists = "Y";}
			$answer .= ">{$ansrow['answer']}</option>\n";
			if (strlen($ansrow['answer']) > $maxoptionsize) 
				{
				$maxoptionsize = strlen($ansrow['answer']);
				}
			}
		if ($ia[6] != "Y" && $shownoanswer == 1)
			{
			if ((!$_SESSION[$ia[1]] && !$defexists) ||($_SESSION[$ia[1]] == ' ' && !$defexists)) 
				{
				$answer .= "\t\t\t\t\t\t<option value=' ' selected>"._NOANSWER."</option>\n";
				}
			elseif ($_SESSION[$ia[1]] && !$defexists) 
				{
				$answer .= "\t\t\t\t\t\t<option value=' '>"._NOANSWER."</option>\n";
				}
			}
		$answer .= "\t\t\t\t\t</select>\n"
				 . "\t\t\t\t\t</td>\n"
				 . "\t\t\t\t</tr>\n"
				 . "\t\t\t\t<tr>\n";
		$fname2 = $ia[1]."comment";
		if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
		if ($tarows > 15) {$tarows=15;}
		$maxoptionsize=$maxoptionsize*0.72;
		if ($maxoptionsize < 33) {$maxoptionsize=33;}
		if ($maxoptionsize > 70) {$maxoptionsize=70;}
		$answer .= "\t\t\t\t\t<td valign='top'>\n";
		$answer .= "\t\t\t\t\t\t<textarea class='textarea' name='$ia[1]comment' id='$ia[1]comment' rows='$tarows' cols='$maxoptionsize'>";
		if (isset($_SESSION[$fname2]) && $_SESSION[$fname2])
			{
			$answer .= str_replace("\\", "", $_SESSION[$fname2]);
			}
		$answer .= "</textarea>\n"
				 . "\t\t\t\t\t</td>\n"
				 . "\t\t\t\t</tr>\n"
				 . "\t\t\t\t<input class='radio' type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n"
				 . "\t\t\t</table>\n";
		$inputnames[]=$ia[1];
		$inputnames[]=$ia[1]."comment";
		}
	return array($answer, $inputnames);
	}

function do_ranking($ia)
	{
	global $dbprefix;
	$qidattributes=getQuestionAttributes($ia[0]);
	$answer="";
	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY RAND()";
	} else {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY sortorder, answer";
	}
	$ansresult = mysql_query($ansquery);
	$anscount = mysql_num_rows($ansresult);
	$answer .= "\t\t\t<script type='text/javascript'>\n"
			 . "\t\t\t<!--\n"
			 . "\t\t\t\tfunction rankthis_{$ia[0]}(\$code, \$value)\n"
			 . "\t\t\t\t\t{\n"
			 . "\t\t\t\t\t\$index=document.phpsurveyor.CHOICES_{$ia[0]}.selectedIndex;\n"
			 . "\t\t\t\t\tdocument.phpsurveyor.CHOICES_{$ia[0]}.selectedIndex=-1;\n"
			 . "\t\t\t\t\tfor (i=1; i<=$anscount; i++)\n"
			 . "\t\t\t\t\t\t{\n"
			 . "\t\t\t\t\t\t\$b=i;\n"
			 . "\t\t\t\t\t\t\$b += '';\n"
			 . "\t\t\t\t\t\t\$inputname=\"RANK_{$ia[0]}\"+\$b;\n"
			 . "\t\t\t\t\t\t\$hiddenname=\"fvalue_{$ia[0]}\"+\$b;\n"
			 . "\t\t\t\t\t\t\$cutname=\"cut_{$ia[0]}\"+i;\n"
			 . "\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='none';\n"
			 . "\t\t\t\t\t\tif (!document.getElementById(\$inputname).value)\n"
			 . "\t\t\t\t\t\t\t{\n"
			 . "\t\t\t\t\t\t\tdocument.getElementById(\$inputname).value=\$value;\n"
			 . "\t\t\t\t\t\t\tdocument.getElementById(\$hiddenname).value=\$code;\n"
			 . "\t\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='';\n"
			 . "\t\t\t\t\t\t\tfor (var b=document.getElementById('CHOICES_{$ia[0]}').options.length-1; b>=0; b--)\n"
			 . "\t\t\t\t\t\t\t\t{\n"
			 . "\t\t\t\t\t\t\t\tif (document.getElementById('CHOICES_{$ia[0]}').options[b].value == \$code)\n"
			 . "\t\t\t\t\t\t\t\t\t{\n"
			 . "\t\t\t\t\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').options[b] = null;\n"
			 . "\t\t\t\t\t\t\t\t\t}\n"
			 . "\t\t\t\t\t\t\t\t}\n"
			 . "\t\t\t\t\t\t\ti=$anscount;\n"
			 . "\t\t\t\t\t\t\t}\n"
			 . "\t\t\t\t\t\t}\n"
			 . "\t\t\t\t\tif (document.getElementById('CHOICES_{$ia[0]}').options.length == 0)\n"
			 . "\t\t\t\t\t\t{\n"
			 . "\t\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').disabled=true;\n"
			 . "\t\t\t\t\t\t}\n"
			 . "\t\t\t\t\tcheckconditions(\$code);\n"
			 . "\t\t\t\t\t}\n"
			 . "\t\t\t\tfunction deletethis_{$ia[0]}(\$text, \$value, \$name, \$thisname)\n"
			 . "\t\t\t\t\t{\n"
			 . "\t\t\t\t\tvar qid='{$ia[0]}';\n"
			 . "\t\t\t\t\tvar lngth=qid.length+4;\n"
			 . "\t\t\t\t\tvar cutindex=\$thisname.substring(lngth, \$thisname.length);\n"
			 . "\t\t\t\t\tcutindex=parseFloat(cutindex);\n"
			 . "\t\t\t\t\tdocument.getElementById(\$name).value='';\n"
			 . "\t\t\t\t\tdocument.getElementById(\$thisname).style.display='none';\n"
			 . "\t\t\t\t\tif (cutindex > 1)\n"
			 . "\t\t\t\t\t\t{\n"
			 . "\t\t\t\t\t\t\$cut1name=\"cut_{$ia[0]}\"+(cutindex-1);\n"
			 . "\t\t\t\t\t\t\$cut2name=\"fvalue_{$ia[0]}\"+(cutindex);\n"
			 . "\t\t\t\t\t\tdocument.getElementById(\$cut1name).style.display='';\n"
			 . "\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n"
			 . "\t\t\t\t\t\t}\n"
			 . "\t\t\t\t\telse\n"
			 . "\t\t\t\t\t\t{\n"
			 . "\t\t\t\t\t\t\$cut2name=\"fvalue_{$ia[0]}\"+(cutindex);\n"
			 . "\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n"
			 . "\t\t\t\t\t\t}\n"
			 . "\t\t\t\t\tvar i=document.getElementById('CHOICES_{$ia[0]}').options.length;\n"
			 . "\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').options[i] = new Option(\$text, \$value);\n"
			 . "\t\t\t\t\tif (document.getElementById('CHOICES_{$ia[0]}').options.length > 0)\n"
			 . "\t\t\t\t\t\t{\n"
			 . "\t\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').disabled=false;\n"
			 . "\t\t\t\t\t\t}\n"
			 . "\t\t\t\t\tcheckconditions('');\n"
			 . "\t\t\t\t\t}\n"
			 . "\t\t\t//-->\n"
			 . "\t\t\t</script>\n";	
	unset($answers);
	//unset($inputnames);
	unset($chosen);
	$ranklist="";
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$answers[] = array($ansrow['code'], $ansrow['answer']);
		}
	$existing=0;
	for ($i=1; $i<=$anscount; $i++)
		{
		$myfname=$ia[1].$i;
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname])
			{
			$existing++;
			}
		}
	for ($i=1; $i<=$anscount; $i++)
		{
		$myfname = $ia[1].$i;
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname])
			{
			foreach ($answers as $ans)
				{
				if ($ans[0] == $_SESSION[$myfname])
					{
					$thiscode=$ans[0];
					$thistext=$ans[1];
					}
				}
			}
		$ranklist .= "\t\t\t\t\t\t&nbsp;<label for='RANK_{$ia[0]}$i'>"
				   ."$i:&nbsp;</label><input class='text' type='text' name='RANK_{$ia[0]}$i' id='RANK_{$ia[0]}$i'";
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname])
			{
			$ranklist .= " value='";
			$ranklist .= $thistext;
			$ranklist .= "'";
			}
		$ranklist .= " onFocus=\"this.blur()\">\n";
		$ranklist .= "\t\t\t\t\t\t<input type='hidden' name='$myfname' id='fvalue_{$ia[0]}$i' value='";

		$chosen[]=""; //create array
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname])
			{
			$ranklist .= $thiscode;
			$chosen[]=array($thiscode, $thistext);
			}
		$ranklist .= "' />\n";
		$ranklist .= "\t\t\t\t\t\t<img src='Cut.gif' alt='"._REMOVEITEM."' title='"._REMOVEITEM."' ";
		if ($i != $existing)
			{
			$ranklist .= "style='display:none'";
			}
		$ranklist .= " id='cut_{$ia[0]}$i' name='cut$i' onClick=\"deletethis_{$ia[0]}(document.phpsurveyor.RANK_{$ia[0]}$i.value, document.phpsurveyor.fvalue_{$ia[0]}$i.value, document.phpsurveyor.RANK_{$ia[0]}$i.name, this.id)\"><br />\n";
		$inputnames[]=$myfname;
		}

	$choicelist = "\t\t\t\t\t\t<select size='$anscount' name='CHOICES_{$ia[0]}' ";
	if (isset($choicewidth)) {$choicelist.=$choicewidth;}
	$choicelist .= " id='CHOICES_{$ia[0]}' onClick=\"rankthis_{$ia[0]}(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" class='select'>\n";
	if (_PHPVERSION <= "4.2.0")
		{
		foreach ($chosen as $chs) {$choose[]=$chs[0];}
		foreach ($answers as $ans)
			{
			if (!in_array($ans[0], $choose))
				{
				$choicelist .= "\t\t\t\t\t\t\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
				if (strlen($ans[1]) > $maxselectlength) {$maxselectlength = strlen($ans[1]);}
				}
			}
		}
	else
		{
		foreach ($answers as $ans)
			{
			if (!in_array($ans, $chosen))
				{
				$choicelist .= "\t\t\t\t\t\t\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
				if (isset($maxselectlength) && strlen($ans[1]) > $maxselectlength) {$maxselectlength = strlen($ans[1]);}
				}
			}
		}
	$choicelist .= "\t\t\t\t\t\t</select>\n";

	$answer .= "\t\t\t<table border='0' cellspacing='5' class='rank'>\n"
			 . "\t\t\t\t<tr>\n"
			 . "\t\t\t\t\t<td colspan='2' class='rank'><font size='1'>\n"
			 . "\t\t\t\t\t\t"._RANK_1."<br />"
			 . "\t\t\t\t\t\t"._RANK_2
			 . "\t\t\t\t\t</font></td>\n"
			 . "\t\t\t\t</tr>\n"
			 . "\t\t\t\t<tr>\n"
			 . "\t\t\t\t\t<td align='left' valign='top' class='rank'>\n"
			 . "\t\t\t\t\t\t<b>&nbsp;&nbsp;<label for='CHOICES_{$ia[0]}'>"._YOURCHOICES.":</label></b><br />\n"
			 . "&nbsp;".$choicelist
			 . "\t\t\t\t\t&nbsp;</td>\n";
	if (isset($maxselectlength) && $maxselectlength > 60) 
		{
		$ranklist = str_replace("<input class='text'", "<input size='60' class='text'", $ranklist);
		$answer .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td align='left' bgcolor='silver' class='rank'>\n"
				 . "\t\t\t\t\t\t<b>&nbsp;&nbsp;"._YOURRANKING.":</b><br />\n";
		}
	else
		{
		$answer .= "\t\t\t\t\t<td align='left' bgcolor='silver' width='200' class='rank'>\n"
				 . "\t\t\t\t\t\t<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"._YOURRANKING.":</b><br />\n";
		}
	$answer .= $ranklist
			 . "\t\t\t\t\t</td>\n"
			 . "\t\t\t\t</tr>\n"
			 . "\t\t\t\t<tr>\n"
			 . "\t\t\t\t\t<td colspan='2' class='rank'><font size='1'>\n"
			 . "\t\t\t\t\t\t"._RANK_3."<br />"
			 . "\t\t\t\t\t\t"._RANK_4.""
			 . "\t\t\t\t\t</font></td>\n"
			 . "\t\t\t\t</tr>\n"
			 . "\t\t\t</table>\n";

	return array($answer, $inputnames);
	}

function do_multiplechoice($ia)
	{
	global $dbprefix;
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($displaycols=arraySearchByKey("display_columns", $qidattributes, "attribute", 1))
		{
	    $dcols=$displaycols['value'];
		}
	else
		{
		$dcols=0;
		}
	$answer  = "\t\t\t<table class='question'>\n"
			 . "\t\t\t\t<tr>\n"
			 . "\t\t\t\t\t<td>&nbsp;</td>\n"
			 . "\t\t\t\t\t<td align='left' class='answertext'>\n";
	$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
	$qresult = mysql_query($qquery);
	while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY RAND()";
	} else {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY sortorder, answer";
	}
	$ansresult = mysql_query($ansquery);
	$anscount = mysql_num_rows($ansresult);
	if ($other == "Y") {$anscount++;} //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!
	$answer .= "\t\t\t\t\t<input type='hidden' name='MULTI$ia[1]' value='$anscount'>\n";
	$divider="";
	$maxrows=0;
	if ($dcols >0 && $anscount > $dcols) //Break into columns
		{
	    $width=sprintf("%0d", 100/$dcols);
		$maxrows=ceil(100*($anscount/$dcols)/100); //Always rounds up to nearest whole number
		$answer .= "<table class='question'><tr>\n <td valign='top' width='$width%' nowrap>";
		$divider=" </td>\n <td valign='top' width='$width%' nowrap>";
		}	
	$fn = 1;
	if (!isset($multifields)) {$multifields="";}
	$rowcounter=0;
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$rowcounter++;
		$myfname = $ia[1].$ansrow['code'];
		$answer .= "\t\t\t\t\t\t<input class='checkbox' type='checkbox' name='$ia[1]{$ansrow['code']}' id='$ia[1]{$ansrow['code']}' value='Y'";
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == "Y") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]{$ansrow['code']}' class='answertext'>{$ansrow['answer']}</label><br />\n";
		$fn++;
		$answer .= "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='";
		if (isset($_SESSION[$myfname])) {$answer .= $_SESSION[$myfname];}
		$answer .= "'>\n";
		$inputnames[]=$myfname;
		if ($rowcounter==$maxrows) {$answer .= $divider; $rowcounter=0;}
		}
	if ($other == "Y")
		{
		$rowcounter++;
		$myfname = $ia[1]."other";
		$answer .= "\t\t\t\t\t\t<label for='$myfname'>"._OTHER.":</label> <input class='text' type='text' name='$myfname' id='$myfname'";
		if (isset($_SESSION[$myfname])) {$answer .= " value='".$_SESSION[$myfname]."'";}
		$answer .= " />\n"
				 . "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='";
		if (isset($_SESSION[$myfname])) {$answer .= $_SESSION[$myfname];}
		$answer .= "'>\n";
		$inputnames[]=$myfname;
		$anscount++;
		if ($rowcounter==$maxrows) {$answer .= $divider; $rowcounter=0;}
		}
	$answer .= "\t\t\t\t\t</td>\n";
	if ($dcols <1) 
		{ //This just makes a single column look a bit nicer
	    $answer .= "\t\t\t\t\t<td>&nbsp;</td>\n";
		}
	$answer .= "\t\t\t\t</tr>\n"
			 . "\t\t\t</table>\n";
	return array($answer, $inputnames);
	}

function do_multiplechoice_withcomments($ia)
	{
	global $dbprefix;
	$qidattributes=getQuestionAttributes($ia[0]);
	$answer  = "\t\t\t<table class='question'>\n"
			 . "\t\t\t\t<tr>\n"
			 . "\t\t\t\t\t<td>&nbsp;</td>\n"
			 . "\t\t\t\t\t<td align='left'>\n";
	$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
	$qresult = mysql_query($qquery);
	while ($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY RAND()";
	} else {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY sortorder, answer";
	}
	$ansresult = mysql_query($ansquery);
	$anscount = mysql_num_rows($ansresult)*2;
	$answer .= "\t\t\t\t\t<input type='hidden' name='MULTI$ia[1]' value='$anscount'>\n"
			 . "\t\t\t\t\t\t<table class='question'>\n";
	$fn = 1;
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$myfname = $ia[1].$ansrow['code'];
		$myfname2 = $myfname."comment";
		$answer .= "\t\t\t\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t\t\t\t<td>\n"
				 . "\t\t\t\t\t\t\t\t\t<input class='checkbox' type='checkbox' name='$myfname' id='$myfname' value='Y'";
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == "Y") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' />"
				 . "<label for='$myfname' class='answertext'>"
				 . $ansrow['answer']."</label>\n"
				 . "\t\t\t\t\t\t\t\t</td>\n"
				 . "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='";
		if (isset($_SESSION[$myfname])) {$answer .= $_SESSION[$myfname];}
		$answer .= "'>\n";
		$fn++;
		$answer .= "\t\t\t\t\t\t\t\t<td>\n"
				 . "\t\t\t\t\t\t\t\t\t<label for='$myfname2'>"
				 ."<input class='text' type='text' size='40' id='$myfname2' name='$myfname2' title='"._PS_COMMENT."' value='";
		if (isset($_SESSION[$myfname2])) {$answer .= $_SESSION[$myfname2];}
		$answer .= "' /></label>\n"
				 . "\t\t\t\t\t\t\t\t</td>\n"
				 . "\t\t\t\t\t\t\t</tr>\n";
		$fn++;
		$inputnames[]=$myfname;
		$inputnames[]=$myfname2;
		}
	if ($other == "Y")
		{
		$myfname = $ia[1]."other";
		$myfname2 = $myfname."comment";
		$anscount = $anscount + 2;
		$answer .= "\t\t\t\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t\t\t\t<td class='answertext'>\n"
				 . "\t\t\t\t\t\t\t\t\t<label for='$myfname'>"._OTHER.":</label><input class='text' type='text' name='$myfname' id='$myfname' title='"._OTHER."' size='10'";
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname]) {$answer .= " value='".$_SESSION[$myfname]."'";}
		$fn++;
		$answer .= " />\n"
				 . "\t\t\t\t\t\t\t\t</td>\n"
				 . "\t\t\t\t\t\t\t\t<td valign='bottom'><label for='$myfname2'>\n"
				 . "\t\t\t\t\t\t\t\t\t<input class='text' type='text' size='40' name='$myfname2' id='$myfname2' title='"._PS_COMMENT."' value='";
		if (isset($_SESSION[$myfname2])) {$answer .= $_SESSION[$myfname2];}
		$answer .= "' />\n"
				 . "\t\t\t\t\t\t\t\t</label></td>\n"
				 . "\t\t\t\t\t\t\t</tr>\n";
		$inputnames[]=$myfname;
		$inputnames[]=$myfname2;
		}
	$answer .= "\t\t\t\t\t\t</table>\n"
			 . "\t\t\t\t\t</td>\n"
			 . "\t\t\t\t\t<td>&nbsp;</td>\n"
			 . "\t\t\t\t</tr>\n"
			 . "\t\t\t</table>\n";
	return array($answer, $inputnames);
	}

function do_multipleshorttext($ia)
	{
	global $dbprefix;
	$qidattributes=getQuestionAttributes($ia[0]);
	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY RAND()";
	} else {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY sortorder, answer";
	}
	$ansresult = mysql_query($ansquery);
	$anscount = mysql_num_rows($ansresult)*2;
	//$answer .= "\t\t\t\t\t<input type='hidden' name='MULTI$ia[1]' value='$anscount'>\n";
	$fn = 1;
	$answer = "\t\t\t\t\t\t<table class='question'>\n";
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$myfname = $ia[1].$ansrow['code'];
		$answer .= "\t\t\t\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t\t\t\t<td align='right' class='answertext'>\n"
				 . "\t\t\t\t\t\t\t\t\t<label for='$myfname'>{$ansrow['answer']}</label>\n"
				 . "\t\t\t\t\t\t\t\t</td>\n"
				 . "\t\t\t\t\t\t\t\t<td>\n"
				 . "\t\t\t\t\t\t\t\t\t<input class='text' type='text' size='40' name='$myfname' id='$myfname' value='";
		if (isset($_SESSION[$myfname])) {$answer .= $_SESSION[$myfname];}
		$answer .= "' />\n"
				 . "\t\t\t\t\t\t\t\t</td>\n"
				 . "\t\t\t\t\t\t\t</tr>\n";
		$fn++;
		$inputnames[]=$myfname;
		}
	$answer .= "\t\t\t\t\t\t</table>\n";
	return array($answer, $inputnames);
	}

function do_numerical($ia)
	{
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($maxchars=arraySearchByKey("maximum_chars", $qidattributes, "attribute", 1))
		{
		$maxsize=$maxchars['value'];
		}
	else
		{
		$maxsize=255;
		}
	if ($maxchars=arraySearchByKey("text_input_width", $qidattributes, "attribute", 1))
		{
		$tiwidth=$maxchars['value'];
		}
	else
		{
		$tiwidth=10;
		}
	$answer = keycontroljs()
			 . "\t\t\t<input class='text' type='text' size='$tiwidth' name='$ia[1]' "
			 . "id='{$ia[1]}' value=\"{$_SESSION[$ia[1]]}\" onKeyPress=\"return goodchars(event,'0123456789.')\" "
			 . "maxlength='$maxsize' /><br />\n"
			 . "\t\t\t<font size='1'><i>"._NUMERICAL_PS."</i></font>\n";
	$inputnames[]=$ia[1];
	$mandatory=null;
	return array($answer, $inputnames, $mandatory);
	}

function do_shortfreetext($ia)
	{
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($maxchars=arraySearchByKey("maximum_chars", $qidattributes, "attribute", 1))
		{
		$maxsize=$maxchars['value'];
		}
	else
		{
		$maxsize=255;
		}
	if ($maxchars=arraySearchByKey("text_input_width", $qidattributes, "attribute", 1))
		{
		$tiwidth=$maxchars['value'];
		}
	else
		{
		$tiwidth=50;
		}
	$answer = "\t\t\t<input class='text' type='text' size='$tiwidth' name='$ia[1]' id='$ia[1]' value=\""
				 .str_replace ("\"", "'", str_replace("\\", "", $_SESSION[$ia[1]]))
				 ."\" maxlength='$maxsize' />\n";
	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
	}

function do_longfreetext($ia)
	{
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($maxchars=arraySearchByKey("maximum_chars", $qidattributes, "attribute", 1))
		{
		$maxsize=$maxchars['value'];
		}
	else
		{
		$maxsize=65525;
		}
	$answer = "<script type='text/javascript'>
			   <!--
			   function textLimit(field, maxlen) {
				if (document.getElementById(field).value.length > maxlen)
				document.getElementById(field).value = document.getElementById(field).value.substring(0, maxlen);
				}
			   //-->
			   </script>\n";
	$answer .= "<textarea class='textarea' name='{$ia[1]}' id='{$ia[1]}' "
			  ."rows='5' cols='40' onkeyup=\"textLimit('".$ia[1]."', $maxsize)\">";
	if ($_SESSION[$ia[1]]) {$answer .= str_replace("\\", "", $_SESSION[$ia[1]]);}	
	$answer .= "</textarea>\n";
	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
	}

function do_hugefreetext($ia)
	{
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($maxchars=arraySearchByKey("maximum_chars", $qidattributes, "attribute", 1))
		{
		$maxsize=$maxchars['value'];
		}
	else
		{
		$maxsize=65525;
		}
	$answer = "<script type='text/javascript'>
			   <!--
			   function textLimit(field, maxlen) {
				if (document.getElementById(field).value.length > maxlen)
				document.getElementById(field).value = document.getElementById(field).value.substring(0, maxlen);
				}
			   //-->
			   </script>\n";
	$answer .= "<textarea class='textarea' name='{$ia[1]}' id='{$ia[1]}' "
			 ."\"\"rows='30' cols='70' onkeyup=\"textLimit('".$ia[1]."', $maxsize)\">";
	if ($_SESSION[$ia[1]]) {$answer .= str_replace("\\", "", $_SESSION[$ia[1]]);}	
	$answer .= "</textarea>\n";
	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
	}

function do_yesno($ia)
	{
	global $shownoanswer;
	$answer = "\t\t\t<table class='question'>\n"
			 . "\t\t\t\t<tr>\n"
			 . "\t\t\t\t\t<td>\n"
			 . "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' id='$ia[1]Y' value='Y'";
	if ($_SESSION[$ia[1]] == "Y") {$answer .= " checked";}
	$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]Y' class='answertext'>"._YES."</label><br />\n"
			 . "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' id='$ia[1]N' value='N'";
	if ($_SESSION[$ia[1]] == "N") {$answer .= " checked";}
	$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]N' class='answertext'>"._NO."</label><br />\n";
	if ($ia[6] != "Y" && $shownoanswer == 1)
		{
		$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' id='$ia[1] ' value=''";
		if ($_SESSION[$ia[1]] == "") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1] ' class='answertext'>"._NOANSWER."</label><br />\n";
		}
	$answer .= "\t\t\t\t\t</td>\n"
			 . "\t\t\t\t</tr>\n"
			 . "\t\t\t\t<input type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n"
			 . "\t\t\t</table>\n";
	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
	}

function do_gender($ia)
	{
	global $shownoanswer;
	$answer = "\t\t\t<table class='question'>\n"
			 . "\t\t\t\t<tr>\n"
			 . "\t\t\t\t\t<td>\n"
			 . "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' id='$ia[1]F' value='F'";
	if ($_SESSION[$ia[1]] == "F") {$answer .= " checked";}
	$answer .= " onClick='checkconditions(this.value, this.name, this.type)' />"
			 . "<label for='$ia[1]F' class='answertext'>"._FEMALE."</label><br />\n"
			 . "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' id='$ia[1]M' value='M'";
	if ($_SESSION[$ia[1]] == "M") {$answer .= " checked";}
	$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]M' class='answertext'>"._MALE."</label><br />\n";
	if ($ia[6] != "Y" && $shownoanswer == 1)
		{
		$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' id='$ia[1] ' value=''";
		if ($_SESSION[$ia[1]] == "") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1] ' class='answertext'>"._NOANSWER."</label>\n";
		}
	$answer .= "\t\t\t\t\t</td>\n"
			 . "\t\t\t\t</tr>\n"
			 . "\t\t\t\t<input type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n"
			 . "\t\t\t</table>\n";
	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
	}

function do_array_5point($ia)
	{
	global $dbprefix, $shownoanswer;
	$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
	$qresult = mysql_query($qquery);
	while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
	$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
	$ansresult = mysql_query($ansquery);
	$anscount = mysql_num_rows($ansresult);
	$fn = 1;
	$answer = "\t\t\t<table class='question'>\n"
			 . "\t\t\t\t<tr>\n"
			 . "\t\t\t\t\t<td></td>\n";
	for ($xc=1; $xc<=5; $xc++)
		{
		$answer .= "\t\t\t\t\t<td align='center' class='array1'>$xc</td>\n";
		}
	if ($ia[6] != "Y" && $shownoanswer == 1) //Question is not mandatory
		{
		$answer .= "\t\t\t\t\t<td align='center' class='array1'>"._NOTAPPLICABLE."</td>\n";
		}
	$answer .= "\t\t\t\t</tr>\n";
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$myfname = $ia[1].$ansrow['code'];
		if (!isset($trbc) || $trbc == "array1" || !$trbc) {$trbc = "array2";} else {$trbc = "array1";}
		$answer .= "\t\t\t\t<tr class='$trbc'>\n"
				 . "\t\t\t\t\t<td align='right'>{$ansrow['answer']}</td>\n";
		for ($i=1; $i<=5; $i++)
			{
			$answer .= "\t\t\t\t\t<td><label for='$myfname-$i'>"
					."<input class='radio' type='radio' name='$myfname' id='$myfname-$i' value='$i' title='$i'";
			if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $i) {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></label></td>\n";
			}
		if ($ia[6] != "Y" && $shownoanswer == 1)
			{
			$answer .= "\t\t\t\t\t<td align='center'><label for='$myfname-'>"
					."<input class='radio' type='radio' name='$myfname' id='$myfname-' value='' title='"._NOTAPPLICABLE."'";
			if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == "") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></label></td>\n";
			}
		$answer .= "\t\t\t\t</tr>\n"
				 . "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='";
		if (isset($_SESSION[$myfname])){$answer .= $_SESSION[$myfname];}
		$answer .= "'>\n";
		$fn++;
		$inputnames[]=$myfname;
		}			
	
	$answer .= "\t\t\t</table>\n";
	return array($answer, $inputnames);
	}

function do_array_10point($ia)
	{
	global $dbprefix, $shownoanswer;
	$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
	$qresult = mysql_query($qquery);
	while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
	$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
	$ansresult = mysql_query($ansquery);
	$anscount = mysql_num_rows($ansresult);
	$fn = 1;
	$answer = "\t\t\t<table class='question'>\n"
			 . "\t\t\t\t<tr>\n"
			 . "\t\t\t\t\t<td></td>\n";
	for ($xc=1; $xc<=10; $xc++)
		{
		$answer .= "\t\t\t\t\t<td align='center' class='array1'>$xc</td>\n";
		}
	if ($ia[6] != "Y" && $shownoanswer == 1) //Question is not mandatory
		{
		$answer .= "\t\t\t\t\t<td align='center' class='array1'>"._NOTAPPLICABLE."</td>\n";
		}
	$answer .= "\t\t\t\t</tr>\n";
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$myfname = $ia[1].$ansrow['code'];
		if (!isset($trbc) || $trbc == "array1" || !$trbc) {$trbc = "array2";} else {$trbc = "array1";}
		$answer .= "\t\t\t\t<tr class='$trbc'>\n";
		$answer .= "\t\t\t\t\t<td align='right'>{$ansrow['answer']}</td>\n";
		for ($i=1; $i<=10; $i++)
			{
			$answer .= "\t\t\t\t\t\t<td><label for='$myfname-$i'>"
					."<input class='radio' type='radio' name='$myfname' id='$myfname-$i' value='$i' title='$i'";
			if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $i) {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></label></td>\n";
			}
		if ($ia[6] != "Y" && $shownoanswer == 1)
			{
			$answer .= "\t\t\t\t\t<td align='center'><label for='$myfname-'>"
					."<input class='radio' type='radio' name='$myfname' id='$myfname-' value='' tite='"._NOTAPPLICABLE."'";
			if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == "") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></label></td>\n";
			}
		$answer .= "\t\t\t\t</tr>\n"
				 . "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='";
		if (isset($_SESSION[$myfname])){$answer .= $_SESSION[$myfname];}
		$answer .= "'>\n";
		$inputnames[]=$myfname;
		$fn++;
		}			
	$answer .= "\t\t\t</table>\n";
	return array($answer, $inputnames);
	}

function do_array_yesnouncertain($ia)
	{
	global $dbprefix, $shownoanswer;
	$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
	$qresult = mysql_query($qquery);
	while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
	$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
	$ansresult = mysql_query($ansquery);
	$anscount = mysql_num_rows($ansresult);
	$fn = 1;
	$answer = "\t\t\t<table class='question'>\n"
			 . "\t\t\t\t<tr>\n"
			 . "\t\t\t\t\t<td></td>\n"
			 . "\t\t\t\t\t<td align='center' class='array1'>"._YES."</td>\n"
			 . "\t\t\t\t\t<td align='center' class='array1'>"._UNCERTAIN."</td>\n"
			 . "\t\t\t\t\t<td align='center' class='array1'>"._NO."</td>\n";
	if ($ia[6] != "Y" && $shownoanswer == 1) //Question is not mandatory
		{
		$answer .= "\t\t\t\t\t<td align='center' class='array1'>"._NOTAPPLICABLE."</td>\n";
		}
	$answer .= "\t\t\t\t</tr>\n";
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$myfname = $ia[1].$ansrow['code'];
		if (!isset($trbc) || $trbc == "array1") {$trbc = "array2";} else {$trbc = "array1";}
		$answer .= "\t\t\t\t<tr class='$trbc'>\n"
				 . "\t\t\t\t\t<td align='right'>{$ansrow['answer']}</td>\n"
				 . "\t\t\t\t\t\t<td align='center'><label for='$myfname-Y'>"
				 ."<input class='radio' type='radio' name='$myfname' id='$myfname-Y' value='Y' title='"._YES."'";
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == "Y") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></label></td>\n"
				 . "\t\t\t\t\t\t<td align='center'><label for='$myfname-U'>"
				 ."<input class='radio' type='radio' name='$myfname' id='$myfname-U' value='U' title='"._UNCERTAIN."'";
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == "U") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></label></td>\n"
				 . "\t\t\t\t\t\t<td align='center'><label for='$myfname-N'>"
				 ."<input class='radio' type='radio' name='$myfname' id='$myfname-N' value='N' title='"._NO."'";
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == "N") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></label></td>\n";
		if ($ia[6] != "Y" && $shownoanswer == 1)
			{
			$answer .= "\t\t\t\t\t<td align='center'><label for='$myfname-'>"
					."<input class='radio' type='radio' name='$myfname' id='$myfname-' value='' title='"._NOTAPPLICABLE."'";
			if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == "") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></label></td>\n";
			}
		$answer .= "\t\t\t\t</tr>\n"
				 . "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='";
		if (isset($_SESSION[$myfname])) {$answer .= $_SESSION[$myfname];}
		$answer .= "'>\n";
		$inputnames[]=$myfname;
		$fn++;
		}			
	$answer .= "\t\t\t</table>\n";
	return array($answer, $inputnames);
	}

function do_array_increasesamedecrease($ia)
	{
	global $dbprefix;
	global $shownoanswer;
	$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
	$qresult = mysql_query($qquery);
	while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
	$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
	$ansresult = mysql_query($ansquery);
	$anscount = mysql_num_rows($ansresult);
	$fn = 1;
	$answer = "\t\t\t<table class='question'>\n"
			 . "\t\t\t\t<tr>\n"
			 . "\t\t\t\t\t<td></td>\n"
			 . "\t\t\t\t\t<td align='center' class='array1'>"._INCREASE."</td>\n"
			 . "\t\t\t\t\t<td align='center' class='array1'>"._SAME."</td>\n"
			 . "\t\t\t\t\t<td align='center' class='array1'>"._DECREASE."</td>\n";
	if ($ia[6] != "Y" && $shownoanswer == 1) //Question is not mandatory
		{
		$answer .= "\t\t\t\t\t<td align='center' class='array1'>"._NOTAPPLICABLE."</td>\n";
		}
	$answer .= "\t\t\t\t</tr>\n";
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$myfname = $ia[1].$ansrow['code'];
		if (!isset($trbc) || $trbc == "array1") {$trbc = "array2";} else {$trbc = "array1";}
		$answer .= "\t\t\t\t<tr class='$trbc'>\n"
				 . "\t\t\t\t\t<td align='right'>{$ansrow['answer']}</td>\n"
				 . "\t\t\t\t\t\t<td align='center'><label for='$myfname-I'>"
				 ."<input class='radio' type='radio' name='$myfname' id='$myfname-I' value='I' title='"._INCREASE."'";
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == "I") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></label></td>\n"
				 . "\t\t\t\t\t\t<td align='center'><label for='$myfname-S'>"
				 ."<input class='radio' type='radio' name='$myfname' id='$myfname-S' value='S' title='"._SAME."'";
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == "S") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></label></td>\n"
				 . "\t\t\t\t\t\t<td align='center'><label for='$myfname-D'>"
				 ."<input class='radio' type='radio' name='$myfname' id='$myfname-D' value='D' title='"._DECREASE."'";
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == "D") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></label></td>\n";
		if ($ia[6] != "Y" && $shownoanswer == 1)
			{
			$answer .= "\t\t\t\t\t<td align='center'><label for='$myfname-'>"
					."<input class='radio' type='radio' name='$myfname' id='$myfname-' value='' title='"._NOTAPPLICABLE."'";
			if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == "") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
			}
		$answer .= "\t\t\t\t</tr>\n"
				 . "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='";
		if (isset($_SESSION[$myfname])) {$answer .= $_SESSION[$myfname];}
		$answer .= "'>\n";
		$inputnames[]=$myfname;
		$fn++;
		}			
	$answer .= "\t\t\t</table>\n";
	return array($answer, $inputnames);
	}

function do_array_flexible($ia)
	{
	global $dbprefix;
	global $shownoanswer;
	global $repeatheadings;
	$qquery = "SELECT other, lid FROM {$dbprefix}questions WHERE qid=".$ia[0];
	$qresult = mysql_query($qquery);
	while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other']; $lid = $qrow['lid'];}
	$lquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid ORDER BY sortorder, code";
	$lresult = mysql_query($lquery);
	if (mysql_num_rows($lresult) > 0)
		{
		while ($lrow=mysql_fetch_array($lresult))
			{
			$labelans[]=$lrow['title'];
			$labelcode[]=$lrow['code'];
			}
		$cellwidth=60/count($labelans);
		if ($ia[6] != "Y" && $shownoanswer == 1) {$cellwidth++;}
		//$cellwidth=sprintf("%02d", 60/$cellwidth);
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		$fn=1;
		$answer = "\t\t\t<table class='question'>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td></td>\n";
		foreach ($labelans as $ld)
			{
			$answer .= "\t\t\t\t\t<th align='center' class='array1'><font size='1'>".$ld."</font></th>\n";
			}
		if ($ia[6] != "Y" && $shownoanswer == 1) //Question is not mandatory and we can show "no answer"
			{
			$answer .= "\t\t\t\t\t<th align='center' class='array1'><font size='1'>"._NOTAPPLICABLE."</font></th>\n";
			}
		$answer .= "\t\t\t\t</tr>\n";
		$ansrowcount=0;
		$ansrowtotallength=0;
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			$ansrowcount++;
			$ansrowtotallength=$ansrowtotallength+strlen($ansrow['answer']);
			}
		$ansrowavg=(($ansrowtotallength/$ansrowcount)/2);
		if ($ansrowavg > 54) {$percwidth=60;}
		elseif ($ansrowavg < 5) {$percwidth=5;}
		else {$percwidth=$ansrowavg*1.2;}
		$percwidth=sprintf("%0d", $percwidth);
		$lblcount=0;
		$lblcount=count($labelans);
		if($ia[6]=="Y") {$lblcount++;}
		$otherwidth=sprintf("%0d",(100-$percwidth)/$cellwidth);
		$ansresult = mysql_query($ansquery);
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0) 
				{
				$answer .= "\t\t\t\t<tr>\n"
				 		 . "\t\t\t\t\t<td></td>\n";
				foreach ($labelans as $ld)
					{
					$answer .= "\t\t\t\t\t<td align='center' class='array1'><font size='1'>".$ld."</font></td>\n";
					}
				if ($ia[6] != "Y" && $shownoanswer == 1) //Question is not mandatory and we can show "no answer"
					{
					$answer .= "\t\t\t\t\t<td align='center' class='array1'><font size='1'>"._NOTAPPLICABLE."</font></td>\n";
					}
				$answer .= "\t\t\t\t</tr>\n";
				}
			$myfname = $ia[1].$ansrow['code'];
			if (!isset($trbc) || $trbc == "array1") {$trbc = "array2";} else {$trbc = "array1";}
			$answer .= "\t\t\t\t<tr class='$trbc'>\n"
					 . "\t\t\t\t\t<td align='right' class='answertext' width='$percwidth%'>{$ansrow['answer']}</td>\n";
			$thiskey=0;
			foreach ($labelcode as $ld)
				{
				$answer .= "\t\t\t\t\t<td align='center' width='$otherwidth%'><label for='$myfname-$ld'>";
				$answer .= "<input class='radio' type='radio' name='$myfname' value='$ld' id='$myfname-$ld' title='"
						 . $labelans[$thiskey]."'";
				if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $ld) {$answer .= " checked";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></label></td>\n";
				$thiskey++;
				}
			if ($ia[6] != "Y" && $shownoanswer == 1)
				{
				$answer .= "\t\t\t\t\t<td align='center' width='$otherwidth%'><label for='$myfname-'>"
						."<input class='radio' type='radio' name='$myfname' value='' id='$myfname-' title='"._NOTAPPLICABLE."'";
				if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == "") {$answer .= " checked";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></label></td>\n";
				}
			$answer .= "\t\t\t\t</tr>\n"
					 . "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='";
			if (isset($_SESSION[$myfname])) {$answer .= $_SESSION[$myfname];}
			$answer .= "'>\n";
			$inputnames[]=$myfname;
			//IF a MULTIPLE of flexi-redisplay figure, repeat the headings
			$fn++;
			}
		$answer .= "\t\t\t</table>\n";
		}
	else
		{
		$answer = "<font color=red>"._ERROR_PS.": Flexible Label Not Found.</font>";
		$inputnames="";
		}
	return array($answer, $inputnames);
	}

function do_array_flexiblecolumns($ia)
	{
	global $dbprefix;
	global $shownoanswer;
	$qquery = "SELECT other, lid FROM {$dbprefix}questions WHERE qid=".$ia[0];
	$qresult = mysql_query($qquery);
	while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other']; $lid = $qrow['lid'];}
	$lquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid ORDER BY sortorder, code";
	$lresult = mysql_query($lquery);
	while ($lrow=mysql_fetch_array($lresult))
		{
		$labelans[]=$lrow['title'];
		$labelcode[]=$lrow['code'];
		$labels[]=array("answer"=>$lrow['title'], "code"=>$lrow['code']);
		}
	if ($ia[6] != "Y" && $shownoanswer == 1) {
	    $labelcode[]="";
		$labelans[]=_NOTAPPLICABLE;
		$labels[]=array("answer"=>_NOTAPPLICABLE, "code"=>"");
	}
	$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
	$ansresult = mysql_query($ansquery);
	$anscount = mysql_num_rows($ansresult);
	$fn=1;
	$answer = "\t\t\t<table class='question' align='center'>\n"
			 . "\t\t\t\t<tr>\n"
			 . "\t\t\t\t\t<td></td>\n";
	$cellwidth=$anscount;
	
	$cellwidth=50/$cellwidth;
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$anscode[]=$ansrow['code'];
		$answers[]=$ansrow['answer'];
		}
	foreach ($answers as $ld)
		{
		if (!isset($trbc) || $trbc == "array1") {$trbc = "array2";} else {$trbc = "array1";}
		$answer .= "\t\t\t\t\t<td align='center' class='$trbc'><span class='answertext'>"
				. $ld."</span></td>\n";
		}
	unset($trbc);
	$answer .= "\t\t\t\t</tr>\n";
	$ansrowcount=0;
	$ansrowtotallength=0;
	while ($ansrow = mysql_fetch_array($ansresult))
		{
		$ansrowcount++;
		$ansrowtotallength=$ansrowtotallength+strlen($ansrow['answer']);
		}
	$percwidth=100 - ($cellwidth*$anscount);
	foreach($labels as $ansrow)
		{
		$answer .= "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td align='right' class='array1' width='$percwidth%'>{$ansrow['answer']}</td>\n";
		foreach ($anscode as $ld)
			{
			if (!isset($trbc) || $trbc == "array1") {$trbc = "array2";} else {$trbc = "array1";}
			$myfname=$ia[1].$ld;
			$answer .= "\t\t\t\t\t<td align='center' class='$trbc' width='$cellwidth%'>"
					 . "<label for='$myfname-".$ansrow['code']."'>";
			$answer .= "<input class='radio' type='radio' name='$myfname' value='".$ansrow['code']."' id='$myfname-".$ansrow['code']."'"
					 . " title='".$ansrow['answer']."'";
			if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $ansrow['code']) {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></label></td>\n";
			}
		$answer .= "\t\t\t\t</tr>\n";
		$fn++;
		}
	
	foreach($anscode as $ld) 
		{
		$myfname=$ia[1].$ld;
		$answer .= "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='";
		if (isset($_SESSION[$myfname])) {$answer .= $_SESSION[$myfname];}
		$answer .= "'>\n";
		$inputnames[]=$myfname;
		}			
	$answer .= "\t\t\t</table>\n";
	return array($answer, $inputnames);
	}

function retrieve_Answer($code)
	{
	//This function checks to see if there is an answer saved in the survey session
	//data that matches the $code. If it does, it returns that data.
	//It is used when building a questions text to allow incorporating the answer
	//to an earlier question into the text of a later question.
	//IE: Q1: What is your name? [Jason]
	//    Q2: Hi [Jason] how are you ?
	//This function is called from the retriveAnswers function.
	global $dbprefix;
	//Find question details
	if (isset($_SESSION[$code]))
		{
		$questiondetails=getsidgidqid($code); 
		//the getsidgidqid function is in common.php and returns
		//a SurveyID, GroupID, QuestionID and an Answer code
		//extracted from a "fieldname" - ie: 1X2X3a
		$query="SELECT type FROM {$dbprefix}questions WHERE qid=".$questiondetails['qid'];
		$result=mysql_query($query) or die("Error getting reference question type<br />$query<br />".mysql_error());
		while($row=mysql_fetch_array($result))
			{
			$type=$row['type'];
			} // while
		if ($_SESSION[$code] || $type == "M")
			{
			switch($type)
				{
				case "L":
				case "P":
					if ($_SESSION[$code]== "-oth-")
						{
					    $newcode=$code."other";
						if($_SESSION[$newcode])
							{
							$return=$_SESSION[$newcode];
							}
						else
							{
							$return=_OTHER;
							}
						}
					else
						{
						$query="SELECT * FROM {$dbprefix}answers WHERE qid=".$questiondetails['qid']." AND code='".$_SESSION[$code]."'";
						$result=mysql_query($query) or die("Error getting answer<br />$query<br />".mysql_error());
						while($row=mysql_fetch_array($result))
							{
							$return=$row['answer'];
							} // while
						}
					break;
				case "M":
				case "P":
					$query="SELECT * FROM {$dbprefix}answers WHERE qid=".$questiondetails['qid'];
					$result=mysql_query($query) or die("Error getting answer<br />$query<br />".mysql_error());
					while($row=mysql_fetch_array($result))
						{
						if (isset($_SESSION[$code.$row['code']]) && $_SESSION[$code.$row['code']] == "Y")
							{
						    $returns[] = $row['answer'];
							}
						}
					if (isset($_SESSION[$code."other"]) && $_SESSION[$code."other"])
						{
						$returns[]=$_SESSION[$code."other"];
						}
					if (isset($returns))
						{
					    $return=implode(", ", $returns);
						if (strpos($return, ","))
							{
						    $return=substr_replace($return, " &", strrpos($return, ","), 1);
							}
						}
					else
						{
						$return=_NOANSWER;
						}
					break;
				default:
				$return=$_SESSION[$code];
				} // switch
			}
		else
			{
			$return=_NOTAPPLICABLE;
			}
		}
	else
		{
		$return=_ERROR_PS . "($code)";
		}
	return $return;
	}
?>