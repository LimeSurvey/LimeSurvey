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
 * @property integer $label_id Related Label ID
 * @property string $title title
 * @property string $language connected language
 */
class LabelL10n extends LSActiveRecord
{
    /**
     * Used for some statistical queries
     * @var int
     */
    public $maxsortorder;

    /** @inheritdoc */
    public function tableName()
    {
        return '{{label_l10ns}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
    }
    /**
     * @inheritdoc
     * @return LabelL10n
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
            array('label_id', 'numerical', 'integerOnly' => true),
            array('title', 'LSYii_Validators'),
            array('language', 'length', 'min' => 2, 'max' => 20), // in array languages ?
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'label' => array(self::BELONGS_TO, 'Label', 'label_id')
        );
    }

    public function defaultScope()
    {
        return array('index' => 'language');
    }
}
