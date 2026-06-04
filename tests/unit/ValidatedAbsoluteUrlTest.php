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
    // isHostAllowed tests
    // ------------------------------------------------------------------

    /**
     * Test that isHostAllowed returns true when host is in allowed_hosts.php.
     */
    public function testIsHostAllowedReturnsTrueForAllowedHost()
    {
        Yii::app()->writeAllowedHosts(['trusted.example.com', 'also-trusted.example.com']);

        $this->assertTrue(Yii::app()->isHostAllowed('trusted.example.com'));
        $this->assertTrue(Yii::app()->isHostAllowed('also-trusted.example.com'));
    }

    /**
     * Test that isHostAllowed returns false for a host not in the list.
     */
    public function testIsHostAllowedReturnsFalseForUnknownHost()
    {
        Yii::app()->writeAllowedHosts(['trusted.example.com']);

        $this->assertFalse(Yii::app()->isHostAllowed('attacker.com'));
    }

    /**
     * Test that isHostAllowed is case-insensitive.
     */
    public function testIsHostAllowedIsCaseInsensitive()
    {
        Yii::app()->writeAllowedHosts(['Example.COM']);

        $this->assertTrue(Yii::app()->isHostAllowed('example.com'));
        $this->assertTrue(Yii::app()->isHostAllowed('EXAMPLE.COM'));
    }

    /**
     * Test that isHostAllowed is lenient (returns true) when no file exists and publicurl is set.
     */
    public function testIsHostAllowedIsLenientWhenNoFileExists()
    {
        Yii::app()->setConfig('publicurl', 'https://public.example.com/limesurvey');

        $this->assertTrue(Yii::app()->isHostAllowed('public.example.com'));
        $this->assertTrue(Yii::app()->isHostAllowed('other.example.com'));
    }

    /**
     * Test that isHostAllowed is lenient (returns true) when no source is configured.
     */
    public function testIsHostAllowedIsLenientWhenNoSource()
    {
        Yii::app()->setConfig('publicurl', '');

        $this->assertTrue(Yii::app()->isHostAllowed('anything.com'));
    }

    /**
     * Test that isHostAllowed is lenient (returns true) when publicurl has no valid host.
     */
    public function testIsHostAllowedIsLenientForInvalidPublicUrl()
    {
        Yii::app()->setConfig('publicurl', '/relative/path');

        $this->assertTrue(Yii::app()->isHostAllowed('localhost'));
    }

    // ------------------------------------------------------------------
    // createValidatedAbsoluteUrl tests
    // ------------------------------------------------------------------

    /**
     * Test that createValidatedAbsoluteUrl is lenient (returns URL) when no file exists.
     */
    public function testCreateValidatedAbsoluteUrlIsLenientWhenNoFile()
    {
        Yii::app()->setConfig('publicurl', '');

        $url = Yii::app()->createValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'abc123']);
        $this->assertIsString($url);
        $this->assertStringContainsString('abc123', $url);
    }

    /**
     * Test that createValidatedAbsoluteUrl returns the URL when host is allowed.
     */
    public function testCreateValidatedAbsoluteUrlReturnsUrlWhenHostAllowed()
    {
        // In test environment, createAbsoluteUrl generates URLs with 'localhost'
        Yii::app()->writeAllowedHosts(['localhost']);

        $url = Yii::app()->createValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'testkey123']);

        $this->assertIsString($url);
        $this->assertStringContainsString('localhost', $url);
        $this->assertStringContainsString('testkey123', $url);
    }

    /**
     * Test that createValidatedAbsoluteUrl uses publicurl for URL construction.
     */
    public function testCreateValidatedAbsoluteUrlUsesPublicUrl()
    {
        // Set publicurl to match the test environment host
        Yii::app()->setConfig('publicurl', 'http://localhost/');

        $url = Yii::app()->createValidatedAbsoluteUrl('admin/authentication/sa/newPassword', ['param' => 'key456']);

        $this->assertIsString($url);
        $this->assertStringContainsString('localhost', $url);
        $this->assertStringContainsString('key456', $url);
    }

    /**
     * Test that createValidatedAbsoluteUrl returns false when host is NOT in allowed list.
     */
    public function testCreateValidatedAbsoluteUrlReturnsFalseWhenHostNotAllowed()
    {
        // Only allow a host that doesn't match the test environment
        Yii::app()->writeAllowedHosts(['production.example.com']);
        // Clear publicurl so localhost is not auto-trusted
        Yii::app()->setConfig('publicurl', '');

        $url = Yii::app()->createValidatedAbsoluteUrl('admin/authentication/sa/login');

        $this->assertFalse($url);
    }

    /**
     * Test that createValidatedAbsoluteUrl preserves the route and params.
     */
    public function testCreateValidatedAbsoluteUrlPreservesRouteAndParams()
    {
        Yii::app()->writeAllowedHosts(['localhost']);

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
     * Test that createValidatedAbsoluteUrl allows multiple hosts in the filter.
     */
    public function testCreateValidatedAbsoluteUrlAllowsMultipleHosts()
    {
        Yii::app()->writeAllowedHosts(['production.example.com', 'localhost']);

        $url = Yii::app()->createValidatedAbsoluteUrl('admin/authentication/sa/login');

        $this->assertIsString($url);
        $this->assertStringContainsString('localhost', $url);
    }
}
