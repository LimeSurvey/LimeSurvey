<?php

namespace ls\tests\unit\api\command\v1;

use Eloquent\Phony\Phpunit\Phony;
use Permission;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use LimeSurvey\Api\Command\V1\SiteSettingsGet;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Status\StatusErrorUnauthorised;
use LimeSurvey\Api\ApiSession;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;

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

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $mockModelPermissionHandle = Phony::mock(Permission::class);
        $mockModelPermissionHandle->hasGlobalPermission
            ->returns(false);
        $mockModelPermission = $mockModelPermissionHandle->get();

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

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $mockModelPermissionHandle = Phony::mock(Permission::class);
        $mockModelPermissionHandle->hasGlobalPermission
            ->returns(true);
        $mockModelPermission = $mockModelPermissionHandle->get();

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

        $mockApiSessionHandle = Phony::mock(ApiSession::class);
        $mockApiSessionHandle
            ->checkKey
            ->returns(true);
        $mockApiSession = $mockApiSessionHandle->get();

        $mockModelPermissionHandle = Phony::mock(Permission::class);
        $mockModelPermissionHandle->hasGlobalPermission
            ->returns(true);
        $mockModelPermission = $mockModelPermissionHandle->get();

        $mockAppBuilder = Phony::mockBuilder();
        $mockAppBuilder->addMethod(
            'getConfig',
            function ($settingName) {
                return true;
            }
        );
        $mockApp = $mockAppBuilder->partial();

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
