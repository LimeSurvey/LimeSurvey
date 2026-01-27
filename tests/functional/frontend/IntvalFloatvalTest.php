<?php
namespace ls\tests;
use Facebook\WebDriver\WebDriverBy;

/**
 - @since 2019-01-20
 * @group jsphp
 */
class IntvalFloatvalTest extends TestBaseClassWeb
{
    /**
     * Launch test comparing JS vs PHP and fixed value
     * @return void
     */
    public function testCompare()
    {
        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_15598_intval_floatval_jsphp_compare.lss';
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
            /* Fill with data */
            $aMultiQuestionInfo = self::$testHelper->getSgqa('MULTI',self::$surveyId);
            $baseSgqa = $aMultiQuestionInfo[2];
            $rawQuestions = \Question::model()->findAll("sid = :sid", [":sid" => self::$surveyId]);
            $questions = [];
            foreach ($rawQuestions as $rawQuestion) {
                $questions[$rawQuestion->title] = $rawQuestion;
            }
            self::$webDriver->answerTextQuestion($baseSgqa . '_S' . $questions['float']->qid, '42.42');
            self::$webDriver->answerTextQuestion($baseSgqa . '_S' . $questions['text']->qid, 'LimeSurvey');
            self::$webDriver->answerTextQuestion($baseSgqa . '_S' . $questions['floattext']->qid, '42.42-LimeSurvey');
            self::$webDriver->answerTextQuestion($baseSgqa . '_S' . $questions['floatoperation']->qid, '+42.42 + 33');
            self::$webDriver->answerTextQuestion($baseSgqa . '_S' . $questions['floatnegative']->qid, '-3.5');
            self::$webDriver->answerTextQuestion($baseSgqa . '_S' . $questions['floatnegativeoperati']->qid, '-3.5+42');

            /* Fill JS result */
            $intvalFloatJS = self::$webDriver->findElement(WebDriverBy::id('intval-float'))->getText();
            $floatvalFloatJS = self::$webDriver->findElement(WebDriverBy::id('floatval-float'))->getText();
            $intvalTextJS = self::$webDriver->findElement(WebDriverBy::id('intval-text'))->getText();
            $floatvalTextJS = self::$webDriver->findElement(WebDriverBy::id('floatval-text'))->getText();
            $intvalFloattextJS = self::$webDriver->findElement(WebDriverBy::id('intval-floattext'))->getText();
            $floatvalFloattextJS = self::$webDriver->findElement(WebDriverBy::id('floatval-floattext'))->getText();
            $intvalFloatoperationJS = self::$webDriver->findElement(WebDriverBy::id('intval-floatoperation'))->getText();
            $floatvalFloatoperationJS = self::$webDriver->findElement(WebDriverBy::id('floatval-floatoperation'))->getText();
            $intvalFloatnegativeJS = self::$webDriver->findElement(WebDriverBy::id('intval-floatnegative'))->getText();
            $floatvalFloatnegativeJS = self::$webDriver->findElement(WebDriverBy::id('floatval-floatnegative'))->getText();
            $intvalFloatnegativeoperatiJS = self::$webDriver->findElement(WebDriverBy::id('intval-floatnegativeoperati'))->getText();
            $floatvalFloatnegativeoperatiJS = self::$webDriver->findElement(WebDriverBy::id('floatval-floatnegativeoperati'))->getText();
            $intvalHiddenJS = self::$webDriver->findElement(WebDriverBy::id('intval-hidden'))->getText();
            $floatvalHiddenJS = self::$webDriver->findElement(WebDriverBy::id('floatval-hidden'))->getText();
            $intvalTrueJS = self::$webDriver->findElement(WebDriverBy::id('intval-true'))->getText();
            $floatvalTrueJS = self::$webDriver->findElement(WebDriverBy::id('floatval-true'))->getText();
            $intvalFalseJS = self::$webDriver->findElement(WebDriverBy::id('intval-false'))->getText();
            $floatvalFalseJS = self::$webDriver->findElement(WebDriverBy::id('floatval-false'))->getText();

            self::$webDriver->executeScript('window.scrollTo(0,document.body.scrollHeight);');
            sleep(1);

            /* Move next */
            $nextButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $nextButton->click();

            /* Fill PHP result */
            $intvalFloatPHP = self::$webDriver->findElement(WebDriverBy::id('intval-float'))->getText();
            $floatvalFloatPHP = self::$webDriver->findElement(WebDriverBy::id('floatval-float'))->getText();
            $intvalTextPHP = self::$webDriver->findElement(WebDriverBy::id('intval-text'))->getText();
            $floatvalTextPHP = self::$webDriver->findElement(WebDriverBy::id('floatval-text'))->getText();
            $intvalFloattextPHP = self::$webDriver->findElement(WebDriverBy::id('intval-floattext'))->getText();
            $floatvalFloattextPHP = self::$webDriver->findElement(WebDriverBy::id('floatval-floattext'))->getText();
            $intvalFloatoperationPHP = self::$webDriver->findElement(WebDriverBy::id('intval-floatoperation'))->getText();
            $floatvalFloatoperationPHP = self::$webDriver->findElement(WebDriverBy::id('floatval-floatoperation'))->getText();
            $intvalFloatnegativePHP = self::$webDriver->findElement(WebDriverBy::id('intval-floatnegative'))->getText();
            $floatvalFloatnegativePHP = self::$webDriver->findElement(WebDriverBy::id('floatval-floatnegative'))->getText();
            $intvalFloatnegativeoperatiPHP = self::$webDriver->findElement(WebDriverBy::id('intval-floatnegativeoperati'))->getText();
            $floatvalFloatnegativeoperatiPHP = self::$webDriver->findElement(WebDriverBy::id('floatval-floatnegativeoperati'))->getText();
            $intvalHiddenPHP = self::$webDriver->findElement(WebDriverBy::id('intval-hidden'))->getText();
            $floatvalHiddenPHP = self::$webDriver->findElement(WebDriverBy::id('floatval-hidden'))->getText();
            $intvalTruePHP = self::$webDriver->findElement(WebDriverBy::id('intval-true'))->getText();
            $floatvalTruePHP = self::$webDriver->findElement(WebDriverBy::id('floatval-true'))->getText();
            $intvalFalsePHP = self::$webDriver->findElement(WebDriverBy::id('intval-false'))->getText();
            $floatvalFalsePHP = self::$webDriver->findElement(WebDriverBy::id('floatval-false'))->getText();

            /* Do the JS vs PHP compare */
            $this->assertEquals($intvalFloatJS, $intvalFloatPHP, 'intval(42.42) : «' . $intvalFloatJS ."» vs «".$intvalFloatPHP."»");
            $this->assertEquals($floatvalFloatJS, $floatvalFloatPHP, 'floatval(42.42) : «' . $floatvalFloatJS ."» vs «".$floatvalFloatPHP."»");
            $this->assertEquals($intvalTextJS, $intvalTextPHP, 'intval(LimeSurvey) : «' . $intvalTextJS ."» vs «".$intvalTextPHP."»");
            $this->assertEquals($floatvalTextJS, $floatvalTextPHP, 'floatval(LimeSurvey) : «' . $floatvalTextJS ."» vs «".$floatvalTextPHP."»");
            $this->assertEquals($intvalFloattextJS, $intvalFloattextPHP, 'intval(42.42-LimeSurvey) : «' . $intvalFloattextJS ."» vs «".$intvalFloattextPHP."»");
            $this->assertEquals($floatvalFloattextJS, $floatvalFloattextPHP, 'floatval(42.42-LimeSurvey) : «' . $floatvalFloattextJS ."» vs «".$floatvalFloattextPHP."»");
            $this->assertEquals($intvalFloatoperationJS, $intvalFloatoperationPHP, 'intval(+42.42 + 33) : «' . $intvalFloatoperationJS ."» vs «".$intvalFloatoperationPHP."»");
            $this->assertEquals($floatvalFloatoperationJS, $floatvalFloatoperationPHP, 'floatval(+42.42 + 33) : «' . $floatvalFloatoperationJS ."» vs «".$floatvalFloatoperationPHP."»");
            $this->assertEquals($intvalFloatnegativeJS, $intvalFloatnegativePHP, 'intval(-3.5) : «' . $intvalFloatnegativeJS ."» vs «".$intvalFloatnegativePHP."»");
            $this->assertEquals($floatvalFloatnegativeJS, $floatvalFloatnegativePHP, 'floatval(-3.5) : «' . $floatvalFloatnegativeJS ."» vs «".$floatvalFloatnegativePHP."»");
            $this->assertEquals($intvalFloatnegativeoperatiJS, $intvalFloatnegativeoperatiPHP, 'intval(-3.5+42) : «' . $intvalFloatnegativeoperatiJS ."» vs «".$intvalFloatnegativeoperatiPHP."»");
            $this->assertEquals($floatvalFloatnegativeoperatiJS, $floatvalFloatnegativeoperatiPHP, 'floatval(-3.5+42) : «' . $floatvalFloatnegativeoperatiJS ."» vs «".$floatvalFloatnegativeoperatiPHP."»");
            $this->assertEquals($intvalHiddenJS, $intvalHiddenPHP, 'intval(HIDDEN.NAOK) : «' . $intvalHiddenJS ."» vs «".$intvalHiddenPHP."»");
            $this->assertEquals($floatvalHiddenJS, $floatvalHiddenPHP, 'floatval(HIDDEN.NAOK) : «' . $floatvalHiddenJS ."» vs «".$floatvalHiddenPHP."»");
            $this->assertEquals($intvalTrueJS, $intvalTruePHP, 'intval(True) : «' . $intvalTrueJS ."» vs «".$intvalTruePHP."»");
            $this->assertEquals($floatvalTrueJS, $floatvalTruePHP, 'floatval(True) : «' . $floatvalTrueJS ."» vs «".$floatvalTruePHP."»");
            $this->assertEquals($intvalFalseJS, $intvalFalsePHP, 'intval(False) : «' . $intvalFalseJS ."» vs «".$intvalFalsePHP."»");
            $this->assertEquals($floatvalFalseJS, $floatvalFalsePHP, 'floatval(False) : «' . $floatvalFalseJS ."» vs «".$floatvalFalsePHP."»");
            
            /* Fixed string compare (wait for) : can happen (not a bug), but broke API number */
            $this->assertEquals($intvalFloatJS, "42", 'intval(42.42) : «' . $intvalFloatJS ."» vs fixed «42»");
            $this->assertEquals($floatvalFloatJS, "42.42", 'floatval(42.42) : «' . $floatvalFloatJS ."» vs fixed «42.42»");
            $this->assertEquals($intvalTextJS, "0", 'intval(LimeSurvey) : «' . $intvalTextJS ."» vs fixed «0»");
            $this->assertEquals($floatvalTextJS, "0", 'floatval(LimeSurvey) : «' . $floatvalTextJS ."» vs fixed «0»");
            $this->assertEquals($intvalFloattextJS, "42", 'intval(42.42-LimeSurvey) : «' . $intvalFloattextJS ."» vs fixed «42»");
            $this->assertEquals($floatvalFloattextJS, "42.42", 'floatval(42.42-LimeSurvey) : «' . $floatvalFloattextJS ."» vs fixed «42.42»");
            $this->assertEquals($intvalFloatoperationJS, "42", 'intval(+42.42 + 33) : «' . $intvalFloatoperationJS ."» vs fixed «42»");
            $this->assertEquals($floatvalFloatoperationJS, "42.42", 'floatval(+42.42 + 33) : «' . $floatvalFloatoperationJS ."» vs fixed «42.42»");
            $this->assertEquals($intvalFloatnegativeJS, "-3", 'intval(-3.5) : «' . $intvalFloatnegativeJS ."» vs fixed «-3»");
            $this->assertEquals($floatvalFloatnegativeJS, "-3.5", 'floatval(-3.5) : «' . $floatvalFloatnegativeJS ."» vs fixed «-3.5»");
            $this->assertEquals($intvalFloatnegativeoperatiJS, "-3", 'intval(-3.5+42) : «' . $intvalFloatnegativeoperatiJS ."» vs fixed «-3»");
            $this->assertEquals($floatvalFloatnegativeoperatiJS, "-3.5", 'floatval(-3.5+42) : «' . $floatvalFloatnegativeoperatiJS ."» vs fixed «-3.5»");
            $this->assertEquals($intvalHiddenJS, "0", 'intval(HIDDEN.NAOK) : «' . $intvalHiddenJS ."» vs fixed «0»");
            $this->assertEquals($floatvalHiddenJS, "0", 'floatval(HIDDEN.NAOK) : «' . $floatvalHiddenJS ."» vs fixed «0»");
            $this->assertEquals($intvalTrueJS, "1", 'intval(True) : «' . $intvalTrueJS ."» vs fixed «1»");
            $this->assertEquals($floatvalTrueJS, "1", 'floatval(True) : «' . $floatvalTrueJS ."» vs fixed «1»");
            $this->assertEquals($intvalFalseJS, "0", 'intval(False) : «' . $intvalFalseJS ."» vs fixed «0»");
            $this->assertEquals($floatvalFalseJS, "0", 'floatval(False) : «' . $floatvalFalseJS ."» vs fixed «0»");

        } catch (\Exception $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/'.__CLASS__.'_' . __FUNCTION__ . '.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
