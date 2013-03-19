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


class htmleditor_pop extends Survey_Common_Action
{

    function index()
    {
        Yii::app()->loadHelper('admin/htmleditor');
        $ckLanguage = sTranslateLangCode2CK(Yii::app()->session['adminlang']);

        $sFieldName = isset($_GET['name']) ? $_GET['name'] : 0;
        $sFieldText = isset($_GET['text']) ? $_GET['text'] : 0;
        $sFieldType = isset($_GET['type']) ? $_GET['type'] : 0;
        $sAction = isset($_GET['action']) ? $_GET['action'] : 0;
        $iSurveyId = isset($_GET['sid']) ? $_GET['sid'] : 0;
        $iGroupId = isset($_GET['gid']) ? $_GET['gid'] : 0;
        $iQuestionId = isset($_GET['qid']) ? $_GET['qid'] : 0;
        $sLanguage = isset($_GET['lang']) ? $_GET['lang'] : 0;
        $aData['clang'] = $this->getController()->lang;
        $aData['sFieldName'] = $sFieldName;
        if (get_magic_quotes_gpc())
            $aData['sFieldText'] = $sFieldText = stripslashes($sFieldText);
        else
            $aData['sFieldText'] = $sFieldText;

        if (!$sFieldName || !$sFieldText)
        {
            $this->getController()->render('/admin/htmleditor/pop_nofields_view', $aData);
        }
        else
        {
            $aData['sFieldType'] = $sFieldType = preg_replace("/[^_.a-zA-Z0-9-]/", "", $sFieldType);
            $aData['sAction'] = preg_replace("/[^_.a-zA-Z0-9-]/", "", $sAction);
            $aData['iSurveyId'] = sanitize_int($iSurveyId);
            $aData['iGroupId'] = sanitize_int($iGroupId);
            $aData['iQuestionId'] = sanitize_int($iQuestionId);
            $aData['sControlIdEna'] = $sFieldName . '_popupctrlena';
            $aData['sControlIdDis'] = $sFieldName . '_popupctrldis';
            $aData['ckLanguage'] = $ckLanguage;

            $aData['toolbarname'] = 'popup';
            $aData['htmlformatoption'] = '';

            if (in_array($sFieldType, array('email-inv', 'email-reg', 'email-conf', 'email-rem')))
            {
                $aData['htmlformatoption'] = ',fullPage:true';
            }

            $this->getController()->render('/admin/htmleditor/pop_editor_view', $aData);
        }

    }

}
