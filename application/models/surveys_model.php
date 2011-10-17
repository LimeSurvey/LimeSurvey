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

    function deleteSurvey($iSurveyID, $recursive=true)
    {
        $this->db->delete('surveys', array('sid' => $iSurveyID));

        if ($recursive)
        {

            $this->load->helper('database');
            $this->load->dbforge();
            $this->load->model('questions_model');
            if (tableExists("survey_{$iSurveyID}"))  //delete the survey_$iSurveyID table
            {
                $dsresult = $this->dbforge->drop_table('survey_'.$iSurveyID) or safe_die ("Couldn't drop table survey_".$iSurveyID);
            }

            if (tableExists("survey_{$iSurveyID}_timings"))  //delete the survey_$iSurveyID_timings table
            {
                $dsresult = $this->dbforge->drop_table('survey_'.$iSurveyID.'_timings') or safe_die ("Couldn't drop table survey_".$iSurveyID."_timings");
            }

            if (tableExists("tokens_$iSurveyID")) //delete the tokens_$iSurveyID table
            {
                $dsresult = $this->dbforge->drop_table('tokens_'.$iSurveyID) or safe_die ("Couldn't drop table token_".$iSurveyID);
            }

            $oResult=$this->questions_model->getSomeRecords(array('qid'),array('sid'=>$iSurveyID));
            foreach ($oResult->result_array() as $aRow)
            {
                $this->db->delete('answers', array('qid' => $aRow['qid']));
                $this->db->delete('conditions', array('qid' => $aRow['qid']));
                $this->db->delete('question_attributes', array('qid' => $aRow['qid']));

            }

            $this->db->delete('questions', array('sid' => $iSurveyID));
            $this->db->delete('assessments', array('sid' => $iSurveyID));
            $this->db->delete('groups', array('sid' => $iSurveyID));
            $this->db->delete('surveys_languagesettings', array('surveyls_survey_id' => $iSurveyID));
            $this->db->delete('survey_permissions', array('sid' => $iSurveyID));
            $this->db->delete('saved_control', array('sid' => $iSurveyID));
            $this->load->model('survey_url_parameters_model');
            $this->survey_url_parameters_model->deleteRecords(array('sid'=>$iSurveyID));

            $this->load->model('quota_model');
            $this->quota_model->deleteQuota(array('sid'=>$iSurveyID));
        }

    }


}
