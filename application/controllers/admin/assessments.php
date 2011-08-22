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
 * Assessments Controller
 *
 * This controller performs assessments actions
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class assessments extends Survey_Common_Controller {

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Shows Assessment Controller page
	 */
	function index($surveyid)
	{
		$action=$this->input->post("action");
		$_POST=$this->input->post();
		$assessmentlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
		$baselang = GetBaseLanguageFromSurveyID($surveyid);
		array_unshift($assessmentlangs,$baselang);      // makes an array with ALL the languages supported by the survey -> $assessmentlangs
		$this->config->set_item("baselang",$baselang);
		$this->config->set_item("assessmentlangs", $assessmentlangs);
		if($this->input->post('action')=="assessmentadd")
			self::_add($surveyid);
		if($this->input->post('action')=="assessmentupdate")
			self::_update($surveyid);
		if($this->input->post('action')=="assessmentdelete")
			self::_delete($surveyid, $_POST['id']);
		$this->load->model("assessments_model");
		$this->load->model("groups_model");
		if (bHasSurveyPermission($surveyid, 'assessments','read'))
		{
			$clang=$this->limesurvey_lang;


			if ($surveyid == "") {
				show_error($clang->gT("No SID Provided"));
				exit;
			}

			$assessments=$this->assessments_model->getAssessments($surveyid);
			//$assessmentsoutput.= "<pre>";print_r($assessments);echo "</pre>";
			$groups=$this->groups_model->getGroups($surveyid);
			$groupselect="<select name='gid' id='gid'>\n";
			foreach($groups as $group) {
				$groupselect.="<option value='".$group['gid']."'>".$group['group_name']."</option>\n";
			}
			$groupselect .="</select>\n";
			$headings=array($clang->gT("Scope"), $clang->gT("Question group"), $clang->gT("Minimum"), $clang->gT("Maximum"));
			$actiontitle=$clang->gT("Add");
			$actionvalue="assessmentadd";
			$thisid="";

			if ($action == "assessmentedit" && bHasSurveyPermission($surveyid, 'assessments','update')) {
				$this->load->helper("database");
				$query = "SELECT * FROM ".$this->db->dbprefix('assessments')." WHERE id=".sanitize_int($_POST['id'])." and language='$baselang'";
				$results = db_execute_assoc($query);
				foreach ($results->result_array() as $row) {
					$editdata=$row;
				}
				$groupselect=str_replace("'".$editdata['gid']."'", "'".$editdata['gid']."' selected", $groupselect);
				$actiontitle=$clang->gT("Edit");
				$actionvalue="assessmentupdate";
				$thisid=$editdata['id'];
			}
			//$assessmentsoutput.= "<pre>"; print_r($edits); $assessmentsoutput.= "</pre>";
			//PRESENT THE PAGE

			$surveyinfo=getSurveyInfo($surveyid);

			self::_js_admin_includes($this->config->item("adminscripts").'assessments.js');
			self::_js_admin_includes($this->config->item("generalscripts").'jquery/jquery.tablesorter.min.js');

			$data['clang']=$clang;
			$data['surveyinfo']=$surveyinfo;
			$data['imageurl'] = $this->config->item('imageurl');
			$data['surveyid']=$surveyid;
			$data['headings']=$headings;
			$data['assessments']=$assessments;
			$data['actionvalue']=$actionvalue;
			$data['actiontitle']=$actiontitle;
			$data['groupselect']=$groupselect;
			$data['assessmentlangs']=$this->config->item("assessmentlangs");
			$data['baselang']=$this->config->item("baselang");
			$data['action']=$action;
			$data['gid']=$this->input->post("gid");
			if(isset($editdata)) $data['editdata']=$editdata;
			$data['thisid']=$thisid;
			$data['groups']=$groups;

			self::_getAdminHeader();
			$this->load->view("admin/assessments_view",$data);
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));


		}

	}

	/**
	 * Inserts an assessment to the database. Receives input from POST
	 */
	function _add($surveyid)
	{
		$_POST = $this->input->post();
		$this->load->model("assessments_model");
		if (bHasSurveyPermission($surveyid, 'assessments','create')) {
		    $inserttable=$this->db->dbprefix("assessments");
		    $first=true;
			$assessmentlangs=$this->config->item("assessmentlangs");
		    foreach ($assessmentlangs as $assessmentlang)
		    {
		        if (!isset($_POST['gid'])) $_POST['gid']=0;

		        $datarray=array(
		        'sid' => $surveyid,
		        'scope' => $_POST['scope'],
		        'gid' => $_POST['gid'],
		        'minimum' => $_POST['minimum'],
		        'maximum' => $_POST['maximum'],
		        'name' => $_POST['name_'.$assessmentlang],
		        'language' => $assessmentlang,
		        'message' => $_POST['assessmentmessage_'.$assessmentlang]);

		        if ($first==false)
		        {
		            $datarray['id']=$aid;
		        }

				$this->assessments_model->insertRecords($datarray);
		        //$query = $connect->GetInsertSQL($inserttable, $datarray, get_magic_quotes_gpc());
		        //$result=$connect->Execute($query) or safe_die("Error inserting<br />$query<br />".$connect->ErrorMsg());
		        if ($first==true)
		        {
		            $first=false;
		            $aid=$this->db->insert_id();
		            //$connect->Insert_ID(db_table_name_nq('assessments'),"id");
		        }
		    }
		}
	}

	/**
	 * Updates an assessment. Receives input from POST
	 */
	function _update($surveyid)
	{
		$_POST = $this->input->post();
		$this->load->model("assessments_model");
		if (bHasSurveyPermission($surveyid, 'assessments','update')) {

		    //if ($filterxsshtml)
		    //{
		    //    require_once("../classes/inputfilter/class.inputfilter_clean.php");
		    //    $myFilter = new InputFilter('','',1,1,1);
		    //}
			$assessmentlangs=$this->config->item("assessmentlangs");
		    foreach ($assessmentlangs as $assessmentlang)
		    {

		        if (!isset($_POST['gid'])) $_POST['gid']=0;
		        //if ($filterxsshtml)
		        //{
		        //    $_POST['name_'.$assessmentlang]=$myFilter->process($_POST['name_'.$assessmentlang]);
		        //    $_POST['assessmentmessage_'.$assessmentlang]=$myFilter->process($_POST['assessmentmessage_'.$assessmentlang]);
		        //}
				$this->assessments_model->updateRecord($_POST, $assessmentlang);
		    }
		}
	}

	/**
	 * Deletes an assessment.
	 */
	function _delete($surveyid, $id)
	{
		if (bHasSurveyPermission($surveyid, 'assessments','delete'))
		{
		    $this->load->model("assessments_model");
			$this->assessments_model->dropRecord($id);
		}
	}

}
