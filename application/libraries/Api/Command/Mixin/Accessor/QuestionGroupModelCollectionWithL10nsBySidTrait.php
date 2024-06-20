<?php

namespace LimeSurvey\Api\Command\Mixin\Accessor;

use QuestionGroup;

trait QuestionGroupModelCollectionWithL10nsBySidTrait
{
    private $questionGroupListWithL10nsBySid = null;

    /**
     * Get question group with L10ns by survey ID
     *
     * Used as a proxy for providing a mock record during testing.
     *
     * @param int $id
     * @return Array
     */
    public function getQuestionGroupModelCollectionWithL10nsBySid($id)
    {
        if (!$this->questionGroupListWithL10nsBySid) {
            $this->questionGroupListWithL10nsBySid = QuestionGroup::model()
                ->with('questiongroupl10ns')
                ->findAllByAttributes(array('sid' => $id));
        }

        return $this->questionGroupListWithL10nsBySid;
    }

    /**
     * Set Question Group
     *
     * Used to set mock record during testing.
     *
     * @param int $id
     * @return void
     */
    public function setQuestionGroupModelCollectionWithL10nsBySid($collection)
    {
        $this->questionGroupListWithL10nsBySid = $collection;
    }
}
