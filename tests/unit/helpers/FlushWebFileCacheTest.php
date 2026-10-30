<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Tests for flushWebFileCache() in updatedb_helper.php.
 *
 * @group dbhelper
 */
class FlushWebFileCacheTest extends TestBaseClass
{
    /**
     * Load the update helper.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.helpers.update.updatedb_helper', true);
    }

    /**
     * When the application cache is a CDummyCache (console with YII_DEBUG), the web file cache
     * must still be emptied, otherwise the web keeps the old table schema after a CLI update.
     */
    public function testFileCacheIsFlushedWhenApplicationCacheIsDummy()
    {
        $originalCache = \Yii::app()->getComponent('cache');
        \Yii::app()->setComponent('cache', new \CDummyCache());
        try {
            $fileCache = new \CFileCache();
            $fileCache->init();
            $fileCache->set('test_flush_web_file_cache', 'stale schema');
            $this->assertSame('stale schema', $fileCache->get('test_flush_web_file_cache'), 'Value was written to the file cache');

            flushWebFileCache();

            $this->assertFalse($fileCache->get('test_flush_web_file_cache'), 'File cache was flushed');
        } finally {
            \Yii::app()->setComponent('cache', $originalCache);
        }
    }
}
