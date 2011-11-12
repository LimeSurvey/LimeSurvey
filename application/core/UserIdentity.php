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

	/**
	 * Checks whether this user has correctly entered password or not
	 *
	 * @access public
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	public function authenticate($username, $password)
	{
		$user = User::model()->findByAttribute(array('users_name' => $username));

		if ($record === null)
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		else if ($record->password !== hash('sha256', $this->password))
			$this->errorCode = self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->id = $record->id;
			$this->errorCode = self::ERROR_NONE;
		}
		return !$this->errorCode;
	}

	public function getId()
	{
		return $this->id;
	}
}