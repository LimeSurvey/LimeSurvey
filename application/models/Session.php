<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
     *	Files Purpose: lots of common functions
*/

/**
 * Class Session
 *
 * @property string $id Primary Key
 * @property integer $expire
 * @property string $data
 */
class Session extends CActiveRecord
{
    /**
     * @inheritdoc
     * @return Session
     */
    public static function model($class = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($class);
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
        // MSSQL delivers hex data (except for dblib driver)
        if ($sDatabasetype == 'sqlsrv' || $sDatabasetype == 'mssql') {
            $this->data = $this->hexToStr($this->data);
        }
        // Postgres delivers a stream pointer
        if (gettype($this->data) == 'resource') {
            $this->data = stream_get_contents($this->data, -1, 0);
        }
        return parent::afterFind();
    }

    private function hexToStr($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i].$hex[$i + 1]));
        }
        return $string;
    }

}
