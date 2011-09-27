<?php
class survey_url_parameters_model extends CI_Model{
    function getParametersForSurvey($iSurveyID)
    {
        $oQuery=$this->db->query("select '' as act, up.*,q.title, sq.title from {$this->db->dbprefix}survey_url_parameters up
                            left join {$this->db->dbprefix}questions q on q.qid=up.targetqid
                            left join {$this->db->dbprefix}questions sq on q.qid=up.targetsqid
                            where up.sid={$iSurveyID}");
        ;
        return $oQuery;
    }
}

?>
