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

/**
 * Service class for managing permission
 */
class PermissionManager
{

    /** @var LSHttpRequest */
    private $request;

    /** @var LSWebUser */
    private $user;

    /**
     * @param LSHttpRequest $request
     * @param LSWebUser $user
     */
    public function __construct(
        LSHttpRequest $request,
        LSWebUser $user
    ) {
        $this->request = $request;
        $this->user = $user;
    }

    /**
     * get the permission data
     * @param string $modelName the model name
     * @param interger $modelId the model name
     * @param integer $userId for this user id
     * @return array[] 
     */
    public function getPermissionData($modelName, $modelId, $userId = null)
    {
        $aObjectPermissions = Permission::model()->getEntityBasePermissions($modelName);
        if(empty($aObjectPermissions)) {
            return $aObjectPermissions;
        }
        /* string[] Crud type array */
        $aCruds = array('create', 'read', 'update', 'delete', 'import', 'export');
        foreach (array_keys($aObjectPermissions) as $sPermission) {
            $aObjectPermissions[$sPermission]['current'] = array();
            foreach($aCruds as $crud) {
                $aObjectPermissions[$sPermission]['current'][$crud] = array(
                    'checked' => false,
                    /* The checkbox are disable if currentuser don't have permission */
                    'disabled' => !Permission::model()->hasPermission($modelId, $modelName, $sPermission, 'create'),
                    'indeterminate' => false
                );
            }
            /* If user id is set : update the data with permission of this user */
            if($userId) {
                $oCurrentPermissions = Permission::model()->find(
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
                            $aObjectPermissions[$sPermission]['current'][$crud]['indeterminate'] = Permission::model()->hasPermission($modelId, $modelName, $sPermission, $crud, $userId);
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
     */
    public function setPermissions()
    {
        // @todo
    }
}
