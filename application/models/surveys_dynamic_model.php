<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Surveys_dynamic_model extends CI_Model {
	
	function getAllRecords($sid,$condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('survey_'.$sid);
		
		return $data;
	}

	function getSomeRecords($fields,$sid,$condition=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('survey_'.$sid);
		
		return $data;
	}
    
}