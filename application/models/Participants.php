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
 * 	$Id$
 */

/**
 * This is the model class for table "{{participants}}".
 *
 * The followings are the available columns in table '{{participants}}':
 * @property string $participant_id
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $language
 * @property string $blacklisted
 * @property integer $owner_uid
 */
class Participants extends CActiveRecord
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
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{participants}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('participant_id, blacklisted, owner_uid', 'required'),
            array('owner_uid', 'numerical', 'integerOnly' => true),
            array('participant_id', 'length', 'max' => 50),
            array('firstname, lastname, language', 'length', 'max' => 40),
            array('email', 'length', 'max' => 80),
            array('blacklisted', 'length', 'max' => 1),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('participant_id, firstname, lastname, email, language, blacklisted, owner_uid', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'participant_id' => 'Participant',
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'email' => 'Email',
            'language' => 'Language',
            'blacklisted' => 'Blacklisted',
            'owner_uid' => 'Owner Uid',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('participant_id', $this->participant_id, true);
        $criteria->compare('firstname', $this->firstname, true);
        $criteria->compare('lastname', $this->lastname, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('language', $this->language, true);
        $criteria->compare('blacklisted', $this->blacklisted, true);
        $criteria->compare('owner_uid', $this->owner_uid);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /*
     * funcion for generation of unique id
     */

    function gen_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /*
     * This function is responsible for adding the participant to the database
     * Parameters : participant data
     * Return Data : none
     */

    function insertParticipant($data)
    {
        Yii::app()->db->createCommand()->insert('{{participants}}', $data);
    }

    /*
     * This function updates the data edited in the jqgrid
     * Parameters : data that is edited
     * Return Data : None
     */

    function updateRow($data)
    {
        Yii::app()->db->createCommand()->update('{{participants}}', $data, 'participant_id = :participant_id')->bindParam(":participant_id", $data["participant_id"], PDO::PARAM_INT);
    }

    function deleteParticipantTokenAnswer($rows)
    {
        $rowid = explode(",", $rows);
        //$rowid = array('243148a0-bf56-4ee1-a6d2-a1f1cb5243d5');
        foreach ($rowid as $row)
        {
            $tokens = Yii::app()->db->createCommand()->select('*')->from('{{survey_links}}')->where('participant_id = "' . $row . '"')->queryAll();
            foreach ($tokens as $key => $value)
            {
                Yii::app()->db->createCommand()->delete('{{participants}}', 'participant_id = :participant_id')->bindParam(":participant_id", $row, PDO::PARAM_INT); //Delete from participants
                if (Yii::app()->db->schema->getTable('tokens_' . Yii::app()->db->quoteValue($value['survey_id'])))
                {
                    $tokenid = Yii::app()->db->createCommand()->select('token')->from('{{tokens_' . intval($value['survey_id']) . '}}')->where('participant_id = :participant_id')->bindParam(":participant_id", $value['participant_id'], PDO::PARAM_INT)->queryAll();
                    $token = $tokenid[0];
                    if (Yii::app()->db->schema->getTable('survey_' . intval($value['survey_id'])))
                    {
                        if (!empty($token['token']))
                        {
                            $gettoken = Yii::app()->db->createCommand()->select('*')->from('{{survey_' . intval($value['survey_id']) . '}}')->where('token = :token')->bindParam(":token", $token['token'], PDO::PARAM_STR)->queryAll();
                            $gettoken = $gettoken[0];
                            Yii::app()->db->createCommand()->delete('{{survey_' . intval($value['survey_id']) . '}}', 'token = :token')->bindParam(":token", $gettoken['token'], PDO::PARAM_STR);
                        }
                    }
                    Yii::app()->db->createCommand()->delete('{{tokens_' . intval($value['survey_id']) . '}}', 'participant_id = :participant_id')->bindParam(":participant_id", $value['participant_id'], PDO::PARAM_INT); // Deletes from token
                }
            }
        }
    }

    /*
     * This function combines the shared participant and the central participant
     * table and searches for any reference of owner id or shared owner id in the rows
     */

    function getParticipantsOwner($userid)
    {
        return Yii::app()->db->createCommand()->select('{{participants}}.*,{{participant_shares}}.can_edit')->from('{{participants}}')->leftJoin('{{participant_shares}}', ' {{participants}}.participant_id={{participant_shares}}.participant_id')->where('owner_uid = :userid OR share_uid = ' . $userid)->group('{{participants}}.participant_id')->bindParam(":userid", $userid, PDO::PARAM_INT)->queryAll();
    }

    function getParticipantsOwnerCount($userid)
    {
        return count(Yii::app()->db->createCommand()->select('{{participants}}.*,{{participant_shares}}.can_edit')->from('{{participants}}')->leftJoin('{{participant_shares}}', ' {{participants}}.participant_id={{participant_shares}}.participant_id')->where('owner_uid = :userid OR share_uid = ' . $userid)->group('{{participants}}.participant_id')->bindParam(":userid", $userid, PDO::PARAM_INT)->queryAll());
    }

    function getParticipantsWithoutLimit()
    {
        return Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
    }

    /*
     * This function combines the shared participant and the central participant
     * table and searches for any reference of owner id in the combined record
     * of the two tables
     */

    function getParticipantsSharedCount($userid)
    {
        return count(Yii::app()->db->createCommand()->select('{{participants}}.*, {{participant_shares}}.*')->from('{{participants}}')->join('{{participant_shares}}', '{{participant_shares}}.participant_id = {{participants}}.participant_id')->where('owner_uid = :userid')->bindParam(":userid", $userid, PDO::PARAM_INT)->queryAll());
    }

    function getParticipants($page, $limit)
    {
        $start = $limit * $page - $limit;
        $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->limit($limit, $start)->queryAll();
        return $data;
    }

    function getSurveyCount($participant_id)
    {
        $count = count(Yii::app()->db->createCommand()->select('*')->from('{{survey_links}}')->where('participant_id = :participant_id')->bindParam(":participant_id", $participant_id, PDO::PARAM_INT)->queryAll());
        return $count;
    }

    /*
     * This function deletes the row marked in the navigator
     * Parameters : row id's
     * Return Data : None
     */

    function deleteParticipant($rows)
    {
        // Converting the comma seperated id's to an array to delete multiple rows
        $rowid = explode(",", $rows);
        foreach ($rowid as $row)
        {
            Yii::app()->db->createCommand()->delete(Participants::model()->tableName(), array('in', 'participant_id', $row));
        	Yii::app()->db->createCommand()->delete(Survey_links::model()->tableName(), array('in', 'participant_id', $row));
        	Yii::app()->db->createCommand()->delete(Participant_attribute::model()->tableName(), array('in', 'participant_id', $row));
        }
    }

    function deleteParticipantToken($rows)
    {
        $rowid = explode(",", $rows);
        foreach ($rowid as $row)
        {
            $tokens = Yii::app()->db->createCommand()->select('*')->from('{{survey_links}}')->where('participant_id = :row')->bindParam(":row", $row, PDO::PARAM_INT)->queryAll();

			foreach ($tokens as $key => $value)
            {
                if (Yii::app()->db->schema->getTable('tokens_' . intval($value['survey_id'])))
                {
                    Yii::app()->db->createCommend()->delete(Tokens::model()->tableName(), array('in', 'participant_id', $row));
				}
            }

        	Yii::app()->db->createCommand()->delete(Participants::model()->tableName(), array('in', 'participant_id', $row));
        	Yii::app()->db->createCommand()->delete(Survey_links::model()->tableName(), array('in', 'participant_id', $row));
        	Yii::app()->db->createCommand()->delete(Participant_attribute::model()->tableName(), array('in', 'participant_id', $row));
        }
    }

    function getParticipantsSearch($condition, $page, $limit)
    {
        $start = $limit * $page - $limit;
        if ($condition[1] == 'equal')
        {
            if ($condition[0] == 'surveys')
            {
                $resultarray = array();
                if ($page == 0 && $limit == 0)
                {
                    $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
                }
                else
                {
                    $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->limit(intval($limit), $start)->queryAll();
                }
                foreach ($data as $key => $value)
                {
					$count = count(Yii::app()->db->createCommand()->where('participant_id = :participant_id')->from('{{survey_links}}')->select('*')->bindParam(":participant_id", $value['participant_id'], PDO::PARAM_INT)->queryAll());
                    if ($count == $condition[2])
                    {
                        array_push($resultarray, $value);
                    }
                }
                return $resultarray;
            }
            else if ($condition[0] == 'owner_name')
            {
                $userid = Yii::app()->db->createCommand()->select('uid')->where('full_name = :condition_2')->from('{{users}}')->bindParam("condition_2", $condition[2], PDO::PARAM_STR)->queryAll();
                $uid = $userid[0];
                $command = Yii::app()->db->createCommand();
                $command->where('owner_uid = :uid');
				$command->bindParam(":uid", $uid['uid'], PDO::PARAM_INT);
                $command->select('*');
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->from('{{participants}}')->queryAll();
                }
                else
                {
                    $data = $command->from('{{participants}}')->limit(intval($limit), $start)->queryAll();
                }
                return $data;
            }
            else if (is_numeric($condition[0]))
            {
                $command = Yii::app()->db->createCommand()->select('{{participant_attribute}}.*,{{participants}}.*')->from('{{participant_attribute}}')->join('{{participants}}', '{{participant_attribute}}.participant_id = {{participants}}.participant_id')->where('{{participant_attribute}}.attribute_id = :condition_0 AND {{participant_attribute}}.value = :condition_2')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", $condition[2], PDO::PARAM_STR);
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit(intval($limit), $start)->queryAll();
                }
                return $data;
            }
            else
            {
                $command = Yii::app()->db->createCommand()->where(Yii::app()->db->quoteValue($condition[0]) . ' = "' . Yii::app()->db->quoteValue($condition[2]) . '"');
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->select('*')->from('{{participants}}')->queryAll();
                }
                else
                {
                    $data = $command->select('*')->from('{{participants}}')->limit(intval($limit), $start)->queryAll();
                }
                return $data;
            }
        }
        else if ($condition[1] == 'contains')
        {
            $condition[2] = '%' . $condition[2] . '%';
            if ($condition[0] == 'surveys')
            {
                $resultarray = array();
                if ($page == 0 && $limit == 0)
                {
                    $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
                }
                else
                {
                    $data = Yii::app()->db->createCommand()->select('*')->limit(intval($limit), $start)->from('{{participants}}')->queryAll();
                }
                foreach ($data as $key => $value)
                {
                    $count = count(Yii::app()->db->createCommand()->where('participant_id = :participiant_id')->from('{{survey_links}}')->bindParam(":participant_id", $value['participant_id'], PDO::PARAM_INT)->queryAll());
                    if ($count == $condition[2])
                    {
                        array_push($resultarray, $value);
                    }
                }
                return $resultarray;
            }
            else if ($condition[0] == 'owner_name')
            {
                $userid = $command = Yii::app()->db->createCommand()->select('uid')->where(array('like', 'full_name', $condition[2]))->from('{{users}}')->queryAll();
                $uid = $userid[0];
                $command = Yii::app()->db->createCommand()->where('owner_uid = :uid')->order("lastname", "asc")->select('*')->from('{{participants}}')->bindParam(":uid", $uid['uid'], PDO::PARAM_INT);
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit(intval($limit), $start)->queryAll();
                }
                return $data;
            }
            else if (is_numeric($condition[0]))
            {
                $command = Yii::app()->db->createCommand()->select('{{participant_attribute}}.*,{{participants}}.*')->from('{{participant_attribute}}')->join('{{participants}}', '{{participant_attribute}}.participant_id = {{participants}}.participant_id')->where('{{participant_attribute}}.attribute_id = :condition_0')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->where(array('like', '{{participant_attribute}}.value', $condition[2]));
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit(intval($limit), $start);
                }
                return $data;
            }
            else
            {
                $command = Yii::app()->db->createCommand()->where(array('like', $condition[0], $condition[2]))->select('*')->from('{{participants}}');
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit(intval($limit), $start)->queryAll();
                }
                return $data;
            }
        }
        else if ($condition[1] == 'notequal')
        {
            if ($condition[0] == 'surveys')
            {
                $resultarray = array();

                if ($page == 0 && $limit == 0)
                {
                    $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
                }
                else
                {
                    $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->limit(intval($limit), $start)->queryAll();
                }
                foreach ($data as $key => $value)
                {
                    $count = count(Yii::app()->db->createCommand()->select('*')->from('{{survey_links}}')->where('participant_id = :participant_id')->bindParam(":participant_id", $value['participant_id'], PDO::PARAM_INT)->queryAll());
                    if ($count != $condition[2])
                    {
                        array_push($resultarray, $value);
                    }
                }
                return $resultarray;
            }
            else if ($condition[0] == 'owner_name')
            {

                $userid = Yii::app()->db->createCommand()->select('uid')->where(array('not in', 'full_name', $condition[2]))->from('{{users}}')->queryAll();
                $uid = $userid[0];
                $command = Yii::app()->db->createCommand()->where('owner_uid = :uid')->bindParam(":uid", $uid['uid'], PDO::PARAM_INT)->from('{{participants}}')->select('*');
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit($limit, $start)->queryAll();
                }
                return $data;
            }
            else if (is_numeric($condition[0]))
            {
                $command = Yii::app()->db->createCommand()->select('{{participant_attribute}}.*,{{participants}}.*')->from('{{participant_attribute}}')->join('{{participants}}', '{{participant_attribute}}.participant_id = {{participants}}.participant_id')->where('{{participant_attribute}}.attribute_id = :condition_0')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->where(array('not in', '{{participant_attribute}}.value', Yii::app()->db->quoteValue($condition[2])));
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit(intval($limit), $start)->queryAll();
                }
                return $data;
            }
            else
            {
                $command = Yii::app()->db->createCommand()->where(array('not in', $condition[0], $condition[2]))->from('{{participants}}')->select('*');
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit(intval($limit), $start)->queryAll();
                }
                return $data;
            }
        }
        else if ($condition[1] == 'notcontains')
        {
            $condition[2] = '%' . $condition[2] . '%';
            if ($condition[0] == 'surveys')
            {
                $resultarray = array();
                $command = Yii::app()->db->createCommand()->order('lastname', 'asc')->from('{{participants}}')->select('*');
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit(intval($limit), $start);
                }
                foreach ($data as $key => $value)
                {
                    $count = count(Yii::app()->db->createCommand()->where('participant_id = :participant_id')->from('{{survey_links}}')->select('*')->bindParam(":participant_id", $value['participant_id'], PDO::PARAM_INT)->queryAll());
                    if ($count != $condition[2])
                    {
                        array_push($resultarray, $value);
                    }
                }
                return $resultarray;
            }
            else if ($condition[0] == 'owner_name')
            {
                $userid = Yii::app()->db->createCommand()->select('uid')->where(array('not like', 'full_name', $condition[2]))->from('{{users}}')->queryAll();
                $uid = $userid[0];
                $command = Yii::app()->db->createCommand()->where('owner_uid = :uid')->bindParam(":uid", $uid['uid'], PDO::PARAM_INT)->from('{{participants}}')->select('*');
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit(intval($limit), $start)->queryAll();
                }
                return $data;
            }
            else if (is_numeric($condition[0]))
            {
                $command = Yii::app()->db->createCommand()->select('{{participant_attribute}}.*,{{participants}}.*')->from('{{participant_attribute}}')->join('{{participants}}', '{{participant_attribute}}.participant_id = {{participants}}.participant_id')->where('{{participant_attribute}}.attribute_id = :condition_0')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->where(array('not like', 'participant_attribute.value', $condition[2]));
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit($limit, $start)->queryAll();
                }
                return $data;
            }
            else
            {
                $command = Yii::app()->db->createCommand()->where(array('not like', $condition[0], $condition[2]))->from('{{participants}}')->select('*');
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit(intval($limit), $start)->queryAll();
                }
                return $data;
            }
        }
        else if ($condition[1] == 'greaterthan')
        {
            if ($condition[0] == 'surveys')
            {
                $resultarray = array();
                if ($page == 0 && $limit == 0)
                {
                    $data = $this->db->get('participants');
                }
                else
                {
                    $data = $this->db->get('participants', $limit, $start);
                }
                foreach ($data->result_array() as $key => $value)
                {
                    $this->db->where('participant_id=:participant_id')->bindParam(":participant_id", $value['participant_id'], PDO::PARAM_INT);
                    $this->db->from('survey_links');
                    $count = $this->db->count_all_results();
                    if ($count > $condition[2])
                    {
                        array_push($resultarray, $value);
                    }
                }
                return $resultarray;
            }
            else if ($condition[0] == 'owner_name')
            {
                $userid = Yii::app()->db->createCommand()->select('uid')->where('full_name = :condition_2')->bindParam(":condition_2", $condition[2], PDO::PARAM_STR)->from('{{users}}')->queryAll();
                $uid = $userid[0];
                $command = Yii::app()->db->createCommand()->where('owner_uid = :uid')->bindParam(":uid", $uid['uid'], PDO::PARAM_INT)->order("lastname", "asc")->select('*')->from('{{participants}}');
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit($limit, $start)->queryAll();
                }
                return $data;
            }
            else if (is_numeric($condition[0]))
            {
                $command = Yii::app()->db->createCommand()->select('{{participant_attribute}}.*,{{participants}}.*')->from('{{participant_attribute}}')->join('{{participants}}', '{{participant_attribute}}.participant_id = {{participants}}.participant_id')->where('{{participant_attribute}}.attribute_id = ' . $condition[0] . ' AND participant_attribute.value > "' . $condition[2] . '"');
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit($limit, $start)->queryAll();
                }
                return $data;
            }
            else
            {
                $command = Yii::app()->db->createCommand()->where(Yii::app()->db->quoteColumnName($condition[0]) . ' > :condition')->bindParam(":condition", $condition[2], PDO::PARAM_INT)->order("lastname", "asc")->select('*')->from('{{participants}}');
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit($limit, $start)->queryAll();
                }
                return $data;
            }
        }
        else if ($condition[1] == 'lessthan')
        {
            if ($condition[0] == 'surveys')
            {
                $resultarray = array();

                if ($page == 0 && $limit == 0)
                {
                    $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
                }
                else
                {
                    $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->limit($limit, $start)->queryAll();
                }
                foreach ($data as $key => $value)
                {
                    $count = count(Yii::app()->db->createCommand()->where('participant_id = :participant_id')->bindParam(":participant_id", $value['participant_id'], PDO::PARAM_INT)->from('{{survey_links}}')->select('*')->queryAll());
                    if ($count < $condition[2])
                    {
                        array_push($resultarray, $value);
                    }
                }
                return $resultarray;
            }
            else if ($condition[0] == 'owner_name')
            {

                $userid = Yii::app()->db->createCommand()->select('uid')->where('full_name = :condition_2')->bindParam(":condition_2", $condition[2], PDO::PARAM_STR)->from('{{users}}')->queryAll();
                $uid = $userid[0];
                $command = Yii::app()->db->createCommand()->where('owner_uid < :uid')->bindParam(":uid", $uid['uid'], PDO::PARAM_INT)->select('*')->from('{{participants}}');

                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit($limit, $start);
                }
                return $data;
            }
            else if (is_numeric($condition[0]))
            {
                $command = Yii::app()->db->createCommand()->select('{{participant_attribute}}.*,{{participants}}.*')->from('{{participant_attribute}}')->join('{{participants}}', '{{participant_attribute}}.participant_id = {{participants}}.participant_id')->where('{{participant_attribute}}.attribute_id = :condition_0')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->where(array('not like', 'participant_attribute.value < :condition_2'))->bindParam(":condition_2", $condition[2], PDO::PARAM_INT);
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $this->db->limit($limit, $start);
                    $data = $command->limit($limit, $start);
                }
                return $data;
            }
            else
            {
                $command = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->where(Yii::app()->db->quoteColumnName($condition[0]) . ' < :condition_2')->bindParam(":condition_2", $condition[2], PDO::PARAM_INT);
                if ($page == 0 && $limit == 0)
                {
                    $data = $command->queryAll();
                }
                else
                {
                    $data = $command->limit($limit, $start)->queryAll();
                }
                return $data;
            }
        }
    }

    function getParticipantsSearchMultiple($condition, $page, $limit)
    {
    	//http://localhost/limesurvey_yii/admin/participants/getParticipantsResults_json/search/email||contains||gov||and||firstname||contains||AL
    	//First contains fieldname, second contains method, third contains value, fourth contains BOOLEAN SQL and, or
        $i = 0;
        $j = 1;
        $tobedonelater = array();
        $start = $limit * $page - $limit;
        $command = new CDbCriteria;
        $command->condition = '';
        $con = count($condition);
        while ($i < $con)
        {
            if ($i < 3)
            {
                $i+=3;
                if ($condition[1] == 'equal')
                {
                    if (is_numeric($condition[0]))
                    {
                        $newsub = $j;
                        $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = ' . $condition[0] . ' AND {{participant_attribute}}.value = "' . $condition[2] . '"')->queryAll();
                        $command->addInCondition('participant_id', $arr);
                        $j++;
                    }
                    else
                    {
                        $command->addCondition($condition[0] . ' = "' . $condition[2] . '"');
                    }
                }
                else if ($condition[1] == 'contains')
                {
                	$condition[2]="%".$condition[2]."%";
                    if (is_numeric($condition[0]))
                    {
                        $newsub = $j;
                        //$arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = :condition_0 AND {{participant_attribute}}.value LIKE :condition_2')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", "%".$condition[2]."%", PDO::PARAM_STR)->queryAll();
						$arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition[0].' AND {{participant_attribute}}.value LIKE '.$condition[2])->queryAll();


                        $command->addInCondition('participant_id', $arr);
                        $j++;
                    }
                    else
                    {
                    	//BUG: bindParam does not exist as a method
                        //$command->addCondition(':condition_0 LIKE :condition_2')->bindParam(":condition_0", $condition[0], PDO::PARAM_STR)->bindParam(":condition_2", "%".$condition[2]."%", PDO::PARAM_STR);
                        $command->addCondition($condition[0] . ' LIKE "'. $condition[2].'"');
					}
                }
                else if ($condition[1] == 'notequal')
                {
                    if (is_numeric($condition[0]))
                    {
                        $newsub = $j;
                        $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = :condition_0 AND {{participant_attribute}}.value NOT IN (:condition_2)')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", $condition[2], PDO::PARAM_STR)->queryAll();


                        $command->addInCondition('participant_id', $arr);
                        $j++;
                    }
                    else
                    {
                        $command->addCondition(':condition_0 NOT IN (:condition_2)')->bindParam(":condition_0", $condition[0], PDO::PARAM_STR)->bindParam(":condition_2", $condition[2], PDO::PARAM_STR);
                    }
                }
                else if ($condition[1] == 'notcontains')
                {
                    if (is_numeric($condition[0]))
                    {
                        $newsub = $j;
                        $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = :condition_0 AND {{participant_attribute}}.value NOT LIKE :condition_2')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", "%".$condition[2]."%", PDO::PARAM_STR)->queryAll();


                        $command->addInCondition('participant_id', $arr);
                        $j++;
                    }
                    else
                    {
                        $command->addCondition(':condition_0 NOT LIKE  :condition_2')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", "%".$condition[2]."%", PDO::PARAM_STR);
                    }
                }
                else if ($condition[1] == 'greaterthan')
                {
                    if (is_numeric($condition[0]))
                    {
                        $newsub = $j;
                        $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = :condition_0 AND {{participant_attribute}}.value > :condition_2')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", $condition[2], PDO::PARAM_INT)->queryAll();


                        $command->addInCondition('participant_id', $arr);
                        $j++;
                    }
                    else
                    {
                        $command->addCondition(':condition_0 > :condition_2')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", $condition[2], PDO::PARAM_INT);
                    }
                }
                else if ($condition[1] == 'lessthan')
                {
                    if (is_numeric($condition[0]))
                    {
                        $newsub = $j;
                        $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = :condition_0 AND {{participant_attribute}}.value < :condition_2')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", $condition[2], PDO::PARAM_INT)->queryAll();


                        $command->addInCondition('participant_id', $arr);
                        $j++;
                    }
                    else
                    {
                        $command->addCondition(':condition_0 < :condition_2')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", $condition[2], PDO::PARAM_INT);
                    }
                }
            }
            else if ($condition[$i] != '') //This section deals with subsequent filter conditions that have boolean joiner
            {
                if ($condition[$i + 2] == 'equal')
                {
                    if (is_numeric($condition[$i + 1]))
                    {
                        if ($condition[$i] == 'and')
                        {

                            $newsub = $j;
                            $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = :condition_p1 AND {{participant_attribute}}.value = :condition_p3')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_INT)->bindParam(":condition_p3", $condition[$i + 3], PDO::PARAM_STR)->queryAll();
                            $command->addInCondition('participant_id', $arr);
                            $j++;
                        }
                        else
                        {
                            $tobedonelater[$condition[$i + 1]][0] = $condition[$i + 2];
                            $tobedonelater[$condition[$i + 1]][1] = $condition[$i + 3];
                        }
                    }
                    else
                    {
                        if ($condition[$i] == 'and')
                        {
                            $command->addCondition(':condition_p1 = :condition_p3')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_INT)->bindParam(":condition_p3", $condition[$i + 3], PDO::PARAM_STR);
                        }
                        else
                        {
                            $command->addCondition(':condition_p1 = :condition_p3', 'OR')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_INT)->bindParam(":condition_p3", $condition[$i + 3], PDO::PARAM_STR);
                        }
                    }
                }
                else if ($condition[$i + 2] == 'contains')
                {
                    if (is_numeric($condition[$i + 1]))
                    {
                        if ($condition[$i] == 'and')
                        {
                            $newsub = $j;
                            //$arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = :condition_p1 AND {{participant_attribute}}.value LIKE :condition_p3')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_INT)->bindParam(":condition_p3", "%".$condition[$i + 3]."%", PDO::PARAM_STR)->queryAll();
							$arr = Yii::app()->db->createComment('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition[$i+1].' AND {{participant_attribute}}.value LIKE "%'.$condition[$i+3].'%"')->queryAll();

                            $command->addInCondition('participant_id', $arr);
                            $j++;
                        }
                        else
                        {
                            $tobedonelater[$condition[$i + 1]][0] = $condition[$i + 2];
                            $tobedonelater[$condition[$i + 1]][1] = $condition[$i + 3];
                        }
                    }
                    else
                    {
                        if ($condition[$i] == 'and')
                        {
                            //$command->addCondition(':condition_p1 LIKE :condition_p3')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_STR)->bindParam(":condition_p3", "%".$condition[$i + 3]."%", PDO::PARAM_STR);
                        	$command->addCondition($condition[$i+1].' LIKE "%'.$condition[$i+3].'%"');
                        }
                        else
                        {
                            //$command->addCondition(':condition_p1 LIKE :condition_p3', 'OR')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_STR)->bindParam(":condition_p3", "%".$condition[$i + 3]."%", PDO::PARAM_STR);
                            $command->addCondition($condition[$i+1].' LIKE "%'.$condition[$i+3].'%"', 'OR');
						}
                    }
                }
                else if ($condition[$i + 2] == 'notequal')
                {
                    if (is_numeric($condition[$i + 1]))
                    {
                        if ($condition[$i] == 'and')
                        {
                            $newsub = $j;
                            $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = :condition_p1 AND {{participant_attribute}}.value NOT IN (:condition_p3)')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_INT)->bindParam(":condition_p3", $condition[$i + 3], PDO::PARAM_STR)->queryAll();


                            $command->addInCondition('participant_id', $arr);
                            $j++;
                        }
                        else
                        {
                            $tobedonelater[$condition[$i + 1]][0] = $condition[$i + 2];
                            $tobedonelater[$condition[$i + 1]][1] = $condition[$i + 3];
                        }
                    }
                    else
                    {
                        if ($condition[$i] == 'and')
                        {

                            $command->addCondition(':condition_p1 NOT IN :condition_p3')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_STR)->bindParam(":condition_p3", "%".$condition[$i + 3]."%", PDO::PARAM_STR);
                        }
                        else
                        {
                            $command->addCondition(':condition_p1 NOT IN :condition_p3', 'OR')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_STR)->bindParam(":condition_p3", "%".$condition[$i + 3]."%", PDO::PARAM_STR);
                        }
                    }
                }
                else if ($condition[$i + 2] == 'notcontains')
                {
                    if (is_numeric($condition[$i + 1]))
                    {
                        if ($condition[$i] == 'and')
                        {
                            $newsub = $j;
                            $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = :condition_p1 AND {{participant_attribute}}.value NOT LIKE :condition_p3')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_INT)->bindParam(":condition_p3", "%".$condition[$i + 3]."%", PDO::PARAM_STR)->queryAll();


                            $command->addInCondition('participant_id', $arr);
                            $j++;
                        }
                        else
                        {
                            $tobedonelater[$condition[$i + 1]][0] = $condition[$i + 2];
                            $tobedonelater[$condition[$i + 1]][1] = $condition[$i + 3];
                        }
                    }
                    else
                    {
                        if ($condition[$i] == 'and')
                        {

                            $command->addCondition(':condition_p1 NOT LIKE :condition_p3')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_STR)->bindParam(":condition_p3", "%".$condition[$i + 3]."%", PDO::PARAM_STR);
                        }
                        else
                        {
                            $command->addCondition(':condition_p1 NOT LIKE :condition_p3', 'OR')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_STR)->bindParam(":condition_p3", "%".$condition[$i + 3]."%", PDO::PARAM_STR);
                        }
                    }
                }
                else if ($condition[$i + 2] == 'greaterthan')
                {
                    if (is_numeric($condition[$i + 1]))
                    {
                        if ($condition[$i] == 'and')
                        {
                            $newsub = $j;
                            $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = :condition_p1 AND {{participant_attribute}}.value > :condition_p3')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_INT)->bindParam(":condition_p3", $condition[$i + 3], PDO::PARAM_INT)->queryAll();


                            $command->addInCondition('participant_id', $arr);
                            $j++;
                        }
                        else
                        {
                            $tobedonelater[$condition[$i + 1]][0] = $condition[$i + 2];
                            $tobedonelater[$condition[$i + 1]][1] = $condition[$i + 3];
                        }
                    }
                    else
                    {
                        if ($condition[$i] == 'and')
                        {

                            $command->addCondition(':condition_p1 > :condition_p3')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_INT)->bindParam(":condition_p3", $condition[$i + 3], PDO::PARAM_INT);
                        }
                        else
                        {
                            $command->addCondition(':condition_p1 > :condition_p3', 'OR')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_INT)->bindParam(":condition_p3",$condition[$i + 3], PDO::PARAM_INT);
                        }
                    }
                }
                else if ($condition[$i + 2] == 'lessthan')
                {
                    if (is_numeric($condition[$i + 1]))
                    {
                        if ($condition[$i] == 'and')
                        {
                            $newsub = $j;
                            $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = :condition_p1 AND {{participant_attribute}}.value < :condition_p3')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_INT)->bindParam(":condition_p3", $condition[$i + 3], PDO::PARAM_INT)->queryAll();


                            $command->addInCondition('participant_id', $arr);
                            $j++;
                        }
                        else
                        {
                            $tobedonelater[$condition[$i + 1]][0] = $condition[$i + 2];
                            $tobedonelater[$condition[$i + 1]][1] = $condition[$i + 3];
                        }
                    }
                    else
                    {
                        if ($condition[$i] == 'and')
                        {
                            $command->addCondition(':condition_p1 < :condition_p3')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_INT)->bindParam(":condition_p3", $condition[$i + 3], PDO::PARAM_INT);
                        }
                        else
                        {
                            $command->addCondition(':condition_p1 < :condition_p3', 'OR')->bindParam(":condition_p1", $condition[$i + 1], PDO::PARAM_INT)->bindParam(":condition_p3", $condition[$i + 3], PDO::PARAM_INT);
                        }
                    }
                }
                $i = $i + 4;
            }
            else
            {
                $i = $i + 4;
            }
        }
    	//print_r($command); die();
    	if ($page == 0 && $limit == 0)
        {
            $arr = Participants::model()->findAll($command);
            $data = array();
            foreach ($arr as $t)
            {
                $data[$t->participant_id] = $t->attributes;
            }
        }
        else
        {
            $command->limit = $limit;
            $command->offset = $start;
            $arr = Participants::model()->findAll($command);
            $data = array();
            foreach ($arr as $t)
            {
                $data[$t->participant_id] = $t->attributes;
            }
        }

        $otherdata = $data;
        if (!empty($tobedonelater))
        {
            $command = new CDBCriteria;
            $command->select = 'participant_id';
            $command->distinct = TRUE;
            $command->condition = '';
            foreach ($tobedonelater as $key => $value)
            {
                if ($value[0] == 'equal')
                {
                    $command->addCondition('attribute_id = :attrid', 'OR')->bindParam(":attrid", $key, PDO::PARAM_INT);
                    $command->addCondition('value = :val1')->bindParam(":val1", $value[1], PDO::PARAM_STR);
                }
                if ($value[0] == 'contains')
                {
                    $command->addCondition('attribute_id = :attrid', 'OR')->bindParam(":attrid", $key, PDO::PARAM_INT);
                    $command->addCondition('value LIKE :val1')->bindParam(":val1", "%".$value[1]."%", PDO::PARAM_STR);
                }
                if ($value[0] == 'notequal')
                {
                    $command->addCondition('attribute_id = :attrid', 'OR')->bindParam(":attrid", $key, PDO::PARAM_INT);
                    $command->addCondition('value != :val1')->bindParam(":val1", $value[1], PDO::PARAM_STR);
                }
                if ($value[0] == 'greaterthan')
                {
                    $command->addCondition('attribute_id = :attrid', 'OR')->bindParam(":attrid", $key, PDO::PARAM_INT);
                    $command->addCondition('value > :val1')->bindParam(":val1", $value[1], PDO::PARAM_STR);
                }
                if ($value[0] == 'lessthan')
                {
                    $command->addCondition('attribute_id = :attrid', 'OR')->bindParam(":attrid", $key, PDO::PARAM_INT);
                    $command->addCondition('value < :val1')->bindParam(":val1", $value[1], PDO::PARAM_STR);
                }
            }
            $participant_id = ParticipantAttributeNames::model()->findAll($command);
            $command = new CDBCriteria;
            $command->select = '*';
            $command->condition = '';
            foreach ($participant_id as $key => $value)
            {
                $command->addCondition('participant_id = :participant_id')->bindParam(":participant_id", $value->participant_id, PDO::PARAM_STR);
            }
            if ($page == 0 && $limit == 0)
            {
                $arr = Participants::model()->findAll($command);
                $data = array();
                foreach ($arr as $t)
                {
                    $data[$t->participant_id] = $t->attributes;
                }
            }
            else
            {
                $command->limit = $limit;
                $command->offset = $start;
                $arr = Participants::model()->findAll($command);
                $data = array();
                foreach ($arr as $t)
                {
                    $data[$t->participant_id] = $t->attributes;
                }
            }


            $orddata = $data;
            $finalanswer = array_merge($otherdata, $orddata);
            return $finalanswer;
        }
        else
        {
            return $otherdata;
        }
    }

    function is_owner($participant_id)
    {
        $userid = Yii::app()->session['loginID'];
        $is_owner = Yii::app()->db->createCommand()->select('participant_id')->where('participant_id = :participant_id AND owner_uid = :userid')->from('{{participants}}')->bindParam(":participant_id", $participant_id, PDO::PARAM_STR)->bindParam(":userid", $userid, PDO::PARAM_INT)->queryAll();
        //$is_owner->num_rows();
        $is_shared = Yii::app()->db->createCommand()->select('participant_id')->where('participant_id = :participant_id AND share_uid = :userid')->from('{{participant_shares}}')->bindParam(":participant_id", $participant_id, PDO::PARAM_STR)->bindParam(":userid", $userid, PDO::PARAM_INT)->queryAll();
        if (count($is_shared) || count($is_owner))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /*
     * This funciton is responsible for showing all the participant's shared by a particular user based on the user id
     */

    function getParticipantShared($userid)
    {
        return Yii::app()->db->createCommand()->select('{{participants}}.*, {{participant_shares}}.*')->from('{{participants}}')->join('{{participant_shares}}', '{{participant_shares}}.participant_id = {{participants}}.participant_id')->where('owner_uid = :userid')->bindParam(":userid", $userid, PDO::PARAM_INT)->queryAll();
    }

    /*
     * This funciton is responsible for showing all the participant's shared to the superadmin
     */

    function getParticipantSharedAll()
    {
        return Yii::app()->db->createCommand()->select('{{participants}}.*,{{participant_shares}}.*')->from('{{participants}}')->join('{{participant_shares}}', '{{participant_shares}}.participant_id = {{participants}}.participant_id')->queryAll();
    }

    function copytosurveyatt($surveyid, $mapped, $newcreate, $participantid)
    {
        Yii::app()->loadHelper('common');
        $duplicate = 0;
        $sucessfull = 0;
        $participantid = explode(",", $participantid);
        if ($participantid[0] == "")
        {
            $participantid = array_slice($participantid, 1);
        }
        $number2add = sanitize_int(count($newcreate));
        $arr = Yii::app()->db->createCommand()->select('*')->from("{{tokens_$surveyid}}")->queryRow();
        if (is_array($arr))
        {
            $tokenfieldnames = array_keys($arr);
            $tokenattributefieldnames = array_filter($tokenfieldnames, 'filterForAttributes');
        }
        else
        {
            $tokenattributefieldnames = array();
        }
        foreach ($tokenattributefieldnames as $key => $value)
        {
            if ($value[10] == 'c')
            {
                $attid = substr($value, 15);
                $mapped[$value] = $attid;
            }
        }
        $attributesadded = array();
        $attributeidadded = array();
        $fieldcontents = "";
        if (!empty($newcreate))
        {
            foreach ($newcreate as $key => $value)
            {
                $fields['attribute_cpdb_' . $value] = array('type' => 'VARCHAR', 'constraint' => '255');
                $attname = Yii::app()->db->createCommand()->select('{{participant_attribute_names_lang}}.attribute_name')->from('{{participant_attribute_names}}')->join('{{participant_attribute_names_lang}}', '{{participant_attribute_names}}.attribute_id = {{participant_attribute_names_lang}}.attribute_id')->where('{{participant_attribute_names}}.attribute_id = :attrid AND lang = "' . Yii::app()->session['adminlang'] . '"')->bindParam(":attrid", $value, PDO::PARAM_INT);
                $attributename = $attname->queryRow();
                $tokenattributefieldnames[] = 'attribute_cpdb_' . $value;
                $fieldcontents.= 'attribute_cpdb_' . $value . '=' . $attributename['attribute_name'] . "\n";
                array_push($attributeidadded, 'attribute_cpdb_' . $value);
                array_push($attributesadded, $value);
            }
            $previousatt = Yii::app()->db->createCommand()->select('attributedescriptions')->where("sid = :sid")->from('{{surveys}}')->bindParam(":sid", $surveyid, PDO::PARAM_INT);
            $previouseattribute = $previousatt->queryRow();
            Yii::app()->db->createCommand()->update('{{surveys}}', array("attributedescriptions" => Yii::app()->db->quoteValue($previouseattribute['attributedescriptions'] . $fieldcontents)), 'sid = '.intval($surveyid)); // load description in the surveys table
            foreach ($fields as $key => $value)
            {
                Yii::app()->db->createCommand("ALTER TABLE {{tokens_$surveyid}} ADD COLUMN ". Yii::app()->db->quoteColumnName($key) ." ". $value['type'] ." ( ".intval($value['constraint'])." )")->query(); // add columns in token's table
            }
        }
        //Function for pushing associative array
        foreach ($participantid as $key => $participant)
        {
            $writearray = array();
            $participantdata = Yii::app()->db->createCommand()->select('firstname,lastname,email,language,blacklisted')->where('participant_id = "' . $participant . '"')->from('{{participants}}');
            $tobeinserted = $participantdata->queryRow();
            $query = Yii::app()->db->createCommand()->select('*')->from('{{tokens_' . $surveyid . '}}')->where('firstname = "' . $tobeinserted['firstname'] . '" AND lastname = "' . $tobeinserted['lastname'] . '" AND email = "' . $tobeinserted['email'] . '"')->queryAll();
            if (count($query) > 0)
            {
                $duplicate++;
            }
            else
            {
                $writearray = array('participant_id' => $participant, 'firstname' => $tobeinserted['firstname'], 'lastname' => $tobeinserted['lastname'], 'email' => $tobeinserted['email'], 'emailstatus' => 'OK', 'language' => $tobeinserted['language']);
                Yii::app()->db->createCommand()->insert('{{tokens_' . $surveyid . '}}', $writearray);
                $insertedtokenid = Yii::app()->db->getLastInsertID();
                $time = time();
                $data = array(
                    'participant_id' => $participant,
                    'token_id' => $insertedtokenid,
                    'survey_id' => $surveyid,
                    'date_created' => date(DATE_W3C, $time));
                Yii::app()->db->createCommand()->insert('{{survey_links}}', $data);
                if (!empty($newcreate))
                {
                    $numberofattributes = count($attributesadded);
                    for ($a = 0; $a < $numberofattributes; $a++)
                    {
                        $val = Yii::app()->db->createCommand()->select('value')->where('participant_id = :participant_id AND attribute_id = :attrid')->from('{{participant_attribute}}')->bindParam("participant_id", $participant, PDO::PARAM_STR)->bindParam(":attrid", $attributesadded[$a], PDO::PARAM_INT);
                        if (count($val->queryAll()) > 0)
                        {
                            $value = $val->queryRow();
                            $data = array($attributeidadded[$a] => $value['value']);
                            if (!empty($value))
                            {
                                Yii::app()->db->createCommand()->update("{{tokens_$surveyid}}", $data, 'participant_id = :participant_id')->bindParam("participant_id", $participant, PDO::PARAM_STR);
                            }
                        }
                    }
                }
                if (!empty($mapped))
                {
                    foreach ($mapped as $key => $value)
                    {
                        $val = Yii::app()->db->createCommand()->select('value')->where('participant_id = :participant_id AND attribute_id = :attrid')->from('{{participant_attribute}}')->bindParam("participant_id", $participant, PDO::PARAM_STR)->bindParam(":attrid", $attributesadded[$a], PDO::PARAM_INT);
                        $value = $val->queryRow();
                        if (isset($value['value']))
                        {
                            $data = array($key => $value['value']);
                            Yii::app()->db->createCommand()->update("{{tokens_$surveyid}}", $data, 'participant_id = :participant_id')->bindParam("participant_id", $participant, PDO::PARAM_STR);
                        }
                    }
                }
                $sucessfull++;
            }
        }
        $returndata = array('success' => $sucessfull, 'duplicate' => $duplicate);
        return $returndata;
    }

    /*
     * This function is responsible for checking for any exsisting record in the token table and if not copy the participant to it
     */

    function copyToCentral($surveyid, $newarr, $mapped)
    {
        $tokenid = Yii::app()->session['participantid'];
        $duplicate = 0;
        $sucessfull = 0;
        $writearray = array();
        $attid = array();
        $pid = "";
        $arr = Yii::app()->db->createCommand()->select('*')->from("{{tokens_$surveyid}}")->queryRow();
        if (is_array($arr))
        {
            $tokenfieldnames = array_keys($arr);
            $tokenattributefieldnames = array_filter($tokenfieldnames, 'filterForAttributes');
        }
        else
        {
            $tokenattributefieldnames = array();
        }
        foreach ($tokenattributefieldnames as $key => $value) //mapping the automatically mapped
        {
            if ($value[10] == 'c')
            {
                $attid = substr($value, 15);
                $mapped[$attid] = $value;
            }
        }
        if (!empty($newarr))
        {
            foreach ($newarr as $key => $value) //creating new central attribute
            {
                $insertnames = array('attribute_type' => 'TB', 'visible' => 'Y');
                Yii::app()->db->createCommand()->insert('{{participant_attribute_names}}', $insertnames);
                $attid[$key] = Yii::app()->db->getLastInsertID();
                $insertnameslang = array('attribute_id' => Yii::app()->db->getLastInsertID(),
                    'attribute_name' => urldecode($key),
                    'lang' => Yii::app()->session['adminlang']);
                Yii::app()->db->createCommand()->insert('{{participant_attribute_names_lang}}', $insertnameslang);
            }
        }
        foreach ($tokenid as $key => $tid)
        {
            if (is_numeric($tid) && $tid != "")
            {
                $tobeinserted = Yii::app()->db->createCommand()->select('participant_id,firstname,lastname,email,language')->where('tid = :tid')->from('{{tokens_' . intval($surveyid) . '}}')->bindParam(":tid", $tid, PDO::PARAM_INT)->queryRow();
                $query = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->where('firstname = :firstname AND lastname = :lastname AND email = :email')->bindParam(":firstname", $tobeinserted['firstname'], PDO::PARAM_STR)->bindParam(":lastname", $tobeinserted['lastname'], PDO::PARAM_STR)->bindParam(":email", $tobeinserted['email'], PDO::PARAM_STR)->queryAll();
                if (count($query) > 0)
                {
                    $duplicate++;
                }
                else
                {
                    if (empty($tobeinserted['blacklisted']))
                    {
                        $black = 'N';
                    }
                    else
                    {
                        $black = $tobeinserted['blacklisted'];
                    }
                    if (!empty($tobeinserted['participant_id']))
                    {
                        $writearray = array('participant_id' => $tobeinserted['participant_id'], 'firstname' => $tobeinserted['firstname'], 'lastname' => $tobeinserted['lastname'], 'email' => $tobeinserted['email'], 'language' => $tobeinserted['language'], 'blacklisted' => $black, 'owner_uid' => Yii::app()->session['loginID']);
                    }
                    else
                    {
                        $writearray = array('participant_id' => $this->gen_uuid(), 'firstname' => $tobeinserted['firstname'], 'lastname' => $tobeinserted['lastname'], 'email' => $tobeinserted['email'], 'language' => $tobeinserted['language'], 'blacklisted' => $black, 'owner_uid' => Yii::app()->session['loginID']);
                    }
                    $pid = $writearray['participant_id'];
                    Yii::app()->db->createCommand()->insert('{{participants}}', $writearray);
                    if (!empty($newarr))
                    {
                        foreach ($newarr as $key => $value)
                        {
                            $val = Yii::app()->db->createCommand()->select($value)->where('tid = :tid')->from('{{tokens_' . intval($surveyid) . '}}')->bindParam(":tid", $tid, PDO::PARAM_INT);
                            $value2 = $val->queryRow();
                            $data = array('participant_id' => $pid,
                                'value' => $value2["$value"],
                                'attribute_id' => $attid[$key]);

                            if (!empty($data['value']))
                            {
                                Yii::app()->db->createCommand()->insert('{{participant_attribute}}', $data);
                            }
                        }
                    }
                    if (!empty($mapped))
                    {
                        foreach ($mapped as $cpdbatt => $tatt)
                        {
                            $val = Yii::app()->db->createCommand()->select($tatt)->where('tid = :tid')->from('{{tokens_' . intval($surveyid) . '}}')->bindParam(":tid", $tid, PDO::PARAM_INT);
                            $value = $val->queryRow();
                            $data = array('participant_id' => $pid,
                                'value' => $value["$tatt"],
                                'attribute_id' => $cpdbatt);
                            if (!empty($data['value']))
                            {
                                Yii::app()->db->createCommand()->insert('{{participant_attribute}}', $data);
                            }
                        }
                    }
                    $sucessfull++;
                }
            }
        }
        if (!empty($newarr))
        {
            foreach ($newarr as $key => $value)
            {
                $newname = 'attribute_cpdb_' . intval($attid[$key]);
                $fields = array($value => array('name' => $newname, 'type' => 'TEXT'));
                Yii::app()->db->createCommand()->renameColumn('{{tokens_' . intval($surveyid) . '}}', $value, $newname);
                Yii::app()->db->createCommand()->alterColumn('{{tokens_' . intval($surveyid) . '}}', $newname, 'TEXT');
                $previousatt = Yii::app()->db->createCommand()->select('attributedescriptions')->where("sid = :sid")->bindParam(":sid", $surveyid, PDO::PARAM_INT)->from('{{surveys}}');
                $previouseattribute = $previousatt->queryRow();
                $newstring = str_replace($value, $newname, $previouseattribute['attributedescriptions']);
                Yii::app()->db->createCommand()->update('{{surveys}}', array("attributedescriptions" => $newstring), 'sid = :sid')->bindParam(":sid", $surveyid, PDO::PARAM_INT); // load description in the surveys table
            }
        }
        if (!empty($mapped))
        {
            foreach ($mapped as $cpdbatt => $tatt)
            {
                if ($tatt[10] != 'c')
                {
                    $newname = 'attribute_cpdb_' . $cpdbatt;
                    $fields = array($tatt => array('name' => $newname, 'type' => 'TEXT'));
                    Yii::app()->db->createCommand()->renameColumn('{{tokens_' . intval($surveyid) . '}}', $tatt, $newname);
                    Yii::app()->db->createCommand()->alterColumn('{{tokens_' . intval($surveyid) . '}}', $newname, 'TEXT');
                    $previousatt = Yii::app()->db->createCommand()->select('attributedescriptions')->where("sid = :sid")->bindParam(":sid", $surveyid, PDO::PARAM_INT)->from('{{surveys}}');
                    $previouseattribute = $previousatt->queryRow();
                    $newstring = str_replace($tatt, $newname, $previouseattribute['attributedescriptions']);
                    Yii::app()->db->createCommand()->update('{{surveys}}', array("attributedescriptions" => $newstring), 'sid = :sid')->bindParam(":sid", $surveyid, PDO::PARAM_INT); // load description in the surveys table
                }
            }
        }
        $returndata = array('success' => $sucessfull, 'duplicate' => $duplicate);
        return $returndata;
    }

	/*
     * The purpose of this function is to check for duplicate in participants
     */

    function checkforDuplicate($fields)
    {
        $query = Yii::app()->db->createCommand()->select('*')->where($fields)->from('{{participants}}')->queryAll();
        if (count($query) > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function insertParticipantCSV($data)
    {
        $insertData = array(
            'participant_id' => $data['participant_id'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'language' => $data['language'],
            'blacklisted' => $data['blacklisted'],
            'owner_uid' => $data['owner_uid']);
        Yii::app()->db->createCommand()->insert('{{participants}}', $insertData);
    }
}
