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

function getEditorPopupScript()
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

function getHtmlControls($fieldtype,$fieldname,$fieldtext)
{
	global $clang, $imagefiles;

	$htmlcode = '';
	$imgopts = '';

	if ($fieldtype == 'oneline')
	{
		$imgopts = "width='20' height='20'";
	}


	$htmlcode .= ""
	. "<a href =\"javascript:start_popup_editor('".$fieldname."','".$fieldtext."')\" id='".$fieldname."_ctrl'><img alt='' id='".$fieldname."_popupctrlena' name='".$fieldname."_popupctrlena' border='0' src='".$imagefiles."/edithtmlpopup.png' /><img alt='' id='".$fieldname."_popupctrldis' name='".$fieldname."_popupctrldis' border='0' src='".$imagefiles."/edithtmlpopup_disabled.png' style='display: none' /></a>";

	return $htmlcode;
}

?>
