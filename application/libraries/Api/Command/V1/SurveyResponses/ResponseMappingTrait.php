<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

use Answer;
use Question;
use Survey;
use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;

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
                            'gid' => $item['gid'],
                            'qid' => $item['qid'],
                            'aid' => $item['aid'] ?? null,
                            'sqid' => $item['sqid'] ?? null,
                            'scaleid' => $item['scale_id'] ?? null,
                            'title' => $item['title'] ?? null,
                            'question' => $item['question'] ?? null,
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
     * Get all answers for the survey questions and cache them.
     *
     * @return array Answers indexed by qid, scale_id, and code
     */
    protected function getAllSurveyAnswers()
    {
        static $answersCache = [];
        $surveyId = $this->survey->sid;

        if (!isset($answersCache[$surveyId])) {
            /** @var Question[] $questions */
            /** @psalm-suppress UndefinedMagicPropertyFetch */
            $questions = $this->survey->questions;
            $questionIds = array_map(function (Question $q): int {
                return $q->qid;
            }, $questions);

            if (empty($questionIds)) {
                $answersCache[$surveyId] = [];
                return $answersCache[$surveyId];
            }

            $answers = $this->answerModel->findAll(
                'qid IN (' . implode(',', $questionIds) . ')'
            );

            $answersCache[$surveyId] = [];
            foreach ($answers as $answer) {
                $answersCache[$surveyId][$answer->qid][$answer->scale_id][$answer->code] = $answer->aid;
            }
        }

        return $answersCache[$surveyId];
    }

    /**
     * Get the actual answer ID efficiently using cached answers.
     *
     * @param int $questionID
     * @param int $scaleId
     * @param string $value
     * @return int|null
     */
    protected function getActualAid($questionID, $scaleId, $value)
    {
        $allAnswers = $this->getAllSurveyAnswers();
        return $allAnswers[$questionID][$scaleId][$value] ?? null;
    }
}
