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
class Assessments_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('assessments');
		
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
		
		$data = $this->db->get('assessments');
		
		return $data;
	}
    
    function insertRecords($data)
    {
        
        return $this->db->insert('assessments',$data);
    }
	
	function updateRecord($data, $assessmentlang)
	{
        $query = "UPDATE ".$this->db->dbprefix('assessments')."
	      SET scope=?,
	      gid=?,
	      minimum=?,
	      maximum=?,
	      name=?,
	      message=?
	      WHERE language='$assessmentlang' and id=".sanitize_int($data['id']);
		$fields=array($data['scope'], $data['gid'], sanitize_signedint($data['minimum']),
					sanitize_signedint($data['maximum']), $data['name_'.$assessmentlang],
					$data['assessmentmessage_'.$assessmentlang]);
		return $this->db->query($query,$fields);
	}
	
	function dropRecord($id)
	{
		$query = "DELETE FROM ".$this->db->dbprefix('assessments')." WHERE id=".sanitize_int($id);
		return $this->db->query($query);
	}

	function getAssessments($surveyid)
	{
	    $query = "SELECT id, sid, scope, gid, minimum, maximum, name, message
				  FROM ".$this->db->dbprefix('assessments')."
				  WHERE sid=? and language=?
				  ORDER BY scope, gid";
	    $result=$this->db->query($query,array($surveyid,$this->config->item("baselang")));
	    $output=array();
	    foreach($result->result_array() as $row) {
	        $output[]=$row;
	    }
	    return $output;
	}

}
