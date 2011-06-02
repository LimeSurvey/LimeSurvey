<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Users_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('users');
		
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
		
		$data = $this->db->get('users');
		return $data;
	}

	function getOTPwd($user)
	{
		$this->db->select('uid, users_name, password, one_time_pw, dateformat, full_name, htmleditormode');
		$this->db->where('users_name',$user);
		$data = $this->db->get('users',1);
		
		return $data;
	}

	function deleteOTPwd($user)
	{
		$data = array(
				'one_time_pw' => ''
				);
		$this->db->where('users_name',$user);
		$this->db->update('users',$data);
	}
	
	function updateLang($uid,$postloginlang)
	{
		$data = array(
				'lang' => $postloginlang
				);
		$this->db->where(array("uid"=>$uid));
		$this->db->update('users',$data);
	}
	
	function updatePassword($uid,$password)
	{
		$data = array(
				'password' => $password
				);
		$this->db->where(array("uid"=>$uid));
		$this->db->update('users',$data);
	}

}