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

/*****
function PrepareEditorPopupScript()
{
    global $clang,$imageurl,$homeurl;

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
    //	. "controlid = fieldname + '_popupctrl';\n"
    . "controlidena = fieldname + '_popupctrlena';\n"
    . "controliddis = fieldname + '_popupctrldis';\n"
    . "numwindows = editorwindowsHash.length;\n"
    . "activepopup = find_popup_editor(fieldname);\n"
    . "if (activepopup == null)\n"
    . "{\n"
    . "\tdocument.getElementsByName(fieldname)[0].readOnly=true;\n"
    . "\tdocument.getElementsByName(fieldname)[0].className='readonly';\n"
    //	. "\tdocument.getElementById(controlid).src='".$imageurl."/edithtmlpopup_disabled.png';\n"
    . "\tdocument.getElementById(controlidena).style.display='none';\n"
    . "\tdocument.getElementById(controliddis).style.display='';\n"
    . "\tpopup = window.open('".$homeurl."/htmleditor-popup.php?fieldname='+fieldname+'&fieldtext='+fieldtext+'&fieldtype='+fieldtype+'&action='+action+'&sid='+sid+'&gid='+gid+'&qid='+qid+'&lang=".$clang->getlangcode()."','', 'location=no, status=yes, scrollbars=auto, menubar=no, resizable=yes, width=600, height=400');\n"
    . "\teditorwindowsHash[fieldname] = popup;\n"
    . "}\n"
    . "else\n"
    . "{\n"
    . "\tactivepopup.focus();\n"
    . "}\n"
    . "\t}\n"
    . "\n"
    . "function updateFCKeditor(fieldname,value)\n"
    . "{\t\n"
    . "\tvar mypopup= editorwindowsHash[fieldname];\n"
    . "\tif (mypopup)\n"
    . "\t{\n"
    . "var oMyEditor = mypopup.FCKeditorAPI.GetInstance('MyTextarea');\n"
    . "if (oMyEditor) {oMyEditor.SetHTML(value);}\n"
    . "mypopup.focus();\n"
    . "\t}\n"
    . "}\n"
    . "--></script>\n";

    return $script;
}
*******/

function PrepareEditorGeneric()
{
    global $clang,$imageurl,$homeurl;
    global $sFCKEditorURL;

    $script ="<script type=\"text/javascript\" src=\"".$sFCKEditorURL."/fckeditor.js\"></script>\n";
    $script .= "<script type='text/javascript'>\n"
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
    //	. "controlid = fieldname + '_popupctrl';\n"
    . "controlidena = fieldname + '_popupctrlena';\n"
    . "controliddis = fieldname + '_popupctrldis';\n"
    . "numwindows = editorwindowsHash.length;\n"
    . "activepopup = find_popup_editor(fieldname);\n"
    . "if (activepopup == null)\n"
    . "{\n"
    . "\tdocument.getElementsByName(fieldname)[0].readOnly=true;\n"
    . "\tdocument.getElementsByName(fieldname)[0].className='readonly';\n"
    //	. "\tdocument.getElementById(controlid).src='".$imageurl."/edithtmlpopup_disabled.png';\n"
    . "\tdocument.getElementById(controlidena).style.display='none';\n"
    . "\tdocument.getElementById(controliddis).style.display='';\n"
    . "\tpopup = window.open('".$homeurl."/htmleditor-popup.php?fieldname='+fieldname+'&fieldtext='+fieldtext+'&fieldtype='+fieldtype+'&action='+action+'&sid='+sid+'&gid='+gid+'&qid='+qid+'&lang=".$clang->getlangcode()."','', 'location=no, status=yes, scrollbars=auto, menubar=no, resizable=yes, width=600, height=400');\n"
    . "\teditorwindowsHash[fieldname] = popup;\n"
    . "}\n"
    . "else\n"
    . "{\n"
    . "\tactivepopup.focus();\n"
    . "}\n"
    . "\t}\n"
    . "\n"
    . "function updateFCKeditor(fieldname,value)\n"
    . "{\t\n"
    . "\tvar mypopup= editorwindowsHash[fieldname];\n"
    . "\tif (mypopup)\n"
    . "\t{\n"
    . "\t\tvar oMyEditor = mypopup.FCKeditorAPI.GetInstance('MyTextarea');\n"
    . "\t\tif (oMyEditor) {oMyEditor.SetHTML(value);}\n"
    . "\t\tmypopup.focus();\n"
    . "\t}\n"
    . "\telse\n"
    . "\t{\n"
    . "\t\tvar oMyEditor = FCKeditorAPI.GetInstance(fieldname);\n"
    . "\t\toMyEditor.SetHTML(value);\n"
    . "\t}\n"
    . "}\n"
    . "--></script>\n";

    return $script;
}

/*********
function PrepareEditorInlineScript()
{
    global $homeurl, $sFCKEditorURL;
    $script ="<script type=\"text/javascript\" src=\"".$sFCKEditorURL."/fckeditor.js\"></script>\n"
    . "<script type=\"text/javascript\">\n"
    . "<!--\n"
    . "function updateFCKeditor(fieldname,value)\n"
    . "{\n"
    . "\tvar oMyEditor = FCKeditorAPI.GetInstance(fieldname);\n"
    . "\toMyEditor.SetHTML(value);\n"
    . "}\n"
    . "-->\n"
    . "</script>\n";

//     $script .= ""
//     . "<script type='text/javascript'>\n"
//     . "<!--\n"
//     ."function FCKeditor_OnComplete( editorInstance )\n"
//     . "{\n"
//     . "\teditorInstance.Events.AttachEvent( 'OnBlur'	, FCKeditor_OnBlur ) ;\n"
//     . "\teditorInstance.Events.AttachEvent( 'OnFocus', FCKeditor_OnFocus ) ;\n"
//     ."}\n"
//     . "function FCKeditor_OnBlur( editorInstance )\n"
//     . "{\n"
//     . "\teditorInstance.ToolbarSet.Collapse() ;\n"
//     . "}\n"
//     . "function FCKeditor_OnFocus( editorInstance )\n"
//     . "{\n"
//     ."\teditorInstance.ToolbarSet.Expand() ;\n"
//     ."}\n"
//     . "--></script>\n";
    return $script;
}
********/

function PrepareEditorScript($fieldtype=null)
{
/***    global $defaulthtmleditormode;

    if (isset($_SESSION['htmleditormode']) &&
    $_SESSION['htmleditormode'] == 'none')
    {
        return "<script type=\"text/javascript\">\n"
        . "<!--\n"
        . "function updateFCKeditor(fieldname,value) { return true;}\n"
        . "-->\n"
        . "</script>\n";
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

    if ($htmleditormode == 'popup' ||
    $fieldtype == 'editanswer' ||
    $fieldtype == 'addanswer' ||
    $fieldtype == 'editlabel' ||
    $fieldtype == 'addlabel')
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
****/
    return PrepareEditorGeneric();
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
	. "\t<img alt=\"".$clang->gT("Start HTML Editor in a Popup Window")."\" id='".$fieldname."_popupctrlena' name='".$fieldname."_popupctrlena' border='0' src='".$imageurl."/edithtmlpopup.png'  $imgopts class='btneditanswerena' />\n"
	. "\t<img alt=\"".$clang->gT("Give focus to the HTML Editor Popup Window")."\" id='".$fieldname."_popupctrldis' name='".$fieldname."_popupctrldis' border='0' src='".$imageurl."/edithtmlpopup_disabled.png' style='display: none'  $imgopts align='top' class='btneditanswerdis' />\n"
	. "</a>\n";

    return $htmlcode;
}

function getInlineEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null,$action=null)
{
    global $clang, $imageurl, $homeurl, $rooturl, $sFCKEditorURL, $fckeditexpandtoolbar, $uploadurl;

    $htmlcode = '';
    $imgopts = '';
    $toolbarname = 'Basic';
    $toolbaroption="";
    $htmlformatoption="";
    $oFCKeditorVarName = "oFCKeditor_".str_replace("-","_",$fieldname);

    if ( ($fieldtype == 'editanswer' ||
    $fieldtype == 'addanswer' ||
    $fieldtype == 'editlabel' ||
    $fieldtype == 'addlabel') && (preg_match("/^translate/",$action) == 0) )
    {
        $toolbarname = 'LimeSurveyToolbarfull';
        $toolbaroption="$oFCKeditorVarName.Config[\"ToolbarLocation\"]=\"Out:xToolbar\";\n"
        . "$oFCKeditorVarName.Config[\"ToolbarStartExpanded\"]=true;\n"
        . "$oFCKeditorVarName.Config[\"ToolbarCanCollapse\"]=false;\n"
        . "$oFCKeditorVarName.Height = \"50\"\n";
    }
    else
    {
        if (!isset($fckeditexpandtoolbar) || $fckeditexpandtoolbar == true)
        {
            $toolbaroption .= "$oFCKeditorVarName.Config[\"ToolbarStartExpanded\"]=true;\n";
        }
    }

    if ( $fieldtype == 'email-inv' ||
         $fieldtype == 'email-reg' ||
         $fieldtype == 'email-conf'||
         $fieldtype == 'email-admin-conf'||
         $fieldtype == 'email-admin-resp'||
         $fieldtype == 'email-rem' )
    {
        $htmlformatoption = "$oFCKeditorVarName.Config[\"FullPage\"]=true;\n"
        . "$oFCKeditorVarName.Height = \"500\"\n";        
    }


    $htmlcode .= ""
    . "<script type=\"text/javascript\">\n"
    . "var $oFCKeditorVarName = new FCKeditor('$fieldname');\n"
    . "$oFCKeditorVarName.BasePath     = '".$sFCKEditorURL."/';\n"
    . "$oFCKeditorVarName.Config[\"CustomConfigurationsPath\"] = \"".$sFCKEditorURL."/limesurvey-config.js\";\n"
    . "$oFCKeditorVarName.Config[\"LimeReplacementFieldsType\"] = \"".$fieldtype."\";\n"
    . "$oFCKeditorVarName.Config[\"LimeReplacementFieldsSID\"] = \"".$surveyID."\";\n"
    . "$oFCKeditorVarName.Config[\"LimeReplacementFieldsGID\"] = \"".$gID."\";\n"
    . "$oFCKeditorVarName.Config[\"LimeReplacementFieldsQID\"] = \"".$qID."\";\n"
    . "$oFCKeditorVarName.Config[\"LimeReplacementFieldsType\"] = \"".$fieldtype."\";\n"
    . "$oFCKeditorVarName.Config[\"LimeReplacementFieldsAction\"] = \"".$action."\";\n"
    . "$oFCKeditorVarName.Config[\"SmileyPath\"] = \"".$uploadurl."/images/smiley/msn/\";\n"
    . $htmlformatoption
    . $toolbaroption;

    if ($fieldtype == 'answer' || $fieldtype == 'label')
    {
        $htmlcode .= ""
        . "$oFCKeditorVarName.Config[ 'ToolbarLocation' ] = 'Out:xToolbar' ;\n";
    }

    $htmlcode .= ""
    . "$oFCKeditorVarName.ToolbarSet = '".$toolbarname."';\n";
    
    if ( $fieldtype == 'email-inv' ||
     $fieldtype == 'email-reg' ||
     $fieldtype == 'email-conf'||
     $fieldtype == 'email-admin-conf'||
     $fieldtype == 'email-admin-resp'||
     $fieldtype == 'email-rem' )
    { // do nothing
        $htmlcode.= "$oFCKeditorVarName.ReplaceTextarea() ;\n";
    }
    else
    {
        $htmlcode.= "$oFCKeditorVarName.ReplaceTextarea() ;\n";
    }
    $htmlcode.= '</script>';

    return $htmlcode;
}

?>
