<?php
/*
 * LimeSurvey
 * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
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
 
 class html extends AdminController {
 
    function __construct()
	{
		parent::__construct();
		
	}
 
    
    function _display($action='',$surveyid)
    {
        
        global $gid,$qid;
        $this->load->helper('common');
        //Ensure script is not run directly, avoid path disclosure
        //include_once("login_check.php");
        if ($this->input->post('uid')) {$postuserid=sanitize_int($this->input->post('uid'));}
        if ($this->input->post('ugid')) {$postusergroupid=sanitize_int($this->input->post('ugid'));}
        //loading surveytranslator helper
        $this->load->helper('surveytranslator');
        
        
        if (isset($surveyid) && $surveyid &&
        $action!='dataentry' && $action!='browse' && $action!='exportspss' &&
        $action!='statistics' && $action!='importoldresponses' && $action!='exportr' &&
        $action!='vvimport' && $action!='vvexport' && $action!='exportresults')
        {
            if(bHasSurveyPermission($surveyid,'survey','read'))
            {
                $js_admin_includes = $this->config->item("js_admin_includes");
                $js_admin_includes[]=$this->config->item('generalscripts').'jquery/jquery.coookie.js';
                $js_admin_includes[]=$this->config->item('generalscripts').'jquery/superfish.js';
                $js_admin_includes[]=$this->config->item('generalscripts').'jquery/hoverIntent.js';
                $js_admin_includes[]=$this->config->item('adminscripts').'surveytoolbar.js';
                $css_admin_includes[]= $this->config->item('styleurl')."admin/default/superfish.css";
                $this->config->set_item("js_admin_includes", $js_admin_includes); 
                
                $baselang = GetBaseLanguageFromSurveyID($surveyid);
                $condition = array('sid' => $surveyid, 'parent_qid' => 0, 'language' => $baselang);
                $this->load->model('questions_model');
                
                //$sumquery3 =  "SELECT * FROM ".db_table_name('questions')." WHERE sid={$surveyid} AND parent_qid=0 AND language='".$baselang."'"; //Getting a count of questions for this survey
                $sumresult3 = $this->questions_model->getAllRecords($condition); //$connect->Execute($sumquery3); //Checked
                $sumcount3 = $sumresult3->num_rows();
        
                //$sumquery6 = "SELECT count(*) FROM ".db_table_name('conditions')." as c, ".db_table_name('questions')." as q WHERE c.qid = q.qid AND q.sid=$surveyid"; //Getting a count of conditions for this survey
                $this->load->model('conditions_model');
                $query = $this->conditions_model->getCountOfConditions($surveyid);
                $sumcount6 = $query->row_array(); //$connect->GetOne($sumquery6); //Checked
                
                $condition = array('sid' => $surveyid, 'language' => $baselang);
                $this->load->model('groups_model');
                //$sumquery2 = "SELECT * FROM ".db_table_name('groups')." WHERE sid={$surveyid} AND language='".$baselang."'"; //Getting a count of groups for this survey
                $sumresult2 = $this->groups_model->getAllRecords($condition); //$connect->Execute($sumquery2); //Checked
                $sumcount2 = $sumresult2->num_rows();
                $this->load->model('surveys_model');
                //$sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
                $sumresult1 = $this->surveys_model->getDataOnSurvey($surveyid); //$sumquery1, 1) ; //Checked
                if ($sumresult1->num_rows()==0){die('Invalid survey id');} //  if surveyid is invalid then die to prevent errors at a later time
                // Output starts here...
                $surveysummary = "";
        
                $surveyinfo = $sumresult1->row_array();
        
                $surveyinfo = array_map('FlattenText', $surveyinfo);
                //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
                $activated = $surveyinfo['active'];
                
                $surveysummary = self::_surveybar($surveyid,$surveyinfo,$activated,$sumcount3,$sumcount2,$action,$gid,$qid);
                
                
                
            }
            
        }
        
        return $surveysummary;
    
    
    }
    
    
    function _surveybar($surveyid,$surveyinfo,$activated,$sumcount3,$sumcount2,$action,$gid,$qid)
    {
        $clang = $this->limesurvey_lang;
        ////////////////////////////////////////////////////////////////////////
        // SURVEY MENU BAR
        ////////////////////////////////////////////////////////////////////////
    
        $surveysummary = ""  //"<tr><td colspan=2>\n"
            . "<div class='menubar surveybar'>\n"
            . "<div class='menubar-title ui-widget-header'>\n"
            . "<strong>".$clang->gT("Survey")."</strong> "
            . "<span class='basic'>{$surveyinfo['surveyls_title']} (".$clang->gT("ID").":{$surveyid})</span></div>\n"
            . "<div class='menubar-main'>\n"
            . "<div class='menubar-left'>\n";
            
            
        $surveysummary .= self::_buttonActivate($surveyid,$surveyinfo,$activated,$sumcount3); 
        $surveysummary .= self::_buttonTest($surveyid,$activated);
        $surveysummary .= self::_buttonEdit($surveyid,$surveyinfo);
        $surveysummary .= self::_buttonTools($surveyid);
        $surveysummary .= self::_buttonExport($surveyid);
        $surveysummary .= self::_buttonResponse($surveyid);
        $surveysummary .= self::_buttonToken($surveyid);
        
        /**
         ////////////////////////////////////////////////////////////////////////
        // QUESTION GROUP TOOLBAR
        ////////////////////////////////////////////////////////////////////////

        $surveysummary.= "<div class='menubar-right'>\n";
        if (bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            $surveysummary .= "<span class=\"boxcaption\">".$clang->gT("Question groups").":</span>"
            . "<select name='groupselect' onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";

            if (getgrouplistlang($gid, $baselang))
            {
                $surveysummary .= getgrouplistlang($gid, $baselang);
            }
            else
            {
                $surveysummary .= "<option>".$clang->gT("None")."</option>\n";
            }
            $surveysummary .= "</select>\n";
        }
        else
        {
            $gid=null;
            $qid=null;
        }

        // QUICK NAVIGATION TO PREVIOUS AND NEXT QUESTION GROUP
        // TODO: Fix functionality to previous and next question group buttons (Andrie)
        $GidPrev = getGidPrevious($surveyid, $gid);
        $surveysummary .= "<span class='arrow-wrapper'>";
        if ($GidPrev != "")
        {
          $link = site_url("admin/index/index/$surveyid/$GidPrev");
          $surveysummary .= ""
            . "<a href='{$link}'>"
            . "<img src='".$this->config->item('imageurl')."/previous_20.png' title='' alt='".$clang->gT("Previous question group")."' "
            ."name='questiongroupprevious' ".$clang->gT("Previous question group")."/> </a>";
        }
        else
        {
          $surveysummary .= ""
            . "<img src='".$this->config->item('imageurl')."/previous_disabled_20.png' title='' alt='".$clang->gT("No previous question group")."' "
            ."name='noquestiongroupprevious' />";
        }

        $GidNext = getGidNext($surveyid, $gid);
        if ($GidNext != "")
        {
          $link = site_url("admin/index/index/$surveyid/$GidNext");
          $surveysummary .= ""
            . "<a href='{$link}'>"
            . "<img src='".$this->config->item('imageurl')."/next_20.png' title='' alt='".$clang->gT("Next question group")."' "
            ."name='questiongroupnext' /> </a>";
        }
        else
        {
          $surveysummary .= ""
            . "<img src='".$this->config->item('imageurl')."/next_disabled_20.png' title='' alt='".$clang->gT("No next question group")."' "
            ."name='noquestiongroupnext' />";
        }
		$surveysummary .= "</span>";


        // ADD NEW GROUP TO SURVEY BUTTON

        if(bHasSurveyPermission($surveyid,'surveycontent','create'))
        {
            if ($activated == "Y")
            {
                $surveysummary .= "<a href='#'>"
                ."<img src='".$this->config->item('imageurl')."/add_disabled.png' title='' alt='".$clang->gT("Disabled").' - '.$clang->gT("This survey is currently active.")."' " .
                " name='AddNewGroup' /></a>\n";
            }
            else
            {
                $link = site_url("admin/addgroup/index/$surveyid");
                $surveysummary .= "<a href=\"#\" onclick=\"window.open('$link', '_top')\""
                . " title=\"".$clang->gTview("Add new group to survey")."\">"
                . "<img src='".$this->config->item('imageurl')."/add.png' alt='".$clang->gT("Add new group to survey")."' name='AddNewGroup' /></a>\n";
            }
        }
        $surveysummary .= "<img src='".$this->config->item('imageurl')."/seperator.gif' alt='' />\n"
        . "<img src='".$this->config->item('imageurl')."/blank.gif' width='15' alt='' />"
        . "<input type='image' src='".$this->config->item('imageurl')."/minus.gif' title='". $clang->gT("Hide details of this Survey")."' "
        . "alt='". $clang->gT("Hide details of this Survey")."' name='MinimiseSurveyWindow' "
        . "onclick='document.getElementById(\"surveydetails\").style.display=\"none\";' />\n";

        $surveysummary .= "<input type='image' src='".$this->config->item('imageurl')."/plus.gif' title='". $clang->gT("Show details of this survey")."' "
        . "alt='". $clang->gT("Show details of this survey")."' name='MaximiseSurveyWindow' "
        . "onclick='document.getElementById(\"surveydetails\").style.display=\"\";' />\n";

        if (!$gid)
        {
            $link = site_url("admin/index/index");
            $surveysummary .= "<input type='image' src='".$this->config->item('imageurl')."/close.gif' title='". $clang->gT("Close this survey")."' "
            . "alt='".$clang->gT("Close this survey")."' name='CloseSurveyWindow' "
            . "onclick=\"window.open('$link', '_top')\" />\n";
        }
        else
        {
            $surveysummary .= "<img src='".$this->config->item('imageurl')."/blank.gif' width='18' alt='' />\n";
        }



        $surveysummary .= "</div>\n"
        . "</div>\n"
        . "</div>\n"; 
        
        */
            
        
        $surveysummary .= self::_surveysummary($surveyid,$surveyinfo,$activated,$action,$gid,$qid,$sumcount3,$sumcount2);
        
        return $surveysummary;
        
          
    }
    
    
    function _buttonActivate($surveyid,$surveyinfo,$activated,$sumcount3)
        {
            // ACTIVATE SURVEY BUTTON
            $clang = $this->limesurvey_lang;
            if ($activated == "N" )
            {
                $surveysummary = "<img src='".$this->config->item('imageurl')."/inactive.png' "
                . "alt='".$clang->gT("This survey is currently not active")."' />\n";
                if($sumcount3>0 && bHasSurveyPermission($surveyid,'surveyactivation','update'))
                {
                    
                    $link = site_url("admin/activate/index/$surveyid");
                    $surveysummary .= "<a href=\"#\" onclick=\"window.open('$link', '_top')\""
                    . " title=\"".$clang->gTview("Activate this Survey")."\" >"
                    . "<img src='".$this->config->item('imageurl')."/activate.png' name='ActivateSurvey' alt='".$clang->gT("Activate this Survey")."'/></a>\n" ;
                }
                else
                {
                    $surveysummary .= "<img src='".$this->config->item('imageurl')."/activate_disabled.png' alt='"
                    . $clang->gT("Survey cannot be activated. Either you have no permission or there are no questions.")."' />\n" ;
                }
            }
            elseif ($activated == "Y")
            {
                if ($surveyinfo['expires']!='' && ($surveyinfo['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $this->config->item('timeadjust'))))
                {
                    $surveysummary .= "<img src='".$this->config->item('imageurl')."/expired.png' "
                    . "alt='".$clang->gT("This survey is active but expired.")."' />\n";
                }
                elseif (($surveyinfo['startdate']!='') && ($surveyinfo['startdate'] > date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $this->config->item('timeadjust'))))
                {
                    $surveysummary .= "<img src='".$this->config->item('imageurl')."/notyetstarted.png' "
                    . "alt='".$clang->gT("This survey is active but has a start date.")."' />\n";
                }
                else
                {
                    $surveysummary .= "<img src='".$this->config->item('imageurl')."/active.png' title='' "
                    . "alt='".$clang->gT("This survey is currently active.")."' />\n";
                }
                if(bHasSurveyPermission($surveyid,'surveyactivation','update'))
                {
                    $link = site_url("admin/deactivate/index/$surveyid");
                    $surveysummary .= "<a href=\"#\" onclick=\"window.open('$link', '_top')\""
                    . " title=\"".$clang->gTview("Deactivate this Survey")."\" >"
                    . "<img src='".$this->config->item('imageurl')."/deactivate.png' alt='".$clang->gT("Deactivate this Survey")."' /></a>\n" ;
                }
                else
                {
                    $surveysummary .= "<img src='".$this->config->item('imageurl')."/blank.gif' alt='' width='14' />\n";
                }
            }
    
            $surveysummary .= "<img src='".$this->config->item('imageurl')."/seperator.gif' alt=''  />\n"
            . "</div>\n";
            
            // Start of suckerfish menu
            $surveysummary .= "<ul class='sf-menu'>\n";
            // ACTIVATE SURVEY BUTTON
            return $surveysummary;
            
        }
        
        
        function _buttonTest($surveyid,$activated)
        {
            $clang = $this->limesurvey_lang;
            if ($activated == "N")
            {
                $icontext=$clang->gT("Test This Survey");
                $icontext2=$clang->gTview("Test This Survey");
            } else
            {
                $icontext=$clang->gT("Execute This Survey");
                $icontext2=$clang->gTview("Execute This Survey");
            }
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            
            /** Require some front-end functionality
            if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
            {
                $surveysummary .= "<li><a href='#' accesskey='d' onclick=\"window.open('"
                . $publicurl."/index.php?sid={$surveyid}&amp;newtest=Y&amp;lang={$baselang}', '_blank')\" title=\"{$icontext2}\" >"
                . "<img src='".$this->config->item('imageurl')."/do.png' alt='{$icontext}' />"
                . "</a></li>\n";
    
            } else {
                $surveysummary .= "<li><a href='#' "
                . "title='{$icontext2}' accesskey='d'>"
                . "<img src='{$imageurl}/do.png' alt='{$icontext}' />"
                . "</a><ul>\n";
                $surveysummary .= "<li><a accesskey='d' target='_blank' href='{$publicurl}/index.php?sid=$surveyid&amp;newtest=Y'>"
                  . "<img src='{$imageurl}/do_30.png' /> $icontext </a><ul>";
                $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                $tmp_survlangs[] = $baselang;
                rsort($tmp_survlangs);
                // Test Survey Language Selection Popup
                foreach ($tmp_survlangs as $tmp_lang)
                {
                    $surveysummary .= "<li><a accesskey='d' target='_blank' href='{$publicurl}/index.php?sid=$surveyid&amp;newtest=Y&amp;lang={$tmp_lang}'>"
                    . "<img src='{$imageurl}/do_30.png' /> ".getLanguageNameFromCode($tmp_lang,false)."</a></li>";
                }
                $surveysummary .= "</ul></li>"
                ."</ul></li>";
            }
            
            */
            // SEPARATOR
            /*$surveysummary .= "<img src='{$imageurl}/seperator.gif' alt=''  />\n"
            . "</div>\n";*/
        }
        
        function _buttonEdit($surveyid,$surveyinfo)
        {
            $clang = $this->limesurvey_lang;
            
            $surveysummary ="<li><a href='#'>"
            . "<img src='".$this->config->item('imageurl')."/edit.png' name='EditSurveyProperties' alt='".$clang->gT("Survey properties")."' /></a><ul>\n";
    
            // EDIT SURVEY TEXT ELEMENTS BUTTON
            if(bHasSurveyPermission($surveyid,'surveylocale','read'))
            {
                $link = site_url("admin/editsurveylocalesettings/index/$surveyid");
                $surveysummary .= "<li><a href='{$link}' >"
                . "<img src='".$this->config->item('imageurl')."/edit_30.png' name='EditTextElements' /> ".$clang->gT("Edit text elements")."</a></li>\n";
            }
    
            // EDIT SURVEY SETTINGS BUTTON
            if(bHasSurveyPermission($surveyid,'surveysettings','read'))
            {
                $link = site_url("admin/editsurveysettings/index/$surveyid");
                $surveysummary .= "<li><a href='{$link}' >"
                . "<img src='".$this->config->item('imageurl')."/token_manage_30.png' name='EditGeneralSettings' /> ".$clang->gT("General settings")."</a></li>\n";
            }
            
            // Survey permission item
            if($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $surveyinfo['owner_id'] == $this->session->userdata('loginID'))
            {
                $link = site_url("admin/surveysecurity/index/$surveyid");
                $surveysummary .= "<li><a href='{$link}'>"
                . "<img src='".$this->config->item('imageurl')."/survey_security_30.png' name='SurveySecurity'/> ".$clang->gT("Survey permissions")."</a></li>\n";
            }        
            
            // CHANGE QUESTION GROUP ORDER BUTTON
            if (bHasSurveyPermission($surveyid,'surveycontent','read'))
            {
                if($activated=="Y")
                {
                    $surveysummary .= "<li><a href=\"#\" onclick=\"alert('".$clang->gT("You can't reorder question groups if the survey is active.", "js")."');\" >"
                    . "<img src='".$this->config->item('imageurl')."/reorder_disabled_30.png' name='translate'/> ".$clang->gT("Reorder question groups")."</a></li>\n";
                }
                elseif (getGroupSum($surveyid,$surveyinfo['language'])>1)
                {
                    $link = site_url("admin/ordergroups/index/$surveyid");
                    $surveysummary .= "<li><a href='{$link}'>"
                    . "<img src='".$this->config->item('imageurl')."/reorder_30.png' /> ".$clang->gT("Reorder question groups")."</a></li>\n";
                }       
                else{
                    $surveysummary .= "<li><a href=\"#\" onclick=\"alert('".$clang->gT("You can't reorder question groups if there is only one group.", "js")."');\" >"
                    . "<img src='".$this->config->item('imageurl')."/reorder_disabled_30.png' name='translate'/> ".$clang->gT("Reorder question groups")."</a></li>\n";
                } 
                
            }
    
            // SET SURVEY QUOTAS BUTTON
            if (bHasSurveyPermission($surveyid,'quotas','read'))
            {
                $link = site_url("admin/quotas/index/$surveyid");
                $surveysummary .= "<li><a href='{$link}'>"
                . "<img src='".$this->config->item('imageurl')."/quota_30.png' /> ".$clang->gT("Quotas")."</a></li>\n" ;
            }
            
            // Assessment menu item
            if (bHasSurveyPermission($surveyid,'assessments','read'))
            {
                $link = site_url("admin/assessments/index/$surveyid");
                $surveysummary .= "<li><a href='{$link}'>"
                . "<img src='".$this->config->item('imageurl')."/assessments_30.png' /> ".$clang->gT("Assessments")."</a></li>\n" ;
            }
    
            // EDIT SURVEY TEXT ELEMENTS BUTTON
            if(bHasSurveyPermission($surveyid,'surveylocale','read'))
            {
                $link = site_url("admin/emailtemplates/index/$surveyid");
                $surveysummary .= "<li><a href='{$link}' >"
                . "<img src='".$this->config->item('imageurl')."/emailtemplates_30.png' name='EditEmailTemplates' /> ".$clang->gT("Email templates")."</a></li>\n";
            }        
            
            $surveysummary .='</ul></li>'; // End if survey properties
            
            return $surveysummary;
        }
        

        function _buttonTools($surveyid)
        {
            $clang = $this->limesurvye_lang;
            // Tools menu item     
            $surveysummary = "<li><a href=\"#\">"
            . "<img src='".$this->config->item('imageurl')."/tools.png' name='SorveyTools' alt='".$clang->gT("Tools")."' /></a><ul>\n";
          
          
            // Delete survey item
            if (bHasSurveyPermission($surveyid,'survey','delete'))
            {
                //            $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=deletesurvey&amp;sid=$surveyid', '_top')\""
                //$link = site_url("admin/emailtemplates/index/$surveyid");
                $surveysummary .= "<li><a href=\"#\" onclick=\"".get2post("{$scriptname}?action=deletesurvey&amp;sid={$surveyid}")."\">"
                . "<img src='".$this->config->item('imageurl')."/delete_30.png' name='DeleteSurvey' /> ".$clang->gT("Delete survey")."</a></li>\n" ;
            }
                
                
            // Translate survey item
            if (bHasSurveyPermission($surveyid,'translations','read'))
            {
              // Check if multiple languages have been activated
              $supportedLanguages = getLanguageData(false);
              if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) > 0)
              {
                $link = site_url("admin/translate/index/$surveyid");
                $surveysummary .= "<li><a href='{$link}'>"
                . "<img src='".$this->config->item('imageurl')."/translate_30.png' /> ".$clang->gT("Quick-translation")."</a></li>\n";
              }
              else
              {
                $surveysummary .= "<li><a href=\"#\" onclick=\"alert('".$clang->gT("Currently there are no additional languages configured for this survey.", "js")."');\" >"
                . "<img src='".$this->config->item('imageurl')."/translate_disabled_30.png' /> ".$clang->gT("Quick-translation")."</a></li>\n";
              }
            }            
             
            // RESET SURVEY LOGIC BUTTON
    
            if (bHasSurveyPermission($surveyid,'surveycontent','update'))
            {
                if ($sumcount6 > 0) {
                    $surveysummary .= "<li><a href=\"#\" onclick=\"".get2post("{$scriptname}?action=resetsurveylogic&amp;sid=$surveyid")."\">"
                    . "<img src='".$this->config->item('imageurl')."/resetsurveylogic_30.png' name='ResetSurveyLogic' /> ".$clang->gT("Reset conditions")."</a></li>\n";
                }
                else
                {
                    $surveysummary .= "<li><a href=\"#\" onclick=\"alert('".$clang->gT("Currently there are no conditions configured for this survey.", "js")."');\" >"
                    . "<img src='".$this->config->item('imageurl')."/resetsurveylogic_disabled_30.png' name='ResetSurveyLogic'/> ".$clang->gT("Reset Survey Logic")."</a></li>\n";
                }
            }         
            $surveysummary .='</ul></li>' ;
            return $surveysummary;
        }


        function _buttonExport($surveyid)
        {
            $clang = $this->limesurvey_lang;
            // Display/Export main menu item     
            $surveysummary = "<li><a href='#'>"
            . "<img src='".$this->config->item('imageurl')."/display_export.png' name='DisplayExport' alt='".$clang->gT("Display / Export")."' /></a><ul>\n";
            
            // Eport menu item
            if (bHasSurveyPermission($surveyid,'surveycontent','export'))
            {
                $link = site_url("admin/exportstructure/index/$surveyid");
                $surveysummary .= "<li><a href='{$link}'>"
                . "<img src='".$this->config->item('imageurl')."/export_30.png' /> ". $clang->gT("Export survey")."</a></li>\n" ;
            }
    
            // PRINTABLE VERSION OF SURVEY BUTTON
    
            if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
            {
                $link = site_url("admin/showprintablesurvey/index/$surveyid");
                $surveysummary .= "<li><a href='{$link}'>"
                . "<img src='".$this->config->item('imageurl')."/print_30.png' name='ShowPrintableSurvey' /> ".$clang->gT("Printable version")."</a></li>";
            }
            else
            {
                $link = site_url("admin/showprintablesurvey/index/$surveyid");
                $surveysummary .= "<li><a href='{$link}'>"
                . "<img src='".$this->config->item('imageurl')."/print_30.png' name='ShowPrintableSurvey' /> ".$clang->gT("Printable version")."</a><ul>";
                $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                $baselang = GetBaseLanguageFromSurveyID($surveyid);
                $tmp_survlangs[] = $baselang;
                rsort($tmp_survlangs);
                foreach ($tmp_survlangs as $tmp_lang)
                {
                    $link = site_url("admin/showprintablesurvey/index/$surveyid/$tmp_lang");
                    $surveysummary .= "<li><a href='{$link}'><img src='".$this->config->item('imageurl')."/print_30.png' /> ".getLanguageNameFromCode($tmp_lang,false)."</a></li>";
                }
                $surveysummary.='</ul></li>';
            }
            
            
            // SHOW PRINTABLE AND SCANNABLE VERSION OF SURVEY BUTTON
    
            if(bHasSurveyPermission($surveyid,'surveycontent','export'))
            {
                if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
                {
    
                    $link = site_url("admin/showquexmlsurvey/index/$surveyid");
                    $surveysummary .= "<li><a href='{$link}'>"
                    . "<img src='".$this->config->item('imageurl')."/scanner_30.png' name='ShowPrintableScannableSurvey' /> ".$clang->gT("QueXML export")."</a></li>";
    
                } else {
    
                    $link = site_url("admin/showquexmlsurvey/index/$surveyid");
                    $surveysummary .= "<li><a href='{$link}'>"
                    . "<img src='".$this->config->item('imageurl')."/scanner_30.png' name='ShowPrintableScannableSurvey' /> ".$clang->gT("QueXML export")."</a><ul>";
    
                    $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                    $baselang = GetBaseLanguageFromSurveyID($surveyid);
                    $tmp_survlangs[] = $baselang;
                    rsort($tmp_survlangs);
    
                    // Test Survey Language Selection Popup
                    foreach ($tmp_survlangs as $tmp_lang)
                    {
                        $link = site_url("admin/showprintablesurvey/index/$surveyid/$tmp_lang");
                        $surveysummary .= "<li><a href='{$link}'>
                        <img src='".$this->config->item('imageurl')."/scanner_30.png' /> ".getLanguageNameFromCode($tmp_lang,false)."</a></li>";
                    }
                    $surveysummary .= "</ul></li>";
                }
            }      
            $surveysummary .='</ul></li>' ;
            
            
            
            // Display/Export main menu item 
            return $surveysummary;
        }
            

        function _buttonResponse($surveyid)
        {
            $clang = $this->limesurvey_lang;
            $surveysummary = "<li><a href='#'><img src='".$this->config->item('imageurl')."/responses.png' name='Responses' alt='".$clang->gT("Responses")."' /></a><ul>\n";

            //browse responses menu item
            if (bHasSurveyPermission($surveyid,'responses','read') || bHasSurveyPermission($surveyid,'statistics','read'))
            {
                if ($activated == "Y")
                {
                    $link = site_url("admin/browse/index/$surveyid");
                    $surveysummary .= "<li><a href='{$link}'>"
                    . "<img src='".$this->config->item('imageurl')."/browse_30.png' name='BrowseSurveyResults' /> ".$clang->gT("Responses & statistics")."</a></li>\n";
                }
                else
                {
                    $surveysummary .= "<li><a href='#' onclick=\"alert('".$clang->gT("This survey is not active - no responses are available.","js")."')\">"
                    . "<img src='".$this->config->item('imageurl')."/browse_disabled_30.png' name='BrowseSurveyResults' /> ".$clang->gT("Responses & statistics")."</a></li>\n";
                }
                
            }
            
            // Data entry screen menu item
            if (bHasSurveyPermission($surveyid,'responses','create'))
            {
                if($activated == "Y")
                {
                    $link = site_url("admin/dataentry/index/$surveyid");
                    $surveysummary .= "<li><a href='{$link}'>"
                    . "<img src='".$this->config->item('imageurl')."/dataentry_30.png' /> ".$clang->gT("Data entry screen")."</a></li>\n";
                }
                else {
                    $surveysummary .= "<li><a href='#' onclick=\"alert('".$clang->gT("This survey is not active, data entry is not allowed","js")."')\">"
                    . "<img src='".$this->config->item('imageurl')."/dataentry_disabled_30.png'/> ".$clang->gT("Data entry screen")."</a></li>\n";
                }        
            }        
            
            
            
            if (bHasSurveyPermission($surveyid,'responses','read'))
            {
                if ($activated == "Y")
                {
                    $link = site_url("admin/saved/index/$surveyid");
                    $surveysummary .= "<li><a href='#' onclick=\"window.open('{$link}', '_top')\" >"
                    . "<img src='".$this->config->item('imageurl')."/saved_30.png' name='BrowseSaved' /> ".$clang->gT("Partial (saved) responses")."</a></li>\n";
                }
                else
                {
                    $surveysummary .= "<li><a href='#' onclick=\"alert('".$clang->gT("This survey is not active - no responses are available.","js")."')\">"
                    . "<img src='".$this->config->item('imageurl')."/saved_disabled_30.png' name='PartialResponses' /> ".$clang->gT("Partial (saved) responses")."</a></li>\n";
                }
                
            }
    
            $surveysummary .='</ul></li>' ;
            return $surveysummary;
        }
            
        
        function _buttonToken($surveyid)
        {
            $clang = $this->limesurvey_lang;
            // TOKEN MANAGEMENT BUTTON

            if (bHasSurveyPermission($surveyid,'surveysettings','update') || bHasSurveyPermission($surveyid,'tokens','read'))
            {
             //   $surveysummary .= "<img src='$imageurl/seperator.gif' alt=''  />\n";
                $link = site_url("admin/tokens/index/$surveyid");
                $surveysummary ="<li><a href='#' onclick=\"window.open('$link', '_top')\""
                . " title=\"".$clang->gTview("Token management")."\" >"
                . "<img src='".$this->config->item('imageurl')."/tokens.png' name='TokensControl' alt='".$clang->gT("Token management")."' /></a></li>\n" ;
            }
    
     
            $surveysummary .= "</ul>";
            
            // End of survey toolbar 2nd page
            return $surveysummary;
        }
        
        function _surveysummary($surveyid,$surveyinfo,$activated,$action,$gid=false,$qid=false,$sumcount3,$sumcount2)
        {
            $clang = $this->limesurvey_lang;
            
            //SURVEY SUMMARY
            if ($gid || $qid || $action=="deactivate"|| $action=="activate" || $action=="surveysecurity"
            || $action=="surveyrights" || $action=="addsurveysecurity" || $action=="addusergroupsurveysecurity"
            || $action=="setsurveysecurity" ||  $action=="setusergroupsurveysecurity" || $action=="delsurveysecurity"
            || $action=="editsurveysettings"|| $action=="editsurveylocalesettings" || $action=="updatesurveysettingsandeditlocalesettings" || $action=="addgroup" || $action=="importgroup"
            || $action=="ordergroups" || $action=="deletesurvey" || $action=="resetsurveylogic"
            || $action=="importsurveyresources" || $action=="translate"  || $action=="emailtemplates" 
            || $action=="exportstructure" || $action=="quotas" || $action=="copysurvey") {$showstyle="style='display: none'";}
            if (!isset($showstyle)) {$showstyle="";}
            $aAdditionalLanguages = GetAdditionalLanguagesFromSurveyID($surveyid);
            $surveysummary = "<table $showstyle id='surveydetails'><tr><td align='right' valign='top' width='15%'>"
            . "<strong>".$clang->gT("Title").":</strong></td>\n"
            . "<td align='left' class='settingentryhighlight'><strong>{$surveyinfo['surveyls_title']} "
            . "(".$clang->gT("ID")." {$surveyinfo['sid']})</strong></td></tr>\n";
            $surveysummary2 = "";
            if ($surveyinfo['anonymized'] != "N") {$surveysummary2 .= $clang->gT("Answers to this survey are anonymized.")."<br />\n";}
            else {$surveysummary2 .= $clang->gT("This survey is NOT anonymous.")."<br />\n";}
            if ($surveyinfo['format'] == "S") {$surveysummary2 .= $clang->gT("It is presented question by question.")."<br />\n";}
            elseif ($surveyinfo['format'] == "G") {$surveysummary2 .= $clang->gT("It is presented group by group.")."<br />\n";}
            else {$surveysummary2 .= $clang->gT("It is presented on one single page.")."<br />\n";}
            if ($surveyinfo['allowjumps'] == "Y")
            {
              if ($surveyinfo['format'] == 'A') {$surveysummary2 .= $clang->gT("No question index will be shown with this format.")."<br />\n";}
              else {$surveysummary2 .= $clang->gT("A question index will be shown; participants will be able to jump between viewed questions.")."<br />\n";}
            }
            if ($surveyinfo['datestamp'] == "Y") {$surveysummary2 .= $clang->gT("Responses will be date stamped.")."<br />\n";}
            if ($surveyinfo['ipaddr'] == "Y") {$surveysummary2 .= $clang->gT("IP Addresses will be logged")."<br />\n";}
            if ($surveyinfo['refurl'] == "Y") {$surveysummary2 .= $clang->gT("Referrer URL will be saved.")."<br />\n";}
            if ($surveyinfo['usecookie'] == "Y") {$surveysummary2 .= $clang->gT("It uses cookies for access control.")."<br />\n";}
            if ($surveyinfo['allowregister'] == "Y") {$surveysummary2 .= $clang->gT("If tokens are used, the public may register for this survey")."<br />\n";}
            if ($surveyinfo['allowsave'] == "Y" && $surveyinfo['tokenanswerspersistence'] == 'N') {$surveysummary2 .= $clang->gT("Participants can save partially finished surveys")."<br />\n";}
            if ($surveyinfo['emailnotificationto'] != '') 
            {
                $surveysummary2 .= $clang->gT("Basic email notification is sent to:")." {$surveyinfo['emailnotificationto']}<br />\n";
            }
            if ($surveyinfo['emailresponseto'] != '') 
            {
                $surveysummary2 .= $clang->gT("Detailed email notification with response data is sent to:")." {$surveyinfo['emailresponseto']}<br />\n";
            }
    
            if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            {
                $surveysummary2 .= $clang->gT("Regenerate question codes:")
                . " [<a href='#' "
                . "onclick=\"if (confirm('".$clang->gT("Are you sure you want regenerate the question codes?","js")."')) {".get2post("$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=straight")."}\" "
                . ">".$clang->gT("Straight")."</a>] "
                . " [<a href='#' "
                . "onclick=\"if (confirm('".$clang->gT("Are you sure you want regenerate the question codes?","js")."')) {".get2post("$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=bygroup")."}\" "
                . ">".$clang->gT("By Group")."</a>]";
                $surveysummary2 .= "</td></tr>\n";
            }
            $surveysummary .= "<tr>"
            . "<td align='right' valign='top'><strong>"
            . $clang->gT("Survey URL") ." (".getLanguageNameFromCode($surveyinfo['language'],false)."):</strong></td>\n";
            
            /** front end functionality required
            if ( $modrewrite ) {
                $tmp_url = $GLOBALS['publicurl'] . '/' . $surveyinfo['sid'];
                $surveysummary .= "<td align='left'> <a href='$tmp_url/lang-".$surveyinfo['language']."' target='_blank'>$tmp_url/lang-".$surveyinfo['language']."</a>";
                foreach ($aAdditionalLanguages as $langname)
                {
                    $surveysummary .= "&nbsp;<a href='$tmp_url/lang-$langname' target='_blank'><img title='".$clang->gT("Survey URL for language:")." ".getLanguageNameFromCode($langname,false)."' alt='".getLanguageNameFromCode($langname,false)." ".$clang->gT("Flag")."' src='../images/flags/$langname.png' /></a>";
                }
            } else {
                $tmp_url = $GLOBALS['publicurl'] . '/index.php?sid=' . $surveyinfo['sid'];
                $surveysummary .= "<td align='left'> <a href='$tmp_url&amp;lang=".$surveyinfo['language']."' target='_blank'>$tmp_url&amp;lang=".$surveyinfo['language']."</a>";
                foreach ($aAdditionalLanguages as $langname)
                {
                    $surveysummary .= "&nbsp;<a href='$tmp_url&amp;lang=$langname' target='_blank'><img title='".$clang->gT("Survey URL for language:")." ".getLanguageNameFromCode($langname,false)."' alt='".getLanguageNameFromCode($langname,false)." ".$clang->gT("Flag")."' src='../images/flags/$langname.png' /></a>";
                }
            }
            */
            
            $surveysummary .= "</td></tr>\n"
            . "<tr><td align='right' valign='top'><strong>"
            . $clang->gT("Description:")."</strong></td>\n<td align='left'>";
            if (trim($surveyinfo['surveyls_description'])!='') {$surveysummary .= " {$surveyinfo['surveyls_description']}";}
            $surveysummary .= "</td></tr>\n"
            . "<tr >\n"
            . "<td align='right' valign='top'><strong>"
            . $clang->gT("Welcome:")."</strong></td>\n"
            . "<td align='left'> {$surveyinfo['surveyls_welcometext']}</td></tr>\n"
            . "<tr ><td align='right' valign='top'><strong>"
            . $clang->gT("Administrator:")."</strong></td>\n"
            . "<td align='left'> {$surveyinfo['admin']} ({$surveyinfo['adminemail']})</td></tr>\n";
            if (trim($surveyinfo['faxto'])!='')
            {
                $surveysummary .="<tr><td align='right' valign='top'><strong>"
                . $clang->gT("Fax to:")."</strong></td>\n<td align='left'>{$surveyinfo['faxto']}";
                $surveysummary .= "</td></tr>\n";
            }
            $surveysummary .= "<tr><td align='right' valign='top'><strong>"
            . $clang->gT("Start date/time:")."</strong></td>\n";
            $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
            if (trim($surveyinfo['startdate'])!= '')
            {
                $constructoritems = array($surveyinfo['startdate'] , "Y-m-d H:i:s");
                $this->load->library('Date_Time_Converter',$items);
                $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($surveyinfo['startdate'] , "Y-m-d H:i:s");
                $startdate=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
            }
            else
            {
                $startdate="-";
            }
            $surveysummary .= "<td align='left'>$startdate</td></tr>\n"
            . "<tr><td align='right' valign='top'><strong>"
            . $clang->gT("Expiry date/time:")."</strong></td>\n";
            if (trim($surveyinfo['expires'])!= '')
            {
                $constructoritems = array($surveyinfo['expires'] , "Y-m-d H:i:s");
                $this->load->library('Date_Time_Converter',$items);
                $datetimeobj = $this->date_time_converter; 
                //$datetimeobj = new Date_Time_Converter($surveyinfo['expires'] , "Y-m-d H:i:s");
                $expdate=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
            }
            else
            {
                $expdate="-";
            }
            $surveysummary .= "<td align='left'>$expdate</td></tr>\n"
            . "<tr ><td align='right' valign='top'><strong>"
            . $clang->gT("Template:")."</strong></td>\n"
            . "<td align='left'> {$surveyinfo['template']}</td></tr>\n"
    
            . "<tr><td align='right' valign='top'><strong>"
            . $clang->gT("Base language:")."</strong></td>\n";
            if (!$surveyinfo['language']) {$language=getLanguageNameFromCode($currentadminlang,false);} else {$language=getLanguageNameFromCode($surveyinfo['language'],false);}
            $surveysummary .= "<td align='left'>$language</td></tr>\n";
    
            // get the rowspan of the Additionnal languages row
            // is at least 1 even if no additionnal language is present
            $additionnalLanguagesCount = count($aAdditionalLanguages);
            $surveysummary .= "<tr><td align='right' valign='top'><strong>"
            . $clang->gT("Additional Languages").":</strong></td>\n";
            $first=true;
            if ($additionnalLanguagesCount == 0)
            {
                        $surveysummary .= "<td align='left'>-</td>\n";
            }
            else
            {
                foreach ($aAdditionalLanguages as $langname)
                {
                    if ($langname)
                    {
                        if (!$first) {$surveysummary .= "<tr><td>&nbsp;</td>";}
                        $first=false;
                        $surveysummary .= "<td align='left'>".getLanguageNameFromCode($langname,false)."</td></tr>\n";
                    }
                }
            }
            if ($first) $surveysummary .= "</tr>";
    
            if ($surveyinfo['surveyls_urldescription']==""){$surveyinfo['surveyls_urldescription']=htmlspecialchars($surveyinfo['surveyls_url']);}
            $surveysummary .= "<tr><td align='right' valign='top'><strong>"
            . $clang->gT("End URL").":</strong></td>\n"
            . "<td align='left'>";                                             
            if ($surveyinfo['surveyls_url']!="") 
            {
                $surveysummary .=" <a target='_blank' href=\"".htmlspecialchars($surveyinfo['surveyls_url'])."\" title=\"".htmlspecialchars($surveyinfo['surveyls_url'])."\">{$surveyinfo['surveyls_urldescription']}</a>";
            }
            else
            {
                $surveysummary .="-";
            }
            $surveysummary .="</td></tr>\n";
            $surveysummary .= "<tr><td align='right' valign='top'><strong>"
            . $clang->gT("Number of questions/groups").":</strong></td><td>$sumcount3/$sumcount2</td></tr>\n";
            $surveysummary .= "<tr><td align='right' valign='top'><strong>"
            . $clang->gT("Survey currently active").":</strong></td><td>";
            if ($activated == "N")
            {
                $surveysummary .= $clang->gT("No");
            }
            else
            {
                $surveysummary .= $clang->gT("Yes");
            }
            $surveysummary .="</td></tr>\n";
    
            if ($activated == "Y")
            {
                $surveysummary .= "<tr><td align='right' valign='top'><strong>"
                . $clang->gT("Survey table name").":</strong></td><td>".$this->db->dbprefix."survey_$surveyid</td></tr>\n";
            }
            $surveysummary .= "<tr><td align='right' valign='top'><strong>"
            . $clang->gT("Hints").":</strong></td><td>\n";
    
            if ($activated == "N" && $sumcount3 == 0)
            {
                $surveysummary .= $clang->gT("Survey cannot be activated yet.")."<br />\n";
                if ($sumcount2 == 0 && bHasSurveyPermission($surveyid,'surveycontent','create'))
                {
                    $surveysummary .= "<span class='statusentryhighlight'>[".$clang->gT("You need to add question groups")."]</span><br />";
                }
                if ($sumcount3 == 0 && bHasSurveyPermission($surveyid,'surveycontent','create'))
                {
                    $surveysummary .= "<span class='statusentryhighlight'>[".$clang->gT("You need to add questions")."]</span><br />";
                }
            }
            $surveysummary .=  $surveysummary2;
    
            //return (array('column'=>array($columns_used,$hard_limit) , 'size' => array($length, $size_limit) ));
            $tableusage = get_dbtableusage($surveyid);
            if ($tableusage != false){
    
                if ($tableusage['dbtype']=='mysql'){
                    $column_usage = round($tableusage['column'][0]/$tableusage['column'][1] * 100,2);
                    $size_usage =  round($tableusage['size'][0]/$tableusage['size'][1] * 100,2);
    
    
                    $surveysummary .="<tr><td align='right' valign='top'><strong>{$clang->gT("Table Column Usage")}: </strong></td><td><div class='progressbar' style='width:20%; height:15px;' name='{$column_usage}'></div> </td></tr>";
                    $surveysummary .="<tr><td align='right' valign='top'><strong>{$clang->gT("Table Size Usage")}: </strong></td><td><div class='progressbar' style='width:20%; height:15px;' name='{$size_usage}'></div></td></tr>";
                }
                elseif (($arrCols['dbtype'] == 'mssqlnative')||($arrCols['dbtype'] == 'postgres')||($arrCols['dbtype'] == 'odbtp')||($arrCols['dbtype'] == 'mssql_n')){
                    $column_usage = round($tableusage['column'][0]/$tableusage['column'][1] * 100,2);
                    $surveysummary .="<tr><td align='right' valign='top'><strong>{$clang->gT("Table Column Usage")}: </strong></td><td><strong>{$column_usage}%</strong><div class='progressbar' style='width:20%; height:15px;' name='{$column_usage}'></div> </td></tr>";
                }
                
            }
            
            $surveysummary .= "</table>\n";
            return $surveysummary;
        }
 
 
 
 
 
 
 
 
 
 }