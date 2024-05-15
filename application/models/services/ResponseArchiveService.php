<?php

namespace LimeSurvey\Models\Services;

use CDbSchema;
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

    /**
     * Searches if questions exist in the latest archived response table
     * @param $surveyId
     * @param $questionIds
     * @return bool if questions with this group exist
     */
    public function searchQuestions($surveyId, $questionIds): bool
    {
        foreach ($questionIds as $question) {
            $this->searchQuestion($surveyId, $question->id);
        }

        return false;
    }

    /**
     * Searches if a question exists in the latest archived response table
     * @param int $surveyId
     * @param int $questionCode The question code (Q) of the SGQA code (survey, group, question, answer)
     * @return bool
     * @throws \CDbException
     */
    public function searchQuestion(int $surveyId, int $questionCode): bool
    {
        $archivedTableName = App()->getDb()->tablePrefix . 'old_survey_' . $surveyId;
        $archivedResponseTable = App()->getDb()->getSchema()->getTable($archivedTableName);
        $archivedReponseTableSchema = PluginDynamic::model($archivedResponseTable)->getTableSchema();

        $pattern = '/(\d+)X(\d+)X(\d+.*)/';
        foreach ($archivedReponseTableSchema->getColumnNames() as $name) {
            // The following columns are not questions
            if (in_array($name, array('id', 'submitdate', 'lastpage', 'startlanguage', 'seed', 'startdate', 'datestamp')
            )) {
                continue;
            }
            $matches = array();
            if (preg_match($pattern, (string)$name, $matches)) {
                // Column name is SIDXGIDXQID
                $archiveQid = $matches[3];
                if ($archiveQuestionCode=== $questionCode) {
                    return true;
                }
            }
        }
        return false;
    }

    public function searchQuestionWithTypeCoercion(int $surveyId, int $questionId): bool
    {
        return false;
    }
}
