<?php

namespace ls\tests;

/**
 * Tests for the per-request cache of ExtensionConfig::loadFromFileCached().
 */
class ExtensionConfigTest extends TestBaseClass
{
    /** @var string Temporary directory holding the test config.xml */
    private $tempDir;

    /** @var string Path of the test config.xml */
    private $configFile;

    /**
     * Create an empty temporary directory and reset the cache.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = createRandomTempDir(\Yii::app()->getConfig('tempdir'), 'extensionconfig_');
        $this->configFile = $this->tempDir . DIRECTORY_SEPARATOR . 'config.xml';
        \ExtensionConfig::clearCache();
    }

    /**
     * Remove the temporary directory and reset the cache.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        \ExtensionConfig::clearCache();
        rmdirr($this->tempDir);
        parent::tearDown();
    }

    /**
     * Write a minimal config.xml with the given version.
     *
     * @param string $version
     * @return void
     */
    private function writeConfig($version)
    {
        file_put_contents(
            $this->configFile,
            '<?xml version="1.0" encoding="UTF-8"?><config><metadata><version>' . $version . '</version></metadata></config>'
        );
    }

    /**
     * The cached loader returns the same instance until the cache is cleared.
     *
     * @return void
     */
    public function testCachedLoaderKeepsInstanceUntilCleared()
    {
        $this->writeConfig('1.0.0');
        $first = \ExtensionConfig::loadFromFileCached($this->configFile);
        $this->assertSame('1.0.0', $first->getVersion());

        $this->writeConfig('2.0.0');
        $this->assertSame($first, \ExtensionConfig::loadFromFileCached($this->configFile));

        \ExtensionConfig::clearCache();
        $this->assertSame('2.0.0', \ExtensionConfig::loadFromFileCached($this->configFile)->getVersion());
    }

    /**
     * The uncached loader always reads the current file content.
     *
     * @return void
     */
    public function testUncachedLoaderIgnoresCache()
    {
        $this->writeConfig('1.0.0');
        \ExtensionConfig::loadFromFileCached($this->configFile);

        $this->writeConfig('2.0.0');
        $this->assertSame('2.0.0', \ExtensionConfig::loadFromFile($this->configFile)->getVersion());
    }

    /**
     * A missing file is not cached, so it is found once it exists.
     *
     * @return void
     */
    public function testMissingFileIsNotCached()
    {
        $this->assertNull(\ExtensionConfig::loadFromFileCached($this->configFile));

        $this->writeConfig('1.0.0');
        $this->assertSame('1.0.0', \ExtensionConfig::loadFromFileCached($this->configFile)->getVersion());
    }
}
