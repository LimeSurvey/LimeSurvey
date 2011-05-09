<?php

class dFunctionIfCount
{
	public function __construct()
	{
	}
	
	public function run($args)
	{		
		//global $connect, $dbprefix;
		list($field, $min, $max, $valueForTrue, $valueForFalse) = $args;
		if($valueForTrue === null)
			$valueForTrue = 'true';	// deafult value
		if($valueForFalse === null)
			$valueForFalse = 'false';	// deafult value
		if($max == 0)	// if field with $max is empty '::'
			$max = PHP_INT_MAX;
		
		$srid = $this->session->userdata('srid');
		$sid = $this->input->post('sid');
        $CI =& get_instance();
        $CI->load->model('surveys_dynamic_model');
		$result = $CI->surveys_dynamic_model->getSomeRecords($field,$sid,'WHERE id = '.$srid);
		//$query = "SELECT * FROM {$dbprefix}survey_$sid WHERE id = $srid";
		if(!$result){
			//throw new Exception("Couldn't get question '$field' answer<br />".$connect->ErrorMsg()); //Checked	
            throw new Exception("Couldn't get question '$field' answer<br />"); //Checked	
		}
		$row = $result->row_array();		

		$hits = 0;
		while($e = each($row))
			if(stripos($e['key'], $field) !== false)	// we're checking only fields containing answer to our question
				if(($e['value'] !== ""))	//	increase hits if user answered that question
					++$hits;
		
		if($hits >= $min && $hits <= $max)
			return $valueForTrue;
		else
			return $valueForFalse;
	}
}
