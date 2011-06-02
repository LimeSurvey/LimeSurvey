<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends AdminController {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$clang=$this->limesurvey_lang;
		self::_showMessageBox($clang->gT("Logged in"), "Placeholder page. <a href='".site_url("admin/authentication/logout")."'>Logout</a>");
	}
}