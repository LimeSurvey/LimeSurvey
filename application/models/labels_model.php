<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Labels_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('labels');
		
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
		
		$data = $this->db->get('labels');
		
		return $data;
	}
    
    function getLabelCodeInfo($lid)
    {
        $this->db->select('code, title, sortorder, language, assessment_value');
        $this->db->where('lid',$lid);
        $this->db->order_by('language, sortorder, code');
        return $this->db->get('labels');
    }

}