<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Question_attributes_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('question_attributes');
		
		return $data;
	}

	function getSomeRecords($fields,$condition=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('question_attributes');
		
		return $data;
	}
    
    function insertRecords($data)
    {
        
        return $this->db->insert('question_attributes',$data);
    }

}