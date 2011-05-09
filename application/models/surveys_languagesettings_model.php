<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Surveys_languagesettings_model extends CI_Model {
	
	function getAllRecords()
	{
		$data = $this->db->get($this->db->dbprefix('surveys_languagesettings'));
		
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
		
		$data = $this->db->get($this->db->dbprefix('surveys_languagesettings'));
		
		return $data;
	}
    
    function getDateFormat($surveyid,$languagecode)
    {
        $this->db->select('surveyls_dateformat');
        $this->db->from('surveys_languagesettings');
        $this->db->join('surveys','surveys.sid = surveys_languagesettings.surveyls_survey_id AND surveyls_survey_id = '.$surveyid);
        $this->db->where('surveyls_language = \''.$languagecode.'\'');
        return $this->db->get();
    }

}