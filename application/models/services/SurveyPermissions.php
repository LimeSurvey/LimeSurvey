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
     * @return array
     */
    public function getUsersSurveyPermissions()
    {
        $usersSurveyPermissions = [];

        // get all users that have permissions for this survey (except owner and admin)

        return $usersSurveyPermissions;
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
     * @param $userGroupId
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
}
