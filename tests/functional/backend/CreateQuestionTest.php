<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Exception\UnknownServerException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\ElementNotVisibleException;
use Facebook\WebDriver\WebDriverKeys;

/**
 * Login and create a survey, add a group
 * and a question.
 *
 * @group question
 */
class CreateQuestionTest extends TestBaseClassWeb
{
    private $urlMan;
    private const HTTP_STRING = 'http://';
    private const INDEX_SITE = '/index.php';

    public static function setUpBeforeClass(): void
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
    public function testCreate5pointQuestion()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_383591_testCreateQuestion.lss';
        self::importSurvey($surveyFile);
        $this->urlMan = \Yii::app()->urlManager;

        try {
            $urlMan = \Yii::app()->urlManager;
            $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
            $url = $urlMan->createUrl(
                'surveyAdministration/view',
                [
                    'iSurveyID' => self::$testSurvey->sid
                ]
            );
            $web = self::$webDriver;
            $web->get($url);
            sleep(1);

            // Ignore welcome modal.
            try {
                $button = self::$webDriver->wait(10)->until(
                    WebDriverExpectedCondition::elementToBeClickable(
                        WebDriverBy::cssSelector('#welcomeModal button.btn-outline-secondary')
                    )
                );
                $button->click();
            } catch (NoSuchElementException $ex) {
                // Do nothing.
            } catch (TimeoutException $ex) {
                // Do nothing.
            }

            $web->dismissModal();

            // Go to structure sidebar
            $selectStructureSidebar = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('adminsidepanel__sidebar--selectorStructureButton')
                )
            );
            $selectStructureSidebar->click();

            // Create question.
            $sidemenuCreateQuestionButton = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('adminsidepanel__sidebar--selectorCreateQuestion')
                )
            );
            $sidemenuCreateQuestionButton->click();

            $questionBadCode = rand(1, 10000) . 'question';
            $questionCode = 'question' . rand(1, 10000);

            $questionBadCode = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('questionCode')
                )
            );
            $questionBadCode->clear()->sendKeys($questionBadCode);
            /* blur out trigger */
            $questionBadCode->sendKeys(WebDriverKeys::TAB);
            $checkValidateText = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementTextIs(
                    WebDriverBy::id('question-title-warning'),
                    'Question codes must start with a letter and may only contain alphanumeric characters.'
                )
            );
            $this->assertEquals(
                "Question codes must start with a letter and may only contain alphanumeric characters.",
                 $checkValidateText,
                 "Title validation didn't update in question-title-warning, get “".$checkValidateText."”"
            );
            $questionCode = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('questionCode')
                )
            );
            $questionCode->clear()->sendKeys($questionCode);
            $questionCode->click();
            /* blur out trigger */
            $questionCode->sendKeys(WebDriverKeys::TAB);
            // need to wait for js to run, no state change
            sleep(1);
            $checkValidateText = trim($web->findById('question-title-warning')->getText());
            $this->assertEquals(
                "",
                 $checkValidateText,
                 "Title validation in question-title-warning are not empty on success, get “".$checkValidateText."”"
            );

            $questionTypeSelector = $web->findById('trigger_questionTypeSelector_button');
            $questionTypeSelector->click();
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('heading_single_choice_questions')
                )
            );

            $questionTypeSelector = $web->findById('heading_single_choice_questions');
            $questionTypeSelector->click();
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('#collapsible_single_choice_questions a:first-child')
                )
            );

            $link = $web->findByCss('#collapsible_single_choice_questions a:first-child');
            $link->click();
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('selector__select-this-questionTypeSelector')
                )
            );

            $link = $web->findById('selector__select-this-questionTypeSelector');
            $link->click();
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('save-button-create-question')
                )
            );

            $link = $web->findById('save-button-create-question');
            $link->click();
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#notif-container .alert-success')
                )
            );

            $question = \Question::model()->findByAttributes(['title' => $questionCode]);
            $this->assertNotEmpty($question);

            // Switch to new tab.
            /*
            $windowHandles = self::$webDriver->getWindowHandles();
            self::$webDriver->switchTo()->window(
                end($windowHandles)
            );

            sleep(1);

            // New tab with active survey.
            $nextButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $nextButton->click();
            print_r('after next button');

            // Get questions.
            $dbo = \Yii::app()->getDb();
            $query = 'SELECT sid FROM {{surveys}} ORDER BY datecreated DESC LIMIT 1';
            $sids = $dbo->createCommand($query)->queryAll();
            $this->assertCount(1, $sids);
            $sid = $sids[0]['sid'];
            $survey = \Survey::model()->findByPk($sid);
            $this->assertNotEmpty($survey);
            $this->assertCount(1, $survey->groups, 'Wrong number of groups: ' . count($survey->groups));
            $questionObjects = $survey->groups[0]->questions;
            $questions = [];
            foreach ($questionObjects as $q) {
                $questions[$q->title] = $q;
            }
            $this->assertCount(1, $questions, 'We have exactly one question');
            $this->assertTrue(isset($questions['question1']), json_encode(array_keys($questions)));

            print_r('Already here');
            // Enter answer text.
            $sgqa = 'Q' . $questions['question1']->qid;
            $question = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa));
            $question->sendKeys('foo bar');

            print_r('After enter answer text');

            sleep(1);

            // Click submit.
            $submitButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $submitButton->click();
            print_r('After click submit');

            // Check so that we see end page.
            $completed = self::$webDriver->findElement(WebDriverBy::cssSelector('div.completed-text'));
            $this->assertEquals(
                $completed->getText(),
                "Thank you!\nYour survey responses have been recorded.",
                'I can see completed text'
            );

            // Check so that response is recorded in database.
            $query = sprintf(
                'SELECT * FROM {{responses_%d}}',
                $sid
            );
            $result = $dbo->createCommand($query)->queryAll();
            $this->assertCount(1, $result, 'Exactly one response');
            $this->assertEquals('foo bar', $result[0][$sgqa], '"foo bar" response');

            // Switch to first window.
            $windowHandles = self::$webDriver->getWindowHandles();
            self::$webDriver->switchTo()->window(
                reset($windowHandles)
            );

            // Delete survey.
            $execute = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('ls-tools-button')
                )
            );
            $execute->click();
            print_r('after click delete survey');

            $execute = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('#ls-tools-button + ul li:first-child')
                )
            );
            $execute->click();
            $execute = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('input[type="submit"]')
                )
            );
            $execute->click();

            sleep(1);

            // Make sure the survey can't be found.
            $query = 'SELECT sid FROM {{surveys}} WHERE sid = ' . $sid;
            $sids = $dbo->createCommand($query)->queryAll();
            $this->assertCount(0, $sids);
             */
        } catch (\Exception $ex) {
            // TODO :Duplicated code.
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
