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
     * Returns the global permissions including description and title
     *
     * @access public
     * @static
     * @return array
     */
    public static function getGlobalBasePermissions()
    {
        $clang = Yii::app()->lang;
        $aPermissions=array(
            'global_surveys'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>true,'title'=>$clang->gT("Surveys"),'description'=>$clang->gT("Permission to create surveys (for which all permissions are automatically given) and view, update and delete surveys from other users"),'img'=>'survey'),
            'global_users'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Users"),'description'=>$clang->gT("Permission to create, view, update and delete users"),'img'=>'security'),
            'global_usergroups'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("User groups"),'description'=>$clang->gT("Permission to create, view, update and delete user groups"),'img'=>'usergroup'),
            'global_templates'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Templates"),'description'=>$clang->gT("Permission to create, view, update, delete, export and import templates"),'img'=>'templates'),
            'global_labelsets'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Label sets"),'description'=>$clang->gT("Permission to create, view, update, delete, export and import label sets/labels"),'img'=>'labels'),
            'global_settings'=>array('create'=>false,'read'=>true,'update'=>true,'delete'=>false,'import'=>true,'export'=>false,'title'=>$clang->gT("Settings & Plugins"),'description'=>$clang->gT("Permission to view and update global settings & plugins and to delete and import plugins"),'img'=>'global'),
            'global_participantpanel'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>true,'title'=>$clang->gT("Participant panel"),'description'=>$clang->gT("Permission to create your own participants in the central participants database (for which all permissions are automatically given) and view, update and delete participants from other users"),'img'=>'cpdb'),
        );
        uasort($aPermissions,"comparePermission");
        $aPermissions=array('global_superadmin'=>array('create'=>false,'read'=>true,'update'=>false,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Superadministrator"),'description'=>$clang->gT("Unlimited administration permissions"),'img'=>'superadmin'))+$aPermissions;
        return $aPermissions;
    }    
    

    /**
     * Sets permissions (global or survey-specific) for a survey administrator
     * Checks what permissions may be set and automatically filters invalid ones. 
     * A permission may be invalid if the permission does not exist or that particular user may not give that permission
     * 
     */
    public static function setPermissions($iUserID, $iSurveyID, $aPermissions)
    {
        $iUserID = sanitize_int($iUserID);
        
        // Filter global permissions on save
        if ($iSurveyID==0)
        {
            $aBasePermissions=Permission::model()->getGlobalBasePermissions();
            if (!Permission::model()->hasGlobalPermission('global_superadmin','read')) // if not superadmin filter the available permissions as no admin may give more permissions than he owns
            {
                // Make sure that he owns the user he wants to give global permissions for
                $oUser = User::model()->findByAttributes(array('uid' => $iUserID, 'parent_id' => Yii::app()->session['loginID']));
                if (!$oUser) {
                    die('You are not allowed to set permisisons for this user');
                }
                $aFilteredPermissions=array();
                foreach  ($aBasePermissions as $PermissionName=>$aPermission)
                {
                    foreach ($aPermission as $sPermissionKey=>&$sPermissionValue)
                    {
                        if ($sPermissionKey!='title' && $sPermissionKey!='img' && !Permission::model()->hasGlobalPermission($PermissionName, $sPermissionKey)) $sPermissionValue=false;
                    }
                    // Only have a row for that permission if there is at least one permission he may give to other users
                    if ($aPermission['create'] || $aPermission['read'] || $aPermission['update'] || $aPermission['delete'] || $aPermission['import'] || $aPermission['export'])
                    {
                        $aFilteredPermissions[$PermissionName]=$aPermission;
                    }
                }
                $aBasePermissions=$aFilteredPermissions;        
            }
        }
        else
        {
            $aBasePermissions=Permission::model()->getBasePermissions();
            
        }

        $condition = array('sid' => $iSurveyID, 'uid' => $iUserID);
        Permission::model()->deleteAllByAttributes($condition);
        foreach ($aBasePermissions as $sPermissionname=>$aPermission)
        {
            $aPermission['create']= (isset($aPermissions[$sPermissionname]['create']) && $aPermissions[$sPermissionname]['create'])? 1:0;
            $aPermission['read']= (isset($aPermissions[$sPermissionname]['read']) && $aPermissions[$sPermissionname]['read'])? 1:0;
            $aPermission['update']= (isset($aPermissions[$sPermissionname]['update']) && $aPermissions[$sPermissionname]['update'])? 1:0;
            $aPermission['delete']= (isset($aPermissions[$sPermissionname]['delete']) && $aPermissions[$sPermissionname]['delete'])? 1:0;
            $aPermission['import']= (isset($aPermissions[$sPermissionname]['import']) && $aPermissions[$sPermissionname]['import'])? 1:0;
            $aPermission['export']= (isset($aPermissions[$sPermissionname]['export']) && $aPermissions[$sPermissionname]['export'])? 1:0;
            if ($aPermission['create']==1 || $aPermission['read']==1 ||$aPermission['update']==1 || $aPermission['delete']==1  || $aPermission['import']==1  || $aPermission['export']==1)
            {
                $data = array(
                    'sid' => $iSurveyID,
                    'uid' => $iUserID,
                    'permission' => $sPermissionname,
                    'create_p' => $aPermission['create'],
                    'read_p' => $aPermission['read'],
                    'update_p' => $aPermission['update'],
                    'delete_p' => $aPermission['delete'],
                    'import_p' => $aPermission['import'],
                    'export_p' => $aPermission['export']
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

        $this->setPermissions($iUserID, $iSurveyID, $aPermissionsToSet);
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
        static $aPermissionCache;
        if (!in_array($sCRUD,array('create','read','update','delete','import','export'))) return false;
        $sCRUD=$sCRUD.'_p';

        if ($iSurveyID>0)
        {
            $thissurvey=getSurveyInfo($iSurveyID);
            if (!$thissurvey) return false;
        }

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
        
        if ($aPermissionCache[0][$iUserID]['global_superadmin']['read_p']) return true;
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
