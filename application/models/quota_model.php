<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Quota_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('quota');
		
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
		
		$data = $this->db->get('quota');
		
		return $data;
	}

	function getQuotaInformation($surveyid,$language,$quotaid)
	{
		//Used by getQuotaInformation helper
        $query = "SELECT * FROM ".$this->db->dbprefix('quota').", ".$this->db->dbprefix('quota_languagesettings')."
		   	  WHERE ".$this->db->dbprefix('quota').".id = ".$this->db->dbprefix('quota_languagesettings').".quotals_quota_id
			  AND sid='".$surveyid."'
              AND quotals_language='".$language."'";
		if ($quotaid != 'all')
		{
			$query .= " AND id=$quotaid";
		}
		return $this->db->query($query);
	}
}