<?php
namespace LimeSurvey\PluginManager;

    abstract class QuestionBase implements iQuestion
    {
        /**
         * @var LimesurveyApi
         */
        protected $api;
        
        /**
         * Array containing meta data for supported question attributes.
         * @var array
         */
        
        protected $attributes;
        
        /**
         * Array containing an array for each column.
         * The supported keys for column meta data are:
         * - type
         * - name
         * - description
         * 
         * @var array
         */
        protected $columns;
        
        /**
         * Array containing attributes that all question types share.
         * @var array
         */
        private $defaultAttributes;
        
        /**
         * Array containing default attributes that are merged into the attribute
         * arrays.
         * @var array
         */
        protected $defaultAttributeProperties = array(
            'localized' => false,
            'advanced' => false
        );
        public static $info = array();
        
        /**
         *
         * @var iPlugin
         */
        protected $plugin;
        
        /**
         *
         * @var int The question id for this question object instance.
         */
        protected $questionId = null;
        
        /**
         * @var int The response id for this question object instance.
         */
        protected $responseId = null;
        /**
         * Contains the subquestion objects for this question.
         * @var iQuestion[]
         */
        protected $subQuestions;
        
        /**
         * The signature array is used for deriving a unique identifier for
         * a question type.
         * After initial release the contents of this array may NEVER be changed.
         * Changing the contents of the array will identify the question object
         * as a new question type and will break many if not all existing surveys.
         * 
         * 
         * - Add more keys & values to make it more unique.
         * @var array
         */
        protected static $signature = array();
        
        /**
         * @param iPlugin $plugin The plugin to which this question belongs.
         * @param int $questionId
         * @param int $responseId Pass a response id to load results.
         */
        
        public function __construct(iPlugin $plugin, LimesurveyApi $api, $questionId = null, $responseId = null)
        {
            $this->plugin = $plugin;
            $this->api = $api;
            $this->responseId = $responseId;
            $this->questionId = $questionId;
            if (isset($questionId)) {
                $this->loadSubQuestions($questionId);
            }
            $this->defaultAttributes = array(
                'questiontype' => array(
                    'type' => 'select',
                    'localized' => false,
                    'advanced' => false,
                    'label' => gT('Question type:'),
                    'options' => CHtml::listData(App()->getPluginManager()->loadQuestionObjects(), 'guid', 'name')
                ),
                'code' => array(
                    'type' => 'string',
                    'localized' => false,
                    'advanced' => false,
                    'label' => gT('Question code:')
                ),
                'gid' => array(
                    'type' => 'select',
                    'localized' => false,
                    'advanced' => false,
                    'label' => gT('Question group:'),
                    'options' => function($that) {
                        return $that->api->getGroupList($that->get('sid'));
                    }
                ),
                'relevance' => array(
                    'type' => 'relevance',
                    'localized' => false,
                    'advanced' => false,
                    'label' => gT('Relevance equation:')
                ),
                'randomization' => array(
                    'type' => 'string',
                    'localized' => false,
                    'advanced' => false,
                    'label' => gT("Randomization group:")
                )
            );
        }
        
        /**
         * This function retrieves question data. Do not cache this data; the plugin storage
         * engine will handling caching. After the first call to this function, subsequent 
         * calls will only consist of a few function calls and array lookups. 
         * 
         * @param string $key String identifier for data.
         * @param mixed $default Default value.
         * @param string $language Language to retrieve.
         * @param int $questionId By default uses the question id for the current instance. Override this to read from another question.
         * @return boolean
         */
        protected function get($key = null, $default = null, $language = null, $questionId = null)
        {
            if (!isset($questionId) && isset($this->questionId)) {
                $questionId = $this->questionId;
                return $this->plugin->getStore()->get($this->plugin, $key, 'Question', $questionId, $default, $language);
            } else {
                return false;
            }
        }
        
        /**
         * Gets the meta data for question attributes.
         * Optionally pass one or more languages to also get current values.
         * Pass * to get all stored languages.
         * @param type $languages
         * @return type
         */
        public function getAttributes($languages = null)
        {
            $allAttributes = array_merge($this->defaultAttributes, $this->attributes);
            if (count($allAttributes) != count($this->defaultAttributes) + count($this->attributes)) {
                throw new Exception(get_class($this)." must not redefine default attributes");
            }
            
            foreach ($allAttributes as $name => &$metaData) {
                $metaData = array_merge($this->defaultAttributeProperties, $metaData);
                if (isset($this->questionId)) {
                    if (is_array($languages)) {
                        foreach ($languages as $language) {
                            $metaData['current'][$language] = $this->get($name, null, $language);
                        }
                    } else {
                        $metaData['current'] = $this->get($name, null, $languages);
                    }
                    
                    // Populate select fields with a list.
                    if ($metaData['type'] == 'select' && is_callable($metaData['options'])) {
                        $metaData['options'] = call_user_func($metaData['options'], $this);
                    }
                }
            }
            return $allAttributes;
        }
        
        public function getColumns()
        {
            return $this->columns;
        }
        
        
        public function getCount()
        {
            return 1;
        }
        /**
         * This function derives a unique identifier for identifying a question type.
         */
        public static function getGUID()
        {
            // We use json_encode because it is faster than serialize.
            return md5(json_encode(static::$signature));
        }
        
        /**
         * Gets the response for the current response id.
         * @return type
         */
        public function getResponse()
        {
            if (isset($this->responseId)) {
                $surveyId = Question::model()->findFieldByPk($this->questionId, 'sid');
                $response = SurveyDynamic::model($surveyId)->findByPk($this->responseId);
                $columns = $this->getColumns();
                foreach ($columns as &$column) {
                    if (isset($response->$column)) {
                        $column['response'] = $response->$column;
                    }
                }
                return $columns;
            }
        }
        
        public function getVariables()
        {
            if (isset($this->questionId)) {
                return array(
                    $this->get('code') => array(
                        'id' => $this->questionId,
                        'relevance' => $this->get('relevance')
                    )
                );
            }
            return array();
        }
        /**
         * Load the question data from the questions model.
         * @param integer $questionId
         */
        public function loadSubQuestions($questionId)
        {
            $subQuestions = Question::model()->findAllByAttributes(array(
                'parent_id' => $questionId
            ));
            foreach ($subQuestions as $subQuestion) {
                /**
                 * @todo Alter this so that subquestion can be of another type.
                 */
                $this->subQuestions[] = new self($subQuestion->qid, $this->responseId);
            }
        }
        
        public function saveAttributes(array $attributeValues, $qid = null)
        {
            $attributes = $this->getAttributes();
            $result = true;
            foreach ($attributeValues as $key => $value) {
                // Check if the attribute is valid for the question.
                if (isset($attributes[$key])) {
                    // If the attribute is localized, save each language.
                    if ($attributes[$key]['localized']) {
                        foreach ($value as $language => $localizedValue) {
                            if (!$this->set($key, $localizedValue, $language, $qid)) {
                                $result = false;
                            }
                        }
                    } else {
                        if (!$this->set($key, $value, $qid)) {
                            $result = false;
                        }
                    }
                        
                    
                }
            }
            
            return $result;
        }
        
        /**
         * This function saves question data. 
         * @param string $key
         * @param string $language
         * @param mixed $value
         * @return boolean
         */
        protected function set($key, $value, $language = null, $questionId = null)
        {
            if (!isset($questionId) && isset($this->questionId)) {
                $questionId = $this->questionId;
                return $this->plugin->getStore()->set($this->plugin, $key, $value, 'Question', $questionId, $language);
            } else {
                return false;
            }
            
            
        }
                
        
        
    }
?>
