<?php

namespace ls\components;
use ls\interfaces\ResponseInterface;
use ls\models\Survey;
use ls\models\QuestionGroup;
/**
 * Class ls\components\SurveySession
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
 * @property \ls\interfaces\ResponseInterface $response;
 * @property string $templateDir;
 */
class SurveySession extends \CComponent
{
    /**
     * These variables are not serialized.
     */
    /**
     * @var \ls\interfaces\ResponseInterface
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
    protected $_responseClass;
    protected $_language = 'en';
    protected $_prevStep = 0;
    protected $_step = 0;
    protected $_maxStep = 0;
    protected $_templateDir;
    protected $_postKey;
    /**
     * This is used to decide if we should display errors.
     * It gets reset when changing the step.
     * @var int The number of times this step has been viewed without viewing other steps.
     */
    protected $_viewCount = 0;
    protected $_token;

    /**
     * @param int $surveyId
     * @param ResponseInterface $response
     * @param integer $id
     */
    public function __construct($surveyId, ResponseInterface $response, $id)
    {
        $this->surveyId = $surveyId;
        $this->_responseId = $response->getId();
        $this->_response = $response;
        $this->_responseClass = get_class($response);
        $this->id = $id;
    }

    public function getSurveyId()
    {
        return $this->surveyId;
    }

    /**
     * @return integer
     */
    public function getResponseId()
    {
        return $this->_responseId;
    }

    public function getIsFinished()
    {
        return $this->response->getIsFinished();
    }

    public function getToken()
    {
        return $this->_response->getToken();
    }

    public function getLanguage()
    {
        return $this->_language;
    }

    public function setLanguage($value)
    {
        $this->_language = $value;
    }

    /**
     * Returns the session id for this session.
     * The session id is unique per browser session and does not need to be unguessable.
     * In fact it is just an auto incremented number.
     */
    public function getId()
    {
        return $this->id;
    }


    public function getResponse()
    {

        if (!isset($this->_response)) {
            $responseClass = $this->_responseClass;
            $this->_response = $responseClass::loadById($this->_responseId);
        }

        return $this->_response;
    }


    /**
     * This function loads a survey and makes sure all related AR objects have their relations filled.
     * @todo Improve performance. Query takes a short time but AR object creation takes ~4s for large surveys.
     * @param $id
     */
    protected function loadSurvey($id)
    {
        bP();
        $cache = App()->cache;
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
                                'subQuestions',
                                'questionAttributes'
                            ]
                        ]
                    ]
                ],
                'languagesettings'
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
            // We do this since $survey is changed (no behaviors / events) due to the serialize.
            // @todo Implement Serializable interface so the AR objects are not altered and the line below is not needed.
            $result = unserialize(serialize($survey));
            eP('computing');
        }
        eP();
        if (!$result instanceof Survey) {
            throw new \Exception("Something went wrong in loadSurvey.");
        }

        return $result;
    }

    /**
     * This function gets the survey active record model for this survey session.
     * @return Survey The survey for this session.
     */
    public function getSurvey()
    {
        if (!isset($this->_survey)) {
            $this->_survey = $this->loadSurvey($this->surveyId);
        }

        return $this->_survey;
    }

    public function setSurvey(\ls\models\Survey $survey)
    {
        $this->_survey = $survey;
    }

    /**
     * Wrapper function that returns the question given by qid to make sure we always get the same object.
     * @param int $id The primary key of the question.
     * @return Question
     */
    public function getQuestion($id)
    {
        bP();
        $result = isset($this->survey->questions[$id]) ? $this->survey->questions[$id] : null;
        eP();

        return $result;
    }

    public function getQuestionIndex($id)
    {
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
    public function getQuestionByIndex($index)
    {
        $i = 0;
        // Get groups in order.
        foreach ($this->getGroups() as $group) {
            foreach ($this->getQuestions($group) as $question) {
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

    public function getQuestionByCode($code)
    {
        $i = 0;
        // Get groups in order.
        foreach ($this->getGroups() as $group) {
            foreach ($this->getQuestions($group) as $question) {
                if ($code == $question->title) {
                    $result = $question;
                    break 2;
                }
                $i++;
            }
        }
        if (!isset($result)) {
            throw new \Exception("Unknown code: $code");
        }

        return $result;
    }

    public function getStepCount()
    {
        switch ($this->format) {
            case Survey::FORMAT_ALL_IN_ONE:
                $result = 1;
                break;
            case Survey::FORMAT_GROUP:
                $result = count($this->getGroups());
                break;
            case Survey::FORMAT_QUESTION:
                $result = array_sum(array_map(function (QuestionGroup $group) {
                    return count($this->getQuestions($group));
                }, $this->getGroups()));
                break;
            default:
                throw new \Exception("Unknown survey format.");
        }

        return $result;
    }

    /**
     * The step for the survey, starts counting at 0.
     * @return int
     */
    public function getStep()
    {
        return $this->_step;
    }

    public function setStep($value)
    {
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


    public function getMaxStep()
    {
        return $this->_maxStep;
    }

    /**
     * @return int The number of times the current page has been viewed.
     */
    public function getViewCount()
    {
        return $this->_viewCount;
    }

    public function getPrevStep()
    {
        return $this->_prevStep;
    }

    public function setPrevStep($value)
    {
        $this->_prevStep = $value;
    }

    public function __wakeup()
    {

    }

    public function __sleep()
    {
        $this->_viewCount++;
        return [
            'surveyId',
            'id',
            '_step',
            '_maxStep',
            '_prevStep',
            '_responseId',
            '_responseClass',
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
    public function setTemplateDir($value)
    {
        if (!is_dir($value)) {
            throw new \InvalidArgumentException("Invalid directory given: $value");
        }
        $this->_templateDir = $value;
    }

    public function getTemplateDir()
    {
        if (!isset($this->_templateDir)) {
            $this->_templateDir = \ls\models\Template::getTemplatePath($this->survey->template) . '/';
        };

        return $this->_templateDir;
    }

    public function getTemplateUrl()
    {
        return \ls\models\Template::getTemplateURL($this->survey->template) . '/';
    }

    /**
     * @param int $id The group id.
     * @return QuestionGroup
     */
    public function getGroup($id)
    {
        if (!is_numeric($id) || $id < 0) {
            throw new \InvalidArgumentException("\$id must of type integer and > 0");
        }

        return $this->survey->groups[$id];
    }

    /**
     * Get the group by index.
     * @return QuestionGroup
     */
    public function getGroupByIndex($index)
    {
        if (!is_numeric($index)) {
            throw new \InvalidArgumentException("\$index must of type integer");
        }

        return $this->getGroups()[$index];

    }

    public function getGroupIndex($id)
    {
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
                unset($randomizationGroups[$group][$randomIndex]);
                $randomizationGroups[$group] = array_values($randomizationGroups[$group]);
            }
        }
        eP();

        return $order;
    }

    /**
     * Getter for the format. In the future we could allow override per session.
     * @return FORMAT_QUESTION|FORMAT_GROUP|FORMAT_SURVEY
     */
    public function getFormat()
    {
        return $this->survey->format;
    }


    /**
     * Returns the questions in group $group, indexed by primary key, ordered as they are shown in the survey.
     * @todo Implement randomization / check randomization code.
     * @param QuestionGroup $group
     * @return Question[]
     */
    public function getQuestions(QuestionGroup $group)
    {
        return $group->questions;

        $questions = $group->questions;

        // Get all randomization groups in order.
        $order = [];
        $randomizationGroups = [];
        foreach ($questions as $question) {
            if (empty($question->randomization_group)) {
                $order[] = $question->randomization_group;
                $randomizationGroups[$question->randomization_group][] = $question;
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

    public function getPostKey()
    {
        if (!isset($this->_postKey)) {
            $this->_postKey = \Cake\Utility\Text::uuid();
        }

        return $this->_postKey;
    }

    public function setPostKey($value)
    {
        $this->_postKey = $value;
    }

    public function getCurrentGroup()
    {
        switch ($this->format) {
            case Survey::FORMAT_ALL_IN_ONE:
                throw new \UnexpectedValueException("An all in one survey does not have a current group.");
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
