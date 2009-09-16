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
	$editquestion .= "<table width='100%' border='0' class='form2columns'>\n\t<tr><th>"
	. "".$clang->gT("Copy Question")."</th></tr></table>\n"
	. "<form name='frmeditquestion' action='$scriptname' method='post'>\n"
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
            $editquestion .= "(".$clang->gT("Base Language").")</h2>"
        	. "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Code:")."</span>\n"
        	. "<span class='settingentry'><input type='text' size='20' maxlength='20' id='title' name='title' value='' /> ".$clang->gT("Note: You MUST enter a new question code!")."\n"
        	. "\t</span></div>\n";
        }
    	else {
    	        $editquestion .= '</h2>';
             }    
		$editquestion .=  "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Question:")."</span>\n"
		. "<span class='settingentry'><textarea cols='50' rows='4' name='question_{$eqrow['language']}'>{$eqrow['question']}</textarea>\n"
		. getEditor("question-text","question_".$eqrow['language'], "[".$clang->gT("Question:", "js")."](".$eqrow['language'].")",$surveyid,$gid,$qid,$action)
		. "\t</span></div>\n"
		. "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Help:")."</span>\n"
		. "<span class='settingentry'><textarea cols='50' rows='4' name='help_{$eqrow['language']}'>{$eqrow['help']}</textarea>\n"
		.  getEditor("question-help","help_".$eqrow['language'], "[".$clang->gT("Help:", "js")."](".$eqrow['language'].")",$surveyid,$gid,$qid,$action)
		. "\t</span></div>\n"
        . "\t<div class='settingrow'><span class='settingcaption'></span>\n"
        . "<span class='settingentry'>\n"
        . "\t</span></div>\n";
		$editquestion .= '</div>';
    }
    $editquestion .= "\t<table class='form2columns'><tr>\n"
	. "<td align='right'><strong>".$clang->gT("Type:")."</strong></td>\n"
	. "<td><select name='type' onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n"
	. getqtypelist($eqrow['type'])
	. "</select></td>\n"
	. "\t</tr>\n";

	$editquestion .= "\t<tr id='Validation'>\n"
	. "<td align='right'><strong>".$clang->gT("Validation:")."</strong></td>\n"
	. "<td>\n"
	. "<input type='text' name='preg' size='50' value=\"".$eqrow['preg']."\" />\n"
	. "</td>\n"
	. "\t</tr>\n";

	$editquestion .= "\t<tr id='LabelSets' style='display: none'>\n"
	. "<td><strong>".$clang->gT("Label Set:")."</strong></td>\n"
	. "<td>\n"
	. "<select name='lid' >\n";
	$labelsets=getlabelsets(GetBaseLanguageFromSurveyID($surveyid));
		if (count($labelsets)>0)
		{
			if (!$eqrow['lid'])
			{
				$editquestion .= "\t<option value=''>".$clang->gT("Please Choose...")."</option>\n";
			}
			foreach ($labelsets as $lb)
			{
				$editquestion .= "\t<option value='{$lb[0]}'";
				if ($eqrow['lid'] == $lb[0]) {$editquestion .= " selected";}
				$editquestion .= ">{$lb[1]}</option>\n";
			}
		}
	$editquestion .= "</select>\n";		
	$editquestion .= "\t<tr id='LabelSets1' style='display: none'>\n"
	. "<td><strong>".$clang->gT("Second Label Set:")."</strong></td>\n"
	. "<td>\n"
	. "<select name='lid1' >\n";
	$labelsets1=getlabelsets(GetBaseLanguageFromSurveyID($surveyid));
		if (count($labelsets1)>0)
		{
			if (!$eqrow['lid1'])
			{
				$editquestion .= "\t<option value=''>".$clang->gT("Please Choose...")."</option>\n";
			}
			foreach ($labelsets1 as $lb)
			{
				$editquestion .= "\t<option value='{$lb[0]}'";
				if ($eqrow['lid1'] == $lb[0]) {$editquestion .= " selected";}
				$editquestion .= ">{$lb[1]}</option>\n";
			}
		}
	
		$editquestion .= "</select>\n"
		. "</td>\n"
		. "\t</tr>\n"
		. "\t<tr>\n"
		. "<td ><strong>".$clang->gT("Question group:")."</strong></td>\n"
		. "<td><select name='gid'>\n"
		. getgrouplist3($eqrow['gid'])
		. "\t</select></td>\n"
		. "\t</tr>\n";

		$editquestion .= "\t<tr id='OtherSelection' style='display: none'>\n"
		. "\t\t<td><strong>".$clang->gT("Option 'Other':")."</strong></td>\n";

		$editquestion .= "<td>\n"
		. "\t".$clang->gT("Yes")." <input type='radio' class='radiobtn' name='other' value='Y'";
		if ($eqrow['other'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n"
		. "\t".$clang->gT("No")." <input type='radio' class='radiobtn' name='other' value='N'";
		if ($eqrow['other'] == "N") {$editquestion .= " checked";}
		$editquestion .= " />\n"
		. "</td>\n"
		. "\t</tr>\n";

		$editquestion .= "\t<tr id='MandatorySelection'>\n"
		. "<td><strong>".$clang->gT("Mandatory:")."</strong></td>\n"
		. "<td>\n"
		. "\t".$clang->gT("Yes")." <input type='radio' class='radiobtn' name='mandatory' value='Y'";
		if ($eqrow['mandatory'] == "Y") {$editquestion .= " checked='checked'";}
		$editquestion .= " />&nbsp;&nbsp;\n"
		. "\t".$clang->gT("No")." <input type='radio' class='radiobtn' name='mandatory' value='N'";
		if ($eqrow['mandatory'] != "Y") {$editquestion .= " checked='checked'";}
		$editquestion .= " />\n"
		. "</td>\n"
		. "\t</tr>\n"
		. "\t<tr>\n"
		. "<td align='right'>";

		$editquestion .= questionjavascript($eqrow['type']);

		if ($eqrow['type'] == "J" || $eqrow['type'] == "I")
		{
			$editquestion .= "\t<tr>\n"
			. "<input type='hidden' name='copyanswers' value='Y'>\n"
			. "<td colspan='2' class='centered'><input type='submit' value='".$clang->gT("Copy Question")."' />\n"
			. "<input type='hidden' name='action' value='copynewquestion' />\n"
			. "<input type='hidden' name='sid' value='$sid' />\n"
			. "<input type='hidden' name='oldqid' value='$qid' />\n"
			. "<input type='hidden' name='gid' value='$gid' />\n"
			. "\t</td></tr>\n"
			. "</table></form>\n";
		}
		else
		{

			$editquestion .= "<strong>".$clang->gT("Copy Answers?")."</strong></td>\n"
			. "<td><input type='checkbox' class='checkboxbtn' checked name='copyanswers' value='Y' />"
			. "</td>\n"
			. "\t</tr>\n"
			. "\t<tr>\n"
			. "<td ><strong>".$clang->gT("Copy Attributes?")."</strong></td>\n"
			. "<td><input type='checkbox' class='checkboxbtn' checked name='copyattributes' value='Y' />"
			. "</td>\n"
			. "\t</tr>\n"
			. "\t<tr>\n"
			. "<td colspan='2'  class='centered'><input type='submit' value='".$clang->gT("Copy Question")."' />\n"
			. "<input type='hidden' name='action' value='copynewquestion' />\n"
			. "<input type='hidden' name='sid' value='$surveyid' />\n"
			. "<input type='hidden' name='oldqid' value='$qid' />\n"
			. "\t</td></tr>\n"
			. "</table>\n</form>\n";
		}
}

if ($action == "editquestion" || $action == "editattribute" || $action == "delattribute" || $action == "addattribute" || $action=="addquestion")
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
			    if ($esrow['language'] == $baselang) $basesettings = array('lid' => $esrow['lid'], 'lid1' => $esrow['lid1'],'question_order' => $esrow['question_order'],'other' => $esrow['other'],'mandatory' => $esrow['mandatory'],'type' => $esrow['type'],'title' => $esrow['title'],'preg' => $esrow['preg'],'question' => $esrow['question'],'help' => $esrow['help']);

		    }
        
	
		    while (list($key,$value) = each($questlangs))
		    {
			    if ($value != 99)
			    {
                    if ($connect->databaseType == 'odbc_mssql' || $connect->databaseType == 'odbtp' || $connect->databaseType == 'mssql_n') {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('questions')." ON");}
				    $egquery = "INSERT INTO ".db_table_name('questions')." (qid, sid, gid, type, title, question, preg, help, other, mandatory, lid, lid1, question_order, language)"
				    ." VALUES ('{$qid}','{$surveyid}', '{$gid}', '{$basesettings['type']}', '{$basesettings['title']}',"
				    ." '{$basesettings['question']}', '{$basesettings['preg']}', '{$basesettings['help']}', '{$basesettings['other']}', '{$basesettings['mandatory']}', '{$basesettings['lid']}', '{$basesettings['lid1']}', '{$basesettings['question_order']}','{$key}')";
				    $egresult = $connect->Execute($egquery);
                    if ($connect->databaseType == 'odbc_mssql' || $connect->databaseType == 'odbtp' || $connect->databaseType == 'mssql_n') {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('questions')." OFF");}
			    }
		    }
	    
	        $eqquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid=$gid AND qid=$qid AND language='{$baselang}'";
	        $eqresult = db_execute_assoc($eqquery);
        }
	$editquestion = PrepareEditorScript();
	$editquestion .= "<table width='100%' border='0'>\n\t<tr><td class='settingcaption'>";
	if (!$adding) {$editquestion .=$clang->gT("Edit question");} else {$editquestion .=$clang->gT("Add a new question");};
    $editquestion .= "</td></tr></table>\n"
	. "<form name='frmeditquestion' id='frmeditquestion' action='$scriptname' method='post' onsubmit=\"return isEmpty(document.getElementById('title'), '".$clang->gT("Error: You have to enter a question code.",'js')."');\">\n"
	. '<div class="tab-pane" id="tab-pane-editquestion-'.$surveyid.'">';
	
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
	$editquestion .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($eqrow['language'],false);
	$editquestion .= '('.$clang->gT("Base Language").')';
	$eqrow  = array_map('htmlspecialchars', $eqrow);
	$editquestion .= '</h2>';
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
	$editquestion .= '</div>';
	
    
    if (!$adding)
    { 
	    $aqquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid=$gid AND qid=$qid AND language != '{$baselang}'";
	    $aqresult = db_execute_assoc($aqquery);
	    while (!$aqresult->EOF) 
	    {
            $aqrow = $aqresult->FetchRow();
		    $editquestion .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($aqrow['language'],false);
		    $aqrow  = array_map('htmlspecialchars', $aqrow);
		    $editquestion .= '</h2>';
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
            $editquestion .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($addlanguage,false);
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
  			$editquestion .= "<select id='question_type' name='type' "
  			. "onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n"
  			. getqtypelist($eqrow['type'])
  			. "</select>\n";
  		}
  		else
  		{
  			$qtypelist=getqtypelist('','array');
            $editquestion .= "{$qtypelist[$eqrow['type']]} - ".$clang->gT("Cannot be modified (Survey is active)")."\n"
  			. "<input type='hidden' name='type' id='question_type' value='{$eqrow['type']}' />\n";
  		}
  
  		$editquestion  .="\t</li><li id='LabelSets' style='display: none'>\n"
  		. "<label for='lid'>".$clang->gT("Label Set:")."</label>\n";

		if (!$adding) {$qattributes=questionAttributes();}
        else
        {
            $qattributes=array();
        }
  		if ($activated != "Y")
  		{
  			$editquestion .= "<select id='lid' name='lid' >\n";
  			$labelsets=getlabelsets(GetBaseLanguageFromSurveyID($surveyid));
  			if (count($labelsets)>0)
  			{
  				if (!$eqrow['lid'])
  				{
  					$editquestion .= "\t<option value=''>".$clang->gT("Please Choose...")."</option>\n";
  				}
  				foreach ($labelsets as $lb)
  				{
  					$editquestion .= "\t<option value='{$lb[0]}'";
  					if ($eqrow['lid'] == $lb[0]) {$editquestion .= " selected='selected'";}
  					$editquestion .= ">{$lb[1]}</option>\n";
  				}
  			}
  			$editquestion .= "</select>\n";

	  		$editquestion  .="\t</li><li id='LabelSets1' style='display: none'>\n"
  			. "<label for='lid1'>".$clang->gT("Second Label Set:")."</label>\n";

  			$editquestion .= "<select id='lid1' name='lid1' >\n";
  			$labelsets1=getlabelsets(GetBaseLanguageFromSurveyID($surveyid));
  			if (count($labelsets1)>0)
  			{
  				if (!$eqrow['lid1'])
  				{
  					$editquestion .= "\t<option value=''>".$clang->gT("Please Choose...")."</option>\n";
  				}
  				foreach ($labelsets1 as $lb)
  				{
  					$editquestion .= "\t<option value='{$lb[0]}'";
  					if ($eqrow['lid1'] == $lb[0]) {$editquestion .= " selected='selected'";}
  					$editquestion .= ">{$lb[1]}</option>\n";
  				}
  			}

  			$editquestion .= "</select>\n";
  		}
  		else
  		{
  			$editquestion .= "[{$eqrow['lid']}] - ".$clang->gT("Cannot be modified")." - ".$clang->gT("Survey is currently active.")."\n";
  			$editquestion .= "[{$eqrow['lid1']}] - ".$clang->gT("Cannot be modified")." - ".$clang->gT("Survey is currently active.")."\n"  			
 			. "\t<input type='hidden' name='lid' value=\"{$eqrow['lid']}\" />\n"
 			. "<input type='hidden' name='lid1' value=\"{$eqrow['lid1']}\" />\n";
  		}
        $editquestion .= "</li>\n";
  		
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
				. getgroupname($eqrow['gid'])." - ".$clang->gT("Cannot be modified (Survey is active)")."\n"
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
  			$editquestion .= " [{$eqrow['other']}] - ".$clang->gT("Cannot be modified")." - ".$clang->gT("Survey is currently active.")."\n"
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
  		. "\t</li></ul>\n";
        $editquestion .= '<p><a id="showadvancedattributes">'.$clang->gT("Show advanced settings").'</a><a id="hideadvancedattributes" style="display:none;">'.$clang->gT("Hide advanced settings").'</a></p>'
                        .'<div id="advancedquestionsettingswrapper" style="display:none;">'
                        .'<div class="loader"></div>'
                        .'<div id="advancedquestionsettings">'.$clang->gT("Loading...").'</div>'
                        .'</div>';
	
	
    if ($adding)
    {
        
        //Get the questions for this group
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $oqquery = "SELECT * FROM ".db_table_name('questions')." WHERE sid=$surveyid AND gid=$gid AND language='".$baselang."' order by question_order" ;
        $oqresult = db_execute_assoc($oqquery);
        if ($oqresult->RecordCount())
        {
        	// select questionposition
            $editquestion .= "\t<tr id='questionposition'>\n"
            . "<td align='right'><strong>".$clang->gT("Position:")."</strong></td>\n"
            . "<td align='left'>\n"
            . "\t<select name='questionposition'>\n"
            . "<option value=''>".$clang->gT("At end")."</option>\n"
            . "<option value='0'>".$clang->gT("At beginning")."</option>\n";
            while ($oq = $oqresult->FetchRow())
            {
		//Bug Fix: add 1 to question_order
		$question_order_plus_one = $oq['question_order']+1;
                $editquestion .= "<option value='".$question_order_plus_one."'>".$clang->gT("After").": ".$oq['title']."</option>\n";
            }
            $editquestion .= "\t</select>\n"
            . "</td>\n"
            . "\t</tr>\n";
        } 
        else      
        {
            $editquestion .= "<input type='hidden' name='questionposition' value='' />";
        }        
        
        $editquestion .= "\t<tr>\n"
        . "<td align='right'></td><td align='left'>";        
        $editquestion .= "\t<tr><td align='center' colspan='2'><input type='submit' value='".$clang->gT("Add question")."' />\n"
        . "\t<input type='hidden' name='action' value='insertnewquestion' /><br/><br/>&nbsp;\n";   
    }
    else
    {
        $editquestion .= "\t<p><input type='submit' value='".$clang->gT("Update Question")."' />\n"
        . "\t<input type='hidden' name='action' value='updatequestion' />\n"
        . "\t<input type='hidden' id='qid' name='qid' value='$qid' />";
    }
	$editquestion .= "\t<input type='hidden' id='sid' name='sid' value='$surveyid' /></p>\n"
    . "</div></div></form><p/>\n";
	


    if ($adding)
    {
        // Import dialogue

        $editquestion .= "<table width='100%' border='0'>\n\t<tr><td class='settingcaption'>";
        $editquestion .=$clang->gT("...or import a question");
        $editquestion .= "</td></tr></table>\n"
        . "\t<form enctype='multipart/form-data' id='importquestion' name='importquestion' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
        . "<table width='100%' border='0' >\n\t"
        . "\t<tr>"
        . "<td align='right' width='35%'><strong>".$clang->gT("Select CSV File").":</strong></td>\n"
        . "<td align='left'><input name=\"the_file\" type=\"file\" size=\"50\" /></td></tr>\n"
        . "<tr><td align='right' width='35%'>".$clang->gT("Convert resources links?")."</td>\n"
        . "<td><input name='translinksfields' type='checkbox' checked='checked'/></td></tr>\n"
        . "\t<tr><td colspan='2' align='center'><input type='submit' "
        . "value='".$clang->gT("Import Question")."' />\n"
        . "\t<input type='hidden' name='action' value='importquestion' />\n"
        . "\t<input type='hidden' name='sid' value='$surveyid' />\n"
        . "\t<input type='hidden' name='gid' value='$gid' />\n"
        . "\t</td></tr></table></form>\n\n"
        ."<script type='text/javascript'>\n"
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
    	case $clang->gT("Up", "unescaped"):
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
    	case $clang->gT("Dn", "unescaped"):
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
        $newpos=$_POST['questionmoveto'];
        $oldpos=$_POST['questionmovefrom'];
	    if($newpos > $oldpos)
	    {
		  //Move the question we're changing out of the way
		  $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=-1 WHERE gid=$gid AND question_order=$oldpos";
    	  $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
	      //Move all question_orders that are less than the newpos down one
	      $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=question_order-1 WHERE gid=$gid AND question_order > 0 AND question_order <= $newpos";
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
	      $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=question_order+1 WHERE gid=$gid AND question_order > ".$newpos." AND question_order <= $oldpos";
    	  $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
    	  //Renumber the question we're changing
		  $cdquery = "UPDATE ".db_table_name('questions')." SET question_order=".($newpos+1)." WHERE gid=$gid AND question_order=-1";
    	  $cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());
		}
	 }

    //Get the questions for this group
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    $oqquery = "SELECT * FROM ".db_table_name('questions')." WHERE sid=$surveyid AND gid=$gid AND language='".$baselang."' order by question_order" ;
    $oqresult = db_execute_assoc($oqquery);
    
    $orderquestions = "<table width='100%' border='0'>\n\t<tr ><td colspan='2' class='settingcaption'>"
    	. "".$clang->gT("Change Question Order")."</td></tr>"
        . "</table>\n";

    $questioncount = $oqresult->RecordCount();        
    $oqarray = $oqresult->GetArray();
    $minioqarray=$oqarray;

    // Get the condition dependecy array for all questions in this array and group
    $questdepsarray = GetQuestDepsForConditions($surveyid,$gid);
    if (!is_null($questdepsarray))
    {
	    $orderquestions .= "<ul><li class='movableNode'><strong><font color='orange'>".$clang->gT("Warning").":</font> ".$clang->gT("Current group is using conditional questions")."</strong><br /><br /><i>".$clang->gT("Re-ordering questions in this group is restricted to ensure that questions on which conditions are based aren't reordered after questions having the conditions set")."</i></strong><br /><br/>".$clang->gT("See the conditions marked on the following questions").":<ul>\n";
	    foreach ($questdepsarray as $depqid => $depquestrow)
	    {
		    foreach ($depquestrow as $targqid => $targcid)
		    {
			    $listcid=implode("-",$targcid);
			    $question=arraySearchByKey($depqid, $oqarray, "qid", 1);

			    $orderquestions .= "<li><a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$gid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."','_top')\">".$question['title'].": ".$question['question']. " [QID: ".$depqid."] </a> ";
		    }
		    $orderquestions .= "</li>\n";
	    }
	    $orderquestions .= "</ul></li></ul>";
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
		    $downdisabled = "disabled=\"true\" class=\"disabledbtn\"";
	    }
	    //Check if question has a condition dependency on the preceding question, and if so, don't allow moving up
	    if ( !is_null($questdepsarray) && $i !=0  &&
	      array_key_exists($oqarray[$i]['qid'],$questdepsarray) &&
	      array_key_exists($oqarray[$i-1]['qid'],$questdepsarray[$oqarray[$i]['qid']]) )
	    {
		    $updisabled = "disabled=\"true\" class=\"disabledbtn\"";
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
	    $orderquestions.="' type='submit' name='questionordermethod' value='".$clang->gT("Up")."' onclick=\"this.form.sortorder.value='{$oqarray[$i]['question_order']}'\" ".$updisabled."/>\n";
	    if ($i < $questioncount-1)
	    {
		    // Fill the sortorder hiddenfield so we know what field is moved down
		    $orderquestions.= "\t<input type='submit' style='float:right;' name='questionordermethod' value='".$clang->gT("Dn")."' onclick=\"this.form.sortorder.value='{$oqarray[$i]['question_order']}'\" ".$downdisabled."/>\n";
	    }
	    $orderquestions.= "<a href='admin.php?sid=$surveyid&amp;gid=$gid&amp;qid={$oqarray[$i]['qid']}' title='".$clang->gT("View Question")."'>".$oqarray[$i]['title']."</a>: ".$oqarray[$i]['question'];
	    $orderquestions.= "</li>\n" ;
	}

  	$orderquestions.="</ul>\n"
	. "<input type='hidden' name='questionmovefrom' />\n"
	. "<input type='hidden' name='questionmoveto' />\n"
  	. "\t<input type='hidden' name='sortorder' />"
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
    . "document.getElementById('LabelSets').style.display = 'none';\n"
    . "if (document.getElementById('LabelSets1'))  {document.getElementById('LabelSets1').style.display = 'none';}\n"    
    . "document.getElementById('Validation').style.display = 'none';\n"
    . "document.getElementById('MandatorySelection').style.display='';\n"
    . "}\n"
    . "\telse if (QuestionType == 'W' || QuestionType == 'Z')\n"
    . "{\n"
    . "document.getElementById('OtherSelection').style.display = '';\n"
    . "document.getElementById('LabelSets').style.display = '';\n"
    . "if (document.getElementById('LabelSets1'))  {document.getElementById('LabelSets1').style.display = 'none';}\n"    
    . "document.getElementById('Validation').style.display = 'none';\n"
    . "document.getElementById('MandatorySelection').style.display='';\n"
    . "}\n"
    . "\telse if (QuestionType == 'F' || QuestionType == 'H' || QuestionType == ':' || QuestionType == ';')\n"
    . "{\n"
    . "document.getElementById('LabelSets').style.display = '';\n"
    . "if (document.getElementById('LabelSets1'))  {document.getElementById('LabelSets1').style.display = 'none';}\n"    
    . "document.getElementById('OtherSelection').style.display = 'none';\n"
    . "document.getElementById('Validation').style.display = 'none';\n"
    . "document.getElementById('MandatorySelection').style.display='';\n"
    . "}\n"
    . "\telse if (QuestionType == '1')\n"
    . "{\n"
    . "document.getElementById('LabelSets').style.display = '';\n"
    . "if (document.getElementById('LabelSets1'))  {document.getElementById('LabelSets1').style.display = '';}\n"    
    . "document.getElementById('OtherSelection').style.display = 'none';\n"
    . "document.getElementById('Validation').style.display = 'none';\n"
    . "document.getElementById('MandatorySelection').style.display='';\n"
    . "}\n"
    . "\telse if (QuestionType == 'S' || QuestionType == 'T' || QuestionType == 'U' || QuestionType == 'N' || QuestionType=='' || QuestionType=='K')\n"
    . "{\n"
    . "document.getElementById('Validation').style.display = '';\n"
    . "document.getElementById('OtherSelection').style.display ='none';\n"
    . "if (document.getElementById('ON'))  {document.getElementById('ON').checked = true;}\n"    
    . "document.getElementById('LabelSets').style.display='none';\n"
    . "document.getElementById('MandatorySelection').style.display='';\n"
    . "}\n"
    . "\telse if (QuestionType == 'X')\n"
    . "{\n"
    . "document.getElementById('Validation').style.display = 'none';\n"
    . "document.getElementById('OtherSelection').style.display ='none';\n"
    . "document.getElementById('LabelSets').style.display='none';\n"
    . "document.getElementById('MandatorySelection').style.display='none';\n"
    . "}\n"
    . "\telse\n"
    . "{\n"
    . "document.getElementById('LabelSets').style.display = 'none';\n"
    . "if (document.getElementById('LabelSets1'))  {document.getElementById('LabelSets1').style.display = 'none';}\n"    
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

if ($action == "ajaxquestionattributes")  
{
        $type=returnglobal('question_type');
        if (isset($qid))
        {
            $attributesettings=getQuestionAttributes($qid);
        }
        
        $availableattributes=questionAttributes();
        if (isset($availableattributes[$type]))
        {
            $ajaxoutput = "<ul>\n";
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
                $ajaxoutput .= "<li>"
                                ."<label for='{$qa['name']}' title='".$qa['help']."'>".$qa['caption']."</label>";
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
					case 'textarea':	$ajaxoutput .= "<textarea id='{$qa['name']}' name='{$qa['name']}' value='$value' />";
										break;
                }
                $ajaxoutput .="</li>\n";
            }
            $ajaxoutput .= "</ul>";
        }
    
}

?>
