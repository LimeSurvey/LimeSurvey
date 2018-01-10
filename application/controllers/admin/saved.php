<?php if (!defined('BASEPATH')) {
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

/**
 * Saved controller
 *
 * @package LimeSurvey
 * @copyright 2011
 * @access public
 */
class saved extends Survey_Common_Action
{

    public function view($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $aViewUrls = array();

        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'read')) {
            die();
        }

        $aThisSurvey = getSurveyInfo($iSurveyId);
        $aData['sSurveyName'] = $aThisSurvey['name'];
        $aData['iSurveyId'] = $iSurveyId;
        $aViewUrls[] = 'savedbar_view';
        $aViewUrls['savedlist_view'][] = $this->_showSavedList($iSurveyId);

        // saved.js bugs if table is empty
        if (count($aViewUrls['savedlist_view'][0]['aResults'])) {
            App()->getClientScript()->registerPackage('jquery-tablesorter');
            App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'saved.js');
        }


        $this->_renderWrappedTemplate('saved', $aViewUrls, $aData);
    }

    /**
     * Function responsible to delete saved responses.
     */
    public function delete($iSurveyId, $iSurveyResponseId, $iSavedControlId)
    {
        $survey = Survey::model()->findByPk($iSurveyId);
        SavedControl::model()->deleteAllByAttributes(array('scid' => $iSavedControlId, 'sid' => $iSurveyId)) or die(gT("Couldn't delete"));
        Yii::app()->db->createCommand()->delete($survey->responsesTableName, 'id=:id', array('id' => $iSurveyResponseId)) or die(gT("Couldn't delete"));

        $this->getController()->redirect(array("admin/saved/sa/view/surveyid/{$iSurveyId}"));
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string[] $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'saved', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        $aData['display']['menu_bars']['browse'] = gT('Browse responses'); // browse is independent of the above
        $aData['surveyid'] = $iSurveyId = $aData['iSurveyId'];
        $oSurvey = Survey::model()->findByPk($aData['iSurveyId']);

        $aData['title_bar']['title'] = gT('Browse responses').': '.$oSurvey->currentLanguageSettings->surveyls_title;
        $aData['menu']['close'] = true;
        $aData['menu']['edition'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

    /**
     * Load saved list.
     * @param mixed $iSurveyId Survey id
     * @return array
     */
    private function _showSavedList($iSurveyId)
    {
        $aResults = SavedControl::model()->findAll(array(
            'select' => array('scid', 'srid', 'identifier', 'ip', 'saved_date', 'email', 'access_code'),
            'condition' => 'sid=:sid',
            'order' => 'saved_date desc',
            'params' => array(':sid' => $iSurveyId),
        ));

        if (!empty($aResults)) {
            return compact('aResults');
        } else
        {return array('aResults'=>array()); }
    }

}
