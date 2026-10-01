<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClassWeb;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Remote\LocalFileDetector;

/**
 * This test must be run as the web server user, e.g. "sudo -u www-data ..."
 *
 * @since 2017-10-15
 * @group tempcontr
 * @group theme1
 */
class ThemeControllerTest extends TestBaseClassWeb
{
    /**
     * Login etc.
     */
    public static function setupBeforeClass(): void
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

        \Yii::import('application.controllers.admin.Themes', true);


        $contr = new \themes(new \ls\tests\DummyController('dummyid'));
        $_POST['templatename'] = 'vanilla_version_1';
        $contr->delete();
        unset($_POST['templatename']);

        $_POST['templatename'] = 'vanilla_version_renamed';
        $contr->delete();
        unset($_POST['templatename']);

        $_POST['templatename'] = 'vanilla_test_3';
        $contr->delete();
        unset($_POST['templatename']);

        //$flashes = \Yii::app()->session['aFlashMessage'];
    }

    /**
     * Test copy a template.
     * @group copytemplate
     * TODO: Marked as incomplete, cause there is an error inside it.
     */
    public function testCopyTemplate()
    {
        \Yii::app()->session['loginID'] = 1;

        // TODO: Clean up should be inside teardown()!
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
     * @todo Split up in separate tests.
     */
    public function testExtendTheme()
    {
        \Yii::import('application.controllers.admin.Themes', true);


        // Delete theme vanilla_version_1 if present.
        $contr = new \themes(new \ls\tests\DummyController('dummyid'));
        $_POST['templatename'] = 'vanilla_version_1';
        $contr->delete();
        unset($_POST['templatename']);

        //foreach (App()->session['aFlashMessage'] as $flash) {
            //var_dump($flash['message']);
        //}

        // ...and the renamed theme.
        $contr = new \themes(new \ls\tests\DummyController('dummyid'));
        $_POST['templatename'] = 'vanilla_version_renamed';
        $contr->delete();
        unset($_POST['templatename']);

        //foreach (App()->session['aFlashMessage'] as $flash) {
            //var_dump($flash['message']);
        //}

        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');

        // NB: Less typing.
        $w = self::$webDriver;

        try {
            // Navigate directly to the theme editor for vanilla.
            // The "Theme editor" link in the grid uses a dropdown portaled to <body>,
            // making it unreliable to click via WebDriver.
            $url = $urlMan->createUrl('admin/themes/sa/view', ['templatename' => 'vanilla']);
            $w->get($url);

            // Wait for possible modal.
            sleep(1);

            $w->dismissModal();

            $button = $w->wait(20)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('button-extend-vanilla')
                )
            );
            $button->click();

            sleep(1);

            // Write new theme name.
            $w->switchTo()->alert()->sendKeys('vanilla_version_1');
            $w->switchTo()->alert()->accept();

            sleep(1);

            // Check that we have the correct page header.
            // Wait for the page to redirect to the new theme editor.
            $w->wait(20)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath("//h1[contains(.,'Theme editor: vanilla_version_1')]")
                )
            );

            // Check that the preview is working.
            $iframe = $w->findElement(WebDriverBy::id('previewiframe'));
            $w->switchTo()->frame($iframe);
            $p = $w->findElement(WebDriverBy::tagName('p'));
            $this->assertEquals($p->getText(), 'This is a sample survey description. It could be quite long.');
            $w->switchTo()->defaultContent();

            // Try to save to local theme (copies inherited file locally and reloads).
            $button = $w->findElement(WebDriverBy::id('button-save-changes'));
            $button->click();

            // Wait for the page to reload after save — the button should now show "Save".
            $w->wait(20)->until(
                WebDriverExpectedCondition::stalenessOf($button)
            );
            $button = $w->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('button-save-changes')
                )
            );
            $value  = trim($button->getText()) ?: $button->getAttribute('value');
            $this->assertEquals('Save', $value, 'Button text is ' . $value);

            // Test rename the theme.
            $button = $w->findElement(WebDriverBy::id('button-rename-theme'));
            $button->click();

            sleep(1);

            // Write new theme name.
            $w->switchTo()->alert()->sendKeys('vanilla_version_renamed');
            $w->switchTo()->alert()->accept();

            sleep(1);

            // Check that we have the renamed page header.
            $w->wait(20)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath("//h1[contains(.,'Theme editor: vanilla_version_renamed')]")
                )
            );
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * Test upload and delete file.
     * @todo Don't test two things in one test
     * @todo Must be run as web user for templatecopy() to work
     */
    public function testUploadFile()
    {
        \Yii::import('application.controllers.admin.Themes', true);


        // Clear flashes.
        \Yii::app()->session['aFlashMessage'] = [];

        // Extend vanilla.
        $dummy = new \ls\tests\DummyController('dummyid');
        $contr = new \themes($dummy);
        $_POST['copydir'] = 'vanilla';
        $_POST['newname'] = 'vanilla_version_1';
        // NB: Must run as web user to get correct permissions here.
        $contr->templatecopy();
        exec('sudo chmod -R 777 ./upload'); // Add permisions to ./upload directory, neede for CI pipeline
        //$dummy->lastAction;
        //$flashes = \Yii::app()->session['aFlashMessage'];

        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/themes',
            [
                'sa'           => 'view',
                'editfile'     => 'layout_global.twig',
                'screenname'   => 'welcome',
                'templatename' => 'vanilla_version_1'
            ]
        );

        // NB: Less typing.
        $w = self::$webDriver;

        try {
            $w->get($url);

            // Wait for possible modal to appear.
            sleep(1);

            $w->dismissModal();
            $w->dismissModal();

            // Test upload file.
            $fileInput = $w->findElement(WebDriverBy::id('upload_file'));
            $fileInput->setFileDetector(new LocalFileDetector());
            $file = ROOT . '/tests/data/file_upload/dalahorse.jpg';
            $this->assertTrue(file_exists($file));
            $fileInput->sendKeys($file)->submit();

            sleep(2);

            // Check that file is last in list.
            $files = $w->findElements(WebDriverBy::className('other-files-filename'));
            $text = $files[count($files) - 1]->getText();
            $this->assertEquals($text, 'dalahorse.jpg', 'Did not find dalahorse, but ' . $text);

            $w->executeScript('window.scrollTo(0,document.body.scrollHeight / 2);');
            sleep(1);

            // Delete file.
            $deleteButtons = $w->findElements(WebDriverBy::className('other-files-delete-button'));
            $deleteButton  = $deleteButtons[count($deleteButtons) - 1];
            $deleteButton->click();
            $w->switchTo()->alert()->accept();

            sleep(3);

            // Check that file does not exist in list anymore.
            $files = $w->findElements(WebDriverBy::className('other-files-filename'));
            foreach ($files as $file) {
                $this->assertNotEquals($file->getText(), 'dalahorse.jpg');
            }
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * @group themeexport
     */
    public function testExportAndImport()
    {

        \Yii::import('application.controllers.admin.Themes', true);


        // Make sure there's no vanilla_test_3 yet.
        $temp = \Template::model()->findAll(
            'name = :name',
            ['name' => 'vanilla_test_3']
        );
        $this->assertEmpty($temp, 'vanilla_test_3 is not yet created');

        // Create URL.
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/themes',
            [
                'sa'           => 'view',
                'editfile'     => 'layout_global.twig',
                'screenname'   => 'welcome',
                'templatename' => 'vanilla'
            ]
        );

        // NB: Less typing.
        $w = self::$webDriver;

        try {
            $w->get($url);

            // Wait for possible modal to appear.
            // Two modals on fresh install.
            sleep(1);
            $w->dismissModal();
            $w->dismissModal();

            // Extend vanilla.
            $w->clickButton('button-extend-vanilla');
            $w->switchTo()->alert()->sendKeys('vanilla_test_3');
            $w->switchTo()->alert()->accept();
            sleep(10);

            // Make sure vanilla_test_3 was created.
            $temp = \Template::model()->findAll(
                'name = :name',
                ['name' => 'vanilla_test_3']
            );
            $this->assertNotEmpty($temp, 'vanilla_test_3 was created');

            // Export theme directly via PHP (browser file downloads cannot be verified server-side).
            $oTemplate = \Template::getInstance('vanilla_test_3', null, null, true);
            $zipfile = ROOT . '/tmp/vanilla_test_3.zip';
            if (file_exists($zipfile)) {
                unlink($zipfile);
            }
            $this->assertFalse(file_exists($zipfile), 'Zip file should not exist before export');
            $zip = new \LimeSurvey\Zip();
            $openResult = $zip->open($zipfile, \ZipArchive::CREATE);
            $this->assertTrue($openResult === true, 'ZipArchive::open() failed with code ' . var_export($openResult, true));
            $zipHelper = new \LimeSurvey\Helpers\ZipHelper($zip);
            $zipHelper->addFolder($oTemplate->path);
            $zip->close();
            $this->assertTrue(file_exists($zipfile), 'Zip export was created');
            $this->assertGreaterThan(0, filesize($zipfile), 'Zip export should not be empty');

            // Delete the theme via browser to test import.
            // The delete button opens a Bootstrap confirm modal, not a native alert.
            $w->clickButton('button-delete');
            $confirmBtn = $w->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('#confirm-delete-modal .selector--button-confirm')
                )
            );
            // Wait for shown.bs.modal to fire — it binds the click handler
            // only after the fade animation completes.
            sleep(1);
            $confirmBtn->click();

            sleep(2);

            $url = $urlMan->createUrl('themeOptions/index');
            $w->get($url);
            sleep(1);
            $button = $w->findById('uploadandinstall');
            $button->click();

            sleep(1);

            // Test upload file.
            $fileInput = $w->findElement(WebDriverBy::id('the_file'));
            $fileInput->setFileDetector(new LocalFileDetector());
            $file = ROOT . '/tmp/vanilla_test_3.zip';
            $this->assertTrue(file_exists($file));
            $fileInput->sendKeys($file)->submit();

            sleep(1);

            $w->clickButton('button-open-theme');

            sleep(1);

            // Check that we have the correct page header.
            $w->wait(20)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath("//h1[contains(.,'Theme editor: vanilla_test_3')]")
                )
            );
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
