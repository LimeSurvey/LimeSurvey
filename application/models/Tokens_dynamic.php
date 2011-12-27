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
        return Yii::app()->db->createCommand('UPDATE ' . $this->tableName() . ' SET token=\'' . $newtoken . '\' WHERE tid=' . $tid)->execute();
    }
    function selectEmptyTokens($iSurveyID)
    {
        return Yii::app()->db->createCommand("SELECT tid FROM ".$this->tableName()." WHERE token IS NULL OR token=''")->queryAll();
    }
    function createTokens($iSurveyID)
    {
        //get token length from survey settings
        $tlrow = Survey::model()->getSomeRecords("tokenlength",array("sid"=>$iSurveyID));
        $iTokenLength = $tlrow[0]['tokenlength'];

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
                $newtoken = sRandomChars($iTokenLength);
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
    public function getSomeRecords($fields,$condition=FALSE)
    {
		$criteria = new CDbCriteria;

        if ($condition != FALSE)
        {
		    foreach ($condition as $item => $value)
			{
				$criteria->addCondition($item.'="'.$value.'"');
			}
        }

		$data = $this->findAll($criteria);

        return $data;
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
            $command = Yii::app()->db->createCommand()->where($condition[0].' = "'.$condition[2].'"');
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
            $command = Yii::app()->db->createCommand()->where(array('like',$condition[0],$condition[2]))->select('*')->from(Tokens_dynamic::tableName());
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
            $command = Yii::app()->db->createCommand()->where(array('not in',$condition[0],$condition[2]))->from(Tokens_dynamic::tableName())->select('*');
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
            $command = Yii::app()->db->createCommand()->where(array('not like',$condition[0],$condition[2]))->from(Tokens_dynamic::tableName())->select('*');
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
            $command = Yii::app()->db->createCommand()->where($condition[0].' > "'.$condition[2].'"')->order("lastname", "asc")->select('*')->from(Tokens_dynamic::tableName());
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
            $command = Yii::app()->db->createCommand()->select('*')->from(Tokens_dynamic::tableName())->where($condition[0].' < "'.$condition[2].'"');
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
                    $command->addCondition($condition[0].' = "'.$condition[2].'"');
                }
                else if($condition[1]=='contains')
                {
                    $command->addCondition($condition[0].' LIKE "%'.$condition[2].'%"');
                }
                else if($condition[1]=='notequal')
                {
                    $command->addCondition($condition[0].' NOT IN ("'.$condition[2].'")');
                }
                else if($condition[1]=='notcontains')
                {
                    $command->addCondition($condition[0].' NOT LIKE "%'.$condition[2].'%"');
                }
                else if($condition[1]=='greaterthan')
                {
                    $command->addCondition($condition[0].' > "'.$condition[2].'"');
                }
                else if($condition[1]=='lessthan')
                {
                    $command->addCondition($condition[0].' < "'.$condition[2].'"');
                }
            }
	        else if($condition[$i]!='')
	        {
	           if($condition[$i+2]=='equal')
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
	            else if($condition[$i+2]=='contains')
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
	            else if($condition[$i+2]=='notequal')
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
	           else if($condition[$i+2]=='notcontains')
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
	            else if($condition[$i+2]=='greaterthan')
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
	            else if($condition[$i+2]=='lessthan')
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
        $dlquery = "DELETE FROM ".Tokens_dynamic::tableName()." WHERE tid={$tokenid}";
        return Yii::app()->db->createCommand($dlquery)->query();
    }

    function deleteRecords($iTokenIds)
    {
        $dlquery = "DELETE FROM ".Tokens_dynamic::tableName()." WHERE tid IN (".implode(", ", $iTokenIds).")";
        return Yii::app()->db->createCommand($dlquery)->query();
    }

    function getEmailStatus($sid,$token)
    {
        $usquery = 'SELECT emailstatus from {{tokens_'.$sid.'}} where token="'.$token.'"';
        return Yii::app()->db->createCommand($usquery)->queryRow();
    }

    function updateEmailStatus($sid,$token,$status)
    {
        return Yii::app()->db->createCommand()->update('{{tokens_'.$sid.'}}',array('emailstatus' => $status),'token = :token',array(':token' => $token ));
    }
}
?>
