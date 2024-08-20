<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

use LimeSurvey\Models\Services\QuestionAttributeHelper;
use Question;
use QuestionAttribute;
use LimeSurvey\Models\Services\Exception\PersistErrorException;
use Survey;

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
    private QuestionAttributeHelper $questionAttributeHelper;
    private Survey $modelSurvey;

    public function __construct(
        QuestionAttribute $modelQuestionAttribute,
        QuestionAttributeHelper $questionAttributeHelper,
        Survey $modelSurvey
    ) {
        $this->modelQuestionAttribute = $modelQuestionAttribute;
        $this->questionAttributeHelper = $questionAttributeHelper;
        $this->modelSurvey = $modelSurvey;
    }

    /**
     * Based on QuestionAdministrationController::unparseAndSetAdvancedOptions()
     * Saves the advanced question attributes as they are in the $dataSet array
     *
     * @param Question $question
     * @param array {
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
     * Saves the base attributes of questions as they come in
     *
     * @param Question $question
     * @param array {
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
                !isset($attributeValue) ||
                in_array($attributeKey, ['qid', 'debug', 'tempId'])
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

    /**
     * Adds missing question attributes with default values to the passed question
     * @param Question $question
     * @param int $surveyId
     * @return Question
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function saveMissingAttributes(Question $question, int $surveyId)
    {
        $existingAttrSimplified = [];
        $existingAttributes = $this->modelQuestionAttribute->resetScope(
        )->findAll(
            'qid = :qid',
            [':qid' => $question->qid]
        );

        foreach ($existingAttributes as $attr) {
            /* @var QuestionAttribute $attr ; */
                $existingAttrSimplified[$attr->attribute][$attr->language] = $attr->value;
        }

        $defaultSet = $this->questionAttributeHelper->getQuestionAttributesWithValues(
            $question,
            null,
            null,
            true
        );
        // get all languages of the survey:
        $surveyModel = $this->modelSurvey->findByPk($surveyId);
        $allSurveyLanguages = $surveyModel->getAllLanguages();

        //only add those with their default values who are not already there
        foreach ($defaultSet as $attrName => $attrData) {
            $default = $attrData['default'] !== null ? $attrData['default'] : '';
            if (
                $attrData['i18n'] !== '1' &&
                !array_key_exists(
                    $attrName,
                    $existingAttrSimplified
                )
            ) {
                $this->modelQuestionAttribute->setQuestionAttributeWithLanguage(
                    $question->qid,
                    $attrName,
                    $default,
                    ''
                );
            } elseif ($attrData['i18n'] === '1') {
                // for language based attributes, add the default value for each language if not existing
                foreach ($allSurveyLanguages as $lngKey) {
                    if (
                        !array_key_exists($attrName, $existingAttrSimplified) ||
                        !array_key_exists(
                            $lngKey,
                            $existingAttrSimplified[$attrName]
                        )
                    ) {
                        $this->modelQuestionAttribute->setQuestionAttributeWithLanguage(
                            $question->qid,
                            $attrName,
                            $default,
                            $lngKey
                        );
                    }
                }
            }
        }
        $question->refresh();
        return $question;
    }
}
