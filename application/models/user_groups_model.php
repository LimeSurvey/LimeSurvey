<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class User_groups_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('user_groups');
		
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
		
		$data = $this->db->get('user_groups');
		
		return $data;
	}

    function insertRecords($data)
    {
        
        return $this->db->insert('user_groups',$data);
    }

}