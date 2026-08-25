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
     * A second final submit must be cancelled even if the browser has already
     * queued both submit events before the submit button is disabled.
     */
    public function testRepeatedFinalSubmitIsPrevented()
    {
        $web = self::$webDriver;

        try {
            $web->get($this->getSurveyUrl());

            // Welcome page, first group, then the final group.
            $web->clickButton('ls-button-submit');
            $web->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-0 .group-title')
                )
            );
            $web->clickButton('ls-button-submit');
            $web->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-1 .group-title')
                )
            );

            $result = $web->executeScript(<<<'JS'
                var form = document.getElementById('limesurvey');
                var submitButton = document.getElementById('ls-button-submit');

                function dispatchFinalSubmit() {
                    var event = document.createEvent('Event');
                    event.initEvent('submit', true, true);
                    Object.defineProperty(event, 'submitter', {value: submitButton});
                    return form.dispatchEvent(event);
                }

                return {
                    firstSubmitAllowed: dispatchFinalSubmit(),
                    secondSubmitAllowed: dispatchFinalSubmit(),
                    submitValueInputCount: form.querySelectorAll('#onsubmitbuttoninput').length
                };
                JS);

            $this->assertTrue($result['firstSubmitAllowed']);
            $this->assertFalse($result['secondSubmitAllowed']);
            $this->assertSame(1, $result['submitValueInputCount']);
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(true, self::$testHelper->javaTrace($ex));
        }
    }

    /**
     * The same protection must work on browsers without SubmitEvent.submitter,
     * Safari below 15.4 mainly, where the clicked button is used instead.
     */
    public function testRepeatedFinalSubmitIsPreventedWithoutSubmitter()
    {
        $web = self::$webDriver;

        try {
            $web->get($this->getSurveyUrl());

            // Welcome page, first group, then the final group.
            $web->clickButton('ls-button-submit');
            $web->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-0 .group-title')
                )
            );
            $web->clickButton('ls-button-submit');
            $web->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-1 .group-title')
                )
            );

            $result = $web->executeScript(<<<'JS'
                var form = document.getElementById('limesurvey');
                var submitButton = document.getElementById('ls-button-submit');

                // The click must not submit the form by itself: the submit event is
                // dispatched below instead, without any submitter.
                submitButton.addEventListener('click', function (event) {
                    event.preventDefault();
                });

                function clickThenSubmitWithoutSubmitter() {
                    submitButton.dispatchEvent(
                        new MouseEvent('click', {bubbles: true, cancelable: true})
                    );
                    var event = document.createEvent('Event');
                    event.initEvent('submit', true, true);
                    return form.dispatchEvent(event);
                }

                var firstSubmitAllowed = clickThenSubmitWithoutSubmitter();
                var secondSubmitAllowed = clickThenSubmitWithoutSubmitter();
                var hiddenInput = form.querySelector('#onsubmitbuttoninput');

                return {
                    firstSubmitAllowed: firstSubmitAllowed,
                    secondSubmitAllowed: secondSubmitAllowed,
                    submitValueInputCount: form.querySelectorAll('#onsubmitbuttoninput').length,
                    submitValue: hiddenInput ? hiddenInput.value : null
                };
                JS);

            $this->assertTrue($result['firstSubmitAllowed']);
            $this->assertFalse($result['secondSubmitAllowed']);
            $this->assertSame(1, $result['submitValueInputCount']);
            $this->assertSame('movesubmit', $result['submitValue']);
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(true, self::$testHelper->javaTrace($ex));
        }
    }

    /**
     * A final submit that reaches the server a second time - double click on a slow
     * server, browser "Try again" prompt, back navigation, reload - must display the
     * completed page again. The response is already recorded at that point, so
     * reporting an expired session tells the participant their answers are lost
     * while the server counted the response as complete.
     */
    public function testReplayedFinalSubmitShowsCompletedPage()
    {
        $web = self::$webDriver;

        try {
            $web->get($this->getSurveyUrl());

            // Welcome page, first group, then the final group.
            $web->clickButton('ls-button-submit');
            $web->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-0 .group-title')
                )
            );
            $web->clickButton('ls-button-submit');
            $web->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#group-1 .group-title')
                )
            );

            // Send the very same final submit twice, straight from the last page, so
            // the second request is an exact replay of the first one.
            $result = $web->executeAsyncScript(<<<'JS'
                var callback = arguments[arguments.length - 1];
                var form = document.getElementById('limesurvey');
                var body = new URLSearchParams(new FormData(form));
                body.set('move', 'movesubmit');

                function submitOnce() {
                    return fetch(form.action, {
                        method: 'POST',
                        body: body,
                        credentials: 'same-origin',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                    }).then(function (response) { return response.text(); });
                }

                submitOnce().then(function (first) {
                    return submitOnce().then(function (second) {
                        callback({
                            firstCompleted: /completed-text/.test(first),
                            secondCompleted: /completed-text/.test(second),
                            secondExpired: /session has expired/i.test(second)
                        });
                    });
                }).catch(function (error) {
                    callback({error: String(error)});
                });
                JS, []);

            $this->assertArrayNotHasKey('error', $result);
            $this->assertTrue($result['firstCompleted'], 'The first final submit must show the completed page.');
            $this->assertFalse($result['secondExpired'], 'A replayed final submit must not report an expired session.');
            $this->assertTrue($result['secondCompleted'], 'A replayed final submit must show the completed page again.');
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
