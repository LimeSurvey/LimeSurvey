<?php

namespace ls\tests;

/**
 * @since 2017-06-16
 * @group datetimedefaultanswer
 */
class DateTimeDefaultAnswerExpressionTest extends TestBaseClass
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $fileName = self::$surveysFolder . '/limesurvey_survey_454287.lss';
        self::importSurvey($fileName);
    }

    /**
     * Test the question with lacking default answer expression,
     * date('Y-m-d'), will be filled with ' 00:00' to work with
     * createFromFormat.
     * @group expr
     */
    public function testDefaultAnswerExpressionFill()
    {
        global $thissurvey;
        $thissurvey = self::$surveyId;

        list($question, $group, $sgqa) = self::$testHelper->getSgqa('G1Q00005', self::$surveyId);

        $surveyOptions = self::$testHelper->getSurveyOptions(self::$surveyId);

        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new DummyController('dummyid'));

        // NOTE: This block us. We can't refactore buildsurveysession
        // So as soon as the test are rewrote to use selenium, buildsurveysession TODOs can be done    
        buildsurveysession(self::$surveyId);
        $surveyMode = 'group';
        $LEMdebugLevel = 0;
        $result = \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            $surveyMode,
            $surveyOptions,
            false,
            $LEMdebugLevel
        );
        $this->assertEquals(
            [
                'hasNext' => 1,
                'hasPrevious' => null
            ],
            $result
        );

        // Qanda needs this.
        $_SESSION['survey_' . self::$surveyId]['maxstep'] = 2;
        $_SESSION['survey_' . self::$surveyId]['step'] = 1;

        // Move one step to run expressions.
        $moveResult = \LimeExpressionManager::NavigateForwards();

        // Check result from qanda.
        $qanda = \retrieveAnswers(
            $_SESSION['survey_' . self::$surveyId]['fieldarray'][0]
        );

        $correctDate = date('d/m/Y');
        $this->assertNotEquals(
            false,
            strpos(
                $qanda[0][1],
                sprintf(
                    "value=\"%s\"",
                    $correctDate
                )
            ),
            'Showing todays date'
        );

    }

    /**
     * Test full default answer expression,
     * date('Y-m-d H:i').
     * @group expr2
     */
    public function testCorrectDefaultAnswerExpression()
    {
        global $thissurvey;
        $thissurvey = self::$surveyId;
        $survey = \Survey::model()->findByPk(self::$surveyId);

        list($question, $group, $sgqa) = self::$testHelper->getSgqa('q2', self::$surveyId);

        $surveyOptions = self::$testHelper->getSurveyOptions(self::$surveyId);

        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new DummyController('dummyid'));
        buildsurveysession(self::$surveyId);
        $surveyMode = 'group';
        $LEMdebugLevel = 0;
        $result = \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            $surveyMode,
            $surveyOptions,
            false,
            $LEMdebugLevel
        );
        $this->assertEquals(
            [
                'hasNext' => 1,
                'hasPrevious' => null
            ],
            $result
        );

        // Qanda needs this.
        $_SESSION['survey_' . self::$surveyId]['maxstep'] = 2;
        $_SESSION['survey_' . self::$surveyId]['step'] = 1;

        // Move one step to run expressions.
        $moveResult = \LimeExpressionManager::NavigateForwards();

        // Check result from qanda.
        $qanda = \retrieveAnswers(
            $_SESSION['survey_' . self::$surveyId]['fieldarray'][1] // 1 = second question (q2)
        );

        $correctDate = date('d/m/Y');

        $this->assertNotEquals(
            false,
            strpos(
                $qanda[0][1],
                sprintf(
                    "value=\"%s\"",
                    $correctDate
                )
            ),
            'Showing todays date'
        );
    }

    /**
     * Test default answer, date format HH:MM, expression
     * date('HH:ii'). Return empty value.
     */
    public function testWrongDefaultAnswerExpression()
    {
        global $thissurvey;
        $thissurvey = self::$surveyId;

        list($question, $group, $sgqa) = self::$testHelper->getSgqa('q3', self::$surveyId);

        $surveyOptions = self::$testHelper->getSurveyOptions(self::$surveyId);

        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new DummyController('dummyid'));
        buildsurveysession(self::$surveyId);
        $surveyMode = 'group';
        $LEMdebugLevel = 0;
        $result = \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            $surveyMode,
            $surveyOptions,
            false,
            $LEMdebugLevel
        );
        $this->assertEquals(
            [
                'hasNext' => 1,
                'hasPrevious' => null
            ],
            $result
        );

        // Qanda needs this.
        $_SESSION['survey_' . self::$surveyId]['maxstep'] = 2;
        $_SESSION['survey_' . self::$surveyId]['step'] = 1;

        // Move one step to run expressions.
        $moveResult = \LimeExpressionManager::NavigateForwards();

        // Check result from qanda.
        $qanda = \retrieveAnswers(
            $_SESSION['survey_' . self::$surveyId]['fieldarray'][2] //  2 = third question (q3)
        );

        // NB: Empty value, since default answer expression is not parsed by qanda.
        $this->assertNotEquals(
            false,
            strpos($qanda[0][1], "value=\"\""),
            'Showing empty date due to wrong expression'
        );

        // NB: Value below is todays time in format H:i, which can't be
        // parsed by qanda (expects Y-m-d H:i).
        //print_r($_SESSION['survey_' . self::$surveyId][$sgqa]);
    }
}
