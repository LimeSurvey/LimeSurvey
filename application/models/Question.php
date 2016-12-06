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
class Question extends LSActiveRecord
{

    // Stock the active group_name for questions list filtering
    public $group_name;

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
    * Returns the primary key of this table
    *
    * @access public
    * @return string[]
    */
    public function primaryKey()
    {
        return array('qid', 'language');
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
            'survey' => array(self::BELONGS_TO, 'Survey', 'sid'),
            'groups' => array(self::BELONGS_TO, 'QuestionGroup', 'gid, language', 'together' => true),
            'parents' => array(self::HAS_ONE, 'Question', '', 'on' => "$alias.parent_qid = parents.qid"),
            'subquestions' => array(self::HAS_MANY, 'Question', 'parent_qid', 'on' => "$alias.language = subquestions.language")
        );
    }

    /**
    * Returns this model's validation rules
    * TODO: make it easy to read (if possible)
    */
    public function rules()
    {
        $aRules= array(
                    array('title','required','on' => 'update, insert','message'=>gT('Question code may not be empty.','unescaped')),
                    array('title','length', 'min' => 1, 'max'=>20,'on' => 'update, insert'),
                    array('qid', 'numerical','integerOnly'=>true),
                    array('qid', 'unique', 'criteria'=>array(
                                    'condition'=>'language=:language',
                                    'params'=>array(':language'=>$this->language)
                            ),
                            'message'=>'{attribute} "{value}" is already in use.'),
                    array('language','length', 'min' => 2, 'max'=>20),// in array languages ?
                    array('title,question,help','LSYii_Validators'),
                    array('other', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
                    array('mandatory', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
                    array('question_order','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
                    array('scale_id','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
                    array('same_default','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
                );

        if($this->parent_qid)// Allways enforce unicity on Sub question code (DB issue).
        {
            $aRules[]=array('title', 'unique', 'caseSensitive'=>false, 'criteria'=>array(
                                'condition' => 'language=:language AND sid=:sid AND parent_qid=:parent_qid and scale_id=:scale_id',
                                'params' => array(
                                    ':language' => $this->language,
                                    ':sid' => $this->sid,
                                    ':parent_qid' => $this->parent_qid,
                                    ':scale_id' => $this->scale_id
                                    )
                                ),
                            'message' => gT('Subquestion codes must be unique.'));
            // Disallow other title if question allow other
            $oParentQuestion=Question::model()->findByPk(array("qid"=>$this->parent_qid,'language'=>$this->language));
            if($oParentQuestion->other=="Y")
            {
                $aRules[]= array('title', 'LSYii_CompareInsensitiveValidator','compareValue'=>'other','operator'=>'!=', 'message'=> sprintf(gT("'%s' can not be used if the 'Other' option for this question is activated."),"other"), 'except' => 'archiveimport');
            }
        }
        else
        {
            // Disallow other if sub question have 'other' for title
            $oSubquestionOther=Question::model()->find("parent_qid=:parent_qid and LOWER(title)='other'",array("parent_qid"=>$this->qid));
            if($oSubquestionOther)
            {
                $aRules[]= array('other', 'compare','compareValue'=>'Y','operator'=>'!=', 'message'=> sprintf(gT("'%s' can not be used if the 'Other' option for this question is activated."),'other'), 'except' => 'archiveimport' );
            }
        }
        if(!$this->isNewRecord)
        {
            $oActualValue=Question::model()->findByPk(array("qid"=>$this->qid,'language'=>$this->language));
            if($oActualValue && $oActualValue->title==$this->title)
            {
                return $aRules; // We don't change title, then don't put rules on title
            }
        }
        if(!$this->parent_qid)// 0 or empty
        {
            $aRules[]=array('title', 'unique', 'caseSensitive'=>true, 'criteria'=>array(
                                'condition' => 'language=:language AND sid=:sid AND parent_qid=0',
                                'params' => array(
                                    ':language' => $this->language,
                                    ':sid' => $this->sid
                                    )
                                ),
                            'message' => gT('Question codes must be unique.'), 'except' => 'archiveimport');
            $aRules[]= array('title', 'match', 'pattern' => '/^[a-z,A-Z][[:alnum:]]*$/', 'message' => gT('Question codes must start with a letter and may only contain alphanumeric characters.'), 'except' => 'archiveimport');
        }
        else
        {
            $aRules[]= array('title', 'compare','compareValue'=>'time','operator'=>'!=', 'message'=> gT("'time' is a reserved word and can not be used for a subquestion."), 'except' => 'archiveimport' );
            $aRules[]= array('title', 'match', 'pattern' => '/^[[:alnum:]]*$/', 'message' => gT('Subquestion codes may only contain alphanumeric characters.'), 'except' => 'archiveimport');
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
        $questions = self::model()->findAllByAttributes(array('gid' => $gid, 'sid' => $surveyid, 'language' => Survey::model()->findByPk($surveyid)->language), array('order'=>'question_order') );
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
    * @return void
    */
    function updateQuestionOrder($gid,$language,$position=0)
    {
        $data=Yii::app()->db->createCommand()->select('qid')
        ->where(array('and','gid=:gid','language=:language', 'parent_qid=0'))
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
        $aAttributeValues=QuestionAttribute::model()->getQuestionAttributes($iQuestionID,$sLanguage);
        $aAttributeNames = \ls\helpers\questionHelper::getQuestionAttributesSettings($sQuestionType);
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
     * TODO: replace this function call by $oSurvey->questions defining a relation in SurveyModel
     */
    function getQuestions($sid, $gid, $language)
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
     * This function is only called from database.php
     * TODO : create a relation to self called subquestion
     */
    function getSubQuestions($parent_qid)
    {
        return Yii::app()->db->createCommand()
        ->select()
        ->from(self::tableName())
        ->where('parent_qid=:parent_qid')
        ->bindParam(":parent_qid", $parent_qid, PDO::PARAM_INT)
        ->order('question_order asc')
        ->query();
    }

    /**
     * This function is only called from surveyadmin.php
     * TODO : create a relation to self called subquestion
     */
    function getQuestionsWithSubQuestions($iSurveyID, $sLanguage, $sCondition = FALSE)
    {
        $command = Yii::app()->db->createCommand()
        ->select('{{questions}}.*, q.qid as sqid, q.title as sqtitle,  q.question as sqquestion, ' . '{{groups}}.*')
        ->from($this->tableName())
        ->leftJoin('{{questions}} q', "q.parent_qid = {{questions}}.qid AND q.language = {{questions}}.language")
        ->join('{{groups}}', "{{groups}}.gid = {{questions}}.gid  AND {{questions}}.language = {{groups}}.language");
        $command->where("({{questions}}.sid = '$iSurveyID' AND {{questions}}.language = '$sLanguage' AND {{questions}}.parent_qid = 0)");

        if ($sCondition != FALSE)
        {
            $command->where("({{questions}}.sid = :iSurveyID AND {{questions}}.language = :sLanguage AND {{questions}}.parent_qid = 0) AND {$sCondition}")
            ->bindParam(":iSurveyID", $iSurveyID, PDO::PARAM_STR)
            ->bindParam(":sLanguage", $sLanguage, PDO::PARAM_STR);
        }
        $command->order("{{groups}}.group_order asc, {{questions}}.question_order asc");

        return $command->query()->readAll();
    }

    /**
    * Insert an array into the questions table
    * Returns null if insertion fails, otherwise the new QID
    *
    * This function is called from database.php and import_helper.php
    * TODO: as said by Shnoulle, it must be replace by using validate and save from controller.
    *
    * @param array $data
    */
    function insertRecords($data)
    {
        // This function must be deprecated : don't find a way to have getErrors after (Shnoulle on 131206)
        $oRecord = new self;
        foreach ($data as $k => $v){
            $oRecord->$k = $v;
            }
        if($oRecord->validate())
        {
            $oRecord->save();
            return $oRecord->qid;
        }
        tracevar($oRecord->getErrors());
    }

    /**
     * Delete a bunch of questions in one go
     *
     * @param mixed $questionsIds
     * @return void
     */
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

    /**
     * This function is called from everywhere, which is quiet weird...
     * TODO: replace it everywhere by Answer::model()->findAll([Critieria Object])
     */
    function getAllRecords($condition, $order=FALSE)
    {
        $command=Yii::app()->db->createCommand()->select('*')->from($this->tableName())->where($condition);
        if ($order != FALSE)
        {
            $command->order($order);
        }
        return $command->query();
    }


    /**
     * TODO: replace it everywhere by Answer::model()->findAll([Critieria Object])
     */
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


    public function getTypedesc()
    {
        $types = self::typeList();
        $typeDesc = $types[$this->type]["description"];

        if(YII_DEBUG)
        {
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
        /**
         * @todo Check if this actually does anything, since the values are arrays.
         */
        asort($questionTypes);

        return $questionTypes;
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
        switch($sType)
        {
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
        $language = Survey::model()->findByPk($this->sid)->language;
        return QuestionGroup::model()->findAll("sid=:sid and language=:lang",array(':sid'=>$this->sid, ':lang'=>$language));
        //return QuestionGroup::model()->getGroups($this->sid);
    }

    public function getbuttons()
    {

        $url         = Yii::app()->createUrl("/admin/questions/sa/view/surveyid/");
        $url        .= '/'.$this->sid.'/gid/'.$this->gid.'/qid/'.$this->qid;
        $button      = '<a class="btn btn-default" href="'.$url.'" role="button"><span class="glyphicon glyphicon-pencil" ></span></a>';
        $previewUrl  = Yii::app()->createUrl("survey/index/action/previewquestion/sid/");
        $previewUrl .= '/'.$this->sid.'/gid/'.$this->gid.'/qid/'.$this->qid;
        $editurl     = Yii::app()->createUrl("admin/questions/sa/editquestion/surveyid/$this->sid/gid/$this->gid/qid/$this->qid");
        $button      = '<a class="btn btn-default open-preview"  data-toggle="tooltip" title="'.gT("Question preview").'"  aria-data-url="'.$previewUrl.'" aria-data-sid="'.$this->sid.'" aria-data-gid="'.$this->gid.'" aria-data-qid="'.$this->qid.'" aria-data-language="'.$this->language.'" href="# role="button" ><span class="glyphicon glyphicon-eye-open"  ></span></a> ';

        if (Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'update'))
        {
            $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Edit question").'" href="'.$editurl.'" role="button"><span class="glyphicon glyphicon-pencil" ></span></a>';
        }

        if (Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'read'))
        {
            $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Question summary").'" href="'.$url.'" role="button"><span class="glyphicon glyphicon-list-alt" ></span></a>';
        }

        $oSurvey = Survey::model()->findByPk($this->sid);

        if($oSurvey->active != "Y" && Permission::model()->hasSurveyPermission($this->sid,'surveycontent','delete' ))
        {
                $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Delete").'" href="#" role="button"
                            onclick="if (confirm(\' '.gT("Deleting  will also delete any answer options and subquestions it includes. Are you sure you want to continue?","js").' \' )){ '.convertGETtoPOST(Yii::app()->createUrl("admin/questions/sa/delete/surveyid/$this->sid/gid/$this->gid/qid/$this->qid")).'} ">
                                <span class="text-danger glyphicon glyphicon-trash"></span>
                                </a>';
        }

        return $button;
    }

    public function getOrderedAnswers($random=0, $alpha=0)
    {
        //question attribute random order set?
        if ($random==1)
        {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid='$this->qid' AND language='$this->language' and scale_id=0 ORDER BY ".dbRandom();
        }

        //question attribute alphasort set?
        elseif ($alpha==1)
        {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid='$this->qid' AND language='$this->language' and scale_id=0 ORDER BY answer";
        }

        //no question attributes -> order by sortorder
        else
        {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid='$this->qid' AND language='$this->language' and scale_id=0 ORDER BY sortorder, answer";
        }

        $ansresult = dbExecuteAssoc($ansquery)->readAll();
        return $ansresult;

    }

    /**
    * get subquestions fort the current question object in the right order
    */
    public function getOrderedSubQuestions($random=0, $exclude_all_others='')
    {
        if ($random==1)
        {
            // TODO : USE AR PATTERN
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid='$this->qid' AND scale_id=0 AND language='$this->language' ORDER BY ".dbRandom();
        }
        else
        {
            // TODO : USE AR PATTERN
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid='$this->qid' AND scale_id=0 AND language='$this->language' ORDER BY question_order";
        }

        $ansresult = dbExecuteAssoc($ansquery)->readAll();  //Checked

        //if  exclude_all_others is set then the related answer should keep its position at all times
        //thats why we have to re-position it if it has been randomized
        if (trim($exclude_all_others)!='' && $random==1)
        {
            $position=0;
            foreach ($ansresult as $answer)
            {
                if (  ($answer['title']==trim($exclude_all_others)))
                {
                    if ($position==$answer['question_order']-1) break; //already in the right position
                    $tmp  = array_splice($ansresult, $position, 1);
                    array_splice($ansresult, $answer['question_order']-1, 0, $tmp);
                    break;
                }
                $position++;
            }
        }

        return $ansresult;
    }

    public function getMandatoryIcon()
    {
        if ($this->type != "X"  && $this->type != "|")
        {
            $sIcon = ($this->mandatory=="Y")?'<span class="fa fa-asterisk text-danger"></span>':'<span></span>';
        }
        else
        {
            $sIcon = '<span class="fa fa-ban text-danger" data-toggle="tooltip" title="'.gT('Not relevant for this question type').'"></span>';
        }
        return $sIcon;
    }

    public function getOtherIcon()
    {

        if (( $this->type == "L") || ($this->type == "!") || ($this->type == "P") || ($this->type=="M"))
        {
            $sIcon = ($this->other==="Y")?'<span class="fa fa-dot-circle-o"></span>':'<span></span>';
        }
        else
        {
            $sIcon = '<span class="fa fa-ban text-danger" data-toggle="tooltip" title="'.gT('Not relevant for this question type').'"></span>';
        }
        return $sIcon;
    }

    /**
     * Get an new title/code for a question
     * @param integer|string $index base for question code (exemple : inde of question when survey import)
     * @return string|null : new title, null if impossible
     */
    public function getNewTitle($index=0)
    {
        $sOldTitle=$this->title;
        if($this->validate(array('title'))){
            return $sOldTitle;
        }
        /* Maybe it's an old invalid title : try to fix it */
        $sNewTitle=preg_replace("/[^A-Za-z0-9]/", '', $sOldTitle);
        if (is_numeric(substr($sNewTitle,0,1)))
        {
            $sNewTitle='q' . $sNewTitle;
        }
        /* Maybe there are another question with same title try to fix it 10 times */
        $attempts = 0;
        while (!$this->validate(array('title')))
        {
            $rand = mt_rand(0, 1024);
            $sNewTitle= 'q' . $index.'r' . $rand ;
            $this->title = $sNewTitle;
            $attempts++;
            if ($attempts > 10)
            {
                $this->addError('title', 'Failed to resolve question code problems after 10 attempts.');
                return null;
            }
        }
        return $sNewTitle;
    }

    public function search()
    {
        $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);

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

        $sort->defaultOrder = array('question_order' => CSort::SORT_ASC );

        $criteria = new CDbCriteria;
        $criteria->with=array('groups');
        $criteria->compare("t.sid", $this->sid, false, 'AND' );
        $criteria->compare("t.language", $this->language, false, 'AND' );
        $criteria->compare("t.parent_qid", 0, false, 'AND' );

        $criteria2 = new CDbCriteria;
        $criteria2->compare('t.title', $this->title, true, 'OR');
        $criteria2->compare('t.question', $this->title, true, 'OR');
        $criteria2->compare('t.type', $this->title, true, 'OR');

        $qid_reference = (Yii::app()->db->getDriverName() == 'pgsql' ?' t.qid::varchar' : 't.qid');
        $criteria2->compare($qid_reference, $this->title, true, 'OR');


        if($this->group_name != '')
        {
            $criteria->compare('groups.group_name', $this->group_name, true, 'AND');
        }

        $criteria->mergeWith($criteria2, 'AND');

        $dataProvider=new CActiveDataProvider('Question', array(
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
        if (parent::beforeSave())
        {
            $surveyIsActive = Survey::model()->findByPk($this->sid)->active !== 'N';

            if ($surveyIsActive && $this->getIsNewRecord())
            {
                return false;
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Used in frontend helper, buildsurveysession.
     * @param int $surveyid
     * @return int
     */
    public static function getTotalQuestions($surveyid)
    {
        $sQuery = "SELECT count(*)\n"
        ." FROM {{groups}} INNER JOIN {{questions}} ON {{groups}}.gid = {{questions}}.gid\n"
        ." WHERE {{questions}}.sid=".$surveyid."\n"
        ." AND {{groups}}.language='".App()->getLanguage()."'\n"
        ." AND {{questions}}.language='".App()->getLanguage()."'\n"
        ." AND {{questions}}.parent_qid=0\n";
        return Yii::app()->db->createCommand($sQuery)->queryScalar();
    }

    /**
     * Used in frontend helper, buildsurveysession.
     * @todo Rename
     * @param int $surveyid
     * @return array|false??? Return from CDbDataReader::read()
     */
    public static function getNumberOfQuestions($surveyid)
    {
        return dbExecuteAssoc("SELECT count(*)\n"
        ." FROM {{questions}}"
        ." WHERE type in ('X','*')\n"
        ." AND sid={$surveyid}"
        ." AND language='".$_SESSION['survey_'.$surveyid]['s_lang']."'"
        ." AND parent_qid=0")->read();
    }

}
