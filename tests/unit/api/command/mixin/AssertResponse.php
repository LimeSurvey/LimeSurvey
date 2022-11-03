<?php

namespace ls\tests\unit\api\command\mixin;

use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusAbstract;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;

/**
 *
 */
trait AssertResponse
{
    protected function assertResponseStatus(Response $response, StatusAbstract $status)
    {
        $this->assertEquals(
            $response->getStatus()->getCode(),
            $status->getCode()
        );
    }

    protected function assertResponseDataStatus(Response $response, $status)
    {
        $this->assertEquals(
            array('status' => $status),
            $response->getData()
        );
    }

    protected function assertResponseInvalidSession(Response $response)
    {
        $this->assertResponseStatus(
            $response,
            new StatusErrorUnauthorised()
        );

        $this->assertResponseDataStatus(
            $response,
            ApiSession::INVALID_SESSION_KEY
        );
    }
}
