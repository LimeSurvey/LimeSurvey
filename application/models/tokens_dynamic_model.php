<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Tokens_dynamic_model extends CI_Model {
	
	function getAllRecords($sid,$condition=FALSE,$limit=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		if ($limit != FALSE)
		{
			$this->db->limit($limit);	
		}
		
		$data = $this->db->get('tokens_'.$sid);
		
		return $data;
	}

	function getSomeRecords($fields,$sid,$condition=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('tokens_'.$sid);
		
		return $data;
	}
    
	function newTokensTable()
	{
	    $createtokentable=
		"tid int I NOTNULL AUTO PRIMARY,\n "
		. "firstname C(40),\n "
		. "lastname C(40),\n ";
		//MSSQL needs special treatment because of some strangeness in ADODB
	    if ($connect->databaseType == 'odbc_mssql' || $connect->databaseType == 'odbtp' || $connect->databaseType == 'mssql_n' || $connect->databaseType == 'mssqlnative')
		{
		    $createtokentable.= "email C(320),\n "
		    ."emailstatus C(300) DEFAULT 'OK',\n ";
		}
		else
		{
		    $createtokentable.= "email X(320),\n "
		    ."emailstatus X(300) DEFAULT 'OK',\n ";
		}
	
		$createtokentable.= "token C(36) ,\n "
		. "language C(25) ,\n "
		. "sent C(17) DEFAULT 'N',\n "
		. "remindersent C(17) DEFAULT 'N',\n "
		. "remindercount int I DEFAULT 0,\n "
		. "completed C(17) DEFAULT 'N',\n "
		. "usesleft I DEFAULT 1,\n"
		. "validfrom T ,\n "
		. "validuntil T ,\n "
		. "mpid I ";
	
	
		$tabname = "{$dbprefix}tokens_{$surveyid}"; # not using db_table_name as it quotes the table name (as does CreateTableSQL)
		$taboptarray = array('mysql' => 'ENGINE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci',
	                         'mysqli' => 'ENGINE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci');
		$dict = NewDataDictionary($connect);
		$sqlarray = $dict->CreateTableSQL($tabname, $createtokentable, $taboptarray);
		$execresult=$dict->ExecuteSQLArray($sqlarray, false);
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
}