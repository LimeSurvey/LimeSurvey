<?php

/**
 * Service class to deactivate survey.
 * @todo Move to models/services/survey/ folder.
 */

class SurveyDeactivator
{
    /** @var Survey */
    protected $survey;

    /** @var array */
    protected $tableDefinition = [];

    /** @var array */
    protected $timingsTableDefinition = [];

    /** @var string */
    protected $error;

    public function __construct($survey = null)
    {
        $this->survey = $survey;
    }

    /**
     * @param Survey $survey
     * @return SurveyDeactivator
     */
    public function setSurvey(Survey $survey)
    {
        $this->survey = $survey;
        return $this;
    }

    /**
     * Prepares a survey to Set it into "deactivate" state.
     * Fires events "beforeSurveyDeactivate"
     *
     * @return PluginEvent
     * @throws CException
     */
    public function beforeDeactivate()
    {
        EmCacheHelper::init(['sid' => $this->survey->sid, 'active' => 'Y']);
        EmCacheHelper::flush();

        // Fire event beforeSurveyDeactivate
        $beforeSurveyDeactivate = new PluginEvent('beforeSurveyDeactivate');
        $beforeSurveyDeactivate->set('surveyId', $this->survey->sid);
        App()->getPluginManager()->dispatchEvent($beforeSurveyDeactivate);
        return $beforeSurveyDeactivate;
    }

    /**
     * Performs work after a survey was Set it into "deactivate" state.
     * Archives necessary tables "responseTable", "timingTable".
     * Fires events "afterSurveyDeactivate"
     *
     * @return PluginEvent
     * @throws CException
     */
    public function afterDeactivate()
    {
        $event = new PluginEvent('afterSurveyDeactivate');
        $event->set('surveyId', $this->survey->sid);
        App()->getPluginManager()->dispatchEvent($event);
        return $event;
    }
}
