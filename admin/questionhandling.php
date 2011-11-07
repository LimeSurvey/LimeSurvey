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
 */


//Ensure script is not run directly, avoid path disclosure
include_once("login_check.php");

if (isset($_POST['sortorder'])) {$postsortorder=sanitize_int($_POST['sortorder']);}
if ($action == "copyquestion")
{
    $questlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    array_unshift($questlangs,$baselang);
    $qattributes=questionAttributes();
    $editquestion = PrepareEditorScript();
    $editquestion .= "<div class='header ui-widget-header'>".$clang->gT("Copy Question")."</div>\n"
    . "<form id='frmcopyquestion' class='form30' name='frmcopyquestion' action='$scriptname' method='post' onsubmit=\"return isEmpty(document.getElementById('title'), '".$clang->gT("Error: You have to enter a question code.",'js')."');\">\n"
    . '<div class="tab-pane" id="tab-pane-copyquestion">';
    foreach ($questlangs as $language)
    {
        $egquery = "SELECT * FROM ".db_table_name('questions')." WHERE sid=$surveyid AND gid=$gid AND qid=$qid and language=".db_quoteall($language);
        $egresult = db_execute_assoc($egquery);
        $eqrow = $egresult->FetchRow();
        $eqrow = array_map('htmlspecialchars', $eqrow);
        $editquestion .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($eqrow['language'],false);
        if ($eqrow['language']==GetBaseLanguageFromSurveyID($surveyid))
        {
            $editquestion .= "(".$clang->gT("Base language").")</h2><ul>"
            . "\t<li><label for='title'>".$clang->gT("Code:")."</label>\n"
            . "<input type='text' size='20' maxlength='20' id='title' name='title' value='' /> ".$clang->gT("Note: You MUST enter a new question code!")."\n"
            . "\t</li>\n";
        }
        else {
            $editquestion .= '</h2><ul>';
        }
        $editquestion .=  "\t<li><label for='question_{$eqrow['language']}'>".$clang->gT("Question:")."</label>\n"
        . "<textarea cols='50' rows='4' id='question_{$eqrow['language']}' name='question_{$eqrow['language']}'>{$eqrow['question']}</textarea>\n"
        . getEditor("question-text","question_".$eqrow['language'], "[".$clang->gT("Question:", "js")."](".$eqrow['language'].")",$surveyid,$gid,$qid,$action)
        . "\t</li>\n"
        . "\t<li><label for='help_{$eqrow['language']}'>".$clang->gT("Help:")."</label>\n"
        . "<textarea cols='50' rows='4' name='help_{$eqrow['language']}'>{$eqrow['help']}</textarea>\n"
        .  getEditor("question-help","help_".$eqrow['language'], "[".$clang->gT("Help:", "js")."](".$eqrow['language'].")",$surveyid,$gid,$qid,$action)
        . "\t</li>\n";
        $editquestion .= '</ul></div>';
    }
    $editquestion .= "\t</div><ul>\n"
    . "<li><label for='type'>".$clang->gT("Type:")."</label>\n"
    . "<select id='type' name='type' onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n"
    . getqtypelist($eqrow['type'])
    . "</select></li>\n";

    $editquestion .= "\t<li id='Validation'>\n"
    . "<label for='preg'>".$clang->gT("Validation:")."</label>\n"
    . "<input type='text' id='preg' name='preg' size='50' value=\"".$eqrow['preg']."\" />\n"
    . "</li>\n"

    . "<li ><label for='gid'>".$clang->gT("Question group:")."</label>\n"
    . "<select id='gid' name='gid'>\n"
    . getgrouplist3($eqrow['gid'])
    . "\t</select></li>\n";

    $editquestion .= "\t<li id='OtherSelection' style='display: none'>\n"
    . "\t\t<label>".$clang->gT("Option 'Other':")."</label>\n";

    $editquestion .= "<label>\n"
    . "\t".$clang->gT("Yes")."</label> <input type='radio' class='radiobtn' name='other' value='Y'";
    if ($eqrow['other'] == "Y") {$editquestion .= " checked";}
    $editquestion .= " />&nbsp;&nbsp;\n"
    . "\t<label>".$clang->gT("No")."</label> <input type='radio' class='radiobtn' name='other' value='N'";
    if ($eqrow['other'] == "N") {$editquestion .= " checked";}
    $editquestion .= " />\n"
    . "</li>\n";

    $editquestion .= "\t<li id='MandatorySelection'>\n"
    . "<label>".$clang->gT("Mandatory:")."</label>\n"
    . "<label>".$clang->gT("Yes")." </label><input type='radio' class='radiobtn' name='mandatory' value='Y'";
    if ($eqrow['mandatory'] == "Y") {$editquestion .= " checked='checked'";}
    $editquestion .= " />&nbsp;&nbsp;\n"
    . "\t<label>".$clang->gT("No")." </label><input type='radio' class='radiobtn' name='mandatory' value='N'";
    if ($eqrow['mandatory'] != "Y") {$editquestion .= " checked='checked'";}
    $editquestion .= " />\n";

    $editquestion .= questionjavascript($eqrow['type'])."</li>\n";

    $editquestion .= "<li><label for='copysubquestions'>".$clang->gT("Copy subquestions?")."</label>\n"
    . "<input type='checkbox' class='checkboxbtn' checked='checked' id='copysubquestions' name='copysubquestions' value='Y' />"
    . "</li>\n";
    $editquestion .= "<li><label for='copyanswers'>".$clang->gT("Copy answer options?")."</label>\n"
        . "<input type='checkbox' class='checkboxbtn' checked='checked' id='copyanswers' name='copyanswers' value='Y' />"
        . "</li>\n"
    . "<li><label for='copyattributes'>".$clang->gT("Copy advanced settings?")."</label>\n"
        . "<input type='checkbox' class='checkboxbtn' checked='checked' id='copyattributes' name='copyattributes' value='Y' />"
        . "</li></ul>\n"
    . "<p><input type='submit' value='".$clang->gT("Copy question")."' />\n"
        . "<input type='hidden' name='action' value='copynewquestion' />\n"
        . "<input type='hidden' name='sid' value='$surveyid' />\n"
        . "<input type='hidden' name='oldqid' value='$qid' />\n"
        . "\t</form>\n";

    }


if ($action == "editdefaultvalues")
{
    $questlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    array_unshift($questlangs,$baselang);

    $questionrow=$connect->GetRow("SELECT type, other, title, question, same_default FROM ".db_table_name('questions')." WHERE sid=$surveyid AND gid=$gid AND qid=$qid AND language='$baselang'");
    $qtproperties=getqtypelist('','array');

    $editdefvalues="<div class='header ui-widget-header'>".$clang->gT('Edit default answer values')."</div> "
    . '<div class="tab-pane" id="tab-pane-editdefaultvalues-'.$surveyid.'">'
    . "<form class='form30' id='frmdefaultvalues' name='frmdefaultvalues' action='$scriptname' method='post'>\n";
    foreach ($questlangs as $language)
    {
        $editdefvalues .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($language,false).'</h2>';
        $editdefvalues.="<ul> ";
        // If there are answerscales
        if ($qtproperties[$questionrow['type']]['answerscales']>0)
        {
            for ($scale_id=0;$scale_id<$qtproperties[$questionrow['type']]['answerscales'];$scale_id++)
            {
                $editdefvalues.=" <li><label for='defaultanswerscale_{$scale_id}_{$language}'>";
                if ($qtproperties[$questionrow['type']]['answerscales']>1)
                {
                    $editdefvalues.=sprintf($clang->gT('Default answer for scale %s:'),$scale_id)."</label>";
                }
                else
                {
                    $editdefvalues.=sprintf($clang->gT('Default answer value:'),$scale_id)."</label>";
                }
                $defaultvalue=$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE qid=$qid AND specialtype='' and scale_id={$scale_id} AND language='{$language}'");

                $editdefvalues.="<select name='defaultanswerscale_{$scale_id}_{$language}' id='defaultanswerscale_{$scale_id}_{$language}'>";
                $editdefvalues.="<option value='' ";
                if (is_null($defaultvalue)) {
                 $editdefvalues.= " selected='selected' ";
                }
                $editdefvalues.=">".$clang->gT('<No default value>')."</option>";
                $answerquery = "SELECT code, answer FROM ".db_table_name('answers')." WHERE qid=$qid and language='$language' order by sortorder";
                $answerresult = db_execute_assoc($answerquery);
                foreach ($answerresult as $answer)
                {
                    $editdefvalues.="<option ";
                    if ($answer['code']==$defaultvalue)
                    {
                        $editdefvalues.= " selected='selected' ";
                    }
                    $editdefvalues.="value='{$answer['code']}'>{$answer['answer']}</option>";
                }
                $editdefvalues.="</select></li> ";
                if ($questionrow['other']=='Y')
                {
                    $defaultvalue=$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE qid=$qid and specialtype='other' AND scale_id={$scale_id} AND language='{$language}'");
                    if (is_null($defaultvalue)) $defaultvalue='';
                    $editdefvalues.="<li><label for='other_{$scale_id}_{$language}'>".$clang->gT("Default value for option 'Other':")."<label><input type='text' name='other_{$scale_id}_{$language}' value='$defaultvalue' id='other_{$scale_id}_{$language}'></li>";
                }
            }
        }

        // If there are subquestions and no answerscales
        if ($qtproperties[$questionrow['type']]['answerscales']==0 && $qtproperties[$questionrow['type']]['subquestions']>0)
        {
            for ($scale_id=0;$scale_id<$qtproperties[$questionrow['type']]['subquestions'];$scale_id++)
            {
                $sqquery = "SELECT * FROM ".db_table_name('questions')." WHERE sid=$surveyid AND gid=$gid AND parent_qid=$qid and language=".db_quoteall($language)." and scale_id=0 order by question_order";
                $sqresult = db_execute_assoc($sqquery);
                $sqrows = $sqresult->GetRows();
                if ($qtproperties[$questionrow['type']]['subquestions']>1)
                {
                    $editdefvalues.=" <div class='header ui-widget-header'>".sprintf($clang->gT('Default answer for scale %s:'),$scale_id)."</div>";
                }
                if ($questionrow['type']=='M' || $questionrow['type']=='P')
                {
                    $options=array(''=>$clang->gT('<No default value>'),'Y'=>$clang->gT('Checked'));
                }
                $editdefvalues.="<ul>";

                foreach ($sqrows as $aSubquestion)
                {
                    $defaultvalue=$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE qid=$qid AND specialtype='' and sqid={$aSubquestion['qid']} and scale_id={$scale_id} AND language='{$language}'");
                    $editdefvalues.="<li><label for='defaultanswerscale_{$scale_id}_{$language}_{$aSubquestion['qid']}'>{$aSubquestion['title']}: ".FlattenText($aSubquestion['question'])."</label>";
                    $editdefvalues.="<select name='defaultanswerscale_{$scale_id}_{$language}_{$aSubquestion['qid']}' id='defaultanswerscale_{$scale_id}_{$language}_{$aSubquestion['qid']}'>";
                    foreach ($options as $value=>$label)
                    {
                        $editdefvalues.="<option ";
                        if ($value==$defaultvalue)
                        {
                            $editdefvalues.= " selected='selected' ";
                        }
                        $editdefvalues.="value='{$value}'>{$label}</option>";
                    }
                    $editdefvalues.="</select></li> ";
                }
            }
        }
            if ($language==$baselang && count($questlangs)>1)
            {
            $editdefvalues.="<li><label for='samedefault'>".$clang->gT('Use same default value across languages:')."<label><input type='checkbox' name='samedefault' id='samedefault'";
            if ($questionrow['same_default'])
            {
                $editdefvalues.=" checked='checked'";
            }
            $editdefvalues.="></li>";
        }
            $editdefvalues.="</ul> ";
            $editdefvalues.="</div> "; // Closing page
        }
    $editdefvalues.="</div> "; // Closing pane
    $editdefvalues.="<input type='hidden' id='action' name='action' value='updatedefaultvalues'> "
        . "\t<input type='hidden' id='sid' name='sid' value='$surveyid' /></p>\n"
        . "\t<input type='hidden' id='gid' name='gid' value='$gid' /></p>\n"
        . "\t<input type='hidden' id='qid' name='qid' value='$qid' />";
    $editdefvalues.="<p><input type='submit' value='".$clang->gT('Save')."'/></form>";
}



if ($action == "editquestion" || $action=="addquestion")
{
    $adding=($action=="addquestion");
    $questlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    $questlangs[] = $baselang;
    $questlangs = array_flip($questlangs);
    if (!$adding)
    {
        $egquery = "SELECT * FROM ".db_table_name('questions')." WHERE sid=$surveyid AND gid=$gid AND qid=$qid";
        $egresult = db_execute_assoc($egquery);
        while ($esrow = $egresult->FetchRow())
        {
            if(!array_key_exists($esrow['language'], $questlangs)) // Language Exists, BUT ITS NOT ON THE SURVEY ANYMORE.
            {
                $egquery = "DELETE FROM ".db_table_name('questions')." WHERE sid='{$surveyid}' AND gid='{$gid}' AND qid='{$qid}' AND language='".$esrow['language']."'";
                $egresultD = $connect->Execute($egquery);
            } else {
                $questlangs[$esrow['language']] = 99;
            }
            if ($esrow['language'] == $baselang)
            {
                $basesettings = array('question_order' => $esrow['question_order'],
                                       'other' => $esrow['other'],
                                       'mandatory' => $esrow['mandatory'],
                                       'type' => $esrow['type'],
                                       'title' => $esrow['title'],
                                       'preg' => $esrow['preg'],
                                       'question' => $esrow['question'],
                                       'help' => $esrow['help']);
            }
        }
        if ($egresult==false or $egresult->RecordCount()==0)
        {
            safe_die('Invalid question id');
        }


        while (list($key,$value) = each($questlangs))
        {
            if ($value != 99)
            {
                db_switchIDInsert('questions',true);
                $egquery = "INSERT INTO ".db_table_name('questions')." (qid, sid, gid, type, title, question, preg, help, other, mandatory, question_order, language)"
                ." VALUES ('{$qid}','{$surveyid}', '{$gid}', '{$basesettings['type']}', '{$basesettings['title']}',"
                ." '{$basesettings['question']}', '{$basesettings['preg']}', '{$basesettings['help']}', '{$basesettings['other']}', '{$basesettings['mandatory']}', '{$basesettings['question_order']}','{$key}')";
                $egresult = $connect->Execute($egquery);
                db_switchIDInsert('questions',false);
            }
        }

        $eqquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid=$gid AND qid=$qid AND language='{$baselang}'";
        $eqresult = db_execute_assoc($eqquery);
    }

    $js_admin_includes[] = '../scripts/jquery/jquery.dd.js';
    $css_admin_includes[] = '../scripts/jquery/dd.css';

    $editquestion = PrepareEditorScript();

    $qtypelist=getqtypelist('','array');
    $qDescToCode = 'qDescToCode = {';
    $qCodeToInfo = 'qCodeToInfo = {';
    foreach ($qtypelist as $qtype=>$qdesc){
        $qDescToCode .= " '{$qdesc['description']}' : '{$qtype}', \n";
        $qCodeToInfo .= " '{$qtype}' : '".json_encode($qdesc)."', \n";
    }
    $qTypeOutput = "$qDescToCode 'null':'null' }; \n $qCodeToInfo 'null':'null' };";

    $editquestion .= "<script type='text/javascript'>\n{$qTypeOutput}\n</script>\n<div class='header ui-widget-header'>";
    if (!$adding) {$editquestion .=$clang->gT("Edit question");} else {$editquestion .=$clang->gT("Add a new question");};
    $editquestion .= "</div>\n";


	  if (!$adding)
    {
        $eqrow = $eqresult->FetchRow();  // there should be only one datarow, therefore we don't need a 'while' construct here.
        // Todo: handler in case that record is not found
    }
    else
    {
        $eqrow['language']=$baselang;
        $eqrow['title']='';
        $eqrow['question']='';
        $eqrow['help']='';
        $eqrow['type']='T';
        $eqrow['lid']=0;
        $eqrow['lid1']=0;
        $eqrow['gid']=$gid;
        $eqrow['other']='N';
        $eqrow['mandatory']='N';
        $eqrow['preg']='';
    }
   $editquestion .= "<div id='tabs'><ul>";



	$editquestion .= '<li><a href="#'.$eqrow['language'].'">'.getLanguageNameFromCode($eqrow['language'],false);
    $editquestion .= '('.$clang->gT("Base language").')';
	$editquestion .= "</a></li>\n";

	$addlanguages=GetAdditionalLanguagesFromSurveyID($surveyid);
        foreach  ($addlanguages as $addlanguage)
        {
		$editquestion .= '<li><a href="#'.$addlanguage.'">'.getLanguageNameFromCode($addlanguage,false);
	$editquestion .= "</a></li>\n";
		}

		$editquestion .= "\n</ul>\n";
		$editquestion .=  "<form name='frmeditquestion' id='frmeditquestion' action='$scriptname' method='post' onsubmit=\"return isEmpty(document.getElementById('title'), '".$clang->gT("Error: You have to enter a question code.",'js')."');\">\n";


    $editquestion .= '<div id="'.$eqrow['language'].'">';
    $eqrow  = array_map('htmlspecialchars', $eqrow);
    $editquestion .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Code:")."</span>\n"
    . "<span class='settingentry'><input type='text' size='20' maxlength='20'  id='title' name='title' value=\"{$eqrow['title']}\" />\n"
    . "\t</span></div>\n";
    $editquestion .=  "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Question:")."</span>\n"
    . "<span class='settingentry'><textarea cols='50' rows='4' name='question_{$eqrow['language']}'>{$eqrow['question']}</textarea>\n"
    . getEditor("question-text","question_".$eqrow['language'], "[".$clang->gT("Question:", "js")."](".$eqrow['language'].")",$surveyid,$gid,$qid,$action)
    . "\t</span></div>\n"
    . "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Help:")."</span>\n"
    . "<span class='settingentry'><textarea cols='50' rows='4' name='help_{$eqrow['language']}'>{$eqrow['help']}</textarea>\n"
    . getEditor("question-help","help_".$eqrow['language'], "[".$clang->gT("Help:", "js")."](".$eqrow['language'].")",$surveyid,$gid,$qid,$action)
    . "\t</span></div>\n"
    . "\t<div class='settingrow'><span class='settingcaption'>&nbsp;</span>\n"
    . "<span class='settingentry'>&nbsp;\n"
    . "\t</span></div>\n";
    $editquestion .= '&nbsp;</div>';


    if (!$adding)
    {
        $aqquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid=$gid AND qid=$qid AND language != '{$baselang}'";
        $aqresult = db_execute_assoc($aqquery);
        while (!$aqresult->EOF)
        {
            $aqrow = $aqresult->FetchRow();
            $editquestion .= '<div id="'.$aqrow['language'].'">';
            $aqrow  = array_map('htmlspecialchars', $aqrow);
            $editquestion .=  "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Question:")."</span>\n"
            . "<span class='settingentry'><textarea cols='50' rows='4' name='question_{$aqrow['language']}'>{$aqrow['question']}</textarea>\n"
            . getEditor("question-text","question_".$aqrow['language'], "[".$clang->gT("Question:", "js")."](".$aqrow['language'].")",$surveyid,$gid,$qid,$action)
            . "\t</span></div>\n"
            . "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Help:")."</span>\n"
            . "<span class='settingentry'><textarea cols='50' rows='4' name='help_{$aqrow['language']}'>{$aqrow['help']}</textarea>\n"
            . getEditor("question-help","help_".$aqrow['language'], "[".$clang->gT("Help:", "js")."](".$aqrow['language'].")",$surveyid,$gid,$qid,$action)
            . "\t</span></div>\n";
            $editquestion .= '</div>';
        }
    }
    else
    {
        $addlanguages=GetAdditionalLanguagesFromSurveyID($surveyid);
        foreach  ($addlanguages as $addlanguage)
        {
            $editquestion .= '<div id="'.$addlanguage.'">';
            $editquestion .= '</h2>';
            $editquestion .=  "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Question:")."</span>\n"
            . "<span class='settingentry'><textarea cols='50' rows='4' name='question_{$addlanguage}'></textarea>\n"
            . getEditor("question-text","question_".$addlanguage, "[".$clang->gT("Question:", "js")."](".$addlanguage.")",$surveyid,$gid,$qid,$action)
            . "\t</span></div>\n"
            . "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Help:")."</span>\n"
            . "<span class='settingentry'><textarea cols='50' rows='4' name='help_{$addlanguage}'></textarea>\n"
            . getEditor("question-help","help_".$addlanguage, "[".$clang->gT("Help:", "js")."](".$addlanguage.")",$surveyid,$gid,$qid,$action)
            . "\t</span></div>\n"
            . "\t<div class='settingrow'><span class='settingcaption'>&nbsp;</span>\n"
            . "<span class='settingentry'>&nbsp;\n"
            . "\t</span></div>\n";
            $editquestion .= '</div>';
        }
    }


    //question type:
    $editquestion .= "\t<div id='questionbottom'><ul>\n"
    . "<li><label for='question_type'>".$clang->gT("Question Type:")."</label>\n";
    if ($activated != "Y")
    {
        $editquestion .= "<select id='question_type' style='margin-bottom:5px' name='type' "
        . ">\n"
        . getqtypelist($eqrow['type'],'group')
        . "</select>\n";
    }
    else
    {
        $qtypelist=getqtypelist('','array');
        $editquestion .= "{$qtypelist[$eqrow['type']]['description']} - ".$clang->gT("Cannot be changed (survey is active)")."\n"
        . "<input type='hidden' name='type' id='question_type' value='{$eqrow['type']}' />\n";
    }

    $editquestion  .="\t</li>\n";

    if (!$adding) {$qattributes=questionAttributes();}
    else
    {
        $qattributes=array();
    }

    if ($activated != "Y")
    {
        $editquestion .= "\t<li>\n"
        . "\t<label for='gid'>".$clang->gT("Question group:")."</label>\n"
        . "<select name='gid' id='gid'>\n"
        . getgrouplist3($eqrow['gid'])
        . "\t\t</select></li>\n";
    }
    else
    {
        $editquestion .= "\t<li>\n"
        . "\t<label>".$clang->gT("Question group:")."</label>\n"
        . getgroupname($eqrow['gid'])." - ".$clang->gT("Cannot be changed (survey is active)")."\n"
        . "\t<input type='hidden' name='gid' value='{$eqrow['gid']}' />"
        . "</li>\n";
    }
    $editquestion .= "\t<li id='OtherSelection'>\n"
    . "<label>".$clang->gT("Option 'Other':")."</label>\n";

    if ($activated != "Y")
    {
        $editquestion .= "<label for='OY'>".$clang->gT("Yes")."</label><input id='OY' type='radio' class='radiobtn' name='other' value='Y'";
        if ($eqrow['other'] == "Y") {$editquestion .= " checked";}
        $editquestion .= " />&nbsp;&nbsp;\n"
        . "\t<label for='ON'>".$clang->gT("No")."</label><input id='ON' type='radio' class='radiobtn' name='other' value='N'";
        if ($eqrow['other'] == "N" || $eqrow['other'] == "" ) {$editquestion .= " checked='checked'";}
        $editquestion .= " />\n";
    }
    else
    {
        $editquestion .= " [{$eqrow['other']}] - ".$clang->gT("Cannot be changed (survey is active)")."\n"
        . "\t<input type='hidden' name='other' value=\"{$eqrow['other']}\" />\n";
    }
    $editquestion .= "\t</li>\n";

    $editquestion .= "\t<li id='MandatorySelection'>\n"
    . "<label>".$clang->gT("Mandatory:")."</label>\n"
    . "\t<label for='MY'>".$clang->gT("Yes")."</label><input id='MY' type='radio' class='radiobtn' name='mandatory' value='Y'";
    if ($eqrow['mandatory'] == "Y") {$editquestion .= " checked='checked'";}
    $editquestion .= " />&nbsp;&nbsp;\n"
    . "\t<label for='MN'>".$clang->gT("No")."</label><input id='MN' type='radio' class='radiobtn' name='mandatory' value='N'";
    if ($eqrow['mandatory'] != "Y") {$editquestion .= " checked='checked'";}
    $editquestion .= " />\n"
    . "</li>\n";

    $editquestion .= "\t<li id='Validation'>\n"
    . "<label for='preg'>".$clang->gT("Validation:")."</label>\n"
    . "<input type='text' id='preg' name='preg' size='50' value=\"".$eqrow['preg']."\" />\n"
    . "\t</li>";


    if ($adding)
    {

        //Get the questions for this group
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $oqquery = "SELECT * FROM ".db_table_name('questions')." WHERE sid=$surveyid AND gid=$gid AND language='".$baselang."' and parent_qid=0 order by question_order" ;
        $oqresult = db_execute_assoc($oqquery);
        if ($oqresult->RecordCount())
        {
            // select questionposition
            $editquestion .= "\t<li>\n"
            . "<label for='questionposition'>".$clang->gT("Position:")."</label>\n"
            . "\t<select name='questionposition' id='questionposition'>\n"
            . "<option value=''>".$clang->gT("At end")."</option>\n"
            . "<option value='0'>".$clang->gT("At beginning")."</option>\n";
            while ($oq = $oqresult->FetchRow())
            {
                //Bug Fix: add 1 to question_order
                $question_order_plus_one = $oq['question_order']+1;
                $editquestion .= "<option value='".$question_order_plus_one."'>".$clang->gT("After").": ".$oq['title']."</option>\n";
            }
            $editquestion .= "\t</select>\n"
            . "</li>\n";
        }
        else
        {
            $editquestion .= "<input type='hidden' name='questionposition' value='' />";
        }
    }

    $editquestion .="</ul>\n";
    $editquestion .= '<p><a id="showadvancedattributes">'.$clang->gT("Show advanced settings").'</a><a id="hideadvancedattributes" style="display:none;">'.$clang->gT("Hide advanced settings").'</a></p>'
    .'<div id="advancedquestionsettingswrapper" style="display:none;">'
    .'<div class="loader">'.$clang->gT("Loading...").'</div>'
    .'<div id="advancedquestionsettings"></div>'
    .'</div>'
    ."<p><input type='submit' value='".$clang->gT("Save")."' />";

    if ($adding)
    {
        $editquestion .="\t<input type='hidden' name='action' value='insertquestion' />\n";
    }
    else
    {
        $editquestion .= "\t<input type='hidden' name='action' value='updatequestion' />\n"
        . "\t<input type='hidden' id='qid' name='qid' value='$qid' />";
    }
    $editquestion .= "\t<input type='hidden' id='sid' name='sid' value='$surveyid' /></p>\n"
    . "</div></form></div>\n";



    if ($adding)
    {
        // Import dialogue

        if (bHasSurveyPermission($surveyid,'surveycontent','import'))
        {
            $editquestion .= "<br /><div class='header ui-widget-header'>".$clang->gT("...or import a question")."</div>\n"
            . "\t<form enctype='multipart/form-data' id='importquestion' name='importquestion' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
            . "<ul>\n"
            . "\t<li>\n"
            . "\t<label for='the_file'>".$clang->gT("Select LimeSurvey question file (*.lsq/*.csv)").":</label>\n"
            . "\t<input name='the_file' id='the_file' type=\"file\" size=\"50\" />\n"
            . "\t</li>\n"
            . "\t<li>\n"
            . "\t<label for='translinksfields'>".$clang->gT("Convert resource links?")."</label>\n"
            . "\t<input name='translinksfields' id='translinksfields' type='checkbox' checked='checked'/>\n"
            . "\t</li>\n"
            . "</ul>\n"
            . "<p>\n"
            . "<input type='submit' value='".$clang->gT("Import Question")."' />\n"
            . "<input type='hidden' name='action' value='importquestion' />\n"
            . "<input type='hidden' name='sid' value='$surveyid' />\n"
            . "<input type='hidden' name='gid' value='$gid' />\n"
            ."</form>\n\n";

        }

        $editquestion .= "<script type='text/javascript'>\n"
        ."<!--\n"
        ."document.getElementById('title').focus();\n"
        ."//-->\n"
        ."</script>\n";

    }

    $editquestion .= questionjavascript($eqrow['type']);
}

//Constructing the interface here...
if($action == "orderquestions")
{
    if (isset($_POST['questionordermethod']))
    {
        switch($_POST['questionordermethod'])
        {
            // Pressing the Up button
            case 'up':
                $newsortorder=$postsortorder-1;
                $oldsortorder=$postsortorder;
                $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=-1 WHERE gid=$gid AND question_order=$newsortorder";
                $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
                $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=$newsortorder WHERE gid=$gid AND question_order=$oldsortorder";
                $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
                $cdquery = "UPDATE ".db_table_name('questions')." SET question_order='$oldsortorder' WHERE gid=$gid AND question_order=-1";
                $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
                break;

                // Pressing the Down button
            case 'down':
                $newsortorder=$postsortorder+1;
                $oldsortorder=$postsortorder;
                $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=-1 WHERE gid=$gid AND question_order=$newsortorder";
                $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
                $cdquery = "UPDATE ".db_table_name('questions')." SET question_order='$newsortorder' WHERE gid=$gid AND question_order=$oldsortorder";
                $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
                $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=$oldsortorder WHERE gid=$gid AND question_order=-1";
                $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
                break;
        }
    }
    if ((!empty($_POST['questionmovefrom']) || (isset($_POST['questionmovefrom']) && $_POST['questionmovefrom'] == '0')) && (!empty($_POST['questionmoveto']) || (isset($_POST['questionmoveto']) && $_POST['questionmoveto'] == '0')))
    {
        $newpos=(int)$_POST['questionmoveto'];
        $oldpos=(int)$_POST['questionmovefrom'];
        if($newpos > $oldpos)
        {
            //Move the question we're changing out of the way
            $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=-1 WHERE gid=$gid AND question_order=$oldpos";
            $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
            //Move all question_orders that are less than the newpos down one
            $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=question_order-1 WHERE gid=$gid AND question_order > $oldpos AND question_order <= $newpos";
            $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
            //Renumber the question we're changing
            $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=$newpos WHERE gid=$gid AND question_order=-1";
            $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
        }
        if(($newpos+1) < $oldpos)
        {
            //echo "Newpos $newpos, Oldpos $oldpos";
            //Move the question we're changing out of the way
            $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=-1 WHERE gid=$gid AND question_order=$oldpos";
            $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
            //Move all question_orders that are later than the newpos up one
            $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=question_order+1 WHERE gid=$gid AND question_order > $newpos AND question_order <= $oldpos";
            $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
            //Renumber the question we're changing
            $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=".($newpos+1)." WHERE gid=$gid AND question_order=-1";
            $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
        }
    }

    //Get the questions for this group
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    $oqquery = "SELECT * FROM ".db_table_name('questions')." WHERE sid=$surveyid AND gid=$gid AND language='".$baselang."' and parent_qid=0 order by question_order" ;
    $oqresult = db_execute_assoc($oqquery);

    $orderquestions = "<div class='header ui-widget-header'>".$clang->gT("Change Question Order")."</div>";

    $questioncount = $oqresult->RecordCount();
    $oqarray = $oqresult->GetArray();
    $minioqarray=$oqarray;

    // Get the condition dependecy array for all questions in this array and group
    $questdepsarray = GetQuestDepsForConditions($surveyid,$gid);
    if (!is_null($questdepsarray))
    {
        $orderquestions .= "<br/><div class='movableNode' style='margin:0 auto;'><strong><font color='orange'>".$clang->gT("Warning").":</font> ".$clang->gT("Current group is using conditional questions")."</strong><br /><br /><i>".$clang->gT("Re-ordering questions in this group is restricted to ensure that questions on which conditions are based aren't reordered after questions having the conditions set")."</i></strong><br /><br/>".$clang->gT("See the conditions marked on the following questions").":<ul>\n";
        foreach ($questdepsarray as $depqid => $depquestrow)
        {
            foreach ($depquestrow as $targqid => $targcid)
            {
                $listcid=implode("-",$targcid);
                $question=arraySearchByKey($depqid, $oqarray, "qid", 1);

                $orderquestions .= "<li><a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$gid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."','_top')\">".$question['title'].": ".FlattenText($question['question']). " [QID: ".$depqid."] </a> ";
            }
            $orderquestions .= "</li>\n";
        }
        $orderquestions .= "</ul></div>";
    }

    $orderquestions	.= "<form method='post' action=''><ul class='movableList'>";

    for($i=0; $i < $questioncount ; $i++) //Assumes that all question orders start with 0
    {
        $downdisabled = "";
        $updisabled = "";
        //Check if question is relied on as a condition dependency by the next question, and if so, don't allow moving down
        if ( !is_null($questdepsarray) && $i < $questioncount-1 &&
        array_key_exists($oqarray[$i+1]['qid'],$questdepsarray) &&
        array_key_exists($oqarray[$i]['qid'],$questdepsarray[$oqarray[$i+1]['qid']]) )
        {
            $downdisabled = "disabled=\"true\" class=\"disabledUpDnBtn\"";
        }
        //Check if question has a condition dependency on the preceding question, and if so, don't allow moving up
        if ( !is_null($questdepsarray) && $i !=0  &&
        array_key_exists($oqarray[$i]['qid'],$questdepsarray) &&
        array_key_exists($oqarray[$i-1]['qid'],$questdepsarray[$oqarray[$i]['qid']]) )
        {
            $updisabled = "disabled=\"true\" class=\"disabledUpDnBtn\"";
        }

        //Move to location
        $orderquestions.="<li class='movableNode'>\n" ;
        $orderquestions.="\t<select style='float:right; margin-left: 5px;";
        $orderquestions.="' name='questionmovetomethod$i' onchange=\"this.form.questionmovefrom.value='".$oqarray[$i]['question_order']."';this.form.questionmoveto.value=this.value;submit()\">\n";
        $orderquestions.="<option value=''>".$clang->gT("Place after..")."</option>\n";
        //Display the "position at beginning" item
        if(empty($questdepsarray) || (!is_null($questdepsarray)  && $i != 0 &&
        !array_key_exists($oqarray[$i]['qid'], $questdepsarray)))
        {
            $orderquestions.="<option value='-1'>".$clang->gT("At beginning")."</option>\n";
        }
        //Find out if there are any dependencies
        $max_start_order=0;
        if ( !is_null($questdepsarray) && $i!=0 &&
        array_key_exists($oqarray[$i]['qid'], $questdepsarray)) //This should find out if there are any dependencies
        {
            foreach($questdepsarray[$oqarray[$i]['qid']] as $key=>$val) {
                //qet the question_order value for each of the dependencies
                foreach($minioqarray as $mo) {
                    if($mo['qid'] == $key && $mo['question_order'] > $max_start_order) //If there is a matching condition, and the question order for that condition is higher than the one already set:
                    {
                        $max_start_order = $mo['question_order']; //Set the maximum question condition to this
                    }
                }
            }
        }
        //Find out if any questions use this as a dependency
        $max_end_order=$questioncount+1;
        if ( !is_null($questdepsarray))
        {
            //There doesn't seem to be any choice but to go through the questdepsarray one at a time
            //to find which question has a dependence on this one
            foreach($questdepsarray as $qdarray)
            {
                if (array_key_exists($oqarray[$i]['qid'], $qdarray))
                {
                    $cqidquery = "SELECT question_order
				          FROM ".db_table_name('conditions').", ".db_table_name('questions')."
						  WHERE ".db_table_name('conditions').".qid=".db_table_name('questions').".qid
						  AND cid=".$qdarray[$oqarray[$i]['qid']][0];
                    $cqidresult = db_execute_assoc($cqidquery);
                    $cqidrow = $cqidresult->FetchRow();
                    $max_end_order=$cqidrow['question_order'];
                }
            }
        }
        $minipos=$minioqarray[0]['question_order']; //Start at the very first question_order
        foreach($minioqarray as $mo)
        {
            if($minipos >= $max_start_order && $minipos < $max_end_order)
            {
                $orderquestions.="<option value='".$mo['question_order']."'>".$mo['title']."</option>\n";
            }
            $minipos++;
        }
        $orderquestions.="</select>\n";

        $orderquestions.= "\t<input style='float:right;";
        if ($i == 0) {$orderquestions.="visibility:hidden;";}
        $orderquestions.="' type='image' src='$imageurl/up.png' name='btnup_$i' onclick=\"$('#sortorder').val('{$oqarray[$i]['question_order']}');$('#questionordermethod').val('up');\" ".$updisabled."/>\n";
        if ($i < $questioncount-1)
        {
            // Fill the sortorder hiddenfield so we know what field is moved down
            $orderquestions.= "\t<input type='image' src='$imageurl/down.png' style='float:right;' name='btndown_$i' onclick=\"$('#sortorder').val('{$oqarray[$i]['question_order']}');$('#questionordermethod').val('down')\" ".$downdisabled."/>\n";
        }
        $orderquestions.= "<a href='admin.php?sid=$surveyid&amp;gid=$gid&amp;qid={$oqarray[$i]['qid']}' title='".$clang->gT("View Question")."'>".$oqarray[$i]['title']."</a>: ".FlattenText($oqarray[$i]['question']);
        $orderquestions.= "</li>\n" ;
    }

    $orderquestions.="</ul>\n"
    . "<input type='hidden' name='questionmovefrom' />\n"
    . "<input type='hidden' name='questionordermethod' id='questionordermethod' />\n"
    . "<input type='hidden' name='questionmoveto' />\n"
    . "\t<input type='hidden' id='sortorder' name='sortorder' />"
    . "\t<input type='hidden' name='action' value='orderquestions' />"
    . "</form>" ;
    $orderquestions .="<br />" ;
}

function questionjavascript($type)
{
    $newquestionoutput = "<script type='text/javascript'>\n"
    ."if (navigator.userAgent.indexOf(\"Gecko\") != -1)\n"
    ."window.addEventListener(\"load\", init_gecko_select_hack, false);\n";
    $jc=0;
    $newquestionoutput .= "\tvar qtypes = new Array();\n";
    $newquestionoutput .= "\tvar qnames = new Array();\n\n";
    $newquestionoutput .= "\tvar qhelp = new Array();\n\n";
    $newquestionoutput .= "\tvar qcaption = new Array();\n\n";

    //The following javascript turns on and off (hides/displays) various fields when the questiontype is changed
    $newquestionoutput .="\nfunction OtherSelection(QuestionType)\n"
    . "\t{\n"
    . "if (QuestionType == '') {QuestionType=document.getElementById('question_type').value;}\n"
    . "\tif (QuestionType == 'M' || QuestionType == 'P' || QuestionType == 'L' || QuestionType == '!')\n"
    . "{\n"
    . "document.getElementById('OtherSelection').style.display = '';\n"
    . "document.getElementById('Validation').style.display = 'none';\n"
    . "document.getElementById('MandatorySelection').style.display='';\n"
    . "}\n"
    . "\telse if (QuestionType == 'W' || QuestionType == 'Z')\n"
    . "{\n"
    . "document.getElementById('OtherSelection').style.display = '';\n"
    . "document.getElementById('Validation').style.display = 'none';\n"
    . "document.getElementById('MandatorySelection').style.display='';\n"
    . "}\n"
    . "\telse if (QuestionType == '|')\n"
    . "{\n"
    . "document.getElementById('OtherSelection').style.display = 'none';\n"
    . "document.getElementById('Validation').style.display = 'none';\n"
    . "document.getElementById('MandatorySelection').style.display='none';\n"
    . "}\n"
    . "\telse if (QuestionType == 'F' || QuestionType == 'H' || QuestionType == ':' || QuestionType == ';')\n"
    . "{\n"
    . "document.getElementById('OtherSelection').style.display = 'none';\n"
    . "document.getElementById('Validation').style.display = 'none';\n"
    . "document.getElementById('MandatorySelection').style.display='';\n"
    . "}\n"
    . "\telse if (QuestionType == '1')\n"
    . "{\n"
    . "document.getElementById('OtherSelection').style.display = 'none';\n"
    . "document.getElementById('Validation').style.display = 'none';\n"
    . "document.getElementById('MandatorySelection').style.display='';\n"
    . "}\n"
    . "\telse if (QuestionType == 'S' || QuestionType == 'T' || QuestionType == 'U' || QuestionType == 'N' || QuestionType=='' || QuestionType=='K')\n"
    . "{\n"
    . "document.getElementById('Validation').style.display = '';\n"
    . "document.getElementById('OtherSelection').style.display ='none';\n"
    . "if (document.getElementById('ON'))  {document.getElementById('ON').checked = true;}\n"
    . "document.getElementById('MandatorySelection').style.display='';\n"
    . "}\n"
    . "\telse if (QuestionType == 'X')\n"
    . "{\n"
    . "document.getElementById('Validation').style.display = 'none';\n"
    . "document.getElementById('OtherSelection').style.display ='none';\n"
    . "document.getElementById('MandatorySelection').style.display='none';\n"
    . "}\n"
    . "\telse\n"
    . "{\n"
    . "document.getElementById('OtherSelection').style.display = 'none';\n"
    . "if (document.getElementById('ON'))  {document.getElementById('ON').checked = true;}\n"
    . "document.getElementById('Validation').style.display = 'none';\n"
    . "document.getElementById('MandatorySelection').style.display='';\n"
    . "}\n"
    . "\t}\n"
    . "\tOtherSelection('$type');\n"
    . "</script>\n";

    return $newquestionoutput;
}


if ($action == "ajaxlabelsetdetails")
{
    $lid=returnglobal('lid');
    $query='select * from '.db_table_name('labelsets').' where lid='.$lid;
    $labelsetdata=$connect->GetArray($query);
    $labelsetlanguages=explode(' ',$labelsetdata[0]['languages']);
    foreach  ($labelsetlanguages as $language){
        $query='select * from '.db_table_name('labels').' where lid='.$lid." and language='{$language}' order by sortorder";
        $labels=$connect->GetArray($query);
        $resultdata[]=array($language=>array($labels,getLanguageNameFromCode($language,false)));
    }
    echo json_encode($resultdata);
}


if ($action == "ajaxlabelsetpicker")
{
    $match=(int)returnglobal('match');
    $surveyid=returnglobal('sid');
    if ($match==1)
    {
        $language=GetBaseLanguageFromSurveyID($surveyid);
    }
    else
    {
        $language=null;
    }
    $resultdata=getlabelsets($language);
    echo json_encode($resultdata);
}


if ($action == "ajaxquestionattributes")
{
    $thissurvey=getSurveyInfo($surveyid);
    $type=returnglobal('question_type');
    if (isset($qid))
    {
        $attributesettings=getQuestionAttributes($qid);
    }

    $availableattributes=questionAttributes();
    if (isset($availableattributes[$type]))
    {
        uasort($availableattributes[$type],'CategorySort');
        $ajaxoutput = '';
        $currentfieldset='';
        foreach ($availableattributes[$type] as $qa)
        {
            if (isset($attributesettings[$qa['name']]))
            {
                $value=$attributesettings[$qa['name']];
            }
            else
            {
                $value=$qa['default'];
            }
            if ($currentfieldset!=$qa['category'])
            {
                if ($currentfieldset!='')
                {
                    $ajaxoutput.='</ul></fieldset>';
                }
                $ajaxoutput.="<fieldset>\n";
                $ajaxoutput.="<legend>{$qa['category']}</legend>\n<ul>";
                $currentfieldset=$qa['category'];
            }

            $ajaxoutput .= "<li>"
            ."<label for='{$qa['name']}' title='".$qa['help']."'>".$qa['caption']."</label>";

            if (isset($qa['readonly']) && $qa['readonly']==true && $thissurvey['active']=='Y')
            {
                $ajaxoutput .= "$value";
            }
            else
            {
                switch ($qa['inputtype']){
                    case 'singleselect':    $ajaxoutput .="<select id='{$qa['name']}' name='{$qa['name']}'>";
                    foreach($qa['options'] as $optionvalue=>$optiontext)
                    {
                        $ajaxoutput .="<option value='$optionvalue' ";
                        if ($value==$optionvalue)
                        {
                            $ajaxoutput .=" selected='selected' ";
                        }
                        $ajaxoutput .=">$optiontext</option>";
                    }
                    $ajaxoutput .="</select>";
                    break;
                    case 'text':    $ajaxoutput .="<input type='text' id='{$qa['name']}' name='{$qa['name']}' value='$value' />";
                    break;
                    case 'integer': $ajaxoutput .="<input type='text' id='{$qa['name']}' name='{$qa['name']}' value='$value' />";
                    break;
                    case 'textarea':$ajaxoutput .= "<textarea id='{$qa['name']}' name='{$qa['name']}'>$value</textarea>";
                    break;
                }
            }
            $ajaxoutput .="</li>\n";
        }
        $ajaxoutput .= "</ul></fieldset>";
    }

}

?>
