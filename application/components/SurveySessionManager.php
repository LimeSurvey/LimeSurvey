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
     * @var SurveySession[]
     */
    protected $sessions;


    public function init()
    {
        $session = App()->session;
        $this->sessions = $session->get('SSM', null);
        if (!isset($this->sessions)) {
            $session->add('SSM', $this->sessions = new CTypedMap('SurveySession'));
        }
        if ((null !== $current = App()->request->getParam('SSM')) && isset($this->sessions[$current])) {
            $this->current = $this->sessions[$current];
        }
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

    public function getSession($id)
    {
        return $this->sessions[$id];

    }

    public function newSession($surveyId, $responseId)
    {
        /** @var SurveySession $session */
        foreach($this->sessions as $session) {
            if ($session->getSurveyId() == $surveyId && $session->getResponseId() == $responseId) {
                throw new \Exception("Duplicate session detected.");
            }
        }
        return $this->sessions[] = new SurveySession($surveyId, $responseId, count($this->sessions));
    }


}