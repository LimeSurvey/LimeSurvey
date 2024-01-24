<?php

namespace LimeSurvey\Api\Command\Mixin\Accessor;

use Survey;

trait SurveyModelTrait
{
    private $survey = null;

    /**
     * Get Survey
     *
     * Used as a proxy for providing a mock record during testing.
     *
     * @param int $id
     * @return Survey
     */
    public function getSurveyModel($id): ?Survey
    {
        if (!$this->survey) {
            $this->survey = Survey::model()->findByPk($id);
        }

        return $this->survey;
    }

    /**
     * Set Survey
     *
     * Used to set mock record during testing.
     *
     * @param Survey $survey
     * @return void
     */
    public function setSurveyModel(Survey $survey)
    {
        $this->survey = $survey;
    }
}
