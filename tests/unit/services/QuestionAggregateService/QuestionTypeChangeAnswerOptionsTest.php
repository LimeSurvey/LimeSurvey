<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use Answer;
use Condition;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\QuestionAggregateService\QuestionService;
use ls\tests\TestBaseClass;
use Question;

/**
 * Answer options on question type change.
 */
class QuestionTypeChangeAnswerOptionsTest extends TestBaseClass
{
    /** @var QuestionService */
    private $questionService;

    /**
     * Imports a survey with a condition on answer AO02 of dropdown G01Q03.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::importSurvey(self::$surveysFolder . '/survey_fixMovedQuestionConditionsNew.lss');
    }

    /**
     * Gets the question service.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->questionService = DI::getContainer()->get(QuestionService::class);
    }

    /**
     * Only types with answer options have a layout, and only matching layouts keep them.
     *
     * @return void
     */
    public function testAnswerOptionLayouts()
    {
        $layouts = $this->questionService->getAnswerOptionLayouts();

        $this->assertSame($layouts[Question::QT_L_LIST], $layouts[Question::QT_EXCLAMATION_LIST_DROPDOWN]);
        $this->assertSame($layouts[Question::QT_F_ARRAY], $layouts[Question::QT_H_ARRAY_COLUMN]);
        $this->assertNotSame($layouts[Question::QT_L_LIST], $layouts[Question::QT_F_ARRAY]);
        $this->assertArrayNotHasKey(Question::QT_S_SHORT_FREE_TEXT, $layouts);
    }

    /**
     * A compatible type keeps the answer options and conditions.
     *
     * @return void
     */
    public function testChangeToCompatibleTypeKeepsAnswerOptions()
    {
        $question = $this->getQuestionByTitle('G01Q03');
        $answerCount = $this->countAnswers($question->qid);
        $conditionCount = $this->countConditionsOn($question->qid);
        $this->assertGreaterThan(0, $answerCount);
        $this->assertGreaterThan(0, $conditionCount);

        $this->changeType($question, Question::QT_L_LIST, 'listradio');

        $this->assertSame(Question::QT_L_LIST, $this->getQuestionByTitle('G01Q03')->type);
        $this->assertSame($answerCount, $this->countAnswers($question->qid));
        $this->assertSame($conditionCount, $this->countConditionsOn($question->qid));
    }

    /**
     * A type without answer options deletes them and their conditions.
     *
     * @depends testChangeToCompatibleTypeKeepsAnswerOptions
     * @return void
     */
    public function testChangeToTypeWithoutAnswerOptionsDeletesThem()
    {
        $question = $this->getQuestionByTitle('G01Q03');

        $this->changeType($question, Question::QT_S_SHORT_FREE_TEXT, 'shortfreetext');

        $this->assertSame(Question::QT_S_SHORT_FREE_TEXT, $this->getQuestionByTitle('G01Q03')->type);
        $this->assertSame(0, $this->countAnswers($question->qid));
        $this->assertSame(0, $this->countConditionsOn($question->qid));
    }

    /**
     * Saves a new question type.
     *
     * @param Question $question Question.
     * @param string $type Type code.
     * @param string $themeName Theme name.
     * @return void
     */
    private function changeType(Question $question, string $type, string $themeName)
    {
        $this->questionService->save([
            'sid' => self::$surveyId,
            'question' => [
                'qid' => $question->qid,
                'sid' => self::$surveyId,
                'type' => $type,
                'question_theme_name' => $themeName,
            ],
        ]);
    }

    /**
     * Finds a question by code.
     *
     * @param string $title Question code.
     * @return Question Question.
     */
    private function getQuestionByTitle(string $title): Question
    {
        $question = Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => $title]);
        $this->assertNotNull($question);
        return $question;
    }

    /**
     * Counts a question's answer options.
     *
     * @param int $qid Question ID.
     * @return int Number of answer options.
     */
    private function countAnswers(int $qid): int
    {
        return (int) Answer::model()->countByAttributes(['qid' => $qid]);
    }

    /**
     * Counts the conditions on a question.
     *
     * @param int $qid Question ID.
     * @return int Number of conditions.
     */
    private function countConditionsOn(int $qid): int
    {
        return (int) Condition::model()->countByAttributes(['cqid' => $qid]);
    }
}
