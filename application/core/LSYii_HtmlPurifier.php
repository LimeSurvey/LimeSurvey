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

class LSYii_HtmlPurifier extends CHtmlPurifier
{

    /**
	 * Get the config object for the HTML Purifier instance.
	 * @return mixed the HTML Purifier instance config
	 */
	public function getConfig()
	{
        $purifier = $this->getPurifier();
		if($purifier!==null) return $purifier->config;
	}

}
