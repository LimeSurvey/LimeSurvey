<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Test cleanExtensionInstallTempDirectories function.
 */
class CleanExtensionInstallTempDirectoriesTest extends TestBaseClass
{
    /**
     * Orphaned extension upload folders older than 1 day are removed,
     * recent ones and unrelated folders are kept.
     */
    public function testOnlyOldInstallDirectoriesAreRemoved()
    {
        \Yii::import('application.helpers.common_helper', true);
        $tempdir = \Yii::app()->getConfig('tempdir');

        $oldInstallDir = createRandomTempDir($tempdir, 'install_');
        file_put_contents($oldInstallDir . DIRECTORY_SEPARATOR . 'config.xml', 'test');
        touch($oldInstallDir, strtotime('-2 days'));

        $recentInstallDir = createRandomTempDir($tempdir, 'install_');

        $oldOtherDir = createRandomTempDir($tempdir, 'other_');
        touch($oldOtherDir, strtotime('-2 days'));

        cleanExtensionInstallTempDirectories();

        $this->assertDirectoryDoesNotExist($oldInstallDir);
        $this->assertDirectoryExists($recentInstallDir);
        $this->assertDirectoryExists($oldOtherDir);

        rmdirr($recentInstallDir);
        rmdirr($oldOtherDir);
    }
}
