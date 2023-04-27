<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 - @since 2019-07-31
 * @group jsphp
 */
class MinMaxCompareTest extends TestBaseClassWeb
{
    /**
     * 
     */
    public function testCompare()
    {
        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_MinMaxCompareTest.lss';
        self::importSurvey($surveyFile);

        // Preview survey.
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => 'Y',
            ]
        );

        // Get questions.
        $survey = \Survey::model()->findByPk(self::$surveyId);
        try {
            // Get first page.
            self::$webDriver->get($url);

            /* Fill JS result */
            $MultiNumResultJS = self::$webDriver->findElement(WebDriverBy::id('MultiNum'))->getText();
            $MultiTextResultJS = self::$webDriver->findElement(WebDriverBy::id('MultiText'))->getText();
            $MultiText2ResultJS = self::$webDriver->findElement(WebDriverBy::id('MultiText2'))->getText();
            $MultiText3ResultJS = self::$webDriver->findElement(WebDriverBy::id('MultiText3'))->getText();
            $MultiText4ResultJS = self::$webDriver->findElement(WebDriverBy::id('MultiText4'))->getText();
            
            self::$webDriver->executeScript('window.scrollTo(0,document.body.scrollHeight);');
            sleep(1);

            /* Move next */
            $nextButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $nextButton->click();
            
            /* Fill PHP result */
            $MultiNumResultPHP = self::$webDriver->findElement(WebDriverBy::id('MultiNum'))->getText();
            $MultiTextResultPHP = self::$webDriver->findElement(WebDriverBy::id('MultiText'))->getText();
            $MultiText2ResultPHP = self::$webDriver->findElement(WebDriverBy::id('MultiText2'))->getText();
            $MultiText3ResultPHP = self::$webDriver->findElement(WebDriverBy::id('MultiText3'))->getText();
            $MultiText4ResultPHP = self::$webDriver->findElement(WebDriverBy::id('MultiText4'))->getText();
            /* Do the JS vs PHP compare */
            $this->assertEquals($MultiNumResultJS, $MultiNumResultPHP, 'Comparaison with forced number : «' . $MultiNumResultJS ."» vs «".$MultiNumResultPHP."»");     
            $this->assertEquals($MultiTextResultJS, $MultiTextResultPHP, 'Comparaison with number and string : «' . $MultiTextResultJS ."» vs «".$MultiTextResultPHP."»");     
            $this->assertEquals($MultiText2ResultJS, $MultiText2ResultPHP, 'Comparaison with string : «' . $MultiText2ResultJS ."» vs «".$MultiText2ResultPHP."»");     
            $this->assertEquals($MultiText3ResultJS, $MultiText3ResultPHP, 'Comparaison with number string start by number: «' . $MultiText3ResultJS ."» vs «".$MultiText3ResultPHP."»");     
            $this->assertEquals($MultiText4ResultJS, $MultiText4ResultPHP, 'Comparaison with string and number : «' . $MultiText4ResultJS ."» vs «".$MultiText4ResultPHP."»");     
            /* Fixed string compare (wait for) */
            $this->assertEquals($MultiNumResultPHP, "-2//4", 'Comparaison with forced number got «' . $MultiNumResultPHP ."», wait «-2//4»");     
            $this->assertEquals($MultiTextResultPHP, "-1/ /Anything", 'Comparaison with number and string : got «' . $MultiTextResultPHP ."», wait «-1/ /Anything»");     
            $this->assertEquals($MultiText2ResultPHP, "A//C", 'Comparaison with string : got «' . $MultiText2ResultPHP ."», wait «A//C»");     
            $this->assertEquals($MultiText3ResultPHP, "1//4", 'Comparaison with string start by number: got «' . $MultiText3ResultPHP ."», wait «1//4»");     
            $this->assertEquals($MultiText4ResultPHP, "//AAA", 'Comparaison with string and number : got «' . $MultiText4ResultPHP ."», wait «//AAA»");     

        } catch (\Exception $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/MinMaxCompareTest_' . __FUNCTION__ . '.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
