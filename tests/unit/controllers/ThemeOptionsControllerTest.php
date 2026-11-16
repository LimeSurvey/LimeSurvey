<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;
use ThemeOptionsController;
use TemplateConfiguration;

/**
 * Tests for ThemeOptionsController.
 */
class ThemeOptionsControllerTest extends TestBaseClass
{
    /**
     * @var ThemeOptionsController Controller recording the rendered partials instead of rendering them
     */
    private $controller;

    /**
     * @var \User[] Users created by the test
     */
    private $testUsers = [];

    /**
     * Creates the controller with rendering stubbed out.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        \Yii::import('application.controllers.ThemeOptionsController', true);
        // Rendering needs a full HTTP request context, which unit tests don't have
        $this->controller = new class ('themeOptions') extends ThemeOptionsController {
            /** @var array[] Data passed to each renderPartial() call */
            public $renderedPartials = [];

            public function renderPartial($view, $data = null, $return = false, $processOutput = false)
            {
                $this->renderedPartials[] = $data;
                return '';
            }
        };
        \Yii::app()->setController($this->controller);
    }

    /**
     * Deletes the test users and their permissions.
     * @return void
     */
    public function tearDown(): void
    {
        $_POST = [];
        \Yii::app()->session['aFlashMessage'] = [];
        \Yii::app()->user->setId(1);
        foreach ($this->testUsers as $user) {
            \Permission::model()->deleteAllByAttributes(['uid' => $user->uid]);
            $user->delete();
        }
        $this->controller = null;
        parent::tearDown();
    }

    /**
     * A user who can update themes, but not delete them, can't uninstall
     * themes through the massive action (#18538).
     * @return void
     */
    public function testUninstallMultipleWithoutDeletePermissionIsRefused()
    {
        $user = $this->createTestUser('theme_updater', [
            'templates' => ['read' => 'on', 'update' => 'on'],
        ]);
        \Yii::app()->user->setId($user->uid);
        $theme = $this->getDefaultThemeConfiguration();

        $this->uninstallMultiple([$theme->id]);

        $this->assertEmpty($this->controller->renderedPartials, 'Uninstall results were rendered without delete permission');
        $this->assertNotNull(TemplateConfiguration::model()->findByPk($theme->id), 'Theme was uninstalled');
        $flashMessages = \Yii::app()->session['aFlashMessage'];
        $this->assertNotEmpty($flashMessages, 'No error message was set');
        $this->assertSame('error', end($flashMessages)['type']);
    }

    /**
     * A user with the delete permission passes the permission check. The
     * default theme itself is protected and reported as not uninstalled.
     * @return void
     */
    public function testUninstallMultipleWithDeletePermissionKeepsDefaultTheme()
    {
        $user = $this->createTestUser('theme_deleter', [
            'templates' => ['read' => 'on', 'delete' => 'on'],
        ]);
        \Yii::app()->user->setId($user->uid);
        $theme = $this->getDefaultThemeConfiguration();

        $this->uninstallMultiple([$theme->id]);

        $this->assertCount(1, $this->controller->renderedPartials, 'Uninstall results were not rendered');
        $results = $this->controller->renderedPartials[0]['aResults'];
        $this->assertArrayHasKey($theme->id, $results);
        $this->assertFalse($results[$theme->id]['result']);
        $this->assertNotNull(TemplateConfiguration::model()->findByPk($theme->id), 'Default theme was uninstalled');
    }

    /**
     * Runs the "Uninstall" massive action for survey themes.
     * @param int[] $themeIds Ids of the theme configurations to uninstall
     * @return void
     */
    private function uninstallMultiple(array $themeIds)
    {
        $_POST = [
            'sItems' => json_encode($themeIds),
            'grididvalue' => 'themeoptions-grid',
        ];
        $this->controller->actionUninstallMultiple();
    }

    /**
     * Returns the global configuration of the default survey theme.
     * @return TemplateConfiguration
     */
    private function getDefaultThemeConfiguration()
    {
        $theme = TemplateConfiguration::model()->findByAttributes(
            ['template_name' => \Yii::app()->getConfig('defaulttheme')],
            'sid IS NULL AND gsid IS NULL AND uid IS NULL'
        );
        $this->assertNotNull($theme, 'Default theme configuration not found');
        return $theme;
    }

    /**
     * Creates a user with the given global permissions.
     * @param string $prefix Prefix of the user name
     * @param array $permissions Global permissions, e.g. ['templates' => ['read' => 'on']]
     * @return \User
     */
    private function createTestUser($prefix, array $permissions = [])
    {
        $userName = $prefix . '_' . uniqid();
        $user = self::createUserWithPermissions([
            'users_name' => $userName,
            'full_name' => $userName,
            'email' => $userName . '@example.org',
            'lang' => 'auto',
            'password' => 'testpassword123',
        ], $permissions);
        $this->testUsers[] = $user;
        return $user;
    }
}
