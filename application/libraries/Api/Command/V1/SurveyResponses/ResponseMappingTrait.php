<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

/**
 * Trait for shared response mapping functionality
 * Used by both SurveyResponses API command and ExportSurveyResultsService
 */
trait ResponseMappingTrait
{
    /**
     * Get the question field map.
     *
     * @return array
     */
    protected function getQuestionFieldMap(): array
    {
        $fieldMap = $this->transformerOutputSurveyResponses->fieldMap;

        return array_filter(
            array_map(
                function ($item) {
                    if (!empty($item['qid'])) {
                        return [
                            'fieldname' => $item['fieldname'] ?? null,
                            'gid' => $item['gid'],
                            'qid' => $item['qid'],
                            'aid' => $item['aid'] ?? null,
                            'sqid' => $item['sqid'] ?? null,
                            'scaleid' => $item['scale_id'] ?? null,
                            'title' => $item['title'] ?? null,
                            'question' => $item['question'] ?? null,
                            'subquestion' => $item['subquestion'] ?? null,
                            'subquestion1' => $item['subquestion1'] ?? null,
                            'subquestion2' => $item['subquestion2'] ?? null,
                            'scale' => $item['scale'] ?? null,
                            'type' => $item['type'] ?? null,
                        ];
                    }
                    return null;
                },
                $fieldMap
            )
        );
    }

    /**
     * Map survey responses to survey questions.
     *
     * @param array $responses
     * @param array $surveyQuestions
     * @return array
     */
    protected function mapResponsesToQuestions(array $responses, array $surveyQuestions): array
    {
        foreach ($responses as &$response) {
            foreach ($response['answers'] as &$answer) {
                $qid = $answer['key'];
                if (isset($surveyQuestions[$qid])) {
                    $answer = array_merge(
                        $answer,
                        $surveyQuestions[$qid]
                    );
                    $answer['actual_aid'] = $this->getActualAid(
                        $answer['qid'],
                        $answer['scale_id'] ?? $answer['scaleid'] ?? 0,
                        $answer['value'],
                    );
                }
            }
        }
        return $responses;
    }

    /**
     * Get the actual answer ID using the shared answer cache.
     *
     * @param int $questionID
     * @param int $scaleId
     * @param string $value
     * @return int|null
     */
    protected function getActualAid($questionID, $scaleId, $value)
    {
        return $this->answerCache->getAid($questionID, $scaleId, $value);
    }
}
