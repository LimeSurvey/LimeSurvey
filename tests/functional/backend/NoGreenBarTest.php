<?php

namespace ls\tests;

use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * Small example test from workshop.
 *
 * @see https://bugs.gitit-tech.com/view.php?id=15336
 */
class NoGreenBarTest extends TestBaseClassWeb
{
    /**
     * Setup green bar test.
     */
    public function setUp(): void
    {
        // Import suprvey.
        $surveyFile =  'tests/data/surveys/survey_archive_358746_no_green_bar.lsa';
        self::importSurvey($surveyFile);

        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }

        // Browser login.
        self::adminLogin($username, $password);

        // Permission to everything.
        \Yii::app()->session['loginID'] = 1;

        $web = self::$webDriver;

        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/tokens',
            [
                'sa'=>'addnew',
                'surveyid'=>self::$testSurvey->sid,
            ]
        );
        $web = self::$webDriver;
        $web->get($url);
        $input = $web->findById('firstname');
        $input->sendKeys('dummy name');
        sleep(1);
        self::ignoreAdminNotification();
        self::ignoreAdminNotification();
        $savebutton = $web->findById('save-and-close-button');
        $savebutton->click();
        sleep(1);
    }

    /**
     * Test that we have green bar in confirm view.
     */
    public function testNoGreenBar()
    {
        $web = self::$webDriver;
        try {
            $web->findById('breadcrumb-container');
            $this->assertTrue(true, 'Found green bar');
        } catch (NoSuchElementException $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertTrue(false, 'Found no green bar, NoSuchElementException');
        }
    }
}
