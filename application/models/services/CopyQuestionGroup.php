<?php

namespace LimeSurvey\Models\Services;

use QuestionGroup;
use QuestionGroupL10n;

class CopyQuestionGroup
{
    /** @var QuestionGroup */
    private $questionGroup;

    /** @var int */
    private $surveyId;

    /**
     * @param QuestionGroup $questionGroup
     * @param string|null $newQuestionGroupCode if set to null, the survey id of the given group will be used
     * @param int $surveyId
     */
    public function __construct($questionGroup, $surveyId)
    {
        $this->questionGroup = $questionGroup;
        $this->surveyId = $surveyId;
    }

    /**
     * Copies a question group.
     *
     * It is possible to copy the question group to a different survey
     * by setting the surveyId.
     *
     * @return QuestionGroup
     * @throws \Exception
     */
    public function copyQuestionGroup()
    {
        //copy the group
        $newQuestionGroup = new QuestionGroup();
        $newQuestionGroup->attributes = $this->questionGroup->attributes;
        if ($this->surveyId != null) {
            $newQuestionGroup->sid = $this->surveyId;
        }
        if (!$newQuestionGroup->save()) {
            throw new \Exception('Failed to copy question group');
        } else {
            //copy questiongroup languages
            $questionGroupLanguages = QuestionGroupL10n::model()->findAllByAttributes(['gid' => $this->questionGroup->gid]);
            foreach ($questionGroupLanguages as $questionGroupLanguage) {
                $newQuestionGroupLanguage = new QuestionGroupL10n();
                $newQuestionGroupLanguage->attributes = $questionGroupLanguage->attributes;
                $newQuestionGroupLanguage->gid = $newQuestionGroup->gid;
                $newQuestionGroupLanguage->save();
            }
        }

        return $newQuestionGroup;
    }
}
