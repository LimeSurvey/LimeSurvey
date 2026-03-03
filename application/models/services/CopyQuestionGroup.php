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
     * @param int $surveyId  the new survey id
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
     * @param bool $adaptLinks if true, links in the question group description will be adapted to the new survey id.
     *
     * @return QuestionGroup
     * @throws \Exception
     */
    public function copyQuestionGroup($adaptLinks)
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
            //copy question group languages
            $questionGroupLanguages = QuestionGroupL10n::model()->findAllByAttributes(['gid' => $this->questionGroup->gid]);
            foreach ($questionGroupLanguages as $questionGroupLanguage) {
                $newQuestionGroupLanguage = new QuestionGroupL10n();
                $newQuestionGroupLanguage->attributes = $questionGroupLanguage->attributes;
                if ($adaptLinks) {
                    $newQuestionGroupLanguage->description = translateLinks(
                        'survey',
                        $this->questionGroup->sid, //old survey id
                        $this->surveyId, //new survey id
                        $questionGroupLanguage->description
                    );
                }
                $newQuestionGroupLanguage->gid = $newQuestionGroup->gid;
                $newQuestionGroupLanguage->save();
            }
        }

        return $newQuestionGroup;
    }
}
