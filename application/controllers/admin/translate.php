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
		$this->route("action", array());
	}

    public function action()
    {
        $surveyid = ( isset($_GET['surveyid']) ) ? sanitize_int(CHttpRequest::getParam('surveyid')) : NULL;
        $tolang = ( isset($_GET['lang']) ) ? CHttpRequest::getParam('lang') : "";

        $action = returnglobal('action');

        if ( $action == "ajaxtranslategoogleapi" )
        {
            echo $this->translate_google_api();
            return;
        }

        $this->getController()->_js_admin_includes(Yii::app()->getConfig("adminscripts") . 'translation.js');

        $clang = Yii::app()->lang;
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $langs = GetAdditionalLanguagesFromSurveyID($surveyid);

        Yii::app()->loadHelper("database");
		Yii::app()->loadHelper("admin/htmleditor");

		$tolang = ( isset($_POST['lang']) ) ? CHttpRequest::getPost('lang') : $tolang;
		$actionvalue  = ( isset($_POST['actionvalue']) ) ? CHttpRequest::getPost('actionvalue') : "";

        if ( empty($tolang) && count($langs) == 1 )
        {
            $tolang = $langs[0];
        }

        // TODO need to do some validation here on surveyid
        $surveyinfo = getSurveyInfo($surveyid);
        $survey_title = $surveyinfo['name'];

        Yii::app()->loadHelper("surveytranslator");
        $supportedLanguages = getLanguageData(FALSE);

        $baselangdesc = $supportedLanguages[$baselang]['description'];

        $this->getController()->_getAdminHeader();

        $data = array
		(
			"surveyid" => $surveyid,
			"survey_title" => $survey_title,
			"tolang" => $tolang,
			"clang" => $clang,
			"adminmenu" => $this->showTranslateAdminmenu($surveyid, $survey_title, $tolang)
		);
        $this->getController()->render("/admin/translate/translateheader_view", $data);

        $tab_names = array("title", "welcome", "group", "question", "subquestion", "answer",
						"emailinvite", "emailreminder", "emailconfirmation", "emailregistration");

        if ( ! empty($tolang) )
        {
			if ( $actionvalue == "translateSave" )
			{
				$this->_translateSave($surveyid, $tolang, $baselang, $tab_names);
			} // end if

            $tolangdesc = $supportedLanguages[$tolang]['description'];
			// Display tabs with fields to translate, as well as input fields for translated values
			$this->_displayUntranslatedFields($surveyid, $tolang, $baselang, $tab_names, $baselangdesc, $tolangdesc);
        } // end if

		$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
    }

	private function _translateSave($surveyid, $tolang, $baselang, $tab_names)
	{
		$tab_names_full = "";

		foreach( $tab_names as $type )
		{
			$amTypeOptions = $this->setupTranslateFields($surveyid, $type, $tolang, $baselang);
			$type2 = $amTypeOptions["associated"];

			$tab_names_full[] = ( ! empty($type2) ) ? $type2 : $type;
		}

		foreach( $tab_names_full as $type )
		{
			$size = ( isset($_POST["{$type}_size"]) ) ? CHttpRequest::getPost("{$type}_size") : 0;

			// start a loop in order to update each record
			$i = 0;
			while ($i < $size)
			{
				// define each variable
				if ( isset($_POST["{$type}_newvalue_{$i}"]) )
				{
					$old = CHttpRequest::getPost("{$type}_oldvalue_{$i}");
					$new = CHttpRequest::getPost("{$type}_newvalue_{$i}");

					// check if the new value is different from old, and then update database
					if ( $new != $old )
					{
						$id1 = CHttpRequest::getPost("{$type}_id1_{$i}");
						$id2 = CHttpRequest::getPost("{$type}_id2_{$i}");

						$amTypeOptions = $this->setupTranslateFields($surveyid, $type, $tolang, $baselang, $id1, $id2, $new);
					}
				}
				$i++;
			} // end while
		} // end foreach
	}

	private function _displayUntranslatedFields($surveyid, $tolang, $baselang, $tab_names, $baselangdesc, $tolangdesc)
	{
		$data['surveyid'] = $surveyid;
		$data['clang'] = Yii::app()->lang;
		$data['tab_names'] = $tab_names;
		$data['tolang'] = $tolang;
		$data['baselang'] = $baselang;

		foreach( $tab_names as $type )
		{
			$data['amTypeOptions'][] = $this->setupTranslateFields($surveyid, $type, $tolang, $baselang);
		}
		$this->getController()->render("/admin/translate/translateformheader_view", $data);

		// Define content of each tab
		foreach( $tab_names as $type )
		{
			$amTypeOptions = $this->setupTranslateFields($surveyid, $type, $tolang, $baselang);

			$type2 = $amTypeOptions["associated"];

			$associated = FALSE;
			if ( ! empty($type2) )
			{
				$associated = TRUE;
				$amTypeOptions2 = $this->setupTranslateFields($surveyid, $type2, $tolang, $baselang);
                $resultbase2 = $amTypeOptions2["querybase"];
				$resultto2 = $amTypeOptions2["queryto"];
			}
			// Setup form
			// start a counter in order to number the input fields for each record
			$i = 0;
			$evenRow = FALSE;
			$all_fields_empty = TRUE;

			$resultbase = $amTypeOptions["querybase"];
			$resultto = $amTypeOptions["queryto"];

			$data['baselangdesc'] = $baselangdesc;
			$data['tolangdesc'] = $tolangdesc;
			$data['type'] = $type;
			$data['translateTabs'] = $this->displayTranslateFieldsHeader($baselangdesc, $tolangdesc);
			$this->getController()->render("/admin/translate/translatetabs_view", $data);
			foreach ( $resultbase->queryAll() as $rowfrom )
			{
				$textfrom = htmlspecialchars_decode($rowfrom[$amTypeOptions["dbColumn"]]);
				$rowto  = $resultto->queryRow();

				$textto = $rowto[$amTypeOptions["dbColumn"]];
				if ( $associated )
				{
					$rowfrom2 = $resultbase2->queryRow();
					$textfrom2 = htmlspecialchars_decode($rowfrom2[$amTypeOptions2["dbColumn"]]);
					$rowto2  = $resultto2->queryRow();
					$textto2 = $rowto2[$amTypeOptions2["dbColumn"]];
				}

				$gid = ( $amTypeOptions["gid"] == TRUE ) ? $gid = $rowfrom['gid'] : NULL;
				$qid = ( $amTypeOptions["qid"] == TRUE ) ? $qid = $rowfrom['qid'] : NULL;

				$textform_length = strlen(trim($textfrom));
				if ( $textform_length > 0 )
				{
					$all_fields_empty = FALSE;
					$evenRow = ! ($evenRow);
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

				$evenRow = !($evenRow);
				$data['translateFields'] = $this->displayTranslateFields($surveyid, $gid, $qid, $type,
											$amTypeOptions, $baselangdesc, $tolangdesc, $textfrom, $textto, $i, $rowfrom, $evenRow);
				if ($associated && strlen(trim((string)$textfrom2)) > 0)
				{
					$evenRow = !($evenRow);
					$data['translateFields'] .= $this->displayTranslateFields($surveyid, $gid, $qid, $type2,
											$amTypeOptions2, $baselangdesc, $tolangdesc, $textfrom2, $textto2, $i, $rowfrom2, $evenRow);
				}

				$this->getController()->render("/admin/translate/translatefields_view", $data);

				$i++;
			} // end while

			$data['all_fields_empty'] = $all_fields_empty;
			$data['translateFieldsFooter'] = $this->displayTranslateFieldsFooter();
			$this->getController()->render("/admin/translate/translatefieldsfooter_view", $data);
		} // end foreach

		// Submit button
		$this->getController()->render("/admin/translate/translatefooter_view", $data);
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
    private function showTranslateAdminmenu($surveyid, $survey_title, $tolang)
    {
        $clang = Yii::app()->lang;
        $publicurl = Yii::app()->getConfig('publicurl');
		$menuitem_url = "{$publicurl}/index.php?sid={$surveyid}&newtest=Y&lang=";

		$adminmenu = "";
        $adminmenu .= CHtml::openTag('div', array('class'=>'menubar'));
        $adminmenu .= CHtml::openTag('div', array('class'=>'menubar-title ui-widget-header'));
        $adminmenu .= CHtml::tag('strong', array(), $clang->gT("Translate survey") . ": $survey_title");
        $adminmenu .= CHtml::closeTag("div");
        $adminmenu .= CHtml::openTag('div', array('class'=>'menubar-main'));
        $adminmenu .= CHtml::openTag('div', array('class'=>'menubar-left'));

        // Return to survey administration button
        $adminmenu .= $this->menuItem(
							$clang->gT("Return to survey administration"),
							$clang->gTview("Return to survey administration"),
							"Administration",
							"home.png",
							$this->getController()->createUrl("admin/survey/sa/view/surveyid/{$surveyid}/")
						);

        // Separator
        $adminmenu .= $this->menuSeparator();

        // Test / execute survey button
        if ( ! empty ($tolang) )
        {
			$adminmenu .= $this->_getSurveyButton($surveyid, $menuitem_url);
		}

        // End of survey-bar-left
		$adminmenu .= CHtml::closeTag('div');


        // Survey language list
		$adminmenu .= $this->_getLanguageList($surveyid, $tolang);
		$adminmenu .= CHtml::closeTag('div');
		$adminmenu .= CHtml::closeTag('div');

        return $adminmenu;
    }

	/*
	* _getSurveyButton() returns test / execute survey button
	* @param string $surveyid Survey id
	* @param string $menuitem_url Menu item url
	*/
	private function _getSurveyButton($surveyid, $menuitem_url)
	{
		$survey_button = "";

        $imageurl = Yii::app()->getConfig("imageurl");
        $clang = Yii::app()->lang;

        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $langs = GetAdditionalLanguagesFromSurveyID($surveyid);

		$surveyinfo = Survey::model()->getDataJoinLanguageSettings($surveyid);

		$surveyinfo = array_map('FlattenText', $surveyinfo);
		$menutext = ( $surveyinfo['active'] == "N" ) ? $clang->gT("Test This Survey") : $clang->gT("Execute This Survey");
		$menutext2 = ( $surveyinfo['active'] == "N" ) ? $clang->gTview("Test This Survey") : $clang->gTview("Execute This Survey");

		if ( count($langs) == 0 )
		{
			$survey_button .= $this->menuItem(
								$menutext,
								$menutext2,
								'',
								"do.png",
								$menuitem_url . $baselang
							);
		}
		else
		{
			$icontext = $clang->gT($menutext);
			$icontext2 = $clang->gT($menutext);

			$img_tag = CHtml::image($imageurl . '/do.png', $icontext);
			$survey_button .= CHtml::link($img_tag, '#', array(
				'id' 		=> 	'dosurvey',
				'class' 	=> 	'dosurvey',
				'title' 	=> 	$icontext2,
				'accesskey' => 	'd'
			));

			$tmp_survlangs = $langs;
			$tmp_survlangs[] = $baselang;
			rsort($tmp_survlangs);

			// Test Survey Language Selection Popup
			$survey_button .= CHtml::openTag(
									'div',
									array(
										'class' => 'langpopup',
										'id' => 'dosurveylangpopup'
									)
								);

			$survey_button .= $clang->gT("Please select a language:") . CHtml::openTag('ul');

			foreach ( $tmp_survlangs as $tmp_lang )
			{
				$survey_button .= CHtml::tag('li', array(),
					CHtml::link(getLanguageNameFromCode($tmp_lang, FALSE), $menuitem_url . $tmp_lang, array(
						'target' 	=> 	'_blank',
						'onclick' 	=> 	"$('.dosurvey').qtip('hide');",
						'accesskey' => 	'd'
					))
				);
			}
			$survey_button .= CHtml::closeTag('ul');
			$survey_button .= CHtml::closeTag('div');
		}

		return $survey_button;
	}

	/*
	* _getLanguageList() returns survey language list
	* @param string $surveyid Survey id
	* @param string @clang Language object
	* @param string $tolang The target translation code
	*/
	private function _getLanguageList($surveyid, $tolang)
	{
		$language_list = "";

        $clang = Yii::app()->lang;

        $langs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $supportedLanguages = getLanguageData(FALSE);

		$language_list .= CHtml::openTag('div', array('class'=>'menubar-right')); // Opens .menubar-right div
		$language_list .= CHtml::tag('label', array('for'=>'translationlanguage'), $clang->gT("Translate to") . ":");
		$language_list .= CHtml::openTag(
							'select',
							array(
								'id' => 'translationlanguage',
								'name' => 'translationlanguage',
								'onchange' => "window.open(this.options[this.selectedIndex].value,'_top')"
							)
						);

        if ( count(GetAdditionalLanguagesFromSurveyID($surveyid)) > 1 )
        {
			$selected = ( ! isset($tolang) ) ? "selected" : "";

			$language_list .= CHtml::tag(
								'option',
								array(
									'selected' => $selected,
									'value' => $this->getController()->createUrl("admin/translate/surveyid/{$surveyid}/")
								),
								$clang->gT("Please choose...")
							);
        }

        foreach( $langs as $lang )
        {
            $selected = ( $tolang == $lang ) ? "selected" : "";

            $tolangtext = $supportedLanguages[$lang]['description'];
			$language_list .= CHtml::tag(
								'option',
								array(
									'selected' => $selected,
									'value' => $this->getController()->createUrl("admin/translate/surveyid/{$surveyid}/lang/{$lang}")
								),
								$tolangtext
							);
        }

		$language_list .= CHtml::closeTag('select');
		$language_list .= CHtml::closeTag('div'); // End of menubar-right

		return $language_list;
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
    private function setupTranslateFields($surveyid, $type, $tolang, $baselang, $id1="", $id2="", $new="")
    {
        $clang = Yii::app()->lang;

		$data = array();

        switch ( $type )
        {
            case 'title':
				$data = array(
					'type' => 1,
					'dbColumn' => 'surveyls_title',
					'id1' => '',
					'id2' => '',
					'gid' => FALSE,
					'qid' => FALSE,
					'description' => $clang->gT("Survey title and description"),
					'HTMLeditorType' => "title",
					'HTMLeditorDisplay' => "Inline",
					'associated' => "description"
				);
			break;

            case 'description':
				$data = array(
					'type' => 1,
					'dbColumn' => 'surveyls_description',
					'id1' => '',
					'id2' => '',
					'gid' => FALSE,
					'qid' => FALSE,
					'description' => $clang->gT("Description:"),
					'HTMLeditorType' => "description",
					'HTMLeditorDisplay' => "Inline",
					'associated' => ""
				);
			break;

            case 'welcome':
				$data = array(
					'type' => 1,
					'dbColumn' => 'surveyls_welcometext',
					'id1' => '',
					'id2' => '',
					'gid' => FALSE,
					'qid' => FALSE,
					'description' => $clang->gT("Welcome and end text"),
					'HTMLeditorType' => "welcome",
					'HTMLeditorDisplay' => "Inline",
					'associated' => "end"
				);
			break;

            case 'end':
				$data = array(
					'type' => 1,
					'dbColumn' => 'surveyls_endtext',
					'id1' => '',
					'id2' => '',
					'gid' => FALSE,
					'qid' => FALSE,
					'description' => $clang->gT("End message:"),
					'HTMLeditorType' => "end",
					'HTMLeditorDisplay' => "Inline",
					'associated' => ""
				);
			break;

            case 'group':
				$data = array(
					'type' => 2,
					'dbColumn' => 'group_name',
					'id1' => 'gid',
					'id2' => '',
					'gid' => TRUE,
					'qid' => FALSE,
					'description' => $clang->gT("Question groups"),
					'HTMLeditorType' => "group",
					'HTMLeditorDisplay' => "Popup",
					'associated' => "group_desc"
				);
			break;

            case 'group_desc':
				$data = array(
					'type' => 2,
					'dbColumn' => 'description',
					'id1' => 'gid',
					'id2' => '',
					'gid' => TRUE,
					'qid' => FALSE,
					'description' => $clang->gT("Group description"),
					'HTMLeditorType' => "group_desc",
					'HTMLeditorDisplay' => "Popup",
					'associated' => ""
				);
			break;

            case 'question':
				$data = array(
					'type' => 3,
					'dbColumn' => 'question',
					'id1' => 'qid',
					'id2' => '',
					'gid' => TRUE,
					'qid' => TRUE,
					'description' => $clang->gT("Questions"),
					'HTMLeditorType' => "question",
					'HTMLeditorDisplay' => "Popup",
					'associated' => "question_help"
				);
			break;

            case 'question_help':
				$data = array(
					'type' => 3,
					'dbColumn' => 'help',
					'id1' => 'qid',
					'id2' => '',
					'gid' => TRUE,
					'qid' => TRUE,
					'description' => "",
					'HTMLeditorType' => "question_help",
					'HTMLeditorDisplay' => "Popup",
					'associated' => ""
				);
			break;

            case 'subquestion':
				$data = array(
					'type' => 4,
					'dbColumn' => 'question',
					'id1' => 'qid',
					'id2' => '',
					'gid' => TRUE,
					'qid' => TRUE,
					'description' => $clang->gT("Subquestions"),
					'HTMLeditorType' => "question",
					'HTMLeditorDisplay' => "Popup",
					'associated' => ""
				);
			break;

            case 'answer': // TODO not touched
				$data = array(
					'type' => 5,
					'dbColumn' => 'answer',
					'id1' => 'qid',
					'id2' => 'code',
					'gid' => FALSE,
					'qid' => TRUE,
					'description' => $clang->gT("Answer options"),
					'HTMLeditorType' => "subquestion",
					'HTMLeditorDisplay' => "Popup",
					'associated' => ""
				);
			break;

            case 'emailinvite':
				$data = array(
					'type' => 1,
					'dbColumn' => 'surveyls_email_invite_subj',
					'id1' => '',
					'id2' => '',
					'gid' => FALSE,
					'qid' => FALSE,
					'description' => $clang->gT("Invitation email"),
					'HTMLeditorType' => "email",
					'HTMLeditorDisplay' => "Popup",
					'associated' => "emailinvitebody"
				);
			break;

            case 'emailinvitebody':
				$data = array(
					'type' => 1,
					'dbColumn' => 'surveyls_email_invite',
					'id1' => '',
					'id2' => '',
					'gid' => FALSE,
					'qid' => FALSE,
					'description' => "",
					'HTMLeditorType' => "email",
					'HTMLeditorDisplay' => "",
					'associated' => ""
				);
			break;

            case 'emailreminder':
				$data = array(
					'type' => 1,
					'dbColumn' => 'surveyls_email_remind_subj',
					'id1' => '',
					'id2' => '',
					'gid' => FALSE,
					'qid' => FALSE,
					'description' => $clang->gT("Reminder email"),
					'HTMLeditorType' => "email",
					'HTMLeditorDisplay' => "",
					'associated' => "emailreminderbody"
				);
			break;

            case 'emailreminderbody':
				$data = array(
					'type' => 1,
					'dbColumn' => 'surveyls_email_remind',
					'id1' => '',
					'id2' => '',
					'gid' => FALSE,
					'qid' => FALSE,
					'description' => "",
					'HTMLeditorType' => "email",
					'HTMLeditorDisplay' => "",
					'associated' => ""
				);
			break;

            case 'emailconfirmation':
				$data = array(
					'type' => 1,
					'dbColumn' => 'surveyls_email_confirm_subj',
					'id1' => '',
					'id2' => '',
					'gid' => FALSE,
					'qid' => FALSE,
					'description' => $clang->gT("Confirmation email"),
					'HTMLeditorType' => "email",
					'HTMLeditorDisplay' => "",
					'associated' => "emailconfirmationbody"
				);
			break;

            case 'emailconfirmationbody':
				$data = array(
					'type' => 1,
					'dbColumn' => 'surveyls_email_confirm',
					'id1' => '',
					'id2' => '',
					'gid' => FALSE,
					'qid' => FALSE,
					'description' => "",
					'HTMLeditorType' => "email",
					'HTMLeditorDisplay' => "",
					'associated' => ""
				);
			break;

            case 'emailregistration':
				$data = array(
					'type' => 1,
					'dbColumn' => 'surveyls_email_register_subj',
					'id1' => '',
					'id2' => '',
					'gid' => FALSE,
					'qid' => FALSE,
					'description' => $clang->gT("Registration email"),
					'HTMLeditorType' => "email",
					'HTMLeditorDisplay' => "",
					'associated' => "emailregistrationbody"
				);
			break;

            case 'emailregistrationbody':
				$data = array(
					'type' => 1,
					'dbColumn' => 'surveyls_email_register',
					'id1' => '',
					'id2' => '',
					'gid' => FALSE,
					'qid' => FALSE,
					'description' => "",
					'HTMLeditorType' => "email",
					'HTMLeditorDisplay' => "",
					'associated' => ""
				);
			break;

            case 'email_confirm':
				$data = array(
					'type' => 1,
					'dbColumn' => 'surveyls_email_confirm_subj',
					'id1' => '',
					'id2' => '',
					'gid' => FALSE,
					'qid' => FALSE,
					'description' => $clang->gT("Confirmation email"),
					'HTMLeditorType' => "email",
					'HTMLeditorDisplay' => "",
					'associated' => "email_confirmbody"
				);
			break;

            case 'email_confirmbody':
				$data = array(
					'type' => 1,
					'dbColumn' => 'surveyls_email_confirm',
					'id1' => '',
					'id2' => '',
					'gid' => FALSE,
					'qid' => FALSE,
					'description' => "",
					'HTMLeditorType' => "email",
					'HTMLeditorDisplay' => "",
					'associated' => ""
				);
			break;
        }

		$amTypeOptions = $this->_amTypeOptions($data, $surveyid, $tolang, $baselang, $id1, $id2, $new);

        return $amTypeOptions;
    }

	private function _amTypeOptions($data, $surveyid, $tolang, $baselang, $id1 = "", $id2 = "", $new = "")
	{
		$amTypeOptions = array();

		switch ( $data['type'] )
		{
			case 1 :
				$amTypeOptions = array_merge($amTypeOptions, array(
					"querybase" => Surveys_languagesettings::model()->getAllRecords(
						"surveyls_survey_id = '{$surveyid}' AND surveyls_language = '{$baselang}'", FALSE
					),
					"queryto" => Surveys_languagesettings::model()->getAllRecords(
						"surveyls_survey_id = '{$surveyid}' AND surveyls_language = '{$tolang}'", FALSE
					),
					"queryupdate" => Surveys_languagesettings::model()->update(
						array(
							$data['dbColumn'] => $new
						),
						array(
							'surveyls_survey_id' => $surveyid,
							'surveyls_language'  => $tolang
						)
					)
				));
			break;

			case 2 :
				$amTypeOptions = array_merge($amTypeOptions, array(
					"querybase" => Groups::model()->getAllRecords(
						"sid = '{$surveyid}' AND language = '{$baselang}'",
						'gid', FALSE
					),
					"queryto" => Groups::model()->getAllRecords(
						"sid = '{$surveyid}' AND language = '{$tolang}'",
						'gid', FALSE
					),
					"queryupdate" => Groups::model()->update(
						array(
							$data['dbColumn'] => $new
						),
						"gid = '{$id1}' AND sid = '{$surveyid}' AND language = '{$tolang}'"
					)
				));
			break;

			case 3 :
				$amTypeOptions = array_merge($amTypeOptions, array(
					"querybase" => Questions::model()->getSomeRecords(
						'*',
						"sid = '{$surveyid}' AND language = '{$baselang}' AND parent_qid = 0",
						'qid', FALSE
					),
					"queryto" => Questions::model()->getSomeRecords(
						'*',
						"sid = '{$surveyid}' AND language = '{$tolang}' AND parent_qid = 0",
						'qid', FALSE
					),
					"queryupdate" => Questions::model()->update(
						array(
							$data['dbColumn'] => $new
						),
						"gid = '{$id1}' AND sid = '{$surveyid}' AND parent_qid = 0 AND language = '{$tolang}'"
					)
				));
			break;

			case 4 :
				$amTypeOptions = array_merge($amTypeOptions, array(
					"querybase" => Questions::model()->getSomeRecords(
						'*',
						"sid = '{$surveyid}' AND language = '{$baselang}' AND parent_qid > 0",
						'parent_qid,qid', FALSE
					),
					"queryto" => Questions::model()->getSomeRecords(
						'*',
						"sid = '{$surveyid}' AND language = '{$tolang}' AND parent_qid > 0",
						'parent_qid,qid', FALSE
					),
					"queryupdate" => Questions::model()->update(
						array(
							'question' => $new
						),
						"gid = '{$id1}' AND sid = '{$surveyid}' AND language = '{$tolang}'"
					)
				));
			break;

			case 5 :
				$amTypeOptions = array_merge($amTypeOptions, array(
					"querybase" => Answers::model()->getAnswerQuery($surveyid, $baselang, FALSE),
					"queryto" => Answers::model()->getAnswerQuery($surveyid, $tolang, FALSE),
					"queryupdate" => Answers::model()->update(
						array(
							$data['dbColumn'] => $new
						),
						"qid = '{$id1}' AND code = '{$id2}' AND language = '{$tolang}'"
					)
				));
			break;
		}

		$amTypeOptions = array_merge((array)$amTypeOptions, (array)$data);

		return $amTypeOptions;
	}

    /**
    * displayTranslateFieldsHeader() Formats and displays header of translation fields table
    * @param string $baselangdesc The source translation language, e.g. "English"
    * @param string $tolangdesc The target translation language, e.g. "German"
    * @return string $translateoutput
    */
    private function displayTranslateFieldsHeader($baselangdesc, $tolangdesc)
    {
		$translateoutput = "";
        $translateoutput .= CHtml::openTag('table', array('class'=>'translate'));
		$translateoutput .= '<colgroup valign="top" width="45%" />';
		$translateoutput .= '<colgroup valign="top" width="55%" />';
        $translateoutput .= CHtml::openTag('tr');
        $translateoutput .= CHtml::tag('td', array(), CHtml::tag('b', array(), $baselangdesc));
        $translateoutput .= CHtml::tag('td', array(), CHtml::tag('b', array(), $tolangdesc));
        $translateoutput .= CHtml::closeTag("tr");

        return $translateoutput;
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
    * @param boolean $evenRow TRUE for even rows, FALSE for odd rows
    * @return string $translateoutput
    */
    private function displayTranslateFields($surveyid, $gid, $qid, $type, $amTypeOptions,
    $baselangdesc, $tolangdesc, $textfrom, $textto, $i, $rowfrom, $evenRow)
    {
        $translateoutput = "";
		$translateoutput .= CHtml::openTag('tr', array('class' => ( $evenRow ) ? 'odd' : 'even'));

        $value1 = ( ! empty($amTypeOptions["id1"]) ) ? $rowfrom[$amTypeOptions["id1"]] : "";
        $value2 = ( ! empty($amTypeOptions["id2"]) ) ? $rowfrom[$amTypeOptions["id2"]] : "";

        // Display text in original language
        // Display text in foreign language. Save a copy in type_oldvalue_i to identify changes before db update
		$translateoutput .= CHtml::tag(
								'td',
								array(
									'class' => '_from_',
									'id' => "${type}_from_${i}"
								),
								"$textfrom"
							);
        $translateoutput .= CHtml::openTag('td');
		$translateoutput .= CHtml::hiddenField("{$type}_id1_{$i}", $value1);
		$translateoutput .= CHtml::hiddenField("{$type}_id2_{$i}", $value2);

        $nrows = max($this->calc_nrows($textfrom), $this->calc_nrows($textto));

		$translateoutput .= CHtml::hiddenField("{$type}_oldvalue_{$i}", htmlspecialchars($textto, ENT_QUOTES));
		$translateoutput .= CHtml::textArea("{$type}_newvalue_{$i}", htmlspecialchars($textto),
								array(
									'cols' => '80',
									'rows' => $nrows,
								)
							);

		$htmleditor_data = array(
			"edit" . $type ,
			$type . "_newvalue_" . $i,
			htmlspecialchars($textto),
			$surveyid,
			$gid,
			$qid,
			"translate" . $amTypeOptions["HTMLeditorType"]
		);
		$translateoutput .= $this->_loadEditor($amTypeOptions, $htmleditor_data);

        $translateoutput .= CHtml::closeTag("td");
        $translateoutput .= CHtml::closeTag("tr");

        return $translateoutput;
    }

	private function _loadEditor($htmleditor, $data)
	{
		$editor_function = "";

        if ( $htmleditor["HTMLeditorDisplay"] == "Inline" OR  $htmleditor["HTMLeditorDisplay"] == "" )
        {
            $editor_function = "getEditor";
        }
		else if ( $htmleditor["HTMLeditorDisplay"] == "Popup" )
        {
            $editor_function = "getPopupEditor";
			$data[2] = urlencode($htmleditor['description']);
        }

		return call_user_func_array($editor_function, $data);
	}

    /**
    * calc_nrows($subject) calculates the vertical size of textbox for survey translation.
    * The function adds the number of line breaks <br /> to the number of times a string wrap occurs.
    * @param string $subject The text string that is being translated
    * @return integer
    */
    private function calc_nrows( $subject )
    {
        // Determines the size of the text box
        // A proxy for box sixe is string length divided by 80
        $pattern = "(<br..?>)";
        $pattern = '[(<br..?>)|(/\n/)]';

        $nrows_newline = preg_match_all($pattern, $subject, $matches);

		$subject_length = strlen((string)$subject);
        $nrows_char = ceil($subject_length / 80);

        return $nrows_newline + $nrows_char;
    }

    /**
    * displayTranslateFieldsFooter() Formats and displays footer of translation fields table
    * @return string $translateoutput
    */
    private function displayTranslateFieldsFooter()
    {
		$translateoutput = CHtml::closeTag("table");

        return $translateoutput;
    }

    /**
    * menuItem() creates a menu item with text and image in the admin screen menus
    * @param string $menuText
    * @return string
    */
    private function menuItem($menuText, $jsMenuText, $menuImageText, $menuImageFile, $scriptname)
    {
		$img_tag = CHtml::image(Yii::app()->getConfig("imageurl") . "/" . $menuImageFile, $jsMenuText, array('name'=>$menuImageText));
		$menuitem = CHtml::link($img_tag, '#', array(
			'onclick' => "window.open('{$scriptname}', '_top')",
			'title' => $menuText
		));
        return $menuitem;
    }

    /**
    * menuSeparator() creates a separator bar in the admin screen menus
    * @return string
    */
    private function menuSeparator()
    {
		$image = CHtml::image(Yii::app()->getConfig("imageurl") . "/seperator.gif", '');
        return $image;
    }

    /*
    * translate_google_api.php
    * Creates a JSON interface for the auto-translate feature
    */
    private function translate_google_api()
    {
        header('Content-type: application/json');

        $sBaselang   = CHttpRequest::getPost('baselang');
        $sTolang     = CHttpRequest::getPost('tolang');
        $sToconvert  = CHttpRequest::getPost('text');

        $aSearch     = array('zh-Hans','zh-Hant-HK','zh-Hant-TW',
						'nl-informal','de-informal','it-formal','pt-BR','es-MX','nb','nn');
        $aReplace    = array('zh-CN','zh-TW','zh-TW','nl','de','it','pt','es','no','no');

        $sTolang = str_replace($aSearch, $aReplace, $sTolang);

		$error = FALSE;
        try
		{
            Yii::app()->loadLibrary('admin/gtranslate/GTranslate');
			$gtranslate = new Gtranslate();
            $objGt = $gtranslate;

            // Gtranslate requires you to run function named XXLANG_to_XXLANG
            $sProcedure = $sBaselang . "_to_" . $sTolang;

            // Replace {TEXT} with <TEXT>. Text within <> act as a placeholder and are
            // not translated by Google Translate
            $sToNewconvert  = preg_replace("/\{(\w+)\}/", "<$1>" ,$sToconvert);
            $bDoNotConvertBack = FALSE;

            if ( $sToNewconvert == $sToconvert )
			{
                $bDoNotConvertBack = TRUE;
			}

            $sToconvert = $sToNewconvert;
            $sConverted = $objGt->$sProcedure($sToconvert);
            $sConverted = str_replace("<br>", "\r\n", $sConverted);

            if ( ! $bDoNotConvertBack )
			{
                $sConverted  = preg_replace("/\<(\w+)\>/", '{$1}', $sConverted);
			}

            $sOutput  = html_entity_decode(stripcslashes($sConverted));
        }
		catch ( GTranslateException $ge )
		{
            // Get the error message and build the ouput array
			$error = TRUE;
            $sOutput  = $ge->getMessage();
        }

		$aOutput = array(
			'error'     =>  $error,
			'baselang'  =>  $sBaselang,
			'tolang'    =>  $sTolang,
			'converted' =>  $sOutput
		);

        return ls_json_encode($aOutput) . "\n";
    }
}
