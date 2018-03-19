<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2007-2017 The LimeSurvey Project Team / Carsten Schmitz
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
 * Class Answer
 * @property integer $qid Question id
 * @property string $code Answer code
 * @property string $answer Answer text
 * @property integer $sortorder Answer sort order
 * @property integer $assessment_value
 * @property string $language Language code
 * @property integer $scale_id
 *
 * @property Question $questions
 * @property Question $groups
 */
class Answer extends LSActiveRecord
{
    /**
     * @inheritdoc
     * @return Answer
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
        return '{{answers}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return array('qid', 'code', 'language', 'scale_id');
    }

    /** @inheritdoc */
    public function relations()
    {
        $alias = $this->getTableAlias();
        return array(
            // TODO HAS_ONE relation should be in singular, not plural $answer->group, $answer->question
            'questions' => array(self::HAS_ONE, 'Question', '',
                'on' => "$alias.qid = questions.qid",
            ),
            'groups' => array(self::HAS_ONE, 'QuestionGroup', '', 'through' => 'questions',
                'on' => 'questions.gid = groups.gid'
            ),
        );
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('qid', 'numerical', 'integerOnly'=>true),
            array('code', 'length', 'min' => 1, 'max'=>5),
            array('language', 'length', 'min' => 2, 'max'=>20), // in array languages ?
            // Unicity of key
            array(
                'code', 'unique', 'caseSensitive'=>false, 'criteria'=>array(
                    'condition' => 'language=:language AND qid=:qid AND scale_id=:scale_id',
                    'params' => array(
                        ':language' => $this->language,
                        ':qid' => $this->qid,
                        ':scale_id' => $this->scale_id
                    )
                ),
                'message' => gT('Answer codes must be unique by question.')
            ),
            array('answer', 'LSYii_Validators'),
            array('sortorder', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
            array('assessment_value', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
            array('scale_id', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
        );
    }

    /**
     * @param integer $qid
     * @return CDbDataReader
     */
    public function getAnswers($qid)
    {
        // TODO get via Question relations
        return Yii::app()->db->createCommand()
            ->select()
            ->from(self::tableName())
            ->where(array('and', 'qid='.$qid))
            ->order('code asc')
            ->query();
    }

    /**
     * Return the key=>value answer for a given $qid
     *
     * @staticvar array $answerCache
     * @param integer $qid
     * @param string $code
     * @param string $sLanguage
     * @param integer $iScaleID
     * @return array
     */
    public function getAnswerFromCode($qid, $code, $sLanguage, $iScaleID = 0)
    {
        static $answerCache = array();

        if (array_key_exists($qid, $answerCache)
                && array_key_exists($code, $answerCache[$qid])
                && array_key_exists($sLanguage, $answerCache[$qid][$code])
                && array_key_exists($iScaleID, $answerCache[$qid][$code][$sLanguage])) {
            // We have a hit :)
            return $answerCache[$qid][$code][$sLanguage][$iScaleID];
        } else {
            $answerCache[$qid][$code][$sLanguage][$iScaleID] = Yii::app()->db->cache(6)->createCommand()
            ->select('answer')
            ->from(self::tableName())
            ->where(array('and', 'qid=:qid', 'code=:code', 'scale_id=:scale_id', 'language=:lang'))
            ->bindParam(":qid", $qid, PDO::PARAM_INT)
            ->bindParam(":code", $code, PDO::PARAM_STR)
            ->bindParam(":lang", $sLanguage, PDO::PARAM_STR)
                        ->bindParam(":scale_id", $iScaleID, PDO::PARAM_INT)
            ->query()->readAll();

            return $answerCache[$qid][$code][$sLanguage][$iScaleID];
        }
    }

    /**
     * @param integer $newsid
     * @param integer $oldsid
     * @return static[]
     */
    public function oldNewInsertansTags($newsid, $oldsid)
    {
        $criteria = new CDbCriteria;
        $criteria->compare('questions.sid', $newsid);
        $criteria->compare('answer', '{INSERTANS::'.$oldsid.'X');
        return $this->with('questions')->findAll($criteria);
    }

    /**
     * @param array $data
     * @param bool|mixed $condition
     * @return int
     */
    public function updateRecord($data, $condition = false)
    {
        return Yii::app()->db->createCommand()->update(self::tableName(), $data, $condition ? $condition : '');
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
        Yii::log(\CVarDumper::dumpAsString($oRecord->getErrors()), 'warning', 'application.models.Answer.insertRecords');
    }

    /**
     * Updates sort order of answers inside a question
     *
     * @static
     * @access public
     * @param int $qid
     * @param string $lang
     * @return void
     */
    public static function updateSortOrder($qid, $lang)
    {
        $data = self::model()->findAllByAttributes(array('qid' => $qid, 'language' => $lang), array('order' => 'sortorder asc'));
        $position = 0;

        foreach ($data as $row) {
            $row->sortorder = $position++;
            $row->save();
        }
    }

    /**
     * @param integer $surveyid
     * @param string $lang
     * @param bool $return_query
     * @return array|CDbCommand
     */
    public function getAnswerQuery($surveyid, $lang, $return_query = true)
    {
        $query = Yii::app()->db->createCommand();
        $query->select("{{answers}}.*, {{questions}}.gid");
        $query->from("{{answers}}, {{questions}}");
        $query->where("{{questions}}.sid = :surveyid AND {{questions}}.qid = {{answers}}.qid AND {{questions}}.language = {{answers}}.language AND {{questions}}.language = :lang");
        $query->order('qid, code, sortorder');
        $query->bindParams(":surveyid", $surveyid, PDO::PARAM_INT);
        $query->bindParams(":lang", $lang, PDO::PARAM_STR);
        return ($return_query) ? $query->queryAll() : $query;
    }

    function getAllRecords($condition, $order = false)
    {
        $command = Yii::app()->db->createCommand()->select('*')->from($this->tableName())->where($condition);
        if ($order != false) {
            $command->order($order);
        }
        return $command->query();
    }

    /**
     * @param string $fields
     * @param string $orderby
     * @param mixed $condition
     * @return array
     */
    public function getQuestionsForStatistics($fields, $condition, $orderby)
    {
        return Yii::app()->db->createCommand()
            ->select($fields)
            ->from(self::tableName())
            ->where($condition)
            ->order($orderby)
            ->queryAll();
    }
}
