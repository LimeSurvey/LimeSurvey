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
/**
 * This is the model class for table "{{participant_shares}}".
 *
 * The followings are the available columns in table '{{participant_shares}}':
 * @property string $participant_id
 * @property integer $share_uid
 * @property string $date_added
 * @property string $can_edit
 */
class ParticipantShare extends LSActiveRecord
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
            'participant' => array(self::HAS_ONE, 'Participant', array('participant_id' => 'participant_id')), 
            'owner' => array(self::HAS_ONE, 'User', array('uid' => 'participant.owner_uid')),
            'shared_with' => array(self::HAS_ONE, 'User', array('uid' => 'share_uid')),
            'surveylinks' => array(self::HAS_ONE, 'SurveyLink', 'participant_id'),
            'participantAttributes' => array(self::HAS_MANY, 'ParticipantAttribute', 'participant_id', 'with'=>'participant_attribute_names', 'joinType'=> 'LEFT JOIN')
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

    public function getColumns(){
        $cols = array(
            array(
                "name" => 'buttons',
                "type" => 'raw',
                "header" => gT("Action"),
                "filter" => false
            ),
            array(
                "name" => 'participant.firstname',
                "header" => gT("Firstname"),
                "filter" => TbHtml::textField("ParticipantShare[Participant][full_name]", $this->participant['firstname'])
            ),
            array(
                "name" => 'participant.lastname',
                "header" => gT("Lastname"),
                "filter" => TbHtml::textField("ParticipantShare[Participant][lastname]",$this->participant['lastname'])
            ),
            array(
                "name" => 'participant.email',
                "header" => gT("Email"),
                "filter" => TbHtml::textField("ParticipantShare[Participant][email]",$this->participant['email'])
            ),
            array(
                "name" => 'shared_with.full_name',
                "header" => gT("Shared with"),
                "filter" => TbHtml::textField("ParticipantShare[SharedWith][full_name]",$this->shared_with['full_name'])
            ),
            array(
                "name" => 'owner.full_name',
                "header" => gT("Owner"),
                "filter" => TbHtml::textField("ParticipantShare[Owner][full_name]",$this->owner['full_name'])
            ),
            array(
                "name" => 'date_added',
                "header" => gT("Date added")
            ),
            array(
                "name" => 'can_edit',
                "header" => gT("Can edit?")
            ),
        );
        return $cols;

    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.
        $sort = new CSort;
        $sortAttributes = array(
          'firstname'=>array(
            'asc'=>'participant.firstname',
            'desc'=>'participant.firstname desc',
          ),
          'lastname'=>array(
            'asc'=>'participant.lastname',
            'desc'=>'participant.lastname desc',
          ),
          'email'=>array(
            'asc'=>'participant.email',
            'desc'=>'participant.email desc',
          ),
          'language'=>array(
            'asc'=>'language',
            'desc'=>'language desc',
          ),

          'owner.full_name'=>array(
            'asc'=>'owner.full_name',
            'desc'=>'owner.full_name desc',
          ),
          'blacklisted'=>array(
            'asc'=>'blacklisted',
            'desc'=>'blacklisted desc',
          )
        );

        $criteria=new CDbCriteria;

        $criteria->compare('participant_id',$this->participant_id, false);
        $criteria->compare('share_uid',$this->share_uid);
        $criteria->compare('date_added',$this->date_added,true);
        $criteria->compare('can_edit',$this->can_edit,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    public function storeParticipantShare($data)
    {
        $ownerid = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->where('participant_id = :participant_id')->bindParam(":participant_id", $data['participant_id'], PDO::PARAM_STR)->queryRow();
        // CHeck if share already exists
        $arShare=self::findByPk(array('participant_id'=>$data['participant_id'],'share_uid'=>$data['share_uid']));
        if ($ownerid['owner_uid'] == $data['share_uid']) return;
        if(is_null($arShare ))// A check to ensure that the participant is not added to it's owner
        {
            Yii::app()->db->createCommand()->insert('{{participant_shares}}',$data);
        }
        else
        {
           self::updateShare($data);
        }
    }

    function updateShare($data)
    {
        if (strpos( $data['participant_id'],'--' )!==false)
        {
            list($participantId, $shareuid)=explode("--", $data['participant_id']);
            $data=array("participant_id"=>$participantId, "share_uid"=>$shareuid, "can_edit"=>$data['can_edit']);
        }
        $criteria = new CDbCriteria;
        $criteria->addCondition("participant_id = '{$data['participant_id']}'");
        $criteria->addCondition("share_uid = '{$data['share_uid']}' ");
        ParticipantShare::model()->updateAll($data,$criteria);
    }

    function deleteRow($rows)
    {
        // Converting the comma separated id's to an array to delete multiple rows
        $rowid=explode(",",$rows);
        foreach($rowid as $row)
        {
            list($participantId, $uId)=explode("--", $row);
            Yii::app()->db
                      ->createCommand()
                      ->delete('{{participant_shares}}',"participant_id = '$participantId' AND share_uid = $uId");
        }
    }
}
