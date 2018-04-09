<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;
use ls\tests\TestBaseClassWeb;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Exception\UnknownServerException;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Exception\ElementNotVisibleException;

/**
 * @since 2017-10-15
 * @group tempcontr
 * @group theme1
 */
class TemplateControllerTest extends TestBaseClassWeb
{
    /**
     * Login etc.
     */
    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }

        // Permission to everything.
        \Yii::app()->session['loginID'] = 1;

        // Browser login.
        self::adminLogin($username, $password);
    }

    /**
     * Test copy a template.
     * @group copytemplate
     */
    public function testCopyTemplate()
    {
        \Yii::app()->session['loginID'] = 1;
        \Yii::import('application.controllers.admin.themes', true);
        \Yii::import('application.helpers.globalsettings_helper', true);

        // Clean up from last test.
        $templateName = 'foobartest';
        \TemplateConfiguration::uninstall($templateName);
        \Template::model()->deleteAll('name = \'foobartest\'');
        \Permission::model()->deleteAllByAttributes(array('permission' => $templateName,'entity' => 'template'));

        // Remove folder from last test.
        $newname = 'foobartest';
        $newdirname  = \Yii::app()->getConfig('userthemerootdir') . "/" . $newname;
        if (file_exists($newdirname)) {
            exec('rm -r ' . $newdirname);
        }

        $config = require(\Yii::app()->getBasePath() . '/config/config-defaults.php');
        // Simulate a POST.
        $_POST['newname'] = $newname;
        // NB: If default theme is not installed, this test will fail.
        $_POST['copydir'] = $config['defaulttheme'];
        $_SERVER['SERVER_NAME'] = 'localhost';

        $contr = new \themes(new \ls\tests\DummyController('dummyid'));
        $contr->templatecopy();

        $flashes = \Yii::app()->user->getFlashes();
        $this->assertEmpty($flashes, 'No flash messages');

        $template = \Template::model()->find(
            sprintf(
                'name = \'%s\'',
                $templateName
            )
        );
        $this->assertNotEmpty($template);
        $this->assertEquals($templateName, $template->name);

        // Clean up.
        \Template::model()->deleteAll('name = \'foobartest\'');
    }

    /**
     * @group extendtheme
     */
    public function testExtendTheme()
    {
        \Yii::import('application.controllers.admin.themes', true);
        \Yii::import('application.helpers.globalsettings_helper', true);

        // Delete theme vanilla_version_1 if present.
        $contr = new \themes(new \ls\tests\DummyController('dummyid'));
        $contr->delete('vanilla_version_1');

        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl('admin/themeoptions');

        // NB: Less typing.
        $w = self::$webDriver;

        $w->get($url);

        sleep(1);

        try {
            // If not clickable, dismiss modal.
            $button = $w->findElement(WebDriverBy::cssSelector('#admin-notification-modal .modal-footer .btn'));
            $button->click();

            sleep(1);
        } catch (\Exception $ex) {
            // Do nothing.
        }

        try {
            // Click "Theme editor" for vanilla theme.
            $button = $w->findElement(WebDriverBy::id('template_editor_link_vanilla'));
            $button->click();

            $button = $w->findElement(WebDriverBy::id('button-extend-vanilla'));
            $button->click();

            sleep(1);

            $w->switchTo()->alert()->sendKeys('vanilla_version_1');
            $w->switchTo()->alert()->accept();

            sleep(1);

            $header = $w->findElement(WebDriverBy::className('theme-editor-header'));
            $this->assertEquals($header->getText(), 'Theme editor: vanilla_version_1');
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
