<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Failed_login_attempts_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		IF ($CONSITION != FALSE)
		{
			$this->db->where($condition);
		}
		$data = $this->db->get($this->db->dbprefix('failed_login_attempts'));
		
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
		
		$data = $this->db->get($this->db->dbprefix('failed_login_attempts'));
		
		return $data;
	}

	
}