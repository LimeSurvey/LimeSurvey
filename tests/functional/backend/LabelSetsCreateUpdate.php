<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;

/**
 * Class AdminViewsTest
 * This test loops through all basic admin view pages and cheks if they open withour errors
 *
 * @package ls\tests
 * @group adminviews
 */
class LabelSetsCreateUpdate extends TestBaseClassWeb
{

    /**
     * @var integer $superadminlabelSetId sample
     */
    private static $superadminlabelSetId;
    /**
     * @var integer $userlabelSetId sample
     */
    private static $userlabelSetId;
    /**
     * @var integer $userId used for action
     */
    private static $userId;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();
        /* Create a random label sets (no need values) */
        $oLabelSet = new \LabelSet();
        $oLabelSet->owner_id = 1;
        $oLabelSet->label_name = \Yii::app()->securityManager->generateRandomString(50);
        $oLabelSet->languages = 'en';
        $oLabelSet->save();
        self::$superadminlabelSetId = $oLabelSet->lid;
        /* Create an random user and login */
        $username = "test_" . \Yii::app()->securityManager->generateRandomString(8);
        $password = createPassword();
        self::$userId = \User::insertUser($username, $password, 'Test user for label sets', 1, 'user@example.org');
        $oLabelSet = new \LabelSet();
        $oLabelSet->owner_id = self::$userId;
        $oLabelSet->label_name = \Yii::app()->securityManager->generateRandomString(50);
        $oLabelSet->languages = 'en';
        $oLabelSet->save();
        self::$userlabelSetId = $oLabelSet->lid;

        \User::model()->updateByPk(self::$userId, ['lang' => 'en']);
        \Permission::model()->setGlobalPermission(self::$userId, 'labelsets', array('create_p'));
        \Permission::model()->setGlobalPermission(self::$userId, 'auth_db', array('read_p'));
        self::adminLogin($username, $password);
    }

    /**
     * Check if link is visible
     */
    public function testLinkVisibility()
    {
        $urlMan = \Yii::app()->urlManager;
        // Go to admin
        $url = $urlMan->createUrl('admin');
        self::$webDriver->get($url);
        self::$webDriver->findElement(WebDriverBy::className('mainmenu-dropdown-toggle'))->click();
        $urlToCheck = $urlMan->createUrl('admin/labels/sa/view');
        try {
            $linkLabels = self::$webDriver->wait(1)->until(
                WebDriverExpectedCondition::visibilityOf(
                    self::$webDriver->findElement(WebDriverBy::className('link-labels'))
                )
            );
        } catch (\Exception $e) {
            //throw new Exception($e->getMessage());
            $screenshot = self::$webDriver->takeScreenshot();
            file_put_contents(self::$screenshotsFolder . '/testLinkVisibility.png', $screenshot);
        }
        $this->assertNotEmpty(
            $linkLabels,
            'Unable to find link to edit Label sets'
        );
    }

    /**
     * Check access disable if not owner
     */
    public function testViewInvalidLabelSet()
    {
        $urlMan = \Yii::app()->urlManager;
        $url = $urlMan->createUrl('admin/labels/sa/view', ['lid' => self::$superadminlabelSetId]);
        self::$webDriver->get($url);
        $title = self::$webDriver->getTitle();
        if (App()->getConfig('debug')) {
            $this->assertEquals("CHttpException", trim($title));
        } else {
            $this->assertEquals("403: Forbidden", trim($title));
        }
    }

    /**
     * Check access disable if not owner
     */
    public function testDeleteInvalidLabelSet()
    {
        $urlMan = \Yii::app()->urlManager;
        $url = $urlMan->createUrl('admin/labels/sa/view', ['lid' => self::$userlabelSetId]);
        self::$webDriver->get($url);
        /* Update the data-post-datas via JS */
        self::$webDriver->executeScript("$('#create-import-button').attr('data-post-datas','{\"lid\":" . self::$superadminlabelSetId . "}')");
        /* Delete it */
        try {
            $labelDeleteButton = self::$webDriver->wait(1)->until(
                WebDriverExpectedCondition::visibilityOf(
                    self::$webDriver->findElement(WebDriverBy::id('create-import-button'))
                )
            );
            $labelDeleteButton->click();
            $confirmButton = self::$webDriver->wait(3)->until(
                WebDriverExpectedCondition::visibilityOf(
                    self::$webDriver->findElement(WebDriverBy::id('actionBtn'))
                )
            );
        } catch (\Exception $e) {
            //throw new Exception($e->getMessage());
            $screenshot = self::$webDriver->takeScreenshot();
            file_put_contents(self::$screenshotsFolder . '/testDeleteInvalidLabelSet.png', $screenshot);
        }
        $this->assertNotEmpty(
            $confirmButton,
            'Unable to find confirm button to delete Label set'
        );
        $confirmButton->click();
        $title = self::$webDriver->getTitle();
        if (App()->getConfig('debug')) {
            $this->assertEquals("CHttpException", trim($title));
        } else {
            $this->assertEquals("403: Forbidden", trim($title));
        }
    }

    /**
     * Check access to list, create, view
     */
    public function testCreateAndDeleteLabelSet()
    {
        $urlMan = \Yii::app()->urlManager;
        // Go to User Management page
        $url = $urlMan->createUrl('admin/labels/sa/view');
        self::$webDriver->get($url);
        try {
            // Click on "Add Label set" button.
            $addLabelButton = self::$webDriver->wait(1)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('create-import-button')
                )
            );
            $addLabelButton->click();
            // Wait for form
            $labelNameInput = self::$webDriver->wait(1)->until(
                WebDriverExpectedCondition::visibilityOf(
                    self::$webDriver->findElement(WebDriverBy::id('label_name'))
                )
            );
            /* Fill value and save */
            $labelNameInput->clear()->sendKeys("Sample label set");
            /* Save */
            self::$webDriver->findElement(WebDriverBy::id('save-form-button'))->click();
            // Wait for label set title and check it
            $labelNameTitle = self::$webDriver->wait(1)->until(
                WebDriverExpectedCondition::visibilityOf(
                    self::$webDriver->findElement(WebDriverBy::className('pagetitle'))
                )
            );
        } catch (\Exception $e) {
            //throw new Exception($e->getMessage());
            $screenshot = self::$webDriver->takeScreenshot();
            file_put_contents(self::$screenshotsFolder . '/testCreateDeleteLabelSet-Add.png', $screenshot);
        }
        $this->assertEquals(trim($labelNameTitle->getText()), "Labels - Sample label set");
        /* Delete it */
        try {
            $labelDeleteButton = self::$webDriver->wait(1)->until(
                WebDriverExpectedCondition::visibilityOf(
                    self::$webDriver->findElement(WebDriverBy::id('create-import-button'))
                )
            );
            $labelDeleteButton->click();
            $confirmButton = self::$webDriver->wait(3)->until(
                WebDriverExpectedCondition::visibilityOf(
                    self::$webDriver->findElement(WebDriverBy::id('actionBtn'))
                )
            );
        } catch (\Exception $e) {
            //throw new Exception($e->getMessage());
            $screenshot = self::$webDriver->takeScreenshot();
            file_put_contents(self::$screenshotsFolder . '/testCreateDeleteLabelSet-Delete.png', $screenshot);
        }
        $this->assertNotEmpty(
            $confirmButton,
            'Unable to find confirm button to delete Label set'
        );
        $confirmButton->click();
        /* Check if deleted : one Label sets must be shown only */
        $isEmptyElement = self::$webDriver->wait(3)->until(
            WebDriverExpectedCondition::visibilityOf(
                self::$webDriver->findElement(WebDriverBy::cssSelector('#labelsets-grid table.items tbody tr'))
            )
        );
        $lineCount = count(self::$webDriver->findElements(WebDriverBy::cssSelector("#labelsets-grid table.items tbody tr")));
        $this->assertEquals($lineCount, 1);
    }

    /**
     * Check access to list and view
     */
    public function testViewListLabelSet()
    {
        $urlMan = \Yii::app()->urlManager;
        // Go to User Management page
        $url = $urlMan->createUrl('admin/labels/sa/view');
        self::$webDriver->get($url);
        /* Check if deleted : one Label sets must be shown only */
        self::$webDriver->wait(1)->until(
            WebDriverExpectedCondition::visibilityOf(
                self::$webDriver->findElement(WebDriverBy::cssSelector('#labelsets-grid table.items tbody tr'))
            )
        );
        $isEmptyCount = count(self::$webDriver->findElements(WebDriverBy::cssSelector('#labelsets-grid table.items tbody tr .empty')));
        $this->assertEquals($isEmptyCount, 0);
    }


    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        \LabelSet::model()->deleteByPk(self::$superadminlabelSetId);
        \LabelSet::model()->deleteByPk(self::$userlabelSetId);
        \User::model()->deleteByPk(self::$userId);
        parent::tearDownAfterClass();
    }
}
