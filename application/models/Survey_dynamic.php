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
        return 'id';
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

        try
        {
            $record->save();
            return $record->id;
        }
        catch(Exception $e)
        {
            return false;
        }
        
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
     * Return criteria updated with the ones needed for including results from the timings table
     *
     * @param CDbCriteria|string $criteria
     *
     * @return CDbCriteria
     */
    public function addTimingCriteria($condition)
    {
        $newCriteria = new CDbCriteria();
        $criteria = $this->getCommandBuilder()->createCriteria($condition);

        if ($criteria->select == '*')
        {
            $criteria->select = 't.*';
        }

        $newCriteria->join = "LEFT JOIN {{survey_" . self::$sid . "_timings}} survey_timings ON t.id = survey_timings.id";
        $newCriteria->select = 'survey_timings.*';  // Otherwise we don't get records from the token table
        $newCriteria->mergeWith($criteria);

        return $newCriteria;
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
        $aSelectFields=Yii::app()->db->schema->getTable('{{survey_' . self::$sid  . '}}')->getColumnNames();
        $aSelectFields=array_diff($aSelectFields, array('token'));
        $aSelect=array();
        foreach($aSelectFields as $sField)
            $aSelect[]='t.'.Yii::app()->db->schema->quoteColumnName($sField);
        $aSelectFields=$aSelect;        
        $aSelectFields[]='t.token';

        if ($criteria->select == '*')
        {
            $criteria->select = $aSelectFields;
        }

        $newCriteria->join = "LEFT JOIN {{tokens_" . self::$sid . "}} tokens ON t.token = tokens.token";

        $aTokenFields=Yii::app()->db->schema->getTable('{{tokens_' . self::$sid . '}}')->getColumnNames();
        $aTokenFields=array_diff($aTokenFields, array('token'));
        
        $newCriteria->select = $aTokenFields;  // Otherwise we don't get records from the token table
        $newCriteria->mergeWith($criteria);

        return $newCriteria;
    }
    
    public static function countAllAndPartial($sid)
    {
        $select = array(
            'count(*) AS cntall',
            'sum(CASE 
                 WHEN '. Yii::app()->db->quoteColumnName('submitdate') . ' IS NULL THEN 1
                          ELSE 0
                 END) AS cntpartial',
            );
        $result = Yii::app()->db->createCommand()->select($select)->from('{{survey_' . $sid . '}}')->queryRow();
        return $result;
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
        static $resultCache = array();
        
        $sid = self::$sid;
        if (array_key_exists($sid, $resultCache) && array_key_exists($srid, $resultCache[$sid])) {
            return $resultCache[$sid][$srid];
        }
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
        $resultCache[$sid][$srid] = $completed;
        return $completed;
    }

    /**
     * Return true if actual respnse exist in database
     *
     * @param $srid : actual save survey id
     *
     * @return boolean
     */
    public function exist($srid)
    {
        $sid = self::$sid;
        $exist=false;

        if(Yii::app()->db->schema->getTable($this->tableName())){
            $data=Yii::app()->db->createCommand()
                ->select("id")
                ->from($this->tableName())
                ->where('id=:id', array(':id'=>$srid))
                ->queryRow();
            if($data)
            {
                $exist=true;
            }
        }
        return $exist;
    }

    /**
     * Return next id if next response exist in database
     *
     * @param integer $srid : actual save survey id
     * @param boolean $usefilterstate
     *
     * @return integer
     */
    public function next($srid,$usefilterstate=false)
    {
        $sid = self::$sid;
        $next=false;
        if ($usefilterstate && incompleteAnsFilterState() == 'incomplete')
            $wherefilterstate='submitdate IS NULL';
        elseif ($usefilterstate && incompleteAnsFilterState() == 'complete')
            $wherefilterstate='submitdate IS NOT NULL';
        else
            $wherefilterstate='1=1';

        if(Yii::app()->db->schema->getTable($this->tableName())){
            $data=Yii::app()->db->createCommand()
                ->select("id")
                ->from($this->tableName())
                ->where(array('and',$wherefilterstate,'id > :id'), array(':id'=>$srid))
                ->order('id ASC')
                ->queryRow();
            if($data)
            {
                $next=$data['id'];
            }
        }
        return $next;
    }

    /**
     * Return previous id if previous response exist in database
     *
     * @param integer $srid : actual save survey id
     * @param boolean $usefilterstate
     *
     * @return integer
     */
    public function previous($srid,$usefilterstate=false)
    {
        $sid = self::$sid;
        $previous=false;
        if ($usefilterstate && incompleteAnsFilterState() == 'incomplete')
            $wherefilterstate='submitdate IS NULL';
        elseif ($usefilterstate && incompleteAnsFilterState() == 'complete')
            $wherefilterstate='submitdate IS NOT NULL';
        else
            $wherefilterstate='1=1';

        if(Yii::app()->db->schema->getTable($this->tableName())){
            $data=Yii::app()->db->createCommand()
                ->select("id")
                ->from($this->tableName())
                ->where(array('and',$wherefilterstate,'id < :id'), array(':id'=>$srid))
                ->order('id DESC')
                ->queryRow();
            if($data)
            {
                $previous=$data['id'];
            }
        }
        return $previous;
    }
}
?>
