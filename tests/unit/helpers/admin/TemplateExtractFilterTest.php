<?php

namespace ls\tests\unit\helpers\admin;

use ls\tests\TestBaseClass;

class TemplateExtractFilterTest extends TestBaseClass
{
    /**
     * Loads the template helper that defines templateExtractFilter().
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.helpers.admin.template_helper', true);
    }

    /**
     * Theme files with ordinary names, including spaces, brackets and apostrophes, are extracted.
     * Backslashes are allowed, because some ZIP tools on Windows use them as folder separators.
     */
    public function testAllowsRegularThemeFiles()
    {
        $cases = [
            ['name' => 'config.xml', 'is_folder' => false],
            ['name' => 'files/', 'is_folder' => true],
            ['name' => 'files/logo.png', 'is_folder' => false],
            ['name' => 'files/logo (1).png', 'is_folder' => false],
            ['name' => "files/O'Brien-logo.png", 'is_folder' => false],
            ['name' => 'css/custom.css', 'is_folder' => false],
            ['name' => 'css\\custom.css', 'is_folder' => false],
            ['name' => 'views/subviews/content/main.twig', 'is_folder' => false],
            ['name' => 'LICENSE', 'is_folder' => false],
        ];

        foreach ($cases as $case) {
            $this->assertSame(1, templateExtractFilter($case), 'Expected file to be extracted: ' . json_encode($case));
        }
    }

    /**
     * Files and folders with < > " or control characters in their path are skipped.
     */
    public function testSkipsUnsafeNames()
    {
        $cases = [
            ['name' => 'files/a"b.png', 'is_folder' => false],
            ['name' => 'files/<b>.png', 'is_folder' => false],
            ['name' => "files/a\x01b.png", 'is_folder' => false],
            ['name' => 'a"b/', 'is_folder' => true],
            ['name' => 'a"b/logo.png', 'is_folder' => false],
        ];

        foreach ($cases as $case) {
            $this->assertSame(0, templateExtractFilter($case), 'Expected file to be skipped: ' . json_encode($case));
        }
    }

    /**
     * Files with an extension that is not allowed for themes are skipped.
     */
    public function testSkipsDisallowedExtensions()
    {
        $this->assertSame(0, templateExtractFilter(['name' => 'files/script.php', 'is_folder' => false]));
    }
}
