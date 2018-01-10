<?php if (!defined('BASEPATH')) {
    die('No direct script access allowed');
}
/*
 * LimeSurvey (tm)
 * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *
 */

/**
 * Class Label
 *
 * @property integer $id Primary Key
 * @property integer $lid Related Label Set
 * @property string $code
 * @property string $title
 * @property integer $sortorder
 * @property string $language
 * @property integer $assessment_value
 */
class Label extends LSActiveRecord
{
    /**
     * Used for some statistical queries
     * @var int
     */
    public $maxsortorder;

    /** @inheritdoc */
    public function tableName()
    {
        return '{{labels}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return array('id');
    }
    /**
     * @inheritdoc
     * @return Label
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
            array('lid', 'numerical', 'integerOnly'=>true),
            array('code', 'unique', 'caseSensitive'=>true, 'criteria'=>array(
                            'condition'=>'lid = :lid AND language=:language',
                            'params'=>array(':lid'=>$this->lid, ':language'=>$this->language)
                    ),
                    'message'=>'{attribute} "{value}" is already in use.'),
            array('title', 'LSYii_Validators'),
            array('sortorder', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
            array('language', 'length', 'min' => 2, 'max'=>20), // in array languages ?
            array('assessment_value', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>true),
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'labelset' => array(self::HAS_ONE, 'LabelSet', 'lid')
        );
    }

    /**
     * @param mixed|bool $condition
     * @return static[]
     */
    public function getAllRecords($condition = false)
    {
        $criteria = new CDbCriteria;
        if ($condition != false) {
            foreach ($condition as $item => $value) {
                $criteria->addCondition($item.'="'.$value.'"');
            }
        }

        return $this->findAll($criteria);
    }

    /**
     * @param integer $lid
     * @return array
     */
    public function getLabelCodeInfo($lid)
    {
        return Yii::app()->db->createCommand()->select('code, title, sortorder, language, assessment_value')->order('language, sortorder, code')->where('lid=:lid')->from($this->tableName())->bindParam(":lid", $lid, PDO::PARAM_INT)->query()->readAll();
    }

    public function insertRecords($data)
    {
        $lbls = new self;
        foreach ($data as $k => $v) {
                    $lbls->$k = $v;
        }
        $lbls->save();
    }

}
