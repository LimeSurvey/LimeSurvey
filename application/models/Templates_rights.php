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
class Templates_rights extends CActiveRecord {
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
		return '{{templates_rights}}';
	}
	public function primaryKey()
	{
		return 'uid';
	}
	
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('templates_rights');
		
		return $data;
	}

	function getSomeRecords($fields,$condition=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('templates_rights');
		
		return $data;
	}
	
	function insert($values)
	{
		return (bool) $this->db->insert('templates_rights', $values);
	}
	
	function update($what, $where)
	{
		$this->db->where($where);
		return (bool) $this->db->insert('templates_rights', $what);
	}

	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
	 * @return CActiveRecord
	 */
	
	
    function insertRecords($data)
    {
    	$tablename = $this->tableName(); 
        //$ans = new self;
	//	//foreach ($data as $k => $v)
	//		$ans->$k = $v;
		//return $ans->save();
		return Yii::app()->db->createCommand()->insert('{{templates_rights}}', $data);
    }

}
