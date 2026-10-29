<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Test viewHelper::purified() handling of data URIs.
 */
class ViewHelperPurifiedTest extends TestBaseClass
{
    /** @var string A 1x1 PNG image */
    private const PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.helpers.viewHelper');
    }

    /**
     * Data URI images are removed by default.
     */
    public function testDataUriImageRemovedByDefault()
    {
        $html = '<p>Signature: <img src="data:image/png;base64,' . self::PNG_BASE64 . '" alt="Signature" /></p>';
        $this->assertStringNotContainsString('<img', \viewHelper::purified($html));
    }

    /**
     * Data URI images are kept when data URIs are allowed.
     */
    public function testDataUriImageKeptWhenAllowed()
    {
        $html = '<p>Signature: <img src="data:image/png;base64,' . self::PNG_BASE64 . '" alt="Signature" /></p>';
        $this->assertStringContainsString(
            'src="data:image/png;base64,' . self::PNG_BASE64 . '"',
            \viewHelper::purified($html, true)
        );
    }

    /**
     * Data URIs that are not images are still removed when data URIs are allowed.
     */
    public function testNonImageDataUriRemovedWhenAllowed()
    {
        $html = '<p><img src="data:text/plain;base64,' . base64_encode('Hello') . '" alt="Text" /></p>';
        $this->assertStringNotContainsString('<img', \viewHelper::purified($html, true));
    }

    /**
     * Allowing data URIs keeps the other default schemes.
     */
    public function testDefaultSchemesKeptWhenDataUriAllowed()
    {
        $html = '<p><a href="https://example.org/">Link</a> <a href="mailto:info@example.org">Mail</a></p>';
        $purified = \viewHelper::purified($html, true);
        $this->assertStringContainsString('href="https://example.org/"', $purified);
        $this->assertStringContainsString('href="mailto:info@example.org"', $purified);
    }
}
