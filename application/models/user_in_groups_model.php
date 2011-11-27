<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class User_in_groups_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('user_in_groups');
		
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
		
		$data = $this->db->get('user_in_groups');
		
		return $data;
	}
	
	function insert($data)
	{
		return (bool) $this->db->insert('user_in_groups', $data);
	}
	
	function join($fields, $from, $condition=FALSE, $join=FALSE, $order=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		
		$this->db->from($from);
		
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}

		if ($order != FALSE)
		{
			$this->db->order_by($order);	
		}
		
		if (isset($join['where'], $join['type'], $join['on']))
		{
			$this->db->join($condition);	
		}
		
		$data = $this->db->get();
		return $data;
	}

}
