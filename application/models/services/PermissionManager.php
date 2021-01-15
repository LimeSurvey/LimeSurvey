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
use PermissionInterface;
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

    /** @var PermissionInterface model where permission is checked*/
    private $model;

    /**
     * @param LSHttpRequest $request
     * @param LSWebUser $user
     */
    public function __construct(
        LSHttpRequest $request,
        LSWebUser $user,
        PermissionInterface $model
    ) {
        $this->request = $request;
        $this->user = $user;
        $this->model = $model;
    }

    /**
     * get the permission data
     * @param integer $userId for this user id
     * @return array[]
     */
    public function getPermissionData($userId = null)
    {
        $aObjectPermissions = $this->model::getPermissionData(); // Usage of static, db not needed
        if (empty($aObjectPermissions)) {
            return $aObjectPermissions;
        }
        /* string[] Crud type array */
        $aCruds = array('create', 'read', 'update', 'delete', 'import', 'export');
        foreach (array_keys($aObjectPermissions) as $sPermission) {
            $aObjectPermissions[$sPermission]['current'] = array();
            foreach ($aCruds as $crud) {
                if (!isset($aObjectPermissions[$sPermission][$crud])) {
                    /* Not set mean true (in Survey on 3.X) */
                    $aObjectPermissions[$sPermission][$crud] = true;
                }
                $aObjectPermissions[$sPermission]['current'][$crud] = array(
                    'checked' => false,
                    /* The checkbox are disable if currentuser don't have permission */
                    'disabled' => !$this->getCurrentPermission($sPermission, $crud, $this->user->id),
                    'indeterminate' => false
                );
            }
            /* If user id is set : update the data with permission of this user */
            if (!is_null($userId)) {
                $oCurrentPermission = $this->getDbPermission(
                    get_class($this->model),
                    $this->model->getPrimaryKey(),
                    $userId,
                    $sPermission
                );
                foreach ($aCruds as $crud) {
                    if ($aObjectPermissions[$sPermission][$crud]) {
                        $havePermissionSet = !empty($oCurrentPermission) && $oCurrentPermission->getAttribute("{$crud}_p");
                        /* The user have the permission set */
                        $aObjectPermissions[$sPermission]['current'][$crud]['checked'] = $havePermissionSet;
                        /* The user didn't have the permission set, but have permission by other way (inherited, plugin …) */
                        if (!$havePermissionSet) {
                            $aObjectPermissions[$sPermission]['current'][$crud]['indeterminate'] = $this->getCurrentPermission($sPermission, $crud, $userId);
                        }
                    }
                }
            }
            $aObjectPermissions[$sPermission]['entity'] = get_class($this->model);
        }
        return $aObjectPermissions;
    }

    /**
     * @todo : Save Permission by POST value according to current user permssion
     * @see Permission::setPermissions
     * @param mixed $iUserID
     * @return boolean
     */
    public function setPermissions($userId)
    {
        $success = true;
        $permissionsToSet = $this->request->getPost('set');
        if (empty($permissionsToSet[get_class($this->model)])) {
            /* Nothing to do */
            return $success;
        }

        /* string[] Crud type array */
        $aCruds = array('create', 'read', 'update', 'delete', 'import', 'export');
        /* @array[] the permissions to set for this model (via POST value) */
        $entityPermissionsToSet = $permissionsToSet[get_class($this->model)];
        $aBasePermissions = $this->getPermissionData();

        /* string[] The array to set (or not) */
        $aSetPermissions = array();
        foreach ($aBasePermissions as $sPermission => $aPermission) {
            $aSetPermissions[$sPermission] = array();
            foreach ($aCruds as $crud) {
                /* Only set value if current user have the permission to set */
                if ($this->getCurrentPermission($sPermission, $crud, $this->user->id)) {
                    $aSetPermissions[$sPermission][$crud] = !empty($aPermission[$crud]) && !empty($entityPermissionsToSet[$sPermission][$crud]);
                }
            }
        }
        /* remove uneeded Permission (user don't have any rights) */
        $aSetPermissions = array_filter($aSetPermissions);
        // Event
        $oEvent = new \LimeSurvey\PluginManager\PluginEvent('beforePermissionSetSave');
        $oEvent->set('aNewPermissions', $aSetPermissions);
        if (get_class($this->model) == 'Survey') {
            $oEvent->set('iSurveyID', $this->model->getPrimaryKey());
        }
        $oEvent->set('entity', get_class($this->model)); /* New in 4.4.X */
        $oEvent->set('entityId', $this->model->getPrimaryKey()); /* New in 4.4.X */
        $oEvent->set('iUserID', $userId);
        App()->getPluginManager()->dispatchEvent($oEvent);

        foreach ($aSetPermissions as $sPermission => $aSetPermission) {
            $oCurrentPermission = $this->getDbPermission(
                get_class($this->model),
                $this->model->getPrimaryKey(),
                $userId,
                $sPermission
            );
            if (empty($oCurrentPermission)) {
                $oCurrentPermission = $this->setDbPermission(
                    get_class($this->model),
                    $this->model->getPrimaryKey(),
                    $userId,
                    $sPermission
                );
            }
            /* Set only the permission set in $aSetPermission : user have the rights */
            foreach ($aSetPermission as $crud => $permission) {
                $oCurrentPermission->setAttribute("{$crud}_p", intval($permission));
            }
            if (!$oCurrentPermission->save()) {
                $success = false;
                App()->setFlashMessage(CHtml::errorSummary($oCurrentPermission), 'warning');
            }
        }
        Permission::setMinimalEntityPermission($userId, $this->model->getPrimaryKey(), get_class($this->model));
        return $success;
    }

    /**
     * get the current permission
     * To be mocked for test
     * @return boolean
     */
    public function getCurrentPermission($sPermission, $crud, $userId)
    {
        if (empty($this->model)) {
            return false;
        }
        return $this->model->hasPermission($sPermission, $crud, $userId);
    }

    /**
     * Set a new DB permission
     * To be mocked for test
     * @return \Permission
     */
    public function setDbPermission($entityName, $entityId, $userId, $sPermission)
    {
        $oPermission = new Permission();
        $oPermission->entity = $entityName;
        $oPermission->entity_id = $entityId;
        $oPermission->uid = $userId;
        $oPermission->permission = $sPermission;
        return $oPermission;
    }

    /**
     * Get DB permission
     * To be mocked for test
     * @return \Permission
     */
    public function getDbPermission($entityName, $entityId, $userId, $sPermission)
    {
        $oPermission =  Permission::model()->find(
            "entity = :entity AND entity_id = :entity_id AND uid = :uid AND permission = :permission",
            array(
                ":entity" => $entityName,
                ":entity_id" => $entityId,
                ":uid" => $userId,
                ":permission" => $sPermission
            )
        );
        return $oPermission;
    }
}
