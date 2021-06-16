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
 * Class FailedLoginAttempt
 *
 * @property integer $id
 * @property string $ip Ip address
 * @property string $last_attempt
 * @property integer $number_attempts
 */
class FailedLoginAttempt extends LSActiveRecord
{
    const TYPE_LOGIN = 'login';
    const TYPE_TOKEN = 'token';

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
        $ip = substr(getIPAddress(), 0, 40);
        $this->deleteAllByAttributes(array('ip' => $ip));
    }

    /**
     * Check if an IP address is allowed to login or not
     *
     * @param string $attemptType  The attempt type ('login' or 'token'). Used to check the white lists.
     *
     * @return boolean Returns true if the user is blocked
     */
    public function isLockedOut($attemptType = '')
    {
        $isLockedOut = false;
        $ip = substr(getIPAddress(), 0, 40);

        // Return false if IP is whitelisted
        if (!empty($attemptType) && $this->isWhitelisted($ip, $attemptType)) {
            return false;
        }

        $criteria = new CDbCriteria();
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
            $ip = substr(getIPAddress(), 0, 40);
            $row = $this->findByAttributes(array('ip' => $ip));
    
            if ($row !== null) {
                $row->number_attempts = $row->number_attempts + 1;
                $row->last_attempt = $timestamp;
                $row->save();
            } else {
                $record = new FailedLoginAttempt();
                $record->ip = $ip;
                $record->number_attempts = 1;
                $record->last_attempt = $timestamp;
                $record->save();
            }
        }
        return true;
    }

    /**
     * Returns true if the specified IP is whitelisted
     *
     * @param string $ip
     * @param string $attemptType   'login' or 'token'
     *
     * @throws InvalidArgumentException if an invalid attempt type is specified
     * @return boolean
     */
    private function isWhitelisted($ip, $attemptType)
    {
        // Init
        if ($attemptType != self::TYPE_LOGIN && $attemptType != self::TYPE_TOKEN) {
            throw new InvalidArgumentException(sprintf("Invalid attempt type: %s", $attemptType));
        }
        if (empty($ip)) {
            return false;
        }
        $binaryIP = inet_pton($ip);

        $whiteList = Yii::app()->getConfig($attemptType . 'IpWhitelist');
        if (empty($whiteList)) {
            return false;
        }

        // Validating
        $whiteListEntries = preg_split('/\n|,/', $whiteList);
        foreach ($whiteListEntries as $whiteListEntry) {
            if (empty($whiteListEntry)) {
                continue;
            }
            // Compare directly
            if ($whiteListEntry == $ip) {
                // The IP is whitelisted
                return true;
            }
            // Compare binary representations
            $binaryWhiteListEntry = inet_pton($whiteListEntry);
            if ($binaryWhiteListEntry !== false && $binaryWhiteListEntry == $binaryIP) {
                // The IP is whitelisted
                return true;
            }
        }

        // Not whitelisted
        return false;
    }
}
