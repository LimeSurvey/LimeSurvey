<?php

namespace ls\tests\unit\api\command\v1;

use Permission;
use Question;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\QuestionPropertiesSet;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\Auth\AuthSession;
use Mockery;

/**
 * @testdox API command v1 QuestionPropertiesSet.
 */
class QuestionPropertiesSetTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * @testdox Returns invalid session response (error unauthorised) if session key is not valid.
     */
    public function testQuestionListInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'questionID' => 'questionID',
            'questionData' => 'questionData',
            'language' => 'language'
        ));
        $response = (new QuestionPropertiesSet())->run($request);

        $this->assertResponseInvalidSession($response);
    }


    /**
     * @testdox Returns error not-found if question id is not valid.
     */
    public function testQuestionPropertiesGetInvalidQuestionId()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'questionID' => 'questionID',
            'questionData' => 'questionData',
            'language' => 'language'
        ));

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $command = new QuestionPropertiesSet();
        $command->setAuthSession($mockAuthSession);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorNotFound()
        );

        $this->assertResponseDataStatus(
            $response,
            'Error: Invalid group ID'
        );
    }

    /**
     * @testdox Returns error unauthorised if user does not have permission.
     */
    public function testQuestionPropertiesSetNoPermission()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'questionID' => 'mock',
            'questionData' => 'questionData',
            'language' => 'language'
        ));

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockModelQuestion= $this->createStub(Question::class);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(null, 'survey', 'update', null)
            ->andReturns(false);

        $command = new QuestionPropertiesSet();
        $command->setAuthSession($mockAuthSession);
        $command->setPermissionModel($mockModelPermission);
        $command->setQuestionModel($mockModelQuestion);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorUnauthorised()
        );

        $this->assertResponseDataStatus(
            $response,
            'No permission'
        );
    }

    /**
     * @testdox Returns error bad request if invalid language specified.
     */
    public function testQuestionPropertiesSetInvalidLanguage()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'questionID' => 'mock',
            'questionData' => 'questionData',
            'language' => 'invalid'
        ));

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockQuestion= $this->createStub(Question::class);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(null, 'survey', 'update', null)
            ->andReturns(true);

        $command = new QuestionPropertiesSet();
        $command->setAuthSession($mockAuthSession);
        $command->setQuestionModel($mockQuestion);
        $command->setPermissionModel($mockModelPermission);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorBadRequest()
        );

        $this->assertResponseDataStatus(
            $response,
            'Error: Invalid language'
        );
    }

    /**
     * @testdox Returns error bad-request no data provided.
     */
    public function testQuestionPropertiesGetInvalidSettings()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'questionID' => 'mock',
            'questionData' => array(),
            'language' => 'en'
        ));

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockQuestion= $this->createStub(Question::class);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(null, 'survey', 'update', null)
            ->andReturns(true);

        $command = new QuestionPropertiesSet();
        $command->setAuthSession($mockAuthSession);
        $command->setQuestionModel($mockQuestion);
        $command->setPermissionModel($mockModelPermission);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorBadRequest()
        );

        $this->assertResponseDataStatus(
            $response,
            'No valid Data'
        );
    }
}
