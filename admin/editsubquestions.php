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

    $js_admin_includes[]='scripts/subquestions.js';
    $js_admin_includes[]='../scripts/jquery/jquery.blockUI.js';
    $js_admin_includes[]='../scripts/jquery/jquery.selectboxes.min.js';

    $_SESSION['FileManagerContext']="edit:answer:{$surveyid}";
    // Get languages select on survey.
    $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);

    $sQuery = "SELECT type FROM ".db_table_name('questions')." WHERE qid={$qid} AND language='{$baselang}'";
    $sQuestiontype=$connect->GetOne($sQuery);
    $aQuestiontypeInfo=getqtypelist($sQuestiontype,'array');
    $iScaleCount=$aQuestiontypeInfo[$sQuestiontype]['subquestions'];

    for ($iScale = 0; $iScale < $iScaleCount; $iScale++)
    {
        $sQuery = "SELECT * FROM ".db_table_name('questions')." WHERE parent_qid={$qid} AND language='{$baselang}' and scale_id={$iScale}";
        $subquestiondata=$connect->GetArray($sQuery);
    if (count($subquestiondata)==0)
    {
            $sQuery = "INSERT INTO ".db_table_name('questions')." (sid,gid,parent_qid,title,question,question_order,language,scale_id)
                       VALUES($surveyid,$gid,$qid,'SQ001',".db_quoteall($clang->gT('Some example subquestion')).",1,".db_quoteall($baselang).",{$iScale})";
            $connect->Execute($sQuery); //Checked
            $sQuery = "SELECT * FROM ".db_table_name('questions')." WHERE parent_qid={$qid} AND language='{$baselang}' and scale_id={$iScale}";
            $subquestiondata=$connect->GetArray($sQuery);
    }
    // check that there are subquestions for every language supported by the survey
    foreach ($anslangs as $language)
    {
        foreach ($subquestiondata as $row)
        {
                $sQuery = "SELECT count(*) FROM ".db_table_name('questions')." WHERE parent_qid={$qid} AND language='{$language}' AND qid={$row['qid']} and scale_id={$iScale}";
                $qrow = $connect->GetOne($sQuery); //Checked
            if ($qrow == 0)   // means that no record for the language exists in the questions table
            {
                    db_switchIDInsert('questions',true);
                    $sQuery = "INSERT INTO ".db_table_name('questions')." (qid,sid,gid,parent_qid,title,question,question_order,language, scale_id)
                               VALUES({$row['qid']},$surveyid,{$row['gid']},$qid,".db_quoteall($row['title']).",".db_quoteall($row['question']).",{$row['question_order']},".db_quoteall($language).",{$iScale})";
                    $connect->Execute($sQuery); //Checked
                    db_switchIDInsert('questions',false);
            }
        }
    }
    }


    array_unshift($anslangs,$baselang);      // makes an array with ALL the languages supported by the survey -> $anslangs

    $vasummary = "\n<script type='text/javascript'>
                      var languagecount=".count($anslangs).";\n
                      var newansweroption_text='".$clang->gT('New answer option','js')."';
                      var strcode='".$clang->gT('Code','js')."';
                      var strlabel='".$clang->gT('Label','js')."';
                      var strCantDeleteLastAnswer='".$clang->gT('You cannot delete the last subquestion.','js')."';
                      var lsbrowsertitle='".$clang->gT('Label set browser','js')."';
                      var quickaddtitle='".$clang->gT('Quick-add subquestions','js')."';
                      var duplicateanswercode='".$clang->gT('Error: You are trying to use duplicate subquestion codes.','js')."';
                      var langs='".implode(';',$anslangs)."';</script>\n";


    //delete the subquestions in languages not supported by the survey
    $qquery = "SELECT DISTINCT language FROM ".db_table_name('questions')." WHERE (parent_qid = $qid) AND (language NOT IN ('".implode("','",$anslangs)."'))";
    $qresult = db_execute_assoc($qquery); //Checked
    while ($qrow = $qresult->FetchRow())
    {
        $qquery = "DELETE FROM ".db_table_name('questions')." WHERE (parent_qid = $qid) AND (language = '".$qrow["language"]."')";
        $connect->Execute($qquery); //Checked
    }


    // Check sort order for subquestions
    $qquery = "SELECT type FROM ".db_table_name('questions')." WHERE qid=$qid AND language='".$baselang."'";
    $qresult = db_execute_assoc($qquery); //Checked
    while ($qrow=$qresult->FetchRow()) {$qtype=$qrow['type'];}
    if (!isset($_POST['ansaction']))
    {
        //check if any nulls exist. If they do, redo the sortorders
        $caquery="SELECT * FROM ".db_table_name('questions')." WHERE parent_qid=$qid AND question_order is null AND language='".$baselang."'";
        $caresult=$connect->Execute($caquery); //Checked
        $cacount=$caresult->RecordCount();
        if ($cacount)
        {
            fixsortorderAnswers($qid); // !!Adjust this!!
        }
    }

    // Print Key Control JavaScript
    $vasummary .= PrepareEditorScript("editanswer");

    $query = "SELECT question_order FROM ".db_table_name('questions')." WHERE parent_qid='{$qid}' AND language='".GetBaseLanguageFromSurveyID($surveyid)."' ORDER BY question_order desc";
    $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked
    $anscount = $result->RecordCount();
    $row=$result->FetchRow();
    $maxsortorder=$row['question_order']+1;
    $vasummary .= "<div class='header ui-widget-header'>\n"
    .$clang->gT("Edit subquestions")
    ."</div>\n"
    ."<form id='editsubquestionsform' name='editsubquestionsform' method='post' action='$scriptname'onsubmit=\"return codeCheck('code_',$maxsortorder,'".$clang->gT("Error: You are trying to use duplicate answer codes.",'js')."','".$clang->gT("Error: 'other' is a reserved keyword.",'js')."');\">\n"
    . "<input type='hidden' name='sid' value='$surveyid' />\n"
    . "<input type='hidden' name='gid' value='$gid' />\n"
    . "<input type='hidden' name='qid' value='$qid' />\n"
    . "<input type='hidden' id='action' name='action' value='updatesubquestions' />\n"
    . "<input type='hidden' id='sortorder' name='sortorder' value='' />\n"
    . "<input type='hidden' id='deletedqids' name='deletedqids' value='' />\n";
    $vasummary .= "<div class='tab-pane' id='tab-pane-assessments-$surveyid'>";
    $first=true;
    $sortorderids='';
    $codeids='';

    $vasummary .= "<div id='xToolbar'></div>\n";

    // the following line decides if the assessment input fields are visible or not
    // for some question types the assessment values is set in the label set instead of the answers
    $qtypes=getqtypelist('','array');

    $scalecount=$qtypes[$qtype]['subquestions'];
    foreach ($anslangs as $anslang)
    {
        $vasummary .= "<div class='tab-page' id='tabpage_$anslang'>"
        ."<h2 class='tab'>".getLanguageNameFromCode($anslang, false);
        if ($anslang==GetBaseLanguageFromSurveyID($surveyid)) {$vasummary .= '('.$clang->gT("Base language").')';}
        $vasummary .= "</h2>";

        for ($scale_id = 0; $scale_id < $scalecount; $scale_id++)
        {
            $position=0;
            if ($scalecount>1)
            {
                if ($scale_id==0)
                {
                    $vasummary .="<div class='header ui-widget-header'>\n".$clang->gT("Y-Scale")."</div>";
                }
                else
                {
                    $vasummary .="<div class='header ui-widget-header'>\n".$clang->gT("X-Scale")."</div>";
                }
            }
            $query = "SELECT * FROM ".db_table_name('questions')." WHERE parent_qid='{$qid}' AND language='{$anslang}' AND scale_id={$scale_id} ORDER BY question_order, title";
            $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked
            $anscount = $result->RecordCount();
            $vasummary .="<table class='answertable' id='answertable_{$anslang}_{$scale_id}' align='center'>\n"
            ."<thead>"
            ."<tr><th>&nbsp;</th>\n"
            ."<th align='right'>".$clang->gT("Code")."</th>\n"
            ."<th align='center'>".$clang->gT("Subquestion")."</th>\n";
            if ($activated != 'Y' && $first)
            {
                $vasummary .="<th align='center'>".$clang->gT("Action")."</th>\n";
            }
            $vasummary .="</tr></thead>"
            ."<tbody align='center'>";
            $alternate=false;
            while ($row=$result->FetchRow())
            {
                $row['title'] = htmlspecialchars($row['title']);
                $row['question']=htmlspecialchars($row['question']);

                if ($first) {$codeids=$codeids.' '.$row['question_order'];}

                $vasummary .= "<tr id='row_{$row['language']}_{$row['qid']}_{$row['scale_id']}'";
                if ($alternate==true)
                {
                    $vasummary.=' class="highlight" ';
                    $alternate=false;
                }
                else
                {
                    $alternate=true;
                }

                $vasummary .=" ><td align='right'>\n";

                if ($activated == 'Y' ) // if activated
                {
                    $vasummary .= "&nbsp;</td><td><input type='hidden' name='code_{$row['qid']}_{$row['scale_id']}' value=\"{$row['title']}\" maxlength='5' size='5'"
                    ." />{$row['title']}";
                }
                elseif ($activated != 'Y' && $first) // If survey is decactivated
                {
                    $vasummary .= "<img class='handle' src='$imageurl/handle.png' /></td><td><input type='hidden' class='oldcode' id='oldcode_{$row['qid']}_{$row['scale_id']}' name='oldcode_{$row['qid']}_{$row['scale_id']}' value=\"{$row['title']}\" /><input type='text' id='code_{$row['qid']}_{$row['scale_id']}' class='code' name='code_{$row['qid']}_{$row['scale_id']}' value=\"{$row['title']}\" maxlength='5' size='5'"
                    ." onkeypress=\" if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_$anslang').click(); return false;} return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_')\""
                    ." />";

                }
                else
                {
                    $vasummary .= "</td><td>{$row['title']}";

                }
                //      <img class='handle' src='$imageurl/handle.png' /></td><td>
                $vasummary .= "</td><td>\n"
                ."<input type='text' size='100' id='answer_{$row['language']}_{$row['qid']}_{$row['scale_id']}' name='answer_{$row['language']}_{$row['qid']}_{$row['scale_id']}' value=\"{$row['question']}\" onkeypress=\" if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_$anslang').click(); return false;}\" />\n"
                . getEditor("editanswer","answer_".$row['language']."_".$row['qid']."_{$row['scale_id']}", "[".$clang->gT("Subquestion:", "js")."](".$row['language'].")",$surveyid,$gid,$qid,'editanswer')
                ."</td>\n"
                ."<td>\n";

                // Deactivate delete button for active surveys
                if ($activated != 'Y' && $first)
                {
                    $vasummary.="<img src='$imageurl/addanswer.png' class='btnaddanswer' />";
                    $vasummary.="<img src='$imageurl/deleteanswer.png' class='btndelanswer' />";
                }

                $vasummary .= "</td></tr>\n";
                $position++;
            }
            ++$anscount;
            $vasummary .= "</tbody></table>\n";
            $disabled='';
            if ($activated == 'Y')
            {
                $disabled="disabled='disabled'";
            }
            $vasummary .= "<button class='btnlsbrowser' id='btnlsbrowser_{$scale_id}' $disabled type='button'>".$clang->gT('Predefined label sets...')."</button>";
            $vasummary .= "<button class='btnquickadd' id='btnquickadd_{$scale_id}' $disabled type='button'>".$clang->gT('Quick add...')."</button>";
        }

        $first=false;
        $vasummary .= "</div>";
    }

    // Label set browser
//                      <br/><input type='checkbox' checked='checked' id='languagefilter' /><label for='languagefilter'>".$clang->gT('Match language')."</label>
    $vasummary .= "<div id='labelsetbrowser' style='display:none;'><div style='float:left; width:260px;'>
                      <label for='labelsets'>".$clang->gT('Available label sets:')."</label>
                      <br /><select id='labelsets' size='10' style='width:250px;'><option>&nbsp;</option></select>
                      <br /><button id='btnlsreplace' type='button'>".$clang->gT('Replace')."</button>
                      <button id='btnlsinsert' type='button'>".$clang->gT('Add')."</button>
                      <button id='btncancel' type='button'>".$clang->gT('Cancel')."</button></div>
                   <div id='labelsetpreview' style='float:right;width:500px;'></div></div> ";
    $vasummary .= "<div id='quickadd' style='display:none;'><div style='float:left;'>
                      <label for='quickadd'>".$clang->gT('Enter your subquestions:')."</label>
                      <br /><textarea id='quickaddarea' class='tipme' title='".$clang->gT('Enter one subquestion per line. You can provide a code by separating code and subquestion text with a semikolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semikolon/tab.')."' rows='30' style='width:570px;'></textarea>
                      <br /><button id='btnqareplace' type='button'>".$clang->gT('Replace')."</button>
                      <button id='btnqainsert' type='button'>".$clang->gT('Add')."</button>
                      <button id='btnqacancel' type='button'>".$clang->gT('Cancel')."</button></div>
                   </div> ";
    $vasummary .= "<p>"
    ."<input type='submit' id='saveallbtn_$anslang' name='method' value='".$clang->gT("Save changes")."' />\n";
    $position=sprintf("%05d", $position);
    if ($activated == 'Y')
    {
        $vasummary .= "<p>\n"
        ."<font color='red' size='1'><i><strong>"
        .$clang->gT("Warning")."</strong>: ".$clang->gT("You cannot add/remove subquestions or edit their codes because the survey is active.")."</i></font>\n"
        ."</td>\n"
        ."</tr>\n";
    }

    $vasummary .= "</div></form>";




?>
