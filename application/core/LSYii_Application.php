<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
   * LimeSurvey
   * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
   * All rights reserved.
   * License: GNU/GPL License v2 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
   * $Id: survey.php 10433 2011-07-06 14:18:45Z dionet $
   *
*/

/**
 * Implements global registry and config
 */
class LSYii_Application extends CWebApplication
{
	protected $config = array();
	protected $registry = array();

	/**
	 * Initiates the application
	 *
	 * @access public
	 * @param array $config
	 * @return void
	 */
	public function __construct($config = null)
	{
		parent::__construct($config);

		// Load the default and environmental settings from different files into self.
		$app_config = require(APPPATH . '/config/application.php');
		$ls_config = require(APPPATH . '/config/lsconfig.php');
		$settings = array_merge($app_config, $ls_config);
		foreach ($settings as $key => $value)
			$this->setConfig($key, $value);
	}

	/**
	 * Sets a configuration variable into the registry
	 *
	 * @access public
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setConfig($name, $value)
	{
		$this->$config[$name] = $value;
	}

	/**
	 * Returns a config variable from the registry
	 *
	 * @access public
	 * @param string $name
	 * @return mixed
	 */
	public function getConfig($name)
	{
		return isset($this->config[$name]) ? $this->config[$name] : false;
	}

	/**
	 * Sets a configuration variable into the registry
	 *
	 * @access public
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setRegistry($name, $value)
	{
		$this->registry[$name] = $value;
	}

	/**
	 * Returns a config variable from the registry
	 *
	 * @access public
	 * @param string $name
	 * @return mixed
	 */
	public function getRegistry($name)
	{
		return isset($this->registry[$name]) ? $this->registry[$name] : false;
	}
}