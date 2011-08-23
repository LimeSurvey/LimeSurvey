<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class participant_attribute_model extends CI_Model
{
    function getAttributeCount()
    {
        return $this->db->count_all_results('participant_attribute_names');
    }
    function getAttributeNames($attributeid)
    {
        $this->db->where('attribute_id',$attributeid);
        $this->db->where('lang',$this->session->userdata('adminlang'));
        $data = $this->db->get('participant_attribute_names_lang');
        return $data->result_array();
    }
    function getAttribute($attribute_id)
    {
        $data = $this->db->get_where('participant_attribute_names', array('participant_attribute_names.attribute_id' => $attribute_id));
        return $data->row();
    }
    // this is a very specific function used to get the attributes that are not present for the participant
    function getnotaddedAttributes($attributeid)
    {
        $this->db->select('*');
        $this->db->from('participant_attribute_names');
        $this->db->join('participant_attribute_names_lang', 'participant_attribute_names.attribute_id = participant_attribute_names_lang.attribute_id');
        $this->db->where_not_in('participant_attribute_names.attribute_id', $attributeid);
        $query = $this->db->get();
        return $query->result_array();
    }
    function getAttributeID()
    {
        $this->db->select('attribute_id');
        $this->db->order_by('attribute_id','desc');
        $query = $this->db->get('participant_attribute_names');
        return $query->result_array();
    }
    function getAttributeVisibleID()
    {

        $this->db->select('participant_attribute_names.*,participant_attribute_names_lang.*');
        $this->db->order_by('participant_attribute_names.attribute_id', 'desc'); 
        $this->db->where('participant_attribute_names.visible','TRUE');
        $this->db->join('participant_attribute_names_lang', 'participant_attribute_names_lang.attribute_id = participant_attribute_names.attribute_id');
        $this->db->where('participant_attribute_names_lang.lang',$this->session->userdata('adminlang'));
        $data = $this->db->get('participant_attribute_names');
     	return $data->result_array();
    }
    function getAttributesValues($attribute_id)
    {
        $data = $this->db->get_where('participant_attribute_values', array('attribute_id' => $attribute_id));
        return $data->result_array();
    }
    function getAttributeValue($participantid,$attributeid)
    {
        $data = $this->db->get_where('participant_attribute', array('participant_id' => $participantid,'attribute_id'=>$attributeid));	
        return $data->row();
    }
    function getAllAttributesValues()
    {
        $data = $this->db->get('participant_attribute_values');
     	return $data->result_array();
    }
    function getParticipantVisibleAttribute($participant_id)
    {
        $this->db->select('participant_attribute.*,participant_attribute_names.*,participant_attribute_names_lang.*');
        $this->db->from('participant_attribute');
        $this->db->order_by('participant_attribute.attribute_id','desc'); 
        $this->db->where('participant_id',$participant_id); 
        $this->db->join('participant_attribute_names_lang', 'participant_attribute.attribute_id = participant_attribute_names_lang.attribute_id');
        $this->db->join('participant_attribute_names', 'participant_attribute.attribute_id = participant_attribute_names.attribute_id','inner');
        $this->db->where('participant_attribute_names_lang.lang',$this->session->userdata('adminlang'));
        $this->db->where('lang',$this->session->userdata('adminlang'));
        $query = $this->db->get();
     	return $query->result_array();
    }
    //give the attribute type corresponding to attribute id
    function getAttributeType($attid)
    {
        $this->db->select('attribute_type');
        $data = $this->db->get_where('participant_attribute_names', array('attribute_id' => $attid));
     	return $data->row();
    }
      //give the attribute name corresponding to attribute id
    function getAttributeName($attributeid)
    {
        $this->db->select('attribute_name');
        $this->db->where('attribute_id',$attributeid);
        $this->db->where('lang',$this->session->userdata('adminlang'));
        $data = $this->db->get('participant_attribute_names_lang');
     	return $data->row();
    }
    function getAttributes()
    {
        $this->db->select('participant_attribute_names.*,participant_attribute_names_lang.*');
        $this->db->from('participant_attribute_names');
        $this->db->join('participant_attribute_names_lang', 'participant_attribute_names.attribute_id = participant_attribute_names_lang.attribute_id');
     	$this->db->where('lang',$this->session->userdata('adminlang'));
        $data=$this->db->get();
        return $data->result_array();
    }
//give all visible attributes in participant_attribute_names
    function getVisibleAttributes()
    {
        $this->db->select('participant_attribute_names.*,participant_attribute_names_lang.*');
        $this->db->order_by('participant_attribute_names.attribute_id', 'desc'); 
        $this->db->join('participant_attribute_names_lang', 'participant_attribute_names_lang.attribute_id = participant_attribute_names.attribute_id');
        $data = $this->db->get_where('participant_attribute_names', array('participant_attribute_names.visible' => "TRUE",'participant_attribute_names_lang.lang'=>$this->session->userdata('adminlang')));
     	return $data->result_array();
    }
    function getAllAttributes()
    {
        $this->db->select('participant_attribute_names.*,participant_attribute_names_lang.*');
        $this->db->order_by('participant_attribute_names.attribute_id', 'desc'); 
        $this->db->join('participant_attribute_names_lang', 'participant_attribute_names_lang.attribute_id = participant_attribute_names.attribute_id');
        $data = $this->db->get_where('participant_attribute_names', array('participant_attribute_names_lang.lang'=>$this->session->userdata('adminlang')));
     	return $data->result_array();
    }
    //give the count visible attributes in participant_attribute_names
    function getVisibleAttributeCount()
    {   
        $this->db->where('visible', "TRUE");
        $this->db->from('participant_attribute_names');
        return $this->db->count_all_results();
    }
    //updates the attribute values in participant_attribute_values
    function saveAttributeValue($data)
    {
        $this->db->update('participant_attribute_values', $data, array('attribute_id' => $data['attribute_id'],'value_id'=>$data['value_id']));
    }
    function saveParticipantAttributeValue($data)
    {
        $this->db->insert('participant_attribute',$data);
    }
    function saveAttributeVisible($attid,$visiblecondition)
    {
    
        $attribute_id = explode("_", $attid);
        $data=array('visible'=>$visiblecondition);
        if($visiblecondition == "")
        {
            $data=array('visible'=>'FALSE');
        }
        $this->db->where('attribute_id',$attribute_id[1]);
        $this->db->update('participant_attribute_names',$data); 
    }
    function editParticipantAttributeValue($data)
    {
        $this->db->where('participant_id', $data['participant_id']);
        $this->db->where('attribute_id', $data['attribute_id']);
        $query = $this->db->get('participant_attribute');
        if($query->num_rows() == 0)
        {
            $this->db->insert('participant_attribute',$data); 
        }
        else
        {
            $this->db->where('participant_id', $data['participant_id']);
            $this->db->where('attribute_id', $data['attribute_id']);
            $this->db->update('participant_attribute',$data); 
          }
   	
    }
    function saveAttribute($data)
    {
        $this->db->where('attribute_id',$data['attribute_id']);
        $this->db->update('participant_attribute_names', $data); 
    }
    function saveAttributeLanguages($data)
    {
        $query = $this->db->get_where('participant_attribute_names_lang', array('attribute_id'=>$data['attribute_id'],'lang'=>$data['lang']));
        if ($query->num_rows() == 0) 
        {
              // A record does not exist, insert one.
               $record = array('attribute_id'=>$data['attribute_id'],'attribute_name'=>$data['attribute_name'],'lang'=>$data['lang']);
               $query = $this->db->insert('participant_attribute_names_lang', $data);
        }
        else 
        {
             // A record does exist, update it.
            $query = $this->db->update('participant_attribute_names_lang',array('attribute_name'=>$data['attribute_name']), array('attribute_id'=>$data['attribute_id'],'lang'=>$data['lang']));
        }
    }
    function storeAttribute($data)
    {
        $insertnames = array('attribute_type' => $data['attribute_type'],
                            'visible' => $data['visible']);
        $this->db->insert('participant_attribute_names',$insertnames);
        $insertnameslang = array('attribute_id' => $this->db->insert_id(),
                                 'attribute_name'=>$data['attribute_name'],
                                 'lang' => $this->session->userdata('adminlang'));
        $this->db->insert('participant_attribute_names_lang',$insertnameslang);
        
    }
    //Returns the inserted id as well
    function storeAttributeCSV($data)
    {
        $insertnames = array('attribute_type' => $data['attribute_type'],
                            'visible' => $data['visible']);
        $this->db->insert('participant_attribute_names',$insertnames);
        $insertid = $this->db->insert_id();
        $insertnameslang = array('attribute_id' => $insertid,
                                 'attribute_name'=>$data['attribute_name'],
                                 'lang' => $this->session->userdata('adminlang'));
        $this->db->insert('participant_attribute_names_lang',$insertnameslang);
        return $insertid;
    }
    function delAttribute($attid)
    {
        $this->db->delete('participant_attribute_names_lang', array('attribute_id' => $attid)); 
        $this->db->delete('participant_attribute_names', array('attribute_id' => $attid)); 
        $this->db->delete('participant_attribute_values', array('attribute_id' => $attid)); 
        $this->db->delete('participant_attribute', array('attribute_id' => $attid)); 
    }
    function delAttributeValues($attid,$valid)
    {
        $this->db->delete('participant_attribute_values', array('attribute_id' => $attid,'value_id' => $valid)); 
    }
    function storeAttributeValues($data)
    {
    $this->db->insert_batch('participant_attribute_values',$data);
    }
}
?>
