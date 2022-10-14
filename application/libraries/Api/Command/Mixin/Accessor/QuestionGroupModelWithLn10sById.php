<?php

namespace LimeSurvey\Api\Command\Mixin\Accessor;

use QuestionGroup;

trait QuestionGroupModelWithLn10sById
{
    private $questionGroupWithLn10sById = null;

    /**
     * Get question group with ln10s by id
     *
     * Used as a proxy for providing a mock record during testing.
     *
     * @param int $id
     * @return Array
     */
    public function getQuestionGroupModelWithLn10sById($id)
    {
        if (!$this->questionGroupWithLn10sById) {
            $this->questionGroupWithLn10sById = QuestionGroup::model()
                ->with('questiongroupl10ns')
                ->findByAttributes(array('gid' => $id));
        }

        return $this->questionGroupWithLn10sById;
    }

    /**
     * Set Question Group
     *
     * Used to set mock record during testing.
     *
     * @param int $id
     * @return void
     */
    public function setQuestionGroupModelWithLn10sById($collection)
    {
        $this->questionGroupWithLn10sById = $collection;
    }
}
