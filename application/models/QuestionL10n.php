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
 * Class QuestionL10n
 * @property integer $id primary key
 * @property integer $qid question id
 * @property string $language Question language code. Note: There is a unique key on qid & language columns combined
 * @property string $question Question display text. The actual question.
 * @property string $help Question help-text for display
 * @property string $script Question script to be executed on runtime
 *
 */
class QuestionL10n extends LSActiveRecord
{

    /** @inheritdoc */
    public function tableName()
    {
        return '{{question_l10ns}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * @inheritdoc
     * @return QuestionL10n
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
        return array(
            // FIXME this conflicts with the attribute "question"
            //'question' => array(self::BELONGS_TO, 'Question', 'qid'),
        );
    }
    
    /**
     * This defaultScope indexes the ActiveRecords given back by language
     * Important: This does not work if you want to retrieve records for more than one question at a time.
     * in that case reset disable the defaultScope by using MyModel::model()->resetScope()->findAll();
     * @return array Scope that indexes the records by their language
     */
    public function defaultScope()
    {
        return array('index'=>'language');
    }

    /** @inheritdoc */
    public function rules()
    {
        $rules = array(
            ['qid,language', 'required'],
            ['qid', 'numerical', 'integerOnly'=>true],
            array('question', 'LSYii_Validators'),
            array('help', 'LSYii_Validators'),
            array('script', 'LSYii_Validators'),
            array('language', 'length', 'min' => 2, 'max'=>20), // in array languages ?
        );
        if (!Yii::app()->user->isScriptUpdateAllowed()) {
            $rules[] = array('script', 'LSYii_NoUpdateValidator');
        }
        return $rules;
    }
}
