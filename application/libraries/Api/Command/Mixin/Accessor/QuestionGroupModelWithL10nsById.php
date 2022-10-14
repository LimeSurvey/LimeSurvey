<?php

namespace LimeSurvey\Api\Command\Mixin\Accessor;

use QuestionGroup;

trait QuestionGroupModelWithL10nsById
{
    private $questionGroupWithL10nsById = null;

    /**
     * Get question group with L10ns by id
     *
     * Used as a proxy for providing a mock record during testing.
     *
     * @param int $id
     * @return QuestionGroup
     */
    public function getQuestionGroupModelWithL10nsById($id)
    {
        if (!$this->questionGroupWithL10nsById) {
            $this->questionGroupWithL10nsById = QuestionGroup::model()
                ->with('questiongroupl10ns')
                ->findByAttributes(array('gid' => $id));
        }

        return $this->questionGroupWithL10nsById;
    }

    /**
     * Set Question Group
     *
     * Used to set mock record during testing.
     *
     * @param int $id
     * @return void
     */
    public function setQuestionGroupModelWithL10nsById($collection)
    {
        $this->questionGroupWithL10nsById = $collection;
    }
}
