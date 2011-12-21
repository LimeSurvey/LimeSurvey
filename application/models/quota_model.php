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
class Quota_model extends CI_Model {

    function getAllRecords($condition=FALSE)
    {
        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }

        $data = $this->db->get('quota');

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

        $data = $this->db->get('quota');

        return $data;
    }

    function deleteQuota($condition=false,$recursive=true)
    {
        if ($recursive)
        {
            $this->db->select('id');
            $this->db->where($condition);
            $oResult = $this->db->get('quota');
            foreach ($oResult->result_array() as $aRow)
            {
                $this->db->delete('quota_languagesettings', array('quotals_quota_id' => $aRow['id']));
                $this->db->delete('quota_members', array('quota_id' => $aRow['id']));
            }
        }

        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }
        $this->db->delete('quota');
    }

    function getQuotaInformation($surveyid,$language,$quotaid)
    {
        //Used by getQuotaInformation helper
        $query = "SELECT * FROM ".$this->db->dbprefix('quota').", ".$this->db->dbprefix('quota_languagesettings')."
        WHERE ".$this->db->dbprefix('quota').".id = ".$this->db->dbprefix('quota_languagesettings').".quotals_quota_id
        AND sid='".$surveyid."'
        AND quotals_language='".$language."'";
        if ($quotaid != 'all')
        {
            $query .= " AND id=$quotaid";
        }
        return $this->db->query($query);
    }

    /**
    * Inserts record(s) to the quota table
    *
    * @param array $data Records to insert
    */
    function insertRecords($data)
    {
        return $this->db->insert('quota',$data);
    }

}
