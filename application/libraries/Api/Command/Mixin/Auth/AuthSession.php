<?php

namespace LimeSurvey\Api\Command\Mixin\Auth;

use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;

trait AuthSession
{
    protected function checkKey($sSessionKey)
    {
        $apiSession = new ApiSession();
        if ($apiSession->checkKey($sSessionKey)) {
            return true;
        } else {
            return new Response(
                array('status' => ApiSession::INVALID_SESSION_KEY),
                new StatusErrorUnauthorised()
            );
        }
    }
}
