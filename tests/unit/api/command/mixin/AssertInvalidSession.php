<?php

namespace ls\tests\unit\api\command\mixin;

use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;

/**
 *
 */
trait AssertInvalidSession
{
    protected function assertInvalidSession(Response $response)
    {
        $this->assertEquals(
            $response->getStatus()->getCode(),
            (new StatusErrorUnauthorised)->getCode()
        );

        $this->assertEquals(
            $response->getData(),
            array('status' => ApiSession::INVALID_SESSION_KEY)
        );
    }
}
