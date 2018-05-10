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
 * @property string $preg
 * @property string $other Other option enabled for question (Y/N)
 * @property string $mandatory Whther question is mandatory (Y/N)
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
            $aRules[] = array('title', 'unique', 'caseSensitive'=>false, 'criteria'=>array(
                                'condition' => 'sid=:sid AND parent_qid=:parent_qid and scale_id=:scale_id',
                                'params' => array(
                                    ':sid' => $this->sid,
                                    ':parent_qid' => $this->parent_qid,
                                    ':scale_id' => $this->scale_id
                                    )
                                ),
                            'message' => gT('Subquestion codes must be unique.'));
            // Disallow other title if question allow other
            $oParentQuestion = Question::model()->findByPk(array("qid"=>$this->parent_qid));
            if ($oParentQuestion->other == "Y") {
                $aRules[] = array('title', 'LSYii_CompareInsensitiveValidator', 'compareValue'=>'other', 'operator'=>'!=', 'message'=> sprintf(gT("'%s' can not be used if the 'Other' option for this question is activated."), "other"), 'except' => 'archiveimport');
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
                return $aRules; // We don't change title, then don't put rules on title
            }
        }
        // 0 or empty
        if (!$this->parent_qid) {
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
            $aRules[] = array('title', 'match', 'pattern' => '/^[a-z,A-Z][[:alnum:]]*$/',
                'message' => gT('Question codes must start with a letter and may only contain alphanumeric characters.'),
                'except' => 'archiveimport');
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
    
    public function defaultScope()
    {
        return array('order'=>'question_order');
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
        $aAttributeNames = questionHelper::getQuestionAttributesSettings($sQuestionType);

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
                        $aCustomAttribute = json_decode(json_encode((array) $oCustomAttribute), 1);

                        if (!isset($aCustomAttribute['i18n'])) {
                            $aCustomAttribute['i18n'] = false;
                        }

                        if (!isset($aCustomAttribute['readonly'])) {
                            $aCustomAttribute['readonly'] = false;
                        }

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
     * TODO: replace it everywhere by Answer::model()->findAll([Critieria Object])
     * @param string $fields
     * @param mixed $condition
     * @param string $orderby
     * @return array
     */
    public function getQuestionsForStatistics($fields, $condition, $orderby = false)
    {
        return Question::model()->findAll($condition);
    }

    /**
     * @param integer $surveyid
     * @param string $language
     * @return array
     */
    public function getQuestionList($surveyid)
    {
        return Question::model()->with('group')->findAll(array('condition'=>'t.sid='.$surveyid, 'order'=>'group_order DESC, question_order'));
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
    public static function typeList()
    {
        $questionTypes = QuestionType::modelsAttributes();

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
     * @deprecated use $this->>questionType->description instead
     */
    public static function getQuestionTypeName($sType)
    {
        $typeList = self::typeList();
        return $typeList[$sType]['description'];
    }


    /**
     * Return all group of the active survey
     * Used to render group filter in questions list
     */
    public function getAllGroups()
    {
        return QuestionGroup::model()->findAllByAttributes(['sid' => $this->iSurveyID]);
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

        if ($oSurvey->active != "Y" && Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'delete')) {
            $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Delete").'" href="#" role="button"
                        onclick="if (confirm(\' '.gT("Deleting  will also delete any answer options and subquestions it includes. Are you sure you want to continue?", "js").' \' )){ '.convertGETtoPOST(Yii::app()->createUrl("admin/questions/sa/delete/surveyid/$this->sid/qid/$this->qid")).'} ">
                            <span class="text-danger fa fa-trash"></span>
                            </a>';
        }

        return $button;
    }

    public function getOrderedAnswers($random = 0, $alpha = 0)
    {
        if ($random == 1) {
            $sOrder = dbRandom();
        } elseif ($alpha == 1) {
            $sOrder = 'answer';
        } else {
            $sOrder = 'sortorder';
        }
        $aAnswers = Answer::model()->findAll(array('order'=>$sOrder, 'condition'=>'qid=:qid AND scale_id=0', 'params'=>array(':qid'=>$this->qid)));        
        return $aAnswers;

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
        $criteria->params = [':qid'=>$this->qid];
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
        if ($this->type != Question::QT_X_BOILERPLATE_QUESTION && $this->type != Question::QT_VERTICAL_FILE_UPLOAD) {
            $sIcon = ($this->mandatory == "Y") ? '<span class="fa fa-asterisk text-danger"></span>' : '<span></span>';
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
                'asc'=>'t.question_order asc',
                'desc'=>'t.question_order desc',
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
                'asc'=>'group.group_name asc',
                'desc'=>'group.group_name desc',
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

        $sort->defaultOrder = array('question_order' => CSort::SORT_ASC);

        $criteria = new CDbCriteria;
        $criteria->with = array('group');
        $criteria->compare("t.sid", $this->sid, false, 'AND');
        $criteria->compare("t.parent_qid", 0, false, 'AND');

        $criteria2 = new CDbCriteria;
        $criteria2->compare('t.title', $this->title, true, 'OR');
        $criteria2->compare('questionL10ns.question', $this->title, true, 'OR');
        $criteria2->compare('t.type', $this->title, true, 'OR');

        $qid_reference = (Yii::app()->db->getDriverName() == 'pgsql' ? ' t.qid::varchar' : 't.qid');
        $criteria2->compare($qid_reference, $this->title, true, 'OR');

        if ($this->gid != '') {
            $criteria->compare('group.gid', $this->gid, true, 'AND');
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
        $criteria = new CDbCriteria();
        $criteria->addCondition('qid=:qid');
        $criteria->params = [':qid'=>$this->qid];
        return QuestionAttribute::model()->findAll($criteria);
    }

    /**
     * @return null|QuestionType
     */
    public function getQuestionType()
    {
        $model = QuestionType::findOne($this->type);
        if (!empty($model)) {
            $model->question = $this;
        }
        return $model;
    }

}
