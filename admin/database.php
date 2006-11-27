<?php
/*
#############################################################
# >>> PHPSurveyor                                          #
#############################################################
# > Author:  Jason Cleeland                                 #
# > E-mail:  jason@cleeland.org                             #
# > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
# >          CARLTON SOUTH 3053, AUSTRALIA
# > Date:    20 February 2003                               #
#                                                           #
# This set of scripts allows you to develop, publish and    #
# perform data-entry on surveys.                            #
#############################################################
#                                                           #
#   Copyright (C) 2003  Jason Cleeland                      #
#                                                           #
# This program is free software; you can redistribute       #
# it and/or modify it under the terms of the GNU General    #
# Public License as published by the Free Software          #
# Foundation; either version 2 of the License, or (at your  #
# option) any later version.                                #
#                                                           #
# This program is distributed in the hope that it will be   #
# useful, but WITHOUT ANY WARRANTY; without even the        #
# implied warranty of MERCHANTABILITY or FITNESS FOR A      #
# PARTICULAR PURPOSE.  See the GNU General Public License   #
# for more details.                                         #
#                                                           #
# You should have received a copy of the GNU General        #
# Public License along with this program; if not, write to  #
# the Free Software Foundation, Inc., 59 Temple Place -     #
# Suite 330, Boston, MA  02111-1307, USA.                   #
#############################################################
*/
//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix)) {die ("Cannot run this script directly [database.php]");}

if (!isset($action)) {$action=returnglobal('action');}

if (get_magic_quotes_gpc())
$_POST  = array_map('stripslashes', $_POST);

function db_quote($str)
{
	global $connect;
	return $connect->escape($str);
}

/*
* Gets the maximum question_order field value for a group
* @gid: The id of the group
*/
function get_max_order($gid)
{
	global $connect ;
	global $dbprefix ;
	$query="SELECT MAX(question_order) as max FROM {$dbprefix}questions where gid=".$gid ;
	//$query = "INSERT INTO {$dbprefix}questions (sid, gid, type, title, question, help, other, mandatory, lid) VALUES ('{$_POST['sid']}', '{$_POST['gid']}', '{$_POST['type']}', '{$_POST['title']}', '{$_POST['question']}', '{$_POST['help']}', '{$_POST['other']}', '{$_POST['mandatory']}', '{$_POST['lid']}')";
	$result = $connect->Execute($query) or die($query);
	$gv = $result->FetchRow();
	return $gv['max'];
}

if(isset($surveyid))
{
	$actsurquery = "SELECT define_questions, edit_survey_property, delete_survey FROM {$dbprefix}surveys_rights WHERE sid=$surveyid AND uid = ".$_SESSION['loginID']; //Getting rights for this survey
	$actsurresult = db_execute_assoc($actsurquery);
	$actsurrows = $actsurresult->FetchRow();

	if ($action == "delattribute" && $actsurrows['define_questions'])
	{
		settype($_POST['qaid'], "integer");
		$query = "DELETE FROM ".db_table_name('question_attributes')."
				  WHERE qaid={$_POST['qaid']} AND qid={$_POST['qid']}";
		$result=$connect->Execute($query) or die("Couldn't delete attribute<br />".htmlspecialchars($query)."<br />".htmlspecialchars($connect->ErrorMsg()));
	}
	elseif ($action == "addattribute" && $actsurrows['define_questions'])
	{
		if (isset($_POST['attribute_value']) && $_POST['attribute_value'])
		{
			$query = "INSERT INTO ".db_table_name('question_attributes')."
					  (qid, attribute, value)
					  VALUES ('{$_POST['qid']}', '{$_POST['attribute_name']}', '{$_POST['attribute_value']}')";
			$result = $connect->Execute($query) or die("Error<br />".htmlspecialchars($query)."<br />".htmlspecialchars($connect->ErrorMsg()));
		}
	}
	elseif ($action == "editattribute" && $actsurrows['define_questions'])
	{
		if (isset($_POST['attribute_value']) && $_POST['attribute_value'])
		{
			settype($_POST['qaid'], "integer");
			$query = "UPDATE ".db_table_name('question_attributes')."
					  SET value='{$_POST['attribute_value']}' WHERE qaid='{$_POST['qaid']}' AND qid='{$_POST['qid']}'";
			$result = $connect->Execute($query) or die("Error<br />".htmlspecialchars($query)."<br />".htmlspecialchars($connect->ErrorMsg()));
		}
	}
	elseif ($action == "insertnewgroup" && $actsurrows['define_questions'])
	{
  		$grplangs = GetAdditionalLanguagesFromSurveyID($_POST['sid']);
  		$baselang = GetBaseLanguageFromSurveyID($_POST['sid']);
  		$grplangs[] = $baselang;
        $errorstring = '';  
        foreach ($grplangs as $grouplang)
        {
          if (!$_POST['group_name_'.$grouplang]) { $errorstring.= GetLanguageNameFromCode($grouplang).", ";}
		}
        if ($errorstring!='') 
        {	
            echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Group could not be added. It is missing the group name for the following languages ").":".$errorstring."\")\n //-->\n</script>\n";
        }  

		else
		{
			$_POST  = array_map('db_quote', $_POST);
            $first=true;
    		foreach ($grplangs as $grouplang)
	       	{
		      	if ($first)
                  {
                    $query = "INSERT INTO ".db_table_name('groups')." (sid, group_name, description,group_order,language) VALUES ('{$_POST['sid']}', '{$_POST['group_name_'.$grouplang]}', '{$_POST['description_'.$grouplang]}',".getMaxgrouporder($_POST['sid']).",'{$grouplang}')";
                    $result = $connect->Execute($query);
                    $groupid=$connect->Insert_Id();
                    $first=false;
                  }
                  else{
                        $query = "INSERT INTO ".db_table_name('groups')." (gid, sid, group_name, description,group_order,language) VALUES ('{$groupid}','{$_POST['sid']}', '{$_POST['group_name_'.$grouplang]}', '{$_POST['description_'.$grouplang]}',".getMaxgrouporder($_POST['sid']).",'{$grouplang}')";
                        $result = $connect->Execute($query);
                     }
				if (!$result)
				{
					echo _("Error: The database reported the following error:")."<br />\n";
					echo "<font color='red'>" . htmlspecialchars($connect->ErrorMsg()) . "</font>\n";
					echo "<pre>".htmlspecialchars($query)."</pre>\n";
					echo "</body>\n</html>";
					exit;
				}
			}     

		}
	}

	elseif ($action == "updategroup" && $actsurrows['define_questions'])
	{
		$_POST  = array_map('db_quote', $_POST);
		$grplangs = GetAdditionalLanguagesFromSurveyID($_POST['sid']);
		$baselang = GetBaseLanguageFromSurveyID($_POST['sid']);
		array_push($grplangs,$baselang[0].$baselang[1]);
		foreach ($grplangs as $grplang)
		{
			if (isset($grplang) && $grplang != "")
			{
				$ugquery = "UPDATE ".db_table_name('groups')." SET group_name='{$_POST['group_name_'.$grplang]}', description='{$_POST['description_'.$grplang]}' WHERE sid={$_POST['sid']} AND gid={$_POST['gid']} AND language='{$grplang}'";
				$ugresult = $connect->Execute($ugquery);
				if ($ugresult)
				{
					//echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your Group ($group_name) has been updated!\")\n //-->\n< /script>\n";
					$groupsummary = getgrouplist($_POST['gid']);
				}
				else
				{
					echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Group could not be updated")."\")\n //-->\n</script>\n";
				}
			}
		}

	}

	elseif ($action == "delgroupnone" && $actsurrows['define_questions'])
	{
		if (!isset($gid)) $gid=returnglobal('gid');
		$query = "DELETE FROM ".db_table_name('groups')." WHERE sid=$surveyid AND gid=$gid";
		$result = $connect->Execute($query) or die($connect->ErrorMsg()) ;
		if ($result)
		{
			$gid = "";
			$groupselect = getgrouplist($gid);
			fixsortorderGroups();
		}
		else
		{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Group could not be deleted")."\n$error\")\n //-->\n</script>\n";
		}
	}

	elseif ($action == "delgroup" && $actsurrows['define_questions'])
	{
		if (!isset($gid)) $gid=returnglobal('gid');
		$query = "SELECT qid FROM ".db_table_name('groups').", ".db_table_name('questions')." WHERE ".db_table_name('groups').".gid=".db_table_name('questions').".gid AND ".db_table_name('groups').".gid=$gid";
		if ($result = db_execute_assoc($query))
		{
			if (!isset($total)) $total=0;
			$qtodel=$result->RecordCount();
			while ($row=$result->FetchRow())
			{
				$dquery = "DELETE FROM ".db_table_name('conditions')." WHERE qid={$row['qid']}";
				if ($dresult=$connect->Execute($dquery)) {$total++;}
				$dquery = "DELETE FROM ".db_table_name('answers')." WHERE qid={$row['qid']}";
				if ($dresult=$connect->Execute($dquery)) {$total++;}
				$dquery = "DELETE FROM ".db_table_name('qeustions')." WHERE qid={$row['qid']}";
				if ($dresult=$connect->Execute($dquery)) {$total++;}
			}
			if ($total != $qtodel*3)
			{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Group could not be deleted")."\")\n //-->\n</script>\n";
			}
		}
		$query = "DELETE FROM ".db_table_name('groups')." WHERE sid=$surveyid AND gid=$gid";
		$result = $connect->Execute($query) or die($connect->ErrorMsg()) ;
		if ($result)
		{
			$gid = "";
			$groupselect = getgrouplist($gid);
			fixsortorderGroups();
		}
		else
		{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Group could not be deleted")."\n$error\")\n //-->\n</script>\n";
		}
	}
	elseif ($action == "reordergroups" && $actsurrows['define_questions'])
	{
		$grouporder = explode(",",$_POST['hiddenNodeIds']) ;
		foreach($grouporder as $key =>$value)
		{
			$upgrorder_query="UPDATE ".db_table_name('groups')." SET group_order=$key where gid=$value" ;
			$upgrorder_result = $connect->Execute($upgrorder_query) or die($connect->ErrorMsg()) ;
		}
	}

	elseif ($action == "reorderquestions" && $actsurrows['define_questions'])
	{
		//Getting the hiddeNodeIds field and constructing the question order array
		$questionorder=explode(",",$_POST['hiddenNodeIds']) ;

		foreach($questionorder as $key =>$value)
		{
			$upordquery="UPDATE ".db_table_name('questions')." SET question_order='".str_pad($key+1, 4, "0", STR_PAD_LEFT)."' WHERE qid=".$value."";
			$upordresult= $connect->Execute($upordquery) or die($connect->ErrorMsg()) ;
		}
	}
	elseif ($action == "insertnewquestion" && $actsurrows['define_questions'])
	{
		if (!$_POST['title'])
		{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Answer could not be added. You must insert a code in the mandatory field")."\")\n //-->\n</script>\n";
		}
		else
		{
			$_POST  = array_map('db_quote', $_POST);
			if (!isset($_POST['lid']) || $_POST['lid'] == '') {$_POST['lid']="0";}
			$baselang = GetBaseLanguageFromSurveyID($_POST['sid']);	
			$query = "INSERT INTO ".db_table_name('questions')." (sid, gid, type, title, question, preg, help, other, mandatory, lid, question_order, language)"
			." VALUES ('{$_POST['sid']}', '{$_POST['gid']}', '{$_POST['type']}', '{$_POST['title']}',"
			." '{$_POST['question']}', '{$_POST['preg']}', '{$_POST['help']}', '{$_POST['other']}', '{$_POST['mandatory']}', '{$_POST['lid']}',".getMaxquestionorder($_POST['gid']).",'{$baselang}')";
			$result = $connect->Execute($query);
			// Add other languages
			if ($result)
			{
				$query = "SELECT qid FROM ".db_table_name('questions')." WHERE gid='{$_POST['gid']}' AND sid='{$_POST['sid']}' AND title='{$_POST['title']}' AND language='{$baselang}'";
				$result2 = db_execute_assoc($query);
				while ($res = $result2->FetchRow()) {$curquest = $res['qid'];}
				$addlangs = GetAdditionalLanguagesFromSurveyID($_POST['sid']);
				foreach ($addlangs as $alang)
				{
					if ($alang != "")
					{	
						$query = "INSERT INTO ".db_table_name('questions')." (qid, sid, gid, type, title, question, preg, help, other, mandatory, lid, question_order, language)"
						." VALUES ('$curquest','{$_POST['sid']}', '{$_POST['gid']}', '{$_POST['type']}', '{$_POST['title']}',"
						." '{$_POST['question']}', '{$_POST['preg']}', '{$_POST['help']}', '{$_POST['other']}', '{$_POST['mandatory']}', '{$_POST['lid']}',".getMaxquestionorder($_POST['gid']).",'{$alang}')";
						$result2 = $connect->Execute($query);
						if (!$result2)
						{
							echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Question in lang={$alang} could not be created.")."\\n".$connect->ErrorMsg()."\")\n //-->\n</script>\n";

						}
					}
				}
			}
			
			
			if (!$result)
			{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Question could not be created.")."\\n".$connect->ErrorMsg()."\")\n //-->\n</script>\n";

			}
			else
			{
				$query = "SELECT qid FROM ".db_table_name('questions')." ORDER BY qid DESC LIMIT 1"; //get last question id
				$result=db_execute_assoc($query);
				while ($row=$result->FetchRow()) {$qid = $row['qid'];}
			}
			if (isset($_POST['attribute_value']) && $_POST['attribute_value'])
			{
				$query = "INSERT INTO ".db_table_name('question_attributes')."
					  (qid, attribute, value)
					  VALUES
					  ($qid, '".$_POST['attribute_name']."', '".$_POST['attribute_value']."')";
				$result = $connect->Execute($query);
			}
		}
	}
	elseif ($action == "renumberquestions" && $actsurrows['define_questions'])
	{
		//Automatically renumbers the "question codes" so that they follow
		//a methodical numbering method
		$question_number=1;
		$group_number=0;
		$gselect="SELECT *\n"
		."FROM ".db_table_name('questions').", ".db_table_name('groups')."\n"
		."WHERE ".db_table_name('questions').".gid=".db_table_name('groups').".gid\n"
		."AND ".db_table_name('questions').".sid=$surveyid\n"
		."ORDER BY ".db_table_name('groups').".group_order, title";
		$gresult=db_execute_assoc($gselect) or die ("Error: ".htmlspecialchars($connect->ErrorMsg()));
		$grows = array(); //Create an empty array in case FetchRow does not return any rows
		while ($grow = $gresult->FetchRow()) {$grows[] = $grow;} // Get table output into array
		usort($grows, 'CompareGroupThenTitle');
		foreach($grows as $grow)
		{
			//Go through all the questions
			if ((isset($_GET['style']) && $_GET['style']=="bygroup") && (!isset($groupname) || $groupname != $grow['group_name']))
			{
				$question_number=1;
				$group_number++;
			}
			//echo "GROUP: ".$grow['group_name']."<br />";
			$usql="UPDATE ".db_table_name('questions')."\n"
			."SET question_order='".str_pad($question_number, 4, "0", STR_PAD_LEFT)."'\n"
			."WHERE qid=".$grow['qid'];
			//echo "[$sql]";
			$uresult=$connect->Execute($usql) or die("Error: ".htmlspecialchars($connect->ErrorMsg()));
			$question_number++;
			$groupname=$grow['group_name'];
		}
	}

	elseif ($action == "updatequestion" && $actsurrows['define_questions'])
	{
		$cqquery = "SELECT type FROM ".db_table_name('questions')." WHERE qid={$_POST['qid']}";
		$cqresult=db_execute_assoc($cqquery) or die ("Couldn't get question type to check for change<br />".htmlspecialchars($cqquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
		while ($cqr=$cqresult->FetchRow()) {$oldtype=$cqr['type'];}

		global $change;
		$change = "0";
		if (($oldtype == "J" && $_POST['type']== "I") || ($oldtype == "I" && $_POST['type']== "J") || ($oldtype == $_POST['type']))
		{
			$change = "1";
		}

		if ($oldtype != $_POST['type'])
		{
			//Make sure there are no conditions based on this question, since we are changing the type
			$ccquery = "SELECT * FROM ".db_table_name('conditions')." WHERE cqid={$_POST['qid']}";
			$ccresult = db_execute_assoc($ccquery) or die ("Couldn't get list of cqids for this question<br />".htmlspecialchars($ccquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
			$cccount=$ccresult->RecordCount();
			while ($ccr=$ccresult->FetchRow()) {$qidarray[]=$ccr['qid'];}
			if (isset($qidarray) && $qidarray) {$qidlist=implode(", ", $qidarray);}
		}
		$_POST  = array_map('db_quote', $_POST);
		if (isset($cccount) && $cccount)
		{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Question could not be updated. There are conditions for other questions that rely on the answers to this question and changing the type will cause problems. You must delete these conditions before you can change the type of this question.")." ($qidlist)\")\n //-->\n</script>\n";
		}
		else
		{
			if (isset($_POST['gid']) && $_POST['gid'] != "")
			{
				
				$questlangs = GetAdditionalLanguagesFromSurveyID($_POST['sid']);
				$baselang = GetBaseLanguageFromSurveyID($_POST['sid']);
				array_push($questlangs,$baselang[0].$baselang[1]);
				foreach ($questlangs as $qlang)
				{
					if (isset($qlang) && $qlang != "")
					{
						$uqquery = "UPDATE {$dbprefix}questions "
						. "SET type='{$_POST['type']}', title='{$_POST['title']}', "
						. "question='{$_POST['question_'.$qlang]}', preg='{$_POST['preg']}', help='{$_POST['help_'.$qlang]}', "
						. "gid='{$_POST['gid']}', other='{$_POST['other']}', "
						. "mandatory='{$_POST['mandatory']}'";
						if (isset($_POST['lid']) && trim($_POST['lid'])!="")
						{
							$uqquery.=", lid='{$_POST['lid']}' ";
						}
						$uqquery.= "WHERE sid='{$_POST['sid']}' AND qid='{$_POST['qid']}' AND language='{$qlang}'";
						$uqresult = $connect->Execute($uqquery) or die ("Error Update Question: ".htmlspecialchars($uqquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
						if (!$uqresult)
						{
							echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Question could not be updated")."\n".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
						}
					}
				}
				
				if ($oldtype !=  $_POST['type'] & $change == "0")
				{
					$query = "DELETE FROM {$dbprefix}answers WHERE qid={$_POST['qid']}";
					$result = $connect->Execute($query) or die("Error: ".htmlspecialchars($connect->ErrorMsg()));
					if (!$result)
					{
						echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Answers can't be deleted")."\n".htmlspecialchars($connect->ErrorMsg())."\")\n //-->\n</script>\n";
					}
				}
			}
			else
			{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Question could not be updated")."\")\n //-->\n</script>\n";
			}
		}
	}

	elseif ($action == "copynewquestion" && $actsurrows['define_questions'])
	{
		if (!$_POST['title'])
		{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Answer could not be added. You must insert a code in the mandatory field")."\")\n //-->\n</script>\n";
		}
		else
		{
			$_POST  = array_map('db_quote', $_POST);
			if (!isset($_POST['lid']) || $_POST['lid']=='') {$_POST['lid']=0;}
			//Get maximum order from the question group
			$max=get_max_order($_POST['gid'])+1 ;

			$query = "INSERT INTO {$dbprefix}questions (sid, gid, type, title, question, help, other, mandatory, lid, question_order) VALUES ('{$_POST['sid']}', '{$_POST['gid']}', '{$_POST['type']}', '{$_POST['title']}', '{$_POST['question']}', '{$_POST['help']}', '{$_POST['other']}', '{$_POST['mandatory']}', '{$_POST['lid']}',$max)";
			$result = $connect->Execute($query) or die($connect->ErrorMsg());
			$newqid = $connect->Insert_ID();
			if (!$result)
			{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Question could not be created.")."\\n".htmlspecialchars($connect->ErrorMsg())."\")\n //-->\n</script>\n";

			}
			if (returnglobal('copyanswers') == "Y")
			{
				$q1 = "SELECT * FROM {$dbprefix}answers WHERE qid="
				. returnglobal('oldqid')
				. " ORDER BY code";
				$r1 = db_execute_assoc($q1);
				while ($qr1 = $r1->FetchRow())
				{
					$qr1 = array_map('db_quote', $qr1);
					$i1 = "INSERT INTO {$dbprefix}answers (qid, code, answer, default_value, sortorder) "
					. "VALUES ('$newqid', '{$qr1['code']}', "
					. "'{$qr1['answer']}', '{$qr1['default_value']}', "
					. "'{$qr1['sortorder']}')";
					$ir1 = $connect->Execute($i1);
				}
			}
			if (returnglobal('copyattributes') == "Y")
			{
				$q1 = "SELECT * FROM {$dbprefix}question_attributes
				   WHERE qid=".returnglobal('oldqid')."
				   ORDER BY qaid";
				$r1 = db_execute_assoc($q1);
				while($qr1 = $r1->FetchRow())
				{
					$qr1 = array_map('db_quote', $qr1);
					$i1 = "INSERT INTO {$dbprefix}question_attributes
					   (qid, attribute, value)
					   VALUES ('$newqid',
					   '{$qr1['attribute']}',
					   '{$qr1['value']}')";
					$ir1 = $connect->Execute($i1);
				} // while
			}
		}
	}
	elseif ($action == "delquestion" && $actsurrows['define_questions'])
	{
		if (!isset($qid)) {$qid=returnglobal('qid');}
		//check if any other questions have conditions which rely on this question. Don't delete if there are.
		$ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid=$qid";
		$ccresult = db_execute_assoc($ccquery) or die ("Couldn't get list of cqids for this question<br />".htmlspecialchars($ccquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
		$cccount=$ccresult->RecordCount();
		while ($ccr=$ccresult->FetchRow()) {$qidarray[]=$ccr['qid'];}
		if (isset($qidarray)) {$qidlist=implode(", ", $qidarray);}
		if ($cccount) //there are conditions dependant on this question
		{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Question could not be deleted. There are conditions for other questions that rely on this question. You cannot delete this question until those conditions are removed")." ($qidlist)\")\n //-->\n</script>\n";
		}
		else
		{
			$result = db_execute_assoc("SELECT gid FROM ".db_table_name('questions')." WHERE qid='{$qid}'");
			$row=$result->FetchRow();
			$gid = $row['gid'];
			//see if there are any conditions/attributes/answers for this question, and delete them now as well
			$cquery = "DELETE FROM {$dbprefix}conditions WHERE qid=$qid";
			$cresult = $connect->Execute($cquery);
			$query = "DELETE FROM {$dbprefix}question_attributes WHERE qid=$qid";
			$result = $connect->Execute($query);
			$cquery = "DELETE FROM {$dbprefix}answers WHERE qid=$qid";
			$cresult = $connect->Execute($cquery);
			$query = "DELETE FROM {$dbprefix}questions WHERE qid=$qid";
			$result = $connect->Execute($query);
			fixsortorderQuestions(0,$gid);
			if ($result)
			{
				$qid="";
				$_POST['qid']="";
				$_GET['qid']="";
			}
			else
			{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Question could not be deleted")."\n$error\")\n //-->\n</script>\n";
			}
		}
	}

	elseif ($action == "delquestionall" && $actsurrows['define_questions'])
	{
		if (!isset($qid)) {$qid=returnglobal('qid');}
		//check if any other questions have conditions which rely on this question. Don't delete if there are.
		$ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid={$_GET['qid']}";
		$ccresult = db_execute_assoc($ccquery) or die ("Couldn't get list of cqids for this question<br />".htmlspecialchars($ccquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
		$cccount=$ccresult->RecordCount();
		while ($ccr=$ccresult->FetchRow()) {$qidarray[]=$ccr['qid'];}
		if (isset($qidarray) && $qidarray) {$qidlist=implode(", ", $qidarray);}
		if ($cccount) //there are conditions dependant on this question
		{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Question could not be deleted. There are conditions for other questions that rely on this question. You cannot delete this question until those conditions are removed")." ($qidlist)\")\n //-->\n</script>\n";
		}
		else
		{
			//First delete all the answers
			if (!isset($total)) {$total=0;}
			$query = "DELETE FROM {$dbprefix}answers WHERE qid=$qid";
			if ($result=$connect->Execute($query)) {$total++;}
			$query = "DELETE FROM {$dbprefix}conditions WHERE qid=$qid";
			if ($result=$connect->Execute($query)) {$total++;}
			$query = "DELETE FROM {$dbprefix}questions WHERE qid=$qid";
			if ($result=$connect->Execute($query)) {$total++;}
		}
		if ($total==3)
		{
			$qid="";
			$_POST['qid']="";
			$_GET['qid']="";
		}
		else
		{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Question could not be deleted")."\n$error\")\n //-->\n</script>\n";
		}
	}

	elseif ($action == "modanswer" && $actsurrows['define_questions'])
	{
		switch($_POST['method'])
		{
			// Add a new answer button
			case _("Add new Answer"):
			if (isset($_POST['insertcode']) && $_POST['insertcode']!='')
			{
                $query = "select max(sortorder) as maxorder from ".db_table_name('answers')." where qid='$qid'";
        	    $result = $connect->Execute($query);
       			$newsortorder=sprintf("%05d", $result->fields['maxorder']+1);
	        	$anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
				$baselang = GetBaseLanguageFromSurveyID($surveyid);
				array_unshift($anslangs,$baselang);
       			foreach ($anslangs as $anslang)
    	    	{
    				if(!isset($_POST['default'])) $_POST['default'] = "";
    	    		$query = "INSERT INTO ".db_table_name('answers')." (qid, code, answer, sortorder, default_value,language) VALUES ('{$_POST['qid']}', '{$_POST['insertcode']}', '{$_POST['insertanswer_'.$anslang]}', '{$newsortorder}', '{$_POST['default']}','$anslang')";
           		    if (!$result = $connect->Execute($query))
    				{
    					echo "<script type=\"text/javascript\">\n<!--\n alert(\"".('Failed to insert answer')." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
    				}
				}
			}
		break;
		// Save all answers with one button
		case _("Save All"):
			//Determine autoids by evaluating the hidden field		
            $sortorderids=explode(' ', trim($_POST['sortorderids']));
            $codeids=explode(' ', trim($_POST['codeids']));
            $count=0; 

         	foreach ($sortorderids as $sortorderid)
        	{
        		$langid=substr($sortorderid,0,strpos($sortorderid,'_')); 
        		$orderid=substr($sortorderid,strpos($sortorderid,'_')+1,20);
        		$query = "UPDATE ".db_table_name('answers')." SET code='".$_POST['code_'.$codeids[$count]]."', answer='{$_POST['answer_'.$sortorderid]}' WHERE sortorder=$orderid and language='$langid'";
        		if (!$result = $connect->Execute($query))
        		{
        			echo "<script type=\"text/javascript\">\n<!--\n alert(\"".('Failed to update answers')." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
    			}
    			$count++;
    			if ($count>count($codeids)-1) {$count=0;}
		    }

		break;

		// Pressing the Up button
		case _("Up"):
		$newsortorder=$_POST['sortorder']-1;
		$oldsortorder=$_POST['sortorder'];
		$cdquery = "UPDATE ".db_table_name('answers')." SET sortorder=-1 WHERE qid=$qid AND sortorder='$newsortorder'";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE ".db_table_name('answers')." SET sortorder=$newsortorder WHERE qid=$qid AND sortorder=$oldsortorder";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE ".db_table_name('answers')." SET sortorder='$oldsortorder' WHERE qid=$qid AND sortorder=-1";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		break;

        // Pressing the Down button
		case _("Dn"):
		$newsortorder=$_POST['sortorder']+1;
		$oldsortorder=$_POST['sortorder'];
		$cdquery = "UPDATE ".db_table_name('answers')." SET sortorder=-1 WHERE qid=$qid AND sortorder='$newsortorder'";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE ".db_table_name('answers')." SET sortorder='$newsortorder' WHERE qid=$qid AND sortorder=$oldsortorder";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE ".db_table_name('answers')." SET sortorder=$oldsortorder WHERE qid=$qid AND sortorder=-1";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		break;
		
		// Delete Button
		case _("Del"):
			$query = "DELETE FROM ".db_table_name('answers')." WHERE qid={$qid} AND sortorder='{$_POST['sortorder']}'";
			if (!$result = $connect->Execute($query))
			{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\"".('Failed to delete answer')." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
			}
            fixsortorderAnswers($qid);
		break;
		}
		/*
		
		if ((!isset($_POST['olddefault']) || ($_POST['olddefault'] != $_POST['default']) && $_POST['default'] == "Y") || ($_POST['default'] == "Y" && $_POST['ansaction'] == _("Add"))) //TURN ALL OTHER DEFAULT SETTINGS TO NO
		{
			$query = "UPDATE {$dbprefix}answers SET default_value = 'N' WHERE qid={$_POST['qid']}";
			$result=$connect->Execute($query) or die("Error occurred updating default_value settings");
		}
		if (isset($_POST['code'])) $_POST['code'] = db_quote($_POST['code']);
		if (isset($_POST['oldcode'])) {$_POST['oldcode'] = db_quote($_POST['oldcode']);}
		if (isset($_POST['answer'])) $_POST['answer'] = db_quote($_POST['answer']);
		if (isset($_POST['oldanswer'])) {$_POST['oldanswer'] = db_quote($_POST['oldanswer']);}
		if (isset($_POST['default_value'])) {$_POST['oldanswer'] = db_quote($_POST['default_value']);}
		switch ($_POST['ansaction'])
		{
			case _("Fix Sort"):
			fixsortorderAnswers($_POST['qid']);
			break;
			case _("Sort Alpha"):
			$uaquery = "SELECT * FROM {$dbprefix}answers WHERE qid='{$_POST['qid']}' ORDER BY answer";
			$uaresult = db_execute_assoc($uaquery) or die("Cannot get answers<br />".htmlspecialchars($uaquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
			while($uarow=$uaresult->FetchRow())
			{
				$orderedanswers[]=array("qid"=>$uarow['qid'],
				"code"=>$uarow['code'],
				"answer"=>$uarow['answer'],
				"default_value"=>$uarow['default_value'],
				"sortorder"=>$uarow['sortorder']);
			} // while
			$i=0;
			foreach ($orderedanswers as $oa)
			{
				$position=sprintf("%05d", $i);
				$upquery = "UPDATE {$dbprefix}answers SET sortorder='$position' WHERE qid='{$oa['qid']}' AND code='{$oa['code']}'";
				$upresult = $connect->Execute($upquery);
				$i++;
			} // foreach
			break;
			case _("Add"):
			if ((trim($_POST['code'])=='') || (trim($_POST['answer'])==''))
			{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Answer could not be added. You must include both a Code and an Answer")."\")\n //-->\n</script>\n";
			}
			else
			{
				$uaquery = "SELECT * FROM {$dbprefix}answers WHERE code = '{$_POST['code']}' AND qid={$_POST['qid']}";
				$uaresult = $connect->Execute($uaquery) or die ("Cannot check for duplicate codes<br />".htmlspecialchars($uaquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
				$matchcount = $uaresult->RecordCount();
				if ($matchcount) //another answer exists with the same code
				{
					echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Answer could not be added. There is already an answer with this code")."\")\n //-->\n</script>\n";
				}
				else
				{
					$cdquery = "INSERT INTO {$dbprefix}answers (qid, code, answer, sortorder, default_value) VALUES ('{$_POST['qid']}', '{$_POST['code']}', '{$_POST['answer']}', '{$_POST['sortorder']}', '{$_POST['default']}')";
					$cdresult = $connect->Execute($cdquery) or die ("Couldn't add answer<br />".htmlspecialchars($cdquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
				}
			}
			break;
			case _("Save"):
			if ((trim($_POST['code'])=='') || (trim($_POST['answer'])==''))
			{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Answer could not be updated. You must include both a Code and an Answer")."\")\n //-->\n</script>\n";
			}
			else
			{
				if ($_POST['code'] != $_POST['oldcode']) //code is being changed. Check against other codes and conditions
				{
					$uaquery = "SELECT * FROM {$dbprefix}answers WHERE code = '{$_POST['code']}' AND qid={$_POST['qid']}";
					$uaresult = $connect->Execute($uaquery) or die ("Cannot check for duplicate codes<br />".htmlspecialchars($uaquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
					$matchcount = $uaresult->RecordCount();
					$ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid={$_POST['qid']} AND value='{$_POST['oldcode']}'";
					$ccresult = db_execute_assoc($ccquery) or die ("Couldn't get list of cqids for this answer<br />".htmlspecialchars($ccquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
					$cccount=$ccresult->RecordCount();
					while ($ccr=$ccresult->FetchRow()) {$qidarray[]=$ccr['qid'];}
					if (isset($qidarray)) {$qidlist=implode(", ", $qidarray);}
				}
				if (isset($matchcount) && $matchcount) //another answer exists with the same code
				{
					echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Answer could not be updated. There is already an answer with this code")."\")\n //-->\n</script>\n";
				}
				else
				{
					if (isset($cccount) && $cccount) // there are conditions dependent upon this answer to this question
					{
						echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Answer could not be updated. You have changed the answer code, but there are conditions to other questions which are dependant upon the old answer code to this question. You must delete these conditions before you can change the code to this answer.")." ($qidlist)\")\n //-->\n</script>\n";
					}
					else
					{
						$cdquery = "UPDATE {$dbprefix}answers SET qid='{$_POST['qid']}', code='{$_POST['code']}', answer='{$_POST['answer']}', sortorder='{$_POST['sortorder']}', default_value='{$_POST['default']}' WHERE code='{$_POST['oldcode']}' AND qid='{$_POST['qid']}'";
						$cdresult = $connect->Execute($cdquery) or die ("Couldn't update answer<br />".htmlspecialchars($cdquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
					}
				}
			}
			break;
			case _("Del"):
			$ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid={$_POST['qid']} AND value='{$_POST['oldcode']}'";
			$ccresult = db_execute_assoc($ccquery) or die ("Couldn't get list of cqids for this answer<br />".htmlspecialchars($ccquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
			$cccount=$ccresult->RecordCount();
			while ($ccr=$ccresult->FetchRow()) {$qidarray[]=$ccr['qid'];}
			if (isset($qidarray) && $qidarray) {$qidlist=implode(", ", $qidarray);}
			if ($cccount)
			{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Answer could not be deleted. There are conditions for other questions that rely on this answer. You cannot delete this answer until those conditions are removed")." ($qidlist)\")\n //-->\n</script>\n";
			}
			else
			{
				$cdquery = "DELETE FROM {$dbprefix}answers WHERE code='{$_POST['oldcode']}' AND answer='{$_POST['oldanswer']}' AND qid='{$_POST['qid']}'";
				$cdresult = $connect->Execute($cdquery) or die ("Couldn't update answer<br />".htmlspecialchars($cdquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
			}
			fixsortorderAnswers($qid);
			break;
			case _("Up"):
			$newsortorder=sprintf("%05d", $_POST['sortorder']-1);
			$replacesortorder=$newsortorder;
			$newreplacesortorder=sprintf("%05d", $_POST['sortorder']);
			$cdquery = "UPDATE {$dbprefix}answers SET sortorder='PEND' WHERE qid=$qid AND sortorder='$newsortorder'";
			$cdresult=$connect->Execute($cdquery) or die(htmlspecialchars($connect->ErrorMsg()));
			$cdquery = "UPDATE {$dbprefix}answers SET sortorder='$newsortorder' WHERE qid=$qid AND sortorder='$newreplacesortorder'";
			$cdresult=$connect->Execute($cdquery) or die(htmlspecialchars($connect->ErrorMsg()));
			$cdquery = "UPDATE {$dbprefix}answers SET sortorder='$newreplacesortorder' WHERE qid=$qid AND sortorder='PEND'";
			$cdresult=$connect->Execute($cdquery) or die(htmlspecialchars($connect->ErrorMsg()));
			break;
			case _("Dn"):
			$newsortorder=sprintf("%05d", $_POST['sortorder']+1);
			$replacesortorder=$newsortorder;
			$newreplacesortorder=sprintf("%05d", $_POST['sortorder']);
			$newreplace2=sprintf("%05d", $_POST['sortorder']);
			$cdquery = "UPDATE {$dbprefix}answers SET sortorder='PEND' WHERE qid=$qid AND sortorder='$newsortorder'";
			$cdresult=$connect->Execute($cdquery) or die(htmlspecialchars($connect->ErrorMsg()));
			$cdquery = "UPDATE {$dbprefix}answers SET sortorder='$newsortorder' WHERE qid=$qid AND sortorder='{$_POST['sortorder']}'";
			$cdresult=$connect->Execute($cdquery) or die(htmlspecialchars($connect->ErrorMsg()));
			$cdquery = "UPDATE {$dbprefix}answers SET sortorder='$newreplacesortorder' WHERE qid=$qid AND sortorder='PEND'";
			$cdresult=$connect->Execute($cdquery) or die(htmlspecialchars($connect->ErrorMsg()));
			break;
			default:
			break;
		}*/
	}

	elseif ($action == "insertCSV" && $actsurrows['define_questions'])
	{
		if (get_magic_quotes_gpc() == "0")
		{
			$_POST['svettore'] = addcslashes($_POST['svettore'], "'");
		}
		$vettore = explode ("^", $svettore );
		$band = 0;
		$indice = $_POST['numcol'] - 1;
		foreach ($vettore as $k => $v)
		{
			$vettoreriga = explode ($elem, $v);
			if ($band == 1)
			{
				$valore = $vettoreriga[$indice];
				$valore = trim($valore);
				if (!is_null($valore))
				{
					$cdquery = "INSERT INTO {$dbprefix}answers (qid, code, answer, sortorder, default_value) VALUES ('{$_POST['qid']}', '$k', '$valore', '00000', 'N')";
					$cdresult = $cdresult = $connect->Execute($cdquery) or die(htmlspecialchars($connect->ErrorMsg()));
				}
			}
			$band = 1;
		}
	}

	elseif ($action == "updatesurvey" && $actsurrows['edit_survey_property'])
	{
		if ($_POST['url'] == "http://") {$_POST['url']="";}
		$_POST  = array_map('db_quote', $_POST);

		if (trim($_POST['expires'])=="")
		{
			$_POST['expires']='1980-01-01';
		}
		else
		{
			$_POST['expires']="'".$_POST['expires']."'";
		}


		$usquery = "UPDATE {$dbprefix}surveys \n"
		. "SET admin='{$_POST['admin']}', useexpiry='{$_POST['useexpiry']}',\n"
		. "expires={$_POST['expires']}, adminemail='{$_POST['adminemail']}',\n"
		. "private='{$_POST['private']}', faxto='{$_POST['faxto']}',\n"
		. "format='{$_POST['format']}', template='{$_POST['template']}',\n"
		. "url='{$_POST['url']}', \n"
		. "language='{$_POST['language']}', additional_languages='{$_POST['languageids']}',\n"
		. "datestamp='{$_POST['datestamp']}', ipaddr='{$_POST['ipaddr']}', refurl='{$_POST['refurl']}',\n"
		. "usecookie='{$_POST['usecookie']}', notification='{$_POST['notification']}',\n"
		. "allowregister='{$_POST['allowregister']}', attribute1='{$_POST['attribute1']}',\n"
		. "attribute2='{$_POST['attribute2']}', allowsave='{$_POST['allowsave']}',\n"
		. "autoredirect='{$_POST['autoredirect']}', allowprev='{$_POST['allowprev']}'\n"
		. "WHERE sid={$_POST['sid']}";
		$usresult = $connect->Execute($usquery) or die("Error updating<br />".htmlspecialchars($usquery)."<br /><br /><strong>".htmlspecialchars($connect->ErrorMsg()));
		$sqlstring ='';
		foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname)
		{
			if ($langname)
			{
				$sqlstring .= "and surveyls_language <> '".$langname."' ";
			}
		}
		// Add base language too
		$sqlstring .= "and surveyls_language <> '".GetBaseLanguageFromSurveyID($surveyid)."' ";

		$usquery = "Delete from ".db_table_name('surveys_languagesettings')." where surveyls_survey_id={$_POST['sid']} ".$sqlstring;
		$usresult = $connect->Execute($usquery) or die("Error deleting obsolete surveysettings<br />".htmlspecialchars($usquery)."<br /><br /><strong>".htmlspecialchars($connect->ErrorMsg()));

		foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname)
		{
			if ($langname)
			{
				$usquery = "select * from ".db_table_name('surveys_languagesettings')." where surveyls_survey_id={$_POST['sid']} and surveyls_language='".$langname."'";
				$usresult = $connect->Execute($usquery) or die("Error deleting obsolete surveysettings<br />".htmlspecialchars($usquery)."<br /><br /><strong>".htmlspecialchars($connect->ErrorMsg()));
				if ($usresult->RecordCount()==0)
				{
					$usquery = "insert into ".db_table_name('surveys_languagesettings')." SET surveyls_survey_id={$_POST['sid']}, surveyls_language='".$langname."'";
					$usresult = $connect->Execute($usquery) or die("Error deleting obsolete surveysettings<br />".htmlspecialchars($usquery)."<br /><br /><strong>".htmlspecialchars($connect->ErrorMsg()));
				}
			}
		}



		if ($usresult)
		{
			$surveyselect = getsurveylist();
		}
		else
		{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Survey could not be updated")."\n".$connect->ErrorMsg() ." ($usquery)\")\n //-->\n</script>\n";
		}
	}

	elseif ($action == "delsurvey" && $actsurrows['delete_survey']) //can only happen if there are no groups, no questions, no answers etc.
	{
		$query = "DELETE FROM {$dbprefix}surveys WHERE sid=$surveyid";
		$result = $connect->Execute($query);
		if ($result)
		{
			$surveyid = "";
			$surveyselect = getsurveylist();
		}
		else
		{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\"Survey id($surveyid) was NOT DELETED!\n$error\")\n //-->\n</script>\n";

		}
	}


	// Save the 2nd page from the survey-properties
	elseif ($action == "updatesurvey2" && $actsurrows['edit_survey_property'])
	{
		$languagelist = GetAdditionalLanguagesFromSurveyID($surveyid);
		$languagelist[]=GetBaseLanguageFromSurveyID($surveyid);
		foreach ($languagelist as $langname)
		{
			if ($langname)
			{
				$usquery = "UPDATE ".db_table_name('surveys_languagesettings')." \n"
				. "SET surveyls_title='".$_POST['short_title_'.$langname]."', surveyls_description='".$_POST['description_'.$langname]."',\n"
				. "surveyls_welcometext='".str_replace("\n", "<br />", $_POST['welcome_'.$langname])."',\n"
				. "surveyls_urldescription='".$_POST['urldescrip_'.$langname]."', surveyls_email_invite_subj='".$_POST['email_invite_subj_'.$langname]."',\n"
				. "surveyls_email_invite='".$_POST['email_invite_'.$langname]."', surveyls_email_remind_subj='".$_POST['email_remind_subj_'.$langname]."',\n"
				. "surveyls_email_remind='".$_POST['email_remind_'.$langname]."', surveyls_email_register_subj='".$_POST['email_register_subj_'.$langname]."',\n"
				. "surveyls_email_register='".$_POST['email_register_'.$langname]."', surveyls_email_confirm_subj='".$_POST['email_confirm_subj_'.$langname]."',\n"
				. "surveyls_email_confirm='".$_POST['email_confirm_'.$langname]."'\n"
				. "WHERE surveyls_survey_id=".$_POST['sid']." and surveyls_language='".$langname."'";
				$usresult = $connect->Execute($usquery) or die("Error updating<br />".htmlspecialchars($usquery)."<br /><br /><strong>".htmlspecialchars($connect->ErrorMsg()));
			}
		}
	}

}


elseif ($action == "insertnewsurvey" && $_SESSION['USER_RIGHT_CREATE_SURVEY'])
{
	if ($_POST['url'] == "http://") {$_POST['url']="";}
	if (!$_POST['short_title'])
	{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Survey could not be created because it did not have a short title")."\")\n //-->\n</script>\n";
	}
	else
	{
		$_POST  = array_map('db_quote', $_POST);
		if (trim($_POST['expires'])=="")
		{
			$_POST['expires']='1980-01-01';
		}
		else
		{
			$_POST['expires']="'".$_POST['expires']."'";
		}
		// Get random ids until one is found that is not used
		do
		{
			$surveyid = getRandomID();
			$isquery = "SELECT sid FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
			$isresult = db_execute_assoc($isquery);
		}
		while ($isresult->RecordCount()>0);

		$isquery = "INSERT INTO {$dbprefix}surveys\n"
		. "(sid, creator_id, short_title, description, admin, active, welcome, useexpiry, expires, "
		. "adminemail, private, faxto, format, template, url, urldescrip, "
		. "language, datestamp, ipaddr, refurl, usecookie, notification, allowregister, attribute1, attribute2, "
		. "email_invite_subj, email_invite, email_remind_subj, email_remind, "
		. "email_register_subj, email_register, email_confirm_subj, email_confirm, "
		. "allowsave, autoredirect, allowprev,datecreated)\n"
		. "VALUES ($surveyid, {$_SESSION['loginID']},'{$_POST['short_title']}', '{$_POST['description']}',\n"
		. "'{$_POST['admin']}', 'N', '".str_replace("\n", "<br />", $_POST['welcome'])."',\n"
		. "'{$_POST['useexpiry']}',{$_POST['expires']}, '{$_POST['adminemail']}', '{$_POST['private']}',\n"
		. "'{$_POST['faxto']}', '{$_POST['format']}', '{$_POST['template']}', '{$_POST['url']}',\n"
		. "'{$_POST['urldescrip']}', '{$_POST['language']}', '{$_POST['datestamp']}', '{$_POST['ipaddr']}', '{$_POST['refurl']}',\n"
		. "'{$_POST['usecookie']}', '{$_POST['notification']}', '{$_POST['allowregister']}',\n"
		. "'{$_POST['attribute1']}', '{$_POST['attribute2']}', '{$_POST['email_invite_subj']}',\n"
		. "'{$_POST['email_invite']}', '{$_POST['email_remind_subj']}',\n"
		. "'{$_POST['email_remind']}', '{$_POST['email_register_subj']}',\n"
		. "'{$_POST['email_register']}', '{$_POST['email_confirm_subj']}',\n"
		. "'{$_POST['email_confirm']}', \n"
		. "'{$_POST['allowsave']}', '{$_POST['autoredirect']}', '{$_POST['allowprev']}','".date("Y-m-d")."')";
		$isresult = $connect->Execute($isquery);

		// insert base language into surveys_language_settings
		$isquery = "INSERT INTO ".db_table_name('surveys_languagesettings')
		. "(surveyls_survey_id, surveyls_language, surveyls_title, surveyls_description, surveyls_welcometext, surveyls_urldescription, "
		. "surveyls_email_invite_subj, surveyls_email_invite, surveyls_email_remind_subj, surveyls_email_remind, "
		. "surveyls_email_register_subj, surveyls_email_register, surveyls_email_confirm_subj, surveyls_email_confirm)\n"
		. "VALUES ($surveyid, '{$_POST['language']}', '{$_POST['short_title']}', '{$_POST['description']}',\n"
		. "'".str_replace("\n", "<br />", $_POST['welcome'])."',\n"
		. "'{$_POST['urldescrip']}', '{$_POST['email_invite_subj']}',\n"
		. "'{$_POST['email_invite']}', '{$_POST['email_remind_subj']}',\n"
		. "'{$_POST['email_remind']}', '{$_POST['email_register_subj']}',\n"
		. "'{$_POST['email_register']}', '{$_POST['email_confirm_subj']}',\n"
		. "'{$_POST['email_confirm']}')";


		$isresult = $connect->Execute($isquery);

		// Insert into survey_rights
		$isrquery = "INSERT INTO {$dbprefix}surveys_rights VALUES($surveyid,". $_SESSION['loginID'].",1,1,1,1,1,1)"; //ADDED by Moses inserts survey rights for creator
		$isrresult = $connect->Execute($isrquery) or die ($isrquery."<br />".$connect->ErrorMsg()); //ADDED by Moses
		if ($isresult)
		{
			$surveyselect = getsurveylist();
		}
		else
		{
			$errormsg=_("Survey could not be created")." - ".$connect->ErrorMsg();
			echo "<script type=\"text/javascript\">\n<!--\n alert(\"$errormsg\")\n //-->\n</script>\n";
			echo htmlspecialchars($isquery);
		}
	}
}

else
{

	include("access_denied.php");
}


?>
