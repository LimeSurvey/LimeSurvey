<?php
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
 * menuItem() creates a menu item with text and image in the admin screen menus
 * @param string $menuText
 * @global string $clang, $imageurl
 * @return string
 */
function menuItem($menuText, $jsMenuText, $menuImageText, $menuImageFile, $scriptname)
{
  global $clang, $imageurl;
  $menu = ""
      ."<a href=\"#\" onclick=\"window.open('".$scriptname."', '_top')\""
          ."title='".$menuText."'>"
          ."<img name='".$menuImageText."' src='$imageurl/".$menuImageFile."' alt='"
          .$jsMenuText."' /></a>\n"
      ."<img src='$imageurl/blank.gif' alt='' width='11'  />\n";
  return $menu;
}

/**
 * menuSeparator() creates a separator bar in the admin screen menus
 * @global string $imageurl
 * @return string
 */
function menuSeparator()
{
  global $imageurl;
  return ("<img src='$imageurl/seperator.gif' alt='' />\n");
}

/**
 * showTranslateAdminmenu() creates the main menu options for the survey translation page
 * @param string $surveyid The survey ID
 * @param string $survey_title 
 * @param string $tolang
 * @param string $activated
 * @param string $scriptname
 * @global string $imageurl, $clang, $publicurl
 * @return string
 */
  function showTranslateAdminmenu($surveyid, $survey_title, $tolang, $scriptname)
{
   global $imageurl, $clang, $publicurl;

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
  $adminmenu .= menuItem($clang->gT("Return to survey administration"),
          $clang->gTview("Return to survey administration"),
          "Administration", "home.png", "$scriptname?sid=$surveyid");

  // Separator
  $adminmenu .= menuSeparator();
  
  // Test / execute survey button

  if ($tolang != "")
  {
    $sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
    $sumresult1 = db_select_limit_assoc($sumquery1, 1) ; //Checked
    $surveyinfo = $sumresult1->FetchRow();

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
        ."<span class=\"boxcaption\">".$clang->gT("Translate to").":</span>"
        ."<select onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
        if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) > 1)
        {
          $adminmenu .= "<option {$selected} value='$scriptname?action=translate&amp;sid={$surveyid}'>".$clang->gT("Please choose...")."</option>\n";
        }
          foreach($langs as $lang)
          {
            $selected="";
            if ($tolang==$lang)
            {
              $selected = " selected='selected' ";
            }
            $tolangtext   = $supportedLanguages[$lang]['description'];
            $adminmenu .= "<option {$selected} value='$scriptname?action=translate&amp;sid={$surveyid}&amp;tolang={$lang}'> " . $tolangtext ." </option>\n";
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
 * @global $dbprefix, $clang;
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
  global $dbprefix, $clang;
  
  switch ( $type )
  {
    case 'title':
      $amTypeOptions = array(
        "querybase" => "SELECT * "
                                    ."FROM ".db_table_name('surveys_languagesettings')
                                    ." WHERE surveyls_survey_id=".db_quoteall($surveyid)
                                      ." AND surveyls_language=".db_quoteall($baselang),
        "queryto"   => "SELECT * "
                                    ."FROM ".db_table_name('surveys_languagesettings')
                                    ." WHERE surveyls_survey_id=".db_quoteall($surveyid)
                                      ." AND surveyls_language=".db_quoteall($tolang),
        "queryupdate" => "UPDATE ".db_table_name('surveys_languagesettings')
                         ." SET surveyls_title = ".db_quoteall($new)
                         ." WHERE surveyls_survey_id=".db_quoteall($surveyid)
                           ." AND surveyls_language=".db_quoteall($tolang),
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
                                    ."FROM ".db_table_name('surveys_languagesettings')
                                    ."WHERE surveyls_survey_id=".db_quoteall($surveyid)
                                      ."AND surveyls_language='{$baselang}'  ",
        "queryto"   => "SELECT * "
                                    ."FROM ".db_table_name('surveys_languagesettings')
                                     ."WHERE surveyls_survey_id=".db_quoteall($surveyid)
                                       ."AND surveyls_language='{$tolang}'  ",
        "queryupdate" => "UPDATE ".db_table_name('surveys_languagesettings')
                         ."SET surveyls_description = ".db_quoteall($new)
                         ."WHERE surveyls_survey_id=".db_quoteall($surveyid)
                           ."AND surveyls_language='{$tolang}'",
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
                                    ."FROM ".db_table_name('surveys_languagesettings')
                                    ."WHERE surveyls_survey_id=".db_quoteall($surveyid)
                                      ."AND surveyls_language='{$baselang}'  ",
        "queryto"   => "SELECT * "
                                    ."FROM ".db_table_name('surveys_languagesettings')
                                     ."WHERE surveyls_survey_id=".db_quoteall($surveyid)
                                       ."AND surveyls_language='{$tolang}'  ",
        "queryupdate" => "UPDATE ".db_table_name('surveys_languagesettings')
                         ."SET surveyls_welcometext = ".db_quoteall($new)
                         ."WHERE surveyls_survey_id=".db_quoteall($surveyid)
                           ."AND surveyls_language='{$tolang}'",
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
                                    ."FROM ".db_table_name('surveys_languagesettings')
                                    ."WHERE surveyls_survey_id=".db_quoteall($surveyid)
                                      ."AND surveyls_language='{$baselang}'  ",
        "queryto"   => "SELECT * "
                                    ."FROM ".db_table_name('surveys_languagesettings')
                                     ."WHERE surveyls_survey_id=".db_quoteall($surveyid)
                                       ."AND surveyls_language='{$tolang}'  ",
        "queryupdate" => "UPDATE ".db_table_name('surveys_languagesettings')
                         ."SET surveyls_endtext = ".db_quoteall($new)
                         ."WHERE surveyls_survey_id=".db_quoteall($surveyid)
                           ."AND surveyls_language='{$tolang}'",
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
                                     ."FROM ".db_table_name('groups')
                                     ."WHERE sid=".db_quoteall($surveyid)
                                       ."AND language='{$baselang}' "
                                     ."ORDER BY gid ",
        "queryto"   => "SELECT * "
                                     ."FROM ".db_table_name('groups')
                                     ."WHERE sid=".db_quoteall($surveyid)
                                       ."AND language=".db_quoteall($tolang)
                                     ."ORDER BY gid ",
        "queryupdate" => "UPDATE ".db_table_name('groups')
                         ."SET group_name = ".db_quoteall($new)
                         ."WHERE gid = '{$id1}' "
                           ."AND sid=".db_quoteall($surveyid)
                           ."AND language='{$tolang}'",
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
                                     ."FROM ".db_table_name('groups')
                                     ."WHERE sid=".db_quoteall($surveyid)
                                       ."AND language='{$baselang}' "
                                     ."ORDER BY gid ",
        "queryto"   => "SELECT *"
                                     ."FROM ".db_table_name('groups')
                                     ."WHERE sid=".db_quoteall($surveyid)
                                       ."AND language=".db_quoteall($tolang)
                                     ."ORDER BY gid ",
        "queryupdate" => "UPDATE ".db_table_name('groups')
                         ."SET description = ".db_quoteall($new)
                         ."WHERE gid = '{$id1}' "
                           ."AND sid=".db_quoteall($surveyid)
                           ."AND language='{$tolang}'",
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
//                                   ."FROM ".db_table_name('labels')
//                                   ."WHERE language='{$baselang}' "
//                                   .  "AND lid='$code' ",
//        "queryto"   => "SELECT * "
//                                    ."FROM ".db_table_name('labels')
//                                    ."WHERE language=".db_quoteall($tolang)
//                                    .  "AND lid='$code' ",
//        "queryupdate" => "UPDATE ".db_table_name('labels')
//                   ."SET title = ".db_quoteall($new)
//                         ."WHERE lid = '{$id1}' "
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
                                   ."FROM ".db_table_name('questions')
                                   ."WHERE sid=".db_quoteall($surveyid)
                                     ."AND language='{$baselang}' "
                                     ."AND parent_qid=0 "
                                   ."ORDER BY qid ",
        "queryto"   => "SELECT * "
                                    ."FROM ".db_table_name('questions')
                                    ."WHERE sid=".db_quoteall($surveyid)
                                      ."AND language='{$tolang}' "
                                     ." AND parent_qid=0 "
                                    ."ORDER BY qid ",
        "queryupdate" => "UPDATE ".db_table_name('questions')
                         ."SET question = ".db_quoteall($new)
                         ."WHERE qid = '{$id1}' "
                           ."AND sid=".db_quoteall($surveyid)
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
                                   ."FROM ".db_table_name('questions')
                                   ."WHERE sid=".db_quoteall($surveyid)
                                     ."AND language='{$baselang}' "
                                     ."AND parent_qid=0 "
                                   ."ORDER BY qid ",
        "queryto"   => "SELECT * "
                                    ."FROM ".db_table_name('questions')
                                    ."WHERE sid=".db_quoteall($surveyid)
                                      ."AND language='{$tolang}' "
                                     ." AND parent_qid=0 "
                                    ."ORDER BY qid ",
        "queryupdate" => "UPDATE ".db_table_name('questions')
                         ."SET help = ".db_quoteall($new)
                         ."WHERE qid = '{$id1}' "
                           ."AND sid=".db_quoteall($surveyid)
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
                                   ."FROM ".db_table_name('questions')
                                   ."WHERE sid=".db_quoteall($surveyid)
                                     ."AND language='{$baselang}' AND parent_qid>0 "
                                   ."ORDER BY parent_qid,qid ",
        "queryto"   => "SELECT * "
                                    ."FROM ".db_table_name('questions')
                                    ."WHERE sid=".db_quoteall($surveyid)
                                      ."AND language=".db_quoteall($tolang)
                                    ." AND parent_qid>0 ORDER BY parent_qid,qid ",
        "queryupdate" => "UPDATE ".db_table_name('questions')
                         ."SET question = ".db_quoteall($new)
                         ."WHERE qid = '{$id1}' "
                           ."AND sid=".db_quoteall($surveyid)
                           ."AND language='{$tolang}'",
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
        "querybase" => "SELECT".db_table_name('answers').".*, ".db_table_name('questions').".gid "
                                     ." FROM ".db_table_name('answers').", ".db_table_name('questions')
                                    ." WHERE ".db_table_name('questions').".sid ='{$surveyid}' "
                                      ." AND ".db_table_name('questions').".qid = ".db_table_name('answers').".qid "
                                      ." AND ".db_table_name('questions').".language = ".db_table_name('answers').".language "
                                      ." AND ".db_table_name('questions').".language='{$baselang}' "
                                    ." ORDER BY qid,code,sortorder" ,
        "queryto" => "SELECT".db_table_name('answers').".*, ".db_table_name('questions').".gid "
                                     ." FROM ".db_table_name('answers').", ".db_table_name('questions')
                                    ." WHERE ".db_table_name('questions').".sid ='{$surveyid}' "
                                      ." AND ".db_table_name('questions').".qid = ".db_table_name('answers').".qid "
                                      ." AND ".db_table_name('questions').".language = ".db_table_name('answers').".language "
                                      ." AND ".db_table_name('questions').".language=".db_quoteall($tolang)
                                  ."ORDER BY qid,code,sortorder" ,
        "queryupdate" => "UPDATE ".db_table_name('answers')
                         ."SET answer = ".db_quoteall($new)
                         ."WHERE qid = '{$id1}' "
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
        "querybase" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                       ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
        "queryto" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                             ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
        "queryupdate" => "UPDATE ".db_table_name("surveys_languagesettings")
                           ."SET surveyls_email_invite_subj = ".db_quoteall($new)
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
        "querybase" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                       ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
        "queryto" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                             ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
        "queryupdate" => "UPDATE ".db_table_name("surveys_languagesettings")
                           ."SET surveyls_email_invite = ".db_quoteall($new)
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
        "querybase" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                       ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
        "queryto" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                             ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
        "queryupdate" => "UPDATE ".db_table_name("surveys_languagesettings")
                           ."SET surveyls_email_remind_subj = ".db_quoteall($new)
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
        "querybase" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                       ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
        "queryto" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                             ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
        "queryupdate" => "UPDATE ".db_table_name("surveys_languagesettings")
                           ."SET surveyls_email_remind = ".db_quoteall($new)
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
        "querybase" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                       ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
        "queryto" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                             ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
        "queryupdate" => "UPDATE ".db_table_name("surveys_languagesettings")
                           ."SET surveyls_email_confirm_subj = ".db_quoteall($new)
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
        "querybase" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                       ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
        "queryto" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                             ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
        "queryupdate" => "UPDATE ".db_table_name("surveys_languagesettings")
                           ."SET surveyls_email_confirm = ".db_quoteall($new)
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
        "querybase" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                       ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
        "queryto" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                             ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
        "queryupdate" => "UPDATE ".db_table_name("surveys_languagesettings")
                           ."SET surveyls_email_register_subj = ".db_quoteall($new)
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
        "querybase" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                       ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
        "queryto" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                             ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
        "queryupdate" => "UPDATE ".db_table_name("surveys_languagesettings")
                           ."SET surveyls_email_register = ".db_quoteall($new)
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
        "querybase" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                       ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
        "queryto" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                             ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
        "queryupdate" => "UPDATE ".db_table_name("surveys_languagesettings")
                           ."SET surveyls_email_confirm_subj = ".db_quoteall($new)
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
        "querybase" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                       ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$baselang'" ,
        "queryto" => "SELECT * FROM ".db_table_name("surveys_languagesettings")
                             ." WHERE surveyls_survey_id=$surveyid and surveyls_language='$tolang'" ,
        "queryupdate" => "UPDATE ".db_table_name("surveys_languagesettings")
                           ."SET surveyls_email_confirm = ".db_quoteall($new)
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
  $translateoutput = "<div class=\"translate\" >"
   .'<table class="translate">'
      . '<colgroup valign="top" width="45%" />'
      . '<colgroup valign="top" width="55%" />'
      . "<tr>\n"
        . "<td><b>$baselangdesc</b></td>\n"
        . "<td><b>$tolangdesc</b></td>\n"
      . "</tr>\n";
  return($translateoutput);
}


/**
 * displayTranslateFieldsHeader() Formats and displays footer of translation fields table
 * @return string $translateoutput
 */
function displayTranslateFieldsFooter()
{
  $translateoutput = ""
    . "</table>\n"
  . "</div>\n";
  return($translateoutput);
}


/**
 * displayTranslateFields() Formats and displays translation fields (base language as well as to language)
 * @global $dbprefix, $clang;
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
    . "<td class='_from_' id='${type}_from_${i}' lan=''>$textfrom</td>\n"
    . "<td>\n";
      $translateoutput .= "<input type='hidden' name='{$type}_id1_{$i}' value='{$value1}' />\n";
      $translateoutput .= "<input type='hidden' name='{$type}_id2_{$i}' value='{$value2}' />\n";
      $nrows = max(calc_nrows($textfrom), calc_nrows($textto));
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
          .getPopupEditor("edit".$type , $type."_newvalue_".$i, htmlspecialchars($textto), $surveyid, $gid, $qid, "translate".$amTypeOptions["HTMLeditorType"]);
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


?>