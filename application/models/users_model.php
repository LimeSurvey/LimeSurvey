<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Users_model extends CI_Model {

    function getAllRecords($condition=FALSE)
    {
        if ($condition != FALSE)
        {
            $this->db->where($condition);
        }

        $data = $this->db->get('users');

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

        $data = $this->db->get('users');
        return $data;
    }   

	function parentAndUser()
	{
		$this->db->select('a.users_name, a.full_name, a.email, a.uid, b.users_name AS parent');
		$this->db->select('users AS a');
		$this->db->join('users AS b', 'a.parent_id = b.uid', 'left');
		$this->db->where('a.uid', $postuserid);
		$this->db->limit(1);
		return $this->db->get();
	}

    function getOTPwd($user)
    {
        $this->db->select('uid, users_name, password, one_time_pw, dateformat, full_name, htmleditormode');
        $this->db->where('users_name',$user);
        $data = $this->db->get('users',1);

        return $data;
    }

    function deleteOTPwd($user)
    {
        $data = array(
        'one_time_pw' => ''
        );
        $this->db->where('users_name',$user);
        $this->db->update('users',$data);
    }
	
    function delete($where)
    {
        $this->db->where($where);
        return (bool) $this->db->delete('users');
    }

    function insert($new_user, $new_pass,$new_full_name,$parent_user,$new_email)
    {
        $this->load->library('admin/sha256','sha256');
        $data=array($new_user, $this->sha256->hashing($new_pass),$new_full_name,$parent_user,$new_email);
        $uquery = "INSERT INTO ".$this->db->dbprefix("users")." (users_name, password,full_name,parent_id,lang,email,create_survey,create_user,delete_user,superadmin,configurator,manage_template,manage_label)
        VALUES (?, ?, ?, ?, 'auto', ?,0,0,0,0,0,0,0)";
        return $this->db->query($uquery,$data);
    }

    function update($uid,$data)
    {
        $this->db->where(array("uid"=>$uid));
        return $this->db->update('users',$data);
    }    
	
	function parent_update($where,$data)
    {
        $this->db->where($where);
        return $this->db->update('users',$data);
    }

    function updateLang($uid,$postloginlang)
    {
        $data = array(
        'lang' => $postloginlang
        );
        $this->db->where(array("uid"=>$uid));
        $this->db->update('users',$data);
    }
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
    function getID($fullname)
    {
        $this->db->select('uid');
        $this->db->from('users');
        $this->db->where(array("full_name"=>$fullname));
        $result = $this->db->get();
        return $result->row();
    }
    function updatePassword($uid,$password)
    {
        $data = array('password' => $password);
        $this->db->where(array("uid"=>$uid));
        $this->db->update('users',$data);
    }
    function insertRecords($data)
    {

        return $this->db->insert('users',$data);
    }

}