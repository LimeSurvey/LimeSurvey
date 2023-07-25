<?php

namespace LimeSurvey\Api\Command\Mixin\Auth;

use Permission;
use LimeSurvey\Api\Command\Response\{
    Response,
    Status\StatusErrorUnauthorised
};

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
     * @return Response | boolean
     */
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

    /**
     * @param int $iSurveyID
     * @param string $sPermission
     * @param string $sCRUD
     * @param ?string $iUserID
     * @return Response | boolean
     */
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
