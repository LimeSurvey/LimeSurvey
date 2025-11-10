<?php

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
 * printanswers
 *
 * @package LimeSurvey
 * @copyright 2011
 * @access public
 */
class PrintanswersController extends LSYii_Controller
{
    /* @var string : Default layout when using render : leave at bare actually : just send content */
    public $layout = 'survey';
    /* @var string the template name to be used when using layout */
    public $sTemplate;
    /* @var string[] Replacement data when use templatereplace function in layout, @see templatereplace $replacements */
    public $aReplacementData = array();
    /* @var array Global data when use templatereplace function  in layout, @see templatereplace $redata */
    public $aGlobalData = array();

    /**
     * printanswers::view()
     * View answers at the end of a survey in one place. To export as pdf, set 'usepdfexport' = 1 in lsconfig.php and $printableexport='pdf'.
     * @param mixed $surveyid
     * @param bool $printableexport
     * @return void
     */
    public function actionView($surveyid, $printableexport = false)
    {
        Yii::app()->loadHelper("frontend");
        Yii::import('application.libraries.admin.pdf');
        $survey = Survey::model()->findByPk($surveyid);
        $iSurveyID = $survey->sid;
        $sExportType = $printableexport;

        Yii::app()->loadHelper('database');

        if (isset($_SESSION['responses_' . $iSurveyID]['sid'])) {
            $iSurveyID = $_SESSION['responses_' . $iSurveyID]['sid'];
        } else {
            //die('Invalid survey/session');
        }
        // Get the survey inforamtion
        // Set the language for dispay
        if (isset($_SESSION['responses_' . $iSurveyID]['s_lang'])) {
            $sLanguage = $_SESSION['responses_' . $iSurveyID]['s_lang'];
        } elseif ($survey) {
            // survey exist
            {
            $sLanguage = $survey->language;
            }
        } else {
            $iSurveyID = 0;
            $sLanguage = Yii::app()->getConfig("defaultlang");
        }
        SetSurveyLanguage($iSurveyID, $sLanguage);
        Yii::import('application.helpers.SurveyRuntimeHelper');
        $SurveyRuntimeHelper = new SurveyRuntimeHelper();
        $SurveyRuntimeHelper->setJavascriptVar($iSurveyID);
        $aSurveyInfo = getSurveyInfo($iSurveyID, $sLanguage);
        $oTemplate = Template::model()->getInstance(null, $iSurveyID);
        /* Need a Template function to replace this line */
        //Yii::app()->clientScript->registerPackage( 'survey-template' );

        //Survey is not finished or don't exist
        if (!isset($_SESSION['responses_' . $iSurveyID]['srid'])) {
            //display "sorry but your session has expired"
            $this->sTemplate = $oTemplate->sTemplateName;
            $error = $this->renderPartial("/survey/system/errorWarning", array(
                'aErrors' => array(
                    gT("We are sorry but your session has expired."),
                ),
            ), true);
            $message = $this->renderPartial("/survey/system/message", array(
                'aMessage' => array(
                    gT("Either you have been inactive for too long, you have cookies disabled for your browser, or there were problems with your connection."),
                ),
            ), true);
            /* Set the data for templatereplace */
            $aReplacementData['title'] = 'session-timeout';
            $aReplacementData['message'] = $error . "<br/>" . $message;

            $aData = array();
            $aData['aSurveyInfo'] = getSurveyInfo($iSurveyID);
            $aData['aError'] = $aReplacementData;

            Yii::app()->twigRenderer->renderTemplateFromFile('layout_errors.twig', $aData, false);
            // $content=templatereplace(file_get_contents($oTemplate->pstplPath."message.pstpl"),$aReplacementData,$this->aGlobalData);
            // $this->render("/survey/system/display",array('content'=>$content));
            // App()->end();
        }
        //Fin session time out
        $sSRID = $_SESSION['responses_' . $iSurveyID]['srid']; //I want to see the answers with this id
        //Ensure script is not run directly, avoid path disclosure
        //if (!isset($rootdir) || isset($_REQUEST['$rootdir'])) {die( "browse - Cannot run this script directly");}

        //Ensure Participants printAnswer setting is set to true or that the logged user have read permissions over the responses.
        if ($aSurveyInfo['printanswers'] == 'N' && !Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'read')) {
            throw new CHttpException(401, gT('You are not allowed to print answers.'));
        }

        //CHECK IF SURVEY IS ACTIVATED AND EXISTS
        $sSurveyName = $aSurveyInfo['surveyls_title'];
        $sAnonymized = $aSurveyInfo['anonymized'];
        //OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.
        //SHOW HEADER
        $oResponseRow = SurveyDynamic::model($iSurveyID);
        $printanswershonorsconditions = Yii::app()->getConfig('printanswershonorsconditions');
        $groupArray = $oResponseRow->getPrintAnswersArray($sSRID, $sLanguage, $printanswershonorsconditions);

        // Remove all <script>...</script> content from result.
        Yii::import('application.helpers.viewHelper');
        foreach ($groupArray as &$group) {
            $group['description'] = viewHelper::purified($group['description']);
            foreach ($group['answerArray'] as &$answer) {
                $answer['question'] = viewHelper::purified($answer['question']);
            }
        }

        $aData['aSurveyInfo'] = $aSurveyInfo;
        $aData['aSurveyInfo']['dateFormat'] = getDateFormatData(Yii::app()->session['dateformat']);
        $aData['aSurveyInfo']['groupArray'] = $groupArray;
        $aData['aSurveyInfo']['printAnswersHeadFormUrl'] = Yii::App()->getController()->createUrl('printanswers/view/', array('surveyid' => $iSurveyID, 'printableexport' => 'pdf'));
        $aData['aSurveyInfo']['printAnswersHeadFormQueXMLUrl'] = Yii::App()->getController()->createUrl('printanswers/view/', array('surveyid' => $iSurveyID, 'printableexport' => 'quexmlpdf'));

        if (empty($sExportType)) {
            Yii::app()->setLanguage($sLanguage);
            $aData['aSurveyInfo']['include_content'] = 'printanswers';
            $aData['aSurveyInfo']['trackUrlPageName'] = 'printanswers';
            Yii::app()->twigRenderer->renderTemplateFromFile('layout_printanswers.twig', $aData, false);
        } elseif ($sExportType == 'pdf') {
            // Get images for TCPDF from template directory
            define('K_PATH_IMAGES', Template::getTemplatePath($aSurveyInfo['template']) . DIRECTORY_SEPARATOR);

            Yii::import('application.libraries.admin.pdf', true);
            Yii::import('application.helpers.pdfHelper');
            $aPdfLanguageSettings = pdfHelper::getPdfLanguageSettings(App()->language);

            $oPDF = new pdf();
            $oPDF->setCellMargins(1, 1, 1, 1);
            $oPDF->setCellPaddings(1, 1, 1, 1);
            $sDefaultHeaderString = $sSurveyName . " (" . gT("ID", 'unescaped') . ":" . $iSurveyID . ")";
            $oPDF->initAnswerPDF($aSurveyInfo, $aPdfLanguageSettings, Yii::app()->getConfig('sitename'), $sSurveyName, $sDefaultHeaderString);
            LimeExpressionManager::StartProcessingPage(true); // means that all variables are on the same page
            // Since all data are loaded, and don't need JavaScript, pretend all from Group 1
            LimeExpressionManager::StartProcessingGroup(1, ($aSurveyInfo['anonymized'] != "N"), $iSurveyID);
            $aData['aSurveyInfo']['printPdf'] = 1;
            $aData['aSurveyInfo']['include_content'] = 'printanswers';
            Yii::app()->clientScript->registerPackage($oTemplate->sPackageName);

            $html = Yii::app()->twigRenderer->renderTemplateFromFile('layout_printanswers.twig', $aData, true);
            //filter all scripts
            $html = preg_replace("/<script>[^<]*<\/script>/", '', (string) $html);
            //replace fontawesome icons
            $html = preg_replace('/(<i class="ri-checkbox-line"><\/i>|<i class="ri-close-fill"><\/i>)/', '[X]', $html);
            $html = preg_replace('/<i class="ri-checkbox-indeterminate-line">\<\/i>/', '[-]', $html);
            $html = preg_replace('/<i class="ri-checkbox-blank-line"><\/i>/', '[ ]', $html);
            $html = preg_replace('/<i class="ri-add-line"><\/i>/', '+', $html);
            $html = preg_replace('/<i class="ri-checkbox-blank-circle-fill"><\/i>/', '|', $html);
            $html = preg_replace('/<i class="ri-subtract-fill"><\/i>/', '-', $html);

            $oPDF->writeHTML($html, true, false, true, false, '');

            header("Cache-Control: must-revalidate, no-store, no-cache"); // Don't store in cache because it is sensitive data

            $sExportFileName = sanitize_filename($sSurveyName);
            $oPDF->write_out($sExportFileName . "-" . $iSurveyID . ".pdf");
            LimeExpressionManager::FinishProcessingGroup();
            LimeExpressionManager::FinishProcessingPage();
        } elseif ($sExportType == 'quexmlpdf') {
            Yii::import("application.libraries.admin.quexmlpdf", true);

            $quexmlpdf = new quexmlpdf();
            $quexmlpdf->applyGlobalSettings();

            // Setting the selected language for printout
            App()->setLanguage($sLanguage);

            $quexmlpdf->setLanguage($sLanguage);

            set_time_limit(120);

            Yii::app()->loadHelper('export');

            $quexml = quexml_export($iSurveyID, $sLanguage, $sSRID, true);

            $quexmlpdf->create($quexmlpdf->createqueXML($quexml));

            $sExportFileName = sanitize_filename($sSurveyName);
            $quexmlpdf->write_out($sExportFileName . "-" . $iSurveyID . "-queXML.pdf");
        }
    }
}
