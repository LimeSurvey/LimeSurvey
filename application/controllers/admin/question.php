<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
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
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */

 /**
 * question
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
 * @version $Id: question.php 11260 2011-10-25 18:34:55Z tmswhite $
 * @access public
 */
class question extends Survey_Common_Action
{
    /**
     * Routes to the correct sub-action
     *
     * @access public
     * @param string $sa
     * @return void
     */
    public function run($sa)
    {
        if ($sa == 'addquestion' || $sa == 'index' || $sa == 'editquestion' || $sa == 'copyquestion')
            $this->route('index', array('sa', 'surveyid', 'gid', 'qid'));
        elseif ($sa == 'subquestions')
            $this->route('subquestions', array('surveyid', 'gid', 'qid'));
        elseif ($sa == 'import')
            $this->route('import', array());
        elseif ($sa == 'preview')
            $this->route('preview', array('surveyid', 'qid', 'lang'));
        elseif ($sa == 'ajaxquestionattributes')
            $this->route('ajaxquestionattributes', array());
        elseif ($sa == 'answeroptions')
            $this->route('answeroptions', array('surveyid', 'gid', 'qid'));
        elseif ($sa == 'editdefaultvalues')
            $this->route('editdefaultvalues', array('surveyid', 'gid', 'qid'));
        elseif ($sa == 'deletequestion')
            $this->route('delete', array('sa', 'surveyid', 'gid', 'qid'));
    }

    /**
     * Function responsible to import a question.
     *
     * @access public
     * @return void
     */
    public function import()
    {
        $action = returnglobal('action');
        $surveyid = returnglobal('sid');
        $gid = returnglobal('gid');
        $clang = $this->getController()->lang;

        $css_admin_includes[] = Yii::app()->getConfig('styleurl') . "/admin/default/superfish.css";
        Yii::app()->setConfig("css_admin_includes", $css_admin_includes);

        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($surveyid);
        $this->_surveybar($surveyid, $gid);
        $this->_surveysummary($surveyid, "viewquestion");
        $this->_questiongroupbar($surveyid, $gid, NULL, "viewgroup");

        if ($action == 'importquestion')
        {
            $sFullFilepath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $_FILES['the_file']['name'];
            $aPathInfo = pathinfo($sFullFilepath);
            $sExtension = $aPathInfo['extension'];

            if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath))
                $fatalerror = sprintf($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."), Yii::app()->getConfig('tempdir'));

            // validate that we have a SID and GID
            if (!$surveyid)
                $fatalerror .= $clang->gT("No SID (Survey) has been provided. Cannot import question.");

            if (!$gid)
                $fatalerror .= $clang->gT("No GID (Group) has been provided. Cannot import question");

            if (isset($fatalerror))
            {
                unlink($sFullFilepath);
                $this->getController()->error($fatalerror);
            }

            // IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY
            Yii::app()->loadHelper('admin/import');

            if (strtolower($sExtension) == 'csv')
                $aImportResults = CSVImportQuestion($sFullFilepath, $surveyid, $gid);
            elseif (strtolower($sExtension) == 'lsq')
                $aImportResults = XMLImportQuestion($sFullFilepath, $surveyid, $gid);
            else
                $this->getController()->error($clang->gT('Unknown file extension'));

            FixLanguageConsistency($surveyid);

            if (isset($aImportResults['fatalerror']))
            {
                unlink($sFullFilepath);
                $this->getController()->error($aImportResults['fatalerror']);
            }

            unlink($sFullFilepath);

            $this->getController()->render('/admin/survey/Question/import_view', array(
                'clang' => $clang,
                'aImportResults' => $aImportResults,
                'surveyid' => $surveyid,
                'gid' => $gid,
                'sExtension' => $sExtension,
            ));
        }

        $this->getController()->_loadEndScripts();

        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
    }

    /**
     * Load edit default values of a question screen
     *
     * @access public
     * @param int $surveyid
     * @param int $gid
     * @param int $qid
     * @return void
     */
    public function editdefaultvalues($surveyid, $gid, $qid)
    {
        $surveyid = sanitize_int($surveyid);
        $gid = sanitize_int($gid);
        $qid = sanitize_int($qid);

        $this->getController()->_css_admin_includes(Yii::app()->getConfig('styleurl') . "admin/default/superfish.css");
        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($surveyid);

        $this->_surveybar($surveyid, $gid);
        $this->_surveysummary($surveyid, "editdefaultvalues");
        $this->_questiongroupbar($surveyid, $gid, $qid, "editdefaultvalues");
        $this->_questionbar($surveyid, $gid, $qid, "editdefaultvalues");

        $clang = $this->getController()->lang;

        Yii::app()->loadHelper('surveytranslator');

        $questlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($questlangs, $baselang);

        $questionrow = Questions::model()->findByAttributes(array(
            'qid' => $qid,
            'gid' => $gid,
            'language' => $baselang
        ))->attributes;
        $qtproperties = getqtypelist('', 'array');

        $langopts = array();
        foreach ($questlangs as $language)
        {
            $langopts[$language] = array();
            $langopts[$language][$questionrow['type']] = array();

            // If there are answerscales
            if ($qtproperties[$questionrow['type']]['answerscales'] > 0)
            {
                for ($scale_id = 0; $scale_id < $qtproperties[$questionrow['type']]['answerscales']; $scale_id++)
                {
                    $langopts[$language][$questionrow['type']][$scale_id] = array();

                    $defaultvalue = Defaultvalues::model()->findByAttributes(array(
                        'specialtype' => '',
                        'qid' => $qid,
                        'scale_id' => $scale_id,
                        'language' => $language
                    ));

                    $defaultvalue = $defaultvalue != null ? $defaultvalue->defaultvalue : null;

                    $langopts[$language][$questionrow['type']][$scale_id]['defaultvalue'] = $defaultvalue;

                    $answerresult = Answers::model()->findAllByAttributes(array(
                        'qid' => $qid,
                        'language' => $language
                    ), array('order' => 'sortorder'));
                    $langopts[$language][$questionrow['type']][$scale_id]['answers'] = $answerresult;

                    if ($questionrow['other'] == 'Y')
                    {
                        $defaultvalue = Defaultvalues::model()->findByAttributes(array(
                            'specialtype' => 'other',
                            'qid' => $qid,
                            'scale_id' => $scale_id,
                            'language' => $language
                        ));

                        $defaultvalue = $defaultvalue != null ? $defaultvalue->defaultvalue : null;
                        $langopts[$language][$questionrow['type']]['Ydefaultvalue'] =
                                $defaultvalue == null ? '' : $defaultvalue->defaultvalue;
                    }
                }
            }

            // If there are subquestions and no answerscales
            if ($qtproperties[$questionrow['type']]['answerscales'] == 0 &&
                    $qtproperties[$questionrow['type']]['subquestions'] > 0)
            {
                for ($scale_id = 0; $scale_id < $qtproperties[$questionrow['type']]['subquestions']; $scale_id++)
                {
                    $langopts[$language][$questionrow['type']][$scale_id] = array();

                    $sqresult = Questions::model()->findAllByAttributes(array(
                        'sid' => $surveyid,
                        'gid' => $gid,
                        'parent_qid' => $qid,
                        'language' => $language,
                        'scale_id' => 0
                    ), array('order' => 'question_order'));

                    $langopts[$language][$questionrow['type']][$scale_id]['sqresult'] = array();

                    $options = array();
                    if ($questionrow['type'] == 'M' || $questionrow['type'] == 'P')
                        $options = array('' => $clang->gT('<No default value>'), 'Y' => $clang->gT('Checked'));

                    foreach ($sqresult as $aSubquestion)
                    {
                        $defaultvalue = Defaultvalues::model()->findByAttributes(array(
                            'specialtype' => '',
                            'qid' => $qid,
                            'scale_id' => $scale_id,
                            'language' => $language
                        ));
                        $defaultvalue = $defaultvalue != null ? $defaultvalue->defaultvalue : null;

                        $aSubquestion = $aSubquestion->attributes;
                        $aSubquestion['defaultvalue'] = $defaultvalue;
                        $aSubquestion['options'] = $options;

                        $langopts[$language][$questionrow['type']][$scale_id]['sqresult'][] = $aSubquestion;
                    }
                }
            }
        }

        $this->getController()->render('/admin/survey/Question/editdefaultvalues_view', array(
            'qid' => $qid,
            'surveyid' => $surveyid,
            'langopts' => $langopts,
            'questionrow' => $questionrow,
            'questlangs' => $questlangs,
            'gid' => $gid,
            'qtproperties' => $qtproperties,
            'baselang' => $baselang,
            'clang' => $clang,
        ));

        $this->getController()->_loadEndScripts();

        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
    }

    /**
     * Load complete editing of answer options screen.
     *
     * @access public
     * @param int $surveyid
     * @param int $gid
     * @param int $qid
     * @return
     */
    public function answeroptions($surveyid, $gid, $qid)
    {
        $surveyid = sanitize_int($surveyid);
        $qid = sanitize_int($qid);
        $gid = sanitize_int($gid);
        $this->getController()->_js_admin_includes(Yii::app()->baseUrl .'/scripts/jquery/jquery.dd.js');
        $this->getController()->_js_admin_includes(Yii::app()->baseUrl .'/scripts/admin/answers.js');
        $this->getController()->_js_admin_includes(Yii::app()->baseUrl .'/scripts/jquery/jquery.blockUI.js');
        $this->getController()->_js_admin_includes(Yii::app()->baseUrl .'/scripts/jquery/jquery.selectboxes.min.js');

        $css_admin_includes[] = Yii::app()->baseUrl . 'scripts/jquery/dd.css';

        $css_admin_includes[] = Yii::app()->getConfig('styleurl') . "admin/default/superfish.css";
        Yii::app()->setConfig("css_admin_includes", $css_admin_includes);

        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($surveyid);

        $this->_surveybar($surveyid, $gid);
        $this->_surveysummary($surveyid, "viewgroup");
        $this->_questiongroupbar($surveyid, $gid, $qid, "addquestion");
        $this->_questionbar($surveyid, $gid, $qid, "editansweroptions");

        Yii::app()->session['FileManagerContext'] = "edit:answer:{$surveyid}";

        $this->_editansweroptions($surveyid, $gid, $qid);
        $this->getController()->_loadEndScripts();

        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
    }

    /**
     * Load editing of answer options specific screen only.
     *
     * @access public
     * @param int $surveyid
     * @param int $gid
     * @param int $qid
     * @return void
     */
    public function _editansweroptions($surveyid, $gid, $qid)
    {
        Yii::app()->loadHelper('database');
        $surveyid = sanitize_int($surveyid);
        $qid = sanitize_int($qid);
        $gid = sanitize_int($gid);

        // Get languages select on survey.
        $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);

        $qrow = Questions::model()->findByAttributes(array('qid' => $qid, 'language' => $baselang))->attributes;

        $qtype = $qrow['type'];

        $qtypes = getqtypelist('', 'array');

        $scalecount = $qtypes[$qtype]['answerscales'];

        $clang = $this->getController()->lang;

        // Check if there is at least one answer
        for ($i = 0; $i < $scalecount; $i++)
        {
            $ans = new CDbCriteria;
            $ans->addCondition("qid=$qid")->addCondition("scale_id=$i")->addCondition("language='$baselang'");
            $qresult = Answers::model()->count($ans);

            if ((int)$qresult=0)
                Answers::model()->insert(array(
                    'qid' => $qid,
                    'code' => 'A1',
                    'answer' => $clang->gT('Some example answer option'),
                    'language' => $baselang,
                    'sortorder' => 0,
                    'scale_id' => $i,
                ));
        }


        // Check that there are answers for every language supported by the survey
        for ($i = 0; $i < $scalecount; $i++)
        {
            foreach ($anslangs as $language)
            {
                $ans = new CDbCriteria;
                $ans->addCondition("qid=$qid")->addCondition("scale_id=$i")->addCondition("language='$language'");
                $iAnswerCount = Answers::model()->count($ans);

                // Means that no record for the language exists in the answers table
                if (empty($iAnswerCount))
                    foreach (Answers::model()->findAllByAttributes(array(
                                'qid' => $qid,
                                'scale_id' => $i,
                                'language' => $baselang
                            )) as $answer)
                        Answers::model()->insert(array(
                            'qid' => $answer->qid,
                            'code' => $answer->code,
                            'answer' => $answer->answer,
                            'language' => $language,
                            'sortorder' => $answer->sortorder,
                            'scale_id' => $i,
                            'assessment_value' => $answer->assessment_value,
                        ));
            }
        }

        // Makes an array with ALL the languages supported by the survey -> $anslangs
        array_unshift($anslangs, $baselang);

        // Delete the answers in languages not supported by the survey
        $criteria = new CDbCriteria;
        $criteria->addColumnCondition(array('qid' => $qid));
        $criteria->addNotInCondition('language', $anslangs);
        $languageresult = Answers::model()->deleteAll($criteria);

        if (!isset($_POST['ansaction']))
        {
            // Check if any nulls exist. If they do, redo the sortorders
            $ans = new CDbCriteria;
            $ans->addCondition("qid=$qid")->addCondition("scale_id=$i")->addCondition("language='$baselang'");
            $cacount = Answers::model()->count($ans);
            if (!empty($cacount))
                Answers::updateSortOrder($qid, GetBaseLanguageFromSurveyID($surveyid));
        }

        Yii::app()->loadHelper('admin/htmleditor');

        $row = Answers::model()->findByAttributes(array(
            'qid' => $qid,
            'language' => GetBaseLanguageFromSurveyID($surveyid)
        ), array('order' => 'sortorder desc'));

        if (!is_null($row))
            $maxsortorder = $row->sortorder + 1;
        else
            $maxsortorder = 1;

        $data['clang'] = $this->getController()->lang;
        $data['surveyid'] = $surveyid;
        $data['gid'] = $gid;
        $data['qid'] = $qid;
        $data['anslangs'] = $anslangs;
        $data['scalecount'] = $scalecount;

        // The following line decides if the assessment input fields are visible or not
        $sumresult1 = Survey::model()->with('languagesettings')->together()->findByAttributes(array('sid' => $surveyid));
        if (is_null($sumresult1))
            $this->getController()->error('Invalid survey ID');

        $surveyinfo = $sumresult1->attributes;
        $surveyinfo = array_merge($surveyinfo, $sumresult1->languagesettings->attributes);
        $surveyinfo = array_map('FlattenText', $surveyinfo);
        $assessmentvisible = ($surveyinfo['assessments'] == 'Y' && $qtypes[$qtype]['assessable'] == 1);
        $data['assessmentvisible'] = $assessmentvisible;
        $this->getController()->render('/admin/survey/Question/answerOptions_view', $data);
    }

    /**
     * Load complete subquestions screen.
     *
     * @access public
     * @param int $surveyid
     * @param int $gid
     * @param int $qid
     * @return void
     */
    public function subquestions($surveyid, $gid, $qid)
    {
        $surveyid = sanitize_int($surveyid);
        $qid = sanitize_int($qid);
        $gid = sanitize_int($gid);

        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.dd.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'admin/subquestions.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.blockUI.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery.selectboxes.min.js');

        $css_admin_includes[] = Yii::app()->getConfig('generalscripts') . 'jquery/dd.css';
        $css_admin_includes[] = Yii::app()->getConfig('styleurl') . "admin/default/superfish.css";
        Yii::app()->setConfig("css_admin_includes", $css_admin_includes);

        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($surveyid);
        $this->_surveybar($surveyid, $gid);
        $this->_surveysummary($surveyid, "viewgroup");
        $this->_questiongroupbar($surveyid, $gid, $qid, "addquestion");
        $this->_questionbar($surveyid, $gid, $qid, "editsubquestions");
        $this->getController()->_loadEndScripts();

        Yii::app()->session['FileManagerContext'] = "edit:answer:{$surveyid}";

        /* @todo Make this work */
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
    }

    /**
     * Load only subquestion specific screen only.
     *
     * @access public
     * @param int $surveyid
     * @param int $gid
     * @param int $qid
     * @return void
     */
    public function _editsubquestion($surveyid, $gid, $qid)
    {
        $surveyid = sanitize_int($surveyid);
        $qid = sanitize_int($qid);
        $gid = sanitize_int($gid);

        $clang = $this->getController()->lang;

        // Get languages select on survey.
        $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);

        $resultrow = Questions::model()->findAllByPk(array('qid' => $qid, 'language' => $baselang))->attributes;

        $sQuestiontype = $resultrow['type'];
        $aQuestiontypeInfo = getqtypelist($sQuestiontype, 'array');
        $iScaleCount = $aQuestiontypeInfo[$sQuestiontype]['subquestions'];

        for ($iScale = 0; $iScale < $iScaleCount; $iScale++)
        {
            $subquestiondata = Questions::model()->findAllByAttributes(array(
                'parent_qid' => $qid,
                'language' => $baselang,
                'scale_id' => $iScale
            ));

            if (!is_null($subquestiondata))
            {
                Questions::model()->insert(array(
                    'sid' => $surveyid,
                    'gid' => $gid,
                    'parent_qid' => $qid,
                    'title' => 'SQ001',
                    'question' => $clang->gT('Some example subquestion'),
                    'question_order' => 1,
                    'language' => $baselang,
                    'scale_id' => $iScale,
                ));

                $subquestiondata = Questions::model()->findAllByAttributes(array(
                    'parent_qid' => $qid,
                    'language' => $baselang,
                    'scale_id' => $iScale
                ));
            }

            // Check that there are subquestions for every language supported by the survey
            foreach ($anslangs as $language)
            {
                foreach ($subquestiondata as $row)
                {
                    $qrow = Questions::model()->count(array(
                        'parent_qid' => $qid,
                        'language' => $language,
                        'qid' => $row->qid,
                        'scale_id' => $iScale
                    ));

                    // Means that no record for the language exists in the questions table
                    if (empty($qrow))
                    {
                        db_switchIDInsert('questions', true);

                        Questions::model()->insert(array(
                            'qid' => $row->qid,
                            'sid' => $surveyid,
                            'gid' => $row->gid,
                            'parent_qid' => $qid,
                            'title' => $row->title,
                            'question' => $row->question,
                            'question_order' => $row->question_order,
                            'language' => $language,
                            'scale_id' => $iScale,
                        ));

                        db_switchIDInsert('questions', false);
                    }
                }
            }
        }

        array_unshift($anslangs, $baselang);

        // Delete the subquestions in languages not supported by the survey
        $criteria = new CDbCriteria;
        $criteria->addColumnCondition(array('parent_qid' => $qid));
        $criteria->addNotInCondition('languge', $anslangs);
        Questions::model()->deleteAll($criteria);

        // Check sort order for subquestions
        $qresult = Questions::model()->findByAttributes(array('qid' => $qid, 'language' => $baselang));
        if (!is_null($qresult))
            $qtype = $qresult->type;

        if (!empty($_POST['ansaction']))
        {
            // Check if any nulls exist. If they do, redo the sortorders
            $cacount = Questions::model()->count(array(
                'parent_qid' => $qid,
                'question_order' => null,
                'language' => $baselang
            ));

            if ($cacount)
                Answers::updateSortOrder($qid, GetBaseLanguageFromSurveyID($surveyid));
        }

        Yii::app()->loadHelper('admin/htmleditor_helper');

        // Print Key Control JavaScript
        $result = Questions::model()->findAllBYAttributes(array(
            'parent_qid' => $qid,
            'language' => GetBaseLanguageFromSurveyID($surveyid)
        ), array('order' => 'question_order desc'));

        $data['anscount'] = $anscount = count($result);
        $row = $result[0]->attributes;
        $data['row'] = $row;
        $maxsortorder = $row['question_order'] + 1;

        /**
         * The following line decides if the assessment input fields are visible or not
         * for some question types the assessment values is set in the label set instead of the answers
         */
        $qtypes = getqtypelist('', 'array');
        Yii::app()->loadHelper('surveytranslator');

        $data['scalecount'] = $scalecount = $qtypes[$qtype]['subquestions'];

        $sumresult1 = Survey::model()->with('languagesettings')->together()->findByAttributes(array('t.sid' => $surveyid));
        if ($sumresult1->num_rows() == 0)
            $this->getController()->error('Invalid survey id');

        $surveyinfo = $sumresult1->attributes;
        $surveyinfo = array_merge($surveyinfo, $sumresult1->languagesettings->attributes);
        $surveyinfo = array_map('FlattenText', $surveyinfo);

        $data['activated'] = $activated = $surveyinfo['active'];
        $data['clang'] = $clang;
        $data['surveyid'] = $surveyid;
        $data['gid'] = $gid;
        $data['qid'] = $qid;
        $data['anslangs'] = $anslangs;
        $data['maxsortorder'] = $maxsortorder;

        $this->getController()->render('admin/survey/Question/subQuestion_view', $data);
    }

    /**
     * Load edit/new question screen depending on $action.
     *
     * @access public
     * @param string $action
     * @param int $surveyid
     * @param int $gid
     * @param int $qid
     * @return void
     */
    public function index($action, $surveyid, $gid, $qid=null)
    {
        $surveyid = sanitize_int($surveyid);
        if (isset($qid))
            $qid = sanitize_int($qid);
        $gid = sanitize_int($gid);

        $this->getController()->_js_admin_includes(Yii::app()->baseUrl . '/scripts/jquery/jquery.dd.js');
        $this->getController()->_css_admin_includes(Yii::app()->baseUrl . '/scripts/jquery/dd.css');
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('styleurl') . "admin/default/superfish.css");

        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($surveyid);
        $this->_surveybar($surveyid, $gid);
        $this->_surveysummary($surveyid, "viewgroup");
        $this->_questiongroupbar($surveyid, $gid, $qid, "addquestion");

        if ($action != "addquestion")
            $this->_questionbar($surveyid, $gid, $qid, "editquestion");

        if (bHasSurveyPermission($surveyid, 'surveycontent', 'read'))
        {
            Yii::app()->session['FileManagerContext'] = "edit:question:" . $surveyid;

            $clang = $this->getController()->lang;
            Yii::app()->loadHelper('admin/htmleditor');
            Yii::app()->loadHelper('surveytranslator');

            if (isset($_POST['sortorder']))
                $postsortorder = sanitize_int($_POST['sortorder']);

            $data['adding'] = $adding = $action == 'addquestion';
            $data['copying'] = $copying = $action == 'copyquestion';
            $questlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            $questlangs[] = $baselang;
            $questlangs = array_flip($questlangs);

            // Prepare selector Mode TODO: with and without image
            if (!$adding)
            {
                $egresult = Questions::model()->findAllByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'qid' => $qid));

                foreach ($egresult as $esrow)
                {
                    if (!array_key_exists($esrow->language, $questlangs)) // Language Exists, BUT ITS NOT ON THE SURVEY ANYMORE.
                        $esrow->delete();
                    else
                        $questlangs[$esrow->language] = 99;
                    
                    if ($esrow->language == $baselang)
                    {
                        $esrow = $esrow->attributes;
                        $basesettings = array(
                            'question_order' => $esrow['question_order'],
                            'other' => $esrow['other'],
                            'mandatory' => $esrow['mandatory'],
                            'type' => $esrow['type'],
                            'title' => $esrow['title'],
                            'preg' => $esrow['preg'],
                            'question' => $esrow['question'],
                            'help' => $esrow['help']
                        );
                    }
                }

                if (!$egresult)
                    $this->getController()->error('Invalid question id');

                while (list($key, $value) = each($questlangs))
                {
                    if ($value != 99)
                    {
                        Questions::model()->insert(array(
                            'qid' => $qid,
                            'sid' => $surveyid,
                            'gid' => $gid,
                            'type' => $basesettings['tyoe'],
                            'title' => $basesettings['title'],
                            'question' => $basesettings['question'],
                            'preg' => $basesettings['preg'],
                            'help' => $basesettings['help'],
                            'other' => $basesettings['other'],
                            'mandatory' => $basesettings['mandatory'],
                            'question_order' => $basesettings['question_order'],
                            'language' => $key,
                        ));
                    }
                }

                $eqresult = Questions::model()->with('groups')->together()->findByAttributes(array(
                    'sid' => $surveyid,
                    'gid' => $gid,
                    'qid' => $qid,
                    'language' => $baselang
                ));
            }

            $qtypelist = getqtypelist('', 'array');
            $qDescToCode = 'qDescToCode = {';
            $qCodeToInfo = 'qCodeToInfo = {';
            foreach ($qtypelist as $qtype => $qdesc)
            {
                $qDescToCode .= " '{$qdesc['description']}' : '{$qtype}', \n";
                $qCodeToInfo .= " '{$qtype}' : '" . ls_json_encode($qdesc) . "', \n";
            }
            $data['qTypeOutput'] = "$qDescToCode 'null':'null' }; \n $qCodeToInfo 'null':'null' };";

            if (!$adding)
            {
                $eqrow = array_merge($eqresult->attributes, $eqresult->groups->attributes);;

                // Todo: handler in case that record is not found
                if ($copying)
                    $eqrow['title'] = '';
            }
            else
            {
                $eqrow['language'] = $baselang;
                $eqrow['title'] = '';
                $eqrow['question'] = '';
                $eqrow['help'] = '';
                $eqrow['type'] = 'T';
                $eqrow['lid'] = 0;
                $eqrow['lid1'] = 0;
                $eqrow['gid'] = $gid;
                $eqrow['other'] = 'N';
                $eqrow['mandatory'] = 'N';
                $eqrow['preg'] = '';
                $eqrow['relevance'] = 1;
                $eqrow['group_name'] = '';
            }

            $data['eqrow'] = $eqrow;
            $data['surveyid'] = $surveyid;
            $data['gid'] = $gid;

            if (!$adding)
            {
                $criteria = new CDbCriteria;
                $criteria->addColumnCondition(array('sid' => $surveyid, 'gid' => $gid, 'qid' => $qid));
                $criteria->params[':lang'] = $baselang;
                $criteria->addCondition('language != :lang');
                $aqresult = Questions::model()->findAll($criteria);
                $data['aqresult'] = $aqresult;
            }

            $data['clang'] = $clang;
            $data['action'] = $action;

            $sumresult1 = Survey::model()->findByPk($surveyid);
            if (is_null($sumresult1))
                $this->getController()->error('Invalid Survey ID');

            $surveyinfo = $sumresult1->attributes;
            $surveyinfo = array_map('FlattenText', $surveyinfo);
            $data['activated'] = $activated = $surveyinfo['active'];

            if ($activated != "Y")
            {
                // Prepare selector Class for javascript function : TODO with or without picture
                $selectormodeclass = 'full';
                if (Yii::app()->session['questionselectormode'] == 'none')
                    $selectormodeclass = 'none';

                $data['selectormodeclass'] = $selectormodeclass;
            }

            if (!$adding)
                $qattributes = questionAttributes();
            else
                $qattributes = array();

            if ($adding)
            {
                // Get the questions for this group
                $baselang = GetBaseLanguageFromSurveyID($surveyid);
                $oqresult = Questions::model()->findAllByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'language' => $baselang), array('order' => 'question_order'));
                $data['oqresult'] = $oqresult;
            }

            $data['qid'] = $qid;

            $this->getController()->render("/admin/survey/Question/editQuestion_view", $data);
            $this->_questionJavascript($eqrow['type']);
        }
        else
            include('access_denied.php');

        $this->getController()->_loadEndScripts();

        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
    }

    /**
     * Load javascript functions required in question screen.
     *
     * @access public
     * @param string $type
     * @return void
     */
    public function _questionjavascript($type)
    {
        $this->getController()->render('/admin/survey/Question/questionJavascript_view', array('type' => $type));
    }

    /**
     * Function responsible for deleting a question.
     *
     * @access public
     * @param string $action
     * @param int $surveyid
     * @param int $gid
     * @param int $qid
     * @return void
     */
    public function delete($action, $surveyid, $gid, $qid)
    {
        $clang = $this->getController()->lang;
        $surveyid = sanitize_int($surveyid);
        $gid = sanitize_int($gid);
        $qid = sanitize_int($qid);

        if ($action == "deletequestion" && bHasSurveyPermission($surveyid, 'surveycontent', 'delete'))
        {
            if (!isset($qid))
                $qid = returnglobal('qid');

            // Check if any other questions have conditions which rely on this question. Don't delete if there are.
            // TMSW Conditions->Relevance:  Allow such deletes - can warn about missing relevance separately.
            $ccresult = Conditions::model()->findAllByAttributes(array('cqid' => $qid));

            $cccount = count($ccresult);
            foreach ($ccresult as $ccr)
                $qidarray[] = $ccr->qid;

            if (isset($qidarray))
                $qidlist = implode(", ", $qidarray);

            // There are conditions dependent on this question
            if ($cccount)
                $this->getController()->error($clang->gT("Question could not be deleted. There are conditions for other questions that rely on this question. You cannot delete this question until those conditions are removed"));
            else
            {
                $row = Questions::model()->findByAttributes(array('qid' => $qid))->attributes;
                $gid = $row['gid'];

                // See if there are any conditions/attributes/answers/defaultvalues for this question,
                // and delete them now as well
                Conditions::model()->deleteAllByAttributes(array('qid' => $qid));
                Question_attributes::model()->deleteAllByAttributes(array('qid' => $qid));
                Answers::model()->deleteAllByAttributes(array('qid' => $qid));

                $criteria = new CDbCriteria;
                $criteria->addCondition('qid = :qid or parent_qid = :qid');
                $criteria->params[':qid'] = $qid;
                Questions::model()->deleteAll($criteria);

                Defaultvalues::model()->deleteAllByAttributes(array('qid' => $qid));
                Quota_members::model()->deleteAllByAttributes(array('qid' => $qid));

                Questions::updateSortOrder($gid, $surveyid);

                $qid = "";
                $postqid = "";
                $_GET['qid'] = "";
            }

            Yii::app()->session['flashmessage'] = $clang->gT("Question was successfully deleted.");

            $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/' . $surveyid . '/gid/' . $gid));
        }
        else
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You are not authorized to delete questions.");
            $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/' . $surveyid . '/gid/' . $gid));
        }
    }

    /**
     * This function prepares the data for the advanced question attributes view
     *
     * @access public
     * @return void
     */
    public function ajaxquestionattributes()
    {
        $surveyid = (int) $_POST['sid'];
        $qid = (int) $_POST['qid'];
        $type = $_POST['question_type'];

        $aLanguages = array_merge(array(GetBaseLanguageFromSurveyID($surveyid)), GetAdditionalLanguagesFromSurveyID($surveyid));
        $thissurvey = getSurveyInfo($surveyid);

        $aAttributesWithValues = Questions::model()->getAdvancedSettingsWithValues($qid, $type, $surveyid);
        uasort($aAttributesWithValues, 'CategorySort');

        $aAttributesPrepared = array();
        foreach ($aAttributesWithValues as $iKey => $aAttribute)
        {
            if ($aAttribute['i18n'] == false)
                $aAttributesPrepared[] = $aAttribute;
            else
            {
                foreach ($aLanguages as $sLanguage)
                {
                    $aAttributeModified = $aAttribute;
                    $aAttributeModified['name'] = $aAttributeModified['name'] . '_' . $sLanguage;
                    $aAttributeModified['language'] = $sLanguage;
                    if ($aAttributeModified['readonly'] == true && $thissurvey['active'] == 'N')
                        $aAttributeModified['readonly'] == false;

                    if (isset($aAttributeModified[$sLanguage]['value']))
                        $aAttributeModified['value'] = $aAttributeModified[$sLanguage]['value'];
                    else
                        $aAttributeModified['value'] = $aAttributeModified['default'];

                    $aAttributesPrepared[] = $aAttributeModified;
                }
            }
        }

        $aData['attributedata'] = $aAttributesPrepared;
        $this->getController()->render('/admin/survey/Question/advanced_settings_view', $aData);
    }

    /**
     * Load preview of a question screen.
     *
     * @access public
     * @param int $surveyid
     * @param int $qid
     * @param string $lang
     * @return void
     */
    public function preview($surveyid, $qid, $lang = null)
    {
        $surveyid = sanitize_int($surveyid);
        $qid = sanitize_int($qid);

        Yii::app()->loadHelper("qanda");

        Yii::app()->loadHelper("surveytranslator");

        if (!isset($surveyid))
            $surveyid = returnglobal('sid');

        $surveyid = (int) $surveyid;
        if (!isset($qid))
            $qid = returnglobal('qid');

        if (empty($surveyid))
            $this->getController()->error('No Survey ID provided');
        if (empty($qid))
            $this->getController()->error('No Question ID provided');

        if (!isset($lang) || $lang == "")
            $language = GetBaseLanguageFromSurveyID($surveyid);
        else
            $language = $lang;

        // Use $_SESSION instead of $this->session for frontend features.
        $_SESSION['s_lang'] = $language;
        $_SESSION['fieldmap'] = createFieldMap($surveyid, 'full', true, $qid);

        // Prefill question/answer from defaultvalues
        foreach ($_SESSION['fieldmap'] as $field)
            if (isset($field['defaultvalue']))
                $_SESSION[$field['fieldname']] = $field['defaultvalue'];

        $clang = new limesurvey_lang(array($language));

        $thissurvey = getSurveyInfo($surveyid);

        setNoAnswerMode($thissurvey);

        $_SESSION['dateformats'] = getDateFormatData($thissurvey['surveyls_dateformat']);

        $qresult = Questions::model()->findByAttributes(array('sid' => $surveyid, 'qid' => $qid, 'language' => $language));

        $qrows = $qresult->attributes;
        $ia = array(
            0 => $qid,
            1 => $surveyid . 'X' . $qrows['gid'] . 'X' . $qid,
            2 => $qrows['title'],
            3 => $qrows['question'],
            4 => $qrows['type'],
            5 => $qrows['gid'],
            6 => $qrows['mandatory'],
            7 => 'N',
            8 => 'N'
        );

        // This is needed to properly detect and color code EM syntax errors
        LimeExpressionManager::StartProcessingPage();

        $answers = retrieveAnswers($ia);

        if (!$thissurvey['template'])
            $thistpl = sGetTemplatePath(Yii::app()->getConfig('defaulttemplate'));
        else
            $thistpl = sGetTemplatePath(validate_templatedir($thissurvey['template']));

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
				</script>';


        $answer = $answers[0][1];
        $help = $answers[0][2];

        $question = $answers[0][0];
        $question['code'] = $answers[0][5];
        $question['class'] = question_class($qrows['type']);
        $question['essentials'] = 'id="question' . $qrows['qid'] . '"';
        $question['sgq'] = $ia[1];

        // Temporary fix for error condition arising from linked question via replacement fields
        // @todo: find a consistent way to check and handle this - I guess this is already
        // handled but the wrong values are entered into the DB
        // TMSW Conditions->Relevance:  Show relevance instead of this dependency notation

        $search_for = '{INSERTANS';
        if (strpos($question['text'], $search_for) !== false)
        {
            $pattern_text = '/{([A-Z])*:([0-9])*X([0-9])*X([0-9])*}/';
            $replacement_text = $clang->gT('[Dependency on another question (ID $4)]');
            $text = preg_replace($pattern_text, $replacement_text, $question['text']);
            $question['text'] = $text;
        }

        if ($qrows['mandatory'] == 'Y')
            $question['man_class'] = ' mandatory';
        else
            $question['man_class'] = '';

        $redata = compact(array_keys(get_defined_vars()));
        $content = templatereplace(file_get_contents("$thistpl/startpage.pstpl"), array(), $redata, 'question[1312]');
        $content .='<form method="post" action="index.php" id="limesurvey" name="limesurvey" autocomplete="off">';
        $content .= templatereplace(file_get_contents("$thistpl/startgroup.pstpl"), array(), $redata, 'question[1314]');

        $question_template = file_get_contents("$thistpl/question.pstpl");
        // the following has been added for backwards compatiblity.
        if (substr_count($question_template, '{QUESTION_ESSENTIALS}') > 0)
        {
            // LS 1.87 and newer templates
            $content .= "\n" . templatereplace($question_template, array(), $redata, 'question[1319]', false, $qid) . "\n";
        }
        else
        {
            // LS 1.86 and older templates
            $content .= '<div ' . $question['essentials'] . ' class="' . $question['class'] . $question['man_class'] . '">';
            $content .= "\n" . templatereplace($question_template, array(), $redata, 'question[1324]', false, $qid) . "\n";
            $content .= "\n\t</div>\n";
        };

        $content .= templatereplace(file_get_contents("$thistpl/endgroup.pstpl"), array(), $redata, 'question[1328]') . $dummy_js;
        $content .= '<p>&nbsp;</form>';
        $content .= templatereplace(file_get_contents("$thistpl/endpage.pstpl"), array(), $redata, 'question[1330]');

        // If want to  include Javascript in question preview, uncomment these.
        // However, Group level preview is probably adequate
        LimeExpressionManager::FinishProcessingGroup();
        LimeExpressionManager::FinishProcessingPage();

        echo $content;
        echo "</html>\n";

        exit;
    }
}