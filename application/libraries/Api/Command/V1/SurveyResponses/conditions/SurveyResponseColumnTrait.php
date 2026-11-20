<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use CDbColumnSchema;

/**
 * Survey context for condition handlers that need to know how a response column is stored.
 */
trait SurveyResponseColumnTrait
{
    /** @var int|null Survey whose responses are filtered, set by the builder */
    private ?int $surveyId = null;

    /**
     * Set the survey whose responses are filtered.
     *
     * @param int|null $surveyId Survey id, null when unknown
     * @return void
     */
    public function setSurveyId(?int $surveyId): void
    {
        $this->surveyId = $surveyId;
    }

    /**
     * A column of the survey's responses table.
     *
     * @param string $field Column name, optionally prefixed with a table alias
     * @return CDbColumnSchema|null Null without a survey, a responses table or that column
     */
    protected function responseColumn(string $field): ?CDbColumnSchema
    {
        if ($this->surveyId === null) {
            return null;
        }

        $dot = strrpos($field, '.');
        if ($dot !== false) {
            $field = substr($field, $dot + 1);
        }

        $table = App()->db->schema->getTable('{{responses_' . $this->surveyId . '}}');

        return $table === null ? null : $table->getColumn($field);
    }
}
