<?php
class survey_url_parameters_model extends CI_Model{
    function getParametersForSurvey($iSurveyID)
    {
        $this->db->select('id,sid,parameter,targetqid')->from('survey_url_parameters')->where('sid',$iSurveyID);
        $query = $this->db->get();
        return $query;
    }
}

?>
