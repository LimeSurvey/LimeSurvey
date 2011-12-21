<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */
class User_groups_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('user_groups');
		
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
		
		$data = $this->db->get('user_groups');
		
		return $data;
	}

    function insertRecords($data)
    {
        
        return $this->db->insert('user_groups',$data);
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
	
	function multi_select($fields, $from, $condition=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		
		foreach ($from AS $f)
		{
			$this->db->from($f);
		}
		
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
	
	function update($what, $where=FALSE)
	{
		if ($where != FALSE) $this->db->where($where);
		return (bool) $this->db->update('user_groups', $what);
	}
	
	function delete($condition)
	{
		return (bool) $this->db->delete('user_groups', $condition);
	}

}
