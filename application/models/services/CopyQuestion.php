<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Datavalueobjects\CopyQuestionValues;

/**
 * Class CopyQuestion
 *
 * This class is responsible for the copy question process.
 *
 * @package LimeSurvey\Models\Services
 */
class CopyQuestion
{
    /**
     * @var CopyQuestionValues values needed to copy a question (e.g. questioncode, questionGroupId ...)
     */
    private $copyQuestionValues;

    /**
     * @var \Question the new question
     */
    private $newQuestion;

    /**
     * @var array map between original subquestion id and new subquestion id
     */
    private $mappedSubquestionIds = [];

    /**
     * CopyQuestion constructor.
     *
     * @param CopyQuestionValues $copyQuestionValues
     */
    public function __construct($copyQuestionValues)
    {
        $this->copyQuestionValues = $copyQuestionValues;
        $this->newQuestion = null;
    }

    /**
     * Copies the question and all necessary values/parameters
     * (languages, subquestions, answeroptions, defaultanswers, settings)
     *
     * @param array $copyOptions has the following boolean elements
     *                          ['copySubquestions']
     *                          ['copyAnswerOptions']
     *                          ['copyDefaultAnswers']
     *                          ['copySettings'] --> generalSettings and advancedSettings
     * @param int|null $surveyId The id of the survey to which the new question should be added. If null, it will be added to the current survey.
     *
     * @return boolean True if new copied question could be saved, false otherwise
     */
    public function copyQuestion($copyOptions, $surveyId = null)
    {
        $copySuccessful = $this->createNewCopiedQuestion(
            $this->copyQuestionValues->getQuestionCode(),
            $this->copyQuestionValues->getQuestionGroupId(),
            $this->copyQuestionValues->getQuestiontoCopy(),
            $surveyId
        );
        if ($copySuccessful) {
            //copy question languages
            $this->copyQuestionLanguages($this->copyQuestionValues->getQuestiontoCopy(), $this->copyQuestionValues->getQuestionL10nData());

            //copy subquestions
            if ($copyOptions['copySubquestions']) {
                $this->copyQuestionsSubQuestions($this->copyQuestionValues->getQuestiontoCopy()->qid, $surveyId);
            }

            //copy answer options
            if ($copyOptions['copyAnswerOptions']) {
                $this->copyQuestionsAnswerOptions($this->copyQuestionValues->getQuestiontoCopy()->qid);
            }

            //copy default answers
            if ($copyOptions['copyDefaultAnswers']) {
                $this->copyQuestionsDefaultAnswers($this->copyQuestionValues->getQuestiontoCopy()->qid);
            }

            ////copy question settings (generalsettings and advanced settings)
            if ($copyOptions['copySettings']) {
                $this->copyQuestionsSettings($this->copyQuestionValues->getQuestiontoCopy()->qid);
            }
        }
        return $copySuccessful;
    }


    /**
     * Creates a new question copying the values from questionToCopy
     *
     * @param string $questionCode
     * @param int $groupId
     * @param \Question $questionToCopy the question that should be copied
     * @param int $surveyId null if copied to same survey
     *
     * @return bool true if question could be saved, false otherwise
     */
    public function createNewCopiedQuestion($questionCode, $groupId, $questionToCopy, $surveyId = null)
    {
        $this->newQuestion = new \Question();
        // We need to use setAttributes here with $safeOnly=false to avoid issue #18323.
        // Otherwise validators are loaded before setting the attributes.
        // Right now, probably a bug, some rules are added as validators conditionally, depending on the attribute values.
        // Then the rules set (final validators loaded) may not match the attributes which are later set.
        $this->newQuestion->setAttributes($questionToCopy->attributes, false);
        $this->newQuestion->title = $questionCode;
        $this->newQuestion->gid = $groupId;
        $this->newQuestion->question_order = $this->copyQuestionValues->getQuestionPositionInGroup();
        $this->newQuestion->qid = null;
        $this->newQuestion->sid = $surveyId;

        return $this->newQuestion->save();
    }

    /**
     * Copies the languages of a question.
     *
     * @param \Question $oQuestion old question from where to copy the languages (see table questions_l10ns)
     * @param array<string,\LimeSurvey\Datavalueobjects\CopyQuestionTextValues> $newQuestionL10nData the text values to override
     *
     * @before $this->newQuestion must exist and should not be null
     *
     * @return bool true if all languages could be copied,
     *              false if no language was copied or save failed for one language
     */
    private function copyQuestionLanguages($oQuestion, $newQuestionL10nData = [])
    {
        $allLanguagesAreCopied = false;
        if ($oQuestion !== null) {
            $allLanguagesAreCopied = true;
            foreach ($oQuestion->questionl10ns as $questionl10n) {
                $copyLanguage = new \QuestionL10n();
                $copyLanguage->attributes = $questionl10n->attributes;
                $copyLanguage->id = null; //new id needed
                $copyLanguage->qid = $this->newQuestion->qid;
                if (isset($newQuestionL10nData[$questionl10n->language])) {
                    $copyLanguage->question = $newQuestionL10nData[$questionl10n->language]->getQuestionText();
                    $copyLanguage->help = $newQuestionL10nData[$questionl10n->language]->getHelp();
                }
                $allLanguagesAreCopied = $allLanguagesAreCopied && $copyLanguage->save();
            }
        }

        return $allLanguagesAreCopied;
    }

    /**
     * Copy subquestions of a question
     *
     * @param int $parentId id of question to be copied
     * @param int|null $surveyId The id of the survey to which the new question should be added.
     *                           If null, it will be added to the current survey.
     *
     * * @before $this->newQuestion must exist and should not be null
     *
     * @return bool true if all subquestions could be copied&saved, false if a subquestion could not be saved
     */
    private function copyQuestionsSubQuestions($parentId, $surveyId = null)
    {
        //copy subquestions
        $areSubquestionsCopied = true;
        $subquestions = \Question::model()->findAllByAttributes(['parent_qid' => $parentId]);

        foreach ($subquestions as $subquestion) {
            $copiedSubquestion = new \Question();
            // We need to use setAttributes here with $safeOnly=false to avoid issue #18323.
            // Otherwise validators are loaded before setting the attributes.
            // Right now, probably a bug, some rules are added as validators conditionally, depending on the attribute values.
            // Then the rules set (final validators loaded) may not match the attributes which are later set.
            $copiedSubquestion->setAttributes($subquestion->attributes, false);
            $copiedSubquestion->parent_qid = $this->newQuestion->qid;
            $copiedSubquestion->qid = null; //new question id needed ...
            if ($surveyId !== null) {
                $copiedSubquestion->sid = $surveyId;
            }
            $areSubquestionsCopied = $areSubquestionsCopied && $copiedSubquestion->save();
            $this->mappedSubquestionIds[$subquestion->qid] = $copiedSubquestion->qid; // map old subquestion id to new subquestion id
            foreach ($subquestion->questionl10ns as $subquestLanguage) {
                $newSubquestLanguage = new \QuestionL10n();
                $newSubquestLanguage->attributes = $subquestLanguage->attributes;
                $newSubquestLanguage->qid = $copiedSubquestion->qid;
                $newSubquestLanguage->id = null;
                $newSubquestLanguage->save();
            }
        }

        return $areSubquestionsCopied;
    }

    /**
     * Returns the mapping of subquestions
     *
     * * @before $this->newQuestion must exist and should not be null
     */
    public function getMappedSubquestionIds()
    {
        return $this->mappedSubquestionIds;
    }

    /**
     * Copies the answer options of a question
     *
     * * @before $this->newQuestion must exist and should not be null
     *
     * @param int $questionIdToCopy
     */
    private function copyQuestionsAnswerOptions($questionIdToCopy)
    {
        $answerOptions = \Answer::model()->findAllByAttributes(['qid' => $questionIdToCopy]);
        foreach ($answerOptions as $answerOption) {
            $copiedAnswerOption = new \Answer();
            $copiedAnswerOption->attributes = $answerOption->attributes;
            $copiedAnswerOption->aid = null;
            $copiedAnswerOption->qid = $this->newQuestion->qid;
            if ($copiedAnswerOption->save()) {
                //copy the languages
                foreach ($answerOption->answerl10ns as $answerLanguage) {
                    $copiedAnswerOptionLanguage = new \AnswerL10n();
                    $copiedAnswerOptionLanguage->attributes = $answerLanguage->attributes;
                    $copiedAnswerOptionLanguage->id = null;
                    $copiedAnswerOptionLanguage->aid = $copiedAnswerOption->aid;
                    $copiedAnswerOptionLanguage->save();
                }
            }
        }
    }

    /**
     * Copies the default answers of the question
     *
     * * @before $this->newQuestion must exist and should not be null
     *
     * @param int $questionIdToCopy
     */
    private function copyQuestionsDefaultAnswers($questionIdToCopy)
    {
        $defaultAnswers = \DefaultValue::model()->findAllByAttributes(['qid' => $questionIdToCopy]);
        foreach ($defaultAnswers as $defaultAnswer) {
            $copiedDefaultAnswer = new \DefaultValue();
            $copiedDefaultAnswer->attributes = $defaultAnswer->attributes;
            $copiedDefaultAnswer->qid = $this->newQuestion->qid;
            $copiedDefaultAnswer->dvid = null;
            if ($copiedDefaultAnswer->save()) {
                //copy languages if needed
                $defaultValLanguages = \DefaultValueL10n::model()
                  ->findAllByAttributes(['dvid' => $defaultAnswer->dvid]);
                foreach ($defaultValLanguages as $defaultAnswerL10n) {
                    $copieDefaultAnswerLanguage = new \DefaultValueL10n();
                    $copieDefaultAnswerLanguage->attributes = $defaultAnswerL10n->attributes;
                    $copieDefaultAnswerLanguage->dvid = $copiedDefaultAnswer->dvid;
                    $copieDefaultAnswerLanguage->id = null;
                    $copieDefaultAnswerLanguage->save();
                }
            }
        }
    }

    /**
     * Copies the question settings (general_settings (on the left in questioneditor) and advanced settings (bottom)
     *
     * @param $questionIdToCopy
     *
     * * @before $this->newQuestion must exist and should not be null
     *
     * @return boolean True if settings are copied, false otherwise
     */
    private function copyQuestionsSettings($questionIdToCopy)
    {
        $settingsFromQuestionToCopy = \QuestionAttribute::model()->findAllByAttributes(['qid' => $questionIdToCopy]);
        $areSettingsCopied = false;
        if ($this->newQuestion !== null) {
            $areSettingsCopied = true;
            foreach ($settingsFromQuestionToCopy as $settingToCopy) {
                $newSetting = new \QuestionAttribute();
                $newSetting->attributes = $settingToCopy->attributes;
                $newSetting->qaid = null;  //create new id
                $newSetting->qid = $this->newQuestion->qid;
                $areSettingsCopied = $areSettingsCopied && $newSetting->save();
            }
        }

        return $areSettingsCopied;
    }

    /**
     * Returns the new created question or null if question was not copied.
     *
     * @return \Question|null
     */
    public function getNewCopiedQuestion()
    {
        return $this->newQuestion;
    }
}
