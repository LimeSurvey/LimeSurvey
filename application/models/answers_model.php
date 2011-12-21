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
class Answers_model extends CI_Model {

	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}

		$data = $this->db->get('answers');

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
		if($order != FALSE)
		{
			$this->db->order_by($order);
		}

		$data = $this->db->get('answers');

		return $data;
	}

    function updateSortOrder($qid,$lang)
    {
        $this->db->select('qid, code, sortorder');
        $this->db->where('qid',$qid);
        $this->db->where('language',$lang);
        $this->db->order_by("sortorder","asc");
        $data = $this->db->get('answers');

		$position=0;

        foreach($data->result_array() as $row)
        {
            $datatoupdate = array('sortorder' => $position);
            $this->db->where('qid',$row['qid']);
            $this->db->where('code',$row['code']);
            $this->db->where('sortorder',$row['sortorder']);
            $this->db->update('answers',$datatoupdate);
            $position++;
        }
    }

    function getAnswerCode($qid,$code,$lang)
    {
        $this->db->select('code, answer');
        $this->db->where('qid',$qid);
        $this->db->where('code',$code);
        $this->db->where('scale_id',0);
        $this->db->where('language',$lang);
        $data = $this->db->get('answers');

		return $data;
    }

	function oldNewInsertansTags($newsid,$oldsid)
	{
		$sql = "SELECT a.qid, a.language, a.code, a.answer from ".$this->db->dbprefix('answers')." as a INNER JOIN ".$this->db->dbprefix('questions')." as b ON a.qid=b.qid WHERE b.sid=".$newsid." AND a.answer LIKE '%{INSERTANS:".$oldsid."X%'";
    	return $this->db->query($sql);
	}

    function getCountOfCode($qid,$language)
    {
        $data = $this->db->query("SELECT count(code) AS codecount FROM ".$this->db->dbprefix('answers')." WHERE qid={$qid} AND language='{$language}'");
        return $data;
    }


	function update($data, $condition=FALSE)
	{

		if ($condition != FALSE)
		{
			$this->db->where($condition);
		}

		$this->db->update('answers', $data);

	}

    function insertRecords($data)
    {

        return $this->db->insert('answers',$data);
    }
    
    /**
     * Return array of language-specific answer codes
     * @param <type> $surveyid
     * @param <type> $qid
     * @return <type> 
     */

    function getAllAnswersForEM($surveyid=NULL,$qid=NULL,$lang=NULL)
    {
        if (!is_null($qid)) {
            $where = "a.qid = ".$qid;
        }
        else if (!is_null($surveyid)) {
            $where = "a.qid = q.qid and q.sid = ".$surveyid;
        }
        else {
            $where = "1";
        }
        if (!is_null($lang)) {
            $lang = " and a.language='".$lang."' and q.language='".$lang."'";
        }

        $query = "SELECT a.qid, a.code, a.answer, a.scale_id"
            ." FROM ".$this->db->dbprefix('answers')." AS a, ".$this->db->dbprefix('questions')." as q"
            ." WHERE ".$where
            .$lang
            ." ORDER BY qid, scale_id, sortorder";
        
        $data = $this->db->query($query);
//        log_message('debug',$this->db->last_query());

        $qans = array();

        foreach($data->result_array() as $row) {
            if (!isset($qans[$row['qid']])) {
                $qans[$row['qid']] = array();
            }
            $qans[$row['qid']][$row['scale_id'].'~'.$row['code']] = $row['answer'];
        }

        return $qans;
    }

    function getAnswerQueryBase($surveyid, $baselang)
    {
        return $this->db->select($this->db->dbprefix('answers').".*, ".$this->db->dbprefix('questions').".gid")
                 ->from($this->db->dbprefix('answers').", ".$this->db->dbprefix('questions'))
                 ->where(array(
                     $this->db->dbprefix('questions') . '.sid' => $surveyid,
                     $this->db->dbprefix('questions') . '.qid' => $this->db->dbprefix('answers') . '.qid',
                     $this->db->dbprefix('questions') . '.language' => $this->db->dbprefix('answers') . '.language',
                     $this->db->dbprefix('questions') . '.language' => $baselang
                 ))
                 ->order_by('qid,code,sortorder')
                 ->get();
    }

    function getAnswerQueryTo($surveyid, $tolang)
    {
        return $this->db->select($this->db->dbprefix('answers').".*, ".$this->db->dbprefix('questions').".gid")
                 ->from($this->db->dbprefix('answers').", ".$this->db->dbprefix('questions'))
                 ->where(array(
                     $this->db->dbprefix('questions') . '.sid' => $surveyid,
                     $this->db->dbprefix('questions') . '.qid' => $this->db->dbprefix('answers') . '.qid',
                     $this->db->dbprefix('questions') . '.language' => $this->db->dbprefix('answers') . '.language',
                     $this->db->dbprefix('questions') . '.language' => $tolang
                 ))
                 ->order_by('qid,code,sortorder')
                 ->get();
    }

}
