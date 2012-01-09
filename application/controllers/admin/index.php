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
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */
class Index extends Survey_Common_Action
{
	public function run()
	{
        $clang = Yii::app()->lang;
        $aViewUrls = array();

		if (count(getsurveylist(true)) == 0)
		{
            $aViewUrls = 'firststeps';
		}
        elseif (Yii::app()->session['just_logged_in'])
        {
            $aViewUrls = array('message' => array(
                'title' => $clang->gT("Logged in"),
                'message' => Yii::app()->session['loginsummary']
            ));
            unset(Yii::app()->session['just_logged_in'], Yii::app()->session['loginsummary']);
        }

        $this->_renderWrappedTemplate('super', $aViewUrls);

	}

}
