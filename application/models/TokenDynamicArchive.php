<?php

/*
   * LimeSurvey
   * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
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
 * Class TokenDynamicArchive
 *
 * @property integer $tid
 * @property string $participant_id
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $emailstatus
 * @property string $token
 * @property string $language
 * @property string $blacklisted
 * @property string $sent
 * @property string $remindersent
 * @property integer $remindercount
 * @property string $completed
 * @property integer $usesleft
 * @property string $validfrom
 * @property string $validuntil
 * @property integer $mpid //TODO Describe me!
 *
 * @property Survey $survey
 * @property SurveyDynamicArchive[] $responses
 *
 * @property array $standardCols
 * @property array $standardColsForGrid
 * @property array $custom_attributes
 */
class TokenDynamicArchive extends TokenDynamic
{
    /** @var int $timestamp */
    protected static $timestamp = 0;


     /**
     * Set the timestamp for next archive model.
     *
     * @param int $timestamp
     * @return void
     */
    public static function setTimestamp(int $timestamp): void
    {
        self::$timestamp = $timestamp;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{old_tokens_' . self::$sid . '_' . self::$timestamp . '}}';
    }

    /** @inheritdoc */
    public function relations()
    {
        SurveyDynamicArchive::sid(self::$sid);
        SurveyDynamicArchive::setTimestamp(self::$timestamp);
        return array(
            'survey'      => array(self::BELONGS_TO, 'Survey', array(), 'condition' => 'sid=' . self::$sid, 'together' => true),
            'responses'   => array(self::HAS_MANY, 'SurveyDynamicArchive', array('token' => 'token'))
        );
    }
}
