<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
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

session_start();

if ($move == "clearall" || $move == "here" || $move == "completed") 
	{
	session_unset();
	session_destroy();
	}

if ($fvalue) 
	{
	if ($fvalue == " ")
		{
		$$lastfield="";
		}
	else
		{
		$$lastfield=$fvalue;
		}
	}

if ($multi)
	{
	$myfields=explode("|", $lastfield);
	for ($i=1; $i<=$multi; $i++)
		{
		$mylist = "fvalue$i";
		$arrayno=$i-1;
		$$myfields[$arrayno]=$$mylist;
		}
	$mylist=substr($mylist, 0, strlen($mylist)-1);
	}

if ($move == " << prev " && $newgroup != "yes") {$step=$thisstep-1;} else {$step=$thisstep;}
if ($move == " next >> ") {$step=$thisstep+1;}
if ($move == " last ") {$step=$thisstep+1;}

include("./admin/config.php");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0


echo "<HTML><HEAD><TITLE>$sitename</TITLE></HEAD>\n<BODY><FONT FACE='VERDANA'>";

//FIRST, LETS HANDLE SOME CONTINGENCIES

if (!$sid && (!$move == "clearall" || !$move=="here"))
	{
	echo "<CENTER><B>$sitename</B><BR><BR><B>You cannot access this website without a valid Survey ID code.</B><BR>";
	echo "<BR>Please contact $siteadminemail for information.";
	exit;
	}

if (!mysql_selectdb ($databasename, $connect))
	{
	echo "<CENTER><B>$sitename<BR><BR><FONT COLOR='RED'>ERROR</FONT></B><BR><BR>";
	echo "This system has not yet been installed properly.<BR>";
	echo "Contact your $siteadminemail for information";
	exit;
	}

// NOW LETS GATHER SOME INFORMATION ABOUT THIS PARTICULAR SURVEY
if ($sid)
	{
	$desquery = "SELECT * FROM surveys WHERE sid=$sid";
	$desresult = mysql_query($desquery);
	$descount=mysql_num_rows($desresult);
	while ($desr=mysql_fetch_row($desresult))
		{$expirydate=$desr[6];}
	if ($descount == 0) 
		{
		echo "There is no survey with that SID. Sorry. [$descount][$desquery]";
		exit;
		}
	elseif ($expirydate < date("Y-m-d") && $expirydate != "0000-00-00")
		{
		echo "<CENTER><B>$sitename<BR><BR><FONT COLOR='RED'>ERROR</FONT></B><BR><BR>";
		echo "Sorry. This survey has expired and is no longer available.<BR>(Expiry date $expirydate)";
		exit;
		}
	$desresult = mysql_query($desquery);
	while ($desrow=mysql_fetch_row($desresult))
		{
		$surveyname=$desrow[1];
		$surveydesc=$desrow[2];
		$surveyactive=$desrow[4];
		$surveytable="survey_$desrow[0]";
		$surveywelcome=$desrow[5];
		$surveyadminname=$desrow[3];
		$surveyadminemail=$desrow[7];
		}
	$surveyheader = "<TABLE WIDTH='95%' ALIGN='CENTER' BORDER='1' style='border-collapse: collapse' bordercolor='#111111'>\n";
	$surveyheader .= "<TR><TD COLSPAN='2' BGCOLOR='SILVER' ALIGN='CENTER'><BR><FONT COLOR='#000080'><FONT SIZE='4'><B>$surveyname</B></FONT><BR>\n";
	$surveyheader .= "<FONT SIZE='1' COLOR='#444444'>$surveydesc<BR>&nbsp;</TD></TR>\n";	
	
	//LETS SEE IF THERE ARE TOKENS FOR THIS SURVEY
	$i=0; $tokensexist=0;
	$tresult=@mysql_list_tables($databasename);
	while($tbl=@mysql_tablename($tresult, $i++))
		{
		if ($tbl=="tokens_$sid") {$tokensexist=1;}
		}
	}

//THIS CLEARS ALL DATA WHEN CLEARALL OR FINISH HAS BEEN CHOSEN
if ($move == "clearall" || $move == "here")
	{
	$fieldname="";
	$fieldarray="";
	$step="";
	$totalsteps="";
	$token="";
	echo "<BR>&nbsp;<BR><CENTER>All data has been deleted<BR>&nbsp;<BR><a href='javascript:window.close()'>Close</a><BR><BR>&nbsp;$sid";
	exit;
	}


//THIS IS THE LAST POINT. HERE, WE GATHER ALL THE SESSION VARIABLES AND INSERT THEM INTO THE DATABASE.
if ($move == "completed")
	{
	echo "<TABLE WIDTH='95%' ALIGN='CENTER' BORDER='1' style='border-collapse: collapse' bordercolor='#111111'>\n";
	echo "<TR><TD COLSPAN='2' BGCOLOR='SILVER' ALIGN='CENTER'><FONT COLOR='#000080'><FONT SIZE='4'><B>$sitename</B></FONT><BR>\n";
	echo "<FONT SIZE='1' COLOR='#444444'>&nbsp;</TD></TR>\n";	
	echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>$setfont<BR>This is the \"$sitename\" Survey site.<BR>";
	echo "<BR><BR><a href='javascript: window.close()'>Close Window</a><BR></TD></TR>\n";
	exit;
	}

if ($move == " last ")
	{
	echo $surveyheader;
	$s=$step-1;
	$t=$s-1;
	$u=$totalsteps;
	$chart=105;
	echo "<TR><TD COLSPAN='2' ALIGN='CENTER' BGCOLOR='EEEEEE'>";
	echo "Survey Complete<BR>";
	echo "<TABLE WIDTH='175' ALIGN='CENTER' BORDER='1' style='border-collapse: collapse' bordercolor='#111111'>";
	echo "<TR><TD WIDTH='35' ALIGN='RIGHT'><FONT SIZE='1'>0%</TD><TD WIDTH='105'>";
	echo "<IMG SRC='chart.jpg' HEIGHT='15' WIDTH='$chart'>";
	echo "</TD><TD WIDTH='35'><FONT SIZE='1'>100%</TD></TR></TABLE>\n";
	echo "</TD></TR>\n";
	echo "<FORM METHOD='POST'>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='thisstep' VALUE='$step'>\n";
	echo "<TR><TD ALIGN='CENTER' COLSPAN='2'><TABLE WIDTH='500' ALIGN='CENTER'><TR><TD ALIGN='CENTER'>";
	echo "$setfont<B>Congratulations. You have completed answering the<BR>questions in this survey.</B><P>";
	echo "Click on \"Submit\" now to complete the process and submit your answers to our records.";
	echo "If you want to check any of the answers you have made, and/or change them, you can do that now by ";
	echo "clicking on the \" << prev \" button and browsing through your responses.<BR>&nbsp;";
	echo "<BR><input type='submit' value=' submit ' name='move'><BR>&nbsp;";
	echo "</TD></TR></TABLE>\n";
	echo "<TABLE WIDTH='400' ALIGN='CENTER' BGCOLOR='#EFEFEF'><TR><TD ALIGN='CENTER'>$setfont<B>A note on privacy</B><BR>";
	echo "<FONT SIZE='1'>The record kept of this survey does not contain any identifying information about you unless ";
	echo "a specific question in the survey has asked for this. If you have responded to a survey that ";
	echo "used an identifying token to allow you to access the survey, you can rest assured that the ";
	echo "identifying token is not kept with your responses. It is managed in a seperate database, and will ";
	echo "only be updated to indicate that you have (or haven't) completed this survey. There is no way of ";
	echo "relating identification tokens with responses in this system.</TD></TR></TABLE>\n";
	echo "<FONT SIZE='1'>&nbsp;<BR>If you do not wish to submit responses to this survey,<BR>and you would like to delete all records";
	echo " on your computer that<BR> may have saved your responses, click ";
	echo "<a href='index.php?move=clearall&sid=$sid'>here</a><BR>&nbsp;";
	//echo "<input type='submit' name='move' value='here' style='height:15; font-size:9; font-family:verdana' onclick=\"window.open('index.php?clearall', '_top')\">\n";
	echo "</TD></TR>\n";
	echo surveymover();
	exit;
	}

if ($move == " submit ")
	{
	echo "$surveyheader";
	//echo $surveyactive;
	echo "<TR><TD><BR>&nbsp;<BR>";
	echo "<TABLE WIDTH='175' ALIGN='CENTER' BORDER='1' style='border-collapse: collapse' bordercolor='#111111'>";
	echo "<TR><TD COLSPAN='2' ALIGN='CENTER' BGCOLOR='#CCCCCC'><BR><B>Results are being submitted...<BR><BR></TD></TR>\n";	
	$subquery = "INSERT INTO $surveytable VALUES ('',";
	foreach ($insertarray as $in)
		{
		if (get_magic_quotes_gpc()=="0")
			{
			$subquery .= "'". addcslashes($$in, "'") . "',";
			}
		else
			{
			$subquery .= "'".$$in."',";
			}
		//echo "$in<BR>";
		}
	$subquery = substr($subquery, 0, strlen($subquery)-1);
	$subquery .= ")";
	//echo $subquery;
	if ($surveyactive == "Y")
		{
		$subresult=mysql_query($subquery) or die ("Couldn't update $surveytable<BR>".mysql_error()."<BR><BR>$subquery");
		echo "<TR><TD COLSPAN='2' ALIGN='CENTER' BGCOLOR='#EEEEEE'><BR><FONT COLOR='RED'>Thank you!</FONT><BR>Results have been successfully updated.<BR>&nbsp;</TD></TR>\n";
		if ($token)
			{
			$utquery = "UPDATE tokens_$sid SET completed='Y' WHERE token='$token'";
			$utresult=mysql_query($utquery) or die ("Couldn't update tokens table!<BR>$utquery<BR>".mysql_error());
			//MAIL CONFIRMATION TO PARTICIPANT
			$cnfquery="SELECT * FROM tokens_$sid WHERE token='$token' AND completed='Y'";
			$cnfresult=mysql_query($cnfquery);
			while ($cnfrow=mysql_fetch_row($cnfresult))
				{
				$headers="From: $surveyadminemail\r\n";
				$headers.="X-Mailer: $sitename Email Inviter";
				$to=$cnfrow[3];
				$subject="Confirmation: $surveyname Survey Completed";
				$message = "Dear $cnfrow[1],\n\n";
				$message .= "This email is to confirm that you have completed the survey titled \"$surveyname\" ";
				$message .= "and your response has been saved. Thank you for participating.\n\n";
				$message .= "Please note that your survey submission does not contain any link to your personal ";
				$message .= "information used to send you this confirmation or the original invitation.\n";
				$message .= "The information you submitted in the survey is anonymous unless a question in the ";
				$message .= "survey itself actually asks for such information.\n\n";
				$message .= "If you have any questions about this survey please contact $surveyadminname on ";
				$message .= "$surveyadminemail.\n\n";
				$message .= "Sincerely,\n\n";
				$message .= "$surveyadminname";
				mail($to, $subject, $message, $headers);
				}
			}
		}
	else
		{
		echo"<TR><TD COLSPAN='2' ALIGN='CENTER' BGCOLOR='#EEEEEE'><BR><FONT COLOR='RED'>Sorry!</FONT><BR>Could not submit results - survey has not been activated<BR>&nbsp;</TD></TR>\n";
		echo "<TR><TD><FONT SIZE='1'>$subquery</TD></TR>\n";
		}
	echo "</TABLE><CENTER><BR><a href='?move=completed'>Finish</a></CENTER><BR>\n</TD></TR></TABLE>";
	exit;
	}
	
// THIS IS FOR WHEN THE SURVEY SCRIPTS AND STUFF HAVEN'T STARTED YET
if (!$step)
	{
	if ($tokensexist == 1 && !$token)
		{
		echo "<CENTER><B>$sitename</B><BR><BR><B>You cannot access this website without a valid token.</B><BR>";
		echo "Tokens are issued to invited participants. If you have been invited to participate in this<BR>";
		echo "survey but have not got a token, please contact $siteadminemail for information.<BR>&nbsp;";
		echo "<TABLE ALIGN='CENTER' BGCOLOR='#EEEEEE'><TR><FORM METHOD='POST'>\n";
		echo "<TD ALIGN='CENTER'>If you have been issued a token, please enter it here to proceed:<BR>";
		echo "<INPUT TYPE='TEXT' SIZE='10' NAME='token'><BR>";
		echo "<INPUT TYPE='SUBMIT' VALUE='Go'>\n";
		echo "</TD><INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'></TR></FORM></TABLE>\n";
		exit;
		}
	if ($tokensexist == 1 && $token)
		{
		//check if token actually does exist
		$tkquery = "SELECT * FROM tokens_$sid WHERE token='$token' AND completed != 'Y'";
		$tkresult=mysql_query($tkquery);
		$tkexist=mysql_num_rows($tkresult);
		if ($tkexist > 0)
			{
			session_register("token");
			}
		else
			{
			echo "<CENTER><B>$sitename</B><BR><BR><B>The token you have submitted has either been used or does not exist.</B><BR>";
			echo "Tokens are issued to invited participants. If you have been invited to participate in this<BR>";
			echo "survey but your token has failed, please contact $siteadminemail for more information.<BR>&nbsp;";
			echo "<TABLE ALIGN='CENTER' BGCOLOR='#EEEEEE'><TR><FORM METHOD='POST'>\n";
			echo "<TD ALIGN='CENTER'>If you have been issued a token, please enter it here to proceed:<BR>";
			echo "<INPUT TYPE='TEXT' SIZE='10' NAME='token'><BR>";
			echo "<INPUT TYPE='SUBMIT' VALUE='Go'>\n";
			echo "</TD><INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'></TR></FORM></TABLE>\n";
			exit;
			}
		}
	echo "<TABLE WIDTH='95%' ALIGN='CENTER' BORDER='1' style='border-collapse: collapse' bordercolor='#111111'>\n";
	echo "<TR><TD COLSPAN='2' BGCOLOR='SILVER' ALIGN='CENTER'><FONT COLOR='#000080'><B>$sitename</B></FONT>";
	echo "</TD></TR>\n\n";	
	echo "<TR><TD COLSPAN='2' BGCOLOR='#DDDDDD' ALIGN='CENTER'>";
	echo "<FONT SIZE='4'><B>Welcome</B></FONT></TD></TR>";
	echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>&nbsp;<BR>";
	echo "<B>$surveyname</B><BR>";
	//echo "$setfont<I>$surveydesc</I><BR>";
	echo "$surveywelcome<BR>&nbsp;<BR>Click \"Next\" to begin.<BR>&nbsp;";
	echo "</TD></TR>\n";
	$aquery="SELECT * FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid=$sid ORDER BY group_name";
	$aresult=mysql_query($aquery);
	$totalsteps=mysql_num_rows($aresult);
	echo "<TR><TD ALIGN='CENTER' COLSPAN='2' BGCOLOR='#DDDDDD'>$setfont There are $totalsteps questions in this survey.</TD></TR>\n";
	echo "<FORM METHOD='POST'>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='thisstep' VALUE='$step'>\n";
	echo "<TR><TD ALIGN='CENTER' COLSPAN='2'>";

	session_register("fieldarray");
	$_SESSION["step"] = $step; // session_register("step") causes really strange session behavior on PHP 4.3.0, Apache 2.0.43, WinXP
	session_register("totalsteps");
	session_register("insertarray");
	session_register("sid");
	
	while ($arow=mysql_fetch_array($aresult)) {$arows[]=$arow;} // Get table output into array
	
	if ($totalsteps > 0)
		{
		// Perform a case insensitive natural sort on title column of a multidimensional array
		usort($arows, create_function('$a,$b', 'return strnatcasecmp($a["title"],$b["title"]);'));
		} // end if there's anything to sort
	
	foreach ($arows as $arow)
		{
		//WE ARE CREATING A SESSION VARIABLE FOR EVERY FIELD IN THE SURVEY
		$fieldname="{$arow['sid']}X{$arow['gid']}X{$arow['qid']}";
		if ($arow['type'] == "M" || $arow['type'] == "A" || $arow['type'] == "B" || $arow['type'] == "C" || $arow['type'] == "P")
			{
			$abquery = "SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND sid=$sid AND questions.qid={$arow['qid']} ORDER BY code";
			$abresult=mysql_query($abquery);
			while ($abrow=mysql_fetch_row($abresult))
				{
				session_register("F$fieldname".$abrow[1]); //THE F HAS TO GO IN FRONT OF THE FIELDNAME SO THAT PHP RECOGNISES IT AS A VARIABLE
				$insertarray[]="F$fieldname".$abrow[1];
				if ($abrow[4]=="Y") {$alsoother="Y";}
				if ($arow['type'] == "P") 
					{
					session_register("F$fieldname".$abrow[1]."comment");
					$insertarray[]="F$fieldname".$abrow[1]."comment";	
					}
				}
			if ($alsoother) 
				{
				session_register("F$fieldname"."other");
				$insertarray[]="F$fieldname"."other";
				}
			
			}
		elseif ($arow['type'] == "O")
			{
			session_register("F$fieldname");
			$insertarray[]="F$fieldname";
			$fn2="F$fieldname"."comment";
			session_register("$fn2");
			$insertarray[]="$fn2";
			
			}
		else
			{
			session_register("F$fieldname");
			$insertarray[]="F$fieldname";
			}
		//echo "F$fieldname, {$arow['title']}, {$arow['question']}, {$arow['type']}<BR>"; //MORE DEBUGGING STUFF
		//NOW WE'RE CREATING AN ARRAY CONTAINING EACH FIELD
		//ARRAY CONTENTS - [0]=questions.qid, [1]=fieldname, [2]=questions.title, [3]=questions.question
		//                 [4]=questions.type, [5]=questions.gid
		$fieldarray[]=array("{$arow['qid']}", "$fieldname", "{$arow['title']}", "{$arow['question']}", "{$arow['type']}", "{$arow['gid']}");
		}
	//echo count($fieldarray);
	echo "</TD></TR>\n";
	//$step=1;
	}

else
	{
	echo $surveyheader;
	$s=$step;
	//$t indicates which question in the array we should be displaying
	$t=$s-1;
	$v=$t-1;
	$u=$totalsteps;
	$chart=(($s-1)/$u*100);

	// GET AND SHOW GROUP NAME
	$gdquery = "SELECT group_name, groups.description FROM groups, questions WHERE groups.gid=questions.gid and qid={$fieldarray[$t][0]}";
	$gdresult = mysql_query($gdquery);
	while ($gdrow=mysql_fetch_row($gdresult))
		{
		$currentgroupname=$gdrow[0];
		echo "<TR><TD COLSPAN='2' ALIGN='CENTER' BGCOLOR='#DDDDDD'>$setfont<FONT COLOR='#800000'><B>";
		echo "<BR>$currentgroupname<BR>&nbsp;</TD></TR>\n";
		$groupdescription=$gdrow[1];
		}

	//if (($currentgroupname != $lastgroupname) && ($move != " << prev "))
	if ($fieldarray[$t][5] != $fieldarray[$v][5] && $newgroup != "yes" && $groupdescription)
		{
		$presentinggroupdescription="yes";
		echo "<FORM METHOD='POST'>\n";
		//echo "<FORM>\n";
		echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>$setfont<BR>$groupdescription<BR>&nbsp;";
		echo "</TD></TR>\n";
		echo "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
		echo "<INPUT TYPE='HIDDEN' NAME='thisstep' VALUE='$t'>\n";
		echo "<INPUT TYPE='HIDDEN' NAME='newgroup' VALUE='yes'>\n";
		}
	
	
	else
		{
		// SHOW % CHART
		echo "<TR><TD COLSPAN='2' ALIGN='CENTER' BGCOLOR='EEEEEE'>$setfont";
		
		echo "<TABLE WIDTH='175' ALIGN='CENTER' BORDER='1' style='border-collapse: collapse' bordercolor='#111111'>";
		echo "<TR><TD WIDTH='35' ALIGN='RIGHT'><FONT SIZE='1'>0%</TD><TD WIDTH='105'>";
		echo "<IMG SRC='chart.jpg' HEIGHT='15' WIDTH='$chart'>";
		echo "</TD><TD WIDTH='35'><FONT SIZE='1'>100%</TD></TR></TABLE>\n";
	
		echo "</TD></TR>\n";
	
		// PRESENT QUESTION
		echo "<FORM METHOD='POST'>\n";
		//echo "<FORM>\n";
		echo "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
		echo "<INPUT TYPE='HIDDEN' NAME='thisstep' VALUE='$step'>\n";
		
		// QUESTION STUFF
		echo "<TR><TD COLSPAN='2'>\n\n";
		echo "<!-- THE QUESTION IS HERE -->\n";
		echo "<TABLE WIDTH='100%' BORDER='0'>\n";
		echo "<TR><TD COLSPAN='2' HEIGHT='20'></TD></TR>\n";
		echo "<TR><TD COLSPAN='2' HEIGHT='4'>";
		echo "<TABLE WIDTH='50%' ALIGN='CENTER'><TR><TD BGCOLOR='#888888' HEIGHT='3'></TD></TR></TABLE>";
		echo "</TD></TR>\n";
		echo "<TR><TD COLSPAN='2' ALIGN='CENTER' VALIGN='TOP'><B><FONT COLOR='#000080'>";
		echo $fieldarray[$t][3];	
		echo "</TD></TR>";
		echo "<TR><TD COLSPAN='2' HEIGHT='4'>";
		echo "<TABLE WIDTH='50%' ALIGN='CENTER'><TR><TD BGCOLOR='SILVER' HEIGHT='3'></TD></TR></TABLE>";
		echo "</TD></TR>\n";
		$fname="F".$fieldarray[$t][1];
		
		// THE FOLLOWING PRESENTS THE QUESTION BASED ON THE QUESTION TYPE
		switch ($fieldarray[$t][4])
			{
			case "G": //Gender List
				echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>";
				echo "<INPUT TYPE='HIDDEN' NAME='lastfield' VALUE='$fname'>";
				echo "<SELECT NAME='fvalue'>\n";
				echo "  <OPTION VALUE='F'";
				if ($$fname == "F") {echo " SELECTED";}
				echo ">Female</OPTION>\n";
				echo "  <OPTION VALUE='M'";
				if ($$fname == "M") {echo " SELECTED";}
				echo ">Male</OPTION>\n";
				echo "<OPTION VALUE=' '";
				if ($$fname != "F" && $$fname !="M") {echo " SELECTED";}
				echo ">Please choose</OPTION>\n";
				echo "</SELECT>\n";
				break;
			case "Y": //yes/no dropdown list
				echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>";
				echo "<INPUT TYPE='HIDDEN' NAME='lastfield' VALUE='$fname'>";
				echo "<SELECT NAME='fvalue'>\n";
				echo "  <OPTION VALUE='Y'";
				if ($$fname == "Y") {echo " SELECTED";}
				echo ">Yes</OPTION>\n";
				echo "  <OPTION VALUE='N'";
				if ($$fname == "N") {echo " SELECTED";}
				echo ">No</OPTION>\n";
				echo "  <OPTION VALUE=' '";
				if ($$fname != "Y" && $$fname != "N") {echo " SELECTED";}
				echo ">Please choose</OPTION>\n";
				echo "</SELECT>\n";
				break;
			case "L": //dropdown list
				echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>";
				echo "<INPUT TYPE='HIDDEN' NAME='lastfield' VALUE='$fname'>";
				$ansquery = "SELECT * FROM answers WHERE qid=".$fieldarray[$t][0];
				$ansresult = mysql_query($ansquery);
				if ($dropdowns == "L" || !$dropdowns)
					{
					echo "<SELECT NAME='fvalue'>\n";
					while ($ansrow=mysql_fetch_row($ansresult))
						{
						echo "  <OPTION VALUE='$ansrow[1]'";
						if ($$fname == $ansrow[1])
							{ echo " SELECTED"; }
						elseif ($ansrow[3]== "Y") {echo " SELECTED"; $defexists="Y";}
						echo ">$ansrow[2]</OPTION>\n";
						}
					if (!$$fname && !$defexists) {echo "  <OPTION VALUE=' ' SELECTED>Please choose..</OPTION>\n";}
					if ($$fname && !$defexists) {echo "  <OPTION VALUE=' '>No answer</OPTION>\n";}
					echo "</SELECT>\n";
					}
				elseif ($dropdowns == "R")
					{
					echo "<TABLE ALIGN='CENTER'><TR><TD>$setfont";
					while ($ansrow=mysql_fetch_row($ansresult))
						{
						echo "  <INPUT TYPE='RADIO' VALUE='$ansrow[1]' NAME='fvalue'";
						if ($$fname == $ansrow[1])
							{ echo " CHECKED"; }
						elseif ($ansrow[3] == "Y") {echo " CHECKED"; $defexists="Y";}
						echo ">$ansrow[2]<BR>\n";
						}
					if (!$$fname && !$defexists) {echo "  <INPUT TYPE='RADIO' NAME='fvalue' VALUE=' ' CHECKED>No answer\n";}
					elseif ($ffname && !$defexists) {echo "  <INPUT TYPE='RADIO' NAME='fvalue' VALUE=' '>No answer\n";}
					echo "</TD></TR></TABLE>\n";
					}
				break;
			case "O": //dropdown list
				echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>";
				//echo "<INPUT TYPE='HIDDEN' NAME='lastfield' VALUE='$fname'>";
				$ansquery = "SELECT * FROM answers WHERE qid=".$fieldarray[$t][0];
				$ansresult = mysql_query($ansquery);
				$anscount=mysql_num_rows($ansresult);
				echo "<TABLE ALIGN='CENTER'>";
				echo "<TR><TD>$setfont<U>Choose one of the following:</U></TD><TD>$setfont<U>Please enter your comment here:</TD></TR>\n";
				echo "<TR><TD VALIGN='TOP'>$setfont";
				while ($ansrow=mysql_fetch_row($ansresult))
					{
					echo "  <INPUT TYPE='RADIO' VALUE='$ansrow[1]' NAME='fvalue1'";
					if ($$fname == $ansrow[1])
						{ echo " CHECKED"; }
					elseif ($ansrow[3] == "Y") {echo " CHECKED"; $defexists="Y";}
					echo ">$ansrow[2]<BR>\n";
					}
				if (!$$fname && !$defexists) {echo "  <INPUT TYPE='RADIO' NAME='fvalue1' VALUE=' ' CHECKED>No answer\n";}
				elseif ($$fname && !$defexists) {echo "  <INPUT TYPE='RADIO' NAME='fvalue1' VALUE=' '>No answer\n";}
				echo "</TD>\n";
				$fname2=$fname."comment";
				if ($anscount > 8) {$tarows=$anscount/1.2;} else {$tarows=4;}
				echo "<TD VALIGN='TOP'><TEXTAREA NAME='fvalue2' ROWS='$tarows' COLS='30'>".$$fname2."</TEXTAREA>\n";
				$multifields = "$fname|$fname"."comment|";
				echo "<INPUT TYPE='HIDDEN' NAME='multi' VALUE='2'>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='lastfield' VALUE='$multifields'>\n";
				echo "</TR></TABLE>\n";
				break;
			case "M": //MULTIPLE OPTIONS
				echo "<TR><TD WIDTH='30%'></TD><TD WIDTH='70%' ALIGN='LEFT'>";
				$qquery="SELECT other FROM questions WHERE qid=".$fieldarray[$t][0];
				$qresult=mysql_query($qquery);
				while($qrow=mysql_fetch_row($qresult)) {$other=$qrow[0];}
				$ansquery = "SELECT * FROM answers WHERE qid=".$fieldarray[$t][0];
				$ansresult = mysql_query($ansquery);
				$anscount = mysql_num_rows($ansresult);
				$fn=1;
				while ($ansrow=mysql_fetch_row($ansresult))
					{
					$myfname=$fname.$ansrow[1];
					$multifields .= "$fname$ansrow[1]|";
					echo "$setfont<INPUT TYPE='checkbox' NAME='fvalue$fn' VALUE='Y'";
					if ($$myfname == "Y") {echo " CHECKED";}
					echo ">$ansrow[2]<BR>\n";
					$fn++;
					}
				$multifields=substr($multifields, 0, strlen($multifields)-1);
				if ($other == "Y")
					{
					$myfname=$fname."other";
					echo "Other: <INPUT TYPE='TEXT' NAME='fvalue$fn'";
					if ($$myfname) {echo " VALUE='".$$myfname."'";}
					echo ">\n";
					$multifields .= "|$fname"."other";
					$anscount++;
					}
				echo "<INPUT TYPE='HIDDEN' NAME='multi' VALUE='$anscount'>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='lastfield' VALUE='$multifields'>\n";
				break;
			case "P": //MULTIPLE OPTIONS
				echo "<TR><TD WIDTH='30%'></TD><TD WIDTH='70%' ALIGN='LEFT'>";
				$qquery="SELECT other FROM questions WHERE qid=".$fieldarray[$t][0];
				$qresult=mysql_query($qquery);
				while($qrow=mysql_fetch_row($qresult)) {$other=$qrow[0];}
				$ansquery = "SELECT * FROM answers WHERE qid=".$fieldarray[$t][0];
				$ansresult = mysql_query($ansquery);
				$anscount = mysql_num_rows($ansresult)*2;
				$fn=1;
				echo "<TABLE>\n";
				while ($ansrow=mysql_fetch_row($ansresult))
					{
					$myfname=$fname.$ansrow[1];
					$myfname2=$myfname."comment";
					$multifields .= "$fname$ansrow[1]|$fname$ansrow[1]comment|";
					echo "<TR><TD>$setfont<INPUT TYPE='checkbox' NAME='fvalue$fn' VALUE='Y'";
					if ($$myfname == "Y") {echo " CHECKED";}
					echo "><B>$ansrow[2]</B></TD>";
					$fn++;
					echo "<TD><input style='background-color: #EEEEEE; height:18; font-face: verdana; font-size: 9' type='text' SIZE='40' NAME='fvalue$fn' VALUE='".$$myfname2."'></TD></TR>\n";
					$fn++;
					}
				echo "</TABLE>\n";
				$multifields=substr($multifields, 0, strlen($multifields)-1);
				echo "<INPUT TYPE='HIDDEN' NAME='multi' VALUE='$anscount'>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='lastfield' VALUE='$multifields'>\n";
				break;
			case "A": //MULTI ARRAY
				echo "<TR><TD COLSPAN='2'>";
				$qquery="SELECT other FROM questions WHERE qid=".$fieldarray[$t][0];
				$qresult=mysql_query($qquery);
				while($qrow=mysql_fetch_row($qresult)) {$other=$qrow[0];}
				$ansquery = "SELECT * FROM answers WHERE qid=".$fieldarray[$t][0];
				$ansresult = mysql_query($ansquery);
				$anscount = mysql_num_rows($ansresult);
				$fn=1;
				echo "<TABLE>";
				while ($ansrow=mysql_fetch_row($ansresult))
					{
					$myfname=$fname.$ansrow[1];
					$multifields .= "$fname$ansrow[1]|";
					if ($trbc == "#E1E1E1" || !$trbc) {$trbc = "#F1F1F1";} else {$trbc="#E1E1E1";}
					echo "<TR bgcolor='$trbc'><TD ALIGN='RIGHT'>$setfont$ansrow[2]</tD><TD>";
					for ($i=1; $i<=5; $i++)
						{
						echo "$setfont<INPUT TYPE='RADIO' NAME='fvalue$fn' VALUE='$i'";
						if ($$myfname == $i) {echo " CHECKED";}
						echo ">$i&nbsp;";
						}
					echo "</TD></TR>";
					$fn++;
					}			
				echo "</TABLE>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='multi' VALUE='$anscount'>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='lastfield' VALUE='$multifields'>\n";
				break;
			case "B": //MULTI ARRAY
				echo "<TR><TD COLSPAN='2'>";
				$qquery="SELECT other FROM questions WHERE qid=".$fieldarray[$t][0];
				$qresult=mysql_query($qquery);
				while($qrow=mysql_fetch_row($qresult)) {$other=$qrow[0];}
				$ansquery = "SELECT * FROM answers WHERE qid=".$fieldarray[$t][0];
				$ansresult = mysql_query($ansquery);
				$anscount = mysql_num_rows($ansresult);
				$fn=1;
				echo "<TABLE ALIGN='CENTER'>";
				while ($ansrow=mysql_fetch_row($ansresult))
					{
					$myfname=$fname.$ansrow[1];
					$multifields .= "$fname$ansrow[1]|";
					if ($trbc == "#E1E1E1" || !$trbc) {$trbc = "#F1F1F1";} else {$trbc="#E1E1E1";}
					echo "<TR bgcolor='$trbc'><TD ALIGN='RIGHT'>$setfont$ansrow[2]</tD><TD>";
					for ($i=1; $i<=10; $i++)
						{
						echo "$setfont<INPUT TYPE='RADIO' NAME='fvalue$fn' VALUE='$i'";
						if ($$myfname == $i) {echo " CHECKED";}
						echo ">$i&nbsp;";
						}
					echo "</TD></TR>";
					$fn++;
					}			
				echo "</TABLE>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='multi' VALUE='$anscount'>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='lastfield' VALUE='$multifields'>\n";
				break;
			case "C": //MULTI ARRAY
				echo "<TR><TD COLSPAN='2'>";
				$qquery="SELECT other FROM questions WHERE qid=".$fieldarray[$t][0];
				$qresult=mysql_query($qquery);
				while($qrow=mysql_fetch_row($qresult)) {$other=$qrow[0];}
				$ansquery = "SELECT * FROM answers WHERE qid=".$fieldarray[$t][0];
				$ansresult = mysql_query($ansquery);
				$anscount = mysql_num_rows($ansresult);
				$fn=1;
				echo "<TABLE ALIGN='CENTER'>";
				while ($ansrow=mysql_fetch_row($ansresult))
					{
					$myfname=$fname.$ansrow[1];
					$multifields .= "$fname$ansrow[1]|";
					if ($trbc == "#E1E1E1" || !$trbc) {$trbc = "#F1F1F1";} else {$trbc="#E1E1E1";}
					echo "<TR bgcolor='$trbc'><TD ALIGN='RIGHT'>$setfont$ansrow[2]</tD><TD>";
					echo "$setfont<INPUT TYPE='RADIO' NAME='fvalue$fn' VALUE='Y'";
					if ($$myfname == "Y") {echo " CHECKED";}
					echo ">Yes&nbsp;";
					echo "$setfont<INPUT TYPE='RADIO' NAME='fvalue$fn' VALUE='U'";
					if ($$myfname == "U") {echo " CHECKED";}
					echo ">Uncertain&nbsp;";
					echo "$setfont<INPUT TYPE='RADIO' NAME='fvalue$fn' VALUE='N'";
					if ($$myfname == "N") {echo " CHECKED";}
					echo ">No&nbsp;";
					echo "</TD></TR>";
					$fn++;
					}			
				echo "</TABLE>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='multi' VALUE='$anscount'>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='lastfield' VALUE='$multifields'>\n";
				break;
			case "T": //LONG TEXT
				echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>";
				echo "<INPUT TYPE='HIDDEN' NAME='lastfield' VALUE='$fname'>";
				echo "<TEXTAREA NAME='fvalue' ROWS='5' COLS='40'>";
				if ($$fname) {echo str_replace("\\", "", $$fname);}	
				echo "</TEXTAREA>\n";
				break;
			case "S": //SHORT TEXT
				echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>";
				echo "<INPUT TYPE='HIDDEN' NAME='lastfield' VALUE='$fname'>";
				echo "<INPUT TYPE='TEXT' SIZE=50 NAME='fvalue' VALUE=\"".str_replace ("\"", "'", str_replace("\\", "", $$fname))."\">";
				break;
			case "D": //DATE
				echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='lastfield' VALUE='$fname'>\n";
				echo "<INPUT TYPE='TEXT' SIZE=10 NAME='fvalue' VALUE=\"".$$fname."\">";
				echo "<TABLE WIDTH='200' ALIGN='CENTER' BGCOLOR='#EEEEEE'><TR><TD ALIGN='CENTER'><FONT SIZE='1'>Format: YYYY-MM-DD<BR>(eg: 2003-12-25 for christmas day)</TD></TR></TABLE>\n";
				break;
			case "5": //5 POINT OPTION
				echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='lastfield' VALUE='$fname'>\n";
				for ($fp=1; $fp<=5; $fp++)
					{
					echo "<INPUT TYPE='radio' NAME='fvalue' VALUE='$fp'";
					if ($$fname == $fp) {echo " CHECKED";}
					echo ">$fp ";
					}
				break;
			}	

		echo "</TD></TR>\n";
		echo "<TR><TD COLSPAN='2' HEIGHT='4'>";
		echo "<TABLE WIDTH='50%' ALIGN='CENTER'><TR><TD BGCOLOR='SILVER' HEIGHT='3'></TD></TR></TABLE>";
		echo "</TD></TR>\n";


		//SHOW HELP INFORMATION IF THERE IS ANY
		$helpquery = "SELECT help FROM questions WHERE qid=".$fieldarray[$t][0];
		$helpresult = mysql_query($helpquery);
		while ($helprow=mysql_fetch_row($helpresult))
			{
			if ($helprow[0])
				{
				echo "<TR><TD COLSPAN='2'>";
				echo "<TABLE WIDTH='50%' ALIGN='CENTER' CELLSPACING='0'>";
				echo "<TR><TD BGCOLOR='#DEDEDE' VALIGN='TOP'>";
				echo "<IMG SRC='help.gif' vspace='1' ALIGN='LEFT' ALT='Help for this question..'></TD>";
				echo "<TD BGCOLOR='#DEDEDE'><FONT SIZE='1'>$helprow[0]";
				echo "</TD></TR></TABLE></TD></TR>\n";
				}
			}

	
		echo "<TR><TD COLSPAN='2' HEIGHT='20'></TD></TR>\n";
		echo "</TABLE>\n";
		echo "<!-- END OF QUESTION -->\n";
		echo "</TD></TR>\n";
	//echo "<TR><TD COLSPAN='2'>$token</TD></TR>\n";
		}
	}

echo surveymover();
echo "<INPUT TYPE='HIDDEN' NAME='lastgroupname' VALUE='$currentgroupname'>\n";
echo "</FORM>\n";
if ($surveyactive != "Y")
	{
	echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>$setfont<FONT COLOR='RED'>Warning: Survey Not Active. Your survey results will not be recorded</tD></TR>\n";
	}
echo "</TABLE>\n";

function surveymover()
	{
	global $step, $sid, $totalsteps, $presentinggroupdescription;
	$surveymover = "<TR><TD COLSPAN='2' ALIGN='CENTER' BGCOLOR='#EEEEEE'><TABLE WIDTH='50%' ALIGN='CENTER'><TR><TD ALIGN='CENTER'>";
	if ($step > 0) { $surveymover .= "<input type='submit' value=' << prev ' name='move'>";}
	if ($step && (!$totalsteps || ($step < $totalsteps))) { $surveymover .=  " <input type='submit' value=' next >> ' name='move'>";}
	if (!$step) {$surveymover .=  "<input type='submit' value=' next >> ' name='move'>";}
	if ($step && ($step == $totalsteps) && $presentinggroupdescription == "yes") {$surveymover .=  "<input type='submit' value=' next >> ' name='move'>";}
	if ($step && ($step == $totalsteps) && !$presentinggroupdescription) {$surveymover .= " <input type='submit' value=' last ' name='move'>";}
	//if ($step && ($step == $totalsteps+1)) {$surveymover .= " <input type='submit' value=' submit ' name='move'>";}
	//$surveymover .= " <a href='?move=clearall&sid=$sid'>X</a>";
	$surveymover .=  "</TD></TR></TABLE>";
	$surveymover .= "<FONT SIZE='1'>[<a href='index.php?sid=$sid&move=clearall'>Exit and Clear Survey</a>]";
	$surveymover .= "</TD></TR>\n";
	return $surveymover;	
	}
?>