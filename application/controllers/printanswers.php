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
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */

/**
 * printanswers
 *
 * @package LimeSurvey
 * @copyright 2011
 * @version $Id$
 * @access public
 */
class printanswers extends LSYii_Controller {

	/**
	 * printanswers::__construct()
	 * Constructor
	 * @return
	 */
	function __construct()
	{
		parent::__construct();
	}

    /**
     * printanswers::view()
     * View answers at the end of a survey in one place. To export as pdf, set 'usepdfexport' = 1 in lsconfig.php and $printableexport='pdf'.
     * @param mixed $surveyid
     * @param bool $printableexport
     * @return
     */
    function view($surveyid,$printableexport=FALSE)
    {

        global $siteadminname, $siteadminemail;

        if(Yii::app()->getConfig('usepdfexport'))
        {
            Yii::import('application.libraries.admin.pdf');
        }

        //if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
        //else {
            //This next line ensures that the $surveyid value is never anything but a number.
        $surveyid = (int)($surveyid);
        //}
        Yii::app()->loadHelper('database');
        //$clang = $this->limesurvey_lang;

        // Compute the Session name
        // Session name is based:
        // * on this specific limesurvey installation (Value SessionName in DB)
        // * on the surveyid (from Get or Post param). If no surveyid is given we are on the public surveys portal
        $usresult = Settings_global::model()->find('stg_name = "SessionName"');
        $session = new CHttpSession;
        if ($usresult != null)
        {
            $stg_SessionName = $usrow->stg_value;
            if ($surveyid)
            {
                $session->sessionName = $stg_SessionName.'-runtime-'.$surveyid;
            }
            else
            {
                $session->sessionName = $stg_SessionName.'-runtime-publicportal';
            }
        }
        else
        {
            $session->sessionName = "LimeSurveyRuntime-$surveyid";
        }
        $session->setCookieParams(array('lifetime' => 0, 'path' => Yii::app()->getConfig('relativeurl').'/'));
        $session->open;

        if (isset($session['sid']))
        {
            $surveyid = $session['sid'];
        }
        else
        {
            show_error('Invalid survey/session');
        }

        //Debut session time out
        if (!isset($session['finished']) || !isset($session['srid']))
        // Argh ... a session time out! RUN!
        //display "sorry but your session has expired"
        {
            //require_once($rootdir.'/classes/core/language.php');
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            Yii::import('application.libraries.Limesurvey_lang', true);
            $clang = new Limesurvey_lang(array('langcode' => $baselang));
            //A nice exit

            sendcacheheaders();
            doHeader();

            echo templatereplace(file_get_contents(sGetTemplatePath(validate_templatedir("default"))."/startpage.pstpl"),array(),array());
            echo "<center><br />\n"
            ."\t<font color='RED'><strong>".$clang->gT("ERROR")."</strong></font><br />\n"
            ."\t".$clang->gT("We are sorry but your session has expired.")."<br />".$clang->gT("Either you have been inactive for too long, you have cookies disabled for your browser, or there were problems with your connection.")."<br />\n"
            ."\t".sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$siteadminname,$siteadminemail)."\n"
            ."</center><br />\n";

            echo templatereplace(file_get_contents(sGetTemplatePath(validate_templatedir("default"))."/endpage.pstpl"),array(),array());
            doFooter();
            exit;
        }
        //Fin session time out

        $id = $session['srid']; //I want to see the answers with this id
        $clang = $session['s_lang'];

        //Ensure script is not run directly, avoid path disclosure
        //if (!isset($rootdir) || isset($_REQUEST['$rootdir'])) {die( "browse - Cannot run this script directly");}

        // Set the language for dispay
        //require_once($rootdir.'/classes/core/language.php');  // has been secured
        if (isset($session['s_lang']))
        {

            $clang = SetSurveyLanguage( $surveyid, $session['s_lang']);
            $language = $session['s_lang'];
        }
        else
        {
            $language = GetBaseLanguageFromSurveyID($surveyid);
            $clang = SetSurveyLanguage( $surveyid, $language);
        }

        // Get the survey inforamtion
        $thissurvey = getSurveyInfo($surveyid,$language);

        //SET THE TEMPLATE DIRECTORY
        if (!isset($thissurvey['templatedir']) || !$thissurvey['templatedir'])
        {
            $thistpl = validate_templatedir("default");
        }
        else
        {
            $thistpl = validate_templatedir($thissurvey['templatedir']);
        }

        if ($thissurvey['printanswers'] == 'N')
        {
            die();  //Die quietly if print answers is not permitted
        }

        //CHECK IF SURVEY IS ACTIVATED AND EXISTS
        $actquery = new CDbCriteria;
        $actquery->join = 'INNER JOIN  {{surveys_languagesettings}} as b on (b.surveyls_surveyid = t.sid and b.surveyls_language = t.language)';
        $actquery->condition = 't.sid = :sid';
        $actquery->params(array(':sid' => $surveyid));
        $actresult = Surveys::model()->findAll($actquery);
        $actcount = count($actresult);
        if ($actcount > 0)
        {
            foreach ($actresult as $actrow)
            {
                $surveytable = $this->db->dbprefix."survey_".$actrow->sid;
                $surveyname = "{$actrow->surveyls_title}";
            }
        }


        //OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.
        //SHOW HEADER
        $printoutput = '';
        if(Yii::app()->getConfig('usepdfexport') == 1)
        {
            $printoutput .= "<form action='".Yii::app()->createUrl('printanswers/view/'.$surveyid.'/pdf')."' method='post'>\n<center><input type='submit' value='".$clang->gT("PDF export")."'id=\"exportbutton\"/><input type='hidden' name='printableexport' /></center></form>";
        }
        if($printableexport == 'pdf')
        {
            require (Yii::app()->getConfig('rootdir').'/application/config/tcpdf_config_ci.php');
            Yii::import('application.libraries.admin.pdf', true);
            $pdf = new pdf();
            $pdf->_config = $tcpdf;
            //$pdf->SetFont($pdfdefaultfont,'',$pdffontsize);
            $pdf->AddPage();
            //$pdf->titleintopdf($clang->gT("Survey name (ID)",'unescaped').": {$surveyname} ({$surveyid})");
            $pdf->SetTitle($clang->gT("Survey name (ID)",'unescaped').": {$surveyname} ({$surveyid})");
        }
        $printoutput .= "\t<div class='printouttitle'><strong>".$clang->gT("Survey name (ID):")."</strong> $surveyname ($surveyid)</div><p>&nbsp;\n";

        LimeExpressionManager::StartProcessingPage(false,true,true);  // means that all variables are on the same page
        // Since all data are loaded, and don't need JavaScript, pretend all from Group 1
        LimeExpressionManager::StartProcessingGroup(1,($thissurvey['anonymized']!="N"),$surveyid);

        $aFullResponseTable = aGetFullResponseTable($surveyid,$id,$language,true);

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
        		    $pdf->intopdf(FlattenText($fname[0],false,true));
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
                    $pdf->intopdf(FlattenText($fname[0].$fname[1],false,true).": ".$fname[2]);
                    $pdf->ln(2);
                }
                else
                {
                    $printoutput .= "\t<tr class='printanswersquestionhead'><td  colspan='2'>{$fname[0]}</td></tr>\n";
                }
            }
            else
            {
                if($printableexport == 'pdf')
                {
                    $pdf->intopdf(FlattenText($fname[0].$fname[1],false,true).": ".$fname[2]);
                    $pdf->ln(2);
                }
                else
                {
                    $printoutput .= "\t<tr class='printanswersquestion'><td>{$fname[0]} {$fname[1]}</td><td class='printanswersanswertext'>{$fname[2]}</td></tr>";
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

        //Display the page with user answers
        if(!$printableexport)
        {

            sendcacheheaders();
            doHeader();

            echo templatereplace(file_get_contents(sGetTemplatePath($thistpl).'/startpage.pstpl'));
            echo templatereplace(file_get_contents(sGetTemplatePath($thistpl).'/printanswers.pstpl'),array('ANSWERTABLE'=>$printoutput));
            echo templatereplace(file_get_contents(sGetTemplatePath($thistpl).'/endpage.pstpl'));
            echo "</body></html>";
        }

        LimeExpressionManager::FinishProcessingGroup();
        LimeExpressionManager::FinishProcessingPage();
    }
}
