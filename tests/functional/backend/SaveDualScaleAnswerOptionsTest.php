<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

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
     *  TODO: This test will be marked as incomplete, cause some tests inside are not working correctly. See TODOS.
     */
    public function testQuestionEditor()
    {
        $this->markTestIncomplete();
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_677328.lss';
        self::importSurvey($surveyFile);

        $survey = \Survey::model()->findByPk(self::$surveyId);
        $this->assertNotEmpty($survey);
        $this->assertCount(1, $survey->groups, 'Wrong number of groups: ' . count($survey->groups));
        $this->assertCount(1, $survey->groups[0]->questions, 'We have exactly one question');

        $qid = $survey->groups[0]->questions[0]->qid;
        $answers = \Answer::model()->findAllByAttributes(['qid' => $survey->groups[0]->questions[0]->qid]);
        $this->assertCount(2, $answers);

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
        
        $button = self::$webDriver->wait(5)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('questionEditorButton')
            )
        );
        $button->click();

        $button = self::$webDriver->findElement(WebDriverBy::linkText('Answer options'));
        $button->click();

        $name1 = sprintf('input[name=answer_en_%d_0]', $answers[0]->aid);
        $answer1 = self::$webDriver->findElement(WebDriverBy::cssSelector($name1));
        $answer1->sendKeys('123');

        $name2 = sprintf('input[name=answer_en_%d_1]', $answers[1]->aid);
        $answer2 = self::$webDriver->findElement(WebDriverBy::cssSelector($name2));
        $answer2->sendKeys('abc');

        sleep(1);

        $savebutton = self::$webDriver->findElement(WebDriverBy::id('save-button'));
        $savebutton->click();

        sleep(1);

        $answers[0]->refresh();
        $answers[1]->refresh();
        $this->assertEquals('123', $answers[0]->answerL10ns['en']->answer);
        $this->assertEquals('abc', $answers[1]->answerL10ns['en']->answer);

        //TODO: This element does not exists.
        $notif = self::$webDriver->findElement(WebDriverBy::className('questioneditor-alert-pan'));
        $notifText = $notif->getText();
        $this->assertContains('Question successfully stored', $notifText);
    }
}
