<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');
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
   */

class Permission extends CActiveRecord
{
    /**
     * Returns the table's name
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{permissions}}';
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
            'assessments'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Assessments"),'description'=>$clang->gT("Permission to create/view/update/delete assessments rules for a survey"),'img'=>'assessments'),  
            'quotas'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Quotas"),'description'=>$clang->gT("Permission to create/view/update/delete quota rules for a survey"),'img'=>'quota'),
            'responses'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Responses"),'description'=>$clang->gT("Permission to create(data entry)/view/update/delete/import/export responses"),'img'=>'browse'),
            'statistics'=>array('create'=>false,'read'=>true,'update'=>false,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Statistics"),'description'=>$clang->gT("Permission to view statistics"),'img'=>'statistics'),   
            'survey'=>array('create'=>false,'read'=>true,'update'=>false,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey deletion"),'description'=>$clang->gT("Permission to delete a survey"),'img'=>'delete'),   
            'surveyactivation'=>array('create'=>false,'read'=>false,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey activation"),'description'=>$clang->gT("Permission to activate/deactivate a survey"),'img'=>'activate_deactivate'),  
            'surveycontent'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Survey content"),'description'=>$clang->gT("Permission to create/view/update/delete/import/export the questions, groups, answers & conditions of a survey"),'img'=>'add'),
            'surveylocale'=>array('create'=>false,'read'=>true,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey locale settings"),'description'=>$clang->gT("Permission to view/update the survey locale settings"),'img'=>'edit'),
            'surveysecurity'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey security"),'description'=>$clang->gT("Permission to modify survey security settings"),'img'=>'survey_security'),
            'surveysettings'=>array('create'=>false,'read'=>true,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey settings"),'description'=>$clang->gT("Permission to view/update the survey settings including token table creation"),'img'=>'survey_settings'),
            'tokens'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Tokens"),'description'=>$clang->gT("Permission to create/update/delete/import/export token entries"),'img'=>'tokens'),
            'translations'=>array('create'=>false,'read'=>true,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Quick translation"),'description'=>$clang->gT("Permission to view & update the translations using the quick-translation feature"),'img'=>'translate')
        );
        uasort($aPermissions,"comparePermission");
        return $aPermissions;
    }
    
    
    /**
     * Returns the global permissions
     *
     * @access public
     * @static
     * @return array
     */
    public static function getGlobalBasePermissions()
    {
        $clang = Yii::app()->lang;
        $aPermissions=array(
            'global_surveys'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Surveys"),'description'=>$clang->gT("Permission to create, view, update and delete any surveys"),'img'=>'survey'),
            'global_users'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Users"),'description'=>$clang->gT("Permission to create, view, update and delete users"),'img'=>'user'),
            'global_usergroups'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("User groups"),'description'=>$clang->gT("Permission to create, view, update and delete user groups"),'img'=>'user'),
            'global_templates'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Templates"),'description'=>$clang->gT("Permission to create, view, update, delete, export and import templates"),'img'=>'template'),
            'global_labelsets'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Label sets"),'description'=>$clang->gT("Permission to create, view, update, delete, export and import label sets/labels"),'img'=>'template'),
            'global_settings'=>array('create'=>false,'read'=>true,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Settings"),'description'=>$clang->gT("Permission to view and update global settings"),'img'=>'settings'),
            'global_participantpanel'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Participant panel"),'description'=>$clang->gT("Permission to create, view, update, delete, export and import participants in the participant panel"),'img'=>'settings'),
            'global_superadmin'=>array('create'=>false,'read'=>true,'update'=>false,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Superadministrator"),'description'=>$clang->gT("Unlimited administration permissions"),'img'=>'superadmin')
        );
        uasort($aPermissions,"comparePermission");
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
            $aPermissions['create']= (isset($aPermissions['create']) && $aPermissions['create'])? 1:0;
            $aPermissions['read']= (isset($aPermissions['read']) && $aPermissions['read'])? 1:0;
            $aPermissions['update']= (isset($aPermissions['update']) && $aPermissions['update'])? 1:0;
            $aPermissions['delete']= (isset($aPermissions['delete']) && $aPermissions['delete'])? 1:0;
            $aPermissions['import']= (isset($aPermissions['import']) && $aPermissions['import'])? 1:0;
            $aPermissions['export']= (isset($aPermissions['export']) && $aPermissions['export'])? 1:0;
            if ($aPermissions['create']==1 || $aPermissions['read']==1 ||$aPermissions['update']==1 || $aPermissions['delete']==1  || $aPermissions['import']==1  || $aPermissions['export']==1)
            {
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
            $criteria->addCondition($item."='".$value."'");
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
        $sQuery = "SELECT p.sid, p.uid, u.users_name, u.full_name FROM {{permissions}} AS p INNER JOIN {{users}}  AS u ON p.uid = u.uid
            WHERE p.sid = :surveyid AND u.uid != :userid
            GROUP BY p.sid, p.uid, u.users_name, u.full_name
            ORDER BY u.users_name";
        $iUserID=Yii::app()->user->getId();
        return Yii::app()->db->createCommand($sQuery)->bindParam(":userid", $iUserID, PDO::PARAM_INT)->bindParam("surveyid", $surveyid, PDO::PARAM_INT)->query()->readAll(); //Checked
    }

    function copySurveyPermissions($iSurveyIDSource,$iSurveyIDTarget)
    {
        $aRows=self::model()->findAll('sid=:sid', array(':sid'=>$iSurveyIDSource));
        foreach ($aRows as $aRow)
        {
            $aRow['sid']=$iSurveyIDTarget;
            try  {
                $this->insertSomeRecords($aRow);
            }
            catch (Exception $e)
            {
                //Ignore
            }
        }
    }
    
    
    /**
    * Checks if a user has a certain permission in the given survey
    *
    * @param $iSurveyID integer The survey ID
    * @param $sPermission string Name of the permission
    * @param $sCRUD string The permission detail you want to check on: 'create','read','update','delete','import' or 'export'
    * @param $iUserID integer User ID - if not given the one of the current user is used
    * @return bool True if user has the permission
    */
    function hasSurveyPermission($iSurveyID, $sPermission, $sCRUD, $iUserID=null)
    {
        if (!in_array($sCRUD,array('create','read','update','delete','import','export'))) return false;
        $sCRUD=$sCRUD.'_p';

        if ($iSurveyID>0)
        {
            $thissurvey=getSurveyInfo($iSurveyID);
            if (!$thissurvey) return false;
        }

        $aPermissionCache = Yii::app()->getConfig("aPermissionCache");
        if (is_null($iUserID))
        {
            if (!Yii::app()->user->getIsGuest()) $iUserID = Yii::app()->session['loginID'];
            else return false;
            // Some user types have access to whole survey settings
            if (isset($thissurvey) && $iUserID==$thissurvey['owner_id']) return true;
        }

        // Check if superadmin and cache it
        if (!isset($aPermissionCache[0][$iUserID]['global_superadmin']['read_p']))
        {
            $aPermission = $this->findByAttributes(array("sid"=>0,"uid"=> $iUserID,"permission"=>'global_superadmin'));
            $bPermission = is_null($aPermission) ? array() : $aPermission->attributes;
            if (!isset($bPermission['read_p']) || $bPermission['read_p']==0)
            {
                $bPermission=false;
            }
            else
            {
                $bPermission=true;
            }            
            $aPermissionCache[0][$iUserID]['global_superadmin']['read_p']= $bPermission;
        }
        
        if (!isset($aPermissionCache[$iSurveyID][$iUserID][$sPermission][$sCRUD]))
        {
            $query = $this->findByAttributes(array("sid"=> $iSurveyID,"uid"=> $iUserID,"permission"=>$sPermission));
            $bPermission = is_null($query) ? array() : $query->attributes;
            if (!isset($bPermission[$sCRUD]) || $bPermission[$sCRUD]==0)
            {
                $bPermission=false;
            }
            else
            {
                $bPermission=true;
            }
            $aPermissionCache[$iSurveyID][$iUserID][$sPermission][$sCRUD]=$bPermission;
        }
        Yii::app()->setConfig("aPermissionCache", $aPermissionCache);
        return $aPermissionCache[$iSurveyID][$iUserID][$sPermission][$sCRUD];
    }
    
    /**
    * Returns true if a user has global permission for a certain action. 
    * @param $sPermission string Name of the permission - see function getGlobalPermissions
    * @param $sCRUD string The permission detailsyou want to check on: 'create','read','update','delete','import' or 'export'
    * @param $iUserID integer User ID - if not given the one of the current user is used
    * @return bool True if user has the permission
    */
    function hasGlobalPermission($sPermission, $sCRUD, $iUserID=null)
    {
        return $this->hasSurveyPermission(0, $sPermission, $sCRUD, $iUserID);
    }    
    

}
