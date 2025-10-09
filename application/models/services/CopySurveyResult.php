<?php

namespace LimeSurvey\Models\Services;

use Survey;

/**
 * This class has all the results from the copy survey process.
 */

class CopySurveyResult
{
    /** @var Survey */
    private $copiedSurvey = null;

    /** @var int */
    private $cntSurveys = 1;

    /** @var int */
    private $cntSurveyLanguages = 1;

    /** @var int */
    private $cntQuestionGroups = 0;

    /** @var int */
    private $cntQuestions = 0;

    /** @var int */
    private $cntQuestionAttributes = 0;

    /** @var int */
    private $cntAnswerOptions = 0;

    /** @var int */
    private $cntSubquestions = 0;

    /** @var int */
    private $cntDefaultAnswers = 0;

    /** @var int */
    private $cntAssessments = 0;

    /** @var int */
    private $cntQuotas = 0;

    /** @var int */
    private $cntQuotaMembers = 0;

    /** @var int */
    private $cntQuotaLanguageSettings = 0;

    /** @var array */
    private $warnings = [];

    /** @var array */
    private $errors = [];

    /**
     * @return Survey|null
     */
    public function getCopiedSurvey()
    {
        return $this->copiedSurvey;
    }

    /**
     * @param Survey $copiedSurvey
     * @return void
     */
    public function setCopiedSurvey($copiedSurvey)
    {
        $this->copiedSurvey = $copiedSurvey;
    }

    /**
     * @return int
     */
    public function getCntSurveys(): int
    {
        return $this->cntSurveys;
    }

    /**
     * @param int $cntSurveys
     */
    public function setCntSurveys(int $cntSurveys): void
    {
        $this->cntSurveys = $cntSurveys;
    }

    /**
     * @return int
     */
    public function getCntSurveyLanguages(): int
    {
        return $this->cntSurveyLanguages;
    }

    /**
     * @param int $cntSurveyLanguages
     */
    public function setCntSurveyLanguages(int $cntSurveyLanguages): void
    {
        $this->cntSurveyLanguages = $cntSurveyLanguages;
    }

    /**
     * @return int
     */
    public function getCntQuestionGroups(): int
    {
        return $this->cntQuestionGroups;
    }

    /**
     * @param int $cntQuestionGroups
     */
    public function setCntQuestionGroups(int $cntQuestionGroups): void
    {
        $this->cntQuestionGroups = $cntQuestionGroups;
    }

    /**
     * @return int
     */
    public function getCntQuestions(): int
    {
        return $this->cntQuestions;
    }

    /**
     * @param int $cntQuestions
     */
    public function setCntQuestions(int $cntQuestions): void
    {
        $this->cntQuestions = $cntQuestions;
    }

    /**
     * @return int
     */
    public function getCntQuestionAttributes(): int
    {
        return $this->cntQuestionAttributes;
    }

    /**
     * @param int $cntQuestionAttributes
     */
    public function setCntQuestionAttributes(int $cntQuestionAttributes): void
    {
        $this->cntQuestionAttributes = $cntQuestionAttributes;
    }

    /**
     * @return int
     */
    public function getCntAnswerOptions(): int
    {
        return $this->cntAnswerOptions;
    }

    /**
     * @param int $cntAnswerOptions
     */
    public function setCntAnswerOptions(int $cntAnswerOptions): void
    {
        $this->cntAnswerOptions = $cntAnswerOptions;
    }

    /**
     * @return int
     */
    public function getCntSubquestions(): int
    {
        return $this->cntSubquestions;
    }

    /**
     * @param int $cntSubquestions
     */
    public function setCntSubquestions(int $cntSubquestions): void
    {
        $this->cntSubquestions = $cntSubquestions;
    }

    /**
     * @return int
     */
    public function getCntDefaultAnswers(): int
    {
        return $this->cntDefaultAnswers;
    }

    /**
     * @param int $cntDefaultAnswers
     */
    public function setCntDefaultAnswers(int $cntDefaultAnswers): void
    {
        $this->cntDefaultAnswers = $cntDefaultAnswers;
    }

    /**
     * @return int
     */
    public function getCntAssessments(): int
    {
        return $this->cntAssessments;
    }

    /**
     * @param int $cntAssessments
     */
    public function setCntAssessments(int $cntAssessments): void
    {
        $this->cntAssessments = $cntAssessments;
    }

    /**
     * @return int
     */
    public function getCntQuotas(): int
    {
        return $this->cntQuotas;
    }

    /**
     * @param int $cntQuotas
     */
    public function setCntQuotas(int $cntQuotas): void
    {
        $this->cntQuotas = $cntQuotas;
    }

    /**
     * @return int
     */
    public function getCntQuotaMembers(): int
    {
        return $this->cntQuotaMembers;
    }

    /**
     * @param int $cntQuotaMembers
     */
    public function setCntQuotaMembers(int $cntQuotaMembers): void
    {
        $this->cntQuotaMembers = $cntQuotaMembers;
    }

    /**
     * @return int
     */
    public function getCntQuotaLanguageSettings(): int
    {
        return $this->cntQuotaLanguageSettings;
    }

    /**
     * @param int $cntQuotaLanguageSettings
     */
    public function setCntQuotaLanguageSettings(int $cntQuotaLanguageSettings): void
    {
        $this->cntQuotaLanguageSettings = $cntQuotaLanguageSettings;
    }

    /**
     * @return array
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * @param array $warnings
     */
    public function setWarnings(array $warnings): void
    {
        $this->warnings[] = $warnings;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }


}
