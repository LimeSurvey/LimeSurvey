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
      ."<a href=\"#\" onclick=\"window.open('".$scriptname."', '_top')\"
          .title='".$menuText."'>"
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
      ."<div class='menubar-title'>\n"
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
        ."<ul>"
          ."<li>"
            ."<label for='language'>" . $clang->gT("Translate to: ") . "</label>\n"
              ."<select onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n"
              ."<option {$selected} value='$scriptname?action=translate&amp;sid={$surveyid}'>".$clang->gT("Please choose...")."</option>\n";
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
          ."</li>\n"
        ."</ul>\n"
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
 * @global $dbprefix, $surveyid, $clang;
 * @param string $surveyid Survey id
 * @param string $type Type of database field that is being translated, e.g. title, question, etc.
 * @param string $baselang The source translation language
 * @param string $tolang The target translation language
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
                           ." AND surveyls_language=".db_quoteall($tolang)." LIMIT 1",
        "id1"  => "",
        "id2"  => "",
        "gid"  => FALSE,
        "qid"  => FALSE,
        "dbColumn" => 'surveyls_title',
        "description" => $clang->gT("Survey title"),
        "HTMLeditorInline"  => "Yes"
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
                           ."AND surveyls_language='{$tolang}' LIMIT 1",
        "id1"  => "",
        "id2"  => "",
        "gid"  => FALSE,
        "qid"  => FALSE,
        "dbColumn" => 'surveyls_description',
        "description" => $clang->gT("Description:"),
        "HTMLeditorInline"  => "Yes"
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
                           ."AND surveyls_language='{$tolang}' LIMIT 1",
        "id1"  => "",
        "id2"  => "",
        "gid"  => FALSE,
        "qid"  => FALSE,
        "dbColumn" => 'surveyls_welcometext',
        "description" => $clang->gT("Welcome:"),
        "HTMLeditorInline"  => "Yes"
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
                           ."AND surveyls_language='{$tolang}' LIMIT 1",
        "id1"  => "",
        "id2"  => "",
        "gid"  => FALSE,
        "qid"  => FALSE,
        "dbColumn" => 'surveyls_endtext',
        "description" => $clang->gT("End message:"),
        "HTMLeditorInline"  => "Yes"
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
                           ."AND language='{$tolang}' LIMIT 1",
        "id1"  => "gid",
        "id2"  => "",
        "gid"  => TRUE,
        "qid"  => FALSE,
        "dbColumn" => "group_name",
        "description" => $clang->gT("Question groups"),
        "HTMLeditorInline"  => "No"
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
                           ."AND language='{$tolang}' LIMIT 1",
        "id1"  => "gid",
        "id2"  => "",
        "gid"  => TRUE,
        "qid"  => FALSE,
        "dbColumn" => "description",
        "description" => $clang->gT("Group description"),
        "HTMLeditorInline"  => "No"
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
                                   ."ORDER BY qid ",
        "queryto"   => "SELECT * "
                                    ."FROM ".db_table_name('questions')
                                    ."WHERE sid=".db_quoteall($surveyid)
                                      ."AND language=".db_quoteall($tolang)
                                    ."ORDER BY qid ",
        "queryupdate" => "UPDATE ".db_table_name('questions')
                         ."SET question = ".db_quoteall($new)
                         ."WHERE qid = '{$id1}' "
                           ."AND sid=".db_quoteall($surveyid)
                           ."AND language='{$tolang}' LIMIT 1",
        "dbColumn" => 'question',
        "id1"  => 'qid',
        "id2"  => "",
        "gid"  => TRUE,
        "qid"  => TRUE,
        "description" => $clang->gT("Questions"),
        "HTMLeditorInline"  => "No"
      );
      break;

    case 'question_help':
      $amTypeOptions = array(
        "querybase" => "SELECT * "
                                     ."FROM ".db_table_name('questions')
                                     ."WHERE sid=".db_quoteall($surveyid)
                                       ."AND language='{$baselang}' "
                                     ."ORDER BY qid ",
        "queryto"   => "SELECT * "
                                    ."FROM ".db_table_name('questions')
                                    ."WHERE sid=".db_quoteall($surveyid)
                                      ."AND language=".db_quoteall($tolang)
                                    ."ORDER BY qid ",
        "queryupdate" => "UPDATE ".db_table_name('questions')
                   ."SET help = ".db_quoteall($new)
                   ."WHERE qid = '{$id1}' "
                   ."AND sid=".db_quoteall($surveyid)
                   ."AND language='{$tolang}' LIMIT 1",
        "dbColumn" => 'help',
        "id1"  => 'qid',
        "id2"  => "",
        "gid"  => TRUE,
        "qid"  => TRUE,
        "description" => $clang->gT("Help"),
        "HTMLeditorInline"  => "No"
      );
      break;

    case 'answer':
      $amTypeOptions = array(
//        "querybase" => "SELECT {$dbprefix}answers.* "
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
                           ."AND language='{$tolang}' LIMIT 1",
        "dbColumn" => 'answer',
        "id1"  => 'qid',
        "id2"  => 'code',
        "gid"  => TRUE,
        "qid"  => TRUE,
        "description" => $clang->gT("Subquestions"),
        "HTMLeditorInline"  => "No"
      );
      break;
  }
  return($amTypeOptions);
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
