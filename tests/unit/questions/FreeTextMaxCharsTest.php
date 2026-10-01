<?php

namespace ls\tests;

/**
 * Maximum characters of Long (T) and Huge (U) free text questions.
 * @see https://bugs.limesurvey.org/view.php?id=18275
 * @group freetextmaxchars
 */
class FreeTextMaxCharsTest extends TestBaseClass
{
    /** @var int Maximum characters set on the test question */
    private const MAX_CHARS = 10;

    /**
     * Import a survey with one Long free text question and set its maximum characters.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_666368.lss';
        self::importSurvey($surveyFile);

        list($question) = self::$testHelper->getSgqa('Q00', self::$surveyId);
        $attribute = new \QuestionAttribute();
        $attribute->qid = $question->qid;
        $attribute->attribute = 'maximum_chars';
        $attribute->value = (string) self::MAX_CHARS;
        $attribute->language = '';
        $attribute->save();
    }

    /**
     * Default, configured and capped maximum characters.
     */
    public function testEffectiveMaxCharsForType()
    {
        $this->assertSame(
            \QuestionBaseRenderer::DEFAULT_MAX_CHARS_LONG_TEXT,
            \QuestionBaseRenderer::getEffectiveMaxCharsForType(\Question::QT_T_LONG_FREE_TEXT, '')
        );
        $this->assertSame(
            \QuestionBaseRenderer::DEFAULT_MAX_CHARS_HUGE_TEXT,
            \QuestionBaseRenderer::getEffectiveMaxCharsForType(\Question::QT_U_HUGE_FREE_TEXT, null)
        );
        $this->assertSame(500, \QuestionBaseRenderer::getEffectiveMaxCharsForType(\Question::QT_T_LONG_FREE_TEXT, ' 500 '));
        $this->assertSame(
            \QuestionBaseRenderer::MAX_CHARS_CAP,
            \QuestionBaseRenderer::getEffectiveMaxCharsForType(\Question::QT_U_HUGE_FREE_TEXT, \QuestionBaseRenderer::MAX_CHARS_CAP + 1)
        );
    }

    /**
     * A submitted answer longer than the maximum is shortened and the question gets an error.
     */
    public function testTooLongAnswerIsShortened()
    {
        list($sgqa) = $this->processAnswer('abcdefghijklmno');

        $this->assertSame('abcdefghij', $_SESSION['responses_' . self::$surveyId][$sgqa]);
        $this->assertArrayHasKey($sgqa, $this->getInvalidAnswerStrings());
    }

    /**
     * A line break counts as one character, as the textarea maxlength in the browser does.
     */
    public function testLineBreakCountsAsOneCharacter()
    {
        $answer = "abcd\r\nefgh\r\n";
        list($sgqa) = $this->processAnswer($answer);

        $this->assertSame($answer, $_SESSION['responses_' . self::$surveyId][$sgqa]);
        $this->assertArrayNotHasKey($sgqa, $this->getInvalidAnswerStrings());
    }

    /**
     * Update_709 changes TEXT response columns of Long free text questions to MEDIUMTEXT on MySQL.
     */
    public function testUpdate709ChangesColumnToMediumtext()
    {
        $db = \Yii::app()->db;
        if ($db->driverName != 'mysql') {
            $this->markTestSkipped('Only MySQL/MariaDB is changed by Update_709');
        }
        list($question, , $sgqa) = self::$testHelper->getSgqa('Q00', self::$surveyId);
        self::$testHelper->activateSurvey(self::$surveyId);
        $tableName = \Yii::app()->db->tablePrefix . 'responses_' . self::$surveyId;
        $db->createCommand()->alterColumn($tableName, $sgqa, 'text');
        $db->schema->refresh();

        $update = new \LimeSurvey\Helpers\Update\Update_709($db, []);
        $update->up();
        // Running it again must not fail
        $db->schema->refresh();
        $update->up();

        $db->schema->refresh();
        $this->assertSame('mediumtext', strtolower($db->schema->getTable($tableName)->getColumn($sgqa)->dbType));
    }

    /**
     * Submit an answer to the Long free text question and process it with ExpressionManager.
     *
     * @param string $answer The submitted answer
     * @return array{0: string} The answer column
     */
    private function processAnswer($answer)
    {
        \Yii::app()->setController(new DummyController('dummyid'));
        list($question, $group, $sgqa) = self::$testHelper->getSgqa('Q00', self::$surveyId);

        $_POST = [];
        $_SESSION = [];
        $surveyOptions = self::$testHelper->getSurveyOptions(self::$surveyId);
        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \buildsurveysession(self::$surveyId);
        $_SESSION['LEMsid'] = self::$surveyId;
        \LimeExpressionManager::StartSurvey(self::$surveyId, 'group', $surveyOptions, false, 0);
        \LimeExpressionManager::singleton()->setCurrentQset([
            $question->qid => [
                'info' => [
                    'qid' => $question->qid,
                    'gseq' => 0,
                    'type' => \Question::QT_T_LONG_FREE_TEXT,
                    'hidden' => false,
                ],
                'sgqa' => $sgqa,
            ],
        ]);
        $_POST['relevance' . $question->qid] = 1;
        $_POST['relevanceG0'] = 1;
        $_POST[$sgqa] = $answer;
        $this->getInvalidAnswerStringProperty()->setValue(\LimeExpressionManager::singleton(), []);

        \LimeExpressionManager::ProcessCurrentResponses();
        return [$sgqa];
    }

    /**
     * Get the invalid answer strings set by ExpressionManager.
     *
     * @return array<string, string>
     */
    private function getInvalidAnswerStrings()
    {
        return $this->getInvalidAnswerStringProperty()->getValue(\LimeExpressionManager::singleton());
    }

    /**
     * Get the accessible invalidAnswerString property of ExpressionManager.
     *
     * @return \ReflectionProperty
     */
    private function getInvalidAnswerStringProperty()
    {
        $property = new \ReflectionProperty(\LimeExpressionManager::class, 'invalidAnswerString');
        $property->setAccessible(true);
        return $property;
    }
}
