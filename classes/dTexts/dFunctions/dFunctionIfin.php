<?php

class dFunctionIfIn implements dFunctionInterface
{
	public function __construct()
	{
	}
	
	public function run($args)
	{		
		global $connect, $dbprefix;
		$field = array_shift($args);
		$valueForTrue = array_shift($args);	// value that will'be inserted if user's answer hits one of our options
		$srid = $_SESSION['srid'];
		$sid = $_POST['sid'];
		$query = "SELECT $field FROM {$dbprefix}survey_$sid WHERE id = $srid";
		if(!$result = $connect->Execute($query)){
			throw new Exception("Couldn't get question '$field' answer<br />".$connect->ErrorMsg()); //Checked	
		}
		$row = $result->fetchRow();		
		$value = $row[$field];
		
		if(in_array($value, $args))
			return $valueForTrue;
		else
			return "";
	}
}
