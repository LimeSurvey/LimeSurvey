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
 * Class DefaultValue
 *
 * @property integer $id Primary Key
 * @property integer $dvid
 * @property string $defaultvalue default value
 * @property string $language connected language
 */
class DefaultValueL10n extends LSActiveRecord
{
    /**
     * Used for some statistical queries
     * @var int
     */
    public $maxsortorder;

    /** @inheritdoc */
    public function tableName()
    {
        return '{{defaultvalue_l10ns}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
    }
    /**
     * @inheritdoc
     * @return DefaultValueL10n
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
            array('dvid', 'numerical', 'integerOnly' => true),
            array('defaultvalue', 'LSYii_Validators'),
            array('language', 'length', 'min' => 2, 'max' => 20), // in array languages ?
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        $alias = $this->getTableAlias();
        return array(
            'defaultvalue' => array(self::BELONGS_TO, 'defaultvalue', '', 'on' => "$alias.dvid = defaultvalue.dvid"),
        );
    }

    public function defaultScope()
    {
        return array('index' => 'language');
    }
}
