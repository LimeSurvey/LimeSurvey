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
        * @return string
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
                'groups' => array(self::HAS_ONE, 'QuestionGroup', '', 'on' => "$alias.gid = groups.gid AND $alias.language = groups.language"),
                'parents' => array(self::HAS_ONE, 'Question', '', 'on' => "$alias.parent_qid = parents.qid"),
                'subquestions' => array(self::HAS_MANY, 'Question', 'parent_qid', 'on' => "$alias.language = subquestions.language")

            );
        }

        /**
        * Returns this model's validation rules
        *
        */
        public function rules()
        {
            $clang = Yii::app()->lang;
            $aRules= array(
                        array('title','required','on' => 'update, insert'),// 140207 : Before was commented, put only on update/insert ?
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
                                'message' => $clang->gT('Subquestion codes must be unique.'));
            }
            if($this->qid && $this->language)
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
                                'message' => $clang->gT('Question codes must be unique.'), 'except' => 'archiveimport');
                $aRules[]= array('title', 'match', 'pattern' => '/^[a-z,A-Z][[:alnum:]]*$/', 'message' => $clang->gT('Question codes must start with a letter and may only contain alphanumeric characters.'), 'except' => 'archiveimport');
            }
            else
            {
                $aRules[]= array('title', 'match', 'pattern' => '/^[[:alnum:]]*$/', 'message' => $clang->gT('Subquestion codes may only contain alphanumeric characters.'), 'except' => 'archiveimport');
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

    }

?>
