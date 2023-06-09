<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
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
 * This is the model class for table "{{{{participant_attribute_names_lang}}}}".
 *
 * The following are the available columns in table '{{{{participant_attribute_names_lang}}}}':
 * @property integer $attribute_id
 * @property string $attribute_name
 * @property string $lang
 *
 * @property ParticipantAttributeName $participant_attribute_name
 */
class ParticipantAttributeNameLang extends LSActiveRecord
{
    /** @inheritdoc */
    public function primaryKey()
    {
        return array('attribute_id', 'lang');
    }

    /**
     * @inheritdoc
     * @return ParticipantAttributeNameLang
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
        return '{{participant_attribute_names_lang}}';
    }

    /** @inheritdoc */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that will receive user inputs.
        return array(
            array('attribute_name', 'LSYii_FilterValidator', 'filter' => 'strip_tags', 'skipOnEmpty' => true),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('attribute_id, attribute_name, lang', 'safe', 'on' => 'search'),
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'participant_attribute_name' => array(self::BELONGS_TO, 'ParticipantAttributeName', 'attribute_id')
        );
    }
}
