<?php

namespace ls\tests;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * Subclass of Facebook webdriver.
 * This class contains helper methods to interact with a LimeSurvey
 * survey, like filling in questions, going to next question group,
 * changing language etc.
 */
class LimeSurveyWebDriver extends RemoteWebDriver
{
    /**
     * Change language using the <select> element
     * on survey welcome page.
     * @param string $newLang Like 'de' or 'en'.
     * @return void
     */
    public function changeLanguageSelect($newLang)
    {
        // Try with welcome page select first.
        $langSelectOption = $this->findElement(
            WebDriverBy::cssSelector(
                sprintf(
                    '#lang option[value="%s"]',
                    $newLang
                )
            )
        );
        $langSelectOption->click();
        $langSubmit = $this->findElement(
            WebDriverBy::cssSelector('button[value=changelang]')
        );
        $langSubmit->click();
    }

    /**
     * Change language using links in top-right corner.
     * @param string $newLang Like 'de' or 'en'.
     * @return void
     */
    public function changeLanguage($newLang)
    {
        $langSelect = $this->findElement(
            WebDriverBy::cssSelector('.form-change-lang')
        );
        $langSelect->click();
        $langSelectLink = $this->findElement(
            WebDriverBy::cssSelector(
                sprintf(
                    '.form-change-lang a[data-limesurvey-lang="%s"]',
                    $newLang
                )
            )
        );
        $langSelectLink->click();
    }

    /**
     * @param string $sgqa Like 123X345X567
     * @param string $answer Answer to question.
     * @return void
     */
    public function answerTextQuestion($sgqa, $answer)
    {
        $firstQuestion = $this->findElement(
            WebDriverBy::cssSelector(
                sprintf(
                    'textarea[name="%s"], input[name="%s"]',
                    $sgqa,
                    $sgqa
                )
            )
        );
        $firstQuestion->sendKeys($answer);
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

    /**
     * Alias for next().
     */
    public function submit()
    {
        $this->next();
    }

    /**
     * @return void
     */
    public function clickButton($id)
    {
        $button = $this->findElement(WebDriverBy::id($id));
        $button->click();
    }

    /**
     * Debug method to dump all text in <body></body>.
     * @return void
     */
    public function dumpBody()
    {
        $body = $this->findElement(WebDriverBy::tagName('body'));
        var_dump('body text = ' . $body->getText());
    }

    /**
     * @param string $id
     * @return Element
     */
    public function findById($id)
    {
        return $this->findElement(WebDriverBy::id($id));
    }

    /**
     * @param string $css
     * @return ElementCollection
     */
    public function findByCss($css)
    {
        return $this->findElement(WebDriverBy::cssSelector($css));
    }
}
