<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Question_attributes_model extends CI_Model {

	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}

        $this->db->order_by('attribute');
		$data = $this->db->get('question_attributes');

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

        $this->db->order_by('attribute');
		$data = $this->db->get('question_attributes');

		return $data;
	}

    function insertRecords($data)
    {

        return $this->db->insert('question_attributes',$data);
    }
    
    function getEMRelatedRecordsForSurvey($surveyid=NULL,$qid=NULL)
    {
        if (!is_null($qid)) {
            $where = " qid = ".$qid." and ";
        }
        else if (!is_null($surveyid)) {
            $where = " qid in (select qid from ".$this->db->dbprefix('questions')." where sid = ".$surveyid.") and ";
        }
        else {
            $where = "";
        }

        // TODO - does this need to be filtered by language
        $query = "select distinct qid, attribute, value"
                ." from ".$this->db->dbprefix('question_attributes')
                ." where " . $where
                ." attribute in ('hidden', 'array_filter', 'array_filter_exclude', 'code_filter', 'equals_num_value', 'exclude_all_others', 'exclude_all_others_auto', 'max_answers', 'max_num_value', 'max_num_value_n', 'max_num_value_sgqa', 'min_answers', 'min_num_value', 'min_num_value_n', 'min_num_value_sgqa', 'multiflexible_max', 'multiflexible_min', 'num_value_equals_sgqa', 'show_totals')"
                ." order by qid, attribute";
        
		$data = $this->db->query($query);
        $qattr = array();

        foreach($data->result_array() as $row) {
            $qattr[$row['qid']][$row['attribute']] = $row['value'];
        }

		return $qattr;
    }

    function deleteRecords($condition)
    {
        $this->db->where($condition);

        return $this->db->delete('question_attribute');
    }
}
