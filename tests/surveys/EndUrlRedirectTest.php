<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2018-06-20
 * @group endurl
 */
class EndUrlRedirectTest extends TestBaseClassWeb
{
    /**
     * Setup before class.
     */
    public static function setupBeforeClass()
    {
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_928171.lss';
        self::importSurvey($surveyFile);

        // Activate survey.
        self::$testHelper->activateSurvey(self::$surveyId);

        // Don't use "inherit all" so we can switch ajaxmod on/off without
        // affecting global state.
        $templateConfiguration = self::$testSurvey->templateConfiguration;
        $globalConfiguration   = $templateConfiguration->getGlobalParent();
        $templateConfiguration->options = $globalConfiguration->options;
        $templateConfiguration->update();
    }

    /**
     * @return void
     */
    public function testWithAjaxMode()
    {
        $this->doTheTest("on");
    }

    /**
     * @return void
     */
    public function testWithoutAjaxMode()
    {
        $this->doTheTest("off");
    }

    /**
     * @param string $ajaxmode "on" or "off"
     * @return void
     */
    public function doTheTest($ajaxmode)
    {
        $templateConfiguration = self::$testSurvey->templateConfiguration;
        $templateConfiguration->setOption("ajaxmode", $ajaxmode);

        // To make writing shorter.
        $web = self::$webDriver;
        $url = $this->getSurveyUrl();

        try {
            // Open survey.
            $web->get($url);

            // Click next.
            $web->next();

            // Wait for Ajax to load page.
            sleep(1);

            // Submit survey.
            $web->next();

            $body = $web->findElement(WebDriverBy::tagName('body'));
            $text = $body->getText();
            $this->assertTrue(strpos($text, 'Gmail') !== false, 'Ended up on google.com with text ' . $text);

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, 'EndUrlRedirectTest' . $ajaxmode);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                .  'Screenshot taken.' . PHP_EOL
                .  self::$testHelper->javaTrace($ex)
            );
        }
    }
}
