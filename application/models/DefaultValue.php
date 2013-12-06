<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
   * LimeSurvey
   * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
   * All rights reserved.
   * License: GNU/GPL License v2 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
     *	Files Purpose: lots of common functions
*/

class DefaultValue extends LSActiveRecord
{
	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
     * @param string $class
	 * @return CActiveRecord
	 */
	public static function model($class = __CLASS__)
	{
		return parent::model($class);
	}

	/**
	 * Returns the setting's table name to be used by the model
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{defaultvalues}}';
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return array
	 */
	public function primaryKey()
	{
		return array('qid', 'specialtype', 'scale_id', 'sqid', 'language');
	}

	/**
	 * Relations with questions
	 *
	 * @access public
	 * @return array
	 */
	public function relations()
	{
		$alias = $this->getTableAlias();
		return array(
			'question' => array(self::HAS_ONE, 'Question', '',
						'on' => "$alias.qid = question.qid",
			),
		);
	}

	function insertRecords($data)
    {
        $values = new self;
		foreach ($data as $k => $v)
			$values->$k = $v;
		return $values->save();
    }
}
?>
