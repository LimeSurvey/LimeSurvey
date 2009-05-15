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
//Security Checked:
//ToDo: POST, GET, SESSION, DB, REQUEST, returnglobal

//Ensure script is not run directly, avoid path disclosure
include_once("login_check.php");

if (!isset($action)) {$action=returnglobal('action');}
$postsid=returnglobal('sid');
$postgid=returnglobal('gid');
$postqid=returnglobal('qid');
$postqaid=returnglobal('qaid');    

if (get_magic_quotes_gpc())
    {$_POST  = array_map('recursive_stripslashes', $_POST);}

/*
 * Return a sql statement for renaming a table
 */
function db_rename_table($oldtable, $newtable)
{
	global $connect;

    $dict = NewDataDictionary($connect);
    $result=$dict->RenameTableSQL($oldtable, $newtable);
    return $result[0];
}


/*
* Gets the maximum question_order field value for a group
* @gid: The id of the group
*/
function get_max_question_order($gid)
{
	global $connect ;
	global $dbprefix ;
	$query="SELECT MAX(question_order) as maxorder FROM {$dbprefix}questions where gid=".$gid ;
	// echo $query;
	$result = db_execute_assoc($query);
	$gv = $result->FetchRow();
	return $gv['maxorder'];
}

$databaseoutput ='';

if(isset($surveyid))
{
	$actsurquery = "SELECT define_questions, edit_survey_property, delete_survey FROM {$dbprefix}surveys_rights WHERE sid=$surveyid AND uid = ".$_SESSION['loginID']; //Getting rights for this survey
	$actsurresult = db_execute_assoc($actsurquery);
	$actsurrows = $actsurresult->FetchRow();

	if ($action == "delattribute" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
	{
		$query = "DELETE FROM ".db_table_name('question_attributes')."
				  WHERE qaid={$postqaid} AND qid={$postqid}";
		$result=$connect->Execute($query) or safe_die("Couldn't delete attribute<br />".$query."<br />".$connect->ErrorMsg());
	}
	elseif ($action == "addattribute" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
	{
		if (isset($_POST['attribute_value']) && (!empty($_POST['attribute_value']) || $_POST['attribute_value'] == "0"))
		{
            if ($_POST['attribute_name']=='dropdown_separators' || $_POST['attribute_name']=='dualscale_headerA' || $_POST['attribute_name']=='dualscale_headerB' ||
                $_POST['attribute_name']=='dropdown_prepostfix' || $_POST['attribute_name']=='prefix' || $_POST['attribute_name']=='suffix')
            {
               if ($filterxsshtml)
               {
                   require_once("../classes/inputfilter/class.inputfilter_clean.php");
                   $myFilter = new InputFilter('','',1,1,1); 
                   $_POST['attribute_value']=$myFilter->process($_POST['attribute_value']);
                } 
                   else
                          {
                            $_POST['attribute_value'] = html_entity_decode_php4($_POST['attribute_value'], ENT_QUOTES, "UTF-8");
                          }
            }
                
			$_POST  = array_map('db_quote', $_POST);
			$query = "INSERT INTO ".db_table_name('question_attributes')."
					  (qid, attribute, value)
					  VALUES ('{$postqid}', '{$_POST['attribute_name']}', '{$_POST['attribute_value']}')";
			$result = $connect->Execute($query) or safe_die("Error<br />".$query."<br />".$connect->ErrorMsg());
		} 
	}
	elseif ($action == "editattribute" && ( $_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
	{
		if (isset($_POST['attribute_value']) && (!empty($_POST['attribute_value']) || $_POST['attribute_value'] == "0"))
		{
			$query = "UPDATE ".db_table_name('question_attributes')."
					  SET value='{$_POST['attribute_value']}' WHERE qaid=".$postqaid." AND qid=".returnglobal('qid');
			$result = $connect->Execute($query) or safe_die("Error<br />".$query."<br />".$connect->ErrorMsg());
		}
	}
	elseif ($action == "insertnewgroup" && ( $_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
	{
  		$grplangs = GetAdditionalLanguagesFromSurveyID($postsid);
  		$baselang = GetBaseLanguageFromSurveyID($postsid);
  		$grplangs[] = $baselang;
        $errorstring = '';  
        foreach ($grplangs as $grouplang)
        {
          if (!$_POST['group_name_'.$grouplang]) { $errorstring.= GetLanguageNameFromCode($grouplang,false)."\\n";}
		}
        if ($errorstring!='') 
        {
            $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Group could not be added.\\n\\nIt is missing the group name for the following languages","js").":\\n".$errorstring."\")\n //-->\n</script>\n";
        }  

		else
		{
        $first=true;
	   		require_once("../classes/inputfilter/class.inputfilter_clean.php");
		    $myFilter = new InputFilter('','',1,1,1); 
         
    		foreach ($grplangs as $grouplang)
	       	{
		     	//Clean XSS
		     	if ($filterxsshtml)
		     	{
  		 		$_POST['group_name_'.$grouplang]=$myFilter->process($_POST['group_name_'.$grouplang]);
		     	$_POST['description_'.$grouplang]=$myFilter->process($_POST['description_'.$grouplang]);
		     	}
                   else
                          {
                            $_POST['group_name_'.$grouplang] = html_entity_decode_php4($_POST['group_name_'.$grouplang], ENT_QUOTES, "UTF-8");
                            $_POST['description_'.$grouplang] = html_entity_decode_php4($_POST['description_'.$grouplang], ENT_QUOTES, "UTF-8");
                          }
                
                // Fix bug with FCKEditor saving strange BR types
                $_POST['group_name_'.$grouplang]=fix_FCKeditor_text($_POST['group_name_'.$grouplang]);
                $_POST['description_'.$grouplang]=fix_FCKeditor_text($_POST['description_'.$grouplang]);

    			//$_POST  = array_map('db_quote', $_POST);
               
			  	if ($first)
                  {
      			    $query = "INSERT INTO ".db_table_name('groups')." (sid, group_name, description,group_order,language) VALUES ('".db_quote($postsid)."', '".db_quote($_POST['group_name_'.$grouplang])."', '".db_quote($_POST['description_'.$grouplang])."',".getMaxgrouporder(returnglobal('sid')).",'{$grouplang}')";
                    $result = $connect->Execute($query);
                    $groupid=$connect->Insert_Id(db_table_name_nq('groups'),"gid");
                    $first=false;
                  }
                  else{
                        $query = "INSERT INTO ".db_table_name('groups')." (gid, sid, group_name, description,group_order,language) VALUES ('{$groupid}','".db_quote($postsid)."', '".db_quote($_POST['group_name_'.$grouplang])."', '".db_quote($_POST['description_'.$grouplang])."',".getMaxgrouporder(returnglobal('sid')).",'{$grouplang}')";
                        if ($connect->databaseType == 'odbc_mssql' || $connect->databaseType == 'odbtp' || $connect->databaseType == 'mssql_n') $query = 'SET IDENTITY_INSERT '.db_table_name('groups')." ON; " . $query . 'SET IDENTITY_INSERT '.db_table_name('groups')." OFF;";
                        $result = $connect->Execute($query) or safe_die("Error<br />".$query."<br />".$connect->ErrorMsg());
                     }
				if (!$result)
				{
					$databaseoutput .= $clang->gT("Error: The database reported the following error:")."<br />\n";
					$databaseoutput .= "<font color='red'>" . htmlspecialchars($connect->ErrorMsg()) . "</font>\n";
					$databaseoutput .= "<pre>".htmlspecialchars($query)."</pre>\n";
					$databaseoutput .= "</body>\n</html>";
					exit;
				}
			}
		    // This line sets the newly inserted group as the new group
            if (isset($groupid)){$gid=$groupid;}     

		}
	}

	elseif ($action == "updategroup" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
	{
		$grplangs = GetAdditionalLanguagesFromSurveyID($postsid);
		$baselang = GetBaseLanguageFromSurveyID($postsid);
		array_push($grplangs,$baselang);
		require_once("../classes/inputfilter/class.inputfilter_clean.php");
	    $myFilter = new InputFilter('','',1,1,1);
	    foreach ($grplangs as $grplang)
	    {
		    if (isset($grplang) && $grplang != "")
		    {
			    if ($filterxsshtml)
			    {
				    $_POST['group_name_'.$grplang]=$myFilter->process($_POST['group_name_'.$grplang]);
				    $_POST['description_'.$grplang]=$myFilter->process($_POST['description_'.$grplang]);
			    }
			    else
			    {
				    $_POST['group_name_'.$grplang] = html_entity_decode_php4($_POST['group_name_'.$grplang], ENT_QUOTES, "UTF-8");
				    $_POST['description_'.$grplang] = html_entity_decode_php4($_POST['description_'.$grplang], ENT_QUOTES, "UTF-8");
			    }

			    // Fix bug with FCKEditor saving strange BR types
			    $_POST['group_name_'.$grplang]=fix_FCKeditor_text($_POST['group_name_'.$grplang]);
			    $_POST['description_'.$grplang]=fix_FCKeditor_text($_POST['description_'.$grplang]);

			    // don't use array_map db_quote on POST
			    // since this is iterated for each language
			    //$_POST  = array_map('db_quote', $_POST);
			    $ugquery = "UPDATE ".db_table_name('groups')." SET group_name='".db_quote($_POST['group_name_'.$grplang])."', description='".db_quote($_POST['description_'.$grplang])."' WHERE sid=".db_quote($postsid)." AND gid=".db_quote($postgid)." AND language='{$grplang}'";
			    $ugresult = $connect->Execute($ugquery);
			    if ($ugresult)
			    {
				    $groupsummary = getgrouplist($postgid);
			    }
			    else
			    {
				    $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Group could not be updated","js")."\")\n //-->\n</script>\n";
			    }
		    }
	    }

	}

	elseif ($action == "delgroupnone" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
	{
		if (!isset($gid)) $gid=returnglobal('gid');

        $query = "DELETE FROM ".db_table_name('assessments')." WHERE sid=$surveyid AND gid=$gid";
        $result = $connect->Execute($query) or safe_die($connect->ErrorMsg()) ;
        
		$query = "DELETE FROM ".db_table_name('groups')." WHERE sid=$surveyid AND gid=$gid";
		$result = $connect->Execute($query) or safe_die($connect->ErrorMsg()) ;
		
		if ($result)
		{
			$gid = "";
			$groupselect = getgrouplist($gid);
			fixsortorderGroups();
		}
		else
		{
			$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Group could not be deleted","js")."\n$error\")\n //-->\n</script>\n";
		}
	}

	elseif ($action == "delgroup" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
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
                $dquery = "DELETE FROM ".db_table_name('question_attributes')." WHERE qid={$row['qid']}";
                if ($dresult=$connect->Execute($dquery)) {$total++;}
				$dquery = "DELETE FROM ".db_table_name('questions')." WHERE qid={$row['qid']}";
				if ($dresult=$connect->Execute($dquery)) {$total++;}
			}
			if ($total != $qtodel*4)
			{
				$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Group could not be deleted","js")."\")\n //-->\n</script>\n";
			}
		}
        $query = "DELETE FROM ".db_table_name('assessments')." WHERE sid=$surveyid AND gid=$gid";
        $result = $connect->Execute($query) or safe_die($connect->ErrorMsg()) ;
        
		$query = "DELETE FROM ".db_table_name('groups')." WHERE sid=$surveyid AND gid=$gid";
		$result = $connect->Execute($query) or safe_die($connect->ErrorMsg()) ;
		if ($result)
		{
			$gid = "";
			$groupselect = getgrouplist($gid);
			fixsortorderGroups();
		}
		else
		{
			$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Group could not be deleted","js")."\n$error\")\n //-->\n</script>\n";
		}
	}

	elseif ($action == "insertnewquestion" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
	{
        $baselang = GetBaseLanguageFromSurveyID($postsid);    
		if (strlen($_POST['title']) < 1)
		{
			$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n "
                              ."alert(\"".$clang->gT("The question could not be added. You must enter at least enter a question code.","js")."\")\n "
                              ."//-->\n</script>\n";
		}
		else
		{
			if (!isset($_POST['lid']) || $_POST['lid'] == '') {$_POST['lid']="0";}
			if (!isset($_POST['lid1']) || $_POST['lid1'] == '') {$_POST['lid1']="0";}
			if(!empty($_POST['questionposition']) || $_POST['questionposition'] == '0')
			{
			   $question_order=(sanitize_int($_POST['questionposition'])+1);
			    //Need to renumber all questions on or after this
	           $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=question_order+1 WHERE gid=".$postgid." AND question_order >= ".$question_order;
    	       $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
			} else {
			    $question_order=(getMaxquestionorder($postgid));
			    $question_order++;
			}

	     	if ($filterxsshtml)
	     	{
	   			require_once("../classes/inputfilter/class.inputfilter_clean.php");
			    $myFilter = new InputFilter('','',1,1,1); 
				$_POST['title']=$myFilter->process($_POST['title']);
				$_POST['question_'.$baselang]=$myFilter->process($_POST['question_'.$baselang]);
				$_POST['help_'.$baselang]=$myFilter->process($_POST['help_'.$baselang]);
			}	
                   else
                          {
                            $_POST['title'] = html_entity_decode_php4($_POST['title'], ENT_QUOTES, "UTF-8");
                            $_POST['question_'.$baselang] = html_entity_decode_php4($_POST['question_'.$baselang], ENT_QUOTES, "UTF-8");
                            $_POST['help_'.$baselang] = html_entity_decode_php4($_POST['help_'.$baselang], ENT_QUOTES, "UTF-8");
                          }
            
            // Fix bug with FCKEditor saving strange BR types
            $_POST['title']=fix_FCKeditor_text($_POST['title']);
            $_POST['question_'.$baselang]=fix_FCKeditor_text($_POST['question_'.$baselang]);
            $_POST['help_'.$baselang]=fix_FCKeditor_text($_POST['help_'.$baselang]);
            
			$_POST  = array_map('db_quote', $_POST);
			$query = "INSERT INTO ".db_table_name('questions')." (sid, gid, type, title, question, preg, help, other, mandatory, lid,  lid1, question_order, language)"
			." VALUES ('{$postsid}', '{$postgid}', '{$_POST['type']}', '{$_POST['title']}',"
			." '{$_POST['question_'.$baselang]}', '{$_POST['preg']}', '{$_POST['help_'.$baselang]}', '{$_POST['other']}', '{$_POST['mandatory']}', '{$_POST['lid']}', '{$_POST['lid1']}',$question_order,'{$baselang}')";
			$result = $connect->Execute($query);
			// Get the last inserted questionid for other languages
			$qid=$connect->Insert_ID(db_table_name_nq('questions'),"qid");

			// Add other languages
			if ($result)
			{
				$addlangs = GetAdditionalLanguagesFromSurveyID($postsid);
				foreach ($addlangs as $alang)
				{
					if ($alang != "")
					{	
						$query = "INSERT INTO ".db_table_name('questions')." (qid, sid, gid, type, title, question, preg, help, other, mandatory, lid, lid1, question_order, language)"
						." VALUES ('$qid','{$postsid}', '{$postgid}', '{$_POST['type']}', '{$_POST['title']}',"
						." '{$_POST['question_'.$alang]}', '{$_POST['preg']}', '{$_POST['help_'.$alang]}', '{$_POST['other']}', '{$_POST['mandatory']}', '{$_POST['lid']}', '{$_POST['lid1']}',$question_order,'{$alang}')";
                        if ($connect->databaseType == 'odbc_mssql' || $connect->databaseType == 'odbtp' || $connect->databaseType == 'mssql_n') $query = "SET IDENTITY_INSERT ".db_table_name('questions')." ON; " . $query . "SET IDENTITY_INSERT ".db_table_name('questions')." OFF;";
						$result2 = $connect->Execute($query);
						if (!$result2)
						{
							$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question in lang={$alang} could not be created.","js")."\\n".$connect->ErrorMsg()."\")\n //-->\n</script>\n";

						}
					}
				}
			}
			
			
			if (!$result)
			{
				$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be created.","js")."\\n".$connect->ErrorMsg()."\")\n //-->\n</script>\n";

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
	elseif ($action == "renumberquestions" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
	{
		//Automatically renumbers the "question codes" so that they follow
		//a methodical numbering method
		$question_number=1;
		$group_number=0;
		$gselect="SELECT a.qid, a.gid\n"
		."FROM ".db_table_name('questions')." as a, ".db_table_name('groups')."\n"
		."WHERE a.gid=".db_table_name('groups').".gid\n"
		."AND a.sid=$surveyid\n"
        ."GROUP BY a.gid, a.qid, ".db_table_name('groups').".group_order, question_order\n"
		."ORDER BY ".db_table_name('groups').".group_order, question_order";
		$gresult=db_execute_assoc($gselect) or safe_die ("Error: ".$connect->ErrorMsg());
		$grows = array(); //Create an empty array in case FetchRow does not return any rows
		while ($grow = $gresult->FetchRow()) {$grows[] = $grow;} // Get table output into array
		foreach($grows as $grow)
		{
			//Go through all the questions
			if ((isset($_POST['style']) && $_POST['style']=="bygroup") && (!isset($group_number) || $group_number != $grow['gid']))
			{ //If we're doing this by group, restart the numbering when the group number changes
				$question_number=1;
				$group_number++;
			}
			$usql="UPDATE ".db_table_name('questions')."\n"
			."SET title='".str_pad($question_number, 4, "0", STR_PAD_LEFT)."'\n"
			."WHERE qid=".$grow['qid'];
			//$databaseoutput .= "[$sql]";
			$uresult=$connect->Execute($usql) or safe_die("Error: ".$connect->ErrorMsg());
			$question_number++;
			$group_number=$grow['gid'];
		}
	}

	elseif ($action == "updatequestion" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
	{
		$cqquery = "SELECT type, gid FROM ".db_table_name('questions')." WHERE qid={$postqid}";
		$cqresult=db_execute_assoc($cqquery) or safe_die ("Couldn't get question type to check for change<br />".$cqquery."<br />".$connect->ErrorMsg());
		$cqr=$cqresult->FetchRow();
        	$oldtype=$cqr['type'];
		$oldgid=$cqr['gid'];

        // Remove invalid question attributes on saving
        $qattributes=questionAttributes();
        $attsql="delete from ".db_table_name('question_attributes')." where qid='{$postqid}' and ";
        if (isset($qattributes[$_POST['type']])){   
		   $validAttributes=$qattributes[$_POST['type']];
	       foreach ($validAttributes as  $validAttribute)
	       {
	         $attsql.='attribute<>'.db_quoteall($validAttribute['name'])." and ";
	       }
        }
        $attsql.='1=1';
        db_execute_assoc($attsql) or safe_die ("Couldn't delete obsolete question attributes<br />".$attsql."<br />".$connect->ErrorMsg());
        
   		$keepanswers = "1"; // Generally we try to keep answers if the question type has changed
		
		// These are the questions types that have no answers and therefore we delete the answer in that case
		if (($_POST['type']== "5") || ($_POST['type']== "D") || ($_POST['type']== "G") ||
            ($_POST['type']== "I") || ($_POST['type']== "N") || ($_POST['type']== "S") ||        
            ($_POST['type']== "T") || ($_POST['type']== "U") || ($_POST['type']== "X") ||        
            ($_POST['type']=="Y"))
		{
			$keepanswers = "0";
		}

		// These are the questions types that use labelsets and 
		// therefore we set the label set of all other question types to 0
		if (($_POST['type']!= "F") && ($_POST['type']!= "H") && ($_POST['type']!= "W") &&
		    ($_POST['type']!= "Z") && ($_POST['type']!= "1") && ($_POST['type']!= ":") &&
			($_POST['type']!= ";"))
		{
			$_POST['lid']=0;
		}

		// These are the questions types that have the other option therefore we set everything else to 'No Other'
		if (($_POST['type']!= "L") && ($_POST['type']!= "!") && ($_POST['type']!= "P") && ($_POST['type']!="M") && ($_POST['type'] != "W") && ($_POST['type'] != "Z"))
		{
			$_POST['other']='N';
		}

        // These are the questions types that have no validation - so zap it accordingly
        
        if ($_POST['type']== "!" || $_POST['type']== "L" || $_POST['type']== "M" || $_POST['type']== "P" || $_POST['type']== "W" || 
            $_POST['type']== "Z" || $_POST['type']== "F" || $_POST['type']== "H" || $_POST['type']== ":" || $_POST['type']== ";" || 
            $_POST['type']== "K" || $_POST['type']== "X" || $_POST['type']== "")
        {
            $_POST['preg']='';
        }
        
        
        
		if ($oldtype != $_POST['type'])
		{
			//Make sure there are no conditions based on this question, since we are changing the type
			$ccquery = "SELECT * FROM ".db_table_name('conditions')." WHERE cqid={$postqid}";
			$ccresult = db_execute_assoc($ccquery) or safe_die ("Couldn't get list of cqids for this question<br />".$ccquery."<br />".$connect->ErrorMsg());
			$cccount=$ccresult->RecordCount();
			while ($ccr=$ccresult->FetchRow()) {$qidarray[]=$ccr['qid'];}
			if (isset($qidarray) && $qidarray) {$qidlist=implode(", ", $qidarray);}
		}
		if (isset($cccount) && $cccount)
		{
			$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be updated. There are conditions for other questions that rely on the answers to this question and changing the type will cause problems. You must delete these conditions before you can change the type of this question.","js")." ($qidlist)\")\n //-->\n</script>\n";
		}
		else
		{
			if (isset($postgid) && $postgid != "")
			{
				
				$array_result=checkMovequestionConstraintsForConditions(sanitize_int($postsid),sanitize_int($postqid), sanitize_int($postgid));
				// If there is no blocking conditions that could prevent this move
				if (is_null($array_result['notAbove']) && is_null($array_result['notBelow']))
				{

					$questlangs = GetAdditionalLanguagesFromSurveyID($postsid);
					$baselang = GetBaseLanguageFromSurveyID($postsid);
					array_push($questlangs,$baselang);
			     	if ($filterxsshtml)
	    		 	{
						require_once("../classes/inputfilter/class.inputfilter_clean.php");
				    	$myFilter = new InputFilter('','',1,1,1); 
						$_POST['title']=$myFilter->process($_POST['title']);
					}
                          else
                          {
                            $_POST['title'] = html_entity_decode_php4($_POST['title'], ENT_QUOTES, "UTF-8");
                          }
                     
                    // Fix bug with FCKEditor saving strange BR types
                    $_POST['title']=fix_FCKeditor_text($_POST['title']);
                    
					foreach ($questlangs as $qlang)
					{
				     	if ($filterxsshtml)
	     				{
							$_POST['question_'.$qlang]=$myFilter->process($_POST['question_'.$qlang]);
							$_POST['help_'.$qlang]=$myFilter->process($_POST['help_'.$qlang]);
						}
                          else
                          {
                            $_POST['question_'.$qlang] = html_entity_decode_php4($_POST['question_'.$qlang], ENT_QUOTES, "UTF-8");
                            $_POST['help_'.$qlang] = html_entity_decode_php4($_POST['help_'.$qlang], ENT_QUOTES, "UTF-8");
                          }
                        // Fix bug with FCKEditor saving strange BR types
                        $_POST['question_'.$qlang]=fix_FCKeditor_text($_POST['question_'.$qlang]);
                        $_POST['help_'.$qlang]=fix_FCKeditor_text($_POST['help_'.$qlang]);
                       
        		//$_POST  = array_map('db_quote', $_POST);

						if (isset($qlang) && $qlang != "")
						{ // ToDo: Sanitize the POST variables !
							$uqquery = "UPDATE ".db_table_name('questions')
							. "SET type='".db_quote($_POST['type'])."', title='".db_quote($_POST['title'])."', "
							. "question='".db_quote($_POST['question_'.$qlang])."', preg='".db_quote($_POST['preg'])."', help='".db_quote($_POST['help_'.$qlang])."', "
							. "gid='".db_quote($postgid)."', other='".db_quote($_POST['other'])."', "
							. "mandatory='".db_quote($_POST['mandatory'])."'";
	        				if ($oldgid!=$postgid)
						{
							if ( getGroupOrder(returnglobal('sid'),$oldgid) > getGroupOrder(returnglobal('sid'),returnglobal('gid')) )
							{
								// Moving question to a 'upper' group
								// insert question at the end of the destination group
								// this prevent breaking conditions if the target qid is in the dest group
								$insertorder = getMaxquestionorder($postgid) + 1;
								$uqquery .=', question_order='.$insertorder.' '; 
							}
							else
							{
								// Moving question to a 'lower' group
								// insert question at the beginning of the destination group
								shiftorderQuestions($postsid,$postgid,1); // makes 1 spare room for new question at top of dest group
								$uqquery .=', question_order=0 ';
							}
						}
							if (isset($_POST['lid']) && trim($_POST['lid'])!="")
							{
								$uqquery.=", lid='".db_quote($_POST['lid'])."' ";
							}
							if (isset($_POST['lid1']) && trim($_POST['lid1'])!="")
							{
								$uqquery.=", lid1='".db_quote($_POST['lid1'])."' ";
							}

							$uqquery.= "WHERE sid='".db_quote($postsid)."' AND qid='".db_quote($postqid)."' AND language='{$qlang}'";
							$uqresult = $connect->Execute($uqquery) or safe_die ("Error Update Question: ".$uqquery."<br />".$connect->ErrorMsg());
							if (!$uqresult)
							{
								$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be updated","js")."\n".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
							}
						}
					}
					// if the group has changed then fix the sortorder of old and new group
					if ($oldgid!=$postgid) 
	                		{
	                    			fixsortorderQuestions(0,$oldgid);
	                    			fixsortorderQuestions(0,$postgid);

						// If some questions have conditions set on this question's answers
						// then change the cfieldname accordingly
						fixmovedquestionConditions($postqid, $oldgid, $postgid);
	                		}
					if ($keepanswers == "0")
					{
						$query = "DELETE FROM ".db_table_name('answers')." WHERE qid=".db_quote($postqid);
						$result = $connect->Execute($query) or safe_die("Error: ".$connect->ErrorMsg());
						if (!$result)
						{
							$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Answers can't be deleted","js")."\n".htmlspecialchars($connect->ErrorMsg())."\")\n //-->\n</script>\n";
						}
					}
				}
				else
				{
					// There are conditions constraints: alert the user
					$errormsg="";
					if (!is_null($array_result['notAbove']))
					{
						$errormsg.=$clang->gT("This question relies on other question's answers and can't be moved above groupId:","js")
							. " " . $array_result['notAbove'][0][0] . " " . $clang->gT("in position","js")." ".$array_result['notAbove'][0][1]."\\n"
							. $clang->gT("See conditions:")."\\n";
						
						foreach ($array_result['notAbove'] as $notAboveCond)
						{
							$errormsg.="- cid:". $notAboveCond[3]."\\n";	
						}
						
					}
					if (!is_null($array_result['notBelow']))
					{
						$errormsg.=$clang->gT("Some questions rely on this question's answers. You can't move this question below groupId:","js")
							. " " . $array_result['notBelow'][0][0] . " " . $clang->gT("in position","js")." ".$array_result['notBelow'][0][1]."\\n"
							. $clang->gT("See conditions:")."\\n";
						
						foreach ($array_result['notBelow'] as $notBelowCond)
						{
							$errormsg.="- cid:". $notBelowCond[3]."\\n";	
						}
					}

					$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"$errormsg\")\n //-->\n</script>\n";
					$gid= $oldgid; // group move impossible ==> keep display on oldgid
				}
			}
			else
			{
				$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be updated","js")."\")\n //-->\n</script>\n";
			}
		}
	}

	elseif ($action == "copynewquestion" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
	{

		if (!$_POST['title'])
		{
			$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be added. You must insert a code in the mandatory field","js")."\")\n //-->\n</script>\n";
		}
		else
		{
    		$questlangs = GetAdditionalLanguagesFromSurveyID($postsid);
    		$baselang = GetBaseLanguageFromSurveyID($postsid);
    		
			if (!isset($_POST['lid']) || $_POST['lid']=='') {$_POST['lid']=0;}
			if (!isset($_POST['lid1']) || $_POST['lid1']=='') {$_POST['lid1']=0;}			
			//Get maximum order from the question group
			$max=get_max_question_order($postgid)+1 ;
            // Insert the base language of the question
	     	if ($filterxsshtml)
	     	{
		   		require_once("../classes/inputfilter/class.inputfilter_clean.php");
			    $myFilter = new InputFilter('','',1,1,1); 
				// Prevent XSS attacks
				$_POST['title']=$myFilter->process($_POST['title']);
				$_POST['question_'.$baselang]=$myFilter->process($_POST['question_'.$baselang]);
				$_POST['help_'.$baselang]=$myFilter->process($_POST['help_'.$baselang]);
			}
                   else
                          {
                            $_POST['title'] = html_entity_decode_php4($_POST['title'], ENT_QUOTES, "UTF-8");
                            $_POST['question_'.$baselang] = html_entity_decode_php4($_POST['question_'.$baselang], ENT_QUOTES, "UTF-8");
                            $_POST['help_'.$baselang] = html_entity_decode_php4($_POST['help_'.$baselang], ENT_QUOTES, "UTF-8");
                          }
            
            
            // Fix bug with FCKEditor saving strange BR types
            $_POST['title']=fix_FCKeditor_text($_POST['title']);
            $_POST['question_'.$baselang]=fix_FCKeditor_text($_POST['question_'.$baselang]);
            $_POST['help_'.$baselang]=fix_FCKeditor_text($_POST['help_'.$baselang]);
			$_POST  = array_map('db_quote', $_POST);
			$query = "INSERT INTO {$dbprefix}questions (sid, gid, type, title, question, preg, help, other, mandatory, lid, lid1, question_order, language) 
                      VALUES ({$postsid}, {$postgid}, '{$_POST['type']}', '{$_POST['title']}', '".$_POST['question_'.$baselang]."', '{$_POST['preg']}', '".$_POST['help_'.$baselang]."', '{$_POST['other']}', '{$_POST['mandatory']}', '{$_POST['lid']}', '{$_POST['lid1']}',$max,".db_quoteall($baselang).")";
			$result = $connect->Execute($query) or safe_die($connect->ErrorMsg());
			$newqid = $connect->Insert_ID("{$dbprefix}questions","qid");
			if (!$result)
			{
				$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be created.","js")."\\n".htmlspecialchars($connect->ErrorMsg())."\")\n //-->\n</script>\n";

			}
            
            foreach ($questlangs as $qlanguage)
            { 
		     	if ($filterxsshtml)
		     	{
					$_POST['question_'.$qlanguage]=$myFilter->process($_POST['question_'.$qlanguage]);
					$_POST['help_'.$qlanguage]=$myFilter->process($_POST['help_'.$qlanguage]);
				}
                   else
                          {
                            $_POST['question_'.$qlanguage] = html_entity_decode_php4($_POST['question_'.$qlanguage], ENT_QUOTES, "UTF-8");
                            $_POST['help_'.$qlanguage] = html_entity_decode_php4($_POST['help_'.$qlanguage], ENT_QUOTES, "UTF-8");
                          }
                
            // Fix bug with FCKEditor saving strange BR types
            $_POST['question_'.$qlanguage]=fix_FCKeditor_text($_POST['question_'.$qlanguage]);
            $_POST['help_'.$qlanguage]=fix_FCKeditor_text($_POST['help_'.$qlanguage]);
                
            if ($connect->databaseType == 'odbc_mssql' || $connect->databaseType == 'odbtp' || $connect->databaseType == 'mssql_n') {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('questions')." ON");}
			$query = "INSERT INTO {$dbprefix}questions (qid, sid, gid, type, title, question, help, other, mandatory, lid, lid1, question_order, language) 
                      VALUES ($newqid,{$postsid}, {$postgid}, '{$_POST['type']}', '{$_POST['title']}', '".$_POST['question_'.$qlanguage]."', '".$_POST['help_'.$qlanguage]."', '{$_POST['other']}', '{$_POST['mandatory']}', '{$_POST['lid']}', '{$_POST['lid1']}', $max,".db_quoteall($qlanguage).")";
			$result = $connect->Execute($query) or safe_die($connect->ErrorMsg());
            if ($connect->databaseType == 'odbc_mssql' || $connect->databaseType == 'odbtp' || $connect->databaseType == 'mssql_n') {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('questions')." OFF");}
			}
			if (!$result)
			{
				$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be created.","js")."\\n".htmlspecialchars($connect->ErrorMsg())."\")\n //-->\n</script>\n";

			}
			if (returnglobal('copyanswers') == "Y")
			{
				$q1 = "SELECT * FROM {$dbprefix}answers WHERE qid="
				. returnglobal('oldqid')
				. " ORDER BY code";
				$r1 = db_execute_assoc($q1);
				while ($qr1 = $r1->FetchRow())
				{
		     		if ($filterxsshtml)
			     	{
				   		require_once("../classes/inputfilter/class.inputfilter_clean.php");
					    $myFilter = new InputFilter('','',1,1,1); 
					    $qr1['answer']=$myFilter->process($qr1['answer']);
					}			
                    else
                          {
                            $qr1['answer'] = html_entity_decode_php4($qr1['answer'], ENT_QUOTES, "UTF-8");
                          }

                    
                    // Fix bug with FCKEditor saving strange BR types
                    $qr1['answer']=fix_FCKeditor_text($qr1['answer']);
             	    
					$qr1 = array_map('db_quote', $qr1);
					$i1 = "INSERT INTO {$dbprefix}answers (qid, code, answer, default_value, sortorder, language) "
					. "VALUES ('$newqid', '{$qr1['code']}', "
					. "'{$qr1['answer']}', '{$qr1['default_value']}', "
					. "'{$qr1['sortorder']}', '{$qr1['language']}')";
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
	elseif ($action == "delquestion" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
	{
		if (!isset($qid)) {$qid=returnglobal('qid');}
		//check if any other questions have conditions which rely on this question. Don't delete if there are.
		$ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid=$qid";
		$ccresult = db_execute_assoc($ccquery) or safe_die ("Couldn't get list of cqids for this question<br />".$ccquery."<br />".$connect->ErrorMsg());
		$cccount=$ccresult->RecordCount();
		while ($ccr=$ccresult->FetchRow()) {$qidarray[]=$ccr['qid'];}
		if (isset($qidarray)) {$qidlist=implode(", ", $qidarray);}
		if ($cccount) //there are conditions dependant on this question
		{
			$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be deleted. There are conditions for other questions that rely on this question. You cannot delete this question until those conditions are removed","js")." ($qidlist)\")\n //-->\n</script>\n";
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
				$postqid="";
				$_GET['qid']="";
			}
			else
			{
				$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be deleted","js")."\n$error\")\n //-->\n</script>\n";
			}
		}
	}

	elseif ($action == "delquestionall" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
	{
		if (!isset($qid)) {$qid=returnglobal('qid');}
		//check if any other questions have conditions which rely on this question. Don't delete if there are.
		$ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid={$_GET['qid']}";
		$ccresult = db_execute_assoc($ccquery) or safe_die ("Couldn't get list of cqids for this question<br />".$ccquery."<br />".$connect->ErrorMsg());
		$cccount=$ccresult->RecordCount();
		while ($ccr=$ccresult->FetchRow()) {$qidarray[]=$ccr['qid'];}
		if (isset($qidarray) && $qidarray) {$qidlist=implode(", ", $qidarray);}
		if ($cccount) //there are conditions dependant on this question
		{
			$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be deleted. There are conditions for other questions that rely on this question. You cannot delete this question until those conditions are removed","js")." ($qidlist)\")\n //-->\n</script>\n";
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
			$postqid="";
			$_GET['qid']="";
		}
		else
		{
			$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be deleted","js")."\n$error\")\n //-->\n</script>\n";
		}
	}

	elseif ($action == "modanswer" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
	{

        if (isset($_POST['sortorder'])) 
        {
            $postsortorder=sanitize_int($_POST['sortorder']);
        }
        
        switch($_POST['method'])
		{
			// Add a new answer button
			case $clang->gT("Add new Answer", "unescaped"):
			if (isset($_POST['insertcode']) && $_POST['insertcode']!='' && $_POST['insertcode'] != "0")
			{
				//$_POST  = array_map('db_quote', $_POST);//Removed: qstr is used in SQL below
				$_POST['insertcode']=sanitize_paranoid_string($_POST['insertcode']);
				$query = "select max(sortorder) as maxorder from ".db_table_name('answers')." where qid='$qid'";
				$result = $connect->Execute($query);
				$newsortorder=sprintf("%05d", $result->fields['maxorder']+1);
				$anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
				$baselang = GetBaseLanguageFromSurveyID($surveyid);
				$query = "select * from ".db_table_name('answers')." where code=".$connect->qstr($_POST['insertcode'])." and language='$baselang' and qid={$postqid}";
                $result = $connect->Execute($query);
								
                if (isset($result) && $result->RecordCount()>0)
				{
					$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Error adding answer: You can't use the same answer code more than once.","js")."\")\n //-->\n</script>\n";
				}
				 else
				 {
        
	        
		    		    if ($filterxsshtml)
	    	 		    {
				   		    require_once("../classes/inputfilter/class.inputfilter_clean.php");
					        $myFilter = new InputFilter('','',1,1,1); 
					        $_POST['insertanswer']=$myFilter->process($_POST['insertanswer']);
					    }
                        else
                          {
                            $_POST['insertanswer'] = html_entity_decode_php4($_POST['insertanswer'], ENT_QUOTES, "UTF-8");
                          }

                        // Fix bug with FCKEditor saving strange BR types
                        $_POST['insertanswer']=fix_FCKeditor_text($_POST['insertanswer']);
                        
        				// Add new Answer for Base Language Question
                        if (!isset($_POST['insertassessment_value'])) $_POST['insertassessment_value']=0;
                        else {$_POST['insertassessment_value']=(int) $_POST['insertassessment_value'];}
            
        				$query = "INSERT INTO ".db_table_name('answers')." (qid, code, answer, sortorder, default_value,language, assessment_value) VALUES ('{$postqid}', ".$connect->qstr($_POST['insertcode']).", ".$connect->qstr($_POST['insertanswer']).", '{$newsortorder}', 'N','$baselang',{$_POST['insertassessment_value']})";
        	       		if (!$result = $connect->Execute($query))
        				{
        					$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to insert answer","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
        				}
                        
					    // Added by lemeur for AutoSaveAll
					    $_POST['code_'.($newsortorder+0)] = $_POST['insertcode'];
                        $_POST['assessment_'.($newsortorder+0)] = $_POST['insertassessment_value'];
					    $_POST['previouscode_'.($newsortorder+0)] = $_POST['insertcode'];
					    $_POST['codeids'] = $_POST['codeids'] . " ".($newsortorder+0);
					    $_POST['answer_'.$baselang.'_'.($newsortorder+0)] = $_POST['insertanswer'];
					    $_POST['sortorderids'] = $_POST['sortorderids'] . " ".$baselang."_".($newsortorder+0);
					    // End lemeur AutoSaveAll
                        
        				// Last code was successfully inserted - find out the next incrementing code and remember it
						$_SESSION['nextanswercode']=getNextCode($_POST['insertcode']);
                        //Now check if this new code doesn't exist. For now then there is no code inserted.
                        $query = "select * from ".db_table_name('answers')." where code=".$connect->qstr($_SESSION['nextanswercode'])." and language='$baselang' and qid={$postqid}";
                        $result = $connect->Execute($query);
                        if ($result->RecordCount()>0) unset($_SESSION['nextanswercode']);
                        
        				foreach ($anslangs as $anslang)
        				{
        					if(!isset($_POST['default'])) $_POST['default'] = "";
    	    				$query = "INSERT INTO ".db_table_name('answers')." (qid, code, answer, sortorder, default_value,language) VALUES ({$postqid}, ".$connect->qstr($_POST['insertcode']).",".$connect->qstr($_POST['insertanswer']).", '{$newsortorder}', 'N','$anslang')";
        	       		    if (!$result = $connect->Execute($query))
        					{
        						$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to insert answer","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
        					}
					// Added by lemeur for AutoSaveAll
					$_POST['answer_'.$anslang.'_'.($newsortorder+0)] = $_POST['insertanswer'];
					$_POST['sortorderids'] = $_POST['sortorderids'] . " ".$anslang."_".($newsortorder+0);
					// End lemeur AutoSaveAll
        				}
        		}
			} else {
				$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Invalid or empty answer code supplied","js")."\")\n //-->\n</script>\n";
				break; // let's break because in this case we do not want an AutoSaveAll
			}
		//break; // Commented by lemeur for AutoSaveAll
		// Save all answers with one button
		case $clang->gT("Save All", "unescaped"):
			//Determine autoids by evaluating the hidden field		
            $sortorderids=explode(' ', trim($_POST['sortorderids']));
            natsort($sortorderids); // // Added by lemeur for AutoSaveAll
            $codeids=explode(' ', trim($_POST['codeids']));
            $count=0;
            $invalidCode = 0;
            $duplicateCode = 0;
            
            $testarray = array();
            
            for ($i=0; $i<count($sortorderids); $i++)
            {
            	if (isset($codeids[$i])) $testarray[]=$_POST['code_'.$codeids[$i]];
            }

 			$dupanswers = array();
            // Find duplicate codes and add these to dupanswers array
            $foundCat=array_count_values($testarray);
            foreach($foundCat as $key=>$value){
                if($value>=2){
                    $dupanswers[]=$key;
                }
            } 

			require_once("../classes/inputfilter/class.inputfilter_clean.php");
		    $myFilter = new InputFilter('','',1,1,1); 

			//First delete all answers 
  			$query = "delete from ".db_table_name('answers')." where qid=".$connect->qstr($qid);
            $result = $connect->Execute($query);
   			
         	foreach ($sortorderids as $sortorderid)
        	{
        		$defaultanswerset='N';
                $langid=substr($sortorderid,0,strrpos($sortorderid,'_')); 
        		$orderid=substr($sortorderid,strrpos($sortorderid,'_')+1,20);
        		if (isset($_POST['default_answer_'.$codeids[$count]]) && $_POST['default_answer_'.$codeids[$count]]=="Y") 
        		{
        		 $defaultanswerset='Y';
            }
        		if ($_POST['code_'.$codeids[$count]] != "0" && trim($_POST['code_'.$codeids[$count]]) != "" && !in_array($_POST['code_'.$codeids[$count]],$dupanswers))
        		{
					//Sanitize input, strip XSS
	     			if ($filterxsshtml)
	     			{
						$_POST['answer_'.$sortorderid]=$myFilter->process($_POST['answer_'.$sortorderid]);
					}
                        else
                          {
                            $_POST['answer_'.$sortorderid] = html_entity_decode_php4($_POST['answer_'.$sortorderid], ENT_QUOTES, "UTF-8");
                          }
                    
                    // Fix bug with FCKEditor saving strange BR types
                    $_POST['answer_'.$sortorderid]=fix_FCKeditor_text($_POST['answer_'.$sortorderid]);

        			$_POST['code_'.$codeids[$count]]=sanitize_paranoid_string($_POST['code_'.$codeids[$count]]);
					// Now we insert the answers
        			$query = "INSERT INTO ".db_table_name('answers')." (code,answer,qid,sortorder,language,default_value,assessment_value) 
					          VALUES (".$connect->qstr($_POST['code_'.$codeids[$count]]).", ".
							            $connect->qstr($_POST['answer_'.$sortorderid]).", ".
										$connect->qstr($qid).", ".
										$connect->qstr($orderid).", ".
										$connect->qstr($langid).", ".
										$connect->qstr($defaultanswerset).",".
                                        $connect->qstr((int)$_POST['assessment_'.$codeids[$count]]).")";
                    if (!$result = $connect->Execute($query))
        			{
        				$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to update answers","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
    				}
    				//if ($oldcode != false)
				if ($_POST['previouscode_'.$codeids[$count]] != $_POST['code_'.$codeids[$count]])
    				{
    					// Update the Answer code in conditions (updates if row exists right)
    					$query = "UPDATE ".db_table_name('conditions')." SET value = ".$connect->qstr($_POST['code_'.$codeids[$count]])." where cqid='$qid' and value=".$connect->qstr($_POST['previouscode_'.$codeids[$count]]);
        				$result = $connect->Execute($query);
					// also update references like @sidXgidXqidAid@
    					$query = "UPDATE ".db_table_name('conditions')." SET value = '@".$surveyid."X".$gid."X".$qid.db_quote($_POST['code_'.$codeids[$count]])."@' where cqid='$qid' and value='@".$surveyid."X".$gid."X".$qid.db_quote($_POST['previouscode_'.$codeids[$count]])."@'";
        				$result = $connect->Execute($query);
    				}
        		} else {
        			if ($_POST['code_'.$codeids[$count]] == "0" || trim($_POST['code_'.$codeids[$count]]) == "") $invalidCode = 1;
        			if (in_array($_POST['code_'.$codeids[$count]],$dupanswers)) $duplicateCode = 1;
        		}
    			$count++;
    			if ($count>count($codeids)-1) {$count=0;}
		    }
		    if ($invalidCode == 1) $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Answers with a code of 0 (zero) or blank code are not allowed, and will not be saved","js")."\")\n //-->\n</script>\n";
			if ($duplicateCode == 1) $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Duplicate codes found, these entries won't be updated","js")."\")\n //-->\n</script>\n";
		break;

		// Pressing the Up button
		case $clang->gT("Up", "unescaped"):
		    $newsortorder=$postsortorder-1;
		    $oldsortorder=$postsortorder;
		    $cdquery = "UPDATE ".db_table_name('answers')." SET sortorder=-1 WHERE qid=$qid AND sortorder='$newsortorder'";
		    $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
		    $cdquery = "UPDATE ".db_table_name('answers')." SET sortorder=$newsortorder WHERE qid=$qid AND sortorder=$oldsortorder";
		    $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
		    $cdquery = "UPDATE ".db_table_name('answers')." SET sortorder='$oldsortorder' WHERE qid=$qid AND sortorder=-1";
		    $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
		break;

        // Pressing the Down button
		case $clang->gT("Dn", "unescaped"):
		    $newsortorder=$postsortorder+1;
		    $oldsortorder=$postsortorder;
		    $cdquery = "UPDATE ".db_table_name('answers')." SET sortorder=-1 WHERE qid=$qid AND sortorder='$newsortorder'";
		    $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
		    $cdquery = "UPDATE ".db_table_name('answers')." SET sortorder='$newsortorder' WHERE qid=$qid AND sortorder=$oldsortorder";
		    $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
		    $cdquery = "UPDATE ".db_table_name('answers')." SET sortorder=$oldsortorder WHERE qid=$qid AND sortorder=-1";
		    $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
		break;
		
		// Delete Button
		case $clang->gT("Del", "unescaped"):
			$query = "DELETE FROM ".db_table_name('answers')." WHERE qid={$qid} AND sortorder='{$postsortorder}'";
			if (!$result = $connect->Execute($query))
			{
				$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to delete answer","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
			}
            fixsortorderAnswers($qid);
		break;
		
		// Default Button
		case $clang->gT("Default", "unescaped"):
			$query = "SELECT default_value from ".db_table_name('answers')." where qid={$qid} AND sortorder='{$postsortorder}' GROUP BY default_value";
			$result = db_execute_assoc($query);
			$row = $result->FetchRow();
			if ($row['default_value'] == "Y")
			{
				$query = "UPDATE ".db_table_name('answers')." SET default_value='N' WHERE qid={$qid} AND sortorder='{$postsortorder}'";
				if (!$result = $connect->Execute($query))
				{
					$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to make answer not default","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
				}
			} else {	
				$query = "SELECT type from ".db_table_name('questions')." where qid={$qid} GROUP BY type";
				$result = db_execute_assoc($query);
				$row = $result->FetchRow();
				if ($row['type'] == "O" || $row['type'] == "L" || $row['type'] == "!")
				{   // SINGLE CHOICE QUESTION, SET ALL RECORDS TO N, THEN WE SET ONE TO Y
					$query = "UPDATE ".db_table_name('answers')." SET default_value='N' WHERE qid={$qid}";
					$result = $connect->Execute($query);
				}
				$query = "UPDATE ".db_table_name('answers')." SET default_value='Y' WHERE qid={$qid} AND sortorder='{$postsortorder}'";
				if (!$result = $connect->Execute($query))
				{
					$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to make answer default","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
				}
			}
		break;
		}
	}

	elseif ($action == "updatesurvey" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['edit_survey_property']))
	{

		if (trim($_POST['expires'])=="")
		{
			$_POST['expires']='1980-01-01';
		}
		if (trim($_POST['startdate'])=="")
		{
			$_POST['startdate']='1980-01-01';
		}
		CleanLanguagesFromSurvey($postsid,$_POST['languageids']);
		FixLanguageConsistency($postsid,$_POST['languageids']);
		
		if($_SESSION['USER_RIGHT_SUPERADMIN'] != 1 && $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] != 1 && !hasTemplateManageRights($_SESSION['loginID'], $_POST['template'])) $_POST['template'] = "default";
		
		$_POST  = array_map('db_quote', $_POST);
		$usquery = "UPDATE {$dbprefix}surveys \n"
		. "SET admin='{$_POST['admin']}', useexpiry='{$_POST['useexpiry']}',\n"
		. "expires='{$_POST['expires']}', adminemail='{$_POST['adminemail']}',\n"
        . "startdate='{$_POST['startdate']}',usestartdate='{$_POST['usestartdate']}',\n"
		. "bounce_email='{$_POST['bounce_email']}', usetokens='{$_POST['usetokens']}',\n"
		. "private='{$_POST['private']}', faxto='{$_POST['faxto']}',\n"
		. "format='{$_POST['format']}', template='{$_POST['template']}',\n"
		. "assessments='{$_POST['assessments']}',\n"
		. "language='{$_POST['language']}', additional_languages='{$_POST['languageids']}',\n"
		. "datestamp='{$_POST['datestamp']}', ipaddr='{$_POST['ipaddr']}',"
        ." refurl='{$_POST['refurl']}', publicgraphs='{$_POST['publicgraphs']}',\n"
		. "usecookie='{$_POST['usecookie']}', notification='{$_POST['notification']}',\n"
		. "allowregister='{$_POST['allowregister']}', allowsave='{$_POST['allowsave']}',\n"
        . "printanswers='{$_POST['printanswers']}', publicstatistics='{$_POST['publicstatistics']}',\n"   
		. "autoredirect='{$_POST['autoredirect']}', allowprev='{$_POST['allowprev']}',\n"
		. "listpublic='{$_POST['public']}', htmlemail='{$_POST['htmlemail']}',\n"
		. "tokenanswerspersistence='{$_POST['tokenanswerspersistence']}', usecaptcha='{$_POST['usecaptcha']}'\n"
		. "WHERE sid={$postsid}";
		
		$usresult = $connect->Execute($usquery) or safe_die("Error updating<br />".$usquery."<br /><br /><strong>".$connect->ErrorMsg());
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

		$usquery = "Delete from ".db_table_name('surveys_languagesettings')." where surveyls_survey_id={$postsid} ".$sqlstring;
		$usresult = $connect->Execute($usquery) or safe_die("Error deleting obsolete surveysettings<br />".$usquery."<br /><br /><strong>".$connect->ErrorMsg());

		foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname)
		{
			if ($langname)
			{
				$usquery = "select * from ".db_table_name('surveys_languagesettings')." where surveyls_survey_id={$postsid} and surveyls_language='".$langname."'";
				$usresult = $connect->Execute($usquery) or safe_die("Error deleting obsolete surveysettings<br />".$usquery."<br /><br /><strong>".$connect->ErrorMsg());
				if ($usresult->RecordCount()==0)
				{

				if (getEmailFormat($surveyid) == "html")
				{
					$ishtml=true;
				}
				else
				{
					$ishtml=false;
				}

                    $bplang = new limesurvey_lang($langname);        
					$usquery = "INSERT INTO ".db_table_name('surveys_languagesettings')
                             ." (surveyls_survey_id, surveyls_language, surveyls_title, "
                             ." surveyls_email_invite_subj, surveyls_email_invite, "
                             ." surveyls_email_remind_subj, surveyls_email_remind, "
                             ." surveyls_email_confirm_subj, surveyls_email_confirm, "
                             ." surveyls_email_register_subj, surveyls_email_register) "
                             ." VALUES ({$postsid}, '".$langname."', '',"
                             .$connect->qstr($bplang->gT("Invitation to participate in survey",'unescaped')).","
                             .$connect->qstr(conditional2_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",'unescaped'),$ishtml)).","
                             .$connect->qstr($bplang->gT("Reminder to participate in survey",'unescaped')).","
                             .$connect->qstr(conditional2_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",'unescaped'),$ishtml)).","
                             .$connect->qstr($bplang->gT("Confirmation of completed survey",'unescaped')).","
                             .$connect->qstr(conditional2_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}",'unescaped'),$ishtml)).","
                             .$connect->qstr($bplang->gT("Survey Registration Confirmation",'unescaped')).","
                             .$connect->qstr(conditional2_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.",'unescaped'),$ishtml))
                             .")";  
                    unset($bplang);         
					$usresult = $connect->Execute($usquery) or safe_die("Error deleting obsolete surveysettings<br />".$usquery."<br /><br />".$connect->ErrorMsg());
				}
			}
		}



		if ($usresult)
		{
			$surveyselect = getsurveylist();
		}
		else
		{
			$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Survey could not be updated","js")."\n".$connect->ErrorMsg() ." ($usquery)\")\n //-->\n</script>\n";
		}
	}

	elseif ($action == "delsurvey" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['delete_survey'])) //can only happen if there are no groups, no questions, no answers etc.
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
			$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("ERROR deleting Survey id","js")." ($surveyid)!\n$error\")\n //-->\n</script>\n";

		}
	}


	// Save the 2nd page from the survey-properties
	elseif ($action == "updatesurvey2" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['edit_survey_property']))
	{
		$languagelist = GetAdditionalLanguagesFromSurveyID($surveyid);
		$languagelist[]=GetBaseLanguageFromSurveyID($surveyid);
		require_once("../classes/inputfilter/class.inputfilter_clean.php");
	    $myFilter = new InputFilter('','',1,1,1); 
		
		foreach ($languagelist as $langname)
		{
			if ($langname)
			{

                if ($_POST['url_'.$langname] == "http://") {$_POST['url_'.$langname]="";}
                
			    // Clean XSS attacks
    	     	if ($filterxsshtml)
		    	{
				    $_POST['short_title_'.$langname]=$myFilter->process($_POST['short_title_'.$langname]);
				    $_POST['description_'.$langname]=$myFilter->process($_POST['description_'.$langname]);
				    $_POST['welcome_'.$langname]=$myFilter->process($_POST['welcome_'.$langname]);
                    $_POST['endtext_'.$langname]=$myFilter->process($_POST['endtext_'.$langname]);
				    $_POST['urldescrip_'.$langname]=$myFilter->process($_POST['urldescrip_'.$langname]);
                    $_POST['url_'.$langname]=$myFilter->process($_POST['url_'.$langname]);
			  	}
                else
                          {
                            $_POST['short_title_'.$langname] = html_entity_decode_php4($_POST['short_title_'.$langname], ENT_QUOTES, "UTF-8");
                            $_POST['description_'.$langname] = html_entity_decode_php4($_POST['description_'.$langname], ENT_QUOTES, "UTF-8");
                            $_POST['welcome_'.$langname] = html_entity_decode_php4($_POST['welcome_'.$langname], ENT_QUOTES, "UTF-8");
                            $_POST['endtext_'.$langname] = html_entity_decode_php4($_POST['endtext_'.$langname], ENT_QUOTES, "UTF-8");
                            $_POST['urldescrip_'.$langname] = html_entity_decode_php4($_POST['urldescrip_'.$langname], ENT_QUOTES, "UTF-8");
                            $_POST['url_'.$langname] = html_entity_decode_php4($_POST['url_'.$langname], ENT_QUOTES, "UTF-8");
                          }
                
                // Fix bug with FCKEditor saving strange BR types
                $_POST['short_title_'.$langname]=fix_FCKeditor_text($_POST['short_title_'.$langname]);
                $_POST['description_'.$langname]=fix_FCKeditor_text($_POST['description_'.$langname]);
                $_POST['welcome_'.$langname]=fix_FCKeditor_text($_POST['welcome_'.$langname]);
                $_POST['endtext_'.$langname]=fix_FCKeditor_text($_POST['endtext_'.$langname]);
                
				$usquery = "UPDATE ".db_table_name('surveys_languagesettings')." \n"
				. "SET surveyls_title='".db_quote($_POST['short_title_'.$langname])."', surveyls_description='".db_quote($_POST['description_'.$langname])."',\n"
				. "surveyls_welcometext='".db_quote($_POST['welcome_'.$langname])."',\n"
                . "surveyls_endtext='".db_quote($_POST['endtext_'.$langname])."',\n"
                . "surveyls_url='".db_quote($_POST['url_'.$langname])."',\n"
				. "surveyls_urldescription='".db_quote($_POST['urldescrip_'.$langname])."'\n"
				. "WHERE surveyls_survey_id=".db_quote($postsid)." and surveyls_language='".$langname."'";
				$usresult = $connect->Execute($usquery) or safe_die("Error updating<br />".$usquery."<br /><br /><strong>".$connect->ErrorMsg());
			}
		}
	}

}




elseif ($action == "insertnewsurvey" && $_SESSION['USER_RIGHT_CREATE_SURVEY'])
{
	if ($_POST['url'] == "http://") {$_POST['url']="";}
	if (!$_POST['surveyls_title'])
	{
		$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Survey could not be created because it did not have a short title","js")."\")\n //-->\n</script>\n";
	} else
	{
		if (trim($_POST['expires'])=="")
		{
			$_POST['expires']='1980-01-01';
		}

		// Get random ids until one is found that is not used
		do
		{
			$surveyid = getRandomID();
			$isquery = "SELECT sid FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
			$isresult = db_execute_assoc($isquery);
		}
		while ($isresult->RecordCount()>0);

		if (!isset($_POST['template'])) {$_POST['template']='default';}		
		if($_SESSION['USER_RIGHT_SUPERADMIN'] != 1 && $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] != 1 && !hasTemplateManageRights($_SESSION['loginID'], $_POST['template'])) $_POST['template'] = "default";

		// insert base language into surveys_language_settings
     	if ($filterxsshtml)
     	{
	   		require_once("../classes/inputfilter/class.inputfilter_clean.php");
		    $myFilter = new InputFilter('','',1,1,1); 	

	    	$_POST['surveyls_title']=$myFilter->process($_POST['surveyls_title']);
		    $_POST['description']=$myFilter->process($_POST['description']);
		    $_POST['welcome']=$myFilter->process($_POST['welcome']);
		    $_POST['urldescrip']=$myFilter->process($_POST['urldescrip']);	
		}
        else
              {
                $_POST['surveyls_title'] = html_entity_decode_php4($_POST['surveyls_title'], ENT_QUOTES, "UTF-8");
                $_POST['description'] = html_entity_decode_php4($_POST['description'], ENT_QUOTES, "UTF-8");
                $_POST['welcome'] = html_entity_decode_php4($_POST['welcome'], ENT_QUOTES, "UTF-8");
                $_POST['urldescrip'] = html_entity_decode_php4($_POST['urldescrip'], ENT_QUOTES, "UTF-8");
              }


		$_POST  = array_map('db_quote', $_POST);

		$isquery = "INSERT INTO {$dbprefix}surveys\n"
		. "(sid, owner_id, "
        . "admin, active, "
        . "useexpiry, expires, "
        . "usestartdate, startdate,"
		. "adminemail, bounce_email, private, "
        . "faxto, format, template, "
		. "language, datestamp, ipaddr, refurl, "
        . "usecookie, notification, allowregister, "
		. "allowsave, autoredirect, "
        . "allowprev, printanswers, usetokens, "
        . "datecreated, listpublic, htmlemail, tokenanswerspersistence, "
        . "usecaptcha, publicstatistics, publicgraphs, assessments)\n"
		. "VALUES ($surveyid, {$_SESSION['loginID']},\n"
		. "'{$_POST['admin']}', 'N', \n"
		. "'{$_POST['useexpiry']}','{$_POST['expires']}'," 
        . "'{$_POST['usestartdate']}','{$_POST['startdate']}'," 
        . "'{$_POST['adminemail']}', '{$_POST['bounce_email']}', '{$_POST['private']}',\n"
		. "'{$_POST['faxto']}', '{$_POST['format']}', '{$_POST['template']}',\n"
		. "'{$_POST['language']}', '{$_POST['datestamp']}', '{$_POST['ipaddr']}', '{$_POST['refurl']}',\n"
		. "'{$_POST['usecookie']}', '{$_POST['notification']}', '{$_POST['allowregister']}',\n"
		. "'{$_POST['allowsave']}', '{$_POST['autoredirect']}', \n"
        . "'{$_POST['allowprev']}', '{$_POST['printanswers']}', '{$_POST['usetokens']}',\n"
		. "'".date("Y-m-d")."', '{$_POST['public']}', '{$_POST['htmlemail']}', '{$_POST['tokenanswerspersistence']}', \n"
        . "'{$_POST['usecaptcha']}', '{$_POST['publicstatistics']}','{$_POST['publicgraphs']}','{$_POST['assessments']}')";

		$isresult = $connect->Execute($isquery);


                
        // Fix bug with FCKEditor saving strange BR types
        $_POST['surveyls_title']=fix_FCKeditor_text($_POST['surveyls_title']);
        $_POST['description']=fix_FCKeditor_text($_POST['description']);
        $_POST['welcome']=fix_FCKeditor_text($_POST['welcome']);

	// Prepare default emailsettings	
	if ($_POST['htmlemail'] == "Y")
	{
		$ishtml=true;
	}
	else
	{
		$ishtml=false;
	}
	$bplang = new limesurvey_lang($_POST['language']);	

		$isquery = "INSERT INTO ".db_table_name('surveys_languagesettings')
		."(surveyls_survey_id, surveyls_language, "
        ." surveyls_title, surveyls_description, "
        ." surveyls_welcometext, surveyls_urldescription,"
        ." surveyls_endtext, surveyls_url,"
		." surveyls_email_invite_subj, surveyls_email_invite, "
		." surveyls_email_remind_subj, surveyls_email_remind, "
		." surveyls_email_confirm_subj, surveyls_email_confirm, "
		." surveyls_email_register_subj, surveyls_email_register) "
		. "VALUES ($surveyid, '{$_POST['language']}', '{$_POST['surveyls_title']}', '{$_POST['description']}',\n"
		. "'{$_POST['welcome']}','{$_POST['urldescrip']}', "
        . "'{$_POST['endtext']}','{$_POST['url']}', "
		.$connect->qstr($bplang->gT("Invitation to participate in survey",'unescaped')).","
		.$connect->qstr(conditional2_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",'unescaped'),$ishtml)).","
		.$connect->qstr($bplang->gT("Reminder to participate in survey",'unescaped')).","
		.$connect->qstr(conditional2_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",'unescaped'),$ishtml)).","
		.$connect->qstr($bplang->gT("Confirmation of completed survey",'unescaped')).","
		.$connect->qstr(conditional2_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}",'unescaped'),$ishtml)).","
		.$connect->qstr($bplang->gT("Survey Registration Confirmation",'unescaped')).","
		.$connect->qstr(conditional2_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.",'unescaped'),$ishtml))
		.")";  

		$isresult = $connect->Execute($isquery);
		unset($bplang);

		// Insert into survey_rights
		$isrquery = "INSERT INTO {$dbprefix}surveys_rights VALUES($surveyid,". $_SESSION['loginID'].",1,1,1,1,1,1)"; //inserts survey rights for owner
		$isrresult = $connect->Execute($isrquery) or safe_die ($isrquery."<br />".$connect->ErrorMsg()); //ADDED by Moses
		if ($isresult)
		{
			$surveyselect = getsurveylist();
		}
		else
		{
			$errormsg=$clang->gT("Survey could not be created","js")." - ".$connect->ErrorMsg();
			$databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"$errormsg\")\n //-->\n</script>\n";
			$databaseoutput .= htmlspecialchars($isquery);
		}
	}
}

else
{

	include("access_denied.php");
}


?>
