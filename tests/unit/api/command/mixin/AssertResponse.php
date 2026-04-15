<?php

namespace ls\tests\unit\api\command\mixin;

use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\Auth\AuthTokenSimple;

/**
 *
 */
trait AssertResponse
{
    protected function assertResponseStatus(Response $response, Status $status)
    {
        $this->assertEquals(
            $response->getStatus()->getCode(),
            $status->getCode()
        );
    }

    protected function assertResponseDataStatus(Response $response, $status)
    {
        $data = $response->getData();

        // New V2 style error response data
        $code = is_array($data)
            && !empty($data['error'])
            && !empty($data['error']['code'])
            ? $data['error']['code'] : null;

        // Old V1 style error response data
        if (is_null($code) && !empty($data['status'])) {
            $code = $data['status'];
        }

        $this->assertEquals(
            $status,
            $code
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
            AuthTokenSimple::ERROR_INVALID_SESSION_KEY
        );
    }
}
