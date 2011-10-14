<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Groups_model extends CI_Model {

    function getAllRecords($condition=FALSE)
    {
        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }

        $data = $this->db->get('groups');

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

        $data = $this->db->get('groups');

        return $data;
    }

    function getGroupID($sid,$language)
    {
        $this->db->select('gid');
        $this->db->where('sid',$sid);
        $this->db->where('language',$language);
        //$this->db->where('parent_qid',0);
        $this->db->order_by("group_order","asc");
        $data = $this->db->get('groups');
        return $data;
    }

    function getSurveyIDFromGroup($gid)
    {
        $this->db->select('sid');
        $this->db->where('gid',$gid);
        $data = $this->db->get('groups');
        $data=$data->row_array();
        return $data['sid'];
    }


    function getMaximumGroupOrder($sid,$language)
    {
        $this->db->select_max('group_order','max');
        $this->db->where('sid',$sid);
        $this->db->where('language',$language);
        $data = $this->db->get('groups');

        return $data;
    }

    function getOrderOfGroup($sid,$gid,$language)
    {
        $this->db->select('group_order');
        $this->db->where('sid',$sid);
        $this->db->where('gid',$gid);
        $this->db->where('language',$language);
        $data = $this->db->get('groups');

        return $data;
    }

    function getGroupAndID($sid,$language)
    {
        $this->db->select('gid, group_name');
        $this->db->where('sid',$sid);
        $this->db->where('language',$language);

        $this->db->order_by("group_order","asc");
        $data = $this->db->get('groups');
        //echo $data->num_rows();
        return $data;
    }

    function getGroupName($sid,$gid,$language)
    {
        $this->db->select('group_name');
        $this->db->where('sid',$sid);
        $this->db->where('gid',$gid);
        $this->db->where('language',$language);
        $data = $this->db->get('groups');

        return $data;
    }

    function updateGroupOrder($sid,$lang,$position=0)
    {
        $this->db->select('gid');
        $this->db->where('sid',$sid);
        $this->db->where('language',$lang);
        $this->db->order_by('group_order, group_name');
        $data = $this->db->get('groups');

        foreach($data->result_array() as $row)
        {
            $datatoupdate = array('group_order' => $position);
            $this->db->where('gid',$row['gid']);
            $this->db->update('groups',$datatoupdate);
            $position++;
        }
    }

    function update($data, $condition=FALSE)
    {

        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }

        return $this->db->update('groups', $data);

    }

    function insertRecords($data)
    {

        return $this->db->insert('groups',$data);
    }

    function getGroups($surveyid) {
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $query = "SELECT gid, group_name
        FROM ".$this->db->dbprefix('groups')."
        WHERE sid=? and language=?
        ORDER BY group_order";
        $result=$this->db->query($query,array($surveyid,$baselang));
        $output=array();
        foreach($result->result_array() as $row) {
            $output[$row['gid']]=$row;
        }
        return $output;
    }

}