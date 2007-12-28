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
//include_once("login_check.php");

// config.php itself includes common.php
//if (isset($_REQUEST['rootdir'])) {die('You cannot start this script directly');}
require_once(dirname(__FILE__).'/../config.php');
require_once(dirname(__FILE__).'/../classes/core/language.php');


if (!isset($_GET['lang']))
{
	$clang = new limesurvey_lang("en");
}
else
{
	$clang = new limesurvey_lang($_GET['lang']);
}

if (!isset($_GET['fieldname']) || !isset($_GET['fieldtext']))
{
	$output = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html>
<head>
	<title>LimeSurvey '.$clang->gT("HTML Editor").'</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex, nofollow" />
</head>'

	. '
	<body>
		<div class="maintitle">
			LimeSurvey '.$clang->gT("HTML Editor").'
		</div>
		<hr />
		
		<tr><td align="center"><br /><font color="red"><strong>	
		</strong></font><br />
		</table>
		<form  onsubmit="self.close()">
			<input type="submit" value="'.$clang->gT("Close Editor").'" />
		</form>
	</body>
	</html>';
}
else {
	$fieldname=$_GET['fieldname'];
	$fieldtext=$_GET['fieldtext'];
	$controlidena=$_GET['fieldname'].'_popupctrlena';
	$controliddis=$_GET['fieldname'].'_popupctrldis';

	$sid=sanitize_int($_GET['sid']);
	$gid=sanitize_int($_GET['gid']);
	$qid=sanitize_int($_GET['qid']);
	$fieldtype=preg_replace("/[^_.a-zA-Z0-9-]/", "",$_GET['fieldtype']);
	$action=preg_replace("/[^_.a-zA-Z0-9-]/", "",$_GET['action']);

	if (isset($_GET['toolbarname']))
	{
		$toolbarname=$_GET['toolbarname'];
	}
	else
	{
		$toolbarname='LimeSurveyToolbarfullPopup';
	}

	$output = '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
	<html>
	<head>
		<title>'.$clang->gT("Editing").' '.$fieldtext.'</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="noindex, nofollow" />
		<script type="text/javascript" src="'.$homeurl.'/scripts/fckeditor/fckeditor.js"></script>
	</head>';


	$output .= "
	<body>
	<form method='post' onsubmit='saveChanges=true;'>

			<script type='text/javascript'>
	<!--
	window.onbeforeunload= function (evt) {
		close_editor();
		self.close();
	}

	var saveChanges = false;

	var oFCKeditor = new FCKeditor( 'MyTextarea' );
	oFCKeditor.BasePath	= '".$homeurl."/scripts/fckeditor/';
	oFCKeditor.Height	= '350';
	oFCKeditor.Width	= '98%';
	oFCKeditor.Value      = window.opener.document.getElementsByName(\"".$fieldname."\")[0].value;
	oFCKeditor.Config[\"CustomConfigurationsPath\"] = \"".$homeurl."/scripts/fckeditor/limesurvey-config.js\";
	oFCKeditor.Config[\"ToolbarStartExpanded\"] = true;
	oFCKeditor.Config[\"ToolbarCanCollapse\"] = false;
	oFCKeditor.ToolbarSet = '".$toolbarname."';
	oFCKeditor.Config[\"LimeReplacementFieldsSID\"] = \"".$sid."\";
	oFCKeditor.Config[\"LimeReplacementFieldsGID\"] = \"".$gid."\";
	oFCKeditor.Config[\"LimeReplacementFieldsQID\"] = \"".$qid."\";
	oFCKeditor.Config[\"LimeReplacementFieldsType\"] = \"".$fieldtype."\";
	oFCKeditor.Config[\"LimeReplacementFieldsAction\"] = \"".$action."\";
	oFCKeditor.Create();

	function FCKeditor_OnComplete( editorInstance )
	{
		//editorInstance.Events.AttachEvent( 'OnSelectionChange', DoSomething ) ;
		editorInstance.ToolbarSet.CurrentInstance.Commands.GetCommand('FitWindow').Execute();
		window.status='LimeSurvey ".$clang->gT("Editing", "js")." ".javascript_escape($fieldtext)."';
	}

	function html_transfert()
	{
		var oEditor = FCKeditorAPI.GetInstance('MyTextarea');\n";

if ($fieldtype == 'editanswer' ||
	$fieldtype == 'addanswer' ||
	$fieldtype == 'editlabel' ||
	$fieldtype == 'addlabel')
{
	$output .= "\t\tvar editedtext = oEditor.GetXHTML().replace(new RegExp( \"\\n\", \"g\" ),'');\n";
	$output .= "\t\tvar editedtext = oEditor.GetXHTML().replace(new RegExp( \"\\r\", \"g\" ),'');\n";
}
else
{
	$output .= "\t\tvar editedtext = oEditor.GetXHTML();\n";
}



	$output .=	"
		window.opener.document.getElementsByName('".$fieldname."')[0].value = editedtext;
	}

	function close_editor()
	{
		if (saveChanges == false)
		{
			if (confirm('".$clang->gT("Do you want to save your changes ?", "js")."'))
			{
				html_transfert();
			}	
		}

		if (saveChanges == true)
		{
			html_transfert();
		}

		window.opener.document.getElementsByName('".$fieldname."')[0].readOnly= false;
		window.opener.document.getElementsByName('".$fieldname."')[0].className='htmlinput';
		window.opener.document.getElementById('".$controlidena."').style.display='';
		window.opener.document.getElementById('".$controliddis."').style.display='none';
		window.opener.focus();
		return true;
	}

	//-->
			</script>
	</form>";

	//$output .= "<textarea id='MyTextarea' name='MyTextarea'></textarea>";
	$output .= "	
	</body>
	</html>";
}

echo $output;
?>
