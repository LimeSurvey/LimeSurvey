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
 * Class FailedLoginAttempt
 *
 * @property integer $id
 * @property string $ip Ip address
 * @property string $last_attempt
 * @property integer $number_attempts
 */
class FailedLoginAttempt extends LSActiveRecord
{
    /**
     * @inheritdoc
     * @return FailedLoginAttempt
     */
    public static function model($class = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($class);
        return $model;
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{failed_login_attempts}}';
    }

    /**
     * Deletes all the attempts by IP
     *
     * @access public
     * @return void
     */
    public function deleteAttempts()
    {
        $ip = substr($_SERVER['REMOTE_ADDR'], 0, 40);
        $this->deleteAllByAttributes(array('ip' => $ip));
    }

    /**
     * Check if an IP address is allowed to login or not
     *
     * @return boolean Returns true if the user is blocked
     */
    public function isLockedOut()
    {
        $isLockedOut = false;
        $ip = substr($_SERVER['REMOTE_ADDR'], 0, 40);
        $criteria = new CDbCriteria;
        $criteria->condition = 'number_attempts > :attempts AND ip = :ip';
        $criteria->params = array(':attempts' => Yii::app()->getConfig('maxLoginAttempt'), ':ip' => $ip);

        $row = $this->find($criteria);

        if ($row != null) {
            $lastattempt = strtotime($row->last_attempt);
            if (time() > $lastattempt + Yii::app()->getConfig('timeOutTime')) {
                $this->deleteAttempts();
            } else {
                $isLockedOut = true;
            }
        }
        return $isLockedOut;
    }

    /**
     * This function removes obsolete login attempts
     * TODO
     */
    public function cleanOutOldAttempts()
    {
        // this where select whole part
        //$this->db->where('now() > (last_attempt+'.$this->config->item("timeOutTime").')');
        //return $this->db->delete('failed_login_attempts');
    }

    /**
     * Records an failed login-attempt if IP is not already locked out
     *
     * @access public
     * @return true
     */
    public function addAttempt()
    {
        if (!$this->isLockedOut()) {
            $timestamp = date("Y-m-d H:i:s");
            $ip = substr($_SERVER['REMOTE_ADDR'], 0, 40);
            $row = $this->findByAttributes(array('ip' => $ip));
    
            if ($row !== null) {
                $row->number_attempts = $row->number_attempts + 1;
                $row->last_attempt = $timestamp;
                $row->save();
            } else {
                $record = new FailedLoginAttempt;
                $record->ip = $ip;
                $record->number_attempts = 1;
                $record->last_attempt = $timestamp;
                $record->save();
            }
        }
        return true;
    }
}
