<?php

namespace LimeSurvey\Models\Services;

class SurveyPermissions
{
    /* @var $survey \Survey */
    private $survey;

    /**
     * PasswordManagement constructor.
     * @param $user \User
     */
    public function __construct(\Survey $survey)
    {
        $this->survey = $survey;
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
        $isrresult = false;
        $user = \User::model()->findByPk($userid);
        if (isset($user) && in_array($userid, getUserList('onlyuidarray'))) {
            $isrresult = \Permission::model()->insertSomeRecords(
                array(
                'entity_id' => $this->survey->sid,
                'entity' => 'survey',
                'uid' => $userid,
                'permission' => 'survey',
                'read_p' => 1)
            );
        }

        return $isrresult;
    }


    public function addUserGroupToSurveyPermissions($userGroupId)
    {
        $isrresult = false;
        $userGroup = \UserGroup::model()->findByPk($userGroupId);
        if (isset($userGroup) && in_array($userGroupId, getSurveyUserGroupList('simpleugidarray', $this->survey->sid))) {

        }
        return $isrresult;
    }
}
