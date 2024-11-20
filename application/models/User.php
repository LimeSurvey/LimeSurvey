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

use LimeSurvey\Models\Services\UserManager;

/**
 * Class User
 *
 * @property integer $uid User ID - primary key
 * @property string $users_name Users username
 * @property string $password User's password hash
 * @property string $full_name User's full name
 * @property integer $parent_id
 * @property string $lang User's preferred language: (auto: automatic | languagecodes eg 'en')
 * @property string $email User's e-mail address
 * @property string $htmleditormode User's prefferred HTML editor mode:(default|inline|popup|none)
 * @property string $templateeditormode User's prefferred template editor mode:(default|full|none)
 * @property string $questionselectormode User's prefferred Question type selector:(default|full|none)
 * @property string $one_time_pw User's one-time-password hash
 * @property integer $dateformat Date format type 1-12
 * @property string $created Time created Time user was created as 'YYYY-MM-DD hh:mm:ss'
 * @property string $modified Time modified Time created Time user was modified as 'YYYY-MM-DD hh:mm:ss'
 * @property string $validation_key  used for email link to reset or create a password for a survey participant
 *                                   Link is send when user is created or password has been reset
 * @property string $validation_key_expiration datetime when the validation key expires
 * @property string $last_forgot_email_password datetime when user send email for forgot pw the last time (prevent bot)
 *
 * @property Permission[] $permissions
 * @property User $parentUser Parent user
 * @property string $parentUserName  Parent user's name
 * @property string $last_login
 * @property Permissiontemplates[] $roles
 * @property UserGroup[] $groups
 * @property int $user_status User's account status (1: activated | 0: deactivated)
 */

class User extends LSActiveRecord
{
    /** @var int maximum time the validation_key is valid*/
    private const MAX_EXPIRATION_TIME_IN_HOURS = 48;

    /** @var int maximum days the validation key is valid */
    private const MAX_EXPIRATION_TIME_IN_DAYS = 2;

    /** @var int  maximum length for the validation_key*/
    private const MAX_VALIDATION_KEY_LENGTH = 38;

    /**
     * @var string $lang Default value for user language
     */
    public $lang = 'auto';

    public $searched_value;

    /**
     * @inheritdoc
     * @return User
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{users}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'uid';
    }
    /** @inheritdoc */
    public function relations()
    {
        return array(
            'permissions' => array(self::HAS_MANY, 'Permission', 'uid'),
            'parentUser' => array(self::HAS_ONE, 'User', array('uid' => 'parent_id')),
            'settings' => array(self::HAS_MANY, 'SettingsUser', 'uid'),
            'groups' => array(self::MANY_MANY, 'UserGroup', '{{user_in_groups}}(uid,ugid)'),
            'roles' => array(self::MANY_MANY, 'Permissiontemplates', '{{user_in_permissionrole}}(uid,ptid)')
        );
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('users_name, password, email', 'required'),
            array('users_name', 'unique'),
            array('users_name', 'length','max' => 64),
            array('full_name', 'length','max' => 50),
            array('email', 'email'),
            array('full_name', 'LSYii_Validators'), // XSS if non super-admin
            array('parent_id', 'default', 'value' => 0),
            array('parent_id', 'numerical', 'integerOnly' => true),
            array('lang', 'default', 'value' => Yii::app()->getConfig('defaultlang')),
            array('lang', 'LSYii_Validators', 'isLanguage' => true),
            array('htmleditormode', 'default', 'value' => 'default'),
            array('htmleditormode', 'in', 'range' => array('default', 'inline', 'popup', 'none'), 'allowEmpty' => true),
            array('questionselectormode', 'default', 'value' => 'default'),
            array('questionselectormode', 'in', 'range' => array('default', 'full', 'none'), 'allowEmpty' => true),
            array('templateeditormode', 'default', 'value' => 'default'),
            array('templateeditormode', 'in', 'range' => array('default', 'full', 'none'), 'allowEmpty' => true),
            array('dateformat', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('expires', 'date','format' => ['yyyy-M-d H:m:s.???','yyyy-M-d H:m:s','yyyy-M-d H:m'],'allowEmpty' => true),
            array('users_name', 'unsafe' , 'on' => ['update']),

            // created as datetime default current date in create scenario ?
            // modifier as datetime default current date ?
            array('validation_key', 'length','max' => self::MAX_VALIDATION_KEY_LENGTH),
            //todo: write a rule for date (can also be null)
            //array('lastForgotPwEmail', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
        );
    }

    /** @inheritdoc */
    public function scopes()
    {
        $userStatusType = \Yii::app()->db->schema->getTable('{{users}}')->columns['user_status']->dbType;
        $activeScope = array(
            'condition' => 'user_status = :active',
            'params' => array(
                'active' => $userStatusType == 'boolean' ? 'TRUE' :  '1',
            )
        );

        $notExpiredScope = array(
            'condition' => "expires > :now OR expires IS NULL",
            'params' => array(
                'now' => dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig("timeadjust")),
            )
        );

        if (App()->getConfig("DBVersion") < 495) {
            /* No expires column before 495 */
            return array(
                'active' => [],
                'notexpired' => [],
            );
        }

        if (App()->getConfig("DBVersion") < 619) {
            /* No user_status column before 619 */
            return array(
                'active' => [],
                'notexpired' => $notExpiredScope
            );
        }

        return array(
            'active' => $activeScope,
            'notexpired' => $notExpiredScope
        );
    }

    public function attributeLabels()
    {
        return [
            'uid' => gT('User ID'),
            'users_name' => gT('Username'),
            'password' => gT('Password'),
            'full_name' => gT('Full name'),
            'parent_id' => gT('Parent user'),
            'lang' => gT('Language'),
            'email' => gT('Email'),
            'htmleditormode' => gT('Editor mode'),
            'templateeditormode' => gT('Template editor mode'),
            'questionselectormode' => gT('Question selector mode'),
            'one_time_pw' => gT('One-time password'),
            'dateformat' => gT('Date format'),
            'created' => gT('Created at'),
            'modified' => gT('Modified at'),
            'last_login' => gT('Last recorded login'),
            'expires' => gT("Expiry date/time:"),
            'user_status' => gT("Status"),
        ];
    }

    /**
     * @inheritDoc
     * Delete user in related model after deletion
     * return void
     **/
    protected function afterDelete()
    {
        parent::afterDelete();
        /* Delete all permission */
        Permission::model()->deleteAll(
            "uid = :uid",
            [":uid" => $this->uid]
        );
        /* Delete potential roles */
        UserInPermissionrole::model()->deleteAll(
            "uid = :uid",
            [":uid" => $this->uid]
        );
        /* User settings */
        SettingsUser::model()->deleteAll(
            "uid = :uid",
            [":uid" => $this->uid]
        );
        /* User in group */
        UserInGroup::model()->deleteAll(
            "uid = :uid",
            [":uid" => $this->uid]
        );
    }

    /**
     * @return string
     */
    public function getSurveysCreated()
    {
        $noofsurveys = Survey::model()->countByAttributes(array("owner_id" => $this->uid));
        return $noofsurveys;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        $dateFormat = getDateFormatData(Yii::app()->session['dateformat']);
        return $dateFormat['phpdate'];
    }

    /**
     * @todo Not used?
     */
    public function getFormattedDateCreated()
    {
        $dateCreated = $this->created;
        /**
         * @todo: Review this. Cast to string added to keep the original behavior (parameter can't be null since PHP 8.1).
         *        But it returns the current date if the parameter is null (both now with the cast and pre PHP 8.1 without the cast).
         */
        $date = new DateTime((string) $dateCreated);
        return $date->format($this->getDateFormat());
    }

    /**
     * Creates new user
     *
     * @access public
     * @param string $new_user
     * @param string $new_pass
     * @param string $new_full_name
     * @param int $parent_user
     * @param string $new_email
     * @param string|null $expires
     * @param boolean $status
     * @return integer|boolean User ID if success
     */
    public static function insertUser($new_user, $new_pass, $new_full_name, $parent_user, $new_email, $expires = null, $status = true)
    {
        $oUser = new self();
        $oUser->users_name = $new_user;
        $oUser->setPassword($new_pass);
        $oUser->full_name = $new_full_name;
        $oUser->parent_id = $parent_user;
        $oUser->lang = 'auto';
        $oUser->email = $new_email;
        $oUser->created = date('Y-m-d H:i:s');
        $oUser->modified = date('Y-m-d H:i:s');
        $oUser->expires = $expires;
        $oUser->user_status = $status;
        if ($oUser->save()) {
            return $oUser->uid;
        } else {
            return false;
        }
    }

    /**
     * Finds user by username
     * @param string $sUserName
     * @return User
     */
    public static function findByUsername($sUserName)
    {
        /** @var User $oUser */
        $oUser = User::model()->findByAttributes(array(
            'users_name' => $sUserName
        ));
        return $oUser;
    }

    /**
     * Updates user password hash
     *
     * @param int $iUserID The User ID
     * @param string $sPassword The clear text password
     * @return int number of rows updated
     */
    public static function updatePassword($iUserID, $sPassword)
    {
        return User::model()->updateByPk($iUserID, array('password' => password_hash($sPassword, PASSWORD_DEFAULT)));
    }

    /**
     * Set user password with hash
     *
     * @param string $sPassword The clear text password
     * @return \User
     */
    public function setPassword($sPassword, $save = false)
    {
        $this->password = password_hash($sPassword, PASSWORD_DEFAULT);
        if ($save) {
            $this->save();
        }
        return $this; // Return current object
    }

    /**
     * Check if password is OK for current \User
     *
     * @param string $sPassword The clear password
     * @return boolean
     */
    public function checkPassword($sPassword)
    {
        // password can not be empty
        if (empty($this->password)) {
            return false;
        }
        // Password is OK
        if (password_verify($sPassword, $this->password)) {
            return true;
        }
        // It can be an old password
        if ($this->password == hash('sha256', $sPassword)) {
            $this->setPassword($sPassword, true);
            return true;
        }
        return false;
    }


    /**
     * Checks the strength of a given password against configured validation rules.
     *
     * This function evaluates the password strength based on length, presence of lowercase
     * and uppercase letters, numbers, and special characters. It also allows for plugin-based
     * additional password requirement checks.
     *
     * @param string $password The password to check for strength
     *
     * @return string An error message if the password doesn't meet the requirements, or an empty string if it's valid
     */
    public function checkPasswordStrength(string $password)
    {
        $settings = Yii::app()->getConfig("passwordValidationRules");
        $length = strlen($password);
        $lowercase = preg_match_all('@[a-z]@', $password);
        $uppercase = preg_match_all('@[A-Z]@', $password);
        $number    = preg_match_all('@[0-9]@', $password);
        $specialChars = preg_match_all('@[^\w]@', $password);

        $resultDefaultRules = "";
        if ((int) $settings['min'] > 0) {
            if ($length < $settings['min']) {
                $resultDefaultRules = sprintf(ngT('Password must be at least %d character long|Password must be at least %d characters long', $settings['min']), $settings['min']);
            }
        }
        if ((int) $settings['max'] > 0) {
            if ($length > $settings['max']) {
                $resultDefaultRules = sprintf(ngT('Password must be at most %d character long|Password must be at most %d characters long', $settings['max']), $settings['max']);
            }
        }
        if ((int) $settings['lower'] > 0) {
            if ($lowercase < $settings['lower']) {
                $resultDefaultRules = sprintf(ngT('Password must include at least %d lowercase letter|Password must include at least %d lowercase letters', $settings['lower']), $settings['lower']);
            }
        }
        if ((int) $settings['upper'] > 0) {
            if ($uppercase < $settings['upper']) {
                $resultDefaultRules = sprintf(ngT('Password must include at least %d uppercase letter|Password must include at least %d uppercase letters', $settings['upper']), $settings['upper']);
            }
        }
        if ((int) $settings['numeric'] > 0) {
            if ($number < $settings['numeric']) {
                $resultDefaultRules = sprintf(ngT('Password must include at least %d number|Password must include at least %d numbers', $settings['numeric']), $settings['numeric']);
            }
        }
        if ((int) $settings['symbol'] > 0) {
            if ($specialChars < $settings['symbol']) {
                $resultDefaultRules = sprintf(ngT('Password must include at least %d special character|Password must include at least %d special characters', $settings['symbol']), $settings['symbol']);
            }
        }
        $passwordOk = ($resultDefaultRules === '');
        $oPasswordTestEvent = new PluginEvent('checkPasswordRequirement');
        $oPasswordTestEvent->set('password', $password);
        $oPasswordTestEvent->set('passwordOk', $passwordOk);
        $oPasswordTestEvent->set('passwordError', $resultDefaultRules);
        Yii::app()->getPluginManager()->dispatchEvent($oPasswordTestEvent);
        return ($oPasswordTestEvent->get('passwordOk') ? '' : $oPasswordTestEvent->get('passwordError'));
    }

    /**
     * Checks if
     *  -- password strength
     *  -- oldpassword is correct
     *  -- oldpassword and newpassword are identical
     *  -- newpassword and repeatpassword are identical
     *  -- newpassword is not empty
     *
     * @param string $newPassword
     * @param string $oldPassword

     * @param string $repeatPassword
     * @return string empty string means everything is ok, otherwise error message is returned
     */
    public function validateNewPassword(string $newPassword, string $oldPassword, string $repeatPassword)
    {
        $errorMsg = '';

        if (!empty($newPassword)) {
            $errorMsg = $this->checkPasswordStrength($newPassword);
        }

        if ($errorMsg === '') {
            if (!$this->checkPassword($oldPassword)) {
                // Always check password
                $errorMsg = gT("Your new password was not saved because the old password was wrong.");
            } elseif (trim($oldPassword) === trim($newPassword)) {
                //First test if old and new password are identical => no need to save it (or ?)
                $errorMsg = gT("Your new password was not saved because it matches the old password.");
            } elseif (trim($newPassword) !== trim($repeatPassword)) {
                //Then test the new password and the repeat password for identity
                $errorMsg = gT("Your new password was not saved because the passwords did not match.");
                //Now check if the old password matches the old password saved
            } elseif (empty(trim($newPassword))) {
                $errorMsg = gT("The new password can not be empty.");
            }
        }

        return $errorMsg;
    }

    /**
     * @todo document me
     */
    public function getPasswordHelpText()
    {
        $settings =  Yii::app()->getConfig("passwordValidationRules");
        $txt = gT('A password must meet the following requirements: ');
        if ((int) $settings['min'] > 0) {
            $txt .= sprintf(ngT('At least %d character long.|At least %d characters long.', $settings['min']), $settings['min']) . ' ';
        }
        if ((int) $settings['max'] > 0) {
            $txt .= sprintf(ngT('At most %d character long.|At most %d characters long.', $settings['max']), $settings['max']) . ' ';
        }
        if ((int) $settings['min'] > 0 && (int) $settings['max'] > 0) {
            if ($settings['min'] == $settings['max']) {
                $txt .= sprintf(ngT('Exactly %d character long.|Exactly %d characters long.', $settings['min']), $settings['min']) . ' ';
            } elseif ($settings['min'] < $settings['max']) {
                $txt .= sprintf(gT('Between %d and %d characters long.'), $settings['min'], $settings['max']) . ' ';
            }
        }
        if ((int) $settings['lower'] > 0) {
            $txt .= sprintf(ngT('At least %d lower case letter.|At least %d lower case letters.', $settings['lower']), $settings['lower']) . ' ';
        }
        if ((int) $settings['upper'] > 0) {
            $txt .= sprintf(ngT('At least %d upper case letter.|At least %d upper case letters.', $settings['upper']), $settings['upper']) . ' ';
        }
        if ((int) $settings['numeric'] > 0) {
            $txt .= sprintf(ngT('At least %d number.|At least %d numbers.', $settings['numeric']), $settings['numeric']) . ' ';
        }
        if ((int) $settings['symbol'] > 0) {
            $txt .= sprintf(ngT('At least %d special character.|At least %d special characters.', $settings['symbol']), $settings['symbol']) . ' ';
        }
        return($txt);
    }

    /**
     * Adds user record
     *
     * @access public
     * @param array $data
     * @deprecated : just don't use it
     * @return string
     */
    public function insertRecords($data)
    {
        return $this->getDb()->insert('users', $data);
    }

    /**
     * Returns User ID common in Survey_Permissions and User_in_groups
     * @param $surveyid
     * @param $postusergroupid
     * @return CDbDataReader
     */
    public function getCommonUID($surveyid, $postusergroupid)
    {
        $query2 = "SELECT b.uid FROM (SELECT uid FROM {{permissions}} WHERE entity_id = :surveyid AND entity = 'survey') AS c RIGHT JOIN {{user_in_groups}} AS b ON b.uid = c.uid WHERE c.uid IS NULL AND b.ugid = :postugid";
        return Yii::app()->db->createCommand($query2)->bindParam(":surveyid", $surveyid, PDO::PARAM_INT)->bindParam(":postugid", $postusergroupid, PDO::PARAM_INT)->query(); //Checked
    }

    /**
     * @todo document me
     */
    public function getGroupList()
    {
        $collector = array_map(function ($oUserInGroup) {
            return $oUserInGroup->name;
        }, $this->groups);
        return join(', ', $collector);
    }

    /**
     * Return all super admins in the system
     * @return User[]
     */
    public function getSuperAdmins()
    {
        // TODO should be static
        $criteria = new CDbCriteria();
        /* have read superadmin permissions */
        $criteria->with = array('permissions');
        $criteria->compare('permissions.permission', 'superadmin');
        $criteria->compare('permissions.read_p', '1');
        /* OR are inside forcedsuperadmin config */
        $criteria->addInCondition('t.uid', App()->getConfig('forcedsuperadmin'), 'OR');
        /** @var User[] $users */
        $users = $this->findAll($criteria);
        return $users;
    }

    /**
     * Gets the buttons for the GridView
     * @return string
     */
    public function getManagementButtons()
    {
        $permission_superadmin_read = Permission::model()->hasGlobalPermission('superadmin', 'read');
        $permission_users_read = Permission::model()->hasGlobalPermission('users', 'read');
        $permission_users_update = Permission::model()->hasGlobalPermission('users', 'update');
        $permission_users_delete = Permission::model()->hasGlobalPermission('users', 'delete');
        $userManager = new UserManager(App()->user, $this);
        // User is owned or created by you
        $ownedOrCreated = $this->parent_id == App()->session['loginID'];

        $detailUrl = App()->getController()->createUrl('userManagement/viewUser', ['userid' => $this->uid]);
        $setPermissionsUrl = App()->getController()->createUrl('userManagement/userPermissions', ['userid' => $this->uid]);
        $setRoleUrl = App()->getController()->createUrl('userManagement/addRole', ['userid' => $this->uid]);
        $editUrl = App()->getController()->createUrl('userManagement/addEditUser', ['userid' => $this->uid]);
        $setTemplatePermissionsUrl = App()->getController()->createUrl('userManagement/userTemplatePermissions', ['userid' => $this->uid]);
        $changeOwnershipUrl = App()->getController()->createUrl('userManagement/takeOwnership');
        $deleteUrl = App()->getController()->createUrl('userManagement/deleteConfirm', ['userid' => $this->uid, 'user' => $this->full_name]);

        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('User details'),
            'iconClass'        => "ri-search-line",
            'linkClass'        => "UserManagement--action--openmodal UserManagement--action--userdetail",
            'linkAttributes'   => [
                'data-href' => $detailUrl,
            ],
            'enabledCondition' =>
                $permission_superadmin_read || $permission_users_read
                || ($permission_superadmin_read
                    && (Permission::isForcedSuperAdmin($this->uid)
                        || $this->uid == App()->user->getId()
                    )
                )
                || (!$permission_superadmin_read
                    && ($this->uid == App()->user->getId() // You can see yourself
                        || ($permission_users_update
                            && $ownedOrCreated
                        )
                    )
                )
        ];

        $permission = ( $permission_superadmin_read && !(Permission::isForcedSuperAdmin($this->uid) || $this->uid == App()->user->getId()))
            || (!$permission_superadmin_read && ($this->uid != App()->session['loginID'] //Can't change your own permissions
                    && ( $permission_users_update && $ownedOrCreated)
                    && !Permission::isForcedSuperAdmin($this->uid)
                )
            );

        if ($this->user_status) {
            $activateUrl = App()->getController()->createUrl('userManagement/activationConfirm', ['userid' => $this->uid, 'action' => 'deactivate']);
            $dropdownItems[] = [
                'title'            => gT('Deactivate'),
                'iconClass'        => "ri-user-unfollow-fill text-danger",
                'linkClass'        => $permission ? "UserManagement--action--openmodal UserManagement--action--status" : '',
                'linkAttributes'   => [
                    'data-href' => $permission ? $activateUrl : '#',
                ],
                'enabledCondition' => $permission
            ];
        } else {
            $activateUrl = App()->getController()->createUrl('userManagement/activationConfirm', ['userid' => $this->uid, 'action' => 'activate']);
            $dropdownItems[] = [
                'title'            => gT('Activate'),
                'iconClass'        => "ri-user-follow-fill",
                'linkClass'        => $permission ? "UserManagement--action--openmodal UserManagement--action--status" : '',
                'linkAttributes'   => [
                    'data-href' => $permission ? $activateUrl : '#',
                ],
                'enabledCondition' => $permission
            ];
        }
        $dropdownItems[] = [
            'title'            => gT('Edit permissions'),
            'iconClass'        => "ri-lock-fill",
            'linkClass'        => "UserManagement--action--openmodal UserManagement--action--permissions",
            'linkAttributes'   => [
                'data-href'      => $setPermissionsUrl,
                'data-modalsize' => 'modal-xl',
            ],
            'enabledCondition' =>
                ($permission_superadmin_read
                    && !(Permission::isForcedSuperAdmin($this->uid)
                        || $this->uid == App()->user->getId()
                    )
                )
                || (!$permission_superadmin_read
                    && ($this->uid != App()->session['loginID'] //Can't change your own permissions
                        && (
                            $permission_users_update
                            && $ownedOrCreated
                        )
                        && !Permission::isForcedSuperAdmin($this->uid)
                    )
                )
        ];
        $dropdownItems[] = [
            'title'            => gT('User role'),
            'iconClass'        => "ri-group-fill",
            'linkClass'        => "UserManagement--action--openmodal UserManagement--action--addrole",
            'linkAttributes'   => [
                'data-href' => $setRoleUrl,
            ],
            'enabledCondition' => $userManager->canAssignRole() && $this->uid != App()->user->getId()
        ];
        $dropdownItems[] = [
            'title'            => gT('Edit user'),
            'iconClass'        => "ri-pencil-fill",
            'linkClass'        => "UserManagement--action--openmodal UserManagement--action--edituser",
            'linkAttributes'   => [
                'data-href' => $editUrl,
            ],
            'enabledCondition' => $this->canEdit()
                                && $this->uid != App()->user->getId() // To update self : must use personal settings
        ];
        $dropdownItems[] = [
            'title'            => gT('Template permissions'),
            'iconClass'        => "ri-brush-fill",
            'linkClass'        => "UserManagement--action--openmodal UserManagement--action--templatepermissions",
            'linkAttributes'   => [
                'data-href' => $setTemplatePermissionsUrl,
            ],
            'enabledCondition' =>
                ($permission_superadmin_read
                    && !(Permission::isForcedSuperAdmin($this->uid)
                        || $this->uid == App()->user->getId()
                    )
                )
        ];
        $dropdownItems[] = [
            'title'            => gT('Take ownership'),
            'iconClass'        => "ri-user-received-fill",
            'linkId'        => "UserManagement--takeown-$this->uid",
            'linkAttributes'   => [
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#confirmation-modal',
                'data-url'       => $changeOwnershipUrl,
                'data-userid'    => $this->uid,
                'data-user'      => CHtml::encode($this->full_name),
                'data-action'    => 'deluser',
                'data-onclick'   => "LS.UserManagement.triggerRunAction(\"#UserManagement--takeown-$this->uid\")",
                'data-message'   => gT('Do you want to take ownership of this user?'),
            ],
            'enabledCondition' =>
                ($permission_superadmin_read
                    && !(Permission::isForcedSuperAdmin($this->uid)
                        || $this->uid == App()->user->getId()
                    )
                    && $this->parent_id != App()->session['loginID']
                )
                || (!$permission_superadmin_read
                    && (Permission::isForcedSuperAdmin(App()->session['loginID'])
                        && $this->parent_id != App()->session['loginID']
                    )
                )
        ];
        $dropdownItems[] = [
            'title'            => gT('Delete User'),
            'iconClass'        => "ri-delete-bin-fill text-danger",
            'linkClass'        => "UserManagement--action--openmodal UserManagement--action--delete",
            'linkId'           => "UserManagement--delete-$this->uid",
            'linkAttributes'   => [
                'data-href' => $deleteUrl,
            ],
            'enabledCondition' =>
                ($permission_superadmin_read
                    && !(Permission::isForcedSuperAdmin($this->uid)
                        || $this->uid == App()->user->getId()
                    )
                )
                || (!$permission_superadmin_read
                    && ($this->uid != App()->session['loginID'] // One cant delete onesself
                        && (
                            $permission_users_delete // Global permission to delete users
                            && $this->parent_id == App()->session['loginID'] // User is owned by current admin
                        )
                        && !Permission::isForcedSuperAdmin($this->uid) // Can't delete forced superadmins, ever
                    )
                )
        ];

        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
    }

    public function getParentUserName()
    {
        if ($this->parentUser) {
            return $this->parentUser->users_name;
        }
        // root user, no parent
        return null;
    }

    public function getRoleList()
    {
        $list = array_map(
            function ($oRoleMapping) {
                return $oRoleMapping->name;
            },
            $this->roles
        );
        return join(', ', $list);
    }

    /**
     * Returns the last login formatted for displaying.
     * @return string
     */
    public function getLastloginFormatted()
    {
        $lastLogin = $this->last_login;
        if ($lastLogin == null) {
            return '---';
        }

        $date = new DateTime($lastLogin);
        return $date->format($this->getDateFormat()) . ' ' . $date->format('H:i');
    }

    public function getManagementCheckbox()
    {
        return "<input type='checkbox' class='usermanagement--selector-userCheckbox' name='selectedUser[]' value='" . $this->uid . "'>";
    }
    /**
     * @return array
     */
    public function getManagementColums()
    {
        $cols = [
            [
                'name'              => 'managementCheckbox',
                'type'              => 'raw',
                'header'            => "<input type='checkbox' id='usermanagement--action-toggleAllUsers' />",
                'filter'            => false,
                'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column']
            ],
            [
                "name"   => 'uid',
                "header" => gT("User ID"),
                'htmlOptions' => ['class' => 'uid']
            ],
            [
                "name"   => 'users_name',
                "header" => gT("Username")
            ],
            [
                "name"   => 'email',
                "header" => gT("Email")
            ],
            [
                "name"   => 'full_name',
                "header" => gT("Full name")
            ],
            [
                "name"   => "created",
                "header" => gT("Created on"),
                "value"  => '$data->formattedDateCreated',
            ],
            [
                "name"   => "parentUserName",
                "header" => gT("Created by"),
            ],
            [
                "name"   => "user_status",
                "header" => gT("Status"),
                'headerHtmlOptions' => ['class' => 'hidden'],
                'htmlOptions'       => ['class' => 'hidden activation']
            ],
        ];

        // NOTE: Super Administrators with just the "read" flag also have these flags
        $permission_read_users      = Permission::model()->hasGlobalPermission('users', 'read');
        $permission_read_usergroups = Permission::model()->hasGlobalPermission('usergroups', 'read');
        $permission_read_surveys    = Permission::model()->hasGlobalPermission('surveys', 'read');

        // Number of Surveys
        // This info is already guessable by people able to list all Surveys
        if ($permission_read_surveys) {
            $cols[] = array(
                "name" => 'surveysCreated',
                "header" => gT("No of surveys"),
                'filter' => false
            );
        }

        // Usergroups Names
        // This info is safe to be shown to who can read all Users and Groups.
        // TODO: When there will be a more robust Group permissions system,
        //       this column could be enabled by default, since each Group would
        //       be checked individually.
        if ($permission_read_users && $permission_read_usergroups) {
            $cols[] = array(
                "name" => 'groupList',
                "header" => gT("Usergroups"),
                'filter' => false
            );
        }

        // Role Names
        // Knowing this info makes sense if you can read all Users
        if ($permission_read_users) {
            $cols[] = array(
                "name" => 'roleList',
                "header" => gT("Applied role"),
                'filter' => false
            );
        }

        $cols[] = [
            "header"            => gT("Action"),
            "name"              => 'managementButtons',
            "type"              => 'raw',
            'filter'            => false,
            'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
            'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
            'htmlOptions'       => ['class' => 'text-center ls-sticky-column'],
        ];

        return $cols;
    }

    /**
     * @return array
     */
    public function getColums()
    {
        // TODO should be static
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
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $cols[] = array(
                "name" => 'surveysCreated',
                "header" => gT("No of surveys")
            );
        }

        $cols[] = array(
            "name" => "parentUserName",
            "header" => gT("Created by"),
        );

        $cols[] = array(
            "name" => "created",
            "header" => gT("Created on"),
            "value" => '$data->formattedDateCreated',

        );
        return $cols;
    }

    /** @inheritdoc */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
        $criteria = new CDbCriteria();

        $criteria->compare('t.uid', $this->uid);
        $criteria->compare('t.full_name', $this->full_name, true);
        $criteria->compare('t.users_name', $this->users_name, true, 'OR');
        $criteria->compare('t.email', $this->email, true, 'OR');

        //filter for 'created' date comparison
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        if ($this->created) {
            try {
                $dateTimeInput = $this->created . ' 00:00'; //append time
                $s = DateTime::createFromFormat($dateformatdetails['phpdate'] . ' H:i', $dateTimeInput);
                if ($s) {
                    $s2 = $s->format('Y-m-d H:i');
                    $criteria->addCondition('t.created >= \'' . $s2 . '\'');
                } else {
                    throw new Exception('wrong date format.');
                }
            } catch (Exception $e) {
                //could only mean wrong input from user ...reset filter value
                $this->created = '';
            }
        }

        $getUser = Yii::app()->request->getParam('User');
        if (!empty($getUser['parentUserName'])) {
            $getParentName = $getUser['parentUserName'];
            $criteria->join = "LEFT JOIN {{users}} u ON t.parent_id = u.uid";
            $criteria->compare('u.users_name', $getParentName, true, 'OR');
        }

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSize
            )
        ));
    }

    /**
     * Creates a validation key and saves it in table user for this user.
     *
     * @return bool true if validation_key could be saved in db, false otherwise
     */
    public function setValidationKey()
    {
        $this->validation_key = randomChars(self::MAX_VALIDATION_KEY_LENGTH);

        return $this->save();
    }

    /**
     * Creates the validation key expiration date and save it in db
     *
     * @return bool true if datetime could be saved, false otherwise
     * @throws Exception
     */
    public function setValidationExpiration()
    {
        $datePlusMaxExpiration = new DateTime();
        $datePlusString = 'P' . self::MAX_EXPIRATION_TIME_IN_DAYS . 'D';
        $dateInterval = new DateInterval($datePlusString);
        $datePlusMaxExpiration->add($dateInterval);

        $this->validation_key_expiration = $datePlusMaxExpiration->format('Y-m-d H:i:s');

        return $this->save();
    }

    /**
     * Returns true if the user has expired.
     *
     * @return boolean
     */
    public function isExpired()
    {
        $expired = false;
        if (!empty($this->expires)) {
            // Time adjust
            $now = date("Y-m-d H:i:s", strtotime((string) Yii::app()->getConfig('timeadjust'), strtotime(date("Y-m-d H:i:s"))));
            $expirationTime = date("Y-m-d H:i:s", strtotime((string) Yii::app()->getConfig('timeadjust'), strtotime((string) $this->expires)));

            // Time comparison
            $expired = new DateTime($expirationTime) < new DateTime($now);
        }
        return $expired;
    }

    /**
     * Check if user is active
     * @return boolean
     */
    public function isActive()
    {
        /* Default is active, user_status must be set (to be tested during DB update); deactivated set user_status to 0 */
        return !isset($this->user_status) || $this->user_status !== 0;
    }

    /**
     * Check if user can login
     * @return boolean
     */
    public function canLogin()
    {
        return $this->isActive() && !$this->isExpired();
    }

    /**
     * Get the decription to be used in list
     * @return string
     */
    public function getDisplayName()
    {
        if (empty($this->full_name)) {
            return $this->users_name;
        }
        return sprintf(gt("%s (%s)"), $this->users_name, $this->full_name);
    }

    /**
     * @param $userGroupId
     * @return CActiveDataProvider
     */
    public function searchUserGroupMembers($userGroupId)
    {
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
        $criteria = new CDbCriteria();
        $criteria->join = 'INNER JOIN {{user_in_groups}} uig on t.uid = uig.uid';
        $criteria->condition .= 'uig.ugid=:ugid';
        $criteria->params = array(':ugid' => $userGroupId);
        $criteria->compare('t.users_name', $this->users_name, true);
        $criteria->compare('t.email', $this->email, true);


        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSize
            )
        ));
    }

    /**
     * Returns button for gridview.
     * @return string
     */
    public function getGroupMemberListButtons()
    {
        $userGroupId = Yii::app()->request->getQuery('ugid', 0);
        $userGroup = UserGroup::model()->findByPk($userGroupId);

        $currentUserId = $this->uid;
        $canDelete = Permission::model()->hasGlobalPermission('usergroups', 'update')
            && $userGroup && $userGroup->owner_id == Yii::app()->session['loginID'];
        $isDeletable = $userGroup
            && ($canDelete || Permission::model()->hasGlobalPermission('superadmin'))
            && $currentUserId != '1';

        $dropdownItems[] = [
            'title'            => gT('Delete'),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => $isDeletable,
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-btnclass'  => 'btn-danger',
                'data-btntext'   => gt('Delete'),
                'data-post-url'  => App()->createUrl("userGroup/deleteUserFromGroup"),
                'data-post-datas' => json_encode(['ugid' => $userGroupId, 'uid' => $currentUserId]),
                'data-message'   => sprintf(
                    gT("Are you sure you want to delete user '%s' from user group '%s'?"),
                    CHtml::encode($this->users_name),
                    CHtml::encode($userGroup->name)
                ),
                'data-bs-target' => "#confirmation-modal"
            ]
        ];
        return App()->getController()->widget(
            'ext.admin.grid.GridActionsWidget.GridActionsWidget',
            ['dropdownItems' => $dropdownItems],
            true
        );
    }

    /**
     * Return true if user with id $managerId can edit this user
     * @param int|null $managerId default to current user
     *
     * @return bool
     */
    public function canEdit($managerId = null)
    {
        if (is_null($managerId)) {
            $managerId = Permission::model()->getUserId();
        }
        /* user can update himself */
        if ($managerId == $this->uid) {
            return true;
        }
        /* forcedsuperamdin (user #1) can always update all */
        if (Permission::isForcedSuperAdmin($managerId)) {
            return true;
        }
        /* forcedsuperamdin can not be update (except by another forcedsuperamdin done before) */
        if (Permission::isForcedSuperAdmin($this->uid)) {
            return false;
        }
        /* If target user is superamdin : managingUser must be allowed to create superadmin and be parent */
        if (Permission::model()->hasGlobalPermission('superadmin', 'read', $this->uid)) {
            return Permission::model()->hasGlobalPermission('superadmin', 'create', $managerId)
                && $this->parent_id == $managerId;
        }
        /* superamin can update all other user */
        if (Permission::model()->hasGlobalPermission('superadmin', 'read', $managerId)) {
            return true;
        }
        /* Finally : simple user can update only childs users */
        return Permission::model()->hasGlobalPermission('users', 'update', $managerId)
                && $this->parent_id == $managerId;
    }

    /**
     * Set user activation status
     *
     * @param string $status
     * @return bool
     */
    public function setActivationStatus($status = 'activate')
    {
        if ($status == 'activate') {
            $this->user_status = 1;
        } else {
            $this->user_status = 0;
        }

        return $this->save();
    }
}
