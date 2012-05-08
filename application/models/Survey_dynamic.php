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

class Survey_dynamic extends CActiveRecord
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
        if (!is_null($sid)) {
            self::sid($sid);
        }
        $model = parent::model(__CLASS__);
        $model->refreshMetaData();
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
     * Modified version that default to do the same as the original, but allows via a
     * third parameter to retrieve the result as array instead of active records. This
     * solves a joining problem. Usage via findAllAsArray method
     *
     * Performs the actual DB query and populates the AR objects with the query result.
     * This method is mainly internally used by other AR query methods.
     * @param CDbCriteria $criteria the query criteria
     * @param boolean $all whether to return all data
     * @return mixed the AR objects populated with the query result
     * @since 1.1.7
     */
    protected function query($criteria, $all = false, $asAR = true)
    {
        if ($asAR === true)
        {
            return parent::query($criteria, $all);
        } else
        {
            $this->beforeFind();
            $this->applyScopes($criteria);
            if (!$all)
            {
                $criteria->limit = 1;
            }

            $command = $this->getCommandBuilder()->createFindCommand($this->getTableSchema(), $criteria);
            //For debug, this command will get you the generated sql:
            //echo $command->getText();

            return $all ? $command->queryAll() : $command->queryRow();
        }
    }

    /**
     * Finds all active records satisfying the specified condition but returns them as array
     *
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return array list of active records satisfying the specified condition. An empty array is returned if none is found.
     */
    public function findAllAsArray($condition = '', $params = array())
    {
        Yii::trace(get_class($this) . '.findAll()', 'system.db.ar.CActiveRecord');
        $criteria = $this->getCommandBuilder()->createCriteria($condition, $params);
        return $this->query($criteria, true, false);  //Notice the third parameter 'false'
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

}
?>
