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
 * Class SurveyLanguageSetting
 *
 * @property string $language QuestionGroup language code. Note: There is a unique key on qid & language columns combined
 * @property string $group_name QuestionGroup dieplay text. The actual question.
 * @property string $description QuestionGroup help-text for display
 */
class QuestionGroupL10n extends LSActiveRecord
{
    /** @inheritdoc */
    public function tableName()
    {
        return '{{group_l10ns}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * @inheritdoc
     * @return self
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    public function relations()
    {
        $alias = $this->getTableAlias();
        return array(
            'group' => array(self::BELONGS_TO, QuestionGroup::class, '', 'on' => "$alias.gid = group.gid"),
        );
    }

    public function defaultScope()
    {
        return array('index' => 'language');
    }

    /** @inheritdoc */
    public function rules()
    {
        return array(
            array('group_name,description', 'LSYii_Validators'),
            array('language', 'length', 'min' => 2, 'max' => 20), // in array languages ?
            array('gid', 'unique', 'criteria' => array(
                'condition' => 'language=:language',
                'params' => array(':language' => $this->language)
                ),
                'message' => sprintf(gT("Group ID (gid): “%s” already set with language ”%s”."), $this->gid, $this->language),
            ),
        );
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return array(
            'language' => gT('Language'),
            'group_name' => gT('Group name'),
        );
    }
}
