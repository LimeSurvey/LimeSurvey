<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Check if Reponses are edit- and saveable
 *
 * @group responses
 */
class SaveEditedReponseTest extends TestBaseClassWeb
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
        $surveyFile = self::$surveysFolder . '/survey_archive_SaveEditedReponseTest.lsa';
        self::importSurvey($surveyFile);

        $survey = \Survey::model()->findByPk(self::$surveyId);
        $this->assertNotEmpty($survey);
        $this->assertCount(1, $survey->groups, 'Wrong number of groups: ' . count($survey->groups));
        $this->assertCount(1, $survey->groups[0]->questions, 'We have exactly one question');

        $reponseID = 1;
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/dataentry',
            [
                'sa'       => 'editdata',
                'subaction' => 'edit',
                'surveyid' => self::$surveyId,
                'id'      => $reponseID
            ]
        );

        $web = self::$webDriver;
        $web->get($url);

        sleep(2);

        $web->dismissModal();
        $web->dismissModal();

        sleep(5);

        $completedElement = $web->findElement(WebDriverBy::id('startlanguage'));
        $completedElement->clear();
        $completedElement->sendKeys("de");

        sleep(1);

        $savebutton = $web->findElement(WebDriverBy::id('save-button'));
        $savebutton->click();

        sleep(1);

        $oNotifCOntainer = $this->waitForElementShim($web, '#notif-container', 20);
        $web->wait(10)->until(WebDriverExpectedCondition::visibilityOf($oNotifCOntainer));

        $question = \Response::model(self::$surveyId)->findAllByAttributes([], 'id = :id', [':id' => $reponseID]);
        $this->assertEquals('de', $question[0]->startlanguage);
    }
}
