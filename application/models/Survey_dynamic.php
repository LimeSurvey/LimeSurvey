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
    *	$Id$
    *	Files Purpose: lots of common functions
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
        * @return CActiveRecord
        */
        public static function model($sid = NULL)
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
        public static function deleteSomeRecords($condition=FALSE)
        {
            $survey = new Survey_dynamic;
            $criteria = new CDbCriteria;

            if( $condition != FALSE )
            {
                foreach ($condition as $column => $value)
                {
                    return $criteria->addCondition($column."=`".$value."`");
                }
            }

            return $survey->deleteAll($criteria);
        }

    }
?>
