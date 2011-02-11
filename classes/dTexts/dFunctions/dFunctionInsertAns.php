<?php

class dFunctionInsertAns implements dFunctionInterface
{
	public function __construct()
	{
	}
	
	public function run($args)
	{
		global $connect;
		$field = $args[0];
		if (isset($_SESSION['srid'])) $srid = $_SESSION['srid'];
		$sid = $_POST['sid'];
		return retrieve_Answer($field, $_SESSION['dateformats']['phpdate']);
	}
}
