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
*/

/**
 * Class DefaultValue
 * The default values are default answers for questions that could be selected by the user.
 * (e.g. a subquestion that is selected as the default answer for the question in advance)
 *
 * @property integer $dvid primary key
 * @property integer $qid The question id
 * @property integer $scale_id Scale of question
 * @property string $specialtype of column “other” currently (no GUI for comments)
 *
 * @property Question $question
 *
 * @property DefaultValueL10n[] $defaultvalueL10ns
 */
class DefaultValue extends LSActiveRecord
{
    /* Default value when create (from DB) , leave some because add rules */
    public $specialtype = '';
    public $scale_id = '';
    public $sqid = 0;

    /**
     * @inheritdoc
     * @return DefaultValue
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
        return '{{defaultvalues}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return array('dvid');
    }

    /** @inheritdoc */
    public function relations()
    {
        return array(
            'question' => array(self::HAS_ONE, 'Question', "qid"),
            'defaultvaluel10ns' => array(self::HAS_MANY, 'DefaultValueL10n', 'dvid')
        );
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('qid', 'required'),
            array('qid,sqid,scale_id', 'numerical', 'integerOnly' => true),
        );
    }

    /**
     * @param $data
     * @return bool
     * @deprecated at 2018-02-03 use $model->attributes = $data && $model->save()
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
        tracevar($oRecord->getErrors());
    }
    /*
    public function getDefaultValue($language = 'en')
    {
        $oDefaultValue = $this->with('defaultvaluel10ns')->find('language = :language', array(':language' => $language));
        return $oDefaultValue->defaultvaluel10ns[$language]->defaultvalue;
    }*/
}
