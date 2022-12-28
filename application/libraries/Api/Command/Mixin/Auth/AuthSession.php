<?php

namespace LimeSurvey\Api\Command\Mixin\Auth;

use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\Auth\ApiAuthSession;
use LimeSurvey\Api\Command\ResponseData\ResponseDataError;

trait AuthSession
{
    private $apiAuthSession = null;

    public function setApiAuthSession(ApiAuthSession $apiSession)
    {
        $this->apiAuthSession = $apiSession;
    }

    protected function getApiAuthSession(): ApiAuthSession
    {
        if (!$this->apiAuthSession) {
            $this->apiAuthSession = new ApiAuthSession();
        }

        return $this->apiAuthSession;
    }

    protected function checkKey($sSessionKey)
    {
        if ($this->getApiAuthSession()->checkKey($sSessionKey)) {
            return true;
        } else {
            return new Response(
                (new ResponseDataError(
                    ApiAuthSession::ERROR_INVALID_SESSION_KEY,
                    'Invalid session key'
                ))->toArray(),
                new StatusErrorUnauthorised()
            );
        }
    }
}
