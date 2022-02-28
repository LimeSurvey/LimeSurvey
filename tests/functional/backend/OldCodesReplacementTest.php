<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * Tests old codes replacement on survey import.
 *
 * @since 2021-08-06
 */
class OldCodesReplacementTest extends TestBaseClassWeb
{
    /**
     * Test code replacements on survey import
     */
    public function testCodeReplacementOnSurveyImport()
    {
        // Permission to everything.
        \Yii::app()->session['loginID'] = 1;

        /** @var string */
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_OldCodes.lss';

        self::importSurvey($surveyFile);

        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl('admin/expressions/sa/survey_logic_file', ['sid' => self::$surveyId]);
        try {
            self::$webDriver->get($url);
            sleep(1);
            // Check that there are no errors
            $errorAlerts = self::$webDriver->findElements(WebDriverBy::cssSelector('.alert .alert-danger'));
            $this->assertEmpty($errorAlerts);
        } catch (\Exception $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/'.__CLASS__ . '_' . __FUNCTION__ . '.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' .$filename . PHP_EOL . $ex->getMessage()
            );
        }
    }

    /**
     * Test recursive replacements helper function
     */
    public function testRecursivePregReplace()
    {
        // Sample string
        $targetString = "{OLD_CODE + OLD_CODE + OLD_CODE}";

        // Check that the function works without setting a recursion limit
        $result = recursive_preg_replace("~{[^}]*\KOLD_CODE(?=[^}]*?})~", 'NEWCODE', $targetString, -1, $count);
        $this->assertEquals(3, $count);
        $this->assertEquals("{NEWCODE + NEWCODE + NEWCODE}", $result);

        // Check that the function works with a recursion limit
        $result = recursive_preg_replace("~{[^}]*\KOLD_CODE(?=[^}]*?})~", 'NEWCODE', $targetString, -1, $count, 1);
        $this->assertEquals(2, $count);
        $this->assertEquals("{OLD_CODE + NEWCODE + NEWCODE}", $result);
    }
}
