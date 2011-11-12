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
 * $Id: quotas.php 11128 2011-10-08 22:23:24Z dionet $
 * 
 */

/**
 * Quotas Controller
 *
 * This controller performs quota actions
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class quotas extends Survey_Common_Controller {
    
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
	
	function action($surveyid, $subaction = null)
	{
		$surveyid = sanitize_int($surveyid);
		
		self::_js_admin_includes($this->config->item("generalscripts").'/jquery/jquery.tablesorter.min.js');
		self::_js_admin_includes($this->config->item("adminscripts").'/quotas.js');

		if(!bHasSurveyPermission($surveyid, 'quotas','read'))
		{
			show_error("no permissions");
		}
		
		$_POST = $this->input->post();
		$clang = $this->limesurvey_lang;
		$this->load->helper("database");
		$this->load->helper("surveytranslator");
		$data = array();
		
	    if (isset($_POST['quotamax'])) $_POST['quotamax']=sanitize_int($_POST['quotamax']);
	    if (!isset($action)) $action=returnglobal('action');
	    if (!isset($action)) $action="quotas";		
	    if (!isset($subaction)) $subaction=returnglobal('subaction');
	    //if (!isset($quotasoutput)) $quotasoutput = "";
	    if (!isset($_POST['autoload_url']) || empty($_POST['autoload_url'])) {$_POST['autoload_url']=0;}
		
		//Get the languages used in this survey
        $langs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        array_push($langs, $baselang);
		
		$css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
		$this->config->set_item("css_admin_includes", $css_admin_includes);
		self::_getAdminHeader();
        self::_showadminmenu();
        self::_surveybar($surveyid); 
				
	    if($subaction == "insertquota" && bHasSurveyPermission($surveyid, 'quotas','create'))
	    {
	  		if(!isset($_POST['quota_limit']) || $_POST['quota_limit'] < 0 || empty($_POST['quota_limit']) || !is_numeric($_POST['quota_limit']))
	        {
	            $_POST['quota_limit'] = 0;
	          
	        }
	            
	        array_walk( $_POST, array($this->db, "escape"), true);
	
	        $query = "INSERT INTO ".$this->db->dbprefix('quota')." (sid,name,qlimit,action,autoload_url)
			          VALUES ('$surveyid','{$_POST['quota_name']}','{$_POST['quota_limit']}','{$_POST['quota_action']}', '{$_POST['autoload_url']}')";
	        db_execute_assoc($query) or safe_die("Error inserting limit".$connect->ErrorMsg());
	        $quotaid=$this->db->insert_id();//$connect->Insert_Id($this->db->dbprefix_nq('quota'),"id");
	
	        //Get the languages used in this survey
	        $langs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        $baselang = GetBaseLanguageFromSurveyID($surveyid);
	        array_push($langs, $baselang);
	        //Iterate through each language, and make sure there is a quota message for it
	        $errorstring = '';
	        foreach ($langs as $lang)
	        {
	            if (!$_POST['quotals_message_'.$lang]) { $errorstring.= GetLanguageNameFromCode($lang,false)."\\n";}
	        }
	        if ($errorstring!='')
	        {
	            $data['showerror'] = "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Quota could not be added.\\n\\nIt is missing a quota message for the following languages","js").":\\n".$errorstring."\")\n //-->\n</script>\n";
	        }
	        else
	        //All the required quota messages exist, now we can insert this info into the database
	        {
	
	            foreach ($langs as $lang) //Iterate through each language
	            {
	                //Clean XSS - Automatically provided by CI input class
	                $_POST['quotals_message_'.$lang] = html_entity_decode($_POST['quotals_message_'.$lang], ENT_QUOTES, "UTF-8");
	
	                // Fix bug with FCKEditor saving strange BR types
	                $_POST['quotals_message_'.$lang]=fix_FCKeditor_text($_POST['quotals_message_'.$lang]);
	
	                //Now save the language to the database:
	                $query = "INSERT INTO ".$this->db->dbprefix('quota_languagesettings')." (quotals_quota_id, quotals_language, quotals_name, quotals_message, quotals_url, quotals_urldescrip)
			        	      VALUES ('$quotaid', '$lang', ".$this->db->escape($_POST['quota_name'],true).", ".$this->db->escape($_POST['quotals_message_'.$lang],true).", ".$this->db->escape($_POST['quotals_url_'.$lang],true).", ".$this->db->escape($_POST['quotals_urldescrip_'.$lang],true).")";
	                db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	            }
	        } //End insert language based components
	        $viewquota = "1";
	
	    } //End foreach $lang
	
	    if($subaction == "modifyquota" && bHasSurveyPermission($surveyid, 'quotas','update'))
	    {
	        $query = "UPDATE ".$this->db->dbprefix('quota')."
				      SET name=".$this->db->escape($_POST['quota_name'],true).",
					  qlimit=".$this->db->escape($_POST['quota_limit'],true).",
					  action=".$this->db->escape($_POST['quota_action'],true).",
					  autoload_url=".$this->db->escape($_POST['autoload_url'],true)."
					  WHERE id=".$this->db->escape($_POST['quota_id'],true);
	        db_execute_assoc($query) or safe_die("Error modifying quota".$connect->ErrorMsg());
	
	        //Get the languages used in this survey
	        $langs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        $baselang = GetBaseLanguageFromSurveyID($surveyid);
	        array_push($langs, $baselang);
	        //Iterate through each language, and make sure there is a quota message for it
	        $errorstring = '';
	        foreach ($langs as $lang)
	        {
	            if (!$_POST['quotals_message_'.$lang]) { $errorstring.= GetLanguageNameFromCode($lang,false)."\\n";}
	        }
	        if ($errorstring!='')
	        {
	            $data['showerror'] = "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Quota could not be added.\\n\\nIt is missing a quota message for the following languages","js").":\\n".$errorstring."\")\n //-->\n</script>\n";
	        }
	        else
	        //All the required quota messages exist, now we can insert this info into the database
	        {
	
	            foreach ($langs as $lang) //Iterate through each language
	            {
	                //Clean XSS - Automatically provided by CI
	                $_POST['quotals_message_'.$lang] = html_entity_decode($_POST['quotals_message_'.$lang], ENT_QUOTES, "UTF-8");
	
	                // Fix bug with FCKEditor saving strange BR types
	                $_POST['quotals_message_'.$lang]=fix_FCKeditor_text($_POST['quotals_message_'.$lang]);
	
	                //Now save the language to the database:
	                $query = "UPDATE ".$this->db->dbprefix('quota_languagesettings')."
					          SET quotals_name=".$this->db->escape($_POST['quota_name'],true).",
							  quotals_message=".$this->db->escape($_POST['quotals_message_'.$lang],true).",
							  quotals_url=".$this->db->escape($_POST['quotals_url_'.$lang],true).",
							  quotals_urldescrip=".$this->db->escape($_POST['quotals_urldescrip_'.$lang],true)."
					          WHERE quotals_quota_id =".$this->db->escape($_POST['quota_id'],true)."
							  AND quotals_language = '$lang'";
	                db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	            }
	        } //End insert language based components
	
	
	        $viewquota = "1";
	    }
	
	    if($subaction == "insertquotaanswer" && bHasSurveyPermission($surveyid, 'quotas','create'))
	    {
	        array_walk( $_POST, array($this->db, "escape"), true);
	        $query = "INSERT INTO ".$this->db->dbprefix('quota_members')." (sid,qid,quota_id,code) VALUES ('$surveyid','{$_POST['quota_qid']}','{$_POST['quota_id']}','{$_POST['quota_anscode']}')";
	        db_execute_assoc($query) or safe_die($connect->ErrorMsg());
			if(isset($_POST['createanother']) && $_POST['createanother'] == "on") {
				$_POST['action']="quotas";
				$_POST['subaction']="new_answer";
				$subaction="new_answer";
			} else {
				$viewquota = "1";
			}
	    }
	
	    if($subaction == "quota_delans" && bHasSurveyPermission($surveyid, 'quotas','delete'))
	    {
	        array_walk( $_POST, array($this->db, "escape"), true);
	        $query = "DELETE FROM ".$this->db->dbprefix('quota_members')."
				      WHERE id = '{$_POST['quota_member_id']}'
					  AND qid='{$_POST['quota_qid']}' and code='{$_POST['quota_anscode']}'";
	        db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	        $viewquota = "1";
	
	    }
	
	    if($subaction == "quota_delquota" && bHasSurveyPermission($surveyid, 'quotas','delete'))
	    {
	        array_walk( $_POST, array($this->db, "escape"), true);
	        $query = "DELETE FROM ".$this->db->dbprefix('quota')." WHERE id='{$_POST['quota_id']}'";
	        db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	        $query = "DELETE FROM ".$this->db->dbprefix('quota_languagesettings')." WHERE quotals_quota_id='{$_POST['quota_id']}'";
	        db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	        $query = "DELETE FROM ".$this->db->dbprefix('quota_members')." WHERE quota_id='{$_POST['quota_id']}'";
	        db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	        $viewquota = "1";
	    }
	
	    if ($subaction == "quota_editquota" && bHasSurveyPermission($surveyid, 'quotas','update'))
	    {
	        array_walk( $_POST, array($this->db, "escape"), true);
	        $query = "SELECT * FROM ".$this->db->dbprefix('quota')."
			          WHERE id='{$_POST['quota_id']}'";
	        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	        $quotainfo = $result->row_array();
	
	        $langs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        $baselang = GetBaseLanguageFromSurveyID($surveyid);
	        array_push($langs,$baselang);
			
			$data['quotainfo'] = $quotainfo;
			$this->load->view("admin/quotas/editquota_view",$data);

	        foreach ($langs as $lang)
	        {
	            //Get this one
	            $langquery = "SELECT * FROM ".$this->db->dbprefix('quota_languagesettings')." WHERE quotals_quota_id='{$_POST['quota_id']}' AND quotals_language = '$lang'";
	            $langresult = db_execute_assoc($langquery) or safe_die($connect->ErrorMsg());
	            $langquotainfo = $langresult->row_array();
				
				$data['langquotainfo'] = $langquotainfo;
				$data['lang'] = $lang;
	        	$this->load->view("admin/quotas/editquotalang_view",$data);
	
	        };
	        $this->load->view("admin/quotas/editquotafooter_view",$data);
	    }
	
	    $totalquotas=0;
	    $totalcompleted=0;
	    $csvoutput=array();
	    if (($action == "quotas" && !isset($subaction)) || isset($viewquota))
	    {
	        $query = "SELECT * FROM ".$this->db->dbprefix('quota')." , ".$this->db->dbprefix('quota_languagesettings')."
			          WHERE ".$this->db->dbprefix('quota').".id = ".$this->db->dbprefix('quota_languagesettings').".quotals_quota_id
			          AND sid='".$surveyid."'
					  AND quotals_language = '".$baselang."'
					  ORDER BY name";
	        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
			$this->load->view("admin/quotas/viewquotas_view",$data);
	
	        //if there are quotas let's proceed
	        if ($result->num_rows() > 0)
	        {
	            //loop through all quotas
	            foreach ($result->result_array() as $quotalisting)
	            {
	            	$totalquotas+=$quotalisting['qlimit'];
					$completed=get_quotaCompletedCount($surveyid, $quotalisting['id']);
					$highlight=($completed >= $quotalisting['qlimit']) ? "" : "style='color: red'"; //Incomplete quotas displayed in red
					$totalcompleted=$totalcompleted+$completed;
					$csvoutput[]=$quotalisting['name'].",".$quotalisting['qlimit'].",".$completed.",".($quotalisting['qlimit']-$completed)."\r\n";

					$data['quotalisting'] = $quotalisting;
					$data['highlight'] = $highlight;
					$data['completed'] = $completed;
					$data['totalquotas'] = $totalquotas;
					$data['totalcompleted'] = $totalcompleted;
					$this->load->view("admin/quotas/viewquotasrow_view",$data);
	
	                //check how many sub-elements exist for a certain quota
	                $query = "SELECT id,code,qid FROM ".$this->db->dbprefix('quota_members')." where quota_id='".$quotalisting['id']."'";
	                $result2 = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	                if ($result2->num_rows() > 0)
	                {
	                    //loop through all sub-parts
	                    foreach ($result2->result_array() as $quota_questions )
	                    {
	                        $question_answers = self::getQuotaAnswers($quota_questions['qid'],$surveyid,$quotalisting['id']);
							$data['question_answers'] = $question_answers;
							$this->load->view("admin/quotas/viewquotasrowsub_view",$data);
	                    }
	                }
	
	            }
	
	        }
	        else
	        {
	        	// No quotas have been set for this survey
	        	$this->load->view("admin/quotas/viewquotasempty_view",$data);
	        }
			$this->load->view("admin/quotas/viewquotasfooter_view",$data);
	    }
	
	    if(isset($_GET['quickreport']) && $_GET['quickreport'])
	    {
	        header("Content-Disposition: attachment; filename=results-survey".$surveyid.".csv");
	        header("Content-type: text/comma-separated-values; charset=UTF-8");
	        header("Pragma: public");
	        echo $clang->gT("Quota name").",".$clang->gT("Limit").",".$clang->gT("Completed").",".$clang->gT("Remaining")."\r\n";
	        foreach($csvoutput as $line)
	        {
	            echo $line;
	        }
	        die;
	    }
	    if(($subaction == "new_answer" || ($subaction == "new_answer_two" && !isset($_POST['quota_qid']))) && bHasSurveyPermission($surveyid,'quotas','create'))
	    {
	        if ($subaction == "new_answer_two") $_POST['quota_id'] = $_POST['quota_id'];
	
	        $allowed_types = "(type ='G' or type ='M' or type ='Y' or type ='A' or type ='B' or type ='I' or type = 'L' or type='O' or type='!')";
	
	        $query = "SELECT name FROM ".$this->db->dbprefix('quota')." WHERE id='".$_POST['quota_id']."'";
	        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	        foreach ($result->result_array() as $quotadetails) 
	        {
	            $quota_name=$quotadetails['name'];
	        }
	        
	        $query = "SELECT qid, title, question FROM ".$this->db->dbprefix('questions')." WHERE $allowed_types AND sid='$surveyid' AND language='{$baselang}'";
	        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	        if ($result->num_rows() == 0)
	        {
				$this->load->view("admin/quotas/newanswererror_view", $data);
	        } else
	        {
	        	$data['newanswer_result'] = $result->result_array();
				$data['quota_name'] = $quota_name;
				$this->load->view("admin/quotas/newanswer_view", $data);
	        }
	    }
	
	    if($subaction == "new_answer_two" && isset($_POST['quota_qid']) && bHasSurveyPermission($surveyid, 'quotas','create'))
	    {
	        array_walk( $_POST, array($this->db, "escape"), true);
	
	        $query = "SELECT name FROM ".$this->db->dbprefix('quota')." WHERE id='".$_POST['quota_id']."'";
	        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	        foreach ($result->result_array() as $quotadetails) 
	        {
	            $quota_name=$quotadetails['name'];
	        }
	
	        $question_answers = self::getQuotaAnswers($_POST['quota_qid'],$surveyid,$_POST['quota_id']);
	        $x=0;
	
	        foreach ($question_answers as $qacheck)
	        {
	            if (isset($qacheck['rowexists'])) $x++;
	        }
	
	        reset($question_answers);
			$data['question_answers'] = $question_answers;
			$data['x'] = $x;
		    $this->load->view("admin/quotas/newanswertwo_view", $data);
			
	    }
	
	    if ($subaction == "new_quota" && bHasSurveyPermission($surveyid, 'quotas','create'))
	    {
	        $langs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        $baselang = GetBaseLanguageFromSurveyID($surveyid);
	        array_push($langs,$baselang);
	        $thissurvey=getSurveyInfo($surveyid);
			$data['thissurvey'] = $thissurvey;
			$data['langs'] = $langs;
			$this->load->view("admin/quotas/newquota_view", $data);

	    }
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));	
	}
	
	function getQuotaAnswers($qid,$surveyid,$quota_id)
	{
	    $clang = $this->limesurvey_lang;
	    $baselang = GetBaseLanguageFromSurveyID($surveyid);
	    $query = "SELECT type, title FROM ".$this->db->dbprefix('questions')." WHERE qid='{$qid}' AND language='{$baselang}'";
	    $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	    $qtype = $result->row_array();
	
	    if ($qtype['type'] == 'G')
	    {
	        $query = "SELECT * FROM ".$this->db->dbprefix('quota_members')." WHERE sid='{$surveyid}' and qid='{$qid}' and quota_id='{$quota_id}'";
	
	        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	        $answerlist = array('M' => array('Title' => $qtype['title'], 'Display' => $clang->gT("Male"), 'code' => 'M'),
			'F' => array('Title' => $qtype['title'],'Display' => $clang->gT("Female"), 'code' => 'F'));
	
	        if ($result->num_rows() > 0)
	        {
	            foreach ($result->result_array() as $quotalist)
	            {
	                $answerlist[$quotalist['code']]['rowexists'] = '1';
	            }
	
	        }
	    }
	
	    if ($qtype['type'] == 'M')
	    {
	        $query = "SELECT * FROM ".$this->db->dbprefix('quota_members')." WHERE sid='{$surveyid}' and qid='{$qid}' and quota_id='{$quota_id}'";
	        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	        $query = "SELECT title,question FROM ".$this->db->dbprefix('questions')." WHERE parent_qid='{$qid}'";
	        $ansresult = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	        $answerlist = array();
	
	        while ($dbanslist = $ansresult->result_array())
	        {
	            $tmparrayans = array('Title' => $qtype['title'], 'Display' => substr($dbanslist['question'],0,40), 'code' => $dbanslist['title']);
	            $answerlist[$dbanslist['title']]	= $tmparrayans;
	        }
	
	        if ($result->RecordCount() > 0)
	        {
	            while ($quotalist = $result->result_array())
	            {
	                $answerlist[$quotalist['code']]['rowexists'] = '1';
	            }
	
	        }
	    }
	
	    if ($qtype['type'] == 'L' || $qtype['type'] == 'O' || $qtype['type'] == '!')
	    {
	        $query = "SELECT * FROM ".$this->db->dbprefix('quota_members')." WHERE sid='{$surveyid}' and qid='{$qid}' and quota_id='{$quota_id}'";
	        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	        $query = "SELECT code,answer FROM ".$this->db->dbprefix('answers')." WHERE qid='{$qid}'";
	        $ansresult = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	        $answerlist = array();
	
	        foreach ($ansresult->result_array() as $dbanslist)
	        {
	            $answerlist[$dbanslist['code']] = array('Title'=>$qtype['title'],
			                                                  'Display'=>substr($dbanslist['answer'],0,40),
			                                                  'code'=>$dbanslist['code']);
	        }
	
	        if ($result->RecordCount() > 0)
	        {
	            while ($quotalist = $result->result_array())
	            {
	                $answerlist[$quotalist['code']]['rowexists'] = '1';
	            }
	
	        }
	
	    }
	
	    if ($qtype['type'] == 'A')
	    {
	        $query = "SELECT * FROM ".$this->db->dbprefix('quota_members')." WHERE sid='{$surveyid}' and qid='{$qid}' and quota_id='{$quota_id}'";
	        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	        $query = "SELECT title,question FROM ".$this->db->dbprefix('questions')." WHERE parent_qid='{$qid}'";
	        $ansresult = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	        $answerlist = array();
	
	        foreach ($ansresult->result_array() as $dbanslist)
	        {
	            for ($x=1; $x<6; $x++)
	            {
	                $tmparrayans = array('Title' => $qtype['title'], 'Display' => substr($dbanslist['question'],0,40).' ['.$x.']', 'code' => $dbanslist['title']);
	                $answerlist[$dbanslist['title']."-".$x]	= $tmparrayans;
	            }
	        }
	
	        if ($result->num_rows() > 0)
	        {
	            foreach ($result->result_array() as $quotalist)
	            {
	                $answerlist[$quotalist['code']]['rowexists'] = '1';
	            }
	
	        }
	    }
	
	    if ($qtype['type'] == 'B')
	    {
	        $query = "SELECT * FROM ".$this->db->dbprefix('quota_members')." WHERE sid='{$surveyid}' and qid='{$qid}' and quota_id='{$quota_id}'";
	        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	        $query = "SELECT code,answer FROM ".$this->db->dbprefix('answers')." WHERE qid='{$qid}'";
	        $ansresult = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	        $answerlist = array();
	
	        foreach ($ansresult->result_array() as $dbanslist)
	        {
	            for ($x=1; $x<11; $x++)
	            {
	                $tmparrayans = array('Title' => $qtype['title'], 'Display' => substr($dbanslist['answer'],0,40).' ['.$x.']', 'code' => $dbanslist['code']);
	                $answerlist[$dbanslist['code']."-".$x]	= $tmparrayans;
	            }
	        }
	
	        if ($result->num_rows() > 0)
	        {
	            foreach ($result->result_array() as $quotalist)
	            {
	                $answerlist[$quotalist['code']]['rowexists'] = '1';
	            }
	
	        }
	    }
	
	    if ($qtype['type'] == 'Y')
	    {
	        $query = "SELECT * FROM ".$this->db->dbprefix('quota_members')." WHERE sid='{$surveyid}' and qid='{$qid}' and quota_id='{$quota_id}'";
	
	        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	        $answerlist = array('Y' => array('Title' => $qtype['title'], 'Display' => $clang->gT("Yes"), 'code' => 'Y'),
			'N' => array('Title' => $qtype['title'],'Display' => $clang->gT("No"), 'code' => 'N'));
	
	        if ($result->num_rows() > 0)
	        {
	            foreach ($ansresult->result_array() as $quotalist)
	            {
	                $answerlist[$quotalist['code']]['rowexists'] = '1';
	            }
	
	        }
	    }
	
	    if ($qtype['type'] == 'I')
	    {
	
	        $slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        array_unshift($slangs,$baselang);
	
	        $query = "SELECT * FROM ".$this->db->dbprefix('quota_members')." WHERE sid='{$surveyid}' and qid='{$qid}' and quota_id='{$quota_id}'";
	        $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());
	
	        while(list($key,$value) = each($slangs))
	        {
	            $tmparrayans = array('Title' => $qtype['title'], 'Display' => getLanguageNameFromCode($value,false), $value);
	            $answerlist[$value]	= $tmparrayans;
	        }
	
	        if ($result->num_rows() > 0)
	        {
	            foreach ($result->result_array() as $quotalist)
	            {
	                $answerlist[$quotalist['code']]['rowexists'] = '1';
	            }
	
	        }
	    }
	
	    if (!isset($answerlist))
	    {
	        return array();
	    }
	    else
	    {
	        return $answerlist;
	    }
	}
		
	
}
