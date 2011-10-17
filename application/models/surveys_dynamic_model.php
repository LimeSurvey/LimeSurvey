<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Surveys_dynamic_model extends CI_Model {

    function getAllRecords($iSurveyID,$condition=FALSE)
    {
        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }

        $data = $this->db->get('survey_'.$iSurveyID);

        return $data;
    }

    function getSomeRecords($fields,$iSurveyID,$condition=FALSE,$order=FALSE)
    {
        foreach ($fields as $field)
        {
            $this->db->select($field);
        }
        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }
        if ($order != FALSE)
        {
            $this->db->order_by($order);
        }
        $data = $this->db->get('survey_'.$iSurveyID);

        return $data;
    }

    function quotaCompletedCount($iSurveyID,$querycond)
    {
        //Used by get_quotaCompletedCount()
        $querysel = "SELECT count(id) as count FROM ".db_table_name('survey_'.$iSurveyID)." WHERE ".implode(' AND ',$querycond)." "." AND submitdate IS NOT NULL";
        return $this->db->query($querysel);
    }

    function insertRecords($iSurveyID,$data)
    {
        return $this->db->insert('survey_'.$iSurveyID, $data);
    }


}