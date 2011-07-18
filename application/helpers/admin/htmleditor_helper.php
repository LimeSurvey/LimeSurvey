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


function PrepareEditorScript($load=false)
{
    //global $clang; //, $imageurl, $homeurl, $js_admin_includes;
    //global $this->config->item['sCKEditorURL'];
    $CI =& get_instance();
    $js_admin_includes = $CI->config->item("js_admin_includes");
    $clang = $CI->limesurvey_lang;
    $data['clang'] = $clang;
    $js_admin_includes[]=$CI->config->item('sCKEditorURL').'/ckeditor.js';
    $CI->config->set_item("js_admin_includes", $js_admin_includes);
    
    if ($load == false)
    {
        
        return $CI->load->view('admin/Survey/prepareEditorScript_view',$data,true);
    }
    else
    {
        $CI->load->view('admin/Survey/prepareEditorScript_view',$data);
    }
}

function getEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null,$action=null)
{
    //global $CI->config->item('defaulthtmleditormode');
    //error_log("TIBO fieldtype=$fieldtype,fieldname=$fieldname,fieldtext=$fieldtext,surveyID=$surveyID,gID=$gID,qID=$qID,action=$action");
    $CI =& get_instance(); 
    $CI->load->helper('common');
    if ($CI->session->userdata('htmleditormode') &&
    $CI->session->userdata('htmleditormode') == 'none')
    {
        return '';
    }


    if (!$CI->session->userdata('htmleditormode') ||
    ($CI->session->userdata('htmleditormode') != 'inline' &&
    $CI->session->userdata('htmleditormode') != 'popup') )
    {
        $htmleditormode = $CI->config->item('defaulthtmleditormode');
    }
    else
    {
        $htmleditormode = $CI->session->userdata('htmleditormode');
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
    $CI =& get_instance(); //global $clang; //, $imageurl, $homeurl;
    $clang = $CI->limesurvey_lang;
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
	. "\t<img alt=\"".$clang->gT("Start HTML Editor in a Popup Window")."\" id='".$fieldname."_popupctrlena' name='".$fieldname."_popupctrlena' border='0' src='".$CI->config->item('imageurl')."/edithtmlpopup.png'  $imgopts align='top' class='btneditanswerena' />\n"
	. "\t<img alt=\"".$clang->gT("Give focus to the HTML Editor Popup Window")."\" id='".$fieldname."_popupctrldis' name='".$fieldname."_popupctrldis' border='0' src='".$CI->config->item('imageurl')."/edithtmlpopup_disabled.png' style='display: none'  $imgopts align='top' class='btneditanswerdis' />\n"
	. "</a>\n";

    return $htmlcode;
}

function getInlineEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null,$action=null)
{
    //global $clang, $this->config->item['sCKEditorURL'], $ckeditexpandtoolbar;//, $uploadurl;
    $CI =& get_instance(); 
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
        $ckeditexpandtoolbar = $CI->config->item('ckeditexpandtoolbar');
        if (!isset($ckeditexpandtoolbar) ||  $ckeditexpandtoolbar == true)
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
                                                                 customConfig : \"".$CI->config->item('sCKEditorURL')."/limesurvey-config.js\"
                                                                ,LimeReplacementFieldsType : \"".$fieldtype."\"
                                                                ,LimeReplacementFieldsSID : \"".$surveyID."\"
                                                                ,LimeReplacementFieldsGID : \"".$gID."\"
                                                                ,LimeReplacementFieldsQID : \"".$qID."\"
                                                                ,LimeReplacementFieldsType : \"".$fieldtype."\"
                                                                ,LimeReplacementFieldsAction : \"".$action."\"
                                                                ,LimeReplacementFieldsPath : \"".site_url("admin/fck_LimeReplacementFields/index/")."\"
                                                                ,width:'660'
                                                                ,language:'".sTranslateLangCode2CK($CI->session->userdata('adminlang'))."'
                                                                ,smiley_path : \"".$CI->config->item('uploadurl')."/images/smiley/msn/\"\n"
                                                                . $htmlformatoption
                                                                . $toolbaroption
    ."});});";


    $htmlcode.= '</script>';

    return $htmlcode;
}

?>
