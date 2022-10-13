<?php

namespace LimeSurvey\Api\Command\Mixin\Accessor;

use QuestionGroup;

trait QuestionGroupModelCollectionWithLn10sBySid
{
    private $questionGroupListWithLn10sBySid = null;

    /**
     * Get question group with ln10s by survey id
     *
     * Used as a proxy for providing a mock record during testing.
     *
     * @param int $id
     * @return Array
     */
    public function getQuestionGroupModelCollectionWithLn10sBySid($id)
    {
        if (!$this->questionGroupListWithLn10sBySid) {
            $this->questionGroupListWithLn10sBySid = QuestionGroup::model()
                ->with('questiongroupl10ns')
                ->findAllByAttributes(array('sid' => $id));
        }

        return $this->questionGroupListWithLn10sBySid;
    }

    /**
     * Set Question Group
     *
     * Used to set mock record during testing.
     *
     * @param int $id
     * @return void
     */
    public function setQuestionGroupModelCollectionWithLn10sBySid($collection)
    {
        $this->questionGroupListWithLn10sBySid = $collection;
    }
}
