<?php

namespace LimeSurvey\Models\Services;

use Permission;
use Survey;
use Token;
use LimeExpressionManager;
use LSYii_Application;
use ArchivedTableSettings;

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException,
    PermissionDeniedException
};

class SurveyAccessModeService
{
    protected Permission $permission;

    protected Survey $survey;
    protected LSYii_Application $app;

    public static $ACCESS_TYPE_OPEN = 'O';
    public static $ACCESS_TYPE_CLOSED = 'C';
    public static $ACCESS_TYPE_DUAL = 'D';
    public static $ACCESS_TYPE_ANYONE_WITH_LINK = 'A';

    protected static $supportedAccessModes = null;

    public function __construct(
        Permission $permission,
        Survey $survey,
        LSYii_Application $app
    )
    {
        $this->permission = $permission;
        $this->survey = $survey;
        $this->app = $app;
        if (!self::$supportedAccessModes) {
            self::$supportedAccessModes = [
                self::$ACCESS_TYPE_OPEN,
                self::$ACCESS_TYPE_CLOSED,
                self::$ACCESS_TYPE_DUAL,
                self::$ACCESS_TYPE_ANYONE_WITH_LINK
            ];
        }
    }

    /**
     * Checks whether the issuer has the necessary permissions for the action
     * @param int $surveyID the id of the survey
     * @param string $oldMode the access mode we intend to change
     * @param string $newMode the access mode we intend to set
     * @return bool whether all the permissions necessary are present
     */
    protected function hasPermission(int $surveyID, string $oldMode, string $newMode)
    {
        $permissions = [
            'surveysettings' => 'update'
        ];
        if (($oldMode !== self::$ACCESS_TYPE_CLOSED) && ($newMode === self::$ACCESS_TYPE_CLOSED)) {
            $permissions['tokens'] = 'delete';
        } else if (($oldMode === self::$ACCESS_TYPE_CLOSED) && ($newMode !== self::$ACCESS_TYPE_CLOSED)) {
            $permissions['tokens'] = 'create';
        }
        foreach ($permissions as $name => $perm) {
            if (!$this->permission->hasSurveyPermission($surveyID, $name, $perm)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Creates a token table for the survey if it does not already exist
     * @param \Survey $survey
     * @return bool
     */
    protected function newTokenTable(Survey $survey)
    {
        $surveyInfo = getSurveyInfo($survey->sid);
        if ($survey->hasTokensTable) {
            return false; //Tokens table already exists, nothing to do here
        }
        $tokenencryptionoptions = $survey->getTokenEncryptionOptions();
        $tokenencryptionoptions['enabled'] = 'Y';
        $survey->tokenencryptionoptions = ls_json_encode($tokenencryptionoptions);
        Token::createTable($survey->sid);
        LimeExpressionManager::setDirtyFlag();
        return true;
    }

    /**
     * Drops token table if it exists
     * @param \Survey $survey the survey whose participant table is to be dropped
     * @param bool $archive whether we archive the tokens, or remove them
     * @return void
     */
    protected function dropTokenTable(Survey $survey, bool $archive = true)
    {
        $datestamp = time();
        $date = date('YmdHis', $datestamp);
        $DBDate = "date('Y-m-d H:i:s', $datestamp)";
        $oldTable = "tokens_" . $survey->sid;
        $newTable = "old_tokens_" . $survey->sid . "_" . $date;
        $userID = $this->app->user->getId();

        if ($archive) {
            $surveyInfo = getSurveyInfo($survey->sid);
            $this->app->db->createCommand()->renameTable("{{" . $oldTable . "}}", "{{" . $newTable . "}}");
            $archivedTokenSettings = new ArchivedTableSettings();
            $archivedTokenSettings->survey_id = $survey->sid;
            $archivedTokenSettings->user_id = $userID;
            $archivedTokenSettings->tbl_name = $newTable;
            $archivedTokenSettings->tbl_type = 'token';
            $archivedTokenSettings->created = $DBDate;
            $archivedTokenSettings->properties = $surveyInfo['tokenencryptionoptions'];
            $archivedTokenSettings->attributes = json_encode($surveyInfo['attributedescriptions']);
            $archivedTokenSettings->save();
        } else {
            $this->app->db->createCommand()->dropTable("{{" . $oldTable . "}}");
        }
    }

    /**
     * Changes the access mode of the survey
     * @param int $surveyID the id of the survey whose access mode is to be changed
     * @param string $accessMode the access mode we desire to have
     * @param bool $archive whether we intend to archive the tokens table or not
     * @throws \LimeSurvey\Models\Services\Exception\PersistErrorException
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @return bool whether the change was done
     */
    public function changeAccessMode(int $surveyID, string $accessMode, bool $archive = true)
    {
        $survey = Survey::model()->findByPk($surveyID);
        $oldAccessMode = $survey->access_mode;
        if ($oldAccessMode === $accessMode) {
            return false; //Nothing to change
        }
        if (!in_array($accessMode, self::$supportedAccessModes)) {
            throw new PersistErrorException(
                'The access mode given is not supported'
            );
        }
        if (!$this->hasPermission($surveyID, $oldAccessMode, $accessMode)) {
            throw new PermissionDeniedException(
                'Access denied'
            );
        }
        $survey->access_mode = $accessMode;
        if ($oldAccessMode === self::$ACCESS_TYPE_OPEN) {
            $this->newTokenTable($survey);
        } else if ($accessMode === self::$ACCESS_TYPE_OPEN) {
            $this->dropTokenTable($survey, $archive);
        }
        $survey->save();
        return true;
    }
}