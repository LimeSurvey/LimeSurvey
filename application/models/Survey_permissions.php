<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');
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
   * $Id: dragooon $
*/

class Survey_permissions extends CActiveRecord
{
	/**
	 * Returns the table's name
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{survey_permissions}}';
	}

	/**
	 * Returns the table's primary key
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return array('sid', 'uid', 'permission');
	}

	/**
	 * Return the static model for this table
	 *
	 * @static
	 * @access public
	 * @return CActiveRecord
	 */
	public static function model()
	{
		return parent::model(__CLASS__);
	}

	/**
	 * Returns the base permissions
	 *
	 * @access public
	 * @static
	 * @return array
	 */
	public static function getBasePermissions()
	{
		$clang = Yii::app()->lang;

		$aPermissions=array(
			'assessments'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Assessments"),'description'=>$clang->gT("Permission to create/view/update/delete assessments rules for a survey"),'img'=>'assessments'),  // Checked
			'quotas'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Quotas"),'description'=>$clang->gT("Permission to create/view/update/delete quota rules for a survey"),'img'=>'quota'), // Checked
			'responses'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Responses"),'description'=>$clang->gT("Permission to create(data entry)/view/update/delete/import/export responses"),'img'=>'browse'),
			'statistics'=>array('create'=>false,'read'=>true,'update'=>false,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Statistics"),'description'=>$clang->gT("Permission to view statistics"),'img'=>'statistics'),    //Checked
			'survey'=>array('create'=>false,'read'=>true,'update'=>false,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey deletion"),'description'=>$clang->gT("Permission to delete a survey"),'img'=>'delete'),   //Checked
			'surveyactivation'=>array('create'=>false,'read'=>false,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey activation"),'description'=>$clang->gT("Permission to activate/deactivate a survey"),'img'=>'activate_deactivate'),  //Checked
			'surveycontent'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Survey content"),'description'=>$clang->gT("Permission to create/view/update/delete/import/export the questions, groups, answers & conditions of a survey"),'img'=>'add'),
			'surveylocale'=>array('create'=>false,'read'=>true,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey locale settings"),'description'=>$clang->gT("Permission to view/update the survey locale settings"),'img'=>'edit'),
			'surveysecurity'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey security"),'description'=>$clang->gT("Permission to modify survey security settings"),'img'=>'survey_security'),
			'surveysettings'=>array('create'=>false,'read'=>true,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey settings"),'description'=>$clang->gT("Permission to view/update the survey settings including token table creation"),'img'=>'survey_settings'),
			'tokens'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Tokens"),'description'=>$clang->gT("Permission to create/update/delete/import/export token entries"),'img'=>'tokens'),
			'translations'=>array('create'=>false,'read'=>true,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Quick translation"),'description'=>$clang->gT("Permission to view & update the translations using the quick-translation feature"),'img'=>'translate')
		);

		uasort($aPermissions,"aComparePermission");
		return $aPermissions;
	}

	/**
	 * Sets permissions
	 */
	public static function setPermission($uid, $sid, $permissions)
	{
		$iUserID = sanitize_int($uid);
		$condition = array('sid' => $sid, 'uid' => $uid);
		self::model()->deleteAllByAttributes($condition);
		$bResult=true;

		foreach ($permissions as $sPermissionname=>$aPermissions)
		{
			if (!isset($aPermissions['create'])) {$aPermissions['create']=0;}
			if (!isset($aPermissions['read'])) {$aPermissions['read']=0;}
			if (!isset($aPermissions['update'])) {$aPermissions['update']=0;}
			if (!isset($aPermissions['delete'])) {$aPermissions['delete']=0;}
			if (!isset($aPermissions['import'])) {$aPermissions['import']=0;}
			if (!isset($aPermissions['export'])) {$aPermissions['export']=0;}
			if ($aPermissions['create']==1 || $aPermissions['read']==1 ||$aPermissions['update']==1 || $aPermissions['delete']==1  || $aPermissions['import']==1  || $aPermissions['export']==1)
			{
				$data = array();
				$data = array(
					'sid' => $sid,
					'uid' => $uid,
					'permission' => $sPermissionname,
					'create_p' => $aPermissions['create'],
					'read_p' => $aPermissions['read'],
					'update_p' => $aPermissions['update'],
					'delete_p' => $aPermissions['delete'],
					'import_p' => $aPermissions['import'],
					'export_p' => $aPermissions['export']
				);

				$permission = new self;
				foreach ($data as $k => $v)
					$permission->$k = $v;
				$permission->save();
			}
		}

		return true;
	}
	
	function giveAllSurveyPermissions($iUserID, $iSurveyID)
    {
        $aPermissions=$this->getBasePermissions();

        $aPermissionsToSet=array();
        foreach ($aPermissions as $sPermissionName=>$aPermissionDetails)
        {
            foreach ($aPermissionDetails as $sPermissionDetailKey=>$sPermissionDetailValue)
            {
                if (in_array($sPermissionDetailKey,array('create','read','update','delete','import','export')) && $sPermissionDetailValue==true)
                {
                    $aPermissionsToSet[$sPermissionName][$sPermissionDetailKey]=1;
                }

            }
        }

        $this->setSurveyPermissions($iUserID, $iSurveyID, $aPermissionsToSet);
    }
	
	/** setSurveyPermissions
    * Set the survey permissions for a user. Beware that all survey permissions for the particual survey are removed before the new ones are written.
    *
    * @param int $iUserID The User ID
    * @param int $iSurveyID The Survey ID
    * @param array $aPermissions  Array with permissions in format <permissionname>=>array('create'=>0/1,'read'=>0/1,'update'=>0/1,'delete'=>0/1)
    */
    function setSurveyPermissions($iUserID, $iSurveyID, $aPermissions)
    {
        $iUserID=sanitize_int($iUserID);
        $condition = array('sid' => $iSurveyID, 'uid' => $iUserID);
        $this->deleteSomeRecords($condition);
        $bResult=true;

        foreach($aPermissions as $sPermissionname=>$aPermissions)
        {
            if (!isset($aPermissions['create'])) {$aPermissions['create']=0;}
            if (!isset($aPermissions['read'])) {$aPermissions['read']=0;}
            if (!isset($aPermissions['update'])) {$aPermissions['update']=0;}
            if (!isset($aPermissions['delete'])) {$aPermissions['delete']=0;}
            if (!isset($aPermissions['import'])) {$aPermissions['import']=0;}
            if (!isset($aPermissions['export'])) {$aPermissions['export']=0;}
            if ($aPermissions['create']==1 || $aPermissions['read']==1 ||$aPermissions['update']==1 || $aPermissions['delete']==1  || $aPermissions['import']==1  || $aPermissions['export']==1)
            {

                $data = array();
                $data = array(
                'sid' => $iSurveyID,
                'uid' => $iUserID,
                'permission' => $sPermissionname,
                'create_p' => $aPermissions['create'],
                'read_p' => $aPermissions['read'],
                'update_p' => $aPermissions['update'],
                'delete_p' => $aPermissions['delete'],
                'import_p' => $aPermissions['import'],
                'export_p' => $aPermissions['export']
                );
                $this->insertSomeRecords($data);
            }
        }
    }
	
	function deleteSomeRecords($condition)
    {
        $criteria = new CDbCriteria;

		foreach ($condition as $item => $value)
		{
			$criteria->addCondition($item.'="'.$value.'"');
		}
		
		$this->deleteAll($criteria);
    }

	function insertRecords($data)
	{
		foreach ($item as $data)
			$this->insertSomeRecords($item);
	}
	
    function insertSomeRecords($data)
    {
        $permission = new self;
		foreach ($data as $k => $v)
			$permission->$k = $v;
		return $permission->save();
    }

    function getUserDetails($surveyid)
    {
        $query2 = "SELECT p.sid, p.uid, u.users_name, u.full_name FROM {{survey_permissions}} AS p INNER JOIN {{users}}  AS u ON p.uid = u.uid
            WHERE p.sid = {$surveyid} AND u.uid != ".Yii::app()->session['loginID'] ."
            GROUP BY p.sid, p.uid, u.users_name, u.full_name
            ORDER BY u.users_name";
        return Yii::app()->db->createCommand($query2)->query(); //Checked
    }
}
