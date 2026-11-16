<?php

namespace ls\tests;

/**
 * In the survey runtime, getQuestionAttributes() reads the attributes of a question only once per request.
 */
class QuestionAttributeRequestCacheTest extends TestBaseClass
{
    /** @var \Question Short free text question of the test survey */
    private static $question;

    /** @var \CController|null Controller before the test */
    private $previousController;

    /**
     * Imports a survey with a short free text question.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_141451_testSaveQuestionAttributes.lss');
        self::$question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'G01Q01']);
    }

    /**
     * Starts every test with an empty request cache.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->previousController = \Yii::app()->getController();
        \QuestionAttribute::clearRequestCache();
        \QuestionAttribute::model()->setQuestionAttribute(self::$question->qid, 'cssclass', 'before');
    }

    /**
     * Restores the controller and empties the request cache.
     */
    public function tearDown(): void
    {
        \Yii::app()->setController($this->previousController);
        \QuestionAttribute::clearRequestCache();
        parent::tearDown();
    }

    /**
     * Changes the cssclass attribute of the test question without the model, so no cache is cleared.
     *
     * @param string $value
     * @return void
     */
    private function updateCssClassInDatabase($value)
    {
        \Yii::app()->db->createCommand()->update(
            '{{question_attributes}}',
            ['value' => $value],
            "qid = :qid and attribute = 'cssclass'",
            [':qid' => self::$question->qid]
        );
    }

    /**
     * In the survey runtime the attributes are read from the database only the first time.
     */
    public function testSurveyRuntimeReadsAttributesOnce()
    {
        \Yii::app()->setController(new \SurveyController('survey'));
        $qid = self::$question->qid;

        $this->assertSame('before', \QuestionAttribute::model()->getQuestionAttributes($qid)['cssclass']);
        $this->updateCssClassInDatabase('after');
        $this->assertSame('before', \QuestionAttribute::model()->getQuestionAttributes($qid)['cssclass']);
        $this->assertSame('before', \QuestionAttribute::model()->getQuestionAttributes(self::$question)['cssclass']);

        // Another language is a separate cache entry
        $this->assertSame('after', \QuestionAttribute::model()->getQuestionAttributes($qid, 'en')['cssclass']);

        \QuestionAttribute::clearRequestCache($qid);
        $this->assertSame('after', \QuestionAttribute::model()->getQuestionAttributes($qid)['cssclass']);
    }

    /**
     * Saving an attribute or the question through the model clears the kept attributes of the question.
     */
    public function testSavingClearsRequestCache()
    {
        \Yii::app()->setController(new \SurveyController('survey'));
        $qid = self::$question->qid;

        $this->assertSame('before', \QuestionAttribute::model()->getQuestionAttributes($qid)['cssclass']);
        \QuestionAttribute::model()->setQuestionAttribute($qid, 'cssclass', 'saved');
        $this->assertSame('saved', \QuestionAttribute::model()->getQuestionAttributes($qid)['cssclass']);

        $this->updateCssClassInDatabase('question saved');
        $question = \Question::model()->findByPk($qid);
        $this->assertTrue($question->save());
        $this->assertSame('question saved', \QuestionAttribute::model()->getQuestionAttributes($qid)['cssclass']);
    }

    /**
     * Outside the survey runtime the attributes are always read from the database.
     */
    public function testAdminAlwaysReadsDatabase()
    {
        \Yii::app()->setController(new \SurveyAdministrationController('surveyAdministration'));
        $qid = self::$question->qid;

        $this->assertSame('before', \QuestionAttribute::model()->getQuestionAttributes($qid)['cssclass']);
        $this->updateCssClassInDatabase('after');
        $this->assertSame('after', \QuestionAttribute::model()->getQuestionAttributes($qid)['cssclass']);
    }
}
