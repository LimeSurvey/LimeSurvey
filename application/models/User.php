<?php
/*
   * LimeSurvey
   * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
   * All rights reserved.
   * License: GNU/GPL License v2 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
   *	$Id: LSCI_Controller.php 11188 2011-10-17 14:28:02Z mot3 $
*/

class User extends CActiveRecord
{
	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
	 * @return CActiveRecord
	 */
	public static function model()
	{
		return parent::model(__CLASS__);
	}

	/**
	 * Returns the setting's table name to be used by the model
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{users}}';
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'uid';
	}

	/**
	 * Defines several rules for this table
	 *
	 * @access public
	 * @return array
	 */
	public function rules()
	{
		return array(
			array('users_name, password, email, full_name', 'required'),
			array('email', 'email'),
		);
	}
	
	/**
	 * Returns all users
	 *
	 * @access public
	 * @return string
	 */
    public function getAllRecords($condition=FALSE)
    {
		$criteria = new CDbCriteria;

        if ($condition != FALSE)
        {	
		    foreach ($condition as $item => $value)
			{
				$criteria->addCondition($item.'="'.$value.'"');
			}
        }
		
		$data = $this->findAll($criteria);

        return $data;
    }
	
	/**
	 * Returns users meeting given condition
	 *
	 * @access public
	 * @return string
	 */
    public function getSomeRecords($fields,$condition=FALSE)
    {
		$criteria = new CDbCriteria;

        if ($condition != FALSE)
        {	
		    foreach ($condition as $item => $value)
			{
				$criteria->addCondition($item.'="'.$value.'"');
			}
        }
		
		$data = $this->findAll($criteria);

        return $data;
    }
	
	/**
	 * Returns onetime password
	 *
	 * @access public
	 * @return string
	 */
    public function getOTPwd($user)
    {
        $this->db->select('uid, users_name, password, one_time_pw, dateformat, full_name, htmleditormode');
        $this->db->where('users_name',$user);
        $data = $this->db->get('users',1);

        return $data;
    }
	
	/**
	 * Deletes onetime password
	 *
	 * @access public
	 * @return string
	 */
    public function deleteOTPwd($user)
    {
        $data = array(
        'one_time_pw' => ''
        );
        $this->db->where('users_name',$user);
        $this->db->update('users',$data);
    }
	
	/**
	 * Creates new user
	 *
	 * @access public
	 * @return string
	 */
    public function insert($new_user, $new_pass,$new_full_name,$parent_user,$new_email)
    {
        $this->load->library('admin/sha256','sha256');
        $data=array($new_user, $this->sha256->hashing($new_pass),$new_full_name,$parent_user,$new_email);
        $uquery = "INSERT INTO ".$this->db->dbprefix("users")." (users_name, password,full_name,parent_id,lang,email,create_survey,create_user,delete_user,superadmin,configurator,manage_template,manage_label)
        VALUES (?, ?, ?, ?, 'auto', ?,0,0,0,0,0,0,0)";
        return $this->db->query($uquery,$data);
    }
	
	/**
	 * Updates user
	 *
	 * @access public
	 * @return string
	 */
    public function update($uid,$data)
    {
        $this->db->where(array("uid"=>$uid));
        return $this->db->update('users',$data);
    }
	
	/**
	 * Updates user language
	 *
	 * @access public
	 * @return string
	 */
    public function updateLang($uid,$postloginlang)
    {
        $data = array(
        'lang' => $postloginlang
        );
        $this->db->where(array("uid"=>$uid));
        $this->db->update('users',$data);
    }
		
	/**
	 * Returns user share settings
	 *
	 * @access public
	 * @return string
	 */
    public function getShareSetting()
    {
        $this->db->where(array("uid"=>$this->session->userdata('loginID')));
        $result= $this->db->get('users');
        return $result->row();
    }
    	
	/**
	 * Returns full name of user
	 *
	 * @access public
	 * @return string
	 */
    public function getName($userid)
    {
        return Yii::app()->db->createCommand()->select('full_name')->from('{{users}}')->where("uid = $userid")->queryAll();
    }
		
	/**
	 * Returns id of user
	 *
	 * @access public
	 * @return string
	 */
    public function getID($fullname)
    {
        $this->db->select('uid');
        $this->db->from('users');
        $this->db->where(array("full_name"=>$fullname));
        $result = $this->db->get();
        return $result->row();
    }
		
	/**
	 * Updates user password
	 *
	 * @access public
	 * @return string
	 */
    public function updatePassword($uid,$password)
    {
        $data = array('password' => $password);
        $this->db->where(array("uid"=>$uid));
        $this->db->update('users',$data);
    }
		
	/**
	 * Adds user record
	 *
	 * @access public
	 * @return string
	 */
    public function insertRecords($data)
    {

        return $this->db->insert('users',$data);
    }
}
