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

        $aData['sFieldName'] = $sFieldName = sanitize_paranoid_string(App()->request->getQuery('name'));// The fieldname : an input name
        $aData['sFieldText'] = $sFieldText = CHtml::encode(App()->request->getQuery('text')); // Not text : is description of the window
        $aData['sFieldType'] = $sFieldType = sanitize_paranoid_string(App()->request->getQuery('type')); // Type of field : welcome email_invite question ....
        $aData['sAction'] = $sAction = sanitize_paranoid_string(App()->request->getQuery('action'));
        $aData['iSurveyId'] = $iSurveyId = sanitize_int(App()->request->getQuery('sid',0));
        $aData['iGroupId'] = $iGroupId = sanitize_int(App()->request->getQuery('gid',0));
        $aData['iQuestionId'] = $iQuestionId = sanitize_int(App()->request->getQuery('qid',0));
        $sLanguage = sanitize_paranoid_string(App()->request->getQuery('lang')); // Not used : take the content with input name


        //~ if (get_magic_quotes_gpc())
            //~ $aData['sFieldText'] = $sFieldText = stripslashes($sFieldText);
        //~ else
            //~ $aData['sFieldText'] = $sFieldText;

        if (!$sFieldName || !$sFieldText)
        {
            $this->getController()->render('/admin/htmleditor/pop_nofields_view', $aData);
        }
        else
        {
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
