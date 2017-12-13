<?php if (!defined('BASEPATH')) {
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
     *	Files Purpose: lots of common functions
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
    public static function model($class = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($class);
        return $model;
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('code', 'required', 'on'=>array('create'))
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
                    $sFieldName = $this->sid.'X'.$this->question->gid.'X'.$this->qid;
                    $sValue = $this->code;
                    break;
                case "M":
                    $sFieldName = $this->sid.'X'.$this->question->gid.'X'.$this->qid.$this->code;
                    $sValue = "Y";
                    break;
                case "A":
                case "B":
                    $temp = explode('-', $this->code);
                    $sFieldName = $this->sid.'X'.$this->question->gid.'X'.$this->qid.$temp[0];
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


    }

    public function insertRecords($data)
    {
        $members = new self;
        foreach ($data as $k => $v) {
                    $members->$k = $v;
        }
        return $members->save();
    }
}
