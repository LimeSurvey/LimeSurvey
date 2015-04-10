<?php

/**
 * Class SurveySession
 * IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT
 *
 * If you build caches for the getters, make sure to exclude them from serialization (__sleep).
 * This class is stored in the session and therefore requires some extra care:
 *
 * IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT IMPORTANT
 *
 * @property bool $isFinished
 * @property int $surveyId
 * @property string $language
 * @property Survey $survey;
 */
class SurveySession extends CComponent {

    /**
     * These variables are not serialized.
     */
    /**
     * @var Response
     */
    private $_response;
    /**
     * @var Survey
     */
    private $_survey;

    /**
     * These are serialized
     */
    protected $surveyId;
    protected $id;
    protected $responseId;
    protected $finished = false;
    protected $language = 'en';
    /**
     * @param int $surveyId
     * @param int $responseId
     */
    public function __construct($surveyId, $responseId, $id)
    {
        $this->surveyId = $surveyId;
        $this->responseId = $responseId;
        $this->id = $id;
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

    public function getLanguage() {
        return $this->language;
    }
    /**
     * Returns the session id for this session.
     * The session id is unique per browser session and does not need to be unguessable.
     * In fact it is just an auto incremented number.
     */
    public function getId() {
        return $this->id;
    }

    public function getResponse() {
        if (!isset($this->_response)) {
            $this->_response = Response::model($this->surveyId)->findByPk($this->responseId);
        }
        return $this->_response;
    }

    public function getSurvey() {
        if (!isset($this->_survey)) {
            $this->_survey = Survey::model()->findByPk($this->surveyId);
        }
        return $this->_survey;
    }
    public function getStep() {
        return 1;

    }

    public function getMaxStep() {
        return 5;
    }

    public function getPrevStep() {
        return 1;
    }

    public function __sleep() {
        return [
            'surveyId',
            'id',
            'responseId',
            'finished',
            'language'
        ];
    }
}