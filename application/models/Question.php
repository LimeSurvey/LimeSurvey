<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
*/


/**
 * Class Question
 *
 * @property integer $qid Question ID. Note: Primary key is qid & language columns combined
 * @property integer $sid Survey ID
 * @property integer $gid QuestionGroup ID where question is diepolayed
 * @property string $type
 * @property string $title Question Code
 * @property string $question Question dieplay text. The actual question.
 * @property string $preg
 * @property string $help Question help-text for display
 * @property string $other Other option enabled for question (Y/N)
 * @property string $mandatory Whther question is mandatory (Y/N)
 * @property integer $question_order Question order in greoup
 * @property integer $parent_qid Questions parent question ID eg for subquestions
 * @property string $language Question language code. Note: Primary key is qid & language columns combined
 * @property integer $scale_id  The scale ID
 * @property integer $same_default Saves if user set to use the same default value across languages in default options dialog
 * @property string $relevance Questions relevane equation
 * @property string $modulename
 *
 * @property Survey $survey
 * @property QuestionGroup $groups  //TODO should be singular
 * @property Question $parents      //TODO should be singular
 * @property Question[] $subquestions
 * @property QuestionAttribute[] $questionAttributes NB! returns all QuestionArrtibute Models fot this QID regardless of the specified language
 * @property string[] $quotableTypes Question types that can be used for quotas
 * @inheritdoc
 */
class Question extends LSActiveRecord
{

    /** @var string $group_name Stock the active group_name for questions list filtering */
    public $group_name;
    public $gid;

    /**
     * @inheritdoc
     * @return Question
     */
    public static function model($class = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($class);
        return $model;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{questions}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return array('qid', 'language');
    }

    /** @inheritdoc */
    public function relations()
    {
        return array(
            'survey' => array(self::BELONGS_TO, 'Survey', 'sid'),
            'groups' => array(self::BELONGS_TO, 'QuestionGroup', 'gid, language', 'together' => true),
            'parents' => array(self::HAS_ONE, 'Question', array("qid" => "parent_qid", "language" => "language")),
            'questionAttributes' => array(self::HAS_MANY, 'QuestionAttribute', 'qid'),
            'subquestions' => array(self::HAS_MANY, 'Question', array('parent_qid'=>'qid', "language" => "language")),
            'answers' => array(self::HAS_MANY, 'Answer', array('qid'=>'qid', "language" => "language"))
        );
    }

    /**
     * @inheritdoc
     * TODO: make it easy to read (if possible)
     */
    public function rules()
    {
        $aRules = array(
                    array('title', 'required', 'on' => 'update, insert', 'message'=>gT('Question code may not be empty.', 'unescaped')),
                    array('title', 'length', 'min' => 1, 'max'=>20, 'on' => 'update, insert'),
                    array('qid,sid,gid,parent_qid', 'numerical', 'integerOnly'=>true),
                    array('qid', 'unique', 'criteria'=>array(
                            'condition'=>'language=:language',
                            'params'=>array(':language'=>$this->language)
                        ),
                        'message'=>sprintf(gT("Question ID (qid): '%s' is already in use."),$this->qid),// Usage of {attribute} need attributeLabels, {value} never exist in message
                    ),
                    array('language', 'length', 'min' => 2, 'max'=>20), // in array languages ?
                    array('title,question,help', 'LSYii_Validators'),
                    array('other', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
                    array('mandatory', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
                    array('question_order', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
                    array('scale_id', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
                    array('same_default', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
                    array('type', 'length', 'min' => 1, 'max'=>1),
                    array('preg,relevance', 'safe'),
                    array('modulename', 'length', 'max'=>255),
                );
        // Always enforce unicity on Sub question code (DB issue).
        if ($this->parent_qid) {
            $aRules[] = array('title', 'unique', 'caseSensitive'=>false,
                'criteria'=>array(
                    'condition' => 'language=:language AND sid=:sid AND parent_qid=:parent_qid and scale_id=:scale_id',
                    'params' => array(
                        ':language' => $this->language,
                        ':sid' => $this->sid,
                        ':parent_qid' => $this->parent_qid,
                        ':scale_id' => $this->scale_id
                    )
                ),
                'message' => gT('Subquestion codes must be unique.')
            );
            // Disallow other title if question allow other
            $oParentQuestion = Question::model()->findByPk(array("qid"=>$this->parent_qid, 'language'=>$this->language));
            if ($oParentQuestion->other == "Y") {
                $aRules[] = array('title', 'LSYii_CompareInsensitiveValidator', 'compareValue'=>'other', 'operator'=>'!=', 'message'=> sprintf(gT("'%s' can not be used if the 'Other' option for this question is activated."), "other"), 'except' => 'archiveimport');
            }
            // #14495: comment suffix can't be used with P Question (collapse with table name in database)
            if ($oParentQuestion->type == "P") {
                $aRules[] = array('title', 'match', 'pattern'=>'/comment$/', 'not'=>true, 'message'=> gT("'comment' suffix can not be used with multiple choice with comments."));
            }
        } else {
            // Disallow other if sub question have 'other' for title
            $oSubquestionOther = Question::model()->find("parent_qid=:parent_qid and LOWER(title)='other'", array("parent_qid"=>$this->qid));
            if ($oSubquestionOther) {
                $aRules[] = array('other', 'compare', 'compareValue'=>'Y', 'operator'=>'!=', 'message'=> sprintf(gT("'%s' can not be used if the 'Other' option for this question is activated."), 'other'), 'except' => 'archiveimport');
            }
        }
        if (!$this->isNewRecord) {
            $oActualValue = Question::model()->findByPk(array("qid"=>$this->qid, 'language'=>$this->language));
            if ($oActualValue && $oActualValue->title == $this->title) {
                /* We don't change title, then don't put rules on title */
                /* We don't want to broke existing survey,  We only disallow to set it or update it according to this value */
                return $aRules;
            }
        }
        /* Question was new or title was updated : we add minor rules. This rules don't broke DB, only potential “Expression Manager” issue. */
        if (!$this->parent_qid) { // 0 or empty
            /* Unicity for ExpressionManager */
            $aRules[] = array('title', 'unique', 'caseSensitive'=>true,
                'criteria'=>array(
                    'condition' => 'language=:language AND sid=:sid AND parent_qid=0',
                    'params' => array(
                        ':language' => $this->language,
                        ':sid' => $this->sid
                        )
                    ),
                'message' => gT('Question codes must be unique.'),
                'except' => 'archiveimport'
            );
            /* ExpressionManager basic rule */
            $aRules[] = array('title', 'match', 'pattern' => '/^[a-z,A-Z][[:alnum:]]*$/',
                'message' => gT('Question codes must start with a letter and may only contain alphanumeric characters.'),
                'except' => 'archiveimport'
            );
            /* ExpressionManager reserved word (partial) */
            $aRules[] = array('title', 'in', 'not' => true,
                'range' => array(
                    'LANG','SID', // Global var
                    'SAVEDID','TOKEN', // current survey related var
                    'QID','GID','SGQ', // current question related var
                    'self','that','this', // EM reserved variables
                ),
                'message'=> sprintf(gT("Code: '%s' is a reserved word."),$this->title), // Usage of {attribute} need attributeLabels, {value} never exist in message
                'except' => 'archiveimport'
            );
        } else {
            $aRules[] = array('title', 'compare', 'compareValue'=>'time', 'operator'=>'!=',
                'message'=> gT("'time' is a reserved word and can not be used for a subquestion."),
                'except' => 'archiveimport');
            $aRules[] = array('title', 'match', 'pattern' => '/^[[:alnum:]]*$/',
                'message' => gT('Subquestion codes may only contain alphanumeric characters.'),
                'except' => 'archiveimport');
        }
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
        $questions = self::model()->findAllByAttributes(
            array('gid' => $gid, 'sid' => $surveyid, 'parent_qid'=>0, 'language' => Survey::model()->findByPk($surveyid)->language),
            array('order'=>'question_order')
        );

        $p = 0;
        foreach ($questions as $question) {
            $question->question_order = $p;
            $question->save();
            $p++;
        }
    }


    /**
     * Fix sort order for questions in a group
     * @param int $gid
     * @param string $language
     * @param int $position
     */
    public function updateQuestionOrder($gid, $language, $position = 0)
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
            Yii::app()->db->createCommand()->update($this->tableName(),
                array('question_order' => $position), 'qid='.$row['qid']);
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
    public function getAdvancedSettingsWithValues($iQuestionID, $sQuestionType, $iSurveyID, $sLanguage = null)
    {
        if (is_null($sLanguage)) {
            $aLanguages = array_merge(array(Survey::model()->findByPk($iSurveyID)->language), Survey::model()->findByPk($iSurveyID)->additionalLanguages);
        } else {
            $aLanguages = array($sLanguage);
        }
        $aAttributeValues = QuestionAttribute::model()->getQuestionAttributes($iQuestionID, $sLanguage);
        // TODO: move getQuestionAttributesSettings() to QuestionAttribute model to avoid code duplication
        $aAttributeNames = \LimeSurvey\Helpers\questionHelper::getQuestionAttributesSettings($sQuestionType);

        // If the question has a custom template, we first check if it provides custom attributes

        if (!is_null($sLanguage)) {
            $oQuestion = Question::model()->findByPk(array('qid'=>$iQuestionID, 'language'=>$sLanguage));
        } else {
            $oQuestion = Question::model()->find(array('condition'=>'qid=:qid', 'params'=>array(':qid'=>$iQuestionID)));
        }
        $aAttributeNames = self::getQuestionTemplateAttributes($aAttributeNames, $aAttributeValues, $oQuestion);

        uasort($aAttributeNames, 'categorySort');
        foreach ($aAttributeNames as $iKey => $aAttribute) {
            if ($aAttribute['i18n'] == false) {
                if (isset($aAttributeValues[$aAttribute['name']])) {
                    $aAttributeNames[$iKey]['value'] = $aAttributeValues[$aAttribute['name']];
                } else {
                    $aAttributeNames[$iKey]['value'] = $aAttribute['default'];
                }
            } else {
                foreach ($aLanguages as $sLanguage) {
                    if (isset($aAttributeValues[$aAttribute['name']][$sLanguage])) {
                        $aAttributeNames[$iKey][$sLanguage]['value'] = $aAttributeValues[$aAttribute['name']][$sLanguage];
                    } else {
                        $aAttributeNames[$iKey][$sLanguage]['value'] = $aAttribute['default'];
                    }
                }
            }
        }

        return $aAttributeNames;
    }

    /**
     * @param array $aAttributeNames
     * @param array $aAttributeValues
     * @param Question $oQuestion
     * @return mixed
     */
    public static function getQuestionTemplateAttributes($aAttributeNames, $aAttributeValues, $oQuestion)
    {
        if (isset($aAttributeValues['question_template'])) {
            if ($aAttributeValues['question_template'] != 'core') {

                $oQuestionTemplate = QuestionTemplate::getInstance($oQuestion);
                if ($oQuestionTemplate->bHasCustomAttributes) {
                    // Add the custom attributes to the list
                    foreach ($oQuestionTemplate->oConfig->custom_attributes->attribute as $oCustomAttribute) {
                        $sAttributeName = (string) $oCustomAttribute->name;
                        $aCustomAttribute = json_decode(json_encode((array) $oCustomAttribute), 1);
                        $aCustomAttribute = array_merge(
                            QuestionAttribute::getDefaultSettings(),
                            array("category"=>gT("Template")),
                            $aCustomAttribute
                        );
                        $aAttributeNames[$sAttributeName] = $aCustomAttribute;
                    }
                }
            }
        }
        return $aAttributeNames;
    }

    public function getTypeGroup()
    {

    }

    /**
     * TODO: replace this function call by $oSurvey->questions defining a relation in SurveyModel
     * @param integer $sid
     * @param integer $gid
     * @param string $language
     * @return CDbDataReader
     */
    public function getQuestions($sid, $gid, $language)
    {
        return Yii::app()->db->createCommand()
            ->select()
            ->from(self::tableName())
            ->where(array('and', 'sid=:sid', 'gid=:gid', 'language=:language', 'parent_qid=0'))
            ->order('question_order asc')
            ->bindParam(":sid", $sid, PDO::PARAM_INT)
            ->bindParam(":gid", $gid, PDO::PARAM_INT)
            ->bindParam(":language", $language, PDO::PARAM_STR)
            ->query();
    }


    /**
     * This function is only called from surveyadmin.php
     * @param integer $iSurveyID
     * @param string $sLanguage
     * @param string|boolean $sCondition
     * @return array
     */
    public function getQuestionsWithSubQuestions($iSurveyID, $sLanguage, $sCondition = false)
    {
        $command = Yii::app()->db->createCommand()
            ->select('{{questions}}.*, q.qid as sqid, q.title as sqtitle,  q.question as sqquestion, '.'{{groups}}.*')
            ->from($this->tableName())
            ->leftJoin('{{questions}} q', "q.parent_qid = {{questions}}.qid AND q.language = {{questions}}.language")
            ->join('{{groups}}', "{{groups}}.gid = {{questions}}.gid  AND {{questions}}.language = {{groups}}.language");
        $command->where("({{questions}}.sid = '$iSurveyID' AND {{questions}}.language = '$sLanguage' AND {{questions}}.parent_qid = 0)");

        if ($sCondition != false) {
            $command->where("({{questions}}.sid = :iSurveyID AND {{questions}}.language = :sLanguage AND {{questions}}.parent_qid = 0) AND {$sCondition}")
            ->bindParam(":iSurveyID", $iSurveyID, PDO::PARAM_STR)
            ->bindParam(":sLanguage", $sLanguage, PDO::PARAM_STR);
        }
        $command->order("{{groups}}.group_order asc, {{questions}}.question_order asc");

        return $command->query()->readAll();
    }



    /**
     * Delete a bunch of questions in one go
     *
     * @param mixed $questionsIds
     * @return void
     */
    public static function deleteAllById($questionsIds)
    {
        if (!is_array($questionsIds)) {
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

    /**
     * This function is called from everywhere, which is quiet weird...
     * TODO: replace it everywhere by Answer::model()->findAll([Critieria Object]) (thumbs up)
     */
    function getAllRecords($condition, $order = false)
    {
        $command = Yii::app()->db->createCommand()->select('*')->from($this->tableName())->where($condition);
        if ($order != false) {
            $command->order($order);
        }
        return $command->query();
    }



    /**
     * TODO: replace it everywhere by Answer::model()->findAll([Critieria Object])
     * @param string $fields
     * @param mixed $condition
     * @param string $orderby
     * @return array
     */
    public function getQuestionsForStatistics($fields, $condition, $orderby = false)
    {
        $command = Yii::app()->db->createCommand()
        ->select($fields)
        ->from(self::tableName())
        ->where($condition);
        if ($orderby != false) {
            $command->order($orderby);
        }
        return $command->queryAll();
    }

    /**
     * @param integer $surveyid
     * @param string $language
     * @return array
     */
    public function getQuestionList($surveyid, $language)
    {
        $query = "SELECT questions.*, question_groups.group_name, question_groups.group_order"
            ." FROM {{questions}} as questions, {{groups}} as question_groups"
            ." WHERE question_groups.gid=questions.gid"
            ." AND question_groups.language=:language1"
            ." AND questions.language=:language2"
            ." AND questions.parent_qid=0"
            ." AND questions.sid=:sid";
        return Yii::app()->db->createCommand($query)
            ->bindParam(":language1", $language, PDO::PARAM_STR)
            ->bindParam(":language2", $language, PDO::PARAM_STR)
            ->bindParam(":sid", $surveyid, PDO::PARAM_INT)->queryAll();
    }

    /**
     * @return string
     */
    public function getTypedesc()
    {
        $types = self::typeList();
        $typeDesc = $types[$this->type]["description"];

        if (YII_DEBUG) {
            $typeDesc .= ' <em>'.$this->type.'</em>';
        }

        return $typeDesc;
    }

    /**
     * This function contains the question type definitions.
     * @param string $language Language for translation
     * @return array The question type definitions
     *
     * Explanation of questiontype array:
     *
     * description : Question description
     * subquestions : 0= Does not support subquestions x=Number of subquestion scales
     * answerscales : 0= Does not need answers x=Number of answer scales (usually 1, but e.g. for dual scale question set to 2)
     * assessable : 0=Does not support assessment values when editing answerd 1=Support assessment values
     */
    public static function typeList($language = null)
    {
        $questionTypes = array(
            "1" => array(
                'description' => gT("Array dual scale", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'assessable' => 1,
                'hasdefaultvalues' => 0,
                'answerscales' => 2,
                'class' => 'array-flexible-duel-scale',
            ),
            "5" => array(
                'description' => gT("5 Point Choice", "html", $language),
                'group' => gT("Single choice questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => "choice-5-pt-radio"
            ),
            "A" => array(
                'description' => gT("Array (5 Point Choice)", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'array-5-pt'
            ),
            "B" => array(
                'description' => gT("Array (10 Point Choice)", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'array-10-pt'
            ),
            "C" => array(
                'description' => gT("Array (Yes/No/Uncertain)", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'array-yes-uncertain-no'
            ),
            "D" => array(
                'description' => gT("Date/Time", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'date'
            ),
            "E" => array(
                'description' => gT("Array (Increase/Same/Decrease)", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'array-increase-same-decrease'
            ),
            "F" => array(
                'description' => gT("Array", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 1,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 1,
                'class' => 'array-flexible-row'
            ),
            "G" => array(
                'description' => gT("Gender", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'gender'
            ),
            "H" => array(
                'description' => gT("Array by column", "html", $language),
                'group' => gT('Arrays'),
                'hasdefaultvalues' => 0,
                'subquestions' => 1,
                'assessable' => 1,
                'answerscales' => 1,
                'class' => 'array-flexible-column'
            ),
            "I" => array(
                'description' => gT("Language Switch", "html", $language),
                'group' => gT("Mask questions"),
                'hasdefaultvalues' => 0,
                'subquestions' => 0,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'language'
            ),
            "K" => array(
                'description' => gT("Multiple Numerical Input", "html", $language),
                'group' => gT("Mask questions"),
                'hasdefaultvalues' => 1,
                'subquestions' => 1,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'numeric-multi'
            ),
            "L" => array(
                'description' => gT("List (Radio)", "html", $language),
                'group' => gT("Single choice questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 1,
                'class' => 'list-radio'
            ),
            "M" => array(
                'description' => gT("Multiple choice", "html", $language),
                'group' => gT("Multiple choice questions"),
                'subquestions' => 1,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'multiple-opt'
            ),
            "N" => array(
                'description' => gT("Numerical Input", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'numeric'
            ),
            "O" => array(
                'description' => gT("List with comment", "html", $language),
                'group' => gT("Single choice questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 1,
                'class' => 'list-with-comment'
            ),
            "P" => array(
                'description' => gT("Multiple choice with comments", "html", $language),
                'group' => gT("Multiple choice questions"),
                'subquestions' => 1,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'multiple-opt-comments'
            ),
            "Q" => array(
                'description' => gT("Multiple Short Text", "html", $language),
                'group' => gT("Text questions"),
                'subquestions' => 1,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'multiple-short-txt'
            ),
            "R" => array(
                'description' => gT("Ranking", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 1,
                'class' => 'ranking'
            ),
            "S" => array(
                'description' => gT("Short Free Text", "html", $language),
                'group' => gT("Text questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'text-short'
            ),
            "T" => array(
                'description' => gT("Long Free Text", "html", $language),
                'group' => gT("Text questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'text-long'
            ),
            "U" => array(
                'description' => gT("Huge Free Text", "html", $language),
                'group' => gT("Text questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'text-huge'
            ),
            "X" => array(
                'description' => gT("Text display", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'boilerplate'
            ),
            "Y" => array(
                'description' => gT("Yes/No", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'yes-no'
            ),
            "!" => array(
                'description' => gT("List (Dropdown)", "html", $language),
                'group' => gT("Single choice questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 1,
                'assessable' => 1,
                'answerscales' => 1,
                'class' => 'list-dropdown'
            ),
            ":" => array(
                'description' => gT("Array (Numbers)", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 2,
                'hasdefaultvalues' => 0,
                'assessable' => 1,
                'answerscales' => 0,
                'class' => 'array-multi-flexi'
            ),
            ";" => array(
                'description' => gT("Array (Texts)", "html", $language),
                'group' => gT('Arrays'),
                'subquestions' => 2,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'array-multi-flexi-text'
            ),
            "|" => array(
                'description' => gT("File upload", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'upload-files'
            ),
            "*" => array(
                'description' => gT("Equation", "html", $language),
                'group' => gT("Mask questions"),
                'subquestions' => 0,
                'hasdefaultvalues' => 0,
                'assessable' => 0,
                'answerscales' => 0,
                'class' => 'equation'
            ),
        );
        /**
         * @todo Check if this actually does anything, since the values are arrays.
         */
        asort($questionTypes);

        return $questionTypes;
    }

    /**
     * This function return the name by question type
     * @param string question type
     * @return string Question type name
     *
     * Maybe move class in typeList ?
     */
    public static function getQuestionTypeName($sType)
    {
        $typeList = self::typeList();
        return $typeList[$sType]['description'];
    }
    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     *
     * Maybe move class in typeList ?
     */
    public static function getQuestionClass($sType)
    {
        switch ($sType) {
            case "1": return 'array-flexible-duel-scale';
            case '5': return 'choice-5-pt-radio';
            case 'A': return 'array-5-pt';
            case 'B': return 'array-10-pt';
            case 'C': return 'array-yes-uncertain-no';
            case 'D': return 'date';
            case 'E': return 'array-increase-same-decrease';
            case 'F': return 'array-flexible-row';
            case 'G': return 'gender';
            case 'H': return 'array-flexible-column';
            case 'I': return 'language';
            case 'K': return 'numeric-multi';
            case 'L': return 'list-radio';
            case 'M': return 'multiple-opt';
            case 'N': return 'numeric';
            case 'O': return 'list-with-comment';
            case 'P': return 'multiple-opt-comments';
            case 'Q': return 'multiple-short-txt';
            case 'R': return 'ranking';
            case 'S': return 'text-short';
            case 'T': return 'text-long';
            case 'U': return 'text-huge';
            //case 'W': return 'list-dropdown-flexible'; //   LIST drop-down (flexible label)
            case 'X': return 'boilerplate';
            case 'Y': return 'yes-no';
            case 'Z': return 'list-radio-flexible';
            case '!': return 'list-dropdown';
            //case '^': return 'slider';          //  SLIDER CONTROL
            case ':': return 'array-multi-flexi';
            case ";": return 'array-multi-flexi-text';
            case "|": return 'upload-files';
            case "*": return 'equation';
            default:  return 'generic_question'; // fallback
        };
    }

    /**
     * Return all group of the active survey
     * Used to render group filter in questions list
     */
    public function getAllGroups()
    {
        return QuestionGroup::model()->findAll("sid=:sid and language=:lang",
            array(':sid'=>$this->sid,
                ':lang'=>$this->survey->language));
        //return QuestionGroup::model()->getGroups($this->sid);
    }

    public function getbuttons()
    {

        $url         = Yii::app()->createUrl("/admin/questions/sa/view/surveyid/");
        $url        .= '/'.$this->sid.'/gid/'.$this->gid.'/qid/'.$this->qid;
        $previewUrl  = Yii::app()->createUrl("survey/index/action/previewquestion/sid/");
        $previewUrl .= '/'.$this->sid.'/gid/'.$this->gid.'/qid/'.$this->qid;
        $editurl     = Yii::app()->createUrl("admin/questions/sa/editquestion/surveyid/$this->sid/gid/$this->gid/qid/$this->qid");
        $button      = '<a class="btn btn-default open-preview"  data-toggle="tooltip" title="'.gT("Question preview").'"  aria-data-url="'.$previewUrl.'" aria-data-sid="'.$this->sid.'" aria-data-gid="'.$this->gid.'" aria-data-qid="'.$this->qid.'" aria-data-language="'.$this->language.'" href="#" role="button" ><span class="fa fa-eye"  ></span></a> ';

        if (Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'update')) {
            $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Edit question").'" href="'.$editurl.'" role="button"><span class="fa fa-pencil" ></span></a>';
        }

        if (Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'read')) {
            $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Question summary").'" href="'.$url.'" role="button"><span class="fa fa-list-alt" ></span></a>';
        }

        $oSurvey = Survey::model()->findByPk($this->sid);
        $gid_search = Yii::app()->request->getParam('gid');

        if ($oSurvey->active != "Y" && Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'delete')) {
            $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Delete").'" href="#" role="button"'
                ." onclick='$.bsconfirm(\"".CHtml::encode(gT("Deleting  will also delete any answer options and subquestions it includes. Are you sure you want to continue?"))
                            ."\", {\"confirm_ok\": \"".gT("Yes")."\", \"confirm_cancel\": \"".gT("No")."\"}, function() {"
                            . convertGETtoPOST(Yii::app()->createUrl("admin/questions/sa/delete/", ["surveyid" => $this->sid, "qid" => $this->qid, "gid" => $gid_search]))
                        ."});'>"
                    .' <i class="text-danger fa fa-trash"></i>
                </a>';
        }

        return $button;
    }

    public function getOrderedAnswers($random = 0, $alpha = 0)
    {
        //question attribute random order set?
        if ($random == 1) {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid='$this->qid' AND language='$this->language' and scale_id=0 ORDER BY ".dbRandom();
        }

        //question attribute alphasort set?
        elseif ($alpha == 1) {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid='$this->qid' AND language='$this->language' and scale_id=0 ORDER BY answer";
        }

        //no question attributes -> order by sortorder
        else {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid='$this->qid' AND language='$this->language' and scale_id=0 ORDER BY sortorder, answer";
        }

        $ansresult = dbExecuteAssoc($ansquery)->readAll();
        return $ansresult;

    }

    /**
     * get subquestions fort the current question object in the right order
     * @param int $random
     * @param string $exclude_all_others
     * @return array
     */
    public function getOrderedSubQuestions($random = 0, $exclude_all_others = '')
    {
        $criteria = (new CDbCriteria());
        $criteria->addCondition('t.parent_qid=:qid');
        $criteria->addCondition('t.scale_id=0');
        $criteria->addCondition('t.language=:language');
        $criteria->params = [':qid'=>$this->qid, ':language'=>$this->language];
        $criteria->order = ($random == 1 ? (new CDbExpression(dbRandom())) : 'question_order ASC');
        $ansresult = Question::model()->findAll($criteria);

        //if  exclude_all_others is set then the related answer should keep its position at all times
        //thats why we have to re-position it if it has been randomized
        if (trim($exclude_all_others) != '' && $random == 1) {
            $position = 0;
            foreach ($ansresult as $answer) {
                if (($answer['title'] == trim($exclude_all_others))) {
                    if ($position == $answer['question_order'] - 1) {
//already in the right position
                        break;
                    }
                    $tmp = array_splice($ansresult, $position, 1);
                    array_splice($ansresult, $answer['question_order'] - 1, 0, $tmp);
                    break;
                }
                $position++;
            }
        }
        return $ansresult;
    }

    public function getMandatoryIcon()
    {
        if ($this->type != "X" && $this->type != "|") {
            $sIcon = ($this->mandatory == "Y") ? '<span class="fa fa-asterisk text-danger"></span>' : '<span></span>';
        } else {
            $sIcon = '<span class="fa fa-ban text-danger" data-toggle="tooltip" title="'.gT('Not relevant for this question type').'"></span>';
        }
        return $sIcon;
    }

    public function getOtherIcon()
    {

        if (($this->type == "L") || ($this->type == "!") || ($this->type == "P") || ($this->type == "M")) {
            $sIcon = ($this->other === "Y") ? '<span class="fa fa-dot-circle-o"></span>' : '<span></span>';
        } else {
            $sIcon = '<span class="fa fa-ban text-danger" data-toggle="tooltip" title="'.gT('Not relevant for this question type').'"></span>';
        }
        return $sIcon;
    }

    /**
     * Get an new title/code for a question
     * @param integer $index base for question code (exemple : inde of question when survey import)
     * @return string|null : new title, null if impossible
     */
    public function getNewTitle($index = 0)
    {
        $sOldTitle = $this->title;
        if ($this->validate(array('title'))) {
            return $sOldTitle;
        }
        /* Maybe it's an old invalid title : try to fix it */
        $sNewTitle = preg_replace("/[^A-Za-z0-9]/", '', $sOldTitle);
        if (is_numeric(substr($sNewTitle, 0, 1))) {
            $sNewTitle = 'q'.$sNewTitle;
        }
        /* Maybe there are another question with same title try to fix it 10 times */
        $attempts = 0;
        while (!$this->validate(array('title'))) {
            $rand = mt_rand(0, 1024);
            $sNewTitle = 'q'.$index.'r'.$rand;
            $this->title = $sNewTitle;
            $attempts++;
            if ($attempts > 10) {
                $this->addError('title', 'Failed to resolve question code problems after 10 attempts.');
                return null;
            }
        }
        return $sNewTitle;
    }

    public function search()
    {
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);

        $sort = new CSort();
        $sort->attributes = array(
            'question_id'=>array(
                'asc'=>'t.qid asc',
                'desc'=>'t.qid desc',
            ),
            'question_order'=>array(
                'asc'=>'groups.group_order asc, t.question_order asc',
                'desc'=>'groups.group_order desc,t.question_order desc',
            ),
            'title'=>array(
                'asc'=>'t.title asc',
                'desc'=>'t.title desc',
            ),
            'question'=>array(
                'asc'=>'t.question asc',
                'desc'=>'t.question desc',
            ),

            'group'=>array(
                'asc'=>'groups.group_name asc',
                'desc'=>'groups.group_name desc',
            ),

            'mandatory'=>array(
                'asc'=>'t.mandatory asc',
                'desc'=>'t.mandatory desc',
            ),

            'other'=>array(
                'asc'=>'t.other asc',
                'desc'=>'t.other desc',
            ),
        );

        $sort->defaultOrder = array(
            'question_order' => CSort::SORT_ASC,
        );

        $criteria = new CDbCriteria;
        $criteria->with = array('groups');
        $criteria->compare("t.sid", $this->sid, false, 'AND');
        $criteria->compare("t.language", $this->language, false, 'AND');
        $criteria->compare("t.parent_qid", 0, false, 'AND');

        $criteria2 = new CDbCriteria;
        $criteria2->compare('t.title', $this->title, true, 'OR');
        $criteria2->compare('t.question', $this->title, true, 'OR');
        $criteria2->compare('t.type', $this->title, true, 'OR');
        /* search id exactly */
        if(is_numeric($this->title)) {
            $criteria2->compare('t.qid', $this->title, false, 'OR');
        }
        if ($this->gid != '' and is_numeric($this->gid)) {
            $criteria->compare('groups.gid', $this->gid, false, 'AND');
        }

        $criteria->mergeWith($criteria2, 'AND');

        $dataProvider = new CActiveDataProvider('Question', array(
            'criteria'=>$criteria,
            'sort'=>$sort,
            'pagination'=>array(
                'pageSize'=>$pageSize,
            ),
        ));
        return $dataProvider;
    }

    /**
     * Make sure we don't save a new question group
     * while the survey is active.
     *
     * @return bool
     */
    protected function beforeSave()
    {
        if (parent::beforeSave()) {
            $surveyIsActive = Survey::model()->findByPk($this->sid)->active !== 'N';
            if ($surveyIsActive && $this->getIsNewRecord()) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }


    /**
     * Fix sub question of a parent question
     * Must be call after base language subquestion is set
     * @todo : move other fix here ?
     * @return void
     */
    public function fixSubQuestions()
    {
        if ($this->parent_qid) {
            return;
        }
        $oSurvey = $this->survey;

        /* Delete sub question in all other language */
        $criteria = new CDbCriteria;
        $criteria->compare('parent_qid', $this->qid);
        $criteria->addNotInCondition('language', $oSurvey->getAllLanguages());
        Question::model()->deleteAll($criteria); // Must log count of deleted ?

        /* Delete invalid subquestions (not in primary language */
        $validSubQuestion = Question::model()->findAll(array(
            'select'=>'title',
            'condition'=>'parent_qid=:parent_qid AND language=:language',
            'params'=>array('parent_qid' => $this->qid, 'language' => $oSurvey->language)
        ));
        $criteria = new CDbCriteria;
        $criteria->compare('parent_qid', $this->qid);
        $criteria->addNotInCondition('title', CHtml::listData($validSubQuestion, 'title', 'title'));
        Question::model()->deleteAll($criteria); // Must log count of deleted ?
    }
    /** @return string[] */
    public static function getQuotableTypes()
    {
        return array('G', 'M', 'Y', 'A', 'B', 'I', 'L', 'O', '!', '*');
    }


    public function getBasicFieldName()
    {
        if ($this->parent_qid != 0) {
            return "{$this->sid}X{$this->gid}X{$this->parent_qid}";
        } else {
            return "{$this->sid}X{$this->gid}X{$this->qid}";
        }
    }

    /**
     * @return QuestionAttribute[]
     */
    public function getQuestionAttributes()
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('qid=:qid');
        $criteria->params = [':qid'=>$this->qid];
        return QuestionAttribute::model()->findAll($criteria);
    }

    /**
     * @param array $data
     * @return boolean|null
     */
    public function insertRecords($data)
    {
        $oRecord = new self;
        foreach ($data as $k => $v) {
            $oRecord->$k = $v;
        }
        if ($oRecord->validate()) {
            return $oRecord->save();
        }
        Yii::log(\CVarDumper::dumpAsString($oRecord->getErrors()), 'warning', 'application.models.Question.insertRecords');
    }

}
