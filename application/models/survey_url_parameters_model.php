<?php
class survey_url_parameters_model extends CI_Model{
    function getParametersForSurvey($iSurveyID)
    {
        $oQuery=$this->db->query("select '' as act, up.*,q.title, sq.title as sqtitle, q.question, sq.question as sqquestion from {$this->db->dbprefix}survey_url_parameters up
                            left join {$this->db->dbprefix}questions q on q.qid=up.targetqid
                            left join {$this->db->dbprefix}questions sq on q.qid=up.targetsqid
                            where up.sid={$iSurveyID}");
        ;
        return $oQuery;
    }

    function deleteRecords($aConditions)
    {
        foreach  ($aConditions as $sFieldname=>$sFieldvalue)
        {
           $this->db->where($sFieldname,$sFieldvalue);
        }
        return $this->db->delete('survey_url_parameters');// Deletes from token
    }

    function insertRecord($aData)
    {

            $this->db->insert('survey_url_parameters',$aData);
     }

}

?>
