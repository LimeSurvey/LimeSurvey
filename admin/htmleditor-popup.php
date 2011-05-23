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

require_once(dirname(__FILE__).'/../config-defaults.php');
require_once(dirname(__FILE__).'/../common.php');

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

		<tr><td align="center"><br /><span style="color:red;"><strong>
		</strong></span><br />
		</table>
		<form  onsubmit="self.close()">
			<input type="submit" value="'.$clang->gT("Close Editor").'" />
			<input type="hidden" name="checksessionbypost" value="'.$_SESSION['checksessionpost'].'" />
		</form>
	</body>
	</html>';
}
else {
    $fieldname=$_GET['fieldname'];
    $fieldtext=$_GET['fieldtext'];
    if (get_magic_quotes_gpc()) $fieldtext = stripslashes($fieldtext);
    $controlidena=$_GET['fieldname'].'_popupctrlena';
    $controliddis=$_GET['fieldname'].'_popupctrldis';

    $sid=sanitize_int($_GET['sid']);
    $gid=sanitize_int($_GET['gid']);
    $qid=sanitize_int($_GET['qid']);
    $fieldtype=preg_replace("/[^_.a-zA-Z0-9-]/", "",$_GET['fieldtype']);
    $action=preg_replace("/[^_.a-zA-Z0-9-]/", "",$_GET['action']);

    $toolbarname='popup';
    $htmlformatoption='';

    if ( $fieldtype == 'email-inv' ||
    $fieldtype == 'email-reg' ||
    $fieldtype == 'email-conf' ||
    $fieldtype == 'email-rem' )
    {
        $htmlformatoption = ",fullPage:true";
    }

    $output = '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
	<html>
	<head>
		<title>'.$clang->gT("Editing").' '.$fieldtext.'</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="noindex, nofollow" />
        <script type="text/javascript" src="'.$rooturl.'/scripts/jquery/jquery.js"></script>
		<script type="text/javascript" src="'.$sCKEditorURL.'/ckeditor.js"></script>
	</head>';


    $output .= "
	<body>
	<form method='post' onsubmit='saveChanges=true;'>

			<input type='hidden' name='checksessionbypost' value='".$_SESSION['checksessionpost']."' />
			<script type='text/javascript'>
	<!--
	function closeme()
	{
		window.onbeforeunload = new Function('var a = 1;');
		self.close();
	}

	window.onbeforeunload= function (evt) {
		close_editor();
		closeme();
	}


	var saveChanges = false;
    $(document).ready(function(){

    	var oCKeditor = CKEDITOR.replace( 'MyTextarea' ,  { height	: '350',
    	                                            width	: '98%',
    	                                            value   : window.opener.document.getElementsByName(\"".$fieldname."\")[0].value,
    	                                            customConfig : \"".$sCKEditorURL."/limesurvey-config.js\",
                                                    toolbarStartupExpanded : true,
                                                    ToolbarCanCollapse : false,
                                                    toolbar : '".$toolbarname."',
                                                    LimeReplacementFieldsSID : \"".$sid."\",
                                                    LimeReplacementFieldsGID : \"".$gid."\",
                                                    LimeReplacementFieldsQID : \"".$qid."\",
                                                    LimeReplacementFieldsType: \"".$fieldtype."\",
                                                    LimeReplacementFieldsAction: \"".$action."\",
                                                    smiley_path: \"".$rooturl."/upload/images/smiley/msn/\"
                                                    {$htmlformatoption} });
    });

	function FCKeditor_OnComplete( editorInstance )
	{
		//editorInstance.Events.AttachEvent( 'OnSelectionChange', DoSomething ) ;
		editorInstance.ToolbarSet.CurrentInstance.Commands.GetCommand('FitWindow').Execute();
		window.status='LimeSurvey ".$clang->gT("Editing", "js")." ".javascript_escape($fieldtext,true)."';
	}

	function html_transfert()
	{
		var oEditor = CKeditor.instances['MyTextarea'];\n";

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
	    //$output .= "\t\tvar editedtext = oEditor.GetXHTML();\n";
	    $output .= "\t\tvar editedtext = oEditor.GetXHTML('no strip new line');\n"; // adding a parameter avoids stripping \n
	}



	$output .=	"

		window.opener.document.getElementsByName('".$fieldname."')[0].value = editedtext;
	}


	function close_editor()
	{
        html_transfert();

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

	$output .= "<textarea id='MyTextarea' name='MyTextarea'></textarea>";
	$output .= "
	</body>
	</html>";
}

echo $output;
?>
