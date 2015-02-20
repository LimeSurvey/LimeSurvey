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
*/

class User extends LSActiveRecord implements ls\pluginmanager\iUser
{
    /**
     *
     * @var \ls\pluginmanager\iAuthenticationPlugin
     */
    protected $_authenticator;
    /**
    * @var string Default value for user language
    */
    public $lang='auto';

    
    /**
    * Returns the static model of Settings table
    *
    * @static
    * @access public
    * @param string $class
    * @return User
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
    * Defines several rules for this table
    *
    * @access public
    * @return array
    */
    public function rules()
    {
        return array(
        array('users_name, password, email', 'required'),
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
    /**
    * 
    * 
    * @param mixed $postuserid
    */
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
	 * This method is invoked before saving a record (after validation, if any).
	 * The default implementation raises the {@link onBeforeSave} event.
	 * You may override this method to do any preparation work for record saving.
	 * Use {@link isNewRecord} to determine whether the saving is
	 * for inserting or updating record.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 * @return boolean whether the saving should be executed. Defaults to true.
	 */
    public function beforeSave()
    {
         // Postgres delivers bytea fields as streams :-o - if this is not done it looks like Postgres saves something unexpected
        if (gettype($this->password)=='resource')
        {
            $this->password=stream_get_contents($this->password,-1,0); 
        }
        return parent::beforeSave();
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
        $oUser=$this->findByPk($iUserID);
        return (bool) $oUser->delete();
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

    public function getName() {
        return $this->full_name;
    }
    
    public function getUserName() {
        return $this->users_name;
    }
    public function getuidfromparentid($parentid)
    {
        return Yii::app()->db->createCommand()->select('uid')->from('{{users}}')->where('parent_id = :parent_id')->bindParam(":parent_id", $parentid, PDO::PARAM_INT)->queryRow();
    }
    /**
     * Returns id of user
     */
    public function getId()
    {
        return $this->primaryKey;
    }

    /**
    * Updates user password hash
    * 
    * @param int $iUserID The User ID
    * @param string $sPassword The clear text password
    */
    public function updatePassword($iUserID, $sPassword)
    {
        return $this->updateByPk($iUserID, array('password' => hash('sha256', $sPassword)));
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
        $query2 = "SELECT b.uid FROM (SELECT uid FROM {{permissions}} WHERE entity_id = :surveyid AND entity = 'survey') AS c RIGHT JOIN {{user_in_groups}} AS b ON b.uid = c.uid WHERE c.uid IS NULL AND b.ugid = :postugid";
        return Yii::app()->db->createCommand($query2)->bindParam(":surveyid", $surveyid, PDO::PARAM_INT)->bindParam(":postugid", $postusergroupid, PDO::PARAM_INT)->query(); //Checked
    }


	public function relations()
	{
		return array(
			'permissions' => array(self::HAS_MANY, 'Permission', 'uid')
		);
	}
    
    public function validatePassword($password) {
        // Check hash type.
        if (strlen($this->password) == 64 && hash('sha256', $password) == $this->password) {
            // Password is correct but needs rehashing.
            $this->password = CPasswordHelper::hashPassword($password);
            $this->save();
        }
        return CPasswordHelper::verifyPassword($password, $this->password);
    }

    public function getLanguage() {
        return $this->lang;
    }

    public function getSettings() {
        $settings = $this->getProfileSettings();
        unset($settings['oldPassword']);
        return $settings;
    }
    public function setSettings($settings) {
        $errors = [];
        if ($settings['password1'] != $settings['password2']) {
            $errors['password1'][] = 'New password and repeat password must match.';
            $errors['password2'][] = 'New password and repeat password must match.';
        }
        
        if (empty($errors)) {
            // Update password.
            if (!empty($settings['password1'])) {
                $this->password = CPasswordHelper::hashPassword($settings['password1']);
            }
            $this->email = $settings['email'];
            $this->full_name = $settings['name'];
            $this->users_name = $settings['userName'];
            $this->lang = $settings['language'];
        }
        return $errors;
        
    }
    public function getProfileSettings() {
        return [
            'name' => [
                'label' => 'Display name',
                'current' => $this->name,
                'type' => 'string'
            ],
            'email' => [
                'label' => 'Email',
                'current' => $this->email,
                'type' => 'string'
            ],
            'userName' => [
                'label' => 'Username',
                'current' => $this->userName,
                'type' => 'string'
            ],
            'language' => [
                'label' => 'Language',
                'current' => $this->getLanguage(),
                'type' => 'select',
                'options' => TbHtml::listData(App()->supportedLanguages, 'code', function($data) { return "{$data['description']} - {$data['nativedescription']}"; })
                    
            ],
            'oldPassword' => [
                'label' => 'Current password',
                'type' => 'password'
            ],
            'password1' => [
                'label' => 'New password',
                'type' => 'password'
            ],
            'password2' => [
                'label' => 'Repeat password',
                'type' => 'password'
            ],
            
        ];
        
    }

    /**
     * 
     * @param array $settings
     * @return array Array of validation errors. [] on success.
     */
    public function setProfileSettings($settings) {
        $errors = [];
        if ($settings['password1'] != $settings['password2']) {
            $errors['password1'][] = 'New password and repeat password must match.';
            $errors['password2'][] = 'New password and repeat password must match.';
        }
        
        if (!empty($settings['password1']) && empty($settings['oldPassword'])) {
            $errors['oldPassword'][] = 'Current password is required when changing password.';
        }
        
        
        if (empty($errors)) {
            // Update password.
            if (!empty($settings['password1'])) {
                $this->password = CPasswordHelper::hashPassword($settings['password1']);
            }
            $this->email = $settings['email'];
            $this->full_name = $settings['name'];
            $this->users_name = $settings['userName'];
            $this->lang = $settings['language'];
            $this->save();
        }
        return $errors;
        
    }
    
    public function getAuthenticator() {
        if (!isset($this->_authenticator)) {
            return App()->pluginManager->getPlugin('ls_core_plugins_AuthDb');
        } else {
            return $this->_authenticator;
        }
    }
    
    public function setAuthenticator($value) {
        $this->_authenticator = $value ;
    }

}
