<?php

/**
 * Class SurveySessionManager
 * @property-read SurveySession $current
 * @property-read SurveySession[] $sessions
 */
class SurveySessionManager extends CApplicationComponent
{
    /**
     * @var SurveySession
     */
    protected $current;
    /*
     * @var \CTypedMap
     */
    protected $sessions;


    public function init()
    {
        $session = App()->session;
        $this->sessions = $session->get('SSM', null);
        if (!isset($this->sessions)) {
            $session->add('SSM', $this->sessions = new CTypedMap('SurveySession'));
        }
        $current = App()->request->getPost('SSM', []);
    }

    public function getActive() {
        return isset($this->current);
    }

    public function getCurrent() {
        return $this->current;
    }

    /**
     * @return SurveySession[]
     */
    public function getSessions() {
        return $this->sessions;
    }

    public function newSession($surveyId, $responseId)
    {
        if (isset($this->sessions["$surveyId.$responseId"])) {
            throw new \Exception("Duplicate session detected.");
        }
        return $this->sessions["$surveyId.$responseId"] = new SurveySession($surveyId, $responseId);
    }


}