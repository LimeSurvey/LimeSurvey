<?php

class dFunctionSwitch
{
	public function __construct()
	{
	}
	
	public function run($args)
	{		
		//global $connect, $dbprefix;			
		$field = $args[0];
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
