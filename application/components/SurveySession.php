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
    const FORMAT_QUESTION = 'S';
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
    protected $_maxStep = 0;
    protected $postKey;
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
            $this->_survey = Survey::model()->with('groups.questions')->findByPk($this->surveyId);
        }
        return $this->_survey;
    }

    public function getStep() {
        return $this->_step;
    }

    public function setStep($value) {
        if (!is_int($value)) {
            throw new \BadMethodCallException('Parameter $value must be an integer.');
        }
        $this->_step = $value;
        $this->_maxStep = max($this->_step, $this->_maxStep);
    }


    public function getMaxStep() {
        return $this->_maxStep;
    }

    public function getPrevStep() {
        return $this->_step > 1 ? $this->_step - 1 : 1;
    }

    public function __sleep() {
        return [
            'surveyId',
            'id',
            '_step',
            '_maxStep',
            'responseId',
            'finished',
            'language',
            'postKey'
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

    public function getQuestions(QuestionGroup $group) {
        return $group->questions;

        $questions = $group->questions;

        // Get all randomization groups in order.
        $order = [];
        $randomizationGroups = [];
        foreach ($questions as $question) {
            if (empty($question->randomization_group)) {
                $order[] = $question->randomization_group;
                $randomizationGroups[$question->randomization_group][] =$question;
            } else {
                $order[] = $group;
            }
        }
        foreach ($order as $i => $question) {
            if (is_string($question)) {
                // Draw a random question from the randomizationGroups array.
                /**
                 * @todo This is not truly random. It would be better to use mt_rand with the response ID as seed
                 * (so it's reproducible. But Suhosin doesn't allow seeding mt_rand.
                 */
                $seed = array_values(unpack('L',
                    substr(md5($this->responseId . count($randomizationGroups[$question]), true), -4, 4)))[0];

                $randomIndex = $seed % count($randomizationGroups[$question]);

                $order[$i] = $randomizationGroups[$question][$randomIndex];
                $ids[] = $order[$i]->gid;
                unset($randomizationGroups[$question][$randomIndex]);
                $randomizationGroups[$question] = array_values($randomizationGroups[$question]);
            }
        }
        return $order;
    }

    /**
     * This function will be deprecated, for now it is provided as a replacement of direct session access.
     */
    public function getFieldArray() {
        $result = [];
        foreach (createFieldMap($this->surveyId, 'full', true, false, $this->language) as $field)
        {
            if (isset($field['qid']) && $field['qid']!='')
            {
//                $result['fieldnamesInfo'][$field['fieldname']] = $field['sid'].'X'.$field['gid'].'X'.$field['qid'];
//                $result['insertarray'][] = $field['fieldname'];
                //fieldarray ARRAY CONTENTS -
                //            [0]=questions.qid,
                //            [1]=fieldname,
                //            [2]=questions.title,
                //            [3]=questions.question
                //                     [4]=questions.type,
                //            [5]=questions.gid,
                //            [6]=questions.mandatory,
                //            [7]=conditionsexist,
                //            [8]=usedinconditions
                //            [8]=usedinconditions
                //            [9]=used in group.php for question count
                //            [10]=new group id for question in randomization group (GroupbyGroup Mode)

                //JUST IN CASE : PRECAUTION!
                //following variables are set only if $style=="full" in createFieldMap() in common_helper.
                //so, if $style = "short", set some default values here!
                if (isset($field['title']))
                    $title = $field['title'];
                else
                    $title = "";

                if (isset($field['question']))
                    $question = $field['question'];
                else
                    $question = "";

                if (isset($field['mandatory']))
                    $mandatory = $field['mandatory'];
                else
                    $mandatory = 'N';

                if (isset($field['hasconditions']))
                    $hasconditions = $field['hasconditions'];
                else
                    $hasconditions = 'N';

                if (isset($field['usedinconditions']))
                    $usedinconditions = $field['usedinconditions'];
                else
                    $usedinconditions = 'N';
                $result[$field['sid'].'X'.$field['gid'].'X'.$field['qid']]=array($field['qid'],
                $field['sid'].'X'.$field['gid'].'X'.$field['qid'],
                $title,
                $question,
                $field['type'],
                $field['gid'],
                $mandatory,
                $hasconditions,
                $usedinconditions);
                if (isset($field['random_gid']))
                {
                    $result[$field['sid'].'X'.$field['gid'].'X'.$field['qid']][10] = $field['random_gid'];
                }
            }

        }

        return $result;
    }

    public function getPostKey() {
        return $this->postKey;
    }
    public function setPostKey($value) {
        $this->postKey = $value;
    }


}