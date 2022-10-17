<?php

namespace ls\tests\unit\api\command\v1;

use Eloquent\Phony\Phpunit\Phony;
use Permission;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\SurveyPropertiesGet;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\ApiSession;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use Survey;

/**
 * @testdox API command v1 SurveyPropertiesGetTest.
 */
class SurveyPropertiesGetTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * @testdox Returns invalid session response (error unauthorised) if session key is not valid.
     */
    public function testSurveyDeleteInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'surveyID' => 'surveyID',
            'surveySettings' => array()
        ));
        $response = (new SurveyPropertiesGet())->run($request);

        $this->assertResponseInvalidSession($response);
    }

    /**
     * @testdox Returns error not-found if survey id is not valid.
     */
    public function testSurveyDeleteInvalidSurveyId()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'surveyID' => 'invalid',
            'surveySettings' => array()
        ));

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $command = new SurveyPropertiesGet();
        $command->setApiSession($mockApiSession);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorNotFound()
        );

        $this->assertResponseDataStatus(
            $response,
            'Error: Invalid survey ID'
        );
    }

    /**
     * @testdox Returns invalid session response (error unauthorised) user does not have permission.
     */
    public function testSurveyDeleteNoPermission()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'surveyID' => 'mock',
            'surveySettings' => array()
        ));

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $mockModelPermissionHandle = Phony::mock(Permission::class);
        $mockModelPermissionHandle->hasSurveyPermission
            ->returns(false);
        $mockModelPermission = $mockModelPermissionHandle->get();

        $command = new SurveyPropertiesGet();
        $command->setApiSession($mockApiSession);
        $command->setPermissionModel($mockModelPermission);
        $command->setSurveyModel(new Survey());

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
     * @testdox Returns error bad-request if no valid data settings.
     */
    public function testQuestionGroupPropertiesGetNoValidSettingsSpecified()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'surveyID' => 'mock',
            'surveySettings' => array('invalid')
        ));

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $mockModelPermissionHandle = Phony::mock(Permission::class);
        $mockModelPermissionHandle->hasSurveyPermission
            ->returns(true);
        $mockModelPermission = $mockModelPermissionHandle->get();

        $command = new SurveyPropertiesGet();
        $command->setApiSession($mockApiSession);
        $command->setPermissionModel($mockModelPermission);
        $command->setSurveyModel(new Survey());

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
     * @testdox Returns success.
     */
    public function testQuestionGroupPropertiesGetSuccess()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'surveyID' => 'mock',
            'surveySettings' => array()
        ));

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $mockModelPermissionHandle = Phony::mock(Permission::class);
        $mockModelPermissionHandle->hasSurveyPermission
            ->returns(true);
        $mockModelPermission = $mockModelPermissionHandle->get();

        $command = new SurveyPropertiesGet();
        $command->setApiSession($mockApiSession);
        $command->setPermissionModel($mockModelPermission);
        $command->setSurveyModel(new Survey());

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusSuccess()
        );
        $this->assertTrue(
            is_array($response->getData())
            && count($response->getData()) > 10
        );
    }
}
