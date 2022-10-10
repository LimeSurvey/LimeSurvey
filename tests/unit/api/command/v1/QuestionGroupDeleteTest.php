<?php

namespace ls\tests\api\command\v1;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Command\V1\QuestionGroupDelete;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;

/**
 * Tests for the API command v1 QuestionGroupDelete.
 */
class QuestionGroupDeleteTest extends TestBaseClass
{
    public function testQuestionGroupDeleteInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'groupID' => 'groupID',
        ));
        $response = (new QuestionGroupDelete)->run($request);

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
