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
//Last security audit on 2009-10-11

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

/**
 * Gets the maximum question_order field value for a group
 *
 * @param mixed $gid
 * @return mixed
 */
function get_max_question_order($gid)
{
    global $connect ;
    global $dbprefix ;
    $query="SELECT MAX(question_order) as maxorder FROM {$dbprefix}questions where gid=".$gid ;
    // echo $query;
    $result = db_execute_assoc($query);  // Checked
    $gv = $result->FetchRow();
    return $gv['maxorder'];
}

$databaseoutput ='';

if(isset($surveyid))
{
    $actsurquery = "SELECT define_questions, edit_survey_property, delete_survey FROM {$dbprefix}surveys_rights WHERE sid=$surveyid AND uid = ".$_SESSION['loginID']; //Getting rights for this survey
    $actsurresult = db_execute_assoc($actsurquery); // Checked
    $actsurrows = $actsurresult->FetchRow();


    if ($action == "insertnewgroup" && ( $_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
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
                    $_POST['group_name_'.$grouplang] = html_entity_decode($_POST['group_name_'.$grouplang], ENT_QUOTES, "UTF-8");
                    $_POST['description_'.$grouplang] = html_entity_decode($_POST['description_'.$grouplang], ENT_QUOTES, "UTF-8");
                }

                // Fix bug with FCKEditor saving strange BR types
                $_POST['group_name_'.$grouplang]=fix_FCKeditor_text($_POST['group_name_'.$grouplang]);
                $_POST['description_'.$grouplang]=fix_FCKeditor_text($_POST['description_'.$grouplang]);


                if ($first)
                {
                    $query = "INSERT INTO ".db_table_name('groups')." (sid, group_name, description,group_order,language) VALUES ('".db_quote($postsid)."', '".db_quote($_POST['group_name_'.$grouplang])."', '".db_quote($_POST['description_'.$grouplang])."',".getMaxgrouporder(returnglobal('sid')).",'{$grouplang}')";
                    $result = $connect->Execute($query); // Checked
                    $groupid=$connect->Insert_Id(db_table_name_nq('groups'),"gid");
                    $first=false;
                }
                else{
                    db_switchIDInsert('groups',true);
                    $query = "INSERT INTO ".db_table_name('groups')." (gid, sid, group_name, description,group_order,language) VALUES ('{$groupid}','".db_quote($postsid)."', '".db_quote($_POST['group_name_'.$grouplang])."', '".db_quote($_POST['description_'.$grouplang])."',".getMaxgrouporder(returnglobal('sid')).",'{$grouplang}')";
                    $result = $connect->Execute($query) or safe_die("Error<br />".$query."<br />".$connect->ErrorMsg());   // Checked
                    db_switchIDInsert('groups',false);
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
                    $_POST['group_name_'.$grplang] = html_entity_decode($_POST['group_name_'.$grplang], ENT_QUOTES, "UTF-8");
                    $_POST['description_'.$grplang] = html_entity_decode($_POST['description_'.$grplang], ENT_QUOTES, "UTF-8");
                }

                // Fix bug with FCKEditor saving strange BR types
                $_POST['group_name_'.$grplang]=fix_FCKeditor_text($_POST['group_name_'.$grplang]);
                $_POST['description_'.$grplang]=fix_FCKeditor_text($_POST['description_'.$grplang]);

                // don't use array_map db_quote on POST
                // since this is iterated for each language
                //$_POST  = array_map('db_quote', $_POST);
                $ugquery = "UPDATE ".db_table_name('groups')." SET group_name='".db_quote($_POST['group_name_'.$grplang])."', description='".db_quote($_POST['description_'.$grplang])."' WHERE sid=".db_quote($postsid)." AND gid=".db_quote($postgid)." AND language='{$grplang}'";
                $ugresult = $connect->Execute($ugquery);  // Checked
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
        $result = $connect->Execute($query) or safe_die($connect->ErrorMsg());  // Checked

        $query = "DELETE FROM ".db_table_name('groups')." WHERE sid=$surveyid AND gid=$gid";
        $result = $connect->Execute($query) or safe_die($connect->ErrorMsg());  // Checked

        if ($result)
        {
            $gid = "";
            $groupselect = getgrouplist($gid);
            fixSortOrderGroups($surveyid);
        }
        else
        {
            $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Group could not be deleted","js")."\n$error\")\n //-->\n</script>\n";
        }
    }

    elseif ($action == "delgroup" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
    {
        if (!isset($gid)) $gid=returnglobal('gid');
        $query = "SELECT qid FROM ".db_table_name('groups')." g, ".db_table_name('questions')." q WHERE g.gid=q.gid AND g.gid=$gid AND q.parent_qid=0 group by qid";
        if ($result = db_execute_assoc($query)) // Checked
        {
            while ($row=$result->FetchRow())
            {
                $connect->Execute("DELETE FROM {$dbprefix}conditions WHERE qid={$row['qid']}");    // Checked
                $connect->Execute("DELETE FROM {$dbprefix}question_attributes WHERE qid={$row['qid']}"); // Checked
                $connect->Execute("DELETE FROM {$dbprefix}answers WHERE qid={$row['qid']}"); // Checked
                $connect->Execute("DELETE FROM {$dbprefix}questions WHERE qid={$row['qid']} or parent_qid={$row['qid']}"); // Checked
                $connect->Execute("DELETE FROM {$dbprefix}defaultvalues WHERE qid={$row['qid']}"); // Checked
            }
        }
        $query = "DELETE FROM ".db_table_name('assessments')." WHERE sid=$surveyid AND gid=$gid";
        $result = $connect->Execute($query) or safe_die($connect->ErrorMsg());  // Checked

        $query = "DELETE FROM ".db_table_name('groups')." WHERE sid=$surveyid AND gid=$gid";
        $result = $connect->Execute($query) or safe_die($connect->ErrorMsg());  // Checked
        if ($result)
        {
            $gid = "";
            $groupselect = getgrouplist($gid);
            fixSortOrderGroups($surveyid);
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
                //Bug Fix: remove +1 ->  $question_order=(sanitize_int($_POST['questionposition'])+1);
                $question_order=(sanitize_int($_POST['questionposition']));
                //Need to renumber all questions on or after this
                $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=question_order+1 WHERE gid=".$postgid." AND question_order >= ".$question_order;
                $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());  // Checked
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
                $_POST['title'] = html_entity_decode($_POST['title'], ENT_QUOTES, "UTF-8");
                $_POST['question_'.$baselang] = html_entity_decode($_POST['question_'.$baselang], ENT_QUOTES, "UTF-8");
                $_POST['help_'.$baselang] = html_entity_decode($_POST['help_'.$baselang], ENT_QUOTES, "UTF-8");
            }

            // Fix bug with FCKEditor saving strange BR types
            $_POST['title']=fix_FCKeditor_text($_POST['title']);
            $_POST['question_'.$baselang]=fix_FCKeditor_text($_POST['question_'.$baselang]);
            $_POST['help_'.$baselang]=fix_FCKeditor_text($_POST['help_'.$baselang]);

            $_POST  = array_map('db_quote', $_POST);
            $query = "INSERT INTO ".db_table_name('questions')." (sid, gid, type, title, question, preg, help, other, mandatory, question_order, language)"
            ." VALUES ('{$postsid}', '{$postgid}', '{$_POST['type']}', '{$_POST['title']}',"
            ." '{$_POST['question_'.$baselang]}', '{$_POST['preg']}', '{$_POST['help_'.$baselang]}', '{$_POST['other']}', '{$_POST['mandatory']}', $question_order,'{$baselang}')";
            $result = $connect->Execute($query);  // Checked
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
                        db_switchIDInsert('questions',true);
                        $query = "INSERT INTO ".db_table_name('questions')." (qid, sid, gid, type, title, question, preg, help, other, mandatory, question_order, language)"
                        ." VALUES ('$qid','{$postsid}', '{$postgid}', '{$_POST['type']}', '{$_POST['title']}',"
                        ." '{$_POST['question_'.$alang]}', '{$_POST['preg']}', '{$_POST['help_'.$alang]}', '{$_POST['other']}', '{$_POST['mandatory']}', $question_order,'{$alang}')";
                        $result2 = $connect->Execute($query);  // Checked
                        if (!$result2)
                        {
                            $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".sprintf($clang->gT("Question in language %s could not be created.","js"),$alang)."\\n".$connect->ErrorMsg()."\")\n //-->\n</script>\n";

                        }
                        db_switchIDInsert('questions',false);
                }
                }
            }


            if (!$result)
            {
                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be created.","js")."\\n".$connect->ErrorMsg()."\")\n //-->\n</script>\n";

            }

            $qattributes=questionAttributes();
            $validAttributes=$qattributes[$_POST['type']];
            foreach ($validAttributes as $validAttribute)
            {
                if (isset($_POST[$validAttribute['name']]))
                {
                    $query = "INSERT into ".db_table_name('question_attributes')."
                              (qid, value, attribute) values ($qid,'".db_quote($_POST[$validAttribute['name']])."','{$validAttribute['name']}')";
                    $result = $connect->Execute($query) or safe_die("Error updating attribute value<br />".$query."<br />".$connect->ErrorMsg()); // Checked

                }
            }

            fixsortorderQuestions($postgid, $surveyid);
            //include("surveytable_functions.php");
            //surveyFixColumns($surveyid);
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
        ."WHERE a.gid=".db_table_name('groups').".gid AND a.sid=$surveyid AND a.parent_qid=0 "
        ."GROUP BY a.gid, a.qid, ".db_table_name('groups').".group_order, question_order\n"
        ."ORDER BY ".db_table_name('groups').".group_order, question_order";
        $gresult=db_execute_assoc($gselect) or safe_die ("Error: ".$connect->ErrorMsg());  // Checked
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
            $uresult=$connect->Execute($usql) or safe_die("Error: ".$connect->ErrorMsg());  // Checked
            $question_number++;
            $group_number=$grow['gid'];
        }
    }

    
    elseif ($action == "updatedefaultvalues" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
    {
        
        $questlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($questlangs,$baselang);

        $questiontype=$connect->GetOne("SELECT type FROM ".db_table_name('questions')." WHERE qid=$postqid");
        $qtproperties=getqtypelist('','array');
        if ($qtproperties[$questiontype]['answerscales']>0 && $qtproperties[$questiontype]['subquestions']==0)
        {
            for ($scale_id=0;$scale_id<$qtproperties[$questiontype]['answerscales'];$scale_id++)
            {
                foreach ($questlangs as $language)
                {
                   if (isset($_POST['defaultanswerscale_'.$scale_id.'_'.$language]))
                   {                                                                       
                       Updatedefaultvalues($postqid,0,$scale_id,'',$language,$_POST['defaultanswerscale_'.$scale_id.'_'.$language],true);
                   }
                   if (isset($_POST['other_'.$scale_id.'_'.$language]))
                   {
                       Updatedefaultvalues($postqid,0,$scale_id,'other',$language,$_POST['other_'.$scale_id.'_'.$language],true);
                   } 
                }
            }
        }
        if ($qtproperties[$questiontype]['subquestions']>0)
        {

            foreach ($questlangs as $language)
            {
                $sqquery = "SELECT * FROM ".db_table_name('questions')." WHERE sid=$surveyid AND gid=$gid AND parent_qid=$postqid and language=".db_quoteall($language)." and scale_id=0 order by question_order";
                $sqresult = db_execute_assoc($sqquery);
                $sqrows = $sqresult->GetRows();

                for ($scale_id=0;$scale_id<$qtproperties[$questiontype]['subquestions'];$scale_id++)
                {
                   foreach ($sqrows as $aSubquestionrow)
                   {
                       if (isset($_POST['defaultanswerscale_'.$scale_id.'_'.$language.'_'.$aSubquestionrow['qid']]))
                       {                                                                       
                           Updatedefaultvalues($postqid,$aSubquestionrow['qid'],$scale_id,'',$language,$_POST['defaultanswerscale_'.$scale_id.'_'.$language.'_'.$aSubquestionrow['qid']],true);
                       }
/*                       if (isset($_POST['other_'.$scale_id.'_'.$language]))
                       {
                           Updatedefaultvalues($postqid,$qid,$scale_id,'other',$language,$_POST['other_'.$scale_id.'_'.$language],true);
                       } */
                       
                   } 
                }
            }
        }        
            
    }

    
    elseif ($action == "updatequestion" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
    {
        $cqquery = "SELECT type, gid FROM ".db_table_name('questions')." WHERE qid={$postqid}";
        $cqresult=db_execute_assoc($cqquery) or safe_die ("Couldn't get question type to check for change<br />".$cqquery."<br />".$connect->ErrorMsg()); // Checked
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
        db_execute_assoc($attsql) or safe_die ("Couldn't delete obsolete question attributes<br />".$attsql."<br />".$connect->ErrorMsg()); // Checked


        //now save all valid attributes
        $validAttributes=$qattributes[$_POST['type']];
        foreach ($validAttributes as $validAttribute)
        {
            if (isset($_POST[$validAttribute['name']]))
            {
                $query = "select qaid from ".db_table_name('question_attributes')."
                          WHERE attribute='".$validAttribute['name']."' AND qid=".$qid;
                $result = $connect->Execute($query) or safe_die("Error updating attribute value<br />".$query."<br />".$connect->ErrorMsg());  // Checked
                if ($result->Recordcount()>0)
                {
                    $query = "UPDATE ".db_table_name('question_attributes')."
                              SET value='".db_quote($_POST[$validAttribute['name']])."' WHERE attribute='".$validAttribute['name']."' AND qid=".$qid;
                    $result = $connect->Execute($query) or safe_die("Error updating attribute value<br />".$query."<br />".$connect->ErrorMsg());  // Checked
                }
                else
                {
                    $query = "INSERT into ".db_table_name('question_attributes')."
                              (qid, value, attribute) values ($qid,'".db_quote($_POST[$validAttribute['name']])."','{$validAttribute['name']}')";
                    $result = $connect->Execute($query) or safe_die("Error updating attribute value<br />".$query."<br />".$connect->ErrorMsg());  // Checked
                }
            }
        }


        $qtypes=getqtypelist('','array');
        // These are the questions types that have no answers and therefore we delete the answer in that case
        $keepansweroptions = ($qtypes[$_POST['type']]['answerscales']>0);
        $keepsubquestions = ($qtypes[$_POST['type']]['subquestions']>0);

        // These are the questions types that have the other option therefore we set everything else to 'No Other'
        if (($_POST['type']!= "L") && ($_POST['type']!= "!") && ($_POST['type']!= "P") && ($_POST['type']!="M"))
        {
            $_POST['other']='N';
        }

        // These are the questions types that have no validation - so zap it accordingly

        if ($_POST['type']== "!" || $_POST['type']== "L" || $_POST['type']== "M" || $_POST['type']== "P" ||
        $_POST['type']== "F" || $_POST['type']== "H" || $_POST['type']== ":" || $_POST['type']== ";" ||
        $_POST['type']== "X" || $_POST['type']== "")
        {
            $_POST['preg']='';
        }



        if ($oldtype != $_POST['type'])
        {
            //Make sure there are no conditions based on this question, since we are changing the type
            $ccquery = "SELECT * FROM ".db_table_name('conditions')." WHERE cqid={$postqid}";
            $ccresult = db_execute_assoc($ccquery) or safe_die ("Couldn't get list of cqids for this question<br />".$ccquery."<br />".$connect->ErrorMsg()); // Checked
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
                        $_POST['title'] = html_entity_decode($_POST['title'], ENT_QUOTES, "UTF-8");
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
                            $_POST['question_'.$qlang] = html_entity_decode($_POST['question_'.$qlang], ENT_QUOTES, "UTF-8");
                            $_POST['help_'.$qlang] = html_entity_decode($_POST['help_'.$qlang], ENT_QUOTES, "UTF-8");
                        }
                        // Fix bug with FCKEditor saving strange BR types
                        $_POST['question_'.$qlang]=fix_FCKeditor_text($_POST['question_'.$qlang]);
                        $_POST['help_'.$qlang]=fix_FCKeditor_text($_POST['help_'.$qlang]);

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

                            $uqquery.= "WHERE sid='".$postsid."' AND qid='".$postqid."' AND language='{$qlang}'";
                            $uqresult = $connect->Execute($uqquery) or safe_die ("Error Update Question: ".$uqquery."<br />".$connect->ErrorMsg());  // Checked
                            if (!$uqresult)
                            {
                                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be updated","js")."\n".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
                            }
                        }
                    }
                    // if the group has changed then fix the sortorder of old and new group
                    if ($oldgid!=$postgid)
                    {
                        fixsortorderQuestions($oldgid, $surveyid);
                        fixsortorderQuestions($postgid, $surveyid);
                        // If some questions have conditions set on this question's answers
                        // then change the cfieldname accordingly
                        fixmovedquestionConditions($postqid, $oldgid, $postgid);
                    }
                    if (!$keepansweroptions)
                    {
                        $query = "DELETE FROM ".db_table_name('answers')." WHERE qid=".$postqid;
                        $result = $connect->Execute($query) or safe_die("Error: ".$connect->ErrorMsg()); // Checked
                        if (!$result)
                        {
                            $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Answers can't be deleted","js")."\n".htmlspecialchars($connect->ErrorMsg())."\")\n //-->\n</script>\n";
                        }
                    }
                    if (!$keepsubquestions)
                    {
                        $query = "DELETE FROM ".db_table_name('questions')." WHERE parent_qid=".$postqid;
                        $result = $connect->Execute($query) or safe_die("Error: ".$connect->ErrorMsg()); // Checked
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
                $_POST['title'] = html_entity_decode($_POST['title'], ENT_QUOTES, "UTF-8");
                $_POST['question_'.$baselang] = html_entity_decode($_POST['question_'.$baselang], ENT_QUOTES, "UTF-8");
                $_POST['help_'.$baselang] = html_entity_decode($_POST['help_'.$baselang], ENT_QUOTES, "UTF-8");
            }


            // Fix bug with FCKEditor saving strange BR types
            $_POST['title']=fix_FCKeditor_text($_POST['title']);
            $_POST['question_'.$baselang]=fix_FCKeditor_text($_POST['question_'.$baselang]);
            $_POST['help_'.$baselang]=fix_FCKeditor_text($_POST['help_'.$baselang]);
            $_POST  = array_map('db_quote', $_POST);
            $query = "INSERT INTO {$dbprefix}questions (sid, gid, type, title, question, preg, help, other, mandatory, question_order, language)
                      VALUES ({$postsid}, {$postgid}, '{$_POST['type']}', '{$_POST['title']}', '".$_POST['question_'.$baselang]."', '{$_POST['preg']}', '".$_POST['help_'.$baselang]."', '{$_POST['other']}', '{$_POST['mandatory']}', $max,".db_quoteall($baselang).")";
            $result = $connect->Execute($query) or safe_die($connect->ErrorMsg()); // Checked
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
                    $_POST['question_'.$qlanguage] = html_entity_decode($_POST['question_'.$qlanguage], ENT_QUOTES, "UTF-8");
                    $_POST['help_'.$qlanguage] = html_entity_decode($_POST['help_'.$qlanguage], ENT_QUOTES, "UTF-8");
                }

                // Fix bug with FCKEditor saving strange BR types
                $_POST['question_'.$qlanguage]=fix_FCKeditor_text($_POST['question_'.$qlanguage]);
                $_POST['help_'.$qlanguage]=fix_FCKeditor_text($_POST['help_'.$qlanguage]);

                db_switchIDInsert('questions',true);
                $query = "INSERT INTO {$dbprefix}questions (qid, sid, gid, type, title, question, help, other, mandatory, question_order, language)
                      VALUES ($newqid,{$postsid}, {$postgid}, '{$_POST['type']}', '{$_POST['title']}', '".$_POST['question_'.$qlanguage]."', '".$_POST['help_'.$qlanguage]."', '{$_POST['other']}', '{$_POST['mandatory']}', $max,".db_quoteall($qlanguage).")";
                $result = $connect->Execute($query) or safe_die($connect->ErrorMsg()); // Checked
                db_switchIDInsert('questions',false);
            }
            if (!$result)
            {
                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be created.","js")."\\n".htmlspecialchars($connect->ErrorMsg())."\")\n //-->\n</script>\n";

            }
            if (returnglobal('copysubquestions') == "Y")
            {
                $aSQIDMappings=array();
                $q1 = "SELECT * FROM {$dbprefix}questions WHERE parent_qid="
                . returnglobal('oldqid')
                . " ORDER BY question_order";
                $r1 = db_execute_assoc($q1);  // Checked
                $tablename=$dbprefix.'questions';
                while ($qr1 = $r1->FetchRow())
                {
                    $qr1['parent_qid']=$newqid;
                    if (isset($aSQIDMappings[$qr1['qid']]))
                    {
                        $qr1['qid']=$aSQIDMappings[$qr1['qid']];
                        db_switchIDInsert($tablename,true); 
                    }
                    else
                    {
                        $oldqid=$qr1['qid'];
                        unset($qr1['qid']);
                    }
                    $sInsertSQL = $connect->GetInsertSQL($tablename,$qr1);
                    $ir1 = $connect->Execute($sInsertSQL);   // Checked
                    if (isset($qr1['qid']))
                    {
                        db_switchIDInsert($tablename,false); 
                    }
                    else
                    {
                        $aSQIDMappings[$oldqid]=$connect->Insert_ID($tablename,"qid");
                    }

                }
            }

            if (returnglobal('copyanswers') == "Y")
            {
                $q1 = "SELECT * FROM {$dbprefix}answers WHERE qid="
                . returnglobal('oldqid')
                . " ORDER BY code";
                $r1 = db_execute_assoc($q1);  // Checked
                while ($qr1 = $r1->FetchRow())
                {
                    $qr1 = array_map('db_quote', $qr1);
                    $i1 = "INSERT INTO {$dbprefix}answers (qid, code, answer, sortorder, language) "
                    . "VALUES ('$newqid', '{$qr1['code']}', "
                    . "'{$qr1['answer']}', "
                    . "'{$qr1['sortorder']}', '{$qr1['language']}')";
                    $ir1 = $connect->Execute($i1);   // Checked

                }
            }
            if (returnglobal('copyattributes') == "Y")
            {
                $q1 = "SELECT * FROM {$dbprefix}question_attributes
                   WHERE qid=".returnglobal('oldqid')."
                   ORDER BY qaid";
                $r1 = db_execute_assoc($q1); // Checked
                while($qr1 = $r1->FetchRow())
                {
                    $qr1 = array_map('db_quote', $qr1);
                    $i1 = "INSERT INTO {$dbprefix}question_attributes
                       (qid, attribute, value)
                       VALUES ('$newqid',
                       '{$qr1['attribute']}',
                       '{$qr1['value']}')";
                    $ir1 = $connect->Execute($i1);   // Checked
                } // while
            }
            fixsortorderQuestions($postgid, $surveyid);
            $gid=$postgid; //Sets the gid so that admin.php displays whatever group was chosen for this copied question
            $qid=$newqid; //Sets the qid so that admin.php displays the newly created question
        }
    }
    elseif ($action == "delquestion" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
    {
        if (!isset($qid)) {$qid=returnglobal('qid');}
        //check if any other questions have conditions which rely on this question. Don't delete if there are.
        $ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid=$qid";
        $ccresult = db_execute_assoc($ccquery) or safe_die ("Couldn't get list of cqids for this question<br />".$ccquery."<br />".$connect->ErrorMsg()); // Checked
        $cccount=$ccresult->RecordCount();
        while ($ccr=$ccresult->FetchRow()) {$qidarray[]=$ccr['qid'];}
        if (isset($qidarray)) {$qidlist=implode(", ", $qidarray);}
        if ($cccount) //there are conditions dependent on this question
        {
            $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be deleted. There are conditions for other questions that rely on this question. You cannot delete this question until those conditions are removed","js")." ($qidlist)\")\n //-->\n</script>\n";
        }
        else
        {
            $gid = $connect->GetOne("SELECT gid FROM ".db_table_name('questions')." WHERE qid={$qid}"); // Checked
            
            //see if there are any conditions/attributes/answers/defaultvalues for this question, and delete them now as well
            $connect->Execute("DELETE FROM {$dbprefix}conditions WHERE qid={$qid}");    // Checked
            $connect->Execute("DELETE FROM {$dbprefix}question_attributes WHERE qid={$qid}"); // Checked
            $connect->Execute("DELETE FROM {$dbprefix}answers WHERE qid={$qid}"); // Checked
            $connect->Execute("DELETE FROM {$dbprefix}questions WHERE qid={$qid} or parent_qid={$qid}"); // Checked
            $connect->Execute("DELETE FROM {$dbprefix}defaultvalues WHERE qid={$qid}"); // Checked
            fixsortorderQuestions($gid, $surveyid);

            $qid="";
            $postqid="";
            $_GET['qid']="";
        }
    }

    elseif ($action == "updateansweroptions" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
    {

        $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);

        $alllanguages = $anslangs;
        array_unshift($alllanguages,$baselang);


        $query = "select type from ".db_table_name('questions')." where qid=$qid";
        $questiontype = $connect->GetOne($query);    // Checked
        $qtypes=getqtypelist('','array');
        $scalecount=$qtypes[$questiontype]['answerscales'];

        $count=0;
        $invalidCode = 0;
        $duplicateCode = 0;
         
        require_once("../classes/inputfilter/class.inputfilter_clean.php");
        $myFilter = new InputFilter('','',1,1,1);

        //First delete all answers
        $query = "delete from ".db_table_name('answers')." where qid=".db_quote($qid);
        $result = $connect->Execute($query); // Checked

        for ($scale_id=0;$scale_id<$scalecount;$scale_id++)
        {
            $maxcount=(int)$_POST['answercount_'.$scale_id];
            for ($sortorderid=1;$sortorderid<$maxcount;$sortorderid++)
            {
                $code=sanitize_paranoid_string($_POST['code_'.$sortorderid.'_'.$scale_id]);
                $assessmentvalue=(int) $_POST['assessment_'.$sortorderid.'_'.$scale_id];
                foreach ($alllanguages as $language)
                {
                    $answer=$_POST['answer_'.$language.'_'.$sortorderid.'_'.$scale_id];
                    if ($filterxsshtml)
                    {
                        //Sanitize input, strip XSS
                        $answer=$myFilter->process($answer);
                    }
                    else
                    {
                        $answer=html_entity_decode($answer, ENT_QUOTES, "UTF-8");
                    }
                    // Fix bug with FCKEditor saving strange BR types
                    $answer=fix_FCKeditor_text($answer);

                    // Now we insert the answers
                    $query = "INSERT INTO ".db_table_name('answers')." (code,answer,qid,sortorder,language,assessment_value, scale_id)
                              VALUES (".db_quoteall($code).", ".
                    db_quoteall($answer).", ".
                    db_quote($qid).", ".
                    db_quote($sortorderid).", ".
                    db_quoteall($language).", ".
                    db_quote($assessmentvalue).",
                    $scale_id)";
                    if (!$result = $connect->Execute($query)) // Checked
                    {
                        $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to update answers","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
                    }
                } // foreach ($alllanguages as $language)
            }  // for ($sortorderid=0;$sortorderid<$maxcount;$sortorderid++)
        }  //  for ($scale_id=0;

        if ($invalidCode == 1) $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Answers with a code of 0 (zero) or blank code are not allowed, and will not be saved","js")."\")\n //-->\n</script>\n";
        if ($duplicateCode == 1) $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Duplicate codes found, these entries won't be updated","js")."\")\n //-->\n</script>\n";

        $sortorderid--;
        $action='editansweroptions';

        // Special treatment for ranking questions
        $surveyinfo=getSurveyInfo($surveyid);
        if ($questiontype=='R' && $surveyinfo['active']=='N')
        {
            $query="update ".db_table_name('question_attributes')." set value='$sortorderid' where attribute='ranking_slots' and qid=$qid";
            if (!$result = $connect->Execute($query)) // Checked
            {
                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to update answers","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
            }
        }

    }

    elseif ($action == "updatesubquestions" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['define_questions']))
    {

        $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($anslangs,$baselang);

        $query = "select type from ".db_table_name('questions')." where qid=$qid";
        $questiontype = $connect->GetOne($query);    // Checked
        $qtypes=getqtypelist('','array');
        $scalecount=$qtypes[$questiontype]['subquestions'];

        // First delete any deleted ids
        $deletedqids=explode(' ', trim($_POST['deletedqids']));

        foreach ($deletedqids as $deletedqid)
        {
            $deletedqid=(int)$deletedqid;
            $query = "DELETE FROM ".db_table_name('questions')." WHERE qid='{$deletedqid}'";  // Checked
            if (!$result = $connect->Execute($query))
            {
                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to delete answer","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
            }
        }

        //Determine ids by evaluating the hidden field
        $rows=array();
        $codes=array();
        foreach ($_POST as $postkey=>$postvalue)
        {
            $postkey=explode('_',$postkey);
            if ($postkey[0]=='answer')
            {
                $rows[$postkey[3]][$postkey[1]][$postkey[2]]=$postvalue;
            }
            if ($postkey[0]=='code')
            {
                $codes[$postkey[2]][]=$postvalue;
            }
        }
        $count=0;
        $invalidCode = 0;
        $duplicateCode = 0;
        $dupanswers = array();
        /*
         for ($scale_id=0;$scale_id<$scalecount;$scale_id++)
         {

         // Find duplicate codes and add these to dupanswers array
         $foundCat=array_count_values($codes);
         foreach($foundCat as $key=>$value){
         if($value>=2){
         $dupanswers[]=$key;
         }
         }
         }
         */
        require_once("../classes/inputfilter/class.inputfilter_clean.php");
        $myFilter = new InputFilter('','',1,1,1);


        $insertqids=array();
        for ($scale_id=0;$scale_id<$scalecount;$scale_id++)
        {
            foreach ($anslangs as $language)
            {
                $position=0;
                foreach ($rows[$scale_id][$language] as $subquestionkey=>$subquestionvalue)
                {
                    if (substr($subquestionkey,0,3)!='new')
                    {
                        $query='Update '.db_table_name('questions').' set question_order='.($position+1).', title='.db_quoteall($codes[$scale_id][$position]).', question='.db_quoteall($subquestionvalue).', scale_id='.$scale_id.' where qid='.db_quoteall($subquestionkey).' AND language='.db_quoteall($language);
                        $connect->execute($query);
                    }
                    else
                    {
                        if (!isset($insertqid[$position]))
                        {
                            $query='INSERT into '.db_table_name('questions').' (sid, gid, question_order, title, question, parent_qid, language, scale_id) values ('.$surveyid.','.$gid.','.($position+1).','.db_quoteall($codes[$scale_id][$position]).','.db_quoteall($subquestionvalue).','.$qid.','.db_quoteall($language).','.$scale_id.')';
                            $connect->execute($query);
                            $insertqid[$position]=$connect->Insert_Id(db_table_name_nq('questions'),"qid");
                        }
                        else
                        {
                            db_switchIDInsert('questions',true);
                            $query='INSERT into '.db_table_name('questions').' (qid, sid, gid, question_order, title, question, parent_qid, language, scale_id) values ('.$insertqid[$position].','.$surveyid.','.$gid.','.($position+1).','.db_quoteall($codes[$scale_id][$position]).','.db_quoteall($subquestionvalue).','.$qid.','.db_quoteall($language).','.$scale_id.')';
                            $connect->execute($query);
                            db_switchIDInsert('questions',true);
                        }
                    }
                    $position++;
                }

            }
        }
        //include("surveytable_functions.php");
        //surveyFixColumns($surveyid);
        $action='editsubquestions';
    }


    elseif (($action == "updatesurveysettingsandeditlocalesettings" || $action == "updatesurveysettings") && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['edit_survey_property']))
    {

        $formatdata=getDateFormatData($_SESSION['dateformat']);
        if (trim($_POST['expires'])=="")
        {
            $_POST['expires']=null;
        }
        else
        {
            $datetimeobj = new Date_Time_Converter($_POST['expires'], $formatdata['phpdate'].' H:i');
            $_POST['expires']=$datetimeobj->convert("Y-m-d H:i:s");
        }
        if (trim($_POST['startdate'])=="")
        {
            $_POST['startdate']=null;
        }
        else
        {
            $datetimeobj = new Date_Time_Converter($_POST['startdate'],$formatdata['phpdate'].' H:i');
            $_POST['startdate']=$datetimeobj->convert("Y-m-d H:i:s");
        }

        //make sure only numbers are passed within the $_POST variable
        $_POST['tokenlength'] = (int) $_POST['tokenlength'];

        //token length has to be at least 5, otherwise set it to default (15)
        if($_POST['tokenlength'] < 5)
        {
            $_POST['tokenlength'] = 15;
        }

        CleanLanguagesFromSurvey($postsid,$_POST['languageids']);
        FixLanguageConsistency($postsid,$_POST['languageids']);

        if($_SESSION['USER_RIGHT_SUPERADMIN'] != 1 && $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] != 1 && !hasTemplateManageRights($_SESSION['loginID'], $_POST['template'])) $_POST['template'] = "default";

        $sql = "SELECT * FROM {$dbprefix}surveys WHERE sid={$postsid}";  // We are using $dbrepfix here instead of db_table_name on purpose because GetUpdateSQL doesn't work correclty on Postfres with a quoted table name
        $rs = db_execute_assoc($sql); // Checked
        $updatearray= array('admin'=>$_POST['admin'],
                            'expires'=>$_POST['expires'],
                            'adminemail'=>$_POST['adminemail'],
                            'startdate'=>$_POST['startdate'],
                            'bounce_email'=>$_POST['bounce_email'],
        //                            'usetokens'=>$_POST['usetokens'],
                            'private'=>$_POST['private'],
                            'faxto'=>$_POST['faxto'],
                            'format'=>$_POST['format'],
                            'template'=>$_POST['template'],
                            'assessments'=>$_POST['assessments'],
                            'language'=>$_POST['language'],
                            'additional_languages'=>$_POST['languageids'],
                            'datestamp'=>$_POST['datestamp'],
                            'ipaddr'=>$_POST['ipaddr'],
                            'refurl'=>$_POST['refurl'],
                            'publicgraphs'=>$_POST['publicgraphs'],
                            'usecookie'=>$_POST['usecookie'],
                            'notification'=>$_POST['notification'],
                            'allowregister'=>$_POST['allowregister'],
                            'allowsave'=>$_POST['allowsave'],
                            'printanswers'=>$_POST['printanswers'],
                            'publicstatistics'=>$_POST['publicstatistics'],
                            'autoredirect'=>$_POST['autoredirect'],
                            'showXquestions'=>$_POST['showXquestions'],
                            'showgroupinfo'=>$_POST['showgroupinfo'],
                            'showqnumcode'=>$_POST['showqnumcode'],
                            'shownoanswer'=>$_POST['shownoanswer'],
                            'showwelcome'=>$_POST['showwelcome'],
                            'allowprev'=>$_POST['allowprev'],
                            'listpublic'=>$_POST['public'],
                            'htmlemail'=>$_POST['htmlemail'],
                            'tokenanswerspersistence'=>$_POST['tokenanswerspersistence'],
                            'usecaptcha'=>$_POST['usecaptcha'],
                            'emailresponseto'=>$_POST['emailresponseto'],
                            'tokenlength'=>$_POST['tokenlength']
        );

        $usquery=$connect->GetUpdateSQL($rs, $updatearray, false, get_magic_quotes_gpc());
        if ($usquery) {
            $usresult = $connect->Execute($usquery) or safe_die("Error updating<br />".$usquery."<br /><br /><strong>".$connect->ErrorMsg());  // Checked
        }
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
        $usresult = $connect->Execute($usquery) or safe_die("Error deleting obsolete surveysettings<br />".$usquery."<br /><br /><strong>".$connect->ErrorMsg()); // Checked

        foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname)
        {
            if ($langname)
            {
                $usquery = "select * from ".db_table_name('surveys_languagesettings')." where surveyls_survey_id={$postsid} and surveyls_language='".$langname."'";
                $usresult = $connect->Execute($usquery) or safe_die("Error deleting obsolete surveysettings<br />".$usquery."<br /><br /><strong>".$connect->ErrorMsg()); // Checked
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
                    $languagedetails=getLanguageDetails($langname);
                    $usquery = "INSERT INTO ".db_table_name('surveys_languagesettings')
                    ." (surveyls_survey_id, surveyls_language, surveyls_title, "
                    ." surveyls_email_invite_subj, surveyls_email_invite, "
                    ." surveyls_email_remind_subj, surveyls_email_remind, "
                    ." surveyls_email_confirm_subj, surveyls_email_confirm, "
                    ." surveyls_email_register_subj, surveyls_email_register, "
                    ." surveyls_dateformat) "
                    ." VALUES ({$postsid}, '".$langname."', '',"
                    .db_quoteall($bplang->gT("Invitation to participate in survey",'unescaped')).","
                    .db_quoteall(conditional2_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",'unescaped')."\n\n".$bplang->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}",'unescaped'),$ishtml)).","
                    .db_quoteall($bplang->gT("Reminder to participate in survey",'unescaped')).","
                    .db_quoteall(conditional2_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",'unescaped')."\n\n".$bplang->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}",'unescaped'),$ishtml)).","
                    .db_quoteall($bplang->gT("Confirmation of completed survey",'unescaped')).","
                    .db_quoteall(conditional2_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}",'unescaped'),$ishtml)).","
                    .db_quoteall($bplang->gT("Survey Registration Confirmation",'unescaped')).","
                    .db_quoteall(conditional2_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.",'unescaped'),$ishtml)).","
                    .$languagedetails['dateformat'].")";
                    unset($bplang);
                    $usresult = $connect->Execute($usquery) or safe_die("Error deleting obsolete surveysettings<br />".$usquery."<br /><br />".$connect->ErrorMsg()); // Checked
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
        $result = $connect->Execute($query);  // Checked
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
    elseif (($action == "updatesurveylocalesettings") && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['edit_survey_property']))
    {
        $languagelist = GetAdditionalLanguagesFromSurveyID($surveyid);
        $languagelist[]=GetBaseLanguageFromSurveyID($surveyid);
        require_once("../classes/inputfilter/class.inputfilter_clean.php");
        $myFilter = new InputFilter('','',1,1,1);

        foreach ($languagelist as $langname)
        {
            if ($langname)
            {

                if ($_POST['url_'.$langname] == 'http://') {$_POST['url_'.$langname]="";}

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
                    $_POST['short_title_'.$langname] = html_entity_decode($_POST['short_title_'.$langname], ENT_QUOTES, "UTF-8");
                    $_POST['description_'.$langname] = html_entity_decode($_POST['description_'.$langname], ENT_QUOTES, "UTF-8");
                    $_POST['welcome_'.$langname] = html_entity_decode($_POST['welcome_'.$langname], ENT_QUOTES, "UTF-8");
                    $_POST['endtext_'.$langname] = html_entity_decode($_POST['endtext_'.$langname], ENT_QUOTES, "UTF-8");
                    $_POST['urldescrip_'.$langname] = html_entity_decode($_POST['urldescrip_'.$langname], ENT_QUOTES, "UTF-8");
                    $_POST['url_'.$langname] = html_entity_decode($_POST['url_'.$langname], ENT_QUOTES, "UTF-8");
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
                . "surveyls_urldescription='".db_quote($_POST['urldescrip_'.$langname])."',\n"
                . "surveyls_dateformat='".db_quote($_POST['dateformat_'.$langname])."'\n"
                . "WHERE surveyls_survey_id=".$postsid." and surveyls_language='".$langname."'";
                $usresult = $connect->Execute($usquery) or safe_die("Error updating<br />".$usquery."<br /><br /><strong>".$connect->ErrorMsg());   // Checked
            }
        }
    }

}




elseif ($action == "insertnewsurvey" && $_SESSION['USER_RIGHT_CREATE_SURVEY'])
{
    $dateformatdetails=getDateFormatData($_SESSION['dateformat']);
    if ($_POST['url'] == 'http://') {$_POST['url']="";}
    if (!$_POST['surveyls_title'])
    {
        $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Survey could not be created because it did not have a title","js")."\")\n //-->\n</script>\n";
    } else
    {
        // Get random ids until one is found that is not used
        do
        {
            $surveyid = getRandomID();
            $isquery = "SELECT sid FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
            $isresult = db_execute_assoc($isquery); // Checked
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
            $_POST['surveyls_title'] = html_entity_decode($_POST['surveyls_title'], ENT_QUOTES, "UTF-8");
            $_POST['description'] = html_entity_decode($_POST['description'], ENT_QUOTES, "UTF-8");
            $_POST['welcome'] = html_entity_decode($_POST['welcome'], ENT_QUOTES, "UTF-8");
            $_POST['urldescrip'] = html_entity_decode($_POST['urldescrip'], ENT_QUOTES, "UTF-8");
        }

        //make sure only numbers are passed within the $_POST variable
        $_POST['dateformat'] = (int) $_POST['dateformat'];
        $_POST['tokenlength'] = (int) $_POST['tokenlength'];


        if (trim($_POST['expires'])=='')
        {
            $_POST['expires']=null;
        }
        else
        {
            $datetimeobj = new Date_Time_Converter($_POST['expires'] , "d.m.Y H:i");
            $browsedatafield=$datetimeobj->convert("Y-m-d H:i:s");
            $_POST['expires']=$browsedatafield;
        }

        if (trim($_POST['startdate'])=='')
        {
            $_POST['startdate']=null;
        }
        else
        {
            $datetimeobj = new Date_Time_Converter($_POST['startdate'] , "d.m.Y H:i");
            $browsedatafield=$datetimeobj->convert("Y-m-d H:i:s");
            $_POST['startdate']=$browsedatafield;
        }


        $insertarray=array( 'sid'=>$surveyid,
                            'owner_id'=>$_SESSION['loginID'],
                            'admin'=>$_POST['admin'],
                            'active'=>'N',
                            'expires'=>$_POST['expires'],
                            'startdate'=>$_POST['startdate'],
                            'adminemail'=>$_POST['adminemail'],
                            'bounce_email'=>$_POST['bounce_email'],
                            'private'=>$_POST['private'],
                            'faxto'=>$_POST['faxto'],
                            'format'=>$_POST['format'],
                            'template'=>$_POST['template'],
                            'language'=>$_POST['language'],
                            'datestamp'=>$_POST['datestamp'],
                            'ipaddr'=>$_POST['ipaddr'],
                            'refurl'=>$_POST['refurl'],
                            'usecookie'=>$_POST['usecookie'],
                            'notification'=>$_POST['notification'],
                            'allowregister'=>$_POST['allowregister'],
                            'allowsave'=>$_POST['allowsave'],
                            'autoredirect'=>$_POST['autoredirect'],
                            'showXquestions'=>$_POST['showXquestions'],
                            'showgroupinfo'=>$_POST['showgroupinfo'],
                            'showqnumcode'=>$_POST['showqnumcode'],
                            'shownoanswer'=>$_POST['shownoanswer'],
                            'showwelcome'=>$_POST['showwelcome'],
                            'allowprev'=>$_POST['allowprev'],
                            'printanswers'=>$_POST['printanswers'],
        //                            'usetokens'=>$_POST['usetokens'],
                            'datecreated'=>date("Y-m-d"),
                            'public'=>$_POST['public'],
                            'htmlemail'=>$_POST['htmlemail'],
                            'tokenanswerspersistence'=>$_POST['tokenanswerspersistence'],
                            'usecaptcha'=>$_POST['usecaptcha'],
                            'publicstatistics'=>$_POST['publicstatistics'],
                            'publicgraphs'=>$_POST['publicgraphs'],
                            'assessments'=>$_POST['assessments'],
                            'emailresponseto'=>$_POST['emailresponseto'],
                            'tokenlength'=>$_POST['tokenlength']
        );
        $dbtablename=db_table_name_nq('surveys');
        $isquery = $connect->GetInsertSQL($dbtablename, $insertarray);
        $isresult = $connect->Execute($isquery) or safe_die ($isrquery."<br />".$connect->ErrorMsg()); // Checked



        // Fix bug with FCKEditor saving strange BR types
        $_POST['surveyls_title']=fix_FCKeditor_text($_POST['surveyls_title']);
        $_POST['description']=fix_FCKeditor_text($_POST['description']);
        $_POST['welcome']=fix_FCKeditor_text($_POST['welcome']);

        $bplang = new limesurvey_lang($_POST['language']);
        $is_html_email = false;
        if (isset($_POST['htmlemail'])  && $_POST['htmlemail'] == "Y")
        {
            $is_html_email = true;
        }
        $insertarray=array( 'surveyls_survey_id'=>$surveyid,
                            'surveyls_language'=>$_POST['language'],
                            'surveyls_title'=>$_POST['surveyls_title'],
                            'surveyls_description'=>$_POST['description'],
                            'surveyls_welcometext'=>$_POST['welcome'],
                            'surveyls_urldescription'=>$_POST['urldescrip'],
                            'surveyls_endtext'=>$_POST['endtext'],
                            'surveyls_url'=>$_POST['url'],
                            'surveyls_email_invite_subj'=>$bplang->gT("Invitation to participate in survey",'unescaped'),
                            'surveyls_email_invite'=>conditional_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",'unescaped')."\n\n".$bplang->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}",'unescaped'),$is_html_email,'unescaped'),
                            'surveyls_email_remind_subj'=>$bplang->gT("Reminder to participate in survey",'unescaped'),
                            'surveyls_email_remind'=>conditional_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",'unescaped')."\n\n".$bplang->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}",'unescaped'),$is_html_email,'unescaped'),
                            'surveyls_email_confirm_subj'=>$bplang->gT("Confirmation of completed survey",'unescaped'),
                            'surveyls_email_confirm'=>conditional_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}",'unescaped'),$is_html_email,'unescaped'),
                            'surveyls_email_register_subj'=>$bplang->gT("Survey Registration Confirmation",'unescaped'),
                            'surveyls_email_register'=>conditional_nl2br($bplang->gT("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.",'unescaped'),$is_html_email,'unescaped'),
                            'surveyls_dateformat'=>$_POST['dateformat']
        );
        $dbtablename=db_table_name_nq('surveys_languagesettings');
        $isquery = $connect->GetInsertSQL($dbtablename, $insertarray);
        $isresult = $connect->Execute($isquery) or safe_die ($isquery."<br />".$connect->ErrorMsg()); // Checked
        unset($bplang);

        // Update survey_rights
        $isrquery = "INSERT INTO {$dbprefix}surveys_rights (sid,uid,edit_survey_property,define_questions,browse_response,export,delete_survey,activate_survey) VALUES($surveyid,". $_SESSION['loginID'].",1,1,1,1,1,1)"; //inserts survey rights for owner
        $isrresult = $connect->Execute($isrquery) or safe_die ($isrquery."<br />".$connect->ErrorMsg()); // Checked
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
        
        // Create initial Survey table
        //include("surveytable_functions.php");
        //$creationResult = surveyCreateTable($surveyid);
        // Survey table could not be created
        //if ($creationResult !== true)
        //{
        //    safe_die ("Initial survey table could not be created, please report this as a bug."."<br />".$creationResult);
        //}
    }
}
elseif ($action == "savepersonalsettings")
{
    $_POST  = array_map('db_quote', $_POST);
    $uquery = "UPDATE {$dbprefix}users SET lang='{$_POST['lang']}', dateformat='{$_POST['dateformat']}', htmleditormode= '{$_POST['htmleditormode']}'
               WHERE uid={$_SESSION['loginID']}";
    $uresult = $connect->Execute($uquery)  or safe_die ($isrquery."<br />".$connect->ErrorMsg());  // Checked
    $_SESSION['adminlang']=$_POST['lang'];
    $_SESSION['htmleditormode']=$_POST['htmleditormode'];
    $_SESSION['dateformat']= $_POST['dateformat'];
    $databaseoutput.='<div class="messagebox"><strong>'.$clang->gT('Your personal settings were successfully saved.').'</strong></div>';
}
else
{
    include("access_denied.php");
}

/**
* THis is a convenience function to update/delete answer default values. If the given 
* $defaultvalue is empty then the entry is removed from table defaultvalues
* 
* @param mixed $qid   Question ID
* @param mixed $scale_id  Scale ID
* @param mixed $specialtype  Special type (i.e. for  'Other')
* @param mixed $language     Language (defaults are language specific)
* @param mixed $defaultvalue    The default value itself
* @param boolean $ispost   If defaultvalue is from a $_POST set this to true to properly quote things
*/
function Updatedefaultvalues($qid,$sqid,$scale_id,$specialtype,$language,$defaultvalue,$ispost)
{
   global $connect;
   if ($defaultvalue=='')  // Remove the default value if it is empty
   {
      $connect->execute("DELETE FROM ".db_table_name('defaultvalues')." WHERE sqid=$sqid AND qid=$qid AND specialtype='$specialtype' AND scale_id={$scale_id} AND language='{$language}'");                           
   }
   else
   {
       $exists=$connect->GetOne("SELECT qid FROM ".db_table_name('defaultvalues')." WHERE sqid=$sqid AND qid=$qid AND specialtype=$specialtype'' AND scale_id={$scale_id} AND language='{$language}'");
       if (is_null($exists))
       {
           $connect->execute('INSERT INTO '.db_table_name('defaultvalues')." (defaultvalue,qid,scale_id,language,specialtype,sqid) VALUES (".db_quoteall($defaultvalue,$ispost).",{$qid},{$scale_id},'{$language}','{$specialtype}',{$sqid})");        
       }
       else
       {
           $connect->execute('Update '.db_table_name('defaultvalues')." set defaultvalue=".db_quoteall($defaultvalue,$ispost)."  WHERE sqid=$sqid AND qid=$qid AND specialtype='' AND scale_id={$scale_id} AND language='{$language}'");        
       }
   }
}

?>