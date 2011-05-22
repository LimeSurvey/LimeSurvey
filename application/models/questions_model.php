<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Questions_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('questions');
		
		return $data;
	}

	function getSomeRecords($fields,$condition=FALSE,$order=FALSE)
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
		
		$data = $this->db->get('questions');
		
		return $data;
	}
    
    function getQuestions($sid,$gid,$language)
    {
        $this->db->where('sid',$sid);
        $this->db->where('gid',$gid);
        $this->db->where('language',$language);
        $this->db->where('parent_qid',0);
        $this->db->order_by("question_order","asc");
        $data = $this->db->get('questions');
		
		return $data;
    }
    
    function getQuestionID($sid,$gid,$language)
    {
        $this->db->select('qid');
        $this->db->where('sid',$sid);
        $this->db->where('gid',$gid);
        $this->db->where('language',$language);
        $this->db->where('parent_qid',0);
        $this->db->order_by("question_order","asc");
        $data = $this->db->get('questions');
		
		return $data;
    }
    
    function getMaximumQuestionOrder($gid,$language)
    {
        $this->db->select_max('question_order','max');
        $this->db->where('gid',$gid);
        $this->db->where('language',$language);
        $data = $this->db->get('questions');
		
		return $data;
    }
    
    function updateQuestionOrder($gid,$lang,$position=0)
    {
        $this->db->select('qid');
        $this->db->where('gid',$gid);
        $this->db->where('language',$lang);
        $this->db->order_by('question_order, title ASC');
        $data = $this->db->get('questions');
		
		foreach($data->result_array() as $row)
        {
            $datatoupdate = array('question_order' => $position);
            $this->db->where('qid',$row['qid']);
            $this->db->update('questions',$datatoupdate);
            $position++;
        }
    }
    
    function getQuestionType($qid)
    {
        return $this->db->query('SELECT type FROM '.$this->db->dbprefix('questions').' WHERE qid=$qid and parent_qid=0 group by type');
    }
	
	function getSubQuestions($sid,$sLanguage)
	{
		//Used by getSubQuestions helper
		$query = "SELECT sq.*, q.other FROM ".$this->db->dbprefix('questions')." as sq, ".$this->db->dbprefix('questions')." as q"
	            ." WHERE sq.parent_qid=q.qid AND q.sid=".$sid
	            ." AND sq.language='".$sLanguage. "' "
	            ." AND q.language='".$sLanguage. "' "
	            ." ORDER BY sq.parent_qid, q.question_order,sq.scale_id , sq.question_order";
		return $this->db->query($query);
	}
	
	function update($data, $condition=FALSE)
	{
	
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$this->db->update('questions', $data);
		
	}

}