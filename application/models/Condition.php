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
                if (preg_match('/(\S*?)'.$iSurveyID."X".$iOldGroupID."X".$iQuestionID."(.*)/", $oRow->cfieldname, $cfnregs) > 0)
                {
                    $sNewCfn=$cfnregs[1].$iSurveyID."X".$iNewGroupID."X".$iQuestionID.$cfnregs[2];
                    Yii::app()->db->createCommand()
                        ->update($this->tableName(), array('cfieldname' => $sNewCfn),
                        'cid=:cid',array(':cid'=>$oRow->cid));
                    LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyID,$oRow->qid);
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

        public function getScenarios($qid)
        {

            $scenarioquery = "SELECT DISTINCT scenario FROM ".$this->tableName()." WHERE qid=".$qid." ORDER BY scenario";

            return Yii::app()->db->createCommand($scenarioquery)->query();
        }

        public function getSomeConditions($fields, $condition, $order, $group){
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

        public function getConditionsQuestions($distinctrow,$deqrow,$scenariorow,$surveyprintlang)
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

        public function getAllCfieldnameWithDependenciesForOneSurvey($sid)
        {
            $Qids = Yii::app()->db->createCommand()
                    ->select('cfieldname')
                    ->from('{{questions}} questions')
                    ->join('{{conditions}} conditions', 'questions.qid=conditions.cqid')
                    ->where('sid=:sid', array(':sid'=>$sid))
                    ->queryRow();

            return $Qids;
        }

        /**
         * @param int $qid
         * @param Condition $scenarionr
         * @return int
         */
        public function getConditionCount($qid, $language, Condition $scenarionr)
        {
            $query = "SELECT count(*) as recordcount
                FROM {{conditions}} c, {{questions}} q, {{groups}} g
                WHERE c.cqid=q.qid "
                        ."AND q.gid=g.gid "
                        ."AND q.parent_qid=0 "
                        ."AND q.language=:lang "
                        ."AND g.language=:lang "
                        ."AND c.qid=:qid "
                        ."AND c.scenario=:scenario "
                        ."AND c.cfieldname NOT LIKE '{%' "; // avoid catching SRCtokenAttr conditions
            $result=Yii::app()->db->createCommand($query)
                ->bindValue(":scenario", $scenarionr['scenario'])
                ->bindValue(":qid", $qid, PDO::PARAM_INT)
                ->bindValue(":lang", $language, PDO::PARAM_STR)
                ->queryRow();
            return (int) $result['recordcount'];
        }

        /**
         * @param int $qid
         * @param Condition $scenarionr
         * @return array
         */
        public function getConditions($qid, $language, Condition $scenarionr)
        {
            $query = "SELECT c.cid, c.scenario, c.cqid, c.cfieldname, c.method, c.value, q.type
                FROM {{conditions}} c, {{questions}} q, {{groups}} g
                WHERE c.cqid=q.qid "
                        ."AND q.gid=g.gid "
                        ."AND q.parent_qid=0 "
                        ."AND q.language=:lang "
                        ."AND g.language=:lang "
                        ."AND c.qid=:qid "
                        ."AND c.scenario=:scenario "
                        ."AND c.cfieldname NOT LIKE '{%' " // avoid catching SRCtokenAttr conditions
                        ."ORDER BY g.group_order, q.question_order, c.cfieldname";
            $result = Yii::app()->db->createCommand($query)
                ->bindValue(":scenario", $scenarionr['scenario'])
                ->bindValue(":qid", $qid, PDO::PARAM_INT)
                ->bindValue(":lang", $language, PDO::PARAM_STR)
                ->query();
            return $result->readAll();
        }

        /**
         * @param int $qid
         * @param Condition $scenarionr
         * @return int
         */
        public function getConditionCountToken($qid, Condition $scenarionr)
        {
            $querytoken = "SELECT count(*) as recordcount "
                ."FROM {{conditions}} "
                ."WHERE "
                ." {{conditions}}.qid=:qid "
                ."AND {{conditions}}.scenario=:scenario "
                ."AND {{conditions}}.cfieldname LIKE '{%' "; // only catching SRCtokenAttr conditions
            $resulttoken = Yii::app()->db->createCommand($querytoken)
                ->bindValue(":scenario", $scenarionr['scenario'], PDO::PARAM_INT)
                ->bindValue(":qid", $qid, PDO::PARAM_INT)
                ->queryRow();

            if (empty($resulttoken)) {
                throw new \CException('Faulty query in getConditionCountToken');
            }

            return (int) $resulttoken['recordcount'];
        }

        /**
         * @param int $qid
         * @param Condition $scenarionr
         * @return array
         */
        public function getConditionsToken($qid, Condition $scenarionr)
        {
            $querytoken = "SELECT {{conditions}}.cid, "
                ."{{conditions}}.scenario, "
                ."{{conditions}}.cqid, "
                ."{{conditions}}.cfieldname, "
                ."{{conditions}}.method, "
                ."{{conditions}}.value, "
                ."'' AS type "
                ."FROM {{conditions}} "
                ."WHERE "
                ." {{conditions}}.qid=:qid "
                ."AND {{conditions}}.scenario=:scenario "
                ."AND {{conditions}}.cfieldname LIKE '{%' " // only catching SRCtokenAttr conditions
                ."ORDER BY {{conditions}}.cfieldname";
            $result = Yii::app()->db->createCommand($querytoken)
                ->bindValue(":scenario", $scenarionr['scenario'], PDO::PARAM_INT)
                ->bindValue(":qid", $qid, PDO::PARAM_INT)
                ->query();
            return $result;
        }
    }
