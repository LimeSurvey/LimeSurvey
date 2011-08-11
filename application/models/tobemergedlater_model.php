<?php
class tobemergedlater_model extends CI_Model
{
        function getShareSetting()
	{
		$this->db->where(array("uid"=>$this->session->userdata('loginID')));
		$result= $this->db->get('users');
                return $result->row();
	}
        // Resturns the full name of the user
        function getName($userid)
        {
                $this->db->select('full_name');
                $this->db->from('users');
            	$this->db->where(array("uid"=>$userid));
                $result = $this->db->get();
                return $result->row();
        }
        function getSurveyNames()
        {
                $this->db->select('surveyls_survey_id,surveyls_title');
                $this->db->from('surveys_languagesettings');
                $this->db->join('surveys','surveys_languagesettings.surveyls_survey_id = surveys.sid');           
                $this->db->where('owner_id',$this->session->userdata('loginID')); 
                //$this->db->where('usetokens','Y'); // Will be done later
                $query=$this->db->get();
                return $query->result_array();
        }
}
?>
