<?php

namespace ls\tests\controllers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;

/**
 * Deleting a survey theme must remove exactly the theme folder that was asked for,
 * and an uploaded theme must not get a folder name starting with spaces (#19503).
 */
class ThemeDeleteNameTest extends TestBaseClass
{
    /** @var \themes */
    private $controller;

    /** @var string Original user theme root directory */
    private $originalThemeRootDir;

    /** @var string Temporary user theme root directory used by the tests */
    private $themeRootDir;

    /**
     * Points the user theme root to an empty temporary directory.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        \Yii::import('application.controllers.admin.Themes', true);
        \Yii::app()->user->setId(1);
        $this->controller = new \themes(new DummyController('dummyid'));

        $this->originalThemeRootDir = \Yii::app()->getConfig('userthemerootdir');
        $this->themeRootDir = \Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . 'themeroot_' . uniqid();
        mkdir($this->themeRootDir);
        \Yii::app()->setConfig('userthemerootdir', $this->themeRootDir);
    }

    /**
     * Restores the user theme root and removes the temporary one.
     * @return void
     */
    protected function tearDown(): void
    {
        \Yii::app()->setConfig('userthemerootdir', $this->originalThemeRootDir);
        rmdirr($this->themeRootDir);
        $_POST = [];
        $_FILES = [];
        \Yii::app()->session['aFlashMessage'] = [];
        parent::tearDown();
    }

    /**
     * Deleting an uninstalled theme whose name starts with spaces deletes that theme,
     * not the theme with the same name without the spaces.
     * @return void
     */
    public function testDeleteAvailableThemeWithLeadingSpaces()
    {
        mkdir($this->themeRootDir . DIRECTORY_SEPARATOR . '  t19503');
        mkdir($this->themeRootDir . DIRECTORY_SEPARATOR . 't19503');

        $_POST['templatename'] = '  t19503';
        $this->controller->deleteAvailableTheme();

        $this->assertDirectoryDoesNotExist($this->themeRootDir . DIRECTORY_SEPARATOR . '  t19503');
        $this->assertDirectoryExists($this->themeRootDir . DIRECTORY_SEPARATOR . 't19503');
    }

    /**
     * Names resolving to the theme root directory itself are refused.
     * @dataProvider themeRootNameProvider
     * @param string $templateName
     * @return void
     */
    public function testDeleteAvailableThemeRefusesThemeRootDirectory($templateName)
    {
        mkdir($this->themeRootDir . DIRECTORY_SEPARATOR . 't19503');

        $_POST['templatename'] = $templateName;
        try {
            $this->controller->deleteAvailableTheme();
            $this->fail('No exception thrown');
        } catch (\CHttpException $exception) {
            $this->assertSame(403, $exception->statusCode);
        }

        $this->assertDirectoryExists($this->themeRootDir . DIRECTORY_SEPARATOR . 't19503');
    }

    /**
     * @return array
     */
    public static function themeRootNameProvider()
    {
        return [
            'empty' => [''],
            'space' => [' '],
            'dot' => ['.'],
            'parent of a theme' => ['t19503/..'],
        ];
    }

    /**
     * The folder name of an uploaded survey theme has no leading or trailing spaces.
     * @return void
     */
    public function testUploadedThemeDirectoryNameIsTrimmed()
    {
        $_FILES['the_file'] = ['name' => '  t19503 .zip'];
        $method = new \ReflectionMethod(\themes::class, 'getNewDirectoryName');

        $this->assertSame('t19503', $method->invoke($this->controller, 'survey', ''));
    }
}
