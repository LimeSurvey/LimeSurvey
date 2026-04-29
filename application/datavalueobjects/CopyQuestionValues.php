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

        /** @var int the position for the question on the left menu group */
        private $questionPositionInGroup;

        /** @var array<string,CopyQuestionTextValues> the new question texts */
        private $questionL10nData;

        /** @var int  the surveyId from which the question is copied*/
        private $sourceSurveyId;


    public function getSourceSurveyId(): int
    {
        return $this->sourceSurveyId;
    }

    public function setSourceSurveyId(int $sourceSurveyId): void
    {
        $this->sourceSurveyId = $sourceSurveyId;
    }

    /**
     * @return int
     */
    public function getQuestionPositionInGroup(): int
    {
        return $this->questionPositionInGroup;
    }

    /**
     * @param int $questionPositionInGroup
     */
    public function setQuestionPositionInGroup(int $questionPositionInGroup): void
    {
        $this->questionPositionInGroup = $questionPositionInGroup;
    }

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
        return $this->questionGroupId;
    }

    /**
     * @param int $questionGroupId
     */
    public function setQuestionGroupId(int $questionGroupId): void
    {
        $this->questionGroupId = $questionGroupId;
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

    /**
     * @return array<string,CopyQuestionTextValues>
     */
    public function getQuestionL10nData(): array
    {
        return $this->questionL10nData;
    }

    /**
     * @param array<string,CopyQuestionTextValues> $questiontoCopy
     */
    public function setQuestionL10nData(array $questionL10nData): void
    {
        $this->questionL10nData = $questionL10nData;
    }
}
