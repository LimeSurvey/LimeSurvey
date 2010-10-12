<?php

class dFunctionSwitch implements dFunctionInterface
{
	public function __construct()
	{
	}
	
	public function run($args)
	{		
		global $connect, $dbprefix;			
		$field = $args[0];
		$srid = $_SESSION['srid'];
		$sid = $_POST['sid'];
		$query = "SELECT $field FROM {$dbprefix}survey_$sid WHERE id = $srid";
		if(!$result = $connect->Execute($query)){
			throw new Exception("Couldn't get question '$field' answer<br />".$connect->ErrorMsg()); //Checked	
		}
		$row = $result->fetchRow();		
		$value = $row[$field];
		
		$found = array_keys($args, $value);
		if(count($found))
		{
			while($e = each($found))
				if($e['value'] % 2 != 0)	// we check this, as only at odd indexes there are 'cases'
					return $args[$e['value']+1]; // returns value associated with found 'case'
		}
		// return empty string if none of cases matches user's answer 
		return "";		
	}
}
