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
     * Check whether a user has a global permission
     *
     * @param string $permission Permission name, e.g. 'surveys'
     * @param string $crud One of 'create', 'read', 'update', 'delete', 'import', 'export'
     * @param ?int $userId User id, defaults to the current user
     * @return bool true if the user has the global permission
     */
    protected function hasGlobalPermission($permission, $crud, $userId = null): bool
    {
        return $this->getPermissionModel()->hasGlobalPermission(
            $permission,
            $crud,
            $userId
        );
    }

    /**
     * Check whether a user has a permission on a survey
     *
     * @param int $surveyId Survey id
     * @param string $permission Permission name, e.g. 'surveycontent'
     * @param string $crud One of 'create', 'read', 'update', 'delete', 'import', 'export'
     * @param ?int $userId User id, defaults to the current user
     * @return bool true if the user has the survey permission
     */
    protected function hasSurveyPermission($surveyId, $permission, $crud, $userId = null): bool
    {
        return $this->getPermissionModel()->hasSurveyPermission(
            $surveyId,
            $permission,
            $crud,
            $userId
        );
    }
}
