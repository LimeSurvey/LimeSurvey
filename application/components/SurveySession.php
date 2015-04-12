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
    const FORMAT_GROUP = 'G';
    const FORMAT_QUESTION = 'Q';
    const FORMAT_SURVEY = 'A';
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
    protected $_step = 1;
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
        return $this->_step;

    }


    public function getMaxStep() {
        switch($this->survey->format) {
            case 'G':
                return count($this->survey->groups);
            case 'A':
                return 1;
            case' Q':
                return count($this->survey->questions);
        }
    }

    public function getPrevStep() {
        return $this->_step > 1 ? $this->_step - 1 : 1;
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

    /**
     * Returns the list of question groups.
     * Ordered according to the randomization groups.
     */
    public function getGroups()
    {
        $groups = $this->survey->groups;

        // Get all randomization groups in order.
        $order = [];
        $randomizationGroups = [];
        foreach ($groups as $group) {
            if (empty($group->randomization_group)) {
                $order[] = $group->randomization_group;
                $randomizationGroups[$group->randomization_group][] =$group;
            } else {
                $order[] = $group;
            }
        }
        foreach ($order as $i => $group) {
            if (is_string($group)) {
                // Draw a random group from the randomizationGroups array.
                /**
                 * @todo This is not truly random. It would be better to use mt_rand with the response ID as seed
                 * (so it's reproducible. But Suhosin doesn't allow seeding mt_rand.
                 */
                $seed = array_values(unpack('L',
                    substr(md5($this->responseId . count($randomizationGroups[$group]), true), -4, 4)))[0];

                $randomIndex = $seed % count($randomizationGroups[$group]);

                $order[$i] = $randomizationGroups[$group][$randomIndex];
                $ids[] = $order[$i]->gid;
                unset($randomizationGroups[$group][$randomIndex]);
                $randomizationGroups[$group] = array_values($randomizationGroups[$group]);
            }
        }
        return $order;
    }

    /**
     * Getter for the format. In the future we could allow override per session.
     * @return FORMAT_QUESTION|FORMAT_GROUP|FORMAT_SURVEY;
     */
    public function getFormat() {
        return $this->survey->format;
    }
}