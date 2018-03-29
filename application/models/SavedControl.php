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
 * Class SavedControl
 * @property integer $scid Primary key
 * @property integer $sid Survey id
 * @property integer $srid
 * @property string $identifier
 * @property string $access_code
 * @property string $email
 * @property string $ip
 * @property string $saved_thisstep
 * @property string $status
 * @property string $saved_date
 * @property string $refurl
 */
class SavedControl extends LSActiveRecord
{

    /** @inheritdoc */
    public function tableName()
    {
        return '{{saved_control}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'scid';
    }

    /**
     * @inheritdoc
     * @return CActiveRecord
     */
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }


    public function getAllRecords($condition = false)
    {
        if ($condition != false) {
            $this->db->where($condition);
        }

        $data = $this->db->get('saved_control');

        return $data;
    }

    /**
     * @param int $sid
     * @return mixed
     */
    public function getCountOfAll($sid)
    {
        $data = Yii::app()->db->createCommand("SELECT COUNT(*) AS countall FROM {{saved_control}} WHERE sid=:sid")->bindParam(":sid", $sid, PDO::PARAM_INT)->query();
        $row = $data->read();

        return $row['countall'];
    }

    /**
     * Deletes some records meeting specified condition
     *
     * @access public
     * @param array $condition
     * @return int (rows deleted)
     */
    public function deleteSomeRecords($condition)
    {
        $record = new self;
        $criteria = new CDbCriteria;

        if ($condition != false) {
            foreach ($condition as $column=>$value) {
                $criteria->addCondition("$column='$value'");
            }
        }

        return $record->deleteAll($criteria);
    }

    public function insertRecords($data)
    {
        return $this->db->insert('saved_control', $data);
    }

}
