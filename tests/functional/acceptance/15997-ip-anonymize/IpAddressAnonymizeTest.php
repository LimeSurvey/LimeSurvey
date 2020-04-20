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

        //now check if ip was anonymized (ipv4, last digit should be 0)
        //get ipadr from table survey_573837 ...
        $models = \Response::model(self::$surveyId)->findAll();
/*
        if(isset($models[0]->ipaddr)){
            $this->assertTrue($models[0]->ipaddr==='127.0.0.0');
        }
*/
        $this->assertTrue((isset($models[0]->ipaddr)) && ($models[0]->ipaddr==='127.0.0.0'));
    }
}
