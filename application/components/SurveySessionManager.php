<?php

/**
 * Class SurveySessionManager
 * @property SurveySession $current
 * @property-read boolean $active
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

    /**
     * CreateURL wrapper that will add the survey session identifier to the created URL.
     * @param $route
     * @param $params
     * @return string
     */
    public function createUrl($route, $params = []) {
        if ($this->active) {
            $params['SSM'] = array_search($this->_current, $this->sessions->toArray());
        }
        return App()->createUrl($route, $params);
    }

    /**
     * @return SurveySession
     */
    public function getCurrent() {
        if (is_object($this->_current) && !$this->_current instanceof SurveySession) {
            throw new \Exception("Invalid session object of type: '" . get_class($this->_current));
        }
        return $this->_current;
    }


    public function setCurrent(SurveySession $session) {
        $this->_current = $session;
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
        // Doesn't really need to be random
        $sessionId = rand(1, 1000);
        $this->_current = $this->sessions[$sessionId] = new SurveySession($surveyId, $responseId, $sessionId);
        return $this->current;
    }

    /**
     * Destroys a survey session, if no id is given the currently active session is destroyed.
     * @param int $id
     */
    public function destroySession($id = null) {
        if (isset($id)) {
            if ($this->_current == $this->sessions[$id]) {
                $this->_current = null;
            }
            $this->sessions->remove($id);
        } elseif (isset($this->_current)) {
            if (false !== $id = array_search($this->_current, $this->sessions->toArray())) {
                $this->sessions->remove($id);
            }
            $this->_current = null;
        }
    }


}