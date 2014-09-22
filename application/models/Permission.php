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

class Permission extends LSActiveRecord
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
     * @return Permission
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
    public static function getSurveyBasePermissions()
    {
        $defaults = array(
            'create' => true,
            'read' => true,
            'update' => true,
            'delete' => true,
            'import' => true,
            'export' => true
        );
        $aPermissions = array(
            'assessments' => array(
                'import' => false,
                'export' => false,
                'title' => gT("Assessments"),
                'description' => gT("Permission to create/view/update/delete assessments rules for a survey"),
                'img' => 'assessments'
            ),
            'quotas' => array(
                'import' => false,
                'export' => false,
                'title' => gT("Quotas"),
                'description' => gT("Permission to create/view/update/delete quota rules for a survey"),
                'img' => 'quota'
            ),
            'responses' => array(
                'title' => gT("Responses"),
                'description' => gT("Permission to create(data entry)/view/update/delete/import/export responses"),
                'img' => 'browse'
            ),
            'statistics' => array(
                'create' => false,
                'update' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Statistics"),
                'description' => gT("Permission to view statistics"),
                'img' => 'statistics'
            ),
            'survey' => array(
                'create' => false,
                'update' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Survey deletion"),
                'description' => gT("Permission to delete a survey"),
                'img' => 'delete'
            ),
            'surveyactivation' => array(
                'create' => false,
                'read' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Survey activation"),
                'description' => gT("Permission to activate/deactivate a survey"),
                'img' => 'activate_deactivate'
            ),
            'surveycontent' => array(
                'title' => gT("Survey content"),
                'description' => gT("Permission to create/view/update/delete/import/export the questions, groups, answers & conditions of a survey"),
                'img' => 'add'
            ),
            'surveylocale' => array(
                'create' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Survey text elements"),
                'description' => gT("Permission to view/update the survey text elements : survey title, survey description, welcome and end message …"),
                'img'=>'edit'
            ),
            'surveysecurity' => array(
                'import' => false,
                'export' => false,
                'title' => gT("Survey security"),
                'description' => gT("Permission to modify survey security settings"),
                'img' => 'survey_security'
            ),
            'surveysettings' => array(
                'create' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Survey settings"),
                'description' => gT("Permission to view/update the survey settings including token table creation"),
                'img' => 'survey_settings'
            ),
            'tokens' => array(
                'title' => gT("Tokens"),'description'=>gT("Permission to create/update/delete/import/export token entries"),
                'img' => 'tokens'
            ),
            'translations' => array(
                'create' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Quick translation"),
                'description' => gT("Permission to view & update the translations using the quick-translation feature"),
                'img' => 'translate'
            )
        );
        uasort($aPermissions, array(__CLASS__,"comparePermissionTitle"));
        foreach ($aPermissions as &$permission)
        {
            $permission = array_merge($defaults, $permission);
        }
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
        $defaults = array(
            'create' => true,
            'read' => true,
            'update' => true,
            'delete' => true,
            'import' => true,
            'export' => true
        );
        $aPermissions=array(
            'surveys' => array(
                'import' => false,
                'title' => gT("Surveys"),
                'description' => gT("Permission to create surveys (for which all permissions are automatically given) and view, update and delete surveys from other users"),
                'img'=>'survey'
            ),
            'users' => array(
                'import' => false,
                'export' => false,
                'title' => gT("Users"),
                'description' => gT("Permission to create, view, update and delete users"),
                'img' => 'security'
            ),
            'usergroups' => array(
                'import' => false,
                'export' => false,
                'title' => gT("User groups"),
                'description' => gT("Permission to create, view, update and delete user groups"),
                'img' => 'usergroup'
            ),
            'templates' => array(
                'title'=> gT("Templates"),
                'description' => gT("Permission to create, view, update, delete, export and import templates"),
                'img' => 'templates'
            ),
            'labelsets' => array(
                'title' => gT("Label sets"),
                'description' => gT("Permission to create, view, update, delete, export and import label sets/labels"),
                'img' => 'labels'
            ),
            'settings' => array(
                'create' => false,
                'delete' => false,
                'export' => false,
                'title' => gT("Settings & Plugins"),
                'description' => gT("Permission to view and update global settings & plugins and to delete and import plugins"),
                'img' => 'global'
            ),
            'participantpanel' => array(
                'import' => false,
                'title' => gT("Participant panel"),
                'description' => gT("Permission to create your own participants in the central participants database (for which all permissions are automatically given) and view, update and delete participants from other users"),
                'img' => 'cpdb'
            ),
        );
        uasort($aPermissions, array(__CLASS__,"comparePermissionTitle"));
        $aPermissions['superadmin'] = array(
            'create' => false,
            'update' => false,
            'delete' => false,
            'import' => false,
            'export' => false,
            'title' => gT("Superadministrator"),
            'description' => gT("Unlimited administration permissions"),
            'img' => 'superadmin'
        );

        foreach ($aPermissions as &$permission)
        {
            $permission = array_merge($defaults, $permission);
        }
        return $aPermissions;
    }    
    
    public static function getPermissions($iUserID, $iEntityID=null, $sEntityName=null)
    {
        if ($sEntityName=='survey')
        {
            $aBasePermissions=Permission::model()->getSurveyBasePermissions();
        }
        elseif ($sEntityName=='global')
        {   
            $aBasePermissions=Permission::model()->getGlobalBasePermissions();
        }
        
        if (is_null($sEntityName))
        {
            $oPermissions=Permission::model()->findAllByAttributes(array('uid'=>$iUserID));
            $aBasePermissions = array();
            foreach($oPermissions as $oPermission)
            {
                $aBasePermissions[$oPermission->id] = $oPermission->attributes;
            }
        }   
        else
        {
            foreach ($aBasePermissions as $sPermission=>&$aPermissionDetail){
                $oCurrentPermissions=Permission::model()->findByAttributes(array('uid'=>$iUserID,'entity_id'=>$iEntityID, 'permission'=>$sPermission));
                if ($aPermissionDetail['create']) $aPermissionDetail['create']=($oCurrentPermissions?(boolean)$oCurrentPermissions->create_p:false);
                if ($aPermissionDetail['read']) $aPermissionDetail['read']=($oCurrentPermissions?(boolean)$oCurrentPermissions->read_p:false);
                if ($aPermissionDetail['update']) $aPermissionDetail['update']=($oCurrentPermissions?(boolean)$oCurrentPermissions->update_p:false);
                if ($aPermissionDetail['delete']) $aPermissionDetail['delete']=($oCurrentPermissions?(boolean)$oCurrentPermissions->delete_p:false);
                if ($aPermissionDetail['import']) $aPermissionDetail['import']=($oCurrentPermissions?(boolean)$oCurrentPermissions->import_p:false);
                if ($aPermissionDetail['export']) $aPermissionDetail['export']=($oCurrentPermissions?(boolean)$oCurrentPermissions->export_p:false);
            }
        }     
        return $aBasePermissions;
    }
     
    /**
    * Sets permissions (global or survey-specific) for a survey administrator
    * Checks what permissions may be set and automatically filters invalid ones. 
    * A permission may be invalid if the permission does not exist or that particular user may not give that permission
    * 
    * @param mixed $iUserID
    * @param mixed $iEntityID
    * @param mixed $sEntityName
    * @param mixed $aPermissions
    * @param mixed $bBypassCheck
    */
    public static function setPermissions($iUserID, $iEntityID, $sEntityName, $aPermissions, $bBypassCheck=false)
    {
        $iUserID = sanitize_int($iUserID);
        // Filter global permissions on save
        if ($sEntityName=='global')
        {
            $aBasePermissions=Permission::model()->getGlobalBasePermissions();
            if (!Permission::model()->hasGlobalPermission('superadmin','read') && !$bBypassCheck) // if not superadmin filter the available permissions as no admin may give more permissions than he owns
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
            elseif (Permission::model()->hasGlobalPermission('superadmin','read') && Yii::app()->session['loginID']!=1)
            {
                unset($aBasePermissions['superadmin']);
            }
        }
        elseif ($sEntityName=='survey')
        {
            $aBasePermissions=Permission::model()->getSurveyBasePermissions();
        }

        $aFilteredPermissions=array();
        foreach ($aBasePermissions as $sPermissionname=>$aPermission)
        {
            $aFilteredPermissions[$sPermissionname]['create']= (isset($aPermissions[$sPermissionname]['create']) && $aPermissions[$sPermissionname]['create']);
            $aFilteredPermissions[$sPermissionname]['read']  = (isset($aPermissions[$sPermissionname]['read']) && $aPermissions[$sPermissionname]['read']);
            $aFilteredPermissions[$sPermissionname]['update']= (isset($aPermissions[$sPermissionname]['update']) && $aPermissions[$sPermissionname]['update']);
            $aFilteredPermissions[$sPermissionname]['delete']= (isset($aPermissions[$sPermissionname]['delete']) && $aPermissions[$sPermissionname]['delete']);
            $aFilteredPermissions[$sPermissionname]['import']= (isset($aPermissions[$sPermissionname]['import']) && $aPermissions[$sPermissionname]['import']);
            $aFilteredPermissions[$sPermissionname]['export']= (isset($aPermissions[$sPermissionname]['export']) && $aPermissions[$sPermissionname]['export']);
        }
        
        $condition = array('entity_id' => $iEntityID, 'uid' => $iUserID);
        $oEvent=new PluginEvent('beforePermissionSetSave');
        $oEvent->set('aNewPermissions',$aFilteredPermissions);
        $oEvent->set('iSurveyID',$iEntityID);
        $oEvent->set('iUserID',$iUserID);
        $result = App()->getPluginManager()->dispatchEvent($oEvent);

        // Only the original superadmin may change the superadmin permissions
        if (Yii::app()->session['loginID']!=1)
        {
            Permission::model()->deleteAllByAttributes($condition,"permission <> 'superadmin' AND entity <> 'template'");
        }
        else
        {
            Permission::model()->deleteAllByAttributes($condition,"entity <> 'template'");
        }

        foreach ($aFilteredPermissions as $sPermissionname=>$aPermission)
        {
            if ($aPermission['create'] || $aPermission['read'] ||$aPermission['update'] || $aPermission['delete']  || $aPermission['import']  || $aPermission['export'])
            {
                $data = array(
                    'entity_id' => $iEntityID,
                    'entity' => $sEntityName,
                    'uid' => $iUserID,
                    'permission' => $sPermissionname,
                    'create_p' => (int)$aPermission['create'],
                    'read_p' => (int)$aPermission['read'],
                    'update_p' => (int)$aPermission['update'],
                    'delete_p' => (int)$aPermission['delete'],
                    'import_p' => (int)$aPermission['import'],
                    'export_p' => (int)$aPermission['export']
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
        $aPermissions=$this->getSurveyBasePermissions();
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
        $this->setPermissions($iUserID, $iSurveyID, 'survey', $aPermissionsToSet);
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
        $sQuery = "SELECT p.entity_id, p.uid, u.users_name, u.full_name FROM {{permissions}} AS p INNER JOIN {{users}}  AS u ON p.uid = u.uid
            WHERE p.entity_id = :surveyid AND u.uid != :userid and p.entity='survey'
            GROUP BY p.entity_id, p.uid, u.users_name, u.full_name
            ORDER BY u.users_name";
        $iUserID=Yii::app()->user->getId();
        return Yii::app()->db->createCommand($sQuery)->bindParam(":userid", $iUserID, PDO::PARAM_INT)->bindParam("surveyid", $surveyid, PDO::PARAM_INT)->query()->readAll(); //Checked
    }

    function copySurveyPermissions($iSurveyIDSource,$iSurveyIDTarget)
    {
        $aRows=self::model()->findAll("entity_id=:sid AND entity='survey'", array(':sid'=>$iSurveyIDSource));
        foreach ($aRows as $aRow)
        {
            $aRow = $aRow->getAttributes();
            $aRow['entity_id']=$iSurveyIDTarget;    // Set the new survey ID
            unset($aRow['id']);                     // To insert, we reset the id
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
    * Checks if a user has a certain permission
    *
    * @param $iEntityID integer The entity ID
    * @param $sEntityName string The entity name
    * @param $sPermission string Name of the permission
    * @param $sCRUD string The permission detail you want to check on: 'create','read','update','delete','import' or 'export'
    * @param $iUserID integer User ID - if not given the one of the current user is used
    * @return bool True if user has the permission
    */
    function hasPermission($iEntityID, $sEntityName, $sPermission, $sCRUD, $iUserID=null)
    {
        static $aPermissionCache;
        if (!in_array($sCRUD,array('create','read','update','delete','import','export'))) return false;
        $sCRUD=$sCRUD.'_p';

        if (is_null($iUserID))
        {
            if (!Yii::app()->user->getIsGuest()) $iUserID = Yii::app()->session['loginID'];
            else return false;
        }

        if ($iEntityID>0 && $sEntityName=='survey')
        {
            $aSurveyInfo=getSurveyInfo($iEntityID);// OR find but then don't use $static
            if (!$aSurveyInfo) return false;
            // If you own a survey you have access to the whole survey
            if ($iUserID==$aSurveyInfo['owner_id']) return true;
        }

        // Check if superadmin and cache it
        if (!isset($aPermissionCache[0]['global'][$iUserID]['superadmin']['read_p']))
        {
            $aPermission = $this->findByAttributes(array("entity_id"=>0,'entity'=>'global', "uid"=> $iUserID, "permission"=>'superadmin'));
            $bPermission = is_null($aPermission) ? array() : $aPermission->attributes;
            if (!isset($bPermission['read_p']) || $bPermission['read_p']==0)
            {
                $bPermission=false;
            }
            else
            {
                $bPermission=true;
            }
            $aPermissionCache[0]['global'][$iUserID]['superadmin']['read_p']= $bPermission;
        }
        
        if ($aPermissionCache[0]['global'][$iUserID]['superadmin']['read_p']) return true;
        if (!isset($aPermissionCache[$iEntityID][$sEntityName][$iUserID][$sPermission][$sCRUD]))
        {
            $query = $this->findByAttributes(array("entity_id"=> $iEntityID, "uid"=> $iUserID, "entity"=>$sEntityName, "permission"=>$sPermission));
            $bPermission = is_null($query) ? array() : $query->attributes;
            if (!isset($bPermission[$sCRUD]) || $bPermission[$sCRUD]==0)
            {
                $bPermission=false;
            }
            else
            {
                $bPermission=true;
            }
            $aPermissionCache[$iEntityID][$sEntityName][$iUserID][$sPermission][$sCRUD]=$bPermission;
        }
        return $aPermissionCache[$iEntityID][$sEntityName][$iUserID][$sPermission][$sCRUD];
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
        return $this->hasPermission(0, 'global', $sPermission, $sCRUD, $iUserID);
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
        return $this->hasPermission($iSurveyID, 'survey', $sPermission, $sCRUD, $iUserID);
    }        

    /**
    * Returns true if a user has permission to use a certain template
    * @param $sPermission string Name of the permission - see function getGlobalPermissions
    * @param $sCRUD string The permission detailsyou want to check on: 'create','read','update','delete','import' or 'export'
    * @param $iUserID integer User ID - if not given the one of the current user is used
    * @return bool True if user has the permission
    */
    function hasTemplatePermission($sTemplateName, $sCRUD='read', $iUserID=null)
    {
        return $this->hasPermission(0, 'template', $sTemplateName, $sCRUD, $iUserID);
    }

    /**
    /* function used to order Permission by language string
    /* @param aApermission array The first permission information
    /* @param aBpermission array The second permission information
    /* @return bool 
    */
    private static function comparePermissionTitle($aApermission,$aBpermission)
    {
        return strcmp($aApermission['title'], $aBpermission['title']);
    }
}
