<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class participant_shares_model extends CI_Model
{
    function storeParticipantShare($data)
    {
       $pdata = $this->db->get_where('participants', array('participant_id' => $data['participant_id']));
       $ownerid = $pdata->row();
       if($ownerid->owner_uid != $data['shared_uid'])// A check to ensure that the participant is not added to it's owner
       {
          $this->db->insert('participant_shares',$data);
       }
    }
    function updateShare($data)
    {
       $this->db->where('participant_id', $data['participant_id']);
       $this->db->where('shared_uid', $data['shared_uid']);
       $this->db->update('participant_shares', $data); 
    }
    function deleteRow($rows)
    {
	// Converting the comma seperated id's to an array to delete multiple rows
	$rowid=explode(",",$rows['id']);
	foreach($rowid as $row)
		{
		        $this->db->where('participant_id',$row);			
                        $this->db->delete('participant_shares'); 
               }
		
}
}
?>
