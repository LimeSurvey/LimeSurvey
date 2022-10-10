<?php

namespace ls\tests\api\command\v1;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Command\V1\QuestionGroupAdd;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;

/**
 * Tests for the API command v1 QuestionGroupAdd.
 */
class QuestionGroupAddTest extends TestBaseClass
{
    public function testQuestionGroupAddInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'sessionKey' => 'surveyID',
            'groupTitle' => 'groupTitle',
            'groupDescription' => 'groupDescription',
        ));
        $response = (new QuestionGroupAdd)->run($request);

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
