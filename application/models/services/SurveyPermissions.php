<?php

namespace LimeSurvey\Models\Services;

use CActiveDataProvider;
use CSort;
use Yii;

/**
 * This class is responsible for the relationship between permissions, users and surveys.
 * It could be handled as a specific permissions system for surveys.
 *
 */
class SurveyPermissions
{
    /** @var \Survey */
    private $survey;

    /** @var bool */
    private $userControlSameGroupPolicy;

    /**
     * SurveyPermissions constructor.
     *
     * @param \Survey $survey
     * @param bool $userControlSameGroupPolicy
     */
    public function __construct(\Survey $survey, $userControlSameGroupPolicy)
    {
        $this->survey = $survey;
        $this->userControlSameGroupPolicy = $userControlSameGroupPolicy;
    }

    /**
     * Returns an array with data about users and their specific permissions
     * for the survey. The returned array could be empty if there are no users
     * with permissions for this survey.
     *
     * @return \Permission[]
     */
    public function getUsersSurveyPermissions()
    {
        $userPermissionCriteria = $this->getUserPermissionCriteria();
        return \Permission::model()->findAll($userPermissionCriteria);
    }

    /**
     * @return CActiveDataProvider
     */
    public function getUsersSurveyPermissionsDataProvider(): CActiveDataProvider
    {
        $pageSize = App()->user->getState('pageSize', App()->params['defaultPageSize']);
        $userPermissionCriteria = $this->getUserPermissionCriteria();
        // $sort = new CSort();
        // $sort->attributes = array(
        //     'users_name' => array(
        //         'asc' => 'users_name asc',
        //         'desc' => 'users_name desc',
        //     ),
        //     'full_name' => array(
        //         'asc'  => 'u.full_name asc',
        //         'desc' => 'u.full_name desc',
        //     ),

        // );
        // $sort->defaultOrder = array('creation_date' => CSort::SORT_DESC);

        $dataProvider = new CActiveDataProvider('Permission', [
            // 'sort' => $sort,
            'criteria' => $userPermissionCriteria,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ]);
        return $dataProvider;
    }

    /**
     * Returns a CDbCriteria object which selects columns from table
     * permissions and users taking care of samegrouppolicy and not logged in
     * user for this survey.
     *
     * @return \CDbCriteria
     */
    public function getUserPermissionCriteria()
    {
        $userList = getUserList('onlyuidarray'); // Limit the user list for the samegrouppolicy
        /** @var \LSYii_Application */
        $app = \Yii::app();
        $currentUserId = $app->user->getId(); //current logged in user
        $criteria = new \CDbCriteria();
        $criteria->select = 't.entity_id, t.uid, u.users_name AS username, u.full_name';
        $criteria->join = 'INNER JOIN {{users}} AS u ON t.uid = u.uid';
        $criteria->condition = 't.entity_id =:entity_id';
        $criteria->addCondition('t.entity =:entity');
        $criteria->addNotInCondition('u.uid', [$currentUserId]);
        $criteria->addInCondition('t.uid', $userList);
        $criteria->params = array_merge($criteria->params, [
            ':entity_id' => $this->survey->sid,
            ':entity'    => 'survey'
        ]);
        $criteria->group = 't.entity_id, t.uid, u.users_name, u.full_name';
        $criteria->order = 'u.users_name';

        return $criteria;
    }

    /**
     * Get the permissions (crud + import,export) for a survey permission like 'assessements'
     *
     * @param int $userid the userid
     * @param string $permission the survey permission (e.g. 'assessments', 'responses')
     * @return \Permission|null
     */
    public function getUsersSurveyPermissionEntity($userid, $permission)
    {
        $criteria = new \CDbCriteria();
        $criteria->select = 'create_p, read_p, update_p, delete_p, import_p, export_p';
        $criteria->condition = 'entity_id =:entity_id';
        $criteria->addCondition('entity =:entity');
        $criteria->addCondition('permission =:permission');
        $criteria->addCondition('uid=:userid');
        $criteria->params = [
                ':entity_id' => $this->survey->sid,
                ':entity'    => 'survey',
                ':permission' => $permission,
                ':userid' => $userid
        ];
        return \Permission::model()->findByAttributes([
            'entity_id' => $this->survey->sid,
            'entity' => 'survey',
            'permission' => $permission,
            'uid' => $userid
        ]);
    }

    /**
     * Adds a user to the survey permissions. This includes that the user gets the
     * permission 'read' for this survey.
     *
     * @param int $userid the userid
     * @return boolean true if user could be added, false otherwise
     */
    public function addUserToSurveyPermission($userid)
    {
        $isResult = false;
        $user = \User::model()->findByPk($userid);
        if (isset($user) && in_array($userid, getUserList('onlyuidarray'))) {
            $isResult = \Permission::model()->insertSomeRecords(
                array(
                'entity_id' => $this->survey->sid,
                'entity' => 'survey',
                'uid' => $userid,
                'permission' => 'survey',
                'read_p' => 1)
            );
        }

        return $isResult;
    }

    /**
     * Adds users from a group to survey permissions.
     * This includes that the users get the
     * permission 'read' for this survey.
     *
     * @param int $userGroupId the user group id
     * @return int amount of users from the given group added
     */
    public function addUserGroupToSurveyPermissions($userGroupId)
    {
        $amountAdded = 0;
        $userGroup = \UserGroup::model()->findByPk($userGroupId);
        if (
            isset($userGroup) && in_array(
                $userGroupId,
                array_column($this->getSurveyUserGroupList(), 'ugid')
            )
        ) {
            $users = \User::model()->getCommonUID($this->survey->sid, $userGroupId); //Checked
            $users = $users->readAll();
            if (count($users) > 0) {
                foreach ($users as $user) {
                    $isResult = \Permission::model()->insertSomeRecords(
                        [
                            'entity_id' => $this->survey->sid,
                            'entity' => 'survey',
                            'uid' => $user['uid'],
                            'permission' => 'survey',
                            'read_p' => 1
                        ]
                    );
                    if ($isResult) {
                        $amountAdded++;
                    }
                }
            }
        }
        return $amountAdded;
    }

    /**
     * Returns a list of users which still not have survey permissions and
     * could be added to survey permissions,
     * including the check for usercontrolSameGroupPolicy (see config file for more information).
     *
     * @return array
     */
    public function getSurveyUserList()
    {
        $aUserIds = $this->getUserIdsWithSurveyPermissions();
        $criteria = new \CDbCriteria();
        $criteria->select = 't.uid, t.users_name, t.full_name';
        $criteria->addNotInCondition('t.uid', $aUserIds);
        $users = \User::model()->findAll($criteria);

        $authorizedUsersList = [];
        $userList = [];

        if ($this->userControlSameGroupPolicy) {
            $authorizedUsersList = getUserList('onlyuidarray');
        }
        $index = 0;
        foreach ($users as $user) {
            if (
                !$this->userControlSameGroupPolicy || in_array($user->uid, $authorizedUsersList)
            ) {
                $userList[$index]['userid'] = $user->uid;
                $userList[$index]['fullname'] = $user->full_name;
                $userList[$index]['usersname'] = $user->users_name;
                $index++;
            }
        }
        return $userList;
    }


    /**
     * Return a list (array) of user groups which could still be added to survey permissions.
     * A user group could be added to survey permissions if there is at least one user in the group
     * which has not already been added to survey permissions of this survey.
     *
     * @return array containing ['ugid'] and ['name']
     */
    public function getSurveyUserGroupList()
    {
        //find all groups that have not all their users already in 'survey permissions'
        $criteria = new \CDbCriteria();
        $criteria->select = "t.ugid, t.name, MAX(d.ugid)";
        $criteria->join = "LEFT JOIN (
        SELECT b.ugid
        FROM {{user_in_groups}} AS b
            LEFT JOIN (
                SELECT * FROM {{permissions}}
                WHERE entity_id =:surveyid and entity='survey'
            ) AS c ON b.uid = c.uid WHERE c.uid IS NULL
        ) AS d ON t.ugid = d.ugid";
        $criteria->params = [
            'surveyid' => $this->survey->sid,
        ];
        $criteria->group = 't.ugid, t.name';
        $criteria->having = 'MAX(d.ugid) IS NOT NULL'; //make sure that there is at least one possible group
        $criteria->order = 't.name';

        $userGroups = \UserGroup::model()->findAll($criteria);

        $authorizedGroupsList = getUserGroupList();
        $simpleugidarray = [];
        $index = 0;
        foreach ($userGroups as $userGroup) {
            if (in_array($userGroup->ugid, $authorizedGroupsList)) {
                $simpleugidarray[$index]['ugid'] = $userGroup->ugid;
                $simpleugidarray[$index]['name'] = $userGroup->name;
                $index++;
            }
        }
        return $simpleugidarray;
    }

    /**
     * Saves (inserts ) the survey permissions for a specific user.
     *
     * @param int $userId
     * @param array $permissions
     * @return bool true if all permissions could be saved, false otherwise
     */
    public function saveUserPermissions($userId, $permissions)
    {
        $isSaved = true;

        //delete current survey permissions and reset the new ones
        // ...easier as to compare all of them
        $this->deleteUserPermissions($userId);

        //but user has always 'read' permission for this specific survey ...
        $permissions['survey']['read'] = 1;

        foreach ($permissions as $permission => $key) {
            $isSaved = $isSaved && \Permission::model()->insertSomeRecords(
                [
                    'entity_id' => $this->survey->sid,
                    'entity' => 'survey',
                    'uid' => $userId,
                    'permission' => $permission,
                    'create_p' => $key['create'] ?? 0,
                    'read_p' => $key['read'] ?? 0,
                    'update_p' => $key['update'] ?? 0,
                    'delete_p' => $key['delete'] ?? 0,
                    'import_p' => $key['import'] ?? 0,
                    'export_p' => $key['export'] ?? 0,
                ]
            );
        }

        return $isSaved;
    }

    /**
     * Saves (inserts) permissions for a user group.
     *
     * @param int $userGroupId
     * @param array $permissions
     * @return bool
     * @throws \Exception
     */
    public function saveUserGroupPermissions($userGroupId, $permissions)
    {
        $permissionUserID = \Permission::model()->getUserId();
        $surveysGroups    = \SurveysGroups::model()->findByPk($this->survey->sid);
        if ($surveysGroups !== null) {
            $surveysGroupsOwnerID = $surveysGroups->getOwnerId();
            /** @var \UserInGroup[] */
            $oUserInGroups = \UserInGroup::model()->findAll(
                'ugid = :ugid AND uid <> :currentUserId AND uid <> :surveygroupsOwnerId',
                array(
                    ':ugid' => $userGroupId,
                    ':currentUserId' => $permissionUserID, // Don't need to set to current user
                    ':surveygroupsOwnerId' => $surveysGroupsOwnerID, // Don't need to set to owner (?) , get from surveyspermission
                )
            );
        } else {
            /** @var \UserInGroup[] */
            $oUserInGroups = \UserInGroup::model()->findAll(
                'ugid = :ugid AND uid <> :currentUserId',
                array (
                    ':ugid' => $userGroupId,
                    ':currentUserId' => $permissionUserID
                )
            );
        }
        $success = true;
        foreach ($oUserInGroups as $userInGroup) {
            /* @var $userInGroup \UserInGroup */
            $success = $success && $this->saveUserPermissions($userInGroup->uid, $permissions);
        }
        return $success;
    }

    /** Deletes all permissions for a user for this survey.
     *
     * @param int $userId
     * @return int number of deleted permissions, 0 means nothing has been deleted
     */
    public function deleteUserPermissions($userId)
    {
        return \Permission::model()->deleteAllByAttributes([
            'entity_id' => $this->survey->sid,
            'entity' => 'survey',
            'uid' => $userId
        ]);
    }

    /**
     * Returns an array of user group names including 'usercontrolSameGroupPolicy' if set.
     *
     * @param int $userid
     * @param bool $usercontrolSameGroupPolicy
     * @return array names of user groups, or empty array
     */
    public function getUserGroupNames($userid, $usercontrolSameGroupPolicy)
    {
        $group_names = [];
        $authorizedGroupsList = getUserGroupList();
        /** @var \UserInGroup[] */
        $userInGroups = \UserInGroup::model()->with('users')->findAll('users.uid = :uid', array(':uid' => $userid));
        foreach ($userInGroups as $userGroup) {
            /* @var $userGroup \UserInGroup */
            if (!$usercontrolSameGroupPolicy || in_array($userGroup->ugid, $authorizedGroupsList)) {
                $group_names[] = $userGroup->group->name;
            }
        }

        return $group_names;
    }

    /**
     * Checks which permission entities (CRUD + import,export) a user has for the specific
     * permission (e.g. permissionName='assessment'). Returns an array with infos.
     *
     * @param int $userId    the user id
     * @param string $permissioName     permission name (e.g. 'assessments' or 'quotas')
     * @param array $basicPermissionDetails array with basic information about a permission
     *                         (e.g. permission name, single permissions(CRUD) etc.)
     * @return array  structure is ['hasPermissions'] --> if user has at least one permission entity
     *                             ['allPermissions'] --> does the user has ALL possible permission entities
     *                             ['permissionCrudArray'] --> array with permission entities the user has
     */
    public function getTooltipAllPermissions($userId, $permissioName, $basicPermissionDetails)
    {
        $iCount = 0;
        $iPermissionCount = 0;
        $permissionCrudArray = [];
        foreach ($basicPermissionDetails as $sPDetailKey => $sPDetailValue) {
            $userHasPermission = \Permission::model()->hasSurveyPermission($this->survey->sid, $permissioName, $sPDetailKey, $userId);
            if ($sPDetailValue && $userHasPermission) {
                $iCount++;
                $permissionCrudArray[] = $sPDetailKey;
            }
            if (in_array($sPDetailKey, \PermissionInterface::SINGLE_PERMISSIONS) && $sPDetailValue) {
                $iPermissionCount++;
            }
        }
        return [
            'hasPermissions' => $iCount > 0,
            'allPermissionsSet' => $iCount == $iPermissionCount,
            'permissionCrudArray' => $permissionCrudArray
        ];
    }

    /**
     * Get the userids which already have survey permissions.
     *
     * @return array
     */
    public function getUserIdsWithSurveyPermissions(): array
    {
        $allUsersWithPermissions = new \CDbCriteria();
        $allUsersWithPermissions->select = 't.uid';
        $allUsersWithPermissions->condition = 't.entity_id=:entityId';
        $allUsersWithPermissions->addCondition('t.entity=:entity');
        $allUsersWithPermissions->params = [
            'entityId' => $this->survey->sid,
            'entity' => 'survey'
        ];
        $allUsersWithPermissions->group = 't.uid';
        $resultUserIds = \Permission::model()->findAll($allUsersWithPermissions);
        $aUserIds = [];
        foreach ($resultUserIds as $userid) {
            $aUserIds[] = $userid->uid;
        }
        return $aUserIds;
    }
}
