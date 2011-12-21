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
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */
/**
 * This is the model class for table "{{participant_shares}}".
 *
 * The followings are the available columns in table '{{participant_shares}}':
 * @property string $participant_id
 * @property integer $share_uid
 * @property string $date_added
 * @property string $can_edit
 */
class ParticipantShares extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ParticipantShares the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{participant_shares}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('participant_id, share_uid, date_added, can_edit', 'required'),
			array('share_uid', 'numerical', 'integerOnly'=>true),
			array('participant_id', 'length', 'max'=>50),
			array('can_edit', 'length', 'max'=>5),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('participant_id, share_uid, date_added, can_edit', 'safe', 'on'=>'search'),
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
			'share_uid' => 'Share Uid',
			'date_added' => 'Date Added',
			'can_edit' => 'Can Edit',
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

		$criteria=new CDbCriteria;

		$criteria->compare('participant_id',$this->participant_id,true);
		$criteria->compare('share_uid',$this->share_uid);
		$criteria->compare('date_added',$this->date_added,true);
		$criteria->compare('can_edit',$this->can_edit,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function storeParticipantShare($data)
    {
		$ownerid = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->where('participant_id = "'.$data['participant_id'].'"')->queryRow();
		if($ownerid['owner_uid'] != $data['share_uid'])// A check to ensure that the participant is not added to it's owner
		{
			Yii::app()->db->createCommand()->insert('{{participant_shares}}',$data);
		}
    }

    function updateShare($data)
    {
		$criteria = new CDbCriteria;
		$criteria->addCondition('participant_id = "'.$data['participant_id'].'"');
		$criteria->addCondition('share_uid = '.$data['share_uid']);
		ParticipantShares::model()->updateAll($data,$criteria);
    }

    function deleteRow($rows)
    {
		// Converting the comma seperated id's to an array to delete multiple rows
		$rowid=explode(",",$rows['id']);
		foreach($rowid as $row)
		{
			Yii::app()->db->createCommand()->delete('{{participant_shares}}','participant_id = "'.$row.'"');
	    }
	}
}
