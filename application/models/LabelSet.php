<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');
/*
 * LimeSurvey (tm)
 * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *
 */

class LabelSet extends LSActiveRecord
{
	/**
	 * Returns the table's name
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{labelsets}}';
	}

	/**
	 * Returns the table's primary key
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'lid';
	}

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
    * Returns this model's validation rules
    *
    */
    public function rules()
    {
        return array(
            array('label_name','required'),
            array('label_name','length', 'min' => 1, 'max'=>100),
            array('label_name','LSYii_Validators'),
            array('languages','required'),
            array('languages','LSYii_Validators','isLanguageMulti'=>true),
        );
    }

	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
        {
		    foreach ($condition as $item => $value)
			{
				$criteria->addCondition($item.'="'.$value.'"');
			}
        }

		$data = $this->findAll($criteria);

        return $data;
	}

    function getLID()
    {
		return Yii::app()->db->createCommand()->select('lid')->order('lid asc')->from('{{labelsets}}')->query()->readAll();
    }

	function insertRecords($data)
    {
        $lblset = new self;
		foreach ($data as $k => $v)
			$lblset->$k = $v;
		if ($lblset->save())
        {
            return $lblset->lid;
        }
        return false;
    }
}
