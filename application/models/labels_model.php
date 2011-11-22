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

	function getSomeRecords($fields,$condition=FALSE,$max_field=false)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
        if ($max_field != false)
        {
            foreach ($max_field as $key => $maxfield)
            {
                $this->db->select_max($maxfield,$key);
            }
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

    function insertRecords($data)
    {
        
        return $this->db->insert('labels',$data);
    }

    function getLanguageRecords($condition)
    {
        $this->db->where($condition);
        $this->db->order_by('sortorder,code');
        return $this->db->get('labels');
    }

}