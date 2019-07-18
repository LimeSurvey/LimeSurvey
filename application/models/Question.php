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

use \LimeSurvey\Helpers\questionHelper;

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
 * @property integer $question_order Question order in greoup
 * @property integer $parent_qid Questions parent question ID eg for subquestions
 * @property integer $scale_id  The scale ID
 * @property integer $same_default Saves if user set to use the same default value across languages in default options dialog
 * @property string $relevance Questions relevane equation
 * @property string $modulename
 *
 * @property Survey $survey
 * @property QuestionGroup $groups  //@TODO should be singular
 * @property Question $parents      //@TODO should be singular
 * @property Question[] $subquestions
 * @property QuestionAttribute[] $questionAttributes NB! returns all QuestionArrtibute Models fot this QID regardless of the specified language
 * @property QuestionL10n[] $questionL10ns Question Languagesettings indexd by language code
 * @property string[] $quotableTypes Question types that can be used for quotas
 * @property Answer[] $answers
 * @property QuestionType $questionType
 * @property array $allSubQuestionIds QID-s of all question sub-questions, empty array returned if no sub-questions
 * @inheritdoc
 */
class Question extends LSActiveRecord
{
    const QT_1_ARRAY_MULTISCALE = '1'; //ARRAY (Flexible Labels) multi scale
    const QT_5_POINT_CHOICE = '5';
    const QT_A_ARRAY_5_CHOICE_QUESTIONS = 'A'; // ARRAY OF 5 POINT CHOICE QUESTIONS
    const QT_B_ARRAY_10_CHOICE_QUESTIONS = 'B'; // ARRAY OF 10 POINT CHOICE QUESTIONS
    const QT_C_ARRAY_YES_UNCERTAIN_NO = 'C'; // ARRAY OF YES\No\gT("Uncertain") QUESTIONS
    const QT_D_DATE = 'D';
    const QT_E_ARRAY_OF_INC_SAME_DEC_QUESTIONS = 'E';
    const QT_F_ARRAY_FLEXIBLE_ROW = 'F';
    const QT_G_GENDER_DROPDOWN = 'G';
    const QT_H_ARRAY_FLEXIBLE_COLUMN = 'H';
    const QT_I_LANGUAGE = 'I';
    const QT_K_MULTIPLE_NUMERICAL_QUESTION = 'K';
    const QT_L_LIST_DROPDOWN = 'L';
    const QT_M_MULTIPLE_CHOICE = 'M';
    const QT_N_NUMERICAL = 'N';
    const QT_O_LIST_WITH_COMMENT = 'O';
    const QT_P_MULTIPLE_CHOICE_WITH_COMMENTS = 'P';
    const QT_Q_MULTIPLE_SHORT_TEXT = 'Q';
    const QT_R_RANKING_STYLE = 'R';
    const QT_S_SHORT_FREE_TEXT = 'S';
    const QT_T_LONG_FREE_TEXT = 'T';
    const QT_U_HUGE_FREE_TEXT = 'U';
    const QT_X_BOILERPLATE_QUESTION = 'X';
    const QT_Y_YES_NO_RADIO = 'Y';
    const QT_Z_LIST_RADIO_FLEXIBLE = 'Z';
    const QT_EXCLAMATION_LIST_DROPDOWN = '!';
    const QT_VERTICAL_FILE_UPLOAD = '|';
    const QT_ASTERISK_EQUATION = '*';
    const QT_COLON_ARRAY_MULTI_FLEX_NUMBERS = ':';
    const QT_SEMICOLON_ARRAY_MULTI_FLEX_TEXT = ';';


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
        return 'qid';
    }

    /** @inheritdoc */
    public function relations()
    {
        return array(
            'survey' => array(self::BELONGS_TO, 'Survey', 'sid'),
            'group' => array(self::BELONGS_TO, 'QuestionGroup', 'gid', 'together' => true),
            'parent' => array(self::HAS_ONE, 'Question', array("qid" => "parent_qid")),
            'questionAttributes' => array(self::HAS_MANY, 'QuestionAttribute', 'qid'),
            'questionL10ns' => array(self::HAS_MANY, 'QuestionL10n', 'qid', 'together' => true),
            'subquestions' => array(self::HAS_MANY, 'Question', array('parent_qid'=>'qid')),
            'conditions' => array(self::HAS_MANY, 'Condition', 'qid'),
            'answers' => array(self::HAS_MANY, 'Answer', 'qid')
        );
    }

    /**
     * @inheritdoc
     * TODO: make it easy to read (if possible)
     */
    public function rules()
    {
        $aRules = array(
            array('title', 'required', 'on' => 'update, insert', 'message'=>gT('The question code is mandatory.', 'unescaped')),
            array('title', 'length', 'min' => 1, 'max'=>20, 'on' => 'update, insert'),
            array('qid,sid,gid,parent_qid', 'numerical', 'integerOnly'=>true),
            array('qid', 'unique','message'=>sprintf(gT("Question id (qid) : '%s' is already in use."), $this->qid)),// Still needed ?
            array('other', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
            array('mandatory', 'in', 'range'=>array('Y', 'S', 'N'), 'allowEmpty'=>true),
            array('encrypted', 'in', 'range'=>array('Y', 'N'), 'allowEmpty'=>true),
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
                    'condition' => 'sid=:sid AND parent_qid=:parent_qid and scale_id=:scale_id',
                    'params' => array(
                        ':sid' => $this->sid,
                        ':parent_qid' => $this->parent_qid,
                        ':scale_id' => $this->scale_id
                        )
                    ),
                    'message' => gT('Subquestion codes must be unique.')
            );
            // Disallow other title if question allow other
            $oParentQuestion = Question::model()->findByPk(array("qid"=>$this->parent_qid));
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
            $oActualValue = Question::model()->findByPk(array("qid"=>$this->qid));
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
                'message'=> sprintf(gT("Code: '%s' is a reserved word."), $this->title), // Usage of {attribute} need attributeLabels, {value} never exist in message
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
            array('gid' => $gid, 'sid' => $surveyid, 'parent_qid'=>0),
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
     * @param int $position
     */
    public function updateQuestionOrder($gid, $position = 0)
    {
        $data = Yii::app()->db->createCommand()->select('qid')
            ->where(array('and', 'gid=:gid', 'parent_qid=0'))
            ->order('question_order, title ASC')
            ->from('{{questions}}')
            ->bindParam(':gid', $gid, PDO::PARAM_INT)
            ->query();

        $position = intval($position);
        foreach ($data->readAll() as $row) {
            Yii::app()->db->createCommand()->update(
                $this->tableName(),
                array('question_order' => $position),
                'qid='.$row['qid']
            );
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
        $aAttributeNames = QuestionAttribute::getQuestionAttributesSettings($sQuestionType);

        // If the question has a custom template, we first check if it provides custom attributes

        $oQuestion = Question::model()->find(array('condition'=>'qid=:qid', 'params'=>array(':qid'=>$iQuestionID)));
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
                        $sInputType = (string)$oCustomAttribute->inputtype;
                        // remove attribute if inputtype is empty
                        if (empty($sInputType)) {
                            unset($aAttributeNames[$sAttributeName]);
                        } else {
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
     * @return CDbDataReader
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
     * Deletes a question and ALL its relations (subquestions, answers, etc, etc)
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

        // delete defaultvalues and defaultvalueL10ns
        $oDefaultValues = DefaultValue::model()->findAll((new CDbCriteria())->addInCondition('qid', $ids));
        foreach($oDefaultValues as $defaultvalue){
            DefaultValue::model()->deleteAll('dvid = :dvid', array(':dvid' => $defaultvalue->dvid));
            DefaultValueL10n::model()->deleteAll('dvid = :dvid', array(':dvid' => $defaultvalue->dvid));
        }

        $this->deleteAllAnswers();
        $this->removeFromLastVisited();

        if (parent::delete()) {
            Question::model()->updateQuestionOrder($this->gid, $this->sid);
            return true;
        }
        return false;
    }

    /**
     * remove question from lastVisited
     */
    public function removeFromLastVisited()
    {
        $oCriteria = new CDbCriteria();
        $oCriteria->compare('stg_name', 'last_question_%', true, 'AND', false);
        $oCriteria->compare('stg_value', $this->qid, false, 'AND');
        SettingGlobal::model()->deleteAll($oCriteria);
    }

    /**
     * Delete all question and its subQuestion Answers
     */
    private function deleteAllAnswers()
    {
        $ids = array_merge([$this->qid], $this->allSubQuestionIds);
        $qidsCriteria = (new CDbCriteria())->addInCondition('qid', $ids);

        $answerIds = [];
        $answers = Answer::model()->findAll($qidsCriteria);
        if (!empty($answers)) {
            foreach ($answers as $answer) {
                $answerIds[] = $answer->aid;
            }
        }
        $aidsCriteria = (new CDbCriteria())->addInCondition('aid', $answerIds);
        AnswerL10n::model()->deleteAll($aidsCriteria);
        Answer::model()->deleteAll($qidsCriteria);
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
        if ($orderby === false){
            $oQuestions = Question::model()->with('questionL10ns')->findAll(array('condition' => $condition));
        } else {
            $oQuestions = Question::model()->with('questionL10ns')->findAll(array('condition' => $condition, 'order' => $orderby));
        }
        $arr = array();
        foreach($oQuestions as $key => $question)
        {
            $arr[$key] = array_merge($question->attributes, current($question->questionL10ns)->attributes);
        }
        return $arr;
    }

    /**
     * @param integer $surveyid
     * @param string $language
     * @return Question[]
     */
    public function getQuestionList($surveyid)
    {
        return Question::model()->with('group')->findAll(array('condition'=>'t.sid='.$surveyid, 'order'=>'group.group_order DESC, question_order'));
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
     * @deprecated use QuestionType::modelsAttributes() instead
     */
    public static function typeList($language = null)
    {
        $QuestionTypes = QuestionType::modelsAttributes($language);

        /**
         * @todo Check if this actually does anything, since the values are arrays.
         */
        asort($QuestionTypes);

        return $QuestionTypes;
    }

    /**
     * This function return the name by question type
     * @param string question type
     * @return string Question type name
     *
     * Maybe move class in typeList ?
     * @deprecated use $this->>questionType->description instead
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
     * //TODO move to QuestionType
     */
    public static function getQuestionClass($sType)
    {
        switch ($sType) {
            case Question::QT_1_ARRAY_MULTISCALE: return 'array-flexible-duel-scale';
            case Question::QT_5_POINT_CHOICE: return 'choice-5-pt-radio';
            case Question::QT_A_ARRAY_5_CHOICE_QUESTIONS: return 'array-5-pt';
            case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS: return 'array-10-pt';
            case Question::QT_C_ARRAY_YES_UNCERTAIN_NO: return 'array-yes-uncertain-no';
            case Question::QT_D_DATE: return 'date';
            case Question::QT_E_ARRAY_OF_INC_SAME_DEC_QUESTIONS: return 'array-increase-same-decrease';
            case Question::QT_F_ARRAY_FLEXIBLE_ROW: return 'array-flexible-row';
            case Question::QT_G_GENDER_DROPDOWN: return 'gender';
            case Question::QT_H_ARRAY_FLEXIBLE_COLUMN: return 'array-flexible-column';
            case Question::QT_I_LANGUAGE: return 'language';
            case Question::QT_K_MULTIPLE_NUMERICAL_QUESTION: return 'numeric-multi';
            case Question::QT_L_LIST_DROPDOWN: return 'list-radio';
            case Question::QT_M_MULTIPLE_CHOICE: return 'multiple-opt';
            case Question::QT_N_NUMERICAL: return 'numeric';
            case Question::QT_O_LIST_WITH_COMMENT: return 'list-with-comment';
            case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS: return 'multiple-opt-comments';
            case Question::QT_Q_MULTIPLE_SHORT_TEXT: return 'multiple-short-txt';
            case Question::QT_R_RANKING_STYLE: return 'ranking';
            case Question::QT_S_SHORT_FREE_TEXT: return 'text-short';
            case Question::QT_T_LONG_FREE_TEXT: return 'text-long';
            case Question::QT_U_HUGE_FREE_TEXT: return 'text-huge';
            case Question::QT_X_BOILERPLATE_QUESTION: return 'boilerplate';
            case Question::QT_Y_YES_NO_RADIO: return 'yes-no';
            case Question::QT_Z_LIST_RADIO_FLEXIBLE: return 'list-radio-flexible';
            case Question::QT_EXCLAMATION_LIST_DROPDOWN: return 'list-dropdown';
            case Question::QT_COLON_ARRAY_MULTI_FLEX_NUMBERS: return 'array-multi-flexi';
            case Question::QT_SEMICOLON_ARRAY_MULTI_FLEX_TEXT: return 'array-multi-flexi-text';
            case Question::QT_VERTICAL_FILE_UPLOAD: return 'upload-files';
            case Question::QT_ASTERISK_EQUATION: return 'equation';
            default:  return 'generic_question'; // fallback
        };
    }


    public function getbuttons()
    {
        $url         = Yii::app()->createUrl("/admin/questions/sa/view/surveyid/");
        $url        .= '/'.$this->sid.'/gid/'.$this->gid.'/qid/'.$this->qid;
        $previewUrl  = Yii::app()->createUrl("survey/index/action/previewquestion/sid/");
        $previewUrl .= '/'.$this->sid.'/gid/'.$this->gid.'/qid/'.$this->qid;
        $editurl     = Yii::app()->createUrl("admin/questioneditor/sa/view/surveyid/$this->sid/gid/$this->gid/qid/$this->qid");
        $button      = '<a class="btn btn-default open-preview"  data-toggle="tooltip" title="'.gT("Question preview").'"  aria-data-url="'.$previewUrl.'" aria-data-sid="'.$this->sid.'" aria-data-gid="'.$this->gid.'" aria-data-qid="'.$this->qid.'" aria-data-language="'.$this->survey->language.'" href="#" role="button" ><span class="fa fa-eye"  ></span></a> ';

        if (Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'update')) {
            $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Edit question").'" href="'.$editurl.'" role="button"><span class="fa fa-pencil" ></span></a>';
        }

        if (Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'read')) {
            $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Question summary").'" href="'.$url.'" role="button"><span class="fa fa-list-alt" ></span></a>';
        }

        $oSurvey = Survey::model()->findByPk($this->sid);
        $gid_search = $this->gid;

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

    public function getOrderedAnswers($scale_id=null)
    {
        $alpha = $this->getQuestionAttribute('alphasort');
        // Get questions and answers by defined order
        $sOrder = ($this->getQuestionAttribute('random_order') == 1)
            ? dbRandom()
            : ($alpha ? 'answer' : 'question_order');
        $oCriteria = new CDbCriteria();
        $oCriteria->order = $sOrder;
        $oCriteria->addCondition('parent_qid=:parent_qid');

        $oCriteria->params = [':parent_qid'=>$this->qid];

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
        
        if($scale_id !== null) {
            return $aAnswerOptions[$scale_id];
        }

        return $aAnswerOptions;
    }

    /**
     * get subquestions fort the current question object in the right order
     * @param int $random
     * @param string $exclude_all_others
     * @return array
     */
    public function getOrderedSubQuestions($scale_id = null)
    {
        //reset subquestions set prior to this call
        $aSubQuestions = [
            0 => []
        ];
        $excludedSubquestion = null;
        
        $aOrderedSubquestions = $this->subquestions;
        if($this->getQuestionAttribute('random_order') == 1) {
            shuffle($aOrderedSubquestions);
        }
        
        usort($aOrderedSubquestions, function($oQuestionA, $oQuestionB){
            if($oQuestionA->question_order == $oQuestionB->question_order) { return 0; }
            return $oQuestionA->question_order < $oQuestionB->question_order ? -1 : 1;
        });

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
                $excludedSubquestion = $oSubquestion;
                continue;
            }
            $aSubQuestions[$oSubquestion->scale_id][] = $oSubquestion;   
        }
        
        if($excludedSubquestion !== null) {
            array_splice($aSubQuestions[$excludedSubquestion->scale_id][], ($excludedSubquestion->question_order-1), 0, $excludedSubquestion);
        }

        if($scale_id !== null) {
            return $aSubQuestions[$scale_id];
        }

        return $aSubQuestions;
    }

    public function getMandatoryIcon()
    {
        if ($this->type != Question::QT_X_BOILERPLATE_QUESTION && $this->type != Question::QT_VERTICAL_FILE_UPLOAD) {
            if ($this->mandatory == "Y"){
                $sIcon = '<span class="fa fa-asterisk text-danger"></span>';
            } elseif ($this->mandatory == "S"){
                $sIcon = '<span class="fa fa-asterisk text-danger"> ' . gT('Soft') . '</span>';
            } else {
                $sIcon = '<span></span>';
            }            
        } else {
            $sIcon = '<span class="fa fa-ban text-danger" data-toggle="tooltip" title="'.gT('Not relevant for this question type').'"></span>';
        }
        return $sIcon;
    }

    public function getOtherIcon()
    {
        if (($this->type == Question::QT_L_LIST_DROPDOWN) || ($this->type == Question::QT_EXCLAMATION_LIST_DROPDOWN) || ($this->type == Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS) || ($this->type == Question::QT_M_MULTIPLE_CHOICE)) {
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
                           
    /*public function language($sLanguage)
    {
        $this->with(
            array(
                'questionL10ns'=>array('condition'=>"language='".$sLanguage."'"),
                'group'=>array('condition'=>"language='".$sLanguage."'")
            )
        );
        return $this;
    }*/
    public function getQuestionListColumns(){
    return array(
            array(
                'id'=>'id',
                'class'=>'CCheckBoxColumn',
                'selectableRows' => '100',
            ),
            array(
                'header' => gT('Question ID'),
                'name' => 'question_id',
                'value'=>'$data->qid',
            ),
            array(
                'header' => gT("Group / Question order"),
                'name' => 'question_order',
                'value'=>'$data->group->group_order ." / ". $data->question_order',
            ),
            array(
                'header' => gT('Code'),
                'name' => 'title',
                'value'=>'$data->title',
                'htmlOptions' => array('class' => 'col-md-1'),
            ),
            array(
                'header' => gT('Question'),
                'name' => 'question',
                'value'=> 'array_key_exists($data->survey->language, $data->questionL10ns) ? viewHelper::flatEllipsizeText($data->questionL10ns[$data->survey->language]->question,true,0) : ""',
                'htmlOptions' => array('class' => 'col-md-5'),
            ),
            array(
                'header' => gT('Question type'),
                'name' => 'type',
                'type'=>'raw',
                'value'=>'$data->typedesc',
                'htmlOptions' => array('class' => 'col-md-1'),
            ),

            array(
                'header' => gT('Group'),
                'name' => 'group',
                'value'=> '$data->group->questionGroupL10ns[$data->survey->language]->group_name',
            ),

            array(
                'header' => gT('Mandatory'),
                'type' => 'raw',
                'name' => 'mandatory',
                'value'=> '$data->mandatoryIcon',
                'htmlOptions' => array('class' => 'text-center'),
            ),

            array(
                'header' => gT('Other'),
                'type' => 'raw',
                'name' => 'other',
                'value'=> '$data->otherIcon',
                'htmlOptions' => array('class' => 'text-center'),
            ),


            array(
                'header'=>'',
                'name'=>'actions',
                'type'=>'raw',
                'value'=>'$data->buttons',
                'htmlOptions' => array('class' => 'col-md-2 col-xs-1 text-right nowrap'),
            ),

        );
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
                'asc'=>'group.group_order asc, t.question_order asc',
                'desc'=>'group.group_order desc,t.question_order desc',
            ),
            'title'=>array(
                'asc'=>'t.title asc',
                'desc'=>'t.title desc',
            ),
            'question'=>array(
                'asc'=>'questionL10ns.question asc',
                'desc'=>'questionL10ns.question desc',
            ),

            'group'=>array(
                'asc'=>'group.gid asc',
                'desc'=>'group.gid desc',
            ),

            'mandatory'=>array(
                'asc'=>'t.mandatory asc',
                'desc'=>'t.mandatory desc',
            ),

            'encrypted'=>array(
                'asc'=>'t.encrypted asc',
                'desc'=>'t.encrypted desc',
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
        $criteria->compare("t.sid", $this->sid, false, 'AND');
        $criteria->compare("t.parent_qid", 0, false, 'AND');
        //$criteria->group = 't.qid, t.parent_qid, t.sid, t.gid, t.type, t.title, t.preg, t.other, t.mandatory, t.question_order, t.scale_id, t.same_default, t.relevance, t.modulename, t.encrypted';              
        $criteria->with = array('group', 'questionL10ns');
        
        if (!empty($this->title)) {     
            $criteria2 = new CDbCriteria;
            $criteria2->join = 'JOIN {{question_l10ns}} q_L10n ON t.qid = q_L10n.qid ';
            $criteria2->compare('t.title', $this->title, true, 'OR');
            $criteria2->compare('q_L10n.question', $this->title, true, 'OR');
            $criteria2->compare('t.type', $this->title, true, 'OR');
            /* search exact qid and make sure it's a numeric */
            if(is_numeric($this->title)) {
                $criteria2->compare('t.qid', $this->title, false, 'OR');
            }
            $criteria->mergeWith($criteria2, 'AND');
        }
        
        /* make sure gid is a numeric */
        if ($this->gid != '' and is_numeric($this->gid)) {
            $criteria->compare('group.gid', $this->gid, false, 'AND');
        }

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

        /* Delete subquestion l10n for unknown languages */
        $criteria = new CDbCriteria;
        $criteria->with = array("question", array('condition'=>array('sid'=>$this->qid)));
        $criteria->together = true;
        $criteria->addNotInCondition('language', $oSurvey->getAllLanguages());
        QuestionL10n::model()->deleteAll($criteria);

        /* Delete invalid subquestions (not in primary language */
        $validSubQuestion = Question::model()->findAll(array(
            'select'=>'title',
            'condition'=>'parent_qid=:parent_qid',
            'params'=>array('parent_qid' => $this->qid)
        ));
        $criteria = new CDbCriteria;
        $criteria->compare('parent_qid', $this->qid);
        $criteria->addNotInCondition('title', CHtml::listData($validSubQuestion, 'title', 'title'));
        Question::model()->deleteAll($criteria);
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
        $cacheKey = 'getQuestionAttributes_' . $iQuestionID . '_' . $sLanguage;
        $value = EmCacheHelper::get($cacheKey);
        if ($value !== false) {
            return $value;
        }

        $criteria = new CDbCriteria();
        $criteria->addCondition('qid=:qid');
        $criteria->addCondition('(language=:language OR language IS NULL)');
        $criteria->params = [':qid'=>$this->qid];
        $criteria->params = [':language'=>$this->language];
        $aQuestionAttributes = QuestionAttribute::model()->findAll($criteria);

        EmCacheHelper::set($cacheKey, $aQuestionAttributes);

        return $aQuestionAttributes;
    }

    /**
     * @return QuestionAttribute[]
     */
    public function getQuestionAttribute($sAttribute)
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('qid=:qid');
        $criteria->addCondition('attribute=:attribute');
        $criteria->params = [':qid'=>$this->qid, ':attribute' => $sAttribute];
        $oQuestionAttribute =  QuestionAttribute::model()->find($criteria);
        
        if($oQuestionAttribute != null) {
            $oQuestionAttribute->value;
        }
        
        return null;
    }

    /**
     * @return null|QuestionType
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
     * @return array
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
    
    public function getRenderererObject($aFieldArray, $type = null)
    {
        $type = $type === null ? $this->type : $type;
        LoadQuestionTypes::load($type);
        switch ($type) {
            case Question::QT_X_BOILERPLATE_QUESTION: $oRenderer = new RenderBoilerplate($aFieldArray); break;
            case Question::QT_5_POINT_CHOICE: $oRenderer = new RenderFivePointChoice($aFieldArray); break;
            case Question::QT_ASTERISK_EQUATION: $oRenderer = new RenderEquation($aFieldArray); break;
            case Question::QT_D_DATE: $oRenderer = new RenderDate($aFieldArray); break;
            case Question::QT_1_ARRAY_MULTISCALE: $oRenderer = new RenderArrayMultiscale($aFieldArray); break;
            case Question::QT_L_LIST_DROPDOWN: $oRenderer = new RenderListRadio($aFieldArray); break;
            case Question::QT_EXCLAMATION_LIST_DROPDOWN: $oRenderer = new RenderListDropdown($aFieldArray); break;
            case Question::QT_O_LIST_WITH_COMMENT: $oRenderer = new RenderListComment($aFieldArray); break;
            case Question::QT_R_RANKING_STYLE: $oRenderer = new RenderRanking($aFieldArray); break;
            case Question::QT_M_MULTIPLE_CHOICE: $oRenderer = new RenderMultipleChoice($aFieldArray); break;
            case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS: $oRenderer = new RenderMultipleChoiceWithComments($aFieldArray); break;
            case Question::QT_I_LANGUAGE: $oRenderer = new RenderLanguageSelector($aFieldArray); break;
            case Question::QT_Q_MULTIPLE_SHORT_TEXT: $oRenderer = new RenderMultipleShortText($aFieldArray); break;
            case Question::QT_T_LONG_FREE_TEXT: $oRenderer = new RenderLongFreeText($aFieldArray); break;
            case Question::QT_U_HUGE_FREE_TEXT: $oRenderer = new RenderHugeFreeText($aFieldArray); break;
            case Question::QT_K_MULTIPLE_NUMERICAL_QUESTION: $oRenderer = new RenderMultipleNumerical($aFieldArray); break;
            case Question::QT_A_ARRAY_5_CHOICE_QUESTIONS: $oRenderer = new RenderArray5ChoiceQuestion($aFieldArray); break;
            case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS: $oRenderer = new RenderArray10ChoiceQuestion($aFieldArray); break;
            case Question::QT_C_ARRAY_YES_UNCERTAIN_NO: $oRenderer = new RenderArrayYesUncertainNo($aFieldArray); break;
            case Question::QT_E_ARRAY_OF_INC_SAME_DEC_QUESTIONS: $oRenderer = new RenderArrayOfIncSameDecQuestions($aFieldArray); break;
            case Question::QT_F_ARRAY_FLEXIBLE_ROW: $oRenderer = new RenderArrayFlexibleRow($aFieldArray); break;
            case Question::QT_G_GENDER_DROPDOWN: $oRenderer = new RenderGenderDropdown($aFieldArray); break;
            case Question::QT_H_ARRAY_FLEXIBLE_COLUMN: $oRenderer = new RendererArrayFlexibleColumn($aFieldArray); break;
            case Question::QT_N_NUMERICAL: $oRenderer = new RenderNumerical($aFieldArray); break;
            case Question::QT_S_SHORT_FREE_TEXT: $oRenderer = new RenderShortFreeText($aFieldArray); break;
            case Question::QT_Y_YES_NO_RADIO: $oRenderer = new RenderYesNoRadio($aFieldArray); break;
            case Question::QT_Z_LIST_RADIO_FLEXIBLE: $oRenderer = new RenderListRadioFlexible($aFieldArray); break;
            case Question::QT_COLON_ARRAY_MULTI_FLEX_NUMBERS: $oRenderer = new RenderArrayMultiFlexNumbers($aFieldArray); break;
            case Question::QT_SEMICOLON_ARRAY_MULTI_FLEX_TEXT: $oRenderer = new RenderArrayMultiFlexText($aFieldArray); break;
            case Question::QT_VERTICAL_FILE_UPLOAD: $oRenderer = new RenderFileUpload($aFieldArray); break;
            default:  $oRenderer = new DummyQuestionEditContainer($aFieldArray); break;
        };
        
        return $oRenderer;
    }
    
    public function getDataSetObject($type = null)
    {
        $type = $type === null ? $this->type : $type;
        LoadQuestionTypes::load($type);

        switch ($type) {
            case Question::QT_X_BOILERPLATE_QUESTION:           return new DataSetBoilerplate($this->qid);
            case Question::QT_5_POINT_CHOICE:                   return new DataSetFivePointChoice($this->qid);
            case Question::QT_ASTERISK_EQUATION:                return new DataSetEquation($this->qid);
            case Question::QT_D_DATE:                           return new DataSetDate($this->qid);
            case Question::QT_1_ARRAY_MULTISCALE:               return new DataSetArrayMultiscale($this->qid);
            case Question::QT_L_LIST_DROPDOWN:                  return new DataSetListRadio($this->qid);
            case Question::QT_EXCLAMATION_LIST_DROPDOWN:        return new DataSetListDropdown($this->qid);
            case Question::QT_O_LIST_WITH_COMMENT:              return new DataSetListWithComment($this->qid);
            case Question::QT_R_RANKING_STYLE:                  return new RenderRanking($this->qid);
            case Question::QT_M_MULTIPLE_CHOICE:                return new DataSetMultipleChoice($this->qid);
            case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:  return new DataSetMultipleChoiceWithComments($this->qid);
            case Question::QT_I_LANGUAGE:                       return new DataSetLanguage($this->qid);
            case Question::QT_Q_MULTIPLE_SHORT_TEXT:            return new DataSetMultipleShortText($this->qid);
            case Question::QT_T_LONG_FREE_TEXT:                 return new DataSetLongFreeText($this->qid);
            case Question::QT_U_HUGE_FREE_TEXT:                 return new DataSetHugeFreeText($this->qid);
            case Question::QT_K_MULTIPLE_NUMERICAL_QUESTION:    return new RenderMultipleNumerical($this->qid);
            case Question::QT_A_ARRAY_5_CHOICE_QUESTIONS:       return new DataSetArray5ChoiceQuestion($this->qid);
            case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:      return new DataSetArray10ChoiceQuestion($this->qid);
            case Question::QT_C_ARRAY_YES_UNCERTAIN_NO:         return new DataSetArrayYesUncertainNo($this->qid);
            case Question::QT_E_ARRAY_OF_INC_SAME_DEC_QUESTIONS:return new DataSetArrayOfIncSameDecQuestions($this->qid);
            case Question::QT_F_ARRAY_FLEXIBLE_ROW:             return new DataSetArrayFlexibleRow($this->qid);
            case Question::QT_G_GENDER_DROPDOWN:                return new DataSetGenderDropdown($this->qid);
            case Question::QT_H_ARRAY_FLEXIBLE_COLUMN:          return new DataSetArrayFlexibleColumn($this->qid);
            case Question::QT_N_NUMERICAL:                      return new DataSetNumerical($this->qid);
            case Question::QT_S_SHORT_FREE_TEXT:                return new DataSetShortFreeText($this->qid);
            case Question::QT_Y_YES_NO_RADIO:                   return new DataSetYesNoRadio($this->qid);
            case Question::QT_Z_LIST_RADIO_FLEXIBLE:            return new DataSetListRadioFlexible($this->qid);
            case Question::QT_COLON_ARRAY_MULTI_FLEX_NUMBERS:   return new DataSetArrayMultiFlexNumbers($this->qid);
            case Question::QT_SEMICOLON_ARRAY_MULTI_FLEX_TEXT:  return new DataSetArrayMultiFlexText($this->qid);
            case Question::QT_VERTICAL_FILE_UPLOAD:             return new DataSetFileUpload($this->qid);
            default:  return new DummyQuestionEditContainer($aFieldArray);
        };
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


    public function getHasSubquestions(){

    }  
    
    public function getHasAnsweroptions(){

    }
}
