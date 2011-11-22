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
* $Id: assessments.php 11259 2011-10-25 17:06:26Z c_schmitz $
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
class Assessments extends Survey_Common_Action {

    /**
     * Routes to the correct sub-action
     *
     * @access public
     * @param int $surveyid
     * @return void
     */
	public function run($surveyid)
	{
        $surveyid = sanitize_int($surveyid);
        $action=!empty($_POST['action']) ? $_POST['action'] : '';

        $assessmentlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);

        array_unshift($assessmentlangs,$baselang);      // makes an array with ALL the languages supported by the survey -> $assessmentlangs
        Yii::app()->setConfig("baselang",$baselang);
        Yii::app()->setConfig("assessmentlangs", $assessmentlangs);
        if($action=="assessmentadd")
            self::_add($surveyid);
        if($action=="assessmentupdate")
            self::_update($surveyid);
        if($action=="assessmentdelete")
            self::_delete($surveyid, $_POST['id']);

        if (bHasSurveyPermission($surveyid, 'assessments','read'))
        {
            $clang=$this->getController()->lang;


            if ($surveyid == "") {
                show_error($clang->gT("No SID Provided"));
                exit;
            }

            $assessments=Assessment::model()->findAllByAttributes(array('sid' => $surveyid));
            //$assessmentsoutput.= "<pre>";print_r($assessments);echo "</pre>";
            $groups=Groups::model()->findAllByAttributes(array('sid' => $surveyid));
            $groupselect="<select name='gid' id='gid'>\n";
            foreach($groups as $group) {
            	$group = $group->attributes;
                $groupselect.="<option value='".$group['gid']."'>".$group['group_name']."</option>\n";
            }
            $groupselect .="</select>\n";
            $headings=array($clang->gT("Scope"), $clang->gT("Question group"), $clang->gT("Minimum"), $clang->gT("Maximum"));
            $actiontitle=$clang->gT("Add");
            $actionvalue="assessmentadd";
            $thisid="";

            if ($action == "assessmentedit" && bHasSurveyPermission($surveyid, 'assessments','update')) {
            	$results = Assessment::model()->findAllByAttributes(array('id' => $_POST['id'], 'language' => $baselang));

                foreach ($results as $row) {
                    $editdata=$row->attributes;
                }
                $groupselect=str_replace("'".$editdata['gid']."'", "'".$editdata['gid']."' selected", $groupselect);
                $actiontitle=$clang->gT("Edit");
                $actionvalue="assessmentupdate";
                $thisid=$editdata['id'];
            }
            //$assessmentsoutput.= "<pre>"; print_r($edits); $assessmentsoutput.= "</pre>";
            //PRESENT THE PAGE

            $surveyinfo=getSurveyInfo($surveyid);

            $this->getController()->_js_admin_includes(Yii::app()->getConfig("adminscripts").'assessments.js');
            $this->getController()->_js_admin_includes(Yii::app()->getConfig("generalscripts").'jquery/jquery.tablesorter.min.js');

            $data['clang']=$clang;
            $data['surveyinfo']=$surveyinfo;
            $data['imageurl'] = Yii::app()->getConfig('imageurl');
            $data['surveyid']=$surveyid;
            $data['headings']=$headings;
            $data['assessments']=$assessments;
            $data['actionvalue']=$actionvalue;
            $data['actiontitle']=$actiontitle;
            $data['groupselect']=$groupselect;
            $data['assessmentlangs']=Yii::app()->getConfig("assessmentlangs");
            $data['baselang']=Yii::app()->getConfig("baselang");
            $data['action']=$action;
            $data['gid']=empty($_POST['gid']) ? '' : $_POST['gid'];
            if(isset($editdata)) $data['editdata']=$editdata;
            $data['thisid']=$thisid;
            $data['groups']=$groups;

        	Yii::app()->loadHelper('admin/htmleditor');
            $this->getController()->_getAdminHeader();
            $this->getController()->render("/admin/assessments_view",$data);
            $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));


        }

    }

    /**
    * Inserts an assessment to the database. Receives input from POST
    */
    function _add($surveyid)
    {
        if (bHasSurveyPermission($surveyid, 'assessments','create')) {
            $first=true;
            $assessmentlangs=Yii::app()->getConfig("assessmentlangs");
            foreach ($assessmentlangs as $assessmentlang)
            {
                if (!isset($_POST['gid'])) $_POST['gid']=0;

                $dataarray=array(
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
                    $dataarray['id']=$aid;
                }
				$assessment = new Assessment;
            	foreach ($dataarray as $k => $v)
            		$assessment->$k = $v;
            	$assessment->save();
                //$query = $connect->GetInsertSQL($inserttable, $datarray, get_magic_quotes_gpc());
                //$result=$connect->Execute($query) or safe_die("Error inserting<br />$query<br />".$connect->ErrorMsg());
                if ($first==true)
                {
                    $first=false;
                    $aid=$assessment->id;
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
        if (bHasSurveyPermission($surveyid, 'assessments','update')) {

            $assessmentlangs=Yii::app()->getConfig("assessmentlangs");
            foreach ($assessmentlangs as $assessmentlang)
            {

                if (!isset($_POST['gid'])) $_POST['gid']=0;
                if (Yii::app()->getConfig('filterxsshtml'))
                {
                    $_POST['name_'.$assessmentlang]=htmlspecialchars($_POST['name_'.$assessmentlang]);
                    $_POST['assessmentmessage_'.$assessmentlang]=htmlspecialchars($_POST['assessmentmessage_'.$assessmentlang]);
                }

            	$assessment = Assessment::model()->findByAttributes(array('id' => $_POST['id'], 'language' => $assessmentlang));
            	if (!is_null($assessment))
            	{
            		$assessment->scope = $_POST['scope'];
            		$assessment->gid = $_POST['gid'];
            		$assessment->minimum = $_POST['minimum'];
            		$assessment->maximum = $_POST['maximum'];
            		$assessment->name = $_POST['name_' . $assessmentlang];
            		$assessment->message = $_POST['assessmentmessage_' . $assessmentlang];
            		$assessment->save();
            	}
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
            Assessment::model()->deleteAllByAttributes(array('id' => $id));
        }
    }

}
