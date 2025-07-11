<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Exception\UnknownServerException;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Exception\ElementNotVisibleException;

/**
 * Login and create a survey, add a group
 * and a question.
 * @since 2017-11-17
 * @group createsurvey
 */
class CreateSurveyTest extends TestBaseClassWeb
{
    private $urlMan;
    private const HTTP_STRING = 'http://';
    private const INDEX_SITE = '/index.php';

    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();
        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }

        // Permission to everything.
        \Yii::app()->session['loginID'] = 1;

        // Browser login.
        self::adminLogin($username, $password);
    }

    /**
     * Login, create survey, add group and question,
     * activate survey, execute survey, check database
     * result.
     */
    public function testCreateSurvey()
    {
        try {
            // Ignore welcome modal.
            try {
                $button = self::$webDriver->wait(1)->until(
                    WebDriverExpectedCondition::elementToBeClickable(
                        WebDriverBy::cssSelector('#welcomeModal button.btn-outline-secondary')
                    )
                );
                $button->click();
            } catch (NoSuchElementException $ex) {
                // Do nothing.
            } catch (TimeOutException $ex) {
                // Do nothing.
            }

            sleep(1);

            // Ignore password warning.
            try {
                $button = self::$webDriver->wait(1)->until(
                    WebDriverExpectedCondition::elementToBeClickable(
                        WebDriverBy::cssSelector('#admin-notification-modal button.btn-outline-secondary')
                    )
                );
                $button->click();
            } catch (TimeOutException $ex) {
                // Do nothing.
            } catch (NoSuchElementException $ex) {
                // Do nothing.
            }

            sleep(1);

            // Open menu by clicking the "+" sign in top bar
            $link = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('btn-create')
                )
            );
            $link->click();

            //click on "Survey" to create a new survey with default values
            // Open menu by clicking the "+" sign in top bar
            $link = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('create-survey-link')
                )
            );
            $link->click();

            sleep(3); //wait until survey is created

            //title is set to a default value
            $title = 'Untitled survey';

            $survey = \Survey::model()
                ->with(['defaultlanguage' => ['condition' => 'surveyls_title=' . \Yii::app()->db->quoteValue($title)]])
                ->findAll();

            $this->assertCount(1, $survey);
        } catch (\Throwable $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
