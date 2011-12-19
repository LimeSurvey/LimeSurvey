<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
   * $Id: survey.php 10433 2011-07-06 14:18:45Z dionet $
   *
*/

class SurveyController extends LSYii_Controller
{
	/**
	 * Routes all the actions to their respective places
	 *
	 * @access public
	 * @return array
	 */
	public function actions()
	{
		return array(
			'tcpdf_check' => 'application.controllers.tcpdf_check',
            'index' => 'application.controllers.surveyaction',
            'optin' => 'application.controllers.optin',
            'optout' => 'application.controllers.optout',
            'printanswers' => 'application.controllers.printanswers',
            'register' => 'application.controllers.register',
            'statistics_user' => 'application.controllers.statistics_user',
            'uploader' => 'application.controllers.uploader',
            'verification' => 'application.controllers.verification',
		);
	}
}
