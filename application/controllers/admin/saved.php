<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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
        $clang = $this->getController()->lang;
        $aViewUrls = array();

        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'read'))
        {
            die();
        }

        App()->getClientScript()->registerPackage('jquery-tablesorter');
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'saved.js');

        $aThisSurvey = getSurveyInfo($iSurveyId);
        $aData['sSurveyName'] = $aThisSurvey['name'];
        $aData['iSurveyId'] = $iSurveyId;
        $aViewUrls[] = 'savedbar_view';
        $aViewUrls['savedlist_view'][] = $this->_showSavedList($iSurveyId);

        $this->_renderWrappedTemplate('saved', $aViewUrls, $aData);
    }

    /**
     * Function responsible to delete saved responses.
     */
    public function delete($iSurveyId, $iSurveyResponseId, $iSavedControlId)
    {
        $clang = $this->getController()->lang;

        SavedControl::model()->deleteAllByAttributes(array('scid' => $iSavedControlId, 'sid' => $iSurveyId)) or die($clang->gT("Couldn't delete"));
        Yii::app()->db->createCommand()->delete("{{survey_".intval($iSurveyId)."}}", 'id=:id', array('id' => $iSurveyResponseId)) or die($clang->gT("Couldn't delete"));

        $this->getController()->redirect(array("admin/saved/sa/view/surveyid/{$iSurveyId}"));
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'saved', $aViewUrls = array(), $aData = array())
    {
        $aData['display']['menu_bars'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

    /**
     * Load saved list.
     * @param mixed $iSurveyId Survey id
     */
    private function _showSavedList($iSurveyId)
    {
        $clang = $this->getController()->lang;
        $aResults = SavedControl::model()->findAll(array(
            'select' => array('scid', 'srid', 'identifier', 'ip', 'saved_date', 'email', 'access_code'),
            'condition' => 'sid=:sid',
            'order' => 'saved_date desc',
            'params' => array(':sid' => $iSurveyId),
        ));

        if (!empty($aResults))
        {
            return compact('aResults');
        }
        else
        {return array('aResults'=>array());}
    }

}
