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
 *	$Id$
 */

/**
 * printanswers
 *
 * @package LimeSurvey
 * @copyright 2011
 * @version $Id$
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

        global $siteadminname, $siteadminemail;
        Yii::app()->loadHelper("frontend");

        Yii::import('application.libraries.admin.pdf');

        $surveyid = (int)($surveyid);
        Yii::app()->loadHelper('database');

        if (isset($_SESSION['survey_'.$surveyid]['sid']))
        {
            $surveyid = $_SESSION['survey_'.$surveyid]['sid'];
        }
        else
        {
            //die('Invalid survey/session');
        }
        // Get the survey inforamtion
        // Set the language for dispay
        if (isset($_SESSION['survey_'.$surveyid]['s_lang']))
        {
            $language = $_SESSION['survey_'.$surveyid]['s_lang'];
        }
        elseif(Survey::model()->findByPk($surveyid))// survey exist
        {
            $language = Survey::model()->findByPk($surveyid)->language;
        }
        else
        {
            $surveyid=0;
            $language = Yii::app()->getConfig("defaultlang");
        }
        $clang = SetSurveyLanguage($surveyid, $language);
        $thissurvey = getSurveyInfo($surveyid,$language);
        //SET THE TEMPLATE DIRECTORY
        if (!isset($thissurvey['templatedir']) || !$thissurvey['templatedir'])
        {
            $thissurvey['templatedir']=Yii::app()->getConfig('defaulttemplate');
        }
        $thistpl = validateTemplateDir($thissurvey['templatedir']);

        //Survey is not finished or don't exist
        if (!isset($_SESSION['survey_'.$surveyid]['finished']) || !isset($_SESSION['survey_'.$surveyid]['srid']))
        //display "sorry but your session has expired"
        {
            sendCacheHeaders();
            doHeader();
            echo templatereplace(file_get_contents(getTemplatePath($thistpl).'/startpage.pstpl'),array(),$redata);
            echo "<center><br />\n"
            ."\t<font color='RED'><strong>".$clang->gT("Error")."</strong></font><br />\n"
            ."\t".$clang->gT("We are sorry but your session has expired.")."<br />".$clang->gT("Either you have been inactive for too long, you have cookies disabled for your browser, or there were problems with your connection.")."<br />\n"
            ."\t".sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$siteadminname,$siteadminemail)."\n"
            ."</center><br />\n";
            echo templatereplace(file_get_contents(getTemplatePath($thistpl).'/endpage.pstpl'),array(),$redata);
            doFooter();
            exit;
        }
        //Fin session time out
        $id = $_SESSION['survey_'.$surveyid]['srid']; //I want to see the answers with this id

        //Ensure script is not run directly, avoid path disclosure
        //if (!isset($rootdir) || isset($_REQUEST['$rootdir'])) {die( "browse - Cannot run this script directly");}

        if ($thissurvey['printanswers'] == 'N')
        {
            die();  //Die quietly if print answers is not permitted
        }

        //CHECK IF SURVEY IS ACTIVATED AND EXISTS

        $surveytable = "{{survey_{$surveyid}}}";
        $surveyname = $thissurvey['surveyls_title'];
        $anonymized = $thissurvey['anonymized'];


        //OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.
        //SHOW HEADER
        $printoutput = CHtml::form(array("printanswers/view/surveyid/{$surveyid}/printableexport/pdf"), 'post')
        ."<center><input type='submit' value='".$clang->gT("PDF export")."'id=\"exportbutton\"/><input type='hidden' name='printableexport' /></center></form>";
        if($printableexport == 'pdf')
        {
            require (Yii::app()->getConfig('rootdir').'/application/config/tcpdf.php');
            Yii::import('application.libraries.admin.pdf', true);
            $pdf = new pdf();
            $pdf->setConfig($tcpdf);
            //$pdf->SetFont($pdfdefaultfont,'',$pdffontsize);
            $pdf->AddPage();
            //$pdf->titleintopdf($clang->gT("Survey name (ID)",'unescaped').": {$surveyname} ({$surveyid})");
            $pdf->SetTitle($clang->gT("Survey name (ID)",'unescaped').": {$surveyname} ({$surveyid})");
        }
        $printoutput .= "\t<div class='printouttitle'><strong>".$clang->gT("Survey name (ID):")."</strong> $surveyname ($surveyid)</div><p>&nbsp;\n";

        LimeExpressionManager::StartProcessingPage(true);  // means that all variables are on the same page
        // Since all data are loaded, and don't need JavaScript, pretend all from Group 1
        LimeExpressionManager::StartProcessingGroup(1,($thissurvey['anonymized']!="N"),$surveyid);

        $aFullResponseTable = getFullResponseTable($surveyid,$id,$language,true);

        //Get the fieldmap @TODO: do we need to filter out some fields?
        unset ($aFullResponseTable['id']);
        unset ($aFullResponseTable['token']);
        unset ($aFullResponseTable['lastpage']);
        unset ($aFullResponseTable['startlanguage']);
        unset ($aFullResponseTable['datestamp']);
        unset ($aFullResponseTable['startdate']);

        $printoutput .= "<table class='printouttable' >\n";
        if($printableexport == 'pdf')
        {
            $pdf->intopdf($clang->gT("Question",'unescaped').": ".$clang->gT("Your answer",'unescaped'));
        }

        $oldgid = 0;
        $oldqid = 0;
        foreach ($aFullResponseTable as $sFieldname=>$fname)
        {
            if (substr($sFieldname,0,4) == 'gid_')
            {

        	    if($printableexport)
        	    {
        		    $pdf->intopdf(flattenText(templatereplace($fname[0]),false,true));
        		    $pdf->ln(2);
                }
                else
                {
                   $printoutput .= "\t<tr class='printanswersgroup'><td colspan='2'>{$fname[0]}</td></tr>\n";
                }
            }
            elseif (substr($sFieldname,0,4)=='qid_')
            {
                if($printableexport == 'pdf')
                {
                    $pdf->intopdf(flattenText(templatereplace($fname[0]).templatereplace($fname[1]),false,true).": ".$fname[2]);
                    $pdf->ln(2);
                }
                else
                {
                    $printoutput .= "\t<tr class='printanswersquestionhead'><td colspan='2'>{$fname[0]}</td></tr>\n";
                }
            }
            elseif ($sFieldname=='submitdate')
            {
                if($anonymized != 'Y')
                {
                   if($printableexport == 'pdf')
                   {
                       $pdf->intopdf(flattenText($fname[0].$fname[1],false,true).": ".$fname[2]);
                       $pdf->ln(2);
                   }
                   else
                   {
                       $printoutput .= "\t<tr class='printanswersquestion'><td>{$fname[0]} {$fname[1]} {$sFieldname}</td><td class='printanswersanswertext'>{$fname[2]}</td></tr>";
                   }
                }
            }
            else
            {
                if($printableexport == 'pdf')
                {
                    $pdf->intopdf(flattenText(templatereplace($fname[0]).templatereplace($fname[1]),false,true).": ".$fname[2]);
                    $pdf->ln(2);
                }
                else
                {
                    $printoutput .= "\t<tr class='printanswersquestion'><td>{$fname[0]} {$fname[1]}</td><td class='printanswersanswertext'>".flattenText($fname[2])."</td></tr>";
                }
            }
        }
        $printoutput .= "</table>\n";

        if($printableexport == 'pdf')
        {
            header("Pragma: public");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            $sExportFileName = sanitize_filename($surveyname);
            $pdf->Output($sExportFileName."-".$surveyid.".pdf","D");
        }
        else//Display the page with user answers
        {
            sendCacheHeaders();
            doHeader();
            $redata['thissurvey']=$thissurvey;
            echo templatereplace(file_get_contents(getTemplatePath($thistpl).'/startpage.pstpl'),array(),$redata);
            echo templatereplace(file_get_contents(getTemplatePath($thistpl).'/printanswers.pstpl'),array('ANSWERTABLE'=>$printoutput),$redata);
            echo templatereplace(file_get_contents(getTemplatePath($thistpl).'/endpage.pstpl'),array(),$redata);
            echo "</body></html>";
        }

        LimeExpressionManager::FinishProcessingGroup();
        LimeExpressionManager::FinishProcessingPage();
    }
}
