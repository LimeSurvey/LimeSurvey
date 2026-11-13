<?php

namespace ls\tests\unit\helpers;

use LimeSurvey\Helpers\ZipHelper;
use ls\tests\TestBaseClass;

/**
 * Test ZipHelper::addFolder().
 */
class ZipHelperTest extends TestBaseClass
{
    /** @var string Folder that is zipped */
    private $sourceDir;

    /** @var string Path of the created archive */
    private $zipFile;

    /**
     * Creates a folder with a file, a nested file and empty folders, like an extended survey theme.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $base = \Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . 'ziphelpertest_' . uniqid();
        $this->sourceDir = $base . '_src';
        $this->zipFile = $base . '.zip';
        mkdir($this->sourceDir . '/files', 0777, true);
        mkdir($this->sourceDir . '/views/subviews/header', 0777, true);
        mkdir($this->sourceDir . '/css/variations', 0777, true);
        mkdir($this->sourceDir . '/scripts', 0777, true);
        file_put_contents($this->sourceDir . '/config.xml', '<config/>');
        file_put_contents($this->sourceDir . '/files/logo.png', 'png');
    }

    /**
     * Removes the folder and the archive.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        \Yii::app()->loadHelper('admin.template');
        rmdirr($this->sourceDir);
        if (is_file($this->zipFile)) {
            unlink($this->zipFile);
        }
        parent::tearDown();
    }

    /**
     * Zips the source folder and returns the sorted entry names of the archive.
     *
     * @param string $pathInZip Path inside the archive to add the folder content to
     * @return string[] Entry names of the archive
     */
    private function zipAndListEntries($pathInZip = '')
    {
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($this->zipFile, \ZipArchive::CREATE));
        (new ZipHelper($zip))->addFolder($this->sourceDir, $pathInZip);
        $zip->close();

        $this->assertTrue($zip->open($this->zipFile));
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }
        $zip->close();
        sort($entries);
        return $entries;
    }

    /**
     * Empty folders (e.g. the views folder of an extended theme) are part of the archive.
     *
     * @return void
     */
    public function testEmptyFoldersAreAdded()
    {
        $this->assertSame(
            [
                'config.xml',
                'css/',
                'css/variations/',
                'files/',
                'files/logo.png',
                'scripts/',
                'views/',
                'views/subviews/',
                'views/subviews/header/',
            ],
            $this->zipAndListEntries()
        );
    }

    /**
     * With a path inside the archive, files and empty folders are added below it.
     *
     * @return void
     */
    public function testFoldersAreAddedBelowPathInZip()
    {
        $entries = $this->zipAndListEntries('assets/');
        $this->assertContains('assets/files/logo.png', $entries);
        $this->assertContains('assets/views/subviews/header/', $entries);
        foreach ($entries as $entry) {
            $this->assertStringStartsWith('assets/', $entry);
        }
    }

    /**
     * Empty folders are recreated when the archive is extracted.
     *
     * @return void
     */
    public function testEmptyFoldersSurviveExtraction()
    {
        $this->zipAndListEntries();
        $extractDir = $this->sourceDir . '/extracted';
        mkdir($extractDir);
        $extractor = new \LimeSurvey\Models\Services\ZipExtractor($this->zipFile);
        $this->assertTrue($extractor->extractTo($extractDir));
        $this->assertDirectoryExists($extractDir . '/views/subviews/header');
        $this->assertDirectoryExists($extractDir . '/scripts');
        $this->assertFileExists($extractDir . '/files/logo.png');
    }
}
