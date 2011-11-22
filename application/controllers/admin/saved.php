<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey (tm)
 * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id$
 */

 /**
  * saved
  *
  * @package LimeSurvey
  * @author
  * @copyright 2011
  * @version $Id$
  * @access public
  */
 class saved extends Survey_Common_Controller {

    /**
     * saved::__construct()
     * Constructor
     * @return
     */
    function __construct()
	{
		parent::__construct();
	}

    /**
     * saved::view()
     * Load viewing of unsaved responses screen.
     * @param mixed $surveyid
     * @return
     */
    function view($surveyid)
    {
    	$surveyid = sanitize_int($surveyid);
        self::_js_admin_includes(base_url().'scripts/jquery/jquery.tablesorter.min.js');
        self::_js_admin_includes(base_url().'scripts/admin/saved.js');
        self::_getAdminHeader();

        if(bHasSurveyPermission($surveyid,'responses','read'))
        {
            $clang = $this->limesurvey_lang;
            $thissurvey=getSurveyInfo($surveyid);

            $savedsurveyoutput = "<div class='menubar'>\n"
            . "<div class='menubar-title ui-widget-header'><span style='font-weight:bold;'>\n";
            $savedsurveyoutput .= $clang->gT("Saved Responses")."</span> ".$thissurvey['name']." (ID: $surveyid)</div>\n"
            . "<div class='menubar-main'>\n"
            . "<div class='menubar-left'>\n";

            $savedsurveyoutput .= self::_savedmenubar($surveyid);

            $savedsurveyoutput .= "</div></div></div>\n";

            $savedsurveyoutput .= "<div class='header ui-widget-header'>".$clang->gT("Saved Responses:") . " ". getSavedCount($surveyid)."</div><p>";

            $data['display'] = $savedsurveyoutput;
            $this->load->view('survey_view',$data);
            self::_showSavedList($surveyid);
        }

        self::_loadEndScripts();


	   self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));


    }

    /**
     * saved::delete()
     * Function responsible to delete saved responses.
     * @return
     */
    function delete()
    {
        $surveyid=$this->input->post('sid');
        $srid=$this->input->post('srid');
        $scid=$this->input->post('scid');
        $subaction=$this->input->post('subaction');

        if ($subaction == "delete" && $surveyid && $scid)
        {
            $this->load->model('saved_control_model');
            $condition = array('scid' => $scid, 'sid' => $surveyid);
            $result = $this->saved_control_model->deleteSurveyRecords($condition);

            if ($result)
            {
                $this->load->model('surveys_dynamic_model');
                $condition = array('id' => $srid);
                $result = $this->surveys_dynamic_model->deleteRecords($surveyid,$condition);

                if(!$result)
                {
                    show_error("Couldn't delete from survey table<br /><br />");
                }
            }
            else
            {
                show_error("Couldn't delete from saved_control table<br /><br />");
            }

        }
        redirect("admin/saved/view/".$surveyid,'refresh');
    }

    /**
     * saved::_showSavedList()
     * Load saved list.
     * @param mixed $surveyid
     * @return
     */
    function _showSavedList($surveyid)
    {
        $this->load->helper('database');
        $this->load->model('saved_control_model');        
        $condition = array('sid' => $surveyid);        
        $result = $this->saved_control_model->getSavedList($condition);
        if ($result->num_rows() > 0)
        {

            $data['result'] = $result;
            $data['clang'] = $clang;
            $data['surveyid'] = $surveyid;

            $this->load->view('admin/saved/savedlist_view',$data);
        }
        else {
            show_error("Couldn't summarise saved entries<br /><br />");
        }
    }


    /**
     * saved::_savedmenubar()
     * Load menu bar of saved controller.
     * @param mixed $surveyid
     * @return
     */
    function _savedmenubar($surveyid)
    {
        //BROWSE MENU BAR
        $clang = $this->limesurvey_lang;
        if (!isset($surveyoptions)) {$surveyoptions="";}
        $surveyoptions .= "<a href='".site_url('admin/survey/view/'.$surveyid)."' title='".$clang->gTview("Return to survey administration")."' >" .
    			"<img name='Administration' src='".$this->config->item('imageurl')."/home.png' alt='".$clang->gT("Return to survey administration")."' align='left'></a>\n";

        return $surveyoptions;
    }


 }