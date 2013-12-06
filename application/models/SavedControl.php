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
class SavedControl extends LSActiveRecord {
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
		return 'scid';
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

	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}

		$data = $this->db->get('saved_control');

		return $data;
	}

    public function getCountOfAll($sid)
    {
        $data = Yii::app()->db->createCommand("SELECT COUNT(*) AS countall FROM {{saved_control}} WHERE sid=:sid")->bindParam(":sid", $sid, PDO::PARAM_INT)->query();
        $row = $data->read();

        return $row['countall'];
    }

    /**
    * Deletes some records meeting speicifed condition
    *
    * @access public
    * @param array $condition
    * @return int (rows deleted)
    */
    public function deleteSomeRecords($condition)
    {
    	$record = new self;
    	$criteria = new CDbCriteria;

    	if($condition != FALSE)
    	{
    		foreach($condition as $column=>$value)
    		{
    			$criteria->addCondition("$column='$value'");
    		}
    	}

    	return $record->deleteAll($criteria);
    }

    function insertRecords($data)
    {
        return $this->db->insert('saved_control', $data);
    }

}
