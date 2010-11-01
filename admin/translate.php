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

//TODO Use javascript to create tabs, in style of conditions editor.  Create translation.js file (http://jqueryui.com/demos/tabs/)
//TODO For database save, don't use getUpdateSQL, but block saves

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

  $translateoutput .= "<div class='header'>".$clang->gT("Translate survey")."</div>\n";
  
  $tab_names=array("title", "description", "welcome", "end", "group", "group_desc", "question", "question_help", "answer");



  if ($tolang != "" && $actionvalue=="translateSave")
  // Saves translated values to database
  {
    foreach($tab_names as $type)
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

    $translateoutput .= "<form name='translateform' method='post' "
      ."action='$scriptname' id='translateform' >\n"
      ."<input type='hidden' name='sid' value='$surveyid' />\n"
      ."<input type='hidden' name='action' value='translate' />\n"
      ."<input type='hidden' name='actionvalue' value='translateSave' />\n"
      ."<input type='hidden' name='tolang' value='$tolang' />\n"
      ."<input type='hidden' name='checksessionbypost' value='".$_SESSION['checksessionpost']."' />\n";

    // set up tabs
    $translateoutput .= ""
      ."<div id=\"translationtabs\">\n"
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
      // Create tab names and heading
      $translateoutput .= "\t<div id='tab-".$type."'>\n";
      $translateoutput .= PrepareEditorScript("noneedforvalue");
      // Setup form
        // start a counter in order to number the input fields for each record
        $i = 0;
        $all_fields_empty = TRUE;

        $querybase = $amTypeOptions["querybase"];
        $resultbase = db_execute_assoc($querybase);

        $queryto = $amTypeOptions["queryto"];
        $resultto = db_execute_assoc($queryto);

        while ($rowfrom = $resultbase->FetchRow())
        {
          $textfrom = htmlspecialchars_decode($rowfrom[$amTypeOptions["dbColumn"]]);
          $gid = NULL;
          if($amTypeOptions["gid"]==TRUE)
          {  
            $gid = $rowfrom['gid'];
          }
          $qid = NULL;
          if($amTypeOptions["qid"]==TRUE)
          {
            $qid = $rowfrom['qid'];
          }
          $rowto = $resultto->FetchRow();
          $textto   = htmlspecialchars_decode($rowto[$amTypeOptions["dbColumn"]]);

          if (strlen(trim((string)$textfrom)) > 0)
          {
            $all_fields_empty = FALSE;
            $value1 = "";
            if ($amTypeOptions["id1"] != "")
            {
              $value1 = $rowfrom[$amTypeOptions["id1"]];
            }
            $value2 = "";
            if ($amTypeOptions["id2"] != "")
            {
              $value2 = $rowfrom[$amTypeOptions["id2"]];
            }
            $translateoutput .= "<input type='hidden' name='{$type}_id1_{$i}' value='{$value1}' />\n";
            $translateoutput .= "<input type='hidden' name='{$type}_id2_{$i}' value='{$value2}' />\n";
          
            $translateoutput .= "<div style=\"margin:10 10%; border-top:1px solid #0000ff;\">\n"
               . '<table cellpadding="5" cellspacing="0" align="center" width="100%" >'
                . '<colgroup valign="top" width="25%">'
                . '<colgroup valign="top" width="75%">'
                // Display text in original language
                . "<tr>\n"
                  . "<td>$baselangdesc</td>\n"
                  . "<td>$textfrom</td>\n"
                . "</tr>\n";
                $translateoutput .= "<tr>"
                    // Display text in foreign language. Save a copy in type_oldvalue_i to identify changes before db update
                  . "<td>$tolangdesc</td>\n"
                  . "<td>\n";
                    $nrows = max(calc_nrows($textfrom), calc_nrows($textto));
                    $translateoutput .= "<input type='hidden' "
                      ."name='".$type."_oldvalue_".$i."' "
                      ."value='".htmlspecialchars($textto, ENT_QUOTES)."' />\n";
                    $translateoutput .= "<textarea cols='80' rows='".($nrows)."' "
                      ." name='{$type}_newvalue_{$i}' >".htmlspecialchars($textto)."</textarea>\n";

                    if ($amTypeOptions["HTMLeditorInline"]=="Yes")
                    {  
                      $translateoutput .= ""
                        .getEditor("edit".$type , $type."_newvalue_".$i, $textto, $surveyid, $gid, $qid, "translate".$type);
                    }
                    else
                    {
                      $translateoutput .= ""
                        .getPopupEditor("edit".$type , $type."_newvalue_".$i, $textto, $surveyid, $gid, $qid, "translate".$type);
                    }
                    $translateoutput .= "</td>\n"
                . "</tr>\n"
              . "</table>\n"
            . "</div>\n";
          }
          else
          {
            $translateoutput .= "<input type='hidden' name='{$type}_newvalue[$i]' value='$textto' />";
          }
          ++$i;
        } // end while
        if ($all_fields_empty)
        {
          $translateoutput .= "<p>".$clang->gT("Nothing to translate on this page")."</p><br />";
        }
      $translateoutput .= "<input type='hidden' name='{$type}_size' value='$i' />";
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
