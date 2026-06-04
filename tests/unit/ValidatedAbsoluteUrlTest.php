<?php

namespace ls\tests;

use Yii;

/**
 * Tests for the createValidatedAbsoluteUrl, getValidatedHost,
 * loadAllowedHosts, and writeAllowedHosts methods in LSApplicationTrait.
 *
 * These tests verify the host header injection prevention mechanism.
 */
class ValidatedAbsoluteUrlTest extends TestBaseClass
{
    /** @var string|null */
    private static $originalPublicUrl;

    /** @var string Path to allowed_hosts.php */
    private static $allowedHostsFile;

    /** @var string|null Original content of allowed_hosts.php if it existed */
    private static $originalAllowedHostsContent;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$originalPublicUrl = Yii::app()->getConfig('publicurl');
        self::$allowedHostsFile = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'allowed_hosts.php';

        // Backup existing allowed_hosts.php if present
        if (file_exists(self::$allowedHostsFile)) {
            self::$originalAllowedHostsContent = file_get_contents(self::$allowedHostsFile);
        }
    }

    public static function tearDownAfterClass(): void
    {
        // Restore publicurl
        Yii::app()->setConfig('publicurl', self::$originalPublicUrl);

        // Restore allowed_hosts.php
        if (self::$originalAllowedHostsContent !== null) {
            file_put_contents(self::$allowedHostsFile, self::$originalAllowedHostsContent);
        } elseif (file_exists(self::$allowedHostsFile)) {
            unlink(self::$allowedHostsFile);
        }

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Clean state before each test
        if (file_exists(self::$allowedHostsFile)) {
            unlink(self::$allowedHostsFile);
        }
        Yii::app()->setConfig('publicurl', '');
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        if (file_exists(self::$allowedHostsFile)) {
            unlink(self::$allowedHostsFile);
        }
        Yii::app()->setConfig('publicurl', self::$originalPublicUrl);
        parent::tearDown();
    }

    // ------------------------------------------------------------------
    // loadAllowedHosts tests
    // ------------------------------------------------------------------

    /**
     * Test that loadAllowedHosts returns empty array when file does not exist.
     */
    public function testLoadAllowedHostsReturnsEmptyWhenFileNotExists()
    {
        $hosts = Yii::app()->loadAllowedHosts();
        $this->assertIsArray($hosts);
        $this->assertEmpty($hosts);
    }

    /**
     * Test that loadAllowedHosts returns the array from the file.
     */
    public function testLoadAllowedHostsReturnsArrayFromFile()
    {
        file_put_contents(self::$allowedHostsFile, "<?php\nreturn ['example.com', 'www.example.com'];\n");

        $hosts = Yii::app()->loadAllowedHosts();
        $this->assertIsArray($hosts);
        $this->assertCount(2, $hosts);
        $this->assertSame('example.com', $hosts[0]);
        $this->assertSame('www.example.com', $hosts[1]);
    }

    /**
     * Test that loadAllowedHosts returns empty array when file returns empty array.
     */
    public function testLoadAllowedHostsReturnsEmptyWhenFileHasEmptyArray()
    {
        file_put_contents(self::$allowedHostsFile, "<?php\nreturn [];\n");

        $hosts = Yii::app()->loadAllowedHosts();
        $this->assertIsArray($hosts);
        $this->assertEmpty($hosts);
    }

    /**
     * Test that loadAllowedHosts returns empty array when file returns non-array.
     */
    public function testLoadAllowedHostsReturnsEmptyWhenFileReturnsNonArray()
    {
        file_put_contents(self::$allowedHostsFile, "<?php\nreturn 'not an array';\n");

        $hosts = Yii::app()->loadAllowedHosts();
        $this->assertIsArray($hosts);
        $this->assertEmpty($hosts);
    }

    // ------------------------------------------------------------------
    // writeAllowedHosts tests
    // ------------------------------------------------------------------

    /**
     * Test that writeAllowedHosts creates the file with correct content.
     */
    public function testWriteAllowedHostsCreatesFile()
    {
        $hosts = ['mysite.com', 'admin.mysite.com'];
        $result = Yii::app()->writeAllowedHosts($hosts);

        $this->assertTrue($result);
        $this->assertFileExists(self::$allowedHostsFile);

        // Verify the file is valid PHP and returns the correct array
        $loaded = require(self::$allowedHostsFile);
        $this->assertSame($hosts, $loaded);
    }

    /**
     * Test that writeAllowedHosts overwrites existing file.
     */
    public function testWriteAllowedHostsOverwritesExistingFile()
    {
        Yii::app()->writeAllowedHosts(['old.example.com']);
        Yii::app()->writeAllowedHosts(['new.example.com']);

        $loaded = require(self::$allowedHostsFile);
        $this->assertSame(['new.example.com'], $loaded);
    }

    // ------------------------------------------------------------------
    // getValidatedHost tests
    // ------------------------------------------------------------------

    /**
     * Test that getValidatedHost returns first allowed host from file.
     */
    public function testGetValidatedHostReturnsFromAllowedHostsFile()
    {
        Yii::app()->writeAllowedHosts(['trusted.example.com', 'also-trusted.example.com']);

        $host = Yii::app()->getValidatedHost();
        $this->assertSame('trusted.example.com', $host);
    }

    /**
     * Test that getValidatedHost falls back to publicurl when allowed_hosts.php is empty.
     */
    public function testGetValidatedHostFallsBackToPublicUrl()
    {
        Yii::app()->setConfig('publicurl', 'https://public.example.com/limesurvey');

        $host = Yii::app()->getValidatedHost();
        $this->assertSame('public.example.com', $host);
    }

    /**
     * Test that getValidatedHost extracts host from publicurl.
     */
    public function testGetValidatedHostExtractsHostFromPublicUrl()
    {
        Yii::app()->setConfig('publicurl', 'https://public.example.com:8443/limesurvey');

        $host = Yii::app()->getValidatedHost();
        $this->assertSame('public.example.com', $host);
    }

    /**
     * Test that getValidatedHost prefers allowed_hosts.php over publicurl.
     */
    public function testGetValidatedHostPrefersAllowedHostsOverPublicUrl()
    {
        Yii::app()->writeAllowedHosts(['allowed.example.com']);
        Yii::app()->setConfig('publicurl', 'https://public.example.com/');

        $host = Yii::app()->getValidatedHost();
        $this->assertSame('allowed.example.com', $host);
    }

    /**
     * Test that getValidatedHost returns false when no host source available.
     */
    public function testGetValidatedHostReturnsFalseWhenNoSource()
    {
        Yii::app()->setConfig('publicurl', '');

        $host = Yii::app()->getValidatedHost();
        $this->assertFalse($host);
    }

    /**
     * Test that getValidatedHost returns false when publicurl has no scheme/host.
     */
    public function testGetValidatedHostReturnsFalseForInvalidPublicUrl()
    {
        Yii::app()->setConfig('publicurl', '/relative/path');

        $host = Yii::app()->getValidatedHost();
        $this->assertFalse($host);
    }

    // ------------------------------------------------------------------
    // createValidatedAbsoluteUrl tests
    // ------------------------------------------------------------------

    /**
     * Test that createValidatedAbsoluteUrl returns false when no validated host available.
     */
    public function testCreateValidatedAbsoluteUrlReturnsFalseWhenNoHost()
    {
        Yii::app()->setConfig('publicurl', '');

        $url = Yii::app()->createValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'abc123']);
        $this->assertFalse($url);
    }

    /**
     * Test that createValidatedAbsoluteUrl uses the validated host.
     */
    public function testCreateValidatedAbsoluteUrlUsesValidatedHost()
    {
        Yii::app()->writeAllowedHosts(['secure.example.com']);

        $url = Yii::app()->createValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'testkey123']);

        $this->assertIsString($url);
        $this->assertStringContainsString('secure.example.com', $url);
        $this->assertStringContainsString('testkey123', $url);
    }

    /**
     * Test that createValidatedAbsoluteUrl uses publicurl as fallback host.
     */
    public function testCreateValidatedAbsoluteUrlUsesPublicUrlAsFallback()
    {
        Yii::app()->setConfig('publicurl', 'https://mysurvey.example.org/ls');

        $url = Yii::app()->createValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'key456']);

        $this->assertIsString($url);
        $this->assertStringContainsString('mysurvey.example.org', $url);
        $this->assertStringContainsString('key456', $url);
    }

    /**
     * Test that createValidatedAbsoluteUrl does not use the Host header.
     */
    public function testCreateValidatedAbsoluteUrlIgnoresHostHeader()
    {
        Yii::app()->writeAllowedHosts(['real-server.com']);

        // The generated URL should use real-server.com, not whatever host header is set
        $url = Yii::app()->createValidatedAbsoluteUrl('admin/authentication/sa/login');

        $this->assertIsString($url);
        $this->assertStringContainsString('real-server.com', $url);
        $this->assertStringNotContainsString('attacker.com', $url);
    }

    /**
     * Test that createValidatedAbsoluteUrl preserves the route and params.
     */
    public function testCreateValidatedAbsoluteUrlPreservesRouteAndParams()
    {
        Yii::app()->writeAllowedHosts(['myhost.com']);

        $url = Yii::app()->createValidatedAbsoluteUrl(
            'admin/authentication/sa/newPassword',
            ['param' => 'validation_key_xyz', 'extra' => 'value']
        );

        $this->assertIsString($url);
        $this->assertStringContainsString('validation_key_xyz', $url);
        $this->assertStringContainsString('extra', $url);
        $this->assertStringContainsString('value', $url);
    }

    /**
     * Test that createValidatedAbsoluteUrl works correctly.
     */
    public function testCreateValidatedAbsoluteUrlDomainOnly()
    {
        Yii::app()->writeAllowedHosts(['myhost.com']);

        $url = Yii::app()->createValidatedAbsoluteUrl('admin/authentication/sa/login');

        $this->assertIsString($url);
        $this->assertStringContainsString('myhost.com', $url);
        $this->assertStringNotContainsString(':8443', $url);
    }

    // ------------------------------------------------------------------
    // LimeMailer::replaceHostInUrl tests
    // ------------------------------------------------------------------

    /**
     * Test replaceHostInUrl replaces host correctly.
     */
    public function testReplaceHostInUrlBasic()
    {
        $url = 'http://evil.attacker.com/index.php?r=optout/tokens&surveyid=123&token=abc';
        $validatedHost = 'real-server.com';

        $result = \LimeMailer::replaceHostInUrl($url, $validatedHost);

        $this->assertStringStartsWith('http://real-server.com/', $result);
        $this->assertStringContainsString('surveyid=123', $result);
        $this->assertStringContainsString('token=abc', $result);
        $this->assertStringNotContainsString('evil.attacker.com', $result);
    }

    /**
     * Test replaceHostInUrl preserves path and scheme.
     */
    public function testReplaceHostInUrlPreservesPath()
    {
        $url = 'https://localhost/limesurvey/index.php?r=survey/index&sid=123';
        $validatedHost = 'production.example.com';

        $result = \LimeMailer::replaceHostInUrl($url, $validatedHost);

        $this->assertStringStartsWith('https://production.example.com/limesurvey/', $result);
        $this->assertStringContainsString('sid=123', $result);
    }

    /**
     * Test replaceHostInUrl with port in original URL preserves port.
     */
    public function testReplaceHostInUrlPreservesOriginalPort()
    {
        $url = 'http://localhost:8080/index.php?r=admin';
        $validatedHost = 'myserver.com';

        $result = \LimeMailer::replaceHostInUrl($url, $validatedHost);

        $this->assertStringStartsWith('http://myserver.com:8080/', $result);
    }

    /**
     * Test replaceHostInUrl returns original URL when validated host is empty.
     */
    public function testReplaceHostInUrlReturnsOriginalWhenEmptyValidatedHost()
    {
        $url = 'http://localhost/index.php';
        $validatedHost = '';

        $result = \LimeMailer::replaceHostInUrl($url, $validatedHost);

        $this->assertSame($url, $result);
    }

    /**
     * Test replaceHostInUrl returns original URL when URL has no host.
     */
    public function testReplaceHostInUrlReturnsOriginalWhenUrlHasNoHost()
    {
        $url = '/relative/path?param=value';
        $validatedHost = 'valid.com';

        $result = \LimeMailer::replaceHostInUrl($url, $validatedHost);

        $this->assertSame($url, $result);
    }
}
