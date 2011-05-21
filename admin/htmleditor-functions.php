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


function PrepareEditorScript($surveyid=null)
{
    global $clang, $imageurl, $homeurl, $uploaddir, $uploadurl;
    global $sCKEditorURL, $js_admin_includes, $defaulthtmleditormode;
    $sHTMLEditorMode=$_SESSION['htmleditormode'];
    if ($sHTMLEditorMode=='default') {
        $sHTMLEditorMode=$defaulthtmleditormode;
    }

    $js_admin_includes[]=$sCKEditorURL.'/ckeditor.js';
    $js_admin_includes[]=$sCKEditorURL.'/adapters/jquery.js';
    $js_admin_includes[]='scripts/editor.js';
    if (isset($surveyid)) {
        $_SESSION['KCFINDER'] = array();
        $_SESSION['KCFINDER']['disabled'] = false;
        $_SESSION['KCFINDER']['uploadURL'] = $uploadurl.'/'.$surveyid;
        $_SESSION['KCFINDER']['uploadDir'] = $uploaddir.'/'.$surveyid;
    }



    $sReturnScrip="<script type=\"text/javascript\">\n"
        ."sHTMLEditorMode='".$sHTMLEditorMode."';"
        ."</script>";

    return $sReturnScrip;
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
    global $clang, $imageurl, $homeurl, $rooturl, $sCKEditorURL, $fckeditexpandtoolbar, $uploadurl;

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
    . "$oFCKeditorVarName.BasePath     = '".$sCKEditorURL."/';\n"
    . "$oFCKeditorVarName.Config[\"CustomConfigurationsPath\"] = \"".$sCKEditorURL."/limesurvey-config.js\";\n"
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
