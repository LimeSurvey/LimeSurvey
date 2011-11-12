<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Expression_errors_model extends CI_Model {

	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}

		$data = $this->db->get('expression_errors');

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

		$data = $this->db->get('expression_errors');

		return $data;
	}

	function update($data, $condition=FALSE)
	{

		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}

		$this->db->update('expression_errors', $data);

	}

    function insertRecords($data)
    {

        return $this->db->insert('expression_errors',$data);
    }

}