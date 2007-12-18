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

//include_once("login_check.php");

function PrepareEditorPopupScript()
{
	global $clang,$imagefiles,$rooturl;

	$script = "<script type='text/javascript'>\n"
	. "<!--\n"
	. "var editorwindowsHash = new Object();\n"
	. "function find_popup_editor(fieldname)\n"
	. "\t{\t\n"
	. "\t\tvar window = null;\n"
	. "\t\tfor (var key in editorwindowsHash)\n"
	. "\t\t{\n"
	. "\t\t\tif (key==fieldname && !editorwindowsHash[key].closed)\n"
	. "\t\t\t{\n"
	. "\t\t\t\twindow = editorwindowsHash[key];\n"
	. "\t\t\t\treturn window;\n"
	. "\t\t\t}\n"
	. "\t\t}\n"
	. "\treturn null;\n"
	. "\t}\t\n"
	. "\n"
	. "function start_popup_editor(fieldname, fieldtext, sid, gid, qid, fieldtype, action)\n"	
	. "\t{\t\n"
//	. "\t\tcontrolid = fieldname + '_popupctrl';\n"
	. "\t\tcontrolidena = fieldname + '_popupctrlena';\n"
	. "\t\tcontroliddis = fieldname + '_popupctrldis';\n"
	. "\t\tnumwindows = editorwindowsHash.length;\n"
	. "\t\tactivepopup = find_popup_editor(fieldname);\n"
	. "\t\tif (activepopup == null)\n"
	. "\t\t{\n"
	. "\t\t\tdocument.getElementsByName(fieldname)[0].readOnly=true;\n"
	. "\t\t\tdocument.getElementsByName(fieldname)[0].className='readonly';\n"
//	. "\t\t\tdocument.getElementById(controlid).src='".$imagefiles."/edithtmlpopup_disabled.png';\n"
	. "\t\t\tdocument.getElementById(controlidena).style.display='none';\n"
	. "\t\t\tdocument.getElementById(controliddis).style.display='';\n"
	. "\t\t\tpopup = window.open('".$rooturl."/htmleditor-popup.php?fieldname='+fieldname+'&fieldtext='+fieldtext+'&fieldtype='+fieldtype+'&action='+action+'&sid='+sid+'&gid='+gid+'&qid='+qid+'&lang=".$clang->getlangcode()."','', 'location=no, status=yes, scrollbars=auto, menubar=no, resizable=yes, width=600, height=400');\n"
	. "\t\t\teditorwindowsHash[fieldname] = popup;\n"
	. "\t\t}\n"
	. "\t\telse\n"
	. "\t\t{\n"
	. "\t\t\tactivepopup.focus();\n"
	. "\t\t}\n"
	. "\t}\n\n"
	. "--></script>\n";

	return $script;
}

function PrepareEditorInlineScript()
{
	global $rooturl;
	$script = ""
	. "<script type=\"text/javascript\" src=\"".$rooturl."/scripts/fckeditor/fckeditor.js\"></script>\n"
	. "<script type='text/javascript'>\n"
	. "<!--\n"
	."function FCKeditor_OnComplete( editorInstance )\n"
	. "{\n"
	. "\teditorInstance.Events.AttachEvent( 'OnBlur'	, FCKeditor_OnBlur ) ;\n"
	. "\teditorInstance.Events.AttachEvent( 'OnFocus', FCKeditor_OnFocus ) ;\n"
	."}\n"
	. "function FCKeditor_OnBlur( editorInstance )\n"
	. "{\n"
	. "\teditorInstance.ToolbarSet.Collapse() ;\n"
	. "}\n"
	. "function FCKeditor_OnFocus( editorInstance )\n"
	. "{\n"
	."\teditorInstance.ToolbarSet.Expand() ;\n"
	."}\n"
	. "--></script>\n";
	return $script;
}

function PrepareEditorScript()
{
	global $htmleditormode;
	if ($htmleditormode == 'popup')
	{
		return PrepareEditorPopupScript();
	}
	elseif ($htmleditormode == 'inline')
	{
		return PrepareEditorInlineScript();
	}
	else
	{
		return '';
	}
}

function getEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null,$action=null)
{
	global $htmleditormode;

	if ($htmleditormode == 'popup')
	{
		return getPopupEditor($fieldtype,$fieldname,$fieldtext, $surveyID,$gID,$qID,$action);
	}
	elseif ($htmleditormode == 'inline')
	{
		return getInlineEditor($fieldtype,$fieldname,$fieldtext, $surveyID,$gID,$qID,$action);
	}
	else
	{
		return '';
	}
}

function getPopupEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null,$action=null)
{
	global $clang, $imagefiles, $rooturl;

	$htmlcode = '';
	$imgopts = '';
	$toolbarname = 'Basic';

	if ($fieldtype == 'answer' || $fieldtype == 'label')
	{
		$imgopts = "width='20' height='20'";
	}

	$htmlcode .= ""
	. "<a href =\"javascript:start_popup_editor('".$fieldname."','".$fieldtext."','".$surveyID."','".$gID."','".$qID."','".$fieldtype."','".$action."')\" id='".$fieldname."_ctrl'><img alt='' id='".$fieldname."_popupctrlena' name='".$fieldname."_popupctrlena' border='0' src='".$imagefiles."/edithtmlpopup.png'  $imgopts /><img alt='' id='".$fieldname."_popupctrldis' name='".$fieldname."_popupctrldis' border='0' src='".$imagefiles."/edithtmlpopup_disabled.png' style='display: none'  $imgopts /></a>";

	return $htmlcode;
}

function getInlineEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null,$action=null)
{
	global $clang, $imagefiles, $rooturl;

	$htmlcode = '';
	$imgopts = '';
	$toolbarname = 'Basic';

	if ($fieldtype == 'answer' || $fieldtype == 'label')
	{
		$toolbarname = 'LimeSurveyToolbarfull';
	}

	$htmlcode .= ""
	. "<script type=\"text/javascript\">\n"
	. "var oFCKeditor = new FCKeditor('$fieldname');\n"
	. "oFCKeditor.BasePath     = '".$rooturl."/scripts/fckeditor/';\n"
	. "oFCKeditor.Config[\"CustomConfigurationsPath\"] = \"".$rooturl."/scripts/fckeditor/limesurvey-config.js\";\n"
	. "oFCKeditor.Config[\"LimeReplacementFieldsType\"] = \"".$fieldtype."\";\n"
	. "oFCKeditor.Config[\"LimeReplacementFieldsSID\"] = \"".$surveyID."\";\n"
	. "oFCKeditor.Config[\"LimeReplacementFieldsGID\"] = \"".$gID."\";\n"
	. "oFCKeditor.Config[\"LimeReplacementFieldsQID\"] = \"".$qID."\";\n"
	. "oFCKeditor.Config[\"LimeReplacementFieldsType\"] = \"".$fieldtype."\";\n"
	. "oFCKeditor.Config[\"LimeReplacementFieldsAction\"] = \"".$action."\";\n";

	if ($fieldtype == 'answer' || $fieldtype == 'label')
	{
		 $htmlcode .= ""
		. "oFCKeditor.Config[ 'ToolbarLocation' ] = 'Out:xToolbar' ;\n";
	}

	 $htmlcode .= ""
	. "oFCKeditor.ToolbarSet = '".$toolbarname."';\n"
	. "oFCKeditor.ReplaceTextarea() ;\n"
	. '</script>';

	return $htmlcode;
}

?>
