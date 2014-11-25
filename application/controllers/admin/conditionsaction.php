<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/
/**
* Condition Controller
*
* This controller performs token actions
*
* @package		LimeSurvey
* @subpackage	Backend
*/
class conditionsaction extends Survey_Common_Action {

    function index($subaction, $iSurveyID=null, $gid=null, $qid=null)
    {
        $iSurveyID = sanitize_int($iSurveyID);
        $gid = sanitize_int($gid);
        $qid = sanitize_int($qid);

        $clang = $this->getController()->lang;
        $imageurl = Yii::app()->getConfig("adminimageurl");
        Yii::app()->loadHelper("database");

        if( !empty($_POST['subaction']) ) $subaction=Yii::app()->request->getPost('subaction');

        //BEGIN Sanitizing POSTed data
        if ( !isset($iSurveyID) ) { $iSurveyID = returnGlobal('sid'); }
        if ( !isset($qid) ) { $qid = returnGlobal('qid'); }
        if ( !isset($gid) ) { $gid = returnGlobal('gid'); }
        if ( !isset($p_scenario)) {$p_scenario=returnGlobal('scenario');}
        if ( !isset($p_cqid))
        {
            $p_cqid = returnGlobal('cqid');
            if ($p_cqid == '') $p_cqid=0; // we are not using another question as source of condition
        }

        if (!isset($p_cid)) { $p_cid=returnGlobal('cid'); }
        if (!isset($p_subaction)) { if (isset($_POST['subaction'])) $p_subaction=$_POST['subaction']; else $p_subaction=$subaction;}
        if (!isset($p_cquestions)) {$p_cquestions=returnGlobal('cquestions');}
        if (!isset($p_csrctoken)) {$p_csrctoken=returnGlobal('csrctoken');}
        if (!isset($p_prevquestionsgqa)) {$p_prevquestionsgqa=returnGlobal('prevQuestionSGQA');}

        if (!isset($p_canswers))
        {
            if (isset($_POST['canswers']) && is_array($_POST['canswers']))
            {
                foreach ($_POST['canswers'] as $key => $val)
                {
                    $p_canswers[$key]= preg_replace("/[^_.a-zA-Z0-9]@/", "", $val);
                }
            }
        }

        // this array will be used soon,
        // to explain wich conditions is used to evaluate the question
        if (Yii::app()->getConfig('stringcomparizonoperators') == 1)
        {
            $method = array(
            "<"  	=> $clang->gT("Less than"),
            "<=" 	=> $clang->gT("Less than or equal to"),
            "==" 	=> $clang->gT("equals"),
            "!=" 	=> $clang->gT("Not equal to"),
            ">=" 	=> $clang->gT("Greater than or equal to"),
            ">"  	=> $clang->gT("Greater than"),
            "RX" 	=> $clang->gT("Regular expression"),
            "a<b"  	=> $clang->gT("Less than (Strings)"),
            "a<=b" 	=> $clang->gT("Less than or equal to (Strings)"),
            "a>=b" 	=> $clang->gT("Greater than or equal to (Strings)"),
            "a>b"  	=> $clang->gT("Greater than (Strings)")
            );
        }
        else
        {
            $method = array(
            "<"  => $clang->gT("Less than"),
            "<=" => $clang->gT("Less than or equal to"),
            "==" => $clang->gT("equals"),
            "!=" => $clang->gT("Not equal to"),
            ">=" => $clang->gT("Greater than or equal to"),
            ">"  => $clang->gT("Greater than"),
            "RX" => $clang->gT("Regular expression")
            );
        }

        if (isset($_POST['method']))
        {
            if ( !in_array($_POST['method'], array_keys($method)))
            {
                $p_method = "==";
            }
            else
            {
                $p_method = trim ($_POST['method']);
            }
        }


        if (isset($_POST['newscenarionum']))
        {
            $p_newscenarionum = sanitize_int($_POST['newscenarionum']);
        }
        //END Sanitizing POSTed data

        //include_once("login_check.php");
        include_once("database.php");
        // Caution (lemeur): database.php uses autoUnescape on all entries in $_POST
        // Take care to not use autoUnescape on $_POST variables after this

        $br = CHtml::openTag('br /');

        //MAKE SURE THAT THERE IS A SID
        if (!isset($iSurveyID) || !$iSurveyID)
        {
            $conditionsoutput = $clang->gT("You have not selected a survey").str_repeat($br, 2);
            $conditionsoutput .= CHtml::submitButton($clang->gT("Main admin screen"), array(
            'onclick' => "window.open('".$this->getController()->createUrl("admin/")."', '_top')"
            )).$br;
            safeDie($conditionsoutput);
            return;
        }



        if (isset($p_subaction) && $p_subaction == "resetsurveylogic")
        {

            $clang = $this->getController()->lang;
            $resetsurveylogicoutput = $br;
            $resetsurveylogicoutput .= CHtml::openTag('table', array('class'=>'alertbox'));
            $resetsurveylogicoutput .= CHtml::openTag('tr').CHtml::openTag('td', array('colspan'=>'2'));
            $resetsurveylogicoutput .= CHtml::tag('font', array('size'=>'1'), CHtml::tag('strong', array(), $clang->gT("Reset Survey Logic")));
            $resetsurveylogicoutput .= CHtml::closeTag('td').CHtml::closeTag('tr');

            if (!isset($_GET['ok']))
            {
                $button_yes = CHtml::submitButton($clang->gT("Yes"), array(
                'onclick' => "window.open('".$this->getController()->createUrl("admin/conditions/sa/index/subaction/resetsurveylogic/surveyid/$iSurveyID")."?ok=Y"."', '_top')"
                ));
                $button_cancel = CHtml::submitButton($clang->gT("Cancel"), array(
                'onclick' => "window.open('".$this->getController()->createUrl("admin/survey/sa/view/surveyid/$iSurveyID")."', '_top')"
                ));

                $messagebox_content = $clang->gT("You are about to delete all conditions on this survey's questions")."($iSurveyID)"
                . $br . $clang->gT("We recommend that before you proceed, you export the entire survey from the main administration screen.")
                . $br . $clang->gT("Continue?")
                . $br . $button_yes . $button_cancel;

                $this->_renderWrappedTemplate('conditions', array('message' => array(
                'title' => $clang->gT("Warning"),
                'message' => $messagebox_content
                )));
                exit;
            }
            else
            {
                LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyID);
                Condition::model()->deleteRecords("qid in (select qid from {{questions}} where sid={$iSurveyID})");
                Yii::app()->session['flashmessage']=$clang->gT("All conditions in this survey have been deleted.");
                $this->getController()->redirect(array('admin/survey/sa/view/surveyid/'.$iSurveyID));

            }
        }



        // MAKE SURE THAT THERE IS A QID
        if ( !isset($qid) || !$qid )
        {
            $conditionsoutput = $clang->gT("You have not selected a question").str_repeat($br, 2);
            $conditionsoutput .= CHtml::submitButton($clang->gT("Main admin screen"), array(
            'onclick' => "window.open('".$this->getController()->createUrl("admin/")."', '_top')"
            )).$br;
            safeDie($conditionsoutput);
            return;
        }


        // If we made it this far, then lets develop the menu items
        // add the conditions container table

        $extraGetParams = "";
        if (isset($qid) && isset($gid))
        {
            $extraGetParams = "/gid/{$gid}/qid/{$qid}";
        }

        $conditionsoutput_action_error = ""; // defined during the actions

        $markcidarray = Array();
        if ( isset($_GET['markcid']) )
        {
            $markcidarray = explode("-", $_GET['markcid']);
        }

        //BEGIN PROCESS ACTIONS
        // ADD NEW ENTRY IF THIS IS AN ADD

        if (isset($p_subaction) && $p_subaction == "insertcondition")
        {
            if ((	!isset($p_canswers) &&
            !isset($_POST['ConditionConst']) &&
            !isset($_POST['prevQuestionSGQA']) &&
            !isset($_POST['tokenAttr']) &&
            !isset($_POST['ConditionRegexp'])) ||
            (!isset($p_cquestions) && !isset($p_csrctoken))
            )
            {
                $conditionsoutput_action_error .= CHtml::script("\n<!--\n alert(\"".$clang->gT("Your condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer.","js")."\")\n //-->\n");
            }
            else
            {
                if (isset($p_cquestions) && $p_cquestions != '')
                {
                    $conditionCfieldname = $p_cquestions;
                }
                elseif(isset($p_csrctoken) && $p_csrctoken != '')
                {
                    $conditionCfieldname = $p_csrctoken;
                }

                $condition_data = array(
                'qid' 			=> $qid,
                'scenario' 		=> $p_scenario,
                'cqid' 			=> $p_cqid,
                'cfieldname' 	=> $conditionCfieldname,
                'method'		=> $p_method
                );

                if (isset($p_canswers))
                {
                    foreach ($p_canswers as $ca)
                    {
                        //First lets make sure there isn't already an exact replica of this condition
                        $condition_data['value'] = $ca;

                        $result = Condition::model()->findAllByAttributes($condition_data);

                        $count_caseinsensitivedupes = count($result);

                        if ($count_caseinsensitivedupes == 0)
                        {
                            $result = Condition::model()->insertRecords($condition_data);;
                        }
                    }
                }

                unset($posted_condition_value);
                // Please note that autoUnescape is already applied in database.php included above
                // so we only need to db_quote _POST variables
                if (isset($_POST['ConditionConst']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#CONST")
                {
                    $posted_condition_value = Yii::app()->request->getPost('ConditionConst');
                }
                elseif (isset($_POST['prevQuestionSGQA']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#PREVQUESTIONS")
                {
                    $posted_condition_value = Yii::app()->request->getPost('prevQuestionSGQA');
                }
                elseif (isset($_POST['tokenAttr']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#TOKENATTRS")
                {
                    $posted_condition_value = Yii::app()->request->getPost('tokenAttr');
                }
                elseif (isset($_POST['ConditionRegexp']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#REGEXP")
                {
                    $posted_condition_value = Yii::app()->request->getPost('ConditionRegexp');
                }

                if (isset($posted_condition_value))
                {
                    $condition_data['value'] = $posted_condition_value;
                    $result = Condition::model()->insertRecords($condition_data);
                }
            }
            LimeExpressionManager::UpgradeConditionsToRelevance(NULL,$qid);
        }

        // UPDATE ENTRY IF THIS IS AN EDIT
        if (isset($p_subaction) && $p_subaction == "updatecondition")
        {
            if ((	!isset($p_canswers) &&
            !isset($_POST['ConditionConst']) &&
            !isset($_POST['prevQuestionSGQA']) &&
            !isset($_POST['tokenAttr']) &&
            !isset($_POST['ConditionRegexp'])) ||
            (!isset($p_cquestions) && !isset($p_csrctoken))
            )
            {
                $conditionsoutput_action_error .= CHtml::script("\n<!--\n alert(\"".$clang->gT("Your condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer.","js")."\")\n //-->\n");
            }
            else
            {
                if ( isset($p_cquestions) && $p_cquestions != '' )
                {
                    $conditionCfieldname = $p_cquestions;
                }
                elseif(isset($p_csrctoken) && $p_csrctoken != '')
                {
                    $conditionCfieldname = $p_csrctoken;
                }

                if ( isset($p_canswers) )
                {
                    foreach ($p_canswers as $ca)
                    {
                        // This is an Edit, there will only be ONE VALUE
                        $updated_data = array(
                        'qid' => $qid,
                        'scenario' => $p_scenario,
                        'cqid' => $p_cqid,
                        'cfieldname' => $conditionCfieldname,
                        'method' => $p_method,
                        'value' => $ca
                        );
                        $result = Condition::model()->insertRecords($updated_data, TRUE, array('cid'=>$p_cid));
                    }
                }

                unset($posted_condition_value);
                // Please note that autoUnescape is already applied in database.php included above
                // so we only need to db_quote _POST variables
                if (isset($_POST['ConditionConst']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#CONST")
                {
                    $posted_condition_value = Yii::app()->request->getPost('ConditionConst');
                }
                elseif (isset($_POST['prevQuestionSGQA']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#PREVQUESTIONS")
                {
                    $posted_condition_value = Yii::app()->request->getPost('prevQuestionSGQA');
                }
                elseif (isset($_POST['tokenAttr']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#TOKENATTRS")
                {
                    $posted_condition_value = Yii::app()->request->getPost('tokenAttr');
                }
                elseif (isset($_POST['ConditionRegexp']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#REGEXP")
                {
                    $posted_condition_value = Yii::app()->request->getPost('ConditionRegexp');
                }

                if (isset($posted_condition_value))
                {
                    $updated_data = array(
                    'qid' => $qid,
                    'scenario' => $p_scenario,
                    'cqid' => $p_cqid,
                    'cfieldname' => $conditionCfieldname,
                    'method' => $p_method,
                    'value' => $posted_condition_value
                    );
                    $result = Condition::model()->insertRecords($updated_data, TRUE, array('cid'=>$p_cid));
                }
            }
            LimeExpressionManager::UpgradeConditionsToRelevance(NULL,$qid);
        }

        // DELETE ENTRY IF THIS IS DELETE
        if (isset($p_subaction) && $p_subaction == "delete")
        {
            LimeExpressionManager::RevertUpgradeConditionsToRelevance(NULL,$qid);   // in case deleted the last condition
            $result = Condition::model()->deleteRecords(array('cid'=>$p_cid));
            LimeExpressionManager::UpgradeConditionsToRelevance(NULL,$qid);
        }

        // DELETE ALL CONDITIONS IN THIS SCENARIO
        if (isset($p_subaction) && $p_subaction == "deletescenario")
        {
            LimeExpressionManager::RevertUpgradeConditionsToRelevance(NULL,$qid);   // in case deleted the last condition
            $result = Condition::model()->deleteRecords(array('qid'=>$qid, 'scenario'=>$p_scenario));
            LimeExpressionManager::UpgradeConditionsToRelevance(NULL,$qid);
        }

        // UPDATE SCENARIO
        if (isset($p_subaction) && $p_subaction == "updatescenario" && isset($p_newscenarionum))
        {
            $result = Condition::model()->insertRecords(array('scenario'=>$p_newscenarionum), TRUE, array(
            'qid'=>$qid, 'scenario'=>$p_scenario));
            LimeExpressionManager::UpgradeConditionsToRelevance(NULL,$qid);
        }

        // DELETE ALL CONDITIONS FOR THIS QUESTION
        if (isset($p_subaction) && $p_subaction == "deleteallconditions")
        {
            LimeExpressionManager::RevertUpgradeConditionsToRelevance(NULL,$qid);   // in case deleted the last condition
            $result = Condition::model()->deleteRecords(array('qid'=>$qid));
        }

        // RENUMBER SCENARIOS
        if (isset($p_subaction) && $p_subaction == "renumberscenarios")
        {
            $query = "SELECT DISTINCT scenario FROM {{conditions}} WHERE qid=:qid ORDER BY scenario";
            $result = Yii::app()->db->createCommand($query)->bindParam(":qid", $qid, PDO::PARAM_INT)->query() or safeDie ("Couldn't select scenario<br />$query<br />");
            $newindex = 1;

            foreach ($result->readAll() as $srow)
            {
                // new var $update_result == old var $result2
                $update_result = Condition::model()->insertRecords(array('scenario'=>$newindex), TRUE,
                array( 'qid'=>$qid, 'scenario'=>$srow['scenario'] )
                );
                $newindex++;
            }
            LimeExpressionManager::UpgradeConditionsToRelevance(NULL,$qid);
            Yii::app()->session['flashmessage'] = $clang->gT("All conditions scenarios were renumbered.");



        }

        // COPY CONDITIONS IF THIS IS COPY
        if ( isset($p_subaction) && $p_subaction == "copyconditions" )
        {

            $qid = returnGlobal('qid');
            $copyconditionsfrom = returnGlobal('copyconditionsfrom');
            $copyconditionsto = returnGlobal('copyconditionsto');
            if (isset($copyconditionsto) && is_array($copyconditionsto) && isset($copyconditionsfrom) && is_array($copyconditionsfrom))
            {
                //Get the conditions we are going to copy
                foreach($copyconditionsfrom as &$entry)
                    $entry = Yii::app()->db->quoteValue($entry);
                $query = "SELECT * FROM {{conditions}}\n"
                ."WHERE cid in (";
                $query .= implode(", ", $copyconditionsfrom);
                $query .= ")";
                $result = Yii::app()->db->createCommand($query)->query() or
                safeDie("Couldn't get conditions for copy<br />$query<br />");

                foreach ($result->readAll() as $row)
                {
                    $proformaconditions[] = array(
                    "scenario"		=>	$row['scenario'],
                    "cqid"			=>	$row['cqid'],
                    "cfieldname"	=>	$row['cfieldname'],
                    "method"		=>	$row['method'],
                    "value"			=>	$row['value']
                    );
                } // while

                foreach ($copyconditionsto as $copyc)
                {
                    list($newsid, $newgid, $newqid)=explode("X", $copyc);
                    foreach ($proformaconditions as $pfc)
                    { //TIBO

                        //First lets make sure there isn't already an exact replica of this condition
                        $conditions_data = array(
                        'qid' 			=> 	$newqid,
                        'scenario' 		=> 	$pfc['scenario'],
                        'cqid' 			=> 	$pfc['cqid'],
                        'cfieldname' 	=> 	$pfc['cfieldname'],
                        'method' 		=>	$pfc['method'],
                        'value' 		=> 	$pfc['value']
                        );

                        $result = Condition::model()->findAllByAttributes($conditions_data);

                        $count_caseinsensitivedupes = count($result);

                        $countduplicates = 0;
                        if ($count_caseinsensitivedupes != 0)
                        {
                            foreach ($result as $ccrow)
                            {
                                if ($ccrow['value'] == $pfc['value']) $countduplicates++;
                            }
                        }

                        if ($countduplicates == 0) //If there is no match, add the condition.
                        {
                            $result = Condition::model()->insertRecords($conditions_data);
                            $conditionCopied = true;
                        }
                        else
                        {
                            $conditionDuplicated = true;
                        }
                    }
                }

                if (isset($conditionCopied) && $conditionCopied === true)
                {
                    if (isset($conditionDuplicated) && $conditionDuplicated ==true)
                    {
                        $CopyConditionsMessage = CHtml::tag('div', array('class'=>'partialheader'),
                        '('.$clang->gT("Condition successfully copied (some were skipped because they were duplicates)").')'
                        );
                    }
                    else
                    {
                        $CopyConditionsMessage = CHtml::tag('div', array('class'=>'successheader'),
                        '('.$clang->gT("Condition successfully copied").')'
                        );
                    }
                }
                else
                {
                    $CopyConditionsMessage = CHtml::tag('div', array('class'=>'warningheader'),
                    '('.$clang->gT("No conditions could be copied (due to duplicates)").')'
                    );
                }
            }
            LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyID); // do for whole survey, since don't know which questions affected.
        }
        //END PROCESS ACTIONS

        $cquestions = Array();
        $canswers 	= Array();

        //BEGIN: GATHER INFORMATION
        // 1: Get information for this question
        // @todo : use viewHelper::getFieldText and getFieldCode for 2.06 for string show to user
        if (!isset($qid)) { $qid = returnGlobal('qid'); }
        if (!isset($iSurveyID)) { $iSurveyID = returnGlobal('sid'); }
        $thissurvey = getSurveyInfo($iSurveyID);

        $qresult = Question::model()->with('groups')->findByAttributes(array('qid' => $qid, 'parent_qid' => 0, 'language' => Survey::model()->findByPk($iSurveyID)->language));
        $questiongroupname = $qresult->groups->group_name;
        $questiontitle = $qresult['title'];
        $questiontext = $qresult['question'];
        $questiontype = $qresult['type'];

        // 2: Get all other questions that occur before this question that are pre-determined answer types

        // To avoid natural sort order issues,
        // first get all questions in natural sort order
        // , and find out which number in that order this question is
        $qresult = Question::model()->with(array(
        'groups' => array(
        'condition' => 'groups.language = :lang',
        'params' => array(':lang' => Survey::model()->findByPk($iSurveyID)->language),
        ),
        ))->findAllByAttributes(array('parent_qid' => 0, 'sid' => $iSurveyID, 'language' => Survey::model()->findByPk($iSurveyID)->language));
        $qrows = array();
        foreach ($qresult as $k => $v)
            $qrows[$k] = array_merge($v->attributes, $v->groups->attributes);
        // Perform a case insensitive natural sort on group name then question title (known as "code" in the form) of a multidimensional array
        usort($qrows, 'groupOrderThenQuestionOrder');

        $position="before";
        // Go through each question until we reach the current one
        foreach ($qrows as $qrow)
        {
            if ($qrow["qid"] != $qid && $position=="before")
            {
                // remember all previous questions
                // all question types are supported.
                $questionlist[]=$qrow["qid"];
            }
            elseif ($qrow["qid"] == $qid)
            {
                break;
            }
        }

        // Now, using the same array which is now properly sorted by group then question
        // Create an array of all the questions that appear AFTER the current one
        $position = "before";
        foreach ($qrows as $qrow) //Go through each question until we reach the current one
        {
            if ( $qrow["qid"] == $qid )
            {
                $position = "after";
                //break;
            }
            elseif ($qrow["qid"] != $qid && $position=="after")
            {
                $postquestionlist[] = $qrow['qid'];
            }
        }

        $theserows = array();
        $postrows  = array();

        if (isset($questionlist) && is_array($questionlist))
        {
            foreach ($questionlist as $ql)
            {

                $result = Question::model()->with(array(
                'groups' => array(
                'condition' => 'groups.language = :lang',
                'params' => array(':lang' => Survey::model()->findByPk($iSurveyID)->language),
                ),
                ))->findAllByAttributes(array('qid' => $ql, 'parent_qid' => 0, 'sid' => $iSurveyID, 'language' => Survey::model()->findByPk($iSurveyID)->language));

                $thiscount = count($result);

                // And store again these questions in this array...
                foreach ($result as $myrows)
                {                   //key => value
                    $theserows[] = array(
                    "qid"		=>	$myrows['qid'],
                    "sid"		=>	$myrows['sid'],
                    "gid"		=>	$myrows['gid'],
                    "question"	=>	$myrows['question'],
                    "type"		=>	$myrows['type'],
                    "mandatory"	=>	$myrows['mandatory'],
                    "other"		=>	$myrows['other'],
                    "title"		=>	$myrows['title']
                    );
                }
            }
        }

        if (isset($postquestionlist) && is_array($postquestionlist))
        {
            foreach ($postquestionlist as $pq)
            {
                $result = Question::model()->with(array(
                'groups' => array(
                'condition' => 'groups.language = :lang',
                'params' => array(':lang' => Survey::model()->findByPk($iSurveyID)->language),
                ),
                ))->findAllByAttributes(array('qid' => $pq, 'parent_qid' => 0, 'sid' => $iSurveyID, 'language' => Survey::model()->findByPk($iSurveyID)->language));

                $postcount = count($result);

                foreach ($result as $myrows)
                {
                    $postrows[]=array(
                    "qid"		=>	$myrows['qid'],
                    "sid"		=>	$myrows['sid'],
                    "gid"		=>	$myrows['gid'],
                    "question"	=>	$myrows['question'],
                    "type"		=>	$myrows['type'],
                    "mandatory"	=>	$myrows['mandatory'],
                    "other"		=>	$myrows['other'],
                    "title"		=>	$myrows['title']
                    );
                } // while
            }
            $postquestionscount=count($postrows);
        }

        $questionscount=count($theserows);

        if (isset($postquestionscount) && $postquestionscount > 0)
        { //Build the array used for the questionNav and copyTo select boxes
            foreach ($postrows as $pr)
            {
                $pquestions[]  =array("text" => $pr['title'].": ".substr(strip_tags($pr['question']), 0, 80),
                "fieldname" => $pr['sid']."X".$pr['gid']."X".$pr['qid']);
            }
        }

        // Previous question parsing ==> building cquestions[] and canswers[]
        if ($questionscount > 0)
        {
            $X = "X";

            foreach($theserows as $rows)
            {
                $shortquestion=$rows['title'].": ".strip_tags($rows['question']);

                if ($rows['type'] == "A" ||
                $rows['type'] == "B" ||
                $rows['type'] == "C" ||
                $rows['type'] == "E" ||
                $rows['type'] == "F" ||
                $rows['type'] == "H"
                )
                {
                    $aresult = Question::model()->findAllByAttributes(array('parent_qid'=>$rows['qid'], 'language' => Survey::model()->findByPk($iSurveyID)->language), array('order' => 'question_order ASC'));

                    foreach ($aresult as $arows)
                    {
                        $shortanswer = "{$arows['title']}: [" . flattenText($arows['question']) . "]";
                        $shortquestion = $rows['title'].":$shortanswer ".flattenText($rows['question']);
                        $cquestions[] = array( $shortquestion, $rows['qid'], $rows['type'],
                        $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title']
                        );

                        switch ($rows['type'])
                        {
                            case "A": //Array 5 buttons
                                for ($i=1; $i<=5; $i++)
                                {
                                    $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title'], $i, $i);
                                }
                                break;
                            case "B": //Array 10 buttons
                                for ($i=1; $i<=10; $i++)
                                {
                                    $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title'], $i, $i);
                                }
                                break;
                            case "C": //Array Y/N/NA
                                $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title'], "Y", $clang->gT("Yes"));
                                $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title'], "U", $clang->gT("Uncertain"));
                                $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title'], "N", $clang->gT("No"));
                                break;
                            case "E": //Array >/=/<
                                $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title'], "I", $clang->gT("Increase"));
                                $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title'], "S", $clang->gT("Same"));
                                $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title'], "D", $clang->gT("Decrease"));
                                break;
                            case "F": //Array Flexible Row
                            case "H": //Array Flexible Column

                                $fresult = Answer::model()->findAllByAttributes(array(
                                'qid' => $rows['qid'],
                                "language" => Survey::model()->findByPk($iSurveyID)->language,
                                'scale_id' => 0,
                                ), array('order' => 'sortorder, code'));

                                foreach ($fresult as $frow)
                                {
                                    $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title'], $frow['code'], $frow['answer']);
                                }
                                break;
                        }
                        // Only Show No-Answer if question is not mandatory
                        if ($rows['mandatory'] != 'Y')
                        {
                            $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title'], "", $clang->gT("No answer"));
                        }

                    } //while
                }
                elseif ($rows['type'] == ":" || $rows['type'] == ";")
                { // Multiflexi

                    //Get question attribute for $canswers
                    $qidattributes=getQuestionAttributeValues($rows['qid'], $rows['type']);
                    if (isset($qidattributes['multiflexible_max']) && trim($qidattributes['multiflexible_max'])!='') {
                        $maxvalue=floatval($qidattributes['multiflexible_max']);
                    } else {
                        $maxvalue=10;
                    }
                    if (isset($qidattributes['multiflexible_min']) && trim($qidattributes['multiflexible_min'])!='') {
                        $minvalue=floatval($qidattributes['multiflexible_min']);
                    } else {
                        $minvalue=1;
                    }
                    if (isset($qidattributes['multiflexible_step']) && trim($qidattributes['multiflexible_step'])!='') {
                        $stepvalue=floatval($qidattributes['multiflexible_step']);
                        if ($stepvalue==0) $stepvalue=1;
                    } else {
                        $stepvalue=1;
                    }

                    if (isset($qidattributes['multiflexible_checkbox']) && $qidattributes['multiflexible_checkbox']!=0) {
                        $minvalue=0;
                        $maxvalue=1;
                        $stepvalue=1;
                    }
                    // Get the Y-Axis

                    $fquery = "SELECT sq.*, q.other"
                    ." FROM {{questions sq}}, {{questions q}}"
                    ." WHERE sq.sid=$iSurveyID AND sq.parent_qid=q.qid "
                    . "AND q.language=:lang1"
                    ." AND sq.language=:lang2"
                    ." AND q.qid=:qid
                    AND sq.scale_id=0
                    ORDER BY sq.question_order";
                    $sLanguage=Survey::model()->findByPk($iSurveyID)->language;
                    $y_axis_db = Yii::app()->db->createCommand($fquery)
                        ->bindParam(":lang1", $sLanguage, PDO::PARAM_STR)
                        ->bindParam(":lang2", $sLanguage, PDO::PARAM_STR)
                        ->bindParam(":qid", $rows['qid'], PDO::PARAM_INT)
                        ->query();

                    // Get the X-Axis
                    $aquery = "SELECT sq.*
                    FROM {{questions q}}, {{questions sq}}
                    WHERE q.sid=$iSurveyID
                    AND sq.parent_qid=q.qid
                    AND q.language=:lang1
                    AND sq.language=:lang2
                    AND q.qid=:qid
                    AND sq.scale_id=1
                    ORDER BY sq.question_order";

                    $x_axis_db=Yii::app()->db->createCommand($aquery)
                        ->bindParam(":lang1", $sLanguage, PDO::PARAM_STR)
                        ->bindParam(":lang2", $sLanguage, PDO::PARAM_STR)
                        ->bindParam(":qid", $rows['qid'], PDO::PARAM_INT)
                        ->query() or safeDie ("Couldn't get answers to Array questions<br />$aquery<br />");

                    foreach ($x_axis_db->readAll() as $frow)
                    {
                        $x_axis[$frow['title']]=$frow['question'];
                    }

                    foreach ($y_axis_db->readAll() as $yrow)
                    {
                        foreach($x_axis as $key=>$val)
                        {
                            $shortquestion=$rows['title'].":{$yrow['title']}:$key: [".strip_tags($yrow['question']). "][" .strip_tags($val). "] " . flattenText($rows['question']);
                            $cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$yrow['title']."_".$key);

                            if ($rows['type'] == ":")
                            {
                                for($ii=$minvalue; $ii<=$maxvalue; $ii+=$stepvalue)
                                {
                                    $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$yrow['title']."_".$key, $ii, $ii);
                                }
                            }
                        }
                    }
                    unset($x_axis);
                } //if A,B,C,E,F,H
                elseif ($rows['type'] == "1") //Multi Scale
                {
                    $aresult = Question::model()->findAllByAttributes(array('parent_qid' => $rows['qid'], 'language' => Survey::model()->findByPk($iSurveyID)->language), array('order' => 'question_order desc'));

                    foreach ($aresult as $arows)
                    {
                        $attr = getQuestionAttributeValues($rows['qid']);
                        $sLanguage=Survey::model()->findByPk($iSurveyID)->language;
                        // dualscale_header are allways set, but can be empty
                        $label1 = empty($attr['dualscale_headerA'][$sLanguage]) ? gt('Scale 1') : $attr['dualscale_headerA'][$sLanguage];
                        $label2 = empty($attr['dualscale_headerB'][$sLanguage]) ? gt('Scale 2') : $attr['dualscale_headerB'][$sLanguage];
                        $shortanswer = "{$arows['title']}: [" . strip_tags($arows['question']) . "][$label1]";
                        $shortquestion = $rows['title'].":$shortanswer ".strip_tags($rows['question']);
                        $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title']."#0");

                        $shortanswer = "{$arows['title']}: [" . strip_tags($arows['question']) . "][$label2]";
                        $shortquestion = $rows['title'].":$shortanswer ".strip_tags($rows['question']);
                        $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title']."#1");

                        // first label
                        $lresult = Answer::model()->findAllByAttributes(array('qid' => $rows['qid'], 'scale_id' => 0, 'language' => Survey::model()->findByPk($iSurveyID)->language), array('order' => 'sortorder, answer'));
                        foreach ($lresult as $lrows)
                        {
                            $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title']."#0", "{$lrows['code']}", "{$lrows['code']}");
                        }

                        // second label
                        $lresult = Answer::model()->findAllByAttributes(array(
                        'qid' => $rows['qid'],
                        'scale_id' => 1,
                        'language' => Survey::model()->findByPk($iSurveyID)->language,
                        ), array('order' => 'sortorder, answer'));

                        foreach ($lresult as $lrows)
                        {
                            $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title']."#1", "{$lrows['code']}", "{$lrows['code']}");
                        }

                        // Only Show No-Answer if question is not mandatory
                        if ($rows['mandatory'] != 'Y')
                        {
                            $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title']."#0", "", $clang->gT("No answer"));
                            $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title']."#1", "", $clang->gT("No answer"));
                        }
                    } //while
                }
                elseif ($rows['type'] == "K" ||$rows['type'] == "Q") //Multi shorttext/numerical
                {
                    $aresult = Question::model()->findAllByAttributes(array(
                    "parent_qid" => $rows['qid'],
                    "language" =>Survey::model()->findByPk($iSurveyID)->language,
                    ), array('order' => 'question_order desc'));

                    foreach ($aresult as $arows)
                    {
                        $shortanswer = "{$arows['title']}: [" . strip_tags($arows['question']) . "]";
                        $shortquestion=$rows['title'].":$shortanswer ".strip_tags($rows['question']);
                        $cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title']);

                        // Only Show No-Answer if question is not mandatory
                        if ($rows['mandatory'] != 'Y')
                        {
                            $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title'], "", $clang->gT("No answer"));
                        }

                    } //while
                }
                elseif ($rows['type'] == "R") //Answer Ranking
                {
                    $aresult = Answer::model()->findAllByAttributes(array(
                    "qid" => $rows['qid'],
                    "scale_id" => 0,
                    "language" => Survey::model()->findByPk($iSurveyID)->language,
                    ), array('order' => 'sortorder, answer'));

                    $acount = count($aresult);
                    foreach ($aresult as $arow)
                    {
                        $theanswer = addcslashes($arow['answer'], "'");
                        $quicky[]=array($arow['code'], $theanswer);
                    }
                    for ($i=1; $i<=$acount; $i++)
                    {
                        $cquestions[]=array("{$rows['title']}: [RANK $i] ".strip_tags($rows['question']), $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i);
                        foreach ($quicky as $qck)
                        {
                            $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i, $qck[0], $qck[1]);
                        }
                        // Only Show No-Answer if question is not mandatory
                        if ($rows['mandatory'] != 'Y')
                        {
                            $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i, " ", $clang->gT("No answer"));
                        }
                    }
                    unset($quicky);
                } // End if type R
                elseif($rows['type'] == "M" || $rows['type'] == "P")
                {
                    $shortanswer = " [".$clang->gT("Group of checkboxes")."]";
                    $shortquestion = $rows['title'].":$shortanswer ".strip_tags($rows['question']);
                    $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid']);

                    $aresult = Question::model()->findAllByAttributes(array(
                    "parent_qid" => $rows['qid'],
                    "language" => Survey::model()->findByPk($iSurveyID)->language,
                    ), array('order' => 'question_order desc'));

                    foreach ($aresult as $arows)
                    {
                        $theanswer = addcslashes($arows['question'], "'");
                        $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], $arows['title'], $theanswer);

                        $shortanswer = "{$arows['title']}: [" . strip_tags($arows['question']) . "]";
                        $shortanswer .= "[".$clang->gT("Single checkbox")."]";
                        $shortquestion=$rows['title'].":$shortanswer ".strip_tags($rows['question']);
                        $cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], "+".$rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title']);
                        $canswers[]=array("+".$rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title'], 'Y', $clang->gT("checked"));
                        $canswers[]=array("+".$rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title'], '', $clang->gT("not checked"));
                    }
                }
                elseif($rows['type'] == "X") //Boilerplate question
                {
                    //Just ignore this questiontype
                }
                else
                {
                    $cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid']);
                    switch ($rows['type'])
                    {
                        case "Y": // Y/N/NA
                            $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "Y", $clang->gT("Yes"));
                            $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "N", $clang->gT("No"));
                            // Only Show No-Answer if question is not mandatory
                            if ($rows['mandatory'] != 'Y')
                            {
                                $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
                            }
                            break;
                        case "G": //Gender
                            $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "F", $clang->gT("Female"));
                            $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "M", $clang->gT("Male"));
                            // Only Show No-Answer if question is not mandatory
                            if ($rows['mandatory'] != 'Y')
                            {
                                $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
                            }
                            break;
                        case "5": // 5 choice
                            for ($i=1; $i<=5; $i++)
                            {
                                $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], $i, $i);
                            }
                            // Only Show No-Answer if question is not mandatory
                            if ($rows['mandatory'] != 'Y')
                            {
                                $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
                            }
                            break;

                        case "N": // Simple Numerical questions

                            // Only Show No-Answer if question is not mandatory
                            if ($rows['mandatory'] != 'Y')
                            {
                                $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
                            }
                            break;

                        default:

                            $aresult = Answer::model()->findAllByAttributes(array(
                            'qid' => $rows['qid'],
                            'scale_id' => 0,
                            'language' => Survey::model()->findByPk($iSurveyID)->language,
                            ), array('order' => 'sortorder, answer'));

                            foreach ($aresult as $arows)
                            {
                                $theanswer = addcslashes($arows['answer'], "'");
                                $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], $arows['code'], $theanswer);
                            }
                            if ($rows['type'] == "D")
                            {
                                // Only Show No-Answer if question is not mandatory
                                if ($rows['mandatory'] != 'Y')
                                {
                                    $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
                                }
                            }
                            elseif ($rows['type'] != "M" &&
                            $rows['type'] != "P" &&
                            $rows['type'] != "J" &&
                            $rows['type'] != "I" )
                            {
                                // For dropdown questions
                                // optinnaly add the 'Other' answer
                                if ( (	$rows['type'] == "L" ||
                                $rows['type'] == "!") &&
                                $rows['other'] == "Y" )
                                {
                                    $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "-oth-", $clang->gT("Other"));
                                }

                                // Only Show No-Answer if question is not mandatory
                                if ($rows['mandatory'] != 'Y')
                                {
                                    $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
                                }
                            }
                            break;
                    }//switch row type
                } //else
            } //foreach theserows
        } //if questionscount > 0
        //END Gather Information for this question


        $questionNavOptions = CHtml::openTag('optgroup', array('class'=>'activesurveyselect', 'label'=>$clang->gT("Before","js")));
        foreach ($theserows as $row)
        {
            $question=$row['question'];
            $question=strip_tags($question);
            if (strlen($question)<35)
            {
                $questionselecter = $question;
            }
            else
            {
                //$questionselecter = substr($question, 0, 35)."..";
                $questionselecter = htmlspecialchars(mb_strcut(html_entity_decode($question,ENT_QUOTES,'UTF-8'), 0, 35, 'UTF-8'))."...";
            }

            $questionNavOptions .= CHtml::tag('option', array(
            'value' => $this->getController()->createUrl("/admin/conditions/sa/index/subaction/editconditionsform/surveyid/$iSurveyID/gid/{$row['gid']}/qid/{$row['qid']}")),
            strip_tags($row['title']).':'.$questionselecter
            );
        }
        $questionNavOptions .= CHtml::closeTag('optgroup');
        $questionNavOptions .= CHtml::openTag('optgroup', array('class'=>'activesurveyselect', 'label'=>$clang->gT("Current","js")));
        $question = strip_tags($questiontext);
        if (strlen($question)<35)
        {
            $questiontextshort = $question;
        }
        else
        {
            //$questiontextshort = substr($question, 0, 35)."..";
            $questiontextshort = htmlspecialchars(mb_strcut(html_entity_decode($question,ENT_QUOTES,'UTF-8'), 0, 35, 'UTF-8'))."...";
        }

        $questionNavOptions .= CHtml::tag('option', array(
        'value'=>$this->getController()->createUrl("/admin/conditions/sa/index/subaction/editconditionsform/surveyid/$iSurveyID/gid/$gid/qid/$qid"),
        'selected'=>'selected'),
        $questiontitle .': '. $questiontextshort);
        $questionNavOptions .= CHtml::closeTag('optgroup');
        $questionNavOptions .= CHtml::openTag('optgroup', array('class'=> 'activesurveyselect', 'label'=>$clang->gT("After","js")));

        foreach ($postrows as $row)
        {
            $question=$row['question'];
            $question=strip_tags($question);
            if (strlen($question)<35)
            {
                $questionselecter = $question;
            }
            else
            {
                //$questionselecter = substr($question, 0, 35)."..";
                $questionselecter = htmlspecialchars(mb_strcut(html_entity_decode($question,ENT_QUOTES,'UTF-8'), 0, 35, 'UTF-8'))."...";
            }
            $questionNavOptions .=  CHtml::tag('option', array(
            'value' => $this->getController()->createUrl("/admin/conditions/sa/index/subaction/editconditionsform/surveyid/$iSurveyID/gid/{$row['gid']}/qid/{$row['qid']}")),
            strip_tags($row['title']).':'.$questionselecter
            );
        }
        $questionNavOptions .= CHtml::closeTag('optgroup');

        //Now display the information and forms
        //BEGIN: PREPARE JAVASCRIPT TO SHOW MATCHING ANSWERS TO SELECTED QUESTION
        $javascriptpre = CHtml::openTag('script', array('type' => 'text/javascript'))
        . "<!--\n"
        . "\tvar Fieldnames = new Array();\n"
        . "\tvar Codes = new Array();\n"
        . "\tvar Answers = new Array();\n"
        . "\tvar QFieldnames = new Array();\n"
        . "\tvar Qcqids = new Array();\n"
        . "\tvar Qtypes = new Array();\n";

        $jn = 0;
        if ( isset($canswers) )
        {
            foreach($canswers as $can)
            {
                $an = ls_json_encode(flattenText($can[2]));
                $javascriptpre .= "Fieldnames[$jn]='$can[0]';\n"
                . "Codes[$jn]='$can[1]';\n"
                . "Answers[$jn]={$an};\n";
                $jn++;
            }
        }

        $jn = 0;
        if ( isset($cquestions) )
        {
            foreach ($cquestions as $cqn)
            {
                $javascriptpre .= "QFieldnames[$jn]='$cqn[3]';\n"
                ."Qcqids[$jn]='$cqn[1]';\n"
                ."Qtypes[$jn]='$cqn[2]';\n";
                $jn++;
            }
        }

        //  record a JS variable to let jQuery know if survey is Anonymous
        if ($thissurvey['anonymized'] == 'Y')
        {
            $javascriptpre .= "isAnonymousSurvey = true;";
        }
        else
        {
            $javascriptpre .= "isAnonymousSurvey = false;";
        }

        $javascriptpre .= "//-->\n"
        .CHtml::closeTag('script');

        //END: PREPARE JAVASCRIPT TO SHOW MATCHING ANSWERS TO SELECTED QUESTION

        $aViewUrls = array();

        $aData['clang'] = $clang;
        $aData['surveyid'] = $iSurveyID;
        $aData['qid'] = $qid;
        $aData['gid'] = $gid;
        $aData['imageurl'] = $imageurl;
        $aData['extraGetParams'] = $extraGetParams;
        $aData['quesitonNavOptions'] = $questionNavOptions;
        $aData['conditionsoutput_action_error'] = $conditionsoutput_action_error;
        $aData['javascriptpre'] = $javascriptpre;

        $aViewUrls['conditionshead_view'][] = $aData;

        //BEGIN DISPLAY CONDITIONS FOR THIS QUESTION
        if (	$subaction == 'index' ||
        $subaction == 'editconditionsform' || $subaction == 'insertcondition' ||
        $subaction == "editthiscondition" || $subaction == "delete" ||
        $subaction == "updatecondition" || $subaction == "deletescenario" ||
        $subaction == "renumberscenarios" || $subaction == "deleteallconditions" ||
        $subaction == "updatescenario" ||
        $subaction == 'copyconditionsform' || $subaction == 'copyconditions' || $subaction == 'conditions'
        )
        {

            //3: Get other conditions currently set for this question
            $conditionscount = 0;
            $s=0;
            $criteria=new CDbCriteria;
            $criteria->select='scenario';  // only select the 'scenario' column
            $criteria->condition='qid=:qid';
            $criteria->params=array(':qid'=>$qid);
            $criteria->order='scenario';
            $criteria->group='scenario';

            $scenarioresult = Condition::model()->findAll($criteria);
            $scenariocount=count($scenarioresult);

            $showreplace="$questiontitle". $this->_showSpeaker($questiontext);
            $onlyshow=sprintf($clang->gT("Only show question %s IF"),$showreplace);

            $aData['conditionsoutput'] = '';
            $aData['extraGetParams'] = $extraGetParams;
            $aData['quesitonNavOptions'] = $questionNavOptions;
            $aData['conditionsoutput_action_error'] = $conditionsoutput_action_error;
            $aData['javascriptpre'] = $javascriptpre;
            $aData['onlyshow'] = $onlyshow;
            $aData['subaction'] = $subaction;
            $aData['scenariocount'] = $scenariocount;

            $aViewUrls['conditionslist_view'][] = $aData;

            if ($scenariocount > 0)
            {

                App()->getClientScript()->registerScriptFile(Yii::app()->getConfig("adminscripts").'checkgroup.js');
                foreach ($scenarioresult as $scenarionr)
                {
                    $scenariotext = "";
                    if ($s == 0 && $scenariocount > 1)
                    {
                        $scenariotext = " -------- <i>Scenario {$scenarionr['scenario']}</i> --------";
                    }
                    if ($s > 0)
                    {
                        $scenariotext = " -------- <i>".$clang->gT("OR")." Scenario {$scenarionr['scenario']}</i> --------";
                    }
                    if ($subaction == "copyconditionsform" || $subaction == "copyconditions")
                    {
                        $initialCheckbox = "<td><input type='checkbox' id='scenarioCbx{$scenarionr['scenario']}' checked='checked'/>\n"
                        ."<script type='text/javascript'>$(document).ready(function () { $('#scenarioCbx{$scenarionr['scenario']}').checkgroup({ groupName:'aConditionFromScenario{$scenarionr['scenario']}'}); });</script>"
                        ."</td><td>&nbsp;</td>\n";
                    }
                    else
                    {
                        $initialCheckbox = "";
                    }

                    if (	$scenariotext != "" && ($subaction == "editconditionsform" || $subaction == "insertcondition" ||
                    $subaction == "updatecondition" || $subaction == "editthiscondition" ||
                    $subaction == "renumberscenarios" || $subaction == "updatescenario" ||
                    $subaction == "deletescenario" || $subaction == "delete")
                    )
                    {
                        $img_tag = CHtml::image($imageurl.'/scenario_delete.png', $clang->gT("Delete this scenario"), array(
                        'name'=>'DeleteWholeGroup'
                        ));
                        $additional_main_content = CHtml::link($img_tag, '#', array(
                        'onclick' 	=> 	"if ( confirm('".$clang->gT("Are you sure you want to delete all conditions set in this scenario?", "js")."')) { document.getElementById('deletescenario{$scenarionr['scenario']}').submit();}"
                        ));

                        $img_tag = CHtml::image($imageurl.'/scenario_edit.png', $clang->gT("Edit scenario"), array(
                        'name'=>'DeleteWholeGroup'
                        ));
                        $additional_main_content .= CHtml::link($img_tag, '#', array(
                        'id' 		=> 	'editscenariobtn'.$scenarionr['scenario'],
                        'onclick' 	=> 	"$('#editscenario{$scenarionr['scenario']}').toggle('slow');"
                        ));

                        $aData['additional_content'] = $additional_main_content;
                    }

                    $aData['initialCheckbox'] = $initialCheckbox;
                    $aData['scenariotext'] = $scenariotext;
                    $aData['scenarionr'] = $scenarionr;
                    if (!isset($aViewUrls['output'])) $aViewUrls['output']='';
                    $aViewUrls['output'] .= $this->getController()->renderPartial('/admin/conditions/includes/conditions_scenario',
                    $aData, TRUE);

                    unset($currentfield);

                    $query = "SELECT count(*) as recordcount
                    FROM {{conditions}} c, {{questions}} q, {{groups}} g
                    WHERE c.cqid=q.qid "
                    ."AND q.gid=g.gid "
                    ."AND q.parent_qid=0 "
                    ."AND q.language=:lang1 "
                    ."AND g.language=:lang2 "
                    ."AND c.qid=:qid "
                    ."AND c.scenario=:scenario "
                    ."AND c.cfieldname NOT LIKE '{%' "; // avoid catching SRCtokenAttr conditions
                    $sLanguage=Survey::model()->findByPk($iSurveyID)->language;
                    $result=Yii::app()->db->createCommand($query)
                    ->bindValue(":scenario", $scenarionr['scenario'])
                    ->bindValue(":qid", $qid, PDO::PARAM_INT)
                    ->bindValue(":lang1", $sLanguage, PDO::PARAM_STR)
                    ->bindValue(":lang2", $sLanguage, PDO::PARAM_STR)
                    ->queryRow();
                    $conditionscount=(int)$result['recordcount'];
                    $query = "SELECT c.cid, c.scenario, c.cqid, c.cfieldname, c.method, c.value, q.type
                    FROM {{conditions}} c, {{questions}} q, {{groups}} g
                    WHERE c.cqid=q.qid "
                    ."AND q.gid=g.gid "
                    ."AND q.parent_qid=0 "
                    ."AND q.language=:lang1 "
                    ."AND g.language=:lang2 "
                    ."AND c.qid=:qid "
                    ."AND c.scenario=:scenario "
                    ."AND c.cfieldname NOT LIKE '{%' " // avoid catching SRCtokenAttr conditions
                    ."ORDER BY g.group_order, q.question_order, c.cfieldname";
                    $sLanguage=Survey::model()->findByPk($iSurveyID)->language;
                    $result=Yii::app()->db->createCommand($query)
                    ->bindValue(":scenario", $scenarionr['scenario'])
                    ->bindValue(":qid", $qid, PDO::PARAM_INT)
                    ->bindValue(":lang1", $sLanguage, PDO::PARAM_STR)
                    ->bindValue(":lang2", $sLanguage, PDO::PARAM_STR)
                    ->query() or safeDie ("Couldn't get other conditions for question $qid<br />$query<br />");

                    $querytoken = "SELECT count(*) as recordcount "
                    ."FROM {{conditions}} "
                    ."WHERE "
                    ." {{conditions}}.qid=:qid "
                    ."AND {{conditions}}.scenario=:scenario "
                    ."AND {{conditions}}.cfieldname LIKE '{%' "; // only catching SRCtokenAttr conditions
                    $resulttoken = Yii::app()->db->createCommand($querytoken)
                    ->bindValue(":scenario", $scenarionr['scenario'], PDO::PARAM_INT)
                    ->bindValue(":qid", $qid, PDO::PARAM_INT)
                    ->queryRow() or safeDie ("Couldn't get other conditions for question $qid<br />$query<br />");
                    $conditionscounttoken=(int)$resulttoken['recordcount'];

                    $querytoken = "SELECT {{conditions}}.cid, "
                    ."{{conditions}}.scenario, "
                    ."{{conditions}}.cqid, "
                    ."{{conditions}}.cfieldname, "
                    ."{{conditions}}.method, "
                    ."{{conditions}}.value, "
                    ."'' AS type "
                    ."FROM {{conditions}} "
                    ."WHERE "
                    ." {{conditions}}.qid=:qid "
                    ."AND {{conditions}}.scenario=:scenario "
                    ."AND {{conditions}}.cfieldname LIKE '{%' " // only catching SRCtokenAttr conditions
                    ."ORDER BY {{conditions}}.cfieldname";
                    $resulttoken = Yii::app()->db->createCommand($querytoken)
                    ->bindValue(":scenario", $scenarionr['scenario'], PDO::PARAM_INT)
                    ->bindValue(":qid", $qid, PDO::PARAM_INT)
                    ->query() or safeDie ("Couldn't get other conditions for question $qid<br />$query<br />");

                    $conditionscount=$conditionscount+$conditionscounttoken;

                    if ($conditionscount > 0)
                    {
                        $aConditionsMerged=Array();
                        foreach ($resulttoken->readAll() as $arow)
                        {
                            $aConditionsMerged[]=$arow;
                        }
                        foreach ($result->readAll() as $arow)
                        {
                            $aConditionsMerged[]=$arow;
                        }

                        foreach ($aConditionsMerged as $rows)
                        {
                            if($rows['method'] == "") {$rows['method'] = "==";} //Fill in the empty method from previous versions
                            $markcidstyle="oddrow";
                            if (array_search($rows['cid'], $markcidarray) !== FALSE){
                                // This is the style used when the condition editor is called
                                // in order to check which conditions prevent a question deletion
                                $markcidstyle="markedrow";
                            }
                            if ($subaction == "editthiscondition" && isset($p_cid) &&
                            $rows['cid'] === $p_cid)
                            {
                                // Style used when editing a condition
                                $markcidstyle="editedrow";
                            }

                            if (isset($currentfield) && $currentfield != $rows['cfieldname'] )
                            {
                                $aViewUrls['output'] .= "<tr class='evenrow'>\n"
                                ."\t<td colspan='2' class='operator'>\n"
                                .$clang->gT("and")."</td></tr>";
                            }
                            elseif (isset($currentfield))
                            {
                                $aViewUrls['output'] .= "<tr class='evenrow'>\n"
                                ."\t<td colspan='2' class='operator'>\n"
                                .$clang->gT("or")."</td></tr>";
                            }

                            $aViewUrls['output'] .= "\t<tr class='{$markcidstyle}'>\n"
                            ."\t<td colspan='2'>"
                            .CHtml::form(array("/admin/conditions/sa/index/subaction/{$subaction}/surveyid/{$iSurveyID}/gid/{$gid}/qid/{$qid}/"), 'post', array('id'=>"conditionaction{$rows['cid']}",'name'=>"conditionaction{$rows['cid']}"))
                            ."<table>\n"
                            ."\t<tr>\n";

                            if ( $subaction == "copyconditionsform" || $subaction == "copyconditions" )
                            {
                                $aViewUrls['output'] .= "<td>&nbsp;&nbsp;</td>"
                                . "<td>\n"
                                . "\t<input type='checkbox' name='aConditionFromScenario{$scenarionr['scenario']}' id='cbox{$rows['cid']}' value='{$rows['cid']}' checked='checked'/>\n"
                                . "</td>\n";
                            }
                            $aViewUrls['output'] .= ""
                            ."<td>\n"
                            ."\t<span>\n";

                            $leftOperandType = 'unknown'; // prevquestion, tokenattr
                            if ($thissurvey['anonymized'] != 'Y' && preg_match('/^{TOKEN:([^}]*)}$/',$rows['cfieldname'],$extractedTokenAttr) > 0)
                            {
                                $leftOperandType = 'tokenattr';
                                $aTokenAttrNames=getTokenFieldsAndNames($iSurveyID);
                                if(isset($aTokenAttrNames[strtolower($extractedTokenAttr[1])]))
                                {
                                    $thisAttrName=HTMLEscape($aTokenAttrNames[strtolower($extractedTokenAttr[1])]['description']);
                                }
                                else
                                {
                                    $thisAttrName=HTMLEscape($extractedTokenAttr[1]);
                                }
                                if(tableExists("{{tokens_$iSurveyID}}"))
                                {
                                    $thisAttrName.= " [".$clang->gT("From token table")."]";
                                }
                                else
                                {
                                    $thisAttrName.= " [".$clang->gT("Inexistant token table")."]";
                                }

                                $aViewUrls['output'] .= "\t$thisAttrName\n";
                                // TIBO not sure this is used anymore !!
                                $conditionsList[]=array("cid"=>$rows['cid'],
                                "text"=>$thisAttrName);
                            }
                            else
                            {
                                $leftOperandType = 'prevquestion';
                                foreach ($cquestions as $cqn)
                                {
                                    if ($cqn[3] == $rows['cfieldname'])
                                    {
                                        $aViewUrls['output'] .= "\t$cqn[0] (qid{$rows['cqid']})\n";
                                        $conditionsList[]=array("cid"=>$rows['cid'],
                                        "text"=>$cqn[0]." ({$rows['value']})");
                                    }
                                    else
                                    {
                                        //$aViewUrls['output'] .= "\t<font color='red'>ERROR: Delete this condition. It is out of order.</font>\n";
                                    }
                                }
                            }

                            $aViewUrls['output'] .= "\t</span></td>\n"
                            ."\t<td>\n"
                            ."<span>\n" //    .$clang->gT("Equals")."</font></td>"
                            .$method[trim ($rows['method'])]
                            ."</span>\n"
                            ."\t</td>\n"
                            ."\n"
                            ."\t<td>\n"
                            ."<span>\n";

                            // let's read the condition's right operand
                            // determine its type and display it
                            $rightOperandType = 'unknown'; // predefinedAnsw,constantVal, prevQsgqa, tokenAttr, regexp
                            if ($rows['method'] == 'RX')
                            {
                                $rightOperandType = 'regexp';
                                $aViewUrls['output'] .= "".HTMLEscape($rows['value'])."\n";
                            }
                            elseif (preg_match('/^@([0-9]+X[0-9]+X[^@]*)@$/',$rows['value'],$matchedSGQA) > 0)
                            { // SGQA
                                $rightOperandType = 'prevQsgqa';
                                $textfound=false;
                                foreach ($cquestions as $cqn)
                                {
                                    if ($cqn[3] == $matchedSGQA[1])
                                    {
                                        $matchedSGQAText=$cqn[0];
                                        $textfound=true;
                                        break;
                                    }
                                }
                                if ($textfound === false)
                                {
                                    $matchedSGQAText=$rows['value'].' ('.$clang->gT("Not found").')';
                                }

                                $aViewUrls['output'] .= "".HTMLEscape($matchedSGQAText)."\n";
                            }
                            elseif ($thissurvey['anonymized'] != 'Y' && preg_match('/^{TOKEN:([^}]*)}$/',$rows['value'],$extractedTokenAttr) > 0)
                            {
                                $rightOperandType = 'tokenAttr';
                                $aTokenAttrNames=getTokenFieldsAndNames($iSurveyID);
                                if (count($aTokenAttrNames) != 0)
                                {
                                    $thisAttrName=HTMLEscape($aTokenAttrNames[strtolower($extractedTokenAttr[1])]['description'])." [".$clang->gT("From token table")."]";
                                }
                                else
                                {
                                    $thisAttrName=HTMLEscape($extractedTokenAttr[1])." [".$clang->gT("Inexistant token table")."]";
                                }
                                $aViewUrls['output'] .= "\t$thisAttrName\n";
                            }
                            elseif (isset($canswers))
                            {
                                foreach ($canswers as $can)
                                {
                                    if ($can[0] == $rows['cfieldname'] && $can[1] == $rows['value'])
                                    {
                                        $aViewUrls['output'] .= "$can[2] ($can[1])\n";
                                        $rightOperandType = 'predefinedAnsw';

                                    }
                                }
                            }
                            // if $rightOperandType is still unknown then it is a simple constant
                            if ($rightOperandType == 'unknown')
                            {
                                $rightOperandType = 'constantVal';
                                if ($rows['value'] == ' ' ||
                                $rows['value'] == '')
                                {
                                    $aViewUrls['output'] .= "".$clang->gT("No answer")."\n";
                                }
                                else
                                {
                                    $aViewUrls['output'] .= "".HTMLEscape($rows['value'])."\n";
                                }
                            }

                            $aViewUrls['output'] .= "\t</span></td>\n"
                            ."\t<td>\n";

                            if ( $subaction == "editconditionsform" ||$subaction == "insertcondition" ||
                            $subaction == "updatecondition" || $subaction == "editthiscondition" ||
                            $subaction == "renumberscenarios" || $subaction == "deleteallconditions" ||
                            $subaction == "updatescenario" ||
                            $subaction == "deletescenario" || $subaction == "delete" )
                            { // show single condition action buttons in edit mode

                                $aData['rows'] = $rows;
                                $aData['sImageURL'] = Yii::app()->getConfig('adminimageurl');

                                //$aViewUrls['includes/conditions_edit'][] = $aData;

                                $aViewUrls['output'] .= $this->getController()->renderPartial('/admin/conditions/includes/conditions_edit',$aData, TRUE);

                                // now sets e corresponding hidden input field
                                // depending on the leftOperandType
                                if ($leftOperandType == 'tokenattr')
                                {
                                    $aViewUrls['output'] .= CHtml::hiddenField('csrctoken', HTMLEscape($rows['cfieldname']), array(
                                    'id' => 'csrctoken'.$rows['cid']
                                    ));
                                }
                                else
                                {
                                    $aViewUrls['output'] .= CHtml::hiddenField('cquestions', HTMLEscape($rows['cfieldname']),
                                    array(
                                    'id' => 'cquestions'.$rows['cid']
                                    )
                                    );
                                }

                                // now set the corresponding hidden input field
                                // depending on the rightOperandType
                                // This is used when editing a condition
                                if ($rightOperandType == 'predefinedAnsw')
                                {
                                    $aViewUrls['output'] .= CHtml::hiddenField('EDITcanswers[]', HTMLEscape($rows['value']), array(
                                    'id' => 'editModeTargetVal'.$rows['cid']
                                    ));
                                }
                                elseif ($rightOperandType == 'prevQsgqa')
                                {
                                    $aViewUrls['output'] .= CHtml::hiddenField('EDITprevQuestionSGQA', HTMLEscape($rows['value']),
                                    array(
                                    'id' => 'editModeTargetVal'.$rows['cid']
                                    ));
                                }
                                elseif ($rightOperandType == 'tokenAttr')
                                {
                                    $aViewUrls['output'] .= CHtml::hiddenField('EDITtokenAttr', HTMLEscape($rows['value']), array(
                                    'id' => 'editModeTargetVal'.$rows['cid']
                                    ));
                                }
                                elseif ($rightOperandType == 'regexp')
                                {
                                    $aViewUrls['output'] .= CHtml::hiddenField('EDITConditionRegexp', HTMLEscape($rows['value']),
                                    array(
                                    'id' => 'editModeTargetVal'.$rows['cid']
                                    ));
                                }
                                else
                                {
                                    $aViewUrls['output'] .= CHtml::hiddenField('EDITConditionConst', HTMLEscape($rows['value']),
                                    array(
                                    'id' => 'editModeTargetVal'.$rows['cid']
                                    ));
                                }
                            }

                            $aViewUrls['output'] 	.= 	CHtml::closeTag('td') 	. CHtml::closeTag('tr') .
                            CHtml::closeTag('table'). CHtml::closeTag('form') .
                            CHtml::closeTag('td') 	. CHtml::closeTag('tr');

                            $currentfield = $rows['cfieldname'];
                        }

                    }


                    $s++;
                }
            }
            else
            { // no condition ==> disable delete all conditions button, and display a simple comment
                $aViewUrls['output'] = 	CHtml::openTag('tr') . CHtml::tag('td', array(),
                $clang->gT("This question is always shown.")).CHtml::tag('td', array(),'&nbsp;').CHtml::closeTag('tr');
            }

            $aViewUrls['output'] .= CHtml::closeTag('table');

        }
        //END DISPLAY CONDITIONS FOR THIS QUESTION

        // BEGIN: DISPLAY THE COPY CONDITIONS FORM
        if ($subaction == "copyconditionsform" || $subaction == "copyconditions")
        {
            $aViewUrls['output'] .= "<tr class=''><td colspan='3'>\n"
            .CHtml::form(array("/admin/conditions/sa/index/subaction/copyconditions/surveyid/{$iSurveyID}/gid/{$gid}/qid/{$qid}/"), 'post', array('id'=>"copyconditions",'name'=>"copyconditions"))
            ."<div class='header ui-widget-header'>".$clang->gT("Copy conditions")."</div>\n";


            //CopyConditionsMessage
            if (isset ($CopyConditionsMessage))
            {
                $aViewUrls['output'] .= "<div class='messagebox ui-corner-all'>\n"
                ."$CopyConditionsMessage\n"
                ."</div>\n";
            }

            if (isset($conditionsList) && is_array($conditionsList))
            {
                //TIBO
                App()->getClientScript()->registerScriptFile(Yii::app()->getConfig("generalscripts").'jquery/jquery.multiselect.min.js');

                // TODO
                $aViewUrls['output'] .= "<script type='text/javascript'>$(document).ready(function () { $('#copytomultiselect').multiselect( { autoOpen: true, noneSelectedText: '".$clang->gT("No questions selected")."', checkAllText: '".$clang->gT("Check all")."', uncheckAllText: '".$clang->gT("Uncheck all")."', selectedText: '# ".$clang->gT("selected")."', beforeclose: function(){ return false;},height: 200 } ); });</script>";

                $aViewUrls['output'] .= "\t<div class='conditioncopy-tbl-row'>\n"
                ."\t<div class='condition-tbl-left'>".$clang->gT("Copy the selected conditions to").":</div>\n"
                ."\t<div class='condition-tbl-right'>\n"
                ."\t\t<select name='copyconditionsto[]' id='copytomultiselect'  multiple='multiple' >\n";
                if (isset($pquestions) && count($pquestions) != 0)
                {
                    foreach ($pquestions as $pq)
                    {
                        $aViewUrls['output'] .= "\t\t<option value='{$pq['fieldname']}'>".$pq['text']."</option>\n";
                    }
                }
                $aViewUrls['output'] .= "\t\t</select>\n"
                ."\t</div>\n"
                ."\t</div>\n";

                if ( !isset($pquestions) || count($pquestions) == 0)
                {
                    $disableCopyCondition=" disabled='disabled'";
                }
                else
                {
                    $disableCopyCondition=" ";
                }

                $aViewUrls['output'] .= "\t<div class='condition-tbl-full'>\n"
                //        ."\t\t<input type='submit' value='".$clang->gT("Copy conditions")."' onclick=\"if (confirm('".$clang->gT("Are you sure you want to copy these condition(s) to the questions you have selected?","js")."')){ prepareCopyconditions(); return true;} else { return false;}\" $disableCopyCondition/>\n"
                ."\t\t<input type='submit' value='".$clang->gT("Copy conditions")."' onclick=\"prepareCopyconditions(); return true;\" $disableCopyCondition/>\n"
                ."<input type='hidden' name='subaction' value='copyconditions' />\n"
                ."<input type='hidden' name='sid' value='$iSurveyID' />\n"
                ."<input type='hidden' name='gid' value='$gid' />\n"
                ."<input type='hidden' name='qid' value='$qid' />\n"
                ."</div>\n";

                $aViewUrls['output'] .= "<script type=\"text/javascript\">\n"
                ."function prepareCopyconditions()\n"
                ."{\n"
                ."\t$(\"input:checked[name^='aConditionFromScenario']\").each(function(i,val)\n"
                ."\t{\n"
                ."var thecid = val.value;\n"
                ."var theform = document.getElementById('copyconditions');\n"
                ."addHiddenElement(theform,'copyconditionsfrom[]',thecid);\n"
                ."return true;\n"
                ."\t});\n"
                ."}\n"
                ."</script>\n";

            }
            else
            {
                $aViewUrls['output'] .= "<div class='messagebox ui-corner-all'>\n"
                ."<div class='partialheader'>".$clang->gT("There are no existing conditions in this survey.")."</div><br />\n"
                ."</div>\n";
            }

            $aViewUrls['output'] .= "</form></td></tr>\n";

        }
        // END: DISPLAY THE COPY CONDITIONS FORM

        if ( isset($cquestions) )
        {
            if ( count($cquestions) > 0 && count($cquestions) <=10)
            {
                $qcount = count($cquestions);
            }
            else
            {
                $qcount = 9;
            }
        }
        else
        {
            $qcount = 0;
        }

        //BEGIN: DISPLAY THE ADD or EDIT CONDITION FORM
        if ($subaction == "editconditionsform" || $subaction == "insertcondition" ||
        $subaction == "updatecondition" || $subaction == "deletescenario" ||
        $subaction == "renumberscenarios" || $subaction == "deleteallconditions" ||
        $subaction == "updatescenario" ||
        $subaction == "editthiscondition" || $subaction == "delete")
        {
            $aViewUrls['output'] .= CHtml::form(array("/admin/conditions/sa/index/subaction/{$subaction}/surveyid/{$iSurveyID}/gid/{$gid}/qid/{$qid}/"), 'post', array('id'=>"editconditions",'name'=>"editconditions"));
            if ($subaction == "editthiscondition" &&  isset($p_cid))
            {
                $mytitle = $clang->gT("Edit condition");
            }
            else
            {
                $mytitle = $clang->gT("Add condition");
            }
            $aViewUrls['output'] .= "<div class='header ui-widget-header'>".$mytitle."</div>\n";

            ///////////////////////////////////////////////////////////////////////////////////////////

            // Begin "Scenario" row
            if  ( ( $subaction != "editthiscondition" && isset($scenariocount) && ($scenariocount == 1 || $scenariocount==0)) ||
            ( $subaction == "editthiscondition" && isset($scenario) && $scenario == 1) )
            {
                $scenarioAddBtn = "\t<a id='scenarioaddbtn' href='#' onclick=\"$('#scenarioaddbtn').hide();$('#defaultscenariotxt').hide('slow');$('#scenario').show('slow');\">"
                ."<img src='$imageurl/plus.png' alt='".$clang->gT('Add scenario')."' /></a>\n";
                $scenarioTxt = "<span id='defaultscenariotxt'>".$clang->gT("Default scenario")."</span>";
                $scenarioInputStyle = "style = 'display: none;'";
            }
            else
            {
                $scenarioAddBtn = "";
                $scenarioTxt = "";
                $scenarioInputStyle = "style = ''";
            }

            $aViewUrls['output'] .="<div class='condition-tbl-row'>\n"
            ."<div class='condition-tbl-left'>$scenarioAddBtn&nbsp;".$clang->gT("Scenario")."</div>\n"
            ."<div class='condition-tbl-right'><input type='text' name='scenario' id='scenario' value='1' size='2' $scenarioInputStyle/>"
            ."$scenarioTxt\n"
            ."</div>\n"
            ."</div>\n";

            // Begin "Question" row
            $aViewUrls['output'] .="<div class='condition-tbl-row'>\n"
            ."<div class='condition-tbl-left'>".$clang->gT("Question")."</div>\n"
            ."<div class='condition-tbl-right'>\n"
            ."\t<div id=\"conditionsource\" class=\"tabs-nav\">\n"
            ."\t<ul>\n"
            ."\t<li><a href=\"#SRCPREVQUEST\"><span>".$clang->gT("Previous questions")."</span></a></li>\n"
            ."\t<li><a href=\"#SRCTOKENATTRS\"><span>".$clang->gT("Token fields")."</span></a></li>\n"
            ."\t</ul>\n";

            // Previous question tab
            $aViewUrls['output'] .= "<div id='SRCPREVQUEST'><select name='cquestions' id='cquestions' size='".($qcount+1)."' >\n";
            if (isset($cquestions))
            {
                $js_getAnswers_onload = "";
                foreach ($cquestions as $cqn)
                {
                    $aViewUrls['output'] .= "<option value='$cqn[3]' title=\"".htmlspecialchars($cqn[0])."\"";
                    if (isset($p_cquestions) && $cqn[3] == $p_cquestions) {
                        $aViewUrls['output'] .= " selected";
                        if (isset($p_canswers))
                        {
                            $canswersToSelect = "";
                            foreach ($p_canswers as $checkval)
                            {
                                $canswersToSelect .= ";$checkval";
                            }
                            $canswersToSelect = substr($canswersToSelect,1);
                            $js_getAnswers_onload .= "$('#canswersToSelect').val('$canswersToSelect');\n";
                        }
                    }
                    $aViewUrls['output'] .= ">$cqn[0]</option>\n";
                }
            }

            $aViewUrls['output'] .= "</select>\n"
            ."</div>\n";

            // Source token Tab
            $aViewUrls['output'] .= "<div id='SRCTOKENATTRS'><select name='csrctoken' id='csrctoken' size='".($qcount+1)."' >\n";
            foreach (getTokenFieldsAndNames($iSurveyID) as $tokenattr => $tokenattrName)
            {
                // Check to select
                if (isset($p_csrctoken) && $p_csrctoken == '{TOKEN:'.strtoupper($tokenattr).'}')
                {
                    $selectThisSrcTokenAttr = "selected=\"selected\"";
                }
                else
                {
                    $selectThisSrcTokenAttr = "";
                }
                $aViewUrls['output'] .= "<option value='{TOKEN:".strtoupper($tokenattr)."}' $selectThisSrcTokenAttr>".HTMLEscape($tokenattrName['description'])."</option>\n";
            }

            $aViewUrls['output'] .= "</select>\n"
            ."</div>\n\n";

            $aViewUrls['output'] .= "\t</div>\n"; // end conditionsource div

            $aViewUrls['output'] .= "</div>\n"
            ."</div>\n";

            // Begin "Comparison operator" row
            $aViewUrls['output'] .="<div class='condition-tbl-row'>\n"
            ."<div class='condition-tbl-left'>".$clang->gT("Comparison operator")."</div>\n"
            ."<div class='condition-tbl-right'>\n"
            ."<select name='method' id='method'>\n";
            foreach ($method as $methodCode => $methodTxt)
            {
                $selected=$methodCode=="==" ? " selected='selected'" : "";
                $aViewUrls['output'] .= "\t<option value='".$methodCode."'$selected>".$methodTxt."</option>\n";
            }

            $aViewUrls['output'] .="</select>\n"
            ."</div>\n"
            ."</div>\n";

            // Begin "Answer" row
            $aViewUrls['output'] .="<div class='condition-tbl-row'>\n"
            ."<div class='condition-tbl-left'>".$clang->gT("Answer")."</div>\n";

            if ($subaction == "editthiscondition")
            {
                $multipletext = "";
                if (isset($_POST['EDITConditionConst']) && $_POST['EDITConditionConst'] != '')
                {
                    $EDITConditionConst=HTMLEscape($_POST['EDITConditionConst']);
                }
                else
                {
                    $EDITConditionConst="";
                }
                if (isset($_POST['EDITConditionRegexp']) && $_POST['EDITConditionRegexp'] != '')
                {
                    $EDITConditionRegexp=HTMLEscape($_POST['EDITConditionRegexp']);
                }
                else
                {
                    $EDITConditionRegexp="";
                }
            }
            else
            {
                $multipletext = "multiple";
                if (isset($_POST['ConditionConst']) && $_POST['ConditionConst'] != '')
                {
                    $EDITConditionConst=HTMLEscape($_POST['ConditionConst']);
                }
                else
                {
                    $EDITConditionConst="";
                }
                if (isset($_POST['ConditionRegexp']) && $_POST['ConditionRegexp'] != '')
                {
                    $EDITConditionRegexp=HTMLEscape($_POST['ConditionRegexp']);
                }
                else
                {
                    $EDITConditionRegexp="";
                }
            }


            $aViewUrls['output'] .= ""
            ."<div class='condition-tbl-right'>\n"
            ."<div id=\"conditiontarget\" class=\"tabs-nav\">\n"
            ."\t<ul>\n"
            ."\t\t<li><a href=\"#CANSWERSTAB\"><span>".$clang->gT("Predefined")."</span></a></li>\n"
            ."\t\t<li><a href=\"#CONST\"><span>".$clang->gT("Constant")."</span></a></li>\n"
            ."\t\t<li><a href=\"#PREVQUESTIONS\"><span>".$clang->gT("Questions")."</span></a></li>\n"
            ."\t\t<li><a href=\"#TOKENATTRS\"><span>".$clang->gT("Token fields")."</span></a></li>\n"
            ."\t\t<li><a href=\"#REGEXP\"><span>".$clang->gT("RegExp")."</span></a></li>\n"
            ."\t</ul>\n";

            // Predefined answers tab
            $aViewUrls['output'] .= "\t<div id='CANSWERSTAB'>\n"
            ."\t\t<select  name='canswers[]' $multipletext id='canswers' size='7'>\n"
            ."\t\t</select>\n"
            ."\t\t<br /><span id='canswersLabel'>".$clang->gT("Predefined answer options for this question")."</span>\n"
            ."\t</div>\n";

            // Constant tab
            $aViewUrls['output'] .= "\t<div id='CONST' style='display:block;' >\n"
            ."\t\t<textarea name='ConditionConst' id='ConditionConst' rows='5' cols='113'>$EDITConditionConst</textarea>\n"
            ."\t\t<br /><div id='ConditionConstLabel'>".$clang->gT("Constant value")."</div>\n"
            ."\t</div>\n";
            // Previous answers tab @SGQA@ placeholders
            $aViewUrls['output'] .= "\t<div id='PREVQUESTIONS'>\n"
            ."\t\t<select name='prevQuestionSGQA' id='prevQuestionSGQA' size='7'>\n";
            foreach ($cquestions as $cqn)
            { // building the @SGQA@ placeholders options
                if ($cqn[2] != 'M' && $cqn[2] != 'P')
                { // Type M or P aren't real fieldnames and thus can't be used in @SGQA@ placehodlers
                    $aViewUrls['output'] .= "\t\t<option value='@$cqn[3]@' title=\"".htmlspecialchars($cqn[0])."\"";
                    if (isset($p_prevquestionsgqa) && $p_prevquestionsgqa == "@".$cqn[3]."@")
                    {
                        $aViewUrls['output'] .= " selected='selected'";
                    }
                    $aViewUrls['output'] .= ">$cqn[0]</option>\n";
                }
            }
            $aViewUrls['output'] .= "\t\t</select>\n"
            ."\t\t<br /><span id='prevQuestionSGQALabel'>".$clang->gT("Answer from previous questions")."</span>\n"
            ."\t</div>\n";

            // Token tab
            $aViewUrls['output'] .= "\t<div id='TOKENATTRS'>\n"
            ."\t\t<select name='tokenAttr' id='tokenAttr' size='7'>\n";
            foreach (getTokenFieldsAndNames($iSurveyID) as $tokenattr => $tokenattrName)
            {
                $aViewUrls['output'] .= "\t\t<option value='{TOKEN:".strtoupper($tokenattr)."}'>".HTMLEscape($tokenattrName['description'])."</option>\n";
            }

            $aViewUrls['output'] .= "\t\t</select>\n"
            ."\t\t<br /><span id='tokenAttrLabel'>".$clang->gT("Attributes values from the participant's token")."</span>\n"
            ."\t</div>\n";

            // Regexp Tab
            $aViewUrls['output'] .= "\t<div id='REGEXP' style='display:block;'>\n"
            ."\t\t<textarea name='ConditionRegexp' id='ConditionRegexp' rows='5' cols='113'>$EDITConditionRegexp</textarea>\n"
            ."\t\t<br /><div id='ConditionRegexpLabel'><a href=\"http://manual.limesurvey.org/wiki/Using_regular_expressions\" target=\"_blank\">".$clang->gT("Regular expression")."</a></div>\n"
            ."\t</div>\n";

            $aViewUrls['output'] .= "</div>\n"; // end conditiontarget div


            App()->getClientScript()->registerScriptFile(Yii::app()->getConfig("adminscripts").'conditions.js');
            //App()->getClientScript()->registerScriptFile(Yii::app()->getConfig("generalscripts").'jquery/lime-conditions-tabs.js');

            if ($subaction == "editthiscondition" && isset($p_cid))
            {
                $submitLabel = $clang->gT("Update condition");
                $submitSubaction = "updatecondition";
                $submitcid = sanitize_int($p_cid);
            }
            else
            {
                $submitLabel = $clang->gT("Add condition");
                $submitSubaction = "insertcondition";
                $submitcid = "";
            }

            $aViewUrls['output'] .= "</div>\n"
            ."</div>\n";

            // Begin buttons row
            $aViewUrls['output'] .= "<div class='condition-tbl-full'>\n"
            ."\t<input type='reset' id='resetForm' value='".$clang->gT("Clear")."' />\n"
            ."\t<input type='submit' value='".$submitLabel."' />\n"
            ."<input type='hidden' name='sid' value='$iSurveyID' />\n"
            ."<input type='hidden' name='gid' value='$gid' />\n"
            ."<input type='hidden' name='qid' value='$qid' />\n"
            ."<input type='hidden' name='subaction' value='$submitSubaction' />\n"
            ."<input type='hidden' name='cqid' id='cqid' value='' />\n"
            ."<input type='hidden' name='cid' id='cid' value='".$submitcid."' />\n"
            ."<input type='hidden' name='editTargetTab' id='editTargetTab' value='' />\n" // auto-select tab by jQuery when editing a condition
            ."<input type='hidden' name='editSourceTab' id='editSourceTab' value='' />\n" // auto-select tab by jQuery when editing a condition
            ."<input type='hidden' name='canswersToSelect' id='canswersToSelect' value='' />\n" // auto-select target answers by jQuery when editing a condition
            ."</div>\n"
            ."</form>\n";

            if (!isset($js_getAnswers_onload))
            {
                $js_getAnswers_onload = '';
            }

            $aViewUrls['output'] .= "<script type='text/javascript'>\n"
            . "<!--\n"
            . "\t".$js_getAnswers_onload."\n";
            if (isset($p_method))
            {
                $aViewUrls['output'] .= "\tdocument.getElementById('method').value='".$p_method."';\n";
            }

            if ($subaction == "editthiscondition")
            { // in edit mode we read previous values in order to dusplay them in the corresponding inputs
                if (isset($_POST['EDITConditionConst']) && $_POST['EDITConditionConst'] != '')
                {
                    // In order to avoid issues with backslash escaping, I don't use javascript to set the value
                    // Thus the value is directly set when creating the Textarea element
                    //$aViewUrls['output'] .= "\tdocument.getElementById('ConditionConst').value='".HTMLEscape($_POST['EDITConditionConst'])."';\n";
                    $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#CONST';\n";
                }
                elseif (isset($_POST['EDITprevQuestionSGQA']) && $_POST['EDITprevQuestionSGQA'] != '')
                {
                    $aViewUrls['output'] .= "\tdocument.getElementById('prevQuestionSGQA').value='".HTMLEscape($_POST['EDITprevQuestionSGQA'])."';\n";
                    $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#PREVQUESTIONS';\n";
                }
                elseif (isset($_POST['EDITtokenAttr']) && $_POST['EDITtokenAttr'] != '')
                {
                    $aViewUrls['output'] .= "\tdocument.getElementById('tokenAttr').value='".HTMLEscape($_POST['EDITtokenAttr'])."';\n";
                    $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#TOKENATTRS';\n";
                }
                elseif (isset($_POST['EDITConditionRegexp']) && $_POST['EDITConditionRegexp'] != '')
                {
                    // In order to avoid issues with backslash escaping, I don't use javascript to set the value
                    // Thus the value is directly set when creating the Textarea element
                    //$aViewUrls['output'] .= "\tdocument.getElementById('ConditionRegexp').value='".HTMLEscape($_POST['EDITConditionRegexp'])."';\n";
                    $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#REGEXP';\n";
                }
                elseif (isset($_POST['EDITcanswers']) && is_array($_POST['EDITcanswers']))
                { // was a predefined answers post
                    $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#CANSWERSTAB';\n";
                    $aViewUrls['output'] .= "\t$('#canswersToSelect').val('".$_POST['EDITcanswers'][0]."');\n";
                }

                if (isset($_POST['csrctoken']) && $_POST['csrctoken'] != '')
                {
                    $aViewUrls['output'] .= "\tdocument.getElementById('csrctoken').value='".HTMLEscape($_POST['csrctoken'])."';\n";
                    $aViewUrls['output'] .= "\tdocument.getElementById('editSourceTab').value='#SRCTOKENATTRS';\n";
                }
                else if (isset($_POST['cquestions']) && $_POST['cquestions'] != '')
                    {
                        $aViewUrls['output'] .= "\tdocument.getElementById('cquestions').value='".HTMLEscape($_POST['cquestions'])."';\n";
                        $aViewUrls['output'] .= "\tdocument.getElementById('editSourceTab').value='#SRCPREVQUEST';\n";
                    }
            }
            else
            { // in other modes, for the moment we do the same as for edit mode
                if (isset($_POST['ConditionConst']) && $_POST['ConditionConst'] != '')
                {
                    // In order to avoid issues with backslash escaping, I don't use javascript to set the value
                    // Thus the value is directly set when creating the Textarea element
                    //$aViewUrls['output'] .= "\tdocument.getElementById('ConditionConst').value='".HTMLEscape($_POST['ConditionConst'])."';\n";
                    $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#CONST';\n";
                }
                elseif (isset($_POST['prevQuestionSGQA']) && $_POST['prevQuestionSGQA'] != '')
                {
                    $aViewUrls['output'] .= "\tdocument.getElementById('prevQuestionSGQA').value='".HTMLEscape($_POST['prevQuestionSGQA'])."';\n";
                    $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#PREVQUESTIONS';\n";
                }
                elseif (isset($_POST['tokenAttr']) && $_POST['tokenAttr'] != '')
                {
                    $aViewUrls['output'] .= "\tdocument.getElementById('tokenAttr').value='".HTMLEscape($_POST['tokenAttr'])."';\n";
                    $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#TOKENATTRS';\n";
                }
                elseif (isset($_POST['ConditionRegexp']) && $_POST['ConditionRegexp'] != '')
                {
                    // In order to avoid issues with backslash escaping, I don't use javascript to set the value
                    // Thus the value is directly set when creating the Textarea element
                    //$aViewUrls['output'] .= "\tdocument.getElementById('ConditionRegexp').value='".HTMLEscape($_POST['ConditionRegexp'])."';\n";
                    $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#REGEXP';\n";
                }
                else
                { // was a predefined answers post
                    if (isset($_POST['cquestions']))
                    {
                        $aViewUrls['output'] .= "\tdocument.getElementById('cquestions').value='".HTMLEscape($_POST['cquestions'])."';\n";
                    }
                    $aViewUrls['output'] .= "\tdocument.getElementById('editTargetTab').value='#CANSWERSTAB';\n";
                }

                if (isset($_POST['csrctoken']) && $_POST['csrctoken'] != '')
                {
                    $aViewUrls['output'] .= "\tdocument.getElementById('csrctoken').value='".HTMLEscape($_POST['csrctoken'])."';\n";
                    $aViewUrls['output'] .= "\tdocument.getElementById('editSourceTab').value='#SRCTOKENATTRS';\n";
                }
                else
                {
                    if (isset($_POST['cquestions'])) $aViewUrls['output'] .= "\tdocument.getElementById('cquestions').value='".javascriptEscape($_POST['cquestions'])."';\n";
                    $aViewUrls['output'] .= "\tdocument.getElementById('editSourceTab').value='#SRCPREVQUEST';\n";
                }
            }

            if (isset($p_scenario))
            {
                $aViewUrls['output'] .= "\tdocument.getElementById('scenario').value='".$p_scenario."';\n";
            }
            $aViewUrls['output'] .= "-->\n"
            . "</script>\n";
        }
        //END: DISPLAY THE ADD or EDIT CONDITION FORM

        $conditionsoutput = $aViewUrls['output'];

        $aData['conditionsoutput'] = $conditionsoutput;
        $this->_renderWrappedTemplate('conditions', $aViewUrls, $aData);

        // TMSW Condition->Relevance:  Must call LEM->ConvertConditionsToRelevance() whenever Condition is added or updated - what is best location for that action?
    }

    private function _showSpeaker($hinttext)
    {
        global $max;
        $clang = Yii::app()->lang;
        $imageurl = Yii::app()->getConfig("adminimageurl");

        if(!isset($max))
        {
            $max = 20;
        }
        $htmlhinttext=str_replace("'",'&#039;',$hinttext);  //the string is already HTML except for single quotes so we just replace these only
        $jshinttext=javascriptEscape($hinttext,true,true);

        if(strlen(html_entity_decode($hinttext,ENT_QUOTES,'UTF-8')) > ($max+3))
        {
            $shortstring = flattenText($hinttext,true);

            $shortstring = htmlspecialchars(mb_strcut(html_entity_decode($shortstring,ENT_QUOTES,'UTF-8'), 0, $max, 'UTF-8'));

            //output with hoover effect
            $reshtml= "<span style='cursor: hand' alt='".$htmlhinttext."' title='".$htmlhinttext."' "
            ." onclick=\"alert('".$clang->gT("Question","js").": $jshinttext')\" />"
            ." \"$shortstring...\" </span>"
            ."<img style='cursor: hand' src='$imageurl/speaker.png' align='bottom' alt='$htmlhinttext' title='$htmlhinttext' "
            ." onclick=\"alert('".$clang->gT("Question","js").": $jshinttext')\" />";
        }
        else
        {
            $shortstring = flattenText($hinttext,true);

            $reshtml= "<span title='".$shortstring."'> \"$shortstring\"</span>";
        }

        return $reshtml;

    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'conditions', $aViewUrls = array(), $aData = array())
    {
        $aData['display']['menu_bars'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

}
