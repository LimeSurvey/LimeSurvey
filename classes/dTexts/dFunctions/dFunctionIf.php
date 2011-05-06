<?php

class dFunctionIf implements dFunctionInterface
{
	public function __construct()
	{
	}
	
	public function run($args)
	{
		global $connect, $dbprefix;		
		list($field, $value, $valueForTrue, $valueForFalse) = $args;
		if($valueForTrue === null)
			$valueForTrue = 'true';	// deafult value
		if($valueForFalse === null)
			$valueForFalse = 'false';	// deafult value
		$srid = $_SESSION['srid'];
		$sid = $_POST['sid'];
		$query = "SELECT $field FROM {$dbprefix}survey_$sid WHERE id = $srid";
		if(!$result = $connect->Execute($query)){
			throw new Exception("Couldn't get question '$field' answer<br />".$connect->ErrorMsg()); //Checked	
		}
		$row = $result->fetchRow();		
		
		if ($row[$field] == $value)
		{
			return $valueForTrue;
		}
		else
		{   
			return $valueForFalse;
		}
	}
}
