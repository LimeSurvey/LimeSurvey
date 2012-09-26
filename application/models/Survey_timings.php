<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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

class Survey_timings extends LSActiveRecord
{

    protected static $sid = 0;
	/**
	 * Returns the static model
	 *
	 * @static
	 * @access public
	 * @param int $surveyid
	 * @return CActiveRecord
	 */
	public static function model($sid = null)
	{
        if (!is_null($sid))
            self::sid($sid);

		return parent::model(__CLASS__);
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
        return '{{survey_' . intval(self::$sid) . '_timings}}';
    }

    /**
     * Returns Time statistics for this answer table
     *
     * @access public
     * @return array
     */
    public function statistics()
    {
        $sid = self::$sid;
        if(Yii::app()->db->schema->getTable($this->tableName())){
            $queryAvg=Yii::app()->db->createCommand()
                ->select("AVG(interviewtime) AS avg, COUNT(*) as count")
                ->from($this->tableName()." t")
                ->join("{{survey_{$sid}}} s","t.id = s.id")
                ->where("s.submitdate IS NOT NULL")
                ->queryRow();
            if($queryAvg['count']){
                $statistics['avgmin'] = (int) ($queryAvg['avg'] / 60);
                $statistics['avgsec'] = $queryAvg['avg'] % 60;
                $statistics['count'] = $queryAvg['count'];
                $queryAll=Yii::app()->db->createCommand()
                    ->select("interviewtime")
                    ->from($this->tableName()." t")
                    ->join("{{survey_{$sid}}} s","t.id = s.id")
                    ->where("s.submitdate IS NOT NULL")
                    ->order("t.interviewtime")
                    ->queryAll();
                $middleval = intval($statistics['count'] / 2);
                $statistics['middleval'] = $middleval;
                if ($statistics['count'] % 2)
                {
                    $median=($queryAll[$middleval]['interviewtime'] + $queryAll[$middleval-1]['interviewtime']) / 2;
                }
                else
                {
                    $median=$queryAll[$middleval]['interviewtime'];
                }
                $statistics['median'] = $median;
                $statistics['allmin'] = (int) ($median / 60);
                $statistics['allsec'] = $median % 60;
            }
            else
            {
                $statistics['count'] = 0;
            }
        }
        else
        {
            $statistics['count'] = 0;
        }
        return $statistics;
    }

    public function insertRecords($data)
    {
        $record = new self;
        foreach ($data as $k=>$v) {
            $record->$k = $v;
        }

        try {
            $record->save();
            return $record->id;
        } catch (Exception $e) {
            return false;
        }
    }
}

?>
