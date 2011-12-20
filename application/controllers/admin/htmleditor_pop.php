<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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


class htmleditor_pop extends Survey_Common_Action
{

    function run($sa = 'index')
    {
        if ($sa == 'index')
			$this->route('index', array('fieldname', 'fieldtext', 'fieldtype', 'action', 'surveyid', 'gid', 'qid', 'lang'));
    }

    function index($sFieldName = 0, $sFieldText = 0, $sFieldType = 0, $sAction = 0, $iSurveyId = 0, $iGroupId = 0, $iQuestionId = 0, $sLanguage = 0)
    {
        $aData['clang'] = $this->getController()->lang;
        $aData['sFieldName'] = $sFieldName;
        if (get_magic_quotes_gpc())
            $aData['sFieldText'] = $sFieldText = stripslashes($sFieldText);

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