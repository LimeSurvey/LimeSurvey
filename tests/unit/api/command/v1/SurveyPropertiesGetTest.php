<?php

namespace ls\tests\unit\api\command\v1;

use Permission;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\SurveyPropertiesGet;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\Command\Response\Status\StatusErrorNotFound;
use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use Survey;
use Mockery;

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

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $command = new SurveyPropertiesGet();
        $command->setAuthSession($mockAuthSession);

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

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'surveysettings', 'read', null)
            ->andReturns(false);

        $command = new SurveyPropertiesGet();
        $command->setAuthSession($mockAuthSession);
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

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'surveysettings', 'read', null)
            ->andReturns(true);

        $command = new SurveyPropertiesGet();
        $command->setAuthSession($mockAuthSession);
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

        $mockAuthSession= Mockery::mock(AuthSession::class);
        $mockAuthSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasSurveyPermission(0, 'surveysettings', 'read', null)
            ->andReturns(true);

        $command = new SurveyPropertiesGet();
        $command->setAuthSession($mockAuthSession);
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
