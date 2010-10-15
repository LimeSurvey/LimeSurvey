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
  $fromlang = GetBaseLanguageFromSurveyID($surveyid);
  $supportedLanguages = getLanguageData(false);
  $fromlangdesc = $supportedLanguages[$fromlang]['description'];
  $tolangdesc = $supportedLanguages[$tolang]['description'];

  $translateoutput .= "<form name='translateform' id='translateform' "
                   ."action='$scriptname' method='POST' />";
  $translateoutput .= showTranslateAdminmenu();
  $translateoutput .= "</form>";

  $translateoutput .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>\n"; //CSS Firefox 2 transition fix

  $translateoutput .= "<div class='header'>".$clang->gT("Translate survey")."</div>\n";
  $translateoutput .= "<div class='tab-page'>\n";

  // TODO The following line has been shortened for performance reasons during testing.
//  $tab_names=array("title", "description", "welcome", "end", "group", "group_desc", "label", "question", "question_help", "answer");
  $tab_names=array("title", "description", "welcome", "end", "group");


  if (isset($tolang) && $actionvalue=="translateSave")
  // Saves translated values to database
  {
    foreach($tab_names as $type)
    {
      $size = $_POST["{$type}_size"];
      // start a loop in order to update each record
      $i = 0;
      // TODO Remove comments from while loop
      while ($i < $size)
      {
        // define each variable
        $new = $_POST["{$type}_newvalue"][$i];
        $id1 = $_POST["{$type}_id1"][$i];
        $id2 = $_POST["{$type}_id2"][$i];
        $transarray = setupTranslateFields($type);
        $query = $transarray["query"];
        $connect->execute($query);

        // TODO Delete following lines - for debugging only
//        $translateoutput .= "type = $type <br />\n";
//        $translateoutput .= "size = $size <br />\n";
//        $translateoutput .= $type."_id1[$i] = $id1 <br />\n";
//        $translateoutput .= $type."_id2[$i] = $id2 <br />\n";
//        $translateoutput .= "new = $new <br />\n";
//        $translateoutput .= "query = {$transarray["query"]} <br />\n";
//        $translateoutput .= "<br />\n";

        ++$i;
      } // end while
    } // end foreach
    $actionvalue = "";

  } // end if

  if (isset($tolang))
  // Display tabs with fields to translate, as well as input fields for translated values
  {
    foreach($tab_names as $type)
    {
      $transarray = setupTranslateFields($type);
      // Create tab names and heading
      $translateoutput .= "<div class='tab-pane' id='tab-pane-$type'>\n";
      $translateoutput .= "<div class='tab-page'> <h2 class='tab'>" . $transarray["desc"] . "</h2>\n";
      
      // Setup form
      $translateoutput .= "<form name='{$transarray["formname"]}' method='POST' "
        ."action='$scriptname' id='{$transarray["formname"]}' />\n"
        ."<input type='hidden' name='sid' value='$surveyid' />\n"
        ."<input type='hidden' name='action' value='translate' />\n"
        ."<input type='hidden' name='actionvalue' value='translateSave' />\n"
        ."<input type='hidden' name='tolang' value='$tolang' />\n"
        ."<input type='hidden' name='transtype' value='$type' />\n";

      // start a counter in order to number the input fields for each record
      $i = 0;
      $all_fields_empty = TRUE;

      $queryfrom = $transarray["queryfrom"];
      $resultfrom = db_execute_assoc($queryfrom);

      $queryto = $transarray["queryto"];
      $resultto = db_execute_assoc($queryto);


//      while ($rowfrom = mysql_fetch_array($transarray["queryfrom"]))
      while ($rowfrom = $resultfrom->FetchRow())
      {
        // TODO Decide whether to strip HTML tags or not
        $textfrom = htmlspecialchars_decode($rowfrom[$transarray["what"]]);
//        $rowto    = mysql_fetch_array($transarray["queryto"]);
        $rowto = $resultto->FetchRow();
        $textto   = strip_tags(htmlspecialchars_decode($rowto[$transarray["what"]]));

        if (strlen(trim((string)$textfrom)) > 0)
        {
          $all_fields_empty = FALSE;
          $translateoutput .= "<input type='hidden' name='{$type}_id1[{$i}]' value='{$rowfrom[$transarray["id1"]]}' />\n";
          $translateoutput .= "<input type='hidden' name='{$type}_id2[{$i}]' value='{$rowfrom[$transarray["id2"]]}' />\n";

          $translateoutput .= "<div style=\"margin:10px 10%; border-top:1px solid #0000ff;\">\n"
             . '<table cellpadding="5px" cellspacing="0" align="center" width="100%" >'
              . '<colgroup valign="top" width="25%">'
              . '<colgroup valign="top" width="75%">'
              // Display text in original language
              . "<tr>\n"
                . "<td>$fromlangdesc</td>\n"
                . "<td><b>$textfrom</b></td>\n"
              . "</tr>\n";

              $translateoutput .= "<tr>\n"
                  // Display text in foreign language
                . "<td>$tolangdesc</td>\n"
                . '<td>'
                  . "<style> textwidth {width: 500px;} </style>\n";

                  // TODO Modify code to use HTML FCKeditor
                  $nrows = max(calc_nrows($textfrom), calc_nrows($textto));
                  if ($nrows==1)
                  {
                    $translateoutput .= "<input type='text' class='textwidth' "
                      ."name='{$type}_newvalue[$i]' value='$textto' />";
                  }
                  else
                  {
                    $translateoutput .= "<textarea class='textwidth' rows='$nrows+1' "
                      ."name='{$type}_newvalue[$i]'>$textto</textarea>";
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