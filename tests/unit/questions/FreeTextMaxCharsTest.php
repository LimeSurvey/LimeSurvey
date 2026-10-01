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
        $this->assertSame(
            \QuestionBaseRenderer::MAX_CHARS_ARRAY_TEXT,
            \QuestionBaseRenderer::getEffectiveMaxCharsForType(\Question::QT_SEMICOLON_ARRAY_TEXT, '')
        );
        $this->assertSame(500, \QuestionBaseRenderer::getEffectiveMaxCharsForType(\Question::QT_SEMICOLON_ARRAY_TEXT, '500'));
        $this->assertSame(
            \QuestionBaseRenderer::MAX_CHARS_ARRAY_TEXT,
            \QuestionBaseRenderer::getEffectiveMaxCharsForType(\Question::QT_SEMICOLON_ARRAY_TEXT, \QuestionBaseRenderer::MAX_CHARS_ARRAY_TEXT + 1)
        );
    }

    /**
     * A submitted Array (Texts) answer longer than the default maximum is shortened.
     */
    public function testTooLongArrayTextAnswerIsShortened()
    {
        $qid = 999999;
        $sgqa = 'Q' . $qid . '_SQ001_SQ001';
        $LEM = \LimeExpressionManager::singleton();
        $qattr = new \ReflectionProperty(\LimeExpressionManager::class, 'qattr');
        $qattr->setAccessible(true);
        $originalQattr = $qattr->getValue($LEM);
        $qattr->setValue($LEM, [$qid => []]);
        $this->getInvalidAnswerStringProperty()->setValue($LEM, []);

        $method = new \ReflectionMethod(\LimeExpressionManager::class, 'truncateTextAnswer');
        $method->setAccessible(true);
        $answer = str_repeat('a', \QuestionBaseRenderer::MAX_CHARS_ARRAY_TEXT);

        $this->assertSame($answer, $method->invoke(null, \Question::QT_SEMICOLON_ARRAY_TEXT, $answer, $sgqa, $qid));
        $this->assertArrayNotHasKey($sgqa, $this->getInvalidAnswerStrings());
        $this->assertSame($answer, $method->invoke(null, \Question::QT_SEMICOLON_ARRAY_TEXT, $answer . 'b', $sgqa, $qid));
        $this->assertArrayHasKey($sgqa, $this->getInvalidAnswerStrings());

        $qattr->setValue($LEM, $originalQattr);
        $this->getInvalidAnswerStringProperty()->setValue($LEM, []);
    }

    /**
     * An Equation result that does not fit the TEXT column on MySQL is shortened without breaking a multibyte character.
     */
    public function testTooLongEquationResultIsShortenedOnMysql()
    {
        list($question, , $sgqa) = self::$testHelper->getSgqa('Q00', self::$surveyId);
        $method = new \ReflectionMethod(\LimeExpressionManager::class, 'truncateEquationResult');
        $method->setAccessible(true);
        $shortResult = str_repeat('a', 40000);
        // 'ä' is 2 bytes, so a cut at exactly 65535 bytes would split a character
        $longResult = str_repeat('ä', 40000);

        $this->assertSame($shortResult, $method->invoke(null, $shortResult, $sgqa, $question->qid));
        $result = $method->invoke(null, $longResult, $sgqa, $question->qid);
        if (\Yii::app()->db->driverName != 'mysql') {
            $this->assertSame($longResult, $result);
            return;
        }
        $this->assertSame(65534, strlen($result));
        $this->assertTrue(mb_check_encoding($result, 'UTF-8'));
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
     * Activating a survey creates a MEDIUMTEXT response column for Long free text questions on MySQL.
     */
    public function testActivationCreatesMediumtextColumn()
    {
        $db = \Yii::app()->db;
        if ($db->driverName != 'mysql') {
            $this->markTestSkipped('MEDIUMTEXT is only different from TEXT on MySQL/MariaDB');
        }
        list(, , $sgqa) = self::$testHelper->getSgqa('Q00', self::$surveyId);
        self::$testHelper->activateSurvey(self::$surveyId);
        $tableName = $db->tablePrefix . 'responses_' . self::$surveyId;

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
