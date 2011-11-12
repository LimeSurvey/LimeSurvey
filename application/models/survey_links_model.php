<?php
class survey_links_model extends CI_Model{
    function getLinkInfo($participantid)
    {
        $this->db->select('token_id,survey_id,date_created')->from('survey_links')->where('participant_id',$participantid);
        $query = $this->db->get();
        return $query;
    }
}

?>
