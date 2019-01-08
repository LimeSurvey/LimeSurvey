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

use \LimeSurvey\Helpers\questionHelper;

/**
* question
*
* @package LimeSurvey
* @author
* @copyright 2011
* @access public
*/
class questionedit extends Survey_Common_Action
{
    
    public function view($surveyid, $gid, $qid)
    {
        $aData = array();
        $aData['surveyid'] = $iSurveyID = (int) $surveyid;
        Yii::app()->getClientScript()->registerPackage('questioneditor');
        $oQuestion = Question::model()->findByAttributes(array('qid' => $qid, 'gid' => $gid, 'sid' => $surveyid));
        $qrrow = $oQuestion->attributes;

        $aData['gid'] = $gid;
        $aData['qid'] = $qid;
        $aData['sImageURL'] = Yii::app()->getConfig('adminimageurl');
        $aData['iIconSize'] = Yii::app()->getConfig('adminthemeiconsize');
        $aData['qrrow'] = $qrrow;
        $this->getController()->renderPartial('/admin/survey/Question/questionbar_view', $aData, true);
        $this->_renderWrappedTemplate('survey/Question2', 'view', $aData);
    }

    
    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'survey/Question2', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

}