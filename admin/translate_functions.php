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


  function showTranslateAdminmenu()
{
   global $homedir, $scriptname, $surveyid, $setfont, $imagefiles, $clang, $debug, $action,
          $updateavailable, $updatebuild, $updateversion, $updatelastcheck,
          $databaselocation, $databaseuser, $databasepass;

   global $tolang;

  $fromlang = GetBaseLanguageFromSurveyID($surveyid);
  $supportedLanguages = getLanguageData(false);
  $langs = GetAdditionalLanguagesFromSurveyID($surveyid);

  $adminmenu = ""
    ."<div class='menubar'>\n"
      ."<div class='menubar-title'>\n"
        ."<strong>".$clang->gT("Translate survey")."</strong>\n"
      ."</div>\n" // class menubar-title
      ."<div class='menubar-main'>\n";


  $adminmenu .= ""
    ."<div class='menubar-left'>\n";

// Return to survey administration button

  $adminmenu .= ""
      ."<a href=\"#\" onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\"
          .title='".$clang->gTview("Return to survey administration")."'>"
          ."<img name='Administration' src='$imagefiles/home.png' alt='"
          .$clang->gT("Return to survey administration")."' /></a>\n"
      ."<img src='$imagefiles/blank.gif' alt='' width='11'  />\n";


  // Test / execute survey button
  $adminmenu .= ""
    ."<img src='$imagefiles/seperator.gif' alt='' />\n";

  
  // Test / execute survey button

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
  if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
  {
      $adminmenu .= "<a href=\"#\" accesskey='d' onclick=\"window.open('"
      . $publicurl."/index.php?sid=$surveyid&amp;newtest=Y&amp;lang=$baselang', '_blank')\" title=\"".$icontext2."\" >"
      . "<img src='$imagefiles/do.png' alt='$icontext' />"
      . "</a>\n";

  } else {
      $adminmenu .= "<a href='#' id='dosurvey' class='dosurvey'"
      . "title=\"".$icontext2."\" accesskey='d'>"
      . "<img  src='$imagefiles/do.png' alt='$icontext' />"
      . "</a>\n";

      $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
      $tmp_survlangs[] = $baselang;
      rsort($tmp_survlangs);
      // Test Survey Language Selection Popup
      $adminmenu .="<div class=\"langpopup\" id=\"dosurveylangpopup\">".$clang->gT("Please select a language:")."<ul>";
      foreach ($tmp_survlangs as $tmp_lang)
      {
          $adminmenu .= "<li><a accesskey='d' onclick=\"$('.dosurvey').qtip('hide');\" target='_blank' href='{$publicurl}/index.php?sid=$surveyid&amp;newtest=Y&amp;lang={$tmp_lang}'>".getLanguageNameFromCode($tmp_lang,false)."</a></li>";
      }
      $adminmenu .= "</ul></div>";
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
              ."<option {$selected} value='$scriptname?action=translate&sid={$surveyid}'>None</option>\n";
            foreach($langs as $lang)
            {
              $selected="";
              if ($tolang==$lang)
              {
                $selected = " selected='selected' ";
              }
              $tolangtext   = $supportedLanguages[$lang]['description'];
              $adminmenu .= "<option {$selected} value='$scriptname?action=translate&sid={$surveyid}&tolang={$lang}'> " . $tolangtext ." </option>\n";
            }
            $adminmenu .= ""
            ."</select>\n"
          ."</li>\n"
        ."</ul>\n"
      ."</div>\n";

  $adminmenu .= ""
    ."</div>\n";

  return($adminmenu);
}


function setupTranslateFields($type)
{
  global $dbprefix, $tolang, $fromlang, $surveyid, $new, $id1, $id2;
  
  switch ( $type )
  {
    case 'title':
      $transarray = array(
        "queryfrom" => "SELECT * "
                                    ."FROM ".db_table_name('surveys_languagesettings')
                                    ." WHERE surveyls_survey_id=".db_quoteall($surveyid)
                                      ." AND surveyls_language=".db_quoteall($fromlang),
        "queryto"   => "SELECT * "
                                    ."FROM ".db_table_name('surveys_languagesettings')
                                    ." WHERE surveyls_survey_id=".db_quoteall($surveyid)
                                      ." AND surveyls_language=".db_quoteall($tolang),
        "query" => "UPDATE ".db_table_name('surveys_languagesettings')
                         ." SET surveyls_title = ".db_quoteall($new, TRUE)
                         ." WHERE surveyls_survey_id=".db_quoteall($surveyid)
                           ." AND surveyls_language=".db_quoteall($tolang)." LIMIT 1",
        "id1"  => '',
        "id2"  => '',
        "what" => 'surveyls_title',
        "desc" => 'Survey title',
        "formname" => 'init_update'
      );
      break;

    case 'description':
      $transarray = array(
        "queryfrom" => "SELECT * "
                                    ."FROM {$dbprefix}surveys_languagesettings "
                                    ."WHERE surveyls_survey_id='{$surveyid}' "
                                      ."AND surveyls_language='{$fromlang}'  ",
        "queryto"   => "SELECT * "
                                     ."FROM {$dbprefix}surveys_languagesettings "
                                     ."WHERE surveyls_survey_id='{$surveyid}' "
                                       ."AND surveyls_language='{$tolang}'  ",
        "query" => "UPDATE {$dbprefix}surveys_languagesettings "
                         ."SET surveyls_description = '{$new}' "
                         ."WHERE surveyls_survey_id='{$surveyid}' "
                           ."AND surveyls_language='{$tolang}' LIMIT 1",
        "id1"  => '',
        "id2"  => '',
        "what" => 'surveyls_decription',
        "desc" => 'Survey description',
        "formname" => 'init_update'
      );
      break;

    case 'welcome':
      $transarray = array(
        "queryfrom" => "SELECT * "
                                    ."FROM {$dbprefix}surveys_languagesettings "
                                    ."WHERE surveyls_survey_id='{$surveyid}' "
                                      ."AND surveyls_language='{$fromlang}'  ",
        "queryto"   => "SELECT * "
                                     ."FROM {$dbprefix}surveys_languagesettings "
                                     ."WHERE surveyls_survey_id='{$surveyid}' "
                                       ."AND surveyls_language='{$tolang}'  ",
        "query" => "UPDATE {$dbprefix}surveys_languagesettings "
                         ."SET surveyls_welcometext = '{$new}' "
                         ."WHERE surveyls_survey_id='{$surveyid}' "
                           ."AND surveyls_language='{$tolang}' LIMIT 1",
        "id1"  => '',
        "id2"  => '',
        "what" => 'surveyls_welcometext',
        "desc" => 'Survey welcome text',
        "formname" => 'init_update',
      );
      break;

    case 'end':
      $transarray = array(
        "queryfrom" => "SELECT * "
                                    ."FROM {$dbprefix}surveys_languagesettings "
                                    ."WHERE surveyls_survey_id='{$surveyid}' "
                                      ."AND surveyls_language='{$fromlang}'  ",
        "queryto"   => "SELECT * "
                                     ."FROM {$dbprefix}surveys_languagesettings "
                                     ."WHERE surveyls_survey_id='{$surveyid}' "
                                       ."AND surveyls_language='{$tolang}'  ",
        "query" => "UPDATE {$dbprefix}surveys_languagesettings "
                         ."SET surveyls_endtext = '{$new}' "
                         ."WHERE surveyls_survey_id='{$surveyid}' "
                           ."AND surveyls_language='{$tolang}' LIMIT 1",
        "id1"  => '',
        "id2"  => '',
        "what" => 'surveyls_endtext',
        "desc" => 'Survey end text',
        "formname" => 'init_update'
      );
      break;

    case 'group':
      $transarray = array(
        "queryfrom" => "SELECT * "
                                     ."FROM {$dbprefix}groups "
                                     ."WHERE sid='{$surveyid}' "
                                       ."AND language='{$fromlang}' "
                                     ."ORDER BY gid ",
        "queryto"   => "SELECT * "
                                     ."FROM {$dbprefix}groups "
                                     ."WHERE sid='{$surveyid}' "
                                       ."AND language='{$tolang}' "
                                     ."ORDER BY gid ",
        "query" => "UPDATE {$dbprefix}groups "
                         ."SET group_name = '{$new}' "
                         ."WHERE gid = '{$id1}' "
                           ."AND sid='{$surveyid}' "
                           ."AND language='{$tolang}' LIMIT 1",
        "id1"  => 'gid',
        "id2"  => '',
        "what" => 'group_name',
        "desc" => 'Survey group headings',
        "formname" => 'group_update'
      );
      break;

    case 'group_desc':
      $transarray = array(
        "queryfrom" => "SELECT * "
                                     ."FROM {$dbprefix}groups "
                                     ."WHERE sid='{$surveyid}' "
                                       ."AND language='{$fromlang}' "
                                     ."ORDER BY gid ",
        "queryto"   => "SELECT *"
                                     ."FROM {$dbprefix}groups "
                                     ."WHERE sid='{$surveyid}' "
                                       ."AND language='{$tolang}' "
                                     ."ORDER BY gid ",
        "query" => "UPDATE {$dbprefix}groups "
                         ."SET description = '{$new}' "
                         ."WHERE gid = '{$id1}' "
                           ."AND sid='{$surveyid}' "
                           ."AND language='{$tolang}' LIMIT 1",
        "id1"  => 'gid',
        "id2"  => '',
        "what" => 'description',
        "desc" => 'Survey group decriptions',
        "formname" => 'group_update'
      );
      break;

    case'label':
      $transarray = array(
        "queryfrom" => "SELECT * "
                                   ."FROM {$dbprefix}labels "
                                   ."WHERE language='{$fromlang}' "
                                   .  "AND lid='$code' ",
        "queryto"   => "SELECT * "
                                    ."FROM {$dbprefix}labels "
                                    ."WHERE language='{$tolang}' "
                                    .  "AND lid='$code' ",
        "query" => "UPDATE {$dbprefix}labels "
                   ."SET title = '{$new}' "
                         ."WHERE lid = '{$id1}' "
                           ."AND code='{$id2}' "
                           ."AND language='{$tolang}' LIMIT 1",
        "what" => 'title',
        "id1"  => 'lid',
        "id2"  => 'code',
        "desc" => 'labels',
        "formname" => 'labels_update'
      );
      break;

    case 'question':
      $transarray = array(
        "queryfrom" => "SELECT * "
                                   ."FROM {$dbprefix}questions "
                                   ."WHERE sid='{$surveyid}' "
                                     ."AND language='{$fromlang}' "
                                   ."ORDER BY qid ",
        "queryto"   => "SELECT * "
                                    ."FROM {$dbprefix}questions "
                                    ."WHERE sid='{$surveyid}' "
                                      ."AND language='{$tolang}' "
                                    ."ORDER BY qid ",
        "query" => "UPDATE {$dbprefix}questions "
                         ."SET question = '{$new}' "
                         ."WHERE qid = '{$id1}' "
                           ."AND sid='{$surveyid}' "
                           ."AND language='{$tolang}' LIMIT 1",
        "what" => 'question',
        "id1"  => 'qid',
        "id2"  => '',
        "desc" => 'questions',
        "formname" => 'question_update'
      );
      break;

    case 'question_help':
      $transarray = array(
        "queryfrom" => "SELECT * "
                                     ."FROM {$dbprefix}questions "
                                     ."WHERE sid='{$surveyid}' "
                                       ."AND language='{$fromlang}' "
                                     ."ORDER BY qid ",
        "queryto"   => "SELECT * "
                                    ."FROM {$dbprefix}questions "
                                    ."WHERE sid='{$surveyid}' "
                                      ."AND language='{$tolang}' "
                                    ."ORDER BY qid ",
        "query" => "UPDATE {$dbprefix}questions "
                   ."SET help = '{$new}' "
                   ."WHERE qid = '{$id1}' "
                   ."AND sid='{$surveyid}' "
                   ."AND language='{$tolang}' LIMIT 1",
        "what" => 'help',
        "id1"  => 'qid',
        "id2"  => '',
        "desc" => 'question help',
        "formname" => 'question_update'
      );
      break;

    case 'answer':
      $transarray = array(
        "queryfrom" => "SELECT {$dbprefix}answers.* "
                                     ."FROM {$dbprefix}answers,{$dbprefix}questions "
                                    ."WHERE {$dbprefix}questions.sid =' $surveyid' "
                                      ."AND {$dbprefix}questions.qid = {$dbprefix}answers.qid "
                                      ."AND {$dbprefix}questions.language = {$dbprefix}answers.language "
                                      ."AND {$dbprefix}questions.language='{$fromlang}' "
                                    ."ORDER BY qid,code,sortorder" ,
        "queryto" => "SELECT {$dbprefix}answers.* "
                                   ."FROM {$dbprefix}answers,{$dbprefix}questions "
                                  ."WHERE {$dbprefix}questions.sid =' $surveyid' "
                                    ."AND {$dbprefix}questions.qid = {$dbprefix}answers.qid "
                                    ."AND {$dbprefix}questions.language = {$dbprefix}answers.language "
                                    ."AND {$dbprefix}questions.language='{$tolang}' "
                                  ."ORDER BY qid,code,sortorder" ,
        "query" => "UPDATE {$dbprefix}answers "
                         ."SET answer = '{$new}' "
                         ."WHERE qid = '{$id1}' "
                           ."AND code='{$id2}' "
                           ."AND language='{$tolang}' LIMIT 1",
        "what" => 'answer',
        "id1"  => 'qid',
        "id2"  => 'code',
        "desc" => 'sub-questions',
        "formname" => 'answers_update'
      );
      break;
  }
  return($transarray);
}


function strip_html_tags( $text )
{
    $text = preg_replace(
        array(
          // Remove invisible content
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu',
          // Add line breaks before and after blocks
            '@</?((address)|(blockquote)|(center)|(del))@iu',
            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
            '@</?((table)|(th)|(td)|(caption))@iu',
            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
            '@</?((frameset)|(frame)|(iframe))@iu',
        ),
        array(
            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
            "\n\$0", "\n\$0",
        ),
        $text );
    return strip_tags( $text );
}


function calc_nrows( $subject )
{
  // Determines the size of the text box
  // A proxy for box sixe is string length divided by 80
  $pattern = "(<br..?>)";
  //$pattern = "/\n/";
  $pattern = '[(<br..?>)(/\n/)]';
  $nrows_newline = preg_match_all($pattern, $subject, $matches);

  $nrows_char = ceil(strlen((string)$subject)/80);

  return $nrows_newline + $nrows_char;
}


?>