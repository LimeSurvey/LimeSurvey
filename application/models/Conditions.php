<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
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
 *     $Id: common_helper.php 11335 2011-11-08 12:06:48Z c_schmitz $
 *     Files Purpose: lots of common functions
 */

class Conditions extends CActiveRecord
{
    /**
     * Returns the static model of Settings table
     *
     * @static
     * @access public
     * @return CActiveRecord
     */
    public static function model()
    {
        return parent::model(__CLASS__);
    }

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{conditions}}';
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'cid';
    }

    public function getSomeRecords($fields=FALSE, $conditions=FALSE)
    {
        $criteria = new CDbCriteria;

        if( $fields != FALSE )
        {
            $criteria->select = $fields;
        }

        if( $conditions != FALSE )
        {
            foreach($conditions as $column=>$value)
            {
                $criteria->addCondition("$column='$value'");
            }
        }

        return $this->findAll($criteria);
    }

    public function deleteRecords($condition=FALSE)
    {
        $criteria = new CDbCriteria;

        if( $condition != FALSE )
        {
            if( is_array($condition) )
            {
                foreach($condition as $column=>$value)
                {
                    $criteria->addCondition("$column='$value'");
                }
            }
            else
            {
                $criteria->where = $condition;
            }
        }

        return $this->deleteAll($criteria);
    }

    public function insertRecords($data, $update=FALSE, $condition=FALSE)
    {
        $record = new self;
        foreach ($data as $k => $v)
        {
            $v = str_replace(array("'", '"'), '', $v);
            $record->$k = $v;
        }

        if( $update )
        {
            $criteria = new CdbCriteria;
            if( is_array($condition) )
            {
                foreach($condition as $column=>$value)
                {
                    $criteria->addCondition("$column='$value'");
                }
            }
            else
            {
                $criteria->where = $condition;
            }

            return $record->updateAll($criteria);
        }
        else
            return $record->save();
    }
    function getScenarios($qid)
    {

        $scenarioquery = "SELECT DISTINCT scenario FROM ".$this->tableName()." WHERE qid=".$qid." ORDER BY scenario";

        return Yii::app()->db->createCommand($scenarioquery)->query();
    }
    function getSomeConditions($fields, $condition, $order, $group){
    $record = Yii::app()->db->createCommand()
            ->select($fields)
            ->from($this->tableName())
            ->where($condition);

        if( $order != NULL )
        {
            $record->order($order);
        }
        if( $group != NULL )
        {
            $record->group($group);
        }

        return $record->query();
    }
    function getConditionsQuestions($distinctrow,$deqrow,$scenariorow,$surveyprintlang)
    {
    $conquery="SELECT cid, cqid, q.title,\n"
        ."q.question, value, q.type, cfieldname\n"
        ."FROM {{conditions}} c, {{questions}} q\n"
        ."WHERE c.cqid=q.qid\n"
        ."AND c.cqid={$distinctrow}\n"
        ."AND c.qid={$deqrow} \n"
        ."AND c.scenario={$scenariorow} \n"
        ."AND language='{$surveyprintlang}'";
    return Yii::app()->db->createCommand($conquery)->query();
    }
}

?>
