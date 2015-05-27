<?php

/**
 * Class SurveySessionManager
 * @property-read SurveySession $current
 * @property-read \CTypedMap $sessions
 */
class SurveySessionManager extends CApplicationComponent
{
    /**
     * @var SurveySession
     */
    protected $_current;
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
        if ((null !== $current = App()->request->getParam('SSM')) && isset($this->sessions[$current])) {
            $this->_current = $this->sessions[$current];
        }
    }

    public function getActive() {
        return isset($this->_current);
    }

    public function getCurrent() {
        return $this->_current;
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

    public function newSession($surveyId, $responseId = null)
    {
        /** @var SurveySession $session */
        if (!isset($responseId)) {
            $response = Response::create($surveyId);
            $response->save();
            $responseId = $response->id;
        }

        foreach($this->sessions as $session) {
            if ($session->getSurveyId() == $surveyId && $session->getResponseId() == $responseId) {
                throw new \Exception("Duplicate session detected.");
            }
        }
        $this->_current = $this->sessions[] = new SurveySession($surveyId, $responseId, count($this->sessions));
        return $this->current;
    }


}