<?php

namespace ls\tests\unit\api\command\v1;

use Permission;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\SiteSettingsGet;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\ApiSession;
use Mockery;

/**
 * @testdox API command v1 SiteSettingsGet.
 */
class SiteSettingsGetTest extends TestBaseClass
{
    use AssertResponse;

    /**
     * @testdox Returns invalid session response (error unauthorised) if session key is not valid.
     */
    public function testSiteSettingsGetInvalidSession()
    {
        $request = new Request(array(
            'sessionKey' => 'not-a-valid-session-id',
            'settingName' => 'settingName'
        ));
        $response = (new SiteSettingsGet())->run($request);

        $this->assertResponseInvalidSession($response);
    }

    /**
     * @testdox Returns error unauthorised if user does not have permission.
     */
    public function testSiteSettingsGetNoPermission()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'settingName' => 'settingName'
        ));

        $mockApiSession= Mockery::mock(ApiSession::class);
        $mockApiSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasGlobalPermission('superadmin', 'read', null)
            ->andReturns(false);

        $command = new SiteSettingsGet();
        $command->setApiSession($mockApiSession);
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
     * @testdox Returns error bad-request setting name invalid.
     */
    public function testSiteSettingsGetInvalidSetting()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'settingName' => 'invalid-setting-name'
        ));

        $mockApiSession= Mockery::mock(ApiSession::class);
        $mockApiSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasGlobalPermission('superadmin', 'read', null)
            ->andReturns(true);

        $command = new SiteSettingsGet();
        $command->setApiSession($mockApiSession);
        $command->setPermissionModel($mockModelPermission);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusErrorBadRequest()
        );

        $this->assertResponseDataStatus(
            $response,
            'Invalid setting'
        );
    }

    /**
     * @testdox Returns success with setting value.
     */
    public function testSiteSettingsGetSuccessWithValue()
    {
        $request = new Request(array(
            'sessionKey' => 'mock',
            'settingName' => 'invalid-setting-name'
        ));

        $mockApiSession= Mockery::mock(ApiSession::class);
        $mockApiSession
            ->allows()
            ->checkKey('mock')
            ->andReturns(true);

        $mockModelPermission= Mockery::mock(Permission::class);
        $mockModelPermission
            ->allows()
            ->hasGlobalPermission('superadmin', 'read', null)
            ->andReturns(true);

        $mockApp = Mockery::mock(LSYii_Application::class);
        $mockApp
            ->allows()
            ->getConfig('invalid-setting-name')
            ->andReturns(true);

        $command = new SiteSettingsGet();
        $command->setApiSession($mockApiSession);
        $command->setPermissionModel($mockModelPermission);
        $command->setApp($mockApp);

        $response = $command->run($request);

        $this->assertResponseStatus(
            $response,
            new StatusSuccess()
        );

        $this->assertEquals(
            true,
            $response->getData()
        );
    }
}
