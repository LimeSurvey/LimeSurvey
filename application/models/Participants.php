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
	 * Returns the static model of the specified AR class.
	 * @return Participants the static model class
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
			array('owner_uid', 'numerical', 'integerOnly'=>true),
			array('participant_id', 'length', 'max'=>50),
			array('firstname, lastname, language', 'length', 'max'=>40),
			array('email', 'length', 'max'=>80),
			array('blacklisted', 'length', 'max'=>1),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('participant_id, firstname, lastname, email, language, blacklisted, owner_uid', 'safe', 'on'=>'search'),
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

		$criteria=new CDbCriteria;

		$criteria->compare('participant_id',$this->participant_id,true);
		$criteria->compare('firstname',$this->firstname,true);
		$criteria->compare('lastname',$this->lastname,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('language',$this->language,true);
		$criteria->compare('blacklisted',$this->blacklisted,true);
		$criteria->compare('owner_uid',$this->owner_uid);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/*
	 * funcion for generation of unique id
	 */
	function gen_uuid()
	{
	    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
	    mt_rand( 0, 0xffff ),
	    mt_rand( 0, 0x0fff ) | 0x4000,
	    mt_rand( 0, 0x3fff ) | 0x8000,
	    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	    );
	}

	/*
	 * This function is responsible for adding the participant to the database
	 * Parameters : participant data
	 * Return Data : none
	 */
	function insertParticipant($data)
	{
		Yii::app()->db->createCommand()->insert('{{participants}}',$data)->query();
	}

	/*
	* This function updates the data edited in the jqgrid
	* Parameters : data that is edited
	* Return Data : None
	*/
	function updateRow($data)
	{
		Yii::app()->db->createCommand()->update('{{participants}}',$data,'participant_id = "'.$data['participant_id'].'"');
	}

	function deleteParticipantTokenAnswer($rows)
	{
	    $rowid=explode(",",$rows);
	    //$rowid = array('243148a0-bf56-4ee1-a6d2-a1f1cb5243d5');
	    foreach($rowid as $row)
	    {
	    	$tokens = Yii::app()->db->createCommand()->select('*')->from('{{survey_links}}')->where('participant_id = "'.$row.'"')->queryAll();
            foreach($tokens as $key => $value)
            {
            	Yii::app()->db->createCommand()->delete('{{participants}}','participant_id = "'.$row.'"'); //Delete from participants
                if(Yii::app()->db->schema->getTable('tokens_'.$value['survey_id']))
                {
                	$tokenid = Yii::app()->db->createCommand()->select('token')->from('{{tokens_'.$value['survey_id'].'}}')->where('participant_id = "'.$value['participant_id'].'"')->queryAll();
                    $token = $tokenid[0];
                    if((Yii::app()->db->schema->getTable('survey_'.$value['survey_id'])))
                    {
                        if(!empty($token['token']))
                        {
                        	$gettoken = Yii::app()->db->createCommand()->select('*')->from('{{survey_'.$value['survey_id'].'}}')->where('token = '.$token['token'])->queryAll();
                        	$gettoken = $gettoken[0];
                        	Yii::app()->db->createCommand()->delete('{{survey_'.$value['survey_id'].'}}','token = '. $gettoken['token']);
                        }
                    }
                    Yii::app()->db->createCommand()->delete('{{tokens_'.$value['survey_id'].'}}', 'participant_id = "'.$value['participant_id'].'"');// Deletes from token
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
	   return Yii::app()->db->createCommand()->select('{{participants}}.*,{{participant_shares}}.can_edit')->from('{{participants}}')->leftJoin('{{participant_shares}}',' {{participants}}.participant_id={{participant_shares}}.participant_id')->where('owner_uid = '.$userid.' OR share_uid = '.$userid)->group('{{participants}}.participant_id')->queryAll();
	}

	function getParticipantsOwnerCount($userid)
	{
	    return count(Yii::app()->db->createCommand()->select('{{participants}}.*,{{participant_shares}}.can_edit')->from('{{participants}}')->leftJoin('{{participant_shares}}',' {{participants}}.participant_id={{participant_shares}}.participant_id')->where('owner_uid = '.$userid.' OR share_uid = '.$userid)->group('{{participants}}.participant_id')->queryAll());
	}

	function getParticipantswithoutlimit()
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
	    return count(Yii::app()->db->createCommand()->select('{{participants}}.*, {{participant_shares}}.*')->from('{{participants}}')->join('{{participant_shares}}','{{participant_shares}}.participant_id = {{participants}}.participant_id')->where('owner_uid = '.$userid)->queryAll());
	}

	function getParticipants($page,$limit)
	{
	    $start = $limit*$page - $limit;
	    $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->limit($limit, $start)->queryAll();
	    return $data;
	}

	function getSurveyCount($participant_id)
	{
	    $count = count(Yii::app()->db->createCommand()->select('*')->from('{{survey_links}}')->where('participant_id = "'.$participant_id.'"')->queryAll());
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
	    $rowid=explode(",",$rows);
	    foreach($rowid as $row)
	    {
	    	Yii::app()->db->createCommand()->delete('{{participants}}','participant_id = "'.$row.'"');
	    	Yii::app()->db->createCommand()->delete('{{survey_links}}','participant_id = "'.$row.'"');
	    	Yii::app()->db->createCommand()->delete('{{participant_attribute}}','participant_id = "'.$row.'"');
	    }
	}

	function deleteParticipantToken($rows)
	{
	    $rowid=explode(",",$rows);
	    foreach($rowid as $row)
	    {
	        $tokens = Yii::app()->db->createCommand()->select('*')->from('{{survey_links}}')->where('participant_id = "'.$row.'"')->queryAll();
	        foreach($tokens as $key => $value)
	        {
	            if(Yii::app()->db->schema->getTable('tokens_'.$value['survey_id']))
	            {
	            	Yii::app()->db->createCommand()->delete('{{tokens_'.$value['survey_id'].'}}','participant_id = "'.$value['participant_id'].'"');
	            }
	        }
	        Yii::app()->db->createCommand()->delete('{{participants}}','participant_id = "'.$row.'"');
	    	Yii::app()->db->createCommand()->delete('{{survey_links}}','participant_id = "'.$row.'"');
	    	Yii::app()->db->createCommand()->delete('{{participant_attribute}}','participant_id = "'.$row.'"');
	    }
	}

	function getParticipantsSearch($condition,$page,$limit)
	{
	    $start = $limit*$page - $limit;
	    if($condition[1]=='equal')
	        {
	          if($condition[0]=='surveys')
	          {
	              $resultarray = array();
	              if($page == 0 && $limit == 0)
	              {
	              $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
	              }
	                  else
	              {
	                  $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->limit($limit,$start)->queryAll();
	              }
	              foreach($data as $key=>$value)
	              {
	                  $count = count(Yii::app()->db->createCommand()->where('participant_id = "'.$value['participant_id'].'"')->from('{{survey_links}}')->select('*')->queryAll());
	                  if($count == $condition[2])
	                  {
	                     array_push($resultarray,$value);
	                  }
	              }
	              return $resultarray;
	          }
	          else if($condition[0]=='owner_name')
	          {
	                $userid = Yii::app()->db->createCommand()->select('uid')->where('full_name = "'.$condition[2].'"')->from('{{users}}')->queryAll();
	                $uid = $userid[0];
	                $command = Yii::app()->db->createCommand();
	                $command->where('owner_uid = '.$uid['uid']);
	                $command->select('*');
	                if($page == 0 && $limit == 0)
	                {
	                $data= $command->from('{{participants}}')->queryAll();
	                }
	                else
	                {
	                	$data = $command->from('{{participants}}')->limit($limit,$start)->queryAll();
	                }
	                return $data;
	          }
	          else if(is_numeric($condition[0]))
	          {
	          	    $command = Yii::app()->db->createCommand()->select('{{participant_attribute}}.*,{{participants}}.*')->from('{{participant_attribute}}')->join('{{participants}}', '{{participant_attribute}}.participant_id = {{participants}}.participant_id')->where('{{participant_attribute}}.attribute_id = '.$condition[0].' AND {{participant_attribute}}.value = "'.$condition[2].'"');
	                if($page == 0 && $limit == 0)
	                {
	                $data = $command->queryAll();
	                }
	                else
	                {
	                      $data = $command->limit($limit,$start)->queryAll();
	                }
	                return $data;
	          }
	          else
	          {
	          	$command = Yii::app()->db->createCommand()->where($condition[0].' = "'.$condition[2].'"');
	            if($page == 0 && $limit == 0)
	              {
	            $data=$command->select('*')->from('{{participants}}')->queryAll();
	              }
	              else
	              {
	                  $data = $command->select('*')->from('{{participants}}')->limit($limit,$start)->queryAll();
	              }
	            return $data;
	          }
	        }
	        else if($condition[1]=='contains')
	        {
	            $condition[2] = '%'.$condition[2].'%';
	          if($condition[0]=='surveys')
	          {
	              $resultarray = array();
	              if($page == 0 && $limit == 0)
	              {
	              $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
	              }
	              else
	              {
	                  $data = Yii::app()->db->createCommand()->select('*')->limit($limit,$start)->from('{{participants}}')->queryAll();
	              }
	              foreach($data as $key=>$value)
	              {
	                  $count = count(Yii::app()->db->createCommand()->where('participant_id = "'.$value['participant_id'].'"')->from('{{survey_links}}')->queryAll());
	                  if($count == $condition[2])
	                  {
	                     array_push($resultarray,$value);
	                  }
	              }
	              return $resultarray;
	          }

	          else if($condition[0]=='owner_name')
	          {
	                $userid = $command = Yii::app()->db->createCommand()->select('uid')->where(array('like','full_name',$condition[2]))->from('{{users}}')->queryAll();
	                $uid = $userid[0];
	                $command = Yii::app()->db->createCommand()->where('owner_uid = '.$uid['uid'])->order("lastname", "asc")->select('*')->from('{{participants}}');
	                if($page == 0 && $limit == 0)
	                  {
	                $data=$command->queryAll();
	                  }
	                  else
	                  {
	                  $data = $command->limit($limit,$start)->queryAll();
	                    }
	                return $data;
	          }
	          else if(is_numeric($condition[0]))
	          {
	                $command = Yii::app()->db->createCommand()->select('{{participant_attribute}}.*,{{participants}}.*')->from('{{participant_attribute}}')->join('{{participants}}', '{{participant_attribute}}.participant_id = {{participants}}.participant_id')->where('{{participant_attribute}}.attribute_id = '.$condition[0])->where(array('like','{{participant_attribute}}.value',$condition[2]));
	                if($page == 0 && $limit == 0)
	                {
	                $data=$command->queryAll();
	                }
	                else
	                {
	                      $data = $command->limit($limit,$start);
	                }
	                return $data;
	          }
	          else
	          {
	                $command = Yii::app()->db->createCommand()->where(array('like',$condition[0],$condition[2]))->select('*')->from('{{participants}}');
	                if($page == 0 && $limit == 0)
	                  {
	                $data=$command->queryAll();
	                  }
	                  else
	                  {
	                    $data = $command->limit($limit,$start)->queryAll();
	                  }
	                return $data;
	          }

	        }
	        else if($condition[1]=='notequal')
	        {
	            if($condition[0]=='surveys')
	          {
	              $resultarray = array();

	              if($page == 0 && $limit == 0)
	                  {
	              $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
	                  }
	                  else
	                  {
	                  $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->limit($limit,$start)->queryAll();
	                    }
	              foreach($data as $key=>$value)
	              {
	                  $count = count(Yii::app()->db->createCommand()->select('*')->from('{{survey_links}}')->where('participant_id = "'.$value['participant_id'].'"')->queryAll());
	                  if($count != $condition[2])
	                  {
	                     array_push($resultarray,$value);
	                  }
	              }
	              return $resultarray;
	          }
	          else if($condition[0]=='owner_name')
	          {

	                $userid = Yii::app()->db->createCommand()->select('uid')->where(array('not in','full_name',$condition[2]))->from('{{users}}')->queryAll();
	                $uid = $userid[0];
	                $command = Yii::app()->db->createCommand()->where('owner_uid = '.$uid['uid'])->from('{{participants}}')->select('*');
	                if($page == 0 && $limit == 0)
	                  {
	                $data=$command->queryAll();
	                  }
	                  else
	                  {
	                  $data = $command->limit($limit,$start)->queryAll();
	                    }
	                return $data;
	          }
	          else if(is_numeric($condition[0]))
	          {
	          		$command = Yii::app()->db->createCommand()->select('{{participant_attribute}}.*,{{participants}}.*')->from('{{participant_attribute}}')->join('{{participants}}', '{{participant_attribute}}.participant_id = {{participants}}.participant_id')->where('{{participant_attribute}}.attribute_id = '.$condition[0])->where(array('not in','{{participant_attribute}}.value',$condition[2]));
	                if($page == 0 && $limit == 0)
	                  {
	                    $data = $command->queryAll();
	                  }
	                  else
	                  {
	                    $data = $command->limit($limit,$start)->queryAll();
	                  }
	                return $data;
	          }
	          else
	          {
	          	$command = Yii::app()->db->createCommand()->where(array('not in',$condition[0],$condition[2]))->from('{{participants}}')->select('*');
	            if($page == 0 && $limit == 0)
	                  {
	            $data=$command->queryAll();
	                  }
	                  else
	                  {
	                  $data = $command->limit($limit,$start)->queryAll();
	                    }
	            return $data;
	          }
	        }
	        else if($condition[1]=='notcontains')
	        {
	        	$condition[2] = '%'.$condition[2].'%';
	            if($condition[0]=='surveys')
	          {
	              $resultarray = array();
	              $command = Yii::app()->db->createCommand()->order('lastname','asc')->from('{{participants}}')->select('*');
	              if($page == 0 && $limit == 0)
	                  {
	              $data = $command->queryAll();
	                  }
	                  else
	                  {
	                  $data = $command->limit($limit,$start);
	                    }
	              foreach($data as $key=>$value)
	              {
	                  $count = count(Yii::app()->db->createCommand()->where('participant_id = "',$value['participant_id'].'"')->from('{{survey_links}}')->select('*')->queryAll());
	                  if($count != $condition[2])
	                  {
	                     array_push($resultarray,$value);
	                  }
	              }
	              return $resultarray;
	          }
	          else if($condition[0]=='owner_name')
	          {
	                $userid = Yii::app()->db->createCommand()->select('uid')->where(array('not like','full_name',$condition[2]))->from('{{users}}')->queryAll();
	                $uid = $userid[0];
	                $command = Yii::app()->db->createCommand()->where('owner_uid = '.$uid['uid'])->from('{{participants}}')->select('*');
	                if($page == 0 && $limit == 0)
	                  {
	                $data=$command->queryAll();
	                  }
	                  else
	                  {
	                  $data = $command->limit($limit,$start)->queryAll();
	                    }
	                return $data;
	          }
	          else if(is_numeric($condition[0]))
	          {
	                $command = Yii::app()->db->createCommand()->select('{{participant_attribute}}.*,{{participants}}.*')->from('{{participant_attribute}}')->join('{{participants}}', '{{participant_attribute}}.participant_id = {{participants}}.participant_id')->where('{{participant_attribute}}.attribute_id = '.$condition[0])->where(array('not like','participant_attribute.value',$condition[2]));
	                if($page == 0 && $limit == 0)
	                  {
	                    $data = $command->queryAll();
	                  }
	                  else
	                  {
	                  $data = $command->limit($limit,$start)->queryAll();
	                    }
	                return $data;
	          }
	          else
	          {
	            $command = Yii::app()->db->createCommand()->where(array('not like',$condition[0],$condition[2]))->from('{{participants}}')->select('*');
	            if($page == 0 && $limit == 0)
	                  {
	            $data=$command->queryAll();
	                  }
	                  else
	                  {
	                  $data = $command->limit($limit,$start)->queryAll();
	                    }
	            return $data;
	          }
	        }
	        else if($condition[1]=='greaterthan')
	        {
	          if($condition[0]=='surveys')
	          {
	              $resultarray = array();
	              if($page == 0 && $limit == 0)
	                  {
	              $data = $this->db->get('participants');
	                  }
	                  else
	                  {
	                  $data = $this->db->get('participants',$limit,$start);
	                    }
	              foreach($data->result_array() as $key=>$value)
	              {
	                  $this->db->where('participant_id',$value['participant_id']);
	                  $this->db->from('survey_links');
	                  $count=$this->db->count_all_results();
	                  if($count > $condition[2])
	                  {
	                     array_push($resultarray,$value);
	                  }
	              }
	              return $resultarray;
	          }
	          else if($condition[0]=='owner_name')
	          {
	                $userid = Yii::app()->db->createCommand()->select('uid')->where('full_name = "'.$condition[2].'"')->from('{{users}}')->queryAll();
	                $uid = $userid[0];
	                $command = Yii::app()->db->createCommand()->where('owner_uid = '.$uid['uid'])->order("lastname", "asc")->select('*') ->from('{{participants}}');
	                if($page == 0 && $limit == 0)
	                  {
	                $data=$command->queryAll();
	                  }
	                  else
	                  {
	                  $data = $command->limit($limit,$start)->queryAll();
	                    }
	                return $data;
	          }
	          else if(is_numeric($condition[0]))
	          {
	          		$command = Yii::app()->db->createCommand()->select('{{participant_attribute}}.*,{{participants}}.*')->from('{{participant_attribute}}')->join('{{participants}}', '{{participant_attribute}}.participant_id = {{participants}}.participant_id')->where('{{participant_attribute}}.attribute_id = '.$condition[0].' AND participant_attribute.value > "'.$condition[2].'"');
	                if($page == 0 && $limit == 0)
	                  {
	                    $data = $command->queryAll();
	                  }
	                  else
	                  {
	                  $data = $command->limit($limit,$start)->queryAll();
	                    }
	                return $data;
	          }
	          else
	          {
	            $command = Yii::app()->db->createCommand()->where($condition[0].' > "'.$condition[2].'"')->order("lastname", "asc")->select('*')->from('{{participants}}');
	            if($page == 0 && $limit == 0)
	                  {
	            $data=$command->queryAll();
	                  }
	                  else
	                  {
	                  $data = $command->limit($limit,$start)->queryAll();
	                    }
	            return $data;
	          }
	        }
	       else if($condition[1]=='lessthan')
	        {
	          if($condition[0]=='surveys')
	          {
	              $resultarray = array();

	              if($page == 0 && $limit == 0)
	                  {
	              $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
	                  }
	                  else
	                  {
	                  $data = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->limit($limit,$start)->queryAll();
	                    }
	              foreach($data as $key=>$value)
	              {
	                  $count = count(Yii::app()->db->createCommand()->where('participant_id = "'.$value['participant_id'].'"')->from('{{survey_links}}')->select('*')->queryAll());
	                  if($count < $condition[2])
	                  {
	                     array_push($resultarray,$value);
	                  }
	              }
	              return $resultarray;
	          }
	          else if($condition[0]=='owner_name')
	          {

	                $userid = Yii::app()->db->createCommand()->select('uid')->where('full_name = "'.$condition[2].'"')->from('{{users}}')->queryAll();
	                $uid = $userid[0];
	                $command = Yii::app()->db->createCommand()->where('owner_uid < '.$uid['uid'])->select('*')->from('{{participants}}');

	                if($page == 0 && $limit == 0)
	                  {
	                $data=$command->queryAll();
	                  }
	                  else
	                  {
	                  $data = $command->limit($limit,$start);
	                    }
	                return $data;
	          }
	          else if(is_numeric($condition[0]))
	          {
	                $command = Yii::app()->db->createCommand()->select('{{participant_attribute}}.*,{{participants}}.*')->from('{{participant_attribute}}')->join('{{participants}}', '{{participant_attribute}}.participant_id = {{participants}}.participant_id')->where('{{participant_attribute}}.attribute_id = '.$condition[0])->where(array('not like','participant_attribute.value < "'.$condition[2].'"'));
	              if($page == 0 && $limit == 0)
	              {
	                $data = $command->queryAll();
	              }
	              else
	              {
	                $this->db->limit($limit,$start);
	                $data = $command->limit($limit,$start);
	              }
	                return $data;
	          }
	          else
	          {
	            $command = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->where($condition[0].' < "'.$condition[2].'"');
	            if($page == 0 && $limit == 0)
	            {
	            $data= $command->queryAll();
	            }
	            else
	            {
	                $data = $command->limit($limit,$start)->queryAll();
	            }
	            return $data;
	          }

	        }
	}

	function getParticipantsSearchMultiple($condition,$page,$limit)
	{
	   $i=0;
	   $j=1;
	   $tobedonelater =array();
	   $start = $limit*$page - $limit;
	   $command = new CDbCriteria;
	   $command->condition = '';
	   $con= count($condition);
	   while($i < $con){
	   if($i<3){
	        $i+=3;
	        if($condition[1]=='equal')
	        {
	            if(is_numeric($condition[0]))
	            {
	                $newsub = $j;
	                $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition[0].' AND {{participant_attribute}}.value = "'.$condition[2].'"')->queryAll();
	                $command->addInCondition('participant_id',$arr);
	                $j++;
	            }
	            else
	            {
	            	$command->addCondition($condition[0].' = "'.$condition[2].'"');
	            }
	         }
	         else if($condition[1]=='contains')
	         {
	            if(is_numeric($condition[0]))
	            {
	                $newsub = $j;
	                $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition[0].' AND {{participant_attribute}}.value LIKE "%'.$condition[2].'%"')->queryAll();



	                $command->addInCondition('participant_id',$arr);
	                $j++;
	            }
	            else
	            {
	                $command->addCondition($condition[0].' LIKE "%'.$condition[2].'%"');
	            }
	         }
	         else if($condition[1]=='notequal')
	         {
	            if(is_numeric($condition[0]))
	            {
	                $newsub = $j;
	                $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition[0].' AND {{participant_attribute}}.value NOT IN ("'.$condition[2].'")')->queryAll();


	                $command->addInCondition('participant_id',$arr);
	                $j++;
	            }
	            else
	            {
	               $command->addCondition($condition[0].' NOT IN ("'.$condition[2].'")');

	            }
	         }
	         else if($condition[1]=='notcontains')
	         {
	            if(is_numeric($condition[0]))
	            {
	                $newsub = $j;
	                $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition[0].' AND {{participant_attribute}}.value NOT LIKE "%'.$condition[2].'%"')->queryAll();


	                $command->addInCondition('participant_id',$arr);
	                $j++;
	            }
	            else
	            {
	               $command->addCondition($condition[0].' NOT LIKE "%'.$condition[2].'%"');

	            }
	         }
	         else if($condition[1]=='greaterthan')
	         {
	            if(is_numeric($condition[0]))
	            {
	                $newsub = $j;
	                $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition[0].' AND {{participant_attribute}}.value > "'.$condition[2].'"')->queryAll();


	                $command->addInCondition('participant_id',$arr);
	                $j++;
	            }
	            else
	            {
	               $command->addCondition($condition[0].' > "'.$condition[2].'"');

	            }
	         }
	         else if($condition[1]=='lessthan')
	         {
	            if(is_numeric($condition[0]))
	            {
	                $newsub = $j;
	                $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition[0].' AND {{participant_attribute}}.value < "'.$condition[2].'"')->queryAll();


	                $command->addInCondition('participant_id',$arr);
	                $j++;
	            }
	            else
	            {
	               $command->addCondition($condition[0].' < "'.$condition[2].'"');

	            }
	         }
	        }

	        else if($condition[$i]!='')
	        {
	           if($condition[$i+2]=='equal')
	           {
	               if(is_numeric($condition[$i+1]))
	                {
	                    if($condition[$i]=='and')
	                    {

	                        $newsub = $j;
	                        $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition[$i+1].' AND {{participant_attribute}}.value = "'.$condition[$i+3].'"')->queryAll();


	                        $command->addInCondition('participant_id',$arr);
	                        $j++;
	                    }
	                    else
	                    {
	                        $tobedonelater[$condition[$i+1]][0] = $condition[$i+2];
	                        $tobedonelater[$condition[$i+1]][1] = $condition[$i+3];
	                    }
	               }
	              else
	                {
	                    if($condition[$i]=='and')
	                    {
	                    	$command->addCondition($condition[$i+1].' = "'.$condition[$i+3].'"');
	                    }
	                    else
	                    {
	                        $command->addCondition($condition[$i+1].' = "'.$condition[$i+3].'"','OR');
	                    }
	                }
	            }
	            else if($condition[$i+2]=='contains')
	            {
	              if(is_numeric($condition[$i+1]))
	                {
	                    if($condition[$i]=='and')
	                    {
	                        $newsub = $j;
	                        $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition[$i+1].' AND {{participant_attribute}}.value LIKE "%'.$condition[$i+3].'%"')->queryAll();


	                        $command->addInCondition('participant_id',$arr);
	                        $j++;
	                    }
	                    else
	                    {
	                        $tobedonelater[$condition[$i+1]][0] = $condition[$i+2];
	                        $tobedonelater[$condition[$i+1]][1] = $condition[$i+3];
	                     }
	               }
	              else
	                {
	                    if($condition[$i]=='and')
	                    {

	                        $command->addCondition($condition[$i+1].' LIKE "%'.$condition[$i+3].'%"');
	                    }
	                    else
	                    {
	                        $command->addCondition($condition[$i+1].' LIKE "%'.$condition[$i+3].'%"','OR');
	                    }
	                }
	            }
	            else if($condition[$i+2]=='notequal')
	            {
	              if(is_numeric($condition[$i+1]))
	                {
	                    if($condition[$i]=='and')
	                    {
	                        $newsub = $j;
	                        $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition[$i+1].' AND {{participant_attribute}}.value NOT IN ("'.$condition[$i+3].'")')->queryAll();


	                        $command->addInCondition('participant_id',$arr);
	                        $j++;
	                    }
	                    else
	                    {
	                        $tobedonelater[$condition[$i+1]][0] = $condition[$i+2];
	                        $tobedonelater[$condition[$i+1]][1] = $condition[$i+3];
	                    }
	               }
	              else
	                {
	                    if($condition[$i]=='and')
	                    {
	                    	$command->addCondition($condition[$i+1].' NOT IN ("'.$condition[$i+3].'")');
	                    }
	                    else
	                    {
	                        $command->addCondition($condition[$i+1].' NOT IN ("'.$condition[$i+3].'")','OR');
	                    }
	                }
	            }
	           else if($condition[$i+2]=='notcontains')
	            {
	              if(is_numeric($condition[$i+1]))
	                {
	                    if($condition[$i]=='and')
	                    {
	                        $newsub = $j;
	                        $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition[$i+1].' AND {{participant_attribute}}.value NOT LIKE "%'.$condition[$i+3].'%"')->queryAll();


	                        $command->addInCondition('participant_id',$arr);
	                        $j++;
	                    }
	                    else
	                    {
	                        $tobedonelater[$condition[$i+1]][0] = $condition[$i+2];
	                        $tobedonelater[$condition[$i+1]][1] = $condition[$i+3];
	                    }
	               }
	              else
	                {
	                    if($condition[$i]=='and')
	                    {
	                        $command->addCondition($condition[$i+1].' NOT LIKE "%'.$condition[$i+3].'%"');
	                    }
	                    else
	                    {
	                        $command->addCondition($condition[$i+1].' NOT LIKE "%'.$condition[$i+3].'%"','OR');
	                    }
	                }
	            }
	            else if($condition[$i+2]=='greaterthan')
	            {
	              if(is_numeric($condition[$i+1]))
	                {
	                    if($condition[$i]=='and')
	                    {
	                        $newsub = $j;
	                        $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition[$i+1].' AND {{participant_attribute}}.value > "'.$condition[$i+3].'"')->queryAll();


	                        $command->addInCondition('participant_id',$arr);
	                        $j++;
	                    }
	                    else
	                    {
	                        $tobedonelater[$condition[$i+1]][0] = $condition[$i+2];
	                        $tobedonelater[$condition[$i+1]][1] = $condition[$i+3];
	                     }
	               }
	              else
	                {
	                    if($condition[$i]=='and')
	                    {
	                        $command->addCondition($condition[$i+1].' > "'.$condition[$i+3].'"');
	                    }
	                    else
	                    {
	                        $command->addCondition($condition[$i+1].' > "'.$condition[$i+3].'"','OR');
	                    }
	                }
	            }
	            else if($condition[$i+2]=='lessthan')
	            {
	              if(is_numeric($condition[$i+1]))
	                {
	                    if($condition[$i]=='and')
	                    {
	                        $newsub = $j;
	                        $arr = Yii::app()->db->createCommand('SELECT {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = '.$condition[$i+1].' AND {{participant_attribute}}.value < "'.$condition[$i+3].'"')->queryAll();


	                        $command->addInCondition('participant_id',$arr);
	                        $j++;
	                    }
	                    else
	                    {
	                         $tobedonelater[$condition[$i+1]][0] = $condition[$i+2];
	                         $tobedonelater[$condition[$i+1]][1] = $condition[$i+3];
	                    }
	               }
	              else
	                {
	                    if($condition[$i]=='and')
	                    {
	                        $command->addCondition($condition[$i+1].' < "'.$condition[$i+3].'"');
	                    }
	                    else
	                    {
	                        $command->addCondition($condition[$i+1].' < "'.$condition[$i+3].'"','OR');
	                    }
	                }
	            }
	            $i=$i+4;
	        }
	        else{$i=$i+4;}
	    }
	    if($page == 0 && $limit == 0)
	    {
	        $arr = Participants::model()->findAll($command);
	        $data = array();
			foreach($arr as $t)
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
			foreach($arr as $t)
			{
    			$data[$t->participant_id] = $t->attributes;
			}
	    }

	    $otherdata = $data;
	    if(!empty($tobedonelater))
	    {
	    $command = new CDBCriteria;
	    $command->select = 'participant_id';
	    $command->distinct = TRUE;
	    $command->condition = '';
	    foreach($tobedonelater as $key=>$value)
	    {
	        if($value[0] == 'equal')
	        {
	            $command->addCondition('attribute_id = '.$key,'OR');
	            $command->addCondition('value = "'.$value[1].'"');
	        }
	        if($value[0] == 'contains')
	        {
	            $command->addCondition('attribute_id = '.$key,'OR');
	            $command->addCondition('value LIKE "%'.$value[1].'%"');
	        }
	        if($value[0] == 'notequal')
	        {
	            $command->addCondition('attribute_id = '.$key,'OR');
	            $command->addCondition('value != "'.$value[1].'"');
	        }
	        if($value[0] == 'greaterthan')
	        {
	            $command->addCondition('attribute_id = '.$key,'OR');
	            $command->addCondition('value > "'.$value[1].'"');
	        }
	        if($value[0] == 'lessthan')
	        {
	            $command->addCondition('attribute_id = '.$key,'OR');
	            $command->addCondition('value < "'.$value[1].'"');
	        }
	    }
	    $participant_id = ParticipantAttributeNames::model()->findAll($command);
	    $command = new CDBCriteria;
	    $command->select = '*';
	    $command->condition = '';
	    foreach($participant_id as $key=>$value)
	    {
	        $command->addCondition('participant_id = "'.$value->participant_id.'"');
	    }
	    if($page == 0 && $limit == 0)
	    {
	    	$arr = Participants::model()->findAll($command);
	        $data = array();
			foreach($arr as $t)
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
			foreach($arr as $t)
			{
    			$data[$t->participant_id] = $t->attributes;
			}
	    }


	    $orddata = $data;
	    $finalanswer = array_merge($otherdata,$orddata);
	    return $finalanswer;
	    }
	    else
	    {
	     return $otherdata;
	    }

	}

	function is_owner($participant_id)
	{
	    $userid=Yii::app()->session['loginID'];
	    $is_owner = Yii::app()->db->createCommand()->select('participant_id')->where('participant_id = "'.$participant_id.'" AND owner_uid = '.$userid)->from('{{participants}}')->queryAll();
	    //$is_owner->num_rows();
	    $is_shared = Yii::app()->db->createCommand()->select('participant_id')->where('participant_id = "'.$participant_id.'" AND share_uid = '.$userid)->from('{{participant_shares}}')->queryAll();
	    if(count($is_shared) || count($is_owner))
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
		return Yii::app()->db->createCommand()->select('{{participants}}.*, {{participant_shares}}.*')->from('{{participants}}')->join('{{participant_shares}}','{{participant_shares}}.participant_id = {{participants}}.participant_id')->where('owner_uid = '.$userid)->queryAll();
	}

	/*
	 * This funciton is responsible for showing all the participant's shared to the superadmin
	 */
	function getParticipantSharedAll()
	{
		return Yii::app()->db->createCommand()->select('{{participants}}.*,{{participant_shares}}.*')->from('{{participants}}')->join('{{participant_shares}}','{{participant_shares}}.participant_id = {{participants}}.participant_id')->queryAll();
	}

	function copytosurveyatt($surveyid,$mapped,$newcreate,$participantid)
	{
		Yii::app()->loadHelper('common');
	    $duplicate=0;
	    $sucessfull=0;
	    $participantid = explode(",",$participantid);
	    if($participantid[0]=="")
	    {
	        $participantid = array_slice($participantid,1);
	    }
	    $number2add=sanitize_int(count($newcreate));
	    $arr = Yii::app()->db->createCommand()->select('*')->from("{{tokens_$surveyid}}")->queryRow();
	    if(is_array($arr)){
	        $tokenfieldnames = array_keys($arr);
	        $tokenattributefieldnames=array_filter($tokenfieldnames,'filterforattributes');
    	}
    	else {
        	$tokenattributefieldnames = array();
    	}
	    foreach($tokenattributefieldnames as $key=>$value)
	    {
	       if($value[10]=='c')
	        {
	           $attid = substr($value,15);
	           $mapped[$value] = $attid;
	        }
	    }
	    $attributesadded=array();
	    $attributeidadded=array();
	    $fieldcontents="";
	    if(!empty($newcreate))
	    {
	    foreach ($newcreate as $key=>$value)
	    {
	        $fields['attribute_cpdb_'.$value]=array('type' => 'VARCHAR','constraint' => '255');
	        $attname = Yii::app()->db->createCommand()->select('{{participant_attribute_names_lang}}.attribute_name')->from('{{participant_attribute_names}}')->join('{{participant_attribute_names_lang}}', '{{participant_attribute_names}}.attribute_id = {{participant_attribute_names_lang}}.attribute_id')->where('{{participant_attribute_names}}.attribute_id = '.$value.' AND lang = "'.Yii::app()->session['adminlang'].'"');
	        $attributename = $attname->queryRow();
	        $tokenattributefieldnames[]='attribute_cpdb_'.$value;
	        $fieldcontents.= 'attribute_cpdb_'.$value.'='.$attributename['attribute_name']."\n";
	        array_push($attributeidadded,'attribute_cpdb_'.$value);
	        array_push($attributesadded,$value);
	    }
	    $previousatt = Yii::app()->db->createCommand()->select('attributedescriptions')->where("sid = ".$surveyid)->from('{{surveys}}');
	    $previouseattribute = $previousatt->queryRow();
	    Yii::app()->db->createCommand()->update('{{surveys}}',array("attributedescriptions"=>$previouseattribute['attributedescriptions'].$fieldcontents),'sid = '.$surveyid);// load description in the surveys table
	    foreach($fields as $key=>$value){
	    	Yii::app()->db->createCommand("ALTER TABLE {{tokens_$surveyid}} ADD COLUMN $key ".$value['type'].'('.$value['constraint'].')')->query(); // add columns in token's table
	    }
	    }
	    //Function for pushing associative array
	    foreach($participantid as $key=>$participant)
	    {
	        $writearray = array();
	        $participantdata = Yii::app()->db->createCommand()->select('firstname,lastname,email,language,blacklisted')->where('participant_id = "'.$participant.'"')->from('{{participants}}');
	        $tobeinserted = $participantdata->queryRow();
	        $query = Yii::app()->db->createCommand()->select('*')->from('{{tokens_'.$surveyid.'}}')->where('firstname = "'.$tobeinserted['firstname'].'" AND lastname = "'.$tobeinserted['lastname'].'" AND email = "'.$tobeinserted['email'].'"')->queryAll();
	        if (count($query) > 0)
	        {
	            $duplicate++;
	        }
	        else
	        {
	            $writearray = array('participant_id'=>$participant,'firstname'=>$tobeinserted['firstname'],'lastname' => $tobeinserted['lastname'],'email' => $tobeinserted['email'],'emailstatus'=>'OK','language' =>$tobeinserted['language']);
	            Yii::app()->db->createCommand()->insert('{{tokens_'.$surveyid.'}}', $writearray);
	            $insertedtokenid = Yii::app()->db->getLastInsertID();
	            $time = time();
	            $data = array(
	            'participant_id' => $participant,
	            'token_id' => $insertedtokenid ,
	            'survey_id' => $surveyid,
	            'date_created' =>  date(DATE_W3C,$time));
	            Yii::app()->db->createCommand()->insert('{{survey_links}}', $data);
	            if(!empty($newcreate))
	            {
	            $numberofattributes = count($attributesadded);
	            for($a=0;$a<$numberofattributes;$a++)
	            {
	            	$val = Yii::app()->db->createCommand()->select('value')->where('participant_id = "'.$participant.'" AND attribute_id = '.$attributesadded[$a])->from('{{participant_attribute}}');
	                if(count($val->queryAll())>0)
	                {
	                    $value=$val->queryRow();
	                    $data=array($attributeidadded[$a]=>$value['value']);
	                    if(!empty($value))
	                    {
	                       Yii::app()->db->createCommand()->update("{{tokens_$surveyid}}", $data, 'participant_id = "'.$participant.'"');
	                    }
	                }
	            }
	            }
	            if(!empty($mapped))
	            {
	                foreach($mapped as $key=>$value)
	                {
	                	$val = Yii::app()->db->createCommand()->select('value')->where('participant_id = "'.$participant.'" AND attribute_id = '.$value)->from('{{participant_attribute}}');
	                    $value=$val->queryRow();
	                    if(isset($value['value']))
	                    {
	                        $data=array($key=>$value['value']);
	                        Yii::app()->db->createCommand()->update("{{tokens_$surveyid}}", $data,'participant_id = "'.$participant.'"');
	                    }

	                }
	            }
	            $sucessfull++;
	        }
	    }
	    $returndata = array('success'=>$sucessfull,'duplicate'=>$duplicate);
	    return $returndata;
	}

	/*
	* This function is responsible for checking for any exsisting record in the token table and if not copy the participant to it
	*/
	function copyToCentral($surveyid,$newarr,$mapped)
	{
	    $tokenid = Yii::app()->session['participantid'];
	    $duplicate=0;
	    $sucessfull=0;
	    $writearray = array();
	    $attid = array();
	    $pid="";
	    $arr = Yii::app()->db->createCommand()->select('*')->from("{{tokens_$surveyid}}")->queryRow();
	    if(is_array($arr)){
	        $tokenfieldnames = array_keys($arr);
	        $tokenattributefieldnames=array_filter($tokenfieldnames,'filterforattributes');
    	}
    	else {
        	$tokenattributefieldnames = array();
    	}
	    foreach($tokenattributefieldnames as $key=>$value) //mapping the automatically mapped
	    {
	       if($value[10]=='c')
	        {
	           $attid = substr($value,15);
	           $mapped[$attid] = $value;
	        }
	    }
	    if(!empty($newarr))
	    {
	       foreach ($newarr as $key=>$value) //creating new central attribute
	            {
	                $insertnames = array('attribute_type' => 'TB','visible' => 'Y');
	                Yii::app()->db->createCommand()->insert('{{participant_attribute_names}}',$insertnames);
	                $attid[$key] = Yii::app()->db->getLastInsertID();
	                $insertnameslang = array('attribute_id' => Yii::app()->db->getLastInsertID(),
	                                         'attribute_name'=>urldecode($key),
	                                         'lang' => Yii::app()->session['adminlang']);
	                Yii::app()->db->createCommand()->insert('{{participant_attribute_names_lang}}',$insertnameslang);
	            }
	    }
	    foreach($tokenid as $key=>$tid)
	    {
	        if(is_numeric($tid) && $tid!="")
	        {
	            $tobeinserted = Yii::app()->db->createCommand()->select('participant_id,firstname,lastname,email,language')->where('tid = '.$tid)->from('{{tokens_'.$surveyid.'}}')->queryRow();
	            $query = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->where('firstname = "'.$tobeinserted['firstname'].'" AND lastname = "'.$tobeinserted['lastname'].'" AND email = "'.$tobeinserted['email'].'"')->queryAll();
	            if (count($query) > 0)
	            {
	                $duplicate++;
	            }
	            else
	            {
	                if(empty($tobeinserted['blacklisted']))
	                {
	                    $black = 'N';
	                }
	                else
	                {
	                    $black = $tobeinserted['blacklisted'];
	                }
	                if(!empty($tobeinserted['participant_id']))
	                {
	                    $writearray = array('participant_id'=>$tobeinserted['participant_id'],'firstname'=>$tobeinserted['firstname'],'lastname' => $tobeinserted['lastname'],'email' => $tobeinserted['email'],'language' =>$tobeinserted['language'],'blacklisted'=>$black,'owner_uid' => Yii::app()->session['loginID']);
	                }
	                else
	                {
	                    $writearray = array('participant_id'=>$this->gen_uuid(),'firstname'=>$tobeinserted['firstname'],'lastname' => $tobeinserted['lastname'],'email' => $tobeinserted['email'],'language' =>$tobeinserted['language'],'blacklisted'=>$black,'owner_uid' => Yii::app()->session['loginID']);
	                }
	                $pid = $writearray['participant_id'];
	                Yii::app()->db->createCommand()->insert('{{participants}}',$writearray);
	                if(!empty($newarr))
	                {
	                foreach ($newarr as $key=>$value)
	                    {
	                        $val = Yii::app()->db->createCommand()->select($value)->where('tid = '.$tid)->from('{{tokens_'.$surveyid.'}}');
	                        $value2 = $val->queryRow();
                        	$data = array(  'participant_id' => $pid,
                            	            'value' => $value2["$value"],
                                	        'attribute_id' =>  $attid[$key]);

	                        if(!empty($data['value']))
	                        {
	                        Yii::app()->db->createCommand()->insert('{{participant_attribute}}', $data);
	                        }
	                    }
	                }
	                if(!empty($mapped))
	                {
	                foreach($mapped as $cpdbatt => $tatt)
	                {
	                     $val = Yii::app()->db->createCommand()->select($tatt)->where('tid = '.$tid)->from('{{tokens_'.$surveyid.'}}');
	                     $value = $val->queryRow();
	                     $data = array( 'participant_id' => $pid,
	                                    'value' => $value["$tatt"],
	                                    'attribute_id' => $cpdbatt );
	                     if(!empty($data['value']))
	                     {
	                        Yii::app()->db->createCommand()->insert('{{participant_attribute}}', $data);
	                     }
	                }
	                }
	                $sucessfull++;
	            }
	            }
	        }
	        if(!empty($newarr))
	        {
	            foreach ($newarr as $key=>$value)
	            {
	                $newname = 'attribute_cpdb_'.$attid[$key];
	                $fields = array($value => array('name' => $newname,'type' => 'TEXT'));
	                Yii::app()->db->createCommand()->renameColumn('{{tokens_'.$surveyid.'}}',$value,$newname);
	                Yii::app()->db->createCommand()->alterColumn('{{tokens_'.$surveyid.'}}',$newname,'TEXT');
	                $previousatt = Yii::app()->db->createCommand()->select('attributedescriptions')->where("sid = ".$surveyid)->from('{{surveys}}');
	                $previouseattribute = $previousatt->queryRow();
	                $newstring = str_replace($value,$newname,$previouseattribute['attributedescriptions']);
	                Yii::app()->db->createCommand()->update('{{surveys}}',array("attributedescriptions"=>$newstring),'sid = '.$surveyid); // load description in the surveys table
	            }
	        }
	        if(!empty($mapped))
	        {
	            foreach($mapped as $cpdbatt => $tatt)
	            {
	                if($tatt[10]!='c')
	                {
	                    $newname = 'attribute_cpdb_'.$cpdbatt;
	                    $fields = array($tatt => array('name' => $newname,'type' => 'TEXT'));
	                	Yii::app()->db->createCommand()->renameColumn('{{tokens_'.$surveyid.'}}',$tatt,$newname);
	                	Yii::app()->db->createCommand()->alterColumn('{{tokens_'.$surveyid.'}}',$newname,'TEXT');
	                    $previousatt = Yii::app()->db->createCommand()->select('attributedescriptions')->where("sid = ".$surveyid)->from('{{surveys}}');
	                    $previouseattribute = $previousatt->queryRow();
	                    $newstring = str_replace($tatt,$newname,$previouseattribute['attributedescriptions']);
	                    Yii::app()->db->createCommand()->update('{{surveys}}',array("attributedescriptions"=>$newstring),'sid = '.$surveyid); // load description in the surveys table
	                }
	            }
	        }
	        $returndata = array('success'=>$sucessfull,'duplicate'=>$duplicate);
	        return $returndata;
	}
}
