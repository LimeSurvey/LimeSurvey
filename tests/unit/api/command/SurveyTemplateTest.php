<?php

namespace ls\tests\unit\api\command;

use CHttpSession;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LimeSurvey\Api\Command\Response\Status\StatusErrorForbidden;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
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
 * Tests for the survey-template REST command: Access checks and the trusted
 * base URL of the internal survey preview request.
 */
class SurveyTemplateTest extends TestBaseClass
{
    use AssertResponse;

    /** @var mixed Allowed hosts config before the test */
    private $allowedHosts;

    /** @var mixed Force SSL config before the test */
    private $forceSsl;

    /** @var mixed SSL emergency override config before the test */
    private $sslEmergencyOverride;

    /**
     * Saves the URL, allowed hosts and SSL settings changed by the tests, sets an allowed hosts list and turns Force SSL off
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        self::$testHelper::saveUrlSettings();
        $this->allowedHosts = Yii::app()->getConfig('allowedHosts');
        Yii::app()->setConfig('allowedHosts', ['localhost']);
        $this->forceSsl = Yii::app()->getConfig('force_ssl');
        $this->sslEmergencyOverride = Yii::app()->getConfig('ssl_emergency_override');
        Yii::app()->setConfig('force_ssl', 'off');
        Yii::app()->setConfig('ssl_emergency_override', false);
    }

    /**
     * Restores the URL, allowed hosts and SSL settings changed by the tests
     *
     * @return void
     */
    public function tearDown(): void
    {
        Yii::app()->setConfig('allowedHosts', $this->allowedHosts);
        Yii::app()->setConfig('force_ssl', $this->forceSsl);
        Yii::app()->setConfig('ssl_emergency_override', $this->sslEmergencyOverride);
        self::$testHelper::resetUrlSettings();
        parent::tearDown();
    }

    /**
     * An unknown survey id must give a 403 like a missing permission does,
     * so that users cannot probe which survey ids exist.
     */
    public function testUnknownSurveyGivesForbidden(): void
    {
        $survey = Mockery::mock(Survey::class)->makePartial();
        $survey->shouldNotReceive('findByPk');

        // Permission::hasSurveyPermission() returns false for unknown surveys
        $permission = Mockery::mock(Permission::class)->makePartial();
        $permission->shouldReceive('hasSurveyPermission')
            ->with(123, 'surveycontent', 'read', null)
            ->andReturn(false);

        $command = $this->makeCommand($survey, $permission);
        $response = $command->run(new Request(['_id' => 123]));

        $this->assertResponseStatus($response, new StatusErrorForbidden());
    }

    /**
     * A user without read permission on the survey content must get a 403.
     */
    public function testMissingPermissionGivesForbidden(): void
    {
        $survey = Mockery::mock(Survey::class)->makePartial();
        $survey->shouldReceive('findByPk')->with(123)->andReturn($survey);

        $permission = Mockery::mock(Permission::class)->makePartial();
        $permission->shouldReceive('hasSurveyPermission')
            ->with(123, 'surveycontent', 'read', null)
            ->andReturn(false);

        $command = $this->makeCommand($survey, $permission);
        $response = $command->run(new Request(['_id' => 123]));

        $this->assertResponseStatus($response, new StatusErrorForbidden());
    }

    /**
     * A user with read permission on the survey content gets the template.
     */
    public function testPermissionGivesSuccess(): void
    {
        $survey = Mockery::mock(Survey::class)->makePartial();
        $survey->shouldReceive('findByPk')->with(123)->andReturn($survey);
        $survey->language = 'en';

        $permission = Mockery::mock(Permission::class)->makePartial();
        $permission->shouldReceive('hasSurveyPermission')
            ->with(123, 'surveycontent', 'read', null)
            ->andReturn(true);

        $languageSetting = Mockery::mock(SurveyLanguageSetting::class);
        $languageSetting->shouldReceive('find')->andReturn(null);

        // The template is fetched over HTTP, so that step is stubbed
        $command = Mockery::mock(SurveyTemplate::class, [
            new ResponseFactory(),
            Mockery::mock(CHttpSession::class),
            $survey,
            $languageSetting
        ])->makePartial()->shouldAllowMockingProtectedMethods();
        $command->shouldReceive('getTemplateData')
            ->with(123, 'en')
            ->andReturn('<p>Survey</p>');
        $command->setPermissionModel($permission);

        $response = $command->run(new Request(['_id' => 123]));

        $this->assertResponseStatus($response, new StatusSuccess());
        $this->assertSame(['template' => '<p>Survey</p>'], $response->getData());
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
     * With Force SSL on, an http base URL is changed to https, unless the SSL emergency override is on
     *
     * @dataProvider forceSslProvider
     * @param string $publicUrl The publicurl config value
     * @param bool $isEmergencyOverride The ssl_emergency_override config value
     * @param string $expected The expected trusted base URL
     * @return void
     */
    public function testTrustedBaseUrlUsesHttpsWithForceSsl(
        string $publicUrl,
        bool $isEmergencyOverride,
        string $expected
    ): void {
        Yii::app()->setConfig('publicurl', $publicUrl);
        Yii::app()->setConfig('force_ssl', 'on');
        Yii::app()->setConfig('ssl_emergency_override', $isEmergencyOverride);
        $this->setRequestUrl('https://localhost', '/limesurvey');

        $this->assertSame($expected, $this->getTrustedBaseUrl());
    }

    /**
     * Data provider for testTrustedBaseUrlUsesHttpsWithForceSsl
     *
     * @return array<string, array{string, bool, string}> Test case name => publicurl, emergency override, expected base URL
     */
    public function forceSslProvider(): array
    {
        return [
            'http' => ['http://localhost/limesurvey/', false, 'https://localhost/limesurvey'],
            'http with port 80' => ['http://localhost:80/limesurvey', false, 'https://localhost/limesurvey'],
            'http with other port' => ['http://localhost:8443/limesurvey', false, 'https://localhost:8443/limesurvey'],
            'https' => ['https://localhost/limesurvey', false, 'https://localhost/limesurvey'],
            'emergency override' => ['http://localhost/limesurvey', true, 'http://localhost/limesurvey'],
        ];
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
     * Build the command with mocked collaborators
     *
     * @param Survey $survey
     * @param Permission $permission
     * @return SurveyTemplate
     */
    private function makeCommand(Survey $survey, Permission $permission): SurveyTemplate
    {
        $command = new SurveyTemplate(
            new ResponseFactory(),
            Mockery::mock(CHttpSession::class),
            $survey,
            Mockery::mock(SurveyLanguageSetting::class)
        );
        $command->setPermissionModel($permission);
        return $command;
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
