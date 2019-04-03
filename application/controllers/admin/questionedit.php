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
        
        $this->getController()->renderPartial('/admin/survey/Question/questionbar_view', $aData, true);
        $aData['display']['menu_bars']['gid_action'] = 'viewquestion';
        $aData['questionbar']['buttons']['view'] = true;
        
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
                'Create new Question' => gT('Create new Question'),
                'General Settings' => gT("General Settings"),
                'Code' => gT('Code'),
                'Text elements' => gT('Text elements'),
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
                'No preview available' => gT('No preview available'),
                'Editor' => gT('Editor'),
                'Quick edit' => gT('Quick edit'),
                'Cancel' => gT('Cancel'),
                'Replace' => gT('Replace'),
                'Add' => gT('Add'),
                'Select delimiter' => gT('Select delimiter'),
                'Semicolon' => gT('Semicolon'),
                'Comma' => gT('Comma'),
                'Tab' => gT('Tab'),
                'New rows' => gT('New rows'),
                'Scale' => gT('Scale'),
                'Save and Close' => gT('Save and Close')
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

    public function saveQuestionData($sid)
    {
        $questionData = App()->request->getPost('questionData', []);
        $iSurveyId = (int) $sid;
        
        $oQuestion = Question::model()->findByPk($questionData['question']['qid']);
        if ($oQuestion != null) {
            $oQuestion = $this->_editQuestion($oQuestion, $questionData['question']);
        } else {
            $oQuestion = $this->_newQuestion($questionData['question']);
        }
        //$questionData['questionAttributes'];
        $setApplied = [];
        $setApplied['generalSettings']     = $this->_unparseAndSetGeneralOptions($oQuestion, $questionData['generalSettings']);
        $setApplied['advancedSettings']    = $this->_unparseAndSetAdvancedOptions($oQuestion, $questionData['advancedSettings']);
        $setApplied['questionI10N']        = $this->_applyI10N($oQuestion, $questionData['questionI10N']);
        
        if(isset($questionData['scaledSubquestions'])) {
            $setApplied['scaledSubquestions']  = $this->_storeSubquestions($oQuestion, $questionData['scaledSubquestions']);
        }

        if(isset($questionData['scaledAnswerOptions'])) {
            $setApplied['scaledAnswerOptions'] = $this->_storeAnswerOptions($oQuestion, $questionData['scaledAnswerOptions']);
        }
 
        $aCompiledQuestionData = $this->_getCompiledQuestionData($oQuestion);
        $aQuestionAttributeData = $this->getQuestionAttributeData($oQuestion->qid, true);
        $aQuestionGeneralOptions = $this->getGeneralOptions($oQuestion->qid, null, true);
        $aAdvancedOptions = $this->getAdvancedOptions($oQuestion->qid, null, true);

        $this->renderJSON([
            'success' => array_reduce($setApplied, function($coll, $it){ return $coll && $it; }, true),
            'message' => gT('Question successfully stored'),
            'successDetail' => $setApplied,
            'questionId' => $oQuestion->qid,
            'redirect' => $this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$iSurveyId),
            'newQuestionDetails' => [
                "question" => $aCompiledQuestionData['question'],
                "scaledSubquestions" => $aCompiledQuestionData['subquestions'],
                "scaledAnswerOptions" => $aCompiledQuestionData['answerOptions'],
                "questionI10N" => $aCompiledQuestionData['i10n'],
                "questionAttributes" => $aQuestionAttributeData,
                "generalSettings" => $aQuestionGeneralOptions,
                "advancedSettings" => $aAdvancedOptions
            ],
            'transfer' => $questionData,
        ]);
        Yii::app()->close();
    }


    public function getQuestionData($iQuestionId=null, $type=null)
    {
        $iQuestionId = (int) $iQuestionId;
        $oQuestion = $this->_getQuestionObject($iQuestionId, $type);

        $aQuestionInformationObject = $this->_getCompiledQuestionData($oQuestion);

        $aLanguages = [];
        $aAllLanguages = getLanguageData(false, Yii::app()->session['adminlang']);
        $aSurveyLanguages = $oQuestion->survey->getAllLanguages();
        array_walk($aSurveyLanguages, function ($lngString) use (&$aLanguages, $aAllLanguages) {
            $aLanguages[$lngString] = $aAllLanguages[$lngString]['description'];
        });

        $this->renderJSON(array_merge($aQuestionInformationObject, [
            'languages' => $aLanguages,
            'mainLanguage' => $oQuestion->survey->language
        ]));
    }

    public function getQuestionAttributeData($iQuestionId, $returnArray = false)
    {
        $iQuestionId = (int) $iQuestionId;
        $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($iQuestionId);
        if( $returnArray === true ) {
            return $aQuestionAttributes;
        }
        $this->renderJSON($aQuestionAttributes);
    }

    public function getQuestionTypeList()
    {
        $this->renderJSON(QuestionType::modelsAttributes());
    }
    
    public function getQuestionTypeInformation($sQuestionType) {
        $aTypeInformations = QuestionType::modelsAttributes();
        $aQuestionTypeInformation = $aTypeInformations[$sQuestionType];

        $this->renderJSON($aQuestionTypeInformation);

    }
    
    public function getGeneralOptions($iQuestionId, $sQuestionType=null, $returnArray = false)
    {
        $oQuestion = $this->_getQuestionObject($iQuestionId, $sQuestionType);
        $aGeneralOptionsArray = $oQuestion->getDataSetObject()->getGeneralSettingsArray($oQuestion->qid, $sQuestionType);

        if( $returnArray === true ) {
            return $aGeneralOptionsArray;
        }

        $this->renderJSON($aGeneralOptionsArray);
    }

    public function getAdvancedOptions($iQuestionId, $sQuestionType=null, $returnArray = false)
    {
        $oQuestion = $this->_getQuestionObject($iQuestionId, $sQuestionType);
        $aAdvancedOptionsArray = $oQuestion->getDataSetObject()->getAdvancedOptions($oQuestion->qid, $sQuestionType);
        if( $returnArray === true ) {
            return $aAdvancedOptionsArray;
        }
        $this->renderJSON($aAdvancedOptionsArray);
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
        if ($iQuestionId == null) {
            echo "<h3>No Preview available</h3>";
            return;
        }
        $root = (bool) $root;
        
        $changedText = App()->request->getPost('changedText', []);
        $changedType = App()->request->getPost('changedType', null);
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

    private function _getQuestionObject($iQuestionId=null, $sQuestionType=null)
    {
        $iSurveyId = Yii::app()->request->getParam('sid') ?? Yii::app()->request->getParam('surveyid');
        $oQuestion =  Question::model()->findByPk($iQuestionId);
        if ($oQuestion == null) {
            $oQuestion = QuestionCreate::getInstance($iSurveyId, $sQuestionType);
        }
        if ($sQuestionType != null) {
            $oQuestion->type = $sQuestionType;
        }

        return $oQuestion;
    }

    /**
     * Method to store and filter questionData for a new question
     */
    private function _newQuestion($aQuestionData = null, $subquestion = false)
    {
        $iSurveyId = Yii::app()->request->getParam('sid') ?? Yii::app()->request->getParam('surveyid');
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        $iQuestionGroupId = Yii::app()->request->getParam('gid');

        $aQuestionData = array_merge([
                'sid' => $iSurveyId,
                'gid' => Yii::app()->request->getParam('gid'),
                'type' => SettingsUser::getUserSettingValue('preselectquestiontype', null, null, null, Yii::app()->getConfig('preselectquestiontype')),
                'other' => 'N',
                'mandatory' => 'N',
                'relevance' => 1,
                'group_name' => '',
                'modulename' => '',
        ], $aQuestionData);
        unset($aQuestionData['qid']);
        
        if($subquestion) {
            foreach ($oSurvey->allLanguages as $sLanguage){
                unset($aQuestionData[$sLanguage]);
            }
        } else {
            $aQuestionData['question_order'] = getMaxQuestionOrder($iQuestionGroupId);
        }
        
        $oQuestion = new Question();
        $oQuestion->setAttributes($aQuestionData, false);
        if ($oQuestion == null) {
            throw new CException("Object creation failed, input array malformed or invalid");
        }

        $saved = $oQuestion->save();
        if ($saved == false) {
            throw new CException("Object creation failed, couldn't save.\n ERRORS:".print_r($oQuestion->getErrors(), true));
        }
        
        $i10N = [];
        foreach ($oSurvey->allLanguages as $sLanguage) {
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
    private function _editQuestion(&$oQuestion, $aQuestionData)
    {
        $aOldQuestionData = $oQuestion->attributes;
        $oQuestion->setAttributes($aQuestionData, false);
        if ($oQuestion == null) {
            throw new CException("Object update failed, input array malformed or invalid");
        }

        $saved = $oQuestion->save();
        if ($saved == false) {
            throw new CException("Object update failed, couldn't save. ERRORS:".print_r($oQuestion->getErrors(), true));
        }
        return $oQuestion;
    }


    private function _unparseAndSetGeneralOptions(&$oQuestion, $dataSet)
    {
        $storeValid = true;
        $aQuestionBaseAttributes = $oQuestion->attributes;
        $aQuestionAttributes = $oQuestion->questionAttributes;

        foreach($dataSet as $sAttributeKey => $aAttributeValueArray) {
            if($sAttributeKey === 'debug') continue;
            if(array_key_exists($sAttributeKey, $aQuestionBaseAttributes)) {
                $oQuestion->$sAttributeKey = $aAttributeValueArray['formElementValue'];
            } else { 
                $storeValid = $storeValid && QuestionAttribute::model()->setQuestionAttribute($oQuestion->qid,$sAttributeKey,$aAttributeValueArray['formElementValue']);
            }
        }

        $storeValid = $storeValid && $oQuestion->save();
        
        return $storeValid;
    }

    private function _unparseAndSetAdvancedOptions(&$oQuestion, $dataSet)
    {
        $storeValid = true;
        $aQuestionAttributes = $oQuestion->questionAttributes;
        $aQuestionBaseAttributes = $oQuestion->attributes;

        foreach($dataSet as $sAttributeCategory => $aAttributeCategorySettings) {
            if($sAttributeCategory === 'debug') continue;
            foreach($aAttributeCategorySettings as $sAttributeKey => $aAttributeValueArray) {
                $newValue = is_array($aAttributeValueArray['formElementValue']) 
                    ? $aAttributeValueArray['formElementValue'][$oQuestion->survey->language] 
                    : $aAttributeValueArray['formElementValue'];
                
                if(array_key_exists($sAttributeKey, $aQuestionBaseAttributes)) {
                    $oQuestion->$sAttributeKey = $newValue;
                } else { 
                    $storeValid = $storeValid && QuestionAttribute::model()->setQuestionAttribute($oQuestion->qid,$sAttributeKey,$newValue);
                }
            }
        }

        $storeValid = $storeValid && $oQuestion->save();

        return $storeValid;
    }

    private function _applyI10N(&$oQuestion, $dataSet)
    {
        $storeValid = true;

        foreach ($dataSet as $sLanguage => $aI10NBlock) {
            $i10N = QuestionL10n::model()->findByAttributes(['qid' => $oQuestion->qid,'language' => $sLanguage]);
            $i10N->setAttributes([
                'question' => $aI10NBlock['question'],
                'help' => $aI10NBlock['help'],
            ], false);
            $storeValid = $storeValid && $i10N->save();
        }

        return $storeValid;
    }

    private function _applyI10NSubquestion($oQuestion, $dataSet)
    {
        $storeValid = true;
        
        foreach ($oQuestion->survey->allLanguages as $sLanguage) {
            $aI10NBlock = $dataSet[$sLanguage];
            $i10N = QuestionL10n::model()->findByAttributes(['qid' => $oQuestion->qid,'language' => $sLanguage]);
            $i10N->setAttributes([
                'question' => $aI10NBlock['question'],
                'help' => $aI10NBlock['help'],
            ], false);
            $storeValid = $storeValid && $i10N->save();
        }

        return $storeValid;
    }

    private function _applyAnswerI10N($oAnswer, $oQuestion, $dataSet)
    {
        $storeValid = true;

        foreach ($oQuestion->survey->allLanguages as $sLanguage) {
            $i10N = AnswerL10n::model()->findByAttributes(['aid' => $oAnswer->aid,'language' => $sLanguage]);
            if($i10N == null) {
                $i10N = new AnswerL10n();
                $i10N->setAttributes([
                    'aid' => $oAnswer->aid,
                    'language' => $sLanguage
                ], false);
            }
            $i10N->setAttributes([
                'answer' => $dataSet[$sLanguage]['answer'],
            ], false);
            $storeValid = $storeValid && $i10N->save();
        }

        return $storeValid;
    }

    private function _storeSubquestions(&$oQuestion, $dataSet)
    {
        $storeValid = true;
        foreach($dataSet as $scaleId => $aSubquestions) {
            foreach($aSubquestions as $aSubquestionDataSet) {   
                $oSubQuestion = Question::model()->findByPk($aSubquestionDataSet['qid']);
                if ($oSubQuestion != null) {
                    $oSubQuestion = $this->_editQuestion($oSubQuestion, $aSubquestionDataSet);
                } else {
                    $aSubquestionDataSet['parent_qid'] = $oQuestion->qid;
                    $oSubQuestion = $this->_newQuestion($aSubquestionDataSet, true);
                }
                $storeValid = $storeValid && $this->_applyI10NSubquestion($oSubQuestion, $aSubquestionDataSet);
            }
        }
        return $storeValid;
    }

    private function _storeAnswerOptions(&$oQuestion, $dataSet)
    {
        $storeValid = true;
        foreach($dataSet as $scaleId => $aAnswerOptions) {
            foreach ($aAnswerOptions as $aAnswerOptionDataSet) {

                $oAnswer = Answer::model()->findByPk($aAnswerOptionDataSet['aid']);
                if($oAnswer == null) {
                    $oAnswer = new Answer();
                    unset($aAnswerOptionDataSet['aid']);
                }
                $oAnswer->setAttributes($aAnswerOptionDataSet, false);
                $answerSaved = $oAnswer->save();
                if(!$answerSaved) {
                    throw new CException("Answer option couldn't be saved. Error: ".print_r($oAnswer->getErrors(),true));
                }
                $storeValid = $storeValid && $this->_applyAnswerI10N($oAnswer, $oQuestion, $aAnswerOptionDataSet);
            }
        }
        return $storeValid;
    }

    private function _getCompiledQuestionData(&$oQuestion) {
      
        $aQuestionDefinition = array_merge($oQuestion->attributes, ['typeInformation' => $oQuestion->questionType]);
        $oQuestionGroup = QuestionGroup::model()->findByPk($oQuestion->gid);
        $aQuestionGroupDefinition = array_merge($oQuestionGroup->attributes, $oQuestionGroup->questionGroupL10ns);


        $aScaledSubquestions = $oQuestion->getOrderedSubQuestions();
        foreach ($aScaledSubquestions as $scaleId => $aSubquestions) {
            $aScaledSubquestions[$scaleId] = array_map(function ($oSubQuestion) {
                return array_merge($oSubQuestion->attributes, $oSubQuestion->questionL10ns);
            }, $aSubquestions);
        }

        $aScaledAnswerOptions = $oQuestion->getOrderedAnswers();
        foreach ($aScaledAnswerOptions as $scaleId => $aAnswerOptions) {
            $aScaledAnswerOptions[$scaleId] = array_map(function ($oAnswerOption) {
                return array_merge($oAnswerOption->attributes, $oAnswerOption->answerL10ns);
            }, $aAnswerOptions);
        }

        return [
            'question' => $aQuestionDefinition,
            'questiongroup' => $aQuestionGroupDefinition,
            'i10n' => $oQuestion->questionL10ns,
            'subquestions' => $aScaledSubquestions,
            'answerOptions' => $aScaledAnswerOptions,
        ];
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
