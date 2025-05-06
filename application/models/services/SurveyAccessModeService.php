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

    public static $ACTION_KEEP = 'K';
    public static $ACTION_ARCHIVE = 'A';
    public static $ACTION_DROP = 'D';

    protected static $supportedAccessModes = null;

    protected static $supportedActions = null;

    public function __construct(
        Permission $permission,
        Survey $survey,
        LSYii_Application $app
    ) {
        $this->permission = $permission;
        $this->survey = $survey;
        $this->app = $app;
        if (!self::$supportedAccessModes) {
            self::$supportedAccessModes = [
                self::$ACCESS_TYPE_OPEN,
                self::$ACCESS_TYPE_CLOSED,
                self::$ACCESS_TYPE_DUAL,
            ];
        }
        if (!self::$supportedActions) {
            self::$supportedActions = [
                self::$ACTION_KEEP,
                self::$ACTION_ARCHIVE,
                self::$ACTION_DROP
            ];
        }
    }

    /**
     * Checks whether the issuer has the necessary permissions for the action
     * @param int $surveyID the id of the survey
     * @param string $newMode the access mode we intend to set
     * @return bool whether all the permissions necessary are present
     */
    public function hasPermission(int $surveyID, string $newMode)
    {
        $survey = $this->survey->findByPk($surveyID);
        $oldMode = $survey->access_mode;
        $permissions = [
            'surveysettings' => 'update'
        ];
        if (($oldMode !== self::$ACCESS_TYPE_OPEN) && ($newMode === self::$ACCESS_TYPE_OPEN)) {
            $permissions['tokens'] = 'delete';
        } elseif (($oldMode === self::$ACCESS_TYPE_OPEN) && ($newMode !== self::$ACCESS_TYPE_OPEN)) {
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
        if (($survey->active !== 'Y') || ($survey->hasTokensTable)) {
            return false; //Tokens table already exists or the survey is not active, nothing to do here
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
     * @param string $action whether we archive the tokens, or remove them
     * @return void
     */
    protected function dropTokenTable(Survey $survey, string $action = 'K')
    {
        $datestamp = time();
        $date = date('YmdHis', $datestamp);
        $DBDate = "date('Y-m-d H:i:s', $datestamp)";
        $oldTable = "tokens_" . $survey->sid;
        $newTable = "old_tokens_" . $survey->sid . "_" . $date;
        $userID = $this->app->user->getId();

        if ($survey->active !== 'Y') {
            return;
        }

        if ($survey->hasTokensTable) {
            if (!in_array($action, self::$supportedActions)) {
                $action = self::$ACTION_KEEP;
            }
            $tokenSample = Token::model($survey->sid)->find('1=1');
            if ($tokenSample === null) {
                $action = self::$ACTION_DROP;
            }
            if ($action === self::$ACTION_ARCHIVE) {
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
            } elseif ($action === self::$ACTION_DROP) {
                $this->app->db->createCommand()->dropTable("{{" . $oldTable . "}}");
            } //If action is Keep, do nothing
        }
    }

    /**
     * Changes the access mode of the survey
     * @param int $surveyID the id of the survey whose access mode is to be changed
     * @param string $accessMode the access mode we desire to have
     * @param string $action whether we intend to archive the tokens table or not
     * @throws \LimeSurvey\Models\Services\Exception\PersistErrorException
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @return bool whether the change was done
     */
    public function changeAccessMode(int $surveyID, string $accessMode, string $action = 'K')
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
        if (!$this->hasPermission($surveyID, $accessMode)) {
            throw new PermissionDeniedException(
                'Access denied'
            );
        }
        $survey->access_mode = $accessMode;
        if ($oldAccessMode === self::$ACCESS_TYPE_OPEN) {
            $this->newTokenTable($survey);
        } elseif ($accessMode === self::$ACCESS_TYPE_OPEN) {
            $this->dropTokenTable($survey, $action);
        }
        $survey->save();
        return true;
    }
}
