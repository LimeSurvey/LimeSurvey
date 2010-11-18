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
* $Id:
*
*/

  include_once("login_check.php");  //Login Check dies also if the script is started directly


    $js_admin_includes[]='scripts/answers.js';
    $js_admin_includes[]='../scripts/jquery/jquery.blockUI.js';
    $js_admin_includes[]='../scripts/jquery/jquery.selectboxes.min.js';

    $_SESSION['FileManagerContext']="edit:answer:$surveyid";
    // Get languages select on survey.
    $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);

    $qquery = "SELECT type FROM ".db_table_name('questions')." WHERE qid=$qid AND language='".$baselang."'";
    $qrow = $connect->GetRow($qquery);
    $qtype = $qrow['type'];
    $scalecount=$qtypes[$qtype]['answerscales'];
    //Check if there is at least one answer
    for ($i = 0; $i < $scalecount; $i++)
    {
        $qquery = "SELECT count(*) as num_ans  FROM ".db_table_name('answers')." WHERE qid=$qid AND scale_id=$i AND language='".$baselang."'";
        $qresult = $connect->GetOne($qquery); //Checked
        if ($qresult==0)
        {
            $query="INSERT into ".db_table_name('answers')." (qid,code,answer,language,sortorder,scale_id) VALUES ($qid,'A1',".db_quoteall($clang->gT("Some example answer option")).",'$baselang',0,$i)";
            $connect->execute($query);
        }
    }


    // check that there are answers for every language supported by the survey
    for ($i = 0; $i < $scalecount; $i++)
    {
        foreach ($anslangs as $language)
        {
            $iAnswerCount = $connect->GetOne("SELECT count(*) as num_ans  FROM ".db_table_name('answers')." WHERE qid=$qid AND scale_id=$i AND language='".$language."'");
            if ($iAnswerCount == 0)   // means that no record for the language exists in the answers table
            {
                $qquery = "INSERT INTO ".db_table_name('answers')." (qid,code,answer,sortorder,language,scale_id, assessment_value) (SELECT qid,code,answer,sortorder, '".$language."','$i', assessment_value FROM ".db_table_name('answers')." WHERE qid=$qid AND scale_id=$i AND language='".$baselang."')";
                $connect->Execute($qquery); //Checked
            }
        }
    }

    array_unshift($anslangs,$baselang);      // makes an array with ALL the languages supported by the survey -> $anslangs

    //delete the answers in languages not supported by the survey
    $languagequery = "SELECT DISTINCT language FROM ".db_table_name('answers')." WHERE (qid = $qid) AND (language NOT IN ('".implode("','",$anslangs)."'))";
    $languageresult = db_execute_assoc($languagequery); //Checked
    while ($qrow = $languageresult->FetchRow())
    {
        $deleteanswerquery = "DELETE FROM ".db_table_name('answers')." WHERE (qid = $qid) AND (language = '".$qrow["language"]."')";
        $connect->Execute($deleteanswerquery); //Checked
    }

    if (!isset($_POST['ansaction']))
    {
        //check if any nulls exist. If they do, redo the sortorders
        $caquery="SELECT * FROM ".db_table_name('answers')." WHERE qid=$qid AND sortorder is null AND language='".$baselang."'";
        $caresult=$connect->Execute($caquery); //Checked
        $cacount=$caresult->RecordCount();
        if ($cacount)
        {
            fixsortorderAnswers($qid);
        }
    }

    // Print Key Control JavaScript
    $vasummary = PrepareEditorScript("editanswer");

    $query = "SELECT sortorder FROM ".db_table_name('answers')." WHERE qid='{$qid}' AND language='".GetBaseLanguageFromSurveyID($surveyid)."' ORDER BY sortorder desc";
    $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked
    $anscount = $result->RecordCount();
    $row=$result->FetchRow();
    $maxsortorder=$row['sortorder']+1;
    $vasummary .= "<div class='header ui-widget-header'>\n"
    .$clang->gT("Edit answer options")
    ."</div>\n"
    ."<form id='editanswersform' name='editanswersform' method='post' action='$scriptname'>\n"
    . "<input type='hidden' name='sid' value='$surveyid' />\n"
    . "<input type='hidden' name='gid' value='$gid' />\n"
    . "<input type='hidden' name='qid' value='$qid' />\n"
    . "<input type='hidden' name='action' value='updateansweroptions' />\n"
    . "<input type='hidden' name='sortorder' value='' />\n";
    $vasummary .= "<div class='tab-pane' id='tab-pane-answers-$surveyid'>";
    $first=true;

    $vasummary .= "<div id='xToolbar'></div>\n";

    // the following line decides if the assessment input fields are visible or not
    $assessmentvisible=($surveyinfo['assessments']=='Y' && $qtypes[$qtype]['assessable']==1);

    // Insert some Javascript variables
    $surveysummary .= "\n<script type='text/javascript'>
                          var languagecount=".count($anslangs).";\n
                          var scalecount=".$scalecount.";
                          var assessmentvisible=".($assessmentvisible?'true':'false').";
                          var newansweroption_text='".$clang->gT('New answer option','js')."';
                          var strcode='".$clang->gT('Code','js')."';
                          var strlabel='".$clang->gT('Label','js')."';
                          var strCantDeleteLastAnswer='".$clang->gT('You cannot delete the last answer option.','js')."';
                          var lsbrowsertitle='".$clang->gT('Label set browser','js')."';
                          var quickaddtitle='".$clang->gT('Quick-add subquestions','js')."';
                          var duplicateanswercode='".$clang->gT('Error: You are trying to use duplicate answer codes.','js')."';
                          var langs='".implode(';',$anslangs)."';</script>\n";

    foreach ($anslangs as $anslang)
    {
        $vasummary .= "<div class='tab-page' id='tabpage_$anslang'>"
        ."<h2 class='tab'>".getLanguageNameFromCode($anslang, false);
        if ($anslang==GetBaseLanguageFromSurveyID($surveyid)) {$vasummary .= '('.$clang->gT("Base Language").')';}

        $vasummary .= "</h2>";

        for ($scale_id = 0; $scale_id < $scalecount; $scale_id++)
        {
            $position=0;
            if ($scalecount>1)
            {
                $vasummary.="<div class='header ui-widget-header' style='margin-top:5px;'>".sprintf($clang->gT("Answer scale %s"),$scale_id+1)."</div>";
            }


            $vasummary .= "<table class='answertable' id='answers_{$anslang}_$scale_id' align='center' >\n"
            ."<thead>"
            ."<tr>\n"
            ."<th align='right'>&nbsp;</th>\n"
            ."<th align='center'>".$clang->gT("Code")."</th>\n";
            if ($assessmentvisible)
            {
                $vasummary .="<th align='center'>".$clang->gT("Assessment value");
            }
            else
            {
                $vasummary .="<th style='display:none;'>&nbsp;";
            }

            $vasummary .= "</th>\n"
            ."<th align='center'>".$clang->gT("Answer option")."</th>\n"
            ."<th align='center'>".$clang->gT("Actions")."</th>\n"
            ."</tr></thead>"
            ."<tbody align='center'>";
            $alternate=true;

            $query = "SELECT * FROM ".db_table_name('answers')." WHERE qid='{$qid}' AND language='{$anslang}' and scale_id=$scale_id ORDER BY sortorder, code";
            $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked
            $anscount = $result->RecordCount();
            while ($row=$result->FetchRow())
            {
                $row['code'] = htmlspecialchars($row['code']);
                $row['answer']=htmlspecialchars($row['answer']);

                $vasummary .= "<tr class='row_$position ";
                if ($alternate==true)
                {
                    $vasummary.='highlight';
                }
                $alternate=!$alternate;

                $vasummary .=" '><td align='right'>\n";

                if ($first)
                {
                    $vasummary .= "<img class='handle' src='$imageurl/handle.png' /></td><td><input type='text' class='code' id='code_{$position}_{$scale_id}' name='code_{$position}_{$scale_id}' value=\"{$row['code']}\" maxlength='5' size='5'"
                    ." onkeypress=\"return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_')\""
                    ." />";
                }
                else
                {
                    $vasummary .= "&nbsp;</td><td>{$row['code']}";

                }

                $vasummary .= "</td>\n"
                ."<td\n";

                if ($assessmentvisible && $first)
                {
                    $vasummary .= "><input type='text' class='assessment' id='assessment_{$position}_{$scale_id}' name='assessment_{$position}_{$scale_id}' value=\"{$row['assessment_value']}\" maxlength='5' size='5'"
                    ." onkeypress=\"return goodchars(event,'-1234567890')\""
                    ." />";
                }
                elseif ( $first)
                {
                    $vasummary .= " style='display:none;'><input type='hidden' class='assessment' id='assessment_{$position}_{$scale_id}' name='assessment_{$position}_{$scale_id}' value=\"{$row['assessment_value']}\" maxlength='5' size='5'"
                    ." onkeypress=\"return goodchars(event,'-1234567890')\""
                    ." />";
                }
                elseif ($assessmentvisible)
                {
                    $vasummary .= '>'.$row['assessment_value'];
                }
                else
                {
                    $vasummary .= " style='display:none;'>";
                }

                $vasummary .= "</td><td>\n"
                ."<input type='text' class='answer' id='answer_{$row['language']}_{$row['sortorder']}_{$scale_id}' name='answer_{$row['language']}_{$row['sortorder']}_{$scale_id}' size='100' value=\"{$row['answer']}\" />\n"
                . getEditor("editanswer","answer_".$row['language']."_{$row['sortorder']}_{$scale_id}", "[".$clang->gT("Answer:", "js")."](".$row['language'].")",$surveyid,$gid,$qid,'editanswer');

                // Deactivate delete button for active surveys
                $vasummary.="</td><td><img src='$imageurl/addanswer.png' class='btnaddanswer' />";
                $vasummary.="<img src='$imageurl/deleteanswer.png' class='btndelanswer' />";

                $vasummary .= "</td></tr>\n";
                $position++;
            }
            $vasummary .='</table><br />';
            if ($first)
            {
                $vasummary .=  "<input type='hidden' id='answercount_{$scale_id}' name='answercount_{$scale_id}' value='$anscount' />\n";
            }
            $vasummary .= "<button id='btnlsbrowser_{$scale_id}' class='btnlsbrowser' type='button'>".$clang->gT('Predefined label sets...')."</button>";
            $vasummary .= "<button id='btnquickadd_{$scale_id}' class='btnquickadd' type='button'>".$clang->gT('Quick add...')."</button>";

        }

        $position=sprintf("%05d", $position);

        $first=false;
        $vasummary .= "</div>";
    }
    // Label set browser
//                      <br/><input type='checkbox' checked='checked' id='languagefilter' /><label for='languagefilter'>".$clang->gT('Match language')."</label>
    $vasummary .= "<div id='labelsetbrowser' style='display:none;'><div style='float:left;width:260px;'>
                      <label for='labelsets'>".$clang->gT('Available label sets:')."</label>
                      <br /><select id='labelsets' size='10' style='width:250px;'><option>&nbsp;</option></select>
                      <br /><button id='btnlsreplace' type='button'>".$clang->gT('Replace')."</button>
                      <button id='btnlsinsert' type='button'>".$clang->gT('Add')."</button>
                      <button id='btncancel' type='button'>".$clang->gT('Cancel')."</button></div>

                   <div id='labelsetpreview' style='float:right;width:500px;'></div></div> ";
    $vasummary .= "<div id='quickadd' style='display:none;'><div style='float:left;'>
                      <label for='quickadd'>".$clang->gT('Enter your subquestions:')."</label>
                      <br /><textarea id='quickaddarea' class='tipme' title='".$clang->gT('Enter one answer per line. You can provide a code by separating code and answer text with a semikolon or tab.')."' rows='30' style='width:570px;'></textarea>
                      <br /><button id='btnqareplace' type='button'>".$clang->gT('Replace')."</button>
                      <button id='btnqainsert' type='button'>".$clang->gT('Add')."</button>
                      <button id='btnqacancel' type='button'>".$clang->gT('Cancel')."</button></div>
                   </div> ";
    // Save button
    $vasummary .= "<p><input type='submit' id='saveallbtn_$anslang' name='method' value='".$clang->gT("Save changes")."' />\n";
    $vasummary .= "</div></form>";




?>
