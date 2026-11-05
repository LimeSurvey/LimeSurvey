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
     * @param string $sPermission
     * @param string $sCRUD
     * @param ?int $iUserID
     * @return bool true if the user has the global permission
     */
    protected function hasGlobalPermission($sPermission, $sCRUD, $iUserID = null): bool
    {
        return $this->getPermissionModel()->hasGlobalPermission(
            $sPermission,
            $sCRUD,
            $iUserID
        );
    }

    /**
     * @param int $iSurveyID
     * @param string $sPermission
     * @param string $sCRUD
     * @param ?int $iUserID
     * @return bool true if the user has the survey permission
     */
    protected function hasSurveyPermission($iSurveyID, $sPermission, $sCRUD, $iUserID = null): bool
    {
        return $this->getPermissionModel()->hasSurveyPermission(
            $iSurveyID,
            $sPermission,
            $sCRUD,
            $iUserID
        );
    }
}
