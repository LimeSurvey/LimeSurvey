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
class User_in_groups extends CActiveRecord {

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
		return '{{user_in_groups}}';
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'uid';
	}

	/**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'Users' => array(self::HAS_MANY, 'User','uid')
        );
    }
	
	/*function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('user_in_groups');
		
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
		
		$data = $this->db->get('user_in_groups');
		
		return $data;
	}
	
	function insert($data)
	{
		return (bool) $this->db->insert('user_in_groups', $data);
	}
	
	function join($fields, $from, $condition=FALSE, $join=FALSE, $order=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		
		$this->db->from($from);
		
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}

		if ($order != FALSE)
		{
			$this->db->order_by($order);	
		}
		
		if (isset($join['where'], $join['type'], $join['on']))
		{
			$this->db->join($condition);	
		}
		
		$data = $this->db->get();
		return $data;
	}*/

}
