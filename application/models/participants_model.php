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
class participants_model extends CI_Model
{
/*
 * funcion for generation of unique id
 */
function gen_uuid()
{
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
    mt_rand( 0, 0xffff ),
    mt_rand( 0, 0x0fff ) | 0x4000,
    mt_rand( 0, 0x3fff ) | 0x8000,
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}
/*
 * This function is responsible for adding the participant to the database
 * Parameters : participant data
 * Return Data : none
 */
function insertParticipant($data)
{
    $this->db->insert('participants',$data);
}
/*
 * This function updates the data edited in the jqgrid
 * Parameters : data that is edited
 * Return Data : None
*/
function updateRow($data)
{
    $this->db->where('participant_id',$data['participant_id']);
	$this->db->update('participants',$data);
}
function deleteParticipantTokenAnswer($rows)
{
    $rowid=explode(",",$rows);
    //$rowid = array('243148a0-bf56-4ee1-a6d2-a1f1cb5243d5');
    foreach($rowid as $row)
    {
            $this->db->where('participant_id',$row);
            $tokens = $this->db->get('survey_links');
            foreach($tokens->result_array() as $key => $value)
            {
                $this->db->where('participant_id',$row);
                $this->db->delete('participants'); //Delete from participants
                if($this->db->table_exists('tokens_'.$value['survey_id']))
                {
                    $this->db->select('token');
                    $this->db->where('participant_id',$value['participant_id']);
                    $tokenid = $this->db->get('tokens_'.$value['survey_id']);
                    $token = $tokenid->row();
                    if($this->db->table_exists('survey_'.$value['survey_id']))
                    {
                        if(!empty($token->token))
                        {
                            $this->db->where('token',$tokenid->row()->token);
                            $gettoken=$this->db->get('survey_'.$value['survey_id']);
                            $this->db->where('token',$gettoken->row()->token);
                            $this->db->delete('survey_'.$value['survey_id']);
                        }
                    }
                  $this->db->where('participant_id',$value['participant_id']);
                  $this->db->delete('tokens_'.$value['survey_id']);// Deletes from token
               }
            }
     }

}
function deleteParticipantToken($rows)
{
    $rowid=explode(",",$rows);
    foreach($rowid as $row)
    {
        $this->db->where('participant_id',$row);
	$tokens = $this->db->get('survey_links');
        foreach($tokens->result_array() as $key => $value)
        {
            if($this->db->table_exists('tokens_'.$value['survey_id']))
            {
                $this->db->where('participant_id',$value['participant_id']);
                $this->db->delete('tokens_'.$value['survey_id']);
            }
        }
        $this->db->where('participant_id',$row);
	$this->db->delete('participants');
        $this->db->where('participant_id',$row);
	$this->db->delete('survey_links');
        $this->db->where('participant_id',$row);
	$this->db->delete('participant_attribute');
    }

}
/*
 * This function deletes the row marked in the navigator
 * Parameters : row id's
 * Return Data : None
*/
function deleteParticipant($rows)
{
	// Converting the comma seperated id's to an array to delete multiple rows
    $rowid=explode(",",$rows);
    foreach($rowid as $row)
    {
        $this->db->where('participant_id',$row);
		$this->db->delete('participants');
        $this->db->where('participant_id',$row);
		$this->db->delete('survey_links');
        $this->db->where('participant_id',$row);
		$this->db->delete('participant_attribute');
    }

}
/*
 * This function is responsible for adding the participant to the database from the CSV upload
 * Parameters : participant data
 * Return Data : none
*/
function insertParticipantCSV($data)
{
    $insertData = array('participant_id' => $data['participant_id'],
                        'firstname' => $data['firstname'],
                        'lastname' => $data['lastname'],
                        'email' => $data['email'],
                        'language' => $data['language'],
                        'blacklisted' => $data['blacklisted'],
                        'owner_uid' => $data['owner_uid'] );
    $this->db->insert('participants',$insertData);
}
/* This function returns the active record containing the participants
 * Paramteres : None
 * Return Data : Active Record
*/
function getParticipants($page,$limit)
{
    $start = $limit*$page - $limit;
    $data = $this->db->get('participants',$limit,$start);
    return $data;
}
function getParticipantswithoutlimit()
{
    $data = $this->db->get('participants');
    return $data;
}

function getParticipantsCount()
{
     return $this->db->count_all_results('participants');
}
function getParticipantsSearchMultiple($condition,$page,$limit)
{
   $i=0;
   $j=1;
   $tobedonelater =array();
   $this->load->library('subquery');
   $start = $limit*$page - $limit;
   $this->db->from('participants');
   $con= count($condition);
   while($i < $con){
   if($i<3){
        $i+=3;
        if($condition[1]=='equal')
        {
            if(is_numeric($condition[0]))
            {
                $newsub = $j;
                $newsub = $this->subquery->start_subquery('where_in');
                $newsub->select('participant_attribute.participant_id');
                $newsub->from('participant_attribute');
                $newsub->where('participant_attribute.attribute_id',$condition[0]);
                $newsub->where('participant_attribute.value',$condition[2]);
                $this->subquery->end_subquery('participant_id');
                $j++;
            }
            else
            {
               $this->db->where($condition[0],$condition[2]);
             }
         }
         else if($condition[1]=='contains')
         {
            if(is_numeric($condition[0]))
            {
                $newsub = $j;
                $newsub = $this->subquery->start_subquery('where_in');
                $newsub->select('participant_id');
                $newsub->from('participant_attribute');
                $newsub->where('attribute_id',$condition[0]);
                $newsub->where('value LIKE','%'.$condition[2].'%');
                $this->subquery->end_subquery('participant_id');
                $j++;
            }
            else
            {
                $this->db->where($condition[0].' LIKE','%'.$condition[2].'%');
            }
         }
         else if($condition[1]=='notequal')
         {
            if(is_numeric($condition[0]))
            {
                $newsub = $j;
                $newsub = $this->subquery->start_subquery('where_in');
                $newsub->select('participant_id');
                $newsub->from('participant_attribute');
                $newsub->where('attribute_id',$condition[0]);
                $newsub->where_not_in('value',$condition[2]);
                $this->subquery->end_subquery('participant_id');
                $j++;
            }
            else
            {
               $this->db->where_not_in($condition[0],$condition[2]);

            }
         }
         else if($condition[1]=='notcontains')
         {
            if(is_numeric($condition[0]))
            {
                $newsub = $j;
                $newsub = $this->subquery->start_subquery('where_in');
                $newsub->select('participant_id');
                $newsub->from('participant_attribute');
                $newsub->where('attribute_id',$condition[0]);
                $newsub->where('value NOT LIKE','%'.$condition[2].'%');
                $this->subquery->end_subquery('participant_id');
                $j++;
            }
            else
            {
               $this->db->where($condition[0].' NOT LIKE','%'.$condition[2].'%');

            }
         }
         else if($condition[1]=='greaterthan')
         {
            if(is_numeric($condition[0]))
            {
                $newsub = $j;
                $newsub = $this->subquery->start_subquery('where_in');
                $newsub->select('participant_id');
                $newsub->from('participant_attribute');
                $newsub->where('attribute_id',$condition[0]);
                $newsub->where('value >',$condition[2]);
                $this->subquery->end_subquery('participant_id');
                $j++;
            }
            else
            {
               $this->db->where($condition[0].' >',$condition[2]);

            }
         }
         else if($condition[1]=='lessthan')
         {
            if(is_numeric($condition[0]))
            {
                $newsub = $j;
                $newsub = $this->subquery->start_subquery('where_in');
                $newsub->select('participant_id');
                $newsub->from('participant_attribute');
                $newsub->where('attribute_id',$condition[0]);
                $newsub->where('value <',$condition[2]);
                $this->subquery->end_subquery('participant_id');
                $j++;
            }
            else
            {
               $this->db->where($condition[0].' <',$condition[2]);

            }
         }
        }

        else if($condition[$i]!='')
        {
           if($condition[$i+2]=='equal')
           {
               if(is_numeric($condition[$i+1]))
                {
                    if($condition[$i]=='and')
                    {

                        $newsub = $j;
                        $newsub = $this->subquery->start_subquery('where_in');
                        $newsub->select('participant_id');
                        $newsub->from('participant_attribute');
                        $newsub->where('attribute_id', $condition[$i+1]);
                        $newsub->where('value',$condition[$i+3]);
                        $this->subquery->end_subquery('participant_attribute');
                        $j++;
                    }
                    else
                    {
                        $tobedonelater[$condition[$i+1]][0] = $condition[$i+2];
                        $tobedonelater[$condition[$i+1]][1] = $condition[$i+3];
                    }
               }
              else
                {
                    if($condition[$i]=='and')
                    {
                        $this->db->where($condition[$i+1],$condition[$i+3]);
                    }
                    else
                    {
                        $this->db->or_where($condition[$i+1],$condition[$i+3]);
                    }
                }
            }
            else if($condition[$i+2]=='contains')
            {
              if(is_numeric($condition[$i+1]))
                {
                    if($condition[$i]=='and')
                    {
                        $newsub = $j;
                        $newsub = $this->subquery->start_subquery('where_in');
                        $newsub->select('participant_id');
                        $newsub->from('participant_attribute');
                        $newsub->where('attribute_id', $condition[$i+1]);
                        $newsub->where('value LIKE','%'.$condition[$i+3].'%');
                        $this->subquery->end_subquery('participant_id');
                        $j++;
                    }
                    else
                    {
                        $tobedonelater[$condition[$i+1]][0] = $condition[$i+2];
                        $tobedonelater[$condition[$i+1]][1] = $condition[$i+3];
                     }
               }
              else
                {
                    if($condition[$i]=='and')
                    {

                        $this->db->where($condition[$i+1].' LIKE','%'.$condition[$i+3].'%');
                    }
                    else
                    {
                        $this->db->or_where($condition[$i+1].' LIKE','%'.$condition[$i+3].'%');
                    }
                }
            }
            else if($condition[$i+2]=='notequal')
            {
              if(is_numeric($condition[$i+1]))
                {
                    if($condition[$i]=='and')
                    {
                        $newsub = $j;
                        $newsub = $this->subquery->start_subquery('where_in');
                        $newsub->select('participant_id');
                        $newsub->from('participant_attribute');
                        $newsub->where('attribute_id', $condition[$i+1]);
                        $newsub->where_not_in('value',$condition[$i+3]);
                        $this->subquery->end_subquery('participant_id');
                        $j++;
                    }
                    else
                    {
                        $tobedonelater[$condition[$i+1]][0] = $condition[$i+2];
                        $tobedonelater[$condition[$i+1]][1] = $condition[$i+3];
                    }
               }
              else
                {
                    if($condition[$i]=='and')
                    {
                        $this->db->where_not_in($condition[$i+1],$condition[$i+3]);
                    }
                    else
                    {
                        $this->db->or_where_not_in($condition[$i+1],$condition[$i+3]);
                    }
                }
            }
           else if($condition[$i+2]=='notcontains')
            {
              if(is_numeric($condition[$i+1]))
                {
                    if($condition[$i]=='and')
                    {
                        $newsub = $j;
                        $newsub = $this->subquery->start_subquery('where_in');
                        $newsub->select('participant_id');
                        $newsub->from('participant_attribute');
                        $newsub->where('attribute_id', $condition[$i+1]);
                        $newsub->where('value NOT LIKE','%'.$condition[$i+3].'%');
                        $this->subquery->end_subquery('participant_id');
                        $j++;
                    }
                    else
                    {
                        $tobedonelater[$condition[$i+1]][0] = $condition[$i+2];
                        $tobedonelater[$condition[$i+1]][1] = $condition[$i+3];
                    }
               }
              else
                {
                    if($condition[$i]=='and')
                    {
                        $this->db->where($condition[$i+1].' NOT LIKE','%'.$condition[$i+3].'%');
                    }
                    else
                    {
                        $this->db->or_where($condition[$i+1].' NOT LIKE','%'.$condition[$i+3].'%');
                    }
                }
            }
            else if($condition[$i+2]=='greaterthan')
            {
              if(is_numeric($condition[$i+1]))
                {
                    if($condition[$i]=='and')
                    {
                        $newsub = $j;
                        $newsub = $this->subquery->start_subquery('where_in');
                        $newsub->select('participant_id');
                        $newsub->from('participant_attribute');
                        $newsub->where('attribute_id', $condition[$i+1]);
                        $newsub->where('value >',$condition[$i+3]);
                        $this->subquery->end_subquery('participant_id');
                        $j++;
                    }
                    else
                    {
                        $tobedonelater[$condition[$i+1]][0] = $condition[$i+2];
                        $tobedonelater[$condition[$i+1]][1] = $condition[$i+3];
                     }
               }
              else
                {
                    if($condition[$i]=='and')
                    {
                        $this->db->where($condition[$i+1].' >',$condition[$i+3]);
                    }
                    else
                    {
                        $this->db->or_where($condition[$i+1].' >',$condition[$i+3]);
                    }
                }
            }
            else if($condition[$i+2]=='lessthan')
            {
              if(is_numeric($condition[$i+1]))
                {
                    if($condition[$i]=='and')
                    {
                        $newsub = $j;
                        $newsub = $this->subquery->start_subquery('where_in');
                        $newsub->select('participant_id');
                        $newsub->from('participant_attribute');
                        $newsub->where('attribute_id', $condition[$i+1]);
                        $newsub->where('value <',$condition[$i+3]);
                        $this->subquery->end_subquery('participant_id');
                        $j++;
                    }
                    else
                    {
                         $tobedonelater[$condition[$i+1]][0] = $condition[$i+2];
                         $tobedonelater[$condition[$i+1]][1] = $condition[$i+3];
                    }
               }
              else
                {
                    if($condition[$i]=='and')
                    {
                        $this->db->where($condition[$i+1].' <',$condition[$i+3].'%');
                    }
                    else
                    {
                        $this->db->or_where($condition[$i+1].' <',$condition[$i+3]);
                    }
                }
            }
            $i=$i+4;
        }
        else{$i=$i+4;}
    }
    if($page == 0 && $limit == 0)
    {
        $data= $this->db->get();
    }
    else
    {
        $this->db->limit($limit,$start);
        $data = $this->db->get();
    }

    $otherdata = $data->result_array();
    if(!empty($tobedonelater))
    {
    $this->db->select('participant_id');
    $this->db->from('participant_attribute');
    $this->db->distinct();
    foreach($tobedonelater as $key=>$value)
    {
        if($value[0] == 'equal')
        {
            $this->db->or_where('attribute_id', $key);
            $this->db->where('value',$value[1]);
        }
        if($value[0] == 'contains')
        {
            $this->db->or_where('attribute_id', $key);
            $this->db->where('value LIKE','%'.$value[1].'%');
        }
        if($value[0] == 'notequal')
        {
            $this->db->or_where('attribute_id', $key);
            $this->db->where('value !=',$value[1]);
        }
        if($value[0] == 'greaterthan')
        {
            $this->db->or_where('attribute_id', $key);
            $this->db->where('value >',$value[1]);
        }
        if($value[0] == 'lessthan')
        {
            $this->db->or_where('attribute_id', $key);
            $this->db->where('value <',$value[1]);
        }
    }
    $data=$this->db->get();
    $participant_id = $data->result_array();
    $this->db->select('*');
    $this->db->from('participants');
    foreach($participant_id as $key=>$value)
    {
        $this->db->or_where('participant_id',$value['participant_id']);
    }
    if($page == 0 && $limit == 0)
    {
    $data=$this->db->get();
    }
    else
    {
        $this->db->limit($limit,$start);
        $data = $this->db->get();
    }


    $orddata = $data->result_array();
    $finalanswer = array_merge($otherdata,$orddata);
    return $finalanswer;
    }
    else
    {
     return $otherdata;
    }

}
function is_owner($participant_id)
{
    $userid=$this->session->userdata('loginID');
    $this->db->select('participant_id');
    $this->db->where('participant_id',$participant_id);
    $this->db->where('owner_uid',$userid);
    $is_owner = $this->db->get('participants');
    //$is_owner->num_rows();
    $this->db->select('participant_id');
    $this->db->where('participant_id',$participant_id);
    $this->db->where('share_uid',$userid);
    $is_shared = $this->db->get('participant_shares');
    if($is_shared->num_rows() || $is_shared->num_rows())
    {
        return true;
    }
    else
    {
        return false;
    }

}
function getParticipantsSearch($condition,$page,$limit)
{
    $start = $limit*$page - $limit;
    if($condition[1]=='equal')
        {
          if($condition[0]=='surveys')
          {
              $resultarray = array();
              if($page == 0 && $limit == 0)
              {
              $data = $this->db->get('participants');
              }
                  else
              {
                  $data = $this->db->get('participants',$limit,$start);
              }
              foreach($data->result_array() as $key=>$value)
              {
                  $this->db->where('participant_id',$value['participant_id']);
                  $this->db->from('survey_links');
                  $count=$this->db->count_all_results();
                  if($count == $condition[2])
                  {
                     array_push($resultarray,$value);
                  }
              }
              foreach($resultarray as $key=>$value)
              {

          }
          }
          else if($condition[0]=='owner_name')
          {
                     $this->db->select('uid');
                $this->db->where('full_name',$condition[2]);
                $userid = $this->db->get('users');
                $uid = $userid->row();
                $this->db->where('owner_uid',$uid->uid);
                if($page == 0 && $limit == 0)
                {
                $data=$this->db->get('participants');
                }
                else
                {
                    $data = $this->db->get('participants',$limit,$start);
                }
                return $data->result_array();
          }
          else if(is_numeric($condition[0]))
          {
                $this->db->select('participant_attribute.*,participants.*');
                $this->db->from('participant_attribute');
                $this->db->join('participants', 'participant_attribute.participant_id = participants.participant_id');
                $this->db->where('participant_attribute.attribute_id',$condition[0]);
                $this->db->where('participant_attribute.value',$condition[2]);
                if($page == 0 && $limit == 0)
                {
                $data=$this->db->get();
                }
                else
                {
                      $this->db->limit($limit,$start);
                      $data = $this->db->get();
                }
                return $data->result_array();
          }
          else
          {
            $this->db->where($condition[0],$condition[2]);
            if($page == 0 && $limit == 0)
              {
            $data=$this->db->get('participants');
              }
              else
              {
                  $data = $this->db->get('participants',$limit,$start);
              }
            return $data->result_array();
          }
        }
        else if($condition[1]=='contains')
        {

          if($condition[0]=='surveys')
          {
              $resultarray = array();
              if($page == 0 && $limit == 0)
              {
              $data = $this->db->get('participants');
              }
              else
              {
                  $data = $this->db->get('participants',$limit,$start);
              }
              foreach($data->result_array() as $key=>$value)
              {
                  $this->db->where('participant_id',$value['participant_id']);
                  $this->db->from('survey_links');
                  $count=$this->db->count_all_results();
                  if($count == $condition[2])
                  {
                     array_push($resultarray,$value);
                  }
              }
              return $resultarray;
          }

          else if($condition[0]=='owner_name')
          {

                $this->db->select('uid');
                $this->db->like('full_name',$condition[2]);
                $userid = $this->db->get('users');
                $uid = $userid->row();
                $this->db->where('owner_uid',$uid->uid);
                $this->db->order_by("lastname", "asc");
                if($page == 0 && $limit == 0)
                  {
                $data=$this->db->get('participants');
                  }
                  else
                  {
                  $data = $this->db->get('participants',$limit,$start);
                    }
                return $data->result_array();
          }
          else if(is_numeric($condition[0]))
          {
                $this->db->select('participant_attribute.*,participants.*');
                $this->db->from('participant_attribute');
                $this->db->join('participants', 'participant_attribute.participant_id = participants.participant_id');
                $this->db->where('participant_attribute.attribute_id',$condition[0]);
                $this->db->like('participant_attribute.value',$condition[2]);
                if($page == 0 && $limit == 0)
                {
                $data=$this->db->get();
                }
                else
                {
                      $this->db->limit($limit,$start);
                      $data = $this->db->get();
                }
                return $data->result_array();
          }
          else
          {
                $this->db->like($condition[0],$condition[2]);
                if($page == 0 && $limit == 0)
                  {
                $data=$this->db->get('participants');
                  }
                  else
                  {
                    $data = $this->db->get('participants',$limit,$start);
                  }
                return $data->result_array();
          }

        }
        else if($condition[1]=='notequal')
        {
            if($condition[0]=='surveys')
          {
              $resultarray = array();

              if($page == 0 && $limit == 0)
                  {
              $data = $this->db->get('participants');
                  }
                  else
                  {
                  $data = $this->db->get('participants',$limit,$start);
                    }
              foreach($data->result_array() as $key=>$value)
              {
                  $this->db->where('participant_id',$value['participant_id']);
                  $this->db->from('survey_links');
                  $count=$this->db->count_all_results();
                  if($count != $condition[2])
                  {
                     array_push($resultarray,$value);
                  }
              }
              return $resultarray;
          }
          else if($condition[0]=='owner_name')
          {

                $this->db->select('uid');
                $this->db->where_not_in('full_name',$condition[2]);
                $userid = $this->db->get('users');
                $uid = $userid->row();
                $this->db->where('owner_uid',$uid->uid);
                if($page == 0 && $limit == 0)
                  {
                $data=$this->db->get('participants');
                  }
                  else
                  {
                  $data = $this->db->get('participants',$limit,$start);
                    }
                return $data->result_array();
          }
          else if(is_numeric($condition[0]))
          {
                $this->db->select('participant_attribute.*,participants.*');
                $this->db->from('participant_attribute');
                $this->db->join('participants', 'participant_attribute.participant_id = participants.participant_id');
                $this->db->where('participant_attribute.attribute_id',$condition[0]);
                $this->db->where_not_in('participant_attribute.value',$condition[2]);
                if($page == 0 && $limit == 0)
                  {
                    $data = $this->db->get('participants');
                  }
                  else
                  {
                      $this->db->limit($limit,$start);
                    $data = $this->db->get('participants');
                  }
                return $data->result_array();
          }
          else
          {
            $this->db->where_not_in($condition[0],$condition[2]);
            if($page == 0 && $limit == 0)
                  {
            $data=$this->db->get('participants');
                  }
                  else
                  {
                  $data = $this->db->get('participants',$limit,$start);
                    }
            return $data->result_array();
          }
        }
        else if($condition[1]=='notcontains')
        {
            if($condition[0]=='surveys')
          {
              $resultarray = array();
              $this->db->order_by("lastname", "asc");
              if($page == 0 && $limit == 0)
                  {
              $data = $this->db->get('participants');
                  }
                  else
                  {
                  $data = $this->db->get('participants',$limit,$start);
                    }
              foreach($data->result_array() as $key=>$value)
              {
                  $this->db->where('participant_id',$value['participant_id']);
                  $this->db->from('survey_links');
                  $count=$this->db->count_all_results();
                  if($count != $condition[2])
                  {
                     array_push($resultarray,$value);
                  }
              }
              return $resultarray;
          }
          else if($condition[0]=='owner_name')
          {
                $this->db->select('uid');
                $this->db->not_like('full_name',$condition[2]);
                $userid = $this->db->get('users');
                $uid = $userid->row();
                $this->db->where('owner_uid',$uid->uid);
                if($page == 0 && $limit == 0)
                  {
                $data=$this->db->get('participants');
                  }
                  else
                  {
                  $data = $this->db->get('participants',$limit,$start);
                    }
                return $data->result_array();
          }
          else if(is_numeric($condition[0]))
          {
                $this->db->select('participant_attribute.*,participants.*');
                $this->db->from('participant_attribute');
                $this->db->join('participants', 'participant_attribute.participant_id = participants.participant_id');
                $this->db->where('participant_attribute.attribute_id',$condition[0]);
                $this->db->not_like('participant_attribute.value',$condition[2]);
                if($page == 0 && $limit == 0)
                  {
                    $data = $this->db->get('participants');
                  }
                  else
                  {
                      $this->db->limit($limit,$start);
                  $data = $this->db->get('participants',$limit,$start);
                    }
                return $data->result_array();
          }
          else
          {
            $this->db->not_like($condition[0],$condition[2]);
            if($page == 0 && $limit == 0)
                  {
            $data=$this->db->get('participants');
                  }
                  else
                  {
                  $data = $this->db->get('participants',$limit,$start);
                    }
            return $data->result_array();
          }
        }
        else if($condition[1]=='greaterthan')
        {
          if($condition[0]=='surveys')
          {
              $resultarray = array();
              if($page == 0 && $limit == 0)
                  {
              $data = $this->db->get('participants');
                  }
                  else
                  {
                  $data = $this->db->get('participants',$limit,$start);
                    }
              foreach($data->result_array() as $key=>$value)
              {
                  $this->db->where('participant_id',$value['participant_id']);
                  $this->db->from('survey_links');
                  $count=$this->db->count_all_results();
                  if($count > $condition[2])
                  {
                     array_push($resultarray,$value);
                  }
              }
              return $resultarray;
          }
          else if($condition[0]=='owner_name')
          {
                $this->db->select('uid');
                $this->db->where('full_name >',$condition[2]);
                $userid = $this->db->get('users');
                $uid = $userid->row();
                $this->db->where('owner_uid',$uid->uid);
                $this->db->order_by("lastname", "asc");
                if($page == 0 && $limit == 0)
                  {
                $data=$this->db->get('participants');
                  }
                  else
                  {
                  $data = $this->db->get('participants',$limit,$start);
                    }
                return $data->result_array();
          }
          else if(is_numeric($condition[0]))
          {
                $this->db->select('participant_attribute.*,participants.*');
                $this->db->from('participant_attribute');
                $this->db->join('participants', 'participant_attribute.participant_id = participants.participant_id');
                $this->db->where('participant_attribute.attribute_id',$condition[0]);
                $this->db->where('participant_attribute.value >',$condition[2]);
                if($page == 0 && $limit == 0)
                  {
                    $data = $this->db->get('participants');
                  }
                  else
                  {
                      $this->db->limit($limit,$start);
                  $data = $this->db->get('participants');
                    }
                return $data->result_array();
          }
          else
          {
            $this->db->where($condition[0].' >',$condition[2]);
            $this->db->order_by("lastname", "asc");
            if($page == 0 && $limit == 0)
                  {
            $data=$this->db->get('participants');
                  }
                  else
                  {
                  $data = $this->db->get('participants',$limit,$start);
                    }
            return $data->result_array();
          }
        }
       else if($condition[1]=='lessthan')
        {
          if($condition[0]=='surveys')
          {
              $resultarray = array();

              if($page == 0 && $limit == 0)
                  {
              $data = $this->db->get('participants');
                  }
                  else
                  {
                  $data = $this->db->get('participants',$limit,$start);
                    }
              foreach($data->result_array() as $key=>$value)
              {
                  $this->db->where('participant_id',$value['participant_id']);
                  $this->db->from('survey_links');
                  $count=$this->db->count_all_results();
                  if($count < $condition[2])
                  {
                     array_push($resultarray,$value);
                  }
              }
              return $resultarray;
          }
          else if($condition[0]=='owner_name')
          {

                $this->db->select('uid');
                $this->db->where('full_name',$condition[2]);
                $userid = $this->db->get('users');
                $uid = $userid->row();
                $this->db->where('owner_uid <',$uid->uid);

                if($page == 0 && $limit == 0)
                  {
                $data=$this->db->get('participants');
                  }
                  else
                  {
                  $data = $this->db->get('participants',$limit,$start);
                    }
                return $data->result_array();
          }
          else if(is_numeric($condition[0]))
          {
                $this->db->select('participant_attribute.*,participants.*');
                $this->db->from('participant_attribute');
                $this->db->join('participants', 'participant_attribute.participant_id = participants.participant_id');
                $this->db->where('participant_attribute.attribute_id',$condition[0]);
                $this->db->not_like('participant_attribute.value <',$condition[2]);
              if($page == 0 && $limit == 0)
              {
                $data = $this->db->get('participants');
              }
              else
              {
                $this->db->limit($limit,$start);
                $data = $this->db->get('participants');
              }
                return $data->result_array();
          }
          else
          {
            $this->db->where($condition[0].' <',$condition[2]);
            if($page == 0 && $limit == 0)
            {
            $data=$this->db->get('participants');
            }
            else
            {
                $data = $this->db->get('participants',$limit,$start);
            }
            return $data->result_array();
          }

        }
}
/*
 * This function combines the shared participant and the central participant
 * table and searches for any reference of owner id in the combined record
 * of the two tables
*/
function getParticipantsSharedCount($userid)
{
    $this->db->select('participants.*, participant_shares.*');
    $this->db->from('participants');
    $this->db->join('participant_shares','participant_shares.participant_id = participants.participant_id');
    $this->db->where('owner_uid', $userid);
    $query=$this->db->get();
    return $query->num_rows();
}
/*
 * This function combines the shared participant and the central participant
 * table and searches for any reference of owner id or shared owner id in the
 * rows and return the count for the summary page
*/
function getParticipantsOwnerCount($userid)
{
    $this->db->select('participants.*,participant_shares.can_edit');
    $this->db->from('participants');
    $this->db->join('participant_shares',' participants.participant_id=participant_shares.participant_id','left');
    $this->db->where('owner_uid',$userid);
    $this->db->or_where('share_uid', $userid);
    $this->db->group_by('participants.participant_id');
    $query=$this->db->get();
    return $query->num_rows();
}
/*
 * This function combines the shared participant and the central participant
 * table and searches for any reference of owner id or shared owner id in the rows
*/
function getParticipantsOwner($userid)
{
    $this->db->select('participants.*,participant_shares.can_edit');
    $this->db->from('participants');
    $this->db->join('participant_shares',' participants.participant_id=participant_shares.participant_id','left');
    $this->db->where('owner_uid',$userid);
    $this->db->or_where('share_uid', $userid);
    $this->db->group_by('participants.participant_id');
    $query=$this->db->get();
    return $query;
}
/*
 * This funciton is responsible for showing all the participant's shared by a particular user based on the user id
 */
function getParticipantShared($userid)
{
    $this->db->select('participants.*, participant_shares.*');
    $this->db->from('participants');
    $this->db->join('participant_shares','participant_shares.participant_id = participants.participant_id');
    $this->db->where('owner_uid', $userid);
    $query=$this->db->get();
    return $query;
}
/*
 * This funciton is responsible for showing all the participant's shared to the superadmin
 */
function getParticipantSharedAll()
{
    $this->db->select('participants.*, participant_shares.*');
    $this->db->from('participants');
    $this->db->join('participant_shares','participant_shares.participant_id = participants.participant_id');
    $query=$this->db->get();
    return $query;
}
/*
 * This function returns the count of the number of rows in table mentioned
 * Paramteres : None
 * Return Data : Number of rows
*/
function getBlacklistedCount($userid)
{
    $this->db->where('owner_uid',$userid);
    $this->db->where('blacklisted','Y');
    $count=$this->db->count_all_results('participants');
	return $count;
}
/*
 * This function returns the count of the number of surveys with which participant is linked
 * Paramteres : None
 * Return Data : Number of rows
*/
function getSurveyCount($participant_id)
{
    $this->db->where('participant_id',$participant_id);
    $count=$this->db->count_all_results('survey_links');
    return $count;
}
/*
 * This function returns the count of the number of rows in table mentioned
 * Paramteres : None
 * Return Data : Number of rows
*/
function getParticipantCount()
{
    $count=$this->db->count_all_results('participants');
	return $count;
}
/*
 * This function returns the count of the number of participants
 * ouwned by that particular ownerid
 * Paramteres : None
 * Return Data : Number of rows
*/
function getParticipantOwnedCount($ownerid)
{
    $this->db->where('owner_uid',$ownerid);
    $count=$this->db->count_all_results('participants');
	return $count;
}
/*
 * The purpose of this function is to check for duplicate in participants
*/
function checkforDuplicate($fields)
{
    $query = $this->db->get_where('participants',$fields);
    if ($query->num_rows() > 0){return true;}
    else {return false;}
}
/*
 * This function is responsible for checking for any exsisting record in the token table and if not copy the participant to it
 */
function copyToCentral($surveyid,$newarr,$mapped)
{
    $tokenid = $this->session->userdata('participantid');
    $duplicate=0;
    $sucessfull=0;
    $writearray = array();
    $attid = array();
    $pid="";
    $tokenfieldnames = array_values($this->db->list_fields("tokens_$surveyid"));
    $tokenattributefieldnames=array_filter($tokenfieldnames,'filterforattributes');
    foreach($tokenattributefieldnames as $key=>$value) //mapping the automatically mapped
    {
       if($value[10]=='c')
        {
           $attid = substr($value,15);
           $mapped[$attid] = $value;
        }
    }
    if(!empty($newarr))
    {
       foreach ($newarr as $key=>$value) //creating new central attribute
            {
                $insertnames = array('attribute_type' => 'TB','visible' => 'N');
                $this->db->insert('participant_attribute_names',$insertnames);
                $attid[$key] = $this->db->insert_id();
                $insertnameslang = array('attribute_id' => $this->db->insert_id(),
                                         'attribute_name'=>urldecode($key),
                                         'lang' => $this->session->userdata('adminlang'));
                $this->db->insert('participant_attribute_names_lang',$insertnameslang);
            }
    }
    foreach($tokenid as $key=>$tid)
    {
        if(is_numeric($tid) && $tid!="")
        {
            $this->db->select('participant_id,firstname,lastname,email,language,blacklisted');
            $this->db->where('tid',$tid);
            $participantdata=$this->db->get('tokens_'.$surveyid);
            $tobeinserted = $participantdata->row();
            $query = $this->db->get_where('participants', array('firstname' => $tobeinserted->firstname,'lastname' => $tobeinserted->lastname,'email' => $tobeinserted->email));
            if ($query->num_rows() > 0)
            {
                $duplicate++;
            }
            else
            {
                if(empty($tobeinserted->blacklisted))
                {
                    $black = 'N';
                }
                else
                {
                    $black = $tobeinserted->blacklisted;
                }
                if(!empty($tobeinserted->participant_id))
                {
                    $writearray = array('participant_id'=>$tobeinserted->participant_id,'firstname'=>$tobeinserted->firstname,'lastname' => $tobeinserted->lastname,'email' => $tobeinserted->email,'language' =>$tobeinserted->language,'blacklisted'=>$black,'owner_uid' => $this->session->userdata('loginID'));
                }
                else
                {
                    $writearray = array('participant_id'=>$this->gen_uuid(),'firstname'=>$tobeinserted->firstname,'lastname' => $tobeinserted->lastname,'email' => $tobeinserted->email,'language' =>$tobeinserted->language,'blacklisted' =>$black,'owner_uid' => $this->session->userdata('loginID'));
                }
                $pid = $writearray['participant_id'];
                $this->db->insert('participants',$writearray);
                if(!empty($newarr))
                {
                foreach ($newarr as $key=>$value)
                    {
                        $this->db->select($value);
                        $this->db->where('tid',$tid);
                        $val = $this->db->get('tokens_'.$surveyid);
                        $value2 = $val->row();
                        $data = array(  'participant_id' => $pid,
                                        'value' => $value2->$value,
                                        'attribute_id' =>  $attid[$key]);
                        if(!empty($data['value']))
                        {
                        $this->db->insert('participant_attribute', $data);
                        }
                    }
                }
                if(!empty($mapped))
                {
                foreach($mapped as $cpdbatt => $tatt)
                {
                     $this->db->select($tatt);
                     $this->db->where('tid',$tid);
                     $val = $this->db->get('tokens_'.$surveyid);
                     $value = $val->row();
                     $data = array( 'participant_id' => $pid,
                                    'value' => $value->$tatt,
                                    'attribute_id' => $cpdbatt );
                     if(!empty($data['value']))
                     {
                        $this->db->insert('participant_attribute', $data);
                     }
                }
                }
                $sucessfull++;
            }
            }
        }
        if(!empty($newarr))
        {
            foreach ($newarr as $key=>$value)
            {
                $this->load->dbforge();
                $newname = 'attribute_cpdb_'.$attid[$key];
                $fields = array($value => array('name' => $newname,'type' => 'TEXT'));
                $this->dbforge->modify_column('tokens_'.$surveyid, $fields);
                $this->db->select('attributedescriptions');
                $this->db->where(array("sid"=>$surveyid));
                $previousatt=$this->db->get('surveys');
                $previouseattribute = $previousatt->row();
                $newstring = str_replace($value,$newname,$previouseattribute->attributedescriptions);
                $this->db->where(array("sid"=>$surveyid));
                $this->db->update('surveys',array("attributedescriptions"=>$newstring)); // load description in the surveys table
            }
        }
        if(!empty($mapped))
        {
            foreach($mapped as $cpdbatt => $tatt)
            {
                if($tatt[10]!='c')
                {
                    $this->load->dbforge();
                    $newname = 'attribute_cpdb_'.$cpdbatt;
                    $fields = array($tatt => array('name' => $newname,'type' => 'TEXT'));
                    $this->dbforge->modify_column('tokens_'.$surveyid, $fields);
                    $this->db->select('attributedescriptions');
                    $this->db->where(array("sid"=>$surveyid));
                    $previousatt=$this->db->get('surveys');
                    $previouseattribute = $previousatt->row();
                    $newstring = str_replace($tatt,$newname,$previouseattribute->attributedescriptions);
                    $this->db->where(array("sid"=>$surveyid));
                    $this->db->update('surveys',array("attributedescriptions"=>$newstring)); // load description in the surveys table
                }
            }
        }
        $returndata = array('success'=>$sucessfull,'duplicate'=>$duplicate);
        return $returndata;
}
function copytosurveyatt($surveyid,$mapped,$newcreate,$participantid)
{
    $duplicate=0;
    $sucessfull=0;
    $participantid = explode(",",$participantid);
    if($participantid[0]=="")
    {
        $participantid = array_slice($participantid,1);
    }
    $number2add=sanitize_int(count($newcreate));
    $tokenfieldnames = array_values($this->db->list_fields("tokens_$surveyid"));
    $tokenattributefieldnames=array_filter($tokenfieldnames,'filterforattributes');
    foreach($tokenattributefieldnames as $key=>$value)
    {
       if($value[10]=='c')
        {
           $attid = substr($value,15);
           $mapped[$value] = $attid;
        }
    }
    $attributesadded=array();
    $attributeidadded=array();
    $fieldcontents="";
    if(!empty($newcreate))
    {
    foreach ($newcreate as $key=>$value)
    {
        $fields['attribute_cpdb_'.$value]=array('type' => 'VARCHAR','constraint' => '255');
        $this->db->select('participant_attribute_names_lang.attribute_name');
        $this->db->from('participant_attribute_names');
        $this->db->join('participant_attribute_names_lang', 'participant_attribute_names.attribute_id = participant_attribute_names_lang.attribute_id');
     	$this->db->where('participant_attribute_names.attribute_id',$value);
        $this->db->where('lang',$this->session->userdata('adminlang'));
        $attname=$this->db->get();
        $attributename = $attname->row();
        $tokenattributefieldnames[]='attribute_cpdb_'.$value;
        $fieldcontents.= 'attribute_cpdb_'.$value.'='.$attributename->attribute_name."\n";
        array_push($attributeidadded,'attribute_cpdb_'.$value);
        array_push($attributesadded,$value);
    }
    $this->db->select('attributedescriptions');
    $this->db->where(array("sid"=>$surveyid));
    $previousatt=$this->db->get('surveys');
    $previouseattribute = $previousatt->row();
    $this->db->where(array("sid"=>$surveyid));
    $this->db->update('surveys',array("attributedescriptions"=>$previouseattribute->attributedescriptions.$fieldcontents)); // load description in the surveys table
    $this->load->dbforge();
    $this->dbforge->add_column("tokens_$surveyid", $fields); // add columns in token's table
    }
    //Function for pushing associative array
    foreach($participantid as $key=>$participant)
    {
        $writearray = array();
        $this->db->select('firstname,lastname,email,language','blacklisted');
        $this->db->where('participant_id',$participant);
        $participantdata=$this->db->get('participants');
        $tobeinserted = $participantdata->row();
        $query = $this->db->get_where('tokens_'.$surveyid, array('firstname' => $tobeinserted->firstname,'firstname' => $tobeinserted->firstname,'lastname' => $tobeinserted->lastname,'email' => $tobeinserted->email));
        if ($query->num_rows() > 0)
        {
            $duplicate++;
        }
        else
        {
            $writearray = array('participant_id'=>$participant,'firstname'=>$tobeinserted->firstname,'lastname' => $tobeinserted->lastname,'email' => $tobeinserted->email,'emailstatus'=>'OK','language' =>$tobeinserted->language);
            $this->db->insert('tokens_'.$surveyid, $writearray);
            $insertedtokenid = $this->db->insert_id();
            $this->load->helper('date');
            $format = 'DATE_W3C';
            $time = time();
            $data = array(
            'participant_id' => $participant,
            'token_id' => $insertedtokenid ,
            'survey_id' => $surveyid,
            'date_created' =>  standard_date($format,$time));
            $this->db->insert('survey_links', $data);
            if(!empty($newcreate))
            {
            $numberofattributes = count($attributesadded);
            for($a=0;$a<$numberofattributes;$a++)
            {
                $this->db->select('value');
                $this->db->where('participant_id',$participant);
                $this->db->where('attribute_id',$attributesadded[$a]);
                $val = $this->db->get('participant_attribute');
                if($val->num_rows>0)
                {
                    $value=$val->row();
                    $data=array($attributeidadded[$a]=>$value->value);
                    if(!empty($value))
                    {
                        $this->db->update("tokens_$surveyid", $data, array('participant_id' => $participant));
                    }
                }
            }
            }
            if(!empty($mapped))
            {
                foreach($mapped as $key=>$value)
                {
                    $this->db->select('value');
                    $this->db->where('participant_id',$participant);
                    $this->db->where('attribute_id',$value);
                    $val = $this->db->get('participant_attribute');
                    $value=$val->row();
                    if(isset($value->value))
                    {
                        $data=array($key=>$value->value);
                        $this->db->update("tokens_$surveyid", $data, array('participant_id' => $participant));
                    }

                }
            }
            $sucessfull++;
        }
    }
    $returndata = array('success'=>$sucessfull,'duplicate'=>$duplicate);
    return $returndata;
}
function blacklistparticipantglobal($data)
{
    $this->db->where('participant_id',$data['participant_id']);
    $this->db->get('participants');
    $is_participant = $this->db->affected_rows();
    $this->db->where('participant_id',$data['participant_id']);
    $this->db->update('participants', $data);
    $is_updated = $this->db->affected_rows();
    $result = array('is_participant' => $is_participant,
                    'is_updated' => $is_updated    );
    return $result;

}
function blacklistparticipantlocal($data,$survey_id,$tid)
{
    $is_survey = $this->db->table_exists('tokens_'.$survey_id);
    if($is_survey)
    {
        $this->db->where('tid',$tid);
        $this->db->get('tokens_'.$survey_id);
        $is_participant = $this->db->affected_rows();
        $this->db->where('tid',$tid);
        $this->db->update('tokens_'.$survey_id, $data);
        $is_updated = $this->db->affected_rows();
        $result = array('is_participant' => $is_participant,
                        'is_updated' => $is_updated,
                        'is_survey' => $is_survey);
    }
    else
    {
        $is_survey = $this->db->table_exists('tokens_'.$survey_id);
        $is_participant = "";
        $is_updated="";
        $result = array('is_participant' => $is_participant,
                        'is_updated' => $is_updated,
                        'is_survey' => $is_survey);
    }
    return $result;
}
}

?>
