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
	$cquery = "SELECT conditions.qid, conditions.cqid, conditions.cfieldname, conditions.value, questions.type, questions.sid, questions.gid FROM conditions, questions WHERE conditions.cqid=questions.qid AND conditions.qid=$ia[0]";
	$cresult = mysql_query($cquery) or die ("OOPS<BR />$cquery<br />".mysql_error());
	while ($crow = mysql_fetch_array($cresult))
		{
		$conditions[] = array ($crow['qid'], $crow['cqid'], $crow['cfieldname'], $crow['value'], $crow['type'], $crow['sid']."X".$crow['gid']."X".$crow['cqid']);
		}
	}
//QUESTION NAME
$name = $ia[0];

//GET HELP
$hquery="SELECT help FROM questions WHERE qid=$ia[0]";
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
switch ($ia[4])
	{
	case "5": //5 POINT CHOICE radio-buttons
		for ($fp=1; $fp<=5; $fp++)
			{
			$answer .= "\t\t\t<input class='radio' type='radio' name='$ia[1]' value='$fp'";
			if ($_SESSION[$ia[1]] == $fp) {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' />$fp\n";
			}
		$answer .= "\t\t\t<input type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n";
		$inputnames[]=$ia[1];
		break;
	case "D": //DATE
		$answer .= "\t\t\t<input class='text' type='text' size=10 name='$ia[1]' value=\"".$_SESSION[$ia[1]]."\" />\n";
		$answer .= "\t\t\t<table class='question'>\n";
		$answer .= "\t\t\t\t<tr>\n";
		$answer .= "\t\t\t\t\t<td>\n";
		$answer .= "\t\t\t\t\t\t<font size='1'>"._DATEFORMAT."<br />\n";
		$answer .= "\t\t\t\t\t\t"._DATEFORMATEG."\n";
		$answer .= "\t\t\t\t\t</td>\n";
		$answer .= "\t\t\t\t</tr>\n";
		$answer .= "\t\t\t</table>\n";
		$inputnames[]=$ia[1];
		break;
	case "L": //LIST drop-down/radio-button list
		$ansquery = "SELECT * FROM answers WHERE qid=$ia[0] ORDER BY answer";
		$ansresult = mysql_query($ansquery) or die("Couldn't get answers<br />$ansquery<br />".mysql_error());
		if ($dropdowns == "L" || !$dropdowns)
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
			if (!$_SESSION[$ia[1]] && !$defexists && $ia[6] != "Y") {$answer .= "\t\t\t\t\t\t<option value=' ' selected>"._PLEASECHOOSE."..</option>\n";}
			if ($_SESSION[$ia[1]] && !$defexists && $ia[6] != "Y") {$answer .= "\t\t\t\t\t\t<option value=' '>"._NOANSWER."</option>\n";}
			$answer .= "\t\t\t\t\t</select>\n";
			}
		elseif ($dropdowns == "R")
			{
			$answer .= "\n\t\t\t\t\t<table class='question'>\n";
			$answer .= "\t\t\t\t\t\t<tr>\n";
			$answer .= "\t\t\t\t\t\t\t<td>\n";
			while ($ansrow = mysql_fetch_array($ansresult))
				{
				$answer .= "\t\t\t\t\t\t\t\t  <input class='radio' type='radio' value='{$ansrow['code']}' name='$ia[1]'";
				if ($_SESSION[$ia[1]] == $ansrow['code'])
					{
					$answer .= " checked";
					}
				elseif ($ansrow['default'] == "Y") {$answer .= " checked"; $defexists = "Y";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' />{$ansrow['answer']}<br />\n";
				}
			if (((!$_SESSION[$ia[1]] && !$defexists) || ($_SESSION[$ia[1]] == ' ' && !$defexists)) && $ia[6] != "Y") 
				{
				$answer .= "\t\t\t\t\t\t  <input class='radio' type='radio' name='$ia[1]' value=' ' checked onClick='checkconditions(this.value, this.name, this.type)' />";
				$answer .= _NOANSWER."\n";
				}
			elseif ($_SESSION[$ia[1]] && !$defexists && $ia[6] != "Y") 
				{
				$answer .= "\t\t\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' value=' ' onClick='checkconditions(this.value, this.name, this.type)' />";
				$answer .= _NOANSWER."\n";
				}
			$answer .= "\t\t\t\t\t\t\t</td>\n";
			$answer .= "\t\t\t\t\t\t</tr>\n";
			$answer .= "\t\t\t\t\t\t<input type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n";
			$answer .= "\t\t\t\t\t</table>\n";
			}
		$inputnames[]=$ia[1];
		break;
	case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
		$ansquery = "SELECT * FROM answers WHERE qid={$ia[0]} ORDER BY answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		if ($lwcdropdowns == "R")
			{
			$answer .= "\t\t\t<table class='question'>\n";
			$answer .= "\t\t\t\t<tr>\n";
			$answer .= "\t\t\t\t\t<td><u>"._CHOOSEONE.":</u></td>\n";
			$answer .= "\t\t\t\t\t<td><u>"._ENTERCOMMENT.":</td>\n";
			$answer .= "\t\t\t\t</tr>\n";
			$answer .= "\t\t\t\t<tr>\n";
			$answer .= "\t\t\t\t\t<td valign='top'>\n";
			
			while ($ansrow=mysql_fetch_array($ansresult))
				{
				$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' value='{$ansrow['code']}' name='$ia[1]'";
				if ($_SESSION[$ia[1]] == $ansrow['code'])
					{$answer .= " checked";}
				elseif ($ansrow['default'] == "Y") {$answer .= " checked"; $defexists = "Y";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' />{$ansrow['answer']}<br />\n";
				}
			if ($ia[6] != "Y")
				{
				if ((!$_SESSION[$ia[1]] && !$defexists) ||($_SESSION[$ia[1]] == ' ' && !$defexists)) 
					{
					$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' value=' ' checked onChange='checkconditions(this.value, this.name, this.type)' />";
					$answer .= _NOANSWER."\n";
					}
				elseif ($_SESSION[$ia[1]] && !$defexists) 
					{
					$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' value=' ' onChange='checkconditions(this.value, this.name, this.type)' />";
					$answer .= _NOANSWER."\n";
					}
				}
			$answer .= "\t\t\t\t\t</td>\n";
			$fname2 = $ia[1]."comment";
			if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
			$answer .= "\t\t\t\t\t<td valign='top'>\n";
			$answer .= "\t\t\t\t\t\t<textarea class='textarea' name='$ia[1]comment' rows='$tarows' cols='30'>";
			if ($_SESSION[$fname2]) 
				{
				$answer .= str_replace("\\", "", $_SESSION[$fname2]);
				}
			$answer .= "</textarea>\n";
			$answer .= "\t\t\t\t\t</td>\n";
			$answer .= "\t\t\t\t</tr>\n";
			$answer .= "\t\t\t\t<input class='radio' type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n";
			$answer .= "\t\t\t</table>\n";
			$inputnames[]=$ia[1];
			$inputnames[]=$ia[1]."comment";
			}
		else //Dropdown list
			{
			$answer .= "\t\t\t<table class='question'>\n";
			$answer .= "\t\t\t\t<tr>\n";
			$answer .= "\t\t\t\t\t<td valign='top' align='center'>\n";
			$answer .= "\t\t\t\t\t<select class='select' name='$ia[1]' onClick='checkconditions(this.value, this.name, this.type)'>\n";
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
			$answer .= "\t\t\t\t\t</select>\n";
			$answer .= "\t\t\t\t\t</td>\n";
			$answer .= "\t\t\t\t</tr>\n";
			$answer .= "\t\t\t\t<tr>\n";
			$fname2 = $ia[1]."comment";
			if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
			$maxoptionsize=$maxoptionsize*0.72;
			if ($maxoptionsize < 33) {$maxoptionsize=33;}
			if ($maxoptionsize > 80) {$maxoptionsize=80;}
			$answer .= "\t\t\t\t\t<td valign='top'>\n";
			$answer .= "\t\t\t\t\t\t<textarea class='textarea' name='$ia[1]comment' rows='$tarows' cols='$maxoptionsize'>";
			if ($_SESSION[$fname2]) 
				{
				$answer .= str_replace("\\", "", $_SESSION[$fname2]);
				}
			$answer .= "</textarea>\n";
			$answer .= "\t\t\t\t\t</td>\n";
			$answer .= "\t\t\t\t</tr>\n";
			$answer .= "\t\t\t\t<input class='radio' type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n";
			$answer .= "\t\t\t</table>\n";
			$inputnames[]=$ia[1];
			$inputnames[]=$ia[1]."comment";
			}
		break;
	case "R": //RANKING STYLE
		$ansquery = "SELECT * FROM answers WHERE qid={$ia[0]} ORDER BY answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		$answer .= "\t\t\t<script type='text/javascript'>\n";
		$answer .= "\t\t\t<!--\n";
		$answer .= "\t\t\t\tfunction rankthis(\$code, \$value)\n";
		$answer .= "\t\t\t\t\t{\n";
		$answer .= "\t\t\t\t\t\$index=document.phpsurveyor.CHOICES.selectedIndex;\n";
		$answer .= "\t\t\t\t\tdocument.phpsurveyor.CHOICES.selectedIndex=-1;\n";
		$answer .= "\t\t\t\t\tfor (i=1; i<=$anscount; i++)\n";
		$answer .= "\t\t\t\t\t\t{\n";
		$answer .= "\t\t\t\t\t\t\$b=i;\n";
		$answer .= "\t\t\t\t\t\t\$b += '';\n";
		$answer .= "\t\t\t\t\t\t\$inputname=\"RANK\"+\$b;\n";
		$answer .= "\t\t\t\t\t\t\$hiddenname=\"fvalue\"+\$b;\n";
		$answer .= "\t\t\t\t\t\t\$cutname=\"cut\"+i;\n";
		$answer .= "\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='none';\n";
		$answer .= "\t\t\t\t\t\tif (!document.getElementById(\$inputname).value)\n";
		$answer .= "\t\t\t\t\t\t\t{\n";
		$answer .= "\t\t\t\t\t\t\tdocument.getElementById(\$inputname).value=\$value;\n";
		$answer .= "\t\t\t\t\t\t\tdocument.getElementById(\$hiddenname).value=\$code;\n";
		$answer .= "\t\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='';\n";
		$answer .= "\t\t\t\t\t\t\tfor (var b=document.getElementById('CHOICES').options.length-1; b>=0; b--)\n";
		$answer .= "\t\t\t\t\t\t\t\t{\n";
		$answer .= "\t\t\t\t\t\t\t\tif (document.getElementById('CHOICES').options[b].value == \$code)\n";
		$answer .= "\t\t\t\t\t\t\t\t\t{\n";
		$answer .= "\t\t\t\t\t\t\t\t\tdocument.getElementById('CHOICES').options[b] = null;\n";
		$answer .= "\t\t\t\t\t\t\t\t\t}\n";
		$answer .= "\t\t\t\t\t\t\t\t}\n";
		$answer .= "\t\t\t\t\t\t\ti=$anscount;\n";
		$answer .= "\t\t\t\t\t\t\t}\n";
		$answer .= "\t\t\t\t\t\t}\n";
		$answer .= "\t\t\t\t\tif (document.getElementById('CHOICES').options.length == 0)\n";
		$answer .= "\t\t\t\t\t\t{\n";
		$answer .= "\t\t\t\t\t\tdocument.getElementById('CHOICES').disabled=true;\n";
		$answer .= "\t\t\t\t\t\t}\n";
		$answer .= "\t\t\t\t\tcheckconditions(\$code);\n";
		$answer .= "\t\t\t\t\t}\n";
		$answer .= "\t\t\t\tfunction deletethis(\$text, \$value, \$name, \$thisname)\n";
		$answer .= "\t\t\t\t\t{\n";
		$answer .= "\t\t\t\t\tvar cutindex=\$thisname.substring(3,6);\n";
		$answer .= "\t\t\t\t\tcutindex=parseFloat(cutindex);\n";
		$answer .= "\t\t\t\t\tdocument.getElementById(\$name).value='';\n";
		$answer .= "\t\t\t\t\tdocument.getElementById(\$thisname).style.display='none';\n";
		$answer .= "\t\t\t\t\tif (cutindex > 1)\n";
		$answer .= "\t\t\t\t\t\t{\n";
		$answer .= "\t\t\t\t\t\t\$cut1name=\"cut\"+(cutindex-1);\n";
		$answer .= "\t\t\t\t\t\t\$cut2name=\"fvalue\"+(cutindex);\n";
		$answer .= "\t\t\t\t\t\tdocument.getElementById(\$cut1name).style.display='';\n";
		$answer .= "\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n";
		$answer .= "\t\t\t\t\t\t}\n";
		$answer .= "\t\t\t\t\telse\n";
		$answer .= "\t\t\t\t\t\t{\n";
		$answer .= "\t\t\t\t\t\t\$cut2name=\"fvalue\"+(cutindex);\n";
		$answer .= "\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n";
		$answer .= "\t\t\t\t\t\t}\n";
		$answer .= "\t\t\t\t\tvar i=document.getElementById('CHOICES').options.length;\n";
		$answer .= "\t\t\t\t\tdocument.getElementById('CHOICES').options[i] = new Option(\$text, \$value);\n";
		$answer .= "\t\t\t\t\tif (document.getElementById('CHOICES').options.length > 0)\n";
		$answer .= "\t\t\t\t\t\t{\n";
		$answer .= "\t\t\t\t\t\tdocument.getElementById('CHOICES').disabled=false;\n";
		$answer .= "\t\t\t\t\t\t}\n";
		$answer .= "\t\t\t\t\tcheckconditions('');\n";
		$answer .= "\t\t\t\t\t}\n";
		$answer .= "\t\t\t//-->\n";
		$answer .= "\t\t\t</script>\n";	
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			$answers[] = array($ansrow['code'], $ansrow['answer']);
			}
	
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
			$ranklist .= "\t\t\t\t\t\t&nbsp;$i:&nbsp;<input class='text' type='text' name='RANK$i' id='RANK$i'";
			if ($_SESSION[$myfname])
				{
				$ranklist .= " value='";
				$ranklist .= $thistext;
				$ranklist .= "'";
				}
			$ranklist .= " onFocus=\"this.blur()\">\n";
			$ranklist .= "\t\t\t\t\t\t<input type='hidden' name='$myfname' id='fvalue$i' value='";
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
			$ranklist .= " id='cut$i' name='cut$i' onClick=\"deletethis(document.phpsurveyor.RANK$i.value, document.phpsurveyor.fvalue$i.value, document.phpsurveyor.RANK$i.name, this.id)\"><br />\n";
			$inputnames[]=$myfname;
			}

		$choicelist = "\t\t\t\t\t\t<select size='$anscount' name='CHOICES' $choicewidth id='CHOICES' onClick=\"rankthis(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" class='select'>\n";
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

		//$answer .= "\t<tr>\n";
		//$answer .= "\t\t<td colspan='2'>\n";
		$answer .= "\t\t\t<table border='0' cellspacing='5' class='rank'>\n";
		$answer .= "\t\t\t\t<tr>\n";
		$answer .= "\t\t\t\t\t<td colspan='2' class='rank'><font size='1'>\n";
		$answer .= "\t\t\t\t\t\t"._RANK_1."<br />";
		$answer .= "\t\t\t\t\t\t"._RANK_2;
		$answer .= "\t\t\t\t\t</td>\n";
		$answer .= "\t\t\t\t</tr>\n";
		$answer .= "\t\t\t\t<tr>\n";
		$answer .= "\t\t\t\t\t<td align='left' valign='top' class='rank'>\n";
		$answer .= "\t\t\t\t\t\t<b>&nbsp;&nbsp;"._YOURCHOICES.":</b><br />\n";
		$answer .= "&nbsp;".$choicelist;
		$answer .= "\t\t\t\t\t&nbsp;</td>\n";
		if ($maxselectlength > 60) 
			{
			$answer .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$ranklist = str_replace("<input class='text'", "<input size='60' class='text'", $ranklist);
			$answer .= "\t\t\t\t\t<td align='left' bgcolor='silver' class='rank'>\n";
			$answer .= "\t\t\t\t\t\t<b>&nbsp;&nbsp;"._YOURRANKING.":</b><br />\n";
			}
		else
			{
			$answer .= "\t\t\t\t\t<td align='left' bgcolor='silver' width='200' class='rank'>\n";
			$answer .= "\t\t\t\t\t\t<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"._YOURRANKING.":</b><br />\n";
			}
		$answer .= $ranklist;
		$answer .= "\t\t\t\t\t</td>\n";
		$answer .= "\t\t\t\t</tr>\n";
		$answer .= "\t\t\t\t<tr>\n";
		$answer .= "\t\t\t\t\t<td colspan='2' class='rank'><font size='1'>\n";
		$answer .= "\t\t\t\t\t\t"._RANK_3."<br />";
		$answer .= "\t\t\t\t\t\t"._RANK_4."";
		$answer .= "\t\t\t\t\t</td>\n";
		$answer .= "\t\t\t\t</tr>\n";
		$answer .= "\t\t\t</table>\n";
		break;
	case "M": //MULTIPLE OPTIONS checkbox
		$answer .= "\t\t\t<table class='question'>\n";
		$answer .= "\t\t\t\t<tr>\n";
		$answer .= "\t\t\t\t\t<td>&nbsp;</td>\n";
		$answer .= "\t\t\t\t\t<td align='left'>\n";
		$qquery = "SELECT other FROM questions WHERE qid=".$ia[0];
		$qresult = mysql_query($qquery);
		while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
		$ansquery = "SELECT * FROM answers WHERE qid={$ia[0]} ORDER BY answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		$answer .= "\t\t\t\t\t<input type='hidden' name='MULTI$ia[1]' value='$anscount'>\n";
		$fn = 1;
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			$myfname = $ia[1].$ansrow['code'];
			$multifields .= "$fname{$ansrow['code']}|";
			$answer .= "\t\t\t\t\t\t<input class='checkbox' type='checkbox' name='$ia[1]{$ansrow['code']}' value='Y'";
			if ($_SESSION[$myfname] == "Y") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' />{$ansrow['answer']}<br />\n";
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
			$answer .= " />\n";
			$answer .= "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='{$_SESSION[$myfname]}'>\n";
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
		$answer .= "\t\t\t\t\t</td>\n";
		$answer .= "\t\t\t\t\t<td>&nbsp;</td>\n";
		$answer .= "\t\t\t\t</tr>\n";
		$answer .= "\t\t\t</table>\n";
		break;
	case "P": //MULTIPLE OPTIONS WITH COMMENTS checkbox + text
		$answer .= "\t\t\t<table class='question'>\n";
		$answer .= "\t\t\t\t<tr>\n";
		$answer .= "\t\t\t\t\t<td>&nbsp;</td>\n";
		$answer .= "\t\t\t\t\t<td align='left'>\n";
		$qquery = "SELECT other FROM questions WHERE qid=".$ia[0];
		$qresult = mysql_query($qquery);
		while ($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
		$ansquery = "SELECT * FROM answers WHERE qid={$ia[0]} ORDER BY answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult)*2;
		$answer .= "\t\t\t\t\t<input type='hidden' name='MULTI$ia[1]' value='$anscount'>\n";
		$fn = 1;
		$answer .= "\t\t\t\t\t\t<table class='question'>\n";
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			$myfname = $ia[1].$ansrow['code'];
			$myfname2 = $myfname."comment";
			$answer .= "\t\t\t\t\t\t\t<tr>\n";
			$answer .= "\t\t\t\t\t\t\t\t<td>\n";
			$answer .= "\t\t\t\t\t\t\t\t\t<input class='checkbox' type='checkbox' name='$myfname' value='Y'";
			if ($_SESSION[$myfname] == "Y") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' />{$ansrow['answer']}\n";
			$answer .= "\t\t\t\t\t\t\t\t</td>\n";
			$answer .= "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='{$_SESSION[$myfname]}'>\n";
			$fn++;
			$answer .= "\t\t\t\t\t\t\t\t<td>\n";
			$answer .= "\t\t\t\t\t\t\t\t\t<input class='text' type='text' type='text' size='40' name='$myfname2' value='".$_SESSION[$myfname2]."' />\n";
			$answer .= "\t\t\t\t\t\t\t\t</td>\n";
			$answer .= "\t\t\t\t\t\t\t</tr>\n";
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
			$answer .= "\t\t\t\t\t\t\t<tr>\n";
			$answer .= "\t\t\t\t\t\t\t\t<td>\n";
			$answer .= "\t\t\t\t\t\t\t\t\t"._OTHER.":<input class='text' type='text' name='$myfname' size='10'";
			if ($_SESSION[$myfname]) {$answer .= " value='".$_SESSION[$myfname]."'";}
			$answer .= " />\n";
			$answer .= "\t\t\t\t\t\t\t\t</td>\n";
			$fn++;
			$answer .= "\t\t\t\t\t\t\t\t<td valign='bottom'>\n";
			$answer .= "\t\t\t\t\t\t\t\t\t<input class='text' type='text' size='40' name='$myfname2' value='".$_SESSION[$myfname2]."' />\n";
			$answer .= "\t\t\t\t\t\t\t\t</td>\n";
			$answer .= "\t\t\t\t\t\t\t</tr>\n";
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
		$answer .= "\t\t\t\t\t\t</table>\n";
		$answer .= "\t\t\t\t\t</td>\n";
		$answer .= "\t\t\t\t\t<td>&nbsp;</td>\n";
		$answer .= "\t\t\t\t</tr>\n";
		$answer .= "\t\t\t</table>\n";
		break;
	case "N": //NUMERICAL QUESTION TYPE
		$answer .= keycontroljs();
		$answer .= "\t\t\t<input class='text' type='text' size='10' name='$ia[1]' value=\"{$_SESSION[$ia[1]]}\" onKeyPress=\"return goodchars(event,'0123456789.')\"/><br />\n";
		$answer .= "\t\t\t<font size='1'><i>"._NUMERICAL."</i></font>\n";
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
		$answer .= "\t\t\t<table class='question'>\n";
		$answer .= "\t\t\t\t<tr>\n";
		$answer .= "\t\t\t\t\t<td>\n";
		$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' value='Y'";
		if ($_SESSION[$ia[1]] == "Y") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' />"._YES."<br />\n";
		$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' value='N'";
		if ($_SESSION[$ia[1]] == "N") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' />"._NO."<br />\n";
		if ($ia[6] != "Y")
			{
			$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' value=''";
			if ($_SESSION[$ia[1]] == "") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' />No Answer<br />\n";
			}
		$answer .= "\t\t\t\t\t</td>\n";
		$answer .= "\t\t\t\t</tr>\n";
		$answer .= "\t\t\t\t<input type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n";
		$answer .= "\t\t\t</table>\n";
		$inputnames[]=$ia[1];
		break;
	case "G": //GENDER drop-down list
		$answer .= "\t\t\t<table class='question'>\n";
		$answer .= "\t\t\t\t<tr>\n";
		$answer .= "\t\t\t\t\t<td>\n";
		$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' value='F'";
		if ($_SESSION[$ia[1]] == "F") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' />"._FEMALE."<br />\n";
		$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' value='M'";
		if ($_SESSION[$ia[1]] == "M") {$answer .= " checked";}
		$answer .= " onClick='checkconditions(this.value, this.name, this.type)' />"._MALE."<br />\n";
		if ($ia[6] != "Y")
			{
			$answer .= "\t\t\t\t\t\t<input class='radio' type='radio' name='$ia[1]' value=''";
			if ($_SESSION[$ia[1]] == "") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' />"._NOANSWER."\n";
			}
		$answer .= "\t\t\t\t\t</td>\n";
		$answer .= "\t\t\t\t</tr>\n";
		$answer .= "\t\t\t\t<input type='hidden' name='java$ia[1]' id='java$ia[1]' value='{$_SESSION[$ia[1]]}'>\n";
		$answer .= "\t\t\t</table>\n";
		$inputnames[]=$ia[1];
		break;
	case "A": //ARRAY (5 POINT CHOICE) radio-buttons
		$qquery = "SELECT other FROM questions WHERE qid=".$ia[0];
		$qresult = mysql_query($qquery);
		while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
		$ansquery = "SELECT * FROM answers WHERE qid={$ia[0]} ORDER BY answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		$fn = 1;
		$answer .= "\t\t\t<table class='question'>\n";
		$answer .= "\t\t\t\t<tr>\n";
		$answer .= "\t\t\t\t\t<td></td>\n";
		for ($xc=1; $xc<=5; $xc++)
			{
			$answer .= "\t\t\t\t\t<td align='center' class='array1'>$xc</td>\n";
			}
		$answer .= "\t\t\t\t</tr>\n";
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			$myfname = $ia[1].$ansrow['code'];
			if ($trbc == "array1" || !$trbc) {$trbc = "array2";} else {$trbc = "array1";}
			$answer .= "\t\t\t\t<tr class='$trbc'>\n";
			$answer .= "\t\t\t\t\t<td align='right'>{$ansrow['answer']}</td>\n";
			for ($i=1; $i<=5; $i++)
				{
				$answer .= "\t\t\t\t\t<td><input class='radio' type='radio' name='$myfname' value='$i'";
				if ($_SESSION[$myfname] == $i) {$answer .= " checked";}
				$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
				}
			$answer .= "\t\t\t\t</tr>\n";
			$answer .= "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='{$_SESSION[$myfname]}'>\n";
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
		$qquery = "SELECT other FROM questions WHERE qid=".$ia[0];
		$qresult = mysql_query($qquery);
		while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
		$ansquery = "SELECT * FROM answers WHERE qid={$ia[0]} ORDER BY answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		$fn = 1;
		$answer .= "\t\t\t<table class='question'>\n";
		$answer .= "\t\t\t\t<tr>\n";
		$answer .= "\t\t\t\t\t<td></td>\n";
		for ($xc=1; $xc<=10; $xc++)
			{
			$answer .= "\t\t\t\t\t<td align='center' class='array1'>$xc</td>\n";
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
			$answer .= "\t\t\t\t</tr>\n";
			$answer .= "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='{$_SESSION[$myfname]}'>\n";
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
		$qquery = "SELECT other FROM questions WHERE qid=".$ia[0];
		$qresult = mysql_query($qquery);
		while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
		$ansquery = "SELECT * FROM answers WHERE qid={$ia[0]} ORDER BY answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		$fn = 1;
		$answer .= "\t\t\t<table class='question'>\n";
		$answer .= "\t\t\t\t<tr>\n";
		$answer .= "\t\t\t\t\t<td></td>\n";
		$answer .= "\t\t\t\t\t<td align='center' class='array1'>"._YES."</td>\n";
		$answer .= "\t\t\t\t\t<td align='center' class='array1'>"._UNCERTAIN."</td>\n";
		$answer .= "\t\t\t\t\t<td align='center' class='array1'>"._NO."</td>\n";
		$answer .= "\t\t\t\t</tr>\n";
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			$myfname = $ia[1].$ansrow['code'];
			if ($trbc == "array1" || !$trbc) {$trbc = "array2";} else {$trbc = "array1";}
			$answer .= "\t\t\t\t<tr class='$trbc'>\n";
			$answer .= "\t\t\t\t\t<td align='right'>{$ansrow['answer']}</td>\n";
			$answer .= "\t\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value='Y'";
			if ($_SESSION[$myfname] == "Y") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
			$answer .= "\t\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value='U'";
			if ($_SESSION[$myfname] == "U") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
			$answer .= "\t\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value='N'";
			if ($_SESSION[$myfname] == "N") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
			$answer .= "\t\t\t\t</tr>\n";
			$answer .= "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='{$_SESSION[$myfname]}'>\n";
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
		$qquery = "SELECT other FROM questions WHERE qid=".$ia[0];
		$qresult = mysql_query($qquery);
		while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
		$ansquery = "SELECT * FROM answers WHERE qid={$ia[0]} ORDER BY answer";
		$ansresult = mysql_query($ansquery);
		$anscount = mysql_num_rows($ansresult);
		$fn = 1;
		$answer .= "\t\t\t<table class='question'>\n";
		$answer .= "\t\t\t\t<tr>\n";
		$answer .= "\t\t\t\t\t<td></td>\n";
		$answer .= "\t\t\t\t\t<td align='center' class='array1'>Increase</td>\n";
		$answer .= "\t\t\t\t\t<td align='center' class='array1'>Same</td>\n";
		$answer .= "\t\t\t\t\t<td align='center' class='array1'>Decrease</td>\n";
		$answer .= "\t\t\t\t</tr>\n";
		while ($ansrow = mysql_fetch_array($ansresult))
			{
			$myfname = $ia[1].$ansrow['code'];
			if ($trbc == "array1" || !$trbc) {$trbc = "array2";} else {$trbc = "array1";}
			$answer .= "\t\t\t\t<tr class='$trbc'>\n";
			$answer .= "\t\t\t\t\t<td align='right'>{$ansrow['answer']}</td>\n";
			$answer .= "\t\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value='I'";
			if ($_SESSION[$myfname] == "I") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
			$answer .= "\t\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value='S'";
			if ($_SESSION[$myfname] == "S") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
			$answer .= "\t\t\t\t\t\t<td align='center'><input class='radio' type='radio' name='$myfname' value='D'";
			if ($_SESSION[$myfname] == "D") {$answer .= " checked";}
			$answer .= " onClick='checkconditions(this.value, this.name, this.type)' /></td>\n";
			$answer .= "\t\t\t\t</tr>\n";
			$answer .= "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='{$_SESSION[$myfname]}'>\n";
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
		}
$answer .= "\n\t\t\t<input type='hidden' name='display$ia[1]' id='display$ia[0]' value='";
if ($surveyformat == "S")
	{
    $answer .= "on"; //Ifthis is single format, then it must be showing. Needed for checking conditional mandatories
	}
$answer .= "'>\n"; //for conditional mandatory questions

$qtitle=$ia[3];
if (is_array($notanswered))
	{
	if (in_array($ia[1], $notanswered))
		{
		$qtitle = "</b><font color='red' size='1'>"._MANDATORY.".";
		if ($ia[4] == "A" || $ia[4] == "B" || $ia[4] == "C")
			{ $qtitle .= "<br />\n"._MANDATORY_PARTS."."; }
		if ($ia[4] == "M" || $ia[4] == "P")
			{ $qtitle .= "<br />\n"._MANDATORY_CHECK.".";}
		if ($ia[4] == "R")
			{ $qtitle .= "<br />\n"._MANDATORY_RANK."."; }
		$qtitle .= "</font><b><br />\n";
		$qtitle .= $ia[3];
		}
	}

$qanda[]=array($qtitle, $answer, $help, $display, $name, $ia[2], $gl[0]);
?>