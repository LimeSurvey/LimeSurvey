<?php


namespace LimeSurvey\Datavalueobjects;

/**
 * Class CopyQuestionValues
 *
 * This class owns the values to copy a question
 *
 * @package LimeSurvey\Datavalueobjects
 */
class CopyQuestionValues
{
        /** @var string the question Code */
        private $questionCode;

        /** @var int question Group that the copied question should belong to */
        private $questionGroup;

        /** @var \Survey the survey the question belongs to */
        private $oSurvey;

        /** @var \Question the question that should be copied */
        private $questiontoCopy;

    /**
     * @return string
     */
    public function getQuestionCode(): string
    {
        return $this->questionCode;
    }

    /**
     * @param string $questionCode
     */
    public function setQuestionCode(string $questionCode): void
    {
        $this->questionCode = $questionCode;
    }

}