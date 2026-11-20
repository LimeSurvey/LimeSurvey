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

            // Click on menu option (button with the step to move to, see questionIndexHelper)
            $web->findElement(WebDriverBy::cssSelector("#main-dropdown [data-limesurvey-submit='{ \"move\":2 }']"))->click();

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
            $web->findElement(WebDriverBy::cssSelector("#main-dropdown [data-limesurvey-submit='{ \"move\":1 }']"))->click();

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

    /**
     * Same navigation with the Vanilla theme: the index is a Bootstrap dropdown opened by a button, entries are buttons
     */
    public function testSurveyIndexNavigationVanilla()
    {
        \Survey::model()->updateByPk(self::$surveyId, ['template' => 'vanilla']);
        $web = self::$webDriver;
        $url = $this->getSurveyUrl();

        try {
            // Open survey.
            $web->get($url);

            // Move from Welcome to first group
            $web->findElement(WebDriverBy::cssSelector('button[type="submit"][name="move"][value="movenext"]'))->click();
            $groupTitleDiv = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-0 .group-title')
                )
            );
            $this->assertEquals("My first question group", $groupTitleDiv->getText());

            // Move from first group to second group with the index menu
            $this->openVanillaIndexMenu();
            $this->clickVanillaIndexEntry(2);
            $groupTitleDiv = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-1 .group-title')
                )
            );
            $this->assertEquals("My second question group", $groupTitleDiv->getText());

            // Move from second group back to first group
            $this->openVanillaIndexMenu();
            $this->clickVanillaIndexEntry(1);
            $groupTitleDiv = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-0 .group-title')
                )
            );
            $this->assertEquals("My first question group", $groupTitleDiv->getText());
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(true, self::$testHelper->javaTrace($ex));
        } finally {
            \Survey::model()->updateByPk(self::$surveyId, ['template' => 'inherit']);
        }
    }

    /**
     * Open the Vanilla index menu, the top menu can be collapsed on small screen
     */
    private function openVanillaIndexMenu()
    {
        $web = self::$webDriver;
        $toggler = $web->findElements(WebDriverBy::cssSelector('#survey-nav .navbar-toggler'));
        if (!empty($toggler) && $toggler[0]->isDisplayed()) {
            $toggler[0]->click();
        }
        $web->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('index-menu'))
        )->click();
    }

    /**
     * Click on the index entry to move to the step (button with data-limesurvey-submit, see questionIndexHelper)
     * @param int $step
     */
    private function clickVanillaIndexEntry($step)
    {
        self::$webDriver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::cssSelector("nav[aria-labelledby='index-menu'] [data-limesurvey-submit='{ \"move\":" . $step . " }']")
            )
        )->click();
    }
}
