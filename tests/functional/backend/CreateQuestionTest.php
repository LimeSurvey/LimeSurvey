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

            $web->dismissModal();

            // Go to structure sidebar
            $selectStructureSidebar = $web->findById('adminsidepanel__sidebar--selectorStructureButton');
            $selectStructureSidebar->click();
            sleep(1);

            // Create question.
            $sidemenuCreateQuestionButton = $web->findById('adminsidepanel__sidebar--selectorCreateQuestion');
            $sidemenuCreateQuestionButton->click();
            sleep(1);

            $questionBadCode = rand(1, 10000) . 'question';
            $questionCode = 'question' . rand(1, 10000);
            $input = $web->findById('questionCode');
            $input->clear()->sendKeys($questionBadCode);
            /* blur out action : ajax call */
            $web->findById('relevance')->click();
            sleep(1);
            $checkValidateText = $web->findById('question-title-warning')->getText();
            $this->assertEquals(
                "Question codes must start with a letter and may only contain alphanumeric characters.",
                 $checkValidateText,
                 "Title validation didn't update in question-title-warning, get “".$checkValidateText."”"
            );
            $input->clear()->sendKeys($questionCode);
            $input->click();
            $web->findById('relevance')->click();
            sleep(1);
            $checkValidateText = trim($web->findById('question-title-warning')->getText());
            $this->assertEquals(
                "",
                 $checkValidateText,
                 "Title validation in question-title-warning are not empty on success, get “".$checkValidateText."”"
            );

            $questionTypeSelector = $web->findById('trigger_questionTypeSelector_button');
            $questionTypeSelector->click();
            sleep(1);

            $questionTypeSelector = $web->findById('heading_single_choice_questions');
            $questionTypeSelector->click();
            sleep(1);

            $link = $web->findByCss('#collapsible_single_choice_questions a:first-child');
            $link->click();
            sleep(1);

            $link = $web->findById('selector__select-this-questionTypeSelector');
            $link->click();
            sleep(1);

            $link = $web->findById('save-button-create-question');
            $link->click();
            sleep(1);

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
            $sgqa = $sid . 'X' . $survey->groups[0]->gid . 'X' . $questions['question1']->qid;
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
                'SELECT * FROM {{survey_%d}}',
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
