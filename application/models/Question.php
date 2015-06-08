<?php

    if (!defined('BASEPATH'))
        exit('No direct script access allowed');
    /*
    * LimeSurvey
    * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
    * All rights reserved.
    * License: GNU/GPL License v2 or later, see LICENSE.php
    * LimeSurvey is free software. This version may have been modified pursuant
    * to the GNU General Public License, and as distributed it includes or
    * is derivative of works licensed under the GNU General Public License or
    * other free or open source software licenses.
    * See COPYRIGHT.php for copyright notices and details.
    *
        * 	Files Purpose: lots of common functions
    */


/**
 * Class Question
 * @property-read Translation[] $translations Relation added by translatable behavior
 * @property-read bool $hasSubQuestions
 * @property-read bool $hasAnswers
 * @property-read Survey $survey
 */
    class Question extends LSActiveRecord
    {

        public $before;

        public function getHasSubQuestions()
        {
            return false;
        }

        public function getHasAnswers()
        {
            return false;
        }
        public function behaviors() {
            return [
                'json' => [
                    'class' => SamIT\Yii1\Behaviors\JsonBehavior::class
                ],
                'translatable' => [
                    'class' => SamIT\Yii1\Behaviors\TranslatableBehavior::class,
                    'translationModel' => Translation::class,
                    'model' => __CLASS__, // See TranslatableBehavior comments.
                    'attributes' => [
                        'question',
                        'help'
                    ],
                    'baseLanguage' => function(Question $question) { return $question->isNewRecord ? 'en' : $question->survey->language; }
                ]
            ];
        }

        /**
        * Returns the static model of Settings table
        *
        * @static
        * @access public
        * @param string $class
        * @return CActiveRecord
        */
        public static function model($class = __CLASS__)
        {
            return parent::model($class);
        }

        public function beforeSave() {
            if ($this->isNewRecord && empty($this->parent_qid)) {
                // Set order.
                if (empty($this->before)) {
                    $criteria = (new CDbCriteria())
                        ->addColumnCondition([
                            'sid' => $this->sid,
                            'parent_qid' => 0,
                        ]);
                    $criteria->order = 'question_order DESC';
                    $criteria->limit = 1;
                    if (null != $last = Question::model()->find($criteria)) {
                        $this->question_order = $last->question_order + 1;
                    } else {
                        $this->question_order = 0;
                    }
                } else {
                    // Get the break point.
                    $before = Question::model()->findByPk($this->before);
                    $this->question_order = $before->question_order;
                    $criteria = (new CDbCriteria())
                        ->addColumnCondition([
                            'sid' => $this->sid,
                            'parent_qid' => 0,
                        ])->addCondition("question_order >= " . $this->question_order);
                    Question::updateAll([
                        'question_order' => new CDbExpression('question_order + 1')
                    ], $criteria);
                }
            }
            return true;
        }

        public function __get($name)
        {
            if (substr($name, 0, 5) == 'bool_') {
                $result = parent::__get(substr($name, 5)) === 'Y';
            } elseif (substr($name, 0, 2) == 'a_') {
                $result = isset($this->questionAttributes[substr($name, 2)]) ? $this->questionAttributes[substr($name, 2)]->value : null;
            } else {
                $result = parent::__get($name);
            }
            return $result;
        }

        public function __set($name, $value)
        {
            if (substr($name, 0, 5) == 'bool_') {
                parent::__set(substr($name, 5), $value ? 'Y' : 'N');
            } elseif (substr($name, 0, 2) == 'a_') {
                throw new \Exception("Saving not yet supported");
                /**
                 * Several implementation options:
                 * 1. Save to database on set. (Bad because the record might not get saved.)
                 * 2. Save to memory, watch before/after Save and commit to db then. (Better but loses atomicity).
                 * 3. Save to memory, override save and use a transaction.
                 */

            } else {
                parent::__set($name, $value);
            }
        }
        /**
        * Returns the setting's table name to be used by the model
        *
        * @access public
        * @return string
        */
        public function tableName()
        {
            return '{{questions}}';
        }

              /**
        * Defines the relations for this model
        *
        * @access public
        * @return array
        */
        public function relations()
        {
			$alias = $this->getTableAlias();
            return array(
                'groups' => array(self::HAS_ONE, 'QuestionGroup', '', 'on' => "$alias.gid = groups.gid AND $alias.language = groups.language"),
                'parents' => array(self::HAS_ONE, 'Question', '', 'on' => "$alias.parent_qid = parents.qid"),
                'subQuestions' => array(self::HAS_MANY, 'Question', 'parent_qid'),
                'questionAttributes' => [self::HAS_MANY, QuestionAttribute::class, 'qid', 'index' => 'attribute'],
                'group' => [self::BELONGS_TO, 'QuestionGroup', 'gid'],
                'survey' => [self::BELONGS_TO, 'Survey', 'sid'],
            );
        }

        /**
        * Returns this model's validation rules
        *
        */
        public function rules()
        {
            $aRules= [
                /**
                 * @todo Add a validation for regular expression.
                 * Do this by trying to match it and catching an error,
                 * http://stackoverflow.com/questions/362793/regexp-that-matches-valid-regexps
                 */
                ['preg', 'safe'],
                ['before', 'numerical', 'on' => 'insert', 'integerOnly' => true],
                ['type', 'in', 'range' => array_keys($this->typeList())],
                ['gid', 'exist', 'className' => QuestionGroup::class, 'attributeName' => 'id', 'allowEmpty' => false],
                ['title', 'required', 'on' => ['update', 'insert']],
                ['title','length', 'min' => 1, 'max'=>20,'on' => ['update', 'insert']],
                ['title,question,help', 'LSYii_Validators'],
                ['other', 'in', 'range' => ['Y','N'], 'allowEmpty' => true],
                ['mandatory', 'in', 'range' => ['Y','N'], 'allowEmpty'=>true],
                ['question_order', 'numerical', 'integerOnly' => true, 'allowEmpty' => true],
                ['scale_id','numerical', 'integerOnly'=>true,'allowEmpty'=>true],
                ['same_default','numerical', 'integerOnly'=>true,'allowEmpty'=>true],
            ];

            $aRules[] = ['title', 'match', 'pattern' => '/^[a-z0-9]*$/i',
                'message' => gT('Subquestion codes may only contain alphanumeric characters.'),
                'on' => ['updatesub', 'insertsub']
            ];
            $aRules[] = ['title', 'unique', 'caseSensitive'=>true,
                'criteria'=>[
                    'condition' => 'sid=:sid AND parent_qid=:parent_qid and scale_id=:scale_id',
                    // Use a deferred value since $this->sid might be set after validators are created.
                    'params' => [
                        ':sid' => new DeferredValue(function() { return $this->sid; }),
                        ':parent_qid' => new DeferredValue(function() { return $this->parent_qid; }),
                        ':scale_id' => new DeferredValue(function() { return $this->scale_id; })
                    ]
                ],
                'message' => gT('Question codes must be unique.'),
                'except' => 'archiveimport'
            ];

            $aRules[] = ['title', 'match', 'pattern' => '/^[a-z][a-z0-9]*$/i',
                'message' => gT('Question codes must start with a letter and may only contain alphanumeric characters.'),
                'on' => ['update', 'insert']
            ];
            return $aRules;
        }

        /**
        * Rewrites sort order for questions in a group
        *
        * @static
        * @access public
        * @param int $gid
        * @param int $surveyid
        * @return void
        */
        public static function updateSortOrder($gid, $surveyid)
        {
            $questions = self::model()->findAllByAttributes(array('gid' => $gid, 'sid' => $surveyid, 'language' => Survey::model()->findByPk($surveyid)->language));
            $p = 0;
            foreach ($questions as $question)
            {
                $question->question_order = $p;
                $question->save();
                $p++;
            }
        }
        /**
        * Fixe sort order for questions in a group
        *
        * @static
        * @access public
        * @param int $gid
        * @param int $surveyid
        * @return void
        */
        function updateQuestionOrder($gid,$language,$position=0)
        {
            $data=Yii::app()->db->createCommand()->select('qid')
            ->where(array('and','gid=:gid','language=:language'))
            ->order('question_order, title ASC')
            ->from('{{questions}}')
            ->bindParam(':gid', $gid, PDO::PARAM_INT)
            ->bindParam(':language', $language, PDO::PARAM_STR)
            ->query();

            $position = intval($position);
            foreach($data->readAll() as $row)
            {
                Yii::app()->db->createCommand()->update($this->tableName(),array('question_order' => $position),'qid='.$row['qid']);
                $position++;
            }
        }

        /**
        * This function returns an array of the advanced attributes for the particular question
        * including their values set in the database
        *
        * @access public
        * @param int $iQuestionID  The question ID - if 0 then all settings will use the default value
        * @param string $sQuestionType  The question type
        * @param int $iSurveyID
        * @param string $sLanguage  If you give a language then only the attributes for that language are returned
        * @return array
        */
        public function getAdvancedSettingsWithValues($iQuestionID, $sQuestionType, $iSurveyID, $sLanguage=null)
        {
            if (is_null($sLanguage))
            {
                $aLanguages = array_merge(array(Survey::model()->findByPk($iSurveyID)->language), Survey::model()->findByPk($iSurveyID)->additionalLanguages);
            }
            else
            {
                $aLanguages = array($sLanguage);
            }

            if ($iQuestionID)
            {
                $oAttributeValues = QuestionAttribute::model()->findAll("qid=:qid",array('qid'=>$iQuestionID));
                $aAttributeValues=array();
                foreach($oAttributeValues as $oAttributeValue)
                {
                    if($oAttributeValue->language){
                        $aAttributeValues[$oAttributeValue->attribute][$oAttributeValue->language]=$oAttributeValue->value;
                    }else{
                        $aAttributeValues[$oAttributeValue->attribute]=$oAttributeValue->value;
                    }
                }
            }
            $aAttributeNames = questionAttributes();

            $aAttributeNames = $aAttributeNames[$sQuestionType];
            uasort($aAttributeNames, 'categorySort');
            foreach ($aAttributeNames as $iKey => $aAttribute)
            {
                if ($aAttribute['i18n'] == false)
                {
                    if (isset($aAttributeValues[$aAttribute['name']]))
                    {
                        $aAttributeNames[$iKey]['value'] = $aAttributeValues[$aAttribute['name']];
                    }
                    else
                    {
                        $aAttributeNames[$iKey]['value'] = $aAttribute['default'];
                    }
                }
                else
                {
                    foreach ($aLanguages as $sLanguage)
                    {
                        if (isset($aAttributeValues[$aAttribute['name']][$sLanguage]))
                        {
                            $aAttributeNames[$iKey][$sLanguage]['value'] = $aAttributeValues[$aAttribute['name']][$sLanguage];
                        }
                        else
                        {
                            $aAttributeNames[$iKey][$sLanguage]['value'] = $aAttribute['default'];
                        }
                    }
                }
            }
            return $aAttributeNames;
        }


        /**
        * Insert an array into the questions table
        * Returns false if insertion fails, otherwise the new QID
        *
        * @param array $data
        */
        function insertRecords($data)
        {
            // This function must be deprecated : don't find a way to have getErrors after (Shnoulle on 131206)
            $questions = new self;
            foreach ($data as $k => $v){
                $questions->$k = $v;
                }
            try
            {
                $questions->save();
                return $questions->qid;
            }
            catch(Exception $e)
            {
                return false;
            }
        }

        public static function deleteAllById($questionsIds)
        {
            if ( !is_array($questionsIds) )
            {
                $questionsIds = array($questionsIds);
            }

            Yii::app()->db->createCommand()->delete(Condition::model()->tableName(), array('in', 'qid', $questionsIds));
            Yii::app()->db->createCommand()->delete(QuestionAttribute::model()->tableName(), array('in', 'qid', $questionsIds));
            Yii::app()->db->createCommand()->delete(Answer::model()->tableName(), array('in', 'qid', $questionsIds));
            Yii::app()->db->createCommand()->delete(Question::model()->tableName(), array('in', 'parent_qid', $questionsIds));
            Yii::app()->db->createCommand()->delete(Question::model()->tableName(), array('in', 'qid', $questionsIds));
            Yii::app()->db->createCommand()->delete(DefaultValue::model()->tableName(), array('in', 'qid', $questionsIds));
            Yii::app()->db->createCommand()->delete(QuotaMember::model()->tableName(), array('in', 'qid', $questionsIds));
        }

        function getAllRecords($condition, $order=FALSE)
        {
            $command=Yii::app()->db->createCommand()->select('*')->from($this->tableName())->where($condition);
            if ($order != FALSE)
            {
                $command->order($order);
            }
            return $command->query();
        }

        public function getQuestionsForStatistics($fields, $condition, $orderby=FALSE)
        {
            $command = Yii::app()->db->createCommand()
            ->select($fields)
            ->from(self::tableName())
            ->where($condition);
            if ($orderby != FALSE)
            {
                $command->order($orderby);
            }
            return $command->queryAll();
        }

        public function getQuestionList($surveyid, $language)
        {
            $query = "SELECT questions.*, groups.group_name, groups.group_order"
            ." FROM {{questions}} as questions, {{groups}} as groups"
            ." WHERE groups.gid=questions.gid"
            ." AND groups.language=:language1"
            ." AND questions.language=:language2"
            ." AND questions.parent_qid=0"
            ." AND questions.sid=:sid";
            return Yii::app()->db->createCommand($query)->bindParam(":language1", $language, PDO::PARAM_STR)
                                                        ->bindParam(":language2", $language, PDO::PARAM_STR)
                                                        ->bindParam(":sid", $surveyid, PDO::PARAM_INT)->queryAll();
        }


        /**
         * Returns the type of answers for this question:
         * 0: No answers (ie open text question),
         * 1: Answers on 1 scale.
         * 2: Answers on 2 scales. (Used only by array(dual scale) question type)
         * @return integer
         * @throws CException
         */

        public function getHasAnswerScales()
        {
            $types = self::typeList();
            if (isset($types[$this->type]))
            {
                return $types[$this->type]['answerscales'] == 1;
            }
            throw new CException("Unknown question type: '{$this->type}'");
        }

        /**
         * This function contains the question type definitions.
         * @return array The question type definitions
         *
         * Explanation of questiontype array:
         *
         * description : Question description
         * subquestions : 0= Does not support subquestions x=Number of subquestion scales
         * answerscales : 0= Does not need answers x=Number of answer scales (usually 1, but e.g. for dual scale question set to 2)
         * assessable : 0=Does not support assessment values when editing answerd 1=Support assessment values

         */
        public static function typeList()
        {
            $questionTypes = array(
                "1" => array(
                    'description' => gT("Array dual scale"),
                    'group' => gT('Arrays'),
                    'subquestions' => 1,
                    'assessable' => 1,
                    'hasdefaultvalues' => 0,
                    'answerscales' => 2),
                "5" => array(
                    'description' => gT("5 Point Choice"),
                    'group' => gT("Single choice questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 0,
                    'assessable' => 0,
                    'answerscales' => 0),
                "A" => array(
                    'description' => gT("Array (5 Point Choice)"),
                    'group' => gT('Arrays'),
                    'subquestions' => 1,
                    'hasdefaultvalues' => 0,
                    'assessable' => 1,
                    'answerscales' => 0),
                "B" => array(
                    'description' => gT("Array (10 Point Choice)"),
                    'group' => gT('Arrays'),
                    'subquestions' => 1,
                    'hasdefaultvalues' => 0,
                    'assessable' => 1,
                    'answerscales' => 0),
                "C" => array(
                    'description' => gT("Array (Yes/No/Uncertain)"),
                    'group' => gT('Arrays'),
                    'subquestions' => 1,
                    'hasdefaultvalues' => 0,
                    'assessable' => 1,
                    'answerscales' => 0),
                "D" => array(
                    'description' => gT("Date/Time"),
                    'group' => gT("Mask questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 1,
                    'assessable' => 0,
                    'answerscales' => 0),
                "E" => array(
                    'description' => gT("Array (Increase/Same/Decrease)"),
                    'group' => gT('Arrays'),
                    'subquestions' => 1,
                    'hasdefaultvalues' => 0,
                    'assessable' => 1,
                    'answerscales' => 0),
                "F" => array(
                    'description' => gT("Array"),
                    'group' => gT('Arrays'),
                    'subquestions' => 1,
                    'hasdefaultvalues' => 0,
                    'assessable' => 1,
                    'answerscales' => 1),
                "G" => array(
                    'description' => gT("Gender"),
                    'group' => gT("Mask questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 0,
                    'assessable' => 0,
                    'answerscales' => 0),
                "H" => array(
                    'description' => gT("Array by column"),
                    'group' => gT('Arrays'),
                    'hasdefaultvalues' => 0,
                    'subquestions' => 1,
                    'assessable' => 1,
                    'answerscales' => 1),
                "I" => array(
                    'description' => gT("Language Switch"),
                    'group' => gT("Mask questions"),
                    'hasdefaultvalues' => 0,
                    'subquestions' => 0,
                    'assessable' => 0,
                    'answerscales' => 0),
                "K" => array(
                    'description' => gT("Multiple Numerical Input"),
                    'group' => gT("Mask questions"),
                    'hasdefaultvalues' => 1,
                    'subquestions' => 1,
                    'assessable' => 1,
                    'answerscales' => 0),
                "L" => array(
                    'description' => gT("List (Radio)"),
                    'group' => gT("Single choice questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 1,
                    'assessable' => 1,
                    'answerscales' => 1),
                "M" => array(
                    'description' => gT("Multiple choice"),
                    'group' => gT("Multiple choice questions"),
                    'subquestions' => 1,
                    'hasdefaultvalues' => 1,
                    'assessable' => 1,
                    'answerscales' => 0),
                "N" => array(
                    'description' => gT("Numerical Input"),
                    'group' => gT("Mask questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 1,
                    'assessable' => 0,
                    'answerscales' => 0),
                "O" => array(
                    'description' => gT("List with comment"),
                    'group' => gT("Single choice questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 1,
                    'assessable' => 1,
                    'answerscales' => 1),
                "P" => array(
                    'description' => gT("Multiple choice with comments"),
                    'group' => gT("Multiple choice questions"),
                    'subquestions' => 1,
                    'hasdefaultvalues' => 1,
                    'assessable' => 1,
                    'answerscales' => 0),
                "Q" => array(
                    'description' => gT("Multiple Short Text"),
                    'group' => gT("Text questions"),
                    'subquestions' => 1,
                    'hasdefaultvalues' => 1,
                    'assessable' => 0,
                    'answerscales' => 0),
                "R" => array(
                    'description' => gT("Ranking"),
                    'group' => gT("Mask questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 0,
                    'assessable' => 1,
                    'answerscales' => 1),
                "S" => array(
                    'description' => gT("Short Free Text"),
                    'group' => gT("Text questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 1,
                    'assessable' => 0,
                    'answerscales' => 0),
                "T" => array(
                    'description' => gT("Long Free Text"),
                    'group' => gT("Text questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 1,
                    'assessable' => 0,
                    'answerscales' => 0),
                "U" => array(
                    'description' => gT("Huge Free Text"),
                    'group' => gT("Text questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 1,
                    'assessable' => 0,
                    'answerscales' => 0),
                "X" => array(
                    'description' => gT("Text display"),
                    'group' => gT("Mask questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 0,
                    'assessable' => 0,
                    'answerscales' => 0),
                "Y" => array(
                    'description' => gT("Yes/No"),
                    'group' => gT("Mask questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 1,
                    'assessable' => 0,
                    'answerscales' => 0),
                "!" => array(
                    'description' => gT("List (Dropdown)"),
                    'group' => gT("Single choice questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 1,
                    'assessable' => 1,
                    'answerscales' => 1),
                ":" => array(
                    'description' => gT("Array (Numbers)"),
                    'group' => gT('Arrays'),
                    'subquestions' => 2,
                    'hasdefaultvalues' => 0,
                    'assessable' => 1,
                    'answerscales' => 0),
                ";" => array(
                    'description' => gT("Array (Texts)"),
                    'group' => gT('Arrays'),
                    'subquestions' => 2,
                    'hasdefaultvalues' => 0,
                    'assessable' => 0,
                    'answerscales' => 0),
                "|" => array(
                    'description' => gT("File upload"),
                    'group' => gT("Mask questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 0,
                    'assessable' => 0,
                    'answerscales' => 0),
                "*" => array(
                    'description' => gT("Equation"),
                    'group' => gT("Mask questions"),
                    'subquestions' => 0,
                    'hasdefaultvalues' => 0,
                    'assessable' => 0,
                    'answerscales' => 0),
            );
            // Makes it easier to work with in CHtml::listData
            foreach($questionTypes as $type => &$details) {
                $details['type'] = $type;
            }
            /**
             * @todo Check if this actually does anything, since the values are arrays.
             */
            asort($questionTypes);
            
            return $questionTypes;
        }
        
        public function scopes() {
            return [
                'primary' => [
                    'condition' => 'parent_qid = 0'
                ]
            ];
        }
        
        public function getSgqa() {
            return "{$this->sid}X{$this->gid}X{$this->qid}";
        }
        
        public function getColumns() {
            if (!empty($this->parent_qid)) {
                return [
                    $this->title => 'string(5)'
                ];
            };
            switch ($this->type) {
                case "N":  //Numerical
                case "K":  //Multiple Numerical
                    $result = [$this->sgqa => "decimal (30,10)"];
                    break;
                case "S":  //SHORT TEXT
                case "*":  //Equation
                    $result = [$this->sgqa => "text"];
                    break;
                case "L":  //LIST (RADIO)
                case "!":  //LIST (DROPDOWN)
                    $result = [$this->sgqa => "string(5)"];
                    break;
                case "O":  //DROPDOWN LIST WITH COMMENT
                    $result = [$this->sgqa => "string(5)", "{$this->sgqa}comment" => "text"];
                    break;
                case "F": // Array
                case "R": // Ranking
                case "M": //Multiple choice
                case "Q": //Multiple short text
                    if (count($this->subQuestions) > 0) {
                        $result = call_user_func_array('array_merge', array_map(function (self $subQuestion) {
                            $subResult = [];
                            foreach ($subQuestion->columns as $name => $type) {
                                $subResult[$this->sgqa . $name] = $type;
                            }

                            return $subResult;
                        }, $this->subQuestions));
                    } else {
                        $result = [];
                    }
                    break;
                case "P":  //Multiple choice with comment
                    $result = call_user_func_array('array_merge', array_map(function(self $subQuestion) {
                        $subResult = [];
                        foreach ($subQuestion->columns as $name => $type) {
                            $subResult[$this->sgqa . $name] = $type;
                            $subResult[$this->sgqa . $name . 'comment'] = 'text';
                        }
                        return $subResult;
                    }, $this->subQuestions));
                    break;
                case "U":  //Huge text
                case "T":  //LONG TEXT
                case ";":  //Multi Flexi
                case ":":  //Multi Flexi
                    $result = [$this->sgqa => "text"];
                    break;
                case "D":  //DATE
                    $result = [$this->sgqa => "datetime"];
                    break;
                case "5":  //5 Point Choice
                case "G":  //Gender
                case "Y":  //YesNo
                case "X":  //Boilerplate
                    $result = [$this->sgqa => "string(1)"];
                    break;
                case "I":  //Language switch
                    $result = [$this->sgqa => "string(20)"];
                    break;
                case "|":
                    $result = [
                        $this->sgqa => "text",
                        "{$this->sgqa}_filecount" => "int"
                    ];
                    
                    break;
                default:
                    throw new \Exception("Don't know columns for question type: {$this->type}");
                    
            }
            
            return $result;
        }


        /**
         * This allows us to put question type specific code in a separate class.
         *
         * @param $attributes
         * @return mixed
         */
        protected function instantiate($attributes) {
            if (!isset($attributes['type'])) {
                throw new \Exception('noo');
            }
            if (!empty($attributes['parent_qid'])) {
                $class = \ls\models\questions\SubQuestion::class;
            } else {
                switch ($attributes['type']) {
                    case 'N':
                        $class = \ls\models\questions\NumericalQuestion::class;
                        break;
                    case 'U': // Huge free text
                    case 'S': // Short free text
                    case 'T':
                        $class = \ls\models\questions\TextQuestion::class;
                        break;
                    case 'O': // Single choice with comments.
                    case '!': // Single choice dropdown.
                    case 'L': // Single choice (Radio);
                        $class = \ls\models\questions\SingleChoiceQuestion::class;
                        break;
                    case 'Q': // Multiple (short) text.
                        $class = \ls\models\questions\MultipleTextQuestion::class;
                        break;
                    case 'R': // Ranking
                        $class = \ls\models\questions\RankingQuestion::class;
                        break;
                    case 'F': // Array
                        $class = \ls\models\questions\ArrayQuestion::class;
                        break;
                    case '5': // 5 point choice
                    case '|':
                        $class = get_class($this);
                        break;
                    default:
                        die("noo class for type {$attributes['type']}");

                }
            }
            return new $class(null);
        }

        public function getTypeName() {
            return $this->typeList()[$this->type]['description'];
        }

        public function getDisplayLabel() {
            return strip_tags("{$this->title} - {$this->question}");
        }

        public function attributeLabels()
        {
            return [
                'after' => gT('Position')
            ];
        }


    }



?>
