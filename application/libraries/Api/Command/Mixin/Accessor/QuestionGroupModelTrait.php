<?php

namespace LimeSurvey\Api\Command\Mixin\Accessor;

use QuestionGroup;

trait QuestionGroupModelTrait
{
    private $questionGroup = null;

    /**
     * Get Question Group
     *
     * Used as a proxy for providing a mock record during testing.
     *
     * @param int $id
     * @return QuestionGroup
     */
    public function getQuestionGroupModel($id): ?QuestionGroup
    {
        if (!$this->questionGroup) {
            $this->questionGroup =
                QuestionGroup::model()
                ->findByAttributes(array('gid' => $id));
        }

        return $this->questionGroup;
    }

    /**
     * Set Question Group
     *
     * Used to set mock record during testing.
     *
     * @param int $id
     * @return void
     */
    public function setQuestionGroupModel(QuestionGroup $questionGroup)
    {
        $this->questionGroup = $questionGroup;
    }
}
