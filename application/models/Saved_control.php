<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');
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
class Saved_control extends CActiveRecord {
		/**
	 * Returns the table's name
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{saved_control}}';
	}

	/**
	 * Returns the table's primary key
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'sid';
	}

	/**
	 * Return the static model for this table
	 *
	 * @static
	 * @access public
	 * @return CActiveRecord
	 */
	public static function model()
	{
		return parent::model(__CLASS__);
	}
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('saved_control');
		
		return $data;
	}

	public static function getSomeRecords($condition=FALSE)
	{
		$record = new self;
		$criteria = new CDbCriteria;
		
		if($condition != FALSE)
		{
			foreach ($condition as $column=>$value)
			{
				$criteria->addCondition("$column='$value'");
			}
		}
		
		return $record->findAll($criteria);
	}
    
    public function getCountOfAll($sid)
    {
        $data = Yii::app()->db->createCommand("SELECT COUNT(*) AS countall FROM {{saved_control}} WHERE sid=$sid")->query();
        $row = $data->read();
        
        return $row['countall'];
    }
    
    function insertRecords($data)
    {
        return $this->db->insert('saved_control', $data); 
    }

}
