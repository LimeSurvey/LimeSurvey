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
 * Class UserApiToken
 *
 * Personal API token of a user. It can be used instead of the password to log in to
 * RemoteControl or the REST API, e.g. by users with two-factor authentication.
 * Only a SHA-256 hash of the token is stored; the token itself is shown once when it is created.
 * All dates are stored in UTC.
 *
 * @property integer $id
 * @property integer $uid Owner of the token
 * @property string $name Name given by the user to recognize the token
 * @property string $token_hash SHA-256 hash of the token
 * @property string $created
 * @property string|null $last_used
 * @property string|null $expires Null if the token does not expire
 *
 * @property User $user
 * @property UserApiTokenPermission[] $permissions
 */
class UserApiToken extends LSActiveRecord
{
    /** @var string Prefix of every API token, makes tokens recognizable (e.g. for secret scanners) */
    public const TOKEN_PREFIX = 'lsat_';

    /** @var int Number of random bytes of a token */
    private const TOKEN_BYTES = 24;

    /** @var int[] Allowed lifetimes in days for a new token, 0 means no expiration */
    public const LIFETIMES = [30, 90, 365, 0];

    /** @var string[] Global permissions that can't be given to a token */
    private const EXCLUDED_GLOBAL_PERMISSIONS = ['auth_db'];

    /** @var UserApiToken|null The token the current API session was opened with */
    private static $activeToken = null;

    /** @var int Greater than 0 while the permissions of the user are checked without the token scope */
    private static $scopeSuspended = 0;

    /** @var array|null The permissions of this token, indexed by entity, entity ID and permission name */
    private $permissionMap = null;

    /**
     * @inheritdoc
     * @return UserApiToken
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
        return '{{user_api_tokens}}';
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
            ['uid, name, token_hash, created', 'required'],
            ['uid', 'numerical', 'integerOnly' => true],
            ['name', 'length', 'max' => 150],
            ['name', 'filter', 'filter' => 'trim'],
            ['token_hash', 'length', 'is' => 64],
            ['token_hash', 'unique'],
        ];
    }

    /** @inheritdoc */
    public function relations()
    {
        return [
            'user' => [self::BELONGS_TO, 'User', 'uid'],
            'permissions' => [self::HAS_MANY, 'UserApiTokenPermission', 'token_id'],
        ];
    }

    /**
     * Check if a string has the format of an API token.
     * Used to tell an API token apart from a password on API logins.
     *
     * @param string $token The string to check
     * @return bool True if the string looks like an API token
     */
    public static function isTokenFormat($token)
    {
        return is_string($token)
            && preg_match('/^' . self::TOKEN_PREFIX . '[0-9a-f]{' . (self::TOKEN_BYTES * 2) . '}$/', $token) === 1;
    }

    /**
     * Get the hash of a token as it is stored in the database.
     *
     * @param string $token The plain token
     * @return string SHA-256 hex digest of the token
     */
    public static function hashToken($token)
    {
        return hash('sha256', (string) $token);
    }

    /**
     * Create and save a new API token for a user.
     *
     * @param int $uid The owner of the token
     * @param string $name Name of the token
     * @param int $lifetimeDays Number of days the token is valid, 0 for no expiration
     * @return array{0: UserApiToken, 1: string} The saved token record and the plain token, which is not stored anywhere
     * @throws CException If the token could not be saved
     */
    public static function createForUser($uid, $name, $lifetimeDays)
    {
        $plainToken = self::TOKEN_PREFIX . bin2hex(random_bytes(self::TOKEN_BYTES));
        $now = gmdate('Y-m-d H:i:s');

        $apiToken = new self();
        $apiToken->uid = (int) $uid;
        $apiToken->name = (string) $name;
        $apiToken->token_hash = self::hashToken($plainToken);
        $apiToken->created = $now;
        $apiToken->expires = $lifetimeDays > 0
            ? dateShiftRelative($now, 'Y-m-d H:i:s', '+' . (int) $lifetimeDays . ' days')
            : null;
        if (!$apiToken->save()) {
            throw new CException(CHtml::errorSummary($apiToken, ''));
        }
        return [$apiToken, $plainToken];
    }

    /**
     * Find the token of a user matching a plain token, if it is not expired.
     *
     * @param int $uid The owner of the token
     * @param string $plainToken The plain token sent by the client
     * @return UserApiToken|null The matching token, or null if there is no valid one
     */
    public function findValidToken($uid, $plainToken)
    {
        if (!self::isTokenFormat($plainToken)) {
            return null;
        }
        $apiToken = $this->findByAttributes([
            'uid' => (int) $uid,
            'token_hash' => self::hashToken($plainToken),
        ]);
        if ($apiToken === null || $apiToken->isExpired()) {
            return null;
        }
        return $apiToken;
    }

    /**
     * Check if the token is expired.
     *
     * @return bool True if the token has an expiration date that has passed
     */
    public function isExpired()
    {
        if (empty($this->expires)) {
            return false;
        }
        return new DateTime((string) $this->expires) <= new DateTime(gmdate('Y-m-d H:i:s'));
    }

    /**
     * Store the current time as the last time the token was used.
     *
     * @return void
     */
    public function markUsed()
    {
        $this->last_used = gmdate('Y-m-d H:i:s');
        $this->saveAttributes(['last_used' => $this->last_used]);
    }

    /**
     * Format one of the date attributes for display in the user's date format and time zone.
     *
     * @param string $attribute Name of the date attribute (created, last_used or expires)
     * @return string The formatted date, or an empty string if the date is not set
     */
    public function getFormattedDate($attribute)
    {
        if (empty($this->$attribute)) {
            return '';
        }
        $dateFormat = getDateFormatData(Yii::app()->session['dateformat']);
        return (string) convertDateTimeFormat(
            getDateOfUTC((string) $this->$attribute),
            'Y-m-d H:i:s',
            $dateFormat['phpdate'] . ' H:i'
        );
    }

    /**
     * Delete the permissions of the token and end the API sessions opened with it.
     *
     * @return void
     */
    #[\Override]
    protected function afterDelete()
    {
        parent::afterDelete();
        UserApiTokenPermission::model()->deleteAllByAttributes(['token_id' => $this->id]);
        Session::model()->deleteAllByAttributes(['api_token_id' => $this->id]);
    }

    /**
     * Get the permissions that can be given to a token, i.e. the base permissions without the
     * ones that make no sense for a token.
     *
     * @return array{global: array, survey: array} Global and survey base permissions, as returned by Permission
     */
    public static function getAvailablePermissions()
    {
        return [
            'global' => array_diff_key(
                Permission::getGlobalBasePermissions(),
                array_flip(self::EXCLUDED_GLOBAL_PERMISSIONS)
            ),
            'survey' => Permission::getSurveyBasePermissions(),
        ];
    }

    /**
     * Replace the permissions of the token.
     * Only permissions the owner of the token has are stored, so a token never gets more than its owner.
     *
     * @param array $permissions Requested permissions, as [entity => [entityId => [permission => [crud => bool]]]],
     *                           entity being 'global' (entity ID 0) or 'survey' (entity ID = survey ID)
     * @return void
     * @throws CException If a permission could not be saved
     */
    public function setPermissions(array $permissions)
    {
        $available = self::getAvailablePermissions();
        UserApiTokenPermission::model()->deleteAllByAttributes(['token_id' => $this->id]);
        foreach ($permissions as $entity => $entityPermissions) {
            if (!isset($available[$entity]) || !is_array($entityPermissions)) {
                continue;
            }
            foreach ($entityPermissions as $entityId => $entityIdPermissions) {
                $entityId = $entity === 'global' ? 0 : (int) $entityId;
                if (!is_array($entityIdPermissions) || ($entity === 'survey' && !Survey::model()->findByPk($entityId))) {
                    continue;
                }
                foreach ($entityIdPermissions as $permission => $cruds) {
                    if (!isset($available[$entity][$permission]) || !is_array($cruds)) {
                        continue;
                    }
                    $tokenPermission = new UserApiTokenPermission();
                    $tokenPermission->token_id = $this->id;
                    $tokenPermission->entity = $entity;
                    $tokenPermission->entity_id = $entityId;
                    $tokenPermission->permission = $permission;
                    $hasAny = false;
                    foreach (UserApiTokenPermission::CRUD as $crud) {
                        $granted = !empty($cruds[$crud])
                            && !empty($available[$entity][$permission][$crud])
                            && $this->ownerHasPermission($entity, $entityId, $permission, $crud);
                        $tokenPermission->{$crud . '_p'} = $granted ? 1 : 0;
                        $hasAny = $hasAny || $granted;
                    }
                    if ($hasAny && !$tokenPermission->save()) {
                        throw new CException(CHtml::errorSummary($tokenPermission, ''));
                    }
                }
            }
        }
        $this->permissionMap = null;
    }

    /**
     * Get the permissions to show in the form of the token: the permission details the owner has,
     * and whether the token has them. Permissions the owner doesn't have at all are left out.
     *
     * @param string $entity 'global' or 'survey'
     * @param int $entityId 0 for global permissions, the survey ID for survey permissions
     * @return array<string, array{title: string, description: string, cruds: array<string, bool>}> Indexed by permission name
     */
    public function getPermissionGrid($entity, $entityId = 0)
    {
        $grid = [];
        foreach (self::getAvailablePermissions()[$entity] as $permission => $definition) {
            $cruds = [];
            foreach (UserApiTokenPermission::CRUD as $crud) {
                if (!empty($definition[$crud]) && $this->ownerHasPermission($entity, $entityId, $permission, $crud)) {
                    $cruds[$crud] = !$this->isNewRecord && $this->hasTokenPermission($entity, $entityId, $permission, $crud);
                }
            }
            if (!empty($cruds)) {
                $grid[$permission] = [
                    'title' => (string) $definition['title'],
                    'description' => (string) $definition['description'],
                    'cruds' => $cruds,
                ];
            }
        }
        return $grid;
    }

    /**
     * Get the surveys the token has survey specific permissions for.
     *
     * @return Survey[] The surveys, ordered by survey ID
     */
    public function getSurveysWithPermissions()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'entity_id';
        $criteria->distinct = true;
        $criteria->compare('token_id', $this->id);
        $criteria->compare('entity', 'survey');
        $surveyIds = UserApiTokenPermission::model()->getCommandBuilder()
            ->createFindCommand(UserApiTokenPermission::model()->tableName(), $criteria)
            ->queryColumn();
        if (empty($surveyIds)) {
            return [];
        }
        return Survey::model()->findAllByPk($surveyIds, ['order' => 'sid']);
    }

    /**
     * Check if the owner of the token has a permission, without the scope of any API token.
     *
     * @param string $entity 'global' or 'survey'
     * @param int $entityId 0 for global permissions, the survey ID for survey permissions
     * @param string $permission Name of the permission
     * @param string $crud The permission detail: 'create', 'read', 'update', 'delete', 'import' or 'export'
     * @return bool True if the owner has the permission
     */
    public function ownerHasPermission($entity, $entityId, $permission, $crud)
    {
        return self::withoutScope(function () use ($entity, $entityId, $permission, $crud) {
            if ($entity === 'survey') {
                $survey = Survey::model()->findByPk($entityId);
                return $survey !== null && $survey->hasPermission($permission, $crud, $this->uid);
            }
            return Permission::model()->hasPermission($entityId, $entity, $permission, $crud, $this->uid);
        });
    }

    /**
     * Check if a permission detail is set on the token itself.
     *
     * @param string $entity Entity name, e.g. 'global' or 'survey'
     * @param int $entityId 0 for global permissions, the entity ID otherwise
     * @param string $permission Name of the permission
     * @param string $crud The permission detail: 'create', 'read', 'update', 'delete', 'import' or 'export'
     * @return bool True if the token has the permission detail
     */
    public function hasTokenPermission($entity, $entityId, $permission, $crud)
    {
        if ($this->permissionMap === null) {
            $this->permissionMap = [];
            foreach (UserApiTokenPermission::model()->findAllByAttributes(['token_id' => $this->id]) as $tokenPermission) {
                $this->permissionMap[$tokenPermission->entity][(int) $tokenPermission->entity_id][$tokenPermission->permission] = $tokenPermission->attributes;
            }
        }
        return !empty($this->permissionMap[$entity][(int) $entityId][$permission][$crud . '_p']);
    }

    /**
     * Check if the token allows a permission. Uses the same rules as the user permissions:
     * the global superadmin permission allows everything, and a survey permission is also
     * allowed by the matching global 'surveys' permission (see Survey::hasPermission()).
     * This is only the scope of the token: the user must have the permission as well.
     *
     * @param int $entityId The entity ID, 0 for global permissions
     * @param string $entityName The entity name, e.g. 'global' or 'survey'
     * @param string $permission Name of the permission
     * @param string $crud The permission detail: 'create', 'read', 'update', 'delete', 'import' or 'export'
     * @return bool True if the token allows the permission
     */
    public function allows($entityId, $entityName, $permission, $crud = 'read')
    {
        $entityName = strtolower((string) $entityName);
        if (!in_array($crud, UserApiTokenPermission::CRUD, true)) {
            return false;
        }
        if ($this->hasTokenPermission('global', 0, 'superadmin', 'read')) {
            return true;
        }
        if ($this->hasTokenPermission($entityName, (int) $entityId, $permission, $crud)) {
            return true;
        }
        if ($entityName === 'survey') {
            return $this->hasTokenPermission('global', 0, 'surveys', Survey::getGlobalSurveysCrud($permission, $crud));
        }
        return false;
    }

    /**
     * Set the token the current API session was opened with. From then on, permission checks
     * for its owner are limited to the permissions of the token.
     *
     * @param UserApiToken|null $apiToken The token, or null to remove the scope
     * @return void
     */
    public static function activate(?UserApiToken $apiToken)
    {
        self::$activeToken = $apiToken;
        Permission::resetSurveyPermissionCache();
    }

    /**
     * Check if the current API session was opened with an API token.
     *
     * @return bool True if a token is active, even if its scope is suspended at the moment
     */
    public static function isActive()
    {
        return self::$activeToken !== null;
    }

    /**
     * Get the surveys this token can read, to limit lists of surveys built with SQL.
     *
     * @return int[]|null The survey IDs, or null if the token can read all surveys
     */
    public function getReadableSurveyIds()
    {
        if ($this->allows(0, 'global', 'surveys', 'read')) {
            return null;
        }
        $surveyIds = [];
        foreach (UserApiTokenPermission::model()->findAllByAttributes(['token_id' => $this->id, 'entity' => 'survey', 'permission' => 'survey', 'read_p' => 1]) as $tokenPermission) {
            $surveyIds[] = (int) $tokenPermission->entity_id;
        }
        return $surveyIds;
    }

    /**
     * Get the survey groups this token can read, to limit lists of survey groups built with SQL.
     * Without the global 'surveysgroups' read permission, these are the groups of the surveys the token can read.
     *
     * @return int[]|null The survey group IDs, or null if the token can read all survey groups
     */
    public function getReadableSurveyGroupIds()
    {
        if ($this->allows(0, 'global', 'surveysgroups', 'read')) {
            return null;
        }
        $surveyIds = $this->getReadableSurveyIds();
        if ($surveyIds === null) {
            return null;
        }
        $criteria = new CDbCriteria();
        $criteria->select = 'gsid';
        $criteria->distinct = true;
        $criteria->addInCondition('sid', $surveyIds);
        return array_map('intval', Survey::model()->getCommandBuilder()
            ->createFindCommand(Survey::model()->tableName(), $criteria)
            ->queryColumn());
    }

    /**
     * Get the token whose scope applies to a permission check for a user.
     *
     * @param int|null $userId The user the permission is checked for, null for the current user
     * @return UserApiToken|null The active token if its scope applies, null otherwise
     */
    public static function getScopeFor($userId = null)
    {
        $apiToken = self::$activeToken;
        if ($apiToken === null || self::$scopeSuspended > 0) {
            return null;
        }
        if (!empty($userId) && (int) $userId !== (int) $apiToken->uid) {
            return null;
        }
        return $apiToken;
    }

    /**
     * Check if the scope of the active token allows a global permission, for access granted outside of
     * the permission system (e.g. owners of participants in the central participant database).
     *
     * @param string $permission Name of the global permission
     * @param string $crud The permission detail: 'create', 'read', 'update', 'delete', 'import' or 'export'
     * @return bool True if no token scope applies or the token allows the permission
     */
    public static function scopeAllowsGlobal($permission, $crud = 'read')
    {
        $apiToken = self::getScopeFor();
        return $apiToken === null || $apiToken->allows(0, 'global', $permission, $crud);
    }

    /**
     * Run a permission check without the scope of the active token, e.g. to get the user part
     * of the check or to run the checks nested in another permission check.
     *
     * @param callable $check The permission check
     * @return mixed The result of the check
     */
    public static function withoutScope(callable $check)
    {
        self::$scopeSuspended++;
        try {
            return $check();
        } finally {
            self::$scopeSuspended--;
        }
    }

    /**
     * Activate the token an API session was opened with, if any.
     * If the token was revoked or is expired, the session is deleted.
     *
     * @param Session $session The API session
     * @param User $user The user the session belongs to
     * @return bool False if the session is no longer valid because of its token
     */
    public static function activateForSession(Session $session, User $user)
    {
        if (empty($session->api_token_id)) {
            self::activate(null);
            return true;
        }
        $apiToken = self::model()->findByPk($session->api_token_id);
        if ($apiToken === null || $apiToken->isExpired() || (int) $apiToken->uid !== (int) $user->uid) {
            $session->delete();
            self::activate(null);
            return false;
        }
        self::activate($apiToken);
        return true;
    }
}
