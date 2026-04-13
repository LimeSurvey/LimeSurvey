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
     *  Files Purpose: lots of common functions
*/

/**
 * Class Session
 * Extend CActiveRecord and not LSActiveRecord to disable plugin event (session can be used a lot)
 *
 * @property string $id Primary Key
 * @property integer $expire
 * @property string $data
 */
class Session extends CActiveRecord
{
    /** @var mixed $dataBackup to reset $data after save */
    private $dataBackup = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->attachEventHandler("onBeforeSave", array($this, 'fixDataType'));
        $this->attachEventHandler("onAfterSave", array($this, 'resetDataType'));
    }
    /**
     * @inheritdoc
     * @return Session
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
        return '{{sessions}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
    }

    /** @inheritdoc */
    public function afterFind()
    {
        $sDatabasetype = Yii::app()->db->getDriverName();
        // Postgres delivers a stream pointer
        if (gettype($this->data) == 'resource') {
            $this->data = stream_get_contents($this->data, -1, 0);
        }
        return parent::afterFind();
    }

    /**
     * Update data before saving
     * @see \CDbHttpSession
     * @return void
     */
    public function fixDataType()
    {
        $this->dataBackup = $this->data;
        $db = $this->getDbConnection();
        $dbType = $db->getDriverName();
        switch ($dbType) {
            case 'sqlsrv':
            case 'mssql':
            case 'dblib':
                $this->data = new CDbExpression('CONVERT(VARBINARY(MAX), ' . $db->quoteValue($this->data) . ')');
                break;
            case 'pgsql':
                $this->data = new CDbExpression($db->quoteValueWithType($this->data, PDO::PARAM_LOB) . "::bytea");
                break;
            case 'mysql':
                // Don't seems to need something
            default:
                // No update
        }
    }

    /**
     * Reset data after saving
     * @return void
     */
    public function resetDataType()
    {
        $this->data = $this->dataBackup;
        $this->dataBackup = null;
    }
}
