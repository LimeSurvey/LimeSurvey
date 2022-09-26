<?php

namespace LimeSurvey\Models\Services;

/**
 * This class is responsible for the relationship between permissions, users and surveys.
 * It could be handled as a specific permissions system for surveys.
 *
 */
class SurveyPermissions
{
    /* @var $survey \Survey */
    private $survey;

    /* @var $usercontrolSameGroupPolicy boolean */
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
     * Returns a CDbCriteria object which selects columns from table
     * permissions and users taking care of samegrouppolicy and not logged in
     * user for this survey.
     *
     * @return \CDbCriteria
     */
    public function getUserPermissionCriteria()
    {
        $userList = getUserList('onlyuidarray'); // Limit the user list for the samegrouppolicy
        $currentUserId = \Yii::app()->user->getId(); //current logged in user
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
     * @param $userid int the userid
     * @param $permission string the survey permission (e.g. 'assessments', 'responses')
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
     * @param $userid int the userid
     *
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
     * @param $userGroupId int the user group id
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
     * todo: rewrite the sql with yii-query builder CDBCriteria ...
     *
     * @return array
     */
    public function getSurveyUserList()
    {
        $sSurveyIDQuery = "SELECT a.uid, a.users_name, a.full_name FROM {{users}} AS a
    LEFT OUTER JOIN (SELECT uid AS id FROM {{permissions}} WHERE entity_id = {$this->survey->sid} and entity='survey') AS b ON a.uid = b.id
    WHERE id IS NULL ";
        $sSurveyIDQuery .= 'ORDER BY a.users_name';
        $oSurveyIDResult = \Yii::app()->db->createCommand($sSurveyIDQuery)->query(); //Checked
        $aSurveyIDResult = $oSurveyIDResult->readAll();
        $authorizedUsersList = [];
        $userList = [];

        //take out superadmin (makes no sense to add superadmin, because he always has the permissions for all surveys)

        if ($this->userControlSameGroupPolicy) {
            $authorizedUsersList = getUserList('onlyuidarray');
        }
        $index = 0;
        foreach ($aSurveyIDResult as $sv) {
            if (
                !$this->userControlSameGroupPolicy || in_array($sv['uid'], $authorizedUsersList)
            ) {
                $userList[$index]['userid'] = $sv['uid'];
                $userList[$index]['fullname'] = $sv['full_name'];
                $userList[$index]['usersname'] = $sv['users_name'];
                $index++;
            }
        }
        return $userList;
    }


    /**
     * Return a list (array) of usergroups which could still be added to survey permissions.
     * A user group could be added to survey permissions if there is at least one user in the group
     * which has not already been added to survey permissions of this survey.
     *
     * todo: rewrite the sql with yii-query builder CDBCriteria ...
     *
     * @return array containing ['ugid'] and ['name']
     */
    public function getSurveyUserGroupList()
    {
        $surveyidquery = "SELECT a.ugid, a.name, MAX(d.ugid) AS da
        FROM {{user_groups}} AS a
        LEFT JOIN (
        SELECT b.ugid
        FROM {{user_in_groups}} AS b
        LEFT JOIN (SELECT * FROM {{permissions}}
        WHERE entity_id = {$this->survey->sid} and entity='survey') AS c ON b.uid = c.uid WHERE c.uid IS NULL
        ) AS d ON a.ugid = d.ugid GROUP BY a.ugid, a.name HAVING MAX(d.ugid) IS NOT NULL ORDER BY a.name";
        $surveyidresult = \Yii::app()->db->createCommand($surveyidquery)->query(); //Checked
        $aResult = $surveyidresult->readAll();

        $authorizedGroupsList = getUserGroupList();
        $simpleugidarray = [];
        $index = 0;
        foreach ($aResult as $sv) {
            if (in_array($sv['ugid'], $authorizedGroupsList)) {
                $simpleugidarray[$index]['ugid'] = $sv['ugid'];
                $simpleugidarray[$index]['name'] = $sv['name'];
                $index++;
            }
        }
        return $simpleugidarray;
    }

    /**
     * Saves (inserts ) the survey permissions for a specific user.
     *
     * @param $userId int
     * @param $permissions array
     * @return bool
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
     * @param $userGroupId int
     * @param $permissions array
     * @return bool
     * @throws \Exception
     */
    public function saveUserGroupPermissions($userGroupId, $permissions)
    {
        $permissionUserID = \Permission::model()->getUserId();
        $surveysGroups    = \SurveysGroups::model()->findByPk($this->survey->sid);
        if ($surveysGroups !== null) {
            $surveysGroupsOwnerID = $surveysGroups->getOwnerId();
            $oUserInGroups = \UserInGroup::model()->findAll(
                'ugid = :ugid AND uid <> :currentUserId AND uid <> :surveygroupsOwnerId',
                array(
                    ':ugid' => $userGroupId,
                    ':currentUserId' => $permissionUserID, // Don't need to set to current user
                    ':surveygroupsOwnerId' => $surveysGroupsOwnerID, // Don't need to set to owner (?) , get from surveyspermission
                )
            );
        } else {
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
     * @param $userId
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
     * @param $userid
     * @param $usercontrolSameGroupPolicy
     * @return array names of user groups
     */
    public function getUserGroupNames($userid, $usercontrolSameGroupPolicy)
    {
        $group_names = [];
        $authorizedGroupsList = getUserGroupList();
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
     * @param $userId    int the user id
     * @param $permissioName     string permission name (e.g. 'assessments' or 'quotas')
     * @param $basicPermissionDetails array array with basic information about a permission
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
}
