<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * 	$Id$
 * 	Files Purpose: lots of common functions
 */

class Survey_dynamic extends LSActiveRecord
{
    protected static $sid = 0;

    /**
     * Returns the static model of Settings table
     *
     * @static
     * @access public
     * @param int $surveyid
     * @return Survey_dynamic
     */
    public static function model($sid = NULL)
    {         
        $refresh = false;
        if (!is_null($sid)) {
            self::sid($sid);
            $refresh = true;
        }
        
        $model = parent::model(__CLASS__);
        
        //We need to refresh if we changed sid
        if ($refresh === true) $model->refreshMetaData();
        return $model;
    }

    /**
     * Sets the survey ID for the next model
     *
     * @static
     * @access public
     * @param int $sid
     * @return void
     */
    public static function sid($sid)
    {
        self::$sid = (int) $sid;
    }

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{survey_' . self::$sid . '}}';
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'sid';
    }

    /**
     * Insert records from $data array
     *
     * @access public
     * @param array $data
     * @return boolean
     */
    public function insertRecords($data)
    {
        $record = new self;
        foreach ($data as $k => $v)
        {
            $search = array('`', "'");
            $k = str_replace($search, '', $k);
            $v = str_replace($search, '', $v);
            $record->$k = $v;
        }
        return $record->save();
    }

    /**
     * Deletes some records from survey's table
     * according to specific condition
     *
     * @static
     * @access public
     * @param array $condition
     * @return int
     */
    public static function deleteSomeRecords($condition = FALSE)
    {
        $survey = new Survey_dynamic;
        $criteria = new CDbCriteria;

        if ($condition != FALSE)
        {
            foreach ($condition as $column => $value)
            {
                return $criteria->addCondition($column . "=`" . $value . "`");
            }
        }

        return $survey->deleteAll($criteria);
    }

    /**
     * Return criteria updated with the ones needed for including results from the token table
     *
     * @param CDbCriteria|string $criteria
     *
     * @return CDbCriteria
     */
    public function addTokenCriteria($condition)
    {
        $newCriteria = new CDbCriteria();
        $criteria = $this->getCommandBuilder()->createCriteria($condition);

        if ($criteria->select == '*')
        {
            $criteria->select = 't.*';
        }

        $newCriteria->join = "LEFT JOIN {{tokens_" . self::$sid . "}} tokens ON t.token = tokens.token";
        $newCriteria->select = 'tokens.*';  // Otherwise we don't get records from the token table
        $newCriteria->mergeWith($criteria);

        return $newCriteria;
    }

    /**
     * Return true if actual survey is completed
     *
     * @param $srid : actual save survey id
     *
     * @return boolean
     */
    public function isCompleted($srid)
    {
        $sid = self::$sid;
        $completed=false;

        if(Yii::app()->db->schema->getTable($this->tableName())){
            $data=Yii::app()->db->createCommand()
                ->select("submitdate")
                ->from($this->tableName())
                ->where('id=:id', array(':id'=>$srid))
                ->queryRow();
            if($data && $data['submitdate'])
            {
                $completed=true;
            }
        }
        return $completed;
    }

}
?>
