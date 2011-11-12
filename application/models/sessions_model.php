<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Sessions_model extends CI_Model {

	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}

		$data = $this->db->get('sessions');

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

		$data = $this->db->get('sessions');

		return $data;
	}

    function insertRecords($data)
    {
        return $this->db->insert('sessions',$data);
    }

    function cleanSessions()
    {
        $this->db->where(array('expiry <'=>date( 'Y-m-d H:i:s')));
        return $this->db->delete('sessions');
    }

}