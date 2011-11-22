<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Defaultvalues_model extends CI_Model {

	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}

		$data = $this->db->get('defaultvalues');

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

		$data = $this->db->get('defaultvalues');

		return $data;
	}

    function getSurveyDefaultValues($fields,$sid)
    {
        foreach ($fields as $field)
        {
            $this->db->select($field);
        }
        $this->db->join('questions', 'questions.qid = defaultvalues.qid');
        $this->db->where(array('sid'=>$sid));

        $data = $this->db->get('defaultvalues');

        return $data;
    }

    function insertRecords($data)
    {

        return $this->db->insert('defaultvalues',$data);
    }

    function deleteRecords($condition)
    {
        $this->db->where($condition);

        return $this->db->delete('defaultvalues');
    }

}