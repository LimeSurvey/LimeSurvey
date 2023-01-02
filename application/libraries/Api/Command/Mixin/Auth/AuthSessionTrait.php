<?php

namespace LimeSurvey\Api\Command\Mixin\Auth;

use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\ResponseData\ResponseDataError;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\Auth\AuthSession;

trait AuthSessionTrait
{
    private $authSession = null;

    public function setAuthSession(AuthSession $authSession)
    {
        $this->authSession = $authSession;
    }

    protected function getAuthSession(): AuthSession
    {
        if (!$this->authSession) {
            $this->authSession = new AuthSession();
        }

        return $this->authSession;
    }

    protected function checkKey($sSessionKey)
    {
        if ($this->getAuthSession()->checkKey($sSessionKey)) {
            return true;
        } else {
            return new Response(
                (new ResponseDataError(
                    AuthSession::ERROR_INVALID_SESSION_KEY,
                    'Invalid session key'
                ))->toArray(),
                new StatusErrorUnauthorised()
            );
        }
    }
}
