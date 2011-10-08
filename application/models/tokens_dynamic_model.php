<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Tokens_dynamic_model extends CI_Model {
	
	function getAllRecords($sid,$condition=FALSE,$limit=FALSE,$start=FALSE,$order=FALSE,$like_or=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		if ($limit !== FALSE && $start !== FALSE)
		{
			$this->db->limit($limit,$start);	
		}
		
		if ($order != FALSE)
		{
			$this->db->order_by($order);	
		}
		
		if ($like_or != FALSE)
		{
			$this->db->or_like($like_or);	
		}
		
		//var_dump($this->db->_compile_select());
		//die();
		$data = $this->db->get('tokens_'.$sid);
		
		return $data;
	}

	function getSomeRecords($fields,$sid,$condition=FALSE,$group_by=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		if ($group_by != FALSE)
		{
			$this->db->group_by($group_by); 
		}	
		$data = $this->db->get('tokens_'.$sid);
		
		return $data;
	}
    
	function totalTokens($surveyid)
	{
		$tksq = "SELECT count(tid) FROM ".$this->db->dbprefix("tokens_$surveyid");
		$tksr = $this->db->query($tksq);
		$tkr = $tksr->row_array();
		return $tkr["count(tid)"];	
		//return $tkcount;
	}

	function tokensSummary($surveyid)
	{
	
		// SEE HOW MANY RECORDS ARE IN THE TOKEN TABLE
		$tksq = "SELECT count(tid) FROM ".$this->db->dbprefix("tokens_$surveyid");
		$tksr = $this->db->query($tksq);
		$tkr = $tksr->row_array();
		$tkcount = $tkr["count(tid)"];	
		$data['tkcount']=$tkcount;

	    $tksq = "SELECT count(*) FROM ".$this->db->dbprefix("tokens_$surveyid")." WHERE token IS NULL OR token=''";
		$tksr = $this->db->query($tksq);
		//$tkr = $tksr->result_array();
		//var_dump($tkr);
		$tkr = $tksr->row_array();
	    $data['query1'] = $tkr["count(*)"]." / $tkcount";
	
	    $tksq = "SELECT count(*) FROM ".$this->db->dbprefix("tokens_$surveyid")." WHERE (sent!='N' and sent<>'')";
		$tksr = $this->db->query($tksq);
		$tkr = $tksr->row_array();
	    $data['query2'] = $tkr["count(*)"]." / $tkcount";
	
	    $tksq = "SELECT count(*) FROM ".$this->db->dbprefix("tokens_$surveyid")." WHERE emailstatus = 'optOut'";
		$tksr = $this->db->query($tksq);
		$tkr = $tksr->row_array();
	    $data['query3'] = $tkr["count(*)"]." / $tkcount";
	
	    $tksq = "SELECT count(*) FROM ".$this->db->dbprefix("tokens_$surveyid")." WHERE (completed!='N' and completed<>'')";
		$tksr = $this->db->query($tksq);
		$tkr = $tksr->row_array();
	    $data['query4'] = $tkr["count(*)"]." / $tkcount";
		return $data;
	}
	
	function insertTokens($surveyid,$data)
	{
		return $this->db->insert("tokens_".$surveyid, $data); 
	}
	
	function getOldTableList ($surveyid)
	{
		$this->load->helper("database");
		return $this->db->query(db_select_tables_like($this->db->dbprefix("old\_tokens\_".$surveyid."\_%")));
	}
	
	function ctquery($surveyid,$SQLemailstatuscondition,$tokenid=false,$tokenids=false)
	{
		$ctquery = "SELECT * FROM ".$this->db->dbprefix("tokens_{$surveyid}")." WHERE ((completed ='N') or (completed='')) AND ((sent ='N') or (sent='')) AND token !='' AND email != '' $SQLemailstatuscondition";

        if ($tokenid) {$ctquery .= " AND tid='{$tokenid}'";}
        if ($tokenids) {$ctquery .= " AND tid IN ('".implode("', '", $tokenids)."')";}
		
		return $this->db->query($ctquery);
	}
	
	function emquery($surveyid,$SQLemailstatuscondition,$maxemails,$tokenid=false,$tokenids=false)
	{
        $emquery = "SELECT * FROM ".$this->db->dbprefix("tokens_{$surveyid}")." WHERE ((completed ='N') or (completed='')) AND ((sent ='N') or (sent='')) AND token !='' AND email != '' $SQLemailstatuscondition";

        if ($tokenid) {$emquery .= " and tid='{$tokenid}'";}
        if ($tokenids) {$emquery .= " AND tid IN ('".implode("', '", $tokenids)."')";}
		$this->load->helper("database");
		return db_select_limit_assoc($emquery,$maxemails);
	}
	
	function selectEmptyTokens($surveyid)
	{
		return $this->db->query("SELECT tid FROM ".$this->db->dbprefix("tokens_$surveyid")." WHERE token IS NULL OR token=''");
	}
	
	function updateToken($surveyid,$tid,$newtoken)
	{
		return $this->db->query("UPDATE ".$this->db->dbprefix("tokens_$surveyid")." SET token='$newtoken' WHERE tid=$tid");
	}
	
	function deleteToken($surveyid,$tokenid)
	{
		$dlquery = "DELETE FROM ".$this->db->dbprefix("tokens_$surveyid")." WHERE tid={$tokenid}";
		return $this->db->query($dlquery);
	}
        /*
         * This function is responsible for deletion of links in the lime_survey_links
         */
	function deleteParticipantLinks($data)
        {
            foreach($data['token_id'] as $tid)
            {
                $this->db->where('token_id',$tid);
                $this->db->where('survey_id',$data['survey_id']);
                $this->db->delete('survey_links');    
            }
        }
	function deleteTokens($surveyid,$tokenids)
	{
        $dlquery = "DELETE FROM ".$this->db->dbprefix("tokens_$surveyid")." WHERE tid IN (".implode(", ", $tokenids).")";
		return $this->db->query($dlquery);
	}
    
    function updateRecords($surveyid,$data,$condn)
    {
        return $this->db->update("tokens_$surveyid", $data, $condn);
    }
    
    
}