<?php

namespace ls\tests;

/**
 * Maximum characters of Long (T), Huge (U) free text and Array (Texts) questions and the response column limits.
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
     * Reset the response column limits cached by ExpressionManager.
     */
    protected function tearDown(): void
    {
        $this->getResponseColumnLimitsProperty()->setValue(null, []);
        parent::tearDown();
    }

    /**
     * Default and configured maximum characters, the default is raised to the length of a saved answer.
     */
    public function testTextMaxChars()
    {
        $this->assertSame(\Question::DEFAULT_MAX_CHARS_LONG_TEXT, \Question::getTextMaxChars(\Question::QT_T_LONG_FREE_TEXT, ''));
        $this->assertSame(\Question::DEFAULT_MAX_CHARS_HUGE_TEXT, \Question::getTextMaxChars(\Question::QT_U_HUGE_FREE_TEXT, null));
        $this->assertSame(\Question::DEFAULT_MAX_CHARS_ARRAY_TEXT, \Question::getTextMaxChars(\Question::QT_SEMICOLON_ARRAY_TEXT, ''));
        $this->assertSame(500, \Question::getTextMaxChars(\Question::QT_T_LONG_FREE_TEXT, ' 500 '));
        // A configured value is not capped
        $this->assertSame(20000, \Question::getTextMaxChars(\Question::QT_SEMICOLON_ARRAY_TEXT, '20000'));
        $this->assertSame(5000000, \Question::getTextMaxChars(\Question::QT_U_HUGE_FREE_TEXT, '5000000'));

        $savedAnswer = str_repeat('a', \Question::DEFAULT_MAX_CHARS_ARRAY_TEXT + 5);
        $this->assertSame(\Question::DEFAULT_MAX_CHARS_ARRAY_TEXT + 5, \Question::getTextMaxChars(\Question::QT_SEMICOLON_ARRAY_TEXT, '', $savedAnswer));
        // A configured value is not raised
        $this->assertSame(500, \Question::getTextMaxChars(\Question::QT_SEMICOLON_ARRAY_TEXT, '500', $savedAnswer));
        $this->assertSame(3, \Question::getTextAnswerLength("a\r\nb"));
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
        $answer = str_repeat('a', \Question::DEFAULT_MAX_CHARS_ARRAY_TEXT);

        $this->assertSame($answer, $method->invoke(null, \Question::QT_SEMICOLON_ARRAY_TEXT, $answer, $sgqa, $qid));
        $this->assertArrayNotHasKey($sgqa, $this->getInvalidAnswerStrings());
        $this->assertSame($answer, $method->invoke(null, \Question::QT_SEMICOLON_ARRAY_TEXT, $answer . 'b', $sgqa, $qid));
        $this->assertArrayHasKey($sgqa, $this->getInvalidAnswerStrings());

        $qattr->setValue($LEM, $originalQattr);
        $this->getInvalidAnswerStringProperty()->setValue($LEM, []);
    }

    /**
     * An answer that was saved longer than the default maximum is not shortened.
     */
    public function testSavedLongerAnswerIsNotShortened()
    {
        $qid = 999999;
        $sgqa = 'Q' . $qid;
        $LEM = \LimeExpressionManager::singleton();
        $qattr = new \ReflectionProperty(\LimeExpressionManager::class, 'qattr');
        $qattr->setAccessible(true);
        $originalQattr = $qattr->getValue($LEM);
        $qattr->setValue($LEM, [$qid => []]);
        $sessid = new \ReflectionProperty(\LimeExpressionManager::class, 'sessid');
        $sessid->setAccessible(true);
        $originalSessid = $sessid->getValue($LEM);
        $sessid->setValue($LEM, 'responses_' . self::$surveyId);
        $this->getInvalidAnswerStringProperty()->setValue($LEM, []);
        $savedAnswer = str_repeat('a', \Question::DEFAULT_MAX_CHARS_LONG_TEXT + 5);
        $_SESSION['responses_' . self::$surveyId][$sgqa] = $savedAnswer;

        $method = new \ReflectionMethod(\LimeExpressionManager::class, 'truncateTextAnswer');
        $method->setAccessible(true);
        $this->assertSame($savedAnswer, $method->invoke(null, \Question::QT_T_LONG_FREE_TEXT, $savedAnswer, $sgqa, $qid));
        $this->assertArrayNotHasKey($sgqa, $this->getInvalidAnswerStrings());
        $this->assertSame($savedAnswer, $method->invoke(null, \Question::QT_T_LONG_FREE_TEXT, $savedAnswer . 'b', $sgqa, $qid));
        $this->assertArrayHasKey($sgqa, $this->getInvalidAnswerStrings());

        unset($_SESSION['responses_' . self::$surveyId][$sgqa]);
        $qattr->setValue($LEM, $originalQattr);
        $sessid->setValue($LEM, $originalSessid);
        $this->getInvalidAnswerStringProperty()->setValue($LEM, []);
    }

    /**
     * A value is shortened to the limit of its response column, in bytes or characters, less when encrypted.
     */
    public function testValueIsFittedToResponseColumn()
    {
        $sgqa = 'Q999999';
        $method = new \ReflectionMethod(\LimeExpressionManager::class, 'fitResponseColumn');
        $method->setAccessible(true);

        // No limit: unchanged
        $this->setResponseColumnLimit($sgqa, null);
        $this->assertSame(str_repeat('ä', 100), $method->invoke(null, str_repeat('ä', 100), $sgqa, 999999));

        // 'ä' is 2 bytes, so a cut at exactly 11 bytes would split a character
        $this->setResponseColumnLimit($sgqa, ['length' => 11, 'bytes' => true, 'encrypted' => false]);
        $this->assertSame(str_repeat('ä', 5), $method->invoke(null, str_repeat('ä', 10), $sgqa, 999999));
        $this->assertSame(str_repeat('ä', 5), $method->invoke(null, str_repeat('ä', 5), $sgqa, 999999));

        $this->setResponseColumnLimit($sgqa, ['length' => 3, 'bytes' => false, 'encrypted' => false]);
        $this->assertSame('äää', $method->invoke(null, 'ääää', $sgqa, 999999));

        $this->setResponseColumnLimit($sgqa, ['length' => 65535, 'bytes' => true, 'encrypted' => true]);
        $maxBytes = \Yii::app()->sodium->getMaxPlaintextBytes(65535);
        $result = $method->invoke(null, str_repeat('a', 65535), $sgqa, 999999);
        $this->assertSame($maxBytes, strlen($result));
        $this->assertLessThanOrEqual(65535, strlen(\Yii::app()->sodium->encrypt($result)));
    }

    /**
     * A submitted answer that does not fit the response column is shortened and the question gets an error.
     */
    public function testAnswerNotFittingResponseColumnIsShortened()
    {
        $qid = 999999;
        $sgqa = 'Q' . $qid;
        $LEM = \LimeExpressionManager::singleton();
        $qattr = new \ReflectionProperty(\LimeExpressionManager::class, 'qattr');
        $qattr->setAccessible(true);
        $originalQattr = $qattr->getValue($LEM);
        $qattr->setValue($LEM, [$qid => []]);
        $this->getInvalidAnswerStringProperty()->setValue($LEM, []);
        $this->setResponseColumnLimit($sgqa, ['length' => 5, 'bytes' => true, 'encrypted' => false]);

        $method = new \ReflectionMethod(\LimeExpressionManager::class, 'truncateTextAnswer');
        $method->setAccessible(true);
        $this->assertSame('abcde', $method->invoke(null, \Question::QT_T_LONG_FREE_TEXT, 'abcdefgh', $sgqa, $qid));
        $this->assertArrayHasKey($sgqa, $this->getInvalidAnswerStrings());

        $qattr->setValue($LEM, $originalQattr);
        $this->getInvalidAnswerStringProperty()->setValue($LEM, []);
    }

    /**
     * An Equation result that does not fit the response column is shortened.
     */
    public function testTooLongEquationResultIsShortened()
    {
        $sgqa = 'Q999999';
        $method = new \ReflectionMethod(\LimeExpressionManager::class, 'truncateEquationResult');
        $method->setAccessible(true);
        $this->setResponseColumnLimit($sgqa, ['length' => 65535, 'bytes' => true, 'encrypted' => false]);

        $this->assertSame(str_repeat('a', 40000), $method->invoke(null, str_repeat('a', 40000), $sgqa, 999999));
        $result = $method->invoke(null, str_repeat('ä', 40000), $sgqa, 999999);
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
     * The line breaks of a shortened answer are kept, a line break counting as one character.
     */
    public function testLineBreaksAreKeptWhenShortened()
    {
        list($sgqa) = $this->processAnswer("ab\r\ncd\r\nefghijklmno");

        $this->assertSame("ab\r\ncd\r\nefgh", $_SESSION['responses_' . self::$surveyId][$sgqa]);
        $this->assertArrayHasKey($sgqa, $this->getInvalidAnswerStrings());
    }

    /**
     * Activating a survey creates a MEDIUMTEXT response column for Long free text questions on MySQL.
     * MEDIUMTEXT is only different from TEXT on MySQL/MariaDB.
     * @group mysql
     */
    public function testActivationCreatesMediumtextColumn()
    {
        $db = \Yii::app()->db;
        list(, , $sgqa) = self::$testHelper->getSgqa('Q00', self::$surveyId);
        self::$testHelper->activateSurvey(self::$surveyId);
        $tableName = $db->tablePrefix . 'responses_' . self::$surveyId;

        $db->schema->refresh();
        $this->assertSame('mediumtext', strtolower($db->schema->getTable($tableName)->getColumn($sgqa)->dbType));

        // ExpressionManager reads the limit of the column
        list($question) = self::$testHelper->getSgqa('Q00', self::$surveyId);
        $LEM = \LimeExpressionManager::singleton();
        $sid = new \ReflectionProperty(\LimeExpressionManager::class, 'sid');
        $sid->setAccessible(true);
        $originalSid = $sid->getValue($LEM);
        $sid->setValue($LEM, self::$surveyId);
        $method = new \ReflectionMethod(\LimeExpressionManager::class, 'getResponseColumnLimit');
        $method->setAccessible(true);
        $this->assertSame(
            ['length' => 16777215, 'bytes' => true, 'encrypted' => false],
            $method->invoke(null, $sgqa, $question->qid)
        );
        $sid->setValue($LEM, $originalSid);
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
     * Set the cached limit of a response column of the current survey of ExpressionManager.
     *
     * @param string $sgqa The answer column
     * @param array{length: int, bytes: bool, encrypted: bool}|null $limit
     * @return void
     */
    private function setResponseColumnLimit($sgqa, $limit)
    {
        $sid = new \ReflectionProperty(\LimeExpressionManager::class, 'sid');
        $sid->setAccessible(true);
        $property = $this->getResponseColumnLimitsProperty();
        $limits = $property->getValue();
        $limits[intval($sid->getValue(\LimeExpressionManager::singleton())) . ':' . $sgqa] = $limit;
        $property->setValue(null, $limits);
    }

    /**
     * Get the accessible responseColumnLimits property of ExpressionManager.
     *
     * @return \ReflectionProperty
     */
    private function getResponseColumnLimitsProperty()
    {
        $property = new \ReflectionProperty(\LimeExpressionManager::class, 'responseColumnLimits');
        $property->setAccessible(true);
        return $property;
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
