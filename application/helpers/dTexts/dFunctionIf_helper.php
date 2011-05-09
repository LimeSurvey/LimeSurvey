<?php

class dFunctionIf
{
	public function __construct()
	{
	}
	
	public function run($args)
	{
		//global $connect, $dbprefix;		
		list($field, $value, $valueForTrue, $valueForFalse) = $args;
		if($valueForTrue === null)
			$valueForTrue = 'true';	// deafult value
		if($valueForFalse === null)
			$valueForFalse = 'false';	// deafult value
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
