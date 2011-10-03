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

        return $this->db->insert('surveys', $data);
    }

    function updateSurvey($data,$condition)
    {
        $this->db->where($condition);
        return $this->db->update('surveys', $data);
    }
    
    function getSurveyNames()
    {
        $this->db->select('surveyls_survey_id,surveyls_title');
        $this->db->from('surveys_languagesettings');
        $this->db->join('surveys','surveys_languagesettings.surveyls_survey_id = surveys.sid');
        $this->db->where('owner_id',$this->session->userdata('loginID'));
        //$this->db->where('usetokens','Y'); // Will be done later
        $query=$this->db->get();
        return $query->result_array();
    }
    function getALLSurveyNames()
    {
        $this->db->select('surveyls_survey_id,surveyls_title');
        $this->db->from('surveys_languagesettings');
        $this->db->join('surveys','surveys_languagesettings.surveyls_survey_id = surveys.sid');
        //$this->db->where('usetokens','Y'); // Will be done later
        $query=$this->db->get();
        return $query->result_array();
    }


}
