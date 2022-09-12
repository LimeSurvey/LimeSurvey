<?php

namespace LimeSurvey\Models\Services;

/**
 * This class is responsible for the relationship between permissions, users and surveys.
 * It could be hanled as specific permissions system for surveys.
 *
 */
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

    /**
     * Adds users from a group to survey permissions. This includes that the users get the
     * permission 'read' for this survey.
     *
     * @param $userGroupId
     * @return false
     */
    public function addUserGroupToSurveyPermissions($userGroupId)
    {
        $isrresult = false;
        $userGroup = \UserGroup::model()->findByPk($userGroupId);
        if (isset($userGroup) && in_array($userGroupId, getSurveyUserGroupList('simpleugidarray', $this->survey->sid))) {

        }
        return $isrresult;
    }

    /**
     *
     *
     * todo: OLD Retrieve a HTML <OPTION> list of survey admin users
     *
     * todo: remove HTML and put it in the view itself
     * todo: rewrite the sql with yii-query builder CDBCriteria ...
     *
     * @return string
     */
    public function getSurveyUserList()
    {
        $sSurveyIDQuery = "SELECT a.uid, a.users_name, a.full_name FROM {{users}} AS a
    LEFT OUTER JOIN (SELECT uid AS id FROM {{permissions}} WHERE entity_id = {$surveyid} and entity='survey') AS b ON a.uid = b.id
    WHERE id IS NULL ";
        $sSurveyIDQuery .= 'ORDER BY a.users_name';
        $oSurveyIDResult = Yii::app()->db->createCommand($sSurveyIDQuery)->query(); //Checked
        $aSurveyIDResult = $oSurveyIDResult->readAll();

        $surveyselecter = "";
        $authorizedUsersList = [];

        if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == true) {
            $authorizedUsersList = getUserList('onlyuidarray');
        }

        $svexist = false;
        foreach ($aSurveyIDResult as $sv) {
            if (
                Yii::app()->getConfig('usercontrolSameGroupPolicy') == false ||
                in_array($sv['uid'], $authorizedUsersList)
            ) {
                $surveyselecter .= "<option";
                $surveyselecter .= " value='{$sv['uid']}'>" . \CHtml::encode($sv['users_name']) . " " . \CHtml::encode($sv['full_name']) . "</option>\n";
                $svexist = true;
            }
        }

        if ($svexist) {
            $surveyselecter = "<option value='-1' selected='selected'>" . gT("Please choose...") . "</option>\n" . $surveyselecter;
        } else {
            $surveyselecter = "<option value='-1'>" . gT("None") . "</option>\n" . $surveyselecter;
        }

        return $surveyselecter;
    }


    /**
     * Return HTML <option> list of user groups
     *
     * * todo: remove HTML and put it in the view itself
     * todo: rewrite the sql with yii-query builder CDBCriteria ...
     *
     * @param string $outputformat 'htmloptions' or 'simpleugidarray' (todo: check if this is correct)
     * @param int $surveyid
     * @return string|array
     */
    public function getSurveyUserGroupList($outputformat, $surveyid)
    {

        $surveyid = sanitize_int($surveyid);

        $surveyidquery = "SELECT a.ugid, a.name, MAX(d.ugid) AS da
        FROM {{user_groups}} AS a
        LEFT JOIN (
        SELECT b.ugid
        FROM {{user_in_groups}} AS b
        LEFT JOIN (SELECT * FROM {{permissions}}
        WHERE entity_id = {$surveyid} and entity='survey') AS c ON b.uid = c.uid WHERE c.uid IS NULL
        ) AS d ON a.ugid = d.ugid GROUP BY a.ugid, a.name HAVING MAX(d.ugid) IS NOT NULL ORDER BY a.name";
        $surveyidresult = Yii::app()->db->createCommand($surveyidquery)->query(); //Checked
        $aResult = $surveyidresult->readAll();

        $authorizedGroupsList = getUserGroupList();
        $svexist = false;
        $surveyselecter = "";
        $simpleugidarray = [];
        foreach ($aResult as $sv) {
            if (
                in_array($sv['ugid'], $authorizedGroupsList)
            ) {
                $surveyselecter .= "<option";
                $surveyselecter .= " value='{$sv['ugid']}'>{$sv['name']}</option>\n";
                $simpleugidarray[] = $sv['ugid'];
                $svexist = true;
            }
        }

        if ($svexist) {
            $surveyselecter = "<option value='-1' selected='selected'>" . gT("Please choose...") . "</option>\n" . $surveyselecter;
        } else {
            $surveyselecter = "<option value='-1'>" . gT("None") . "</option>\n" . $surveyselecter;
        }

        if ($outputformat == 'simpleugidarray') {
            return $simpleugidarray;
        } else {
            return $surveyselecter;
        }
    }
}
