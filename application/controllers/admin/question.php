<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey (tm)
 * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *
 */

 /**
  * question
  *
  * @package LimeSurvey
  * @author
  * @copyright 2011
  * @version $Id$
  * @access public
  */
 class question extends Survey_Common_Controller {

    /**
     * question::__construct()
     * Constructor
     * @return
     */
    function __construct()
	{
		parent::__construct();
	}

    /**
     * question::import()
     * Function responsible to import a question.
     * @return void
     */
    function import()
    {
        $action = $this->input->post('action');
        $surveyid = $this->input->post('sid');
        $gid = $this->input->post('gid');
        $clang = $this->limesurvey_lang;


        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
	    $this->config->set_item("css_admin_includes", $css_admin_includes);

        self::_getAdminHeader();
        self::_showadminmenu($surveyid);
        self::_surveybar($surveyid,$gid);
        self::_surveysummary($surveyid,"viewquestion");
        self::_questiongroupbar($surveyid,$gid,NULL,"viewgroup");

        if ($action == 'importquestion')
        {

            $importquestion = "<div class='header ui-widget-header'>".$clang->gT("Import Question")."</div>\n";
            $importquestion .= "<div class='messagebox ui-corner-all'>\n";

            $sFullFilepath = $this->config->item('tempdir'). DIRECTORY_SEPARATOR . $_FILES['the_file']['name'];
            $aPathInfo = pathinfo($sFullFilepath);
            $sExtension = $aPathInfo['extension'];

            if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath))
            {
                $fatalerror = sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$this->config->item('tempdir'));
            }

            // validate that we have a SID and GID
            if (!$surveyid)
            {
                $fatalerror .= $clang->gT("No SID (Survey) has been provided. Cannot import question.");
            }
            //else
            //{
            //   $surveyid=returnglobal('sid');
            //}

            if (!$gid)
            {
                $fatalerror .= $clang->gT("No GID (Group) has been provided. Cannot import question");
                //return;
            }
            /**else
            {
                $postgid=returnglobal('gid');
            }*/

            if (isset($fatalerror))
            {
                $importquestion .= "<div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
                $importquestion .= $fatalerror."<br /><br />\n";
                $importquestion .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('".site_url('admin')."', '_top')\" /><br /><br />\n";
                $importquestion .= "</div>\n";
                unlink($sFullFilepath);
                show_error($importquestion);
                return;
            }

            // IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY
            $importquestion .= "<div class='successheader'>".$clang->gT("Success")."</div>&nbsp;<br />\n"
            .$clang->gT("File upload succeeded.")."<br /><br />\n"
            .$clang->gT("Reading file..")."<br /><br />\n";
            $this->load->helper('admin/import');
            if (strtolower($sExtension)=='csv')
            {
                $aImportResults=CSVImportQuestion($sFullFilepath, $surveyid, $gid);
            }
            elseif (strtolower($sExtension)=='lsq')
            {
                $aImportResults=XMLImportQuestion($sFullFilepath, $surveyid, $gid);
            }
            else show_error('Unknown file extension');
            FixLanguageConsistency($surveyid);

            if (isset($aImportResults['fatalerror']))
            {
                    $importquestion .= "<div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
                    $importquestion .= $aImportResults['fatalerror']."<br /><br />\n";
                    $importquestion .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('".site_url('admin')."', '_top')\" />\n";
                    $importquestion .=  "</div>\n";
                    unlink($sFullFilepath);
                    show_error($importquestion);
                    return;
            }

            $importquestion .= "<div class='successheader'>".$clang->gT("Success")."</div><br />\n"
            ."<strong><u>".$clang->gT("Question import summary")."</u></strong><br />\n"
            ."<ul style=\"text-align:left;\">\n"
            ."\t<li>".$clang->gT("Questions").": ".$aImportResults['questions']."</li>\n"
            ."\t<li>".$clang->gT("Subquestions").": ".$aImportResults['subquestions']."</li>\n"
            ."\t<li>".$clang->gT("Answers").": ".$aImportResults['answers']."</li>\n";
            if (strtolower($sExtension)=='csv')  {
                $importquestion.="\t<li>".$clang->gT("Label sets").": ".$aImportResults['labelsets']." (".$aImportResults['labels'].")</li>\n";
            }
            $importquestion.="\t<li>".$clang->gT("Question attributes:").$aImportResults['question_attributes']."</li>"
            ."</ul>\n";

            $importquestion .= "<strong>".$clang->gT("Question import is complete.")."</strong><br />&nbsp;\n";
            $importquestion .= "<input type='submit' value='".$clang->gT("Go to question")."' onclick=\"window.open('".site_url('admin/survey/view/'.$surveyid.'/'.$gid.'/'.$aImportResults['newqid'])."', '_top')\" />\n";
            $importquestion .= "</div><br />\n";

            unlink($sFullFilepath);

            $data['display'] = $importquestion;
            $this->load->view('survey_view',$data);
        }

        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

    /**
     * question::editdefaultvalues()
     * Load edit default values of a question screen
     * @param mixed $surveyid
     * @param mixed $gid
     * @param mixed $qid
     * @return void
     */
    function editdefaultvalues($surveyid,$gid,$qid)
    {
    	$surveyid = sanitize_int($surveyid);
		$gid = sanitize_int($gid);
		$qid = sanitize_int($qid);
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);

        self::_getAdminHeader();
        self::_showadminmenu($surveyid);
        self::_surveybar($surveyid,$gid);
        self::_surveysummary($surveyid,"editdefaultvalues");
        self::_questiongroupbar($surveyid,$gid,$qid,"editdefaultvalues");

        self::_questionbar($surveyid,$gid,$qid,"editdefaultvalues");

        $clang = $this->limesurvey_lang;
        $this->load->helper('database');
        $this->load->helper('surveytranslator');

        $questlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($questlangs,$baselang);
        $query = "SELECT type, other, title, question, same_default FROM ".$this->db->dbprefix."questions WHERE sid=$surveyid AND gid=$gid AND qid=$qid AND language='$baselang'";
        $res = db_execute_assoc($query);
        $questionrow=$res->row_array(); //$connect->GetRow("SELECT type, other, title, question, same_default FROM ".db_table_name('questions')." WHERE sid=$surveyid AND gid=$gid AND qid=$qid AND language='$baselang'");)
        $qtproperties=getqtypelist('','array');

        $editdefvalues="<div class='header ui-widget-header'>".$clang->gT('Edit default answer values')."</div> "
        . '<div id="tabs">'
        . "<form class='form30' id='frmdefaultvalues' name='frmdefaultvalues' action='".site_url('admin/database/index')."' method='post'>\n"
        ." <ul>";
        foreach ($questlangs as $language)
        {
            $editdefvalues .= "<li> <a href='#df_{$language}'>".getLanguageNameFromCode($language,false).'</a></li>';
        }
        $editdefvalues .='</ul>';


        foreach ($questlangs as $language)
        {
            $editdefvalues.="<div id='df_{$language}'><ul> ";
            // If there are answerscales
            if ($qtproperties[$questionrow['type']]['answerscales']>0)
            {
                for ($scale_id=0;$scale_id<$qtproperties[$questionrow['type']]['answerscales'];$scale_id++)
                {
                    $editdefvalues.=" <li><label for='defaultanswerscale_{$scale_id}_{$language}'>";
                    if ($qtproperties[$questionrow['type']]['answerscales']>1)
                    {
                        $editdefvalues.=sprintf($clang->gT('Default answer for scale %s:'),$scale_id)."</label>";
                    }
                    else
                    {
                        $editdefvalues.=sprintf($clang->gT('Default answer value:'),$scale_id)."</label>";
                    }
                    $query = "SELECT defaultvalue FROM ".$this->db->dbprefix."defaultvalues WHERE qid=$qid AND specialtype='' and scale_id={$scale_id} AND language='{$language}'";
                    $res = db_execute_assoc($query);
                    $defaultvalue=$res->row_array(); //$connect->GetOne("SELECT defaultvalue FROM ".$this->db->dbprefix."defaultvalues WHERE qid=$qid AND specialtype='' and scale_id={$scale_id} AND language='{$language}'");

                    $editdefvalues.="<select name='defaultanswerscale_{$scale_id}_{$language}' id='defaultanswerscale_{$scale_id}_{$language}'>";
                    $editdefvalues.="<option value='' ";
                    if (is_null($defaultvalue)) {
                     $editdefvalues.= " selected='selected' ";
                    }
                    $editdefvalues.=">".$clang->gT('<No default value>')."</option>";
                    $answerquery = "SELECT code, answer FROM ".$this->db->dbprefix."answers WHERE qid=$qid and language='$language' order by sortorder";
                    $answerresult = db_execute_assoc($answerquery);
                    foreach ($answerresult->result_array() as $answer)
                    {
                        $editdefvalues.="<option ";
                        if ($answer['code']==$defaultvalue)
                        {
                            $editdefvalues.= " selected='selected' ";
                        }
                        $editdefvalues.="value='{$answer['code']}'>{$answer['answer']}</option>";
                    }
                    $editdefvalues.="</select></li> ";
                    if ($questionrow['other']=='Y')
                    {
                        $query = "SELECT defaultvalue FROM ".$this->db->dbprefix."defaultvalues WHERE qid=$qid and specialtype='other' AND scale_id={$scale_id} AND language='{$language}'";
                        $res = db_execute_assoc($query);
                        $defaultvalue=$res->row_array(); //$connect->GetOne("SELECT defaultvalue FROM ".$this->db->dbprefix."defaultvalues WHERE qid=$qid and specialtype='other' AND scale_id={$scale_id} AND language='{$language}'");
                        if (is_null($defaultvalue)) $defaultvalue='';
                        $editdefvalues.="<li><label for='other_{$scale_id}_{$language}'>".$clang->gT("Default value for option 'Other':")."<label><input type='text' name='other_{$scale_id}_{$language}' value='$defaultvalue' id='other_{$scale_id}_{$language}'></li>";
                    }
                }
            }

            // If there are subquestions and no answerscales
            if ($qtproperties[$questionrow['type']]['answerscales']==0 && $qtproperties[$questionrow['type']]['subquestions']>0)
            {
                for ($scale_id=0;$scale_id<$qtproperties[$questionrow['type']]['subquestions'];$scale_id++)
                {
                    $sqquery = "SELECT * FROM ".$this->db->dbprefix."questions WHERE sid=$surveyid AND gid=$gid AND parent_qid=$qid and language='".$language."' and scale_id=0 order by question_order";
                    $sqresult = db_execute_assoc($sqquery);
                    //$sqrows = $sqresult->GetRows();
                    if ($qtproperties[$questionrow['type']]['subquestions']>1)
                    {
                        $editdefvalues.=" <div class='header ui-widget-header'>".sprintf($clang->gT('Default answer for scale %s:'),$scale_id)."</div>";
                    }
                    if ($questionrow['type']=='M' || $questionrow['type']=='P')
                    {
                        $options=array(''=>$clang->gT('<No default value>'),'Y'=>$clang->gT('Checked'));
                    }
                    $editdefvalues.="<ul>";

                    foreach ($sqresult->result_array() as $aSubquestion)
                    {
                        $defaultvalue=$connect->GetOne("SELECT defaultvalue FROM ".$this->db->dbprefix."defaultvalues WHERE qid=$qid AND specialtype='' and sqid={$aSubquestion['qid']} and scale_id={$scale_id} AND language='{$language}'");
                        $editdefvalues.="<li><label for='defaultanswerscale_{$scale_id}_{$language}_{$aSubquestion['qid']}'>{$aSubquestion['title']}: ".FlattenText($aSubquestion['question'])."</label>";
                        $editdefvalues.="<select name='defaultanswerscale_{$scale_id}_{$language}_{$aSubquestion['qid']}' id='defaultanswerscale_{$scale_id}_{$language}_{$aSubquestion['qid']}'>";
                        foreach ($options as $value=>$label)
                        {
                            $editdefvalues.="<option ";
                            if ($value==$defaultvalue)
                            {
                                $editdefvalues.= " selected='selected' ";
                            }
                            $editdefvalues.="value='{$value}'>{$label}</option>";
                        }
                        $editdefvalues.="</select></li> ";
                    }
                }
            }
                if ($language==$baselang && count($questlangs)>1)
                {
                $editdefvalues.="<li><label for='samedefault'>".$clang->gT('Use same default value across languages:')."<label><input type='checkbox' name='samedefault' id='samedefault'";
                if ($questionrow['same_default'])
                {
                    $editdefvalues.=" checked='checked'";
                }
                $editdefvalues.="></li>";
            }
                $editdefvalues.="</ul> ";
                $editdefvalues.="</div> "; // Closing page
            }
        $editdefvalues.="</div> "; // Closing pane
        $editdefvalues.="<input type='hidden' id='action' name='action' value='updatedefaultvalues'> "
            . "\t<input type='hidden' id='sid' name='sid' value='$surveyid' /></p>\n"
            . "\t<input type='hidden' id='gid' name='gid' value='$gid' /></p>\n"
            . "\t<input type='hidden' id='qid' name='qid' value='$qid' />";
        $editdefvalues.="<p><input type='submit' value='".$clang->gT('Save')."'/></form>";

        $data['display'] = $editdefvalues;
        $this->load->view('survey_view',$data);

        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

    /**
     * question::answeroptions()
     *  Load complete editing of answer options screen.
     * @param mixed $surveyid
     * @param mixed $gid
     * @param mixed $qid
     * @return
     */
    function answeroptions($surveyid,$gid,$qid)
    {
    	$surveyid = sanitize_int($surveyid);
		$qid = sanitize_int($qid);
		$gid = sanitize_int($gid);
        self::_js_admin_includes(base_url().'scripts/jquery/jquery.dd.js');
        self::_js_admin_includes(base_url().'scripts/admin/answers.js');
        self::_js_admin_includes(base_url().'scripts/jquery/jquery.blockUI.js');
        self::_js_admin_includes(base_url().'scripts/jquery/jquery.selectboxes.min.js');


        $css_admin_includes[] = base_url().'scripts/jquery/dd.css';

        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);

        self::_getAdminHeader();
        self::_showadminmenu($surveyid);;
        self::_surveybar($surveyid,$gid);
        self::_surveysummary($surveyid,"viewgroup");
        self::_questiongroupbar($surveyid,$gid,$qid,"addquestion");
        self::_questionbar($surveyid,$gid,$qid,"editansweroptions");

        $this->session->set_userdata('FileManagerContext',"edit:answer:{$surveyid}");

        self::_editansweroptions($surveyid,$gid,$qid);
        self::_loadEndScripts();


	   self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

    /**
     * question::_editansweroptions()
     * Load editing of answer options specific screen only.
     * @param mixed $surveyid
     * @param mixed $gid
     * @param mixed $qid
     * @return
     */
    function _editansweroptions($surveyid,$gid,$qid)
    {
    	$surveyid = sanitize_int($surveyid);
		$qid = sanitize_int($qid);
		$gid = sanitize_int($gid);
        $this->load->helper('database');
        // Get languages select on survey.
        $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);

        $qquery = "SELECT type FROM ".$this->db->dbprefix."questions WHERE qid=$qid AND language='".$baselang."'";
        $res = db_execute_assoc($qquery);

        $qrow = $res->row_array(); //$connect->GetRow($qquery);
        $qtype = $qrow['type'];
        //
        $qtypes=getqtypelist('','array');

        $scalecount=$qtypes[$qtype]['answerscales'];

        //Check if there is at least one answer
        for ($i = 0; $i < $scalecount; $i++)
        {
            $qquery = "SELECT count(*) as num_ans  FROM ".$this->db->dbprefix."answers WHERE qid=$qid AND scale_id=$i AND language='".$baselang."'";
            $res = db_execute_assoc($qquery);

            $qresult = $res->row_array(); //$connect->GetOne($qquery); //Checked)
            if ($qresult==0)
            {
                $query="INSERT into ".$this->db->dbprefix."answers (qid,code,answer,language,sortorder,scale_id) VALUES ($qid,'A1','".$clang->gT("Some example answer option")."','$baselang',0,$i)";
                db_execute_assoc($query);
            }
        }


        // check that there are answers for every language supported by the survey
        for ($i = 0; $i < $scalecount; $i++)
        {
            foreach ($anslangs as $language)
            {

                $resultcount = db_execute_assoc("SELECT count(*) as num_ans  FROM ".$this->db->dbprefix."answers WHERE qid=$qid AND scale_id=$i AND language='".$language."'");
                $rowcount = $resultcount->row_array();
                $iAnswerCount = $rowcount['num_ans'];

                if ($iAnswerCount == 0)   // means that no record for the language exists in the answers table
                {
                    $qquery = "INSERT INTO ".$this->db->dbprefix."answers (qid,code,answer,sortorder,language,scale_id, assessment_value) (SELECT qid,code,answer,sortorder, '".$language."','$i', assessment_value FROM ".$this->db->dbprefix."answers WHERE qid=$qid AND scale_id=$i AND language='".$baselang."')";
                    db_execute_assoc($qquery); //Checked
                }
            }
        }

        array_unshift($anslangs,$baselang);      // makes an array with ALL the languages supported by the survey -> $anslangs

        //delete the answers in languages not supported by the survey
        $languagequery = "SELECT DISTINCT language FROM ".$this->db->dbprefix."answers WHERE (qid = $qid) AND (language NOT IN ('".implode("','",$anslangs)."'))";
        $languageresult = db_execute_assoc($languagequery); //Checked
        foreach ($languageresult->result_array() as $qrow)
        {
            $deleteanswerquery = "DELETE FROM ".$this->db->dbprefix."answers WHERE (qid = $qid) AND (language = '".$qrow["language"]."')";
            db_execute_assoc($deleteanswerquery); //Checked
        }
        $_POST = $this->input->post();
        if (!isset($_POST['ansaction']))
        {
            //check if any nulls exist. If they do, redo the sortorders
            $caquery="SELECT * FROM ".$this->db->dbprefix."answers WHERE qid=$qid AND sortorder is null AND language='".$baselang."'";
            $caresult=db_execute_assoc($caquery); //Checked
            $cacount=$caresult->num_rows();
            if ($cacount)
            {
                fixsortorderAnswers($qid);
            }
        }
        $this->load->helper('admin/htmleditor');

        $query = "SELECT sortorder FROM ".$this->db->dbprefix."answers WHERE qid='{$qid}' AND language='".GetBaseLanguageFromSurveyID($surveyid)."' ORDER BY sortorder desc";
        $result = db_execute_assoc($query);// or safe_die($connect->ErrorMsg()); //Checked
        $anscount = $result->num_rows();
        $row=$result->row_array();
        if ($result->num_rows > 0)
        $maxsortorder=$row['sortorder']+1;
        else
        $maxsortorder=1;
        $data['clang'] = $this->limesurvey_lang;
        $data['surveyid'] = $surveyid;
        $data['gid'] = $gid;
        $data['qid'] = $qid;
        $data['anslangs'] = $anslangs;
        $data['scalecount'] = $scalecount;

        // the following line decides if the assessment input fields are visible or not
        $this->load->model('surveys_model');
        $sumresult1 = $this->surveys_model->getDataOnSurvey($surveyid); //$sumquery1, 1) ; //Checked
        if ($sumresult1->num_rows()==0){die('Invalid survey id');} //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->row_array();
        $surveyinfo = array_map('FlattenText', $surveyinfo);
        $assessmentvisible=($surveyinfo['assessments']=='Y' && $qtypes[$qtype]['assessable']==1);
        $data['assessmentvisible'] = $assessmentvisible;
        $this->load->view('admin/survey/Question/answerOptions_view',$data);





    }

    /**
     * question::subquestions()
     * Load complete subquestions screen.
     * @param mixed $surveyid
     * @param mixed $gid
     * @param mixed $qid
     * @return
     */
    function subquestions($surveyid,$gid,$qid)
    {
    	$surveyid = sanitize_int($surveyid);
		$qid = sanitize_int($qid);
		$gid = sanitize_int($gid);

        self::_js_admin_includes(base_url().'scripts/jquery/jquery.dd.js');
        self::_js_admin_includes(base_url().'scripts/admin/subquestions.js');
        self::_js_admin_includes(base_url().'scripts/jquery/jquery.blockUI.js');
        self::_js_admin_includes(base_url().'scripts/jquery/jquery.selectboxes.min.js');


        $css_admin_includes[] = base_url().'scripts/jquery/dd.css';

        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);

        self::_getAdminHeader();
        self::_showadminmenu($surveyid);;
        self::_surveybar($surveyid,$gid);
        self::_surveysummary($surveyid,"viewgroup");
        self::_questiongroupbar($surveyid,$gid,$qid,"addquestion");
        self::_questionbar($surveyid,$gid,$qid,"editsubquestions");

        $this->session->set_userdata('FileManagerContext',"edit:answer:{$surveyid}");

        self::_editsubquestion($surveyid,$gid,$qid);
        self::_loadEndScripts();


	   self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));



    }

    /**
     * question::_editsubquestion()
     * Load only subquestion specific screen only.
     * @param mixed $surveyid
     * @param mixed $gid
     * @param mixed $qid
     * @return
     */
    function _editsubquestion($surveyid,$gid,$qid)
    {

    	$surveyid = sanitize_int($surveyid);
		$qid = sanitize_int($qid);
		$gid = sanitize_int($gid);

        $this->load->helper('database');
        $clang = $this->limesurvey_lang;

        // Get languages select on survey.
        $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);

        $sQuery = "SELECT type FROM ".$this->db->dbprefix."questions WHERE qid={$qid} AND language='{$baselang}'";

        $res = db_execute_assoc($sQuery);

        $resultrow = $res->row_array();

        $sQuestiontype=$resultrow['type']; //$connect->GetOne($sQuery);
        $aQuestiontypeInfo=getqtypelist($sQuestiontype,'array');
        $iScaleCount=$aQuestiontypeInfo[$sQuestiontype]['subquestions'];

        for ($iScale = 0; $iScale < $iScaleCount; $iScale++)
        {
            $sQuery = "SELECT * FROM ".$this->db->dbprefix."questions WHERE parent_qid={$qid} AND language='{$baselang}' and scale_id={$iScale}";
            $subquestiondata=db_execute_assoc($sQuery); //$connect->GetArray($sQuery);
            if ($subquestiondata->num_rows() == 0)
            {
                    $sQuery = "INSERT INTO ".$this->db->dbprefix."questions (sid,gid,parent_qid,title,question,question_order,language,scale_id)
                               VALUES($surveyid,$gid,$qid,'SQ001','".$clang->gT('Some example subquestion')."',1,'".$baselang."',{$iScale})";
                    db_execute_assoc($sQuery); //Checked
                    $sQuery = "SELECT * FROM ".$this->db->dbprefix."questions WHERE parent_qid={$qid} AND language='{$baselang}' and scale_id={$iScale}";
                    $subquestiondata=db_execute_assoc($sQuery); //$connect->GetArray($sQuery);
            }
            // check that there are subquestions for every language supported by the survey
            foreach ($anslangs as $language)
            {
                foreach ($subquestiondata->result_array() as $row)
                {

                    $sQuery = "SELECT count(*) AS countall FROM ".$this->db->dbprefix."questions WHERE parent_qid={$qid} AND language='{$language}' AND qid={$row['qid']} AND scale_id={$iScale}";
                    $res = db_execute_assoc($sQuery);
                    $resrow = $res->row_array();
                    $qrow = $resrow['countall']; //$connect->GetOne($sQuery); //Checked
                    if ($qrow == 0)   // means that no record for the language exists in the questions table
                    {
                            db_switchIDInsert('questions',true);
                            $sQuery = "INSERT INTO ".$this->db->dbprefix."questions (qid,sid,gid,parent_qid,title,question,question_order,language, scale_id)
                                       VALUES({$row['qid']},$surveyid,{$row['gid']},$qid,'".$row['title']."','".$row['question']."',{$row['question_order']},'".$language."',{$iScale})";
                            db_execute_assoc($sQuery); //Checked
                            db_switchIDInsert('questions',false);
                    }
                }
            }
        }


        array_unshift($anslangs,$baselang);      // makes an array with ALL the languages supported by the survey -> $anslangs

        //delete the subquestions in languages not supported by the survey
        $qquery = "SELECT DISTINCT language FROM ".$this->db->dbprefix."questions WHERE (parent_qid = $qid) AND (language NOT IN ('".implode("','",$anslangs)."'))";
        $qresult = db_execute_assoc($qquery); //Checked
        foreach ($qresult->result_array() as $qrow)
        {
            $qquery = "DELETE FROM ".$this->db->dbprefix."questions WHERE (parent_qid = $qid) AND (language = '".$qrow["language"]."')";
            db_execute_assoc($qquery); //Checked
        }


        // Check sort order for subquestions
        $qquery = "SELECT type FROM ".$this->db->dbprefix."questions WHERE qid=$qid AND language='".$baselang."'";
        $qresult = db_execute_assoc($qquery); //Checked
        foreach ($qresult->result_array() as $qrow) {$qtype=$qrow['type'];}
        if (!$this->input->post('ansaction'))
        {
            //check if any nulls exist. If they do, redo the sortorders
            $caquery="SELECT * FROM ".$this->db->dbprefix."questions WHERE parent_qid=$qid AND question_order is null AND language='".$baselang."'";
            $caresult=db_execute_assoc($caquery); //Checked
            $cacount=$caresult->num_rows();
            if ($cacount)
            {
                fixsortorderAnswers($qid,$surveyid); // !!Adjust this!!
            }
        }
        $this->load->helper('admin/htmleditor_helper');
        // Print Key Control JavaScript

        $query = "SELECT question_order FROM ".$this->db->dbprefix."questions WHERE parent_qid='{$qid}' AND language='".GetBaseLanguageFromSurveyID($surveyid)."' ORDER BY question_order desc";
        $result = db_execute_assoc($query); // or safe_die($connect->ErrorMsg()); //Checked
        $data['anscount'] = $anscount = $result->num_rows();
        $row=$result->row_array();
        $data['row'] = $row;
        $maxsortorder=$row['question_order']+1;


        // the following line decides if the assessment input fields are visible or not
        // for some question types the assessment values is set in the label set instead of the answers
        $qtypes=getqtypelist('','array');
        $this->load->helper('surveytranslator');
        $data['scalecount'] = $scalecount=$qtypes[$qtype]['subquestions'];

        $this->load->model('surveys_model');
        //$sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
        $sumresult1 = $this->surveys_model->getDataOnSurvey($surveyid); //$sumquery1, 1) ; //Checked
        if ($sumresult1->num_rows()==0){die('Invalid survey id');} //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->row_array();
        $surveyinfo = array_map('FlattenText', $surveyinfo);
        //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
        $data['activated'] = $activated = $surveyinfo['active'];
        $data['clang'] = $clang;
        $data['surveyid'] = $surveyid;
        $data['gid'] = $gid;
        $data['qid'] = $qid;
        $data['anslangs'] = $anslangs;
        $data['maxsortorder'] = $maxsortorder;


        $this->load->view('admin/survey/Question/subQuestion_view',$data);
    }


    /**
     * question::index()
     * Load edit/new question screen depending on $action.
     * @param mixed $action
     * @param mixed $surveyid
     * @param mixed $gid
     * @param mixed $qid
     * @return
     */
    function index($action,$surveyid,$gid,$qid=null)
    {

    	$surveyid = sanitize_int($surveyid);
		if(isset($qid)) $qid = sanitize_int($qid);
		$gid = sanitize_int($gid);

        self::_js_admin_includes(base_url().'scripts/jquery/jquery.dd.js');
        $css_admin_includes[] = base_url().'scripts/jquery/dd.css';

        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);

        self::_getAdminHeader();
        self::_showadminmenu($surveyid);;
        self::_surveybar($surveyid,$gid);
        self::_surveysummary($surveyid,"viewgroup");
        self::_questiongroupbar($surveyid,$gid,$qid,"addquestion");
        self::_questionbar($surveyid,$gid,$qid,"editquestion");


        if(bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            $this->session->set_userdata('FileManagerContext',"edit:question:".$surveyid);
            $_POST = $this->input->post();
            $clang = $this->limesurvey_lang;
            $this->load->helper('admin/htmleditor');
            $this->load->helper('surveytranslator');
            $this->load->helper('database');

            if (isset($_POST['sortorder'])) {$postsortorder=sanitize_int($_POST['sortorder']);}

            $data['adding'] = $adding =($action=="addquestion");
            $questlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            $questlangs[] = $baselang;
            $questlangs = array_flip($questlangs);
            	// prepare selector Mode TODO: with and without image

            if (!$adding)
            {
                $egquery = "SELECT * FROM ".$this->db->dbprefix."questions WHERE sid=$surveyid AND gid=$gid AND qid=$qid";
                $egresult = db_execute_assoc($egquery);
                foreach ($egresult->result_array() as $esrow)
                {
                    if(!array_key_exists($esrow['language'], $questlangs)) // Language Exists, BUT ITS NOT ON THE SURVEY ANYMORE.
                    {
                        $egquery = "DELETE FROM ".$this->db->dbprefix."questions WHERE sid='{$surveyid}' AND gid='{$gid}' AND qid='{$qid}' AND language='".$esrow['language']."'";
                        $egresultD = db_execute_assoc($egquery);
                    } else {
                        $questlangs[$esrow['language']] = 99;
                    }
                    if ($esrow['language'] == $baselang)
                    {
                        $basesettings = array('question_order' => $esrow['question_order'],
                                               'other' => $esrow['other'],
                                               'mandatory' => $esrow['mandatory'],
                                               'type' => $esrow['type'],
                                               'title' => $esrow['title'],
                                               'preg' => $esrow['preg'],
                                               'question' => $esrow['question'],
                                               'help' => $esrow['help']);
                    }
                }
                if ($egresult==false or $egresult->num_rows()==0)
                {
                    safe_die('Invalid question id');
                }


                while (list($key,$value) = each($questlangs))
                {
                    if ($value != 99)
                    {
                        db_switchIDInsert('questions',true);
                        $egquery = "INSERT INTO ".$this->db->dbprefix."questions (qid, sid, gid, type, title, question, preg, help, other, mandatory, question_order, language)"
                        ." VALUES ('{$qid}','{$surveyid}', '{$gid}', '{$basesettings['type']}', '{$basesettings['title']}',"
                        ." '{$basesettings['question']}', '{$basesettings['preg']}', '{$basesettings['help']}', '{$basesettings['other']}', '{$basesettings['mandatory']}', '{$basesettings['question_order']}','{$key}')";
                        $egresult = db_execute_assoc($egquery);
                        db_switchIDInsert('questions',false);
                    }
                }

                $eqquery = "SELECT ".$this->db->dbprefix."questions.*, group_name FROM ".$this->db->dbprefix."questions
                            join ".$this->db->dbprefix."groups on ".$this->db->dbprefix."groups.gid=".$this->db->dbprefix."questions.gid WHERE ".$this->db->dbprefix."questions.sid=$surveyid AND ".$this->db->dbprefix."questions.gid=$gid AND qid=$qid AND ".$this->db->dbprefix."questions.language='{$baselang}'";
                $eqresult = db_execute_assoc($eqquery);
            }

            $qtypelist=getqtypelist('','array');
            $qDescToCode = 'qDescToCode = {';
            $qCodeToInfo = 'qCodeToInfo = {';
            foreach ($qtypelist as $qtype=>$qdesc){
                $qDescToCode .= " '{$qdesc['description']}' : '{$qtype}', \n";
                $qCodeToInfo .= " '{$qtype}' : '".ls_json_encode($qdesc)."', \n";
            }
            $data['qTypeOutput'] = "$qDescToCode 'null':'null' }; \n $qCodeToInfo 'null':'null' };";


            if (!$adding)
            {
                $eqrow = $eqresult->row_array();  // there should be only one datarow, therefore we don't need a 'while' construct here.
                // Todo: handler in case that record is not found
            }
            else
            {
                $eqrow['language']=$baselang;
                $eqrow['title']='';
                $eqrow['question']='';
                $eqrow['help']='';
                $eqrow['type']='T';
                $eqrow['lid']=0;
                $eqrow['lid1']=0;
                $eqrow['gid']=$gid;
                $eqrow['other']='N';
                $eqrow['mandatory']='N';
                $eqrow['preg']='';
                $eqrow['relevance']=1;
            }
            $data['eqrow'] = $eqrow;
            $data['surveyid'] = $surveyid;
            $data['gid'] = $gid;


            if (!$adding)
            {
                $aqquery = "SELECT * FROM ".$this->db->dbprefix."questions WHERE sid=$surveyid AND gid=$gid AND qid=$qid AND language != '{$baselang}'";
                $aqresult = db_execute_assoc($aqquery);
                $data['aqresult'] = $aqresult;
            }
            $data['clang'] = $clang;
            $data['action'] = $action;

            $this->load->model('surveys_model');
            $sumresult1 = $this->surveys_model->getDataOnSurvey($surveyid); //$sumquery1, 1) ; //Checked
            if ($sumresult1->num_rows()==0){die('Invalid survey id');} //  if surveyid is invalid then die to prevent errors at a later time
            $surveyinfo = $sumresult1->row_array();
            $surveyinfo = array_map('FlattenText', $surveyinfo);
            $data['activated'] = $activated = $surveyinfo['active'];


            if ($activated != "Y")
            {
            	// Prepare selector Class for javascript function : TODO with or without picture
            	$selectormodeclass='full'; // default
            	if ($this->session->userdata('questionselectormode')=='none'){$selectormodeclass='none';}
                $data['selectormodeclass'] = $selectormodeclass;
            }

            if (!$adding) {$qattributes=questionAttributes();}
            else
            {
                $qattributes=array();
            }

            if ($adding)
            {

                //Get the questions for this group
                $baselang = GetBaseLanguageFromSurveyID($surveyid);
                $oqquery = "SELECT * FROM ".$this->db->dbprefix."questions WHERE sid=$surveyid AND gid=$gid AND language='".$baselang."' order by question_order" ;
                $oqresult = db_execute_assoc($oqquery);
                $data['oqresult'] = $oqresult;
            }

            $data['qid'] = $qid;

            $this->load->view("admin/survey/Question/editQuestion_view",$data);
            self::_questionJavascript($eqrow['type']);


        }
        else
        {
            include('access_denied.php');
        }

        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));


    }

    /**
     * question::_questionjavascript()
     * Load javascript functions required in question screen.
     * @param mixed $type
     * @return
     */
    function _questionjavascript($type)
    {
        $this->load->view('admin/survey/Question/questionJavascript_view',array('type' => $type));
    }

    /**
     * question::delete()
     * Function responsible for deleting a question.
     * @return
     */
    function delete()
    {
        $clang = $this->limesurvey_lang;
        $action = $this->input->post('action');
        $surveyid = $this->input->post('sid');
        $gid = $this->input->post('gid');
        $qid = $this->input->post('qid');
        $this->load->helper("database");

        if ($action == "delquestion" && bHasSurveyPermission($surveyid, 'surveycontent','delete'))
        {
            if (!isset($qid)) {$qid=returnglobal('qid');}
            //check if any other questions have conditions which rely on this question. Don't delete if there are.
            // TMSW Conditions->Relevance:  Allow such deletes - can warn about missing relevance separately.
            $ccquery = "SELECT * FROM ".$this->db->dbprefix."as WHERE cqid=$qid";
            $ccresult = db_execute_assoc($ccquery); // or safe_die ("Couldn't get list of cqids for this question<br />".$ccquery."<br />".$connect->ErrorMsg()); // Checked
            $cccount=$ccresult->num_rows();
            foreach ($ccresult->result_array() as $ccr) {$qidarray[]=$ccr['qid'];}
            if (isset($qidarray)) {$qidlist=implode(", ", $qidarray);}
            $databaseoutput = '';
            if ($cccount) //there are conditions dependent on this question
            {
                $databaseoutput = "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be deleted. There are conditions for other questions that rely on this question. You cannot delete this question until those conditions are removed","js")." ($qidlist)\")\n //-->\n</script>\n";
            }
            else
            {
                $sql = "SELECT gid FROM ".$this->db->dbprefix."questions WHERE qid={$qid}";
                $result = db_execute_assoc($sql);
                $row = $result->row_array();
                $gid = $row['gid'];

                //see if there are any conditions/attributes/answers/defaultvalues for this question, and delete them now as well
                db_execute_assoc("DELETE FROM ".$this->db->dbprefix."conditions WHERE qid={$qid}");    // Checked
                db_execute_assoc("DELETE FROM ".$this->db->dbprefix."question_attributes WHERE qid={$qid}"); // Checked
                db_execute_assoc("DELETE FROM ".$this->db->dbprefix."answers WHERE qid={$qid}"); // Checked
                db_execute_assoc("DELETE FROM ".$this->db->dbprefix."questions WHERE qid={$qid} or parent_qid={$qid}"); // Checked
                db_execute_assoc("DELETE FROM ".$this->db->dbprefix."defaultvalues WHERE qid={$qid}"); // Checked
                db_execute_assoc("DELETE FROM ".$this->db->dbprefix."quota_members WHERE qid={$qid}");
                fixsortorderQuestions($gid, $surveyid);

                $qid="";
                $postqid="";
                $_GET['qid']="";
            }
            $this->session->set_userdata('flashmessage', $clang->gT("Question was successfully deleted."));

            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                redirect(site_url('admin/survey/view/'.$surveyid."/".$gid));
            }
        }


    }


    /**
     * question::ajaxquestionattributes()
     * This function prepares the data for the advanced question attributes view
     *
     */
    function ajaxquestionattributes()
    {
        $this->load->model('questions_model');
        $surveyid = $this->input->post("sid");
        $qid = (int)$this->input->post("qid");
        $type=$this->input->post('question_type');

        $aLanguages=array_merge(array(GetBaseLanguageFromSurveyID($surveyid)),GetAdditionalLanguagesFromSurveyID($surveyid));
        $thissurvey=getSurveyInfo($surveyid);
        $aAttributesWithValues=$this->questions_model->getAdvancedSettingsWithValues($qid, $type, $surveyid);
        uasort($aAttributesWithValues,'CategorySort');

        $aAttributesPrepared=array();
        foreach ($aAttributesWithValues as $iKey=>$aAttribute)
        {
            if ($aAttribute['i18n']==false)
            {
                 $aAttributesPrepared[]=$aAttribute;
            }
            else  // $qa['i18n'] == true
            {
                foreach($aLanguages as $sLanguage)
                {
                    $aAttributeModified=$aAttribute;
                    $aAttributeModified['name']=$aAttributeModified['name'].'_'.$sLanguage;
                    $aAttributeModified['language']=$sLanguage;
                    if ($aAttributeModified['readonly']==true && $thissurvey['active']=='N')
                    {
                        $aAttributeModified['readonly']==false;
                    }
                    if (isset($aAttributeModified[$sLanguage]['value']))
                    {
                        $aAttributeModified['value']=$aAttributeModified[$sLanguage]['value'];
                    }
                    else
                    {
                        $aAttributeModified['value']=$aAttributeModified['default'];
                    }
                    $aAttributesPrepared[]=$aAttributeModified;
                }
            }
        }


        $aData['attributedata']=$aAttributesPrepared;
        $this->load->view('admin/survey/Question/advanced_settings_view',$aData);
    }

    /**
     * question::preview()
     * Load preview of a question screen.
     * @param mixed $surveyid
     * @param mixed $qid
     * @param mixed $lang
     * @return
     */
    function preview($surveyid, $qid, $lang = null)
    {
    	$surveyid = sanitize_int($surveyid);
		$qid = sanitize_int($qid);

		$this->load->helper("qanda");
		$this->load->helper("surveytranslator");

		if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
		$surveyid = (int) $surveyid;
		if (!isset($qid)) {$qid=returnglobal('qid');}
		if (empty($surveyid)) {die("No SID provided.");}
		if (empty($qid)) {die("No QID provided.");}

		if (!isset($lang) || $lang == "")
		{
		    $language = GetBaseLanguageFromSurveyID($surveyid);
		} else {
		    $language = $lang;
		}

		//Use $_SESSION instead of $this->session for frontend features.
		$_SESSION['s_lang'] = $language;
		$_SESSION['fieldmap']=createFieldMap($surveyid,'full',true,$qid);
		// Prefill question/answer from defaultvalues
		foreach ($_SESSION['fieldmap'] as $field)
		{
		    if (isset($field['defaultvalue']))
		    {
		        $_SESSION[$field['fieldname']]=$field['defaultvalue'];
		    }
		}
		$clang = new limesurvey_lang(array($language));

		$thissurvey=getSurveyInfo($surveyid);
		setNoAnswerMode($thissurvey);
		$_SESSION['dateformats'] = getDateFormatData($thissurvey['surveyls_dateformat']);

		$qquery = 'SELECT * FROM '.$this->db->dbprefix('questions')." WHERE sid='$surveyid' AND qid='$qid' AND language='".$this->db->escape_str($language)."'";
		$qresult = db_execute_assoc($qquery);
		$qrows = $qresult->row_array();
		$ia = array(0 => $qid,
		1 => $surveyid.'X'.$qrows['gid'].'X'.$qid,
		2 => $qrows['title'],
		3 => $qrows['question'],
		4 => $qrows['type'],
		5 => $qrows['gid'],
		6 => $qrows['mandatory'],
		//7 => $qrows['other']); // ia[7] is conditionsexist not other
		7 => 'N',
		8 => 'N' ); // ia[8] is usedinconditions

        // This is needed to properly detect and color code EM syntax errors
        LimeExpressionManager::StartProcessingPage();
        LimeExpressionManager::StartProcessingGroup($qrows['gid'],false,$surveyid,true);  // loads list of replacement values available for this group

		$answers = retrieveAnswers($ia);

		if (!$thissurvey['template'])
		{
		    $thistpl=sGetTemplatePath($defaulttemplate);
		}
		else
		{
		    $thistpl=sGetTemplatePath(validate_templatedir($thissurvey['template']));
		}

		doHeader();
		$dummy_js = '
				<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->
				<script type="text/javascript">
		        /* <![CDATA[ */
		            function checkconditions(value, name, type)
		            {
		            }
				function noop_checkconditions(value, name, type)
				{
				}
		        /* ]]> */
				</script>
		        ';


		$answer=$answers[0][1];
		$help=$answers[0][2];

		$question = $answers[0][0];
		$question['code']=$answers[0][5];
		$question['class'] = question_class($qrows['type']);
		$question['essentials'] = 'id="question'.$qrows['qid'].'"';
		$question['sgq']=$ia[1];

		//Temporary fix for error condition arising from linked question via replacement fields
		//@todo: find a consistent way to check and handle this - I guess this is already handled but the wrong values are entered into the DB
        // TMSW Conditions->Relevance:  Show relevance instead of this dependency notation

		$search_for = '{INSERTANS';
		if(strpos($question['text'],$search_for)!==false){
		    $pattern_text = '/{([A-Z])*:([0-9])*X([0-9])*X([0-9])*}/';
		    $replacement_text = $clang->gT('[Dependency on another question (ID $4)]');
		    $text = preg_replace($pattern_text,$replacement_text,$question['text']);
		    $question['text']=$text;
		}

		if ($qrows['mandatory'] == 'Y')
		{
		    $question['man_class'] = ' mandatory';
		}
		else
		{
		    $question['man_class'] = '';
		};

        $redata = compact(array_keys(get_defined_vars()));
		$content = templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata,'question[1312]');
		$content .='<form method="post" action="index.php" id="limesurvey" name="limesurvey" autocomplete="off">';
		$content .= templatereplace(file_get_contents("$thistpl/startgroup.pstpl"),array(),$redata,'question[1314]');

		$question_template = file_get_contents("$thistpl/question.pstpl");
		if(substr_count($question_template , '{QUESTION_ESSENTIALS}') > 0 ) // the following has been added for backwards compatiblity.
		{// LS 1.87 and newer templates
		$content .= "\n".templatereplace($question_template,array(),$redata,'question[1319]',false,$qid)."\n";
		}
		else
		{// LS 1.86 and older templates
		$content .= '<div '.$question['essentials'].' class="'.$question['class'].$question['man_class'].'">';
		$content .= "\n".templatereplace($question_template,array(),$redata,'question[1324]',false,$qid)."\n";
		$content .= "\n\t</div>\n";
		};

		$content .= templatereplace(file_get_contents("$thistpl/endgroup.pstpl"),array(),$redata,'question[1328]').$dummy_js;
		$content .= '<p>&nbsp;</form>';
		$content .= templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata,'question[1330]');

        // if want to  include Javascript in question preview, uncomment these.  However, Group level preview is probably adequate
        LimeExpressionManager::FinishProcessingGroup();
        $content .= LimeExpressionManager::GetRelevanceAndTailoringJavaScript();
        LimeExpressionManager::FinishProcessingPage();

		echo $content;
		echo "</html>\n";


		exit;
	}


 }
