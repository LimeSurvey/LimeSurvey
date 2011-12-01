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
* $Id$
*
*/

/**
* Translate Controller
*
* This controller performs translation actions
*
* @package		LimeSurvey
* @subpackage	Backend
*/
class translate extends Admin_Controller {

    /**
    * Constructor
    */
    function __construct()
    {
        parent::__construct();
    }

    public function _remap($method, $params = array())
    {
        array_unshift($params, $method);
        return call_user_func_array(array($this, "action"), $params);
    }

    function action($surveyid=null, $tolang="")
    {
        if(isset($surveyid)) $surveyid = sanitize_int($surveyid);
        $action=returnglobal('action');
        if($action=="ajaxtranslategoogleapi")
        {
            echo self::translate_google_api();
            return;
        }
        self::_js_admin_includes($this->config->item("adminscripts").'translation.js');
        $clang =  $this->limesurvey_lang;
        Yii::app()->loadHelper("database");
		Yii::app()->loadHelper("admin/htmleditor");

        //  $js_admin_includes[]= $homeurl.'/scripts/translation.js';

        // TODO need to do some validation here on surveyid

        $surveyinfo=getSurveyInfo($surveyid);
        if (isset($_POST['tolang']))
        {
            $tolang = $_POST['tolang'];
        }
        if ($tolang=="" && count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 1)
        {
            $tmp_langs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $tolang = $tmp_langs[0];
        }
		
        $actionvalue = "";
        if(isset($_POST['actionvalue'])) {$actionvalue = $_POST['actionvalue'];}
        //  if(isset($_GET['actionvalue'])) {$actionvalue = $_GET['actionvalue'];}


        $survey_title = $surveyinfo['name'];
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $this->load->helper("surveytranslator");
        $supportedLanguages = getLanguageData(false);



        $baselangdesc = $supportedLanguages[$baselang]['description'];
        if($tolang != "")
        {
            $tolangdesc = $supportedLanguages[$tolang]['description'];
        }
		
        self::_getAdminHeader();
        $data = array("surveyid" => $surveyid, "survey_title" => $survey_title, "tolang" => $tolang, "clang" => $clang);
        $this->load->view("admin/translate/translateheader_view", $data);

        //  $tab_names=array("title", "description", "welcome", "end", "group", "group_desc", "question", "question_help", "answer");
        //  $tab_names=array("title", "description", "invitation", "reminder");
        $tab_names=array("title", "welcome", "group", "question", "subquestion", "answer", "emailinvite", "emailreminder", "emailconfirmation", "emailregistration");

        if ($tolang != "" && $actionvalue=="translateSave")
        // Saves translated values to database
        {
            $tab_names_full = "";
            foreach($tab_names as $type)
            {
                $tab_names_full[] = $type;
                $amTypeOptions = self::setupTranslateFields($surveyid, $type, $tolang, $baselang);
                $type2 = $amTypeOptions["associated"];
                if ($type2 != "")
                {
                    $tab_names_full[] = $type2;
                }
            }
            foreach($tab_names_full as $type)
            {
                $size = 0;
                if(isset($_POST["{$type}_size"]))
                {
                    $size = $_POST["{$type}_size"];
                }
                // start a loop in order to update each record
                $i = 0;
                while ($i < $size)
                {
                    // define each variable
                    if (isset($_POST["{$type}_newvalue_{$i}"]))
                    {
						$old = $_POST["{$type}_oldvalue_{$i}"];
                        $new = $_POST["{$type}_newvalue_{$i}"];
                        // check if the new value is different from old, and then update database
                        if ($new != $old)
                        {
                            $id1 = $_POST["{$type}_id1_{$i}"];
                            $id2 = $_POST["{$type}_id2_{$i}"];
                            $amTypeOptions = self::setupTranslateFields($surveyid, $type, $tolang, $baselang, $id1, $id2, $new);
                        }
                    }
                    ++$i;
                } // end while
            } // end foreach
            $actionvalue = "";
        } // end if



        if ($tolang != "")
        // Display tabs with fields to translate, as well as input fields for translated values
        {
            $data['tab_names'] = $tab_names;
            $data['baselang'] = $baselang;
            foreach($tab_names as $type)
            {
                $data['amTypeOptions'][] = $this->setupTranslateFields($surveyid, $type, $tolang, $baselang);
            }
            $this->load->view("admin/translate/translateformheader_view", $data);
			
            // Define content of each tab
            foreach($tab_names as $type)
            {
                $amTypeOptions = self::setupTranslateFields($surveyid, $type, $tolang, $baselang);

                $type2 = $amTypeOptions["associated"];
                if ($type2 != "")
                {
                    $associated = TRUE;
                    $amTypeOptions2 = self::setupTranslateFields($surveyid, $type2, $tolang, $baselang);
                }
                else
                {
                    $associated = FALSE;
                }

                // Setup form
                // start a counter in order to number the input fields for each record
                $i = 0;
                $evenRow = FALSE;
                $all_fields_empty = TRUE;

                $resultbase = $amTypeOptions["querybase"];
                if ($associated)
                {
                    $resultbase2 = $amTypeOptions2["querybase"];
                }

                $resultto = $amTypeOptions["queryto"];
				
                if ($associated)
                {
                    $resultto2 = $amTypeOptions2["queryto"];
                }
				
				$data['baselangdesc'] = $baselangdesc;
                $data['tolangdesc'] = $tolangdesc;
                $data['type'] = $type;
                $this->load->view("admin/translate/translatetabs_view", $data);
                foreach ($resultbase->result_array() as $rowfrom)
                {
                    $textfrom = htmlspecialchars_decode($rowfrom[$amTypeOptions["dbColumn"]]);

                    if ($associated)
                    {
                        $rowfrom2 = $resultbase2->row_array();
                        $textfrom2 = htmlspecialchars_decode($rowfrom2[$amTypeOptions2["dbColumn"]]);
                    }

                    $gid = NULL;
                    if($amTypeOptions["gid"]==TRUE) $gid = $rowfrom['gid'];

                    $qid = NULL;
                    if($amTypeOptions["qid"]==TRUE) $qid = $rowfrom['qid'];
                    //echo '<pre>' . print_r($resultto) . '</pre>';
                    $rowto  = $resultto->row_array();
                    $textto = $rowto[$amTypeOptions["dbColumn"]];

                    if ($associated)
                    {
                        $rowto2  = $resultto2->row_array();
                        $textto2 = $rowto2[$amTypeOptions2["dbColumn"]];
                    }

                    if (strlen(trim((string)$textfrom)) > 0)
                    {
                        $all_fields_empty = FALSE;
                        $evenRow = !($evenRow);
                    }

                    $data['textfrom'] = $textfrom;
                    $data['textfrom2'] = $textfrom2;
                    $data['textto'] = $textto;
                    $data['textto2'] = $textto2;
                    $data['rowfrom'] = $rowfrom;
                    $data['rowfrom2'] = $rowfrom2;
                    $data['evenRow'] = $evenRow;
                    $data['gid'] = $gid;
                    $data['qid'] = $qid;
                    $data['amTypeOptions'] = $amTypeOptions;
                    $data['amTypeOptions2'] = $amTypeOptions2;
                    $data['i'] = $i;
                    $data['type'] = $type;
                    $data['type2'] = $type2;
                    $data['associated'] = $associated;
                    $this->load->view("admin/translate/translatefields_view", $data);
                    ++$i;
                } // end while
                $data['all_fields_empty'] = $all_fields_empty;
                $this->load->view("admin/translate/translatefieldsfooter_view", $data);
            } // end foreach
            // Submit button
            $this->load->view("admin/translate/translatefooter_view", $data);
        } // end if
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }

    /**
    * menuItem() creates a menu item with text and image in the admin screen menus
    * @param string $menuText
    * @return string
    */
    function menuItem($menuText, $jsMenuText, $menuImageText, $menuImageFile, $scriptname)
    {
        $menu = ""
        ."<a href=\"#\" onclick=\"window.open('".$scriptname."', '_top')\""
        ."title='".$menuText."'>"
        ."<img name='".$menuImageText."' src='".$this->config->item("imageurl")."/".$menuImageFile."' alt='"
        .$jsMenuText."' /></a>\n"
        ."<img src='".$this->config->item("imageurl")."/blank.gif' alt='' width='11'  />\n";
        return $menu;
    }

    /**
    * menuSeparator() creates a separator bar in the admin screen menus
    * @return string
    */
    function menuSeparator()
    {
        return ("<img src='".$this->config->item("imageurl")."/seperator.gif' alt='' />\n");
    }

    /**
    * showTranslateAdminmenu() creates the main menu options for the survey translation page
    * @param string $surveyid The survey ID
    * @param string $survey_title
    * @param string $tolang
    * @param string $activated
    * @param string $scriptname
    * @return string
    */
    function showTranslateAdminmenu($surveyid, $survey_title, $tolang)
    {
        $imageurl = $this->config->item("imageurl");
        $clang = $this->limesurvey_lang;
        $publicurl = $this->config->item('publicurl');

        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $supportedLanguages = getLanguageData(false);
        $langs = GetAdditionalLanguagesFromSurveyID($surveyid);

        $adminmenu = ""
        ."<div class='menubar'>\n"
        ."<div class='menubar-title ui-widget-header'>\n"
        ."<strong>".$clang->gT("Translate survey").": $survey_title</strong>\n"
        ."</div>\n" // class menubar-title
        ."<div class='menubar-main'>\n";


        $adminmenu .= ""
        ."<div class='menubar-left'>\n";

        // Return to survey administration button
        $adminmenu .= self::menuItem($clang->gT("Return to survey administration"),
        $clang->gTview("Return to survey administration"),
        "Administration", "home.png", site_url("admin/survey/view/$surveyid/"));

        // Separator
        $adminmenu .= self::menuSeparator();

        // Test / execute survey button
        if ($tolang != "")
        {
            $this->load->model('surveys_model');
            $surveyinfo = $this->surveys_model->getDataJoinLanguageSettings($surveyid);

            $surveyinfo = array_map('FlattenText', $surveyinfo);
            $activated = $surveyinfo['active'];

            if ($activated == "N")
            {
                $menutext=$clang->gT("Test This Survey");
                $menutext2=$clang->gTview("Test This Survey");
            } else
            {
                $menutext=$clang->gT("Execute This Survey");
                $menutext2=$clang->gTview("Execute This Survey");
            }
            if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
            {
                $adminmenu .= menuItem($menutext, $menutext2, "do.png", "$publicurl/index.php?sid=$surveyid&amp;newtest=Y&amp;lang=$baselang");
            }
            else
            {
                $icontext = $clang->gT($menutext);
                $icontext2 = $clang->gT($menutext);
                $adminmenu .= "<a href='#' id='dosurvey' class='dosurvey'"
                . "title=\"".$icontext2."\" accesskey='d'>"
                . "<img  src='$imageurl/do.png' alt='$icontext' />"
                . "</a>\n";

                $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                $tmp_survlangs[] = $baselang;
                rsort($tmp_survlangs);
                // Test Survey Language Selection Popup
                $adminmenu .="<div class=\"langpopup\" id=\"dosurveylangpopup\">"
                .$clang->gT("Please select a language:")."<ul>";
                foreach ($tmp_survlangs as $tmp_lang)
                {
                    $adminmenu .= "<li><a accesskey='d' onclick=\"$('.dosurvey').qtip('hide');"
                    ."\" target='_blank' href='{$publicurl}/index.php?sid=$surveyid&amp;"
                    ."newtest=Y&amp;lang={$tmp_lang}'>".getLanguageNameFromCode($tmp_lang,false)."</a></li>";
                }
                $adminmenu .= "</ul></div>";
            }
        }

        // End of survey-bar-left
        $adminmenu .= "</div>";


        // Survey language list
        $selected = "";
        if (!isset($tolang))
        {
            $selected = " selected='selected' ";
        }
        $adminmenu .= ""
        ."<div class='menubar-right'>\n"
        ."<label for='translationlanguage'>".$clang->gT("Translate to").":</label>"
        ."<select id='translationlanguage' name='translationlanguage' onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
        if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) > 1)
        {
            $adminmenu .= "<option {$selected} value='".site_url("admin/translate/$surveyid/")."'>".$clang->gT("Please choose...")."</option>\n";
        }
        foreach($langs as $lang)
        {
            $selected="";
            if ($tolang==$lang)
            {
                $selected = " selected='selected' ";
            }
            $tolangtext   = $supportedLanguages[$lang]['description'];
            $adminmenu .= "<option {$selected} value='".site_url("admin/translate/$surveyid/$lang")."'> " . $tolangtext ." </option>\n";
        }
        $adminmenu .= ""
        ."</select>\n"
        ."</div>\n"; // End of menubar-right

        $adminmenu .= ""
        ."</div>\n";
        $adminmenu .= ""
        ."</div>\n";

        return($adminmenu);
    }


    /**
    * setupTranslateFields() creates a customised array with database query
    * information for use by survey translation
    * @param string $surveyid Survey id
    * @param string $type Type of database field that is being translated, e.g. title, question, etc.
    * @param string $baselang The source translation language code, e.g. "En"
    * @param string $tolang The target translation language code, e.g. "De"
    * @param string $new The new value of the translated string
    * @param string $id1 An index variable used in the database select and update query
    * @param string $id2 An index variable used in the database select and update query
    * @return array
    */

    function setupTranslateFields($surveyid, $type, $tolang, $baselang, $id1="", $id2="", $new="")
    {
        $clang=$this->limesurvey_lang;
        $dbprefix = $this->db->dbprefix;
        switch ( $type )
        {
            case 'title':
                $this->load->model('surveys_languagesettings_model');
                $amTypeOptions = array(
                "querybase" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $baselang
				)),
                "queryto"   => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $tolang
				)),
                "queryupdate" => $this->surveys_languagesettings_model->update(array(
                    'surveyls_title' => $new
                ), array(
                    'surveyls_survey_id' => $surveyid,
                    'surveyls_language'  => $tolang
                )),
                "id1"  => "",
                "id2"  => "",
                "gid"  => FALSE,
                "qid"  => FALSE,
                "dbColumn" => 'surveyls_title',
                "description" => $clang->gT("Survey title and description"),
                "HTMLeditorType"    => "title",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "Inline",  // Allowed values: Inline, Popup or None
                "associated" => "description"
                );
                break;

            case 'description':
                $this->load->model('surveys_languagesettings_model');
                $amTypeOptions = array(
                "querybase" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $baselang
				)),
                "queryto"   => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $tolang
				)),
                "queryupdate" => $this->surveys_languagesettings_model->update(array(
                    'surveyls_description' => $new
                ), array(
                    'surveyls_survey_id' => $surveyid,
                    'surveyls_language'  => $tolang
                )),
                "id1"  => "",
                "id2"  => "",
                "gid"  => FALSE,
                "qid"  => FALSE,
                "dbColumn" => 'surveyls_description',
                "description" => $clang->gT("Description:"),
                "HTMLeditorType"    => "description",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "Inline",  // Allowed values: Inline, Popup or None
                "associated" => ""
                );
                break;

            case 'welcome':
                $this->load->model('surveys_languagesettings_model');
                $amTypeOptions = array(
                "querybase" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $baselang
				)),
                "queryto"   => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $tolang
				)),
                "queryupdate" => $this->surveys_languagesettings_model->update(array(
                    'surveyls_welcometext' => $new
                ), array(
                    'surveyls_survey_id' => $surveyid,
                    'surveyls_language'  => $tolang
                )),
                "id1"  => "",
                "id2"  => "",
                "gid"  => FALSE,
                "qid"  => FALSE,
                "dbColumn" => 'surveyls_welcometext',
                "description" => $clang->gT("Welcome and end text"),
                "HTMLeditorType"    => "welcome",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "Inline",  // Allowed values: Inline, Popup or None
                "associated" => "end"
                );
                break;

            case 'end':
                $this->load->model('surveys_languagesettings_model');
                $amTypeOptions = array(
                "querybase" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $baselang
				)),
                "queryto"   => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $tolang
				)),
                "queryupdate" => $this->surveys_languagesettings_model->update(array(
                    'surveyls_endtext' => $new
                ), array(
                    'surveyls_survey_id' => $surveyid,
                    'surveyls_language'  => $tolang
                )),
                "id1"  => "",
                "id2"  => "",
                "gid"  => FALSE,
                "qid"  => FALSE,
                "dbColumn" => 'surveyls_endtext',
                "description" => $clang->gT("End message:"),
                "HTMLeditorType"    => "end",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "Inline",  // Allowed values: Inline, Popup or None
                "associated" => ""
                );
                break;

            case 'group':
                $this->load->model('groups_model');
                $amTypeOptions = array(
                "querybase" => $this->groups_model->getAllRecords(array(
                        'sid' => $surveyid,
                        'language' => $baselang
                ), 'gid'),
                "queryto" => $this->groups_model->getAllRecords(array(
					'sid' => $surveyid,
					'language' => $tolang
				), 'gid'),
                "queryupdate" => $this->groups_model->update(array(
                    'group_name' => $new
                ), array(
                    'gid' => $id1,
                    'sid' => $surveyid,
                    'language' => $tolang
                )),
                "id1"  => "gid",
                "id2"  => "",
                "gid"  => TRUE,
                "qid"  => FALSE,
                "dbColumn" => "group_name",
                "description" => $clang->gT("Question groups"),
                "HTMLeditorType"    => "group",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "Popup",  // Allowed values: Inline, Popup or None
                "associated" => "group_desc"
                );
                break;
			
            case 'group_desc':
                $this->load->model('groups_model');
                $amTypeOptions = array(
                "querybase" => $this->groups_model->getAllRecords(array(
					'sid' => $surveyid,
					'language' => $baselang
				), 'gid'),
                "queryto"   => $this->groups_model->getAllRecords(array(
					'sid' => $surveyid,
					'language' => $tolang
				), 'gid'),
                "queryupdate" => $this->groups_model->update(array(
                    'description' => $new
                ), array(
                    'gid' => $id1,
                    'sid' => $surveyid,
                    'language' => $tolang
                )),
                "id1"  => "gid",
                "id2"  => "",
                "gid"  => TRUE,
                "qid"  => FALSE,
                "dbColumn" => "description",
                "description" => $clang->gT("Group description"),
                "HTMLeditorType"    => "group_desc",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "Popup",  // Allowed values: Inline, Popup or None
                "associated" => ""
                );
                break;

                //    case 'label':
                //      $amTypeOptions = array(
                //        "querybase" => "SELECT * "
                //                                   ."FROM ".$this->db->dbprefix('labels')
                //                                   ." WHERE language='{$baselang}' "
                //                                   .  "AND lid='$code' ",
                //        "queryto"   => "SELECT * "
                //                                    ."FROM ".$this->db->dbprefix('labels')
                //                                    ." WHERE language=".$this->db->escape($tolang)
                //                                    .  "AND lid='$code' ",
                //        "queryupdate" => "UPDATE ".$this->db->dbprefix('labels')
                //                   ." SET title = ".$this->db->escape($new)
                //                         ." WHERE lid = '{$id1}' "
                //                           ."AND code='{$id2}' "
                //                           ."AND language='{$tolang}' LIMIT 1",
                //        "dbColumn" => 'title',
                //        "id1"  => 'lid',
                //        "id2"  => 'code',
                //        "description" => $clang->gT("Label sets")
                //      );
                //      break;

            case 'question':
                $this->load->model('questions_model');
                $amTypeOptions = array(
                "querybase" => $this->questions_model->getSomeRecords('*', array(
					'sid' => $surveyid,
					'language' => $baselang,
					'parent_qid' => 0
				), 'qid'),
                "queryto"   => $this->questions_model->getSomeRecords('*', array(
					'sid' => $surveyid,
					'language' => $tolang,
					'parent_qid' => 0
				), 'qid'),
                "queryupdate" => $this->questions_model->update(array(
                    'question' => $new
                ), array(
                    'gid' => $id1,
                    'sid' => $surveyid,
                    'parent_qid' => 0,
                    'language' => $tolang
                )),
                "dbColumn" => 'question',
                "id1"  => 'qid',
                "id2"  => "",
                "gid"  => TRUE,
                "qid"  => TRUE,
                "description" => $clang->gT("Questions"),
                "HTMLeditorType"    => "question",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "Popup",  // Allowed values: Inline, Popup or ""
                "associated" => "question_help"
                );
                break;
			
            case 'question_help':
                $this->load->model('questions_model');
                $amTypeOptions = array(
                "querybase" => $this->questions_model->getSomeRecords('*', array(
					'sid' => $surveyid,
					'language' => $baselang,
					'parent_qid' => 0
				), 'qid'),
                "queryto"   => $this->questions_model->getSomeRecords('*', array(
					'sid' => $surveyid,
					'language' => $tolang,
					'parent_qid' => 0
				), 'qid'),
                "queryupdate" => $this->questions_model->update(array(
                    'help' => $new
                ), array(
                    'gid' => $id1,
                    'sid' => $surveyid,
                    'parent_qid' => 0,
                    'language' => $tolang
                )),
                "dbColumn" => 'help',
                "id1"  => 'qid',
                "id2"  => "",
                "gid"  => TRUE,
                "qid"  => TRUE,
                "description" => "",
                "HTMLeditorType"    => "question_help",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "Popup",  // Allowed values: Inline, Popup or ""
                "associated" => ""
                );
                break;

            case 'subquestion':
                $this->load->model('questions_model');
                $amTypeOptions = array(
                "querybase" => $this->questions_model->getSomeRecords('*', array(
					'sid' => $surveyid,
					'language' => $baselang,
					'parent_qid >' => 0
				), 'parent_qid,qid'),
                "queryto"   => $this->questions_model->getSomeRecords('*', array(
					'sid' => $surveyid,
					'language' => $tolang,
					'parent_qid >' => 0
				), 'parent_qid,qid'),
                "queryupdate" => $this->questions_model->update(array(
                    'question' => $new
                ), array(
                    'gid' => $id1,
                    'sid' => $surveyid,
                    'language' => $tolang
                )),
                "dbColumn" => 'question',
                "id1"  => 'qid',
                "id2"  => "",
                "gid"  => TRUE,
                "qid"  => TRUE,
                "description" => $clang->gT("Subquestions"),
                "HTMLeditorType"    => "question",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "Popup",  // Allowed values: Inline, Popup or None
                "associated" => ""
                );
                break;
			
            case 'answer': // TODO not touched
                $this->load->model('answers_model');
                $amTypeOptions = array(
                "querybase" => $this->answers_model->getAnswerQueryBase($surveyid, $baselang),
                "queryto" => $this->answers_model->getAnswerQueryTo($surveyid, $tolang),
                "queryupdate" => $this->answers_model->update(array(
                    'answer' => $new
                ), array(
                    'qid' => $id1,
                    'code' => $id2,
                    'language' => $tolang
                )),
                "dbColumn" => 'answer',
                "id1"  => 'qid',
                "id2"  => 'code',
                "gid"  => FALSE,
                "qid"  => TRUE,
                "description" => $clang->gT("Answer options"),
                "HTMLeditorType"    => "subquestion",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "Popup",  // Allowed values: Inline, Popup or None
                "associated" => ""
                );
                break;

            case 'emailinvite':
                $this->load->model('surveys_languagesettings_model');
                $amTypeOptions = array(
                "querybase" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $baselang
				)),
                "queryto" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $tolang
				)),
                "queryupdate" => $this->surveys_languagesettings_model->update(array(
                    'surveyls_email_invite_subj' => $new
                ), array(
                    'surveyls_survey_id' => $surveyid,
                    'surveyls_language'  => $tolang
                )),
                "dbColumn" => 'surveyls_email_invite_subj',
                "id1"  => '',
                "id2"  => '',
                "gid"  => FALSE,
                "qid"  => FALSE,
                "description" => $clang->gT("Invitation email"),
                "HTMLeditorType"    => "email",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "",  // Allowed values: Inline, Popup or ""
                "associated" => "emailinvitebody"
                );
                break;

            case 'emailinvitebody':
                $this->load->model('surveys_languagesettings_model');
                $amTypeOptions = array(
                "querybase" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $baselang
				)),
                "queryto" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $tolang
				)),
                "queryupdate" => $this->surveys_languagesettings_model->update(array(
                    'surveyls_email_invite' => $new
                ), array(
                    'surveyls_survey_id' => $surveyid,
                    'surveyls_language'  => $tolang
                )),
                "dbColumn" => 'surveyls_email_invite',
                "id1"  => '',
                "id2"  => '',
                "gid"  => FALSE,
                "qid"  => FALSE,
                "description" => "",
                "HTMLeditorType"    => "email",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "",  // Allowed values: Inline, Popup or ""
                "associated" => ""
                );
                break;

            case 'emailreminder':
                $this->load->model('surveys_languagesettings_model');
                $amTypeOptions = array(
                "querybase" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $baselang
				)),
                "queryto" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $tolang
				)),
                "queryupdate" => $this->surveys_languagesettings_model->update(array(
                    'surveyls_email_remind_subj' => $new
                ), array(
                    'surveyls_survey_id' => $surveyid,
                    'surveyls_language'  => $tolang
                )),
                "dbColumn" => 'surveyls_email_remind_subj',
                "id1"  => '',
                "id2"  => '',
                "gid"  => FALSE,
                "qid"  => FALSE,
                "description" => $clang->gT("Reminder email"),
                "HTMLeditorType"    => "email",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "",  // Allowed values: Inline, Popup or ""
                "associated" => "emailreminderbody"
                );
                break;

            case 'emailreminderbody':
                $this->load->model('surveys_languagesettings_model');
                $amTypeOptions = array(
                "querybase" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $baselang
				)),
                "queryto" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $tolang
				)),
                "queryupdate" => $this->surveys_languagesettings_model->update(array(
                    'surveyls_email_remind' => $new
                ), array(
                    'surveyls_survey_id' => $surveyid,
                    'surveyls_language'  => $tolang
                )),
                "dbColumn" => 'surveyls_email_remind',
                "id1"  => '',
                "id2"  => '',
                "gid"  => FALSE,
                "qid"  => FALSE,
                "description" => "",
                "HTMLeditorType"    => "email",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "",  // Allowed values: Inline, Popup or ""
                "associated" => ""
                );
                break;

            case 'emailconfirmation':
                $this->load->model('surveys_languagesettings_model');
                $amTypeOptions = array(
                "querybase" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $baselang
				)),
                "queryto" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $tolang
				)),
                "queryupdate" => $this->surveys_languagesettings_model->update(array(
                    'surveyls_email_confirm_subj' => $new
                ), array(
                    'surveyls_survey_id' => $surveyid,
                    'surveyls_language'  => $tolang
                )),
                "dbColumn" => 'surveyls_email_confirm_subj',
                "id1"  => '',
                "id2"  => '',
                "gid"  => FALSE,
                "qid"  => FALSE,
                "description" => $clang->gT("Confirmation email"),
                "HTMLeditorType"    => "email",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "",  // Allowed values: Inline, Popup or ""
                "associated" => "emailconfirmationbody"
                );
                break;

            case 'emailconfirmationbody':
                $this->load->model('surveys_languagesettings_model');
                $amTypeOptions = array(
                "querybase" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $baselang
				)),
                "queryto" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $tolang
				)),
                "queryupdate" => $this->surveys_languagesettings_model->update(array(
                    'surveyls_email_confirm' => $new
                ), array(
                    'surveyls_survey_id' => $surveyid,
                    'surveyls_language'  => $tolang
                )),
                "dbColumn" => 'surveyls_email_confirm',
                "id1"  => '',
                "id2"  => '',
                "gid"  => FALSE,
                "qid"  => FALSE,
                "description" => "",
                "HTMLeditorType"    => "email",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "",  // Allowed values: Inline, Popup or ""
                "associated" => ""
                );
                break;

            case 'emailregistration':
                $this->load->model('surveys_languagesettings_model');
                $amTypeOptions = array(
                "querybase" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $baselang
				)),
                "queryto" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $tolang
				)),
                "queryupdate" => $this->surveys_languagesettings_model->update(array(
                    'surveyls_email_register_subj' => $new
                ), array(
                    'surveyls_survey_id' => $surveyid,
                    'surveyls_language'  => $tolang
                )),
                "dbColumn" => 'surveyls_email_register_subj',
                "id1"  => '',
                "id2"  => '',
                "gid"  => FALSE,
                "qid"  => FALSE,
                "description" => $clang->gT("Registration email"),
                "HTMLeditorType"    => "email",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "",  // Allowed values: Inline, Popup or ""
                "associated" => "emailregistrationbody"
                );
                break;

            case 'emailregistrationbody':
                $this->load->model('surveys_languagesettings_model');
                $amTypeOptions = array(
                "querybase" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $baselang
				)),
                "queryto" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $tolang
				)),
                "queryupdate" => $this->surveys_languagesettings_model->update(array(
                    'surveyls_email_register' => $new
                ), array(
                    'surveyls_survey_id' => $surveyid,
                    'surveyls_language'  => $tolang
                )),
                "dbColumn" => 'surveyls_email_register',
                "id1"  => '',
                "id2"  => '',
                "gid"  => FALSE,
                "qid"  => FALSE,
                "description" => "",
                "HTMLeditorType"    => "email",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "",  // Allowed values: Inline, Popup or ""
                "associated" => ""
                );
                break;

            case 'email_confirm':
                $this->load->model('surveys_languagesettings_model');
                $amTypeOptions = array(
                "querybase" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $baselang
				)),
                "queryto" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $tolang
				)),
                "queryupdate" => $this->surveys_languagesettings_model->update(array(
                    'surveyls_email_confirm_subj' => $new
                ), array(
                    'surveyls_survey_id' => $surveyid,
                    'surveyls_language'  => $tolang
                )),
                "dbColumn" => 'surveyls_email_confirm_subj',
                "id1"  => '',
                "id2"  => '',
                "gid"  => FALSE,
                "qid"  => FALSE,
                "description" => $clang->gT("Confirmation email"),
                "HTMLeditorType"    => "email",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "",  // Allowed values: Inline, Popup or ""
                "associated" => "email_confirmbody"
                );
                break;

            case 'email_confirmbody':
                $this->load->model('surveys_languagesettings_model');
                $amTypeOptions = array(
                "querybase" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $baselang
				)),
                "queryto" => $this->surveys_languagesettings_model->getAllRecords(array(
					'surveyls_survey_id' => $surveyid,
					'surveyls_language'  => $tolang
				)),
                "queryupdate" => $this->surveys_languagesettings_model->update(array(
                    'surveyls_email_confirm' => $new
                ), array(
                    'surveyls_survey_id' => $surveyid,
                    'surveyls_language'  => $tolang
                )),
                "dbColumn" => 'surveyls_email_confirm',
                "id1"  => '',
                "id2"  => '',
                "gid"  => FALSE,
                "qid"  => FALSE,
                "description" => "",
                "HTMLeditorType"    => "email",  // This value is passed to HTML editor and determines LimeReplacementFields
                "HTMLeditorDisplay"  => "",  // Allowed values: Inline, Popup or ""
                "associated" => ""
                );
                break;

        }
        return($amTypeOptions);
    }


    /**
    * displayTranslateFieldsHeader() Formats and displays header of translation fields table
    * @param string $baselangdesc The source translation language, e.g. "English"
    * @param string $tolangdesc The target translation language, e.g. "German"
    * @return string $translateoutput
    */
    function displayTranslateFieldsHeader($baselangdesc, $tolangdesc)
    {
        $translateoutput = '<table class="translate">'
        . '<colgroup valign="top" width="45%" />'
        . '<colgroup valign="top" width="55%" />'
        . "<tr>\n"
        . "<td><b>$baselangdesc</b></td>\n"
        . "<td><b>$tolangdesc</b></td>\n"
        . "</tr>\n";
        return($translateoutput);
    }


    /**
    * displayTranslateFieldsFooter() Formats and displays footer of translation fields table
    * @return string $translateoutput
    */
    function displayTranslateFieldsFooter()
    {
        $translateoutput = ""
        . "</table>\n";
        return($translateoutput);
    }


    /**
    * displayTranslateFields() Formats and displays translation fields (base language as well as to language)
    * @param string $surveyid Survey id
    * @param string $gid Group id
    * @param string $qid Question id
    * @param string $type Type of database field that is being translated, e.g. title, question, etc.
    * @param array $amTypeOptions Array containing options associated with each $type
    * @param string $baselangdesc The source translation language, e.g. "English"
    * @param string $tolangdesc The target translation language, e.g. "German"
    * @param string $textfrom The text to be translated in source language
    * @param string $textto The text to be translated in target language
    * @param integer $i Counter
    * @param string $rowfrom Contains current row of database query
    * @param boolean $evenRow True for even rows, false for odd rows
    * @return string $translateoutput
    */

    function displayTranslateFields($surveyid, $gid, $qid, $type, $amTypeOptions,
    $baselangdesc, $tolangdesc, $textfrom, $textto, $i, $rowfrom, $evenRow)

    {
        $translateoutput = "";
        if ($evenRow)
        {
            $translateoutput .= "<tr class=\"odd\">";
        }
        else
        {
            $translateoutput .= "<tr class=\"even\">";
        }
        $value1 = "";
        if ($amTypeOptions["id1"] != "") $value1 = $rowfrom[$amTypeOptions["id1"]];
        $value2 = "";
        if ($amTypeOptions["id2"] != "") $value2 = $rowfrom[$amTypeOptions["id2"]];


        // Display text in original language
        // Display text in foreign language. Save a copy in type_oldvalue_i to identify changes before db update
        $translateoutput .= ""
        . "<td class='_from_' id='${type}_from_${i}'>$textfrom</td>\n"
        . "<td>\n";
        $translateoutput .= "<input type='hidden' name='{$type}_id1_{$i}' value='{$value1}' />\n";
        $translateoutput .= "<input type='hidden' name='{$type}_id2_{$i}' value='{$value2}' />\n";
        $nrows = max(self::calc_nrows($textfrom), self::calc_nrows($textto));
        $translateoutput .= "<input type='hidden' "
        ."name='".$type."_oldvalue_".$i."' "
        ."value='".htmlspecialchars($textto, ENT_QUOTES)."' />\n";
        $translateoutput .= "<textarea cols='80' rows='".($nrows)."' "
        ." name='{$type}_newvalue_{$i}' >".htmlspecialchars($textto)."</textarea>\n";

        if ($amTypeOptions["HTMLeditorDisplay"]=="Inline")
        {
            $translateoutput .= ""
            .getEditor("edit".$type , $type."_newvalue_".$i, htmlspecialchars($textto), $surveyid, $gid, $qid, "translate".$amTypeOptions["HTMLeditorType"]);
        }
        if ($amTypeOptions["HTMLeditorDisplay"]=="Popup")
        {
            $translateoutput .= ""
            .getPopupEditor("edit".$type , $type."_newvalue_".$i, urlencode($amTypeOptions['description']), $surveyid, $gid, $qid, "translate".$amTypeOptions["HTMLeditorType"]);
        }
        $translateoutput .= "\n</td>\n"
        . "</tr>\n";
        return($translateoutput);
    }

    /**
    * calc_nrows($subject) calculates the vertical size of textbox for survey translation.
    * The function adds the number of line breaks <br /> to the number of times a string wrap occurs.
    * @param string $subject The text string that is being translated
    * @return integer
    */
    function calc_nrows( $subject )
    {
        // Determines the size of the text box
        // A proxy for box sixe is string length divided by 80
        $pattern = "(<br..?>)";
        //$pattern = "/\n/";
        $pattern = '[(<br..?>)|(/\n/)]';
        $nrows_newline = preg_match_all($pattern, $subject, $matches);

        $nrows_char = ceil(strlen((string)$subject)/80);

        return $nrows_newline + $nrows_char;
    }

    /*
    * translate_google_api.php
    * Creates a JSON interface for the auto-translate feature
    */
    function translate_google_api()
    {
        header('Content-type: application/json');
        $sBaselang   = $this->input->post('baselang');
        $sTolang     = $this->input->post('tolang');
        $sToconvert  = $this->input->post('text');

        $aSearch     = array('zh-Hans','zh-Hant-HK','zh-Hant-TW',
        'nl-informal','de-informal','it-formal','pt-BR','es-MX','nb','nn');
        $aReplace    = array('zh-CN','zh-TW','zh-TW','nl','de','it','pt','es','no','no');

        $sTolang  = str_replace($aSearch,$aReplace,$sTolang);

        try {

            $this->load->library('admin/gtranslate/GTranslate','gtranslate');
            $objGt         = $this->gtranslate;

            // Gtranslate requires you to run function named XXLANG_to_XXLANG
            $sProcedure       = $sBaselang."_to_".$sTolang;

            // Replace {TEXT} with <TEXT>. Text within <> act as a placeholder and are
            // not translated by Google Translate
            $sToNewconvert  = preg_replace("/\{(\w+)\}/", "<$1>",$sToconvert);
            $bDoNotConvertBack = false;
            if ($sToNewconvert == $sToconvert)
                $bDoNotConvertBack = true;
            $sToconvert = $sToNewconvert;
            $sConverted  = $objGt->$sProcedure($sToconvert);
            $sConverted  = str_replace("<br>","\r\n",$sConverted);
            if (!$bDoNotConvertBack)
                $sConverted  = preg_replace("/\<(\w+)\>/", '{$1}',$sConverted);
            $sConverted  = html_entity_decode(stripcslashes($sConverted));

            $aOutput = array(
            'error'     =>  false,
            'baselang'  =>  $sBaselang,
            'tolang'    =>  $sTolang,
            'converted' =>  $sConverted
            );

        }   catch (GTranslateException $ge){

            // Get the error message and build the ouput array
            $sError  = $ge->getMessage();
            $aOutput = array(
            'error'     =>  true,
            'baselang'  =>  $sBaselang,
            'tolang'    =>  $sTolang,
            'error'     =>  $sError
            );

        }

        return ls_json_encode($aOutput). "\n";
    }
}