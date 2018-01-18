<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * @since 2017-11-24
 * @group dualscale
 */
class SaveDualScaleAnswerOptionsTest extends TestBaseClassWeb
{
    /**
     * 
     */
    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();

        // Permission to everything.
        \Yii::app()->session['loginID'] = 1;

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
     * 
     */
    public function setup()
    {
        // Import survey with dual scale question type.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_677328.lss';
        self::importSurvey($surveyFile);
    }

    /**
     * 
     */
    public function tearDown()
    {
        if (self::$testSurvey) {
            self::$testSurvey->delete();
            // NB: Unset so static teardown won't find it.
            self::$testSurvey = null;
        }
    }

    /**
     * 
     */
    public function testBasic()
    {
        // Get questions.
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $this->assertNotEmpty($survey);
        $this->assertCount(1, $survey->groups, 'Wrong number of groups: ' . count($survey->groups));
        $this->assertCount(1, $survey->groups[0]->questions, 'We have exactly one question');

        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/questions',
            [
                'sa'       => 'answeroptions',
                'surveyid' => self::$surveyId,
                'gid'      => $survey->groups[0]->gid,
                'qid'      => $survey->groups[0]->questions[0]->qid
            ]
        );

        self::$webDriver->get($url);

        $answer1 = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="answer_en_1_0"]'));
        $answer1->sendKeys('123');

        $answer2 = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="answer_en_1_1"]'));
        $answer2->sendKeys('abc');

        $savebutton = self::$webDriver->findElement(WebDriverBy::id('save-button'));
        $savebutton->click();

        $notif = self::$webDriver->findElement(WebDriverBy::id('notif-container'));
        $notifText = $notif->getText();
        $this->assertContains('Answer options were successfully saved', $notifText);

        $answers = \Answer::model()->findAllByAttributes(['qid' => $survey->groups[0]->questions[0]->qid]);
        $this->assertCount(2, $answers, 'Two answer options saved');
    }

    /**
     * 
     */
    public function testUsingLinkToEditAnswers()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_677328.lss';
        self::importSurvey($surveyFile);

        $survey = \Survey::model()->findByPk(self::$surveyId);
        $this->assertNotEmpty($survey);
        $this->assertCount(1, $survey->groups, 'Wrong number of groups: ' . count($survey->groups));
        $this->assertCount(1, $survey->groups[0]->questions, 'We have exactly one question');

        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/questions',
            [
                'sa'       => 'view',
                'surveyid' => self::$surveyId,
                'gid'      => $survey->groups[0]->gid,
                'qid'      => $survey->groups[0]->questions[0]->qid
            ]
        );

        self::$webDriver->get($url);

        $button = self::$webDriver->findElement(WebDriverBy::linkText('Edit answer options'));
        $button->click();

        $answer1 = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="answer_en_1_0"]'));
        $answer1->sendKeys('123');

        $answer2 = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="answer_en_1_1"]'));
        $answer2->sendKeys('abc');

        sleep(1);

        $savebutton = self::$webDriver->findElement(WebDriverBy::id('save-button'));
        $savebutton->click();

        $notif = self::$webDriver->findElement(WebDriverBy::id('notif-container'));
        $notifText = $notif->getText();
        $this->assertContains('Answer options were successfully saved', $notifText);

        $answers = \Answer::model()->findAllByAttributes(['qid' => $survey->groups[0]->questions[0]->qid]);
        $this->assertCount(2, $answers, 'Two answer options saved');
    }
}
