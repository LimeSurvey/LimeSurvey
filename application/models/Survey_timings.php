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
 * 	$Id: Survey_timings.php -1   $
 * 	Files Purpose: lots of common functions
 */

class Survey_timings extends CActiveRecord
{

    protected static $sid = 0;

	/**
	 * Returns the static model
	 *
	 * @static
	 * @access public
	 * @param int $surveyid
	 * @return CActiveRecord
	 */
	public static function model($sid = null)
	{
        if (!is_null($sid))
            self::sid($sid);

		return parent::model(__CLASS__);
	}

	/**
	 * Sets the survey ID for the next model
	 *
	 * @static
	 * @access public
	 * @param int $sid
	 * @return void
	 */
	public static function sid($sid)
	{
		self::$sid = (int) $sid;
	}

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{survey_' . self::sid . '_timings}}';
    }

}

?>
