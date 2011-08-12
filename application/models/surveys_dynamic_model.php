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

	function getSomeRecords($fields,$sid,$condition=FALSE,$order=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		if ($order != FALSE)
		{
			$this->db->order_by($order);	
		}
		$data = $this->db->get('survey_'.$sid);
		
		return $data;
	}
	
	function quotaCompletedCount($sid,$querycond)
	{
		//Used by get_quotaCompletedCount()
		$querysel = "SELECT count(id) as count FROM ".db_table_name('survey_'.$sid)." WHERE ".implode(' AND ',$querycond)." "." AND submitdate IS NOT NULL";
        return $this->db->query($querysel);
	}
    
    function insertRecords($sid,$data)
    {
        return $this->db->insert('survey_'.$sid, $data); 
    }
    
}