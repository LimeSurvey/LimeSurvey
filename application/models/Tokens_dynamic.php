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

class Tokens_dynamic extends CActiveRecord
{
	protected static $sid = 0;

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
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
	 * @param int $surveyid
	 * @return CActiveRecord
	 */
	public static function model($sid = null)
	{
        if (!is_null($sid))
            self::sid($sid);

		return parent::model(__CLASS__);
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
	 * Returnvs a summary of this table
	 *
	 * @access public
	 * @return array
	 */
	public function summary()
	{
		$sid = self::$sid;

		$tksq = "SELECT count(tid) FROM {{tokens_$sid}}";
		$tksr = Yii::app()->db->createCommand($tksq)->query();
		$tkr = $tksr->read();
		$tkcount = $tkr["count(tid)"];
		$data['tkcount']=$tkcount;

		$tksq = "SELECT count(*) FROM {{tokens_$sid}} WHERE token IS NULL OR token=''";
		$tksr = Yii::app()->db->createCommand($tksq)->query();
		$tkr = $tksr->read();
		$data['query1'] = $tkr["count(*)"]." / $tkcount";

		$tksq = "SELECT count(*) FROM {{tokens_$sid}} WHERE (sent!='N' and sent<>'')";
		$tksr = Yii::app()->db->createCommand($tksq)->query();
		$tkr = $tksr->read();
		$data['query2'] = $tkr["count(*)"]." / $tkcount";

		$tksq = "SELECT count(*) FROM {{tokens_$sid}} WHERE emailstatus = 'optOut'";
		$tksr = Yii::app()->db->createCommand($tksq)->query();
		$tkr = $tksr->read();
		$data['query3'] = $tkr["count(*)"]." / $tkcount";

		$tksq = "SELECT count(*) FROM {{tokens_$sid}} WHERE (completed!='N' and completed<>'')";
		$tksr = Yii::app()->db->createCommand($tksq)->query();
		$tkr = $tksr->read();
		$data['query4'] = $tkr["count(*)"]." / $tkcount";

		return $data;
	}

    public function findUninvited($aTokenIds = false, $iMaxEmails = 0, $bEmail = true, $SQLemailstatuscondition = '', $SQLremindercountcondition = '', $SQLreminderdelaycondition = '')
    {
        $emquery = "SELECT * FROM {{tokens_" . self::$sid . "}} WHERE ((completed ='N') or (completed='')) AND token <> '' AND email <> ''";

        if ($bEmail) { $emquery .= " AND ((sent = 'N') or (sent = ''))"; } else { $emquery .= " AND sent <> 'N' AND sent <> ''"; }
        if ($SQLemailstatuscondition) {$emquery .= " $SQLemailstatuscondition";}
        if ($SQLremindercountcondition) {$emquery .= " $SQLremindercountcondition";}
        if ($SQLreminderdelaycondition) {$emquery .= " $SQLreminderdelaycondition";}
        if ($aTokenIds) {$emquery .= " AND tid IN ('".implode("', '", $aTokenIds)."')";}
        if ($iMaxEmails) {$emquery .= " LIMIT $iMaxEmails"; }
        $emquery .= " ORDER BY tid";

        return Yii::app()->db->createCommand($emquery)->queryAll();
    }

    function insertToken($iSurveyID, $data)
    {
		self::sid($iSurveyID);
		return Yii::app()->db->createCommand()->insert(self::tableName(), $data);
    }
    function updateToken($tid,$newtoken)
    {
        return Yii::app()->db->createCommand('UPDATE :tablename SET token= :newtoken WHERE tid=:tid')->bindParam(":newtoken", $newtoken, PDO::PARAM_STR)->bindParam(":tid", $tid, PDO::PARAM_INT)->bindParam(":tablename", $this->tableName(), PDO::PARAM_STR)->execute();
    }
    function selectEmptyTokens($iSurveyID)
    {
        return Yii::app()->db->createCommand("SELECT tid FROM {{tokens_{$iSurveyID}}} WHERE token IS NULL OR token=''")->queryAll();
    }
    function createTokens($iSurveyID)
    {
        //get token length from survey settings
        $tlrow = Survey::model()->findByAttributes(array("sid"=>$iSurveyID));
        $iTokenLength = $tlrow->tokenlength;

        //if tokenlength is not set or there are other problems use the default value (15)
        if(empty($iTokenLength))
        {
            $iTokenLength = 15;
        }

		$ntresult = $this->findAll();

        // select all existing tokens
        foreach ($ntresult as $tkrow)
        {
            $existingtokens[] = $tkrow['token'];
        }

        $newtokencount = 0;
        $tkresult = $this->selectEmptyTokens($iSurveyID);
        foreach ($tkresult as $tkrow)
        {
            $bIsValidToken = false;
            while ($bIsValidToken == false)
            {
                $newtoken = randomChars($iTokenLength);
                if (!in_array($newtoken, $existingtokens)) {
                    $existingtokens[] = $newtoken;
                    $bIsValidToken = true;
                }
            }
            $itresult = $this->updateToken($tkrow['tid'], $newtoken);
            $newtokencount++;
        }
        return $newtokencount;

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

    function getSearch($condition,$page,$limit)
	{
	    $start = $limit*$page - $limit;
	    if($condition[1]=='equal')
        {
            $command = Yii::app()->db->createCommand()->where( ':condition_0 = :condition_2') ->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", $condition[2], PDO::PARAM_INT);
            if($page == 0 && $limit == 0)
              {
            $data=$command->select('*')->from(Tokens_dynamic::tableName())->queryAll();
              }
              else
              {
                  $data = $command->select('*')->from(Tokens_dynamic::tableName())->limit($limit,$start)->queryAll();
              }
            return $data;
        }
        else if($condition[1]=='contains')
        {
            $condition[2] = '%'.$condition[2].'%';
            $command = Yii::app()->db->createCommand()->where(array('like',":condition_0",":condition_2"))->select('*')->from(Tokens_dynamic::tableName())->bindParam(":condition_0", $condition[0], PDO::PARAM_STR)->bindParam(":condition_2", $condition[2], PDO::PARAM_STR);
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
        else if($condition[1]=='notequal')
        {
            $command = Yii::app()->db->createCommand()->where(array('not in',":condition_0",":condition_2"))->from(Tokens_dynamic::tableName())->select('*')->bindParam(":condition_0", $condition[0], PDO::PARAM_STR)->bindParam(":condition_2", $condition[2], PDO::PARAM_STR);
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
        else if($condition[1]=='notcontains')
        {
            $condition[2] = '%'.$condition[2].'%';
            $command = Yii::app()->db->createCommand()->where(array('not like',":condition_0",":condition_2"))->from(Tokens_dynamic::tableName())->select('*')->bindParam(":condition_0", $condition[0], PDO::PARAM_STR)->bindParam(":condition_2", $condition[2], PDO::PARAM_STR);
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
        else if($condition[1]=='greaterthan')
        {
            $command = Yii::app()->db->createCommand()->where(":condition_0 > :condition_2")->order("lastname", "asc")->select('*')->from(Tokens_dynamic::tableName())->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", $condition[2], PDO::PARAM_INT);
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
        else if($condition[1]=='lessthan')
        {
            $command = Yii::app()->db->createCommand()->select('*')->from(Tokens_dynamic::tableName())->where(":condition_0 < :condition_2")->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", $condition[2], PDO::PARAM_INT);
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

	function getSearchMultiple($condition,$page,$limit)
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
                    $command->addCondition(':condition_0 = :condition_2')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", $condition[2], PDO::PARAM_INT);
                }
                else if($condition[1]=='contains')
                {
                    $command->addCondition(':condition_0 LIKE :condition_2')->bindParam(":condition_0", $condition[0], PDO::PARAM_STR)->bindParam(":condition_2", "%".$condition[2]."%", PDO::PARAM_STR);
                }
                else if($condition[1]=='notequal')
                {
                    $command->addCondition(':condition_0 NOT IN (:condition_2)')->bindParam(":condition_0", $condition[0], PDO::PARAM_STR)->bindParam(":condition_2", $condition[2], PDO::PARAM_STR);
                }
                else if($condition[1]=='notcontains')
                {
                    $command->addCondition(':condition_0 NOT LIKE :condition_2')->bindParam(":condition_0", $condition[0], PDO::PARAM_STR)->bindParam(":condition_2", "%".$condition[2]."%", PDO::PARAM_STR);
                }
                else if($condition[1]=='greaterthan')
                {
                    $command->addCondition(':condition_0 > :condition_2')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", $condition[2], PDO::PARAM_INT);
                }
                else if($condition[1]=='lessthan')
                {
                    $command->addCondition(':condition_0 < :condition_2')->bindParam(":condition_0", $condition[0], PDO::PARAM_INT)->bindParam(":condition_2", $condition[2], PDO::PARAM_INT);
                }
            }
	        else if($condition[$i]!='')
	        {
	           if($condition[$i+2]=='equal')
	           {
                    if($condition[$i]=='and')
                    {
						$command->addCondition(':condition_0 = :condition_2')->bindParam(":condition_0", $condition[$i+1], PDO::PARAM_INT)->bindParam(":condition_2", $condition[$i+3], PDO::PARAM_INT);
                    }
                    else
                    {
						$command->addCondition(':condition_0 = :condition_2', 'OR')->bindParam(":condition_0", $condition[$i+1], PDO::PARAM_INT)->bindParam(":condition_2", $condition[$i+3], PDO::PARAM_INT);
                    }
	            }
	            else if($condition[$i+2]=='contains')
	            {
                    if($condition[$i]=='and')
                    {
                    	$command->addCondition(':condition_0 LIKE :condition_2')->bindParam(":condition_0", $condition[$i+1], PDO::PARAM_STR)->bindParam(":condition_2", "%".$condition[$i+3]."%", PDO::PARAM_STR);
                    }
                    else
                    {
                    	$command->addCondition(':condition_0 LIKE :condition_2', 'OR')->bindParam(":condition_0", $condition[$i+1], PDO::PARAM_STR)->bindParam(":condition_2", "%".$condition[$i+3]."%", PDO::PARAM_STR);
                    }
	            }
	            else if($condition[$i+2]=='notequal')
	            {
                    if($condition[$i]=='and')
                    {
                    	$command->addCondition(':condition_0 NOT IN (:condition_2)')->bindParam(":condition_0", $condition[$i+1], PDO::PARAM_STR)->bindParam(":condition_2", $condition[$i+3], PDO::PARAM_STR);
                    }
                    else
                    {
                    	$command->addCondition(':condition_0 NOT IN (:condition_2)', 'OR')->bindParam(":condition_0", $condition[$i+1], PDO::PARAM_STR)->bindParam(":condition_2", $condition[$i+3], PDO::PARAM_STR);
                    }
	            }
	           else if($condition[$i+2]=='notcontains')
	            {
                    if($condition[$i]=='and')
                    {
                    	$command->addCondition(':condition_0 NOT LIKE :condition_2')->bindParam(":condition_0", $condition[$i+1], PDO::PARAM_STR)->bindParam(":condition_2", "%".$condition[$i+3]."%", PDO::PARAM_STR);
                    }
                    else
                    {
                    	$command->addCondition(':condition_0 NOT LIKE :condition_2', 'OR')->bindParam(":condition_0", $condition[$i+1], PDO::PARAM_STR)->bindParam(":condition_2", "%".$condition[$i+3]."%", PDO::PARAM_STR);
                    }
	            }
	            else if($condition[$i+2]=='greaterthan')
	            {
                    if($condition[$i]=='and')
                    {
	                    $command->addCondition(':condition_0 > :condition_2')->bindParam(":condition_0", $condition[$i+1], PDO::PARAM_INT)->bindParam(":condition_2", $condition[$i+3], PDO::PARAM_INT);
                    }
                    else
                    {
	                    $command->addCondition(':condition_0 > :condition_2', 'OR')->bindParam(":condition_0", $condition[$i+1], PDO::PARAM_INT)->bindParam(":condition_2", $condition[$i+3], PDO::PARAM_INT);
                    }
	            }
	            else if($condition[$i+2]=='lessthan')
	            {
                    if($condition[$i]=='and')
                    {
	                    $command->addCondition(':condition_0 < :condition_2')->bindParam(":condition_0", $condition[$i+1], PDO::PARAM_INT)->bindParam(":condition_2", $condition[$i+3], PDO::PARAM_INT);
                    }
                    else
                    {
	                    $command->addCondition(':condition_0 < :condition_2', 'OR')->bindParam(":condition_0", $condition[$i+1], PDO::PARAM_INT)->bindParam(":condition_2", $condition[$i+3], PDO::PARAM_INT);
                    }
	            }
	            $i=$i+4;
	        }
	        else{$i=$i+4;}
	    }

        if($page == 0 && $limit == 0)
	    {
	    	$arr = Tokens_dynamic::model()->findAll($command);
	        $data = array();
			foreach($arr as $t)
			{
    			$data[$t->tid] = $t->attributes;
			}
	    }
	    else
	    {
	        $command->limit = $limit;
	        $command->offset = $start;
	        $arr = Tokens_dynamic::model()->findAll($command);
	        $data = array();
			foreach($arr as $t)
			{
    			$data[$t->tid] = $t->attributes;
			}
	    }

	    return $data;
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

    function getEmailStatus($sid,$token)
    {
        $usquery = 'SELECT emailstatus from {{tokens_'.intval($sid).'}} where token=:token';
        return Yii::app()->db->createCommand($usquery)->bindParam(":token", $token, PDO::PARAM_STR)->queryRow();
    }

    function updateEmailStatus($sid,$token,$status)
    {
        return Yii::app()->db->createCommand()->update('{{tokens_'.intval($sid).'}}',array('emailstatus' => $status),'token = :token',array(':token' => $token ));
    }
}
?>
