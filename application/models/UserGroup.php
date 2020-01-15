<?php if (!defined('BASEPATH')) {
    die('No direct script access allowed');
}
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
    public static function model($class = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($class);
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
        return $this->db->insert('user_groups', $data);
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
     * @return boolean
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
            $id = getLastInsertID($this->tableName()); //Yii::app()->db->Insert_Id(db_table_name_nq('user_groups'),'ugid');
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
     * TODO should be in controller
     * @param string $name
     * @param string $description
     * @param integer $ugId
     * @return bool
     */
    public function updateGroup($name, $description, $ugId)
    {
        $group = UserGroup::model()->findByPk($ugId);
        $group->name = $name;
        $group->description = $description;
        $group->save();
        if ($group->getErrors()) {
                    return false;
        } else {
                    return true;
        }
    }

    /**
     * @param integer $ugId
     * @param integer $ownerId
     * @return static
     */
    public function requestEditGroup($ugId, $ownerId)
    {
        $criteria = new CDbCriteria;
        $criteria->select = '*';
        $criteria->condition = "ugid=:ugid";
        $aParams = array();
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $criteria->condition .= " AND owner_id=:ownerid";
            $aParams[':ownerid'] = $ownerId;
        }

        $aParams[':ugid'] = $ugId;
        $criteria->params = $aParams;
        $result = UserGroup::model()->find($criteria);
        return $result;
    }

    /**
     * @param integer $ugId
     * @param integer $userId
     * @return array
     */
    public function requestViewGroup($ugId, $userId)
    {
        $sQuery = "SELECT a.ugid, a.name, a.owner_id, a.description, b.uid FROM {{user_groups}} AS a LEFT JOIN {{user_in_groups}} AS b ON a.ugid = b.ugid WHERE a.ugid = :ugid";
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $sQuery .= "  AND uid = :userid ";
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
            UserInGroup::model()->deleteAllByAttributes(['ugid'=>$this->primaryKey]);
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getCountUsers()
    {
        return (int) UserInGroup::model()->countByAttributes(['ugid'=>$this->ugid]);
    }


    public function getColumns()
    {
        return array(
            array(
                'header' => gT('User group ID'),
                'name' => 'usergroup_id',
                'value'=>'$data->ugid',
                'htmlOptions' => array('class' => 'col-md-1'),
            ),

            array(
                'header' => gT('Name'),
                'name' => 'name',
                'value'=>'$data->name',
                'htmlOptions' => array('class' => 'col-md-2'),
            ),

            array(
                'header' => gT('Description'),
                'name' => 'description',
                'value'=> '$data->description',
                'htmlOptions' => array('class' => 'col-md-5'),
            ),

            array(
                'header' => gT('Owner'),
                'name' => 'owner',
                'value'=> '$data->owner->users_name',
                'htmlOptions' => array('class' => 'col-md-1'),
            ),

            array(
                'header' => gT('Members'),
                'name' => 'members',
                'value'=> '$data->countUsers',
                'htmlOptions' => array('class' => 'col-md-1'),
            ),

            array(
                'header'=>'',
                'name'=>'actions',
                'type'=>'raw',
                'value'=>'',
                'htmlOptions' => array('class' => 'col-md-2 col-xs-1 text-right'),
            ),

        );
    }

    /**
     * @return string
     */
    public function getButtons()
    {

        // View users
        $url = Yii::app()->createUrl("admin/usergroups/sa/view/ugid/$this->ugid");
        $button = '<a class="btn btn-default list-btn" data-toggle="tooltip" data-placement="left" title="'.gT('View users').'" href="'.$url.'" role="button"><span class="fa fa-list-alt" ></span></a>';

        // Edit user group
        if (Permission::model()->hasGlobalPermission('usergroups', 'update')) {
            $url = Yii::app()->createUrl("admin/usergroups/sa/edit/ugid/$this->ugid");
            $button .= ' <a class="btn btn-default list-btn" data-toggle="tooltip" data-placement="left" title="'.gT('Edit user group').'" href="'.$url.'" role="button"><span class="fa fa-pencil" ></span></a>';
        }

        // Mail to user group
        // Which permission should be checked for this button to be available?
        $url = Yii::app()->createUrl("admin/usergroups/sa/mail/ugid/$this->ugid");
        $button .= ' <a class="btn btn-default list-btn" data-toggle="tooltip" data-placement="left" title="'.gT('Email user group').'" href="'.$url.'" role="button"><span class="icon-invite" ></span></a>';

        // Delete user group
        if (Permission::model()->hasGlobalPermission('usergroups', 'delete')) {
            $button .= ' <span data-toggle="tooltip" title="'.gT('Delete user group').'"><button class="btn btn-default list-btn action__delete-group" data-placement="left" href="#delete-modal" data-toggle="modal" data-ugid="'.$this->ugid.'" role="button"><i class="fa fa-trash text-warning"></i></button></span>';
        }

        return $button;
    }


    /**
     * This function search usergroups for a user
     * If $isMine = true then usergroups are those that have been created by the current user
     * else this function provides usergroups which contain the current user
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
            'usergroup_id'=>array(
            'asc'=>'ugid',
            'desc'=>'ugid desc',
            ),
            'name'=>array(
            'asc'=>'name',
            'desc'=>'name desc',
            ),
            'description'=>array(
            'asc'=>'description',
            'desc'=>'description desc',
            ),
            'owner'=>array(
            'asc'=>'users.users_name',
            'desc'=>'users.users_name desc',
            ),
            'members'=>array(
            'asc'=>'member_count',
            'desc'=>'member_count desc',
            ),
        );

        $user_in_groups_table = UserInGroup::model()->tableName();
        $member_count_sql = "(SELECT count(*) FROM $user_in_groups_table AS users_in_groups WHERE users_in_groups.ugid = t.ugid)";

        $criteria = new CDbCriteria;

        // select
        $criteria->select = array(
            '*',
            $member_count_sql." as member_count",
        );

        $criteria->join .= 'LEFT JOIN {{users}} AS users ON ( users.uid = t.owner_id )';

        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            if ($isMine) {
                $criteria->addCondition("t.owner_id=".App()->user->getId(), "AND");
            } else {
                $criteria->addCondition("t.owner_id<>".App()->user->getId(), "AND");
                $criteria->addCondition("t.ugid IN (SELECT ugid FROM $user_in_groups_table WHERE ".$user_in_groups_table.".uid = ".App()->user->getId().")", "AND");
            }
        }

        $dataProvider = new CActiveDataProvider('UserGroup', array(
            'sort'=>$sort,
            'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=>$pageSize,
            ),
        ));

        return $dataProvider;
    }


    /**
     * Checks whether the specified UID is part of that group
     * @param integer $uid
     * @return bool
     */
    public function hasUser($uid)
    {
        // superadmin is part of all groups
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            return true;
        }
        $userInGroup = UserInGroup::model()->findByAttributes(['ugid'=>$this->ugid], 'uid=:uid', [':uid'=>$uid]);
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



}
