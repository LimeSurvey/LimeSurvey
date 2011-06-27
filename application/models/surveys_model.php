<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Surveys_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('surveys');
		
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
		
		$data = $this->db->get('surveys');
		
		return $data;
	}
    
    function getDataOnSurvey($surveyid)
    {
        $sql = "SELECT * FROM ".$this->db->dbprefix('surveys')." inner join ".$this->db->dbprefix('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=".$surveyid;
        $this->load->helper('database');
        return db_select_limit_assoc($sql, 1);
        
    }
    
    function insertNewSurvey($data)
    {
        $this->db->insert('surveys', $data); 
    }
    
    
    
}