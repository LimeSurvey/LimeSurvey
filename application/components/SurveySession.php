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
 * @property int $step;
 * @property mixed $format;
 * @property int $maxStep;
 * @property Response $response;
 * @property string $templateDir;
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

    protected $_responseId;
    protected $_language = 'en';
    protected $_prevStep = 0;
    protected $_step = 0;
    protected $_maxStep = 0;
    protected $_templateDir;
    protected $_postKey;
    protected $_finished = false;
    /**
     * This is used to decide if we should display errors.
     * It gets reset when changing the step.
     * @var int The number of times this step has been viewed without viewing other steps.
     */
    protected $_viewCount = 0;
    protected $_token;
    /**
     * @param int $surveyId
     * @param int $responseId
     * @param integer $id
     */
    public function __construct($surveyId, $responseId, $id)
    {
        $this->surveyId = $surveyId;
        $this->_responseId = $responseId;
        $this->id = $id;
        // Need isset since the token property does not exist for surveys without token.
        $this->_token = isset($this->response->token) ? $this->response->token : null;
    }

    public function getSurveyId() {
        return $this->surveyId;
    }

    /**
     * @return integer
     */
    public function getResponseId() {
        return $this->_responseId;
    }

    public function getIsFinished() {
        return $this->_finished;
    }

    public function setIsFinished($value) {
        $this->_finished = $value;
    }

    public function getToken() {
        return $this->_token;
    }

    public function setToken($value) {
        $this->_token = $value;
    }
    public function getLanguage() {
        return $this->_language;
    }

    public function setLanguage($value) {
        $this->_language = $value;
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
            $this->_response = Response::model($this->surveyId)->findByPk($this->_responseId);
        }
        return $this->_response;
    }

    /**
     * This function loads a survey and makes sure all related AR objects have their relations filled.
     * @todo Improve performance. Query takes a short time but AR object creation takes ~4s for large surveys.
     * @param $id
     */
    protected function loadSurvey($id) {
        bP();
        $cache = App()->cache;
        $cache->hashKey = false;
        $cacheKey = __CLASS__ . "loadSurvey{$this->id}-$id";
        bP('unserialize');
        $result = $cache->get($cacheKey);
        eP('unserialize');
        if (false === $result) {
            bP('computing');
            /** @var Survey $survey */
            $survey = Survey::model()->with([
                'groups' => [
                    'with' => [
                        'questions' => [
                            'with' => [
                                'answers',
//                                'questionAttributes',
//                                'conditions',
//                                'conditionsAsTarget'
                            ]
                        ]
                    ]
                ]
            ])->findByPk($id);
            if (!isset($survey)) {
                throw new \Exception("Survey not found.");
            }
            /**
             * We manually set the questions in survey to the same objects as those in groups.
             * Note that the $survey->questions relation is redundant, but since we have defined it we will also fill it.
             */
            $survey->groupCount = count($survey->groups);
            $questions = [];
            foreach ($survey->groups as $group) {
                foreach ($group->questions as $key => $question) {
                    $questions[$key] = $question;

                    // Also manually fill the group relation, so $group->questions[0]->group === $group
                    $question->group = $group;
                    // And the survey relation
                    $question->survey = $survey;
                }
            }
            $survey->questions = $questions;
            if (!$cache->set($cacheKey, $survey)) {
                throw new \Exception("Failed to cache survey");
            }
            // We do a get to make sure the object gets waken up properly.
            // @todo Implement serializable instead.
            // This is not too bad since it only happens once per session.
            $result = $cache->get($cacheKey);
            eP('computing');
        }
        eP();
        if (!$result instanceof Survey) {
            throw new \Exception("Something went wrong in loadSurvey.");
        }
        vdd($result);
        return $result;


    }
    /**
     * This function gets the survey active record model for this survey session.
     * @return Survey The survey for this session.
     */
    public function getSurvey() {
        if (!isset($this->_survey)) {
            $this->_survey = $this->loadSurvey($this->surveyId);
        }
        return $this->_survey;
    }

    /**
     * Wrapper function that returns the question given by qid to make sure we always get the same object.
     * @param int $id The primary key of the question.
     * @return Question
     */
    public function getQuestion($id) {
        bP();
        $result = isset($this->survey->questions[$id]) ? $this->survey->questions[$id] : null;
        eP();
        return $result;
    }

    public function getQuestionIndex($id) {
        \Yii::beginProfile(__CLASS__ . "::" . __FUNCTION__);
        $questions = $this->survey->questions;
        $question = $questions[$id];
        $result = array_search($question, array_values($questions), true);
        \Yii::endProfile(__CLASS__ . "::" . __FUNCTION__);
        return $result;
    }

    /**
     * @param $index
     * @return Question
     */
    public function getQuestionByIndex($index) {
        $i = 0;
        // Get groups in order.
        foreach($this->getGroups() as $group) {
            foreach($this->getQuestions($group) as $question) {
                if ($index == $i) {
                    $result = $question;
                    break 2;
                }
                $i++;
            }
        }
        if (!isset($result)) {
            throw new \Exception("Invalid step index: $index");
        }
        return $result;
    }

    public function getStepCount() {
        switch ($this->format) {
            case Survey::FORMAT_ALL_IN_ONE:
                $result = 1;
                break;
            case Survey::FORMAT_GROUP:
                $result = count($this->getGroups());
                break;
            case Survey::FORMAT_QUESTION:
                $result = array_sum(array_map(function(QuestionGroup $group) {
                    return count($this->getQuestions($group));
                }, $this->getGroups()));
                break;
            default:
                throw new \Exception("Unknown survey format.");
        }
        return $result;
    }
    /**
     * @return int
     */
    public function getStep() {
        return $this->_step;
    }

    public function setStep($value) {
        if (!is_int($value)) {
            throw new \InvalidArgumentException('Parameter $value must be an integer.');
        }
        if ($value > $this->stepCount) {
            throw new \InvalidArgumentException("Cannot set step to a value greater than stepCount");
        }

        if ($value != $this->_step) {
            $this->_viewCount = 1;
            $this->_prevStep = $this->_step;
            $this->_step = $value;
            $this->_maxStep = max($this->_step, $this->_maxStep);
        }
    }


    public function getMaxStep() {
        return $this->_maxStep;
    }

    /**
     * @return int The number of times the current page has been viewed.
     */
    public function getViewCount() {
        return $this->_viewCount;
    }

    public function getPrevStep() {
        return $this->_prevStep;
    }

    public function setPrevStep($value) {
        $this->_prevStep = $value;
    }

    public function __wakeup() {
        $this->_viewCount++;
    }

    public function __sleep() {
        return [
            'surveyId',
            'id',
            '_step',
            '_maxStep',
            '_prevStep',
            '_responseId',
            '_finished',
            '_language',
            '_postKey',
            '_token',
            '_viewCount'
        ];
    }

    /**
     * Sets the template dir to use for this session.
     * @param string $value
     */
    public function setTemplateDir($value) {
        $this->_templateDir = $value;
    }

    public function getTemplateDir() {
        if (!isset($this->_templateDir)) {
            $this->_templateDir = \Template::getTemplatePath($this->survey->template) . '/';
        };
        return $this->_templateDir;
    }

    public function getTemplateUrl() {
        return \Template::getTemplateURL($this->survey->template) . '/';
    }

    /**
     * @param int $id The group id.
     * @return QuestionGroup
     */
    public function getGroup($id) {
        if (!is_numeric($id) || $id < 0) {
            throw new \InvalidArgumentException("\$id must of type integer and > 0");
        }

        return $this->survey->groups[$id];
    }

    /**
     * Get the group by index.
     * @return QuestionGroup
     */
    public function getGroupByIndex($index) {
        if (!is_numeric($index)) {
            throw new \InvalidArgumentException("\$index must of type integer");
        }
        return $this->getGroups()[$index];

    }

    public function getGroupIndex($id) {
        bP();
        $groups = $this->groups;
        $group = $this->getGroup($id);
        $result = array_search($group, array_values($groups), true);
        eP();
        return $result;
    }
    /**
     * Returns the list of question groups.
     * Ordered according to the randomization groups.
     * @return QuestionGroup[]
     */
    public function getGroups()
    {
        bP();
        $groups = $this->survey->groups;

        // Get all randomization groups in order.
        $order = [];
        $randomizationGroups = [];
        foreach ($groups as $group) {
            if (!empty($group->randomization_group)) {
                $order[] = $group->randomization_group;
                $randomizationGroups[$group->randomization_group][] = $group;
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
                 *
                 * Current approach:
                 * Create hash of response id and the number of groups left, take last 8 chars (== 4 bytes).
                 */
                $seed = array_values(unpack('L',
                    hex2bin(substr(md5($this->responseId . count($randomizationGroups[$group]), true), -8, 4))))[0];

                $randomIndex = $seed % count($randomizationGroups[$group]);

                $order[$i] = $randomizationGroups[$group][$randomIndex];
                $ids[] = $order[$i]->id;
                unset($randomizationGroups[$group][$randomIndex]);
                $randomizationGroups[$group] = array_values($randomizationGroups[$group]);
            }
        }
        eP();
        return $order;
    }

    /**
     * Getter for the format. In the future we could allow override per session.
     * @return FORMAT_QUESTION|FORMAT_GROUP|FORMAT_SURVEY;
     */
    public function getFormat() {
        return $this->survey->format;
    }



    /**
     * Returns the questions in group $group, indexed by primary key, ordered as they are shown in the survey.
     * @todo Implement randomization / check randomization code.
     * @param QuestionGroup $group
     * @return Question[]
     */
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
     * @deprecated
     */
    public function getFieldArray() {
        throw new \Exception();
        $result = [];
        $fieldMap = createFieldMap($this->surveyId, 'full', true, false, $this->language);
        if (!is_array($fieldMap)) {
            echo "Field map should be an array.";
            var_dump($fieldMap);
            die();
        }
        foreach ($fieldMap as $field)
        {
            if ($field instanceof QuestionResponseField)
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
                if (isset($field->name))
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
                $result[$field['sid'].'X'.$field['gid'].'X'.$field['qid']]= [
                    intval($field['qid']),
                    $field['sid'].'X'.$field['gid'].'X'.$field['qid'],
                    $title,
                    $question,
                    $field['type'],
                    intval($field['gid']),
                    $mandatory,
                    $hasconditions,
                    $usedinconditions
                ];
                if (isset($field['random_gid']))
                {
                    $result[$field['sid'].'X'.$field['gid'].'X'.$field['qid']][10] = $field['random_gid'];
                }
            }

        }
        return $result;
    }

    public function getPostKey() {
        if (!isset($this->_postKey)) {
            $this->_postKey = \Cake\Utility\Text::uuid();
        }
        return $this->_postKey;
    }

    public function setPostKey($value) {
        $this->_postKey = $value;
    }

    public function getCurrentGroup() {
        switch ($this->format) {
            case Survey::FORMAT_ALL_IN_ONE:
                throw new \UnexpectedValueException("An all in one survey does not have a current group.");
                break; // for consistency.
            case Survey::FORMAT_GROUP:
                $result = $this->getGroupByIndex($this->step);
                break;
            case Survey::FORMAT_QUESTION:
                $result = $this->getGroup($this->getQuestionByIndex($this->getStep())->gid);
                break;
        }
        return $result;
    }

}
