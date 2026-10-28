<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
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
 * Class UserApiTokenPermission
 *
 * A permission of an API token. Same structure and meaning as Permission, but for an API token
 * instead of a user. A token never gets more than its owner: the permissions of the token only
 * narrow down the permissions of the user (see UserApiToken::allows()).
 *
 * @property integer $id
 * @property integer $token_id
 * @property string $entity 'global' or 'survey'
 * @property integer $entity_id 0 for global permissions, the survey ID for survey permissions
 * @property string $permission Name of the permission, e.g. 'surveys' or 'responses'
 * @property integer $create_p
 * @property integer $read_p
 * @property integer $update_p
 * @property integer $delete_p
 * @property integer $import_p
 * @property integer $export_p
 */
class UserApiTokenPermission extends LSActiveRecord
{
    /** @var string[] The permission details, same as in Permission */
    public const CRUD = ['create', 'read', 'update', 'delete', 'import', 'export'];

    /**
     * @inheritdoc
     * @return UserApiTokenPermission
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
        return '{{user_api_token_permissions}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            ['token_id, entity, entity_id, permission', 'required'],
            ['token_id, entity_id', 'numerical', 'integerOnly' => true],
            ['entity', 'in', 'range' => ['global', 'survey']],
            ['permission', 'length', 'max' => 100],
            ['create_p, read_p, update_p, delete_p, import_p, export_p', 'in', 'range' => [0, 1]],
        ];
    }
}
