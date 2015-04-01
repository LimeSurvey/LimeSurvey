<?php

/**
 * Class SurveySession
 */
class SurveySession extends CComponent {

    protected $surveyId;
    protected $responseId;
    protected $finished = false;
    /**
     * @param int $surveyId
     * @param int $responseId
     */
    public function __construct($surveyId, $responseId)
    {
        $this->surveyId = $surveyId;
        $this->responseId = $responseId;
    }

    public function getSurveyId() {
        return $this->surveyId;
    }

    public function getResponseId() {
        return $this->responseId;
    }

    public function getIsFinished() {
        return $this->finished;
    }
}