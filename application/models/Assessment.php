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
   *	$Id: common_helper.php 11335 2011-11-08 12:06:48Z c_schmitz $
   *	Files Purpose: lots of common functions
*/

class Assessment extends CActiveRecord
{
	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
	 * @return CActiveRecord
	 */
	public static function model()
	{
		return parent::model(__CLASS__);
	}

	/**
	 * Returns the setting's table name to be used by the model
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{assessments}}';
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return array('id', 'language');
	}
	
	public static function insertRecords($data)
    {
        $assessment = new self;

		foreach ($data as $k => $v)
			$assessment->$k = $v;
		$assessment->save();

        return $assessment;
    }

    public static function updateAssessment($id, $language, array $data)
    {
        $assessment = self::model()->findByAttributes(array('id' => $id, 'language' => $language));
        if (!is_null($assessment)) {
            foreach ($data as $k => $v)
                $assessment->$k = $v;
            $assessment->save();
        }
    }
}
?>
