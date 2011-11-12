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

abstract class LSYii_Controller extends CController
{
	/**
	 * Basic initialiser to the base controller class
	 *
	 * @access public
	 * @param string $id
	 * @param CWebModule $module
	 * @return void
	 */
	public function __construct($id, $module = null)
	{
		parent::__construct($id, $module);
		$this->_checkinstallation();
		require_once(APPPATH . '/libraries/LS/LS' . EXT);
		$this->_init();
	}

	/**
	 * Check that installation was already done.
	 *
	 * Will exit the script if not.
	 *
	 * @access protected
	 * @return void
	 */
	protected function _checkinstallation()
	{
		$file_name = '/tmp/sample_installer_file.txt';
		$installer_file = ROOT . $file_name;
		if (is_file($installer_file))
		{
			$message = '<p>Use <a href="'.  $this->createUrl('/installer') . '">the LimeSurvey Web Installer</a> to set-up LimeSurvey.<p>'
			    ."<p>If you are already done with installation, please delete the file lock file (<tt>...{$file_name}</tt>).";
			throw new CException($message);
		}
	}

	protected function _init()
	{
		// Check for most necessary requirements
		// Now check for PHP & db version
		// Do not localize/translate this!

		$dieoutput='';
		if (version_compare(PHP_VERSION, '5.1.6', '<'))
			$dieoutput .= 'This script can only be run on PHP version 5.1.6 or later! Your version: '.PHP_VERSION.'<br />';

		if (!function_exists('mb_convert_encoding'))
			$dieoutput .= "This script needs the PHP Multibyte String Functions library installed: See <a href='http://docs.limesurvey.org/tiki-index.php?page=Installation+FAQ'>FAQ</a> and <a href='http://de.php.net/manual/en/ref.mbstring.php'>PHP documentation</a><br />";

		if ($dieoutput != '')
			throw new CException($dieoutput);

		if (!isset($debug)) $debug=0;  // for some older config.php's

		//Currently set at root index.php
		//if ($debug>0) {//For debug purposes - switch on in config.php
		//    @ini_set("display_errors", 1);
		//    error_reporting(E_ALL);
		//}

		//if ($debug>2) {//For debug purposes - switch on in config.php
		//    error_reporting(E_ALL | E_STRICT);
		//}

		if (ini_get("max_execution_time") < 1200) @set_time_limit(1200); // Maximum execution time - works only if safe_mode is off
		//@ini_set("memory_limit",$memorylimit); // Set Memory Limit for big surveys

		$maildebug='';

		// The following function (when called) includes FireBug Lite if true
		define('FIREBUG' , Yii::app()->getConfig('use_firebug_lite'));

		define("_PHPVERSION", phpversion()); // This is the same as the server defined 'PHP_VERSION'

		// Deal with server systems having not set a default time zone
		if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
			@date_default_timezone_set(@date_default_timezone_get());

		//Every 50th time clean up the temp directory of old files (older than 1 day)
		//depending on the load the  probability might be set higher or lower
		if (rand(1,50)==25)
		{
			cleanTempDirectory();
		}

		// Array of JS and CSS scripts to include in client header
		//$js_header_includes = array();
		//$css_header_includes =  array();

		// JS scripts and CSS to include in admin header
		// updated by admin scripts
		//$js_admin_includes = array();
		//$css_admin_includes = array();

		//if (!$this->config->item('database'))
		//{
		//    show_error("Database Information not provided. If you try to install LimeSurvey please refer to the <a href='http://docs.limesurvey.org'>installation docs</a> and/or contact the system administrator of this webpage.");
			//}

			//if (!tableExists('surveys') && (strcasecmp($slashlesspath,str_replace(array("\\", "/"), "", $homedir."install")) != 0)) {
			//    show_error("<br />The LimeSurvey database does exist but it seems to be empty. Please run the <a href='$homeurl/install/index.php'>install script</a> to create the necessary tables.");
			//}

			// Default global values that should not appear in config-defaults.php
			//$updateavailable=0;
			//$updatebuild='';
			//$updateversion='';
			//$updatelastcheck='';
			//$updatekey='';
			//$updatekeyvaliduntil='';
			Yii::app()->setConfig("updateavailable", 0);

		//GlobalSettings Helper
		Yii::import("application.helpers.globalsettings");

		SSL_mode();// This really should be at the top but for it to utilise getGlobalSetting() it has to be here

		//$showXquestions = getGlobalSetting('showXquestions');
		//$showgroupinfo = getGlobalSetting('showgroupinfo');
		//$showqnumcode = getGlobalSetting('showqnumcode');
		Yii::app()->setConfig("showXquestions", getGlobalSetting('showXquestions'));
		Yii::app()->setConfig("showgroupinfo", getGlobalSetting('showgroupinfo'));
		Yii::app()->setConfig("showqnumcode", getGlobalSetting('showqnumcode'));


		//SET LOCAL TIME
		$timeadjust = Yii::app()->setConfig("timeadjust", null);
		if (substr($timeadjust,0,1)!='-' && substr($timeadjust,0,1)!='+') {$timeadjust='+'.$timeadjust;}
		if (strpos($timeadjust,'hours')===false && strpos($timeadjust,'minutes')===false && strpos($timeadjust,'days')===false)
		{
			Yii::app()->setConfig("timeadjust",$timeadjust.' hours');
		}

		// SITE STYLES
		//$setfont = "<font size='2' face='verdana'>";
		//$singleborderstyle = "style='border: 1px solid #111111'";
	}
}