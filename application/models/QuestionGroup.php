<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
       *	Files Purpose: lots of common functions
    */

    class QuestionGroup extends LSActiveRecord
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
            return '{{groups}}';
        }

        /**
        * Returns the primary key of this table
        *
        * @access public
        * @return string
        */
        public function primaryKey()
        {
            return array('gid', 'language');
        }


    /**
    * Returns this model's validation rules
    *
    */
    public function rules()
    {
        return array(
            array('gid', 'unique', 'caseSensitive'=>true, 'criteria'=>array(
                            'condition'=>'language=:language',
                            'params'=>array(':language'=>$this->language)
                    ),
                    'message'=>'{attribute} "{value}" is already in use.'),
            array('language','length', 'min' => 2, 'max'=>20),// in array languages ?
            array('group_name,description','LSYii_Validators'),
            array('group_order','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
        );
    }

        /**
        * Defines the relations for this model
        *
        * @access public
        * @return array
        */
        public function relations()
        {
            return array('questions' => array(self::HAS_MANY, 'Question', 'gid'));
        }

        function getAllRecords($condition=FALSE, $order=FALSE, $return_query = TRUE)
        {
            $query = Yii::app()->db->createCommand()->select('*')->from('{{groups}}');

            if ($condition != FALSE)
            {
                $query->where($condition);
            }

            if($order != FALSE)
            {
                $query->order($order);
            }

            return ( $return_query ) ? $query->queryAll() : $query;
        }

        function updateGroupOrder($sid,$lang,$position=0)
        {
            $data=Yii::app()->db->createCommand()->select('gid')
            ->where(array('and','sid=:sid','language=:language'))
            ->order('group_order, group_name ASC')
            ->from('{{groups}}')
            ->bindParam(':sid', $sid, PDO::PARAM_INT)
            ->bindParam(':language', $lang, PDO::PARAM_STR)
            ->query();

            $position = intval($position);
            foreach($data->readAll() as $row)
            {
                Yii::app()->db->createCommand()->update($this->tableName(),array('group_order' => $position),'gid='.$row['gid']);
                $position++;
            }
        }

        /**
        * Insert an array into the groups table
        * Returns false if insertion fails, otherwise the new GID
        *
        * @param array $data                           array_merge
        */
        public function insertRecords($data)
        {
            $group = new self;
            foreach ($data as $k => $v)
                $group->$k = $v;
            if  (!$group->save()) return false;
            else return $group->gid;
        }

        function getGroups($surveyid) {
            $language = Survey::model()->findByPk($surveyid)->language;
            return Yii::app()->db->createCommand()
            ->select(array('gid', 'group_name'))
            ->from($this->tableName())
            ->where(array('and', 'sid=:surveyid', 'language=:language'))
            ->order('group_order asc')
            ->bindParam(":language", $language, PDO::PARAM_STR)
            ->bindParam(":surveyid", $surveyid, PDO::PARAM_INT)
            ->query()->readAll();
        }

        public static function deleteWithDependency($groupId, $surveyId)
        {
            $questionIds = QuestionGroup::getQuestionIdsInGroup($groupId);
            Question::deleteAllById($questionIds);
            Assessment::model()->deleteAllByAttributes(array('sid' => $surveyId, 'gid' => $groupId));
            return QuestionGroup::model()->deleteAllByAttributes(array('sid' => $surveyId, 'gid' => $groupId));
        }

        private static function getQuestionIdsInGroup($groupId) {
            $questions = Yii::app()->db->createCommand()
            ->select('qid')
            ->from('{{questions}} q')
            ->join('{{groups}} g', 'g.gid=q.gid AND g.gid=:groupid AND q.parent_qid=0')
            ->group('qid')
            ->bindParam(":groupid", $groupId, PDO::PARAM_INT)
            ->queryAll();

            $questionIds = array();
            foreach ($questions as $question) {
                $questionIds[] = $question['qid'];
            }

            return $questionIds;
        }

        function getAllGroups($condition, $order=false)
        {
            $command = Yii::app()->db->createCommand()->where($condition)->select('*')->from($this->tableName());
            if ($order != FALSE)
            {
                $command->order($order);
            }
            return $command->query();
        }
    }
?>
