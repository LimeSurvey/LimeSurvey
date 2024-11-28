<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LSYii_Application;
use Permission;
use Survey;
use Response;

class SurveyCondition
{
    private LSYii_Application $app;
    private Permission $permission;
    private Survey $survey;
    protected int $iSurveyID;
    protected bool $tokenTableExists;
    protected array $tokenFieldsAndNames;

    public function getSurveyTable($name, $id)
    {
        switch ($name) {
            case 'token':
                return "{{tokens_$id}}";
            default:
                return '';
        }
    }

    public function __construct(
        LSYii_Application $app,
        Permission $permission,
        Survey $survey
    ) {
        $this->app = $app;
        $this->permission = $permission;
        $this->survey = $survey;
    }

    public function initialize($params)
    {
        $this->iSurveyID = $params['iSurveyID'];
        $this->tokenTableExists = tableExists($this->getSurveyTable('token', $this->iSurveyID));
        $this->tokenFieldsAndNames = getTokenFieldsAndNames($this->iSurveyID);
        $this->app->loadHelper("database");
        return [
            $this->iSurveyID,
            $this->tokenTableExists,
            $this->tokenFieldsAndNames,
        ];
    }

    public function resetSurveyLogic()
    {
        LimeExpressionManager::RevertUpgradeConditionsToRelevance($this->iSurveyID);
        Condition::model()->deleteRecords("qid in (select qid from {{questions}} where sid={$this->iSurveyID})");
    }
}
