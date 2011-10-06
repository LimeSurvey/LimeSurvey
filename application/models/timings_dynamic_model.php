<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Timings_dynamic_model extends CI_Model {

	function getAllRecords($iSurveyID,$condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}

		$data = $this->db->get('survey_'.$iSurveyID.'_timings');

		return $data;
	}

	function getSomeRecords($fields,$iSurveyID,$condition=FALSE,$order=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}
		if ($order != FALSE)
		{
			$this->db->order_by($order);
		}
		$data = $this->db->get('survey_'.$iSurveyID.'_timings');

		return $data;
	}



    function insertRecords($iSurveyID,$data)
    {
        return $this->db->insert('survey_'.$iSurveyID.'_timings', $data);
    }

}