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
use LSYii_Application;
use Permission;
use PermissionInterface;
use App;
use CHtml;
use LimeSurvey\PluginManager\PluginEvent;

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

    /** @var LSYii_Application */
    private $app;

    /**
     * @param LSHttpRequest $request
     * @param LSWebUser $user
     */
    public function __construct(
        LSHttpRequest $request,
        LSWebUser $user,
        PermissionInterface $model,
        LSYii_Application $app
    ) {
        $this->request = $request;
        $this->user = $user;
        $this->model = $model;
        $this->app = $app;
    }

    /**
     * get the permission data
     * @param int|null $userId for this user id
     * @return array
     */
    public function getPermissionData($userId = null)
    {
        $aObjectPermissions = $this->model::getPermissionData(); // Usage of static, db not needed
        if (empty($aObjectPermissions)) {
            return $aObjectPermissions;
        }
        /** @var string[] Crud type array */
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
                    'indeterminate' => false,
                    'forced' => false,
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
                        if ($sPermission == $this->model->getMinimalPermissionRead() && $crud == 'read') {
                            $aObjectPermissions[$sPermission]['current'][$crud]['forced'] = true;
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
     *
     * @see Permission::setPermissions
     * @param int $userId
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

        /** @var array<string, array<string, string>> The array to set (or not) */
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
        $oEvent = $this->getNewEvent('beforePermissionSetSave');
        $oEvent->set('aNewPermissions', $aSetPermissions);
        if (get_class($this->model) == 'Survey') {
            $oEvent->set('iSurveyID', $this->model->getPrimaryKey());
        }
        $oEvent->set('entity', get_class($this->model)); /* New in 4.4.X */
        $oEvent->set('entityId', $this->model->getPrimaryKey()); /* New in 4.4.X */
        $oEvent->set('iUserID', $userId);
        $this->app->getPluginManager()->dispatchEvent($oEvent);

        foreach ($aSetPermissions as $sPermission => $aSetPermission) {
            $success = $success && $this->applyPermissions($userId, $sPermission, $aSetPermission);
        }
        $this->setMinimalEntityPermission($userId);
        return $success;
    }

    /**
     * @param int $userId
     * @param string $sPermission
     * @param array $aSetPermission
     * @return bool
     */
    public function applyPermissions($userId, $sPermission, $aSetPermission)
    {
        $success = true;
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
            $this->app->setFlashMessage(CHtml::errorSummary($oCurrentPermission), 'warning');
        }
        return $success;
    }

    /**
     * get the current permission
     * To be mocked for test
     *
     * @param string $sPermission
     * @param string $crud
     * @param int $userId
     * @return boolean
     */
    public function getCurrentPermission($sPermission, $crud, $userId)
    {
        return $this->model->hasPermission($sPermission, $crud, $userId);
    }

    /**
     * Set a new DB permission
     * To be mocked for test
     *
     * @param string $entityName
     * @param int $entityId
     * @param int $userId
     * @param string $sPermission
     * @return \Permission
     */
    public function setDbPermission($entityName, $entityId, $userId, $sPermission)
    {
        $oPermission = $this->getNewPermission();
        $oPermission->entity = $entityName;
        $oPermission->entity_id = $entityId;
        $oPermission->uid = $userId;
        $oPermission->permission = $sPermission;
        return $oPermission;
    }

    /**
     * Get DB permission
     * To be mocked for test
     *
     * @param string $entityName
     * @param int $entityId
     * @param string|int $userId
     * @param string $sPermission
     * @return \Permission|null
     */
    public function getDbPermission($entityName, $entityId, $userId, $sPermission)
    {
        $oPermission =  Permission::model()->find(
            "entity = :entity AND entity_id = :entity_id AND uid = :uid AND permission = :permission",
            array(
                ":entity" => strtolower($entityName),
                ":entity_id" => $entityId,
                ":uid" => $userId,
                ":permission" => $sPermission
            )
        );
        return $oPermission;
    }

    /**
     * @param string $eventName
     * @return PluginEvent
     */
    public function getNewEvent($eventName)
    {
        return new PluginEvent($eventName);
    }

    /**
     * @param int $userId
     * @return void
     */
    public function setMinimalEntityPermission($userId)
    {
        // TODO: Static methods cannot be mocked.
        Permission::setMinimalEntityPermission((int) $userId, $this->model->getPrimaryKey(), get_class($this->model));
    }

    /**
     * @return Permission
     */
    public function getNewPermission()
    {
        return new Permission();
    }
}
