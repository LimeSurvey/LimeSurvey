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
//MANDATORY (for single answer questions) (multi answer questions in select)
if (!$dropdownthreshold) {$dropdownthreshold=25;}

if ($ia[4] == "5" || $ia[4] == "D" || $ia[4] == "G" || $ia[4] == "L" || $ia[4] == "O" || $ia[4] == "N" || $ia[4] == "Y" || $ia[4] == "T" || $ia[4] == "S")
	{
	if ($ia[6] == "Y" && $ia[7] != "Y") //Question is mandatory. Add to mandatory array
		{
		$mandatorys[]=$ia[1];
		$mandatoryfns[]=$ia[1];
		}
	if ($ia[6] == "Y" && $ia[7] == "Y")
		{
		$conmandatorys[]=$ia[1];
		$conmandatoryfns[]=$ia[1];
		}
	}
	
//DISPLAY
$display = $ia[7];
if ($ia[7] == "Y")
	{ //DEVELOP CONDITIONS ARRAY FOR THIS QUESTION
	$cquery = "SELECT {$dbprefix}conditions.qid, {$dbprefix}conditions.cqid, {$dbprefix}conditions.cfieldname, {$dbprefix}conditions.value, {$dbprefix}questions.type, {$dbprefix}questions.sid, {$dbprefix}questions.gid FROM {$dbprefix}conditions, {$dbprefix}questions WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid AND {$dbprefix}conditions.qid=$ia[0]";
	$cresult = mysql_query($cquery) or die ("OOPS<BR />$cquery<br />".mysql_error());
	while ($crow = mysql_fetch_array($cresult))
		{
		$conditions[] = array ($crow['qid'], $crow['cqid'], $crow['cfieldname'], $crow['value'], $crow['type'], $crow['sid']."X".$crow['gid']."X".$crow['cqid']);
		}
	}
//QUESTION NAME
$name = $ia[0];

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
//BUILD FIELDLIST
//BUILD ANSWERS
$answer = "";
if (!isset($_SESSION[$ia[1]])) {$_SESSION[$ia[1]] = "";}
switch ($ia[4])
	{
	case "5": //5 POINT CHOICE radio-buttons
		for ($fp=1; $fp<=5; $fp++)
			{
			$answer .= "\t\t\t<input class='radio' type='radio' name='$ia[1]' <id='$ia[1]$fp' value='$fp'";
			if ($_SESSION[$ia[1]] == $fp) {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]$fp' class='answertext'>$fp</label>\n";
			}
		if ($ia[6] != "Y") // Add "No Answer" option if question is not mandatory
			{
			$answer .= "\t\t\t<input class='radio' type='radio' name='$ia[1]' <id='NoAnswer' value=''";
			if (!$_SESSION[$ia[1]]) {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='NoAnswer'>"._NOTAPPLICABLE."</label>\n";
			}
		$answer .= "\t\t\t<input type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n";
		$inputnames[]=$ia[1];
		break;
	case "D": //DATE
		$answer .= "\t\t\t<input class='text' type='text' size=10 name='$ia[1]' value=\"".$_SESSION[$ia[1]]."\" />\n"
				 . "\t\t\t<table class='question'>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td>\n"
				 . "\t\t\t\t\t\t<font size='1'>"._DATEFORMAT."<br />\n"
				 . "\t\t\t\t\t\t"._DATEFORMATEG."\n"
				 . "\t\t\t\t\t</td>\n"
				 . "\t\t\t\t</tr>\n"
				 . "\t\t\t</table>\n";
		$inputnames[]=$ia[1];
		break;
	case "L": //LIST drop-down/radio-button list
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] ORDER BY sortorder, answer";
		$ansresult = mysql_query($ansquery) or die("Couldn't get answers<br />$ansquery<br />".mysql_error());
		$anscount = mysql_num_rows($ansresult);
		if ($dropdowns == "L" || !$dropdowns || $anscount > $dropdownthreshold)
			{
			$answer .= "\n\t\t\t\t\t<select name='$ia[1]' onChange='checkconditions(this.value, this.name, this.type)'>\n";
			while ($ansrow = mysql_fetch_array($ansresult))
				{
				$answer .= "\t\t\t\t\t\t<option value='{$ansrow['code']}'";
				if ($_SESSION[$ia[1]] == $ansrow['code'])
					{
					$answer .= " selected"; 
					}
				elseif ($ansrow['default'] == "Y") {$answer .= " selected"; $defexists = "Y";}
				$answer .= ">{$ansrow['answer']}</option>\n";
				}
			if (!$_SESSION[$ia[1]] && !$defexists) {$answer .= "\t\t\t\t\t\t<option value='' selected>"._PLEASECHOOSE."..</option>\n";}
			if ($_SESSION[$ia[1]] && !$defexists && $ia[6] != "Y") {$answer .= "\t\t\t\t\t\t<option value=' '>"._NOANSWER."</option>\n";}
			$answer .= "\t\t\t\t\t</select>\n";
			}
		elseif ($dropdowns == "R")
			{
			$answer .= "\n\t\t\t\t\t<table class='question'>\n"
					 . "\t\t\t\t\t\t<tr>\n"
					 . "\t\t\t\t\t\t\t<td>\n";
			while ($ansrow = mysql_fetch_array($ansresult))
				{
				$answer .= "\t\t\t\t\t\t\t\t  <input class='radio' type='radio' value='{$ansrow['code']}' name='$ia[1]' id='$ia[1]{$ansrow['code']}'";
				if ($_SESSION[$ia[1]] == $ansrow['code'])
					{
					$answer .= " checked";
					}
				elseif ($ansrow['default'] == "Y") {$answer .= " checked"; $defexists = "Y";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]{$ansrow['code']}' class='answertext'>{$ansrow['answer']}</label><br />\n";
				}
			if (((!$_SESSION[$ia[1]] && !$defexists) || ($_SESSION[$ia[1]] == ' ' && !$defexists)) && $ia[6] != "Y") 
				{
				$answer .= "\t\t\t\t\t\t  <input class='radio' type='radio' name='$ia[1]' id='$ia[1] ' value=' ' checked onClick='checkconditions(this.value, this.name, this.type)' />"
						 . "<label for='$ia[1] ' class='answertext'>"._NOANSWER."</label>\n";
				}
			elseif ($_SESSION[$ia[1]] && !$defexists && $ia[6] != "Y") 
				{
				$answer .= "\t\t\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' value=' ' onClick='checkconditions(this.value, this.name, this.type)' />"
						 . _NOANSWER."\n";
				}
			$answer .= "\t\t\t\t\t\t\t</td>\n"
					 . "\t\t\t\t\t\t</tr>\n"
					 . "\t\t\t\t\t\t<input type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n"
					 . "\t\t\t\t\t</table>\n";
			}
		$inputnames[]=$ia[1];
		break;
	case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		if ($lwcdropdowns == "R" && $anscount <= $dropdownthreshold)
			{
			$answer .= "\t\t\t<table class='question'>\n"
					 . "\t\t\t\t<tr>\n"
					 . "\t\t\t\t\t<td><u>"._CHOOSEONE.":</u></td>\n"
					 . "\t\t\t\t\t<td><u>"._ENTERCOMMENT.":</td>\n"
					 . "\t\t\t\t</tr>\n"
					 . "\t\t\t\t<tr>\n"
					 . "\t\t\t\t\t<td valign='top'>\n";
			
			while ($ansrow=mysql_fetch_array($ansresult))
				{
				$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' value='{$ansrow['code']}' name='$ia[1]' id='$ia[1]{$ansrow['code']}'";
				if ($_SESSION[$ia[1]] == $ansrow['code'])
					{$answer .= " checked";}
				elseif ($ansrow['default'] == "Y") {$answer .= " checked"; $defexists = "Y";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]{$ansrow['code']}'>{$ansrow['answer']}</label><br />\n";
				}
			if ($ia[6] != "Y")
				{
				$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' id='$ia[1] ' value=' ' onClick='checkconditions(this.value, this.name, this.type)' ";
				if ((!$_SESSION[$ia[1]] && !$defexists) ||($_SESSION[$ia[1]] == ' ' && !$defexists)) 
					{
					$answer .= "checked />";
					}
				elseif ($_SESSION[$ia[1]] && !$defexists) 
					{
					$answer .= " />";
					}
				$answer .= "<label for='$ia[1] ' class='answertext'>"._NOANSWER."</label>\n";
				}
			$answer .= "\t\t\t\t\t</td>\n";
			$fname2 = $ia[1]."comment";
			if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
			$answer .= "\t\t\t\t\t<td valign='top'>\n"
					 . "\t\t\t\t\t\t<textarea class='textarea' name='$ia[1]comment' rows='$tarows' cols='30'>";
			if ($_SESSION[$fname2]) 
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
					 . "\t\t\t\t\t<select class='select' name='$ia[1]' onClick='checkconditions(this.value, this.name, this.type)'>\n";
			while ($ansrow=mysql_fetch_array($ansresult))
				{
				$answer .= "\t\t\t\t\t\t<option value='{$ansrow['code']}'";
				if ($_SESSION[$ia[1]] == $ansrow['code'])
					{$answer .= " selected";}
				elseif ($ansrow['default'] == "Y") {$answer .= " selected"; $defexists = "Y";}
				$answer .= ">{$ansrow['answer']}</option>\n";
				if (strlen($ansrow['answer']) > $maxoptionsize) 
					{
					$maxoptionsize = strlen($ansrow['answer']);
					}
				}
			if ($ia[6] != "Y")
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
			$answer .= "\t\t\t\t\t\t<textarea class='textarea' name='$ia[1]comment' rows='$tarows' cols='$maxoptionsize'>";
			if ($_SESSION[$fname2]) 
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
		break;
	case "R": //RANKING STYLE
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
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
			if ($_SESSION[$myfname])
				{
				$existing++;
				}
			}
		for ($i=1; $i<=$anscount; $i++)
			{
			$myfname = $ia[1].$i;
			if ($_SESSION[$myfname])
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
			$ranklist .= "\t\t\t\t\t\t&nbsp;$i:&nbsp;<input class='text' type='text' name='RANK_{$ia[0]}$i' id='RANK_{$ia[0]}$i'";
			if ($_SESSION[$myfname])
				{
				$ranklist .= " value='";
				$ranklist .= $thistext;
				$ranklist .= "'";
				}
			$ranklist .= " onFocus=\"this.blur()\">\n";
			$ranklist .= "\t\t\t\t\t\t<input type='hidden' name='$myfname' id='fvalue_{$ia[0]}$i' value='";
			if ($ia[6] == "Y" && $ia[7] != "Y") //Question is mandatory. Add to mandatory array
				{
				$mandatorys[]=$myfname;
				$mandatoryfns[]=$ia[1];
				}
			if ($ia[6] == "Y" && $ia[7] == "Y")
				{
				$conmandatorys[]=$myfname;
				$conmandatoryfns[]=$ia[1];
				}

			$chosen[]=""; //create array
			if ($_SESSION[$myfname])
				{
				$ranklist .= $thiscode;
				$chosen[]=array($thiscode, $thistext);
				}
			$ranklist .= "' />\n";
			$ranklist .= "\t\t\t\t\t\t<img src='Cut.gif' title='"._REMOVEITEM."' ";
			if ($i != $existing)
				{
				$ranklist .= "style='display:none'";
				}
			$ranklist .= " id='cut_{$ia[0]}$i' name='cut$i' onClick=\"deletethis_{$ia[0]}(document.phpsurveyor.RANK_{$ia[0]}$i.value, document.phpsurveyor.fvalue_{$ia[0]}$i.value, document.phpsurveyor.RANK_{$ia[0]}$i.name, this.id)\"><br />\n";
			$inputnames[]=$myfname;
			}

		$choicelist = "\t\t\t\t\t\t<select size='$anscount' name='CHOICES_{$ia[0]}' $choicewidth id='CHOICES_{$ia[0]}' onClick=\"rankthis_{$ia[0]}(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" class='select'>\n";
		if ($parser_version <= "4.2.0")
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
					if (strlen($ans[1]) > $maxselectlength) {$maxselectlength = strlen($ans[1]);}
					}
				}
			}
		$choicelist .= "\t\t\t\t\t\t</select>\n";

		$answer .= "\t\t\t<table border='0' cellspacing='5' class='rank'>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td colspan='2' class='rank'><font size='1'>\n"
				 . "\t\t\t\t\t\t"._RANK_1."<br />"
				 . "\t\t\t\t\t\t"._RANK_2
				 . "\t\t\t\t\t</td>\n"
				 . "\t\t\t\t</tr>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td align='left' valign='top' class='rank'>\n"
				 . "\t\t\t\t\t\t<b>&nbsp;&nbsp;"._YOURCHOICES.":</b><br />\n"
				 . "&nbsp;".$choicelist
				 . "\t\t\t\t\t&nbsp;</td>\n";
		if ($maxselectlength > 60) 
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
				 . "\t\t\t\t\t</td>\n"
				 . "\t\t\t\t</tr>\n"
				 . "\t\t\t</table>\n";
		break;
	case "M": //MULTIPLE OPTIONS checkbox
		$answer .= "\t\t\t<table class='question'>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td>&nbsp;</td>\n"
				 . "\t\t\t\t\t<td align='left'>\n";
		$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
		$qresult = mysql_query($qquery);
		while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		$answer .= "\t\t\t\t\t<input type='hidden' name='MULTI$ia[1]' value='$anscount'>\n";
		$fn = 1;
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			$myfname = $ia[1].$ansrow['code'];
			$multifields .= "$fname{$ansrow['code']}|";
			$answer .= "\t\t\t\t\t\t<input class='checkbox' type='checkbox' name='$ia[1]{$ansrow['code']}' id='$ia[1]{$ansrow['code']}' value='Y'";
			if ($_SESSION[$myfname] == "Y") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]{$ansrow['code']}' class='answertext'>{$ansrow['answer']}</label><br />\n";
			$fn++;
			$answer .= "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='{$_SESSION[$myfname]}'>\n";
			$inputnames[]=$myfname;
			if ($ia[6] == "Y" && $ia[7] != "Y") //Question is mandatory. Add to mandatory array
				{
				$mandatorys[]=$myfname;
				$mandatoryfns[]=$ia[1];
				}
			if ($ia[6] == "Y" && $ia[7] == "Y")
				{
				$conmandatorys[]=$myfname;
				$conmandatoryfns[]=$ia[1];
				}
			}
		if ($other == "Y")
			{
			$myfname = $ia[1]."other";
			$answer .= "\t\t\t\t\t\t"._OTHER.": <input class='text' type='text' name='$myfname'";
			if ($_SESSION[$myfname]) {$answer .= " value='".$_SESSION[$myfname]."'";}
			$answer .= " />\n"
					 . "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='{$_SESSION[$myfname]}'>\n";
			$inputnames[]=$myfname;
			$anscount++;
			if ($ia[6] == "Y" && $ia[7] != "Y") //Question is mandatory. Add to mandatory array
				{
				$mandatorys[]=$myfname;
				$mandatoryfns[]=$ia[1];
				}
			if ($ia[6] == "Y" && $ia[7] == "Y")
				{
				$conmandatorys[]=$myfname;
				$conmandatoryfns[]=$ia[1];
				}
			}
		$answer .= "\t\t\t\t\t</td>\n"
				 . "\t\t\t\t\t<td>&nbsp;</td>\n"
				 . "\t\t\t\t</tr>\n"
				 . "\t\t\t</table>\n";
		break;
	case "P": //MULTIPLE OPTIONS WITH COMMENTS checkbox + text
		$answer .= "\t\t\t<table class='question'>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td>&nbsp;</td>\n"
				 . "\t\t\t\t\t<td align='left'>\n";
		$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
		$qresult = mysql_query($qquery);
		while ($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
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
			if ($_SESSION[$myfname] == "Y") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$myfname' class='answertext'>{$ansrow['answer']}</label>\n"
					 . "\t\t\t\t\t\t\t\t</td>\n"
					 . "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='{$_SESSION[$myfname]}'>\n";
			$fn++;
			$answer .= "\t\t\t\t\t\t\t\t<td>\n"
					 . "\t\t\t\t\t\t\t\t\t<input class='text' type='text' type='text' size='40' name='$myfname2' value='".$_SESSION[$myfname2]."' />\n"
					 . "\t\t\t\t\t\t\t\t</td>\n"
					 . "\t\t\t\t\t\t\t</tr>\n";
			$fn++;
			$inputnames[]=$myfname;
			$inputnames[]=$myfname2;
			if ($ia[6] == "Y" && $ia[7] != "Y") //Question is mandatory. Add to mandatory array
				{
				$mandatorys[]=$myfname;
				$mandatoryfns[]=$ia[1];
				}
			if ($ia[6] == "Y" && $ia[7] == "Y")
				{
				$conmandatorys[]=$myfname;
				$conmandatoryfns[]=$ia[1];
				}
			}
		if ($other == "Y")
			{
			$myfname = $ia[1]."other";
			$myfname2 = $myfname."comment";
			$anscount = $anscount + 2;
			$answer .= "\t\t\t\t\t\t\t<tr>\n"
					 . "\t\t\t\t\t\t\t\t<td>\n"
					 . "\t\t\t\t\t\t\t\t\t"._OTHER.":<input class='text' type='text' name='$myfname' size='10'";
			if ($_SESSION[$myfname]) {$answer .= " value='".$_SESSION[$myfname]."'";}
			$fn++;
			$answer .= " />\n"
					 . "\t\t\t\t\t\t\t\t</td>\n"
					 . "\t\t\t\t\t\t\t\t<td valign='bottom'>\n"
					 . "\t\t\t\t\t\t\t\t\t<input class='text' type='text' size='40' name='$myfname2' value='".$_SESSION[$myfname2]."' />\n"
					 . "\t\t\t\t\t\t\t\t</td>\n"
					 . "\t\t\t\t\t\t\t</tr>\n";
			$inputnames[]=$myfname;
			$inputnames[]=$myfname2;
			if ($ia[6] == "Y" && $ia[7] != "Y") //Question is mandatory. Add to mandatory array
				{
				$mandatorys[]=$myfname;
				$mandatoryfns[]=$ia[1];
				}
			if ($ia[6] == "Y" && $ia[7] == "Y")
				{
				$conmandatorys[]=$myfname;
				$conmandatoryfns[]=$ia[1];
				}
			}
		$answer .= "\t\t\t\t\t\t</table>\n"
				 . "\t\t\t\t\t</td>\n"
				 . "\t\t\t\t\t<td>&nbsp;</td>\n"
				 . "\t\t\t\t</tr>\n"
				 . "\t\t\t</table>\n";
		break;
	case "Q": //MULTIPLE SHORT TEXT
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult)*2;
		//$answer .= "\t\t\t\t\t<input type='hidden' name='MULTI$ia[1]' value='$anscount'>\n";
		$fn = 1;
		$answer .= "\t\t\t\t\t\t<table class='question'>\n";
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			$myfname = $ia[1].$ansrow['code'];
			$answer .= "\t\t\t\t\t\t\t<tr>\n"
					 . "\t\t\t\t\t\t\t\t<td align='right'>\n"
					 . "\t\t\t\t\t\t\t\t\t{$ansrow['answer']}\n"
					 . "\t\t\t\t\t\t\t\t</td>\n"
					 . "\t\t\t\t\t\t\t\t<td>\n"
					 . "\t\t\t\t\t\t\t\t\t<input class='text' type='text' type='text' size='40' name='$myfname' value='".$_SESSION[$myfname]."' />\n"
					 . "\t\t\t\t\t\t\t\t</td>\n"
					 . "\t\t\t\t\t\t\t</tr>\n";
			$fn++;
			$inputnames[]=$myfname;
			if ($ia[6] == "Y" && $ia[7] != "Y") //Question is mandatory. Add to mandatory array
				{
				$mandatorys[]=$myfname;
				$mandatoryfns[]=$ia[1];
				}
			if ($ia[6] == "Y" && $ia[7] == "Y")
				{
				$conmandatorys[]=$myfname;
				$conmandatoryfns[]=$ia[1];
				}
			}
		$answer .= "\t\t\t\t\t\t</table>\n";
		break;
	case "N": //NUMERICAL QUESTION TYPE
		$answer .= keycontroljs()
				 . "\t\t\t<input class='text' type='text' size='10' name='$ia[1]' value=\"{$_SESSION[$ia[1]]}\" onKeyPress=\"return goodchars(event,'0123456789.')\"/><br />\n"
				 . "\t\t\t<font size='1'><i>"._NUMERICAL."</i></font>\n";
		$inputnames[]=$ia[1];
		break;
	case "S": //SHORT FREE TEXT
		$answer .= "\t\t\t<input class='text' type='text' size='50' name='$ia[1]' value=\"".str_replace ("\"", "'", str_replace("\\", "", $_SESSION[$ia[1]]))."\" />\n";
		$inputnames[]=$ia[1];
		break;
	case "T": //LONG FREE TEXT
		$answer .= "<textarea class='textarea' name='$ia[1]' rows='5' cols='40'>";
		if ($_SESSION[$ia[1]]) {$answer .= str_replace("\\", "", $_SESSION[$ia[1]]);}	
		$answer .= "</textarea>\n";
		$inputnames[]=$ia[1];
		break;
	case "Y": //YES/NO radio-buttons
		$answer .= "\t\t\t<table class='question'>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td>\n"
				 . "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' id='$ia[1]Y' value='Y'";
		if ($_SESSION[$ia[1]] == "Y") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]Y' class='answertext'>"._YES."</label><br />\n"
				 . "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' id='$ia[1]N' value='N'";
		if ($_SESSION[$ia[1]] == "N") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]N' class='answertext'>"._NO."</label><br />\n";
		if ($ia[6] != "Y")
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
		break;
	case "G": //GENDER drop-down list
		$answer .= "\t\t\t<table class='question'>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td>\n"
				 . "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' id='$ia[1]F' value='F'";
		if ($_SESSION[$ia[1]] == "F") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' />"
				 . "<label for='$ia[1]F' class='answertext'>"._FEMALE."</label><br />\n"
				 . "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' id='$ia[1]M' value='M'";
		if ($_SESSION[$ia[1]] == "M") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /><label for='$ia[1]M' class='answertext'>"._MALE."</label><br />\n";
		if ($ia[6] != "Y")
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
		break;
	case "A": //ARRAY (5 POINT CHOICE) radio-buttons
		$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
		$qresult = mysql_query($qquery);
		while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		$fn = 1;
		$answer .= "\t\t\t<table class='question'>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td></td>\n";
		for ($xc=1; $xc<=5; $xc++)
			{
			$answer .= "\t\t\t\t\t<td align='center' class='array1'>$xc</td>\n";
			}
		if ($ia[6] != "Y") //Question is not mandatory
			{
			$answer .= "\t\t\t\t\t<td align='center' class='array1'>"._NOTAPPLICABLE."</td>\n";
			}
		$answer .= "\t\t\t\t</tr>\n";
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			$myfname = $ia[1].$ansrow['code'];
			if ($trbc == "array1" || !$trbc) {$trbc = "array2";} else {$trbc = "array1";}
			$answer .= "\t\t\t\t<tr class='$trbc'>\n"
					 . "\t\t\t\t\t<td align='right'>{$ansrow['answer']}</td>\n";
			for ($i=1; $i<=5; $i++)
				{
				$answer .= "\t\t\t\t\t<td><input class='radio' type='radio' name='$myfname' value='$i'";
				if ($_SESSION[$myfname] == $i) {$answer .= " checked";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
				}
			if ($ia[6] != "Y")
				{
				$answer .= "\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value=''";
				if ($_SESSION[$myfname] == "") {$answer .= " checked";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
				}
			$answer .= "\t\t\t\t</tr>\n"
					 . "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='{$_SESSION[$myfname]}'>\n";
			$fn++;
			$inputnames[]=$myfname;
			if ($ia[6] == "Y" && $ia[7] != "Y") //Question is mandatory. Add to mandatory array
				{
				$mandatorys[]=$myfname;
				$mandatoryfns[]=$ia[1];
				}
			if ($ia[6] == "Y" && $ia[7] == "Y")
				{
				$conmandatorys[]=$myfname;
				$conmandatoryfns[]=$ia[1];
				}
			}			
		
		$answer .= "\t\t\t</table>\n";
		break;
	case "B": //ARRAY (10 POINT CHOICE) radio-buttons
		$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
		$qresult = mysql_query($qquery);
		while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		$fn = 1;
		$answer .= "\t\t\t<table class='question'>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td></td>\n";
		for ($xc=1; $xc<=10; $xc++)
			{
			$answer .= "\t\t\t\t\t<td align='center' class='array1'>$xc</td>\n";
			}
		if ($ia[6] != "Y") //Question is not mandatory
			{
			$answer .= "\t\t\t\t\t<td align='center' class='array1'>"._NOTAPPLICABLE."</td>\n";
			}
		$answer .= "\t\t\t\t</tr>\n";
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			$myfname = $ia[1].$ansrow['code'];
			if ($trbc == "array1" || !$trbc) {$trbc = "array2";} else {$trbc = "array1";}
			$answer .= "\t\t\t\t<tr class='$trbc'>\n";
			$answer .= "\t\t\t\t\t<td align='right'>{$ansrow['answer']}</td>\n";
			for ($i=1; $i<=10; $i++)
				{
				$answer .= "\t\t\t\t\t\t<td><input class='radio' type='radio' name='$myfname' value='$i'";
				if ($_SESSION[$myfname] == $i) {$answer .= " checked";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
				}
			if ($ia[6] != "Y")
				{
				$answer .= "\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value=''";
				if ($_SESSION[$myfname] == "") {$answer .= " checked";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
				}
			$answer .= "\t\t\t\t</tr>\n"
					 . "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='{$_SESSION[$myfname]}'>\n";
			$inputnames[]=$myfname;
			$fn++;
			if ($ia[6] == "Y" && $ia[7] != "Y") //Question is mandatory. Add to mandatory array
				{
				$mandatorys[]=$myfname;
				$mandatoryfns[]=$ia[1];
				}
			if ($ia[6] == "Y" && $ia[7] == "Y")
				{
				$conmandatoryfns[]=$ia[1];
				$conmandatorys[]=$myfname;
				}
			}			
		$answer .= "\t\t\t</table>\n";
		break;
	case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
		$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
		$qresult = mysql_query($qquery);
		while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		$fn = 1;
		$answer .= "\t\t\t<table class='question'>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td></td>\n"
				 . "\t\t\t\t\t<td align='center' class='array1'>"._YES."</td>\n"
				 . "\t\t\t\t\t<td align='center' class='array1'>"._UNCERTAIN."</td>\n"
				 . "\t\t\t\t\t<td align='center' class='array1'>"._NO."</td>\n";
		if ($ia[6] != "Y") //Question is not mandatory
			{
			$answer .= "\t\t\t\t\t<td align='center' class='array1'>"._NOTAPPLICABLE."</td>\n";
			}
		$answer .= "\t\t\t\t</tr>\n";
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			$myfname = $ia[1].$ansrow['code'];
			if ($trbc == "array1" || !$trbc) {$trbc = "array2";} else {$trbc = "array1";}
			$answer .= "\t\t\t\t<tr class='$trbc'>\n"
					 . "\t\t\t\t\t<td align='right'>{$ansrow['answer']}</td>\n"
					 . "\t\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value='Y'";
			if ($_SESSION[$myfname] == "Y") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n"
					 . "\t\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value='U'";
			if ($_SESSION[$myfname] == "U") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n"
					 . "\t\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value='N'";
			if ($_SESSION[$myfname] == "N") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
			if ($ia[6] != "Y")
				{
				$answer .= "\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value=''";
				if ($_SESSION[$myfname] == "") {$answer .= " checked";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
				}
			$answer .= "\t\t\t\t</tr>\n"
					 . "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='{$_SESSION[$myfname]}'>\n";
			$inputnames[]=$myfname;
			$fn++;
			if ($ia[6] == "Y" && $ia[7] != "Y") //Question is mandatory. Add to mandatory array
				{
				$mandatorys[]=$myfname;
				$mandatoryfns[]=$ia[1];
				}
			if ($ia[6] == "Y" && $ia[7] == "Y")
				{
				$conmandatorys[]=$myfname;
				$conmandatoryfns[]=$ia[1];
				}
			}			
		$answer .= "\t\t\t</table>\n";
		break;
	case "E": //ARRAY (Increase/Same/Decrease) radio-buttons
		$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
		$qresult = mysql_query($qquery);
		while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		$fn = 1;
		$answer .= "\t\t\t<table class='question'>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td></td>\n"
				 . "\t\t\t\t\t<td align='center' class='array1'>"._INCREASE."</td>\n"
				 . "\t\t\t\t\t<td align='center' class='array1'>"._SAME."</td>\n"
				 . "\t\t\t\t\t<td align='center' class='array1'>"._DECREASE."</td>\n";
		if ($ia[6] != "Y") //Question is not mandatory
			{
			$answer .= "\t\t\t\t\t<td align='center' class='array1'>"._NOTAPPLICABLE."</td>\n";
			}
		$answer .= "\t\t\t\t</tr>\n";
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			$myfname = $ia[1].$ansrow['code'];
			if ($trbc == "array1" || !$trbc) {$trbc = "array2";} else {$trbc = "array1";}
			$answer .= "\t\t\t\t<tr class='$trbc'>\n"
					 . "\t\t\t\t\t<td align='right'>{$ansrow['answer']}</td>\n"
					 . "\t\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value='I'";
			if ($_SESSION[$myfname] == "I") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n"
					 . "\t\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value='S'";
			if ($_SESSION[$myfname] == "S") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n"
					 . "\t\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value='D'";
			if ($_SESSION[$myfname] == "D") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
			if ($ia[6] != "Y")
				{
				$answer .= "\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value=''";
				if ($_SESSION[$myfname] == "") {$answer .= " checked";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
				}
			$answer .= "\t\t\t\t</tr>\n"
					 . "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='{$_SESSION[$myfname]}'>\n";
			$inputnames[]=$myfname;
			$fn++;
			if ($ia[6] == "Y" && $ia[7] != "Y") //Question is mandatory. Add to mandatory array
				{
				$mandatorys[]=$myfname;
				$mandatoryfns[]=$ia[1];
				}
			if ($ia[6] == "Y" && $ia[7] == "Y")
				{
				$conmandatorys[]=$myfname;
				$conmandatoryfns[]=$ia[1];
				}
			}			
		$answer .= "\t\t\t</table>\n";
		break;
	case "F": //ARRAY (Flexible)
		$qquery = "SELECT other, lid FROM {$dbprefix}questions WHERE qid=".$ia[0];
		$qresult = mysql_query($qquery);
		while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other']; $lid = $qrow['lid'];}
		$lquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid ORDER BY sortorder, code";
		//echo $lquery;
		$lresult = mysql_query($lquery);
		while ($lrow=mysql_fetch_array($lresult))
			{
			$labelans[]=$lrow['title'];
			$labelcode[]=$lrow['code'];
			}
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} ORDER BY sortorder, answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		$fn=1;
		$answer .= "\t\t\t<table class='question'>\n"
				 . "\t\t\t\t<tr>\n"
				 . "\t\t\t\t\t<td></td>\n";
		$cellwidth=count($labelans);
		if ($ia[6] != "Y") {$cellwidth++;}
		$cellwidth=60/$cellwidth;
		foreach ($labelans as $ld)
			{
			$answer .= "\t\t\t\t\t<td align='center' class='array1'><font size='1'>".$ld."</td>\n";
			}
		if ($ia[6] != "Y") //Question is not mandatory
			{
			$answer .= "\t\t\t\t\t<td align='center' class='array1'><font size='1'>"._NOTAPPLICABLE."</td>\n";
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
		//elseif ($ansrowavg > 25) {$percwidth=30;}
		else {$percwidth=$ansrowavg*1.2;}
		$otherwidth=(100-$percwidth)/$cellwidth;
		$ansresult = mysql_query($ansquery);
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			$myfname = $ia[1].$ansrow['code'];
			if ($trbc == "array1" || !$trbc) {$trbc = "array2";} else {$trbc = "array1";}
			$answer .= "\t\t\t\t<tr class='$trbc'>\n"
					 . "\t\t\t\t\t<td align='right' width='$percwidth%'>{$ansrow['answer']}</td>\n";
			foreach ($labelcode as $ld)
				{
				$answer .= "\t\t\t\t\t<td align='center' width='$otherwidth%'><input class='radio' type='radio' name='$myfname' value='$ld'";
				if ($_SESSION[$myfname] == $ld['code']) {$answer .= " checked";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
				}
			if ($ia[6] != "Y")
				{
				$answer .= "\t\t\t\t\t<td align='center' width='$otherwidth%'><input class='radio' type='radio' name='$myfname' value=''";
				if ($_SESSION[$myfname] == "") {$answer .= " checked";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
				}
			$answer .= "\t\t\t\t</tr>\n"
					 . "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='{$_SESSION[$myfname]}'>\n";
			$inputnames[]=$myfname;
			$fn++;
			if ($ia[6] == "Y" && $ia[7] != "Y") //Question is mandatory. Add to mandatory array
				{
				$mandatorys[]=$myfname;
				$mandatoryfns[]=$ia[1];
				}
			if ($ia[6] == "Y" && $ia[7] == "Y")
				{
				$conmandatorys[]=$myfname;
				$conmandatoryfns[]=$ia[1];
				}
			}
		$answer .= "\t\t\t</table>\n";
		unset($labelans);
		unset($labelcode);
		break;
		}
$answer .= "\n\t\t\t<input type='hidden' name='display$ia[1]' id='display$ia[0]' value='";
if ($surveyformat == "S")
	{
    $answer .= "on"; //Ifthis is single format, then it must be showing. Needed for checking conditional mandatories
	}
$answer .= "'>\n"; //for conditional mandatory questions

$qtitle=$ia[3];


switch ($ia[4])
	{
	case "L":
	case "O":
		$qtitle .= "<br />\n</b><i><font size='1'>";
		$qtitle .= _INSTRUCTION_LIST;
		break;
	case "M":
	case "P":
		$qtitle .= "<br />\n</b><i><font size='1'>";
		$qtitle .= _INSTRUCTION_MULTI;
		break;
	
	}

if (isset($notanswered) && is_array($notanswered)) //ADD WARNINGS TO QUESTIONS IF THEY WERE MANDATORY BUT NOT ANSWERED
	{
	if (in_array($ia[1], $notanswered))
		{
		$qtitle = "</b><font color='red' size='1'>"._MANDATORY.".";
		if ($ia[4] == "A" || $ia[4] == "B" || $ia[4] == "C" || $ia[4] == "Q" || $ia[4] == "F")
			{ $qtitle .= "<br />\n"._MANDATORY_PARTS."."; }
		if ($ia[4] == "M" || $ia[4] == "P")
			{ $qtitle .= "<br />\n"._MANDATORY_CHECK.".";}
		if ($ia[4] == "R")
			{ $qtitle .= "<br />\n"._MANDATORY_RANK."."; }
		$qtitle .= "</font><b><br />\n";
		$qtitle .= $ia[3];
		}
	//POPUP WARNING
	if (!isset($mandatorypopup))
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._MANDATORY_POPUP."\")\n //-->\n</script>\n";
		$mandatorypopup="Y";
		}
	}

$qanda[]=array($qtitle, $answer, $help, $display, $name, $ia[2], $gl[0]);
?>