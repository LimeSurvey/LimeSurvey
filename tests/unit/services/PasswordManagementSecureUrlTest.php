<?php

namespace ls\tests;

use LimeSurvey\Models\Services\PasswordManagement;

/**
 * Tests for PasswordManagement::createValidatedAbsoluteUrl()
 *
 * Verifies the Host Header Injection fix (bug #20548):
 * - URLs use allowedHosts-validated request host when configured
 * - URLs fall back to absolute publicurl
 * - Returns null when no secure base URL is available
 * - Spoofed Host header is rejected
 * - No double base-path prefixing
 */
class PasswordManagementSecureUrlTest extends TestBaseClass
{
    /** @var \ReflectionMethod */
    private static $method;

    /** @var array Original $_SERVER values */
    private $originalServer;

    /** @var array Original config values */
    private $originalConfig = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$method = new \ReflectionMethod(PasswordManagement::class, 'createValidatedAbsoluteUrl');
        self::$method->setAccessible(true);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalServer = $_SERVER;
        $this->originalConfig = [
            'allowedHosts' => \Yii::app()->getConfig('allowedHosts'),
            'publicurl' => \Yii::app()->getConfig('publicurl'),
        ];
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        \Yii::app()->setConfig('allowedHosts', $this->originalConfig['allowedHosts']);
        \Yii::app()->setConfig('publicurl', $this->originalConfig['publicurl']);
        parent::tearDown();
    }

    /**
     * Helper to invoke the private static method.
     */
    private function invokecreateValidatedAbsoluteUrl(string $route, array $params = []): ?string
    {
        return self::$method->invoke(null, $route, $params);
    }

    /**
     * When allowedHosts is configured and request host is whitelisted,
     * the URL should use the request host.
     */
    public function testAllowedHostUsesRequestHost(): void
    {
        $_SERVER['HTTP_HOST'] = 'trusted.example.com';
        $_SERVER['HTTPS'] = 'on';
        \Yii::app()->setConfig('allowedHosts', ['trusted.example.com']);
        \Yii::app()->setConfig('publicurl', '/');

        $url = $this->invokecreateValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'abc123']);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://trusted.example.com', $url);
        $this->assertStringContainsString('abc123', $url);
    }

    /**
     * When allowedHosts is configured but the request Host header is NOT in the whitelist,
     * it should fall back to publicurl (or null).
     */
    public function testSpoofedHostRejectedByAllowedHosts(): void
    {
        $_SERVER['HTTP_HOST'] = 'evil.attacker.com';
        $_SERVER['HTTPS'] = 'off';
        \Yii::app()->setConfig('allowedHosts', ['legitimate.example.com']);
        \Yii::app()->setConfig('publicurl', '/');

        $url = $this->invokecreateValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'abc123']);

        // Since the Host doesn't match allowedHosts AND publicurl is relative â†’ null
        $this->assertNull($url);
    }

    /**
     * When allowedHosts rejects the host but publicurl is absolute,
     * it should fall back to publicurl.
     */
    public function testSpoofedHostFallsBackToAbsolutePublicUrl(): void
    {
        $_SERVER['HTTP_HOST'] = 'evil.attacker.com';
        $_SERVER['HTTPS'] = 'off';
        \Yii::app()->setConfig('allowedHosts', ['legitimate.example.com']);
        \Yii::app()->setConfig('publicurl', 'https://mysite.com/limesurvey/');

        $url = $this->invokecreateValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'token123']);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://mysite.com', $url);
        $this->assertStringNotContainsString('evil.attacker.com', $url);
        $this->assertStringContainsString('token123', $url);
    }

    /**
     * When only publicurl is set as absolute URL (no allowedHosts),
     * the URL should use publicurl's host.
     */
    public function testAbsolutePublicUrlUsedWhenNoAllowedHosts(): void
    {
        $_SERVER['HTTP_HOST'] = 'anything.example.com';
        $_SERVER['HTTPS'] = 'off';
        \Yii::app()->setConfig('allowedHosts', null);
        \Yii::app()->setConfig('publicurl', 'https://configured-host.example.com/survey/');

        $url = $this->invokecreateValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'xyz']);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://configured-host.example.com', $url);
        $this->assertStringNotContainsString('anything.example.com', $url);
    }

    /**
     * When neither allowedHosts nor absolute publicurl is configured,
     * the method should return null (refuse to generate an insecure URL).
     */
    public function testReturnsNullWhenNoSecureConfigAvailable(): void
    {
        $_SERVER['HTTP_HOST'] = 'spoofed.example.com';
        $_SERVER['HTTPS'] = 'off';
        \Yii::app()->setConfig('allowedHosts', null);
        \Yii::app()->setConfig('publicurl', '/limesurvey/');

        $url = $this->invokecreateValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'test']);

        $this->assertNull($url);
    }

    /**
     * Verify no double base-path: the returned URL should not contain
     * the application base path duplicated.
     */
    public function testNoDoubleBasePathPrefixing(): void
    {
        $_SERVER['HTTP_HOST'] = 'myhost.com';
        $_SERVER['HTTPS'] = 'on';
        \Yii::app()->setConfig('allowedHosts', ['myhost.com']);
        \Yii::app()->setConfig('publicurl', '/');

        $url = $this->invokecreateValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'val']);
        $this->assertNotNull($url);

        $basePath = \Yii::app()->getBaseUrl();
        if ($basePath !== '' && $basePath !== '/') {
            // The base path should appear exactly once
            $count = substr_count($url, $basePath);
            $this->assertEquals(1, $count, "Base path '$basePath' appears $count times in URL: $url");
        }
    }

    /**
     * Test that attacker-controlled port in Host header is NOT propagated.
     * Only the validated hostname from allowedHosts should be used.
     */
    public function testPortFromHostHeaderNotPropagated(): void
    {
        $_SERVER['HTTP_HOST'] = 'myhost.com:8443';
        $_SERVER['HTTPS'] = 'on';
        \Yii::app()->setConfig('allowedHosts', ['myhost.com']);
        \Yii::app()->setConfig('publicurl', '/');

        $url = $this->invokecreateValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'x']);

        $this->assertNotNull($url);
        // Should use only the validated hostname, NOT the raw port from HTTP_HOST
        $this->assertStringStartsWith('https://myhost.com', $url);
        $this->assertStringNotContainsString(':8443', $url);
    }

    /**
     * Test that port from publicurl is preserved.
     */
    public function testPortPreservedWithPublicUrl(): void
    {
        $_SERVER['HTTP_HOST'] = 'untrusted.com';
        \Yii::app()->setConfig('allowedHosts', null);
        \Yii::app()->setConfig('publicurl', 'http://mysite.local:9090/limesurvey/');

        $url = $this->invokecreateValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'y']);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('http://mysite.local:9090', $url);
    }

    /**
     * Test that publicurl path prefix is preserved for subdirectory/proxied installs.
     */
    public function testPublicUrlPathPreserved(): void
    {
        $_SERVER['HTTP_HOST'] = 'untrusted.com';
        \Yii::app()->setConfig('allowedHosts', null);
        \Yii::app()->setConfig('publicurl', 'https://proxy.example.com/surveys/');

        $url = $this->invokecreateValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'abc']);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://proxy.example.com', $url);
        $this->assertStringContainsString('/surveys/', $url);
        $this->assertStringContainsString('abc', $url);
    }

    /**
     * Test HTTP scheme when HTTPS is off.
     */
    public function testHttpSchemeWhenNotSecure(): void
    {
        $_SERVER['HTTP_HOST'] = 'plain.example.com';
        $_SERVER['HTTPS'] = '';
        \Yii::app()->setConfig('allowedHosts', ['plain.example.com']);
        \Yii::app()->setConfig('publicurl', '/');

        $url = $this->invokecreateValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'z']);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('http://plain.example.com', $url);
    }

    /**
     * Domain aliasing: different whitelisted hosts should produce
     * URLs with their respective domains.
     */
    public function testDomainAliasingUsesCorrectHost(): void
    {
        \Yii::app()->setConfig('allowedHosts', ['domain-a.com', 'domain-b.com']);
        \Yii::app()->setConfig('publicurl', '/');
        $_SERVER['HTTPS'] = 'on';

        // Request via domain-a
        $_SERVER['HTTP_HOST'] = 'domain-a.com';
        $urlA = $this->invokecreateValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => '1']);
        $this->assertNotNull($urlA);
        $this->assertStringStartsWith('https://domain-a.com', $urlA);

        // Request via domain-b
        $_SERVER['HTTP_HOST'] = 'domain-b.com';
        $urlB = $this->invokecreateValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => '2']);
        $this->assertNotNull($urlB);
        $this->assertStringStartsWith('https://domain-b.com', $urlB);
    }
}
