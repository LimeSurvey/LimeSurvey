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

    function totalRecords($iSurveyID)
    {
        $tksq = "SELECT count(tid) FROM ".$this->db->dbprefix("tokens_$iSurveyID");
        $tksr = $this->db->query($tksq);
        $tkr = $tksr->row_array();
        return $tkr["count(tid)"];
    }

    function tokensSummary($iSurveyID)
    {

        // SEE HOW MANY RECORDS ARE IN THE TOKEN TABLE
        $tksq = "SELECT count(tid) FROM ".$this->db->dbprefix("tokens_$iSurveyID");
        $tksr = $this->db->query($tksq);
        $tkr = $tksr->row_array();
        $tkcount = $tkr["count(tid)"];
        $data['tkcount']=$tkcount;

        $tksq = "SELECT count(*) FROM ".$this->db->dbprefix("tokens_$iSurveyID")." WHERE token IS NULL OR token=''";
        $tksr = $this->db->query($tksq);
        $tkr = $tksr->row_array();
        $data['query1'] = $tkr["count(*)"]." / $tkcount";

        $tksq = "SELECT count(*) FROM ".$this->db->dbprefix("tokens_$iSurveyID")." WHERE (sent!='N' and sent<>'')";
        $tksr = $this->db->query($tksq);
        $tkr = $tksr->row_array();
        $data['query2'] = $tkr["count(*)"]." / $tkcount";

        $tksq = "SELECT count(*) FROM ".$this->db->dbprefix("tokens_$iSurveyID")." WHERE emailstatus = 'optOut'";
        $tksr = $this->db->query($tksq);
        $tkr = $tksr->row_array();
        $data['query3'] = $tkr["count(*)"]." / $tkcount";

        $tksq = "SELECT count(*) FROM ".$this->db->dbprefix("tokens_$iSurveyID")." WHERE (completed!='N' and completed<>'')";
        $tksr = $this->db->query($tksq);
        $tkr = $tksr->row_array();
        $data['query4'] = $tkr["count(*)"]." / $tkcount";
        return $data;
    }

    function insertToken($iSurveyID,$data)
    {
        return $this->db->insert("tokens_".$iSurveyID, $data);
    }

    /**
    * Inserts one or more tokens
    *
    * @param mixed $iSurveyID
    * @param mixed $data
    */
    function insertTokens($iSurveyID,$data)
    {
        return $this->db->insert_batch("tokens_".$iSurveyID, $data);
    }

    function getOldTableList ($iSurveyID)
    {
        $this->load->helper("database");
        return $this->db->query(db_select_tables_like($this->db->dbprefix("old\_tokens\_".$iSurveyID."\_%")));
    }

    function ctquery($iSurveyID,$SQLemailstatuscondition,$tokenid=false,$tokenids=false)
    {
        $ctquery = "SELECT * FROM ".$this->db->dbprefix("tokens_{$iSurveyID}")." WHERE ((completed ='N') or (completed='')) AND ((sent ='N') or (sent='')) AND token !='' AND email != '' $SQLemailstatuscondition";

        if ($tokenid) {$ctquery .= " AND tid='{$tokenid}'";}
        if ($tokenids) {$ctquery .= " AND tid IN ('".implode("', '", $tokenids)."')";}

        return $this->db->query($ctquery);
    }

    function emquery($iSurveyID,$SQLemailstatuscondition,$maxemails,$tokenid=false,$tokenids=false)
    {
        $emquery = "SELECT * FROM ".$this->db->dbprefix("tokens_{$iSurveyID}")." WHERE ((completed ='N') or (completed='')) AND ((sent ='N') or (sent='')) AND token !='' AND email != '' $SQLemailstatuscondition";

        if ($tokenid) {$emquery .= " and tid='{$tokenid}'";}
        if ($tokenids) {$emquery .= " AND tid IN ('".implode("', '", $tokenids)."')";}
        Yii::app()->loadHelper("database");
        return db_select_limit_assoc($emquery,$maxemails);
    }

    function selectEmptyTokens($iSurveyID)
    {
        return $this->db->query("SELECT tid FROM ".$this->db->dbprefix("tokens_$iSurveyID")." WHERE token IS NULL OR token=''");
    }

    function updateToken($iSurveyID,$tid,$newtoken)
    {
        return $this->db->query("UPDATE ".$this->db->dbprefix("tokens_$iSurveyID")." SET token='$newtoken' WHERE tid=$tid");
    }

    function deleteToken($iSurveyID,$tokenid)
    {
        $dlquery = "DELETE FROM ".$this->db->dbprefix("tokens_$iSurveyID")." WHERE tid={$tokenid}";
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
    function deleteRecords($iSurveyID,$tokenids)
    {
        $dlquery = "DELETE FROM ".$this->db->dbprefix("tokens_$iSurveyID")." WHERE tid IN (".implode(", ", $tokenids).")";
        return $this->db->query($dlquery);
    }

    function updateRecords($iSurveyID,$data,$condn)
    {
        return $this->db->update("tokens_$iSurveyID", $data, $condn);
    }

    /**
    * Create token for all token table entries not having a token key set already
    *
    * @param mixed $iSurveyID
    * @return integer Number of entries that have been a token key assigned
    */
    function createTokens($iSurveyID)
    {
        //get token length from survey settings
        $this->load->model("surveys_model");
        $tlresult = $this->surveys_model->getSomeRecords(array("tokenlength"),array("sid"=>$iSurveyID));
        $tlrow = $tlresult->row_array();
        $iTokenLength = $tlrow['tokenlength'];

        //if tokenlength is not set or there are other problems use the default value (15)
        if(!isset($iTokenLength) || $iTokenLength == '')
        {
            $iTokenLength = 15;
        }

        // select all existing tokens
        $ntresult = $this->getSomeRecords(array("token"),$iSurveyID,FALSE,"token");
        foreach ($ntresult->result_array() as $tkrow)
        {
            $existingtokens[$tkrow['token']]=null;
        }
        $newtokencount = 0;
        $tkresult = $this->selectEmptyTokens($iSurveyID);
        foreach ($tkresult->result_array() as $tkrow)
        {
            $bIsValidToken = false;
            while ($bIsValidToken == false)
            {
                $newtoken = sRandomChars($iTokenLength);
                if (!isset($existingtokens[$newtoken])) {
                    $bIsValidToken = true;
                    $existingtokens[$newtoken]=null;
                }
            }
            $itresult = $this->updateToken($iSurveyID,$tkrow['tid'],$newtoken);
            $newtokencount++;
        }
        return $newtokencount;

    }

    /**
    * Create a new token key for a certain token entry
    *
    * @param mixed $iSurveyID
    * @param mixed $iTokenID
    * @return string
    */
    function createToken($iSurveyID, $iTokenID)
    {
        //get token length from survey settings
        $this->load->model("surveys_model");
        $tlresult = $this->surveys_model->getSomeRecords(array("tokenlength"),array("sid"=>$iSurveyID));
        $tlrow = $tlresult->row_array();
        $iTokenLength = $tlrow['tokenlength'];

        //if tokenlength is not set or there are other problems use the default value (15)
        if(!isset($iTokenLength) || $iTokenLength == '')
        {
            $iTokenLength = 15;
        }

        $bIsValidToken = false;
        while ($bIsValidToken == false)
        {
            $newtoken = sRandomChars($iTokenLength);
            $ntresult = $this->getSomeRecords(array("token"),$iSurveyID,array('token'=>$newtoken),"token");
            if ($ntresult->num_rows()==0)
            {
                $this->db->update("tokens_$iSurveyID", array('token'=>$newtoken), array('tid'=>$iTokenID));
                $bIsValidToken=true;
            }
        }
        return $newtoken;

    }


}
