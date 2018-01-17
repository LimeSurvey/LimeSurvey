<?php

namespace ls\tests;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

/**
 * Subclass of Facebook webdriver.
 * This class contains helper methods to interact with a LimeSurvey
 * survey, like filling in questions, going to next question group,
 * changing language etc.
 */
class LimeSurveyWebDriver extends RemoteWebDriver
{
    /**
     * @param string $newLang Like 'de' or 'en'.
     * @return void
     */
    public function changeLanguage($newLang)
    {
        $langSelectOption = $this->findElement(
            WebDriverBy::cssSelector(
                sprintf(
                    '#langchangerSelectMain option[value="%s"]',
                    $newLang
                )
            )
        );
        $langSelectOption->click();
    }

    /**
     * Go to next question/question group.
     * @return void
     */
    public function next()
    {
        $nextButton = $this->findElement(WebDriverBy::id('ls-button-submit'));
        $nextButton->click();
    }
}
