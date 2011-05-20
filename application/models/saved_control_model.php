<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Saved_control_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('saved_control');
		
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
		
		$data = $this->db->get('saved_control');
		
		return $data;
	}
    
    function getCountOfAll($sid)
    {
        $data = $this->db->query("SELECT COUNT(*) FROM ".$this->db->prefix('saved_control')." WHERE sid=$sid");
        return $data;
    }

}