<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 *
 */

/**
 * Conditions Controller
 *
 * This controller performs token actions
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class conditions extends Survey_Common_Controller {

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
	}

	public function _remap($method, $params = array())
	{
		array_unshift($params, $method);
	    return call_user_func_array(array($this, "action"), $params);
	}

	function action($subaction, $surveyid=null, $gid=null, $qid=null)
	{

		//Compatibility variables for CI
		$_POST = $this->input->post();
		$clang = $this->limesurvey_lang;
		$dbprefix = $this->db->dbprefix;
		$imageurl=$this->config->item("imageurl");
		$this->load->helper("database");

		if($this->input->post("subaction")) $subaction=$this->input->post("subaction");

		//BEGIN Sanitizing POSTed data
		if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
		if (!isset($qid)) {$qid=returnglobal('qid');}
		if (!isset($gid)) {$gid=returnglobal('gid');}
		if (!isset($p_scenario)) {$p_scenario=returnglobal('scenario');}
		if (!isset($p_cqid))
		{
		    $p_cqid=returnglobal('cqid');
		    if ($p_cqid == '') $p_cqid=0; // we are not using another question as source of condition
		}
		if (!isset($p_cid)) {$p_cid=returnglobal('cid');}
		if (!isset($p_subaction)) {$p_subaction=returnglobal('subaction');}
		if (!isset($p_cquestions)) {$p_cquestions=returnglobal('cquestions');}
		if (!isset($p_csrctoken)) {$p_csrctoken=returnglobal('csrctoken');}
		if (!isset($p_prevquestionsgqa)) {$p_prevquestionsgqa=returnglobal('prevQuestionSGQA');}

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
		if (isset($stringcomparizonoperators) && $stringcomparizonoperators == 1)
		{
		    $method = array(
		            "<"  => $clang->gT("Less than"),
		            "<=" => $clang->gT("Less than or equal to"),
		            "==" => $clang->gT("equals"),
		            "!=" => $clang->gT("Not equal to"),
		            ">=" => $clang->gT("Greater than or equal to"),
		            ">"  => $clang->gT("Greater than"),
		            "RX" => $clang->gT("Regular expression"),
		            "a<b"  => $clang->gT("Less than (Strings)"),
		            "a<=b" => $clang->gT("Less than or equal to (Strings)"),
		            "a>=b" => $clang->gT("Greater than or equal to (Strings)"),
		            "a>b"  => $clang->gT("Greater than (Strings)")
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
		    if (!in_array($_POST['method'], array_keys($method)))
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
		//include_once("database.php");
		// Caution (lemeur): database.php uses auto_unescape on all entries in $_POST
		// Take care to not use auto_unescape on $_POST variables after this


		//MAKE SURE THAT THERE IS A SID
		if (!isset($surveyid) || !$surveyid)
		{
		    $conditionsoutput = $clang->gT("You have not selected a survey")."<br /><br />"
		    ."<input type='submit' value='".$clang->gT("Main admin screen")."' onclick=\"window.open('".site_url("admin/")."', '_top')\" /><br />\n";
			show_error($conditionsoutput);
		    return;
		}

		//MAKE SURE THAT THERE IS A QID
		if (!isset($qid) || !$qid)
		{
		    $conditionsoutput = $clang->gT("You have not selected a question")."<br /><br />"
		    ."<input type='submit' value='".$clang->gT("Main admin screen")."' onclick=\"window.open('".site_url("admin/")."', '_top')\" /><br />\n";
			show_error($conditionsoutput);
		    return;
		}


		// If we made it this far, then lets develop the menu items
		// add the conditions container table

		$extraGetParams ="";
		if (isset($qid) && isset($gid))
		{
		    $extraGetParams="/$gid/$qid/";
		}


		$conditionsoutput_action_error = ""; // defined during the actions
		$conditionsoutput_main_content = ""; // everything after the menubar

		$markcidarray=Array();
		if (isset($_GET['markcid']))
		{
		    $markcidarray=explode("-",$_GET['markcid']);
		}


		//BEGIN PROCESS ACTIONS
		// ADD NEW ENTRY IF THIS IS AN ADD
		if (isset($p_subaction) && $p_subaction == "insertcondition")
		{
		    if ((!isset($p_canswers) &&
		    !isset($_POST['ConditionConst']) &&
		    !isset($_POST['prevQuestionSGQA']) &&
		    !isset($_POST['tokenAttr']) &&
		    !isset($_POST['ConditionRegexp'])) ||
		    (!isset($p_cquestions) && !isset($p_csrctoken)))
		    {
		        $conditionsoutput_action_error .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Your condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer.","js")."\")\n //-->\n</script>\n";
		    }
		    else
		    {
		        if (isset($p_cquestions) && $p_cquestions != '')
		        {
		            $conditionCfieldname=$p_cquestions;
		        }
		        elseif(isset($p_csrctoken) && $p_csrctoken != '')
		        {
		            $conditionCfieldname=$p_csrctoken;
		        }

		        if (isset($p_canswers))
		        {
		            foreach ($p_canswers as $ca)
		            {
		                //First lets make sure there isn't already an exact replica of this condition
		                $query = "SELECT * FROM {$dbprefix}conditions\n"
		                ."WHERE qid='$qid'\n"
		                ."AND scenario='".$p_scenario."'\n"
		                ."AND cqid='".$p_cqid."'\n"
		                ."AND cfieldname='".$conditionCfieldname."'\n"
		                ."AND method='".$p_method."'\n"
		                ."AND value='".$ca."'";
		                $result = db_execute_assoc($query) or safe_die("Couldn't check for existing condition<br />$query<br />".$connect->ErrorMsg());
		                $count_caseinsensitivedupes = $result->num_rows();

		                if ($count_caseinsensitivedupes == 0)
		                {
		                $query = "INSERT INTO {$dbprefix}conditions (qid, scenario, cqid, cfieldname, method, value) VALUES "
		                . "('{$qid}', '{$p_scenario}', '{$p_cqid}', '{$conditionCfieldname}', '{$p_method}', '$ca')";
		                $result = db_execute_assoc($query) or safe_die ("Couldn't insert new condition<br />$query<br />".$connect->ErrorMsg());
		            }
		        }
		        }

		        unset($posted_condition_value);
		        // Please note that auto_unescape is already applied in database.php included above
		        // so we only need to db_quote _POST variables
		        if (isset($_POST['ConditionConst']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#CONST")
		        {
		            $posted_condition_value = db_quote($_POST['ConditionConst']);
		        }
		        elseif (isset($_POST['prevQuestionSGQA']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#PREVQUESTIONS")
		        {
		            $posted_condition_value = db_quote($_POST['prevQuestionSGQA']);
		        }
		        elseif (isset($_POST['tokenAttr']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#TOKENATTRS")
		        {
		            $posted_condition_value = db_quote($_POST['tokenAttr']);
		        }
		        elseif (isset($_POST['ConditionRegexp']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#REGEXP")
		        {
		            $posted_condition_value = db_quote($_POST['ConditionRegexp']);
		        }

		        if (isset($posted_condition_value))
		        {
		            $query = "INSERT INTO {$dbprefix}conditions (qid, scenario, cqid, cfieldname, method, value) VALUES "
		            . "('{$qid}', '{$p_scenario}', '{$p_cqid}', '{$conditionCfieldname}', '{$p_method}', '".$posted_condition_value."')";
		            $result = db_execute_assoc($query) or safe_die ("Couldn't insert new condition<br />$query<br />".$connect->ErrorMsg());
		        }
		    }
		}

		// UPDATE ENTRY IF THIS IS AN EDIT
		if (isset($p_subaction) && $p_subaction == "updatecondition")
		{
		    if ((!isset($p_canswers) &&
		    !isset($_POST['ConditionConst']) &&
		    !isset($_POST['prevQuestionSGQA']) &&
		    !isset($_POST['tokenAttr']) &&
		    !isset($_POST['ConditionRegexp'])) ||
		    (!isset($p_cquestions) && !isset($p_csrctoken)))
		    {
		        $conditionsoutput_action_error .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Your condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer.","js")."\")\n //-->\n</script>\n";
		    }
		    else
		    {
		        if (isset($p_cquestions) && $p_cquestions != '')
		        {
		            $conditionCfieldname=$p_cquestions;
		        }
		        elseif(isset($p_csrctoken) && $p_csrctoken != '')
		        {
		            $conditionCfieldname=$p_csrctoken;
		        }

		        if (isset($p_canswers))
		        {
		            foreach ($p_canswers as $ca)
		            { // This is an Edit, there will only be ONE VALUE
		                $query = "UPDATE {$dbprefix}conditions SET qid='{$qid}', scenario='{$p_scenario}', cqid='{$p_cqid}', cfieldname='{$conditionCfieldname}', method='{$p_method}', value='$ca' "
		                . " WHERE cid={$p_cid}";
		                $result = db_execute_assoc($query) or safe_die ("Couldn't update condition<br />$query<br />".$connect->ErrorMsg());
		            }
		        }

		        unset($posted_condition_value);
		        // Please note that auto_unescape is already applied in database.php included above
		        // so we only need to db_quote _POST variables
		        if (isset($_POST['ConditionConst']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#CONST")
		        {
		            $posted_condition_value = db_quote($_POST['ConditionConst']);
		        }
		        elseif (isset($_POST['prevQuestionSGQA']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#PREVQUESTIONS")
		        {
		            $posted_condition_value = db_quote($_POST['prevQuestionSGQA']);
		        }
		        elseif (isset($_POST['tokenAttr']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#TOKENATTRS")
		        {
		            $posted_condition_value = db_quote($_POST['tokenAttr']);
		        }
		        elseif (isset($_POST['ConditionRegexp']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#REGEXP")
		        {
		            $posted_condition_value = db_quote($_POST['ConditionRegexp']);
		        }

		        if (isset($posted_condition_value))
		        {
		            $query = "UPDATE {$dbprefix}conditions SET qid='{$qid}', scenario='{$p_scenario}' , cqid='{$p_cqid}', cfieldname='{$conditionCfieldname}', method='{$p_method}', value='".$posted_condition_value."' "
		            . " WHERE cid={$p_cid}";
		            $result = db_execute_assoc($query) or safe_die ("Couldn't insert new condition<br />$query<br />".$connect->ErrorMsg());
		        }
		    }
		}

		// DELETE ENTRY IF THIS IS DELETE
		if (isset($p_subaction) && $p_subaction == "delete")
		{
		    $query = "DELETE FROM {$dbprefix}conditions WHERE cid={$p_cid}";
		    $result = db_execute_assoc($query) or safe_die ("Couldn't delete condition<br />$query<br />".$connect->ErrorMsg());
		}

		// DELETE ALL CONDITIONS IN THIS SCENARIO
		if (isset($p_subaction) && $p_subaction == "deletescenario")
		{
		    $query = "DELETE FROM {$dbprefix}conditions WHERE qid={$qid} AND scenario={$p_scenario}";
		    $result = db_execute_assoc($query) or safe_die ("Couldn't delete scenario<br />$query<br />".$connect->ErrorMsg());
		}

		// UPDATE SCENARIO
		if (isset($p_subaction) && $p_subaction == "updatescenario" && isset($p_newscenarionum))
		{
		    $query = "UPDATE {$dbprefix}conditions SET scenario=$p_newscenarionum WHERE qid={$qid} AND scenario={$p_scenario}";
		    $result = db_execute_assoc($query) or safe_die ("Couldn't delete scenario<br />$query<br />".$connect->ErrorMsg());
		}

		// DELETE ALL CONDITIONS FOR THIS QUESTION
		if (isset($p_subaction) && $p_subaction == "deleteallconditions")
		{
		    $query = "DELETE FROM {$dbprefix}conditions WHERE qid={$qid}";
		    $result = db_execute_assoc($query) or safe_die ("Couldn't delete scenario<br />$query<br />".$connect->ErrorMsg());
		}

		// RENUMBER SCENARIOS
		if (isset($p_subaction) && $p_subaction == "renumberscenarios")
		{
		    $query = "SELECT DISTINCT scenario FROM {$dbprefix}conditions WHERE qid={$qid} ORDER BY scenario";
		    $result = db_execute_assoc($query) or safe_die ("Couldn't select scenario<br />$query<br />".$connect->ErrorMsg());
		    $newindex=1;
		    foreach ($result->result_array() as $srow)
		    {
		        $query2 = "UPDATE {$dbprefix}conditions set scenario=$newindex WHERE qid={$qid} AND scenario=".$srow['scenario'].";";
		        $result2 = db_execute_assoc($query2) or safe_die ("Couldn't renumber scenario<br />$query<br />".$connect->ErrorMsg());
		        $newindex++;
		    }

		}

		// COPY CONDITIONS IF THIS IS COPY
		if (isset($p_subaction) && $p_subaction == "copyconditions")
		{
		    $qid=returnglobal('qid');
		    $copyconditionsfrom=returnglobal('copyconditionsfrom');
		    $copyconditionsto=returnglobal('copyconditionsto');
		    if (isset($copyconditionsto) && is_array($copyconditionsto) && isset($copyconditionsfrom) && is_array($copyconditionsfrom))
		    {
		        //Get the conditions we are going to copy
		        $query = "SELECT * FROM {$dbprefix}conditions\n"
		        ."WHERE cid in ('";
		        $query .= implode("', '", $copyconditionsfrom);
		        $query .= "')";
		        $result = db_execute_assoc($query) or safe_die("Couldn't get conditions for copy<br />$query<br />".$connect->ErrorMsg());
		        foreach ($result->result_array() as $row)
		        {
		            $proformaconditions[]=array("scenario"=>$row['scenario'],
					"cqid"=>$row['cqid'],
					"cfieldname"=>$row['cfieldname'],
					"method"=>$row['method'],
					"value"=>$row['value']);
		        } // while
		        foreach ($copyconditionsto as $copyc)
		        {
		            list($newsid, $newgid, $newqid)=explode("X", $copyc);
		            foreach ($proformaconditions as $pfc)
		            { //TIBO
		                //First lets make sure there isn't already an exact replica of this condition
		                $query = "SELECT * FROM {$dbprefix}conditions\n"
		                ."WHERE qid='$newqid'\n"
		                ."AND scenario='".$pfc['scenario']."'\n"
		                ."AND cqid='".$pfc['cqid']."'\n"
		                ."AND cfieldname='".$pfc['cfieldname']."'\n"
		                ."AND method='".$pfc['method']."'\n"
		                ."AND value='".$pfc['value']."'";
		                $result = db_execute_assoc($query) or safe_die("Couldn't check for existing condition<br />$query<br />".$connect->ErrorMsg());
		                $count_caseinsensitivedupes = $result->num_rows();

		                $countduplicates = 0;
		                if ($count_caseinsensitivedupes != 0)
		                {
		                    foreach ($result->result_array() as $ccrow)
		                    {
		                        if ($ccrow['value'] == $pfc['value']) $countduplicates++;
		                    }
		                }

		                if ($countduplicates == 0) //If there is no match, add the condition.
		                {
		                    $query = "INSERT INTO {$dbprefix}conditions ( qid,scenario,cqid,cfieldname,method,value) \n"
		                    ."VALUES ( '$newqid', '".$pfc['scenario']."', '".$pfc['cqid']."',"
		                    ."'".$pfc['cfieldname']."', '".$pfc['method']."',"
		                    ."'".$pfc['value']."')";
		                    $result=db_execute_assoc($query) or safe_die ("Couldn't insert query<br />$query<br />".$connect->ErrorMsg());
		                    $conditionCopied=true;
		                }
		                else
		                {
		                    $conditionDuplicated=true;
		                }
		            }
		        }
		        if (isset($conditionCopied) && $conditionCopied === true)
		        {
		            if (isset($conditionDuplicated) && $conditionDuplicated ==true)
		            {
		                $CopyConditionsMessage = "<div class='partialheader'>(".$clang->gT("Conditions successfully copied (some were skipped because they were duplicates)").")</div>";
		            }
		            else
		            {
		                $CopyConditionsMessage = "<div class='successheader'>(".$clang->gT("Conditions successfully copied").")</div>";
		            }
		        }
		        else
		        {
		            $CopyConditionsMessage = "<div class='warningheader'>(".$clang->gT("No conditions could be copied (due to duplicates)").")</div>";
		        }
		    }

		        }
		//END PROCESS ACTIONS



		$cquestions=Array();
		$canswers=Array();



		//BEGIN: GATHER INFORMATION
		// 1: Get information for this question
		if (!isset($qid)) {$qid=returnglobal('qid');}
		if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
		$thissurvey=getSurveyInfo($surveyid);

		$query = "SELECT * "
		."FROM {$dbprefix}questions, "
		."{$dbprefix}groups "
		."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid "
		."AND qid=$qid "
		."AND parent_qid=0 "
		."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."'" ;
		$result = db_execute_assoc($query) or safe_die ("Couldn't get information for question $qid<br />$query<br />".$connect->ErrorMsg());
		foreach ($result->result_array() as $rows)
		{
		    $questiongroupname=$rows['group_name'];
		    $questiontitle=$rows['title'];
		    $questiontext=$rows['question'];
		    $questiontype=$rows['type'];
		}

		// 2: Get all other questions that occur before this question that are pre-determined answer types

		// To avoid natural sort order issues,
		// first get all questions in natural sort order
		// , and find out which number in that order this question is
		$qquery = "SELECT * "
		."FROM {$dbprefix}questions, "
		."{$dbprefix}groups "
		."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid "
		."AND parent_qid=0 "
		."AND {$dbprefix}questions.sid=$surveyid "
		."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		."AND {$dbprefix}groups.language='".GetBaseLanguageFromSurveyID($surveyid)."' " ;

		$qresult = db_execute_assoc($qquery) or safe_die ("$qquery<br />".$connect->ErrorMsg());
		$qrows = $qresult->result_array();
		// Perform a case insensitive natural sort on group name then question title (known as "code" in the form) of a multidimensional array
		usort($qrows, 'GroupOrderThenQuestionOrder');

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
		    if ($qrow["qid"] == $qid)
		    {
		        $position="after";
		        //break;
		    }
		    elseif ($qrow["qid"] != $qid && $position=="after")
		    {
		        $postquestionlist[]=$qrow['qid'];
		    }
		}

		$theserows=array();
		$postrows=array();

		if (isset($questionlist) && is_array($questionlist))
		{
		    foreach ($questionlist as $ql)
		    {
		        $query = "SELECT {$dbprefix}questions.qid, "
		        ."{$dbprefix}questions.sid, "
		        ."{$dbprefix}questions.gid, "
		        ."{$dbprefix}questions.question, "
		        ."{$dbprefix}questions.type, "
		        ."{$dbprefix}questions.title, "
		        ."{$dbprefix}questions.other, "
		        ."{$dbprefix}questions.mandatory "
		        ."FROM {$dbprefix}questions, "
		        ."{$dbprefix}groups "
		        ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid "
		        ."AND parent_qid=0 "
		        ."AND {$dbprefix}questions.qid=$ql "
		        ."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		        ."AND {$dbprefix}groups.language='".GetBaseLanguageFromSurveyID($surveyid)."'" ;

		        $result=db_execute_assoc($query) or die("Couldn't get question $qid");

		        $thiscount=$result->num_rows();

		        // And store again these questions in this array...
		        foreach ($result->result_array() as $myrows)
		        {                   //key => value
		            $theserows[]=array("qid"=>$myrows['qid'],
							"sid"=>$myrows['sid'],
							"gid"=>$myrows['gid'],
							"question"=>$myrows['question'],
							"type"=>$myrows['type'],
							"mandatory"=>$myrows['mandatory'],
							"other"=>$myrows['other'],
							"title"=>$myrows['title']);
		        }
		    }
		}

		if (isset($postquestionlist) && is_array($postquestionlist))
		{
		    foreach ($postquestionlist as $pq)
		    {
		        $query = "SELECT q.qid, "
		        ."q.sid, "
		        ."q.gid, "
		        ."q.question, "
		        ."q.type, "
		        ."q.title, "
		        ."q.other, "
		        ."q.mandatory "
		        ."FROM {$dbprefix}questions q, "
		        ."{$dbprefix}groups g "
		        ."WHERE q.gid=g.gid AND "
		        ."q.parent_qid=0 AND "
		        ."q.qid=$pq AND "
		        ."q.language='".GetBaseLanguageFromSurveyID($surveyid)."' AND "
		        ."g.language='".GetBaseLanguageFromSurveyID($surveyid)."'";


		        $result = db_execute_assoc($query) or safe_die("Couldn't get postquestions $qid<br />$query<br />".$connect->ErrorMsg());

		        $postcount=$result->num_rows();

		        foreach ($result->result_array() as $myrows)
		        {
		            $postrows[]=array("qid"=>$myrows['qid'],
		                        "sid"=>$myrows['sid'],
		                        "gid"=>$myrows['gid'],
		                        "question"=>$myrows['question'],
		                        "type"=>$myrows['type'],
		                        "mandatory"=>$myrows['mandatory'],
		                        "other"=>$myrows['other'],
		                        "title"=>$myrows['title']);
		        } // while
		    }
		    $postquestionscount=count($postrows);
		}

		$questionscount=count($theserows);

		if (isset($postquestionscount) && $postquestionscount > 0)
		{ //Build the array used for the questionNav and copyTo select boxes
		foreach ($postrows as $pr)
		{
		    $pquestions[]=array("text"=>$pr['title'].": ".substr(strip_tags($pr['question']), 0, 80),
				"fieldname"=>$pr['sid']."X".$pr['gid']."X".$pr['qid']);
		}
		}

		// Previous question parsing ==> building cquestions[] and canswers[]
		if ($questionscount > 0)
		{
		    $X="X";

		    foreach($theserows as $rows)
		    {
		        $shortquestion=$rows['title'].": ".strip_tags($rows['question']);

		        if ($rows['type'] == "A" ||
		        $rows['type'] == "B" ||
		        $rows['type'] == "C" ||
		        $rows['type'] == "E" ||
		        $rows['type'] == "F" ||
		        $rows['type'] == "H" )
		        {
		            $aquery="SELECT * "
		            ."FROM {$dbprefix}questions "
		            ."WHERE parent_qid={$rows['qid']} "
		            ."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		            ."ORDER BY question_order";

		            $aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to Array questions<br />$aquery<br />".$connect->ErrorMsg());

		            foreach ($aresult->result_array() as $arows)
		            {
		                $shortanswer = "{$arows['title']}: [" . FlattenText($arows['question']) . "]";
		                $shortquestion=$rows['title'].":$shortanswer ".FlattenText($rows['question']);
		                $cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title']);

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
		                        $fquery = "SELECT * "
		                        ."FROM {$dbprefix}answers "
		                        ."WHERE qid={$rows['qid']} "
		                        ."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		                        ."AND scale_id=0 "
		                        ."ORDER BY sortorder, code ";
		                        $fresult = db_execute_assoc($fquery);
		                        foreach ($fresult->result_array() as $frow)
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
		                $maxvalue=$qidattributes['multiflexible_max'];
		            } else {
		                $maxvalue=10;
		            }
		            if (isset($qidattributes['multiflexible_min']) && trim($qidattributes['multiflexible_min'])!='') {
		                $minvalue=$qidattributes['multiflexible_min'];
		            } else {
		                $minvalue=1;
		            }
		            if (isset($qidattributes['multiflexible_step']) && trim($qidattributes['multiflexible_step'])!='') {
		                $stepvalue=$qidattributes['multiflexible_step'];
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
		            ." FROM ".$this->db->dbprefix('questions')." sq, ".$this->db->dbprefix('questions')." q"
		            ." WHERE sq.sid=$surveyid AND sq.parent_qid=q.qid "
		            . "AND q.language='".GetBaseLanguageFromSurveyID($surveyid)."'"
		            ." AND sq.language='".GetBaseLanguageFromSurveyID($surveyid)."'"
		            ." AND q.qid={$rows['qid']}
		               AND sq.scale_id=0
		               ORDER BY sq.question_order";

		            $y_axis_db = db_execute_assoc($fquery);

		             // Get the X-Axis
		             $aquery = "SELECT sq.*
		                         FROM ".$this->db->dbprefix('questions')." q, ".$this->db->dbprefix('questions')." sq
		                         WHERE q.sid=$surveyid
		                         AND sq.parent_qid=q.qid
		                         AND q.language='".GetBaseLanguageFromSurveyID($surveyid)."'
		                         AND sq.language='".GetBaseLanguageFromSurveyID($surveyid)."'
		                         AND q.qid=".$rows['qid']."
		                         AND sq.scale_id=1
		                         ORDER BY sq.question_order";

		            $x_axis_db=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to Array questions<br />$aquery<br />".$connect->ErrorMsg());

		            foreach ($x_axis_db->result_array() as $frow)
		            {
		                $x_axis[$frow['title']]=$frow['question'];
		            }

		            foreach ($y_axis_db->result_array() as $arow)
		            {
		                foreach($x_axis as $key=>$val)
		                {
		                    $shortquestion=$rows['title'].":{$arows['title']}:$key: [".strip_tags($arows['question']). "][" .strip_tags($val). "] " . FlattenText($rows['question']);
		                    $cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title']."_".$key);

		                    if ($rows['type'] == ":")
		                    {
		                        for($ii=$minvalue; $ii<=$maxvalue; $ii+=$stepvalue)
		                        {
		                            $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title'], $ii, $ii);
		                        }
		                    }
		                }
		            }
		            unset($x_axis);
		        } //if A,B,C,E,F,H
		        elseif ($rows['type'] == "1") //Multi Scale
		        {
		            $aquery="SELECT * "
		            ."FROM {$dbprefix}questions "
		            ."WHERE parent_qid={$rows['qid']} "
		            ."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		            ."ORDER BY question_order";
		            $aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to Array questions<br />$aquery<br />".$connect->ErrorMsg());

		            foreach ($aresult->result_array() as $arows)
		            {
		                $attr = getQuestionAttributeValues($rows['qid']);
		                $label1 = isset($attr['dualscale_headerA']) ? $attr['dualscale_headerA'] : 'Label1';
		                $label2 = isset($attr['dualscale_headerB']) ? $attr['dualscale_headerB'] : 'Label2';
		                $shortanswer = "{$arows['title']}: [" . strip_tags($arows['question']) . "][$label1]";
		                $shortquestion=$rows['title'].":$shortanswer ".strip_tags($rows['question']);
		                $cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title']."#0");

		                $shortanswer = "{$arows['title']}: [" . strip_tags($arows['question']) . "][$label2]";
		                $shortquestion=$rows['title'].":$shortanswer ".strip_tags($rows['question']);
		                $cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title']."#1");

		                // first label
		                $lquery="SELECT * "
		                ."FROM {$dbprefix}answers "
		                ."WHERE qid={$rows['qid']} "
		                ."AND scale_id=0 "
		                ."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		                ."ORDER BY sortorder, answer";
		                $lresult=db_execute_assoc($lquery) or safe_die ("Couldn't get labels to Array <br />$lquery<br />".$connect->ErrorMsg());
		                foreach ($lresult->result_array() as $lrows)
		                {
		                    $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['title']."#0", "{$lrows['code']}", "{$lrows['code']}");
		                }

		                // second label
		                $lquery="SELECT * "
		                ."FROM {$dbprefix}answers "
		                ."WHERE qid={$rows['qid']} "
		                ."AND scale_id=1 "
		                ."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		                ."ORDER BY sortorder, answer";
		                $lresult=db_execute_assoc($lquery) or safe_die ("Couldn't get labels to Array <br />$lquery<br />".$connect->ErrorMsg());
		                foreach ($lresult->result_array() as $lrows)
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
		            $aquery="SELECT * "
		            ."FROM {$dbprefix}questions "
		            ."WHERE parent_qid={$rows['qid']} "
		            ."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		            ."ORDER BY question_order";
		            $aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to Array questions<br />$aquery<br />".$connect->ErrorMsg());

		            foreach ($aresult->result_array() as $arows)
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
		            $aquery="SELECT * "
		            ."FROM {$dbprefix}answers "
		            ."WHERE qid={$rows['qid']} "
		            ."AND ".$this->db->dbprefix('answers').".language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		            ."AND scale_id=0 "
		            ."ORDER BY sortorder, answer";
		            $aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to Ranking question<br />$aquery<br />".$connect->ErrorMsg());
		            $acount=$aresult->num_rows();
		            foreach ($aresult->result_array() as $arow)
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
		            $shortquestion=$rows['title'].":$shortanswer ".strip_tags($rows['question']);
		            $cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid']);
		            $aquery="SELECT * "
		            ."FROM {$dbprefix}questions "
		            ."WHERE parent_qid={$rows['qid']} "
		            ."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		            ."ORDER BY question_order";
		            $aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to this question<br />$aquery<br />".$connect->ErrorMsg());

		            foreach ($aresult->result_array() as $arows)
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
		                    $aquery="SELECT * "
		                    ."FROM {$dbprefix}answers "
		                    ."WHERE qid={$rows['qid']} "
		                    ."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		                    ."AND scale_id=0 "
		                    ."ORDER BY sortorder, "
		                    ."answer";
		                    // Ranking question? Replacing "Ranking" by "this"
		                    $aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to this question<br />$aquery<br />".$connect->ErrorMsg());

		                    foreach ($aresult->result_array() as $arows)
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
		                        if ( ($rows['type'] == "L" ||
		                        $rows['type'] == "!") &&
		                        $rows['other'] == "Y")
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


		$quesitonNavOptions = "<optgroup class='activesurveyselect' label='".$clang->gT("Before","js")."'>";
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
		    $quesitonNavOptions .= "<option value='".site_url("/admin/conditions/editconditionsform/$surveyid/{$row['gid']}/{$row['qid']}")."'>{$row['title']}: ".$questionselecter."</option>";
		}
		$quesitonNavOptions .= "</optgroup>\n";
		$quesitonNavOptions .= "<optgroup class='activesurveyselect' label='".$clang->gT("Current","js")."'>\n";
		$question=strip_tags($questiontext);
		if (strlen($question)<35)
		{
		    $questiontextshort = $question;
		}
		else
		{
		    //$questiontextshort = substr($question, 0, 35)."..";
		    $questiontextshort = htmlspecialchars(mb_strcut(html_entity_decode($question,ENT_QUOTES,'UTF-8'), 0, 35, 'UTF-8'))."...";
		}
		$quesitonNavOptions .= "<option value='".site_url("/admin/conditions/editconditionsform/$surveyid/$gid/$qid")."' selected='selected'>$questiontitle: $questiontextshort</option>";
		$quesitonNavOptions .= "</optgroup>\n";
		$quesitonNavOptions .= "<optgroup class='activesurveyselect' label='".$clang->gT("After","js")."'>\n";
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
		    $quesitonNavOptions .=  "<option value='".site_url("/admin/conditions/editconditionsform/$surveyid/{$row['gid']}/{$row['qid']}")."'>{$row['title']}: ".$questionselecter."</option>";
		}
		$quesitonNavOptions .= "</optgroup>\n";

		//Now display the information and forms
		//BEGIN: PREPARE JAVASCRIPT TO SHOW MATCHING ANSWERS TO SELECTED QUESTION
		$javascriptpre = "<script type='text/javascript'>\n"
		."<!--\n"
		."\tvar Fieldnames = new Array();\n"
		."\tvar Codes = new Array();\n"
		."\tvar Answers = new Array();\n"
		."\tvar QFieldnames = new Array();\n"
		."\tvar Qcqids = new Array();\n"
		."\tvar Qtypes = new Array();\n";
		$jn=0;
		if (isset($canswers))
		{
            foreach($canswers as $can)
            {
                $an=json_encode(FlattenText($can[2]));
                $conditionsoutput_main_content .= "Fieldnames[$jn]='$can[0]';\n"
                ."Codes[$jn]='$can[1]';\n"
                ."Answers[$jn]={$an};\n";
                $jn++;
            }
		}
		$jn=0;

		if (isset($cquestions))
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
		."</script>\n";

		//END: PREPARE JAVASCRIPT TO SHOW MATCHING ANSWERS TO SELECTED QUESTION


		$css_admin_includes[] = $this->config->item("generalscripts").'jquery/css/jquery.multiselect.css';
		$this->config->set_item("css_admin_includes", $css_admin_includes);
		self::_getAdminHeader();
		$data['clang'] = $clang;
		$data['surveyid'] = $surveyid;
		$data['qid'] = $qid;
		$data['gid'] = $gid;
		$data['imageurl'] = $imageurl;
		$data['extraGetParams'] = $extraGetParams;
		$data['quesitonNavOptions'] = $quesitonNavOptions;
		$data['conditionsoutput_action_error'] = $conditionsoutput_action_error;
		$data['javascriptpre'] = $javascriptpre;
		$this->load->view("admin/conditions/conditionshead_view",$data);


		//BEGIN DISPLAY CONDITIONS FOR THIS QUESTION
		if ($subaction=='index' ||
		$subaction=='editconditionsform' || $subaction=='insertcondition' ||
		$subaction == "editthiscondition" || $subaction == "delete" ||
		$subaction == "updatecondition" || $subaction == "deletescenario" ||
		$subaction == "renumberscenarios" || $subaction == "deleteallconditions" ||
		$subaction == "updatescenario" ||
		$subaction=='copyconditionsform' || $subaction=='copyconditions')
		{

		    //3: Get other conditions currently set for this question
		    $conditionscount=0;
		    $s=0;
		    $scenarioquery = "SELECT DISTINCT {$dbprefix}conditions.scenario "
		    ."FROM {$dbprefix}conditions "
		    ."WHERE {$dbprefix}conditions.qid=$qid\n"
		    ."ORDER BY {$dbprefix}conditions.scenario";
		    $scenarioresult = db_execute_assoc($scenarioquery) or safe_die ("Couldn't get other (scenario) conditions for question $qid<br />$query<br />".$connect->Error);
		    $scenariocount=$scenarioresult->num_rows();

		    $showreplace="$questiontitle". self::_showSpeaker($questiontext);
		    $onlyshow=str_replace("{QID}", $showreplace, $clang->gT("Only show question {QID} IF"));

			$data['conditionsoutput'] = $conditionsoutput_main_content;
			$data['extraGetParams'] = $extraGetParams;
			$data['quesitonNavOptions'] = $quesitonNavOptions;
			$data['conditionsoutput_action_error'] = $conditionsoutput_action_error;
			$data['javascriptpre'] = $javascriptpre;
			$data['onlyshow'] = $onlyshow;
			$data['subaction'] = $subaction;
			$data['scenariocount'] = $scenariocount;
			$this->load->view("admin/conditions/conditionslist_view",$data);

		    if ($scenariocount > 0)
		    {

				self::_js_admin_includes($this->config->item("generalscripts").'jquery/jquery.checkgroup.js');
		        foreach ($scenarioresult->result_array() as $scenarionr)
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
		                ."<script type='text/javascript'>$(document).ready(function () { $('#scenarioCbx{$scenarionr['scenario']}').checkgroup({groupName:'aConditionFromScenario{$scenarionr['scenario']}'}); });</script>"
		                ."</td><td>&nbsp;</td>\n";
		            }
		            else
		            {
		                $initialCheckbox = "";
		            }

		            $conditionsoutput_main_content .= "<tr><td>\n"
		            ."<table width='100%' cellspacing='0'><tr>$initialCheckbox<td width='90%'>$scenariotext&nbsp;\n"
		            ."<form action='".site_url("/admin/conditions/updatescenario/$surveyid/$gid/$qid/")."' method='post' id='editscenario{$scenarionr['scenario']}' style='display: none'>\n"
		            ."<label>".$clang->gT("New scenario number").":&nbsp;\n"
		            ."<input type='text' name='newscenarionum' size='3'/></label>\n"
		            ."<input type='hidden' name='scenario' value='{$scenarionr['scenario']}'/>\n"
		            ."<input type='hidden' name='sid' value='$surveyid' />\n"
		            ."<input type='hidden' name='gid' value='$gid' />\n"
		            ."<input type='hidden' name='qid' value='$qid' />\n"
		            ."<input type='hidden' name='subaction' value='updatescenario' />&nbsp;&nbsp;\n"
		            ."<input type='submit' name='scenarioupdated' value='".$clang->gT("Update scenario")."' />\n"
		            ."<input type='button' name='cancel' value='".$clang->gT("Cancel")."' onclick=\"$('#editscenario{$scenarionr['scenario']}').hide('slow');\"/>\n"
		            ."</form></td>\n"
		            . "<td width='10%' valign='middle' align='right'><form id='deletescenario{$scenarionr['scenario']}' action='".site_url("/admin/conditions/deletescenario/$surveyid/$gid/$qid/")."' method='post' name='deletescenario{$scenarionr['scenario']}' style='margin-bottom:0;'>\n";

		            if ($scenariotext != "" && ($subaction == "editconditionsform" ||$subaction == "insertcondition" ||
		            $subaction == "updatecondition" || $subaction == "editthiscondition" ||
		            $subaction == "renumberscenarios" || $subaction == "updatescenario" ||
		            $subaction == "deletescenario" || $subaction == "delete") )
		            {
		                $conditionsoutput_main_content .= "\t<a href='#' "
		                ." onclick=\"if ( confirm('".$clang->gT("Are you sure you want to delete all conditions set in this scenario?","js")."')) {document.getElementById('deletescenario{$scenarionr['scenario']}').submit();}\""
		                ." title='".$clang->gTview("Delete this scenario")."' >"
		                ." <img src='$imageurl/scenario_delete.png' ".$clang->gT("Delete this scenario")." name='DeleteWholeGroup' /></a>\n";

		                $conditionsoutput_main_content .= "\t<a href='#' "
		                ." id='editscenariobtn{$scenarionr['scenario']}'"
		                ." onclick=\"$('#editscenario{$scenarionr['scenario']}').toggle('slow');\""
		                ." title='".$clang->gTview("Edit scenario")."' >"
		                ." <img src='$imageurl/scenario_edit.png' alt='".$clang->gT("Edit scenario")."' name='DeleteWholeGroup' /></a>\n";

		            }

		            $conditionsoutput_main_content .= "\t<input type='hidden' name='scenario' value='{$scenarionr['scenario']}' />\n"
		            ."\t<input type='hidden' name='qid' value='$qid' />\n"
		            ."\t<input type='hidden' name='sid' value='$surveyid' />\n"
		            ."\t<input type='hidden' name='subaction' value='deletescenario' />\n"
		            ."</form></td></tr></table></td></tr>\n";

		            unset($currentfield);

		            $query = "SELECT {$dbprefix}conditions.cid, "
		            ."{$dbprefix}conditions.scenario, "
		            ."{$dbprefix}conditions.cqid, "
		            ."{$dbprefix}conditions.cfieldname, "
		            ."{$dbprefix}conditions.method, "
		            ."{$dbprefix}conditions.value, "
		            ."{$dbprefix}questions.type "
		            ."FROM {$dbprefix}conditions, "
		            ."{$dbprefix}questions, "
		            ."{$dbprefix}groups "
		            ."WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid "
		            ."AND {$dbprefix}questions.gid={$dbprefix}groups.gid "
		            ."AND {$dbprefix}questions.parent_qid=0 "
		            ."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		            ."AND {$dbprefix}groups.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		            ."AND {$dbprefix}conditions.qid=$qid "
		            ."AND {$dbprefix}conditions.scenario={$scenarionr['scenario']}\n"
		            ."AND {$dbprefix}conditions.cfieldname NOT LIKE '{%' \n" // avoid catching SRCtokenAttr conditions
		            ."ORDER BY {$dbprefix}groups.group_order,{$dbprefix}questions.question_order";
		            $result = db_execute_assoc($query) or safe_die ("Couldn't get other conditions for question $qid<br />$query<br />".$connect->ErrorMsg());
		            $conditionscount=$result->num_rows();

		            $querytoken = "SELECT {$dbprefix}conditions.cid, "
		            ."{$dbprefix}conditions.scenario, "
		            ."{$dbprefix}conditions.cqid, "
		            ."{$dbprefix}conditions.cfieldname, "
		            ."{$dbprefix}conditions.method, "
		            ."{$dbprefix}conditions.value, "
		            ."'' AS type "
		            ."FROM {$dbprefix}conditions "
		            ."WHERE "
		            ." {$dbprefix}conditions.qid=$qid "
		            ."AND {$dbprefix}conditions.scenario={$scenarionr['scenario']}\n"
		            ."AND {$dbprefix}conditions.cfieldname LIKE '{%' \n" // only catching SRCtokenAttr conditions
		            ."ORDER BY {$dbprefix}conditions.cfieldname";
		            $resulttoken = db_execute_assoc($querytoken) or safe_die ("Couldn't get other conditions for question $qid<br />$query<br />".$connect->ErrorMsg());
		            $conditionscounttoken=$resulttoken->num_rows();

		            $conditionscount=$conditionscount+$conditionscounttoken;

		            if ($conditionscount > 0)
		            {
		                $aConditionsMerged=Array();
		                foreach ($resulttoken->result_array() as $arow)
		                {
		                    $aConditionsMerged[]=$arow;
		                }
		                foreach ($result->result_array() as $arow)
		                {
		                    $aConditionsMerged[]=$arow;
		                }

		                //				while ($rows=$result->FetchRow())
		                foreach ($aConditionsMerged as $rows)
		                {
		                    if($rows['method'] == "") {$rows['method'] = "==";} //Fill in the empty method from previous versions
		                    $markcidstyle="";
		                    if (array_search($rows['cid'], $markcidarray) === FALSE) // PHP5
		                    // === required cause key 0 would otherwise be interpreted as FALSE
		                    {
		                        $markcidstyle="";
		                    }
		                    else {
		                        // This is the style used when the condition editor is called
		                        // in order to check which conditions prevent a question deletion
		                        $markcidstyle="background-color: #5670A1;";
		                    }
		                    if ($subaction == "editthiscondition" && isset($p_cid) &&
		                    $rows['cid'] === $p_cid)
		                    {
		                        // Style used when editing a condition
		                        $markcidstyle="background-color: #FCCFFF;";
		                    }

		                    if (isset($currentfield) && $currentfield != $rows['cfieldname'])
		                    {
		                        $conditionsoutput_main_content .= "<tr class='evenrow'>\n"
		                        ."\t<td valign='middle' align='center'>\n"
		                        ."<font size='1'><strong>"
		                        .$clang->gT("and")."</strong></font></td></tr>";
		                    }
		                    elseif (isset($currentfield))
		                    {
		                        $conditionsoutput_main_content .= "<tr class='evenrow'>\n"
		                        ."\t<td valign='top' align='center'>\n"
		                        ."<font size='1'><strong>"
		                        .$clang->gT("OR")."</strong></font></td></tr>";
		                    }
		                    $conditionsoutput_main_content .= "\t<tr class='oddrow' style='$markcidstyle'>\n"
		                    ."\t<td><form style='margin-bottom:0;' name='conditionaction{$rows['cid']}' id='conditionaction{$rows['cid']}' method='post' action='".site_url("/admin/conditions/conditions/$surveyid/$gid/$qid/")."'>\n"
		                    ."<table width='100%' style='height: 13px;' cellspacing='0' cellpadding='0'>\n"
		                    ."\t<tr>\n";

		                    if ( $subaction == "copyconditionsform" || $subaction == "copyconditions")
		                    {
		                        $conditionsoutput_main_content .= "<td>&nbsp;&nbsp;</td>"
		                        . "<td valign='middle' align='right'>\n"
		                        . "\t<input type='checkbox' name='aConditionFromScenario{$scenarionr['scenario']}' id='cbox{$rows['cid']}' value='{$rows['cid']}' checked='checked'/>\n"
		                        . "</td>\n";
		                    }
		                    $conditionsoutput_main_content .= ""
		                    ."<td valign='middle' align='right' width='40%'>\n"
		                    ."\t<font size='1' face='verdana'>\n";

		                    $leftOperandType = 'unknown'; // prevquestion, tokenattr
		                    if ($thissurvey['anonymized'] != 'Y' && preg_match('/^{TOKEN:([^}]*)}$/',$rows['cfieldname'],$extractedTokenAttr) > 0)
		                    {
		                        $leftOperandType = 'tokenattr';
		                        $aTokenAttrNames=GetTokenFieldsAndNames($surveyid);
		                        if (count($aTokenAttrNames) != 0)
		                        {
		                            $thisAttrName=html_escape($aTokenAttrNames[strtolower($extractedTokenAttr[1])])." [".$clang->gT("From token table")."]";
		                        }
		                        else
		                        {
		                            $thisAttrName=html_escape($extractedTokenAttr[1])." [".$clang->gT("Inexistant token table")."]";
		                        }
		                        $conditionsoutput_main_content .= "\t$thisAttrName\n";
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
		                                $conditionsoutput_main_content .= "\t$cqn[0] (qid{$rows['cqid']})\n";
		                                $conditionsList[]=array("cid"=>$rows['cid'],
											"text"=>$cqn[0]." ({$rows['value']})");
		                            }
		                            else
		                            {
		                                //$conditionsoutput_main_content .= "\t<font color='red'>ERROR: Delete this condition. It is out of order.</font>\n";
		                            }
		                        }
		                    }

		                    $conditionsoutput_main_content .= "\t</font></td>\n"
		                    ."\t<td align='center' valign='middle' width='20%'>\n"
		                    ."<font size='1'>\n" //    .$clang->gT("Equals")."</font></td>"
		                    .$method[trim ($rows['method'])]
		                    ."</font>\n"
		                    ."\t</td>\n"
		                    ."\n"
		                    ."\t<td align='left' valign='middle' width='30%'>\n"
		                    ."<font size='1' face='verdana'>\n";

		                    // let's read the condition's right operand
		                    // determine its type and display it
		                    $rightOperandType = 'unknown'; // predefinedAnsw,constantVal, prevQsgqa, tokenAttr, regexp
		                    if ($rows['method'] == 'RX')
		                    {
		                        $rightOperandType = 'regexp';
		                        $conditionsoutput_main_content .= "".html_escape($rows['value'])."\n";
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

		                        $conditionsoutput_main_content .= "".html_escape($matchedSGQAText)."\n";
		                    }
		                    elseif ($thissurvey['anonymized'] != 'Y' && preg_match('/^{TOKEN:([^}]*)}$/',$rows['value'],$extractedTokenAttr) > 0)
		                    {
		                        $rightOperandType = 'tokenAttr';
		                        $aTokenAttrNames=GetTokenFieldsAndNames($surveyid);
		                        if (count($aTokenAttrNames) != 0)
		                        {
		                            $thisAttrName=html_escape($aTokenAttrNames[strtolower($extractedTokenAttr[1])])." [".$clang->gT("From token table")."]";
		                        }
		                        else
		                        {
		                            $thisAttrName=html_escape($extractedTokenAttr[1])." [".$clang->gT("Inexistant token table")."]";
		                        }
		                        $conditionsoutput_main_content .= "\t$thisAttrName\n";
		                    }
		                    elseif (isset($canswers))
		                    {
		                        foreach ($canswers as $can)
		                        {
		                            if ($can[0] == $rows['cfieldname'] && $can[1] == $rows['value'])
		                            {
		                                $conditionsoutput_main_content .= "$can[2] ($can[1])\n";
		                                $rightOperandType = 'predefinedAnsw';

		                            }
		                        }
		                    }
		                    // if $rightOperandType is still unkown then it is a simple constant
		                    if ($rightOperandType == 'unknown')
		                    {
		                        $rightOperandType = 'constantVal';
		                        if ($rows['value'] == ' ' ||
		                        $rows['value'] == '')
		                        {
		                            $conditionsoutput_main_content .= "".$clang->gT("No answer")."\n";
		                        }
		                        else
		                        {
		                            $conditionsoutput_main_content .= "".html_escape($rows['value'])."\n";
		                        }
		                    }

		                    $conditionsoutput_main_content .= "\t</font></td>\n"
		                    ."\t<td align='right' valign='middle' width='10%'>\n";

		                    if ($subaction == "editconditionsform" ||$subaction == "insertcondition" ||
		                    $subaction == "updatecondition" || $subaction == "editthiscondition" ||
		                    $subaction == "renumberscenarios" || $subaction == "deleteallconditions" ||
		                    $subaction == "updatescenario" ||
		                    $subaction == "deletescenario" || $subaction == "delete")
		                    { // show single condition action buttons in edit mode
		                        $conditionsoutput_main_content .= ""
		                        ."<a href='#' "
		                        ." onclick=\"if ( confirm('".$clang->gT("Are you sure you want to delete this condition?","js")."')) {\$('#editModeTargetVal{$rows['cid']}').remove();\$('#cquestions{$rows['cid']}').remove();document.getElementById('conditionaction{$rows['cid']}').submit();}\""
		                        ." title='".$clang->gTview("Delete this condition")."' >"
		                        ." <img src='$imageurl/conditions_delete_16.png'  alt='".$clang->gT("Delete this condition")."' name='DeleteThisCondition' title='' /></a>\n"
		                        ."<a href='#' "
		                        ." onclick='document.getElementById(\"subaction{$rows['cid']}\").value=\"editthiscondition\";document.getElementById(\"conditionaction{$rows['cid']}\").submit();'>"
		                        ." <img src='$imageurl/conditions_edit_16.png'  alt='".$clang->gT("Edit this condition")."' name='EditThisCondition' /></a>\n"
		                        ."\t<input type='hidden' name='subaction' id='subaction{$rows['cid']}' value='delete' />\n"
		                        ."\t<input type='hidden' name='cid' value='{$rows['cid']}' />\n"
		                        ."\t<input type='hidden' name='scenario' value='{$rows['scenario']}' />\n"
		                        //							."\t<input type='hidden' id='cquestions{$rows['cid']}'  name='cquestions' value='{$rows['cfieldname']}' />\n"
		                        ."\t<input type='hidden' name='method' value='{$rows['method']}' />\n"
		                        ."\t<input type='hidden' name='sid' value='$surveyid' />\n"
		                        ."\t<input type='hidden' name='gid' value='$gid' />\n"
		                        ."\t<input type='hidden' name='qid' value='$qid' />\n";
		                        // now sets e corresponding hidden input field
		                        // depending on the leftOperandType
		                        if ($leftOperandType == 'tokenattr')
		                        {
		                            $conditionsoutput_main_content .= ""
		                            ."\t<input type='hidden' id='csrctoken{$rows['cid']}' name='csrctoken' value='".html_escape($rows['cfieldname'])."' />\n";
		                        }
		                        else
		                        {
		                            $conditionsoutput_main_content .= ""
		                            ."\t<input type='hidden' id='cquestions{$rows['cid']}' name='cquestions' value='".html_escape($rows['cfieldname'])."' />\n";
		                        }

		                        // now set the corresponding hidden input field
		                        // depending on the rightOperandType
		                        // This is used when Editting a condition
		                        if ($rightOperandType == 'predefinedAnsw')
		                        {
		                            $conditionsoutput_main_content .= ""
		                            ."\t<input type='hidden' name='EDITcanswers[]' id='editModeTargetVal{$rows['cid']}' value='".html_escape($rows['value'])."' />\n";
		                        }
		                        elseif ($rightOperandType == 'prevQsgqa')
		                        {
		                            $conditionsoutput_main_content .= ""
		                            ."\t<input type='hidden' id='editModeTargetVal{$rows['cid']}' name='EDITprevQuestionSGQA' value='".html_escape($rows['value'])."' />\n";
		                        }
		                        elseif ($rightOperandType == 'tokenAttr')
		                        {
		                            $conditionsoutput_main_content .= ""
		                            ."\t<input type='hidden' id='editModeTargetVal{$rows['cid']}' name='EDITtokenAttr' value='".html_escape($rows['value'])."' />\n";
		                        }
		                        elseif ($rightOperandType == 'regexp')
		                        {
		                            $conditionsoutput_main_content .= ""
		                            ."\t<input type='hidden' id='editModeTargetVal{$rows['cid']}' name='EDITConditionRegexp' value='".html_escape($rows['value'])."' />\n";
		                        }
		                        else
		                        {
		                            $conditionsoutput_main_content .= ""
		                            ."\t<input type='hidden' id='editModeTargetVal{$rows['cid']}' name='EDITConditionConst' value='".html_escape($rows['value'])."' />\n";
		                        }
		                    }

		                    $conditionsoutput_main_content .= ""
		                    ."\t</td>\n"
		                    ."\t</tr>\n"
		                    ."\t</table></form>\n"
		                    ."\t</td>\n"
		                    ."\t</tr>\n";
		                    $currentfield=$rows['cfieldname'];
		                }
		                $conditionsoutput_main_content .= "\t<tr>\n"
		                ."<td height='3'>\n"
		                ."</td>\n"
		                ."\t</tr>\n";
		            }
		            else
		            {
		                $conditionsoutput_main_content .= "\t<tr>\n"
		                ."<td colspan='3' height='3'>\n"
		                ."</td>\n"
		                ."\t</tr>\n";
		            }
		            $s++;
		        }
		    }
		    else
		    { // no condition ==> disable delete all conditions button, and display a simple comment
		        $conditionsoutput_main_content .= "<tr><td valign='middle' align='center'>".$clang->gT("This question is always shown.")."\n"
		        . "</td></tr>\n";
		    }
		    $conditionsoutput_main_content .= ""
		    . "</table>\n";

		    $conditionsoutput_main_content .= "</td></tr>\n";

		}
		//END DISPLAY CONDITIONS FOR THIS QUESTION


		// Separator
		$conditionsoutput_main_content .= "\t<tr bgcolor='#555555'><td colspan='3'></td></tr>\n";


		// BEGIN: DISPLAY THE COPY CONDITIONS FORM
		if ($subaction == "copyconditionsform" || $subaction == "copyconditions")
		{
		    $conditionsoutput_main_content .= "<tr class=''><td colspan='3'>\n"
		    ."<form action='".site_url("admin/conditions/copyconditions/$surveyid/$gid/$qid/")."' name='copyconditions' id='copyconditions' method='post'>\n";

		    $conditionsoutput_main_content .= "<div class='header ui-widget-header'>".$clang->gT("Copy conditions")."</div>\n";


		    //CopyConditionsMessage
		    if (isset ($CopyConditionsMessage))
		    {
		        $conditionsoutput_main_content .= "<div class='messagebox ui-corner-all'>\n"
		        ."$CopyConditionsMessage\n"
		        ."</div>\n";
		    }

		    if (isset($conditionsList) && is_array($conditionsList))
		    {
		        //TIBO
				self::_js_admin_includes($this->config->item("generalscripts").'jquery/jquery.multiselect.min.js');

		        $conditionsoutput_main_content .= "<script type='text/javascript'>$(document).ready(function () { $('#copytomultiselect').multiselect( {autoOpen: true, noneSelectedText: '".$clang->gT("No questions selected")."', checkAllText: '".$clang->gT("Check all")."', uncheckAllText: '".$clang->gT("Uncheck all")."', selectedText: '# ".$clang->gT("selected")."', beforeclose: function(){return false;},height: 200 } ); });</script>";

		        $conditionsoutput_main_content .= "\t<div class='conditioncopy-tbl-row'>\n"
		        ."\t<div class='condition-tbl-left'>".$clang->gT("Copy the selected conditions to").":</div>\n"
		        ."\t<div class='condition-tbl-right'>\n"
		        ."\t\t<select name='copyconditionsto[]'id='copytomultiselect'  multiple style='font-family:verdana; font-size:10; width:600px' size='10'>\n";
		        if (isset($pquestions) && count($pquestions) != 0)
		        {
		            foreach ($pquestions as $pq)
		            {
		                $conditionsoutput_main_content .= "\t\t<option value='{$pq['fieldname']}'>".$pq['text']."</option>\n";
		            }
		        }
		        $conditionsoutput_main_content .= "\t\t</select>\n"
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

		        $conditionsoutput_main_content .= "\t<div class='condition-tbl-full'>\n"
		//        ."\t\t<input type='submit' value='".$clang->gT("Copy conditions")."' onclick=\"if (confirm('".$clang->gT("Are you sure you want to copy these condition(s) to the questions you have selected?","js")."')){prepareCopyconditions(); return true;} else {return false;}\" $disableCopyCondition/>\n"
		        ."\t\t<input type='submit' value='".$clang->gT("Copy conditions")."' onclick=\"prepareCopyconditions(); return true;\" $disableCopyCondition/>\n"
		        ."<input type='hidden' name='subaction' value='copyconditions' />\n"
		        ."<input type='hidden' name='sid' value='$surveyid' />\n"
		        ."<input type='hidden' name='gid' value='$gid' />\n"
		        ."<input type='hidden' name='qid' value='$qid' />\n"
		        ."</div>\n";

		        $conditionsoutput_main_content .= "<script type=\"text/javascript\">\n"
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
		        $conditionsoutput_main_content .= "<div class='messagebox ui-corner-all'>\n"
		        ."<div class='partialheader'>".$clang->gT("This survey's questions don't use conditions")."</div><br />\n"
		        ."</div>\n";
		    }

		    $conditionsoutput_main_content .= "</form></td></tr>\n";

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
		    $conditionsoutput_main_content .= "<tr><td colspan='3'>\n";
		    $conditionsoutput_main_content .= "<form action='".site_url("/admin/conditions/conditions/$surveyid/$gid/$qid/")."' name='editconditions' id='editconditions' method='post'>\n";
		    if ($subaction == "editthiscondition" &&  isset($p_cid))
		    {
		        $mytitle = $clang->gT("Edit condition");
		    }
		    else
		    {
		        $mytitle = $clang->gT("Add condition");
		    }
		    $conditionsoutput_main_content .= "<div class='header ui-widget-header'>".$mytitle."</div>\n";

		    ///////////////////////////////////////////////////////////////////////////////////////////

		    // Begin "Scenario" row
		    if  ( ( $subaction != "editthiscondition" && isset($scenariocount) && ($scenariocount == 1 || $scenariocount==0)) ||
		    ( $subaction == "editthiscondition" && isset($scenario) && $scenario == 1) )
		    {
		        $scenarioAddBtn = "\t<a id='scenarioaddbtn' href='#' title='".$clang->gTview('Add scenario')."' onclick=\"$('#scenarioaddbtn').hide();$('#defaultscenariotxt').hide('slow');$('#scenario').show('slow');\">"
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

		    $conditionsoutput_main_content .="<div class='condition-tbl-row'>\n"
		    ."<div class='condition-tbl-left'>$scenarioAddBtn&nbsp;".$clang->gT("Scenario")."</div>\n"
		    ."<div class='condition-tbl-right'><input type='text' name='scenario' id='scenario' value='1' size='2' $scenarioInputStyle/>"
		    ."$scenarioTxt\n"
		    ."</div>\n"
		    ."</div>\n";

		    // Begin "Question" row
		    $conditionsoutput_main_content .="<div class='condition-tbl-row'>\n"
		    ."<div class='condition-tbl-left'>".$clang->gT("Question")."</div>\n"
		    ."<div class='condition-tbl-right'>\n"
		    ."\t<div id=\"conditionsource\" class=\"tabs-nav\">\n"
		    ."\t<ul>\n"
		    ."\t<li><a href=\"#SRCPREVQUEST\"><span>".$clang->gT("Previous questions")."</span></a></li>\n"
		    ."\t<li><a href=\"#SRCTOKENATTRS\"><span>".$clang->gT("Token fields")."</span></a></li>\n"
		    ."\t</ul>\n";

		    // Previous question tab
		    $conditionsoutput_main_content .= "<div id='SRCPREVQUEST'><select name='cquestions' id='cquestions' size='".($qcount+1)."' >\n";
		    if (isset($cquestions))
		    {
		        $js_getAnswers_onload = "";
		        foreach ($cquestions as $cqn)
		        {
		            $conditionsoutput_main_content .= "<option value='$cqn[3]' title=\"".htmlspecialchars($cqn[0])."\"";
		            if (isset($p_cquestions) && $cqn[3] == $p_cquestions) {
		                $conditionsoutput_main_content .= " selected";
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
		            $conditionsoutput_main_content .= ">$cqn[0]</option>\n";
		        }
		    }

		    $conditionsoutput_main_content .= "</select>\n"
		    ."</div>\n";

		    // Source token Tab
		    $conditionsoutput_main_content .= "<div id='SRCTOKENATTRS'><select name='csrctoken' id='csrctoken' size='".($qcount+1)."' >\n";
		    foreach (GetTokenFieldsAndNames($surveyid) as $tokenattr => $tokenattrName)
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
		        $conditionsoutput_main_content .= "<option value='{TOKEN:".strtoupper($tokenattr)."}' $selectThisSrcTokenAttr>".html_escape($tokenattrName)."</option>\n";
		    }

		    $conditionsoutput_main_content .= "</select>\n"
		    ."</div>\n\n";

		    $conditionsoutput_main_content .= "\t</div>\n"; // end conditionsource div

		    $conditionsoutput_main_content .= "</div>\n"
		    ."</div>\n";

		    // Begin "Comparison operator" row
		    $conditionsoutput_main_content .="<div class='condition-tbl-row'>\n"
		    ."<div class='condition-tbl-left'>".$clang->gT("Comparison operator")."</div>\n"
		    ."<div class='condition-tbl-right'>\n"
		    ."<select name='method' id='method' style='font-family:verdana; font-size:10' >\n";
		    foreach ($method as $methodCode => $methodTxt)
		    {
		    	$selected=$methodCode=="==" ? " selected='selected'" : "";
		        $conditionsoutput_main_content .= "\t<option value='".$methodCode."'$selected>".$methodTxt."</option>\n";
		    }
		/**
		    ."\t<option value='<'>".$clang->gT("Less than")."</option>\n"
		    ."\t<option value='<='>".$clang->gT("Less than or equal to")."</option>\n"
		    ."\t<option selected='selected' value='=='>".$clang->gT("Equals")."</option>\n"
		    ."\t<option value='!='>".$clang->gT("Not equal to")."</option>\n"
		    ."\t<option value='>='>".$clang->gT("Greater than or equal to")."</option>\n"
		    ."\t<option value='>'>".$clang->gT("Greater than")."</option>\n"
		    ."\t<option value='RX'>".$clang->gT("Regular expression")."</option>\n"
		    ."\t<option value='a<b'>".$clang->gT("Less than (Strings)")."</option>\n"
		    ."\t<option value='a<=b'>".$clang->gT("Less than or equal to (Strings)")."</option>\n"
		    ."\t<option value='a>=b'>".$clang->gT("Greater than or equal to (Strings)")."</option>\n"
		    ."\t<option value='a>b'>".$clang->gT("Greater than (Strings)")."</option>\n"
		**/
		    $conditionsoutput_main_content .="</select>\n"
		    ."</div>\n"
		    ."</div>\n";

		    // Begin "Answer" row
		    $conditionsoutput_main_content .="<div class='condition-tbl-row'>\n"
		    ."<div class='condition-tbl-left'>".$clang->gT("Answer")."</div>\n";

		    if ($subaction == "editthiscondition")
		    {
		        $multipletext = "";
		        if (isset($_POST['EDITConditionConst']) && $_POST['EDITConditionConst'] != '')
		        {
		            $EDITConditionConst=html_escape($_POST['EDITConditionConst']);
		        }
		        else
		        {
		            $EDITConditionConst="";
		        }
		        if (isset($_POST['EDITConditionRegexp']) && $_POST['EDITConditionRegexp'] != '')
		        {
		            $EDITConditionRegexp=html_escape($_POST['EDITConditionRegexp']);
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
		            $EDITConditionConst=html_escape($_POST['ConditionConst']);
		        }
		        else
		        {
		            $EDITConditionConst="";
		        }
		        if (isset($_POST['ConditionRegexp']) && $_POST['ConditionRegexp'] != '')
		        {
		            $EDITConditionRegexp=html_escape($_POST['ConditionRegexp']);
		        }
		        else
		        {
		            $EDITConditionRegexp="";
		        }
		    }


		    $conditionsoutput_main_content .= ""
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
		    $conditionsoutput_main_content .= "\t<div id='CANSWERSTAB'>\n"
		    ."\t\t<select  name='canswers[]' $multipletext id='canswers' size='7'>\n"
		    ."\t\t</select>\n"
		    ."\t\t<br /><span id='canswersLabel'>".$clang->gT("Predefined answer options for this question")."</span>\n"
		    ."\t</div>\n";

		    // Constant tab
		    $conditionsoutput_main_content .= "\t<div id='CONST' style='display:' >\n"
		    ."\t\t<textarea name='ConditionConst' id='ConditionConst' rows='5' cols='113'>$EDITConditionConst</textarea>\n"
		    ."\t\t<br /><div id='ConditionConstLabel'>".$clang->gT("Constant value")."</div>\n"
		    ."\t</div>\n";
		    // Previous answers tab @SGQA@ placeholders
		    $conditionsoutput_main_content .= "\t<div id='PREVQUESTIONS'>\n"
		    ."\t\t<select name='prevQuestionSGQA' id='prevQuestionSGQA' size='7'>\n";
		    foreach ($cquestions as $cqn)
		    { // building the @SGQA@ placeholders options
		        if ($cqn[2] != 'M' && $cqn[2] != 'P')
		        { // Type M or P aren't real fieldnames and thus can't be used in @SGQA@ placehodlers
		            $conditionsoutput_main_content .= "\t\t<option value='@$cqn[3]@' title=\"".htmlspecialchars($cqn[0])."\"";
		            if (isset($p_prevquestionsgqa) && $p_prevquestionsgqa == "@".$cqn[3]."@")
		            {
		                $conditionsoutput_main_content .= " selected='selected'";
		            }
		            $conditionsoutput_main_content .= ">$cqn[0]</option>\n";
		        }
		    }
		    $conditionsoutput_main_content .= "\t\t</select>\n"
		    ."\t\t<br /><span id='prevQuestionSGQALabel'>".$clang->gT("Answers from previous questions")."</span>\n"
		    ."\t</div>\n";

		    // Token tab
		    $conditionsoutput_main_content .= "\t<div id='TOKENATTRS'>\n"
		    ."\t\t<select name='tokenAttr' id='tokenAttr' size='7'>\n";
		    foreach (GetTokenFieldsAndNames($surveyid) as $tokenattr => $tokenattrName)
		    {
		        $conditionsoutput_main_content .= "\t\t<option value='{TOKEN:".strtoupper($tokenattr)."}'>".html_escape($tokenattrName)."</option>\n";
		    }

		    $conditionsoutput_main_content .= "\t\t</select>\n"
		    ."\t\t<br /><span id='tokenAttrLabel'>".$clang->gT("Attributes values from the participant's token")."</span>\n"
		    ."\t</div>\n";

		    // Regexp Tab
		    $conditionsoutput_main_content .= "\t<div id='REGEXP' style='display:'>\n"
		    ."\t\t<textarea name='ConditionRegexp' id='ConditionRegexp' rows='5' cols='113'>$EDITConditionRegexp</textarea>\n"
		    ."\t\t<br /><div id='ConditionRegexpLabel'><a href=\"http://docs.limesurvey.org/tiki-index.php?page=Using+Regular+Expressions\" target=\"_blank\">".$clang->gT("Regular expression")."</a></div>\n"
		    ."\t</div>\n";

		    $conditionsoutput_main_content .= "</div>\n"; // end conditiontarget div


		    self::_js_admin_includes($this->config->item("adminscripts").'conditions.js');
		    self::_js_admin_includes($this->config->item("generalscripts").'jquery/lime-conditions-tabs.js');

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

		    $conditionsoutput_main_content .= "</div>\n"
		    ."</div>\n";

		    // Begin buttons row
		    $conditionsoutput_main_content .= "<div class='condition-tbl-full'>\n"
		    ."\t<input type='reset' id='resetForm' value='".$clang->gT("Clear")."' />\n"
		    ."\t<input type='submit' value='".$submitLabel."' />\n"
		    ."<input type='hidden' name='sid' value='$surveyid' />\n"
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

		    $conditionsoutput_main_content .= "<script type='text/javascript'>\n"
		    . "<!--\n"
		    . "\t".$js_getAnswers_onload."\n";
		    if (isset($p_method))
		    {
		        $conditionsoutput_main_content .= "\tdocument.getElementById('method').value='".$p_method."';\n";
		    }

		    if ($subaction == "editthiscondition")
		    { // in edit mode we read previous values in order to dusplay them in the corresponding inputs
		        if (isset($_POST['EDITConditionConst']) && $_POST['EDITConditionConst'] != '')
		        {
		            // In order to avoid issues with backslash escaping, I don't use javascript to set the value
		            // Thus the value is directly set when creating the Textarea element
		            //$conditionsoutput_main_content .= "\tdocument.getElementById('ConditionConst').value='".html_escape($_POST['EDITConditionConst'])."';\n";
		            $conditionsoutput_main_content .= "\tdocument.getElementById('editTargetTab').value='#CONST';\n";
		        }
		        elseif (isset($_POST['EDITprevQuestionSGQA']) && $_POST['EDITprevQuestionSGQA'] != '')
		        {
		            $conditionsoutput_main_content .= "\tdocument.getElementById('prevQuestionSGQA').value='".html_escape($_POST['EDITprevQuestionSGQA'])."';\n";
		            $conditionsoutput_main_content .= "\tdocument.getElementById('editTargetTab').value='#PREVQUESTIONS';\n";
		        }
		        elseif (isset($_POST['EDITtokenAttr']) && $_POST['EDITtokenAttr'] != '')
		        {
		            $conditionsoutput_main_content .= "\tdocument.getElementById('tokenAttr').value='".html_escape($_POST['EDITtokenAttr'])."';\n";
		            $conditionsoutput_main_content .= "\tdocument.getElementById('editTargetTab').value='#TOKENATTRS';\n";
		        }
		        elseif (isset($_POST['EDITConditionRegexp']) && $_POST['EDITConditionRegexp'] != '')
		        {
		            // In order to avoid issues with backslash escaping, I don't use javascript to set the value
		            // Thus the value is directly set when creating the Textarea element
		            //$conditionsoutput_main_content .= "\tdocument.getElementById('ConditionRegexp').value='".html_escape($_POST['EDITConditionRegexp'])."';\n";
		            $conditionsoutput_main_content .= "\tdocument.getElementById('editTargetTab').value='#REGEXP';\n";
		        }
		        elseif (isset($_POST['EDITcanswers']) && is_array($_POST['EDITcanswers']))
		        { // was a predefined answers post
		            $conditionsoutput_main_content .= "\tdocument.getElementById('editTargetTab').value='#CANSWERSTAB';\n";
		            $conditionsoutput_main_content .= "\t$('#canswersToSelect').val('".$_POST['EDITcanswers'][0]."');\n";
		        }

		        if (isset($_POST['csrctoken']) && $_POST['csrctoken'] != '')
		        {
		            $conditionsoutput_main_content .= "\tdocument.getElementById('csrctoken').value='".html_escape($_POST['csrctoken'])."';\n";
		            $conditionsoutput_main_content .= "\tdocument.getElementById('editSourceTab').value='#SRCTOKENATTRS';\n";
		        }
		        else
		        {
		            $conditionsoutput_main_content .= "\tdocument.getElementById('cquestions').value='".html_escape($_POST['cquestions'])."';\n";
		            $conditionsoutput_main_content .= "\tdocument.getElementById('editSourceTab').value='#SRCPREVQUEST';\n";
		        }
		    }
		    else
		    { // in other modes, for the moment we do the same as for edit mode
		        if (isset($_POST['ConditionConst']) && $_POST['ConditionConst'] != '')
		        {
		            // In order to avoid issues with backslash escaping, I don't use javascript to set the value
		            // Thus the value is directly set when creating the Textarea element
		            //$conditionsoutput_main_content .= "\tdocument.getElementById('ConditionConst').value='".html_escape($_POST['ConditionConst'])."';\n";
		            $conditionsoutput_main_content .= "\tdocument.getElementById('editTargetTab').value='#CONST';\n";
		        }
		        elseif (isset($_POST['prevQuestionSGQA']) && $_POST['prevQuestionSGQA'] != '')
		        {
		            $conditionsoutput_main_content .= "\tdocument.getElementById('prevQuestionSGQA').value='".html_escape($_POST['prevQuestionSGQA'])."';\n";
		            $conditionsoutput_main_content .= "\tdocument.getElementById('editTargetTab').value='#PREVQUESTIONS';\n";
		        }
		        elseif (isset($_POST['tokenAttr']) && $_POST['tokenAttr'] != '')
		        {
		            $conditionsoutput_main_content .= "\tdocument.getElementById('tokenAttr').value='".html_escape($_POST['tokenAttr'])."';\n";
		            $conditionsoutput_main_content .= "\tdocument.getElementById('editTargetTab').value='#TOKENATTRS';\n";
		        }
		        elseif (isset($_POST['ConditionRegexp']) && $_POST['ConditionRegexp'] != '')
		        {
		            // In order to avoid issues with backslash escaping, I don't use javascript to set the value
		            // Thus the value is directly set when creating the Textarea element
		            //$conditionsoutput_main_content .= "\tdocument.getElementById('ConditionRegexp').value='".html_escape($_POST['ConditionRegexp'])."';\n";
		            $conditionsoutput_main_content .= "\tdocument.getElementById('editTargetTab').value='#REGEXP';\n";
		        }
		        else
		        { // was a predefined answers post
		            if (isset($_POST['cquestions']))
		            {
		                $conditionsoutput_main_content .= "\tdocument.getElementById('cquestions').value='".html_escape($_POST['cquestions'])."';\n";
		            }
		            $conditionsoutput_main_content .= "\tdocument.getElementById('editTargetTab').value='#CANSWERSTAB';\n";
		        }

		        if (isset($_POST['csrctoken']) && $_POST['csrctoken'] != '')
		        {
		            $conditionsoutput_main_content .= "\tdocument.getElementById('csrctoken').value='".html_escape($_POST['csrctoken'])."';\n";
		            $conditionsoutput_main_content .= "\tdocument.getElementById('editSourceTab').value='#SRCTOKENATTRS';\n";
		        }
		        else
		        {
		            if (isset($_POST['cquestions'])) $conditionsoutput_main_content .= "\tdocument.getElementById('cquestions').value='".javascript_escape($_POST['cquestions'])."';\n";
		            $conditionsoutput_main_content .= "\tdocument.getElementById('editSourceTab').value='#SRCPREVQUEST';\n";
		        }
		    }

		    if (isset($p_scenario))
		    {
		        $conditionsoutput_main_content .= "\tdocument.getElementById('scenario').value='".$p_scenario."';\n";
		    }
		    $conditionsoutput_main_content .= "-->\n"
		    . "</script>\n";
		    $conditionsoutput_main_content .= "</td></tr>\n";
		}
		//END: DISPLAY THE ADD or EDIT CONDITION FORM


		$conditionsoutput_main_content .= "</table>\n";

		$conditionsoutput = $conditionsoutput_main_content;

		$data['conditionsoutput'] = $conditionsoutput;
		$this->load->view("admin/conditions/conditionsforms_view",$data);
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
	}

	function _showSpeaker($hinttext)
	{
	    global $max;
		$clang = $this->limesurvey_lang;
		$imageurl = $this->config->item("imageurl");

	    if(!isset($max))
	    {
	        $max = 20;
	    }
	    $htmlhinttext=str_replace("'",'&#039;',$hinttext);  //the string is already HTML except for single quotes so we just replace these only
	    $jshinttext=javascript_escape($hinttext,true,true);

	    if(strlen(html_entity_decode($hinttext,ENT_QUOTES,'UTF-8')) > ($max+3))
	    {
	        $shortstring = FlattenText($hinttext);

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
	        $shortstring = FlattenText($hinttext);

	        $reshtml= "<span title='".$shortstring."'> \"$shortstring\"</span>";
	    }

	    return $reshtml;

	}

}
