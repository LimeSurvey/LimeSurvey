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

    /**
    * Check if an IP address is allowed to login or not
    *
    * @param string $sIPAddress IP Address to check
    * @return boolean Returns true if the user is blocked
    */
    function isLockedOut($sIPAddress)
    {
        $this->db->where('number_attempts >',$this->config->item("maxLoginAttempt"));
        $this->db->where('ip >',$sIPAddress);
        $oQuery = $this->db->get('failed_login_attempts');
        return ($oQuery->num_rows()>0);
    }

    /**
    * This function removes obsolete login attempts
    *
    */
    function cleanOutOldAttempts()
    {
        $this->db->where('now() > (last_attempt+'.$this->config->item("timeOutTime").')');
        return $this->db->delete('failed_login_attempts');
    }


	function addAttempt($sIp)
	{

        $timestamp = date("Y-m-d H:m:s");
        $this->db->where('ip', $ip);
        $oData=$this->db->select('failed_login_attempts');
	    if ($oData->num_rows()>0)
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