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
class FailedLoginAttempt extends LSActiveRecord
{
	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
     * @param string $class
	 * @return FailedLoginAttempt
	 */
	public static function model($class = __CLASS__)
	{
		return parent::model($class);
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'id';
	}

	/**
	 * Returns the table's name
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{failed_login_attempts}}';
	}

	/**
	 * Deletes all the attempts by IP
	 *
	 * @access public
	 * @return void
	 */
	public function deleteAttempts()
	{
		$ip = substr(getIPAddress(),0,40);
		$this->deleteAllByAttributes(array('ip' => $ip));
	}

	/**
	 * Check if an IP address is allowed to login or not
	 *
	 * @return boolean Returns true if the user is blocked
	 */
	public function isLockedOut()
	{
		$isLockedOut = false;
		$ip = substr(getIPAddress(),0,40);
		$criteria = new CDbCriteria;
		$criteria->condition = 'number_attempts > :attempts AND ip = :ip';
		$criteria->params = array(':attempts' => Yii::app()->getConfig('maxLoginAttempt'), ':ip' => $ip);

		$row = $this->find($criteria);

		if ($row != null)
		{
			$lastattempt = strtotime($row->last_attempt);
			if (time() > $lastattempt + Yii::app()->getConfig('timeOutTime'))
				$this->deleteAttempts($ip);
			else
				$isLockedOut = true;
		}
		return $isLockedOut;
	}

	/**
	 * This function removes obsolete login attempts
	 * TODO
	 */
	public function cleanOutOldAttempts()
	{
		// this where select whole part
		//$this->db->where('now() > (last_attempt+'.$this->config->item("timeOutTime").')');
		//return $this->db->delete('failed_login_attempts');
	}

	/**
	 * Creates an attempt
	 *
	 * @access public
	 * @return true
	 */
	public function addAttempt()
	{
		$timestamp = date("Y-m-d H:i:s");
		$ip = substr(getIPAddress(),0,40);
		$row = $this->findByAttributes(array('ip' => $ip));

		if ($row !== null)
		{
			$row->number_attempts = $row->number_attempts + 1;
			$row->last_attempt = $timestamp;
			$row->save();
		}
		else
		{
			$record = new FailedLoginAttempt;
			$record->ip = $ip;
			$record->number_attempts = 1;
			$record->last_attempt = $timestamp;
			$record->save();
		}

		return true;
	}
}
