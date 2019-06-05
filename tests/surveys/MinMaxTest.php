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
            $MultiNumElement = self::$webDriver->findElement(WebDriverBy::id('MultiNum'));
            $MultiNum = $MultiNumElement->getText();
            $this->assertEquals('-2/4', $MultiNum);
            $MultiTextElement = self::$webDriver->findElement(WebDriverBy::id('MultiText'));
            $MultiText = $MultiTextElement->getText();
            $this->assertEquals('-1/Anything', $MultiText);
            $MultiText2Element = self::$webDriver->findElement(WebDriverBy::id('MultiText2'));
            $MultiText2 = $MultiText2Element->getText();
            $this->assertEquals('A/C', $MultiText2);
            $MultiText3Element = self::$webDriver->findElement(WebDriverBy::id('MultiText3'));
            $MultiText3 = $MultiText3Element->getText();
            $this->assertEquals('1/4', $MultiText3);
            $MultiText4Element = self::$webDriver->findElement(WebDriverBy::id('MultiText4'));
            $MultiText4 = $MultiText4Element->getText();
            $this->assertEquals('/AAA', $MultiText4);
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
