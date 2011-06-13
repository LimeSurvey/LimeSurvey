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
 
 class survey extends AdminController {
    
    function __construct()
	{
		parent::__construct();
        self::_js_admin_includes("");
	}
    
    function index($action)
    {
        global $surveyid;
        if(!bHasSurveyPermission($surveyid,'surveysettings','read') && !bHasGlobalPermission('USER_RIGHT_CREATE_SURVEY'))
        {
            //include("access_denied.php");
        }
        $this->load->helper('surveytranslator');
        $clang = $this->limesurvey_lang;
        self::_js_admin_includes(base_url().'application/scripts/surveysettings.js');
        $esrow = array();
        $editsurvey = '';
        if ($action == "newsurvey") {
            $esrow = self::_fetchSurveyInfo('newsurvey');
            $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
        
            $editsurvey = PrepareEditorScript();
            $editsurvey .= "";
            $editsurvey .="<script type=\"text/javascript\">
                        standardtemplaterooturl='".$this->config->item('standardtemplaterooturl')."';
                        templaterooturl='".$this->config->item('usertemplaterooturl')."'; \n";
            $editsurvey .= "</script>\n";

        // header
        $editsurvey .= "<div class='header ui-widget-header'>" . $clang->gT("Create, import, or copy survey") . "</div>\n";
        } elseif ($action == "editsurveysettings") {
            $esrow = self::_fetchSurveyInfo('editsurvey');
            // header
            $editsurvey = "<div class='header ui-widget-header'>".$clang->gT("Edit survey settings")."</div>\n";
        }
        if ($action == "newsurvey") {
            $editsurvey .= self::_generalTabNewSurvey();
        } elseif ($action == "editsurveysettings") {
            $editsurvey = self::_generalTabEditSurvey($surveyid,$esrow);
        }
        
        $editsurvey .= self::_tabPresentationNavigation($esrow);
        $editsurvey .= self::_tabPublicationAccess($esrow);
        $editsurvey .= self::_tabNotificationDataManagement($esrow);
        $editsurvey .= self::_tabTokens($esrow);
        
        if ($action == "newsurvey") {
            $editsurvey .= "<input type='hidden' id='surveysettingsaction' name='action' value='insertsurvey' />\n";
        } elseif ($action == "editsurveysettings") {
            $editsurvey .= "<input type='hidden' id='surveysettingsaction' name='action' value='updatesurveysettings' />\n"
            . "<input type='hidden' name='sid' value=\"{$esrow['sid']}\" />\n"
            . "<input type='hidden' name='languageids' id='languageids' value=\"{$esrow['additional_languages']}\" />\n"
            . "<input type='hidden' name='language' value=\"{$esrow['language']}\" />\n";
        }
        $editsurvey .= "</form>";
        if ($action == "newsurvey") {
            $editsurvey .= self::_tabImport();
            $editsurvey .= self::_tabCopy();
        } elseif ($action == "editsurveysettings") {
            $editsurvey .= self::_tabResourceManagement($surveyid);
        }
        
        
        // End TAB pane
        $editsurvey .= "</div>\n";
        
        
        if ($action == "newsurvey") {
            $cond = "if (isEmpty(document.getElementById('surveyls_title'), '" . $clang->gT("Error: You have to enter a title for this survey.", 'js') . "'))";
            $editsurvey .= "<p><button onclick=\"$cond {document.getElementById('addnewsurvey').submit();}\" class='standardbtn' >" . $clang->gT("Save") . "</button></p>\n";
        } elseif ($action == "editsurveysettings") {
            $cond = "if (UpdateLanguageIDs(mylangs,'" . $clang->gT("All questions, answers, etc for removed languages will be lost. Are you sure?", "js") . "'))";
            if (bHasSurveyPermission($surveyid,'surveysettings','update'))
            {
                $editsurvey .= "<p><button onclick=\"$cond {document.getElementById('addnewsurvey').submit();}\" class='standardbtn' >" . $clang->gT("Save") . "</button></p>\n";
            }
            if (bHasSurveyPermission($surveyid,'surveylocale','read'))
            {
                $editsurvey .= "<p><button onclick=\"$cond {document.getElementById('surveysettingsaction').value = 'updatesurveysettingsandeditlocalesettings'; document.getElementById('addnewsurvey').submit();}\" class='standardbtn' >" . $clang->gT("Save & edit survey text elements") . " >></button></p>\n";
            }
        }
       	self::_js_admin_includes("");
        self::_getAdminHeader();
		self::_showadminmenu();
        //echo $editsurvey;
        $data['display'] = $editsurvey;
        $this->load->view('survey_view',$data);
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
        
    }
    
    
    function _fetchSurveyInfo($action)
    {
        if ($action == 'newsurvey')
        {
            $esrow = array();
            $esrow['active']                   = 'N';
            $esrow['allowjumps']               = 'N';   
            $esrow['format']                   = 'G'; //Group-by-group mode
            $esrow['template']                 = $this->config->item('defaulttemplate');
            $esrow['allowsave']                = 'Y';
            $esrow['allowprev']                = 'N';
            $esrow['nokeyboard']               = 'N';   
            $esrow['printanswers']             = 'N';
            $esrow['publicstatistics']         = 'N';
            $esrow['publicgraphs']             = 'N';
            $esrow['public']                   = 'Y';
            $esrow['autoredirect']             = 'N';
            $esrow['tokenlength']              = 15;
            $esrow['allowregister']            = 'N';
            $esrow['usecookie']                = 'N';
            $esrow['usecaptcha']               = 'D';
            $esrow['htmlemail']                = 'Y';
            $esrow['emailnotificationto']      = '';
            $esrow['anonymized']               = 'N';
            $esrow['datestamp']                = 'N';
            $esrow['ipaddr']                   = 'N';
            $esrow['refurl']                   = 'N';
            $esrow['tokenanswerspersistence']  = 'N';
            $esrow['alloweditaftercompletion'] = 'N';
            $esrow['assesments']               = 'N';
            $esrow['startdate']                = '';
            $esrow['savetimings']              = 'N';
            $esrow['expires']                  = '';
            $esrow['showqnumcode']             = 'X';
            $esrow['showwelcome']              = 'Y';
            $esrow['emailresponseto']          = '';
            $esrow['assessments']              = 'N';
        } elseif ($action == 'editsurvey') {
            $condition = array('sid' => $surveyid);
            $this->load->model('surveys_model');
            //$esquery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid";
            $esresult = $this->surveys_model->getAllRecords($condition); //($esquery); //Checked)
            if ($esrow = $esresult->row_array()) {
                $esrow = array_map('htmlspecialchars', $esrow);
            }
            
        }
        
        return $esrow;
    }
    
    function _generalTabNewSurvey()
    {
        global $siteadminname,$siteadminemail;
        $clang = $this->limesurvey_lang;
        
        $editsurvey = "<div id='tabs'><ul>
        <li><a href='#general'>".$clang->gT("General")."</a></li>
        <li><a href='#presentation'>".$clang->gT("Presentation & navigation")."</a></li>
        <li><a href='#publication'>".$clang->gT("Publication & access control")."</a></li>
        <li><a href='#notification'>".$clang->gT("Notification & data management")."</a></li>
        <li><a href='#tokens'>".$clang->gT("Tokens")."</a></li>
        <li><a href='#import'>".$clang->gT("Import")."</a></li>
        <li><a href='#copy'>".$clang->gT("Copy")."</a></li>
        </ul>
        \n";
        $editsurvey .= "<form class='form30' name='addnewsurvey' id='addnewsurvey' action='admin/index' method='post' onsubmit=\"alert('hi');return isEmpty(document.getElementById('surveyls_title'), '" . $clang->gT("Error: You have to enter a title for this survey.", 'js') . "');\" >\n";

        // General & Contact TAB
        $editsurvey .= "<div id='general'>\n";
        
        // Survey Language
        $editsurvey .= "<ul><li><label for='language' title='" . $clang->gT("This is the base language of your survey and it can't be changed later. You can add more languages after you have created the survey.") . "'><span class='annotationasterisk'>*</span>" . $clang->gT("Base language:") . "</label>\n"
                            . "<select id='language' name='language'>\n";
        
        foreach (getLanguageData () as $langkey2 => $langname) {
            $editsurvey .= "<option value='" . $langkey2 . "'";
            if ($this->config->item('defaultlang') == $langkey2) {
                $editsurvey .= " selected='selected'";
            }
            $editsurvey .= ">" . $langname['description'] . "</option>\n";
        }
        $editsurvey .= "</select>\n";
            
        $condition = array('users_name' => $this->session->userdata('user'));
        $fieldstoselect = array('full_name', 'email');
        $this->load->model('users_model');
        //Use the current user details for the default administrator name and email for this survey
        //$query = "SELECT full_name, email FROM " . db_table_name('users') . " WHERE users_name = " . db_quoteall($_SESSION['user']);
        $result = $this->users_model->getSomeRecords($fieldstoselect,$condition); //($query) or safe_die($connect->ErrorMsg());)
        $owner = $result->row_array();
        //Degrade gracefully to $siteadmin details if anything is missing.
        if (empty($owner['full_name']))
            $owner['full_name'] = $siteadminname;
        if (empty($owner['email']))
            $owner['email'] = $siteadminemail;
        //Bounce setting by default to global if it set globally
        $this->load->helper('globalsettings');
        if (getGlobalSetting('bounceaccounttype')!='off'){
            $owner['bounce_email']         = getGlobalSetting('siteadminbounce');
        } else {
            $owner['bounce_email']        = $owner['email'];
        }
        $editsurvey .= "<span class='annotation'> " . $clang->gT("*This setting cannot be changed later!") . "</span></li>\n";
        $action = "newsurvey";
        $editsurvey .= ""
                    . "<li><label for='surveyls_title'><span class='annotationasterisk'>*</span>" . $clang->gT("Title") . ":</label>\n"
                    . "<input type='text' size='82' maxlength='200' id='surveyls_title' name='surveyls_title' /> <span class='annotation'>" . $clang->gT("*Required") . "</span></li>\n"
                    . "<li><label for='description'>" . $clang->gT("Description:") . "</label>\n"
                    . "<textarea cols='80' rows='10' id='description' name='description'></textarea>"
                    . getEditor("survey-desc", "description", "[" . $clang->gT("Description:", "js") . "]", '', '', '', $action)
                    . "</li>\n"
                    . "<li><label for='welcome'>" . $clang->gT("Welcome message:") . "</label>\n"
                    . "<textarea cols='80' rows='10' id='welcome' name='welcome'></textarea>"
                    . getEditor("survey-welc", "welcome", "[" . $clang->gT("Welcome message:", "js") . "]", '', '', '', $action)
                    . "</li>\n"
                    . "<li><label for='endtext'>" . $clang->gT("End message:") . "</label>\n"
                    . "<textarea cols='80' id='endtext' rows='10' name='endtext'></textarea>"
                    . getEditor("survey-endtext", "endtext", "[" . $clang->gT("End message:", "js") . "]", '', '', '', $action)
                    . "</li>\n";
        
            // End URL
            $editsurvey .= "<li><label for='url'>" . $clang->gT("End URL:") . "</label>\n"
                    . "<input type='text' size='50' id='url' name='url' value='http://";
            $editsurvey .= "' /></li>\n";

            // URL description
            $editsurvey.= "<li><label for='urldescrip'>" . $clang->gT("URL description:") . "</label>\n"
                    . "<input type='text' maxlength='255' size='50' id='urldescrip' name='urldescrip' value='";
            $editsurvey .= "' /></li>\n"

                    //Default date format
                    . "<li><label for='dateformat'>" . $clang->gT("Date format:") . "</label>\n"
                    . "<select size='1' id='dateformat' name='dateformat'>\n";
            foreach (getDateFormatData () as $index => $dateformatdata) {
                $editsurvey.= "<option value='{$index}'";
                $editsurvey.= ">" . $dateformatdata['dateformat'] . '</option>';
            }
            $editsurvey.= "</select></li>"

                    . "<li><label for='admin'>" . $clang->gT("Administrator:") . "</label>\n"
                    . "<input type='text' size='50' id='admin' name='admin' value='" . $owner['full_name'] . "' /></li>\n"
                    . "<li><label for='adminemail'>" . $clang->gT("Admin Email:") . "</label>\n"
                    . "<input type='text' size='50' id='adminemail' name='adminemail' value='" . $owner['email'] . "' /></li>\n"
                    . "<li><label for='bounce_email'>" . $clang->gT("Bounce Email:") . "</label>\n"
                    . "<input type='text' size='50' id='bounce_email' name='bounce_email' value='" . $owner['bounce_email'] . "' /></li>\n"
                    . "<li><label for='faxto'>" . $clang->gT("Fax to:") . "</label>\n"
                    . "<input type='text' size='50' id='faxto' name='faxto' /></li>\n";

            $editsurvey.= "</ul>";


            // End General TAB
            $editsurvey .= "</div>\n";
            return $editsurvey;
    }
        
    function _generalTabEditSurvey($surveyid,$esrow)
    {
        global $siteadminname,$siteadminemail;
        $clang = $this->limesurvey_lang;
            
        $editsurvey = "<div id='tabs'><ul>
        <li><a href='#general'>".$clang->gT("General")."</a></li>
        <li><a href='#presentation'>".$clang->gT("Presentation & navigation")."</a></li>
        <li><a href='#publication'>".$clang->gT("Publication & access control")."</a></li>
        <li><a href='#notification'>".$clang->gT("Notification & data management")."</a></li>
        <li><a href='#tokens'>".$clang->gT("Tokens")."</a></li>
        <li><a href='#resources'>".$clang->gT("Resources")."</a></li>
        </ul>
        \n";
        $editsurvey .= "<form class='form30' name='addnewsurvey' id='addnewsurvey' action='admin/index' method='post' onsubmit=\"alert('hi');return isEmpty(document.getElementById('surveyls_title'), '" . $clang->gT("Error: You have to enter a title for this survey.", 'js') . "');\" >\n";

        // General & Contact TAB
        $editsurvey .= "<div id='general'>\n";

        // Base language
        $editsurvey .= "<ul><li><label>" . $clang->gT("Base language:") . "</label>\n"
        .GetLanguageNameFromCode($esrow['language'])
        . "</li>\n"

        // Additional languages listbox
        . "<li><label for='additional_languages'>".$clang->gT("Additional Languages").":</label>\n"
        . "<table><tr><td align='left'><select style='min-width:220px;' size='5' id='additional_languages' name='additional_languages'>";
        $jsX=0;
        $jsRemLang ="<script type=\"text/javascript\">
                    var mylangs = new Array();
                    standardtemplaterooturl='".$this->config->item('standardtemplaterooturl')."';
                    templaterooturl='".$this->config->item('usertemplaterooturl')."'; \n";

        foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname) {
            if ($langname && $langname != $esrow['language']) { // base languag must not be shown here
            $jsRemLang .="mylangs[$jsX] = \"$langname\"\n";
            $editsurvey .= "<option id='".$langname."' value='".$langname."'";
            $editsurvey .= ">".getLanguageNameFromCode($langname,false)."</option>\n";
            $jsX++;
            }
        }
        $jsRemLang .= "</script>\n";
        $editsurvey .= $jsRemLang;
        //  Add/Remove Buttons
        $editsurvey .= "</select></td>"
        . "<td align='left'><input type=\"button\" value=\"<< ".$clang->gT("Add")."\" onclick=\"DoAdd()\" id=\"AddBtn\" /><br /> <input type=\"button\" value=\"".$clang->gT("Remove")." >>\" onclick=\"DoRemove(0,'')\" id=\"RemoveBtn\"  /></td>\n"

        // Available languages listbox
        . "<td align='left'><select size='5' style='min-width:220px;' id='available_languages' name='available_languages'>";
        $tempLang=GetAdditionalLanguagesFromSurveyID($surveyid);
        foreach (getLanguageData () as $langkey2 => $langname) {
            if ($langkey2 != $esrow['language'] && in_array($langkey2, $tempLang) == false) {  // base languag must not be shown here
                $editsurvey .= "<option id='".$langkey2."' value='".$langkey2."'";
                $editsurvey .= ">".$langname['description']."</option>\n";
            }
        }
        $editsurvey .= "</select></td>"
        . " </tr></table></li>\n";
        // Administrator...
        $editsurvey .= ""
        . "<li><label for='admin'>".$clang->gT("Administrator:")."</label>\n"
        . "<input type='text' size='50' id='admin' name='admin' value=\"{$esrow['admin']}\" /></li>\n"
        . "<li><label for='adminemail'>".$clang->gT("Admin Email:")."</label>\n"
        . "<input type='text' size='50' id='adminemail' name='adminemail' value=\"{$esrow['adminemail']}\" /></li>\n"
        . "<li><label for='bounce_email'>".$clang->gT("Bounce Email:")."</label>\n"
        . "<input type='text' size='50' id='bounce_email' name='bounce_email' value=\"{$esrow['bounce_email']}\" /></li>\n"
        . "<li><label for='faxto'>".$clang->gT("Fax to:")."</label>\n"
        . "<input type='text' size='50' id='faxto' name='faxto' value=\"{$esrow['faxto']}\" /></li></ul>\n";

        // End General TAB
        $editsurvey .= "</div>\n";
        
        
    }
    
    function _tabPresentationNavigation($esrow)
    {
        $clang = $this->limesurvey_lang;
        global $showXquestions,$showgroupinfo,$showqnumcode;
        
        // Presentation and navigation TAB
        $editsurvey = "<div id='presentation'><ul>\n";

        //Format
        $editsurvey .= "<li><label for='format'>".$clang->gT("Format:")."</label>\n"
        . "<select id='format' name='format'>\n"
        . "<option value='S'";
        if ($esrow['format'] == "S" || !$esrow['format']) {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Question by Question")."</option>\n"
            . "<option value='G'";
        if ($esrow['format'] == "G") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Group by Group")."</option>\n"
            . "<option value='A'";
        if ($esrow['format'] == "A") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("All in one")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //TEMPLATES
            $editsurvey .= "<li><label for='template'>".$clang->gT("Template:")."</label>\n"
            . "<select id='template' name='template'>\n";
        foreach (array_keys(gettemplatelist()) as $tname) {

            if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $this->session->userdata('USER_RIGHT_MANAGE_TEMPLATE') == 1 || hasTemplateManageRights($this->session->userdata("loginID"), $tname) == 1) {
                    $editsurvey .= "<option value='$tname'";
                if ($esrow['template'] && htmlspecialchars($tname) == $esrow['template']) {
                    $editsurvey .= " selected='selected'";
                } elseif (!$esrow['template'] && $tname == "default") {
                    $editsurvey .= " selected='selected'";
                }
                    $editsurvey .= ">$tname</option>\n";
                }
            }
            $editsurvey .= "</select>\n"
            . "</li>\n";

            $editsurvey .= "<li><label for='preview'>".$clang->gT("Template Preview:")."</label>\n"
            . "<img alt='".$clang->gT("Template preview image")."' id='preview' src='".sGetTemplateURL($esrow['template'])."/preview.png' />\n"
            . "</li>\n" ;

        //SHOW WELCOMESCRN
        $editsurvey .= "<li><label for='showwelcome'>" . $clang->gT("Show welcome screen?") . "</label>\n"
                . "<select id='showwelcome' name='showwelcome'>\n"
                . "<option value='Y'";
        if (!$esrow['showwelcome'] || $esrow['showwelcome'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">" . $clang->gT("Yes") . "</option>\n"
                . "<option value='N'";
        if ($esrow['showwelcome'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">" . $clang->gT("No") . "</option>\n"
                . "</select></li>\n";

        //Navigation Delay
        if (!isset($esrow['navigationdelay'])) 
        {
            $esrow['navigationdelay']=0;
        }
        $editsurvey .= "<li><label for='navigationdelay'>".$clang->gT("Navigation delay (seconds):")."</label>\n"
        . "<input type='text' value=\"{$esrow['navigationdelay']}\" name='navigationdelay' id='navigationdelay' size='12' maxlength='2' onkeypress=\"return goodchars(event,'0123456789')\" />\n"
        . "</li>\n";

        //Show Prev Button
        $editsurvey .= "<li><label for='allowprev'>".$clang->gT("Show [<< Prev] button")."</label>\n"
        . "<select id='allowprev' name='allowprev'>\n"
        . "<option value='Y'";
        if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

        //Show Question Index
        $editsurvey .= "<li><label for='allowjumps'>".$clang->gT("Show question index / allow jumping")."</label>\n"
                . "<select id='allowjumps' name='allowjumps'>\n"
                . "<option value='Y'";
        if (!isset($esrow['allowjumps']) || !$esrow['allowjumps'] || $esrow['allowjumps'] == "Y") {$editsurvey .= " selected='selected'";}
        $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
        if (isset($esrow['allowjumps']) && $esrow['allowjumps'] == "N") {$editsurvey .= " selected='selected'";}
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select></li>\n";

        //No Keyboard
        $editsurvey .= "<li><label for='nokeyboard'>".$clang->gT("Keyboard-less operation")."</label>\n"
                . "<select id='nokeyboard' name='nokeyboard'>\n"
                . "<option value='Y'";
        if (!isset($esrow['nokeyboard']) || !$esrow['nokeyboard'] || $esrow['nokeyboard'] == "Y") {$editsurvey .= " selected='selected'";}
        $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
        if (isset($esrow['nokeyboard']) && $esrow['nokeyboard'] == "N") {$editsurvey .= " selected='selected'";}
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select></li>\n";

	//Show Progress
	$editsurvey .= "<li><label for='showprogress'>".$clang->gT("Show progress bar")."</label>\n"
                . "<select id='showprogress' name='showprogress'>\n"
                . "<option value='Y'";
	if (!isset($esrow['showprogress']) || !$esrow['showprogress'] || $esrow['showprogress'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
	$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
	if (isset($esrow['showprogress']) && $esrow['showprogress'] == "N") {
            $editsurvey .= " selected='selected'";
        }
	$editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select></li>\n";

            //Result printing
            $editsurvey .= "<li><label for='printanswers'>".$clang->gT("Participants may print answers?")."</label>\n"
            . "<select id='printanswers' name='printanswers'>\n"
            . "<option value='Y'";
        if (!isset($esrow['printanswers']) || !$esrow['printanswers'] || $esrow['printanswers'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['printanswers']) && $esrow['printanswers'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //Public statistics
            $editsurvey .= "<li><label for='publicstatistics'>".$clang->gT("Public statistics?")."</label>\n"
            . "<select id='publicstatistics' name='publicstatistics'>\n"
            . "<option value='Y'";
        if (!isset($esrow['publicstatistics']) || !$esrow['publicstatistics'] || $esrow['publicstatistics'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['publicstatistics']) && $esrow['publicstatistics'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //Public statistics
            $editsurvey .= "<li><label for='publicgraphs'>".$clang->gT("Show graphs in public statistics?")."</label>\n"
            . "<select id='publicgraphs' name='publicgraphs'>\n"
            . "<option value='Y'";
        if (!isset($esrow['publicgraphs']) || !$esrow['publicgraphs'] || $esrow['publicgraphs'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['publicgraphs']) && $esrow['publicgraphs'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";


            // End URL block
            $editsurvey .= "<li><label for='autoredirect'>".$clang->gT("Automatically load URL when survey complete?")."</label>\n"
            . "<select id='autoredirect' name='autoredirect'>";
            $editsurvey .= "<option value='Y'";
        if (isset($esrow['autoredirect']) && $esrow['autoredirect'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n";
            $editsurvey .= "<option value='N'";
        if (!isset($esrow['autoredirect']) || $esrow['autoredirect'] != "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>";

            // Show {THEREAREXQUESTIONS} block
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_showXquestions\">".$clang->gT('Show "There are X questions in this survey"')."</label>\n\t\t".'<input type="hidden" name="showXquestions" id="" value="';
	    $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_showXquestions" id="dis_showXquestions" disabled="disabled" value="';
	    $show_dis_post = "\" size=\"70\" />\n\t</li>\n";
        switch ($showXquestions) {
		case 'show':
		    $editsurvey .= $show_dis_pre.'Y'.$show_dis_mid.$clang->gT('Yes (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'hide':
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('No (Forced by the system administrator)').$show_dis_post;
		    break;
	    	case 'choose':
		default:
		    $sel_showxq = array( 'Y' => '' , 'N' => '' );
                if (isset($esrow['showXquestions'])) {
		    	$set_showxq = $esrow['showXquestions'];
			$sel_showxq[$set_showxq] = ' selected="selected"';
		    }
                if (empty($sel_showxq['Y']) && empty($sel_showxq['N'])) {
		    	$sel_showxq['Y'] = ' selected="selected"';
		    };
		    $editsurvey .= "\n\t<li>\n\t\t<label for=\"showXquestions\">".$clang->gT('Show "There are X questions in this survey"')."</label>\n\t\t"
		    . "<select id=\"showXquestions\" name=\"showXquestions\">\n\t\t\t"
		    . '<option value="Y"'.$sel_showxq['Y'].'>'.$clang->gT('Yes')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showxq['N'].'>'.$clang->gT('No')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    unset($sel_showxq,$set_showxq);
		    break;
	    };

            // Show {GROUPNAME} and/or {GROUPDESCRIPTION} block
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_showgroupinfo\">".$clang->gT('Show group name and/or group description')."</label>\n\t\t".'<input type="hidden" name="showgroupinfo" id="showgroupinfo" value="';
            $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_showgroupinfo" id="dis_showgroupinfo" disabled="disabled" value="';
        switch ($showgroupinfo) {
		case 'both':
		    $editsurvey .= $show_dis_pre.'B'.$show_dis_mid.$clang->gT('Show both (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'name':
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('Show group name only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'description':
		    $editsurvey .= $show_dis_pre.'D'.$show_dis_mid.$clang->gT('Show group description only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'none':
		    $editsurvey .= $show_dis_pre.'X'.$show_dis_mid.$clang->gT('Hide both (Forced by the system administrator)').$show_dis_post;
		    break;
	    	case 'choose':
		default:
		    $sel_showgri = array( 'B' => '' , 'D' => '' , 'N' => '' , 'X' => '' );
                if (isset($esrow['showgroupinfo'])) {
		    	$set_showgri = $esrow['showgroupinfo'];
                $sel_showgri[$set_showgri] = ' selected="selected"';
		    }
                if (empty($sel_showgri['B']) && empty($sel_showgri['D']) && empty($sel_showgri['N']) && empty($sel_showgri['X'])) {
		    	$sel_showgri['C'] = ' selected="selected"';
		    };
		    $editsurvey .= "\n\t<li>\n\t\t<label for=\"showgroupinfo\">".$clang->gT('Show group name and/or group description')."</label>\n\t\t"
		    . "<select id=\"showgroupinfo\" name=\"showgroupinfo\">\n\t\t\t"
		    . '<option value="B"'.$sel_showgri['B'].'>'.$clang->gT('Show both')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showgri['N'].'>'.$clang->gT('Show group name only')."</option>\n\t\t\t"
		    . '<option value="D"'.$sel_showgri['D'].'>'.$clang->gT('Show group description only')."</option>\n\t\t\t"
		    . '<option value="X"'.$sel_showgri['X'].'>'.$clang->gT('Hide both')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    unset($sel_showgri,$set_showgri);
		    break;
	    };

            // Show {QUESTION_CODE} and/or {QUESTION_NUMBER} block
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_showqnumcode\">".$clang->gT('Show question number and/or code')."</label>\n\t\t".'<input type="hidden" name="showqnumcode" id="showqnumcode" value="';
            $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_showqnumcode" id="dis_showqnumcode" disabled="disabled" value="';
        switch ($showqnumcode) {
		case 'none':
		    $editsurvey .= $show_dis_pre.'X'.$show_dis_mid.$clang->gT('Hide both (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'number':
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('Show question number only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'code':
		    $editsurvey .= $show_dis_pre.'C'.$show_dis_mid.$clang->gT('Show question code only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'both':
		    $editsurvey .= $show_dis_pre.'B'.$show_dis_mid.$clang->gT('Show both (Forced by the system administrator)').$show_dis_post;
		    break;
	    	case 'choose':
		default:
		    $sel_showqnc = array( 'B' => '' , 'C' => '' , 'N' => '' , 'X' => '' );
                if (isset($esrow['showqnumcode'])) {
		    	$set_showqnc = $esrow['showqnumcode'];
			$sel_showqnc[$set_showqnc] = ' selected="selected"';
		    }
                if (empty($sel_showqnc['B']) && empty($sel_showqnc['C']) && empty($sel_showqnc['N']) && empty($sel_showqnc['X'])) {
		    	$sel_showqnc['X'] = ' selected="selected"';
		    };
		    $editsurvey .= "\n\t<li>\n\t\t<label for=\"showqnumcode\">".$clang->gT('Show question number and/or code')."</label>\n\t\t"
		    . "<select id=\"showqnumcode\" name=\"showqnumcode\">\n\t\t\t"
		    . '<option value="B"'.$sel_showqnc['B'].'>'.$clang->gT('Show both')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showqnc['N'].'>'.$clang->gT('Show question number only')."</option>\n\t\t\t"
		    . '<option value="C"'.$sel_showqnc['C'].'>'.$clang->gT('Show question code only')."</option>\n\t\t\t"
		    . '<option value="X"'.$sel_showqnc['X'].'>'.$clang->gT('Hide both')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    unset($sel_showqnc,$set_showqnc);
		    break;
	    };

            // Show "No Answer" block
	    $shownoanswer = isset($shownoanswer)?$shownoanswer:'Y';
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_shownoanswer\">".$clang->gT('Show "No answer"')."</label>\n\t\t".'<input type="hidden" name="shownoanswer" id="shownoanswer" value="';
            $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_shownoanswer" id="dis_shownoanswer" disabled="disabled" value="';
        switch ($shownoanswer) {
	    	case 0:
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('Off (Forced by the system administrator)').$show_dis_post;
		    break;
	        case 2:
		    $sel_showno = array( 'Y' => '' , 'N' => '' );
                if (isset($esrow['shownoanswer'])) {
		    	$set_showno = $esrow['shownoanswer'];
			$sel_showno[$set_showno] = ' selected="selected"';
		    };
                if (empty($sel_showno)) {
		    	$sel_showno['Y'] = ' selected="selected"';
		    };
	    	    $editsurvey .= "\n\t<li>\n\t\t<label for=\"shownoanswer\">".$clang->gT('Show "No answer"')."</label>\n\t\t"
		    . "<select id=\"shownoanswer\" name=\"shownoanswer\">\n\t\t\t"
		    . '<option value="Y"'.$sel_showno['Y'].'>'.$clang->gT('Yes')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showno['N'].'>'.$clang->gT('No')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    break;
		default:
		    $editsurvey .= $show_dis_pre.'Y'.$show_dis_mid.$clang->gT('On (Forced by the system administrator)').$show_dis_post;
		    break;
	    };

            // End Presention and navigation TAB
            $editsurvey .= "</ul></div>\n";
        return $editsurvey;
    }
    
    function _tabPublicationAccess($esrow)
    {
        $clang = $this->limesurvey_lang;
        $editsurvey = "<div id='publication'><ul>\n";

             //Public Surveys
            $editsurvey .= "<li><label for='public'>".$clang->gT("List survey publicly:")."</label>\n"
            . "<select id='public' name='public'>\n"
            . "<option value='Y'";
        if (!isset($esrow['listpublic']) || !$esrow['listpublic'] || $esrow['listpublic'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['listpublic']) && $esrow['listpublic'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            // Self registration
            $editsurvey .= "<li><label for='allowregister'>".$clang->gT("Allow public registration?")."</label>\n"
            . "<select id='allowregister' name='allowregister'>\n"
            . "<option value='Y'";
        if ($esrow['allowregister'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['allowregister'] != "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

            // Start date
            $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
            $startdate='';
        if (trim($esrow['startdate']) != '') {
                $items = array($esrow['startdate'] , "Y-m-d H:i:s");
                $this->load->library('Date_Time_Converter',$items);
                $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($esrow['startdate'] , "Y-m-d H:i:s");
                $startdate=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
            }

            $editsurvey .= "<li><label for='startdate'>".$clang->gT("Start date/time:")."</label>\n"
            . "<input type='text' class='popupdatetime' id='startdate' size='20' name='startdate' value=\"{$startdate}\" /></li>\n";

            // Expiration date
            $expires='';
        if (trim($esrow['expires']) != '') {
                $items = array($esrow['expires'] , "Y-m-d H:i:s");
                $this->load->library('Date_Time_Converter',$items);
                $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($esrow['expires'] , "Y-m-d H:i:s");
                $expires=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
            }
            $editsurvey .="<li><label for='expires'>".$clang->gT("Expiry date/time:")."</label>\n"
            . "<input type='text' class='popupdatetime' id='expires' size='20' name='expires' value=\"{$expires}\" /></li>\n";

            //COOKIES
            $editsurvey .= "<li><label for=''>".$clang->gT("Set cookie to prevent repeated participation?")."</label>\n"
            . "<select name='usecookie'>\n"
            . "<option value='Y'";
        if ($esrow['usecookie'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['usecookie'] != "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            // Use Captcha
            $editsurvey .= "<li><label for=''>".$clang->gT("Use CAPTCHA for").":</label>\n"
            . "<select name='usecaptcha'>\n"
            . "<option value='A'";
        if ($esrow['usecaptcha'] == "A") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='B'";
        if ($esrow['usecaptcha'] == "B") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ---------</option>\n"
            . "<option value='C'";
        if ($esrow['usecaptcha'] == "C") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("Survey Access")." / ------------ / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='D'";
        if ($esrow['usecaptcha'] == "D") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">------------- / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='X'";

        if ($esrow['usecaptcha'] == "X") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("Survey Access")." / ------------ / ---------</option>\n"
            . "<option value='R'";
        if ($esrow['usecaptcha'] == "R") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">------------- / ".$clang->gT("Registration")." / ---------</option>\n"
            . "<option value='S'";
        if ($esrow['usecaptcha'] == "S") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">------------- / ------------ / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='N'";
        if ($esrow['usecaptcha'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">------------- / ------------ / ---------</option>\n"
            . "</select>\n</li>\n";

            // Email format
            $editsurvey .= "<li><label for=''>".$clang->gT("Use HTML format for token emails?")."</label>\n"
            . "<select name='htmlemail' onchange=\"alert('".$clang->gT("If you switch email mode, you'll have to review your email templates to fit the new format","js")."');\">\n"
            . "<option value='Y'";
        if ($esrow['htmlemail'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['htmlemail'] == "N") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

            // End Publication and access control TAB
            $editsurvey .= "</ul></div>\n";
        return $editsurvey;
        
    }
    
    function _tabNotificationDataManagement($esrow)
    {
        $clang = $this->limesurvey_lang;
        // Notification and Data management TAB
            $editsurvey = "<div id='notification'><ul>\n";


            //NOTIFICATION
            $editsurvey .= "<li><label for='emailnotificationto'>".$clang->gT("Send basic admin notification email to:")."</label>\n"
            . "<input size='70' type='text' value=\"{$esrow['emailnotificationto']}\" id='emailnotificationto' name='emailnotificationto' />\n"
            . "</li>\n";

            //EMAIL SURVEY RESPONSES TO
            $editsurvey .= "<li><label for='emailresponseto'>".$clang->gT("Send detailed admin notification email to:")."</label>\n"
            . "<input size='70' type='text' value=\"{$esrow['emailresponseto']}\" id='emailresponseto' name='emailresponseto' />\n"
            . "</li>\n";

            //ANONYMOUS
            $editsurvey .= "<li><label for=''>".$clang->gT("Anonymized responses?")."\n";
            // warning message if anonymous + tokens used
            $editsurvey .= "\n"
            . "<script type=\"text/javascript\"><!-- \n"
            . "function alertPrivacy()\n"
            . "{\n"
            . "if (document.getElementById('tokenanswerspersistence').value == 'Y')\n"
            . "{\n"
            . "alert('".$clang->gT("You can't use Anonymized responses when Token-based answers persistence is enabled.","js")."');\n"
            . "document.getElementById('anonymized').value = 'N';\n"
            . "}\n"
            . "else if (document.getElementById('anonymized').value == 'Y')\n"
            . "{\n"
            . "alert('".$clang->gT("Warning").": ".$clang->gT("If you turn on the -Anonymized responses- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.","js")."');\n"
            . "}\n"
            . "}"
            . "//--></script></label>\n";

        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['anonymized'] == "N") {
                $editsurvey .= " " . $clang->gT("This survey is NOT anonymous.");
            } else {
                $editsurvey .= $clang->gT("Answers to this survey are anonymized.");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='anonymized' value=\"{$esrow['anonymized']}\" />\n";
        } else {
                $editsurvey .= "<select id='anonymized' name='anonymized' onchange='alertPrivacy();'>\n"
                . "<option value='Y'";
            if ($esrow['anonymized'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['anonymized'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";

            // date stamp
            $editsurvey .= "<li><label for='datestamp'>".$clang->gT("Date Stamp?")."</label>\n";
        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['datestamp'] != "Y") {
                $editsurvey .= " " . $clang->gT("Responses will not be date stamped.");
            } else {
                $editsurvey .= $clang->gT("Responses will be date stamped.");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='datestamp' value=\"{$esrow['datestamp']}\" />\n";
        } else {
                $editsurvey .= "<select id='datestamp' name='datestamp' onchange='alertPrivacy();'>\n"
                . "<option value='Y'";
            if ($esrow['datestamp'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['datestamp'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";

            // Ip Addr
            $editsurvey .= "<li><label for=''>".$clang->gT("Save IP Address?")."</label>\n";

        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['ipaddr'] != "Y") {
                $editsurvey .= " " . $clang->gT("Responses will not have the IP address logged.");
            } else {
                $editsurvey .= $clang->gT("Responses will have the IP address logged");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='ipaddr' value='".$esrow['ipaddr']."' />\n";
        } else {
                $editsurvey .= "<select name='ipaddr'>\n"
                . "<option value='Y'";
            if ($esrow['ipaddr'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['ipaddr'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }

            $editsurvey .= "</li>\n";

            // begin REF URL Block
            $editsurvey .= "<li><label for=''>".$clang->gT("Save referrer URL?")."</label>\n";

        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['refurl'] != "Y") {
                $editsurvey .= " " . $clang->gT("Responses will not have their referring URL logged.");
            } else {
                $editsurvey .= $clang->gT("Responses will have their referring URL logged.");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='refurl' value='".$esrow['refurl']."' />\n";
        } else {
                $editsurvey .= "<select name='refurl'>\n"
                . "<option value='Y'";
            if ($esrow['refurl'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['refurl'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";
            // BENBUN - END REF URL Block

            // Enable assessments
            $editsurvey .= "<li><label for=''>".$clang->gT("Enable assessment mode?")."</label>\n"
            . "<select id='assessments' name='assessments'>\n"
            . "<option value='Y'";
        if ($esrow['assessments'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['assessments'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

            // Allow editing answers after completion 
            $editsurvey .= "<li><label for=''>".$clang->gT("Allow editing answers after completion?")."</label>\n"
            . "<select id='alloweditaftercompletion' name='alloweditaftercompletion' onchange=\"javascript: if (document.getElementById('private').value == 'Y') {alert('".$clang->gT("This option can't be set if Anonymous answers are used","js")."'); this.value='N';}\">\n"
            . "<option value='Y'";
            if ($esrow['alloweditaftercompletion'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if ($esrow['alloweditaftercompletion'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

        // Save timings
        $editsurvey .= "<li><label for='savetimings'>".$clang->gT("Save timings?")."</label>\n";
        if ($esrow['active']=="Y")
        {
            $editsurvey .= "\n";
            if ($esrow['savetimings'] != "Y") {$editsurvey .= " ".$clang->gT("Timings will not be saved.");}
            else {$editsurvey .= $clang->gT("Timings will be saved.");}
            $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
            . "</font>\n";
            $editsurvey .= "<input type='hidden' name='savetimings' value='".$esrow['savetimings']."' />\n";
		}
		else
        {
			$editsurvey .= "<select id='savetimings' name='savetimings'>\n"
			. "<option value='Y'";
			if (!isset($esrow['savetimings']) || !$esrow['savetimings'] || $esrow['savetimings'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "<option value='N'";
			if (isset($esrow['savetimings']) && $esrow['savetimings'] == "N") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "</select>\n"
			. "</li>\n";
		}
        //ALLOW SAVES
        $editsurvey .= "<li><label for='allowsave'>".$clang->gT("Participant may save and resume later?")."</label>\n"
        . "<select id='allowsave' name='allowsave'>\n"
        . "<option value='Y'";
        if (!$esrow['allowsave'] || $esrow['allowsave'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['allowsave'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></li>\n";


        // End Notification and Data management TAB
        $editsurvey .= "</ul></div>\n";
        return $editsurvey;
    }
    
    function _tabTokens($esrow)
    {
        $clang = $this->limesurvey_lang;
        // Tokens TAB
        $editsurvey = "<div id='tokens'><ul>\n";
        // Token answers persistence
        $editsurvey .= "<li><label for=''>".$clang->gT("Enable token-based response persistence?")."</label>\n"
        . "<select id='tokenanswerspersistence' name='tokenanswerspersistence' onchange=\"javascript: if (document.getElementById('anonymized').value == 'Y') {alert('".$clang->gT("This option can't be set if the `Anonymized responses` option is active.","js")."'); this.value='N';}\">\n"
        . "<option value='Y'";
        if ($esrow['tokenanswerspersistence'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
        . "<option value='N'";
        if ($esrow['tokenanswerspersistence'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        //Set token length
        $editsurvey .= "<li><label for='tokenlength'>".$clang->gT("Set token length to:")."</label>\n"
        . "<input type='text' value=\"{$esrow['tokenlength']}\" name='tokenlength' id='tokenlength' size='12' maxlength='2' onkeypress=\"return goodchars(event,'0123456789')\" />\n"
        . "</li>\n";

        // End Tokens TAB
        $editsurvey .= "</ul></div>\n";
        return $editsurvey;
    }
    
    function _tabImport()
    {
        $clang = $this->limesurvey_lang;
        // Import TAB
        $editsurvey = "<div id='import'>\n";

        // Import survey
        $editsurvey .= "<form enctype='multipart/form-data' class='form30' id='importsurvey' name='importsurvey' action='admin/index' method='post' onsubmit='return validatefilename(this,\"" . $clang->gT('Please select a file to import!', 'js') . "\");'>\n"
                    . "<ul>\n"
                    . "<li><label for='the_file'>" . $clang->gT("Select survey structure file (*.lss, *.csv):") . "</label>\n"
                    . "<input id='the_file' name=\"the_file\" type=\"file\" size=\"50\" /></li>\n"
                    . "<li><label for='translinksfields'>" . $clang->gT("Convert resource links and INSERTANS fields?") . "</label>\n"
                    . "<input id='translinksfields' name=\"translinksfields\" type=\"checkbox\" checked='checked'/></li></ul>\n"
                    . "<p><input type='submit' value='" . $clang->gT("Import survey") . "' />\n"
                    . "<input type='hidden' name='action' value='importsurvey' /></p></form>\n";

        // End Import TAB
        $editsurvey .= "</div>\n";
        return $editsurvey;
    }
    
    function _tabCopy()
    {
        $clang = $this->limesurvey_lang;
        
        // Copy survey TAB
        $editsurvey = "<div id='copy'>\n";

        // Copy survey
        $editsurvey .= "<form class='form30' action='admin/index' id='copysurveyform' method='post'>\n"
                    . "<ul>\n"
                    . "<li><label for='copysurveylist'><span class='annotationasterisk'>*</span>" . $clang->gT("Select survey to copy:") . "</label>\n"
                    . "<select id='copysurveylist' name='copysurveylist'>\n"
                    . getsurveylist(false, true) . "</select> <span class='annotation'>" . $clang->gT("*Required") . "</span></li>\n"
                    . "<li><label for='copysurveyname'><span class='annotationasterisk'>*</span>" . $clang->gT("New survey title:") . "</label>\n"
                    . "<input type='text' id='copysurveyname' size='82' maxlength='200' name='copysurveyname' value='' />"
                    . "<span class='annotation'>" . $clang->gT("*Required") . "</span></li>\n"
                    . "<li><label for='copysurveytranslinksfields'>" . $clang->gT("Convert resource links and INSERTANS fields?") . "</label>\n"
                    . "<input id='copysurveytranslinksfields' name=\"copysurveytranslinksfields\" type=\"checkbox\" checked='checked'/></li>\n"
                    . "<li><label for='copysurveyexcludequotas'>" . $clang->gT("Exclude quotas?") . "</label>\n"
                    . "<input id='copysurveyexcludequotas' name=\"copysurveyexcludequotas\" type=\"checkbox\" /></li>\n"
                    . "<li><label for='copysurveyexcludeanswers'>" . $clang->gT("Exclude answers?") . "</label>\n"
                    . "<input id='copysurveyexcludeanswers' name=\"copysurveyexcludeanswers\" type=\"checkbox\" /></li>\n"
                    . "<li><label for='copysurveyresetconditions'>" . $clang->gT("Reset conditions?") . "</label>\n"
                    . "<input id='copysurveyresetconditions' name=\"copysurveyresetconditions\" type=\"checkbox\" /></li></ul>\n"
                    . "<p><input type='submit' value='" . $clang->gT("Copy survey") . "' />\n"
                    . "<input type='hidden' name='action' value='copysurvey' /></p></form>\n";

        // End Copy survey TAB
        $editsurvey .= "</div>\n";
               
        return $editsurvey;
    }
    
    function _tabResourceManagement($surveyid)
    {
        $clang = $this->limesurvey_lang;
        global $sCKEditorURL;
        // TAB Uploaded Resources Management
        $ZIPimportAction = " onclick='if (validatefilename(this.form,\"".$clang->gT('Please select a file to import!','js')."\")) {this.form.submit();}'";
        if (!function_exists("zip_open")) {
            $ZIPimportAction = " onclick='alert(\"".$clang->gT("zip library not supported by PHP, Import ZIP Disabled","js")."\");'";
        }

        $disabledIfNoResources = '';
        if (hasResources($surveyid, 'survey') === false) {
            $disabledIfNoResources = " disabled='disabled'";
        }
        // functionality not ported 
        $editsurvey = "<div id='resources'>\n"
            . "<form enctype='multipart/form-data'  class='form30' id='importsurveyresources' name='importsurveyresources' action='admin/index' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
            . "<input type='hidden' name='sid' value='$surveyid' />\n"
            . "<input type='hidden' name='action' value='importsurveyresources' />\n"
            . "<ul>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' onclick='window.open(\"$sCKEditorURL/editor/filemanager/browser/default/browser.html?Connector=../../connectors/php/connector.php\", \"_blank\")' value=\"".$clang->gT("Browse Uploaded Resources")."\" $disabledIfNoResources /></li>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' onclick='window.open(\"$scriptname?action=exportsurvresources&amp;sid={$surveyid}\", \"_blank\")' value=\"".$clang->gT("Export Resources As ZIP Archive")."\" $disabledIfNoResources /></li>\n"
            . "<li><label for='the_file'>".$clang->gT("Select ZIP File:")."</label>\n"
            . "<input id='the_file' name='the_file' type='file' size='50' /></li>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' value='".$clang->gT("Import Resources ZIP Archive")."' $ZIPimportAction /></li>\n"
            . "</ul></form>\n";

        // End TAB Uploaded Resources Management
        $editsurvey .= "</div>\n";
        return $editsurvey;
        
    }
    
 }