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
 * @property string $language Question language code. Note: There is a unique key on qid & language columns combined
 * @property string $question Question dieplay text. The actual question.
 * @property string $help Question help-text for display


 */
class QuestionL10n extends LSActiveRecord
{

    /** @inheritdoc */
    public function tableName()
    {
        return '{{question_l10n}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * @inheritdoc
     * @return SurveyLanguageSetting
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
            'question' => array(self::BELONGS_TO, 'Question', 'qid'),
        );
    }
    
    public function defaultScope()
    {
        return array('index'=>'language');
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('question', 'LSYii_Validators'),
            array('help', 'LSYii_Validators'),
            array('language', 'length', 'min' => 2, 'max'=>20), // in array languages ?
        );
    }

}
