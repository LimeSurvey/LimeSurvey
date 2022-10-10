<?php

namespace LimeSurvey\Api\Command\Mixin\Auth;

use Permission;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;

trait AuthGlobalPermission
{
    protected function hasGlobalPermission($sPermission, $sCRUD, $iUserID = null)
    {
        $result =
            Permission::model()
            ->hasGlobalPermission(
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
