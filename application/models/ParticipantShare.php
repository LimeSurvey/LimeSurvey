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
            array('participant_id, participant.firstname, participant.lastname, participant.email, share_uid, date_added, can_edit', 'safe', 'on'=>'search'),
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
            'shared_by' => array(self::HAS_ONE, 'User', array('uid' => 'share_uid')),
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

  public function getSharedByList($selected){
        $share_uids = Yii::app()->db->createCommand()
            ->selectDistinct('share_uid')
            ->from('{{participant_shares}}')
            ->queryAll();
        $shareList = array(''=>"");
        foreach($share_uids as $id){
            $user = User::model()->getName($id['share_uid']);
            $shareList[$id['share_uid']] = $user['full_name'];
        }
        return TbHtml::dropDownList('ParticipantShare[share_uid]',$selected, $shareList);
        
    }

    /**
     * @return string HTML
     */
    public function getCanEditHtml(){
        $loggedInUser = yii::app()->user->getId();
        if($this->participant->owner_uid == $loggedInUser)
        {
            $inputHtml = "<input type='checkbox' data-size='small' data-off-color='warning' data-on-color='primary' data-off-text='".gT('No')."' data-on-text='".gT('Yes')."' class='action_changeEditableStatus' "
            . ($this->can_edit ? "checked" : "")
            . "/>";
            return  $inputHtml;
        }
        else 
        {
            return ($this->can_edit ? gT("Yes") : gT('No'));
        }
    }

    /**
     * @return string HTML
     */
    public function getButtons(){
        $loggedInUser = yii::app()->user->getId();
        if($this->participant->owner_uid == $loggedInUser)
        {
            return "<a href='#' data-toggle='modal' data-target='#confirmation-modal' data-onclick='rejectParticipantShareAjax(\"".$this->participant_id."\")'>"
                . "<button class='btn btn-xs btn-default action_delete_shareParticipant'><i class='fa fa-trash text-danger'></i></button>"
                . "</a>";
        } 
        else 
        {
            return "<button class='btn btn-xs btn-default disabled'><i class='fa fa-ban text-muted'></i></button>";
        }
    }
    public function getColumns(){
        $participantFilter = yii::app()->request->getPost('Participant');
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
                "filter" => TbHtml::textField("Participant[firstname]", $participantFilter['firstname'])
            ),
            array(
                "name" => 'participant.lastname',
                "header" => gT("Lastname"),
                "filter" => TbHtml::textField("Participant[lastname]",$participantFilter['lastname'])
            ),
            array(
                "name" => 'participant.email',
                "header" => gT("Email"),
                "filter" => TbHtml::textField("Participant[email]",$participantFilter['email'])
            ),
            array(
                "name" => 'share_uid',
                "value" => '$data->shared_by[\'full_name\']',
                "header" => gT("Shared By"),
                "filter" => $this->getSharedByList($this->share_uid)
            ),
            array(
                "name" => 'date_added',
                "header" => gT("Date added")
            ),
            array(
                "name" => 'can_edit',
                "value" => '$data->getCanEditHtml()',
                "header" => gT("Can edit?"),
                "filter" => array(1 => gT('Yes'), 0=> gT('No')),
                "type" =>"raw"
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
            'participant.firstname'=>array(
                'asc'=>'participant.firstname asc',
                'desc'=>'participant.firstname desc',
            ),
            'participant.lastname'=>array(
                'asc'=>'participant.lastname asc',
                'desc'=>'participant.lastname desc',
            ),
            'participant.email'=>array(
                'asc'=>'participant.email asc',
                'desc'=>'participant.email desc',
            ),
            'share_uid'=>array(
                'asc'=>'shared_by.full_name asc',
                'desc'=>'shared_by.full_name desc',
            ),
            'date_added'=>array(
                'asc'=>'date_added asc',
                'desc'=>'date_added desc',
            ),
            'can_edit'=>array(
                'asc'=>'can_edit asc',
                'desc'=>'can_edit desc',
            ),
        );
        $sort->attributes = $sortAttributes;
        $sort->defaultOrder = 'participant.firstname ';

        $participantFilter = Yii::app()->request->getPost('Participant');

        $criteria=new CDbCriteria;
        $criteria->with = array('participant','shared_by');
        $criteria->compare('participant_id',$this->participant_id, false);
        $criteria->compare('share_uid',$this->share_uid);
        $criteria->compare('date_added',$this->date_added,true);
        $criteria->compare('can_edit',$this->can_edit,true);
        $criteria->compare('participant.lastname',$participantFilter['lastname'],true);
        $criteria->compare('participant.firstname',$participantFilter['firstname'],true);
        $criteria->compare('participant.email',$participantFilter['email'],true);

        $pageSize = Yii::app()->user->getState('pageSizeShareParticipantView', Yii::app()->params['defaultPageSize']);
        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
            'sort' => $sort,
            'pagination' => array(
                'pageSize' => $pageSize
            )
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
