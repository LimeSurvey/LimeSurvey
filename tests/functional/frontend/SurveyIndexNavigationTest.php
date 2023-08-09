<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2022-06-30
 * @group navigation
 */
class SurveyIndexNavigationTest extends TestBaseClassWeb
{
    /**
     * Import survey before test
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile = self::$surveysFolder . '/survey_index_navigation_test.lss';
        self::importSurvey($surveyFile);

        // Activate survey.
        self::$testHelper->activateSurvey(self::$surveyId);
    }

    public function testSurveyIndexNavigation()
    {
        $web = self::$webDriver;
        $url = $this->getSurveyUrl();

        try {
            // Open survey.
            $web->get($url);

            // Move from Welcome to first group
            // Click next.
            $web->findElement(WebDriverBy::cssSelector('button[type="submit"][name="move"][value="movenext"]'))->click();

            // Wait max 10 second to find the first group title
            $groupTitleDiv = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-0 .group-title')
                )
            );
            $groupTitle = $groupTitleDiv->getText();
            $this->assertEquals("My first question group", $groupTitle);

            // Move from first group to second group
            // Click on index menu
            $web->findElement(WebDriverBy::cssSelector('#navbar-toggler'))->click();
            $web->findElement(WebDriverBy::cssSelector('[data-navtargetid="index-dropdown"]'))->click();

            // Click on menu option
            $web->findElement(WebDriverBy::cssSelector('#main-dropdown a[href$="move=2"]'))->click();

            // Wait max 10 second to find the second group title
            $groupTitleDiv = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-1 .group-title')
                )
            );
            $groupTitle = $groupTitleDiv->getText();
            $this->assertEquals("My second question group", $groupTitle);

            // Move from second group back to first group
            //Click on index menu
            $web->findElement(WebDriverBy::cssSelector('#navbar-toggler'))->click();
            $web->findElement(WebDriverBy::cssSelector('[data-navtargetid="index-dropdown"]'))->click();

            //Click on menu option
            $web->findElement(WebDriverBy::cssSelector('#main-dropdown a[href$="move=1"]'))->click();

            // Wait max 10 second to find the second group title
            $groupTitleDiv = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-0 .group-title')
                )
            );
            $groupTitle = $groupTitleDiv->getText();
            $this->assertEquals("My first question group", $groupTitle);
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(true, self::$testHelper->javaTrace($ex));
        }
    }
}
