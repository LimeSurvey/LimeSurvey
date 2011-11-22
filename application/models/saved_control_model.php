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
        $data = $this->db->query("SELECT COUNT(*) AS countall FROM ".$this->db->dbprefix."saved_control WHERE sid=$sid");
        $row = $data->row_array();
        
        return $row['countall'];
    }
    
    function insertRecords($data)
    {
        return $this->db->insert('saved_control', $data); 
    }

    function deleteSurveyRecords($condition=FALSE) {
        if ($condition != FALSE && is_array($condition))
        {            
            $this->db->where($condition);
            return $this->db->delete('saved_control');
        }
        
        return false;
    }

    function getSavedList($condition)
    {
        $this->db->select('scid, srid, identifier, ip, saved_date, email, access_code');
        $this->db->where($condition);
        $this->db->order_by('saved_date','desc');

        $data = $this->db->get('saved_control');

        return $data;
    }

}
