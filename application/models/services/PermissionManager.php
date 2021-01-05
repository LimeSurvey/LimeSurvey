<?php
/**
 * Management of Permission
 * @version 0.1.0
 * @author Denis Chenu
 * @copyright LimeSurvey
 */

namespace LimeSurvey\Models\Services;

use LSHttpRequest;
use LSWebUser;
use Permission;

use App;
use CHtml;

/**
 * Service class for managing permission
 */
class PermissionManager
{

    /** @var LSHttpRequest */
    private $request;

    /** @var LSWebUser */
    private $user;

    /** @var Permission model */
    private $permission;

    /**
     * @param LSHttpRequest $request
     * @param LSWebUser $user
     */
    public function __construct(
        LSHttpRequest $request,
        LSWebUser $user,
        Permission $permission
    ) {
        $this->request = $request;
        $this->user = $user;
        $this->permission = $permission;
    }

    /**
     * get the permission data
     * @param string $modelName the model name
     * @param integer $modelId the model id
     * @param integer $userId for this user id
     * @return array[] 
     */
    public function getPermissionData($modelName, $modelId, $userId = null)
    {
        $aObjectPermissions = Permission::getEntityBasePermissions($modelName); // Usage of static, db not needed
        if(empty($aObjectPermissions)) {
            return $aObjectPermissions;
        }
        $permissionFunctionName = "has{$modelName}Permission";
        /* string[] Crud type array */
        $aCruds = array('create', 'read', 'update', 'delete', 'import', 'export');
        foreach (array_keys($aObjectPermissions) as $sPermission) {
            $aObjectPermissions[$sPermission]['current'] = array();
            foreach($aCruds as $crud) {
                $aObjectPermissions[$sPermission]['current'][$crud] = array(
                    'checked' => false,
                    /* The checkbox are disable if currentuser don't have permission */
                    'disabled' => !$this->permission->$permissionFunctionName($modelId, $sPermission, $crud, $this->user->id),
                    'indeterminate' => false
                );
            }
            /* If user id is set : update the data with permission of this user */
            if($userId) {
                $oCurrentPermissions = $this->permission->find(
                    "entity = :entity AND entity_id = :entity_id AND uid = :uid AND permission = :permission",
                    array(
                        ":entity" => $modelName,
                        ":entity_id" => $modelId,
                        ":uid" => $userId,
                        ":permission" => $sPermission
                    )
                );
                foreach ($aCruds as $crud) {
                    if ($aObjectPermissions[$sPermission][$crud]) {
                        $havePermissionSet = !empty($oCurrentPermissions) && $oCurrentPermissions->getAttribute("{$crud}_p");
                        /* The user have the permission set */
                        $aObjectPermissions[$sPermission]['current'][$crud]['checked'] = $havePermissionSet;
                        /* The user didn't have the permission set, but have permission by other way (inherited, plugin …) */
                        if(!$havePermissionSet) {
                            $functionName = "has{$modelName}Permission";
                            $aObjectPermissions[$sPermission]['current'][$crud]['indeterminate'] = $this->permission->$permissionFunctionName($modelId, $sPermission, $crud, $userId);
                        }
                    }
                }
            }
            $aObjectPermissions[$sPermission]['entity'] = $modelName;
        }
        return $aObjectPermissions;
    }

    /**
     * @todo : Save Permission by POST value according to current user permssion
     * @see Permission::setPermissions
     * @param mixed $iUserID
     * @param string $modelName
     * @param mixed $modelId
     * @return boolean
     */
    public function setPermissions($userId, $modelName, $modelId)
    {
        $success = true;
        $permissionsToSet = $this->request->getPost('set');
        if (empty($permissionsToSet[$modelName])) {
            /* Nothing to do */
            return $success;
        }
        /* string : function name to use for final Permission */
        $permissionFunctionName = "has{$modelName}Permission";
        /* string[] Crud type array */
        $aCruds = array('create', 'read', 'update', 'delete', 'import', 'export');
        $entityPermissionsToSet = $permissionsToSet[$modelName];
        $aBasePermissions = Permission::getEntityBasePermissions($modelName);

        /* string[] The array to set (or not) */
        $aSetPermissions = array();
        foreach ($aBasePermissions as $sPermission => $aPermission) {
            $aSetPermissions[$sPermission] = array();
            foreach ($aCruds as $crud) {
                /* Check if current user have the permission to set */
                if($this->permission->$permissionFunctionName($modelId, $sPermission, $crud, $this->user->id)) {
                    $aSetPermissions[$sPermission][$crud] = !empty($aPermission[$crud]) && !empty($entityPermissionsToSet[$sPermission][$crud]);
                }
            }
        }
        /* remove uneeded Permission (user don't have any rights) */
        $aSetPermissions = array_filter($aSetPermissions);
        // Event
        $oEvent = new \LimeSurvey\PluginManager\PluginEvent('beforePermissionSetSave');
        $oEvent->set('aNewPermissions', $aSetPermissions);
        if($modelName == 'Survey') {
            $oEvent->set('iSurveyID', $modelId);
        }
        $oEvent->set('entity', $modelName); /* New in 4.4.X */
        $oEvent->set('entityId', $modelId); /* New in 4.4.X */
        $oEvent->set('iUserID', $userId);
        App()->getPluginManager()->dispatchEvent($oEvent);

        foreach($aSetPermissions as $sPermission => $aSetPermission) {
            $oCurrentPermission = $this->permission->find(
                "entity = :entity AND entity_id = :entity_id AND uid = :uid AND permission = :permission",
                array(
                    ":entity" => $modelName,
                    ":entity_id" => $modelId,
                    ":uid" => $userId,
                    ":permission" => $sPermission
                )
            );
            if(empty($oCurrentPermission)) {
                $oCurrentPermission = $this->getNewPermission();
                $oCurrentPermission->entity = $modelName;
                $oCurrentPermission->entity_id = $modelId;
                $oCurrentPermission->uid = $userId;
                $oCurrentPermission->permission = $sPermission;
            }
            /* Set only the permission set in $aSetPermission : user have the rights */
            foreach($aSetPermission as $crud => $permission) {
                $oCurrentPermission->setAttribute("{$crud}_p",intval($permission));
            }
            if(!$oCurrentPermission->save()) {
                $success = false;
                App()->setFlashMessage(CHtml::errorSummary($oCurrentPermission),'warning');
            }
        }
        $this->permission::setMinimalEntityPermission($userId, $modelId, $modelName);
        return $success;
    }

    /**
     * Create a new Permission Model
     * To be mocked for test
     * @return \Permission
     */
    public function getNewPermission()
    {
        return new Permission();
    }
}
