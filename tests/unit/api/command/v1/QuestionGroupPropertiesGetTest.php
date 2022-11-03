<?php

namespace ls\tests\unit\api\command\v1;

use Permission;
use QuestionGroup;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\QuestionGroupPropertiesGet;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;
use Mockery;

/**
 * @testdox API command v1 QuestionGroupPropertiesGet.
 *
 */
class QuestionGroupPropertiesGetTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * @testdox Returns invalid session response (error unauthorised) if session key is not valid.
     */
    public function testQuestionGroupPropertiesGetInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'groupID' => 'groupID',
            'groupSettings' => 'groupSettings',
            'language' => 'language'
        ));
        $response = (new QuestionGroupPropertiesGet())->run($request);

        $this->assertResponseInvalidSession($response);

        $this->assertResponseDataStatus(
            $response,
            'Invalid session key'
        );
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

        $mockApiSession= Mockery::mock(ApiSession::class);
        $mockApiSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $command = new QuestionGroupPropertiesGet();
        $command->setApiSession($mockApiSession);

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
     * @testdox Returns invalid session response (error unauthorised) user does not have permission.
     */
    public function testQuestionGroupPropertiesGetNoPermission()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'groupID' => 'mock',
            'groupSettings' => 'groupSettings',
            'language' => 'language'
        ));

        $mockApiSession= Mockery::mock(ApiSession::class);
        $mockApiSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockQuestionGroupModel= $this->createStub(QuestionGroup::class);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'survey', 'read', null)
            ->andReturns(false);

        $command = new QuestionGroupPropertiesGet();
        $command->setApiSession($mockApiSession);
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

    /**
     * @testdox Returns error bad request if language is not valid.
     */
    public function testQuestionGroupPropertiesGetInvalidLanguage()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'groupID' => 'mock',
            'groupSettings' => 'groupSettings',
            'language' => 'invalid-language'
        ));

        $mockApiSession= Mockery::mock(ApiSession::class);
        $mockApiSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockQuestionGroupModel= $this->createStub(QuestionGroup::class);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'survey', 'read', null)
            ->andReturns(true);

        $command = new QuestionGroupPropertiesGet();
        $command->setApiSession($mockApiSession);
        $command->setQuestionGroupModelWithL10nsById($mockQuestionGroupModel);
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
     * @testdox Returns error bad-request if no valid settings specified.
     */
    public function testQuestionGroupPropertiesGetNoValidSettingsSpecified()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'groupID' => 'mock',
            'groupSettings' => array('invalid-setting'),
            'language' => 'en'
        ));

        $mockApiSession= Mockery::mock(ApiSession::class);
        $mockApiSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockQuestionGroupModel= $this->createStub(QuestionGroup::class);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'survey', 'read', null)
            ->andReturns(true);

        $command = new QuestionGroupPropertiesGet();
        $command->setApiSession($mockApiSession);
        $command->setQuestionGroupModelWithL10nsById($mockQuestionGroupModel);
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

    /**
     * @testdox Returns success with default settings if no settings specified.
     */
    public function testQuestionGroupPropertiesGetDefaultSettings()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'groupID' => 'mock',
            'groupSettings' => array(),
            'language' => 'en'
        ));

        $mockApiSession= Mockery::mock(ApiSession::class);
        $mockApiSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockQuestionGroupModel= $this->createStub(QuestionGroup::class);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'survey', 'read', null)
            ->andReturns(true);

        $command = new QuestionGroupPropertiesGet();
        $command->setApiSession($mockApiSession);
        $command->setQuestionGroupModelWithL10nsById($mockQuestionGroupModel);
        $command->setPermissionModel($mockModelPermission);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusSuccess()
        );
    }
}
