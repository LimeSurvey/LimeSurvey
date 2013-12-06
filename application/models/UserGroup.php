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
 */
class UserGroup extends LSActiveRecord {

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
        $iLoginID=intval(Yii::app()->session['loginID']);
	    $iquery = "INSERT INTO {{user_groups}} (name, description, owner_id) VALUES(:group_name, :group_desc, :loginID)";
	    $command = Yii::app()->db->createCommand($iquery)->bindParam(":group_name", $group_name, PDO::PARAM_STR)
                                                         ->bindParam(":group_desc", $group_description, PDO::PARAM_STR)
                                                         ->bindParam(":loginID", $iLoginID, PDO::PARAM_INT);
	    $result = $command->query();
	    if($result) { //Checked
	    	$id = getLastInsertID($this->tableName()); //Yii::app()->db->Insert_Id(db_table_name_nq('user_groups'),'ugid');
	        if($id > 0) {
	           	$user_in_groups_query = 'INSERT INTO {{user_in_groups}} (ugid, uid) VALUES (:ugid, :uid)';
	           	$command = Yii::app()->db->createCommand($user_in_groups_query)->bindParam(":ugid", $id, PDO::PARAM_INT)->bindParam(":uid", $iLoginID, PDO::PARAM_INT)->query();
	        }
	        return $id;
		}
	    else
	    	return -1;

    	}

	function updateGroup($name, $description, $ugid)
    {
		$group = UserGroup::model()->findByPk($ugid);
		$group->name=$name;
		$group->description=$description;
		$group->save();
		if ($group->getErrors())
			return false;
		else
			return true;
    }

	function requestEditGroup($ugid, $ownerid)
	{
		$criteria=new CDbCriteria;
		$criteria->select='*';
		$criteria->condition="ugid=:ugid";
        $aParams=array();
        if (!Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $criteria->condition.=" AND owner_id=:ownerid";
            $aParams[':ownerid']=$ownerid;
        }

        $aParams[':ugid']=$ugid;
		$criteria->params=$aParams;
		$result=UserGroup::model()->find($criteria);
		return $result;
	}

	function requestViewGroup($ugid, $userid)
	{
		$sQuery = "SELECT a.ugid, a.name, a.owner_id, a.description, b.uid FROM {{user_groups}} AS a LEFT JOIN {{user_in_groups}} AS b ON a.ugid = b.ugid WHERE a.ugid = :ugid";
        if (!Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $sQuery.="  AND uid = :userid ";
        }
        $sQuery.=" ORDER BY name";
        $command = Yii::app()->db->createCommand($sQuery)->bindParam(":ugid", $ugid, PDO::PARAM_INT);
        if (!Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $command->bindParam(":userid", $userid, PDO::PARAM_INT);
        }
		return $command->query()->readAll();
	}

	function deleteGroup($ugid, $ownerid)
	{
        $aParams=array();
        $aParams[':ugid']=$ugid;
        $sCondition="ugid = :ugid";
        if (!Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $sCondition.=" AND owner_id=:ownerid";
            $aParams[':ownerid']=$ownerid;
        }
        
        
		$group = UserGroup::model()->find($sCondition, $aParams);
		$group->delete();
        
		if($group->getErrors())
			return false;
		else
			return true;
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
