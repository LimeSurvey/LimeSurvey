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
*/

/**
 * Class DefaultValue
 *
 * @property integer $qid Question id
 * @property integer $scale_id
 * @property string $language
 * @property string $specialtype
 * @property string $defaultvalue
 *
 * @property Question $question
 */
class DefaultValue extends LSActiveRecord
{
    /* Default value when create (from DB) , leave some because add rules */
    public $specialtype = '';
    public $scale_id = '';
    public $sqid = 0;
    public $language = ''; // required ?

    /**
     * @inheritdoc
     * @return DefaultValue
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
        return '{{defaultvalues}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return array('qid', 'specialtype', 'scale_id', 'sqid', 'language');
    }

    /** @inheritdoc */
    public function relations()
    {
        $alias = $this->getTableAlias();
        return array(
            'question' => array(self::HAS_ONE, 'Question', '',
                'on' => "$alias.qid = question.qid",
            ),
        );
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('qid', 'required'),
            array('qid', 'numerical', 'integerOnly'=>true),
            array('qid', 'unique', 'criteria'=>array(
                    'condition'=>'specialtype=:specialtype and scale_id=:scale_id and sqid=:sqid and language=:language',
                    'params'=>array(
                        ':specialtype'=>$this->specialtype,
                        ':scale_id'=>$this->scale_id,
                        ':sqid'=>$this->sqid,
                        ':language'=>$this->language,
                    )
                ),
                'message'=>'{attribute} "{value}" is already in use.'),
        );
    }

    public function insertRecords($data)
    {
        $oRecord = new self;
        foreach ($data as $k => $v) {
                    $oRecord->$k = $v;
        }
        if ($oRecord->validate()) {
                    return $oRecord->save();
        }
        tracevar($oRecord->getErrors());
    }
}
