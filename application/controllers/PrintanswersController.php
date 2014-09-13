<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
    class PrintanswersController extends LSYii_Controller {



        /**
        * printanswers::view()
        * View answers at the end of a survey in one place. To export as pdf, set 'usepdfexport' = 1 in lsconfig.php and $printableexport='pdf'.
        * @param mixed $surveyid
        * @param bool $printableexport
        * @return
        */
        function actionView($surveyid,$printableexport=FALSE)
        {
            Yii::app()->loadHelper("frontend");
            Yii::import('application.libraries.admin.pdf');

            $iSurveyID = (int)$surveyid;
            $sExportType = $printableexport;

            Yii::app()->loadHelper('database');

            if (isset($_SESSION['survey_'.$iSurveyID]['sid']))
            {
                $iSurveyID = $_SESSION['survey_'.$iSurveyID]['sid'];
            }
            else
            {
                //die('Invalid survey/session');
            }
            // Get the survey inforamtion
            // Set the language for dispay
            if (isset($_SESSION['survey_'.$iSurveyID]['s_lang']))
            {
                $sLanguage = $_SESSION['survey_'.$iSurveyID]['s_lang'];
            }
            elseif(Survey::model()->findByPk($iSurveyID))// survey exist
            {
                $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
            }
            else
            {
                $iSurveyID=0;
                $sLanguage = Yii::app()->getConfig("defaultlang");
            }
            $clang = SetSurveyLanguage($iSurveyID, $sLanguage);
            $aSurveyInfo = getSurveyInfo($iSurveyID,$sLanguage);
            //SET THE TEMPLATE DIRECTORY
            if (!isset($aSurveyInfo['templatedir']) || !$aSurveyInfo['templatedir'])
            {
                $aSurveyInfo['templatedir']=Yii::app()->getConfig('defaulttemplate');
            }
            $sTemplate = validateTemplateDir($aSurveyInfo['templatedir']);
            //Survey is not finished or don't exist
            if (!isset($_SESSION['survey_'.$iSurveyID]['finished']) || !isset($_SESSION['survey_'.$iSurveyID]['srid']))
            //display "sorry but your session has expired"
            {
                sendCacheHeaders();
                doHeader();
                echo templatereplace(file_get_contents(getTemplatePath($sTemplate).'/startpage.pstpl'),array());
                echo "<center><br />\n"
                ."\t<font color='RED'><strong>".$clang->gT("Error")."</strong></font><br />\n"
                ."\t".$clang->gT("We are sorry but your session has expired.")."<br />".$clang->gT("Either you have been inactive for too long, you have cookies disabled for your browser, or there were problems with your connection.")."<br />\n"
                ."\t".sprintf($clang->gT("Please contact %s ( %s ) for further assistance."), Yii::app()->getConfig("siteadminname"), Yii::app()->getConfig("siteadminemail"))."\n"
                ."</center><br />\n";
                echo templatereplace(file_get_contents(getTemplatePath($sTemplate).'/endpage.pstpl'),array());
                doFooter();
                exit;
            }
            //Fin session time out
            $sSRID = $_SESSION['survey_'.$iSurveyID]['srid']; //I want to see the answers with this id
            //Ensure script is not run directly, avoid path disclosure
            //if (!isset($rootdir) || isset($_REQUEST['$rootdir'])) {die( "browse - Cannot run this script directly");}
            if ($aSurveyInfo['printanswers'] == 'N')
            {
                die();  //Die quietly if print answers is not permitted
            }
            //CHECK IF SURVEY IS ACTIVATED AND EXISTS
            $sSurveyName = $aSurveyInfo['surveyls_title'];
            $sAnonymized = $aSurveyInfo['anonymized'];
            //OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.
            //SHOW HEADER
            $sOutput = CHtml::form(array("printanswers/view/surveyid/{$iSurveyID}/printableexport/pdf"), 'post')
            ."<center><input type='submit' value='".$clang->gT("PDF export")."'id=\"exportbutton\"/><input type='hidden' name='printableexport' /></center></form>";
            if($sExportType == 'pdf')
            {
                //require (Yii::app()->getConfig('rootdir').'/application/config/tcpdf.php');
                Yii::import('application.libraries.admin.pdf', true);
                Yii::import('application.helpers.pdfHelper');
                $aPdfLanguageSettings=pdfHelper::getPdfLanguageSettings($clang->langcode);
                $oPDF = new pdf();
                $oPDF->SetTitle($clang->gT("Survey name (ID)",'unescaped').": {$sSurveyName} ({$iSurveyID})");
                $oPDF->SetSubject($sSurveyName);
                $oPDF->SetDisplayMode('fullpage', 'two');
                $oPDF->setLanguageArray($aPdfLanguageSettings['lg']);
                $oPDF->setHeaderFont(Array($aPdfLanguageSettings['pdffont'], '', PDF_FONT_SIZE_MAIN));
                $oPDF->setFooterFont(Array($aPdfLanguageSettings['pdffont'], '', PDF_FONT_SIZE_DATA));
                $oPDF->SetFont($aPdfLanguageSettings['pdffont'], '', $aPdfLanguageSettings['pdffontsize']);
                $oPDF->AddPage();
                $oPDF->titleintopdf($clang->gT("Survey name (ID)",'unescaped').": {$sSurveyName} ({$iSurveyID})");
            }
            $sOutput .= "\t<div class='printouttitle'><strong>".$clang->gT("Survey name (ID):")."</strong> $sSurveyName ($iSurveyID)</div><p>&nbsp;\n";
            LimeExpressionManager::StartProcessingPage(true);  // means that all variables are on the same page
            // Since all data are loaded, and don't need JavaScript, pretend all from Group 1
            LimeExpressionManager::StartProcessingGroup(1,($aSurveyInfo['anonymized']!="N"),$iSurveyID);
            $printanswershonorsconditions = Yii::app()->getConfig('printanswershonorsconditions');
            $aFullResponseTable = getFullResponseTable($iSurveyID,$sSRID,$sLanguage,$printanswershonorsconditions);
            //Get the fieldmap @TODO: do we need to filter out some fields?
            if($aSurveyInfo['datestamp']!="Y" || $sAnonymized == 'Y'){
                unset ($aFullResponseTable['submitdate']);
            }else{
                unset ($aFullResponseTable['id']);
            }
            unset ($aFullResponseTable['token']);
            unset ($aFullResponseTable['lastpage']);
            unset ($aFullResponseTable['startlanguage']);
            unset ($aFullResponseTable['datestamp']);
            unset ($aFullResponseTable['startdate']);
            $sOutput .= "<table class='printouttable' >\n";
            foreach ($aFullResponseTable as $sFieldname=>$fname)
            {
                if (substr($sFieldname,0,4) == 'gid_')
                {
                        $sOutput .= "\t<tr class='printanswersgroup'><td colspan='2'>{$fname[0]}</td></tr>\n";
                }
                elseif (substr($sFieldname,0,4)=='qid_')
                {
                        $sOutput .= "\t<tr class='printanswersquestionhead'><td colspan='2'>{$fname[0]}</td></tr>\n";
                }
                elseif ($sFieldname=='submitdate')
                {
                    if($sAnonymized != 'Y')
                    {
                            $sOutput .= "\t<tr class='printanswersquestion'><td>{$fname[0]} {$fname[1]} {$sFieldname}</td><td class='printanswersanswertext'>{$fname[2]}</td></tr>";
                    }
                }
                else
                {
                       $sOutput .= "\t<tr class='printanswersquestion'><td>{$fname[0]} {$fname[1]}</td><td class='printanswersanswertext'>".flattenText($fname[2])."</td></tr>";
                }
            }
            $sOutput .= "</table>\n";
            $sData['thissurvey']=$aSurveyInfo;
            $sOutput=templatereplace($sOutput, array() , $sData, '', $aSurveyInfo['anonymized']=="Y",NULL, array(), true);// Do a static replacement
            if($sExportType == 'pdf')
            {
                $oPDF->writeHTML($sOutput);
                header("Pragma: public");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                $sExportFileName = sanitize_filename($sSurveyName);
                $oPDF->Output($sExportFileName."-".$iSurveyID.".pdf","D");
            }
            else//Display the page with user answers
            {
                ob_start(function($buffer, $phase) {
                    App()->getClientScript()->render($buffer);
                    App()->getClientScript()->reset();
                    return $buffer;
                });
                ob_implicit_flush(false);
                
                sendCacheHeaders();
                doHeader();
                echo templatereplace(file_get_contents(getTemplatePath($sTemplate).'/startpage.pstpl'),array(),$sData);
                echo templatereplace(file_get_contents(getTemplatePath($sTemplate).'/printanswers.pstpl'),array('ANSWERTABLE'=>$sOutput),$sData);
                echo templatereplace(file_get_contents(getTemplatePath($sTemplate).'/endpage.pstpl'),array(),$sData);
                echo "</body></html>";
                
                ob_flush();
            }

            LimeExpressionManager::FinishProcessingGroup();
            LimeExpressionManager::FinishProcessingPage();
        }
    }
