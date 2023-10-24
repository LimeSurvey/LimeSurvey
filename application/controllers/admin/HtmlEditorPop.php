<?php

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

class HtmlEditorPop extends SurveyCommonAction
{
    public function index()
    {
        Yii::app()->loadHelper('admin.htmleditor');
        $aData = array(
            'ckLanguage' => sTranslateLangCode2CK(Yii::app()->session['adminlang']),
            'sFieldName' => sanitize_xss_string(App()->request->getQuery('name')), // The fieldname : an input name
            'sFieldText' => sanitize_xss_string(App()->request->getQuery('text')), // Not text : is description of the window
            'sFieldType' => sanitize_xss_string(App()->request->getQuery('type')), // Type of field : welcome email_invite question ....
            'sAction' => sanitize_paranoid_string(App()->request->getQuery('action')),
            'iSurveyId' => sanitize_int(App()->request->getQuery('sid', 0)),
            'iGroupId' => sanitize_int(App()->request->getQuery('gid', 0)),
            'iQuestionId' => sanitize_int(App()->request->getQuery('qid', 0)),
        );
        if (!$aData['sFieldName']) {
            $this->getController()->render('/admin/htmleditor/pop_nofields_view', $aData);
        } else {
            $aData['sControlIdEna'] = $aData['sFieldName'] . '_popupctrlena';
            $aData['sControlIdDis'] = $aData['sFieldName'] . '_popupctrldis';
            $aData['toolbarname'] = 'popup';
            $aData['htmlformatoption'] = '';
            $contentsLangDirection = App()->request->getQuery('contdir');
            if (!in_array(strtolower((string) $contentsLangDirection), ['ltr', 'rtl'])) {
                $contentsLangDirection = getLanguageRTL(Yii::app()->session['adminlang']) ? 'rtl' : 'ltr';
            }
            $aData['contentsLangDirection'] = $contentsLangDirection;
            if (in_array($aData['sFieldType'], array('email-invitation', 'email-registration', 'email-confirmation', 'email-reminder'))) {
                $aData['htmlformatoption'] = ',fullPage:true';
            }

            $this->getController()->render('/admin/htmleditor/pop_editor_view', $aData);
        }
    }
}
