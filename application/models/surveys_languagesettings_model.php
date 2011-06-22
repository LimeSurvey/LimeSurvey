<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Surveys_languagesettings_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('surveys_languagesettings');
		
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
		
		$data = $this->db->get('surveys_languagesettings');
		
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
    
    function getAllSurveys($hasPermission = FALSE)
    {
        $this->db->select('a.*, surveyls_title, surveyls_description, surveyls_welcometext, surveyls_url');
        $this->db->from('surveys AS a');
        $this->db->join('surveys_languagesettings','surveyls_survey_id=a.sid AND surveyls_language=a.language');
        
        if ($hasPermission)
        {
            $this->db->where('a.sid IN (SELECT sid FROM '.$this->db->dbprefix("survey_permissions").' WHERE uid='.$this->session->userdata("loginID").' AND permission=\'survey\' and read_p=1) ');
        }
        $this->db->order_by('active DESC, surveyls_title');
        return $this->db->get();
    }
	
	function update($data, $condition=FALSE)
	{
	
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$this->db->update('surveys_languagesettings', $data);
		
	}
    
    function getAllData($sid,$lcode)
    {
        $this->db->query('SELECT * FROM '. $this->db->prefix('surveys') .' , '. $this->db->prefix('surveys_languagesettings') .' WHERE sid='.$sid.' AND surveyls_survey_id='.$sid.' AND surveyls_language='.$lcode);
        return $this->db->get();
    }
    
    function insertNewSurvey($data)
    {
        $this->db->insert('surveys_languagesettings', $data); 
    }

}