<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

use Question;
use QuestionAttribute;
use LimeSurvey\Models\Services\Exception\PersistErrorException;

/**
 * Question Aggregate Attributes Service
 *
 * Service class for editing question attributes data.
 *
 * Based on QuestionAdministrationController::unparseAndSetAdvancedOptions()
 */
class AttributesService
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
     *      ?min_answers: int,
     *      ?max_answers: int,
     *      ?array_filter_style: int,
     *      ?array_filter: string,
     *      ?array_filter_exclude: string,
     *      ?exclude_all_others: int,
     *      ?random_group: string,
     *      ?em_validation_q: string,
     *      ?em_validation_q_tip: array{
     *          ?en: string,
     *          de: string,
     *          ...<array-key, mixed>
     *      },
     *      ...<array-key, mixed>
     * } $dataSet
     * @return void
     * @throws PersistErrorException
     */
    public function save($question, $dataSet)
    {
        $questionBaseAttributes = $question->attributes;

        foreach ($dataSet as $attributeKey => $attributeValue) {
            if (
                $attributeKey === 'qid' ||
                $attributeKey === 'debug' ||
                !isset($attributeValue)
            ) {
                continue;
            }

            if (is_array($attributeValue)) {
                foreach ($attributeValue as $lngKey => $content) {
                    if ($lngKey === 'expression') {
                        continue;
                    }
                    if (
                        !$this->modelQuestionAttribute->setQuestionAttributeWithLanguage(
                            $question->qid,
                            $attributeKey,
                            $content,
                            $lngKey
                        )
                    ) {
                        throw new PersistErrorException(
                            gT("Could not store advanced options")
                        );
                    }
                }
            } elseif (
                array_key_exists(
                    $attributeKey,
                    $questionBaseAttributes
                )
            ) {
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

        $question->refresh();
    }
}
