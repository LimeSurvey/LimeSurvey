<?php

    if (!defined('BASEPATH'))
        exit('No direct script access allowed');
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
    *     Files Purpose: lots of common functions
    */

    class Condition extends LSActiveRecord
    {
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

        /**
        * Defines the relations for this model
        *
        * @access public
        * @return array
        */
        public function relations()
        {
			$alias = $this->getTableAlias();
            return array(
            'questions' => array(self::HAS_ONE, 'Question', '',
            'on' => "$alias.cqid = questions.qid",
            ),
            );
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
                    $criteria->addCondition($condition);
                }
            }

            return $this->deleteAll($criteria);
        }


        /**
        * Updates the group ID for all conditions
        * 
        * @param integer $iSurveyID
        * @param integer $iQuestionID
        * @param integer $iOldGroupID
        * @param integer $iNewGroupID
        */
        public function updateCFieldName($iSurveyID, $iQuestionID, $iOldGroupID, $iNewGroupID)
        {
            $oResults=$this->findAllByAttributes(array('cqid'=>$iQuestionID));
            foreach ($oResults as $oRow)
            {

                $cfnregs='';
                if (preg_match('/'.$surveyid."X".$iOldGroupID."X".$iQuestionID."(.*)/", $oRow->cfieldname, $cfnregs) > 0)
                {
                    $newcfn=$surveyid."X".$iNewGroupID."X".$iQuestionID.$cfnregs[1];
                    $c2query="UPDATE ".db_table_name('conditions')
                    ." SET cfieldname='{$newcfn}' WHERE cid={$oRow->cid}";

                    Yii::app()->db->createCommand($c2query)->query();
                }
            }
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

                return $record->updateAll($data,$criteria);
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
            $conquery="SELECT cid, cqid, q.title, q.question, value, q.type, cfieldname "
            ."FROM {{conditions}} c, {{questions}} q "
            ."WHERE c.cqid=q.qid "
            ."AND c.cqid=:distinctrow "
            ."AND c.qid=:deqrow "
            ."AND c.scenario=:scenariorow "
            ."AND language=:surveyprintlang ";
            return Yii::app()->db->createCommand($conquery)
            ->bindParam(":distinctrow", $distinctrow, PDO::PARAM_INT)
            ->bindParam(":deqrow", $deqrow, PDO::PARAM_INT)
            ->bindParam(":scenariorow", $scenariorow, PDO::PARAM_INT)
            ->bindParam(":surveyprintlang", $surveyprintlang, PDO::PARAM_STR)
            ->query();
        }
    }

?>
