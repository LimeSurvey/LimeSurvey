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
class Failed_login_attempts_model extends CI_Model {

    function getAllRecords($condition=FALSE)
    {
        if ($condition != FALSE)
        {
            $this->db->where($condition);
    }

        $data = $this->db->get('failed_login_attempts');

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

        $data = $this->db->get('failed_login_attempts');

        return $data;
    }

    function deleteAttempts($ip) {
        $this->db->where('ip', $ip);
        return $this->db->delete('failed_login_attempts');
    }

    /**
    * Check if an IP address is allowed to login or not
    *
    * @param string $sIPAddress IP Address to check
    * @return boolean Returns true if the user is blocked
    */
    function isLockedOut($sIPAddress)
    {
        $isLockedOut = false;
        $this->db->where('number_attempts >',$this->config->item("maxLoginAttempt"));
        $this->db->where('ip',$sIPAddress);
        $oQuery = $this->db->get('failed_login_attempts');
        if ($oQuery->num_rows()>0)
        {
            $row = $oQuery->row_array();
            $lastattempt=strtotime($row['last_attempt']);
            if (time() > $lastattempt + $this->config->item("timeOutTime")){
                $isLockedOut = false;
                $this->db->where('ip',$sIPAddress);
                $this->db->delete('failed_login_attempts');
            }
            else
            {
                $isLockedOut = true;
            }
        }
        return $isLockedOut;
    }

    /**
    * This function removes obsolete login attempts
    * TODO
    */
    function cleanOutOldAttempts()
    {
        // this where select whole part
        //$this->db->where('now() > (last_attempt+'.$this->config->item("timeOutTime").')');
        //return $this->db->delete('failed_login_attempts');
    }


    function addAttempt($ip)
    {
        $timestamp = date("Y-m-d H:i:s");
        $this->db->where('ip', $ip);
        $oData=$this->db->get('failed_login_attempts');
        if ($oData->num_rows()>0)
        {
            $query = $this->db->query("UPDATE ".$this->db->dbprefix('failed_login_attempts')
            ." SET number_attempts=number_attempts+1, last_attempt = '".$timestamp."' WHERE ip='".$ip."'");
        }
        else
        {
            $query = $this->db->query("INSERT INTO ".$this->db->dbprefix('failed_login_attempts') . "(ip, number_attempts,last_attempt)"
                ." VALUES('".$ip."',1,'".$timestamp."')");
        }

        return $query;
    }

}
