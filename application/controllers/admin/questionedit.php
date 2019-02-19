<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
* question
*
* @package LimeSurvey
* @author
* @copyright 2011
* @access public
*/
class questionedit extends Survey_Common_Action
{
    public function view($surveyid, $gid, $qid=null)
    {
        $aData = array();
        $iSurveyID = (int) $surveyid;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $oQuestion = $this->_getQuestionObject($qid);
        $oTemplateConfiguration = TemplateConfiguration::getInstance($oSurvey->template, null, $iSurveyID);
        Yii::app()->getClientScript()->registerPackage('questioneditor');
        $qrrow = $oQuestion->attributes;
        $baselang = $oSurvey->language;
        $aAttributesWithValues = Question::model()->getAdvancedSettingsWithValues($oQuestion->qid, $qrrow['type'], $iSurveyID, $baselang);
        $DisplayArray = array();
        
        foreach ($aAttributesWithValues as $aAttribute) {
            if (($aAttribute['i18n'] == false && isset($aAttribute['value']) && $aAttribute['value'] != $aAttribute['default'])
                || ($aAttribute['i18n'] == true && isset($aAttribute['value'][$baselang]) && $aAttribute['value'][$baselang] != $aAttribute['default'])) {
                if ($aAttribute['inputtype'] == 'singleselect') {
                    if (isset($aAttribute['options'][$aAttribute['value']])) {
                        $aAttribute['value'] = $aAttribute['options'][$aAttribute['value']];
                    }
                }
                $DisplayArray[] = $aAttribute;
            }
        }
        
        $condarray = ($oQuestion->qid != null) ? getQuestDepsForConditions($iSurveyID, "all", "all", $oQuestion->qid, "by-targqid", "outsidegroup") : [];
        
  
        

        // Last question visited : By user (only one by user)
        $setting_entry = 'last_question_'.Yii::app()->user->getId();
        SettingGlobal::setSetting($setting_entry, $oQuestion->qid);
 
        // we need to set the sid for this question
        $setting_entry = 'last_question_sid_'.Yii::app()->user->getId();
        SettingGlobal::setSetting($setting_entry, $iSurveyID);
 
        // we need to set the gid for this question
        $setting_entry = 'last_question_gid_'.Yii::app()->user->getId();
        SettingGlobal::setSetting($setting_entry, $gid);
 
        // Last question for this survey (only one by survey, many by user)
        $setting_entry = 'last_question_'.Yii::app()->user->getId().'_'.$iSurveyID;
        SettingGlobal::setSetting($setting_entry, $oQuestion->qid);
 
        // we need to set the gid for this question
        $setting_entry = 'last_question_'.Yii::app()->user->getId().'_'.$iSurveyID.'_gid';
        SettingGlobal::setSetting($setting_entry, $gid);

        ///////////
        // combine aData
        $aData['surveyid'] = $iSurveyID;
        $aData['oSurvey'] = $oSurvey;
        $aData['gid'] = $gid;
        $aData['qid'] = $oQuestion->qid;
        //$aData['qct']
        //$aData['sqct']
        $aData['activated'] = $oSurvey->active;
        $aData['oQuestion'] = $oQuestion;
        $aData['languagelist'] = $oSurvey->allLanguages;
        $aData['qshowstyle'] = '';
        $aData['qrrow'] = $qrrow;
        $aData['baselang'] = $baselang;
        $aData['advancedsettings'] = $DisplayArray;
        $aData['sImageURL'] = Yii::app()->getConfig('adminimageurl');
        $aData['iIconSize'] = Yii::app()->getConfig('adminthemeiconsize');
        $aData['display']['menu_bars']['qid_action'] = 'editquestion';
        $aData['display']['menu_bars']['gid_action'] = 'viewquestion';
        $aData['action'] = 'editquestion';
        $aData['editing'] = true;

        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";
        $aData['surveyIsActive'] = $oSurvey->active !== 'N';
        $aData['activated'] = $oSurvey->active;
        $aData['jsData'] = [
            'surveyid' => $iSurveyID,
            'gid' => $gid,
            'qid' => $oQuestion->qid,
            'startType' => $oQuestion->type,
            'connectorBaseUrl' => $this->getController()->createUrl('admin/questioneditor/sid/'.$iSurveyID.'/gid/'.$gid.'/sa'),
            'i10N' => [
                'General Settings' => gT("General Settings"),
                'Code' => gT('Code'),
                'Question type' => gT('Question type'),
                'Question' => gT('Question'),
                'Help' => gT('Help'),
                'subquestions' => gT('Subquestions'),
                'answeroptions' => gT('Answer options'),
                'Quick add' => gT('Quick add'),
                'Predefined label sets' => gT('Predefined label sets'),
                'Save as label set' => gT('Save as label set'),
                'More languages' => gT('More languages'),
                'Add subquestion' => gT('Add subquestion'),
                'Reset' => gT('Reset'),
                'Save' => gT('Save'),
                'Some example subquestion' => gT('Some example subquestion'),
                'Delete' => gT('Delete'),
                'Open editor' => gT('Open editor'),
                'Duplicate' => gT('Duplicate'),
                'No preview available' => gT('No preview available')
            ]
        ];
        $aData['questiongroupbar']['importquestion'] = true;
        $aData['questiongroupbar']['savebutton']['form'] = 'frmeditgroup';
        $aData['questiongroupbar']['saveandclosebutton']['form'] = 'frmeditgroup';
        $aData['questiongroupbar']['closebutton']['url'] = '/admin/survey/sa/listquestions/surveyid/'.$iSurveyID; // Close button

        $this->_renderWrappedTemplate('survey/Question2', 'view', $aData);
    }


    /****
    **** A lot of getter function regarding functionalities and views.
    **** All called via ajax
    ****/

    public function getPossibleLanguages($iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        $aLanguages = Survey::model()->findByPk($iSurveyId)->allLanguages;
        $this->renderJSON($aLanguages);
    }

    public function saveQuestionData() 
    {
        $questionData = App()->request->getPost('questionData', []);

        
        $oQuestion = Question::model()->findByPk($questionData['question']['qid']);
        if($oQuestion != null) {
            $oQuestion = $this->_editQuestion($oQuestion, $questionData['question']);
        } else {
            $oQuestion = $this->_newQuestion($questionData['question']);
        }

        $questionData['advancedSettings'];
        $questionData['generalSettings'];
        $questionData['questionAttributes'];
        $questionData['questionI10N'];
        $questionData['scaledAnswerOptions'];
        $questionData['scaledSubquestions'];

        $this->renderJSON([
            'transfer' => $questionData,
        ]);
    }


    public function getQuestionData($iQuestionId=null, $type=null)
    {
        $iQuestionId = (int) $iQuestionId;
        $oQuestion = $this->_getQuestionObject($iQuestionId,$type);

        $aLanguages = [];
        $aAllLanguages = getLanguageData(false, Yii::app()->session['adminlang']);
        $aSurveyLanguages = $oQuestion->survey->getAllLanguages();
        array_walk($aSurveyLanguages, function ($lngString) use (&$aLanguages, $aAllLanguages) {
            $aLanguages[$lngString] = $aAllLanguages[$lngString]['description'];
        });
        $aQuestionDefinition = array_merge($oQuestion->attributes, ['typeInformation' => $oQuestion->questionType]);

        $aScaledSubquestions = $oQuestion->getOrderedSubQuestions();
        foreach($aScaledSubquestions as $scaleId => $aSubquestions) {
            $aScaledSubquestions[$scaleId] = array_map(function($oSubQuestion) { return array_merge($oSubQuestion->attributes, $oSubQuestion->questionL10ns);}, $aSubquestions);
        }

        $aScaledAnswerOptions = $oQuestion->getOrderedAnswers();
        foreach($aScaledAnswerOptions as $scaleId => $aAnswerOptions) {
            $aScaledAnswerOptions[$scaleId] = array_map(function($oAnswerOption) { return array_merge($oAnswerOption->attributes, $oAnswerOption->answerL10ns);}, $aAnswerOptions);
        }

        $this->renderJSON([
            'question' => $aQuestionDefinition,
            'i10n' => $oQuestion->questionL10ns,
            'subquestions' => $aScaledSubquestions,
            'answerOptions' => $aScaledAnswerOptions,
            'languages' => $aLanguages,
            'mainLanguage' => $oQuestion->survey->language
        ]);
    }

    public function getQuestionAttributeData($iQuestionId)
    {
        $iQuestionId = (int) $iQuestionId;
        $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($iQuestionId);
        $this->renderJSON($aQuestionAttributes);
    }

    public function getQuestionTypeList()
    {
        $this->renderJSON(QuestionType::modelsAttributes());
    }
    
    public function getGeneralOptions($iQuestionId, $sQuestionType=null)
    {
        $oQuestion = $this->_getQuestionObject($iQuestionId, $sQuestionType);
        $this->renderJSON($oQuestion->getDataSetObject()->getGeneralSettingsArray(null, $sQuestionType));
    }

    public function getAdvancedOptions($iQuestionId, $sQuestionType=null)
    {
        $oQuestion = $this->_getQuestionObject($iQuestionId, $sQuestionType);
        $this->renderJSON($oQuestion->getDataSetObject()->getAdvancedOptions(null, $sQuestionType));
    }

    /**
     * Live preview rendering
     *
     * @param int $iQuestionId
     * @param string $sLanguage
     * @param boolean $root
     *
     * @return void
     */
    public function getRenderedPreview($iQuestionId, $sLanguage, $root=false)
    {
        if($iQuestionId == null) {
            echo "<h3>No Preview available</h3>";
            return;
        }
        $root = (bool) $root;
        
        $changedText = App()->request->getPost('changedText', []);
        $changedType = App()->request->getPost('changedType', null );
        $oQuestion = Question::model()->findByPk($iQuestionId);
        
        $changedType = $changedType == null ? $oQuestion->type : $changedType;

        if ($changedText !== []) {
            Yii::app()->session['edit_'.$iQuestionId.'_changedText'] = $changedText;
        } else {
            $changedText = isset(Yii::app()->session['edit_'.$iQuestionId.'_changedText'])
                ? Yii::app()->session['edit_'.$iQuestionId.'_changedText']
                : [];
        }

        $aFieldArray = [
        //*  0 => string qid
            $oQuestion->qid,
        //*  1 => string sgqa | This should be working because it is only about parent questions here!
            "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}",
        //*  2 => string questioncode
            $oQuestion->title,
        //*  3 => string question | technically never used in the new renderers and totally unessecary therefor empty
            "",
        //*  4 => string type
            $oQuestion->type,
        //*  5 => string gid
            $oQuestion->gid,
        //*  6 => string mandatory,
            ($oQuestion->mandatory == 'Y'),
        ];
        Yii::import('application.helpers.qanda_helper', true);
        setNoAnswerMode(['shownoanswer' => $oQuestion->survey->shownoanswer ]);
    
        $oQuestionRenderer = $oQuestion->getRenderererObject($aFieldArray, $changedType);
        $aRendered =  $oQuestionRenderer->render('applyCkToFields');
        $aSurveyInfo = $oQuestion->survey->attributes;
        $aQuestion = array_merge(
            $oQuestion->attributes,
            QuestionAttribute::model()->getQuestionAttributes($iQuestionId),
            ['answer' => $aRendered[0]],
            [
                'number' => $oQuestion->question_order,
                'code' => $oQuestion->title,
                'text' => isset($changedText['question']) ? $changedText['question'] : $oQuestion->questionL10ns[$sLanguage]->question,
                'help' => [
                    'show' => true,
                    'text' => (isset($changedText['help']) ? $changedText['help'] : $oQuestion->questionL10ns[$sLanguage]->help)
                ],
            ]
        );
        $oTemplate = Template::model()->getInstance($oQuestion->survey->template);
        Yii::app()->twigRenderer->renderTemplateForQuestionEditPreview(
            '/subviews/survey/question_container.twig',
            ['aSurveyInfo' => $aSurveyInfo, 'aQuestion' => $aQuestion, 'session' => $_SESSION],
            $root
        );
        
        
        return;
    }

    private function _getQuestionObject($iQuestionId=null, $sQuestionType=null )
    {
        $iSurveyId = Yii::app()->request->getParam('sid') ?? Yii::app()->request->getParam('surveyid');
        $oQuestion =  Question::model()->findByPk($iQuestionId);
        if($oQuestion == null) {
            $oQuestion = QuestionCreate::getInstance($iSurveyId, $sQuestionType);
        } 
        if($sQuestionType != null) {
            $oQuestion->type = $sQuestionType;
        }

        return $oQuestion;

    }

    /**
     * Method to store and filter questionData for a new question
     */
    private function _newQuestion($aQuestionData = null) {

        $iSurveyId = Yii::app()->request->getParam('sid') ?? Yii::app()->request->getParam('surveyid');
        
        $aQuestionData = array_merge([
                'sid' => $iSurveyId,
                'gid' => Yii::app()->request->getParam('gid'),
                'type' => SettingsUser::getUserSettingValue('preselectquestiontype', null, null, null, Yii::app()->getConfig('preselectquestiontype')),
                'other' => 'N',
                'mandatory' => 'N',
                'relevance' => 1,
                'group_name' => '',
                'modulename' => '',
                'title' => $temporaryTitle,
                'question_order' => 9999,
        ],$aQuestionData);

        $oQuestion = new Question();
        $oQuestion->setAttributes($aQuestionData, false);
        if ($oQuestion == null) {
            throw new CException("Object creation failed, input array malformed or invalid");
        }

        $saved = $oQuestion->save();
        if ($saved == false) {
            throw new CException("Object creation failed, couldn't save. ERRORS:".print_r($oQuestion->getErrors(), true));
        }
        
        $i10N = [];
        foreach ($oQuestion->survey->allLanguages as $sLanguage) {
            $i10N[$sLanguage] = new QuestionL10n();
            $i10N[$sLanguage]->setAttributes([
                'qid' => $oQuestion->qid,
                'language' => $sLanguage,
                'question' => '',
                'help' => '',
            ], false);
            $i10N[$sLanguage]->save();
        }
        
        return $oQuestion;
    }

    /**
     * Method to store and filter questionData for editing a question
     */
    private function _editQuestion($oQuestion, $aQuestionData) {
        $aOldQuestionData = $oQuestion->attributes;
    }


    /******************************/
    
    /**
     * Method to render an array as a json document
     *
     * @param array $aData
     * @return void
     */
    protected function renderJSON($aData)
    {
        if (Yii::app()->getConfig('debug') > 0) {
            $aData['debug'] = [$_POST, $_GET];
        }

        echo Yii::app()->getController()->renderPartial('/admin/super/_renderJson', ['data' => $aData], true, false);
        return;
    }
    
    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'survey/Question2', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
