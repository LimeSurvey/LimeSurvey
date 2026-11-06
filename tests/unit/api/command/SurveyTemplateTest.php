<?php

namespace ls\tests\unit\api\command;

use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LimeSurvey\Api\Command\Response\Status\StatusErrorForbidden;
use LimeSurvey\Api\Command\V1\SurveyArchive;
use LimeSurvey\Api\Command\V1\SurveyTemplate;
use ls\tests\TestBaseClass;
use ls\tests\unit\api\command\mixin\AssertResponse;
use Mockery;
use Permission;
use ReflectionMethod;
use Survey;
use SurveyLanguageSetting;
use Yii;

/**
 * Tests for the REST survey template and survey archives commands
 */
class SurveyTemplateTest extends TestBaseClass
{
    use AssertResponse;

    /** @var mixed Public URL config before the test */
    private $publicUrl;

    /** @var string Request host info before the test */
    private $hostInfo;

    /** @var string Request base URL before the test */
    private $baseUrl;

    /**
     * Saves the URL settings changed by the tests
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->publicUrl = Yii::app()->getConfig('publicurl');
        $this->hostInfo = Yii::app()->getRequest()->getHostInfo();
        $this->baseUrl = Yii::app()->getRequest()->getBaseUrl();
    }

    /**
     * Restores the URL settings changed by the tests
     *
     * @return void
     */
    public function tearDown(): void
    {
        Yii::app()->setConfig('publicurl', $this->publicUrl);
        Yii::app()->getRequest()->setHostInfo($this->hostInfo);
        Yii::app()->getRequest()->setBaseUrl($this->baseUrl);
        parent::tearDown();
    }

    /**
     * A user without survey permission must get "forbidden" from the survey template command
     *
     * @return void
     */
    public function testSurveyTemplateRequiresSurveyPermission(): void
    {
        $command = new SurveyTemplate(
            new ResponseFactory(),
            Yii::app()->session,
            Survey::model(),
            SurveyLanguageSetting::model()
        );
        $command->setPermissionModel($this->getPermissionModelWithoutPermissions());

        $response = $command->run(new Request(['_id' => 123456]));

        $this->assertResponseStatus($response, new StatusErrorForbidden());
    }

    /**
     * A user without survey permission must get "forbidden" from the survey archives command
     *
     * @return void
     */
    public function testSurveyArchiveRequiresSurveyPermission(): void
    {
        $command = new SurveyArchive(
            Yii::app()->session,
            new ResponseFactory(),
            Survey::model()
        );
        $command->setPermissionModel($this->getPermissionModelWithoutPermissions());

        $response = $command->run(new Request(['_id' => 123456]));

        $this->assertResponseStatus($response, new StatusErrorForbidden());
    }

    /**
     * The configured publicurl is used for the internal request, whatever the request host is
     *
     * @return void
     */
    public function testTrustedBaseUrlUsesPublicUrl(): void
    {
        Yii::app()->setConfig('publicurl', 'http://localhost:8082/limesurvey/');
        $this->setRequestUrl('http://localhost:9098', '/other');

        $this->assertSame('http://localhost:8082/limesurvey', $this->getTrustedBaseUrl());
    }

    /**
     * Without publicurl, the request URL is used when it has no port or the default port
     *
     * @dataProvider defaultPortProvider
     * @param string $hostInfo
     * @return void
     */
    public function testTrustedBaseUrlAcceptsDefaultPort(string $hostInfo): void
    {
        Yii::app()->setConfig('publicurl', '');
        $this->setRequestUrl($hostInfo, '/limesurvey');

        $this->assertSame($hostInfo . '/limesurvey', $this->getTrustedBaseUrl());
    }

    /**
     * Data provider for testTrustedBaseUrlAcceptsDefaultPort
     *
     * @return array
     */
    public function defaultPortProvider(): array
    {
        return [
            'http without port' => ['http://localhost'],
            'https without port' => ['https://localhost'],
            'http with port 80' => ['http://localhost:80'],
            'https with port 443' => ['https://localhost:443'],
        ];
    }

    /**
     * Without publicurl, a port taken from the Host header must never be requested
     *
     * @dataProvider otherPortProvider
     * @param string $hostInfo
     * @return void
     */
    public function testTrustedBaseUrlRejectsOtherPort(string $hostInfo): void
    {
        Yii::app()->setConfig('publicurl', '');
        $this->setRequestUrl($hostInfo, '');

        $this->assertNull($this->getTrustedBaseUrl());
    }

    /**
     * Data provider for testTrustedBaseUrlRejectsOtherPort
     *
     * @return array
     */
    public function otherPortProvider(): array
    {
        return [
            'http with other port' => ['http://localhost:9098'],
            'http with https port' => ['http://localhost:443'],
            'https with http port' => ['https://localhost:80'],
        ];
    }

    /**
     * Returns a permission model that denies every permission
     *
     * @return Permission
     */
    private function getPermissionModelWithoutPermissions(): Permission
    {
        $permission = Mockery::mock(Permission::class);
        $permission->shouldReceive('hasSurveyPermission')->andReturn(false);
        $permission->shouldReceive('hasGlobalPermission')->andReturn(false);
        return $permission;
    }

    /**
     * Sets the host info and base URL of the current request
     *
     * @param string $hostInfo
     * @param string $baseUrl
     * @return void
     */
    private function setRequestUrl(string $hostInfo, string $baseUrl): void
    {
        Yii::app()->getRequest()->setHostInfo($hostInfo);
        Yii::app()->getRequest()->setBaseUrl($baseUrl);
    }

    /**
     * Calls SurveyTemplate::getTrustedBaseUrl()
     *
     * @return string|null
     */
    private function getTrustedBaseUrl(): ?string
    {
        $command = new SurveyTemplate(
            new ResponseFactory(),
            Yii::app()->session,
            Survey::model(),
            SurveyLanguageSetting::model()
        );
        $method = new ReflectionMethod(SurveyTemplate::class, 'getTrustedBaseUrl');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }
        return $method->invoke($command);
    }
}
