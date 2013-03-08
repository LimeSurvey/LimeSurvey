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

class Tokens_dynamic extends LSActiveRecord
{
	protected static $sid = 0;

    public $emailstatus='OK'; // Default value for email status

    /**
     * Returns the static model of Settings table
     *
     * @static
     * @access public
     * @param int $surveyid
     * @return Tokens_dynamic
     */
    public static function model($sid = NULL)
    {
        $refresh = false;
        if (!is_null($sid)) {
            self::sid($sid);
            $refresh = true;
        }
        
        $model = parent::model(__CLASS__);
        
        //We need to refresh if we changed sid
        if ($refresh === true) $model->refreshMetaData();
        return $model;
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
		return '{{tokens_' . self::$sid . '}}';
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'tid';
	}
	
	
	/**
	* Returns this model's validation rules
	*
	*/
	public function rules()
	{
		return array(
		array('remindercount','numerical', 'integerOnly'=>true,'allowEmpty'=>true), 
		array('usesleft','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
		array('mpid','numerical', 'integerOnly'=>true,'allowEmpty'=>true), 	
		array('blacklisted', 'in','range'=>array('Y','N'), 'allowEmpty'=>true), 
//        array('validfrom','date', 'format'=>array('yyyy-MM-dd', 'yyyy-MM-dd HH:mm', 'yyyy-MM-dd HH:mm:ss',), 'allowEmpty'=>true),   
//        array('validuntil','date', 'format'=>array('yyyy-MM-dd', 'yyyy-MM-dd HH:mm', 'yyyy-MM-dd HH:mm:ss',), 'allowEmpty'=>true),             			 
// Date rules currently don't work properly with MSSQL, deactivating for now
		);  
	}	

    
    /**
     * Returns summary information of this token table
     *
     * @access public
     * @return array
     */
    public function summary()
    {
        $sid = self::$sid;
        if(Yii::app()->db->schema->getTable("{{tokens_$sid}}")){
            $data=Yii::app()->db->createCommand()
                ->select("COUNT(*) as tkcount,
                            SUM(CASE WHEN (token IS NULL OR token='') THEN 1 ELSE 0 END) as tkinvalid,
                            SUM(CASE WHEN (sent!='N' AND sent<>'') THEN 1 ELSE 0 END) as tksent,
                            SUM(CASE WHEN (emailstatus LIKE 'OptOut%') THEN 1 ELSE 0 END) as tkoptout,
                            SUM(CASE WHEN (completed!='N' and completed<>'') THEN 1 ELSE 0 END) as tkcompleted
                            ")
                ->from("{{tokens_$sid}}")
                ->queryRow();
        }
        else
        {
            $data=false;
        }

        return $data;
    }

    /**
    * Checks to make sure that all required columns exist in this tokens table
    * (some older tokens tables dont' get udated properly)
    *
    */
    public function checkColumns() {
        $sid = self::$sid;
        $surveytable='{{tokens_'.$sid.'}}';
        $columncheck=array("tid", "participant_id", "firstname", "lastname", "email", "emailstatus","token","language","blacklisted","sent","remindersent","completed","usesleft","validfrom","validuntil");
        $columns = Yii::app()->db->schema->getTable($surveytable)->getColumnNames();
        $missingcolumns=array_diff($columncheck,$columns);
        if(count($missingcolumns)>0) //Some columns are missing - we need to create them
        {
            Yii::app()->loadHelper('update/updatedb'); //Load the admin helper to allow column creation
            setVarchar(); //Set the appropriate varchar settings according to the database
            $sVarchar=Yii::app()->getConfig('varchar'); //Retrieve the db specific varchar setting
            $columninfo=array('validfrom'=>'datetime',
                              'validuntil'=>'datetime',
                              'blacklisted'=>$sVarchar.'(17) NOT NULL',
                              'participant_id'=>$sVarchar.'(50) NOT NULL',
                              'remindercount'=>"integer DEFAULT '0'",
                              'usesleft'=>'integer NOT NULL default 1'); //Not sure if any other fields would ever turn up here - please add if you can think of any others
            foreach($missingcolumns as $columnname) {
                addColumn($surveytable,$columnname,$columninfo[$columnname]);
            }
        }
    }

    public function findUninvited($aTokenIds = false, $iMaxEmails = 0, $bEmail = true, $SQLemailstatuscondition = '', $SQLremindercountcondition = '', $SQLreminderdelaycondition = '')
    {
		$command = new CDbCriteria;
		$command->condition = '';	
		$command->addCondition("(completed ='N') or (completed='')");
		$command->addCondition("token <> ''");
		$command->addCondition("email <> ''");

		if ($bEmail) { 
			$command->addCondition("(sent = 'N') or (sent = '')");
		} else {
			$command->addCondition("(sent <> 'N') AND (sent <> '')");
		}

		if ($SQLemailstatuscondition)
			$command->addCondition($SQLemailstatuscondition);
			
		if ($SQLremindercountcondition)
			$command->addCondition($SQLremindercountcondition);
			
		if ($SQLreminderdelaycondition)
			$command->addCondition($SQLreminderdelaycondition);
			
		if ($aTokenIds) 	
			$command->addCondition("tid IN ('".implode("', '", $aTokenIds)."')" );
			
		if ($iMaxEmails)
			$command->limit = $iMaxEmails;
			
		$command->order = 'tid';	

		$oResult = Tokens_dynamic::model()->findAll($command);
		return $oResult;
    }

	function insertParticipant($data)
	{
            $token = new self;
            foreach ($data as $k => $v)
                $token->$k = $v;
            try
            {
            	$token->save();
            	return $token->tid;
            }
            catch(Exception $e)
            {
            	return false;
        	}
	}

    function insertToken($iSurveyID, $data)
    {
		self::sid($iSurveyID);
		return Yii::app()->db->createCommand()->insert(self::tableName(), $data);
    }
    function updateToken($tid,$newtoken)
    {
        return Yii::app()->db->createCommand("UPDATE {$this->tableName()} SET token = :newtoken WHERE tid = :tid")
        ->bindParam(":newtoken", $newtoken, PDO::PARAM_STR)
        ->bindParam(":tid", $tid, PDO::PARAM_INT)
        ->execute();
    }

    /**
     * Retrieve an array of records with an empty token, in the result is just the id (tid)
     *
     * @param int $iSurveyID
     * @return array
     */
    function selectEmptyTokens($iSurveyID)
    {
        return Yii::app()->db->createCommand("SELECT tid FROM {{tokens_{$iSurveyID}}} WHERE token IS NULL OR token=''")->queryAll();
    }
    
    public static function countAllAndCompleted($sid)
    {
        $select = array(
            'count(*) AS cntall',
            'sum(CASE '. Yii::app()->db->quoteColumnName('completed') . '
                 WHEN '.Yii::app()->db->quoteValue('N').' THEN 0
                          ELSE 1
                 END) AS cntcompleted',
            );
        $result = Yii::app()->db->createCommand()->select($select)->from('{{tokens_' . $sid . '}}')->queryRow();
        return $result;
    }

   /**
     * Creates and inserts token for a specific token record and returns the token string created
     *
     * @param int $iTokenID
     * @return string  token string
     */
    function createToken($iTokenID)
    {
		//get token length from survey settings
        $tlrow = Survey::model()->findByAttributes(array("sid"=>self::$sid));
        $iTokenLength = $tlrow->tokenlength;
       
		//get all existing tokens
        $criteria = $this->getDbCriteria();
        $criteria->select = 'token';
		$ntresult = $this->findAllAsArray($criteria);   
        foreach ($ntresult as $tkrow)
        {
            $existingtokens[] = $tkrow['token'];
        }
        //create new_token
		$bIsValidToken = false;
		while ($bIsValidToken == false)
		{
			$newtoken = randomChars($iTokenLength);
			if (!in_array($newtoken, $existingtokens)) {
				$existingtokens[] = $newtoken;
				$bIsValidToken = true;
			}
		}
		//update specific token row
        $itresult = $this->updateToken($iTokenID, $newtoken);
		return $newtoken;
	}  

    /**
     * Creates tokens for all token records that have empty token fields and returns the number
     * of tokens created
     *
     * @param int $iSurveyID
     * @return array ( int number of created tokens, int number to be created tokens)
     */
    function createTokens($iSurveyID)
    {
        $tkresult = $this->selectEmptyTokens($iSurveyID);
        //Exit early if there are not empty tokens
        if (count($tkresult)===0) return array(0,0);

        //get token length from survey settings
        $tlrow = Survey::model()->findByAttributes(array("sid"=>$iSurveyID));
        $iTokenLength = $tlrow->tokenlength;

        //if tokenlength is not set or there are other problems use the default value (15)
        if(empty($iTokenLength))
        {
            $iTokenLength = 15;
        }
        //Add some criteria to select only the token field
        $criteria = $this->getDbCriteria();
        $criteria->select = 'token';
        $ntresult = $this->findAllAsArray($criteria);   //Use AsArray to skip active record creation

        // select all existing tokens
        foreach ($ntresult as $tkrow)
        {
            $existingtokens[$tkrow['token']] = true;
        }

        $newtokencount = 0;
        $invalidtokencount=0;
        foreach ($tkresult as $tkrow)
        {
            $bIsValidToken = false;
            while ($bIsValidToken == false && $invalidtokencount<50)
            {
                $newtoken = randomChars($iTokenLength);
                if (!isset($existingtokens[$newtoken]))
                {
                    $existingtokens[$newtoken] = true;
                    $bIsValidToken = true;
                    $invalidtokencount=0;
                }
                else
                {
                    $invalidtokencount ++;
                }
            }
            if($bIsValidToken)
            {
                $itresult = $this->updateToken($tkrow['tid'], $newtoken);
                $newtokencount++;
            }
            else
            {
                break;
            }
        }

        return array($newtokencount,count($tkresult));
    }
    
    
     /**
     * This method is invoked before saving a record (after validation, if any).
     * The default implementation raises the {@link onBeforeSave} event.
     * You may override this method to do any preparation work for record saving.
     * Use {@link isNewRecord} to determine whether the saving is
     * for inserting or updating record.
     * Make sure you call the parent implementation so that the event is raised properly.
     * @return boolean whether the saving should be executed. Defaults to true.
     */
    public function beforeSave()
    {
         // Postgres delivers bytea fields as streams :-o - if this is not done it looks like Postgres saves something unexpected
        if ($this->usesleft>0)
        {
            $this->completed='N'; 
        }
        return parent::beforeSave();
    }

    public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('tid',$this->tid,true);
		$criteria->compare('firstname',$this->firstname,true);
		$criteria->compare('lastname',$this->lastname,true);
		$criteria->compare('email',$this->email,true);
        $criteria->compare('emailstatus',$this->emailstatus,true);
        $criteria->compare('token',$this->token,true);
		$criteria->compare('language',$this->language,true);
        $criteria->compare('sent',$this->sent,true);
        $criteria->compare('sentreminder',$this->sentreminder,true);
        $criteria->compare('remindercount',$this->remindercount,true);
        $criteria->compare('completed',$this->completed,true);
        $criteria->compare('usesleft',$this->usesleft,true);
        $criteria->compare('validfrom',$this->validfrom,true);
        $criteria->compare('validuntil',$this->validuntil,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
    
    /**
     * Get CDbCriteria for a json search string
     * 
     * @param array $condition
     * @return \CDbCriteria
     */
    function getSearchMultipleCondition($condition)
	{
        $i=0;
        $j=1;
        $tobedonelater =array(); 
        $command = new CDbCriteria;
        $command->condition = '';
        $iNumberOfConditions = (count($condition)+1)/4;
        $sConnectingOperator = 'AND';
        $aParams=array();
        while($i < $iNumberOfConditions){
            $sFieldname=$condition[$i*4];
            $sOperator=$condition[($i*4)+1];
            $sValue=$condition[($i*4)+2];
            switch ($sOperator)
            {
                case 'equal': 
                    $command->addCondition($sFieldname.' = :condition_'.$i, $sConnectingOperator);
                    $aParams[':condition_'.$i] = $sValue;
                    break;
                case 'contains': 
                    $command->addCondition($sFieldname.' LIKE :condition_'.$i, $sConnectingOperator);
                    $aParams[':condition_'.$i] = '%'.$sValue.'%';
                    break;
                case 'notequal': 
                    $command->addCondition($sFieldname.' <> :condition_'.$i, $sConnectingOperator);
                    $aParams[':condition_'.$i] = $sValue;
                    break;
                case 'notcontains': 
                    $command->addCondition($sFieldname.' NOT LIKE :condition_'.$i, $sConnectingOperator);
                    $aParams[':condition_'.$i] = '%'.$sValue.'%';
                    break;
                case 'greaterthan': 
                    $command->addCondition($sFieldname.' > :condition_'.$i, $sConnectingOperator);
                    $aParams[':condition_'.$i] = $sValue;
                    break;
                case 'lessthan': 
                    $command->addCondition($sFieldname.' < :condition_'.$i, $sConnectingOperator);
                    $aParams[':condition_'.$i] = $sValue;
                    break;
            }
            if (isset($condition[($i*4)+3]))
            {
                $sConnectingOperator=$condition[($i*4)+3];
            }
            else
            {
                $sConnectingOperator='AND';
            }
            $i++;

        }
        if (count($aParams)>0)
        {
            $command->params = $aParams;
        }
        
        return $command;
	}
    
    function deleteToken($tokenid)
    {
        $dlquery = "DELETE FROM ".Tokens_dynamic::tableName()." WHERE tid=:tokenid";
        return Yii::app()->db->createCommand($dlquery)->bindParam(":tokenid", $tokenid)->query();
    }

    function deleteRecords($iTokenIds)
    {
    	foreach($iTokenIds as &$currentrow)
			$currentrow = Yii::app()->db->quoteValue($currentrow);
        $dlquery = "DELETE FROM ".Tokens_dynamic::tableName()." WHERE tid IN (".implode(", ", $iTokenIds).")";
        return Yii::app()->db->createCommand($dlquery)->query();
    }

    function getEmailStatus($token)
    {
        $command = Yii::app()->db->createCommand()
            ->select('emailstatus')
            ->from('{{tokens_'.intval(self::$sid).'}}')
            ->where('token=:token')
            ->bindParam(':token', $token, PDO::PARAM_STR);

        return $command->queryRow();
    }

    function updateEmailStatus($token,$status)
    {
        return Yii::app()->db->createCommand()->update('{{tokens_'.intval(self::$sid).'}}',array('emailstatus' => $status),'token = :token',array(':token' => $token ));
    }
}
?>
