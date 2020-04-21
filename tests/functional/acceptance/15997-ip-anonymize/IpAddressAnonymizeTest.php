<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class IpAddressAnonymizeTest extends TestBaseClassWeb
{
    /**
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_573837.lss';
        self::importSurvey($surveyFile);

        /* Login */
        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }
        // Browser login.
        self::adminLogin($username, $password);

    }

    /**
     * Test IP anonymization.
     */
    public function testIpAnonymizeInActiveSurvey()
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/survey/sa/view/surveyid/'.self::$surveyId
        );
        self::$webDriver->get($url);
        sleep(1);

        // Click "Activate survey".
        $overview = self::$webDriver->findElement(WebDriverBy::id('ls-activate-survey'));
        $overview->click();

        sleep(1);

        // Confirm.
        $overview = self::$webDriver->findElement(WebDriverBy::id('activateSurvey__basicSettings--proceed'));
        $overview->click();

        sleep(1);

        // Click "Execute survey".
        $exceuteBtn = self::$webDriver->findById('execute_survey_button') ;
        $exceuteBtn->click();

        sleep(1);

        // Switch to new tab.
        $windowHandles = self::$webDriver->getWindowHandles();
        self::$webDriver->switchTo()->window(
            end($windowHandles)
        );

        sleep(1);

        // New tab with active survey.
        $nextButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
        $nextButton->click();
        sleep(2);

        //now check if ip was anonymized (ipv4, last digit should be 0)
        //get ipadr from table survey_573837 ...
        $models = \Response::model(self::$surveyId)->findAll();

        $this->assertTrue((isset($models[0]->ipaddr)) && ($models[0]->ipaddr==='127.0.0.0'));

        //after this deactivate survey for next test ...
        // Switch to first window.
        $windowHandles = self::$webDriver->getWindowHandles();
        self::$webDriver->switchTo()->window(
            reset($windowHandles)
        );

        //click button stop this survey
        $stopSurveyButton = self::$webDriver->findElement(WebDriverBy::id('ls-stop-survey'));
        $stopSurveyButton->click();
        sleep(1);

        //click to deactivate survey
        $execute = self::$webDriver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::cssSelector('input[type="submit"][value="Deactivate survey"]')
            )
        );
        $execute->click();
        sleep(2);
    }

    /**
     * Test so that previous behaviour is still working.
     */
    public function testNormalActiveSurvey()
    {
        // TODO: As above, but without ip anon and ip === 127.0.0.1
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/survey/sa/view/surveyid/'.self::$surveyId
        );
        self::$webDriver->get($url);
        sleep(1);


        //set ipanonymize to off ...
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $survey->ipanonymize = 'N';
        $survey->save();

        // Click "Activate survey".
        $overview = self::$webDriver->findElement(WebDriverBy::id('ls-activate-survey'));
        $overview->click();

        sleep(3);

        // Confirm.
        $overview = self::$webDriver->findElement(WebDriverBy::id('activateSurvey__basicSettings--proceed'));
        $overview->click();

        sleep(3);

        // Click "Execute survey".
        $exceuteBtn = self::$webDriver->findById('execute_survey_button') ;
        $exceuteBtn->click();

        sleep(1);

        // Switch to new tab.
        $windowHandles = self::$webDriver->getWindowHandles();
        self::$webDriver->switchTo()->window(
            end($windowHandles)
        );

        sleep(2);

        // New tab with active survey.
        $nextButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
        $nextButton->click();

        sleep(2);

        /*

        // Enter answer text. (this must be only for the second test, i don't know why?!?!
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $questionObjects = $survey->groups[0]->questions;
        $questions = [];
        foreach ($questionObjects as $q) {
            $questions[$q->title] = $q;
        }
        $sgqa = self::$surveyId . 'X' . $survey->groups[0]->gid . 'X' . $questions['G01Q01']->qid;
        $question = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa));
        $question->sendKeys('foo bar');

        sleep(2);

        // Click submit.
        /*
        $submitButton = self::$webDriver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::cssSelector('input[type="submit"][value="movesubmit"]')
            )
        );
       $submitButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
        $submitButton->click();

        sleep(2); */

        //now check if ip was anonymized (ipv4, last digit should be 0)
        //get ipadr from table survey_573837 ...
        $models = \Response::model(self::$surveyId)->findAll();

        $this->assertTrue((isset($models[0]->ipaddr)) && ($models[0]->ipaddr==='127.0.0.1'));
    }
}
