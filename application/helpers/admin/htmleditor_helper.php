<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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

    $sAllowedExtensions = getAllowedExtensions();
    $_SESSION['KCFINDER']['types'] = array(
        'files' => $sAllowedExtensions,
        'flash' => $sAllowedExtensions,
        'images' => $sAllowedExtensions
    );
    if (!empty(App()->getSession()->cookieParams['domain'])) {
        $_SESSION['KCFINDER']['cookieDomain'] = App()->getSession()->cookieParams['domain'];
    }
    if (App()->getRequest()->enableCsrfValidation && !empty(App()->getRequest()->csrfCookie['domain'])) {
        $_SESSION['KCFINDER']['cookieDomain'] = Yii::app()->getRequest()->csrfCookie['domain'];
    }

    if (
        Yii::app()->getConfig('demoMode') === false &&
            isset(Yii::app()->session['loginID']) &&
            isset(Yii::app()->session['FileManagerContext'])
    ) {
        // disable upload at survey creation time
        // because we don't know the sid yet
        if (
            preg_match('/^(create|edit):(question|group|answer)/', (string) Yii::app()->session['FileManagerContext']) != 0 ||
                preg_match('/^edit:survey/', (string) Yii::app()->session['FileManagerContext']) != 0 ||
                preg_match('/^edit:assessments/', (string) Yii::app()->session['FileManagerContext']) != 0 ||
                preg_match('/^edit:emailsettings/', (string) Yii::app()->session['FileManagerContext']) != 0
        ) {
            $contextarray = explode(':', (string) Yii::app()->session['FileManagerContext'], 3);
            $surveyid = $contextarray[2];

            if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
                $surveyid = convertSurveyIdWhenUniqUploadDir($surveyid);

                $_SESSION['KCFINDER']['disabled'] = false;
                if (preg_match('/^edit:emailsettings/', (string) $_SESSION['FileManagerContext']) != 0) {
                    // Uploadurl use public url or getBaseUrl(true);
                    // Maybe need external function
                    $sBaseAbsoluteUrl = Yii::app()->getBaseUrl(true);
                    $sPublicUrl = Yii::app()->getConfig("publicurl");
                    $aPublicUrl = parse_url((string) $sPublicUrl);
                    if (isset($aPublicUrl['scheme']) && isset($aPublicUrl['host'])) {
                        $sBaseAbsoluteUrl = $sPublicUrl;
                    }
                    $sBaseUrl = Yii::app()->getBaseUrl();
                    $sUploadUrl = Yii::app()->getConfig('uploadurl');
                    if (substr((string) $sUploadUrl, 0, strlen((string) $sBaseUrl)) == $sBaseUrl) {
                        $sUploadUrl = substr((string) $sUploadUrl, strlen((string) $sBaseUrl));
                    }
                    $_SESSION['KCFINDER']['uploadURL'] = trim((string) $sBaseAbsoluteUrl, "/") . $sUploadUrl . "/surveys/{$surveyid}/";
                } else {
                    $_SESSION['KCFINDER']['uploadURL'] = Yii::app()->getConfig('uploadurl') . "/surveys/{$surveyid}/";
                }

                $_SESSION['KCFINDER']['uploadDir'] = getSurveyUploadDir($surveyid);
            }
        } elseif (preg_match('/^edit:label/', (string) Yii::app()->session['FileManagerContext']) != 0) {
            $contextarray = explode(':', (string) Yii::app()->session['FileManagerContext'], 3);
            $labelid = $contextarray[2];
            // check if the user has label management right and labelid defined
            if (Permission::model()->hasGlobalPermission('labelsets', 'update') && isset($labelid) && $labelid != '') {
                $_SESSION['KCFINDER']['disabled'] = false;
                $_SESSION['KCFINDER']['uploadURL'] = Yii::app()->getConfig('uploadurl') . "/labels/{$labelid}/";
                $_SESSION['KCFINDER']['uploadDir'] = realpath(Yii::app()->getConfig('uploaddir')) . DIRECTORY_SEPARATOR . 'labels' . DIRECTORY_SEPARATOR . $labelid . DIRECTORY_SEPARATOR;
            }
        }
    }
}

function sTranslateLangCode2CK($sLanguageCode)
{
    $aTranslationTable = array(
    'ca-valencia' => 'ca',
    'de-informal' => 'de',
    'de-easy' => 'de',
    'es-AR-informal' => 'es',
    'es-AR' => 'es',
    'es-CL' => 'es',
    'es-MX' => 'es',
    'it-informal' => 'it',
    'nl-informal' => 'nl',
    'nn' => 'no',
    'zh-Hans' => 'zh-cn',
    'zh-Hant-HK' => 'zh',
    'zh-Hant-TW' => 'zh'
    );
    if (isset($aTranslationTable[$sLanguageCode])) {
        $sResultCode = $aTranslationTable[$sLanguageCode];
    } else {
        $sResultCode = strtolower((string) $sLanguageCode);
    }
    return $sResultCode;
}


    /**
     * @param CController $controller
     *
     * TODO: Allow to be called automatically (and only load once) from getEditor, or from a widget.
     */
function PrepareEditorScript($load = false, $controller = null)
{
    if ($controller == null) {
        $controller = Yii::app()->getController();
    }
    if ($load == false) {
        return $controller->renderPartial('/admin/survey/prepareEditorScript_view', array(), true);
    } else {
        $controller->renderPartial('/admin/survey/prepareEditorScript_view', array());
    }
}

    /**
     * Returns Editor.
     *
     * PrepareEditorScript function must be called first.
     * If getting a JS error about missing CKEditor files, please review if PrepareEditorScript was called.
     *
     * @param string   $fieldtype Field Type
     * @param string   $fieldname Field Name, the id attribute of the textarea
     * @param int|null $surveyID  Survey ID
     * @param int|null $gID       Group ID
     * @param int|null $qID       Question ID
     * @param string   $action    Action
     * @return string
     */
function getEditor($fieldtype, $fieldname, $fieldtext, $surveyID = null, $gID = null, $qID = null, $action = null)
{
    $surveyID = convertSurveyIdWhenUniqUploadDir($surveyID);

    initKcfinder();

    $session = &Yii::app()->session;

    if ($session['htmleditormode'] && $session['htmleditormode'] == 'none') {
        return '';
    }


    if (!$session['htmleditormode'] || ($session['htmleditormode'] != 'inline' && $session['htmleditormode'] != 'popup')) {
        $htmleditormode = Yii::app()->getConfig('defaulthtmleditormode');
    } else {
        $htmleditormode = $session['htmleditormode'];
    }
    if ($surveyID && getEmailFormat($surveyID) != 'html' && (substr($fieldtype, 0, 6) === "email_" || substr($fieldtype, 0, 6) === "email-" )) {
        // email but survey as text email
        return '';
    }

    if ($htmleditormode == 'popup' || ($fieldtype == 'editanswer' || $fieldtype == 'addanswer' || $fieldtype == 'editlabel' || $fieldtype == 'addlabel') && (preg_match("/^translate/", $action) == 0)) {
        return getPopupEditor($fieldtype, $fieldname, $fieldtext, $surveyID, $gID, $qID, $action);
    } elseif ($htmleditormode == 'inline') {
        return getInlineEditor($fieldtype, $fieldname, $fieldtext, $surveyID, $gID, $qID, $action);
    } elseif ($htmleditormode == 'modal') {
        return getModalEditor($fieldtype, $fieldname, $fieldtext, $surveyID, $gID, $qID, $action);
    } else {
        return '';
    }
}

/**
 * @param $fieldtype
 * @param $fieldname
 * @param $fieldtext
 * @param $surveyID
 * @param $gID
 * @param $qID
 * @param $action
 * @return string
 */
function getPopupEditor($fieldtype, $fieldname, $fieldtext, $surveyID = null, $gID = null, $qID = null, $action = null)
{
    $surveyID = convertSurveyIdWhenUniqUploadDir($surveyID);

    $htmlcode = '';

    if (
        $fieldtype == 'editanswer' ||
        $fieldtype == 'addanswer' ||
        $fieldtype == 'editlabel' ||
        $fieldtype == 'addlabel'
    ) {
        $class = "editorLink";
    } else {
        $class = "editorLink";
    }
    /* @var string[] parameters of the editor url */
    $editorUrlParams = array(
        'name' => $fieldname,
        'text' => javascriptEscape($fieldtext), // usage for title of the new window
        'type' => $fieldtype, // email_XX_lang, question_lang …
    );
    if (!empty($action)) {
        $editorUrlParams['action'] = javascriptEscape($action);
    }
    if (!empty($surveyID)) {
        $editorUrlParams['sid'] = $surveyID;
    }
    if (!empty($gID)) {
        $editorUrlParams['gid'] = $gID;
    }
    if (!empty($qID)) {
        $editorUrlParams['qid'] = $qID;
    }
    $editorUrlParams['lang'] = App()->language;
    $editorUrlParams['contdir'] = getLanguageRTL(App()->language) ? 'rtl' : 'ltr';
    /* @var string the editor url */
    $editorUrl = App()->getController()->createUrl(
        'admin/htmleditorpop/sa/index',
        $editorUrlParams
    );
    /* @var string content of the action link */
    $content = CHtml::tag('i', array(
        'class' => "ri-pencil-fill btneditanswerena",
        'id' => $fieldname . "_popupctrlena",
    ), '')
    . CHtml::tag('span', array(
        'class' => "sr-only",
    ), gT("Start HTML editor in a popup window"))
    . CHtml::tag('i', array(
        'class' => "ri-pencil-fill btneditanswerdis",
        'id' => $fieldname . "_popupctrldis",
        'style' => "display:none",
    ), '');
    /* @var final code to return */
    return CHtml::link(
        $content,
        "javascript:start_popup_editor('{$fieldname}','" . $editorUrl . "');",
        array(
            'id' => $fieldname . "_ctrl",
            'class' => "{$class} btn btn-outline-secondary",
            'title' => gT("Start HTML editor in a popup window"),
            'data-bs-toggle' => "tooltip",
            'data-bs-placement' => "bottom",
        )
    );
}

/**
 * @param $fieldtype
 * @param $fieldname
 * @param $fieldtext
 * @param $surveyID
 * @param $gID
 * @param $qID
 * @param $action
 * @return string
 */
function getModalEditor($fieldtype, $fieldname, $fieldtext, $surveyID = null, $gID = null, $qID = null, $action = null)
{
    $surveyID = convertSurveyIdWhenUniqUploadDir($surveyID);

    $htmlcode = "<a href='#' class='btn btn-sm btn-outline-secondary htmleditor--openmodal' data-target-field-id='$fieldname' data-modal-title='$fieldtext' data-bs-toggle='tooltip' data-bs-original-title='" . gT("Open editor") . "'>\n" .
                "\t<i class='ri-pencil-fill' id='{$fieldname}_modal_icon'></i>\n" .
                "</a>\n";

    return $htmlcode;
}

function getInlineEditor($fieldtype, $fieldname, $fieldtext, $surveyID = null, $gID = null, $qID = null, $action = null)
{
    $surveyID = convertSurveyIdWhenUniqUploadDir($surveyID);
    $htmlcode = '';
    $toolbaroption = "";
    $sFileBrowserAvailable = '';
    $htmlformatoption = "";

    // Compose JS variable name that will hold the CKEditor instance.
    // This variable is named after the fieldname.
    // As to sanitize it and not creating JS syntax errors:
    // - replace any opening square brackets or "-" from the fieldname, to "_"
    // - remove closing square brackets
    //
    // Note: This sanitization process is not much needed now, but leave it in case is usefull for laters.
    // This was used before by this function, when in prior times, fieldname could be derived
    // from the name of a textarea, and not just the id (as now)
    // The name of a texarea can contain quare brackets. Then we needed to sanitize.
    $oCKeditorVarName = "oCKeditor_" . preg_replace("/[-\[]/", "_", (string) $fieldname);
    $oCKeditorVarName = str_replace(']', '', $oCKeditorVarName);

    if (
        ($fieldtype == 'editanswer' ||
        $fieldtype == 'addanswer' ||
        $fieldtype == 'editlabel' ||
        $fieldtype == 'addlabel') && (preg_match("/^translate/", (string) $action) == 0)
    ) {
        $toolbaroption = ",toolbarStartupExpanded:true\n"
        . ",toolbar:'popup'\n"
        . ",toolbarCanCollapse:false\n";
    } else {
        $ckeditexpandtoolbar = Yii::app()->getConfig('ckeditexpandtoolbar');
        if (!isset($ckeditexpandtoolbar) || $ckeditexpandtoolbar == true) {
            $toolbaroption = ",toolbarStartupExpanded:true\n"
            . ",toolbar:'inline2'\n"
            . ",basicToolbar:'inline2'\n"
            . ",fullToolbar:'inline'\n";
        }
    }

    /* fieldtype have language at end , set fullpage for email HTML edit */
    if (substr((string) $fieldtype, 0, 6) === 'email-') {
        $htmlformatoption = ",fullPage:true\n";
        //~ $htmlformatoption = ",allowedContent:true\n"; // seems uneeded
    }
    if ($surveyID == '') {
        $sFakeBrowserURL = Yii::app()->getController()->createUrl('admin/survey/sa/fakebrowser');
        $sFileBrowserAvailable = ",filebrowserBrowseUrl:'{$sFakeBrowserURL}'
            ,filebrowserImageBrowseUrl:'{$sFakeBrowserURL}'
            ,filebrowserUploadUrl:'{$sFakeBrowserURL}'
            ,filebrowserImageUploadUrl:'{$sFakeBrowserURL}'";
    }
    /* @var string[] parameters of the replacementfields url */
    $replacementFieldsUrlParams = array(
        'fieldtype' => $fieldtype, // email_XX_lang, question_lang …
    );
    if (!empty($action)) {
        $replacementFieldsUrlParams['action'] = javascriptEscape($action);
    }
    if (!empty($surveyID)) {
        $replacementFieldsUrlParams['surveyid'] = $surveyID;
    }
    if (!empty($gID)) {
        $replacementFieldsUrlParams['gid'] = $gID;
    }
    if (!empty($qID)) {
        $replacementFieldsUrlParams['qid'] = $qID;
    }
    /* @var string the replacementfields url */
    $replacementFieldsUrl = App()->getController()->createUrl(
        'limereplacementfields/index',
        $replacementFieldsUrlParams
    );
    $loaderHTML = getLoaderHTML($fieldname);

    $scriptCode = ""
    . "
            if($('#" . $fieldname . "').length >0){
                // NB: Can't use `var` if oCKeditorVarName includes [].
                $oCKeditorVarName = CKEDITOR.instances['$fieldname'];
                if ($oCKeditorVarName) {
                        CKEDITOR.remove($oCKeditorVarName);
                    $oCKeditorVarName = null;
                }

                $('#" . $fieldname . "').before('$loaderHTML');

                var ckeConfig = {
                    LimeReplacementFieldsUrl : \"" . $replacementFieldsUrl . "\"
                    ,language:'" . sTranslateLangCode2CK(Yii::app()->session['adminlang']) . "'"
                . $sFileBrowserAvailable
                . $htmlformatoption
                . $toolbaroption
                . "};

                // Override language direction if 'data-contents-dir' attribute is set in the target field
                if ($('#" . $fieldname . "').get(0).hasAttribute('data-contents-dir')) {
                    var inputLangDirection = $('#" . $fieldname . "').attr('data-contents-dir');
                    ckeConfig.contentsLangDirection = inputLangDirection ? inputLangDirection : '';
                }

                // Set the placeholder text
                if ($('#" . $fieldname . "').attr('placeholder')) {
                    ckeConfig.editorplaceholder = $('#" . $fieldname . "').attr('placeholder');
                }

                // Show full toolbar if cookie is set
                var toolbarCookie = CKEDITOR.tools.getCookie('LS_CKE_TOOLBAR');
                if (toolbarCookie == 'full' && ckeConfig.toolbar == ckeConfig.basicToolbar) {
                    ckeConfig.toolbar = ckeConfig.fullToolbar;
                }

                $oCKeditorVarName = CKEDITOR.replace('$fieldname', ckeConfig);

                \$('#$fieldname').parents('ul:eq(0)').addClass('editor-parent');
            }";

    Yii::app()->getClientScript()->registerScript('ckEditorScriptsInline-' . $fieldname, $scriptCode, LSYii_ClientScript::POS_POSTSCRIPT);
}

function getLoaderHTML($fieldname)
{
    $loaderHTML  = '  <div  id="' . $fieldname . '_htmleditor_loader" class="ls-flex ls-flex-column align-items-center align-content-center" style="height: 200px;">';
    $loaderHTML .= '    <div class="loader--loaderWidget ls-flex ls-flex-column align-content-center align-items-center" style="min-height: 100%;">';
    $loaderHTML .= '      <div class="ls-flex align-content-center align-items-center">';
    $loaderHTML .= '        <div class="loader-adminpanel text-center" :class="extraClass">';
    $loaderHTML .= '          <div class="contain-pulse animate-pulse">';
    $loaderHTML .= '              <div class="square"></div>';
    $loaderHTML .= '              <div class="square"></div>';
    $loaderHTML .= '              <div class="square"></div>';
    $loaderHTML .= '              <div class="square"></div>';
    $loaderHTML .= '          </div>';
    $loaderHTML .= '        </div>';
    $loaderHTML .= '      </div>';
    $loaderHTML .= '    </div>';
    $loaderHTML .= '  </div>';
    return $loaderHTML;
}

function getSurveyUploadDir($surveyId) {
    $surveyId = convertSurveyIdWhenUniqUploadDir($surveyId);
    return realpath(Yii::app()->getConfig('uploaddir')) . DIRECTORY_SEPARATOR . 'surveys' . DIRECTORY_SEPARATOR . $surveyId . DIRECTORY_SEPARATOR;
}

/**
 * If config param "uniq_upload_dir" is set to true, convert survey ID to 'uniq'
 * @param $surveyId
 * @return mixed|string
 */
function convertSurveyIdWhenUniqUploadDir($surveyId) {
    if (App()->getConfig('uniq_upload_dir') && !empty($surveyId)) {
        $surveyId = 'uniq';
    }
    return $surveyId;
}

function getAllowedExtensions() {
    return implode(' ', array_map('trim', explode(',', (string) Yii::app()->getConfig('allowedresourcesuploads'))));
}