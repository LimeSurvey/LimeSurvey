<?php

namespace LimeSurvey\Api\Command\Mixin\Auth;

use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;

trait AuthSession
{
    private $apiSession = null;

    public function setApiSession(ApiSession $apiSession)
    {
        $this->apiSession = $apiSession;
    }

    protected function getApiSession(): ApiSession
    {
        if (!$this->apiSession) {
            $this->apiSession = new ApiSession();
        }

        return $this->apiSession;
    }

    protected function checkKey($sSessionKey)
    {
        if ($this->getApiSession()->checkKey($sSessionKey)) {
            return true;
        } else {
            return new Response(
                array('status' => ApiSession::INVALID_SESSION_KEY),
                new StatusErrorUnauthorised()
            );
        }
    }
}
