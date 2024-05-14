<?php

namespace LimeSurvey\Models\Services;

use PluginDynamic;
use Survey;
use Permission;
use Question;
use QuestionGroup;

/**
 * Responsible for interacting with the archived survey responses.
 */
class ResponseArchiveService
{
    private Permission $modelPermission;
    private Survey $modelSurvey;
    private Question $modelQuestion;
    private QuestionGroup $modelQuestionGroup;

    public function __construct(
        Permission $modelPermission,
        Survey $modelSurvey,
        Question $modelQuestion,
        QuestionGroup $modelQuestionGroup,
    ) {
        $this->modelPermission = $modelPermission;
        $this->modelSurvey = $modelSurvey;
        $this->modelQuestion = $modelQuestion;
        $this->modelQuestionGroup = $modelQuestionGroup;
    }

    public function searchGroup()
    {

    }

    /**
     * @param int $surveyId
     * @param int $questionId
     * @return bool
     */
    public function searchQuestion(int $surveyId, int $questionId): bool
    {
        $sourceTable = PluginDynamic::model($_POST['table']);
        if ($sourceTable === null) {
            return false;
        }
        $sourceSchema = $sourceTable->getTableSchema();

        $pattern = '/(\d+)X(\d+)X(\d+.*)/';
        foreach ($sourceSchema->getColumnNames() as $name) {
            $matches = array();
            if (preg_match($pattern, (string) $name, $matches)) {
                // Column name is SIDXGIDXQID
                $archiveQid = $matches[3];
                if ($archiveQid === $questionId) {
                    return true;
                }
            }
        }
        return false;
    }
}
