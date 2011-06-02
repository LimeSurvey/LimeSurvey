<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Failed_login_attempts_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('failed_login_attempts');
		
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
		
		$data = $this->db->get('failed_login_attempts');
		
		return $data;
	}
	
	function deleteAttempts($ip) {
		
		$this->db->where('ip', $ip);
		return $this->db->delete('failed_login_attempts'); 
	}
	
	function addAttempt($la,$sIp)
	{
	    $timestamp = date("Y-m-d H:m:s");    
	    if ($la)
		{
	        //$query = "UPDATE ".db_table_name('failed_login_attempts')
	        //         ." SET number_attempts=number_attempts+1, last_attempt = '$timestamp' WHERE ip='$sIp'";
			$query = $this->db->query("UPDATE ".$this->db->dbprefix('failed_login_attempts')
	                ." SET number_attempts=number_attempts+1, last_attempt = '".$timestamp."' WHERE ip='".$sIp."'");
		}		 
	    else
	        $query = $this->db->query("INSERT INTO ".$this->db->dbprefix('failed_login_attempts') . "(ip, number_attempts,last_attempt)"
	                 ." VALUES('".$sIp."',1,'".$timestamp."')");
	
	    return $query;
	}
	
}