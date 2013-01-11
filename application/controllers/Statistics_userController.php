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
 *  $Id$
 */

/*
 * Created 12-2008 by Maziminke (maziminke@web.de)
 *
 * This file handles the "Show results to users" option:
 * Survey Settings -> Presentation & navigation -> "Public statistics?"
 *
 * The admin can set a question attribute "public_statistics" for each question
 * to determine whether the results of a certain question should be shown to the user
 * after he/she has submitted the survey.
 *
 * See http://docs.limesurvey.org/tiki-index.php?page=Question+attributes#public_statistics
 */

class Statistics_userController extends LSYii_Controller {

    /**
    * put your comment there...
    * 
    * @param integer $surveyid
    * @param mixed $language
    */
    function actionAction($surveyid,$sLanguage)
    {
        $iSurveyID=(int)$surveyid;
        //$postlang = returnglobal('lang');
        Yii::import('application.libraries.admin.progressbar',true);
        Yii::app()->loadHelper("admin/statistics");
        Yii::app()->loadHelper('frontend');
        Yii::app()->loadHelper('database');
        Yii::app()->loadHelper('surveytranslator');

        $aData = array();

        /*
         * List of important settings:
         * - publicstatistics: General survey setting which determines if public statistics for this survey
         *   should be shown at all.
         *
         * - publicgraphs: General survey setting which determines if public statistics for this survey
         *   should include graphs or only show a tabular overview.
         *
         * - public_statistics: Question attribute which has to be applied to each question so that
         *   its statistics will be shown to the user. If not set no statistics for this question will be shown.
         *
         * - filterout_incomplete_answers: Setting taken from config-defaults.php which determines if
         *   not completed answers will be filtered.
         */

        if(!isset($iSurveyID))
        {
            $iSurveyID=returnGlobal('sid');
        }
        else
        {
            $iSurveyID = (int) $iSurveyID;
        }
        if (!$iSurveyID)
        {
            //This next line ensures that the $iSurveyID value is never anything but a number.
            safeDie('You have to provide a valid survey ID.');
        }


        if ($iSurveyID)
        {
            $sResult = Survey::model()->count('sid = :sid AND active = :active', array(':sid' => $iSurveyID, ':active' => 'Y'));      //Checked
            if ($sResult == 0)
            {
                safeDie('You have to provide a valid survey ID.');
            }
            else
            {
                $aSurveyInfo = getSurveyInfo($iSurveyID);
                // CHANGE JSW_NZ - let's get the survey title for display
                $sSurveyTitle = $aSurveyInfo["name"];
                // CHANGE JSW_NZ - let's get css from individual template.css - so define path
                $sSurveyCSSPath = getTemplateURL($aSurveyInfo["template"]);
                if ($aSurveyInfo['publicstatistics']!='Y')
                {
                    safeDie('The public statistics for this survey are deactivated.');
                }

                //check if graphs should be shown for this survey
                if ($aSurveyInfo['publicgraphs']=='Y')
                {
                    $iPublicGraphs = 1;
                }
                else
                {
                    $iPublicGraphs = 0;
                }
            }
        }

        //we collect all the output within this variable
        $sOutput ='';

        // Set language for questions and labels to base language of this survey
        if (isset($_SESSION['survey_'.$iSurveyID]['s_lang']))
            $sLanguage = $_SESSION['survey_'.$iSurveyID]['s_lang'];
        else
            $sLanguage = Survey::model()->findByPk($iSurveyID)->language;

        //set survey language for translations
        $clang = SetSurveyLanguage($iSurveyID, $sLanguage);

        //Create header 
        sendCacheHeaders();
        $bCondition = false;
        $header=  "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n"
                . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"".$sLanguage."\" lang=\"".$sLanguage."\"";
        if (getLanguageRTL($sLanguage))
        {
            $bCondition = true;
            $header.=" dir=\"rtl\" ";
        }
        
        $aData['surveylanguage'] = $sLanguage;
        $aData['sitename'] = Yii::app()->getConfig("sitename");
        $aData['condition'] = $bCondition;
        $aData['thisSurveyCssPath'] = $sSurveyCSSPath;

        //count number of responses
        $sQuery = "SELECT count(*) FROM {{survey_".intval($iSurveyID)."}}";

        //if incompleted responses should be filtered submitdate has to be not null
        //this setting is taken from config-defaults.php
        if (Yii::app()->getConfig("filterout_incomplete_answers"))
        {
            $sQuery .= " WHERE {{survey_".intval($iSurveyID)."}}.submitdate is not null";
        }
        // total number of responses
        $iTotalRecords = Yii::app()->db->createCommand($sQuery)->queryScalar();



        //---------- CREATE SGQA OF ALL QUESTIONS WHICH USE "PUBLIC_STATISTICS" ----------
        $aSummary = createCompleteSGQA($iSurveyID,$sLanguage,true);

        //---------- CREATE STATISTICS ----------


        //some progress bar stuff

        // Create progress bar which is shown while creating the results
        $oProgressBar = new ProgressBar();
        $oProgressBar->pedding = 2;    // Bar Pedding
        $oProgressBar->brd_color = "#404040 #dfdfdf #dfdfdf #404040";    // Bar Border Color

        $oProgressBar->setFrame();    // set ProgressBar Frame
        $oProgressBar->frame['left'] = 50;    // Frame position from left
        $oProgressBar->frame['top'] =     80;    // Frame position from top
        $oProgressBar->addLabel('text','txt1',$clang->gT("Please wait ..."));    // add Text as Label 'txt1' and value 'Please wait'
        $oProgressBar->addLabel('percent','pct1');    // add Percent as Label 'pct1'
        $oProgressBar->addButton('btn1',$clang->gT('Go back'),'?action=statistics&amp;sid='.$iSurveyID);    // add Button as Label 'btn1' and action '?restart=1'

        $oProgressBar->show();    // show the ProgressBar


        // 1: Get list of questions with answers chosen
        //"Getting Questions and Answers ..." is shown above the bar
        $oProgressBar->setLabelValue('txt1',$clang->gT('Getting questions and answers ...'));
        $oProgressBar->moveStep(5);

        // creates array of post variable names
        for (reset($_POST); $key=key($_POST); next($_POST))
        {
            $postvars[]=$key;
        }
        $aData['thisSurveyTitle'] = $sSurveyTitle;
        $aData['totalrecords'] = $iTotalRecords;
        $aData['clang'] = $clang;
        $aData['summary'] = $aSummary;
        //show some main data at the beginnung
        // CHANGE JSW_NZ - let's allow html formatted questions to show


        //push progress bar from 35 to 40
        $iProgressPercentage = 40;
        $oProgressBar->moveStep($iProgressPercentage);

        //Show Summary results
        if (isset($aSummary) && $aSummary)
        {
            //"Generating Summaries ..." is shown above the progress bar
            $oProgressBar->setLabelValue('txt1',$clang->gT('Generating summaries ...'));
            $oProgressBar->moveStep($iProgressPercentage);

            //let's run through the survey // Fixed bug 3053 with array_unique
            $runthrough=array_unique($aSummary);

            //loop through all selected questions
            foreach ($runthrough as $rt)
            {

                //update progress bar
                if ($iProgressPercentage < 100) $iProgressPercentage++;
                $oProgressBar->moveStep($iProgressPercentage);

            }    // end foreach -> loop through all questions

            $oStatisticsHelper = new statistics_helper();
            $sOutput .= $oStatisticsHelper->generate_statistics($iSurveyID, $aSummary, $iPublicGraphs, 'html', null,$sLanguage,false);

        }    //end if -> show summary results

        $aData['statisticsoutput']=$sOutput;
        //done! set progress bar to 100%
        if (isset($oProgressBar))
        {
            $oProgressBar->setLabelValue('txt1',$clang->gT('Completed'));
            $oProgressBar->moveStep(100);
            $oProgressBar->hide();
        }

        //SET THE TEMPLATE DIRECTORY
        if (!isset($aSurveyInfo['templatedir']) || !$aSurveyInfo['templatedir'])
        {
            $aData['sTemplatePath'] = validateTemplateDir("default");
        }
        else
        {
            $aData['sTemplatePath'] = validateTemplateDir($aSurveyInfo['templatedir']);
        }
        
        $redata = compact(array_keys(get_defined_vars()));
        $data['redata'] = $redata;
        header_includes('statistics_user.js');
        $this->render('/statistics_user_view',$aData);

        //output footer
        echo getFooter();

        //Delete all Session Data
        killSurveySession($iSurveyID);
    }

}
