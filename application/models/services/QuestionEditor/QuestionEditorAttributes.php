<?php

namespace LimeSurvey\Models\Services\QuestionEditor;

use QuestionAttribute;

use LimeSurvey\Models\Services\Exception\PersistErrorException;

/**
 * Question Editor Attributes Service
 *
 * Service class for editing question attributes data.
 *
 * Based on QuestionAdministrationController::unparseAndSetAdvancedOptions()
 */
class QuestionEditorAttributes
{
    private QuestionAttribute $modelQuestionAttribute;

    public function __construct(
        QuestionAttribute $modelQuestionAttribute
    ) {
        $this->modelQuestionAttribute = $modelQuestionAttribute;
    }

    /**
     * @todo document me
     *
     * Based on QuestionAdministrationController::unparseAndSetAdvancedOptions()
     *
     * @param Question $question
     * @param array{
     *      ?logic: array{
     *          ?min_answers: int,
     *          ?max_answers: int,
     *          ?array_filter_style: int,
     *          ?array_filter: string,
     *          ?array_filter_exclude: string,
     *          ?exclude_all_others: int,
     *          ?random_group: string,
     *          ?em_validation_q: string,
     *          ?em_validation_q_tip: array{
     *              ?en: string,
     *              ?de: string,
     *              ...<array-key, mixed>
     *          },
     *          ...<array-key, mixed>
     *      },
     *      ?display: array{
     *          ...<array-key, mixed>
     *      },
     *      ?statistics: array{
     *          ...<array-key, mixed>
     *      },
     *      ...<array-key, mixed>
     * } $dataSet
     * @return void
     * @throws PersistErrorException
     */
    public function saveAdvanced($question, $dataSet)
    {
        $questionBaseAttributes = $question->attributes;

        foreach ($dataSet as $category => $categorySettings) {
            if ($category === 'debug') {
                continue;
            }
            $this->save($question, $categorySettings);
        }

        if (!$question->save()) {
            throw new PersistErrorException(
                gT('Could not store advanced options')
            );
        }
    }

    /**
     * @todo document me
     *
     * @param Question $question
     * @param array{
     *  ...<array-key, mixed>
     * } $dataSet
     * @return void
     * @throws PersistErrorException
     */
    public function save($question, $dataSet)
    {
        $questionBaseAttributes = $question->attributes;

        foreach ($dataSet as $attributeKey => $attributeValue) {
            if ($attributeKey === 'debug' || !isset($attributeValue)) {
                continue;
            }
            if (array_key_exists($attributeKey, $questionBaseAttributes)) {
                $question->$attributeKey = $attributeValue;
            } elseif (
                !$this->modelQuestionAttribute->setQuestionAttribute(
                    $question->qid,
                    $attributeKey,
                    $attributeValue
                )
            ) {
                throw new PersistErrorException(
                    gT('Could not save question attributes')
                );
            }
        }

        if (!$question->save()) {
            throw new PersistErrorException(
                gT('Could not save question')
            );
        }
    }
}
