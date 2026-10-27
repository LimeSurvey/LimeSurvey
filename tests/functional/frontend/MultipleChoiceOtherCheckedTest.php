<?php

namespace ls\tests;

use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;

/**
 * Ticking Other without text in a multiple choice question is saved in Q{qid}_Cotherchecked.
 *
 * @since 2026-10-27
 * @group multiplechoice
 */
class MultipleChoiceOtherCheckedTest extends TestBaseClassWeb
{
    /** @var \Question[] questions by title */
    protected $questions;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_213203_other_checked.lss');
    }

    /**
     * Activate the survey at every test, so every test starts with an empty response table.
     */
    public function setUp(): void
    {
        self::$testHelper->activateSurvey(self::$surveyId);
        $this->questions = [];
        foreach (\Question::model()->findAll("sid = :sid", [":sid" => self::$surveyId]) as $question) {
            $this->questions[$question->title] = $question;
        }
    }

    public function tearDown(): void
    {
        self::$testHelper->deactivateSurvey(self::$surveyId);
        // The archived table is named by second, see MultipleChoiceMandatoryWithCommentTest.
        sleep(1);
    }

    /**
     * Test 1: not mandatory, tick Other without text, Next and Previous.
     */
    public function testTickOtherWithoutText()
    {
        $this->tickOtherWithoutTextNextPrevious(self::$webDriver, true);
    }

    /**
     * Test 2: the same as test 1 with JavaScript disabled.
     */
    public function testTickOtherWithoutTextWithoutJavaScript()
    {
        // The grid runs one session at a time.
        self::$webDriver->quit();
        $web = $this->createWebDriverWithoutJavaScript();
        try {
            $this->tickOtherWithoutTextNextPrevious($web, false);
        } finally {
            $web->quit();
            self::$webDriver = self::$testHelper->getWebDriver();
            self::$webDriver->manage()->timeouts()->implicitlyWait(5);
        }
    }

    /**
     * Test 3: typing ticks Other, clearing the text and unticking empties the column.
     */
    public function testTypeTextThenClearAndUntick()
    {
        $web = self::$webDriver;
        $other = $this->otherFieldName('q1');
        try {
            $web->get($this->getSurveyUrl());
            $web->answerTextQuestion($other, 'some text');
            $this->assertTrue($web->findById('answer' . $other . 'cbox')->isSelected(), 'Typing ticks Other');

            $this->next($web, 'q2');
            $response = $this->getOnlyResponse();
            $this->assertSame('Y', $response[$other . 'checked']);
            $this->assertSame('some text', $response[$other]);

            $this->previous($web, 'q1');
            $web->findById('answer' . $other)->clear();
            $web->findById('label-' . $other . '-other')->click();
            $this->assertFalse($web->findById('answer' . $other . 'cbox')->isSelected(), 'Other is unticked');

            $this->next($web, 'q2');
            $response = $this->getOnlyResponse();
            $this->assertEmpty($response[$other . 'checked'], 'The tick is cleared');
            $this->assertEmpty($response[$other], 'The text is cleared');
        } catch (\Exception $ex) {
            $this->failWithScreenshot($web, $ex);
        }
    }

    /**
     * Test 4: mandatory, only Other ticked without text is blocked.
     */
    public function testMandatoryOnlyOtherWithoutTextIsBlocked()
    {
        $this->assertMandatoryBlocked(false);
    }

    /**
     * Test 5: mandatory, Option A and Other ticked without text is blocked.
     */
    public function testMandatoryOptionAndOtherWithoutTextIsBlocked()
    {
        $this->assertMandatoryBlocked(true);
    }

    /**
     * Test 6: save and resume later restores the tick.
     */
    public function testSaveAndResumeRestoresTick()
    {
        $web = self::$webDriver;
        $other = $this->otherFieldName('q1');
        try {
            $web->get($this->getSurveyUrl());
            $web->findById('label-' . $other . '-other')->click();

            // The "Resume later" link may sit in a collapsed menu.
            $web->executeScript("document.querySelector('[data-limesurvey-submit*=\"saveall\"]').click();");
            $web->findById('savename')->sendKeys('jd203');
            $web->findById('savepass')->sendKeys('jd203-password');
            $web->findById('savepass2')->sendKeys('jd203-password');
            $web->findById('savebutton')->click();
            // Back on the survey page.
            $web->waitById('answer' . $other . 'cbox');

            $response = $this->getOnlyResponse();
            $this->assertSame('Y', $response[$other . 'checked'], 'The tick is saved');

            // Resume in a new session.
            $web->manage()->deleteAllCookies();
            $web->get($this->getSurveyUrl());
            $web->executeScript("document.querySelector('[data-limesurvey-submit*=\"loadall\"]').click();");
            $web->findById('loadname')->sendKeys('jd203');
            $web->findById('loadpass')->sendKeys('jd203-password');
            $web->findById('loadbutton')->click();

            $web->waitById('answer' . $other . 'cbox');
            $this->assertTrue($web->findById('answer' . $other . 'cbox')->isSelected(), 'The tick is restored');
            $this->assertSame('', $web->findById('answer' . $other)->getAttribute('value'));
        } catch (\Exception $ex) {
            $this->failWithScreenshot($web, $ex);
        }
    }

    /**
     * Test 12: accessible names, checked state, keyboard operation and error attributes.
     * @dataProvider themeProvider
     */
    public function testAccessibility($theme)
    {
        \Survey::model()->updateByPk(self::$surveyId, ['template' => $theme]);
        $web = self::$webDriver;
        $other = $this->otherFieldName('q1');
        try {
            $web->get($this->getSurveyUrl());

            // Accessible names.
            $checkbox = $web->findById('answer' . $other . 'cbox');
            $this->assertNull($checkbox->getAttribute('aria-hidden'), 'The checkbox is not hidden from assistive technology');
            $label = $web->findByCss('label[for="answer' . $other . 'cbox"]');
            $this->assertNotEmpty(trim($label->getDomProperty('textContent')), 'The checkbox has a label');
            $hint = $web->findById($checkbox->getAttribute('aria-describedby'));
            $this->assertNotEmpty(trim($hint->getDomProperty('textContent')), 'The focus move is announced in advance');
            $text = $web->findById('answer' . $other);
            foreach (explode(' ', $text->getAttribute('aria-labelledby')) as $labelId) {
                $this->assertNotEmpty(trim($web->findById($labelId)->getDomProperty('textContent')), 'The text field has a label');
            }

            // Keyboard: Space ticks Other and moves the focus to the text field.
            $checkbox->sendKeys(WebDriverKeys::SPACE);
            $this->assertTrue($checkbox->isSelected(), 'Space ticks Other');
            $this->assertSame('answer' . $other, $web->switchTo()->activeElement()->getAttribute('id'), 'The focus moves to the text field');
            $web->getKeyboard()->sendKeys('typed');
            $this->assertSame('typed', $text->getAttribute('value'));
            // Back to the checkbox, Space unticks Other and clears the text.
            $web->getKeyboard()->sendKeys([WebDriverKeys::SHIFT, WebDriverKeys::TAB]);
            $web->getKeyboard()->releaseKey(WebDriverKeys::SHIFT);
            $this->assertSame('answer' . $other . 'cbox', $web->switchTo()->activeElement()->getAttribute('id'));
            $web->getKeyboard()->sendKeys(WebDriverKeys::SPACE);
            $this->assertFalse($checkbox->isSelected(), 'Space unticks Other');
            $this->assertSame('', $text->getAttribute('value'), 'Unticking clears the text');

            // Error attributes.
            $this->next($web, 'q2');
            $mandatoryOther = $this->otherFieldName('q2');
            $web->findById('label-' . $mandatoryOther . '-other')->click();
            $web->next();
            $this->dismissAlert($web);
            $mandatoryText = $web->findById('answer' . $mandatoryOther);
            $this->assertSame('true', $mandatoryText->getAttribute('aria-invalid'));
            $tipId = 'mandatory-other-' . $this->questions['q2']->qid;
            $this->assertSame($tipId, $mandatoryText->getAttribute('aria-describedby'));
            $this->assertNotEmpty(trim($web->findById($tipId)->getDomProperty('textContent')), 'The error is read out with the field');
        } catch (\Exception $ex) {
            $this->failWithScreenshot($web, $ex);
        }
    }

    public static function themeProvider()
    {
        return [
            'Fruity TwentyThree' => ['fruity_twentythree'],
            'Fruity' => ['fruity'],
            'Vanilla' => ['vanilla'],
        ];
    }

    /**
     * Tick Other of the not mandatory question without text, then Next and Previous.
     * @param LimeSurveyWebDriver $web
     * @param bool $javaScript whether the browser runs JavaScript
     */
    protected function tickOtherWithoutTextNextPrevious($web, $javaScript)
    {
        $other = $this->otherFieldName('q1');
        try {
            $web->get($this->getSurveyUrl());
            $web->findById('label-' . $other . '-other')->click();
            // Only the script moves the focus to the text field.
            $this->assertSame(
                $javaScript ? 'answer' . $other : 'answer' . $other . 'cbox',
                $web->switchTo()->activeElement()->getAttribute('id'),
                $javaScript ? 'JavaScript is enabled' : 'JavaScript is disabled'
            );

            $this->next($web, 'q2');
            $response = $this->getOnlyResponse();
            $this->assertSame('Y', $response[$other . 'checked'], 'The tick is saved');
            $this->assertEmpty($response[$other], 'The text is empty');

            $this->previous($web, 'q1');
            $this->assertTrue($web->findById('answer' . $other . 'cbox')->isSelected(), 'Other is still ticked');
            $this->assertSame('', $web->findById('answer' . $other)->getAttribute('value'));
        } catch (\Exception $ex) {
            $this->failWithScreenshot($web, $ex);
        }
    }

    /**
     * Tick Other of the mandatory question without text and submit.
     * @param bool $withOption Also tick Option A
     */
    protected function assertMandatoryBlocked($withOption)
    {
        $web = self::$webDriver;
        $other = $this->otherFieldName('q2');
        try {
            $web->get($this->getSurveyUrl());
            $this->next($web, 'q2');
            if ($withOption) {
                $optionA = \Question::model()->findByAttributes(['parent_qid' => $this->questions['q2']->qid, 'title' => 'SQ001']);
                $web->findByCss('label[for="answerQ' . $this->questions['q2']->qid . '_S' . $optionA->qid . '"]')->click();
            }
            $web->findById('label-' . $other . '-other')->click();
            $web->next();
            $this->dismissAlert($web);

            $this->assertTrue($web->findById('answer' . $other . 'cbox')->isSelected(), 'Still on the page with Other ticked');
            $this->assertStringContainsString('please also specify', $web->getPageSource(), 'The "please specify" message is shown');
            $response = $this->getOnlyResponse();
            $this->assertNull($response['submitdate'], 'The response is not submitted');
        } catch (\Exception $ex) {
            $this->failWithScreenshot($web, $ex);
        }
    }

    /**
     * @param string $title question code
     * @return string like Q123_Cother
     */
    protected function otherFieldName($title)
    {
        return 'Q' . $this->questions[$title]->qid . '_Cother';
    }

    /**
     * Click Next and wait for the page of question $title.
     * @param LimeSurveyWebDriver $web
     */
    protected function next($web, $title)
    {
        $web->next();
        $web->waitById('answer' . $this->otherFieldName($title) . 'cbox');
    }

    /**
     * Click Previous and wait for the page of question $title.
     * @param LimeSurveyWebDriver $web
     */
    protected function previous($web, $title)
    {
        $web->findById('ls-button-previous')->click();
        $web->waitById('answer' . $this->otherFieldName($title) . 'cbox');
    }

    /**
     * Close the alert that a blocked submit can show.
     * @param LimeSurveyWebDriver $web
     */
    protected function dismissAlert($web)
    {
        // Modal fade in.
        sleep(1);
        $buttons = $web->findElements(WebDriverBy::cssSelector('#bootstrap-alert-box-modal.show button'));
        if (!empty($buttons)) {
            $buttons[0]->click();
            sleep(1);
        }
    }

    /**
     * @return array the only row of the response table
     */
    protected function getOnlyResponse()
    {
        $rows = \Yii::app()->db->createCommand('SELECT * FROM {{responses_' . self::$surveyId . '}}')->queryAll();
        $this->assertCount(1, $rows, 'Exactly one response');
        return $rows[0];
    }

    /**
     * Firefox with JavaScript disabled, like TestHelper::getWebDriver().
     * @return LimeSurveyWebDriver
     */
    protected function createWebDriverWithoutJavaScript()
    {
        $address = getenv('WEBDRIVERHOST') ?: 'localhost';
        $suffix = getenv('WEBDRIVERSUFFIX') ?: '/wd/hub';
        if ($suffix === 'none') {
            $suffix = '';
        }
        $firefoxOptions = new FirefoxOptions();
        $firefoxOptions->setPreference('javascript.enabled', false);
        $capabilities = DesiredCapabilities::firefox();
        $capabilities->setCapability(FirefoxOptions::CAPABILITY, $firefoxOptions);
        $web = LimeSurveyWebDriver::create('http://' . $address . ':' . self::$webPort . $suffix, $capabilities, 5000);
        $web->manage()->timeouts()->implicitlyWait(5);
        return $web;
    }

    /**
     * @param LimeSurveyWebDriver $web
     * @param \Exception $ex
     */
    protected function failWithScreenshot($web, $ex)
    {
        self::$testHelper->takeScreenshot($web, 'MultipleChoiceOtherCheckedTest');
        $this->fail(self::$testHelper->javaTrace($ex));
    }
}
