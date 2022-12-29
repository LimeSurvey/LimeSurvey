<?php

namespace LimeSurvey\Api\Command\Mixin\Auth;

use Permission;
use LimeSurvey\Api\Command\Response\{
    Response,
    Status\StatusErrorUnauthorised
};

trait AuthPermissionTrait
{
    private $permissionModel = null;

    public function setPermissionModel(Permission $permissionModel)
    {
        $this->permissionModel = $permissionModel;
    }

    protected function getPermissionModel(): Permission
    {
        if (!$this->permissionModel) {
            $this->permissionModel = Permission::model();
        }

        return $this->permissionModel;
    }

    protected function hasGlobalPermission($sPermission, $sCRUD, $iUserID = null)
    {
        $result =
            $this->getPermissionModel()->hasGlobalPermission(
                $sPermission,
                $sCRUD,
                $iUserID
            );

        if ($result) {
            return true;
        } else {
            return new Response(
                array('status' => 'No permission'),
                new StatusErrorUnauthorised()
            );
        }
    }

    protected function hasSurveyPermission($iSurveyID, $sPermission, $sCRUD, $iUserID = null)
    {
        $result =
            $this->getPermissionModel()->hasSurveyPermission(
                $iSurveyID,
                $sPermission,
                $sCRUD,
                $iUserID
            );

        if ($result) {
            return true;
        } else {
            return new Response(
                array('status' => 'No permission'),
                new StatusErrorUnauthorised()
            );
        }
    }
}
