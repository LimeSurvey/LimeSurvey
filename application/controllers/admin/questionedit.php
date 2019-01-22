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

use \LimeSurvey\Helpers\questionHelper;

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
    public function view($surveyid, $gid, $qid)
    {
        $aData = array();
        $aData['surveyid'] = $iSurveyID = (int) $surveyid;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $oQuestion = Question::model()->findByPk($qid);
        $oTemplateConfiguration = TemplateConfiguration::getInstance($oSurvey->template, null, $iSurveyID);
        Yii::app()->getClientScript()->registerPackage('questioneditor');
        
        $qrrow = $oQuestion->attributes;
        $baselang = $oQuestion->survey->language;
        $aData['oQuestion'] = $oQuestion;
        
        $qrrow = $oQuestion->attributes;
        $aData['languagelist'] = $oQuestion->survey->allLanguages;
        $aData['activated'] = $oQuestion->survey->active;
        $aData['qtypes'] = Question::typeList();

        $aAttributesWithValues = Question::model()->getAdvancedSettingsWithValues($qid, $qrrow['type'], $iSurveyID, $baselang);
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
        $aData['advancedsettings'] = $DisplayArray;
        
        $aData['surveyid'] = $iSurveyID;
        $aData['gid'] = $gid;
        $aData['qid'] = $qid;

        $aData['sImageURL'] = Yii::app()->getConfig('adminimageurl');
        $aData['iIconSize'] = Yii::app()->getConfig('adminthemeiconsize');
        $aData['jsData'] = [
            'surveyid' => $iSurveyID,
            'gid' => $gid,
            'qid' => $qid,
            'connectorBaseUrl' => $this->getController()->createUrl('admin/questioneditor/sa/'),
            'i10N' => []
        ];
        $aData['qrrow'] = $qrrow;
        $this->getController()->renderPartial('/admin/survey/Question/questionbar_view', $aData, true);
        $aData['display']['menu_bars']['gid_action'] = 'viewquestion';
        $aData['questionbar']['buttons']['view'] = true;
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

    public function getQuestionData($iQuestionId)
    {
        $iQuestionId = (int) $iQuestionId;
        $oQuestion = Question::model()->findByPk($iQuestionId);
        $aLanguages = [];
        $aAllLanguages = getLanguageData(false, Yii::app()->session['adminlang']);
        $aSurveyLanguages = $oQuestion->survey->getAllLanguages();
        array_walk($aSurveyLanguages, function ($lngString) use (&$aLanguages, $aAllLanguages) {
            $aLanguages[$lngString] = $aAllLanguages[$lngString]['description'];
        });
        $this->renderJSON([
            'question' => $oQuestion, 
            'i10n' => $oQuestion->questionL10ns, 
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
        $oQuestion = Question::model()->findByPk($iQuestionId);
        $this->renderJSON($oQuestion->getDataSetObject()->getGeneralSettingsArray(null, $sQuestionType));
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
        $root = (bool) $root;
        $oQuestion = Question::model()->findByPk($iQuestionId);

        $changedText = App()->request->getPost('changedText', []);
        $changedType = App()->request->getPost('changedType', $oQuestion->type);

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
