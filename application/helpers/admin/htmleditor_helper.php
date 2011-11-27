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
 * $Id: htmleditor-functions.php 10193 2011-06-05 12:20:37Z c_schmitz $
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


function PrepareEditorScript($load=false, $controller)
{
    $js_admin_includes = Yii::app()->getConfig("js_admin_includes");
    $clang = Yii::app()->lang;
    $data['clang'] = $clang;
    $js_admin_includes[]=Yii::app()->getConfig('sCKEditorURL').'/ckeditor.js';
    Yii::app()->setConfig("js_admin_includes", $js_admin_includes);

    if ($load == false)
    {

        return $controller->render('/admin/survey/prepareEditorScript_view',$data,true);
    }
    else
    {
        $controller->render('/admin/survey/prepareEditorScript_view',$data);
    }
}

function getEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null,$action=null)
{
    //error_log("TIBO fieldtype=$fieldtype,fieldname=$fieldname,fieldtext=$fieldtext,surveyID=$surveyID,gID=$gID,qID=$qID,action=$action");
	$session = &Yii::app()->session;
    if ($session['htmleditormode'] &&
    $session['htmleditormode'] == 'none')
    {
        return '';
    }


    if (!$session['htmleditormode'] ||
    ($session['htmleditormode'] != 'inline' &&
    $session['htmleditormode'] != 'popup') )
    {
        $htmleditormode = Yii::app()->getConfig('defaulthtmleditormode');
    }
    else
    {
        $htmleditormode = $session['htmleditormode'];
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
    $clang = Yii::app()->lang;
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
	. "\t<img alt=\"".$clang->gT("Start HTML Editor in a Popup Window")."\" id='".$fieldname."_popupctrlena' name='".$fieldname."_popupctrlena' border='0' src='".Yii::app()->getConfig('imageurl')."/edithtmlpopup.png'  $imgopts align='top' class='btneditanswerena' />\n"
	. "\t<img alt=\"".$clang->gT("Give focus to the HTML Editor Popup Window")."\" id='".$fieldname."_popupctrldis' name='".$fieldname."_popupctrldis' border='0' src='".Yii::app()->getConfig('imageurl')."/edithtmlpopup_disabled.png' style='display: none'  $imgopts align='top' class='btneditanswerdis' />\n"
	. "</a>\n";

    return $htmlcode;
}

function getInlineEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null,$action=null)
{
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
        $ckeditexpandtoolbar = Yii::app()->getConfig('ckeditexpandtoolbar');
        if (!isset($ckeditexpandtoolbar) ||  $ckeditexpandtoolbar == true)
        {
            $toolbaroption = ",toolbarStartupExpanded:true\n"
                            .",toolbar:'inline'\n";
        }
    }

    if ( $fieldtype == 'email-inv' ||
         $fieldtype == 'email-reg' ||
         $fieldtype == 'email-conf'||
         $fieldtype == 'email-admin-notification'||
         $fieldtype == 'email-admin-resp'||
         $fieldtype == 'email-rem' )
    {
        $htmlformatoption = ",fullPage:true\n";
    }


    $htmlcode .= ""
    . "<script type=\"text/javascript\">\n"
    . "$(document).ready(function(){ var $oCKeditorVarName = CKEDITOR.replace('$fieldname', {
                                                                 customConfig : \"".Yii::app()->getConfig('sCKEditorURL')."/limesurvey-config.js\"
                                                                ,LimeReplacementFieldsType : \"".$fieldtype."\"
                                                                ,LimeReplacementFieldsSID : \"".$surveyID."\"
                                                                ,LimeReplacementFieldsGID : \"".$gID."\"
                                                                ,LimeReplacementFieldsQID : \"".$qID."\"
                                                                ,LimeReplacementFieldsType : \"".$fieldtype."\"
                                                                ,LimeReplacementFieldsAction : \"".$action."\"
                                                                ,LimeReplacementFieldsPath : \"".Yii::app()->createUrl("admin/fck_LimeReplacementFields/index/")."\"
                                                                ,width:'660'
                                                                ,language:'".sTranslateLangCode2CK(Yii::app()->getConfig('adminlang'))."'
                                                                ,smiley_path : \"".Yii::app()->getConfig('uploadurl')."/images/smiley/msn/\"\n"
                                                                . $htmlformatoption
                                                                . $toolbaroption
                                                            ."});
                                                            \$('#$fieldname').parents('ul:eq(0)').addClass('editor-parent');
	});";


    $htmlcode.= '</script>';

    return $htmlcode;
}

?>
