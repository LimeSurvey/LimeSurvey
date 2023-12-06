<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Traits;

use LimeSurvey\Api\Command\V1\SurveyPatch\TempIdMapItem;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestion;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionL10ns;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use Question;

trait OpHandlerQuestionTrait
{
    use OpHandlerExceptionTrait;

    private function checkRequiredDataCollection(
        OpInterface $op,
        ?array $collection,
        string $name,
        ?array $additionalEntities = null
    ): void {
        if (is_array($collection)) {
            foreach ($collection as $data) {
                $this->checkRequiredData(
                    $op,
                    $data,
                    $name,
                    $additionalEntities
                );
            }
        }
    }

    /**
     * Converts the subquestions from the raw data to the expected format.
     * @param OpInterface $op
     * @param TransformerInputQuestion $transformerQuestion
     * @param TransformerInputQuestionL10ns $transformerL10n
     * @param array|null $data
     * @param array|null $additionalRequiredEntities
     * @return array
     * @throws OpHandlerException
     */
    private function prepareSubQuestions(
        OpInterface $op,
        TransformerInputQuestion $transformerQuestion,
        TransformerInputQuestionL10ns $transformerL10n,
        ?array $data,
        ?array $additionalRequiredEntities = null
    ): array {
        $preparedSubQuestions = [];
        if (is_array($data)) {
            foreach ($data as $index => $subQuestion) {
                $tfSubQuestion = $transformerQuestion->transform(
                    $subQuestion
                );
                if (
                    is_array($tfSubQuestion) && array_key_exists(
                        'title',
                        $tfSubQuestion
                    )
                ) {
                    $tfSubQuestion['code'] = $tfSubQuestion['title'];
                }
                $this->checkRequiredData(
                    $op,
                    $tfSubQuestion,
                    'subquestions',
                    $additionalRequiredEntities
                );
                if (
                    is_array($subQuestion) && array_key_exists(
                        'l10ns',
                        $subQuestion
                    ) && is_array($subQuestion['l10ns'])
                ) {
                    foreach ($subQuestion['l10ns'] as $lang => $subL10n) {
                        $tfSubL10n = $transformerL10n->transform(
                            $subL10n
                        );
                        $tfSubQuestion['subquestionl10n'][$lang] =
                            (
                                is_array($tfSubL10n)
                                && isset($tfSubL10n['question'])
                            ) ?
                                $tfSubL10n['question'] : null;
                    }
                }
                $qid = $this->getQidFromData($index, $tfSubQuestion);
                $scaleId = $this->getScaleIdFromData($tfSubQuestion);
                $preparedSubQuestions[$qid][$scaleId] = $tfSubQuestion;
            }
        }
        return $preparedSubQuestions;
    }

    /**
     * @param int $index
     * @param array $questionData
     * @return int
     */
    private function getQidFromData(int $index, array $questionData)
    {
        return array_key_exists(
            'qid',
            $questionData
        ) ? (int)$questionData['qid'] : $index;
    }

    /**
     * @param array $questionData
     * @return int
     */
    private function getScaleIdFromData(array $questionData)
    {
        return array_key_exists(
            'scale_id',
            $questionData
        ) ? (int)$questionData['scale_id'] : 0;
    }

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
            foreach ($subQueDataArray as $subQueData) {
                if (
                    isset($subQueData['tempId'])
                    && isset($subQueData[$title])
                ) {
                    $tempIds[$subQueData[$title]] = $subQueData['tempId'];
                }
            }
        }
        if (count($tempIds) > 0) {
            $question->refresh();
            foreach ($question->$object as $subquestion) {
                if (array_key_exists($subquestion->$title, $tempIds)) {
                    $mapping[$object . 'Map'][] = [
                        new TempIdMapItem(
                            $tempIds[$subquestion->$title],
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
