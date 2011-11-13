<?php
/*
   * LimeSurvey
   * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
   * All rights reserved.
   * License: GNU/GPL License v2 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
   *	$Id: LSCI_Controller.php 11188 2011-10-17 14:28:02Z mot3 $
*/

class UserIdentity extends CUserIdentity
{
	protected $id;
	protected $user;

	/**
	 * Checks whether this user has correctly entered password or not
	 *
	 * @access public
	 * @return bool
	 */
	public function authenticate()
	{
		$user = User::model()->findByAttributes(array('users_name' => $this->username));

		if ($user === null)
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		else if ($user->password !== hash('sha256', $this->password))
			$this->errorCode = self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->id = $user->uid;
			$this->user = $user;
			$this->errorCode = self::ERROR_NONE;
		}
		return !$this->errorCode;
	}

	/**
	 * Returns the current user's ID
	 *
	 * @access public
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Returns the active user's record
	 *
	 * @access public
	 * @return CActiveRecord
	 */
	public function getUser()
	{
		return $this->user;
	}
}