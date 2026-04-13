<?php

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
 * @property integer $aid PK
 * @property integer $qid Question id
 * @property string $code Answer code
 * @property integer $sortorder Answer sort order
 * @property integer $assessment_value
 * @property integer $scale_id
 *
 * @property Question $question
 * @property Question $group
 * @property AnswerL10n[] $answerl10ns
 */
class Answer extends LSActiveRecord
{
    private $oldCode;
    private $oldQid;
    private $oldScaleId;

    /**
     * @inheritdoc
     * @return static
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
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
        return 'aid';
    }

    /** @inheritdoc */
    public function relations()
    {
        $alias = $this->getTableAlias();
        return array(
            'question' => array(self::BELONGS_TO, 'Question', '',
                'on' => "$alias.qid = question.qid",
            ),
            'group' => array(self::BELONGS_TO, 'QuestionGroup', '', 'through' => 'question',
                'on' => 'question.gid = ' . Yii::app()->db->quoteTableName('group') . '.gid'
            ),
            'answerl10ns' => array(self::HAS_MANY, 'AnswerL10n', 'aid', 'together' => true),
            'questionl10ns' => array(self::HAS_MANY, 'QuestionL10n', 'qid', 'together' => true)

        );
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('qid', 'numerical', 'integerOnly' => true),
            array('code', 'length', 'min' => 1, 'max' => 5),
            array('code', 'required'),
            // Only alphanumeric
            array(
                'code',
                'match',
                'pattern' => '/^[[:alnum:]]*$/',
                'message' => gT('Answer codes may only contain alphanumeric characters.'),
            ),
            // Unicity of key
            array(
                'code',
                'checkUniqueness',
                'message' => gT('Answer codes must be unique by question.'),
                'except' => 'saveall'
            ),
            array('sortorder', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('assessment_value', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('scale_id', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
        );
    }

    public function defaultScope()
    {
        return array(
            'order' => 'sortorder, code'
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
            ->where(array('and', 'qid=' . $qid))
            ->order('code asc')
            ->query();
    }

    public function checkUniqueness()
    {
        if ($this->code !== $this->oldCode || $this->qid != $this->oldQid || $this->scale_id != $this->oldScaleId) {
            $model = self::model()->find('code = ? AND qid = ? AND scale_id = ?', array($this->code, $this->qid, $this->scale_id));
            if ($model != null) {
                $this->addError('code', 'Answer codes must be unique by question');
            }
        }
    }

    protected function afterFind()
    {
        parent::afterFind();
        $this->oldCode = $this->code;
        $this->oldQid = $this->qid;
        $this->oldScaleId = $this->scale_id;
    }

    /**
     * Return the key=>value answer for a given $qid
     *
     * @staticvar array $answerCache
     * @param integer $qid
     * @param string $code
     * @param string $sLanguage
     * @param integer $iScaleID
     * @return string|null The answer text
     */
    public function getAnswerFromCode($qid, $code, $sLanguage, $iScaleID = 0)
    {
        static $answerCache = array();

        if (
            array_key_exists($qid, $answerCache)
                && array_key_exists($code, $answerCache[$qid])
                && array_key_exists($sLanguage, $answerCache[$qid][$code])
                && array_key_exists($iScaleID, $answerCache[$qid][$code][$sLanguage])
        ) {
            // We have a hit :)
            return $answerCache[$qid][$code][$sLanguage][$iScaleID];
        } else {
            $aAnswer = Answer::model()->findByAttributes(array('qid' => $qid, 'code' => $code, 'scale_id' => $iScaleID));
            if (is_null($aAnswer)) {
                return null;
            }
            if (!isset($aAnswer->answerl10ns[$sLanguage])) {
                Yii::log("AnswerL10n record missing for language \"{$sLanguage}\" and aid {$aAnswer->aid}", 'warning', 'application.models.Answer.getAnswerFromCode');
                return null;
            }
            $answerCache[$qid][$code][$sLanguage][$iScaleID] = $aAnswer->answerl10ns[$sLanguage]->answer;
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
        $criteria = new CDbCriteria();
        $criteria->compare('question.sid', $newsid);
        $criteria->with = ['answerl10ns' => array('condition' => "answer like '%{INSERTANS::{$oldsid}X%'"), 'question'];
        return $this->findAll($criteria);
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
     * @deprecated at 2018-01-29 use $model->attributes = $data && $model->save()
     *
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
        Yii::log(\CVarDumper::dumpAsString($oRecord->getErrors()), 'warning', 'application.models.Answer.insertRecords');
        return null;
    }

    /**
     * Updates sort order of answers inside a question
     *
     * @static
     * @access public
     * @param int $qid
     * @return void
     */
    public static function updateSortOrder($qid)
    {
        $data = self::model()->findAllByAttributes(array('qid' => $qid), array('order' => 'sortorder, code'));
        $position = 0;

        foreach ($data as $row) {
            $row->sortorder = $position++;
            $row->save();
        }
    }

    /**
     * @param string $fields
     * @param string $orderby
     * @param mixed $condition
     * @return array
     */
    public function getAnswersForStatistics($fields, $condition, $orderby)
    {
        return Answer::model()->with('answerl10ns')->findAll(['condition' => $condition, 'order' => $orderby]);
    }

    /**
     * @param string $fields
     * @param string $orderby
     * @param mixed $condition
     * @return array
     */
    public function getQuestionsForStatistics($fields, $condition, $orderby)
    {
        $oAnswers = Answer::model()->with('answerl10ns')->findAll(['condition' => $condition,'order' => $orderby]);
        $arr = array();
        foreach ($oAnswers as $key => $answer) {
            $arr[$key] = array_merge($answer->attributes, current($answer->answerl10ns)->attributes);
        }
        return $arr;
    }
}
