<?php

/*
 * LimeSurvey (tm)
 * Copyright (C) 2011-2026 The LimeSurvey Project Team
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
        return 'id';
    }

    /**
     * @inheritdoc
     * @return Label
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
            array('lid', 'numerical', 'integerOnly' => true),
            array('code', 'unique', 'caseSensitive' => true, 'criteria' => array(
                            'condition' => 'lid = :lid',
                            'params' => array(':lid' => $this->lid)
                    ),
                    'message' => '{attribute} "{value}" is already in use.'),
            // Only alphanumeric
            array(
                'code',
                'match',
                'pattern' => '/^[[:alnum:]]*$/',
                'message' => gT('Label codes may only contain alphanumeric characters.'),
            ),
            array('sortorder', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('assessment_value', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'labelset' => array(self::BELONGS_TO, 'LabelSet', 'lid'),
            'labell10ns' => array(self::HAS_MANY, 'LabelL10n', 'label_id')
        );
    }

    public function getTranslated($sLanguage)
    {
        $ol10N = $this->labell10ns;
        if (isset($ol10N[$sLanguage])) {
            return array_merge($this->attributes, $ol10N[$sLanguage]->attributes);
        }

        return [];
    }

    /**
     * @param integer $lid
     * @return array
     */
    public function getLabelCodeInfo($lid)
    {
        return Yii::app()->db->createCommand()->select('code, title, sortorder, language, assessment_value')->order('language, sortorder, code')->where('lid=:lid')->from($this->tableName())->bindParam(":lid", $lid, PDO::PARAM_INT)->query()->readAll();
    }

    /**
     * @param $data
     * @deprecated at 2018-02-03 use $model->attributes = $data && $model->save()
     */
    public function insertRecords($data)
    {
        $lbls = new self();
        foreach ($data as $k => $v) {
                    $lbls->$k = $v;
        }
        $lbls->save();
    }
}
