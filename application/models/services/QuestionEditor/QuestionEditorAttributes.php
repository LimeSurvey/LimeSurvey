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
     * @param Question $oQuestion
     * @param array $dataSet these are the advancedSettings in an array like
     *                       [display]
     *                         [hidden]
     *                         ...
     *                       [logic]
     *                       ...
     * @return boolean
     * @throws PersistErrorException
     */
    public function updateAdvanced($oQuestion, $dataSet)
    {
        $aQuestionBaseAttributes = $oQuestion->attributes;

        foreach ($dataSet as $sAttributeCategory => $aAttributeCategorySettings) {
            if ($sAttributeCategory === 'debug') {
                continue;
            }
            foreach ($aAttributeCategorySettings as $sAttributeKey => $attributeValue) {
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
                            !$this->modelQuestionAttribute->setQuestionAttributeWithLanguage(
                                $oQuestion->qid,
                                $sAttributeKey,
                                $content,
                                $lngKey
                            )
                        ) {
                            throw new PersistErrorException(
                            gT('Could not store advanced options')
                            );
                        }
                    }
                } elseif (array_key_exists($sAttributeKey, $aQuestionBaseAttributes)) {
                    $oQuestion->$sAttributeKey = $newValue;
                } elseif (
                    !$this->modelQuestionAttribute->setQuestionAttribute(
                        $oQuestion->qid,
                        $sAttributeKey,
                        $newValue
                    )
                ) {
                    throw new PersistErrorException(
                        gT('Could not store advanced options')
                    );
                }
            }
        }

        if (!$oQuestion->save()) {
            throw new PersistErrorException(
                gT('Could not store advanced options')
            );
        }

        return true;
    }

    /**
     * @todo document me
     *
     * @param Question $oQuestion
     * @param array $dataSet
     * @return boolean
     * @throws PersistErrorException
     */
    public function updateGeneral($oQuestion, $dataSet)
    {
        $aQuestionBaseAttributes = $oQuestion->attributes;

        foreach ($dataSet as $sAttributeKey => $attributeValue) {
            if ($sAttributeKey === 'debug' || !isset($attributeValue)) {
                continue;
            }
            if (array_key_exists($sAttributeKey, $aQuestionBaseAttributes)) {
                $oQuestion->$sAttributeKey = $attributeValue;
            } elseif (
                !$this->modelQuestionAttribute->setQuestionAttribute(
                    $oQuestion->qid,
                    $sAttributeKey,
                    $attributeValue
                )
            ) {
                throw new PersistErrorException(
                    gT('Could not save question attributes')
                );
            }
        }

        if (!$oQuestion->save()) {
            throw new PersistErrorException( gT('Could not save question'));
        }

        return true;
    }
}
