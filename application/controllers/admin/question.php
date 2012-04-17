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
 *	$Id$
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
class question extends Survey_Common_Action
{

    /**
     * Function responsible to import a question.
     *
     * @access public
     * @return void
     */
    public function import()
    {
        $action = returnGlobal('action');
        $surveyid = returnGlobal('sid');
        $gid = returnGlobal('gid');
        $clang = $this->getController()->lang;
        $aViewUrls = array();

        $aData['display']['menu_bars']['surveysummary'] = 'viewquestion';
        $aData['display']['menu_bars']['gid_action'] = 'viewgroup';

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

            fixLanguageConsistency($surveyid);

            if (isset($aImportResults['fatalerror']))
            {
                unlink($sFullFilepath);
                $this->getController()->error($aImportResults['fatalerror']);
            }

            unlink($sFullFilepath);

            $aData['aImportResults'] = $aImportResults;
            $aData['surveyid'] = $surveyid;
            $aData['gid'] = $gid;
            $aData['sExtension'] = $sExtension;
            $aViewUrls[] = 'import_view';
        }

        $this->_renderWrappedTemplate('survey/Question', $aViewUrls, $aData);
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

        $aData['display']['menu_bars']['surveysummary'] = 'editdefaultvalues';
        $aData['display']['menu_bars']['gid_action'] = 'editdefaultvalues';
        $aData['display']['menu_bars']['qid_action'] = 'editdefaultvalues';

        $clang = $this->getController()->lang;

        Yii::app()->loadHelper('surveytranslator');

        $questlangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
        $baselang = Survey::model()->findByPk($surveyid)->language;
        array_unshift($questlangs, $baselang);

        $questionrow = Questions::model()->findByAttributes(array(
            'qid' => $qid,
            'gid' => $gid,
            'language' => $baselang
        ))->attributes;
        $qtproperties = getQuestionTypeList('', 'array');

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

        $aData = array(
            'qid' => $qid,
            'surveyid' => $surveyid,
            'langopts' => $langopts,
            'questionrow' => $questionrow,
            'questlangs' => $questlangs,
            'gid' => $gid,
            'qtproperties' => $qtproperties,
            'baselang' => $baselang,
        );

        $this->_renderWrappedTemplate('survey/Question', 'editdefaultvalues_view', $aData);
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
        $this->getController()->_css_admin_includes(Yii::app()->baseUrl . '/scripts/jquery/dd.css');

        $aData['display']['menu_bars']['surveysummary'] = 'viewgroup';
        $aData['display']['menu_bars']['gid_action'] = 'addquestion';
        $aData['display']['menu_bars']['qid_action'] = 'editansweroptions';

        $aData['surveyid'] = $surveyid;
        $aData['gid']      = $gid;
        $aData['qid']      = $qid;

        Yii::app()->session['FileManagerContext'] = "edit:answer:{$surveyid}";

        $aViewUrls = $this->_editansweroptions($surveyid, $gid, $qid);

        $this->_renderWrappedTemplate('survey/Question', $aViewUrls, $aData);
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
        $anslangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
        $baselang = Survey::model()->findByPk($surveyid)->language;

        $qrow = Questions::model()->findByAttributes(array('qid' => $qid, 'language' => $baselang));
        $qtype = $qrow['type'];

        $qtypes = getQuestionTypeList('', 'array');

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
                Answers::updateSortOrder($qid, Survey::model()->findByPk($surveyid)->language);
        }

        Yii::app()->loadHelper('admin/htmleditor');

        $row = Answers::model()->findByAttributes(array(
            'qid' => $qid,
            'language' => Survey::model()->findByPk($surveyid)->language
        ), array('order' => 'sortorder desc'));

        if (!is_null($row))
            $maxsortorder = $row->sortorder + 1;
        else
            $maxsortorder = 1;

        $aData['surveyid'] = $surveyid;
        $aData['gid'] = $gid;
        $aData['qid'] = $qid;
        $aData['anslangs'] = $anslangs;
        $aData['scalecount'] = $scalecount;

        // The following line decides if the assessment input fields are visible or not
        $sumresult1 = Survey::model()->with(array('languagesettings'=>array('condition'=>'surveyls_language=language')))->together()->findByAttributes(array('sid' => $surveyid));
        if (is_null($sumresult1))
            $this->getController()->error('Invalid survey ID');

        $surveyinfo = $sumresult1->attributes;
        $surveyinfo = array_merge($surveyinfo, $sumresult1->languagesettings[0]->attributes);
        $surveyinfo = array_map('flattenText', $surveyinfo);
        $assessmentvisible = ($surveyinfo['assessments'] == 'Y' && $qtypes[$qtype]['assessable'] == 1);
        $aData['assessmentvisible'] = $assessmentvisible;

        $aViewUrls['answerOptions_view'][] = $aData;

        return $aViewUrls;
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
        $aData['surveyid'] = $surveyid = sanitize_int($surveyid);
        $aData['gid'] = $gid = sanitize_int($gid);
        $aData['qid'] = $qid = sanitize_int($qid);

        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.dd.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'admin/subquestions.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.blockUI.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.selectboxes.min.js');
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/dd.css');
        Yii::app()->session['FileManagerContext'] = "edit:answer:{$surveyid}";

        $aData['display']['menu_bars']['surveysummary'] = 'viewgroup';
        $aData['display']['menu_bars']['gid_action'] = 'addquestion';
        $aData['display']['menu_bars']['qid_action'] = 'editsubquestions';
        $aViewUrls = $this->_editsubquestion($surveyid, $gid, $qid);

        $this->_renderWrappedTemplate('survey/Question', $aViewUrls, $aData);
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
        $anslangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
        $baselang = Survey::model()->findByPk($surveyid)->language;

        $resultrow = Questions::model()->findByPk(array('qid' => $qid, 'language' => $baselang))->attributes;

        $sQuestiontype = $resultrow['type'];
        $aQuestiontypeInfo = getQuestionTypeList($sQuestiontype, 'array');
        $iScaleCount = $aQuestiontypeInfo[$sQuestiontype]['subquestions'];

        for ($iScale = 0; $iScale < $iScaleCount; $iScale++)
        {
            $subquestiondata = Questions::model()->findAllByAttributes(array(
                'parent_qid' => $qid,
                'language' => $baselang,
                'scale_id' => $iScale
            ));

            if (empty($subquestiondata))
            {
                //Questions::model()->insert();
                $data = array(
                    'sid' => $surveyid,
                    'gid' => $gid,
                    'parent_qid' => $qid,
                    'title' => 'SQ001',
                    'question' => $clang->gT('Some example subquestion'),
                    'question_order' => 1,
                    'language' => $baselang,
                    'scale_id' => $iScale,
                );
                Questions::model()->insertRecords($data);

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
                    $qrow = Questions::model()->count('
                        parent_qid = :qid AND
                        language = :language AND
                        qid = '.$row->qid.' AND
                        scale_id = :iScale',
                        array(
                            ':qid' => $qid,
                            ':language' => $language,
                            ':iScale' => $iScale
                    ));

                    // Means that no record for the language exists in the questions table
                    if (empty($qrow))
                    {
                        switchMSSQLIdentityInsert('questions', true);

                        $question = new Questions;
                        $question->qid = $row->qid;
                        $question->sid = $surveyid;
                        $question->gid = $row->gid;
                        $question->parent_qid = $qid;
                        $question->title = $row->title;
                        $question->question = $row->question;
                        $question->question_order = $row->question_order;
                        $question->language = $language;
                        $question->scale_id = $iScale;
                        $question->save();
                        /** //activerecord is not not new bugfix!
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
                        */
                        switchMSSQLIdentityInsert('questions', false);
                    }
                }
            }
        }

        array_unshift($anslangs, $baselang);

        // Delete the subquestions in languages not supported by the survey
        $criteria = new CDbCriteria;
        $criteria->addColumnCondition(array('parent_qid' => $qid));
        $criteria->addNotInCondition('language', $anslangs);
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
                Answers::updateSortOrder($qid, Survey::model()->findByPk($surveyid)->language);
        }

        Yii::app()->loadHelper('admin/htmleditor');

        // Print Key Control JavaScript
        $result = Questions::model()->findAllBYAttributes(array(
            'parent_qid' => $qid,
            'language' => Survey::model()->findByPk($surveyid)->language
        ), array('order' => 'question_order desc'));

        $aData['anscount'] = $anscount = count($result);
        $row = $result[0]->attributes;
        $aData['row'] = $row;
        $maxsortorder = $row['question_order'] + 1;

        /**
         * The following line decides if the assessment input fields are visible or not
         * for some question types the assessment values is set in the label set instead of the answers
         */
        $qtypes = getQuestionTypeList('', 'array');
        Yii::app()->loadHelper('surveytranslator');

        $aData['scalecount'] = $scalecount = $qtypes[$qtype]['subquestions'];

        $sumresult1 = Survey::model()->with(array('languagesettings'=>array('condition'=>'surveyls_language=language')))->together()->findByAttributes(array('sid' => $surveyid));
        if ($sumresult1 == null)
            $this->getController()->error('Invalid survey id');

        $surveyinfo = $sumresult1->attributes;
        $surveyinfo = array_merge($surveyinfo, $sumresult1->languagesettings[0]->attributes);
        $surveyinfo = array_map('flattenText', $surveyinfo);

        $aData['activated'] = $activated = $surveyinfo['active'];
        $aData['surveyid'] = $surveyid;
        $aData['gid'] = $gid;
        $aData['qid'] = $qid;
        $aData['anslangs'] = $anslangs;
        $aData['maxsortorder'] = $maxsortorder;

        foreach ($anslangs as $anslang)
        {
            for ($scale_id = 0; $scale_id < $scalecount; $scale_id++)
            {
                $criteria = new CDbCriteria;
                $criteria->condition = 'parent_qid = :pqid AND language = :language AND scale_id = :scale_id';
                $criteria->order = 'question_order, title ASC';
                $criteria->params = array(':pqid' => $qid, ':language' => $anslang, ':scale_id' => $scale_id);
                $aData['results'][$anslang][$scale_id] = Questions::model()->findAll($criteria);
            }
        }

        $aViewUrls['subQuestion_view'][] = $aData;

        return $aViewUrls;
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
    public function index($sa, $surveyid, $gid, $qid=null)
    {
        $action = $sa;
        $surveyid = sanitize_int($surveyid);
        $gid = sanitize_int($gid);
        if (isset($qid))
            $qid = sanitize_int($qid);

        $aViewUrls = array();
        $aData['surveyid'] = $surveyid;
        $aData['gid'] = $gid;
        $aData['qid'] = $qid;
        $aData['display']['menu_bars']['surveysummary'] = 'viewgroup';
        $aData['display']['menu_bars']['gid_action'] = 'addquestion';
        Yii::app()->session['FileManagerContext'] = "create:question:{$surveyid}";

        if (hasSurveyPermission($surveyid, 'surveycontent', 'read'))
        {
            $clang = $this->getController()->lang;
            $surveyinfo = getSurveyInfo($surveyid);
            Yii::app()->loadHelper('admin/htmleditor');
            Yii::app()->loadHelper('surveytranslator');

            if (isset($_POST['sortorder']))
                $postsortorder = sanitize_int($_POST['sortorder']);

            $aData['adding'] = $adding = $action == 'addquestion';
            $aData['copying'] = $copying = $action == 'copyquestion';
            $questlangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
            $baselang = Survey::model()->findByPk($surveyid)->language;
            $questlangs[] = $baselang;
            $questlangs = array_flip($questlangs);

            // Prepare selector Mode TODO: with and without image
            if (!$adding)
            {
                Yii::app()->session['FileManagerContext'] = "edit:question:{$surveyid}";
                $aData['display']['menu_bars']['qid_action'] = 'editquestion';

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
                            'type' => $basesettings['type'],
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
            else
            {
                // This is needed to properly color-code content if it contains replacements
                LimeExpressionManager::StartProcessingPage(false,Yii::app()->baseUrl,true);  // so can click on syntax highlighting to edit questions
            }

            $qtypelist = getQuestionTypeList('', 'array');
            $qDescToCode = 'qDescToCode = {';
            $qCodeToInfo = 'qCodeToInfo = {';
            foreach ($qtypelist as $qtype => $qdesc)
            {
                $qDescToCode .= " '{$qdesc['description']}' : '{$qtype}', \n";
                $qCodeToInfo .= " '{$qtype}' : '" . ls_json_encode($qdesc) . "', \n";
            }
            $aData['qTypeOutput'] = "$qDescToCode 'null':'null' }; \n $qCodeToInfo 'null':'null' };";

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

            $aData['eqrow'] = $eqrow;
            $aData['surveyid'] = $surveyid;
            $aData['gid'] = $gid;

            if (!$adding)
            {
                $criteria = new CDbCriteria;
                $criteria->addColumnCondition(array('sid' => $surveyid, 'gid' => $gid, 'qid' => $qid));
                $criteria->params[':lang'] = $baselang;
                $criteria->addCondition('language != :lang');
                $aqresult = Questions::model()->findAll($criteria);
                $aData['aqresult'] = $aqresult;
            }

            $aData['clang'] = $clang;
            $aData['action'] = $action;

            $sumresult1 = Survey::model()->findByPk($surveyid);
            if (is_null($sumresult1))
                $this->getController()->error('Invalid Survey ID');

            $surveyinfo = $sumresult1->attributes;
            $surveyinfo = array_map('flattenText', $surveyinfo);
            $aData['activated'] = $activated = $surveyinfo['active'];

            if ($activated != "Y")
            {
                // Prepare selector Class for javascript function : TODO with or without picture
                $selectormodeclass = 'full';
                if (Yii::app()->session['questionselectormode'] == 'none')
                    $selectormodeclass = 'none';

                $aData['selectormodeclass'] = $selectormodeclass;
            }

            if (!$adding)
                $qattributes = questionAttributes();
            else
                $qattributes = array();

            if ($adding)
            {
                // Get the questions for this group
                $baselang = Survey::model()->findByPk($surveyid)->language;
                $oqresult = Questions::model()->findAllByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'language' => $baselang), array('order' => 'question_order'));
                $aData['oqresult'] = $oqresult;
            }

            $aViewUrls['editQuestion_view'][] = $aData;
            $aViewUrls['questionJavascript_view'][] = array('type' => $eqrow['type']);
        }
        else
            include('accessDenied.php');

        $this->_renderWrappedTemplate('survey/Question', $aViewUrls, $aData);
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
    public function delete($surveyid, $gid, $qid)
    {
        $clang = $this->getController()->lang;
        $surveyid = sanitize_int($surveyid);
        $gid = sanitize_int($gid);
        $qid = sanitize_int($qid);

        if (hasSurveyPermission($surveyid, 'surveycontent', 'delete'))
        {
            if (!isset($qid))
                $qid = returnGlobal('qid');

            LimeExpressionManager::RevertUpgradeConditionsToRelevance(NULL,$qid);

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

            $this->getController()->redirect($this->getController()->createUrl('admin/survey/view/surveyid/' . $surveyid . '/gid/' . $gid));
        }
        else
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You are not authorized to delete questions.");
            $this->getController()->redirect($this->getController()->createUrl('admin/survey/view/surveyid/' . $surveyid . '/gid/' . $gid));
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

        $aLanguages = array_merge(array(Survey::model()->findByPk($surveyid)->language), Survey::model()->findByPk($surveyid)->additionalLanguages);
        $thissurvey = getSurveyInfo($surveyid);

        $aAttributesWithValues = Questions::model()->getAdvancedSettingsWithValues($qid, $type, $surveyid);
        uasort($aAttributesWithValues, 'categorySort');

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
     * This function prepares the data for label set details
     *
     * @access public
     * @return void
     */
    public function ajaxlabelsetdetails()
    {
        $lid=returnglobal('lid');
        Yii::app()->loadHelper('surveytranslator');

        $labelsetdata=Labelsets::model()->find('lid=:lid',array(':lid' => $lid)); //$connect->GetArray($query);

        $labelsetlanguages=explode(' ',$labelsetdata->languages);
        foreach  ($labelsetlanguages as $language){

            //$query='select * from lime_labels where lid='.$lid." and language='{$language}' order by sortorder";
            $criteria=new CDbCriteria;
            $criteria->condition='lid=:lid and language=:language';
            $criteria->params=array(':lid'=>$lid, ':language'=>$language);
            $criteria->order='sortorder';
            $labelsdata=Label::model()->findAll($criteria);
            $i=0;
            foreach($labelsdata as $labeldata)
            {
                $data[$i]['lid'] = $labeldata->lid;
                $data[$i]['code'] = $labeldata->code;
                $data[$i]['title'] = $labeldata->title;
                $data[$i]['sortorder'] = $labeldata->sortorder;
                $data[$i]['assessment_value'] = $labeldata->assessment_value;
                $data[$i]['language'] = $labeldata->language;
                $i++;
            }
            $labels = $data;
            //$labels=dbExecuteAssoc($query); //Label::model()->find(array('lid' => $lid, 'language' => $language), array('order' => 'sortorder')); //$connect->GetArray($query);
            $resultdata[]=array($language=>array($labels,getLanguageNameFromCode($language,false)));
        }

        echo ls_json_encode($resultdata);
    }

    /**
     * This function prepares the data for labelset
     *
     * @access public
     * @return void
     */
    public function ajaxlabelsetpicker()
    {
        $match=(int)returnglobal('match');
        $surveyid=returnglobal('sid');
        if ($match==1)
        {
            $language=GetBaseLanguageFromSurveyID($surveyid);
        }
        else
        {
            $language=null;
        }
        $resultdata=getlabelsets($language);
        echo ls_json_encode($resultdata);
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
        $LEMdebugLevel=0;

        Yii::app()->loadHelper("qanda");
        Yii::app()->loadHelper("surveytranslator");

        if (empty($surveyid))
            $this->getController()->error('No Survey ID provided');
        if (empty($qid))
            $this->getController()->error('No Question ID provided');

        if (empty($lang))
            $language = Survey::model()->findByPk($surveyid)->language;
        else
            $language = $lang;

        if (!isset(Yii::app()->session['step'])) { Yii::app()->session['step'] = 0; }
        if (!isset(Yii::app()->session['prevstep'])) { Yii::app()->session['prevstep'] = 0; }
        if (!isset(Yii::app()->session['maxstep'])) { Yii::app()->session['maxstep'] = 0; }

        // Use $_SESSION instead of $this->session for frontend features.
        $_SESSION['survey_'.$surveyid]['s_lang'] = $language;
        $_SESSION['survey_'.$surveyid]['fieldmap'] = createFieldMap($surveyid, 'full', true, $qid, $language);


        // Prefill question/answer from defaultvalues
        foreach ($_SESSION['survey_'.$surveyid]['fieldmap'] as $field)
            if (isset($field['defaultvalue']))
                $_SESSION['survey_'.$surveyid][$field['fieldname']] = $field['defaultvalue'];

        $clang = new limesurvey_lang($language);

        $thissurvey = getSurveyInfo($surveyid);

        setNoAnswerMode($thissurvey);

        Yii::app()->session['dateformats'] = getDateFormatData($thissurvey['surveyls_dateformat']);

        $qrows = Questions::model()->findByAttributes(array('sid' => $surveyid, 'qid' => $qid, 'language' => $language))->getAttributes();

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

        $radix=getRadixPointData($thissurvey['surveyls_numberformat']);
        $radix = $radix['seperator'];
        $surveyOptions = array(
            'radix'=>$radix,
            );
        LimeExpressionManager::StartSurvey($surveyid, 'question', $surveyOptions, false, $LEMdebugLevel);
        $qseq = LimeExpressionManager::GetQuestionSeq($qid);
        $moveResult = LimeExpressionManager::JumpTo($qseq + 1, true, false, true);

        $answers = retrieveAnswers($ia,$surveyid);

        if (!$thissurvey['template'])
            $thistpl = getTemplatePath(Yii::app()->getConfig('defaulttemplate'));
        else
            $thistpl = getTemplatePath(validateTemplateDir($thissurvey['template']));

        doHeader();

        $showQuestion = "$('#question$qid').show();";
        $dummy_js = <<< EOD
            <script type='text/javascript'>
            <!--
            LEMradix='$radix';
            function fixnum_checkconditions(value, name, type, evt_type)
            {
                newval = value;
                if (LEMradix === ',') {
                    newval = new String(value);
                    newval = newval.split(',').join('.');
                }
                if (newval != parseFloat(newval)) {
                    newval = '';
                    if (name.match(/other$/)) {
                        $('#answer'+name+'text').val('');
                    }
                    $('#answer'+name).val('');
                }

                if (typeof evt_type === 'undefined')
                {
                    evt_type = 'onchange';
                }
                checkconditions(newval, name, type, evt_type);
            }

            function checkconditions(value, name, type, evt_type)
            {
                if (typeof evt_type === 'undefined')
                {
                    evt_type = 'onchange';
                }
                if (type == 'radio' || type == 'select-one')
                {
                    var hiddenformname='java'+name;
                    document.getElementById(hiddenformname).value=value;
                }
                else if (type == 'checkbox')
                {
                    if (document.getElementById('answer'+name).checked)
                    {
                        $('#java'+name).val('Y');
                    } else
                    {
                        $('#java'+name).val('');
                    }
                }
                else if (type == 'text' && name.match(/other$/) && typeof document.getElementById('java'+name) !== 'undefined' && document.getElementById('java'+name) != null)
                {
                    $('#java'+name).val(value);
                }
                ExprMgr_process_relevance_and_tailoring(evt_type,name,type);
                $showQuestion
            }
            $(document).ready(function() {
                $showQuestion
            });
            $(document).change(function() {
                $showQuestion
            });
            $(document).bind('keydown',function(e) {
                        if (e.keyCode == 9) {
                            $showQuestion
                            return true;
                        }
                        return true;
                    });
        // -->
        </script>
EOD;


        $answer = $answers[0][1];
//        $help = $answers[0][2];

        $qinfo = LimeExpressionManager::GetQuestionStatus($qid);
        $help = $qinfo['info']['help'];


        $question = $answers[0][0];
        $question['code'] = $answers[0][5];
        $question['class'] = getQuestionClass($qrows['type']);
        $question['essentials'] = 'id="question' . $qrows['qid'] . '"';
        $question['sgq'] = $ia[1];
        $question['aid']='unknown';
        $question['sqid']='unknown';

        if ($qrows['mandatory'] == 'Y')
            $question['man_class'] = ' mandatory';
        else
            $question['man_class'] = '';

        $redata = compact(array_keys(get_defined_vars()));
        $content = templatereplace(file_get_contents("$thistpl/startpage.pstpl"), array(), $redata);
        $content .='<form method="post" action="index.php" id="limesurvey" name="limesurvey" autocomplete="off">';
        $content .= templatereplace(file_get_contents("$thistpl/startgroup.pstpl"), array(), $redata);

        $question_template = file_get_contents("$thistpl/question.pstpl");
        // the following has been added for backwards compatiblity.
        if (substr_count($question_template, '{QUESTION_ESSENTIALS}') > 0)
        {
            // LS 1.87 and newer templates
            $content .= "\n" . templatereplace($question_template, array(), $redata, 'Unspecified', false, $qid) . "\n";
        }
        else
        {
            // LS 1.86 and older templates
            $content .= '<div ' . $question['essentials'] . ' class="' . $question['class'] . $question['man_class'] . '">';
            $content .= "\n" . templatereplace($question_template, array(), $redata, 'Unspecified', false, $qid) . "\n";
            $content .= "\n\t</div>\n";
        };

        $content .= templatereplace(file_get_contents("$thistpl/endgroup.pstpl"), array(), $redata) . $dummy_js;
        LimeExpressionManager::FinishProcessingGroup();
        $content .= LimeExpressionManager::GetRelevanceAndTailoringJavaScript();
        $content .= '<p>&nbsp;</form>';
        $content .= templatereplace(file_get_contents("$thistpl/endpage.pstpl"), array(), $redata);

        LimeExpressionManager::FinishProcessingPage();

        echo $content;

        if ($LEMdebugLevel >= 1) {
            echo LimeExpressionManager::GetDebugTimingMessage();
        }
        if ($LEMdebugLevel >= 2) {
             echo "<table><tr><td align='left'><b>Group/Question Validation Results:</b>".$moveResult['message']."</td></tr></table>\n";
        }

        echo "</html>\n";

        exit;
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'survey/Question', $aViewUrls = array(), $aData = array())
    {
        $this->getController()->_js_admin_includes(Yii::app()->baseUrl . '/scripts/jquery/jquery.dd.js');
        $this->getController()->_css_admin_includes(Yii::app()->baseUrl . '/scripts/jquery/dd.css');
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('styleurl') . "admin/".Yii::app()->getConfig('admintheme')."/superfish.css");

        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}
