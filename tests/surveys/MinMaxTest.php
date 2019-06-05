<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * @since 2019-06-05
 * @group autocalc
 */
class MinMaxTest extends TestBaseClassWeb
{
    /**
     * 
     */
    public function testBasic()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_EmMaxMinTest_88442.lss';
        self::importSurvey($surveyFile);

        // Preview survey.
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => 'Y',
                'lang' => 'en'
            ]
        );

        try {
            // Get first page.
            self::$webDriver->get($url);
            try {
                $MultiNumElement = self::$webDriver->findElement(WebDriverBy::id('MultiNum'));
                $MultiNum = $MultiNumElement->getText();
                $this->assertEquals('-2/4', $MultiNum);
            }  catch (\Exception $ex) {
                self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
                $this->assertFalse(
                    true,
                    self::$testHelper->javaTrace($ex)
                );
            }
            try {
                $MultiNumElement = self::$webDriver->findElement(WebDriverBy::id('MultiText'));
                $MultiNum = $MultiNumElement->getText();
                $this->assertEquals('-1/Anything', $MultiNum);
            } catch (\Exception $ex) {
                self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
                $this->assertFalse(
                    true,
                    self::$testHelper->javaTrace($ex)
                );
            }
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
