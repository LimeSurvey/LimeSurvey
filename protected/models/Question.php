<?php
namespace ls\models;

use ls\components\DeferredValue;
use ls\components\QuestionResponseField;
use ls\components\QuestionValidationResult;
use ls\components\ResponseField;
use ExpressionManager;
use \CDbCriteria;
use \TbHtml;


/**
 * Class ls\models\Question
 * @property-read Translation[] $translations Relation added by translatable behavior
 * @property-read bool $hasSubQuestions
 * @property-read bool $hasAnswers
 * @property string $type
 * @property QuestionGroup $group;
 * @property string $title
 * @property boolean $other
 * @property Survey $survey
 * @property-read string $sgqa
 * @property-read string $displayLabel
 * @property-read QuestionAttribute[] $questionAttributes
 */
class Question extends ActiveRecord implements \ls\interfaces\iRenderable
{
    /**
     * ls\models\Question type constants.
     */
    const TYPE_ARRAY_DUAL_SCALE = "1";
    const TYPE_FIVE_POINT_CHOICE = "5";
    const TYPE_ARRAY_FIVE_POINT = "A";
    const TYPE_ARRAY_TEN_POINT = "B";
    const TYPE_ARRAY_YES_NO_UNCERTAIN = "C";
    const TYPE_DATE_TIME = "D";
    const TYPE_ARRAY_INCREASE_SAME_DECREASE = "E";
    const TYPE_ARRAY = "F";
    const TYPE_GENDER = "G";
    const TYPE_ARRAY_BY_COLUMN = "H";
    const TYPE_LANGUAGE_SWITCH = "I";
    const TYPE_MULTIPLE_NUMERICAL_INPUT = "K";
    const TYPE_RADIO_LIST = "L";
    const TYPE_MULTIPLE_CHOICE = "M";
    const TYPE_NUMERICAL_INPUT = "N";
    const TYPE_LIST_WITH_COMMENT = "O";
    const TYPE_MULTIPLE_CHOICE_WITH_COMMENT = "P";
    const TYPE_MULTIPLE_SHORT_TEXT = "Q";
    const TYPE_RANKING = "R";
    const TYPE_SHORT_TEXT = "S";
    const TYPE_LONG_TEXT = "T";
    const TYPE_HUGE_TEXT = "U";
    const TYPE_DISPLAY = "X";
    const TYPE_YES_NO = "Y";
    const TYPE_DROPDOWN_LIST = "!";
    const TYPE_ARRAY_NUMBERS = ":";
    const TYPE_ARRAY_TEXTS = ";";
    const TYPE_UPLOAD = "|";
    const TYPE_EQUATION = "*";

    /**
     * @var ResponseField[]
     */
    protected $_fields = [];
    /**
     * @var ExpressionManager
     */
    protected $_expressionManager;

    protected $customAttributes;
    protected $customLocalizedAttributes = [];

    /**
     * Used only on insert for deriving the question_order.
     * @var int The question id of the previous question.
     */
    public $before;

    protected function loadCustomAttributes()
    {
        if (!isset($this->customAttributes)) {
            $this->customAttributes = [];
            // Fill the question attributes.
            foreach ($this->questionAttributes as $questionAttribute) {
                if (!isset($questionAttribute->language)) {
                    $this->customAttributes[$questionAttribute->attribute] = $questionAttribute->value;
                }
            }
        }
    }

    protected function getCustomAttribute($name)
    {
        $this->loadCustomAttributes();
        if (array_key_exists($name, $this->customAttributes)) {
            return $this->customAttributes[$name];
        }

        // Get default value.
        $config = questionAttributes(true)[$name];

        return isset($config['default']) ? $config['default'] : null;
    }

    protected function setCustomAttribute($name, $value)
    {
        $this->loadCustomAttributes();
        $this->customAttributes[$name] = $value;
    }

    protected function issetCustomAttribute($name)
    {
        $this->loadCustomAttributes();

        return isset($this->customAttributes[$name]);
    }

    protected function getCustomLocalizedAttribute($name)
    {
        if (array_key_exists($name, $this->customLocalizedAttributes)) {
            return $this->customLocalizedAttributes[$name];
        }

        // Fill the localized question attributes.
        foreach ($this->questionAttributes as $questionAttribute) {
            if ($questionAttribute->language == $this->language && $questionAttribute->attribute === $name) {
                $this->customLocalizedAttributes[$name] = $questionAttribute->value;

                return $questionAttribute->value;
            }
        }
    }

    /**
     * After saving the main attributes we save the attributes that are stored in the EAV table.
     * @todo Check if we should save question + attributes in a transaction.
     * @throws Exception
     */
    protected function afterSave()
    {
        parent::afterSave();
        $this->updateAttributes();

    }

    /**
     * Save the advanced question attributes.
     * @throws CDbException
     * @throws Exception
     */
    protected function updateAttributes()
    {
        // Save the question attributes that do not use i18n.
        if (!empty($this->customAttributes)) {
            $db = self::getDbConnection();
            if (!isset($db->currentTransaction)) {
                $transaction = $db->beginTransaction();
            }
            try {
                QuestionAttribute::model()->deleteAllByAttributes([
                    'qid' => $this->primaryKey,
                    'language' => null

                ]);
                $rows = [];
                foreach ($this->customAttributes as $key => $value) {
                    $rows[] = [
                        'qid' => $this->primaryKey,
                        'attribute' => $key,
                        'value' => $value,
                        'language' => null
                    ];
                }
                if (!empty($rows)) {
                    $db->commandBuilder->createMultipleInsertCommand(QuestionAttribute::model()->tableName(),
                        $rows)->execute();
                }
            } catch (\Exception $e) {
                if (isset($transaction)) {
                    $transaction->rollback();
                }
                throw $e;
            }
            if (isset($transaction)) {
                $transaction->commit();
            }
        }

    }

    /**
     * Returns the number of scales for subquestions.
     * @return int Range: {0, 1, 2}
     */
    public function getSubQuestionScales()
    {
        return 0;
    }

    /**
     * Returns the number of scales for answers.
     * @return int Range: {0, 1, 2}
     */
    public function getAnswerScales()
    {
        return 0;
    }

    /**
     * @return bool True if this question supports subquestions.
     */
    final public function getHasSubQuestions()
    {
        return $this->subQuestionScales > 0;
    }

    /**
     * @return bool True if this question supports subquestions.
     */
    final public function getHasAnswers()
    {
        return $this->answerScales > 0;
    }

    public function behaviors()
    {
        return [
            'json' => [
                'class' => \SamIT\Yii1\Behaviors\JsonBehavior::class
            ],
            'translatable' => [
                'class' => \SamIT\Yii1\Behaviors\TranslatableBehavior::class,
                'translationModel' => Translation::class,
                'model' => __CLASS__, // See TranslatableBehavior comments.
                'attributes' => [
                    'question',
                    'help'
                ],
                'baseLanguage' => function (Question $question) {
                    return $question->isNewRecord ? 'en' : $question->survey->language;
                }
            ]
        ];
    }

    /**
     * @return bool
     */
    public function beforeSave()
    {
        /**
         * We set the question order for new records.
         */
        if ($this->isNewRecord && empty($this->parent_qid) && empty($this->question_order)) {
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

    public function __sleep()
    {
        $result = array_flip(parent::__sleep());

        unset($result[chr(0) . '*' . chr(0) . '_fields']);
        unset($result[chr(0) . '*' . chr(0) . '_expressionManager']);

        return array_keys($result);
    }


    /**
     * @param string $name
     * @return bool|mixed|null|string
     */
    public function __get($name)
    {
        /**
         * Since LS uses Y / N instead of the more common 1 / 0 approach for storing booleans,
         * this enables us to use bool_XXXX to get a php boolean for attribute XXXX.
         * @todo Refactor the database to actually store tinyint(1) or similar data and then global replace all bool_ accesses.
         *
         */
        if (substr($name, 0, 5) == 'bool_') {
            // Use $this instead of parent so we can use bool_ for custom attributes.
            $result = $this->__get(substr($name, 5)) === 'Y';
        } elseif ($name != 'type' && in_array($name, $this->customAttributeNames())) {
            $result = $this->getCustomAttribute($name);
        } elseif ($name != 'type' && in_array($name, $this->customLocalizedAttributeNames())) {
            $result = $this->getCustomLocalizedAttribute($name);
        } else {
            $result = parent::__get($name);
        }

        return $result;
    }

    public function __isset($name)
    {
        if (in_array($name, $this->customAttributeNames())) {
            $result = $this->issetCustomAttribute($name);
        } else {
            $result = parent::__isset($name);
        }

        return $result;
    }


    public function __set($name, $value)
    {
        if (substr($name, 0, 5) == 'bool_') {
            $this->__set(substr($name, 5), $value ? 'Y' : 'N');
        } elseif (in_array($name, $this->customAttributeNames())) {
            $this->setCustomAttribute($name, $value);
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
            'parent' => array(self::BELONGS_TO, self::class, 'parent_qid'),
            'subQuestions' => array(self::HAS_MANY, self::class, 'parent_qid'),
            'questionAttributes' => [self::HAS_MANY, QuestionAttribute::class, 'qid', 'index' => 'attribute'],
            'group' => [self::BELONGS_TO, QuestionGroup::class, 'gid'],
            'survey' => [self::BELONGS_TO, Survey::class, 'sid'],
            // Conditions this question has.
            'conditions' => [self::HAS_MANY, Condition::class, 'qid'],
            // Conditions other questions have where this question is the target.
            'conditionsAsTarget' => [self::HAS_MANY, Condition::class, 'cqid'],
            'answers' => [self::HAS_MANY, Answer::class, 'question_id'],
            'defaultValues' => [self::HAS_MANY, DefaultValue::class, 'qid']
        );
    }

    /**
     * Returns this model's validation rules
     *
     */
    public function rules()
    {
        $aRules = [
            /**
             * @todo Add a validation for regular expression.
             * Do this by trying to match it and catching an error,
             * http://stackoverflow.com/questions/362793/regexp-that-matches-valid-regexps
             */
            ['preg', 'safe'],
            ['before', 'numerical', 'on' => 'insert', 'integerOnly' => true],
            ['type', 'in', 'range' => array_keys($this->typeList())],
            [
                'gid',
                'exist',
                'className' => QuestionGroup::class,
                'attributeName' => 'id',
                'allowEmpty' => false,
                'on' => ['insert', 'update']
            ],
            ['title', 'required', 'on' => ['update', 'insert']],
            ['title', 'length', 'min' => 1, 'max' => 20, 'on' => ['update', 'insert']],
            [['question', 'help'], 'length'],
            ['question_order', 'numerical', 'integerOnly' => true, 'allowEmpty' => true],
            ['scale_id', 'numerical', 'integerOnly' => true, 'allowEmpty' => true],
            ['same_default', 'numerical', 'integerOnly' => true, 'allowEmpty' => true],
            /** @todo Create EM validator that validates syntax only. */
            ['relevance', 'safe'],
            [['bool_mandatory', 'bool_other', 'bool_hidden'], 'boolean'],

        ];

        $aRules[] = [
            'title',
            \CUniqueValidator::class,
            'caseSensitive' => true,
            'criteria' => [
                'condition' => 'sid=:sid AND parent_qid=:parent_qid and scale_id=:scale_id',
                // Use a deferred value since $this->sid might be set after validators are created.
                'params' => [
                    ':sid' => new DeferredValue(function () {
                        return $this->sid;
                    }, 'sid'),
                    ':parent_qid' => new DeferredValue(function () {
                        return $this->parent_qid;
                    }, 'parent_qid'),
                    ':scale_id' => new DeferredValue(function () {
                        return $this->scale_id;
                    }, 'scale_id')
                ]
            ],
            'message' => gT('ls\models\Question codes must be unique.'),
            'except' => 'archiveimport'
        ];

        $aRules[] = [
            'title',
            'match',
            'pattern' => '/^[a-z][a-z0-9]*$/i',
            'message' => gT('ls\models\Question codes must start with a letter and may only contain alphanumeric characters.'),
            'on' => ['update', 'insert']
        ];

        // Custom attributes are safe for now.
        $aRules[] = [$this->customAttributeNames(), 'safe'];

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
        $questions = self::model()->findAllByAttributes(array(
            'gid' => $gid,
            'sid' => $surveyid,
            'language' => Survey::model()->findByPk($surveyid)->language
        ), array('order' => 'question_order'));
        $p = 0;
        foreach ($questions as $question) {
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
    function updateQuestionOrder($gid, $language, $position = 0)
    {
        $data = Yii::app()->db->createCommand()->select('qid')
            ->where(array('and', 'gid=:gid', 'language=:language', 'parent_qid=0'))
            ->order('question_order, title ASC')
            ->from('{{questions}}')
            ->bindParam(':gid', $gid, PDO::PARAM_INT)
            ->bindParam(':language', $language, PDO::PARAM_STR)
            ->query();

        $position = intval($position);
        foreach ($data->readAll() as $row) {
            Yii::app()->db->createCommand()->update($this->tableName(), array('question_order' => $position),
                'qid=' . $row['qid']);
            $position++;
        }
    }

    public function getQuestionsForStatistics($fields, $condition, $orderby = false)
    {
        $command = self::getDbConnection()->createCommand()
            ->select($fields)
            ->from(self::tableName())
            ->where($condition);
        if ($orderby != false) {
            $command->order($orderby);
        }

        return $command->queryAll();
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
        if (isset($types[$this->type])) {
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
     * description : ls\models\Question description
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
                'answerscales' => 2
            ),
            "5" => array(
                'description' => gT("5 Point Choice"),
                'group' => gT("Single choice questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0
            ),
            "A" => array(
                'description' => gT("Array (5 Point Choice)"),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0
            ),
            "B" => array(
                'description' => gT("Array (10 Point Choice)"),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0
            ),
            "C" => array(
                'description' => gT("Array (Yes/No/Uncertain)"),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0
            ),
            "D" => array(
                'description' => gT("Date/Time"),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0
            ),
            "E" => array(
                'description' => gT("Array (Increase/Same/Decrease)"),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0
            ),
            "F" => array(
                'description' => gT("Array"),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 1
            ),
            "G" => array(
                'description' => gT("Gender"),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0
            ),
            "H" => array(
                'description' => gT("Array by column"),
                'group' => gT('Arrays'),
                'hasdefaultvalues' => 0,
                'subquestions' => 1,
                'assessable' => 1,
                'answerscales' => 1
            ),
            "I" => array(
                'description' => gT("Language Switch"),
                'group' => gT("Mask questions"),
                'hasdefaultvalues' => 0,
                'subquestions' => 0,
                'assessable' => 0,
                'answerscales' => 0
            ),
            "K" => array(
                'description' => gT("Multiple Numerical Input"),
                'group' => gT("Mask questions"),
                'hasdefaultvalues' => 1,
                'subquestions' => 1,
                'assessable' => 1,
                'answerscales' => 0
            ),
            "L" => array(
                'description' => gT("List (Radio)"),
                'group' => gT("Single choice questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 1
            ),
            "M" => array(
                'description' => gT("Multiple choice"),
                'group' => gT("Multiple choice questions"),
                'subquestions' => 1,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 0
            ),
            "N" => array(
                'description' => gT("Numerical Input"),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0
            ),
            "O" => array(
                'description' => gT("List with comment"),
                'group' => gT("Single choice questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 1
            ),
            "P" => array(
                'description' => gT("Multiple choice with comments"),
                'group' => gT("Multiple choice questions"),
                'subquestions' => 1,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 0
            ),
            "Q" => array(
                'description' => gT("Multiple Short Text"),
                'group' => gT("Text questions"),
                'subquestions' => 1,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0
            ),
            "R" => array(
                'description' => gT("Ranking"),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 1
            ),
            "S" => array(
                'description' => gT("Short Free Text"),
                'group' => gT("Text questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0
            ),
            "T" => array(
                'description' => gT("Long Free Text"),
                'group' => gT("Text questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0
            ),
            "U" => array(
                'description' => gT("Huge Free Text"),
                'group' => gT("Text questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0
            ),
            "X" => array(
                'description' => gT("Text display"),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0
            ),
            "Y" => array(
                'description' => gT("Yes/No"),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0
            ),
            "!" => array(
                'description' => gT("List (Dropdown)"),
                'group' => gT("Single choice questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 1
            ),
            ":" => array(
                'description' => gT("Array (Numbers)"),
                'group' => gT('Arrays'),
                'subquestions' => 2,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0
            ),
            ";" => array(
                'description' => gT("Array (Texts)"),
                'group' => gT('Arrays'),
                'subquestions' => 2,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0
            ),
            "|" => array(
                'description' => gT("File upload"),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0
            ),
            "*" => array(
                'description' => gT("Equation"),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0
            ),
        );
        // Makes it easier to work with in CHtml::listData
        foreach ($questionTypes as $type => &$details) {
            $details['type'] = $type;
        }
        /**
         * @todo Check if this actually does anything, since the values are arrays.
         */
        asort($questionTypes);

        return $questionTypes;
    }

    /**
     * Return the classes to be added to the question wrapper.
     * @return []
     */
    public function getClasses()
    {
        $result = ['question'];
        switch ($this->type) {
            //case 'W': return 'list-dropdown-flexible'; //   LIST drop-down (flexible label)
            case 'X':
                $result[] = 'boilerplate';
                break;
        };

        return $result;
    }

    public function scopes()
    {
        return [
            'primary' => [
                'condition' => 'parent_qid = 0'
            ]
        ];
    }

    public function getSgqa()
    {
        $result = "{$this->sid}X{$this->gid}X{$this->qid}";

        return $result;
    }

    /**
     * @todo Move individual cases to subclasses.
     * @return array|mixed
     * @throws Exception
     */
    public function getColumns()
    {
        if (!empty($this->parent_qid)) {
            return [
                $this->title => 'string(5)'
            ];
        };
        switch ($this->type) {
            case "K":  //Multiple Numerical
                $result = [$this->sgqa => "decimal (30,10)"];
                break;
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
            case "D":  //DATE
                $result = [$this->sgqa => "datetime"];
                break;
            case "I":  //Language switch
                $result = [$this->sgqa => "string(20)"];
                break;
            default:
                $class = get_class($this);
                throw new \Exception("Don't know columns for question type: {$this->type} ({$class})");

        }

        return $result;
    }


    /**
     * This allows us to put question type specific code in a separate class.
     *
     * @param $attributes
     * @return mixed
     */
    protected function instantiate($attributes)
    {
        if (!isset($attributes['type'])) {
            throw new \Exception("The type attribute must be selected for single table inheritance to work.");
        }
        if (!empty($attributes['parent_qid'])) {
            $class = \ls\models\questions\SubQuestion::class;
        } else {
            $class = self::resolveClass($attributes['type']);
        }

        return new $class(null);
    }

    protected static function map()
    {
        return [
            self::TYPE_ARRAY_TEN_POINT => \ls\models\questions\TenPointArrayQuestion::class,
            self::TYPE_NUMERICAL_INPUT => \ls\models\questions\NumericalQuestion::class,
            self::TYPE_DATE_TIME => \ls\models\questions\DateTimeQuestion::class,
            self::TYPE_HUGE_TEXT => \ls\models\questions\HugeTextQuestion::class,
            self::TYPE_LONG_TEXT => \ls\models\questions\LongTextQuestion::class,
            self::TYPE_SHORT_TEXT => \ls\models\questions\ShortTextQuestion::class,
            self::TYPE_LIST_WITH_COMMENT => \ls\models\questions\SingleChoiceWithCommentQuestion::class,
            self::TYPE_DROPDOWN_LIST => \ls\models\questions\DropDownListQuestion::class,
            self::TYPE_RADIO_LIST => \ls\models\questions\RadioListQuestion::class,
            self::TYPE_MULTIPLE_SHORT_TEXT => \ls\models\questions\MultipleTextQuestion::class,
            self::TYPE_RANKING => \ls\models\questions\RankingQuestion::class,
            self::TYPE_ARRAY => \ls\models\questions\ArrayQuestion::class,
            self::TYPE_ARRAY_TEXTS => \ls\models\questions\OpenArrayQuestion::class,
            self::TYPE_ARRAY_NUMBERS => \ls\models\questions\NumericalArrayQuestion::class,
            self::TYPE_YES_NO => \ls\models\questions\YesNoQuestion::class,
            self::TYPE_ARRAY_INCREASE_SAME_DECREASE => \ls\models\questions\IncreaseSameDecreaseArrayQuestion::class,
            self::TYPE_ARRAY_YES_NO_UNCERTAIN => \ls\models\questions\YesNoUncertainArrayQuestion::class,
            self::TYPE_ARRAY_FIVE_POINT => \ls\models\questions\FivePointArrayQuestion::class,
            self::TYPE_UPLOAD => \ls\models\questions\UploadQuestion::class,
            self::TYPE_DISPLAY => \ls\models\questions\DisplayQuestion::class,
            self::TYPE_MULTIPLE_CHOICE => \ls\models\questions\MultipleChoiceQuestion::class,
            self::TYPE_MULTIPLE_CHOICE_WITH_COMMENT => \ls\models\questions\MultipleChoiceWithCommentQuestion::class,
            self::TYPE_GENDER => \ls\models\questions\GenderQuestion::class,
            self::TYPE_LANGUAGE_SWITCH => \ls\models\questions\LanguageQuestion::class,
            self::TYPE_MULTIPLE_NUMERICAL_INPUT => \ls\models\questions\MultipleNumberQuestion::class,
            self::TYPE_FIVE_POINT_CHOICE => \ls\models\questions\FivePointChoiceQuestion::class,
            self::TYPE_ARRAY_BY_COLUMN => \ls\models\questions\ArrayByColumnQuestion::class,
            self::TYPE_ARRAY_DUAL_SCALE => \ls\models\questions\DualScaleArrayQuestion::class,
            self::TYPE_EQUATION => \ls\models\questions\EquationQuestion::class
        ];
    }

    public static function resolveClass($type)
    {
        $map = self::map();
        if (isset($map[$type])) {
            return $map[$type];
        }
        throw new \Exception("No class for question type {$type}");
    }

    public function getTypeName()
    {
        return $this->typeList()[$this->type]['description'];
    }

    /**
     * Strip tags, and line breaks.
     * @return string
     */
    public function getDisplayLabel()
    {
        return "{$this->title} - {$this->getShortText()}";
    }

    public function getShortText()
    {
        return preg_replace('/\s+/', ' ',
            str_replace(['&nbsp;', "\r", "\n"], [' ', ' ', ' '], strip_tags($this->question)));
    }

    public function attributeLabels()
    {
        return [
            'after' => gT('Position'),
            'mandatory' => gT('Mandatory'),
            'other' => gT("Option 'Other'"),
            'exclude_all_others' => gT("Exclusive option"),
        ];
    }

    /**
     * Map bool_ prefix to their underlying properties.
     */
    public function getAttributeLabel($attribute)
    {
        if (substr_compare('bool_', $attribute, 0, 5) === 0) {
            return parent::getAttributeLabel(substr($attribute, 5));
        }

        return parent::getAttributeLabel($attribute);
    }


    /**
     * Returns the relations that map to dependent records.
     * Dependent records should be deleted when this object gets deleted.
     * @return string[]
     */
    public function dependentRelations()
    {
        return [
            'subQuestions',
            'questionAttributes',
            'conditions',
            'answers',
            'defaultValues'
        ];
    }


    /**
     * Returns the question attributes that do use i18n.
     * @return string[]
     */
    public function customAttributeNames()
    {
        if (isset($this->type)) {
            $attributes = array_filter(questionAttributes()[$this->type], function ($attribute) {
                return $attribute['i18n'] === false;
            });
            $result = array_keys($attributes);

            /**
             * Add this to every question type; there is no technical reason not to have it.
             */
            $result[] = 'em_validation_q';
            $result[] = 'em_validation_q_tip';


        } else {
            $result = [];
        }

        return $result;
    }

    public function customLocalizedAttributeNames()
    {
        if (isset($this->type)) {
            $attributes = array_filter(questionAttributes()[$this->type], function ($attribute) {
                return $attribute['i18n'] === true;
            });
            $result = array_keys($attributes);
        } else {
            $result = [];
        }

        return $result;
    }

    public function hasAttribute($name)
    {
        if (strncmp('bool_', $name, 5) === 0) {
            $name = substr($name, 5);
        }

        return in_array($name, $this->customAttributeNames()) || parent::hasAttribute($name);
    }

    /**
     * Returns the fields for this question.
     * @return QuestionResponseField[]
     */
    public function getFields()
    {
        $fields[] = $field = new QuestionResponseField($this->sgqa, $this->title, $this);
        $field->setRelevanceScript($this->relevanceScript);
        if ($this->bool_other) {
            $this->_fields[] = $field = new QuestionResponseField($this->sgqa . 'other', $this->title . 'other', $this);
            $field->setRelevanceScript($this->relevanceScript);
        }

        return $fields;


    }

    /**
     * The variable name for this question.
     */
    public function getVarName()
    {
        if (!empty($this->parent_qid)) {
            throw new \Exception("Don't knwo this for subquestions!");
        }

        return $this->title;

    }

    /**
     * Check if the response passes mandatory requirements for this question.
     * By default a question passes this if any of it's fields have been filled.
     * @return boolean
     */
    final public function validateResponse(\ls\interfaces\ResponseInterface $response)
    {

        $em = $this->getExpressionManager($response);
        foreach ($this->getValidationExpressions() as $expression => $message) {
            if (!$em->ProcessBooleanExpression($expression)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns an array of EM expression that validate this question.
     * @return string[]
     */
    public function getValidationExpressions()
    {
        $result = [];
        if (!empty($this->em_validation_q)) {
            $result[$this->em_validation_q] = $this->em_validation_q_tip;
        }
        if (!empty($this->preg)) {
            $expression = "regexMatch('{$this->preg}', $this->title)";
            $result[$expression] = "Value must match regular expression: " . $this->preg;
        }

        if ($this->bool_mandatory) {
            // Default implementation set field to the sgqa, this should be overwritten in subclasses.
            if (count($this->columns) > 1) {
                //@todo Provide default implementation.
            } elseif (count($this->columns == 1)) {
                $result["!is_empty({$this->title})"] = gT("This question is mandatory");
            }

        }

        return $result;
    }


    public function getRelevanceScript()
    {
        $clauses = [];
        $clauses[] = $this->group->getRelevanceScript();
        if (!empty($this->relevance)) {
            $clauses[] = $this->relevance;
        }

        $clauses = array_filter($clauses, function ($clause) {
            // If a clause is boolean true, we can safely ignore it.
            return $clause !== true;
        });
        if (!empty($clauses)) {
            $em = $this->getExpressionManager();
            $emExpression = '(' . implode(') && (', $clauses) . ')';
            $result = $em->getJavascript($emExpression);

            if (empty($result)) {
                throw new \Exception('NO jS created');
            };

            return $result;
        }

        return true;
    }

    /**
     * Checks if the question is relevant for the current response.
     * @param \ls\interfaces\ResponseInterface $response
     * @return boolean
     */
    public function isRelevant(\ls\interfaces\ResponseInterface $response)
    {
        // Check if the group is relevant first.
        if (!$this->group->isRelevant($response)) {
            $result = false;
        } elseif (empty($this->relevance)) {
            $result = true;
        } else {
            $result = $this->getExpressionManager($response)->ProcessBooleanExpression($this->relevance);
        }

        return $result;

    }


    public function getExpressionManager()
    {
        if (null !== $session = App()->surveySessionManager->current) {
            return \LimeExpressionManager::getExpressionManagerForSession(App()->surveySessionManager->current);
        } else {
            return \LimeExpressionManager::getExpressionManagerForSurvey($this->survey);
        }

    }

    /**
     * @param int $scale
     * @return \ls\interfaces\iSubQuestion[]
     */
    public function getSubQuestions($scale = 0)
    {
        if (isset($scale)) {
            $result = array_filter($this->getRelated('subQuestions'), function (Question $subQuestion) use ($scale) {
                return $scale == $subQuestion->scale_id;
            });
        } else {
            $result = $this->getRelated('subQuestions');
        }

        return $result;
    }

    /**
     * @param null $scale
     * @return \ls\interfaces\iAnswer[]
     * @throws CDbException
     */
    public function getAnswers($scale = null)
    {
        if (isset($scale)) {
            $result = array_filter($this->getRelated('answers'), function (Answer $answer) use ($scale) {
                return $scale == $answer->scale_id;
            });
        } else {
            $result = $this->getRelated('answers');
        }

        return $result;
    }

    /**
     * Will create span replacements for EM expressions.
     * @param string $text
     */
    protected function createReplacements(\ls\interfaces\ResponseInterface $response, $text)
    {
        $em = $this->getExpressionManager($response);
        $parts = $em->asSplitStringOnExpressions($text);
        $result = '';

        foreach ($parts as $part) {
            switch ($part[2]) {
                case 'STRING':
                    $result .= $part[0];
                    break;
                case 'EXPRESSION':
                    if ($em->RDP_Evaluate(substr($part[0], 1, -1))) {
                        $value = $em->GetResult();
                    } else {

                        $value = '';
                    }
                    $result .= TbHtml::tag('span', [
                        'data-expression' => $em->getJavascript(substr($part[0], 1, -1))
                    ], $value);
            }
        }

        return $result;

    }

    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \ls\components\SurveySession $session
     * @return \ls\components\RenderedQuestion
     */
    public function render(\ls\interfaces\ResponseInterface $response, \ls\components\SurveySession $session)
    {
        bP();
        $result = new \ls\components\RenderedQuestion($this);
        $result->setIndex($session->getQuestionIndex($this->primaryKey));
        $em = $this->getExpressionManager($response);


        $result->setQuestionText($this->getExpressionManager($response)->createDynamicReplacements($this->question));

        foreach ($this->getValidationExpressions() as $expression => $message) {
            $result->addValidation($em->getJavascript($expression), $message);
        }

        if ($this->hasAttribute('time_limit')) {
            $result->htmlOptions['data-time-limit'] = $this->time_limit;
        }
        eP();

        return $result;
    }


    public function getArrayFilterStyleOptions()
    {
        return [
            0 => gT('Hidden'),
            1 => gT('Disabled')
        ];

    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        $result = parent::jsonSerialize();
        $this->loadCustomAttributes();

        // Merge, custom first so "real" attributes always override custom attributes.
        return array_merge($this->customAttributes, $result);

    }

    public function __construct($scenario = 'insert')
    {
        parent::__construct($scenario);
        $map = array_flip(self::map());
        $this->type = isset($map[static::class]) ? $map[static::class] : null;
    }

    public function getTimeLimitOptions()
    {
        return [
            1 => gT('Warn and move on'),
            2 => gT('Move on without warning'),
            3 => gT('Disable only')
        ];
    }

    /**
     * Does this question support custom answers?
     * @return boolean
     */
    public function getHasCustomAnswers()
    {
        return false;
    }

    /**
     * Does this question support custom subquestions?
     * @return boolean
     */
    public function getHasCustomSubQuestions()
    {
        return false;
    }

}


