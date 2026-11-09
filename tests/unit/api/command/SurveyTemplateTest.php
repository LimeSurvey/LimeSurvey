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

    /** @var mixed Allowed hosts config before the test */
    private $allowedHosts;

    /**
     * Saves the URL and allowed hosts settings changed by the tests, and sets an allowed hosts list
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        self::$testHelper::saveUrlSettings();
        $this->allowedHosts = Yii::app()->getConfig('allowedHosts');
        Yii::app()->setConfig('allowedHosts', ['localhost']);
    }

    /**
     * Restores the URL and allowed hosts settings changed by the tests
     *
     * @return void
     */
    public function tearDown(): void
    {
        Yii::app()->setConfig('allowedHosts', $this->allowedHosts);
        self::$testHelper::resetUrlSettings();
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
     * A publicurl that is not a plain http(s) base URL is not used
     *
     * @dataProvider untrustedPublicUrlProvider
     * @param string $publicUrl The publicurl config value
     * @return void
     */
    public function testTrustedBaseUrlRejectsUntrustedPublicUrl(string $publicUrl): void
    {
        Yii::app()->setConfig('publicurl', $publicUrl);
        $this->setRequestUrl('http://localhost', '/limesurvey');

        $this->assertNull($this->getTrustedBaseUrl());
    }

    /**
     * Data provider for testTrustedBaseUrlRejectsUntrustedPublicUrl
     *
     * @return array<string, array{string}> Test case name => publicurl
     */
    public function untrustedPublicUrlProvider(): array
    {
        return [
            'other scheme' => ['ftp://localhost/limesurvey'],
            'with user' => ['http://user@localhost/limesurvey'],
            'with query' => ['http://localhost/limesurvey?a=b'],
            'with fragment' => ['http://localhost/limesurvey#a'],
        ];
    }

    /**
     * Without publicurl, the request URL is used when it has no port or the default port
     *
     * @dataProvider defaultPortProvider
     * @param string $hostInfo The request host info, e.g. "http://localhost"
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
     * @return array<string, array{string}> Test case name => request host info
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
     * @param string $hostInfo The request host info, e.g. "http://localhost:9098"
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
     * @return array<string, array{string}> Test case name => request host info
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
     * Without publicurl and without an allowed hosts list, the request host is not checked, so it is never used
     *
     * @return void
     */
    public function testTrustedBaseUrlRequiresAllowedHostsWithoutPublicUrl(): void
    {
        Yii::app()->setConfig('publicurl', '');
        Yii::app()->setConfig('allowedHosts', []);
        $this->setRequestUrl('http://localhost', '/limesurvey');

        $this->assertNull($this->getTrustedBaseUrl());
    }

    /**
     * Returns a permission model that denies every permission
     *
     * @return Permission A mocked permission model
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
     * @param string $hostInfo The host info, e.g. "http://localhost"
     * @param string $baseUrl The base URL path, e.g. "/limesurvey"
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
     * @return string|null The trusted base URL, or null if there is none
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
