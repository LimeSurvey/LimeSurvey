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

class User extends LSActiveRecord
{
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
    public function getParentUser(){
        $parent_user = $this->parentAndUser( $this->uid );
        return $parent_user['parent'];
    }

    public function getSurveysCreated(){
        $noofsurveys = Survey::model()->countByAttributes(array("owner_id" =>$this->uid));
        return $noofsurveys;
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
    * @param string $new_user
    * @param string $new_pass
    * @param string $new_full_name
    * @param string $new_email
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
    * @return boolean
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

    /**
    * Returns full name of user
    *
    * @access public
    * @return string
    */
    public function getName($userid)
    {
        static $aOwnerCache = array();

        if (array_key_exists($userid, $aOwnerCache)) {
            $result = $aOwnerCache[$userid];
        } else {
            $result = Yii::app()->db->createCommand()->select('full_name')->from('{{users}}')->where("uid = :userid")->bindParam(":userid", $userid, PDO::PARAM_INT)->queryRow();
            $aOwnerCache[$userid] = $result;
        }

        return $result;
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
    public function getID($sUserName)
    {
        $oUser = User::model()->findByAttributes(array(
            'users_name' => $sUserName
        ));
        if ($oUser)
        {
            return $oUser->uid;
        }
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

    /**
     * Return all super admins in the system
     * @return User[]
     */
    public function getSuperAdmins()
    {
        $criteria = new CDbCriteria();
        $criteria->join = ' JOIN {{permissions}} AS p ON p.uid = t.uid';
        $criteria->addCondition('p.permission = \'superadmin\'');
        $users = $this->findAll($criteria);
        return $users;
    }

    /**
    * Gets the buttons for the GridView
    */
    public function getButtons(){
        $editUser = "";
        $deleteUser = "";
        $setPermissionsUser = "";
        $setTemplatePermissionUser = "";
        $changeOwnership = "";

        $editUrl = Yii::app()->getController()->createUrl('admin/user/sa/modifyuser');
        $setPermissionsUrl = Yii::app()->getController()->createUrl('admin/user/sa/setuserpermissions');
        $setTemplatePermissionsUrl = Yii::app()->getController()->createUrl('admin/user/sa/setusertemplates');
        $changeOwnershipUrl = Yii::app()->getController()->createUrl('admin/user/sa/setasadminchild');

        $oUser = $this->getName($this->uid);
        if($this->uid == Yii::app()->user->getId())
        {
                $editUser = "<button
                data-toggle='tooltip'
                title='".gT("Edit this user")."'
                data-url='".$editUrl."'
                data-uid='".$this->uid."'
                data-user='".$oUser['full_name']."'
                data-action='modifyuser'
                class='btn btn-default btn-xs action_usercontrol_button'>
                    <span class='fa fa-pencil text-success'></span>
                </button>";
            if ($this->parent_id != 0 && Permission::model()->hasGlobalPermission('users','delete') )
            {
                $deleteUrl = Yii::app()->getController()->createUrl('admin/user/sa/deluser', array(
                        "action"=> "deluser",
                        "uid"=>$this->uid,
                        "user" => htmlspecialchars(Yii::app()->user->getId())
                    ));
                $deleteUser = "<button
                data-toggle='modal'
                data-href='#'
                data-onclick='$.post(".$deleteUrl.",
                        {action: \"deluser\", uid:\"".$this->uid."\", user: \"".htmlspecialchars($oUser['full_name'])."\"});'
                data-target='#confirmation-modal'

                data-uid='".$this->uid."'
                data-action='deluser'
                data-message='".gT("Delete this user")."'
                class='btn btn-default btn-xs'>
                    <span class='fa fa-trash  text-danger'></span>
                </button>";
            }
        } else {
            if (Permission::model()->hasGlobalPermission('superadmin','read')
                || $this->uid == Yii::app()->session['loginID']
                || (Permission::model()->hasGlobalPermission('users','update')
                && $this->parent_id == Yii::app()->session['loginID']))
            {
                $editUser = "<button data-toggle='tooltip' data-url='".$editUrl."' data-user='".htmlspecialchars($oUser['full_name'])."' data-uid='".$this->uid."' data-action='modifyuser' title='".gT("Edit this user")."' type='submit' class='btn btn-default btn-xs action_usercontrol_button'><span class='fa fa-pencil text-success'></span></button>";
            }

            if (((Permission::model()->hasGlobalPermission('superadmin','read') &&
                $this->uid != Yii::app()->session['loginID'] ) ||
                (Permission::model()->hasGlobalPermission('users','update') &&
                $this->parent_id == Yii::app()->session['loginID'])) && $this->uid!=1)
                {
                //'admin/user/sa/setuserpermissions'
                    $setPermissionsUser = "<button data-toggle='tooltip' data-user='".htmlspecialchars($oUser['full_name'])."' data-url='".$setPermissionsUrl."' data-uid='".$this->uid."' data-action='setuserpermissions' title='".gT("Set global permissions for this user")."' type='submit' class='btn btn-default btn-xs action_usercontrol_button'><span class='icon-security text-success'></span></button>";
                }
            if ((Permission::model()->hasGlobalPermission('superadmin','read')
                || Permission::model()->hasGlobalPermission('templates','read'))
                && $this->uid!=1)
                {
                //'admin/user/sa/setusertemplates')
                    $setTemplatePermissionUser = "<button type='submit' data-user='".htmlspecialchars($oUser['full_name'])."' data-url='".$setTemplatePermissionsUrl."' data-uid='".$this->uid."' data-action='setusertemplates' data-toggle='tooltip' title='".gT("Set template permissions for this user")."' class='btn btn-default btn-xs action_usercontrol_button'><span class='icon-templatepermissions text-success'></span></button>";
                }
                if ((Permission::model()->hasGlobalPermission('superadmin','read')
                    || (Permission::model()->hasGlobalPermission('users','delete')
                    && $this->parent_id == Yii::app()->session['loginID'])) && $this->uid!=1)
                    {
                    $deleteUrl = Yii::app()->getController()->createUrl('admin/user/sa/deluser', array(
                        "action"=> "deluser",
                        "uid"=>$this->uid,
                        "user" => htmlspecialchars(Yii::app()->user->getId())
                    ));
                     //'admin/user/sa/deluser'
                    $deleteUser = "<button
                        id='delete_user_".$this->uid."'
                        data-toggle='modal'
                        data-target='#confirmation-modal'
                        data-url='".$deleteUrl."'
                        data-uid='".$this->uid."'
                        data-user='".htmlspecialchars($oUser['full_name'])."'
                        data-action='deluser'
                        data-onclick='triggerRunAction($(\"#delete_user_".$this->uid."\"))'
                        data-message='".gT("Do you want to delete this user?")."'
                        class='btn btn-default btn-xs '>
                            <span class='fa fa-trash  text-danger'></span>
                        </button>";
                    }
                if (Yii::app()->session['loginID'] == "1" && $this->parent_id !=1 ) {
                //'admin/user/sa/setasadminchild'
                    $changeOwnership = "<button data-toggle='tooltip' data-url='".$changeOwnershipUrl."' data-user='".htmlspecialchars($oUser['full_name'])."' data-uid='".$this->uid."' data-action='setasadminchild' title='".gT("Take ownership")."' class='btn btn-default btn-sm action_usercontrol_button' type='submit'><span class='icon-takeownership text-success'></span></button>";
                }
        }
        return "<div>"
            . $editUser
            . $deleteUser
            . $setPermissionsUser
            . $setTemplatePermissionUser
            . $changeOwnership
            . "</div>";
    }

    public function getColums(){
        $cols = array(
            array(
                "name" => 'buttons',
                "type" => 'raw',
                "header" => gT("Action")
            ),
            array(
                "name" => 'uid',
                "header" => gT("User ID")
            ),
            array(
                "name" => 'users_name',
                "header" => gT("Username")
            ),
            array(
                "name" => 'email',
                "header" => gT("Email")
            ),
            array(
                "name" => 'full_name',
                "header" => gT("Full name")
            )
        );
        if(Permission::model()->hasGlobalPermission('superadmin','read')) {
            $cols[] = array(
                "name" => 'surveysCreated',
                "header" => gT("No of surveys")
            );
        }

        $cols[] = array(
            "name" => 'parentUser',
            "header" => gT("Created by")
        );
        return $cols;
    }
   /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.
        $pageSize = Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);
        $criteria=new CDbCriteria;

        // $criteria->compare('uid',$this->uid);
        // $criteria->compare('users_name',$this->users_name,true);
        // $criteria->compare('password',$this->password,true);
        // $criteria->compare('full_name',$this->full_name,true);
        // $criteria->compare('parent_id',$this->parent_id);
        // $criteria->compare('lang',$this->lang,true);
        // $criteria->compare('email',$this->email,true);
        // $criteria->compare('htmleditormode',$this->htmleditormode,true);
        // $criteria->compare('templateeditormode',$this->templateeditormode,true);
        // $criteria->compare('questionselectormode',$this->questionselectormode,true);
        // $criteria->compare('one_time_pw',$this->one_time_pw,true);
        // $criteria->compare('dateformat',$this->dateformat);
        // $criteria->compare('created',$this->created,true);
        // $criteria->compare('modified',$this->modified,true);
        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
            'pagination' => array(
                'pageSize' => $pageSize
            )
        ));
    }

}
