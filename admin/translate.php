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


  include_once("login_check.php");  //Login Check dies also if the script is started directly

  if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
  if (!isset($action)) {$action=returnglobal('action');}
  include_once('translate_functions.php');
  $js_admin_includes[]= $homeurl.'/scripts/translation.js';


//  $js_admin_includes[]= $homeurl.'/scripts/translation.js';
//  $js_admin_includes[]= $rooturl.'/scripts/jquery/jquery-ui.js';

  // TODO need to do some validation here on surveyid

  $surveyinfo=getSurveyInfo($surveyid);
  $tolang="";
  if (isset($_GET['tolang']))
  {
    $tolang = $_GET['tolang'];
  }
  elseif (isset($_POST['tolang']))
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
  $supportedLanguages = getLanguageData(false);



  $baselangdesc = $supportedLanguages[$baselang]['description'];
  if($tolang != "")
  {  
    $tolangdesc = $supportedLanguages[$tolang]['description'];
  }

  $translateoutput = "";
  $translateoutput .= "<form name='translatemenu' id='translatemenu' "
                   ."action='$scriptname' method='get' >";
  $translateoutput .= showTranslateAdminmenu($surveyid, $survey_title, $tolang, $scriptname);
  $translateoutput .= "</form>";

  $translateoutput .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>\n"; //CSS Firefox 2 transition fix

  $translateoutput .= "<div class='header ui-widget-header'>".$clang->gT("Translate survey")."</div>\n";
  
//  $tab_names=array("title", "description", "welcome", "end", "group", "group_desc", "question", "question_help", "answer");
//  $tab_names=array("title", "description", "invitation", "reminder");
  $tab_names=array("title", "welcome", "group", "question", "subquestions", "answer", "emailinvite", "emailreminder", "emailconfirmation", "emailregistration");


  if ($tolang != "" && $actionvalue=="translateSave")
  // Saves translated values to database
  {
    $tab_names_full = "";
    foreach($tab_names as $type)
    {
      $tab_names_full[] = $type;
      $amTypeOptions = setupTranslateFields($surveyid, $type, $tolang, $baselang);
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
            $amTypeOptions = setupTranslateFields($surveyid, $type, $tolang, $baselang, $id1, $id2, $new);
            $query = $amTypeOptions["queryupdate"];
            $connect->execute($query);
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

    $translateoutput .= "<div id=\"translationloading\" style=\"width: 100%; font-weight: bold; color: #000; text-align: center;\"><br />".$clang->gT("Loading Translations")."...<br /><br /></div>";

    $translateoutput .= "<form name='translateform' method='post' "
      ."action='$scriptname' id='translateform' >\n"
      ."<input type='hidden' name='sid' value='$surveyid' />\n"
      ."<input type='hidden' name='action' value='translate' />\n"
      ."<input type='hidden' name='actionvalue' value='translateSave' />\n"
      ."<input type='hidden' name='tolang' value='$tolang' />\n"
      ."<input type='hidden' name='checksessionbypost' value='".$_SESSION['checksessionpost']."' />\n";

    // set up tabs
    $translateoutput .= ""
      ."<div id=\"translationtabs\" style=\"display: none;\" >\n"
      ."\t<ul>\n";
        foreach($tab_names as $type)
        {
          $amTypeOptions = setupTranslateFields($surveyid, $type, $tolang, $baselang);
          $translateoutput .= ""
            ."\t\t<li><a href=\"#tab-".$type."\"><span>".$amTypeOptions["description"]."</span></a></li>\n";
        }
        $translateoutput .= ""
      ."\t</ul>\n";

    // Define content of each tab
    foreach($tab_names as $type)
    {
      $amTypeOptions = setupTranslateFields($surveyid, $type, $tolang, $baselang);

      $type2 = $amTypeOptions["associated"];
      if ($type2 != "")
      {
        $associated = TRUE;
        $amTypeOptions2 = setupTranslateFields($surveyid, $type2, $tolang, $baselang);
      }
      else
      {
        $associated = FALSE;
      }

      // Create tab names and heading
      $translateoutput .= "\t<div id='tab-".$type."'>\n";
      $translateoutput .= PrepareEditorScript("noneedforvalue");
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

        $translateoutput .= displayTranslateFieldsHeader($baselangdesc, $tolangdesc);
        while ($rowfrom = $resultbase->FetchRow())
        {
          $textfrom = htmlspecialchars_decode($rowfrom[$amTypeOptions["dbColumn"]]);

          if ($associated)
          {
            $rowfrom2 = $resultbase2->FetchRow();
            $textfrom2 = htmlspecialchars_decode($rowfrom2[$amTypeOptions2["dbColumn"]]);
          }

          $gid = NULL;
          if($amTypeOptions["gid"]==TRUE) $gid = $rowfrom['gid'];

          $qid = NULL;
          if($amTypeOptions["qid"]==TRUE) $qid = $rowfrom['qid'];

          $rowto  = $resultto->FetchRow();
          $textto = $rowto[$amTypeOptions["dbColumn"]];

          if ($associated)
          {
            $rowto2  = $resultto2->FetchRow();
            $textto2 = $rowto2[$amTypeOptions2["dbColumn"]];
          }

          if (strlen(trim((string)$textfrom)) > 0)
          {
            $all_fields_empty = FALSE;
            $evenRow = !($evenRow);
            // Display translation fields
            $translateoutput .= displayTranslateFields($surveyid, $gid, $qid, $type,
                    $amTypeOptions, $baselangdesc, $tolangdesc, $textfrom, $textto, $i, $rowfrom, $evenRow);
            if ($associated && strlen(trim((string)$textfrom2)) > 0)
            {
              $translateoutput .= displayTranslateFields($surveyid, $gid, $qid, $type2,
                      $amTypeOptions2, $baselangdesc, $tolangdesc, $textfrom2, $textto2, $i, $rowfrom2, $evenRow);
            }
          }
          else
          {
            $translateoutput .= "<input type='hidden' name='{$type}_newvalue[$i]' value='$textto' />";
          }
          ++$i;
        } // end while
        $translateoutput .= displayTranslateFieldsFooter();
        if ($all_fields_empty)
        {
          $translateoutput .= "<p>".$clang->gT("Nothing to translate on this page")."</p><br />";
        }
      $translateoutput .= "<input type='hidden' name='{$type}_size' value='$i' />";
      if ($associated)
      {
              $translateoutput .= "<input type='hidden' name='{$type2}_size' value='$i' />";
      }
      $translateoutput .= "</div>\n";  // tab-page

      } // end foreach

    // Submit button
    $translateoutput .= "<p><input type='submit' class='standardbtn' "
      ."value='".$clang->gT("Save")."' /></p>"
      ."\n";


    $translateoutput .= "</div>\n";
    $translateoutput .= "</form>\n";
  } // end if



?>