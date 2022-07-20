<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2019-05-28
 */
class EmCacheSurveyTest extends TestBaseClassWeb
{
    /**
     * @var string[]|null
     */
    protected static $oldConfig = null;

    /**
     * @var string
     */
    protected $firstDate = null;

    /**
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        if (YII_DEBUG) {
            self::markTestSkipped("emcache can't be tested in debug mode (always off)");
        }

        parent::setUpBeforeClass();

        $configdir = \Yii::app()->getConfig('configdir');
        $filename = $configdir . '/config.php';

        /** @var string[] */
        $lines = file($filename);
        self::$oldConfig = $lines;
        if (empty($lines)) {
            self::assertTrue(false, 'EmCacheSurveyTest: Could not read config.php');
            return;
        }
        $write = fopen($filename, 'w');
        if (!$write) {
            self::assertTrue(false, 'EmCacheSurveyTest: Can not write to config.php');
            return;
        }
        // Write emcache setting to config.
        $couldWriteSetting = false;
        foreach ($lines as $line) {
            if (strpos($line, 'urlManager') !== false) {
                fwrite($write, "\t\t'emcache'=>array('class' => 'CFileCache'),\n");
                $couldWriteSetting = true;
            }
            fwrite($write, $line);
        }
        if (!$couldWriteSetting) {
            self::assertTrue(false, 'EmCacheSurveyTest: Could not write emcache setting to config.php');
            return;
        }
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        // Remove emcache setting from config.
        $configdir = \Yii::app()->getConfig('configdir');
        $filename = $configdir . '/config.php';
        if (!file_put_contents($filename, implode('', self::$oldConfig))) {
            echo 'EmCacheSurveyTest: Could not restore config file';
            self::assertTrue(false, 'Could not restore config file');
        }

        parent::tearDownAfterClass();
    }

    /**
     * Test two different token executed survey after each other.
     */
    public function testTwoTokens()
    {
        // Import survey.
        $surveyFile = self::$surveysFolder . '/survey_archive_464421.lsa';
        self::importSurvey($surveyFile);

        $this->doFirstToken();
        $this->doSecondToken();

        // Second token should not see first token, should see new datetime default answer.

        self::$testHelper->deactivateSurvey(self::$surveyId);
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }

    /**
     * Emcache should be turned off if anything is randomized.
     */
    public function testEmcacheRandomization()
    {
        // Import survey.
        $surveyFile = self::$surveysFolder . '/survey_archive_564265.lsa';
        self::importSurvey($surveyFile);

        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => 'Y'
            ]
        );

        list(, $group, ) = self::$testHelper->getSgqa('question1', self::$surveyId);
        $group->randomization_group = 'rand1';
        $this->assertTrue($group->update());

        try {
            self::$webDriver->get($url);

            $emcacheSpan = self::$webDriver->findElement(WebDriverBy::id('__emcache_debug'));
            $value = $emcacheSpan->getAttribute('value');
            $this->assertEquals('off', $value, json_encode($value));

            // Remove randomization group.
            $group->randomization_group = '';
            $this->assertTrue($group->update());

            self::$webDriver->get($url);
            $emcacheSpan = self::$webDriver->findElement(WebDriverBy::id('__emcache_debug'));
            $value = $emcacheSpan->getAttribute('value');
            $this->assertEquals('on', $value, json_encode($value));
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }

        self::$testHelper->deactivateSurvey(self::$surveyId);
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }

    /**
     * @return void
     */
    protected function doFirstToken()
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'token' => '123',
                'newtest' => 'Y'
            ]
        );

        try {
            self::$webDriver->get($url);

            /** @var string */
            // TODO: Fragile test, can differ with a second.
            //$now = date('i:s');

            // Compare date with now. Should be the same below when comparing with second token.
            list(, , $sgqa) = self::$testHelper->getSgqa('datequestion', self::$surveyId);
            $dateAnswer = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa));
            $this->firstDate = $dateAnswer->getAttribute('value');
            //var_dump($now);
            //var_dump($dateAnswer->getAttribute('value'));
            //$this->assertEquals($now, $dateAnswer->getAttribute('value'));

            // First name of first token is "Olle".
            list(, , $sgqa) = self::$testHelper->getSgqa('firstname', self::$surveyId);
            $nameAnswer = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa));
            $this->assertEquals('Olle', $nameAnswer->getAttribute('value'));

            // Test so that relevance equation for question in second group works.
            list(, , $sgqa) = self::$testHelper->getSgqa('textquestion', self::$surveyId);
            $textQuestion = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa));
            $textQuestion->sendKeys('bla bla bla');

            self::$webDriver->executeScript('window.scrollTo(0,document.body.scrollHeight);');
            sleep(1);

            $nextButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $nextButton->click();

            sleep(1);

            $prev = self::$webDriver->findElement(WebDriverBy::id('ls-button-previous'));
            $prev->click();

            // Make sure the old answer is still there when going back.
            list(, , $sgqa) = self::$testHelper->getSgqa('textquestion', self::$surveyId);
            $textQuestion = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa));
            $this->assertEquals('bla bla bla', $textQuestion->getText(), 'Answer remain when going back');

            self::$webDriver->executeScript('window.scrollTo(0,document.body.scrollHeight);');
            sleep(1);

            // Submit survey.
            $next = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $next->click();

            $next = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $next->click();

            // Get all answers.
            $dbo = \Yii::app()->getDb();
            $query = sprintf('SELECT * FROM {{survey_%d}}', self::$surveyId);
            $result = $dbo->createCommand($query)->queryAll();
            $this->assertCount(1, $result);
            $this->assertEquals('bla bla bla', $result[0][$sgqa]);
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * @return void
     */
    protected function doSecondToken()
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'token' => '234',
                'newtest' => 'Y'
            ]
        );

        try {
            self::$webDriver->get($url);

            // If these two dates are equal, the default answer got cached (fail).
            list(, , $sgqa) = self::$testHelper->getSgqa('datequestion', self::$surveyId);
            $dateAnswer = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa));
            $this->assertNotEquals($this->firstDate, $dateAnswer->getAttribute('value'));

            sleep(1);

            // First name of second token is "Bolle".
            list(, , $sgqa) = self::$testHelper->getSgqa('firstname', self::$surveyId);
            $nameAnswer = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa));
            $this->assertEquals('Bolle', $nameAnswer->getAttribute('value'));
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
