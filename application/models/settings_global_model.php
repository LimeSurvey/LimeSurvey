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
class Settings_global_model extends CI_Model {

    function getAllRecords($condition=FALSE)
    {
        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }

        $data = $this->db->get('settings_global');

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

        $data = $this->db->get('settings_global');

        return $data;
    }

    function updateSetting($settingname, $settingvalue)
    {

        $data = array(
            'stg_name' => $settingname,
            'stg_value' => $settingvalue
        );

        $this->db->where('stg_name', $settingname);
        $query = $this->db->get('settings_global');

        if($query->num_rows() == 0)
        {
            return $this->db->insert('settings_global', $data);
        }
        else
        {
            $this->db->where('stg_name', $settingname);
            return $this->db->update('settings_global', $data);
        }

    }

}
