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
	. "function start_popup_editor(fieldname, fieldtext)\n"	
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
	. "\t\t\tpopup = window.open('".$rooturl."/htmleditor-popup.php?fieldname='+fieldname+'&fieldtext='+fieldtext+'&lang=+".$clang->getlangcode()."','', 'location=no, menubar=no, status=yes, scrollbars=auto, menubar=no, resizable=yes, width=600, height=400');\n"
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
	$script = "<script type='text/javascript'>\n"
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

function getEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null)
{
	global $htmleditormode;

	if ($htmleditormode == 'popup')
	{
		return getPopupEditor($fieldtype,$fieldname,$fieldtext, $surveyID,$gID,$qID);
	}
	elseif ($htmleditormode == 'inline')
	{
		return getInlineEditor($fieldtype,$fieldname,$fieldtext, $surveyID,$gID,$qID);
	}
	else
	{
		return '';
	}
}

function getPopupEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null)
{
	global $clang, $imagefiles, $rooturl;

	$htmlcode = '';
	$imgopts = '';
	$toolbarname = 'Basic';

	if ($fieldtype == 'oneline')
	{
		$imgopts = "width='20' height='20'";
	}


	$htmlcode .= ""
	. "<a href =\"javascript:start_popup_editor('".$fieldname."','".$fieldtext."')\" id='".$fieldname."_ctrl'><img alt='' id='".$fieldname."_popupctrlena' name='".$fieldname."_popupctrlena' border='0' src='".$imagefiles."/edithtmlpopup.png' /><img alt='' id='".$fieldname."_popupctrldis' name='".$fieldname."_popupctrldis' border='0' src='".$imagefiles."/edithtmlpopup_disabled.png' style='display: none' /></a>";

	return $htmlcode;
}

function getInlineEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null)
{
	global $clang, $imagefiles, $rooturl;

	$htmlcode = '';
	$imgopts = '';
	$toolbarname = 'Basic';

	if ($fieldtype == 'oneline')
	{
		$imgopts = "width='20' height='20'";
	}



	$htmlcode .= ""
	. '<script type="text/javascript" src="'.$rooturl.'/scripts/fckeditor/fckeditor.js"></script>'
	. '<script type="text/javascript"> '
	. "var oFCKeditor = new FCKeditor('$fieldname');"
	. "oFCKeditor.BasePath     = '".$rooturl."/scripts/fckeditor/';"
	. "oFCKeditor.Config[\"CustomConfigurationsPath\"] = \"".$rooturl."/scripts/fckeditor/limesurvey-config.js\";"
	. "oFCKeditor.Config[\"LimeReplacementFieldsSID\"] = \"".$surveyID."\";"
	. "oFCKeditor.Config[\"LimeReplacementFieldsGID\"] = \"".$gID."\";"
	. "oFCKeditor.Config[\"LimeReplacementFieldsQID\"] = \"".$qID."\";"
//	. "oFCKeditor.Config[ 'ToolbarLocation' ] = 'Out:xToolbar-".$fieldname."' ;"
	. "oFCKeditor.ToolbarSet = '".$toolbarname."';"
	. "oFCKeditor.ReplaceTextarea() ;"
	. '</script>';

	return $htmlcode;
}

?>
