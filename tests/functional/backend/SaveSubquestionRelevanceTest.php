<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeOutException;

/**
 * Edit subquestion relevance.
 * @since 2021-11-19
 * @group editquestion
 */
class SaveSubquestionRelevanceTest extends TestBaseClassWeb
{
    /**
     *
     */
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

        // Import survey.
        $surveyFile =  'tests/data/surveys/limesurvey_survey_425647_subquestion_relevance_edit.lss';
        self::importSurvey($surveyFile);

        // Browser login.
        self::adminLogin($username, $password);
    }

    /**
     *
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        // Delete survey.
        if (self::$testSurvey) {
            self::$testSurvey->delete();
            // NB: Unset so static teardown won't find it.
            self::$testSurvey = null;
        }
    }

    /**
     * Provides subquestion relevance equations for the test
     */
    public function equationProvider()
    {
        return [
            ['Q01_SQ001 == "Y"'],
            ["Q01_SQ001 == 'Y'"],
            ['Q01_SQ001 > "Y"'],
        ];
    }

    /**
     * Login, edit subquestion relevance, check database result.
     * @dataProvider equationProvider
     */
    public function testEditSubquestionRelevance($subquestionRelevanceEquation)
    {
        try {
            $gid = self::$testSurvey->groups[0]->gid;
            $qid = self::$testSurvey->questions[0]->qid;
            $oQuestion = \Question::model()->findByPk($qid);
            $this->assertNotEmpty($oQuestion);
            $sqid = $oQuestion->subquestions[1]->qid;

            // Go to edit group page.
            $urlMan = \Yii::app()->urlManager;
            $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
            $url = $urlMan->createUrl('questionAdministration/view', array('surveyid'=>self::$testSurvey->sid, 'gid'=>$gid, 'qid'=>$qid));
            self::$webDriver->get($url);

            $this->ignorePasswordWarning();
            $this->ignorePasswordWarning();

            // Switch to edit mode
            $editButton = self::$webDriver->wait(10)->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('questionEditorButton')));
            $editButton->click();

            self::$webDriver->executeScript('window.scrollTo(0,document.body.scrollHeight);');                                                                                                
            sleep(1);

            // Edit subquestion relevance
            $subquestionRelevanceField = self::$webDriver->wait(10)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('subquestions[' . $sqid . '][0][relevance]')));
            $subquestionRelevanceField->clear()->sendKeys($subquestionRelevanceEquation);

            // Save and close
            $saveButton = self::$webDriver->findElement(WebDriverBy::id('save-and-close-button-create-question'));
            $saveButton->click();

            // Switch to edit mode
            $editButton = self::$webDriver->wait(10)->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('questionEditorButton')));
            $editButton->click();

            // Subquestions are recreated on edit, so we need to get the new subquestion id
            $oQuestion->refresh();
            $sqid = $oQuestion->subquestions[1]->qid;

            // Check subquestion relevance
            $subquestionRelevanceField = self::$webDriver->wait(20)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('subquestions[' . $sqid . '][0][relevance]')));
            $result = $subquestionRelevanceField->getAttribute('value');
            $this->assertEquals($subquestionRelevanceEquation, $result);

            // Check the value in the DB
            $this->assertEquals($subquestionRelevanceEquation, $oQuestion->subquestions[1]->relevance);

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }

    protected function ignorePasswordWarning()
    {
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
    }

}
