<?php
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */
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
       if($ownerid->owner_uid != $data['share_uid'])// A check to ensure that the participant is not added to it's owner
       {
          $this->db->insert('participant_shares',$data);
       }
    }
    function updateShare($data)
    {
       $this->db->where('participant_id', $data['participant_id']);
       $this->db->where('share_uid', $data['share_uid']);
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
