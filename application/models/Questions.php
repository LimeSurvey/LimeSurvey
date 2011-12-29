<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * 	$Id: common_helper.php 11335 2011-11-08 12:06:48Z c_schmitz $
 * 	Files Purpose: lots of common functions
 */

class Questions extends CActiveRecord
{

    /**
     * Returns the static model of Settings table
     *
     * @static
     * @access public
     * @return CActiveRecord
     */
    public static function model()
    {
        return parent::model(__CLASS__);
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
        return 'qid';
    }

    /**
     * Defines the relations for this model
     *
     * @access public
     * @return array
     */
    public function relations()
    {
        return array(
            'groups' => array(self::HAS_ONE, 'Groups', '',
                'on' => 't.gid = groups.gid',
            ),
        );
    }

    /**
     * Fixes sort order for questions in a group
     *
     * @static
     * @access public
     * @param int $gid
     * @param int $surveyid
     * @return void
     */
    public static function updateSortOrder($gid, $surveyid)
    {
        $questions = self::model()->findAllByAttributes(array('gid' => $gid, 'sid' => $surveyid, 'language' => GetBaseLanguageFromSurveyID($surveyid)));
        $p = 0;
        foreach ($questions as $question)
        {
            $question->question_order = $p;
            $question->save();
            $p++;
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
            $aLanguages = array_merge(array(GetBaseLanguageFromSurveyID($iSurveyID)), GetAdditionalLanguagesFromSurveyID($iSurveyID));
        }
        else
        {
            $aLanguages = array($sLanguage);
        }
        if ($iQuestionID != 0)
        {
            $aAttributeValues = getQuestionAttributeValues($iQuestionID, $sQuestionType);
        }
        $aAttributeNames = questionAttributes();
        $aAttributeNames = $aAttributeNames[$sQuestionType];
        uasort($aAttributeNames, 'CategorySort');
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
            ->where(array('and', 'sid=' . $sid, 'gid=' . $gid, 'language=:language', 'parent_qid=0'))
            ->order('question_order asc')
            ->bindParam(":language", $language, PDO::PARAM_STR)
            ->query();
    }

    function getSubQuestions($parent_qid)
    {
        return Yii::app()->db->createCommand()
        ->select()
        ->from(self::tableName())
        ->where(array('and', 'parent_qid=' . $parent_qid))
        ->order('question_order asc')
        ->query();
    }

    function getQuestionsWithSubQuestions($iSurveyID, $sLanguage, $sCondition = FALSE)
    {
        $dbprefix = Yii::app()->db->tablePrefix;
        $command = Yii::app()->db->createCommand()
        ->select($dbprefix . 'questions.*, q.qid as sqid, q.title as sqtitle,  q.question as sqquestion, ' . $dbprefix . 'groups.*')
        ->from($this->tableName())
        ->leftJoin($dbprefix . 'questions q', "q.parent_qid = {$dbprefix}questions.qid AND q.language = {$dbprefix}questions.language")
        ->join($dbprefix . 'groups', "{$dbprefix}groups.gid = {$dbprefix}questions.gid  AND {$dbprefix}questions.language = {$dbprefix}groups.language");
        $command->where("({$dbprefix}questions.sid = '$iSurveyID' AND {$dbprefix}questions.language = '$sLanguage' AND {$dbprefix}questions.parent_qid = 0)");
        if ($sCondition != FALSE)
        {
            $command->where("({$dbprefix}questions.sid = '$iSurveyID' AND {$dbprefix}questions.language = '$sLanguage' AND {$dbprefix}questions.parent_qid = 0) AND " . $sCondition);
        }
        $command->order("{$dbprefix}groups.group_order asc, {$dbprefix}questions.question_order asc");

        return $command->query()->readAll();
    }

    function insertRecords($data)
    {
        $questions = new self;
        foreach ($data as $k => $v)
            $questions->$k = $v;
        return $questions->save();
    }

    function getSomeRecords($fields, $condition, $order=NULL, $return_query = TRUE)
    {
        $record = Yii::app()->db->createCommand()
            ->select($fields)
            ->from(self::tableName())
            ->where($condition);

        if( $order != NULL )
        {
            $record->order($order);
        }

        return ( $return_query ) ? $record->queryAll() : $record;
    }

	function update($data, $condition=FALSE)
    {

        return Yii::app()->db->createCommand()->update('{{questions}}', $data, $condition);

    }

    public static function deleteAllById($questionsIds)
    {
        if ( !is_array($questionsIds) )
        {
            $questionsIds = array($questionsIds);
        }

        Yii::app()->db->createCommand()->delete(Conditions::model()->tableName(), array('in', 'qid', $questionsIds));
        Yii::app()->db->createCommand()->delete(Question_attributes::model()->tableName(), array('in', 'qid', $questionsIds));
        Yii::app()->db->createCommand()->delete(Answers::model()->tableName(), array('in', 'qid', $questionsIds));
        Yii::app()->db->createCommand()->delete(Questions::model()->tableName(), array('in', 'parent_qid', $questionsIds));
        Yii::app()->db->createCommand()->delete(Questions::model()->tableName(), array('in', 'qid', $questionsIds));
        Yii::app()->db->createCommand()->delete(Defaultvalues::model()->tableName(), array('in', 'qid', $questionsIds));
        Yii::app()->db->createCommand()->delete(Quota_members::model()->tableName(), array('in', 'qid', $questionsIds));
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

    public function getQuestionsForStatistics($fields, $condition, $orderby)
    {
        return Yii::app()->db->createCommand()
        ->select($fields)
        ->from(self::tableName())
        ->where($condition)
        ->order($orderby)
        ->queryAll();
    }

    public function getQuestionList($surveyid, $language)
    {
        $query = "SELECT questions.*, groups.group_name, groups.group_order\n"
        ." FROM {{questions}} as questions, {{groups}} as groups\n"
        ." WHERE groups.gid=questions.gid\n"
        ." AND groups.language='".$language."'\n"
        ." AND questions.language='".$language."'\n"
        ." AND questions.parent_qid=0\n"
        ." AND questions.sid=$surveyid";
        return Yii::app()->db->createCommand($query)->queryAll();
    }

}

?>
