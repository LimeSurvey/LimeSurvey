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
 * Class UserInGroup
 * @property integer $ugid UserGroup ID
 * @property int $uid User ID
 * @property User $users Group ownre user
 * @property UserGroup $group
 */
class UserInGroup extends LSActiveRecord
{
    /**
     * @inheritdoc
     * @return CActiveRecord
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{user_in_groups}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return array('ugid', 'uid');
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('uid, ugid', 'required'),
            array('uid, ugid', 'numerical', 'integerOnly' => true),
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            // TODO should be singular
            'users' => array(self::BELONGS_TO, 'User', 'uid'),
            'group' => array(self::BELONGS_TO, 'UserGroup', 'ugid'),
        );
    }

    /**
     * @param array $data
     * @return bool
     * @deprecated at 2018-02-03 use $model->attributes = $data && $model->save()
     */
    public function insertRecords($data)
    {
        $changedRows = Yii::app()->db->createCommand()->insert($this->tableName(), $data);
        return (bool) $changedRows;
    }

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
}
