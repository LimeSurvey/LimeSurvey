<?php

namespace LimeSurvey\Api\Command\Mixin\Auth;

use Permission;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;

trait AuthSurveyPermission
{
    protected function hasSurveyPermission($iSurveyID, $sPermission, $sCRUD, $iUserID = null)
    {
        $result =
            Permission::model()
            ->hasSurveyPermission(
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
