<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *
 *
 */

 /**
  * optout
  *
  * @package LimeSurvey
  * @copyright 2011
  * @version $Id$
  * @access public
  */
 class optout extends LS_Controller {

	/**
	 * optout::__construct()
	 * Constructor
	 * @return
	 */
	function __construct()
	{
		parent::__construct();
	}

    /**
     * optout::index()
     * Function responsible to process opting out from a survey and display appropriate message.
     * @param mixed $surveyid
     * @param mixed $postlang
     * @param mixed $token
     * @return
     */
    function index($surveyid,$postlang,$token)
    {

        $this->load->helper('database');

        //$surveyid=$this->input->post('sid');
        //$postlang=$this->input->post('lang');
        //$token=$this->input->post('token');

        //Check that there is a SID
        if (!$surveyid)
        {
            //You must have an SID to use this
            redirect(); //include "index.php";
            //exit;
        }

        // Get passed language from form, so that we dont loose this!
        if (!isset($postlang) || $postlang == "" || !$postlang)
        {
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            $this->load->library('Limesurvey_lang',array($baselang));
            $clang = $this->limesurvey_lang;

            //$baselang = GetBaseLanguageFromSurveyID($surveyid);
            //$clang = new limesurvey_lang($baselang);
        } else {
            $this->load->library('Limesurvey_lang',array($postlang));
            $clang = $this->limesurvey_lang;
            $baselang = $postlang;

            //$clang = new limesurvey_lang($postlang);
            //$baselang = $postlang;
        }
        $thissurvey=getSurveyInfo($surveyid,$baselang);

        $html='<div id="wrapper"><p id="optoutmessage">';
        if ($thissurvey==false || !tableExists("tokens_{$surveyid}")){
            $html .= $clang->gT('This survey does not seem to exist.');
        }
        else
        {
            $usquery = "SELECT emailstatus from ".$this->db->dbprefix."tokens_{$surveyid} where token='".$token."'";
            $res=db_execute_assoc($usquery);
            $row=$res->row_array();
            $usresult = $row['emailstatus']; //'$connect->GetOne($usquery);

            if ($usresult==false)
            {
                $html .= $clang->gT('You are not a participant in this survey.');
            }
            elseif ($usresult=='OK')
            {
                $usquery = "Update ".$this->db->dbprefix."tokens_{$surveyid} set emailstatus='OptOut' where token='".$token."'";
                $usresult = db_execute_assoc($usquery);
                $html .= $clang->gT('You have been successfully removed from this survey.');
            }
            else
            {
                $html .= $clang->gT('You have been already removed from this survey.');
            }
        }
        $html .= '</p></div>';

        //PRINT COMPLETED PAGE
        if (!$thissurvey['templatedir'])
        {
            $thistpl=sGetTemplatePath($defaulttemplate);
        }
        else
        {
            $thistpl=sGetTemplatePath($thissurvey['templatedir']);
        }

        sendcacheheaders();
        doHeader();

        echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
        echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));
        echo $html;
        echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));

        doFooter();

    }

 }
