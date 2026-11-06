<?php

namespace ls\tests;

use statFunctions\countFunctions;

/**
 * statFunctions plugin: statCount() and statCountIf() on the responses of the current survey.
 *
 * The survey has the numeric question Q00 (submitted responses 1, 2 and 3, five more not submitted)
 * and the long free text question Q01 (submitted responses "OK" three times).
 *
 * @group plugins
 */
class StatFunctionsCountTest extends TestBaseClass
{
    /** @var string Response column of the numeric question Q00 */
    private static $numericColumn;

    /** @var string Response column of the text question Q01 */
    private static $textColumn;

    /**
     * Import the survey archive with its responses and load the plugin classes
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        require_once APPPATH . 'core/plugins/statFunctions/countFunctions.php';
        require_once APPPATH . 'core/plugins/statFunctions/questionCodeHelper.php';

        self::importSurvey(self::$surveysFolder . '/survey_archive_statCountFunctionsTest.lsa');
        foreach (['Q00' => 'numericColumn', 'Q01' => 'textColumn'] as $title => $property) {
            $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0, 'title' => $title]);
            self::$$property = "Q{$question->qid}";
        }
    }

    /**
     * Start the survey in ExpressionScript, the functions count the responses of its survey
     */
    public function setUp(): void
    {
        parent::setUp();
        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new DummyController('dummyid'));
        \LimeExpressionManager::SetDirtyFlag();
        \LimeExpressionManager::SetSurveyId(self::$surveyId);
        \buildsurveysession(self::$surveyId);
        \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            'group',
            self::$testHelper->getSurveyOptions(self::$surveyId),
            true,
            0
        );
    }

    /**
     * Reset request and session data
     */
    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        parent::tearDown();
    }

    /**
     * statCount() counts the answered responses of a numeric question
     */
    public function testStatCountOfNumericQuestion()
    {
        $this->assertSame(3, countFunctions::statCount(self::$numericColumn));
        $this->assertSame(8, countFunctions::statCount(self::$numericColumn, false));
    }

    /**
     * statCountIf() compares numbers as numbers and anything else as text
     */
    public function testStatCountIfOfNumericQuestion()
    {
        $this->assertSame(0, countFunctions::statCountIf(self::$numericColumn, 'NOT'));
        $this->assertSame(3, countFunctions::statCountIf(self::$numericColumn, '<>NOT'));
        $this->assertSame(2, countFunctions::statCountIf(self::$numericColumn, '>1'));
        $this->assertSame(2, countFunctions::statCountIf(self::$numericColumn, '3', false));
    }

    /**
     * statCountIf() on a text question
     */
    public function testStatCountIfOfTextQuestion()
    {
        $this->assertSame(3, countFunctions::statCountIf(self::$textColumn, 'OK'));
        $this->assertSame(5, countFunctions::statCountIf(self::$textColumn, 'OK', false));
    }
}
