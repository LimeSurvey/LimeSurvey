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
    
    /**
     *  Get the records with ids from $iFrom to $iFrom + $iCount
     *  @param $iSurveyID       ID of the survey
     *  @param $iFrom           Beginning of the range
     *  @param $iCount          Number of rows
     *  @param $sSortCol        Sort the elements by this colum
     *  @param $sSortOrder      Order of the sort
     *  @return mixed
     */
    function getRecordsInRange($iSurveyID, $iFrom, $iCount, $sSortCol, $sSortOrder)
    {
    	  $this->db->order_by($sSortCol, $sSortOrder);
    	  return $this->db->get('survey_'.$iSurveyID, $iCount, $iFrom);
	}

    function quotaCompletedCount($iSurveyID,$querycond)
    {
        //Used by get_quotaCompletedCount()
        $querysel = "SELECT count(id) as count FROM ".db_table_name('survey_'.$iSurveyID)." WHERE ".implode(' AND ',$querycond)." "." AND submitdate IS NOT NULL";
        return $this->db->query($querysel);
    }

    /**
     *  Insert a new response
     *  @param int $iSurveyID       ID of the survey
     *  @param array $data          The response
     *  @return mixed
     */
    function insertRecords($iSurveyID, $data)
    {
        return $this->db->insert('survey_'.$iSurveyID, $data);
    }
    
    /**
     *  Get data from certain columns for a response
     *  @param array $aFields      Array containing field names
     *  @param int $iSurveyID      ID of the survey
     *  @param int $iResponseID    ID of the response
     *  @return mixed
     */
    function getFieldsForID($aFields, $iSurveyID, $iResponseID)
    {
		 foreach ($aFields as $field)
		 {
		 	  $this->db->select($field);
		 }
		 
		 $this->db->where('id', $iResponse);
		 return $this->db->get('survey_'.$iSurveyID);
    }
    
    
    /**
     * Get a response from the database
     * @param int $iSurveyID       ID of the survey
     * @param int $iResponseID     ID of the response
     * @return mixed
     */
    function getResponse($iSurveyID, $iResponseID)
    {
		 $this->db->where('id', $iResponseID);    
		 return $this->db->get('survey_'.$iSurveyID);	
    }

    
    function deleteResponse($iSurveyID, $iResponseID)
    {
    	  $this->db->where('id', $iResponseID);
    	  return $this->db->delete('survey_'.$iSurveyID);
    }
    
    function getResponseCount($iSurveyID)
    {
        return $this->db->count_all_results('survey_'.$iSurveyID);	
	}
	
	
    /**
     * Update a response
     * @param int $iSurveyID    ID of the survey
     * @param int $iResponseID  ID of the response
     * @param array $aData      Array containg response data
     * @return mixed
     */
	function updateResponse($iSurveyID, $iResponseID, $aData)
	{
	    $this->db->where('id', $iResponseID);
	 	$this->db->update('survey_'.$iSurveyID, $aData);
	}
}
