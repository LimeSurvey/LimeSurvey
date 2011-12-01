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
* $Id: translate.php 11147 2011-10-12 15:29:11Z c_schmitz $
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
class translate extends Survey_Common_Action {

    /**
    * Constructor
    */
	public function run()
    {
		$this->index();
    }

    public function index()
    {
		$action = "";
        $actionvalue = "";
		@$surveyid = $_GET['surveyid'];
		if (!empty($_POST['sid'])) $surveyid = (int)$_POST['sid'];
		$surveyid = sanitize_int($surveyid);

		if(isset($_POST['actionvalue'])) $actionvalue = $_POST['actionvalue'];
		
		if(isset($_POST['action'])) $action = $_POST['action'];
		
		@$tolang = $_GET['lang'];
		if(isset($_POST['tolang'])) $tolang = $_POST['tolang'];
		
        if($action=="ajaxtranslategoogleapi")
        {
            echo $this->translate_google_api();
            return;
        }

        $this->_js_admin_includes(Yii::app()->getConfig("adminscripts").'translation.js');
        $clang =  $this->getController()->lang;
        Yii::app()->loadHelper("database");
		Yii::app()->loadHelper("admin/htmleditor");



        //  $js_admin_includes[]= $homeurl.'/scripts/translation.js';

        // TODO need to do some validation here on surveyid

        $surveyinfo=getSurveyInfo($surveyid);
		
        if ($tolang=="" && count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 1)
        {
            $tmp_langs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $tolang = $tmp_langs[0];
        }

        $survey_title = $surveyinfo['name'];
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        Yii::app()->loadHelper("surveytranslator");
        $supportedLanguages = getLanguageData(false);



        $baselangdesc = $supportedLanguages[$baselang]['description'];
        if($tolang != "")
        {
            $tolangdesc = $supportedLanguages[$tolang]['description'];
        }

        $this->getController()->_getAdminHeader();
        $data = array("surveyid" => $surveyid, "survey_title" => $survey_title, "tolang" => $tolang, "clang" => $clang);
        $this->getController()->render("/admin/translate/translateheader_view", $data);

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
                $amTypeOptions = $this->setupTranslateFields($surveyid, $type, $tolang, $baselang);
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
                while ($i <= $size)
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
                            $amTypeOptions = $this->setupTranslateFields($surveyid, $type, $tolang, $baselang, $id1, $id2, $new);
                            $query = $amTypeOptions["queryupdate"];
                            db_execute_assoc($query);
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

            $this->getController()->render("/admin/translate/translateformheader_view", $data);

            // Define content of each tab
            foreach($tab_names as $type)
            {
                $amTypeOptions = $this->setupTranslateFields($surveyid, $type, $tolang, $baselang);

                $type2 = $amTypeOptions["associated"];
                if ($type2 != "")
                {
                    $associated = TRUE;
                    $amTypeOptions2 = $this->setupTranslateFields($surveyid, $type2, $tolang, $baselang);
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

                $querybase = $amTypeOptions["querybase"];
                $resultbase = db_execute_assoc($querybase);
                if ($associated)
                {
                    $querybase2 = $amTypeOptions2["querybase"];
                    $resultbase2 = db_execute_assoc($querybase2);
                }

                $queryto = $amTypeOptions["queryto"];
                $resultto = db_execute_assoc($queryto);
                if ($associated)
                {
                    $queryto2 = $amTypeOptions2["queryto"];
                    $resultto2 = db_execute_assoc($queryto2);
                }

                $data['baselangdesc'] = $baselangdesc;
                $data['tolangdesc'] = $tolangdesc;
                $data['type'] = $type;
                $this->getController()->render("/admin/translate/translatetabs_view", $data);

                foreach ($resultbase->readAll() as $rowfrom)
                {
                    $textfrom = htmlspecialchars_decode($rowfrom[$amTypeOptions["dbColumn"]]);

                    if ($associated)
                    {
                        $rowfrom2 = $resultbase2->read();
                        $textfrom2 = htmlspecialchars_decode($rowfrom2[$amTypeOptions2["dbColumn"]]);
                    }

                    $gid = NULL;
                    if($amTypeOptions["gid"]==TRUE) $gid = $rowfrom['gid'];

                    $qid = NULL;
                    if($amTypeOptions["qid"]==TRUE) $qid = $rowfrom['qid'];

                    $rowto  = $resultto->read();
                    $textto = $rowto[$amTypeOptions["dbColumn"]];

                    if ($associated)
                    {
                        $rowto2  = $resultto2->read();
                        $textto2 = $rowto2[$amTypeOptions2["dbColumn"]];
                    }

                    if (strlen(trim((string)$textfrom)) > 0)
                    {
                        $all_fields_empty = FALSE;
                        $evenRow = !($evenRow);
                        $data['evenRow'] = $evenRow;
                    }

                    if (strlen(trim((string)$textfrom2)) > 0)
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
                    $data['gid'] = $gid;
                    $data['qid'] = $qid;
                    $data['amTypeOptions'] = $amTypeOptions;
                    $data['amTypeOptions2'] = $amTypeOptions2;
                    $data['i'] = $i;
                    $data['type'] = $type;
                    $data['type2'] = $type2;
                    $data['associated'] = $associated;
                    $this->getController()->render("/admin/translate/translatefields_view", $data);
                    ++$i;
                } // end while
                $data['all_fields_empty'] = $all_fields_empty;
                $this->getController()->render("/admin/translate/translatefieldsfooter_view", $data);
            } // end foreach
            // Submit button
            $this->getController()->render("/admin/translate/translatefooter_view", $data);
        } // end if
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
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
        ."<img name='".$menuImageText."' src='".Yii::app()->getConfig("imageurl")."/".$menuImageFile."' alt='"
        .$jsMenuText."' /></a>\n"
        ."<img src='".Yii::app()->getConfig("imageurl")."/blank.gif' alt='' width='11'  />\n";
        return $menu;
    }

    /**
    * menuSeparator() creates a separator bar in the admin screen menus
    * @return string
    */
    function menuSeparator()
    {
        return ("<img src='".Yii::app()->getConfig("imageurl")."/seperator.gif' alt='' />\n");
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
        $imageurl = Yii::app()->getConfig("imageurl");
        $clang = Yii::app()->lang;
        $publicurl = Yii::app()->baseUrl;

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
        $adminmenu .= translate::menuItem($clang->gT("Return to survey administration"),
        $clang->gTview("Return to survey administration"),
        "Administration", "home.png", "$publicurl/admin/survey/view/surveyid/$surveyid/");

        // Separator
        $adminmenu .= translate::menuSeparator();

        // Test / execute survey button

        if ($tolang != "")
        {
            $sumquery1 = "SELECT * FROM {{surveys}} inner join {{surveys_languagesettings}} on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
            $sumresult1 = db_select_limit_assoc($sumquery1, 1) ; //Checked
            $surveyinfo = $sumresult1->read();

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
            $adminmenu .= "<option {$selected} value='{$publicurl}/index.php/admin/translate/surveyid/$surveyid/'>".$clang->gT("Please choose...")."</option>\n";
        }
        foreach($langs as $lang)
        {
            $selected="";
            if ($tolang==$lang)
            {
                $selected = " selected='selected' ";
            }
            $tolangtext   = $supportedLanguages[$lang]['description'];
            $adminmenu .= "<option {$selected} value='{$publicurl}/index.php/admin/translate/surveyid/$surveyid/lang/$lang'> " . $tolangtext ." </option>\n";
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
        $clang = Yii::app()->lang;

        switch ( $type )
        {
            case 'title':
                $amTypeOptions = array(
                "querybase" => "SELECT * "
                ."FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=".Yii::app()->db->quoteValue($surveyid)
                ." AND surveyls_language=".Yii::app()->db->quoteValue($baselang),
                "queryto"   => "SELECT * "
                ."FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=".Yii::app()->db->quoteValue($surveyid)
                ." AND surveyls_language=".Yii::app()->db->quoteValue($tolang),
                "queryupdate" => "UPDATE {{surveys_languagesettings}}"
                ." SET surveyls_title = ".Yii::app()->db->quoteValue($new)
                ." WHERE surveyls_survey_id=".Yii::app()->db->quoteValue($surveyid)
                ." AND surveyls_language=".Yii::app()->db->quoteValue($tolang),
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
                $amTypeOptions = array(
                "querybase" => "SELECT * "
                ."FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=".Yii::app()->db->quoteValue($surveyid)
                ." AND surveyls_language='{$baselang}'  ",
                "queryto"   => "SELECT * "
                ."FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=".Yii::app()->db->quoteValue($surveyid)
                ." AND surveyls_language='{$tolang}'  ",
                "queryupdate" => "UPDATE {{surveys_languagesettings}}"
                ." SET surveyls_description = ".Yii::app()->db->quoteValue($new)
                ." WHERE surveyls_survey_id=".Yii::app()->db->quoteValue($surveyid)
                ." AND surveyls_language='{$tolang}'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * "
                ."FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=".Yii::app()->db->quoteValue($surveyid)
                ." AND surveyls_language='{$baselang}'  ",
                "queryto"   => "SELECT * "
                ."FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=".Yii::app()->db->quoteValue($surveyid)
                ." AND surveyls_language='{$tolang}'  ",
                "queryupdate" => "UPDATE {{surveys_languagesettings}}"
                ." SET surveyls_welcometext = ".Yii::app()->db->quoteValue($new)
                ." WHERE surveyls_survey_id=".Yii::app()->db->quoteValue($surveyid)
                ." AND surveyls_language='{$tolang}'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * "
                ."FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=".Yii::app()->db->quoteValue($surveyid)
                ." AND surveyls_language='{$baselang}'  ",
                "queryto"   => "SELECT * "
                ."FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=".Yii::app()->db->quoteValue($surveyid)
                ." AND surveyls_language='{$tolang}'  ",
                "queryupdate" => "UPDATE {{surveys_languagesettings}}"
                ." SET surveyls_endtext = ".Yii::app()->db->quoteValue($new)
                ." WHERE surveyls_survey_id=".Yii::app()->db->quoteValue($surveyid)
                ." AND surveyls_language='{$tolang}'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * "
                ."FROM {{groups}}"
                ." WHERE sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND language='{$baselang}' "
                ."ORDER BY gid ",
                "queryto"   => "SELECT * "
                ."FROM {{groups}}"
                ." WHERE sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND language=".Yii::app()->db->quoteValue($tolang)
                ."ORDER BY gid ",
                "queryupdate" => "UPDATE {{groups}}"
                ." SET group_name = ".Yii::app()->db->quoteValue($new)
                ." WHERE gid = '{$id1}' "
                ."AND sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND language='{$tolang}'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * "
                ."FROM {{groups}}"
                ." WHERE sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND language='{$baselang}' "
                ."ORDER BY gid ",
                "queryto"   => "SELECT *"
                ."FROM {{groups}}"
                ." WHERE sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND language=".Yii::app()->db->quoteValue($tolang)
                ."ORDER BY gid ",
                "queryupdate" => "UPDATE {{groups}}"
                ." SET description = ".Yii::app()->db->quoteValue($new)
                ." WHERE gid = '{$id1}' "
                ."AND sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND language='{$tolang}'",
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
                //                                   ."FROM {{labels}}"
                //                                   ." WHERE language='{$baselang}' "
                //                                   .  "AND lid='$code' ",
                //        "queryto"   => "SELECT * "
                //                                    ."FROM {{labels}}"
                //                                    ." WHERE language=".Yii::app()->db->quoteValue($tolang)
                //                                    .  "AND lid='$code' ",
                //        "queryupdate" => "UPDATE {{labels}}"
                //                   ." SET title = ".Yii::app()->db->quoteValue($new)
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
                $amTypeOptions = array(
                "querybase" => "SELECT * "
                ."FROM {{questions}}"
                ." WHERE sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND language='{$baselang}' "
                ."AND parent_qid=0 "
                ."ORDER BY qid ",
                "queryto"   => "SELECT * "
                ."FROM {{questions}}"
                ." WHERE sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND language='{$tolang}' "
                ." AND parent_qid=0 "
                ."ORDER BY qid ",
                "queryupdate" => "UPDATE {{questions}}"
                ." SET question = ".Yii::app()->db->quoteValue($new)
                ." WHERE qid = '{$id1}' "
                ."AND sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND parent_qid=0 "
                ."AND language='{$tolang}'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * "
                ."FROM {{questions}}"
                ." WHERE sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND language='{$baselang}' "
                ."AND parent_qid=0 "
                ."ORDER BY qid ",
                "queryto"   => "SELECT * "
                ."FROM {{questions}}"
                ." WHERE sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND language='{$tolang}' "
                ." AND parent_qid=0 "
                ."ORDER BY qid ",
                "queryupdate" => "UPDATE {{questions}}"
                ." SET help = ".Yii::app()->db->quoteValue($new)
                ." WHERE qid = '{$id1}' "
                ." AND sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND parent_qid=0 "
                ."AND language='{$tolang}'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * "
                ."FROM {{questions}}"
                ." WHERE sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND language='{$baselang}' AND parent_qid>0 "
                ."ORDER BY parent_qid,qid ",
                "queryto"   => "SELECT * "
                ."FROM {{questions}}"
                ." WHERE sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND language=".Yii::app()->db->quoteValue($tolang)
                ." AND parent_qid>0 ORDER BY parent_qid,qid ",
                "queryupdate" => "UPDATE {{questions}}"
                ." SET question = ".Yii::app()->db->quoteValue($new)
                ." WHERE qid = '{$id1}' "
                ." AND sid=".Yii::app()->db->quoteValue($surveyid)
                ." AND language='{$tolang}'",
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

            case 'answer':
                $amTypeOptions = array(
                "querybase" => "SELECT {{answers}}.*, {{questions}}.gid "
                ." FROM {{answers}}, {{questions}}"
                ." WHERE {{questions}}.sid ='{$surveyid}' "
                ." AND {{questions}}.qid = {{answers}}.qid "
                ." AND {{questions}}.language = {{answers}}.language "
                ." AND {{questions}}.language='{$baselang}' "
                ." ORDER BY qid,code,sortorder" ,
                "queryto" => "SELECT {{answers}}.*, {{questions}}.gid "
                ." FROM {{answers}}, {{questions}}"
                ." WHERE {{questions}}.sid ='{$surveyid}' "
                ." AND {{questions}}.qid = {{answers}}.qid "
                ." AND {{questions}}.language = {{answers}}.language "
                ." AND {{questions}}.language=".Yii::app()->db->quoteValue($tolang)
                ."ORDER BY qid,code,sortorder" ,
                "queryupdate" => "UPDATE {{answers}}"
                ." SET answer = ".Yii::app()->db->quoteValue($new)
                ." WHERE qid = '{$id1}' "
                ."AND code='{$id2}' "
                ."AND language='{$tolang}'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
                "queryto" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
                "queryupdate" => "UPDATE {{surveys_languagesettings}}"
                ." SET surveyls_email_invite_subj = ".Yii::app()->db->quoteValue($new)
                ." WHERE surveyls_survey_id=$surveyid "
                ."AND surveyls_language='$tolang'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
                "queryto" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
                "queryupdate" => "UPDATE {{surveys_languagesettings}}"
                ." SET surveyls_email_invite = ".Yii::app()->db->quoteValue($new)
                ." WHERE surveyls_survey_id=$surveyid "
                ."AND surveyls_language='$tolang'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
                "queryto" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
                "queryupdate" => "UPDATE {{surveys_languagesettings}}"
                ." SET surveyls_email_remind_subj = ".Yii::app()->db->quoteValue($new)
                ." WHERE surveyls_survey_id=$surveyid "
                ."AND surveyls_language='$tolang'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
                "queryto" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
                "queryupdate" => "UPDATE {{surveys_languagesettings}}"
                ." SET surveyls_email_remind = ".Yii::app()->db->quoteValue($new)
                ." WHERE surveyls_survey_id=$surveyid "
                ."AND surveyls_language='$tolang'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
                "queryto" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
                "queryupdate" => "UPDATE {{surveys_languagesettings}}"
                ." SET surveyls_email_confirm_subj = ".Yii::app()->db->quoteValue($new)
                ." WHERE surveyls_survey_id=$surveyid "
                ."AND surveyls_language='$tolang'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
                "queryto" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
                "queryupdate" => "UPDATE {{surveys_languagesettings}}"
                ." SET surveyls_email_confirm = ".Yii::app()->db->quoteValue($new)
                ." WHERE surveyls_survey_id=$surveyid "
                ."AND surveyls_language='$tolang'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
                "queryto" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
                "queryupdate" => "UPDATE {{surveys_languagesettings}}"
                ." SET surveyls_email_register_subj = ".Yii::app()->db->quoteValue($new)
                ." WHERE surveyls_survey_id=$surveyid "
                ."AND surveyls_language='$tolang'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
                "queryto" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
                "queryupdate" => "UPDATE {{surveys_languagesettings}}"
                ." SET surveyls_email_register = ".Yii::app()->db->quoteValue($new)
                ." WHERE surveyls_survey_id=$surveyid "
                ."AND surveyls_language='$tolang'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
                "queryto" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
                "queryupdate" => "UPDATE {{surveys_languagesettings}}"
                ." SET surveyls_email_confirm_subj = ".Yii::app()->db->quoteValue($new)
                ." WHERE surveyls_survey_id=$surveyid "
                ."AND surveyls_language='$tolang'",
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
                $amTypeOptions = array(
                "querybase" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
                "queryto" => "SELECT * FROM {{surveys_languagesettings}}"
                ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
                "queryupdate" => "UPDATE {{surveys_languagesettings}}"
                ." SET surveyls_email_confirm = ".Yii::app()->db->quoteValue($new)
                ." WHERE surveyls_survey_id=$surveyid "
                ."AND surveyls_language='$tolang'",
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
        $nrows = max(translate::calc_nrows($textfrom), translate::calc_nrows($textto));
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
        $sBaselang   = @$_POST['baselang'];
        $sTolang     = @$_POST['tolang'];
        $sToconvert  = @$_POST['text'];

        $aSearch     = array('zh-Hans','zh-Hant-HK','zh-Hant-TW',
        'nl-informal','de-informal','it-formal','pt-BR','es-MX','nb','nn');
        $aReplace    = array('zh-CN','zh-TW','zh-TW','nl','de','it','pt','es','no','no');

        $sTolang  = str_replace($aSearch,$aReplace,$sTolang);

        try {

            Yii::import('application.libraries.admin.gtranslate.GTranslate',true);
            $objGt         = new GTranslate;

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