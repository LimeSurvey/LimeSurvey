<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2022-06-30
 * @group navigation
 */
class SurveyNavigationTest extends TestBaseClassWeb
{
    /**
     * Import survey before test
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_navigation.lss';
        self::importSurvey($surveyFile);

        // Activate survey.
        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * Check Next, Previous and Submit
     */
    public function testSurveyNavigation()
    {
        $web = self::$webDriver;
        $url = $this->getSurveyUrl();

        try {
            // Open survey.
            $web->get($url);

            // Move from Welcome to first group
            // Click next.
            $web->clickButton('ls-button-submit');

            // Wait max 10 second to find the first group title
            $groupTitleDiv = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-0 .group-title')
                )
            );
            $groupTitle = $groupTitleDiv->getText();
            $this->assertEquals("First group", $groupTitle);

            // Move from first group to second group
            // Click next.
            $web->clickButton('ls-button-submit');

            // Wait max 10 second to find the second group title
            $groupTitleDiv = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-1 .group-title')
                )
            );
            $groupTitle = $groupTitleDiv->getText();
            $this->assertEquals("Second group", $groupTitle);

            // Move from second group back to first group
            // Click previous.
            $web->clickButton('ls-button-previous');

            // Wait max 10 second to find the first group title
            $groupTitleDiv = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-0 .group-title')
                )
            );
            $groupTitle = $groupTitleDiv->getText();
            $this->assertEquals("First group", $groupTitle);

            // Move to second group again in order to submit
            // Click next.
            $web->clickButton('ls-button-submit');

            // Wait max 10 second to find the second group title
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-1 .group-title')
                )
            );

            // Click submit.
            $web->clickButton('ls-button-submit');

            // Wait max 10 second to find the completed message
            $completedMessage = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::className('completed-text')
                )
            );
            $this->assertNotNull($completedMessage);

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(true, self::$testHelper->javaTrace($ex));
        }
    }

    /**
     * Check Resume Later navigation
     */
    public function testResumeLaterNavigation()
    {
        $web = self::$webDriver;
        $url = $this->getSurveyUrl();

        try {
            // Open survey.
            $web->get($url);

            // Move from Welcome to first group
            // Click next.
            $web->clickButton('ls-button-submit');

            // Wait max 10 second to find the first group title
            $groupTitleDiv = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-0 .group-title')
                )
            );
            $groupTitle = $groupTitleDiv->getText();
            $this->assertEquals("First group", $groupTitle);

            // Click on index menu
            $web->findElement(WebDriverBy::cssSelector('#navbar-toggler'))->click();
            // Click "Resume later"
            $resumeLater = $web->findByLinkText('Resume later');
            $resumeLater->click();

            // Wait max 10 second to find the save message
            $saveMessage = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::className('save-message')
                )
            );
            $this->assertNotNull($saveMessage);

            // Click "Return to survey"
            $returnToSurvey = $web->findByLinkText('Return to survey');
            $returnToSurvey->click();

            // Wait max 10 second to find the first group title
            $groupTitleDiv = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-0 .group-title')
                )
            );
            $groupTitle = $groupTitleDiv->getText();
            $this->assertEquals("First group", $groupTitle);

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(true, self::$testHelper->javaTrace($ex));
        }
    }
}
