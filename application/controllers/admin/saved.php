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
 * $Id: saved.php 11128 2011-10-08 22:23:24Z dionet $
 */

 /**
  * saved
  *
  * @package LimeSurvey
  * @author
  * @copyright 2011
  * @version $Id: saved.php 11128 2011-10-08 22:23:24Z dionet $
  * @access public
  */
 class saved extends Survey_Common_Action {

    /**
     * saved::view()
     * Load viewing of unsaved responses screen.
     * @param mixed $surveyid
     * @return
     */
	
	public function run($sa)
    {
		if ($sa == 'delete')
			$this->route('delete', array());
		$this->route('view', array());
    }

    public function view()
    {
    	@$surveyid = $_REQUEST['surveyid'];
		if (!empty($_REQUEST['sid'])) $surveyid = (int)$_REQUEST['sid'];
		$surveyid = sanitize_int($surveyid);
		$this->_js_admin_includes(Yii::app()->baseUrl.'scripts/jquery/jquery.tablesorter.min.js');
		$this->_js_admin_includes(Yii::app()->baseUrl.'scripts/admin/saved.js');
        $this->getController()->_getAdminHeader();

        if(bHasSurveyPermission($surveyid,'responses','read'))
        {
            $clang = $this->getController()->lang;
            $thissurvey=getSurveyInfo($surveyid);

            $savedsurveyoutput = "<div class='menubar'>\n"
            . "<div class='menubar-title ui-widget-header'><span style='font-weight:bold;'>\n";
            $savedsurveyoutput .= $clang->gT("Saved Responses")."</span> ".$thissurvey['name']." (ID: $surveyid)</div>\n"
            . "<div class='menubar-main'>\n"
            . "<div class='menubar-left'>\n";

            $savedsurveyoutput .= $this->_savedmenubar($surveyid);

            $savedsurveyoutput .= "</div></div></div>\n";

            $savedsurveyoutput .= "<div class='header ui-widget-header'>".$clang->gT("Saved Responses:") . " ". getSavedCount($surveyid)."</div><p>";

            $data['display'] = $savedsurveyoutput;
            $this->getController()->render('/survey_view',$data);
            $this->_showSavedList($surveyid);
        }

        $this->getController()->_loadEndScripts();


	   $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));


    }

    /**
     * saved::delete()
     * Function responsible to delete saved responses.
     * @return
     */
    public function delete()
    {
        @$surveyid=$_REQUEST['sid'];
        @$srid=$_REQUEST['srid'];
        @$scid=$_REQUEST['scid'];
        @$subaction=$_REQUEST['subaction'];
        $surveytable = "{{survey_".$surveyid."}}";

        if ($subaction == "delete" && $surveyid && $scid)
        {
            $query = "DELETE FROM {{saved_control}}
        			  WHERE scid=$scid
        			  AND sid=$surveyid
        			  ";
            Yii::app()->loadHelper('database');
            if ($result = db_execute_assoc($query))
            {
                //If we were succesful deleting the saved_control entry,
                //then delete the rest
                $query = "DELETE FROM {$surveytable} WHERE id={$srid}";
                $result = db_execute_assoc($query) or die("Couldn't delete");

            }
            else
            {
                safe_error("Couldn't delete<br />$query<br />");
            }
        }
        $this->getController()->redirect("admin/saved/view/surveyid/".$surveyid,'refresh');
    }

    /**
     * saved::_showSavedList()
     * Load saved list.
     * @param mixed $surveyid
     * @return
     */
    private function _showSavedList($surveyid)
    {
        Yii::app()->loadHelper('database');

        $query = "SELECT scid, srid, identifier, ip, saved_date, email, access_code\n"
        ."FROM {{saved_control}}\n"
        ."WHERE sid=$surveyid\n"
        ."ORDER BY saved_date desc";
        $result = db_execute_assoc($query) or safe_die ("Couldn't summarise saved entries<br />$query<br />");
        if ($result->count() > 0)
        {

            $data['result'] = $result;
            $data['clang'] = $this->getController()->lang;
            $data['surveyid'] = $surveyid;

            $this->getController()->render('/admin/saved/savedlist_view',$data);
        }
    }


    /**
     * saved::_savedmenubar()
     * Load menu bar of saved controller.
     * @param mixed $surveyid
     * @return
     */
    private function _savedmenubar($surveyid)
    {
        //BROWSE MENU BAR
        $clang = $this->getController()->lang;
        if (!isset($surveyoptions)) {$surveyoptions="";}
        $surveyoptions .= "<a href='".Yii::app()->baseUrl.'admin/survey/view/surveyid/'.$surveyid."' title='".$clang->gTview("Return to survey administration")."' >" .
    			"<img name='Administration' src='".Yii::app()->getConfig('imageurl')."/home.png' alt='".$clang->gT("Return to survey administration")."' align='left'></a>\n";

        return $surveyoptions;
    }


 }