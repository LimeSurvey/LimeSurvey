<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Quota_languagesettings_model extends CI_Model {

    function getAllRecords($condition=FALSE)
    {
        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }

        $data = $this->db->get('quota_languagesettings');

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

        $data = $this->db->get('quota_languagesettings');

        return $data;
    }

    /**
    * Inserts record(s) to the quota_languagesettings table
    *
    * @param array $data Records to insert
    */
    function insertRecords($data)
    {
        return $this->db->insert('quota_languagesettings',$data);
    }

}