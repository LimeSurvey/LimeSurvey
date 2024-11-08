<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class IpAddressAnonymizeTest extends TestBaseClassWeb
{
    /**
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

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
        try {
            $surveyFile = self::$surveysFolder . '/limesurvey_survey_573837.lss';
            self::importSurvey($surveyFile);

            $urlMan = \Yii::app()->urlManager;
            $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
            $url = $urlMan->createUrl(
                'surveyAdministration/view/surveyid/' . self::$surveyId
            );
            self::$webDriver->get($url);
            sleep(1);

            self::$webDriver->dismissModal();

            // Click "Activate survey".
            $overview = self::$webDriver->wait(5)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('ls-activate-survey')
                )
            );
            $overview->click();

            sleep(1);

            //activate survey in open-access mode
            //modal has been opend, activate survey in open-access mode
            $overview = self::$webDriver->wait(5)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(
                    WebDriverBy::id('saveactivateBtn')
                )
            );
            $overview->click();

            /**

            // Confirm.
            $overview = self::$webDriver->wait(5)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::id('activateSurvey__basicSettings--proceed')
            )
            );
            $overview->click();

            // Click "No, thanks"
            $overview = $exceuteBtn = self::$webDriver->wait(5)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::id('activateTokenTable__selector--no')
            )
            );
            $overview->click();
             *
             * */

            sleep(1);
            // Click "Run survey".
            $exceuteBtn = self::$webDriver->wait(20)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('execute_survey_button')
                )
            );
            $exceuteBtn->click();

            sleep(1);

            // Switch to new tab.
            $windowHandles = self::$webDriver->getWindowHandles();
            self::$webDriver->switchTo()->window(
                end($windowHandles)
            );

            // New tab with active survey.
            $nextButton = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('ls-button-submit')
                )
            );
            $nextButton->click();
            sleep(2);

            //now check if ip was anonymized (ipv4, last digit should be 0)
            //get ipadr from table survey_573837 ...
            $models = \Response::model(self::$surveyId)->findAll();

            $this->assertTrue((isset($models[0]->ipaddr)) && ($models[0]->ipaddr === '127.0.0.0'));
        } catch (\Exception $e) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($e)
            );
        }
    }

    /**
     * Test so that previous behaviour is still working.
     */
    public function testNormalActiveSurvey()
    {
        try {
            $surveyFile = self::$surveysFolder . '/limesurvey_survey_573837.lss';
            self::importSurvey($surveyFile);

            $urlMan = \Yii::app()->urlManager;
            $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
            $url = $urlMan->createUrl(
                'surveyAdministration/view/surveyid/' . self::$surveyId
            );
            self::$webDriver->get($url);
            sleep(1);

            self::$webDriver->dismissModal();

            //set ipanonymize to off ...
            $survey = \Survey::model()->findByPk(self::$surveyId);
            $survey->ipanonymize = 'N';
            $survey->save();

            // Click "Activate survey".
            $overview = self::$webDriver->wait(5)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('ls-activate-survey')
                )
            );
            $overview->click();

            //modal has been opend, activate survey in open-access mode
            $overview = self::$webDriver->wait(5)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(
                    WebDriverBy::id('saveactivateBtn')
                )
            );
            $overview->click();

            /**
            // Confirm.
            $overview = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('activateSurvey__basicSettings--proceed')
                )
            );
            $overview->click();

            // Click "No, thanks"
            $overview = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('activateTokenTable__selector--no')
                )
            );
            $overview->click();
             *
             * */
            sleep(1);
            // Click "Run survey".
            $exceuteBtn = self::$webDriver->wait(5)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('execute_survey_button')
                )
            );
            $exceuteBtn->click();
            sleep(1);

            // Switch to new tab.
            $windowHandles = self::$webDriver->getWindowHandles();
            self::$webDriver->switchTo()->window(
                end($windowHandles)
            );

            // New tab with active survey.
            $nextButton = self::$webDriver->wait(20)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('ls-button-submit')
                )
            );
            $nextButton->click();
            sleep(2);
            $models = \Response::model(self::$surveyId)->findAll();

            $this->assertTrue((isset($models[0]->ipaddr)) && ($models[0]->ipaddr === '127.0.0.1'));
        } catch (\Exception $e) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($e)
            );
        }
    }
}
