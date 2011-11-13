<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Failed_login_attempts extends CActiveRecord
{
	/**
	 * Returns the static model
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
	 * @param string $ip
	 * @return void
	 */
	public function deleteAttempts($ip)
	{
		$this->deleteAllByAttributes(array('ip' => $ip));
	}

	/**
	 * Check if an IP address is allowed to login or not
	 *
	 * @param string $sIPAddress IP Address to check
	 * @return boolean Returns true if the user is blocked
	 */
	public function isLockedOut($ip)
	{
		$isLockedOut = false;

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
	 * @param string $ip
	 * @return true
	 */
	public function addAttempt($ip)
	{
		$timestamp = date("Y-m-d H:i:s");

		$row = $this->findByAttributes(array('ip' => $ip));

		if ($row !== null)
		{
			$row->number_attempts = $row->number_attempts + 1;
			$row->last_attempt = $timestamp;
			$row->save();
		}
		else
		{
			$record = new Failed_login_attempts;
			$record->ip = $ip;
			$record->number_attempts = 1;
			$record->last_attempt = $timestamp;
			$record->save();
		}

		return true;
	}
}