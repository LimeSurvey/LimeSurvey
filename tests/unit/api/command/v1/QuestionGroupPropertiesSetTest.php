<?php

namespace ls\tests\unit\api\command\v1;

use Permission;
use QuestionGroup;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\QuestionGroupPropertiesSet;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\Auth\AuthSession;
use Mockery;

/**
 * @testdox API command v1 QuestionGroupPropertiesSet.
 */
class QuestionGroupPropertiesSetTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * @testdox Returns invalid session response (error unauthorised) if session key is not valid.
     */
    public function testQuestionGroupPropertiesSetInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'groupID' => 'groupID',
            'groupSettings' => 'groupData'
        ));
        $response = (new QuestionGroupPropertiesSet())->run($request);

        $this->assertResponseInvalidSession($response);
    }

    /**
     * @testdox Returns error not-found if group id is not valid.
     */
    public function testQuestionGroupPropertiesGetInvalidGroupId()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'groupID' => 'no-found',
            'groupSettings' => 'groupSettings',
            'language' => 'language'
        ));

        $mockAuthSession = Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $command = new QuestionGroupPropertiesSet();
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
     * @testdox Returns invalid session response (error unauthorised) users does not have permission.
     */
    public function testQuestionGroupPropertiesGetNoPermission()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'groupID' => 'mock',
            'groupSettings' => 'groupSettings',
            'language' => 'language'
        ));

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockQuestionGroupModel= $this->createStub(QuestionGroup::class);

        $mockModelPermission = Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'survey', 'update', null)
            ->andReturns(false);

        $command = new QuestionGroupPropertiesSet();
        $command->setAuthSession($mockAuthSession);
        $command->setQuestionGroupModelWithL10nsById($mockQuestionGroupModel);
        $command->setPermissionModel($mockModelPermission);

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
}
