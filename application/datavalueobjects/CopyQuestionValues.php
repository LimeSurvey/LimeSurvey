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

        /** @var int questionGroup id that the copied question should belong to */
        private $questionGroupId;

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

    /**
     * @return int
     */
    public function getQuestionGroupId(): int
    {
        return $this->questionGroup;
    }

    /**
     * @param int $questionGroup
     */
    public function setQuestionGroupId(int $questionGroup): void
    {
        $this->questionGroup = $questionGroup;
    }

    /**
     * @return \Survey
     */
    public function getOSurvey(): \Survey
    {
        return $this->oSurvey;
    }

    /**
     * @param \Survey $oSurvey
     */
    public function setOSurvey(\Survey $oSurvey): void
    {
        $this->oSurvey = $oSurvey;
    }

    /**
     * @return \Question
     */
    public function getQuestiontoCopy(): \Question
    {
        return $this->questiontoCopy;
    }

    /**
     * @param \Question $questiontoCopy
     */
    public function setQuestiontoCopy(\Question $questiontoCopy): void
    {
        $this->questiontoCopy = $questiontoCopy;
    }

}