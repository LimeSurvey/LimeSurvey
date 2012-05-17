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
*	$Id$
*/

class User extends CActiveRecord
{
    /**
    * Returns the static model of Settings table
    *
    * @static
    * @access public
    * @param string $class
    * @return CActiveRecord
    */
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
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
                $criteria->addCondition($item.'='.Yii::app()->db->quoteValue($value));
            }
        }

        $data = $this->findAll($criteria);

        return $data;
    }
    function parentAndUser($postuserid)
    {
        $user = Yii::app()->db->createCommand()
        ->select('a.users_name, a.full_name, a.email, a.uid,  b.users_name AS parent')
        ->limit(1)
        ->where('a.uid = :postuserid')
        ->from("{{users}} a")
        ->leftJoin('{{users}} AS b', 'a.parent_id = b.uid')
        ->bindParam(":postuserid", $postuserid, PDO::PARAM_INT)
        ->queryRow();
        return $user;
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
    public static function insertUser($new_user, $new_pass,$new_full_name,$parent_user,$new_email)
    {
        $oUser = new self;
        $oUser->users_name = $new_user;
        $oUser->password = hash('sha256', $new_pass);
        $oUser->full_name = $new_full_name;
        $oUser->parent_id = $parent_user;
        $oUser->lang = 'auto';
        $oUser->email = $new_email;
        if ($oUser->save())
        {
            return $oUser->uid;

        }
        else{
            return false;
        }
    }


    /**
    * Delete user
    *
    * @param int $iUserID The User ID to delete
    * @return mixed
    */
    function deleteUser($iUserID)
    {
        $iUserID= (int)$iUserID;
        $iRecordsAffected = Yii::app()->db->createCommand()->from('{{users}}')->delete('{{users}}', "uid={$iUserID}");
        return (bool) $iRecordsAffected;
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
        return Yii::app()->db->createCommand()->select('full_name')->from('{{users}}')->where("uid = :userid")->bindParam(":userid", $userid, PDO::PARAM_INT)->queryAll();
    }
    public function getuidfromparentid($parentid)
    {
        return Yii::app()->db->createCommand()->select('uid')->from('{{users}}')->where('parent_id = :parent_id')->bindParam(":parent_id", $parentid, PDO::PARAM_INT)->queryRow();
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
        $this->db->where(array("full_name"=>Yii::app()->db->quoteValue($fullname)));
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
        $data = array('password' => Yii::app()->db->quoteValue($password));
        //$this->db->where(array("uid"=>$uid));
        //$this->db->update('users',$data);
        $this->updateByPk($uid, $data);

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

    /**
    * Returns User ID common in Survey_Permissions and User_in_groups
    *
    * @access public
    * @return CDbDataReader Object
    */
    public function getCommonUID($surveyid, $postusergroupid)
    {
        $query2 = "SELECT b.uid FROM (SELECT uid FROM {{survey_permissions}} WHERE sid = :surveyid) AS c RIGHT JOIN {{user_in_groups}} AS b ON b.uid = c.uid WHERE c.uid IS NULL AND b.ugid = :postugid";
        return Yii::app()->db->createCommand($query2)->bindParam(":surveyid", $surveyid, PDO::PARAM_INT)->bindParam(":postugid", $postusergroupid, PDO::PARAM_INT)->query(); //Checked
    }
}
