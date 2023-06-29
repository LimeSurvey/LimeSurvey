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
     * @param array $dataSet these are the advancedSettings in an array like
     *                       [display]
     *                         [hidden]
     *                         ...
     *                       [logic]
     *                       ...
     * @return boolean
     * @throws PersistErrorException
     */
    public function updateAdvanced($question, $dataSet)
    {
        $questionBaseAttributes = $question->attributes;

        foreach ($dataSet as $category => $categorySettings) {
            if ($category === 'debug') {
                continue;
            }
            foreach ($categorySettings as $attributeKey => $attributeValue) {
                $newValue = $attributeValue;

                // Set default value if empty.
                // TODO: Default value
                if (
                    $newValue === ''
                    && isset($attributeValue['aFormElementOptions']['default'])
                ) {
                    $newValue = $attributeValue['aFormElementOptions']['default'];
                }

                if (is_array($newValue)) {
                    foreach ($newValue as $lngKey => $content) {
                        if ($lngKey === 'expression') {
                            continue;
                        }
                        if (
                            !$this->modelQuestionAttribute
                                ->setQuestionAttributeWithLanguage(
                                    $question->qid,
                                    $attributeKey,
                                    $content,
                                    $lngKey
                                )
                        ) {
                            throw new PersistErrorException(
                            gT('Could not store advanced options')
                            );
                        }
                    }
                } elseif (array_key_exists(
                    $attributeKey,
                    $questionBaseAttributes)
                ) {
                    $question->$attributeKey = $newValue;
                } elseif (
                    !$this->modelQuestionAttribute->setQuestionAttribute(
                        $question->qid,
                        $attributeKey,
                        $newValue
                    )
                ) {
                    throw new PersistErrorException(
                        gT('Could not store advanced options')
                    );
                }
            }
        }

        if (!$question->save()) {
            throw new PersistErrorException(
                gT('Could not store advanced options')
            );
        }

        return true;
    }

    /**
     * @todo document me
     *
     * @param Question $question
     * @param array $dataSet
     * @return boolean
     * @throws PersistErrorException
     */
    public function updateGeneral($question, $dataSet)
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

        return true;
    }
}
