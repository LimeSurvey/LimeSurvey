<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Quota_members_model extends CI_Model {

    function getAllRecords($condition=FALSE)
    {
        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }

        $data = $this->db->get('quota_members');

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

        $data = $this->db->get('quota_members');

        return $data;
    }

    /**
    * Inserts record(s) to the quota_members table
    *
    * @param array $data Records to insert
    */
    function insertRecords($data)
    {
        return $this->db->insert('quota_members',$data);
    }

}