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
//Security Checked: POST/GET/SESSION/DB/returnglobal


function sTranslateLangCode2CK($sLanguageCode){

    $aTranslationTable=array('de-informal'=>'de',
                             'nl-formal'=>'nl');
    if (isset($aTranslationTable[$sLanguageCode])) {
        $sResultCode=$aTranslationTable[$sLanguageCode];
    }
    else
    {
        $sResultCode=$sLanguageCode;
    }
    return $sResultCode;

}


function PrepareEditorScript()
{
    global $clang, $imageurl, $homeurl, $js_admin_includes;
    global $sCKEditorURL;

    $js_admin_includes[]=$sCKEditorURL.'/ckeditor.js';
    $script = "<script type='text/javascript'>\n"
    . "<!--\n"
    . "var editorwindowsHash = new Object();\n"
    . "function find_popup_editor(fieldname)\n"
    . "\t{\t\n"
    . "var window = null;\n"
    . "for (var key in editorwindowsHash)\n"
    . "{\n"
    . "\tif (key==fieldname && !editorwindowsHash[key].closed)\n"
    . "\t{\n"
    . "window = editorwindowsHash[key];\n"
    . "return window;\n"
    . "\t}\n"
    . "}\n"
    . "\treturn null;\n"
    . "\t}\t\n"
    . "\n"
    . "function start_popup_editor(fieldname, fieldtext, sid, gid, qid, fieldtype, action)\n"
    . "\t{\t\n"
    . "controlidena = fieldname + '_popupctrlena';\n"
    . "controliddis = fieldname + '_popupctrldis';\n"
    . "numwindows = editorwindowsHash.length;\n"
    . "activepopup = find_popup_editor(fieldname);\n"
    . "if (activepopup == null)\n"
    . "{\n"
    . "\tdocument.getElementsByName(fieldname)[0].readOnly=true;\n"
    . "\tdocument.getElementsByName(fieldname)[0].className='readonly';\n"
    . "\tdocument.getElementById(controlidena).style.display='none';\n"
    . "\tdocument.getElementById(controliddis).style.display='';\n"
    . "\tpopup = window.open('".$homeurl."/htmleditor-popup.php?fieldname='+fieldname+'&fieldtext='+fieldtext+'&fieldtype='+fieldtype+'&action='+action+'&sid='+sid+'&gid='+gid+'&qid='+qid+'&lang=".$clang->getlangcode()."','', 'location=no, status=yes, scrollbars=auto, menubar=no, resizable=yes, width=690, height=500');\n"
    . "\teditorwindowsHash[fieldname] = popup;\n"
    . "}\n"
    . "else\n"
    . "{\n"
    . "\tactivepopup.focus();\n"
    . "}\n"
    . "\t}\n"
    . "\n"
    . "function updateCKeditor(fieldname,value)\n"
    . "{\t\n"
    . "\tvar mypopup= editorwindowsHash[fieldname];\n"
    . "\tif (mypopup)\n"
    . "\t{\n"
    . "\t\tvar oMyEditor = mypopup.CKEDITOR.instances['MyTextarea'];\n"
    . "\t\tif (oMyEditor) {oMyEditor.setData(value);}\n"
    . "\t\tmypopup.focus();\n"
    . "\t}\n"
    . "\telse\n"
    . "\t{\n"
    . "\t\tvar oMyEditor = CKEDITOR.instances[fieldname];\n"
    . "\t\toMyEditor.setData(value);\n"
    . "\t}\n"
    . "}\n"
    . "--></script>\n";

    return $script;
}

function getEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null,$action=null)
{
    global $defaulthtmleditormode;
    //error_log("TIBO fieldtype=$fieldtype,fieldname=$fieldname,fieldtext=$fieldtext,surveyID=$surveyID,gID=$gID,qID=$qID,action=$action");

    if (isset($_SESSION['htmleditormode']) &&
    $_SESSION['htmleditormode'] == 'none')
    {
        return '';
    }


    if (!isset($_SESSION['htmleditormode']) ||
    ($_SESSION['htmleditormode'] != 'inline' &&
    $_SESSION['htmleditormode'] != 'popup') )
    {
        $htmleditormode = $defaulthtmleditormode;
    }
    else
    {
        $htmleditormode = $_SESSION['htmleditormode'];
    }

    if ( ($fieldtype == 'email-inv' ||
    $fieldtype == 'email-reg' ||
    $fieldtype == 'email-conf' ||
    $fieldtype == 'email-rem' ) &&
    getEmailFormat($surveyID) != 'html')
    {
        return '';
    }

    if ($htmleditormode == 'popup' ||
    ( $fieldtype == 'editanswer' ||
    $fieldtype == 'addanswer' ||
    $fieldtype == 'editlabel' ||
    $fieldtype == 'addlabel') && (preg_match("/^translate/",$action) == 0 ) )
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
    global $clang, $imageurl, $homeurl;

    $htmlcode = '';
    $imgopts = '';
    $toolbarname = 'Basic';

    if ($fieldtype == 'editanswer' ||
    $fieldtype == 'addanswer' ||
    $fieldtype == 'editlabel' ||
    $fieldtype == 'addlabel')
    {
        $imgopts = "width='16' height='16'";
    }

    $htmlcode .= ""
    . "<a href=\"javascript:start_popup_editor('".$fieldname."','".$fieldtext."','".$surveyID."','".$gID."','".$qID."','".$fieldtype."','".$action."')\" id='".$fieldname."_ctrl' title=\"".$clang->gTview("Start HTML Editor in a Popup Window")."\" class='editorLink'>\n"
	. "\t<img alt=\"".$clang->gT("Start HTML Editor in a Popup Window")."\" id='".$fieldname."_popupctrlena' name='".$fieldname."_popupctrlena' border='0' src='".$imageurl."/edithtmlpopup.png'  $imgopts align='top' class='btneditanswerena' />\n"
	. "\t<img alt=\"".$clang->gT("Give focus to the HTML Editor Popup Window")."\" id='".$fieldname."_popupctrldis' name='".$fieldname."_popupctrldis' border='0' src='".$imageurl."/edithtmlpopup_disabled.png' style='display: none'  $imgopts align='top' class='btneditanswerdis' />\n"
	. "</a>\n";

    return $htmlcode;
}

function getInlineEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null,$action=null)
{
    global $clang, $imageurl, $homeurl, $rooturl, $sCKEditorURL, $ckeditexpandtoolbar, $uploadurl;

    $htmlcode = '';
    $imgopts = '';
    $toolbarname = 'inline';
    $toolbaroption="";
    $htmlformatoption="";
    $oCKeditorVarName = "oCKeditor_".str_replace("-","_",$fieldname);

    if ( ($fieldtype == 'editanswer' ||
    $fieldtype == 'addanswer' ||
    $fieldtype == 'editlabel' ||
    $fieldtype == 'addlabel') && (preg_match("/^translate/",$action) == 0) )
    {
        $toolbaroption= ",toolbarStartupExpanded:true\n"
                       .",toolbar:'popup'\n"
                       .",toolbarCanCollapse:false\n";
    }
    else
    {
        if (!isset($ckeditexpandtoolbar) || $ckeditexpandtoolbar == true)
        {
            $toolbaroption = ",toolbarStartupExpanded:true\n"
                            .",toolbar:'inline'\n";
        }
    }

    if ( $fieldtype == 'email-inv' ||
         $fieldtype == 'email-reg' ||
         $fieldtype == 'email-conf'||
         $fieldtype == 'email-admin-conf'||
         $fieldtype == 'email-admin-resp'||
         $fieldtype == 'email-rem' )
    {
        $htmlformatoption = ",fullPage:true\n";
    }


    $htmlcode .= ""
    . "<script type=\"text/javascript\">\n"
    . "$(document).ready(function(){ var $oCKeditorVarName = CKEDITOR.replace('$fieldname', {
                                                                 customConfig : \"".$sCKEditorURL."/limesurvey-config.js\"
                                                                ,LimeReplacementFieldsType : \"".$fieldtype."\"
                                                                ,LimeReplacementFieldsSID : \"".$surveyID."\"
                                                                ,LimeReplacementFieldsGID : \"".$gID."\"
                                                                ,LimeReplacementFieldsQID : \"".$qID."\"
                                                                ,LimeReplacementFieldsType : \"".$fieldtype."\"
                                                                ,LimeReplacementFieldsAction : \"".$action."\"
                                                                ,width:'660'
                                                                ,language:'".sTranslateLangCode2CK($_SESSION['adminlang'])."'
                                                                ,smiley_path : \"".$uploadurl."/images/smiley/msn/\"\n"
                                                                . $htmlformatoption
                                                                . $toolbaroption
    ."});});";

  /*  if ($fieldtype == 'answer' || $fieldtype == 'label')
    {
        $htmlcode .= ""
        . "$oCKeditorVarName.Config[ 'ToolbarLocation' ] = 'Out:xToolbar' ;\n";
    }

    $htmlcode .= ""
    . "$oCKeditorVarName.ToolbarSet = '".$toolbarname."';\n";*/

    if ( $fieldtype == 'email-inv' ||
     $fieldtype == 'email-reg' ||
     $fieldtype == 'email-conf'||
     $fieldtype == 'email-admin-conf'||
     $fieldtype == 'email-admin-resp'||
     $fieldtype == 'email-rem' )
    { // do nothing
        $htmlcode.= "CKEDITOR.replace('$fieldname') ;\n";
    }
    else
    {
        $htmlcode.= "CKEDITOR.replace('$fieldname') ;\n";
    }
    $htmlcode.= '</script>';

    return $htmlcode;
}

?>
