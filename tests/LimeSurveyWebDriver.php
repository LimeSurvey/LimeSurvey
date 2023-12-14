<?php

namespace ls\tests;

use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
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
        $nextButton = $this->wait(5)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('ls-button-submit')
            )
        );
        $nextButton->click();
    }

    /**
     * Wait until element with $id gets clickable. Max 5 sec.
     *
     * @param string $id
     * @return void
     */
    public function waitById($id)
    {
        $this->wait(5)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id($id)
            )
        );
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
     * @todo Should be clickById
     */
    public function clickButton($id)
    {
        $button = $this->findElement(WebDriverBy::id($id));
        $button->click();
    }

    /**
     * Click on element found by $css
     *
     * @param string $css
     * @return void
     */
    public function clickByCss($css)
    {
        $elem = $this->findByCss($css);
        $elem->click();
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
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findById($id)
    {
        return $this->findElement(WebDriverBy::id($id));
    }

    /**
     * @param string $name Name of <input>
     * @return \Facebook\WebDriver\Remote\RemoteWebElement?
     */
    public function findByName($name)
    {
        return $this->findElement(WebDriverBy::name($name));
    }

    /**
     * @param string $css
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findByCss($css)
    {
        return $this->findElement(WebDriverBy::cssSelector($css));
    }

    public function findManyByCss($css)
    {
        return $this->findElements(WebDriverBy::cssSelector($css));
    }

    /**
     * @param string $text
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findByLinkText($text)
    {
        return $this->findElement(WebDriverBy::linkText($text));
    }

    /**
     * @param string $text
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findByPartialLinkText($text)
    {
        return $this->findElement(WebDriverBy::partialLinkText($text));
    }

    /**
     * Click "Close" on notification modal.
     *
     * @return void
     */
    public function dismissModal()
    {
        try {
            // If not clickable, dismiss modal.
            $button = $this->findElement(
                WebDriverBy::cssSelector('#admin-notification-modal .modal-footer .btn')
            );
            $button->click();
            sleep(1);
        } catch (\Exception $ex) {
            // Do nothing.
        }
    }

    /**
     * Scroll to the bottom of the page
     * @see https://stackoverflow.com/questions/45610679/how-can-i-scroll-page-in-php-webdriver
     * @return void
     */
    public function scrollToBottom()
    {
        $this->executeScript('window.scrollTo(0,document.body.scrollHeight);');
        sleep(1);
    }

    public function scrollToTop()
    {
        $this->executeScript('window.scrollTo(0, 0);');
    }

    /**
     * Fixes php-webdriver error when scrolling and instead use the browsers function
     * @param $element RemoteWebElement
     * @return RemoteWebElement
     */
    public function click(RemoteWebElement $element): RemoteWebElement
    {
        $this->executeScript('window.scrollTo({top: (arguments[0].offsetTop + arguments[0].offsetHeight - window.innerHeight), behavior: "instant"});', [$element]);
        return $element->click();
    }
}
