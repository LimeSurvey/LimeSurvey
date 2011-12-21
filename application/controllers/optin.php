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
  * optout
  *
  * @package LimeSurvey
  * @copyright 2011
  * @version $Id$
  * @access public
  */
 class optin extends LSCI_Controller {

	/**
	 * optout::__construct()
	 * Constructor
	 * @return
	 */
  /*  	function __construct()
	{
		parent::__construct();
	}
*/
     function local()
     {
          $this->load->helper('database');
         $this->load->helper('sanitize');
         $sLanguageCode=$this->uri->segment(3);
         $iSurveyID=$this->uri->segment(4);
         $sToken=$this->uri->segment(5);
         $sToken=sanitize_token($sToken);

         if (!$iSurveyID)
         {
            //You must have an SID to use this
            redirect(); //include "index.php";
            //exit;
         }
         $iSurveyID = (int)$iSurveyID;
          //Check that there is a SID
        // Get passed language from form, so that we dont loose this!
        if (!isset($sLanguageCode) || $sLanguageCode == "" || !$sLanguageCode)
        {
            $baselang = GetBaseLanguageFromSurveyID($iSurveyID);
            $this->load->library('Limesurvey_lang',array($baselang));
            $clang = $this->limesurvey_lang;

            //$baselang = GetBaseLanguageFromSurveyID($iSurveyID);
            //$clang = new limesurvey_lang($baselang);
        } else {
            $sLanguageCode = sanitize_languagecode($sLanguageCode);
            $this->load->library('Limesurvey_lang',array($sLanguageCode));
            $clang = $this->limesurvey_lang;
            $baselang = $sLanguageCode;



            //$clang = new limesurvey_lang($sLanguageCode);
            //$baselang = $sLanguageCode;
        }
        $thissurvey=getSurveyInfo($iSurveyID,$baselang);

        $html='<div id="wrapper"><p id="optoutmessage">';
        if ($thissurvey==false || !tableExists("tokens_{$iSurveyID}")){
            $html .= $clang->gT('This survey does not seem to exist.');
        }
        else
        {
            $usquery = "SELECT emailstatus from ".$this->db->dbprefix."tokens_{$iSurveyID} where token='".$sToken."'";
            $res=db_execute_assoc($usquery);
            $row=$res->row_array();
            $usresult = $row['emailstatus']; //'$connect->GetOne($usquery);

            if ($usresult==false)
            {
                $html .= $clang->gT('You are not a participant in this survey.');
            }
            elseif ($usresult=='OptOut')
            {
                $usquery = "Update ".$this->db->dbprefix."tokens_{$iSurveyID} set emailstatus='OK' where token='".$sToken."'";
                $usresult = db_execute_assoc($usquery);
                $html .= $clang->gT('You have been successfully added back to this survey.');
            }
            elseif ($usresult=='OK')
            {
                $html .= $clang->gT('You are already a part of this survey.');
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

    /**
     * optout::index()
     * Function responsible to process opting out from a survey and display appropriate message.
     * @param mixed $iSurveyID
     * @param mixed $sLanguageCode
     * @param mixed $sToken
     * @return
     */

}
