<?php

/*
   * LimeSurvey
   * Copyright (C) 2013-2026 The LimeSurvey Project Team
   * All rights reserved.
   * License: GNU/GPL License v2 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
     *  Files Purpose: lots of common functions
*/

/**
 * Class QuotaMember
 *
 * @property integer $id ID (primary key)
 * @property integer $sid Survey ID
 * @property integer $qid Question ID
 * @property integer $quota_id Quota ID
 * @property string $code Answer code
 *
 * @property Survey $survey
 * @property Question $question
 * @property Quota $quota
 * @property array $memberInfo
 */
class QuotaMember extends LSActiveRecord
{
    /**
     * @inheritdoc
     * @return QuotaMember
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('code', 'required', 'on' => array('create')),
            array('code', 'length', 'max' => 11)

        );
    }
    /**
     * Returns the relations
     *
     * @access public
     * @return array
     */
    public function relations()
    {
        return array(
            'survey' => array(self::BELONGS_TO, 'Survey', 'sid'),
            'question' => array(self::BELONGS_TO, 'Question', 'qid'),
            'quota' => array(self::BELONGS_TO, 'Quota', 'quota_id'),
        );
    }
    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{quota_members}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * @return array
     */
    public function getMemberInfo()
    {
        $sFieldName = null;
        $sValue = null;
        if ($this->question) {
            switch ($this->question->type) {
                case "L":
                case "O":
                case "!":
                case "I":
                case "G":
                case "Y":
                case "*":
                    $sFieldName = 'Q' . $this->qid;
                    $sValue = $this->code;
                    break;
                case "M":
                    $child = Question::model()->find('parent_qid = :parent_qid and title = :code', [':parent_qid' => $this->qid, ':code' => $this->code]);
                    $sFieldName = 'Q' . $this->qid . '_S' . $child->qid;
                    $sValue = "Y";
                    break;
                case "A":
                case "B":
                    $temp = explode('-', $this->code);
                    $sFieldName = 'Q' . $this->qid . '_S' . $temp[0];
                    $sValue = $temp[1];
                    break;
                default:
                    // "Impossible" situation.
                    \Yii::log(
                        sprintf(
                            "This question type %s is not supported for quotas and should not have been possible to set!",
                            $this->question->type
                        ),
                        'warning',
                        'application.model.QuotaMember'
                    );
                    break;
            }

            return array(
                'title' => $this->question->title,
                'type' => $this->question->type,
                'code' => $this->code,
                'value' => $sValue,
                'qid' => $this->qid,
                'fieldname' => $sFieldName,
            );
        }
        return [];
    }

    /**
     * @param $data
     * @return bool
     * @deprecated at 2018-01-29 use $model->attributes = $data && $model->save()
     */
    public function insertRecords($data)
    {
        $members = new self();
        foreach ($data as $k => $v) {
                    $members->$k = $v;
        }
        return $members->save();
    }
}
