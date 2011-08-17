<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Labelsets_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('labelsets');
		
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
		
		$data = $this->db->get('labelsets');
		
		return $data;
	}
    
    function getLID()
    {
        $this->db->select('lid');
        $this->db->order_by('lid','asc');
        return $this->db->get('labelsets');
    }

    function insertRecords($data)
    {
        
        return $this->db->insert('labelsets',$data);
    }

}