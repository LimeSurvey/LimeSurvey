<?php

/**
 * LimeSurvey
 * Copyright (C) 2013-2022 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

use LimeSurvey\Helpers\questionHelper;

/**
 * Class Question
 *
 * @property integer $qid Question ID.
 * @property integer $sid Survey ID
 * @property integer $gid QuestionGroup ID where question is displayed
 * @property string $type
 * @property string $title Question Code
 * @property string $preg
 * @property string $other Other option enabled for question (Y/N)
 * @property string $mandatory Whether question is mandatory (Y/S/N)
 * @property string $encrypted Whether question is encrypted (Y/N)
 * @property string $question_theme_name
 * @property integer $question_order Question order in greoup
 * @property integer $parent_qid Questions parent question ID eg for subquestions
 * @property integer $scale_id  The scale ID
 * @property integer $same_default Saves if user set to use the same default value across languages in default options dialog ('Edit default answers')
 * @property string $relevance Questions relevane equation
 * @property string $modulename
 * @property integer $same_script Whether the same script should be used for all languages
 *
 * @property   Survey $survey
 * @property   QuestionGroup $group
 * @property   Question $parent
 * @property   Question[] $subquestions
 * @property   QuestionAttribute[] $questionAttributes NB! returns all QuestionArrtibute Models fot this QID regardless of the specified language
 * @property   QuestionL10n[] $questionl10ns Question Languagesettings indexd by language code
 * @property   string[] $quotableTypes Question types that can be used for quotas
 * @property   Answer[] $answers
 * @property   QuestionType $questionType
 * @property   array $allSubQuestionIds QID-s of all question subquestions, empty array returned if no subquestions
 * @inheritdoc
 */
class Question extends LSActiveRecord
{
    const QT_1_ARRAY_DUAL = '1'; // Array Dual scale
    const QT_5_POINT_CHOICE = '5';
    const QT_A_ARRAY_5_POINT = 'A'; // Array of 5 point choice questions
    const QT_B_ARRAY_10_CHOICE_QUESTIONS = 'B'; // Array of 10 point choice questions
    const QT_C_ARRAY_YES_UNCERTAIN_NO = 'C'; // ARRAY OF YES\No\gT("Uncertain") QUESTIONS
    const QT_D_DATE = 'D';
    const QT_E_ARRAY_INC_SAME_DEC = 'E';
    const QT_F_ARRAY = 'F';
    const QT_G_GENDER = 'G';
    const QT_H_ARRAY_COLUMN = 'H';
    const QT_I_LANGUAGE = 'I';
    const QT_K_MULTIPLE_NUMERICAL = 'K';
    const QT_L_LIST = 'L';
    const QT_M_MULTIPLE_CHOICE = 'M';
    const QT_N_NUMERICAL = 'N';
    const QT_O_LIST_WITH_COMMENT = 'O';
    const QT_P_MULTIPLE_CHOICE_WITH_COMMENTS = 'P';
    const QT_Q_MULTIPLE_SHORT_TEXT = 'Q';
    const QT_R_RANKING = 'R';
    const QT_S_SHORT_FREE_TEXT = 'S';
    const QT_T_LONG_FREE_TEXT = 'T';
    const QT_U_HUGE_FREE_TEXT = 'U';
    const QT_X_TEXT_DISPLAY = 'X';
    const QT_Y_YES_NO_RADIO = 'Y';
    const QT_EXCLAMATION_LIST_DROPDOWN = '!';
    const QT_VERTICAL_FILE_UPLOAD = '|';
    const QT_ASTERISK_EQUATION = '*';
    const QT_COLON_ARRAY_NUMBERS = ':';
    const QT_SEMICOLON_ARRAY_TEXT = ';';

    const ORDER_TYPES_SUBQUESTION = [
        self::QT_M_MULTIPLE_CHOICE,
        self::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS
    ];
    const ORDER_TYPES_ANSWER = [
        self::QT_L_LIST,
        self::QT_R_RANKING,
        self::QT_EXCLAMATION_LIST_DROPDOWN,
        self::QT_O_LIST_WITH_COMMENT
    ];

    const START_SORTING_VALUE = 1; //this is the start value for question_order

    const DEFAULT_QUESTION_THEME = 'core';  // The question theme name to use when no theme is specified

    /**
     * Stores the active group name used for question list filtering.
     *
     * @var string
     */
    public $group_name;

    public $gid;

    /**
     * Defaut relevance
     **/
    public $relevance = '';

    /**
     * Default value for same_script; prevents breaking public behavior during updates.
     *
     * @var int
     */
    public $same_script = 0;

    /**
     * Cached question theme instance.
     *
     * @var QuestionTheme
     */
    private $relatedQuestionTheme;

    /**
     * Returns the static model instance of the specified AR class.
     *
     * @param string $className The active record class name.
     *
     * @inheritdoc
     *
     * @return Question The static model instance.
     */
    public static function model($className = __CLASS__)
    {
        /**
         * The static model instance.
         *
         * @var self $model
         */
        $model = parent::model($className);
        return $model;
    }

    /**
     * Returns the database table name for the Question model.
     *
     * @inheritdoc
     *
     * @return string The table name.
     */
    public function tableName()
    {
        return '{{questions}}';
    }

    /**
     * Returns the primary key for the Question model.
     *
     * @inheritdoc
     *
     * @return string The primary key column name.
     */
    public function primaryKey()
    {
        return 'qid';
    }

    /**
     * Defines the relations for the Question ActiveRecord model.
     *
     * @inheritdoc
     *
     * @return array The relational rules for this model.
     */
    public function relations()
    {
        return array(
            'survey' => array(self::BELONGS_TO, 'Survey', 'sid'),
            'group' => array(self::BELONGS_TO, 'QuestionGroup', 'gid', 'together' => true),
            'parent' => array(self::HAS_ONE, 'Question', array("qid" => "parent_qid")),
            'questionattributes' => array(self::HAS_MANY, 'QuestionAttribute', 'qid'),
            'questionl10ns' => array(self::HAS_MANY, 'QuestionL10n', 'qid', 'together' => true),
            'subquestions' => array(
                self::HAS_MANY,
                'Question',
                array('parent_qid' => 'qid'),
                'order' => 'subquestions.question_order ASC',
                'together' => false
            ),
            'conditions' => array(self::HAS_MANY, 'Condition', 'qid'),
            'answers' => array(self::HAS_MANY, 'Answer', 'qid'),
            // This relation will fail for non saved questions, which is often the case
            // when using question editor on create mode. Better use getQuestionTheme()
            'question_theme' => [self::HAS_ONE, 'QuestionTheme', ['question_type' => 'type', 'name' => 'question_theme_name']],
        );
    }

    /**
     * Returns a related AR object or list of objects.
     *
     * Overrides the default Yii getRelated() to use Survey::findByPkCache when
     * requesting the 'survey' relation without refresh or extra parameters.
     *
     * @param string $name    The relation name to retrieve.
     * @param bool   $refresh Whether to reload the relation from the database.
     * @param array  $params  Additional parameters for the relation query.
     *
     * @return mixed The related ActiveRecord object(s), or null if not found.
     *
     * @inheritdoc
     */
    public function getRelated($name, $refresh = false, $params = array())
    {
        if ($name == 'survey' && !$refresh && empty($params)) {
            return Survey::model()->findByPk($this->sid);
        }
        return parent::getRelated($name, $refresh, $params);
    }

    /**
     * Defines the validation rules for the Question model.
     *
     * @inheritdoc
     *
     * @return array Validation rules for this model.
     *
     * TODO: make it easy to read (if possible)
     */
    public function rules()
    {
        /* Basic rules */
        $aRules = array(
            array('title', 'required', 'on' => 'update, insert, saveall', 'message' => gT('The question code is mandatory.', 'unescaped')),
            array('title', 'length', 'min' => 1, 'max' => 20, 'on' => 'update, insert, saveall'),
            array('qid,sid,gid,parent_qid', 'numerical', 'integerOnly' => true),
            array('qid', 'unique','message' => sprintf(gT("Question id (qid) : '%s' is already in use."), $this->qid)),// Still needed ?
            array('other', 'in', 'range' => array('Y', 'N'), 'allowEmpty' => true),
            array('mandatory', 'in', 'range' => array('Y', 'S', 'N'), 'allowEmpty' => true),
            array('encrypted', 'in', 'range' => array('Y', 'N'), 'allowEmpty' => true),
            array('question_order', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('scale_id', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('same_default', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('type', 'length', 'min' => 1, 'max' => 1),
            array('relevance', 'LSYii_FilterValidator', 'filter' => 'trim', 'skipOnEmpty' => true),
            array('preg', 'safe'),
            array('modulename', 'length', 'max' => 255),
            array('same_script', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
        );
        /* Filtering */
        /* other must be no when other is not allowed */
        $aRules[] = array('other', 'filter', 'filter' => function ($value) {
            if ($this->getAllowOther()) {
                return $value;
            }
            return 'N';
        });
        /* Don't save empty or 'core' question theme name */
        $aRules[] = ['question_theme_name', 'filter', 'filter' =>  [$this, 'questionThemeNameValidator'] ];
        /* Specific rules to avoid collapse with column name in database */
        if ($this->parent_qid) {
            /* Subquestion specific rules */
            /* unicity of title by scale */
            $aRules[] = array('title', 'unique', 'caseSensitive' => false,
                'criteria' => array(
                    'condition' => 'sid=:sid AND parent_qid=:parent_qid and scale_id=:scale_id',
                    'params' => array(
                        ':sid' => $this->sid,
                        ':parent_qid' => $this->parent_qid,
                        ':scale_id' => $this->scale_id
                        )
                    ),
                    'message' => gT('Subquestion codes must be unique.'),
                    'except' => 'saveall'
            );
            /* Disallow other title if question allow other */
            $oParentQuestion = Question::model()->findByPk(array("qid" => $this->parent_qid));
            if ($oParentQuestion->other == "Y") {
                $aRules[] = array(
                    'title',
                    'LSYii_CompareInsensitiveValidator',
                    'compareValue' => 'other',
                    'operator' => '!=',
                    'message' => sprintf(gT("'%s' can not be used if the 'Other' option for this question is activated."), "other"),
                    'except' => 'archiveimport'
                );
            }
            /* #14495: comment suffix can't be used with P Question */
            if ($oParentQuestion->type == "P") {
                $aRules[] = array('title', 'match', 'pattern' => '/comment$/', 'not' => true, 'message' => gT("'comment' suffix can not be used with multiple choice with comments."));
            }
        } else {
            /* Question specific rules*/
            if ($this->getHasOtherSubquestions()) {
                // Disallow other if sub question have 'other' for title
                $aRules[] = array('other', 'compare', 'compareValue' => 'Y', 'operator' => '!=', 'message' => sprintf(gT("'%s' can not be used if the 'Other' option for this question is activated."), 'other'));
            }
        }
        if ($this->survey->isActive) {
            $aRules = array_merge($aRules, $this->rulesForActiveSurvey());
        }
        /* When question exist and are already set with title, allow keep bad title */
        if (!$this->isNewRecord) {
            $oActualValue = Question::model()->findByPk(array("qid" => $this->qid));
            if ($oActualValue && $oActualValue->title == $this->title) {
                /* We don't change title, then don't put rules on title */
                /* We don't want to broke existing survey,  We only disallow to set it or update it according to this value */
                return $aRules;
            }
        }
        /**
         * Question was new or title was updated : we add minor rules.
         * This rules don't broke DB, only potential “ExpressionScript Engine” issue.
         * usage of 'archiveimport' scenaruio for import LSA (survey archive) file
         **/
        if (empty($this->parent_qid)) {
            /* Unicity for ExpressionManager */
            $aRules[] = array('title', 'unique', 'caseSensitive' => true,
                'criteria' => array(
                    'condition' => 'sid=:sid AND parent_qid=0',
                    'params' => array(
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
                'message' => sprintf(gT("Code: '%s' is a reserved word."), $this->title), // Usage of {attribute} need attributeLabels, {value} never exist in message
                'except' => 'archiveimport'
            );
        } else {
            $aRules[] = array(
                'title', 'compare', 'compareValue' => 'time', 'operator' => '!=',
                'message' => gT("'time' is a reserved word and can not be used for a subquestion."),
                'except' => 'archiveimport'
            );
            $aRules[] = array(
                'title', 'match', 'pattern' => '/^[a-zA-z0-9]*$/',
                'message' => gT('Subquestion codes may only contain alphanumeric characters.'),
                'except' => 'archiveimport'
            );
        }
        return $aRules;
    }

    /**
     * Returns validation rules that apply when the survey is active.
     *
     * @return array Validation rules for active surveys.
     */
    private function rulesForActiveSurvey()
    {
        $aRules = array();
        /* can not update group */
        $aRules[] = array('gid', 'LSYii_DisableUpdateValidator');
        return $aRules;
    }

    /**
     * Rewrites the sort order for all top-level questions in a group.
     *
     * Questions in the given group for the given survey are reordered sequentially
     * based on their current question_order value.
     *
     * @param int $gid      The question group ID.
     * @param int $surveyid The survey ID the group belongs to.
     *
     * @return void
     */
    public static function updateSortOrder($gid, $surveyid)
    {
        $questions = self::model()->findAllByAttributes(
            array('gid' => $gid, 'sid' => $surveyid, 'parent_qid' => 0),
            array('order' => 'question_order')
        );
        $p = 0;
        foreach ($questions as $question) {
            $question->question_order = $p;
            $question->save();
            $p++;
        }
    }


    /**
     * Fixes the sort order for questions in a group.
     *
     * All top-level questions (parent_qid = 0) in the given group will be assigned
     * a sequential question_order value starting from the specified starting value.
     *
     * @param int $gid           The question group ID whose questions should be reordered.
     * @param int $startingOrder The starting question_order value.
     *
     * @return void
     */
    public function updateQuestionOrder($gid, $startingOrder = 1)
    {
        $data = Yii::app()->db->createCommand()->select('qid')
            ->where(array('and', 'gid=:gid', 'parent_qid=0'))
            ->order('question_order, title ASC')
            ->from('{{questions}}')
            ->bindParam(':gid', $gid, PDO::PARAM_INT)
            ->query();

        $position = intval($startingOrder);
        foreach ($data->readAll() as $row) {
            Yii::app()->db->createCommand()->update(
                $this->tableName(),
                array('question_order' => $position),
                'qid=' . $row['qid']
            );
            $position++;
        }
    }

    /**
     * This function returns an array of the advanced attributes for the particular question
     * including their values set in the database
     *
     * @param string|null $sLanguage              If you give a language then only the attributes for that language are returned
     * @param string|null $sQuestionThemeOverride Name of the question theme to use instead of the question's current theme
     *
     * @return array
     */
    public function getAdvancedSettingsWithValues($sLanguage = null, $sQuestionThemeOverride = null)
    {
        $questionAttributeHelper = new LimeSurvey\Models\Services\QuestionAttributeHelper();
        $aAttributes = $questionAttributeHelper->getQuestionAttributesWithValues($this, $sLanguage, $sQuestionThemeOverride, true);
        return $aAttributes;
    }

    /**
     * Adds custom template attributes to the base attribute list and removes
     * attributes with an empty input type.
     *
     * If the question uses a non-core question template that defines custom
     * attributes, those attributes are merged into the given attribute definition
     * list. Attributes with an empty input type are removed. If no changes apply,
     * the original attribute list is returned unchanged.
     *
     * @param array    $aAttributeNames  Base attribute definitions from getQuestionAttributesSettings($sType).
     * @param array    $aAttributeValues Attribute values, including 'question_template'.
     * @param Question $oQuestion        The question instance used to resolve template attributes.
     *
     * @return array The (possibly modified) attribute definition list.
     *
     * @deprecated Use QuestionTheme::getAdditionalAttrFromExtendedTheme() to retrieve
     *             question theme attributes and QuestionAttributeHelper->mergeQuestionAttributes()
     *             to merge them with base attributes.
     */
    public static function getQuestionTemplateAttributes($aAttributeNames, $aAttributeValues, $oQuestion)
    {
        if (isset($aAttributeValues['question_template']) && ($aAttributeValues['question_template'] != 'core')) {
            if (empty($oQuestion)) {
                throw new Exception('oQuestion cannot be empty');
            }
            $oQuestionTemplate = QuestionTemplate::getInstance($oQuestion);
            if ($oQuestionTemplate->bHasCustomAttributes) {
                // Add the custom attributes to the list
                foreach ($oQuestionTemplate->oConfig->attributes->attribute as $attribute) {
                    $sAttributeName = (string)$attribute->name;
                    $sInputType = (string)$attribute->inputtype;
                    // remove attribute if inputtype is empty
                    if (empty($sInputType)) {
                        unset($aAttributeNames[$sAttributeName]);
                    } else {
                        $aCustomAttribute = json_decode(json_encode((array)$attribute), 1);
                        $aCustomAttribute = array_merge(
                            QuestionAttribute::getDefaultSettings(),
                            array("category" => gT("Template")),
                            $aCustomAttribute
                        );
                        $aAttributeNames[$sAttributeName] = $aCustomAttribute;
                    }
                }
            }
        }
        return $aAttributeNames;
    }

    /**
     * Returns the type group for this question.
     *
     * This method is currently unused and not implemented. It exists for legacy
     * compatibility with older LimeSurvey code paths.
     *
     * @return mixed|null Always returns null until implemented.
     */
    public function getTypeGroup()
    {
    }

    /**
     * Retrieves all top-level questions for a given survey and question group.
     *
     * TODO: replace this function call by $oSurvey->questions defining a relation in SurveyModel.
     *
     * @param int $sid The survey ID to fetch questions for.
     * @param int $gid The question group ID within the survey.
     *
     * @return CDbDataReader The data reader for the matching questions.
     */
    public function getQuestions($sid, $gid)
    {
        return Yii::app()->db->createCommand()
            ->select()
            ->from(self::tableName())
            ->where(array('and', 'sid=:sid', 'gid=:gid', 'parent_qid=0'))
            ->order('question_order asc')
            ->bindParam(":sid", $sid, PDO::PARAM_INT)
            ->bindParam(":gid", $gid, PDO::PARAM_INT)
            //->bindParam(":language", $language, PDO::PARAM_STR)
            ->query();
    }

    /**
     * Deletes one or multiple questions and all their related records.
     *
     * @param mixed $questionsIds A single question ID or an array of question IDs to delete.
     *
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
     * Deletes a question and ALL its relations (subquestions, answers, etc, etc)
     *
     * @return bool
     * @throws CDbException
     */
    public function delete()
    {
        $ids = array_merge([$this->qid], $this->allSubQuestionIds);
        $qidsCriteria = (new CDbCriteria())->addInCondition('qid', $ids);

        self::model()->deleteAll((new CDbCriteria())->addInCondition('parent_qid', $ids));
        QuestionAttribute::model()->deleteAll($qidsCriteria);
        QuestionL10n::model()->deleteAll($qidsCriteria);
        QuotaMember::model()->deleteAll($qidsCriteria);
        Condition::model()->deleteAll($qidsCriteria);

        // delete defaultvalues and defaultvalueL10ns
        $oDefaultValues = DefaultValue::model()->findAll((new CDbCriteria())->addInCondition('qid', $ids));
        foreach ($oDefaultValues as $defaultvalue) {
            DefaultValue::model()->deleteAll('dvid = :dvid', array(':dvid' => $defaultvalue->dvid));
            DefaultValueL10n::model()->deleteAll('dvid = :dvid', array(':dvid' => $defaultvalue->dvid));
        }

        $this->deleteAllAnswers();
        $this->removeFromLastVisited();

        if (parent::delete()) {
            Question::model()->updateQuestionOrder($this->gid);
            return true;
        }
        return false;
    }

    /**
     * Removes this question from the user's list of last visited questions.
     *
     * @return void
     */
    public function removeFromLastVisited()
    {
        $oCriteria = new CDbCriteria();
        $oCriteria->compare('stg_name', 'last_question');
        $oCriteria->compare('stg_value', $this->qid);
        SettingsUser::model()->deleteAll($oCriteria);
    }

    /**
     * Delete all subquestions that belong to this question.
     *
     * @param ?array $exceptIds Don't delete subquestions with these ids.
     *
     * @return void
     */
    public function deleteAllSubquestions($exceptIds = [])
    {
        $ids = !empty($exceptIds)
            ? array_diff($this->allSubQuestionIds, $exceptIds)
            : $this->allSubQuestionIds;

        $questions = Question::model()->findAll((new CDbCriteria())->addInCondition('qid', $ids));
        foreach ($questions as $question) {
            $question->delete();
        }
    }

    /**
     * Deletes all answers for this question and its subquestions.
     *
     * @param array $exceptIds Answer IDs that should not be deleted.
     *
     * @return void
     */
    public function deleteAllAnswers(array $exceptIds = [])
    {
        $ids = array_merge([$this->qid], $this->allSubQuestionIds);
        $qidsCriteria = (new CDbCriteria())->addInCondition('qid', $ids);
        $qidsCriteria->addNotInCondition('aid', $exceptIds);
        $answers = Answer::model()->findAll($qidsCriteria);
        if (!empty($answers)) {
            foreach ($answers as $answer) {
                $answerId = $answer->aid;
                if ($answer->delete()) {
                    AnswerL10n::model()->deleteAllByAttributes(['aid' => $answerId]);
                }
            }
        }
    }

    /**
     * Retrieves question records (including translations) for use in statistics.
     *
     * This is a legacy helper and should be replaced by
     * Answer::model()->findAll() with an explicit Criteria object.
     *
     * @param string       $fields    Unused legacy parameter (kept for backward compatibility).
     * @param mixed        $condition The condition passed to the findAll() query.
     * @param string|false $orderby   Optional SQL ORDER BY clause. If false, no ordering is applied.
     *
     * @return array An array of question attributes merged with their localized attributes.
     *
     * @todo Replace with Answer::model()->findAll([Criteria]) everywhere.
     */
    public function getQuestionsForStatistics($fields, $condition, $orderby = false)
    {
        if ($orderby === false) {
            $oQuestions = Question::model()->with('questionl10ns')->findAll(array('condition' => $condition));
        } else {
            $oQuestions = Question::model()->with('questionl10ns')->findAll(array('condition' => $condition, 'order' => $orderby));
        }
        $arr = array();
        foreach ($oQuestions as $key => $question) {
            $arr[$key] = array_merge($question->attributes, current($question->questionl10ns)->attributes);
        }
        return $arr;
    }

    /**
     * Returns all questions of the given survey, ordered by group and question order.
     *
     * @param int $surveyid The ID of the survey to fetch questions for.
     *
     * @return Question[] List of question models belonging to the survey.
     */
    public function getQuestionList($surveyid)
    {
        return Question::model()
            ->with('group')
            ->findAll(
                array(
                    'condition' => 't.sid=:sid',
                    /* table name not needed , see #17777 */
                    'order'     => 'group_order,question_order',
                    'params'    => array(':sid' => $surveyid)
                )
            );
    }

    /**
     * Returns the description for the current question type.
     *
     * @return string The question type description.
     *
     * @deprecated This method is no longer used and is based on deprecated functionality.
     *             Use $this->questionType->description instead.
     */
    public function getTypedesc()
    {
        $types = self::typeList();
        $typeDesc = $types[$this->type]["description"];

        if (YII_DEBUG) {
            $typeDesc .= ' <em>' . $this->type . '</em>';
        }

        return $typeDesc;
    }

    /**
     * Returns the question type definitions.
     *
     * Explanation of the question type array:
     *  - description   : Question description
     *  - subquestions  : 0 = does not support subquestions, x = number of subquestion scales
     *  - answerscales  : 0 = does not need answers, x = number of answer scales
     *                    (usually 1, but e.g. for dual scale questions set to 2)
     *  - assessable    : 0 = does not support assessment values when editing answers,
     *                    1 = supports assessment values
     *
     * @param string|null $language Language code used for translation of labels.
     *
     * @return array The question type definitions.
     *
     * @deprecated Use QuestionTheme::findQuestionMetaDataForAllTypes() instead.
     */
    public static function typeList($language = null)
    {
        $QuestionTypes = QuestionType::modelsAttributes($language);

        /**
 * Checks whether sorting the question types has any effect.
 *
 * @todo Verify if this actually does anything, since the values are arrays.
 */
        asort($QuestionTypes);

        return $QuestionTypes;
    }

    /**
     * Returns the human-readable name for the given question type.
     *
     * @param string $sType The question type identifier.
     *
     * @return string The question type name/description.
     *
     * @deprecated Use $this->questionType->description instead.
     *
     * @todo Move this logic to QuestionType or typeList.
     */
    public static function getQuestionTypeName($sType)
    {
        $typeList = self::typeList();
        return $typeList[$sType]['description'];
    }

    /**
     * Returns the CSS class name associated with the given question type.
     *
     * This class is used for rendering and layout in the question container.
     *
     * @param string $sType The question type identifier.
     *
     * @return string The CSS class name for the question type.
     *
     * @todo Move this logic to QuestionType or typeList.
     */
    public static function getQuestionClass($sType)
    {
        switch ($sType) {
            case Question::QT_1_ARRAY_DUAL:
                return 'array-flexible-dual-scale';
            case Question::QT_5_POINT_CHOICE:
                return 'choice-5-pt-radio';
            case Question::QT_A_ARRAY_5_POINT:
                return 'array-5-pt';
            case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:
                return 'array-10-pt';
            case Question::QT_C_ARRAY_YES_UNCERTAIN_NO:
                return 'array-yes-uncertain-no';
            case Question::QT_D_DATE:
                return 'date';
            case Question::QT_E_ARRAY_INC_SAME_DEC:
                return 'array-increase-same-decrease';
            case Question::QT_F_ARRAY:
                return 'array-flexible-row';
            case Question::QT_G_GENDER:
                return 'gender';
            case Question::QT_H_ARRAY_COLUMN:
                return 'array-flexible-column';
            case Question::QT_I_LANGUAGE:
                return 'language';
            case Question::QT_K_MULTIPLE_NUMERICAL:
                return 'numeric-multi';
            case Question::QT_L_LIST:
                return 'list-radio';
            case Question::QT_M_MULTIPLE_CHOICE:
                return 'multiple-opt';
            case Question::QT_N_NUMERICAL:
                return 'numeric';
            case Question::QT_O_LIST_WITH_COMMENT:
                return 'list-with-comment';
            case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:
                return 'multiple-opt-comments';
            case Question::QT_Q_MULTIPLE_SHORT_TEXT:
                return 'multiple-short-txt';
            case Question::QT_R_RANKING:
                return 'ranking';
            case Question::QT_S_SHORT_FREE_TEXT:
                return 'text-short';
            case Question::QT_T_LONG_FREE_TEXT:
                return 'text-long';
            case Question::QT_U_HUGE_FREE_TEXT:
                return 'text-huge';
            case Question::QT_X_TEXT_DISPLAY:
                return 'boilerplate';
            case Question::QT_Y_YES_NO_RADIO:
                return 'yes-no';
            case Question::QT_EXCLAMATION_LIST_DROPDOWN:
                return 'list-dropdown';
            case Question::QT_COLON_ARRAY_NUMBERS:
                return 'array-multi-flexi';
            case Question::QT_SEMICOLON_ARRAY_TEXT:
                return 'array-multi-flexi-text';
            case Question::QT_VERTICAL_FILE_UPLOAD:
                return 'upload-files';
            case Question::QT_ASTERISK_EQUATION:
                return 'equation';
            default:
                return 'generic_question'; // fallback
        };
    }

    /**
     * Returns the rendered action buttons for this question in the question list.
     *
     * The buttons are rendered via the GridActionsWidget and include actions such as
     * edit, preview, summary and delete, depending on the current permissions.
     *
     * @return string HTML markup for the question action buttons dropdown.
     */
    public function getbuttons()
    {
        $url         = App()->createUrl("questionAdministration/view/surveyid/$this->sid/gid/$this->gid/qid/$this->qid");
        $previewUrl  = Yii::app()->createUrl("survey/index/action/previewquestion/sid/");
        $previewUrl .= '/' . $this->sid . '/gid/' . $this->gid . '/qid/' . $this->qid;
        $editurl     = Yii::app()->createUrl("questionAdministration/edit/questionId/$this->qid/tabOverviewEditor/editor");

        $permission_edit_question = Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'update');
        $permission_summary_question = Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'read');
        $permission_delete_question = Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'delete');

        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Edit question'),
            'iconClass'        => 'ri-pencil-fill',
            'url'              => $editurl,
            'enabledCondition' => $permission_edit_question
        ];
        $dropdownItems[] = [
            'title'            => gT('Question preview'),
            'iconClass'        => 'ri-eye-fill',
            'linkClass'        => 'open-preview',
            'linkAttributes'   => [
                'data-bs-toggle' => 'tooltip',
                'aria-data-url' => $previewUrl,
                'aria-data-sid' => $this->sid,
                'aria-data-gid' => $this->gid,
                'aria-data-qid' => $this->qid,
                'aria-data-language' => $this->survey->language
            ]
        ];
        $dropdownItems[] = [
            'title'            => gT('Question summary'),
            'iconClass'        => 'ri-list-unordered',
            'url'              => $url,
            'enabledCondition' => $permission_summary_question,
            'linkAttributes'   => [
                'data-bs-toggle' => 'tooltip',
            ]
        ];

        $oSurvey = Survey::model()->findByPk($this->sid);
        $surveyIsNotActive = $oSurvey->active !== 'Y';
        $dropdownItems[] = [
            'title'            => gT('Delete question'),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => $surveyIsNotActive && $permission_delete_question,
            'linkAttributes'   => [
                'data-bs-toggle' => 'tooltip',
                'onclick' => '$.fn.bsconfirm("'
                    . CHtml::encode(gT("Deleting will also delete any answer options and subquestions it includes. Are you sure you want to continue?"))
                    . '", {"confirm_ok": "'
                    . gT("Delete")
                    . '", "confirm_cancel": "'
                    . gT("Cancel")
                    . '"}, function() {'
                    . convertGETtoPOST(Yii::app()->createUrl("questionAdministration/delete/", ["qid" => $this->qid]))
                    . "});"
            ]
        ];

        return App()->getController()->widget(
            'ext.admin.grid.GridActionsWidget.GridActionsWidget',
            ['dropdownItems' => $dropdownItems],
            true
        );
    }

    /**
     * Returns the ordered answers for this question.
     *
     * If a scale ID is provided, only answers for that scale are returned. Otherwise,
     * answers for all scales are grouped and ordered according to the question settings.
     *
     * @param int|null    $scale_id Optional scale ID to filter answers by.
     * @param string|null $language Optional language code used when ordering alphabetically.
     *
     * @return Answer[]|array<int, Answer[]> Ordered answer options.
     */
    public function getOrderedAnswers($scale_id = null, $language = null)
    {
        //reset answers set prior to this call
        $aAnswerOptions = [
            0 => []
        ];

        foreach ($this->answers as $oAnswer) {
            if ($scale_id !== null && $oAnswer->scale_id != $scale_id) {
                continue;
            }
            $aAnswerOptions[$oAnswer->scale_id][] = $oAnswer;
        }


        if ($scale_id !== null) {
            return $aAnswerOptions[$scale_id];
        }

        $aAnswerOptions = $this->sortAnswerOptions($aAnswerOptions, $language);
        return $aAnswerOptions;
    }

    /**
     * Sorts the given answer options according to the question's ordering rules.
     *
     * Answer options may be sorted randomly, alphabetically (based on the survey language),
     * or by their internal sort order depending on the question attributes.
     *
     * @param array<int, Answer[]> $answerOptions The answer options grouped by scale ID.
     * @param string|null          $language      Optional language code used for alphabetical sorting.
     *
     * @return array<int, Answer[]> The sorted answer options.
     */
    private function sortAnswerOptions($answerOptions, $language = null)
    {
        // Sort randomly if applicable
        if ($this->shouldOrderAnswersRandomly()) {
            foreach ($answerOptions as $scaleId => $scaleArray) {
                $keys = array_keys($scaleArray);
                shuffle($keys); // See: https://forum.yiiframework.com/t/order-by-rand-and-total-posts/68099

                $sortedScaleAnswers = array();
                foreach ($keys as $key) {
                    $sortedScaleAnswers[$key] = $scaleArray[$key];
                }
                $answerOptions[$scaleId] = $sortedScaleAnswers;
            }
            return $answerOptions;
        }

        // Sort alphabetically if applicable
        if ($this->shouldOrderAnswersAlphabetically()) {
            if (empty($language) || !in_array($language, $this->survey->allLanguages)) {
                $language = $this->survey->language;
            }
            foreach ($answerOptions as $scaleId => $scaleArray) {
                $sorted = array();
                // We create an array sorted that will use the answer in the current language as value, and keep key
                foreach ($scaleArray as $key => $answer) {
                    $sorted[$key] = $answer->answerl10ns[$language]->answer;
                }
                LimeSurvey\Helpers\SortHelper::getInstance($language)->asort($sorted, LimeSurvey\Helpers\SortHelper::SORT_STRING);
                // Now, we create a new array that store the old values of $answerOptions in the order of $sorted
                $sortedScaleAnswers = array();
                foreach ($sorted as $key => $answer) {
                    $sortedScaleAnswers[] = $scaleArray[$key];
                }
                $answerOptions[$scaleId] = $sortedScaleAnswers;
            }
            return $answerOptions;
        }

        // Sort by Answer's own sort order
        foreach ($answerOptions as $scaleId => $scaleArray) {
            usort(
                $scaleArray,
                function ($a, $b) {
                    return $a->sortorder > $b->sortorder
                    ? 1
                    : ($a->sortorder < $b->sortorder ? -1 : 0);
                }
            );
            $answerOptions[$scaleId] = $scaleArray;
        }
        return $answerOptions;
    }

    /**
     * Returns true if the answer options should be ordered randomly.
     *
     * @return bool
     */
    private function shouldOrderAnswersRandomly()
    {
        // Question types supporting both Random Order and Alphabetical Order should
        // implement the 'answer_order' attribute instead of using separate attributes.
        $answerOrder = $this->getQuestionAttribute('answer_order');
        if (!is_null($answerOrder)) {
            return $answerOrder == 'random';
        }
        return $this->getQuestionAttribute('random_order') == 1 && $this->getQuestionType()->subquestions == 0;
    }

    /**
     * Returns true if the answer options should be ordered alphabetically.
     *
     * @return bool
     */
    private function shouldOrderAnswersAlphabetically()
    {
        // Question types supporting both Random Order and Alphabetical Order should
        // implement the 'answer_order' attribute instead of using separate attributes.
        $answerOrder = $this->getQuestionAttribute('answer_order');
        if (!is_null($answerOrder)) {
            return $answerOrder == 'alphabetical';
        }
        return $this->getQuestionAttribute('alphasort') == 1;
    }

    /**
     * Returns the subquestions for this question in the correct display order.
     *
     * If the question attribute "random_order" is set, the subquestions are shuffled
     * with a deterministic seed based on the survey ID. Otherwise they are ordered
     * by their question_order property.
     *
     * @param int|null $scale_id Optional scale identifier (reserved for future use).
     *
     * @return Question[] Ordered list of subquestion models.
     */
    public function getOrderedSubQuestions($scale_id = null)
    {


        //reset subquestions set prior to this call
        $aSubQuestions = [
            0 => []
        ];

        $aOrderedSubquestions = $this->subquestions;

        if ($this->getQuestionAttribute('random_order') == 1) {
            include_once Yii::app()->basePath . '/libraries/MersenneTwister.php';
            ls\mersenne\setSeed($this->sid);

            $aOrderedSubquestions = ls\mersenne\shuffle($aOrderedSubquestions);
        } else {
            usort(
                $aOrderedSubquestions,
                function ($oQuestionA, $oQuestionB) {
                    if ($oQuestionA->question_order == $oQuestionB->question_order) {
                        return 0;
                    }
                    return $oQuestionA->question_order < $oQuestionB->question_order ? -1 : 1;
                }
            );
        }


        $excludedSubquestion = null;
        foreach ($aOrderedSubquestions as $i => $oSubquestion) {
            if ($scale_id !== null && $oSubquestion->scale_id != $scale_id) {
                continue;
            }
            //if  exclude_all_others is set then the related answer should keep its position at all times
            //thats why we have to re-position it if it has been randomized
            if (
                ($this->getQuestionAttribute('exclude_all_others') != '' && $this->getQuestionAttribute('random_order') == 1)
                && ($oSubquestion->title == $this->getQuestionAttribute('exclude_all_others'))
            ) {
                $excludedSubquestionPosition = (safecount($aSubQuestions[$oSubquestion->scale_id]) - 1);
                $excludedSubquestion = $oSubquestion;
                continue;
            }
            $aSubQuestions[$oSubquestion->scale_id][] = $oSubquestion;
        }

        if ($excludedSubquestion != null) {
            array_splice($aSubQuestions[$excludedSubquestion->scale_id], ($excludedSubquestion->question_order - 1), 0, [$excludedSubquestion]);
        }

        if ($scale_id !== null) {
            return $aSubQuestions[$scale_id];
        }

        return $aSubQuestions;
    }

    /**
     * Returns the HTML icon representing the question's mandatory status.
     *
     * Depending on the question type and the mandatory setting ("Y", "S", or neither),
     * this method returns an HTML snippet containing the appropriate icon markup.
     *
     * @return string HTML markup for the mandatory indicator icon.
     */
    public function getMandatoryIcon()
    {
        if ($this->type != Question::QT_X_TEXT_DISPLAY && $this->type != Question::QT_VERTICAL_FILE_UPLOAD) {
            if ($this->mandatory == "Y") {
                $sIcon = '<span class="ri-star-fill text-danger"></span>';
            } elseif ($this->mandatory == "S") {
                $sIcon = '<span class="ri-star-fill text-danger"> ' . gT('Soft') . '</span>';
            } else {
                $sIcon = '<span></span>';
            }
        } else {
            $sIcon = '<span class="ri-forbid-2-line text-danger" data-bs-toggle="tooltip" title="' . gT('Not relevant for this question type') . '"></span>';
        }
        return $sIcon;
    }

    /**
     * Return other icon according to state
     *
     * @return boolean
     */
    public function getOtherIcon()
    {
        if ($this->getAllowOther()) {
            $sIcon = ($this->other === "Y") ? '<span class="ri-record-circle-line"></span>' : '<span></span>';
        } else {
            $sIcon = '<span class="ri-forbid-2-line text-danger" data-bs-toggle="tooltip" title="' . gT('Not relevant for this question type') . '"></span>';
        }
        return $sIcon;
    }


    /**
     * Return if question allow other
     *
     * @return boolean
     */
    public function getAllowOther()
    {
        return (
            !$this->parent_qid
            && $this->getQuestionType()->other
        );
    }

    /**
     * Return if question has managed subquestions
     * usage in rules : allow set other even if existing subquestions 'other' exist (but deleted after)
     *
     * @return boolean
     */
    public function getAllowSubquestions()
    {
        return (
            !$this->parent_qid
            && $this->getQuestionType()->subquestions
        );
    }

    /**
     * Return true if the question type supports answer options
     *
     * @return boolean
     */
    public function getAllowAnswerOptions()
    {
        return (
            !$this->parent_qid
            && $this->getQuestionType()->answerscales > 0
        );
    }

    /**
     * Generates a new valid title/code for the question.
     *
     * If the current title is invalid or conflicts with existing question codes,
     * the method attempts to clean and regenerate it. Up to 10 attempts are made
     * before giving up.
     *
     * @param int $index Base index for generating the question code (e.g., index during survey import).
     *
     * @return string|null The new title, or null if no valid title could be generated.
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
            $sNewTitle = 'q' . $sNewTitle;
        }
        /* Maybe there are another question with same title try to fix it 10 times */
        $attempts = 0;
        while (!$this->validate(array('title'))) {
            $rand = mt_rand(0, 1024);
            $sNewTitle = 'q' . $index . 'r' . $rand;
            $this->title = $sNewTitle;
            $attempts++;
            if ($attempts > 10) {
                $this->addError('title', 'Failed to resolve question code problems after 10 attempts.');
                return null;
            }
        }
        return $sNewTitle;
    }

    /**
     * Filters the query to load question and group translations for a given language.
     *
     * This scope modifies the query using `with()` so that both the questionl10ns and
     * group relations are limited to the specified language.
     *
     * @param string $sLanguage The language code to filter the related records by.
     *
     * @return $this The current query object for method chaining.
     */
    /*public function language($sLanguage)
    {
        $this->with(
            array(
                'questionl10ns'=>array('condition'=>"language='".$sLanguage."'"),
                'group'=>array('condition'=>"language='".$sLanguage."'")
            )
        );
        return $this;
    }
     */

    /**
     * Returns the column configuration used for rendering the question list grid.
     *
     * @return array List of column definitions for the CGridView.
     */
    public function getQuestionListColumns()
    {
        return array(
            array(
                'id' => 'id',
                'class' => 'CCheckBoxColumn',
                'selectableRows' => '100',
            ),
            array(
                'header' => gT('Question ID'),
                'name' => 'question_id',
                'value' => '$data->qid',
            ),
            array(
                'header' => gT("Group / Question order"),
                'name' => 'question_order',
                'value' => '$data->group->group_order ." / ". $data->question_order',
            ),
            array(
                'header' => gT('Code'),
                'name' => 'title',
                'value' => '$data->title',
                'htmlOptions' => array('class' => ''),
            ),
            array(
                'header' => gT('Question'),
                'name' => 'question',
                'value' => 'array_key_exists($data->survey->language, $data->questionl10ns) ? viewHelper::flatEllipsizeText($data->questionl10ns[$data->survey->language]->question,true,0) : ""',
                'htmlOptions' => array('class' => ''),
            ),
            array(
                'header' => gT('Question type'),
                'name' => 'type',
                'type' => 'raw',
                'value' => 'gT($data->question_theme->title) . (YII_DEBUG ? " <em>{$data->type}</em>" : "")',
                'htmlOptions' => array('class' => ''),
            ),

            array(
                'header' => gT('Group'),
                'name' => 'group',
                'value' => '$data->group->questiongroupl10ns[$data->survey->language]->group_name',
            ),

            array(
                'header' => gT('Mandatory'),
                'type' => 'raw',
                'name' => 'mandatory',
                'value' => '$data->mandatoryIcon',
                'htmlOptions' => array('class' => 'text-center'),
            ),

            array(
                'header' => gT('Other'),
                'type' => 'raw',
                'name' => 'other',
                'value' => '$data->otherIcon',
                'htmlOptions' => array('class' => 'text-center'),
            ),
            array(
                'header' => gT('Action'),
                'name' => 'actions',
                'type' => 'raw',
                'value' => '$data->buttons',
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'text-center button-column ls-sticky-column'],
            ),
        );
    }

    /**
     * Builds and returns a data provider for searching and listing questions.
     *
     * This method creates a CActiveDataProvider configured with sorting, filtering,
     * and pagination options based on the current model attributes. It is used in
     * admin grids to display and filter the list of questions belonging to a survey.
     *
     * @return CActiveDataProvider The data provider instance containing the search results.
     */
    public function search()
    {
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);

        $sort = new CSort();
        $sort->attributes = array(
            'question_id' => array(
                'asc' => 't.qid asc',
                'desc' => 't.qid desc',
            ),
            'question_order' => array(
                'asc' => 'g.group_order asc, t.question_order asc',
                'desc' => 'g.group_order desc,t.question_order desc',
            ),
            'title' => array(
                'asc' => 't.title asc',
                'desc' => 't.title desc',
            ),
            'question' => array(
                'asc' => 'ql10n.question asc',
                'desc' => 'ql10n.question desc',
            ),

            'group' => array(
                'asc' => 'g.gid asc',
                'desc' => 'g.gid desc',
            ),

            'mandatory' => array(
                'asc' => 't.mandatory asc',
                'desc' => 't.mandatory desc',
            ),

            'encrypted' => array(
                'asc' => 't.encrypted asc',
                'desc' => 't.encrypted desc',
            ),

            'other' => array(
                'asc' => 't.other asc',
                'desc' => 't.other desc',
            ),
        );

        $sort->defaultOrder = array(
            'question_order' => CSort::SORT_ASC,
        );

        $criteria = new LSDbCriteria();
        $criteria->compare("t.sid", $this->sid, false, 'AND');
        $criteria->compare("t.parent_qid", 0, false, 'AND');
        //$criteria->group = 't.qid, t.parent_qid, t.sid, t.gid, t.type, t.title, t.preg, t.other, t.mandatory, t.question_order, t.scale_id, t.same_default, t.relevance, t.modulename, t.encrypted';
        $criteria->with = [
            'group' => ['alias' => 'g'],
            'questionl10ns' => ['alias' => 'ql10n', 'condition' => "language='" . $this->survey->language . "'"],
            'question_theme' => ['alias' => 'qt']
        ];

        if (!empty($this->title)) {
            $criteria2 = new LSDbCriteria();
            $criteria2->compare('t.title', $this->title, true, 'OR');
            $criteria2->compare('ql10n.question', $this->title, true, 'OR');
            $criteria2->compare('t.type', $this->title, true, 'OR');
            $criteria2->compare('qt.description', $this->title, true, 'OR');
            /* search exact qid and make sure it's a numeric */
            if (is_numeric($this->title)) {
                $criteria2->compare('t.qid', $this->title, false, 'OR');
            }
            $criteria->mergeWith($criteria2, 'AND');
        }

        /* make sure gid is a numeric */
        if ($this->gid != '' and is_numeric($this->gid)) {
            $criteria->compare('g.gid', $this->gid, false, 'AND');
        }

        $dataProvider = new CActiveDataProvider(
            'Question',
            array(
            'criteria' => $criteria,
            'sort' => $sort,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
            )
        );
        return $dataProvider;
    }

    /**
     * Defines the named scopes available for this model.
     *
     * @inheritdoc
     *
     * @return array List of scope definitions indexed by scope name.
     */
    public function scopes()
    {
        return array(
            'primary' => array('condition' => "parent_qid = 0"),
        );
    }

    /**
     * Prepare question before saving.
     *
     * - Block type changes on active surveys (keep original question type).
     * - Update survey lastmodified timestamp when the question changes.
     *
     * @return bool
     */
    protected function beforeSave()
    {
        if (!parent::beforeSave()) {
            return false;
        }

        // Question isn't part of any Survey
        if (empty($this->sid)) {
            return true;
        }

        $survey = Survey::model()->findByPk($this->sid);

        if ($survey !== null && $survey->isActive) {
            // Survey is active, so question type should not be updated.
            // Reset Question Type to original database value.
            if (!$this->getIsNewRecord() && !empty($this->qid)) {
                $originalQuestion = Question::model()->findByPk($this->qid);
                if ($originalQuestion !== null) {
                    // don't override questiontype when survey is active, set it back to what it was...
                    $this->type = $originalQuestion->type;
                }
            }
        }

        Survey::model()->updateByPk(
            $this - sid,
            ['lastmodified' => gmdate('Y-m-d H:i:s')]
        );
    }

    /**
     * Fix sub question of a parent question
     * Must be call after base language subquestion is set
     *
     * @todo   : move other fix here ?
     * @return void
     */
    public function fixSubQuestions()
    {
        if ($this->parent_qid) {
            return;
        }
        $oSurvey = $this->survey;

        /* Delete subquestion l10n for unknown languages */
        $criteria = new CDbCriteria();
        $criteria->with = array("question", array('condition' => array('sid' => $this->qid)));
        $criteria->together = true;
        $criteria->addNotInCondition('language', $oSurvey->getAllLanguages());
        QuestionL10n::model()->deleteAll($criteria);

        /* Delete invalid subquestions (not in primary language */
        $validSubQuestion = Question::model()->findAll(
            array(
            'select' => 'title',
            'condition' => 'parent_qid=:parent_qid',
            'params' => array('parent_qid' => $this->qid)
            )
        );
        $criteria = new CDbCriteria();
        $criteria->compare('parent_qid', $this->qid);
        $criteria->addNotInCondition('title', CHtml::listData($validSubQuestion, 'title', 'title'));
        Question::model()->deleteAll($criteria);
    }

    /**
     * Returns the list of question types that support quotation in expressions.
     *
     * @return string[] Array of quotable question type identifiers.
     */
    public static function getQuotableTypes()
    {
        return array('G', 'M', 'Y', 'A', 'B', 'I', 'L', 'O', '!', '*');
    }

    /**
     * Returns the basic field name used for identifying this question or subquestion.
     *
     * The field name follows the LimeSurvey format "{sid}X{gid}X{qid}". For
     * subquestions, the parent_qid is used instead of the question's own qid.
     *
     * @return string The generated basic field name.
     */
    public function getBasicFieldName()
    {
        if ($this->parent_qid != 0) {
            /* Fix #15228: This survey throw a Error when try to print : seems subquestion gid can be outdated */
            // Use parents relation
            if (!empty($this->parents)) { // Maybe need to throw error or find it if it's not set ?
                return "{$this->parents->sid}X{$this->parents->gid}X{$this->parent_qid}";
            }
            return "{$this->sid}X{$this->gid}X{$this->parent_qid}";
        }
        return "{$this->sid}X{$this->gid}X{$this->qid}";
    }

    /**
     * Returns the value of a specific question attribute.
     *
     * Looks up the QuestionAttribute for the given attribute name and returns its
     * stored value. If the attribute does not exist, null is returned.
     *
     * @param string $sAttribute The name of the attribute to retrieve.
     *
     * @return string|null The attribute value, or null if the attribute does not exist.
     */
    public function getQuestionAttribute($sAttribute)
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('qid=:qid');
        $criteria->addCondition('attribute=:attribute');
        $criteria->params = [':qid' => $this->qid, ':attribute' => $sAttribute];
        $oQuestionAttribute =  QuestionAttribute::model()->find($criteria);

        if ($oQuestionAttribute != null) {
            return $oQuestionAttribute->value;
        }

        return null;
    }

    /**
     * Retrieves the QuestionType model for this question and applies its settings.
     *
     * Looks up the QuestionType associated with the question's type value. If found,
     * the type's configuration is applied to the current question instance before
     * returning the model.
     *
     * @return QuestionType|null The matching QuestionType model, or null if none exists.
     */
    public function getQuestionType()
    {
        $model = QuestionType::findOne($this->type);
        if (!empty($model)) {
            $model->applyToQuestion($this);
        }
        return $model;
    }

    /**
     * Returns the IDs of all subquestions belonging to this question.
     *
     * @return int[] Array of subquestion qid values.
     */
    public function getAllSubQuestionIds()
    {
        $result = [];
        if (!empty($this->subquestions)) {
            foreach ($this->subquestions as $subquestion) {
                $result[] = $subquestion->qid;
            }
        }
        return $result;
    }

    /**
     * Returns the renderer object for this question based on its type.
     *
     * If no type is provided, the question's own type is used. Depending on the
     * question type, the corresponding renderer class is instantiated with the
     * given field definition array. An InvalidArgumentException is thrown if no
     * matching renderer exists for the given type.
     *
     * @param array       $aFieldArray Field definition and metadata used by the renderer.
     * @param string|null $type        Optional question type identifier. Defaults to $this->type.
     *
     * @return object The renderer instance for the specified question type.
     *
     * @throws InvalidArgumentException If the question type is unknown or unsupported.
     */
    public function getRenderererObject($aFieldArray, $type = null)
    {
        $type = $type ?? $this->type;
        LoadQuestionTypes::load($type);
        switch ($type) {
            case Question::QT_X_TEXT_DISPLAY:
                $oRenderer = new RenderBoilerplate($aFieldArray);
                break;
            case Question::QT_5_POINT_CHOICE:
                $oRenderer = new RenderFivePointChoice($aFieldArray);
                break;
            case Question::QT_ASTERISK_EQUATION:
                $oRenderer = new RenderEquation($aFieldArray);
                break;
            case Question::QT_D_DATE:
                $oRenderer = new RenderDate($aFieldArray);
                break;
            case Question::QT_1_ARRAY_DUAL:
                $oRenderer = new RenderArrayMultiscale($aFieldArray);
                break;
            case Question::QT_L_LIST:
                $oRenderer = new RenderListRadio($aFieldArray);
                break;
            case Question::QT_EXCLAMATION_LIST_DROPDOWN:
                $oRenderer = new RenderListDropdown($aFieldArray);
                break;
            case Question::QT_O_LIST_WITH_COMMENT:
                $oRenderer = new RenderListComment($aFieldArray);
                break;
            case Question::QT_R_RANKING:
                $oRenderer = new RenderRanking($aFieldArray);
                break;
            case Question::QT_M_MULTIPLE_CHOICE:
                $oRenderer = new RenderMultipleChoice($aFieldArray);
                break;
            case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:
                $oRenderer = new RenderMultipleChoiceWithComments($aFieldArray);
                break;
            case Question::QT_I_LANGUAGE:
                $oRenderer = new RenderLanguageSelector($aFieldArray);
                break;
            case Question::QT_Q_MULTIPLE_SHORT_TEXT:
                $oRenderer = new RenderMultipleShortText($aFieldArray);
                break;
            case Question::QT_T_LONG_FREE_TEXT:
                $oRenderer = new RenderLongFreeText($aFieldArray);
                break;
            case Question::QT_U_HUGE_FREE_TEXT:
                $oRenderer = new RenderHugeFreeText($aFieldArray);
                break;
            case Question::QT_K_MULTIPLE_NUMERICAL:
                $oRenderer = new RenderMultipleNumerical($aFieldArray);
                break;
            case Question::QT_A_ARRAY_5_POINT:
                $oRenderer = new RenderArray5ChoiceQuestion($aFieldArray);
                break;
            case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:
                $oRenderer = new RenderArray10ChoiceQuestion($aFieldArray);
                break;
            case Question::QT_C_ARRAY_YES_UNCERTAIN_NO:
                $oRenderer = new RenderArrayYesUncertainNo($aFieldArray);
                break;
            case Question::QT_E_ARRAY_INC_SAME_DEC:
                $oRenderer = new RenderArrayOfIncSameDecQuestions($aFieldArray);
                break;
            case Question::QT_F_ARRAY:
                $oRenderer = new RenderArrayFlexibleRow($aFieldArray);
                break;
            case Question::QT_G_GENDER:
                $oRenderer = new RenderGenderDropdown($aFieldArray);
                break;
            case Question::QT_H_ARRAY_COLUMN:
                $oRenderer = new RendererArrayFlexibleColumn($aFieldArray);
                break;
            case Question::QT_N_NUMERICAL:
                $oRenderer = new RenderNumerical($aFieldArray);
                break;
            case Question::QT_S_SHORT_FREE_TEXT:
                $oRenderer = new RenderShortFreeText($aFieldArray);
                break;
            case Question::QT_Y_YES_NO_RADIO:
                $oRenderer = new RenderYesNoRadio($aFieldArray);
                break;
            case Question::QT_COLON_ARRAY_NUMBERS:
                $oRenderer = new RenderArrayMultiFlexNumbers($aFieldArray);
                break;
            case Question::QT_SEMICOLON_ARRAY_TEXT:
                $oRenderer = new RenderArrayMultiFlexText($aFieldArray);
                break;
            case Question::QT_VERTICAL_FILE_UPLOAD:
                $oRenderer = new RenderFileUpload($aFieldArray);
                break;
            default:
                throw new InvalidArgumentException('Missing question type in getRenderererObject');
                break;
        };

        return $oRenderer;
    }

    /**
     * Returns the DataSet object corresponding to the given question type.
     *
     * If no type is provided, the method uses the question's own type. Depending on
     * the type, this method instantiates and returns the appropriate DataSet class.
     * An InvalidArgumentException is thrown if no matching DataSet class exists
     * for the given type.
     *
     * @param string|null $type Optional question type identifier. Defaults to $this->type.
     *
     * @return object An instance of a DataSet class matching the question type.
     *
     * @throws InvalidArgumentException If the question type is unknown or unsupported.
     */
    public function getDataSetObject($type = null)
    {
        $type = $type ?? $this->type;
        LoadQuestionTypes::load($type);

        switch ($type) {
            case Question::QT_X_TEXT_DISPLAY:
                return new DataSetBoilerplate($this->qid);
            case Question::QT_5_POINT_CHOICE:
                return new DataSetFivePointChoice($this->qid);
            case Question::QT_ASTERISK_EQUATION:
                return new DataSetEquation($this->qid);
            case Question::QT_D_DATE:
                return new DataSetDate($this->qid);
            case Question::QT_1_ARRAY_DUAL:
                return new DataSetArrayMultiscale($this->qid);
            case Question::QT_L_LIST:
                return new DataSetListRadio($this->qid);
            case Question::QT_EXCLAMATION_LIST_DROPDOWN:
                return new DataSetListDropdown($this->qid);
            case Question::QT_O_LIST_WITH_COMMENT:
                return new DataSetListWithComment($this->qid);
            case Question::QT_R_RANKING:
                return new DataSetRanking($this->qid);
            case Question::QT_M_MULTIPLE_CHOICE:
                return new DataSetMultipleChoice($this->qid);
            case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:
                return new DataSetMultipleChoiceWithComments($this->qid);
            case Question::QT_I_LANGUAGE:
                return new DataSetLanguage($this->qid);
            case Question::QT_Q_MULTIPLE_SHORT_TEXT:
                return new DataSetMultipleShortText($this->qid);
            case Question::QT_T_LONG_FREE_TEXT:
                return new DataSetLongFreeText($this->qid);
            case Question::QT_U_HUGE_FREE_TEXT:
                return new DataSetHugeFreeText($this->qid);
            case Question::QT_K_MULTIPLE_NUMERICAL:
                return new DataSetMultipleNumerical($this->qid);
            case Question::QT_A_ARRAY_5_POINT:
                return new DataSetArray5ChoiceQuestion($this->qid);
            case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:
                return new DataSetArray10ChoiceQuestion($this->qid);
            case Question::QT_C_ARRAY_YES_UNCERTAIN_NO:
                return new DataSetArrayYesUncertainNo($this->qid);
            case Question::QT_E_ARRAY_INC_SAME_DEC:
                return new DataSetArrayOfIncSameDecQuestions($this->qid);
            case Question::QT_F_ARRAY:
                return new DataSetArrayFlexibleRow($this->qid);
            case Question::QT_G_GENDER:
                return new DataSetGenderDropdown($this->qid);
            case Question::QT_H_ARRAY_COLUMN:
                return new DataSetArrayFlexibleColumn($this->qid);
            case Question::QT_N_NUMERICAL:
                return new DataSetNumerical($this->qid);
            case Question::QT_S_SHORT_FREE_TEXT:
                return new DataSetShortFreeText($this->qid);
            case Question::QT_Y_YES_NO_RADIO:
                return new DataSetYesNoRadio($this->qid);
            case Question::QT_COLON_ARRAY_NUMBERS:
                return new DataSetArrayMultiFlexNumbers($this->qid);
            case Question::QT_SEMICOLON_ARRAY_TEXT:
                return new DataSetArrayMultiFlexText($this->qid);
            case Question::QT_VERTICAL_FILE_UPLOAD:
                return new DataSetFileUpload($this->qid);
            default:
                throw new InvalidArgumentException('Missing question type in getDataSetObject');
        };
    }

    /**
     * Inserts a new record using the provided data.
     *
     * @param array $data Key-value pairs to assign to the new model instance.
     *
     * @return bool|null True if the record was saved, false if validation failed, or null on error.
     */
    public function insertRecords($data)
    {
        $oRecord = new self();
        foreach ($data as $k => $v) {
            $oRecord->$k = $v;
        }
        if ($oRecord->validate()) {
            return $oRecord->save();
        }
        Yii::log(\CVarDumper::dumpAsString($oRecord->getErrors()), 'warning', 'application.models.Question.insertRecords');
    }

    /**
     * Ensures that question_order values for all questions in the given group start at 1
     * and are greater than 0.
     *
     * Some questions may incorrectly have question_order = 0. This method finds all such
     * questions (excluding subquestions) within the given group and assigns consecutive
     * order values starting from START_SORTING_VALUE.
     *
     * @param int $questionGroupId The ID of the question group to normalize question_order for.
     *
     * @return bool True if any sort numbers were updated, false if no changes were required.
     */
    public static function setQuestionOrderForGroup($questionGroupId)
    {
        $criteriaHighestOrderNumber = new CDbCriteria();
        $criteriaHighestOrderNumber->condition = 't.gid=:gid';
        $criteriaHighestOrderNumber->addCondition("parent_qid=0"); //no subquestions here ...
        $criteriaHighestOrderNumber->addCondition('t.question_order=0');//find only those which has to be set
        $criteriaHighestOrderNumber->params = ['gid' => $questionGroupId];
        $criteriaHighestOrderNumber->order = 't.qid ASC';

        $questionsWithZeroSortNumber = Question::model()->findAll($criteriaHighestOrderNumber);
        $isAlreadySorted = count($questionsWithZeroSortNumber) === 0; //means no questions, so resort needed
        if (!$isAlreadySorted) {
            $sortValue = self::START_SORTING_VALUE;
            /* @var Question $question  */
            foreach ($questionsWithZeroSortNumber as $question) {
                $question->question_order = $sortValue;
                $question->save();
                $sortValue++;
            }
        }

        return !$isAlreadySorted;
    }


    /**
     * Returns the highest question_order value that exists for a questiongroup inside the related questions.
     * ($question->question_order).
     *
     * @param int $questionGroupId the question group id
     *
     * @return int|null question highest order number or null if there are no questions belonging to the group
     */
    public static function getHighestQuestionOrderNumberInGroup($questionGroupId)
    {
        $criteriaHighestOrderNumber = new CDbCriteria();
        $criteriaHighestOrderNumber->limit = 1;
        $criteriaHighestOrderNumber->condition = 't.gid=:gid';
        $criteriaHighestOrderNumber->addCondition("parent_qid=0"); //no subquestions here ...
        $criteriaHighestOrderNumber->params = ['gid' => $questionGroupId];
        $criteriaHighestOrderNumber->order = 't.question_order DESC';

        $oQuestionHighestOrderNumber = Question::model()->find($criteriaHighestOrderNumber);

        return ($oQuestionHighestOrderNumber === null) ? null : $oQuestionHighestOrderNumber->question_order;
    }

    /**
     * Increases the question_order value by +1 for all questions in the given group.
     *
     * If $after is provided, only questions with a question_order greater than or equal
     * to the given value are incremented.
     *
     * @param int      $questionGroupId The ID of the question group.
     * @param int|null $after           Optional: only increment questions with an order >= this value.
     *
     * @return void
     */
    public static function increaseAllOrderNumbersForGroup($questionGroupId, $after = null)
    {
        $criteria = new CDbCriteria();
        $criteria->compare("gid", $questionGroupId);
        if ($after) {
            $criteria->compare("question_order", ">=" . $after);
        }
        $questionsInGroup = Question::model()->findAll($criteria);
        foreach ($questionsInGroup as $question) {
            $question->question_order = $question->question_order + 1;
            $question->save();
        }
    }

    /**
     * Legacy stub for checking whether the question has subquestions.
     *
     * @deprecated since 5.3.x This method is no longer used and will be removed in a future release.
     * @return     void
     *
     * Unknown usage; kept for backward compatibility.
     */
    public function getHasSubquestions()
    {
    }

    /**
     * Legacy stub for checking whether the question has answer options.
     *
     * @deprecated since 5.3.x This method is no longer used and will be removed in a future release.
     * @return     void
     *
     * Unknown usage; kept for backward compatibility.
     */
    public function getHasAnsweroptions()
    {
    }

    /**
     * Check if this question have subquestion with other code
     *
     * @return boolean
     */
    public function getHasOtherSubquestions()
    {
        if (!$this->getAllowSubquestions()) {
            return false;
        }
        $otherSubQuestionCount = Question::model()->count(
            "parent_qid=:parent_qid and LOWER(title)='other'",
            array("parent_qid" => $this->qid)
        );
        return boolval($otherSubQuestionCount);
    }

    /**
     * Updates the question and performs cleanup of related data.
     *
     * After successfully saving the parent question via the inherited update(),
     * this method removes any invalid subquestions and answer options.
     *
     * @param array|null $attributes Attributes to update (passed to the parent implementation).
     *
     * @return bool True if the question was updated successfully, false otherwise.
     */
    public function update($attributes = null)
    {
        if (parent::update($attributes)) {
            $this->removeInvalidSubquestions();
            $this->removeInvalidAnswerOptions();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove subquestion if needed when update question type
     *
     * @return void
     */
    protected function removeInvalidSubquestions()
    {
        if ($this->getAllowSubquestions()) {
            return;
        }

        // Remove subquestions if the question's type doesn't allow subquestions
        $aSubquestions = Question::model()->findAll("parent_qid=:parent_qid", array("parent_qid" => $this->qid));
        if (!empty($aSubquestions)) {
            foreach ($aSubquestions as $oSubquestion) {
                /* Use delete to delete related model */
                $oSubquestion->delete();
            }
        }
    }

    /**
     * Removes all answer options if the question's type doesn't allow answer options.
     *
     * @return void
     */
    protected function removeInvalidAnswerOptions()
    {
        if ($this->getAllowAnswerOptions()) {
            return;
        }

        // Remove answer options if the question's type doesn't allow answer options
        $answerOptions = Answer::model()->findAll("qid=:qid", array("qid" => $this->qid));
        if (!empty($answerOptions)) {
            foreach ($answerOptions as $answerOption) {
                $answerOption->delete();
            }
        }
    }

    /**
     * Used by question create form.
     *
     * @return Question
     */
    public function getEmptySubquestion()
    {
        $question = new Question();
        $question->qid = 0;
        $question->title = (SettingsUser::getUserSettingValue('subquestionprefix', App()->user->id) ?? 'SQ') . '001';
        $question->relevance = 1;
        return $question;
    }

    /**
     * Used by question create form.
     *
     * @return Answer
     */
    public function getEmptyAnswerOption()
    {
        $answer = new Answer();
        // TODO: Assuming no collision.
        $answer->aid = 'new' . rand(1, 100000);
        $answer->sortorder = 0;
        $answer->code = (SettingsUser::getUserSettingValue('answeroptionprefix', App()->user->id) ?? 'AO') . '01';

        $l10n = [];
        foreach ($this->survey->allLanguages as $language) {
            $l10n[$language] = new AnswerL10n();
            $l10n[$language]->setAttributes(
                [
                    'aid'      => 0,
                    'answer'   => '',
                    'language' => $language,
                ],
                false
            );
        }
        $answer->answerl10ns = $l10n;

        return $answer;
    }

    /**
     * Get array of answers options, depending on scale count for this question type.
     *
     * @return array Like [0 => Answer[]] or [0 => Answer[], 1 => Answer[]]
     */
    public function getScaledAnswerOptions()
    {
        $answerScales = $this->questionType->answerscales;
        $results = [];
        for ($scale_id = 0; $scale_id < $answerScales; $scale_id++) {
            if (!empty($this->qid)) {
                $criteria = new CDbCriteria();
                $criteria->condition = 'qid = :qid AND scale_id = :scale_id';
                $criteria->params = [':qid' => $this->qid, ':scale_id' => $scale_id];
                $results[$scale_id] = Answer::model()->findAll($criteria);
            }
            if (empty($results[$scale_id])) {
                $results[$scale_id] = [$this->getEmptyAnswerOption()];
            }
        }
        return $results;
    }

    /**
     * Get array of subquestions, depending on scale count for this question type.
     *
     * @return array Like [0 => Question[]] or [0 => Question[], 1 => Question[]]
     */
    public function getScaledSubquestions()
    {
        $subquestionScale = $this->questionType->subquestions;
        $results = [];
        for ($scale_id = 0; $scale_id < $subquestionScale; $scale_id++) {
            if (!empty($this->qid)) {
                $criteria = new CDbCriteria();
                $criteria->condition = 'parent_qid = :parent_qid AND scale_id = :scale_id';
                $criteria->order = 'question_order, title ASC';
                $criteria->params = [':parent_qid' => $this->qid, ':scale_id' => $scale_id];
                $results[$scale_id] = Question::model()->findAll($criteria);
            }
            if (empty($results[$scale_id])) {
                $results[$scale_id] = [$this->getEmptySubquestion()];
            }
        }
        return $results;
    }

    /**
     * Validates and resolves the question theme name.
     *
     * If the question has no type or is a subquestion, null is returned.
     * If a custom theme name is set and exists for the given question type,
     * that name is returned. Otherwise, the default theme name for the
     * question type is returned. Returns null if no valid theme name can be
     * determined.
     *
     * @return string|null The resolved valid theme name, or null if none applies.
     */
    public function questionThemeNameValidator()
    {
        /* need a type */
        if (empty($this->type)) {
            return null;
        }
        /* not needed in child question */
        if (!empty($this->parent_qid)) {
            return null;
        }
        if (!empty($this->question_theme_name) && $this->question_theme_name != 'core') {
            $criteria = new CDbCriteria();
            $criteria->addCondition('question_type = :question_type AND name = :name');
            $criteria->params = [':question_type' => $this->type, ':name' => $this->question_theme_name];
            $questionTheme = QuestionTheme::model()->query($criteria, false);
            if ($questionTheme) {
                return $this->question_theme_name;
            }
        }
        /* Get default theme name from type */
        $baseQuestionThemeName = QuestionTheme::model()->getBaseThemeNameForQuestionType($this->type);
        if (!empty($baseQuestionThemeName)) {
            return $baseQuestionThemeName;
        }
        /* Not a valid type ? */
        return null;
    }

    /**
     * Returns the QuestionTheme related to this question.
     * It's not implemented as a relation because relations only work on
     * persisted models.
     *
     * @return QuestionTheme|null
     */
    public function getQuestionTheme()
    {
        return $this->getRelated("question_theme", $this->isNewRecord);
    }

    /**
     * Returns whether the question is a dual-scale type.
     *
     * @return bool
     */
    public function getIsDualScale()
    {
        $dualScaleTypes = $this->getDualScaleTypes();
        return in_array($this->type, $dualScaleTypes);
    }

    /**
     * Returns the list of question types that are considered dual-scale.
     *
     * @return string[]
     */
    public function getDualScaleTypes()
    {
        $dualScaleTypes = array(
            Question::QT_1_ARRAY_DUAL
        );

        return $dualScaleTypes;
    }
}
