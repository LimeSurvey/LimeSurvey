<?php

namespace LimeSurvey\Api\Command\Mixin\Auth;

use Permission;

trait AuthPermissionTrait
{
    /**
     * @var ?Permission
     */
    private $permissionModel = null;


    /**
     * @return void
     */
    public function setPermissionModel(Permission $permissionModel)
    {
        $this->permissionModel = $permissionModel;
    }

    /**
     * @return Permission
     */
    protected function getPermissionModel(): Permission
    {
        if (!isset($this->permissionModel)) {
            $this->permissionModel = Permission::model();
        }

        return $this->permissionModel;
    }

    /**
     * Check a global permission of the current (or given) user
     *
     * @param string $permission The permission name, e.g. 'surveys'
     * @param string $crud The permission action, e.g. 'read'
     * @param ?int $userId The user ID, null for the current user
     * @return bool True if the user has the permission, false otherwise
     */
    protected function hasGlobalPermission($permission, $crud, $userId = null): bool
    {
        return (bool) $this->getPermissionModel()->hasGlobalPermission(
            $permission,
            $crud,
            $userId
        );
    }

    /**
     * Check a survey permission of the current (or given) user
     *
     * @param int $surveyId The survey ID
     * @param string $permission The permission name, e.g. 'surveycontent'
     * @param string $crud The permission action, e.g. 'read'
     * @param ?int $userId The user ID, null for the current user
     * @return bool True if the user has the permission, false otherwise
     */
    protected function hasSurveyPermission($surveyId, $permission, $crud, $userId = null): bool
    {
        return (bool) $this->getPermissionModel()->hasSurveyPermission(
            $surveyId,
            $permission,
            $crud,
            $userId
        );
    }
}
