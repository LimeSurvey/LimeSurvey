<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Settings_global_model extends CI_Model {
	
	function getAllRecords($condition=FALSE)
	{
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('settings_global');
		
		return $data;
	}

	function getSomeRecords($fields,$condition=FALSE)
	{
		foreach ($fields as $field)
		{
			$this->db->select($field);
		}
		if ($condition != FALSE)
		{
			$this->db->where($condition);	
		}
		
		$data = $this->db->get('settings_global');
		
		return $data;
	}
	
	function updateSetting($settingname, $settingvalue)
	{
		
		$data = array(
	               'stg_name' => $settingname,
	               'stg_value' => $settingvalue
	            );
	
		$this->db->where('stg_name', $settingname);
		$query = $this->db->get('settings_global');
		
		if($query->num_rows() == 0)
		{
			return $this->db->insert('settings_global', $data); 	
		}
		else
		{
			$this->db->where('stg_name', $settingname);
			return $this->db->update('settings_global', $data); 
		}
		
		//$usquery = "update ".db_table_name("settings_global")." set stg_value='".auto_escape($settingvalue)."' where stg_name='$settingname'";
	    //$connect->Execute($usquery);
	    //if ($connect->Affected_Rows()==0)
	    //{
	    //    $usquery = "insert into  ".db_table_name("settings_global")." (stg_value,stg_name) values('".auto_escape($settingvalue)."','$settingname')";
	    //    $connect->Execute($usquery);
	    //}
	}

}