<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    /*
    * LimeSurvey
    * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
    * All rights reserved.
    * License: GNU/GPL License v2 or later, see LICENSE.php
    * LimeSurvey is free software. This version may have been modified pursuant
    * to the GNU General Public License, and as distributed it includes or
    * is derivative of works licensed under the GNU General Public License or
    * other free or open source software licenses.
    * See COPYRIGHT.php for copyright notices and details.
    *
       */
    //include_once("login_check.php");
    //Security Checked: POST/GET/SESSION/DB/returnGlobal
    function initKcfinder()
    {
     Yii::app()->session['KCFINDER'] = array();

        $sAllowedExtensions = implode(' ', array_map('trim', explode(',', Yii::app()->getConfig('allowedresourcesuploads'))));
        $_SESSION['KCFINDER']['types'] = array(
            'files' => $sAllowedExtensions,
            'flash' => $sAllowedExtensions,
            'images' => $sAllowedExtensions
        );

        if (Yii::app()->getConfig('demoMode') === false &&
                isset(Yii::app()->session['loginID']) &&
                isset(Yii::app()->session['FileManagerContext']))
        {
            // disable upload at survey creation time
            // because we don't know the sid yet
            if (preg_match('/^(create|edit):(question|group|answer)/', Yii::app()->session['FileManagerContext']) != 0 ||
                    preg_match('/^edit:survey/', Yii::app()->session['FileManagerContext']) != 0 ||
                    preg_match('/^edit:assessments/', Yii::app()->session['FileManagerContext']) != 0 ||
                    preg_match('/^edit:emailsettings/', Yii::app()->session['FileManagerContext']) != 0)
            {
                $contextarray = explode(':', Yii::app()->session['FileManagerContext'], 3);
                $surveyid = $contextarray[2];

                if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update'))
                {
                    $_SESSION['KCFINDER']['disabled'] = false;
                    if (preg_match('/^edit:emailsettings/',$_SESSION['FileManagerContext']) != 0)
                    {
                        // Uploadurl use public url or getBaseUrl(true);
                        // Maybe need external function
                        $sBaseAbsoluteUrl=Yii::app()->getBaseUrl(true);
                        $sPublicUrl=Yii::app()->getConfig("publicurl");
                        $aPublicUrl=parse_url($sPublicUrl);
                        if(isset($aPublicUrl['scheme']) && isset($aPublicUrl['host']))
                        {
                            $sBaseAbsoluteUrl=$sPublicUrl;
                        }
                        $sBaseUrl=Yii::app()->getBaseUrl();
                        $sUploadUrl=Yii::app()->getConfig('uploadurl');
                        if (substr($sUploadUrl, 0, strlen($sBaseUrl)) == $sBaseUrl) {
                            $sUploadUrl = substr($sUploadUrl, strlen($sBaseUrl));
                        }
                        $_SESSION['KCFINDER']['uploadURL'] = trim($sBaseAbsoluteUrl,"/").$sUploadUrl."/surveys/{$surveyid}/";
                    }
                    else
                    {
                        $_SESSION['KCFINDER']['uploadURL'] = Yii::app()->getConfig('uploadurl')."/surveys/{$surveyid}/";
                    }
                    $_SESSION['KCFINDER']['uploadDir'] = Yii::app()->getConfig('uploaddir') .DIRECTORY_SEPARATOR.'surveys'.DIRECTORY_SEPARATOR.$surveyid.DIRECTORY_SEPARATOR;
                }
            }
            elseif (preg_match('/^edit:label/', Yii::app()->session['FileManagerContext']) != 0)
            {
                $contextarray = explode(':', Yii::app()->session['FileManagerContext'], 3);
                $labelid = $contextarray[2];
                // check if the user has label management right and labelid defined
                if (Permission::model()->hasGlobalPermission('labelsets','update') && isset($labelid) && $labelid != '')
                {
                    $_SESSION['KCFINDER']['disabled'] = false;
                    $_SESSION['KCFINDER']['uploadURL'] = Yii::app()->getConfig('uploadurl')."/labels/{$labelid}/";
                    $_SESSION['KCFINDER']['uploadDir'] = Yii::app()->getConfig('uploaddir') .DIRECTORY_SEPARATOR.'labels'.DIRECTORY_SEPARATOR.$labelid.DIRECTORY_SEPARATOR;
                }
            }
        }
    } 

    function sTranslateLangCode2CK($sLanguageCode){
        $aTranslationTable=array(
        'ca-valencia'=>'ca',
        'de-informal'=>'de',
        'es-AR-informal'=>'es',
        'es-AR'=>'es',
        'es-CL'=>'es',
        'es-MX'=>'es',
        'it-informal'=>'it',
        'nl-informal'=>'nl',
        'nn'=>'no',
        'zh-Hans'=>'zh-cn',
        'zh-Hant-HK'=>'zh',
        'zh-Hant-TW'=>'zh'
        );
        if (isset($aTranslationTable[$sLanguageCode])) {
            $sResultCode=$aTranslationTable[$sLanguageCode];
        }
        else
        {
            $sResultCode=strtolower($sLanguageCode);
        }
        return $sResultCode;
    }


    function PrepareEditorScript($load=false, $controller = null)
    {
        $clang = Yii::app()->lang;
        $data['clang'] = $clang;

        App()->getClientScript()->registerCoreScript('ckeditor');
        if ($controller == null)
        {
            $controller = Yii::app()->getController();
        }

        if ($load == false)
        {

            return $controller->renderPartial('/admin/survey/prepareEditorScript_view',$data,true);
        }
        else
        {
            $controller->renderPartial('/admin/survey/prepareEditorScript_view',$data);
        }
    }

    function getEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null,$action=null)
    {
        initKcfinder();
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
        $fieldtype == 'email-admin-notification' ||
        $fieldtype == 'email-admin-resp' ||
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
        . "<a href=\"javascript:start_popup_editor('".$fieldname."','".addslashes(htmlspecialchars_decode($fieldtext,ENT_QUOTES))."','".$surveyID."','".$gID."','".$qID."','".$fieldtype."','".$action."')\" id='".$fieldname."_ctrl' class='editorLink'>\n"
        . "\t<img alt=\"".$clang->gT("Start HTML editor in a popup window")."\" id='".$fieldname."_popupctrlena' src='".Yii::app()->getConfig('adminimageurl')."edithtmlpopup.png' $imgopts class='btneditanswerena' />\n"
        . "\t<img alt=\"".$clang->gT("Give focus to the HTML editor popup window")."\" id='".$fieldname."_popupctrldis' src='".Yii::app()->getConfig('adminimageurl')."edithtmlpopup_disabled.png' style='display:none' $imgopts class='btneditanswerdis' />\n"
        . "</a>\n";

        return $htmlcode;
    }

    function getInlineEditor($fieldtype,$fieldname,$fieldtext, $surveyID=null,$gID=null,$qID=null,$action=null)
    {                         
        $htmlcode = '';
        $imgopts = '';
        $toolbarname = 'inline';
        $toolbaroption="";
        $sFileBrowserAvailable='';
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

        if ($surveyID=='')
        {
            $sFakeBrowserURL=Yii::app()->getController()->createUrl('admin/survey/sa/fakebrowser');
            $sFileBrowserAvailable=",filebrowserBrowseUrl:'{$sFakeBrowserURL}'
            ,filebrowserImageBrowseUrl:'{$sFakeBrowserURL}'
            ,filebrowserFlashBrowseUrl:'{$sFakeBrowserURL}'
            ,filebrowserUploadUrl:'{$sFakeBrowserURL}'
            ,filebrowserImageUploadUrl:'{$sFakeBrowserURL}'
            ,filebrowserFlashUploadUrl:'{$sFakeBrowserURL}'";
        }
        
        $htmlcode .= ""
        . "<script type=\"text/javascript\">\n"
        . "$(document).ready(
        function(){ var $oCKeditorVarName = CKEDITOR.replace('$fieldname', {
        customConfig : \"".Yii::app()->getConfig('adminscripts')."ckeditor-config.js\"
        ,LimeReplacementFieldsType : \"".$fieldtype."\"
        ,LimeReplacementFieldsSID : \"".$surveyID."\"
        ,LimeReplacementFieldsGID : \"".$gID."\"
        ,LimeReplacementFieldsQID : \"".$qID."\"
        ,LimeReplacementFieldsType : \"".$fieldtype."\"
        ,LimeReplacementFieldsAction : \"".$action."\"
        ,LimeReplacementFieldsPath : \"".Yii::app()->getController()->createUrl("admin/limereplacementfields/sa/index/")."\"
        ,width:'660'
        ,language:'".sTranslateLangCode2CK(Yii::app()->session['adminlang'])."'
        ,smiley_path : \"".Yii::app()->getConfig('uploadurl')."/images/smiley/msn/\"\n"
        . $sFileBrowserAvailable
        . $htmlformatoption
        . $toolbaroption
        ."});
        \$('#$fieldname').parents('ul:eq(0)').addClass('editor-parent');
        });";
        

        $htmlcode.= '</script>';

        return $htmlcode;
    }

?>
