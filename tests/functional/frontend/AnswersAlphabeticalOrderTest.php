<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * @since 6.3.7 : alphabetical order with utf8 caracter
 * @since 6.3.7 : alphabetical order delete answers
 * @group questions
 */
class AnswersAlphabeticalOrderTest extends TestBaseClassWeb
{
    /**
     * @inheritdoc
     * Import needed survey
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_OrderTestSurvey.lss';
        self::importSurvey($surveyFile);
    }

    /**
     * Check if orderig delete answers
     * @see https://bugs.limesurvey.org/view.php?id=19208
     */
    public function testOrderSameAnswersText()
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => "Y",
            ]
        );
        self::$webDriver->get($url);
        $questions = $this->getAllSurveyQuestions();
        $SGQ = "Q" . $questions['MultipleSame']['qid'];
        /* order options must be A,A, B ,B */
        $expectedOrderLabels = ['A', 'A', 'B', 'B'];
        $index = 2; // 1st element is empty(Please select), nth-of-type start at 1
        foreach ($expectedOrderLabels as $expectedLabel) {
            $option = self::$webDriver->findByCss('#answer' . $SGQ . ' option:nth-of-type(' . $index . ')');
            $optionText = $option->getText();
            $this->assertEquals(
                $optionText,
                $expectedLabel,
                "Expected label {$expectedLabel} , get label {$optionText} as index {$index} on MultipleSame question"
            );
            $index++;
        }
    }

    /**
     * Test order UTF8
     * @see https://bugs.limesurvey.org/view.php?id=19208
     */
    public function testOrderUtf8AnswersText()
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => "Y",
            ]
        );
        self::$webDriver->get($url);
        $questions = $this->getAllSurveyQuestions();
        /* 1st with spanish language */
        $SGQ = "Q" . $questions['SpanishLanguage']['qid'];
        /* order options must be */
        $expectedOrderLabels = ['árabe', 'coreano', 'español', 'francés'];
        $index = 2; // nth-of-type start at 1
        foreach ($expectedOrderLabels as $expectedLabel) {
            $option = self::$webDriver->findByCss('#answer' . $SGQ . ' option:nth-of-type(' . $index . ')');
            $optionText = $option->getText();
            $this->assertEquals(
                $optionText,
                $expectedLabel,
                "Expected label {$expectedLabel} , get label {$optionText} as index {$index} on SpanishLanguage question"
            );
            $index++;
        }

        /* 2,nd with french 1st name (in spanich order) */
        $SGQ = "Q" . $questions['FrFirstName']['qid'];
        /* order options must be */
        $expectedOrderLabels = ['emile', 'Emile', 'émile', 'Émile', 'zoé', 'Zoé'];
        $index = 2; // nth-of-type start at 1
        foreach ($expectedOrderLabels as $expectedLabel) {
            $option = self::$webDriver->findByCss('#answer' . $SGQ . ' option:nth-of-type(' . $index . ')');
            $optionText = $option->getText();
            $this->assertEquals(
                $optionText,
                $expectedLabel,
                "Expected label {$expectedLabel} , get label {$optionText} as index {$index} on FrFirstName question"
            );
            $index++;
        }
    }
}
