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
     * Setup
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
     * Test save dual-scale answer options.
     */
    public function testQuestionEditor()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_677328.lss';
        self::importSurvey($surveyFile);

        $survey = \Survey::model()->findByPk(self::$surveyId);
        $this->assertNotEmpty($survey);
        $this->assertCount(1, $survey->groups, 'Wrong number of groups: ' . count($survey->groups));
        $this->assertCount(1, $survey->groups[0]->questions, 'We have exactly one question');

        $answers = \Answer::model()->findAllByAttributes(['qid' => $survey->groups[0]->questions[0]->qid]);
        $this->assertCount(2, $answers);

        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'questionEditor/view',
            [
                //'sa'       => 'view',
                'surveyid' => self::$surveyId,
                'gid'      => $survey->groups[0]->gid,
                'qid'      => $survey->groups[0]->questions[0]->qid
            ]
        );

        $web = self::$webDriver;
        $web->get($url);

        sleep(2);
        
        $web->dismissModal();
        $web->dismissModal();
        
        sleep(5);
        $oElementQuestionEditorButton = $this->waitForElementShim($web, '#questionEditorButton');
        $web->wait(20)->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('#questionEditorButton')));
        $oElementQuestionEditorButton->click();
        sleep(1);
        
        $oElementAdvancedOptionsPanel = $this->waitForElementShim($web, '#advanced-options-container');
        $web->wait(10)->until(WebDriverExpectedCondition::visibilityOf($oElementAdvancedOptionsPanel));

        $oElementAnswerOptionsButton = $web->findElement(WebDriverBy::linkText('Answer options'));
        $oElementAnswerOptionsButton->click();

        $name1 = sprintf('input[name=answer_en_%d_0]', $answers[0]->aid);
        $answer1 = $web->findElement(WebDriverBy::cssSelector($name1));
        $answer1->sendKeys('123');

        $name2 = sprintf('input[name=answer_en_%d_1]', $answers[1]->aid);
        $answer2 = $web->findElement(WebDriverBy::cssSelector($name2));
        $answer2->sendKeys('abc');

        sleep(1);
        
        $savebutton = $web->findElement(WebDriverBy::id('save-button'));
        $savebutton->click();
        
        sleep(1);

        $oElementAdvancedOptionsPanel = $this->waitForElementShim($web, '#advanced-options-container', 20);
        $web->wait(10)->until(WebDriverExpectedCondition::visibilityOf($oElementAdvancedOptionsPanel));

        $answers = \Answer::model()->findAllByAttributes(['qid' => $survey->groups[0]->questions[0]->qid]);
        $this->assertEquals('123', $answers[0]->answerl10ns['en']->answer);
        $this->assertEquals('abc', $answers[1]->answerl10ns['en']->answer);

        $notif = $web->findElement(WebDriverBy::id('notif-container_1'));
        $notifText = $notif->getText();
        $this->assertContains('Question successfully stored', $notifText);
    }
}
