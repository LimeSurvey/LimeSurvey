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
    class SurveyURLParameter extends LSActiveRecord {
        /**
        * Returns the static model of Settings table
        *
        * @static
        * @access public
        * @param string $class
        * @return CActiveRecord
        */
        public static function model($class = __CLASS__)
        {
            return parent::model($class);
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
        * Returns the setting's table name to be used by the model
        *
        * @access public
        * @return string
        */
        public function tableName()
        {
            return '{{survey_url_parameters}}';
        }

        function getParametersForSurvey($iSurveyID)
        {
            return Yii::app()->db->createCommand("select '' as act, up.*,q.title, sq.title as sqtitle, q.question, sq.question as sqquestion from {{survey_url_parameters}} up
                left join {{questions}} q on q.qid=up.targetqid
                left join {{questions}} sq on q.qid=up.targetsqid
            where up.sid=:surveyid")->bindParam(":surveyid", $iSurveyID, PDO::PARAM_INT)->query();
        }

        public function compareToArray($compareToArray){
            
        }

        function deleteRecords($aConditions)
        {
            foreach  ($aConditions as $sFieldname=>$sFieldvalue)
            {
                Yii::app()->db->createCommand()->where($sFieldname,$sFieldvalue);
            }
            return Yii::app()->db->delete('survey_url_parameters');// Deletes from token
        }

        function insertRecord($aData)
        {

            return Yii::app()->db->createCommand()->insert('{{survey_url_parameters}}',$aData);
        }

    }

?>
