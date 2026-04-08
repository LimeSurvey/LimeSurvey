<?php

namespace LimeSurvey\Api\Command\Mixin\Accessor;

use Question;

trait QuestionModelWithL10nsByIdAndLanguageTrait
{
    private $questionWithL10nsByIdAndLanguage = null;

    /**
     * Get question group with L10ns by survey ID
     *
     * Used as a proxy for providing a mock record during testing.
     *
     * @param int $id
     * @return Question
     */
    public function getQuestionModelCollectionWithL10nsByIdAndLanguage($id, $language)
    {
        if (!$this->questionWithL10nsByIdAndLanguage) {
            $this->questionWithL10nsByIdAndLanguage = Question::model()->with('questionl10ns')
                ->find(
                    't.qid = :qid and questionl10ns.language = :language',
                    array(':qid' => $id, ':language' => $language)
                );
        }

        return $this->questionWithL10nsByIdAndLanguage;
    }

    /**
     * Set Question Group
     *
     * Used to set mock record during testing.
     *
     * @param array $collection
     * @return void
     */
    public function setQuestionModelCollectionWithL10nsByIdAndLanguage($collection)
    {
        $this->questionWithL10nsByIdAndLanguage = $collection;
    }
}
