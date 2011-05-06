<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Users_model extends CI_Model {
	
	function getAllRecords()
	{
		$data = $this->db->get($this->db->dbprefix('users'));
		
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
		
		$data = $this->db->get($this->db->dbprefix('users'));
		return $data;
	}

	function getOTPwd($user)
	{
		$this->db->select('uid, users_name, password, one_time_pw, dateformat, full_name, htmleditormode');
		$this->db->where('users_name',$user);
		$data = $this->db->get($this->db->dbprefix('users'),1);
		
		return $data;
	}

	function deleteOTPwd($user)
	{
		$data = array(
				'one_time_pw' => ''
				);
		$this->db->where('users_name',$user);
		$this->db->update($this->db->dbprefix('users'),$data);
	}

}