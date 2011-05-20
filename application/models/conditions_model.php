<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Conditions_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('conditions');
		
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
		
		$data = $this->db->get('conditions');
		
		return $data;
	}
    
    function updateCFieldName($sid,$qid,$oldid,$newid)
    {
        $this->db->select('cid, cfieldname');
        $this->db->where('cqid',$qid);
        $data = $this->db->get('conditions');
		
		foreach($data->result_array() as $crow)
        {
            $mycid=$crow['cid'];
            $mycfieldname=$crow['cfieldname'];
            $cfnregs="";

            if (preg_match('/'.$sid."X".$oldid."X".$qid."(.*)/", $mycfieldname, $cfnregs) > 0)
            {
                $newcfn=$sid."X".$newid."X".$qid.$cfnregs[1];
                $datatoupdate = array('cfieldname' => $newcfn);
                $this->db->where('cid',$mycid);
                $this->db->update('conditions',$datatoupdate);
                
            }
        }
    }
    
    function getConditions($surveyid,$questionid,$language=false)
    {
        
        $aquery = "SELECT *, "
        ." (SELECT count(1) FROM ".$this->db->prefix('conditions')." c\n"
        ." WHERE questions.qid = c.qid) AS hasconditions,\n"
        ." (SELECT count(1) FROM ".$this->db->prefix('conditions')." c\n"
        ." WHERE questions.qid = c.cqid) AS usedinconditions\n"
        ." FROM ".$this->db->prefix('questions')." as questions, ".$this->db->prefix('groups')." as groups"
        ." WHERE questions.gid=groups.gid AND "
        ." questions.sid=$surveyid AND "
        ." questions.language='{$language}' AND "
        ." questions.parent_qid=0 AND "
        ." groups.language='{$language}' ";
        if ($questionid!==false)
        {
            $aquery.=" and questions.qid={$questionid} ";
        }
        $aquery.=" ORDER BY group_order, question_order";
        
        $data = $this->db->query($aquery);
        return $data;
    }

}