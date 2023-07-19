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
 */

/**
 * Class UserGroup
 *
 * @property integer $ugid Model ID (primary key)
 * @property string $name  Group name (max 20 chars)
 * @property string $description Group description
 * @property integer $owner_id Group owner user ID
 *
 * @property User[] $users Users of this group
 * @property User $owner Group ownre user
 * @property integer $countUsers Count of users in this group
 */
class UserGroup extends LSActiveRecord
{
    /** @var integer $member_count  */
    public $member_count = null;

    /**
     * @inheritdoc
     * @return UserGroup
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
        return '{{user_groups}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'ugid';
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('name', 'required'),
            array('ugid, owner_id', 'numerical', 'integerOnly' => true),
            array('name', 'unique', 'message' => gT("Failed to add group! Group already exists.")),
            array(
                'name',
                'length',
                'min' => 1,
                'max' => 20,
                'tooShort' => gT("Name can not be empty."),
                'tooLong' => gT('Failed to add group! Group name length more than 20 characters.')
            ),
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'users' => array(self::MANY_MANY, 'User', '{{user_in_groups}}(ugid, uid)'), // Louis: this is the correct relation
            'owner' => array(self::BELONGS_TO, 'User', 'owner_id'),
        );
    }

    /**
     * @param $data
     * @return mixed
     * @deprecated at 2018-02-03 use $model->attributes = $data && $model->save()
     */
    public function insertRecords($data)
    {
        return Yii::app()->db->insert('user_groups', $data);
    }

    // TODO seems to be unused, probably shouldn't be done like that

    /**
     * @param string[] $fields
     * @param string $from
     */
    public function join($fields, $from, $condition = false, $join = false, $order = false)
    {
        $user = Yii::app()->db->createCommand();
        foreach ($fields as $field) {
            $user->select($field);
        }

        $user->from($from);

        if ($condition != false) {
            $user->where($condition);
        }

        if ($order != false) {
            $user->order($order);
        }

        if (isset($join['where'], $join['on'])) {
            if (isset($join['left'])) {
                $user->leftjoin($join['where'], $join['on']);
            } else {
                $user->join($join['where'], $join['on']);
            }
        }

        $data = $user->queryRow();
        return $data;
    }


    /**
     * @param string $group_name
     * @param string $group_description
     * @return int
     * @todo should use save() and afterSave() methods!!
     */
    public function addGroup($group_name, $group_description)
    {
        $iLoginID = intval(Yii::app()->session['loginID']);
        $iquery = "INSERT INTO {{user_groups}} (name, description, owner_id) VALUES(:group_name, :group_desc, :loginID)";
        $command = Yii::app()->db->createCommand($iquery)->bindParam(":group_name", $group_name, PDO::PARAM_STR)
            ->bindParam(":group_desc", $group_description, PDO::PARAM_STR)
            ->bindParam(":loginID", $iLoginID, PDO::PARAM_INT);
        $result = $command->query();
        if ($result) {
            //Checked
            $id = (int) getLastInsertID($this->tableName());
            if ($id > 0) {
                $user_in_groups_query = 'INSERT INTO {{user_in_groups}} (ugid, uid) VALUES (:ugid, :uid)';
                Yii::app()->db->createCommand($user_in_groups_query)
                    ->bindParam(":ugid", $id, PDO::PARAM_INT)
                    ->bindParam(":uid", $iLoginID, PDO::PARAM_INT)
                    ->query();
            }
            return $id;
        } else {
            return -1;
        }
    }

    /**
     * TODO should be in controller?
     * When user is allowed to edit the group,
     * it will be updated in the database.
     * Returns true, when it was possible to save.
     * @param string $name
     * @param string $description
     * @param integer $ugId
     * @return bool
     */
    public function updateGroup($name, $description, $ugId)
    {
        $group = $this->requestEditGroup($ugId, App()->user->id);
        if ($group !== null) {
            $group->name = $name;
            $group->description = $description;
            $group->save();
            if ($group->getErrors()) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * Works as permission check on db level for editing user groups.
     * The user group needs to exist, and if the user is not a superadmin,
     * user also has to be the owner of that group.
     * If successful, the user group is returned.
     *
     * @param integer $ugId
     * @param integer $ownerId
     * @return static
     */
    public function requestEditGroup($ugId, $ownerId)
    {
        $criteria = new CDbCriteria();
        $criteria->select = '*';
        $criteria->condition = "ugid=:ugid";
        $aParams = array();
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $criteria->condition .= " AND owner_id=:ownerid";
            $aParams[':ownerid'] = $ownerId;
        }

        $aParams[':ugid'] = $ugId;
        $criteria->params = $aParams;

        return UserGroup::model()->find($criteria);
    }

    /**
     * @param integer $ugId
     * @param integer $userId
     * @return array
     * @deprecated Not needed anymore
     */
    public function requestViewGroup($ugId, $userId)
    {
        $sQuery = "SELECT a.ugid, a.name, a.owner_id, a.description, b.uid FROM {{user_groups}} AS a LEFT JOIN {{user_in_groups}} AS b ON a.ugid = b.ugid WHERE a.ugid = :ugid";
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $sQuery .= "  AND (owner_id = :userid OR uid = :userid) ";
        }
        $sQuery .= " ORDER BY name";
        $command = Yii::app()->db->createCommand($sQuery)->bindParam(":ugid", $ugId, PDO::PARAM_INT);
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $command->bindParam(":userid", $userId, PDO::PARAM_INT);
        }
        return $command->query()->readAll();
    }

    /**
     * @param integer $ugId
     * @param integer $ownerId
     * @return bool
     * @deprecated since 2018-04-21 use $this->delete and do the permissions check in controller!!
     */
    public function deleteGroup($ugId, $ownerId)
    {
        $aParams = array();
        $aParams[':ugid'] = $ugId;
        $sCondition = "ugid = :ugid";
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $sCondition .= " AND owner_id=:ownerid";
            $aParams[':ownerid'] = $ownerId;
        }

        $group = UserGroup::model()->find($sCondition, $aParams);
        $group->delete();

        if ($group->getErrors()) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        if (parent::delete()) {
            UserInGroup::model()->deleteAllByAttributes(['ugid' => $this->primaryKey]);
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getCountUsers()
    {
        return (int) UserInGroup::model()->countByAttributes(['ugid' => $this->ugid]);
    }


    public function getColumns()
    {
        return array(
            array(
                'header' => gT('User group ID'),
                'name' => 'usergroup_id',
                'value' => '$data->ugid',
                'htmlOptions' => array('class' => 'col-lg-1'),
            ),

            array(
                'header' => gT('Name'),
                'name' => 'name',
                'value' => '$data->name',
                'htmlOptions' => array('class' => ''),
            ),

            array(
                'header' => gT('Description'),
                'name' => 'description',
                'value' => '$data->description',
                'htmlOptions' => array('class' => 'col-lg-5'),
            ),

            array(
                'header' => gT('Owner'),
                'name' => 'owner',
                'value' => '$data->owner ? $data->owner->users_name : gT("(Deleted user)")',
                'htmlOptions' => array('class' => 'col-lg-1'),
            ),

            array(
                'header' => gT('Members'),
                'name' => 'members',
                'value' => '$data->countUsers',
                'htmlOptions' => array('class' => 'col-lg-1'),
            ),

            array(
                'header' => '',
                'name' => 'actions',
                'type' => 'raw',
                'value' => '',
                'htmlOptions' => array('class' => ''),
            ),

        );
    }

    /**
     * Returns the buttons for grid view
     *
     * @todo where is this used??
     *
     * @return string
     */
    public function getButtons()
    {
        $permissionUsergroupsEdit = Permission::model()->hasGlobalPermission('usergroups', 'update');
        $permissionUsergroupsDelete = Permission::model()->hasGlobalPermission('usergroups', 'delete');

        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Edit user group'),
            'iconClass'        => 'ri-pencil-fill',
            'url'              => Yii::app()->createUrl("userGroup/edit/ugid/$this->ugid"),
            'enabledCondition' => $permissionUsergroupsEdit
        ];

        $dropdownItems[] = [
            'title'            => gT('View users'),
            'iconClass'        => 'ri-list-unordered',
            'url'              => Yii::app()->createUrl("userGroup/viewGroup/ugid/$this->ugid"),
        ];
        $dropdownItems[] = [
            'title'            => gT('Email user group'),
            'iconClass'        => 'ri-mail-send-fill',
            'url'              => Yii::app()->createUrl("userGroup/mailToAllUsersInGroup/ugid/$this->ugid"),
            'enabledCondition' => $permissionUsergroupsEdit
        ];
        $deletePostData = json_encode(['ugid' => $this->ugid]);
        $dropdownItems[] = [
            'title'            => gT('Delete user group'),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => $permissionUsergroupsDelete,
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-post-url'  => App()->createUrl("userGroup/deleteGroup"),
                'data-post-datas' => $deletePostData,
                'data-message'   => sprintf(gt("Are you sure you want to delete user group '%s'?"), CHtml::encode($this->name)),
                'data-bs-target' => "#confirmation-modal",
                'data-btnclass'  => 'btn-danger',
                'data-btntext'   => gt('Delete'),
                'data-title'     => gt('Delete user group')
            ]
        ];

        return App()->getController()->widget(
            'ext.admin.grid.GridActionsWidget.GridActionsWidget',
            ['dropdownItems' => $dropdownItems],
            true
        );
    }

    /**
     * Returns the buttons for grid view
     * @return array
     */
    public function getManagementButtons(): array
    {
        return [
            array(
                'header'      => gT('User group ID'),
                'name'        => 'usergroup_id',
                'value'       => '$data->ugid',
                'htmlOptions' => array('class' => ''),
            ),

            array(
                'header'      => gT('Name'),
                'name'        => 'name',
                'value'       => '$data->name',
                'htmlOptions' => array('class' => ''),
            ),

            array(
                'header'      => gT('Description'),
                'name'        => 'description',
                'value'       => '$data->description',
                'htmlOptions' => array('class' => ''),
            ),

            array(
                'header'      => gT('Owner'),
                'name'        => 'owner',
                'value'       => '$data->owner ? $data->owner->users_name : gT("(Deleted user)")',
                'htmlOptions' => array('class' => ''),
            ),

            array(
                'header'      => gT('Members'),
                'name'        => 'members',
                'value'       => '$data->countUsers',
                'htmlOptions' => array('class' => ''),
            ),
            array(
                'header'      => gT('Actions'),
                'name'        => 'buttons',
                'type'        => 'raw',
                'value'       => '$data->buttons',
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'text-center button-column ls-sticky-column'],
            ),
        ];
    }


    /**
     * This function searches user groups for a user
     * If $isMine = true then user groups are those that have been created by the current user
     * else this function provides s which contain the current user
     *
     * The object \CActiveDataProvider returned is used to generate the view in application/views/admin/usergroup/usergroups_view.php
     *
     * @param bool $isMine
     * @return \CActiveDataProvider
     */
    public function searchMine($isMine)
    {
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);

        $sort = new CSort();
        $sort->attributes = array(
            'usergroup_id' => array(
                'asc' => 'ugid',
                'desc' => 'ugid desc',
            ),
            'name' => array(
                'asc' => 'name',
                'desc' => 'name desc',
            ),
            'description' => array(
                'asc' => 'description',
                'desc' => 'description desc',
            ),
            'owner' => array(
                'asc' => 'users.users_name',
                'desc' => 'users.users_name desc',
            ),
            'members' => array(
                'asc' => 'member_count',
                'desc' => 'member_count desc',
            ),
        );

        $user_in_groups_table = UserInGroup::model()->tableName();
        $member_count_sql = "(SELECT count(*) FROM $user_in_groups_table AS users_in_groups WHERE users_in_groups.ugid = t.ugid)";

        $criteria = new CDbCriteria();

        // select
        $criteria->select = array(
            '*',
            $member_count_sql . " as member_count",
        );

        $criteria->join .= 'LEFT JOIN {{users}} AS users ON ( users.uid = t.owner_id )';

        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            if ($isMine) {
                $criteria->addCondition("t.owner_id=" . App()->user->getId(), "AND");
            } else {
                $criteria->addCondition("t.owner_id<>" . App()->user->getId(), "AND");
                $criteria->addCondition("t.ugid IN (SELECT ugid FROM $user_in_groups_table WHERE " . $user_in_groups_table . ".uid = " . App()->user->getId() . ")", "AND");
            }
        }

        $dataProvider = new CActiveDataProvider('UserGroup', array(
            'sort' => $sort,
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));

        return $dataProvider;
    }


    /**
     * Checks whether the specified UID is part of that group
     *
     * @param integer $uid
     * @return bool
     */
    public function hasUser($uid)
    {
        $userInGroup = UserInGroup::model()->findByAttributes(['ugid' => $this->ugid], 'uid=:uid', [':uid' => $uid]);
        if ($userInGroup) {
            return true;
        }
        return false;
    }



    /**
     * Checks whether the specified UID is part of that group
     * @param integer $uid
     * @return bool
     */
    public function addUser($uid)
    {
        $oModel = new UserInGroup();
        $oModel->uid = $uid;
        $oModel->ugid = $this->ugid;

        return $oModel->save();
    }

    /**
     * Sending emails to all users of a specific group.
     * Returns information about success/errors for sending emails to all single users
     *
     * @param $ugid     integer
     * @param $subject  string  subject of email
     * @param $body     string  body of email
     * @param $copy     integer  1->send copy to user
     * @return string
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendUserEmails($ugid, $subject, $body, $copy)
    {
        $sendEmailSuccessErrors = [];

        $criteria = new CDbCriteria();
        $criteria->compare('ugid', $ugid)->addNotInCondition('users.uid', array(Yii::app()->session['loginID']));
        /**@var $eruresult UserInGroup[] */
        $usersInGroup = UserInGroup::model()->with('users')->findAll($criteria);

        $mailer = \LimeMailer::getInstance(\LimeMailer::ResetComplete);
        $mailer->emailType = "mailsendusergroup";
        $oUserFrom = User::model()->findByPk(Yii::app()->session['loginID']);
        $fromName = empty($oUserFrom->full_name) ? $oUserFrom->users_name : $oUserFrom->full_name;
        $mailer->setFrom($oUserFrom->email, $fromName);

        // Add the sender to the list of users in order to receive a copy
        if ($copy == 1) {
            $oAuxUserInGroup = new UserInGroup();
            $oAuxUserInGroup->users = $oUserFrom;
            $usersInGroup[] = $oAuxUserInGroup;
        }
        $mailer->Subject = $subject;
        $body = str_replace("\n.", "\n..", (string) $body);
        $body = wordwrap($body, 70);
        $mailer->Body = $body;
        $cnt = 0;
        foreach ($usersInGroup as $userInGroup) {
            /**@var $userInGroup UserInGroup */
            /* Set just needed part */
            $mailer->setTo($userInGroup->users->email, $userInGroup->users->users_name);
            $sendEmailSuccessErrors[$cnt]['username'] = $userInGroup->users->users_name;
            if ($mailer->sendMessage()) {
                $sendEmailSuccessErrors[$cnt]['success'] = true;
            } else {
                $sendEmailSuccessErrors[$cnt]['success'] = false;
                $sendEmailSuccessErrors[$cnt]['msg'] = sprintf(
                    gT("Email to %s failed. Error Message : %s"),
                    \CHtml::encode("{$userInGroup->users->users_name} <{$userInGroup->users->email}>"),
                    $mailer->getError()
                );
            }
            $mailer->ErrorInfo = '';
            $cnt++;
        }

        $msgToUser = gT('Sending emails to users(sucess/errors):') . "<br>";
        foreach ($sendEmailSuccessErrors as $emaiLResult) {
            $msgToUser .= $emaiLResult['username'] . ': ';
            if ($emaiLResult['success']) {
                $msgToUser .= gT('Sending successful') . "<br>";
            } else {
                throw new Exception("Failed to send mail");
            }
        }

        return $msgToUser;
    }
}
