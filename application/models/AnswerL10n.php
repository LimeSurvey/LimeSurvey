<?php if (!defined('BASEPATH')) {
    die('No direct script access allowed');
}
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
 * Class SurveyLanguageSetting
 *
 * @property int $id primary key
 * @property int $aid Answer Id
 * @property string $answer The answer text
 * @property string $language language code
 *
 */
class AnswerL10n extends LSActiveRecord
{

    /** @inheritdoc */
    public function tableName()
    {
        return '{{answer_l10ns}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
    }

    public function defaultScope()
    {
        return array('index'=>'language');
    }    

    /**
     * @inheritdoc
     * @return AnswerL10n
     */
    public static function model($class = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($class);
        return $model;
    }

    /** @inheritdoc */
    public function relations()
    {
        $alias = $this->getTableAlias();
        return array(
            //'question' => array(self::BELONGS_TO, 'answer', '', 'on' => "$alias.aid = answer.aid"),
        );
    }


    /** @inheritdoc */
    public function rules()
    {
        return [
            ['aid,language','required'],
            ['aid','numerical','integerOnly'=>true],
            ['answer', 'LSYii_Validators'],
            ['language', 'length', 'min' => 2, 'max'=>20], // in array languages ?
        ];
    }

}
