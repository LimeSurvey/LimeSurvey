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
class User_groups extends CActiveRecord {

	protected $connection;

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
		return '{{user_groups}}';
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'ugid';
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

	function getAllRecords($condition=FALSE)
	{
		$this->connection = Yii::app()->db;
		if ($condition != FALSE)
		{
			$where_clause = array("WHERE");

			foreach($condition as $key=>$val)
			{
				$where_clause[] = $key.'=\''.$val.'\'';
			}

			$where_string = implode(' AND ', $where_clause);
		}

		$query = 'SELECT * FROM '.$this->tableName().' '.$where_string;

		$data = createCommand($query)->query()->resultAll();

		return $data;
	}

	function getSomeRecords($fields,$condition=FALSE, $params=NULL)
	{
		$filter = new CDbCriteria;
		$filter->select = $fields;

		if ($condition != FALSE)
		{
			$filter->condition = $condition;
			$filter->params = $params;
		}

		$data = $this->findAll($filter);

		return $data;
	}

    function insertRecords($data)
    {

        return $this->db->insert('user_groups',$data);
    }

	function join($fields, $from, $condition=FALSE, $join=FALSE, $order=FALSE)
	{
	    $user = Yii::app()->db->createCommand();
		foreach ($fields as $field)
		{
			$user->select($field);
		}

		$user->from($from);

		if ($condition != FALSE)
		{
			$user->where($condition);
		}

		if ($order != FALSE)
		{
			$user->order($order);
		}

		if (isset($join['where'], $join['on']))
		{
		    if (isset($join['left'])) {
			    $user->leftjoin($join['where'], $join['on']);
			}else
			{
			    $user->join($join['where'], $join['on']);
			}
		}

		$data = $user->queryRow();
		return $data;
	}

 	function addGroup($group_name, $group_description) {
	    $connect= Yii::app()->db;
	    $iquery = "INSERT INTO ".Yii::app()->db->tablePrefix."user_groups (`name`, `description`, `owner_id`) VALUES('{$group_name}', '{$group_description}', '{$_SESSION['loginID']}')";
	    $command = $connect->createCommand($iquery);
	    $result = $command->query();
	    if($result) { //Checked
	    	$id = $connect->getLastInsertID(); //$connect->Insert_Id(db_table_name_nq('user_groups'),'ugid');
	        if($id > 0) {
	           	$user_in_groups_query = 'INSERT INTO '.Yii::app()->db->tablePrefix.'user_in_groups (ugid, uid) VALUES ('.$id.','.Yii::app()->session['loginID'].')';
	           	db_execute_assoc($user_in_groups_query);
	        }
	        return $id;
		}
	    else
	    	return -1;

    	}

	function updateGroup($name, $description, $ugid)
    {
    	$query = 'UPDATE '.Yii::app()->db->tablePrefix.'user_groups SET name=\''.$name.'\', description=\''.$description.'\' WHERE ugid=\''.$ugid.'\'';
       	$uquery = db_execute_assoc($query);
        return $uquery;
    }

	function requestEditGroup($ugid, $ownerid)
	{
		$query = 'SELECT * FROM '.Yii::app()->db->tablePrefix.'user_groups WHERE ugid='.$ugid.' AND owner_id='.$ownerid;
        $result = db_execute_assoc($query);
		return $result;
	}

	function requestViewGroup($ugid, $userid)
	{
		$query = "SELECT a.ugid, a.name, a.owner_id, a.description, b.uid FROM ".Yii::app()->db->tablePrefix."user_groups AS a LEFT JOIN ".Yii::app()->db->tablePrefix."user_in_groups AS b ON a.ugid = b.ugid WHERE a.ugid = {$ugid} AND uid = ".$userid." ORDER BY name";
		//$select	= array('a.ugid', 'a.name', 'a.owner_id', 'a.description', 'b.uid');
		//$join	= array('where' => 'user_in_groups AS b', 'type' => 'left', 'on' => 'a.ugid = b.ugid');
		//$where	= array('uid' => $this->session->userdata('loginID'), 'a.ugid' => $ugid);
		return db_execute_assoc($query)->readAll();
	}

	function deleteGroup($ugid, $ownerid)
	{
		$del_query = 'DELETE FROM '.Yii::app()->db->tablePrefix.'user_groups WHERE owner_id=\''.$ownerid.'\' AND ugid='.$ugid;
        //$remquery = $this->user_groups_model->delete(array('owner_id' => $this->session->userdata('loginID'), 'ugid' => $ugid));
        return db_execute_assoc($del_query);
	}

	/*
	function multi_select($fields, $from, $condition=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}

		foreach ($from AS $f)
		{
			$this->db->from($f);
		}

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
	}

	function update($what, $where=FALSE)
	{
		if ($where != FALSE) $this->db->where($where);
		return (bool) $this->db->update('user_groups', $what);
	}

	function delete($condition)
	{
		return (bool) $this->db->delete('user_groups', $condition);
	}*/

}
