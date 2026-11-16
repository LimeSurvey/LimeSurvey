<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Traits;

use LimeSurvey\Api\Command\V1\SurveyPatch\Response\TempIdMapItem;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSubQuestion;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionL10ns;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use Question;

trait OpHandlerQuestionTrait
{
    use OpHandlerExceptionTrait;

    /**
     * Maps the tempIds of new subquestions or answers to the real ids.
     * @param Question $question
     * @param array $data
     * @param bool $answers
     * @return array
     */
    private function getSubQuestionNewIdMapping(
        Question $question,
        array $data,
        bool $answers = false
    ): array {
        $tempIds = [];
        $mapping = [];
        $title = $answers ? 'code' : 'title';
        $object = $answers ? 'answers' : 'subquestions';
        $idField = $answers ? 'aid' : 'qid';
        foreach ($data as $subQueDataArray) {
            foreach ($subQueDataArray as $scaleId => $subQueData) {
                if (
                    isset($subQueData['tempId'])
                    && isset($subQueData['code'])
                ) {
                    $tempIds[(int)$scaleId . ':' . $subQueData['code']] =
                        $subQueData['tempId'];
                }
            }
        }
        if (count($tempIds) > 0) {
            $question->refresh();
            foreach ($question->$object as $subquestion) {
                $key = (int)$subquestion->scale_id . ':' . $subquestion->$title;
                if (array_key_exists($key, $tempIds)) {
                    $mapping[$object . 'Map'][] = [
                        new TempIdMapItem(
                            $tempIds[$key],
                            $subquestion->$idField,
                            $idField
                        )
                    ];
                }
            }
        }
        return $mapping;
    }
}
