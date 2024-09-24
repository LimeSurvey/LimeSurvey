<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2019 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Saved controller
 *
 * @package LimeSurvey
 * @copyright 2011
 * @access public
 */
class Saved extends SurveyCommonAction
{
    /**
     * Show the list of save response
     * @param int $surveyid
     * @return void
     * @throws Exception
     */
    public function view($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $aViewUrls = array();

        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }

        $aThisSurvey = getSurveyInfo($iSurveyId);
        $oSavedControlModel = SavedControl::model();
        $oSavedControlModel->sid = $iSurveyId;

        // Filter state
        $aFilters = App()->request->getParam('SavedControl');
        if (!empty($aFilters)) {
            $oSavedControlModel->setAttributes($aFilters, false);
        }

        $aData['model'] = $oSavedControlModel;
        $aData['sSurveyName'] = $aThisSurvey['name'];
        $aData['iSurveyId'] = $iSurveyId;
        // Set page size
        if (App()->request->getPost('savedResponsesPageSize')) {
            App()->user->setState('savedResponsesPageSize', App()->request->getPost('savedResponsesPageSize'));
        }
        $aData['savedResponsesPageSize'] = App()->user->getState('savedResponsesPageSize', App()->params['defaultPageSize']);
        $aViewUrls[] = 'savedlist_view';
        $this->renderWrappedTemplate('saved', $aViewUrls, $aData);
    }

    /**
     * Unfinished function
     *
     * @todo write function
     * @param int $surveyid
     * @param int $id
     * @return void
     */
    public function resendAccesscode($surveyid, $id)
    {
    }


    /**
     * Function responsible to delete saved responses.
     * @param int $surveyid
     * @return void
     * @throws Exception
     */
    public function actionDelete($surveyid)
    {
        if (!Permission::model()->hasSurveyPermission($surveyid, 'responses', 'delete')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        if (!Yii::app()->getRequest()->isPostRequest) {
            throw new CHttpException(405, gT("Invalid action"));
        }
        Yii::import('application.helpers.admin.ajax_helper', true);

        $iScid = App()->getRequest()->getParam('scid');
        $oSavedControl = SavedControl::model()->find('scid = :scid', array(':scid' => $iScid));
        if (empty($oSavedControl)) {
            throw new CHttpException(401, gT("Saved response not found"));
        }
        if ($oSavedControl->delete()) {
            $oResponse = Response::model($surveyid)->findByPk($oSavedControl->srid);
            if ($oResponse) {
                $oResponse->delete();
            }
        } else {
            if (Yii::app()->getRequest()->isAjaxRequest) {
                ls\ajax\AjaxHelper::outputError(gT('Unable to delete saved response.'));
                Yii::app()->end();
            }
            Yii::app()->setFlashMessage(gT('Unable to delete saved response.'), 'danger');
            $this->getController()->redirect(array("admin/saved/sa/view/surveyid/{$surveyid}"));
        }
        if (Yii::app()->getRequest()->isAjaxRequest) {
            ls\ajax\AjaxHelper::outputSuccess(gT('Saved response deleted.'));
            Yii::app()->end();
        }
        Yii::app()->setFlashMessage(gT('Saved response deleted.'), 'success');
        $this->getController()->redirect(array("admin/saved/sa/view/surveyid/{$surveyid}"));
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function renderWrappedTemplate($sAction = 'saved', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        $aData['display']['menu_bars']['browse'] = gT('Browse responses'); // browse is independent of the above
        $aData['surveyid'] = $iSurveyId = $aData['iSurveyId'];
        $oSurvey = Survey::model()->findByPk($aData['iSurveyId']);

        $aData['title_bar']['title'] = gT('Browse responses') . ': ' . $oSurvey->currentLanguageSettings->surveyls_title;

        $topbarData = TopbarConfiguration::getResponsesTopbarData($aData['surveyid']);
        $aData['topbar']['middleButtons'] = Yii::app()->getController()->renderPartial(
            '/responses/partial/topbarBtns/leftSideButtons',
            $topbarData,
            true
        );

        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
