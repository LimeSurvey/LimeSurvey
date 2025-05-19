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
  *     Files Purpose: extension of SurveyDynamic class to handle archived versions
 */
class SurveyDynamicArchive extends SurveyDynamic
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
        return '{{old_survey_' . self::$sid . '_' . self::$timestamp . '}}';
    }

    /** @inheritdoc */
    public function relations()
    {
        if ($this->getbHaveToken()) {
            TokenDynamicArchive::sid(self::$sid);
            TokenDynamicArchive::setTimestamp(self::$timestamp);
            return array(
                'survey'   => array(self::HAS_ONE, 'Survey', array(), 'condition' => ('sid = ' . self::$sid)),
                'tokens'   => array(self::HAS_ONE, 'TokenDynamicArchive', array('token' => 'token')),
                'saved_control'   => array(self::HAS_ONE, 'SavedControl', array('srid' => 'id'), 'condition' => ('sid = ' . self::$sid))
            );
        } else {
            return array(
                'saved_control'   => array(self::HAS_ONE, 'SavedControl', array('srid' => 'id'), 'condition' => ('sid = ' . self::$sid))
            );
        }
    }

    /**
     * @return bool
     */
    protected function getbHaveToken(): bool
    {
        if (!isset($this->bHaveToken)) {
            $tableName = 'old_tokens_' . self::$sid . '_' . self::$timestamp;
            $this->bHaveToken = tableExists($tableName)
                && Permission::model()->hasSurveyPermission(self::$sid, 'tokens', 'read');
        }
        return $this->bHaveToken;
    }
}
