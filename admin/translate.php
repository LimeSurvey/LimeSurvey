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

//TODO Use javascript to create tabs, in style of conditions editor.  Create translation.js file
//TODO http://jqueryui.com/demos/tabs/
//TODO Look at tokens.php for example code (Search for tokens.js to load)

//TODO For database save, don't use getUpdateSQL, but block saves
//TODO modify code to prevent two users from saving across each other's work, with hidden $_POST fields

//TODO create explicit $gid and $qid variables in foreach(), for use by fckEditor, then contact lemeur

  include_once("login_check.php");  //Login Check dies also if the script is started directly

  if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
  if (!isset($action)) {$action=returnglobal('action');}
  include_once('translate_functions.php');
//  include_once(dirname(__FILE__).'/../common_functions.php');


  // TODO need to do some validation here on surveyid

  $surveyinfo=getSurveyInfo($surveyid);
  if (isset($_GET['tolang']))
  {
    $tolang = $_GET['tolang'];
  }
  elseif (isset($_POST['tolang']))
  {
    $tolang = $_POST['tolang'];
  }

  $actionvalue = "";
  if(isset($_POST['actionvalue']))
  {
    $actionvalue = $_POST['actionvalue'];
  }

  $survey_title = $surveyinfo['name'];
  $baselang = GetBaseLanguageFromSurveyID($surveyid);
  $supportedLanguages = getLanguageData(false);
  $baselangdesc = $supportedLanguages[$baselang]['description'];
  if(isset($tolang))
  {  
    $tolangdesc = $supportedLanguages[$tolang]['description'];
  }

  $translateoutput = "";
  $translateoutput .= "<form name='translateform' id='translateform' "
                   ."action='$scriptname' method='GET' />";
  $translateoutput .= showTranslateAdminmenu();
  $translateoutput .= "</form>";

  $translateoutput .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>\n"; //CSS Firefox 2 transition fix

  $translateoutput .= "<div class='header'>".$clang->gT("Translate survey")."</div>\n";
  $translateoutput .= "<div class='tab-page'>\n";

  $tab_names=array("title", "description", "welcome", "end", "group", "group_desc", "question", "question_help", "answer");

  if (isset($tolang) && $actionvalue=="translateSave")
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
          $new = $_POST["{$type}_newvalue_{$i}"];
          $id1 = $_POST["{$type}_id1_{$i}"];
          $id2 = $_POST["{$type}_id2_{$i}"];
          $transarray = setupTranslateFields($type);
          $query = $transarray["queryupdate"];
          $connect->execute($query);
        }
        ++$i;
      } // end while
    } // end foreach
    $actionvalue = "";
  } // end if

  if (isset($tolang))
  // Display tabs with fields to translate, as well as input fields for translated values
  {
//      $translateoutput .= "<form name='{$transarray["formname"]}' method='POST' "
//        ."action='$scriptname' id='{$transarray["formname"]}' />\n"
      $translateoutput .= "<form name='translateform' method='POST' "
        ."action='$scriptname' id='translateform' />\n"
        ."<input type='hidden' name='sid' value='$surveyid' />\n"
        ."<input type='hidden' name='action' value='translate' />\n"
        ."<input type='hidden' name='actionvalue' value='translateSave' />\n"
        ."<input type='hidden' name='tolang' value='$tolang' />\n";

    foreach($tab_names as $type)
    {
      $transarray = setupTranslateFields($type);
      // Create tab names and heading
      $translateoutput .= "<div class='tab-pane' id='tab-pane-$type'>\n";
      $translateoutput .= "<div class='tab-page'> <h2 class='tab'>" . $clang->gT($transarray["desc"]) . "</h2>\n";
      
      $translateoutput .= PrepareEditorScript("editlabel");
      // Setup form
        // start a counter in order to number the input fields for each record
        $i = 0;
        $all_fields_empty = TRUE;

        $querybase = $transarray["querybase"];
        $resultbase = db_execute_assoc($querybase);

        $queryto = $transarray["queryto"];
        $resultto = db_execute_assoc($queryto);


        while ($rowfrom = $resultbase->FetchRow())
        {
          $textfrom = htmlspecialchars_decode($rowfrom[$transarray["what"]]);
          $rowto = $resultto->FetchRow();
          $textto   = htmlspecialchars_decode($rowto[$transarray["what"]]);

          if (strlen(trim((string)$textfrom)) > 0)
          {
            $all_fields_empty = FALSE;
            $value1 = "";
            if ($transarray["id1"] != "")
            {
              $value1 = $rowfrom[$transarray["id1"]];
            }
            $value2 = "";
            if ($transarray["id2"] != "")
            {
              $value2 = $rowfrom[$transarray["id2"]];
            }
            $translateoutput .= "<input type='hidden' name='{$type}_id1_{$i}' value='{$value1}' />\n";
            $translateoutput .= "<input type='hidden' name='{$type}_id2_{$i}' value='{$value2}' />\n";
          
            $translateoutput .= "<div style=\"margin:10px 10%; border-top:1px solid #0000ff;\">\n"
               . '<table cellpadding="5px" cellspacing="0" align="center" width="100%" >'
                . '<colgroup valign="top" width="25%">'
                . '<colgroup valign="top" width="75%">'
                // Display text in original language
                . "<tr>\n"
                  . "<td>$baselangdesc</td>\n"
                  . "<td>$textfrom</td>\n"
                . "</tr>\n";
                $translateoutput .= "<tr>\n"
                    // Display text in foreign language
                  . "<td>$tolangdesc</td>\n"
                  . '<td>';
                    $nrows = max(calc_nrows($textfrom), calc_nrows($textto));
                    $translateoutput .= "<textarea cols='80' rows='$nrows+1' "
                      ."name='{$type}_newvalue_{$i}'>$textto</textarea>\n"
                      .getEditor("edit".$type , $type."_newvalue_".$i, $textto, $surveyid, '', '', $action);
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
      $translateoutput .= "</div>";  // tab-page

      } // end foreach

    $translateoutput .= "</div>\n";  // tab-pane
    $translateoutput .= '</div>'; // div class='tab-page'

    // Submit button
    $translateoutput .= "<p><input type='submit' class='standardbtn' "
      ."value='".$clang->gT("Save")."' /></p>"
      ."\n";


    $translateoutput .= '</div>';
    $translateoutput .= "</form>";
  } // end if



?>
