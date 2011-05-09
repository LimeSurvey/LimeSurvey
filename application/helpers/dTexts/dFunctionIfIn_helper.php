<?php

class dFunctionIfIn
{
	public function __construct()
	{
	}
	
	public function run($args)
	{		
		//global $connect, $dbprefix;
		$field = array_shift($args);
		$valueForTrue = array_shift($args);	// value that will'be inserted if user's answer hits one of our options
		$srid = $this->session->userdata('srid');
		$sid = $this->input->post('sid');
        $CI =& get_instance();
        $CI->load->model('surveys_dynamic_model');
		$result = $CI->surveys_dynamic_model->getSomeRecords($field,$sid,'WHERE id = '.$srid);
		//$query = "SELECT $field FROM {$dbprefix}survey_$sid WHERE id = $srid";
		if(!$result){
			//throw new Exception("Couldn't get question '$field' answer<br />".$connect->ErrorMsg()); //Checked	
            throw new Exception("Couldn't get question '$field' answer<br />"); //Checked
		}
		$row = $result->row_array();		
		$value = $row[$field];
		
		if(in_array($value, $args))
			return $valueForTrue;
		else
			return "";
	}
}
