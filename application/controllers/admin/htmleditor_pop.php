<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 * $Id: htmleditor-popup.php 10193 2011-06-05 12:20:37Z c_schmitz $
 */

//Ensure script is not run directly, avoid path disclosure
//include_once("login_check.php");


class htmleditor_pop extends CAction {
 
    function run()
	{
		$fieldname = $_GET['index'];
		foreach($_GET[''] as $key=>$val)
		{
			$fieldtext = $key;
			$fieldtype = $val;
	}
    
        $this->index($fieldname, $fieldtext, $fieldtype);
	}
    
    function index($fieldname=0,$fieldtext=0,$fieldtype=0,$action=0,$sid=0,$gid=0,$qid=0,$lang=0)
    {
    	$yii = Yii::app();
    	$sid = (int) $sid;
		$gid = (int) $gid;
		$qid = (int) $qid;
		
        //require_once(dirname(__FILE__).'/../config-defaults.php');
        //require_once(dirname(__FILE__).'/../common.php');
        
        if (!$lang)
        {
            $yii->loadLibrary('Limesurvey_lang',array('en'));
            
            $clang = $yii->lang; // limesurvey_lang("en");
            
        }
        else
        {
            $yii->loadLibrary('Limesurvey_lang',array($lang));
            $clang = $yii->lang; // new limesurvey_lang($_GET['lang']);
        }
        
        if (!$fieldname || !$fieldtext)
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
        			<input type="hidden" name="checksessionbypost" value="'.$yii->session['checksessionpost'].'" />
        		</form>
        	</body>
        	</html>';
        }
        else {
            //$fieldname=$_GET['fieldname'];
            //$fieldtext=$_GET['fieldtext'];
            if (get_magic_quotes_gpc()) $fieldtext = stripslashes($fieldtext);
            $controlidena=$fieldname.'_popupctrlena';
            $controliddis=$fieldname.'_popupctrldis';
        
            $sid=sanitize_int($sid);
            $gid=sanitize_int($gid);
            $qid=sanitize_int($qid);
            $fieldtype=preg_replace("/[^_.a-zA-Z0-9-]/", "",$fieldtype);
            $action=preg_replace("/[^_.a-zA-Z0-9-]/", "",$action);
        
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
        		<title>'.sprintf($clang->gT("Editing %s"), $fieldtext).'</title>
        		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        		<meta name="robots" content="noindex, nofollow" />
                <script type="text/javascript" src="'.$yii->getConfig('generalscripts').'jquery/jquery.js"></script>
        		<script type="text/javascript" src="'.$yii->getConfig('sCKEditorURL').'/ckeditor.js"></script>
        	</head>';
        
        
            $output .= "
        	<body>
        	<form method='post' onsubmit='saveChanges=true;'>
        
        			<input type='hidden' name='checksessionbypost' value='".$yii->session['checksessionpost']."' />
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
                CKEDITOR.on('instanceReady',CKeditor_OnComplete);
            	var oCKeditor = CKEDITOR.replace( 'MyTextarea' ,  { height	: '350',
            	                                            width	: '98%',
            	                                            customConfig : \"".$yii->getConfig('sCKEditorURL')."/limesurvey-config.js\",
                                                            toolbarStartupExpanded : true,
                                                            ToolbarCanCollapse : false,
                                                            toolbar : '".$toolbarname."',
                                                            LimeReplacementFieldsSID : \"".$sid."\",
                                                            LimeReplacementFieldsGID : \"".$gid."\",
                                                            LimeReplacementFieldsQID : \"".$qid."\",
                                                            LimeReplacementFieldsType: \"".$fieldtype."\",
                                                            LimeReplacementFieldsAction: \"".$action."\",
                                                            smiley_path: \"".$yii->getConfig('rooturl')."/upload/images/smiley/msn/\"
                                                            {$htmlformatoption} });
            });
        
        	function CKeditor_OnComplete( evt )
        	{
                var editor = evt.editor;
                editor.setData(window.opener.document.getElementsByName(\"".$fieldname."\")[0].value);
                editor.execCommand('maximize');
        		window.status='LimeSurvey ".$clang->gT("Editing", "js")." ".javascript_escape($fieldtext,true)."';
        	}
        
        	function html_transfert()
        	{
        		var oEditor = CKEDITOR.instances['MyTextarea'];\n";
        
        	if ($fieldtype == 'editanswer' ||
        	$fieldtype == 'addanswer' ||
        	$fieldtype == 'editlabel' ||
        	$fieldtype == 'addlabel')
        	{
        	    $output .= "\t\tvar editedtext = oEditor.getData().replace(new RegExp( \"\\n\", \"g\" ),'');\n";
        	    $output .= "\t\tvar editedtext = oEditor.getData().replace(new RegExp( \"\\r\", \"g\" ),'');\n";
        	}
        	else
        	{
        	    //$output .= "\t\tvar editedtext = oEditor.GetXHTML();\n";
        	    $output .= "\t\tvar editedtext = oEditor.getData('no strip new line');\n"; // adding a parameter avoids stripping \n
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
        			</script>";
        
        	$output .= "<textarea id='MyTextarea' name='MyTextarea'></textarea>";
        	$output .= "
        	</form>
        	</body>
        	</html>";
        }
        
        echo $output;
        
        
        
    }   
    
    
    
}