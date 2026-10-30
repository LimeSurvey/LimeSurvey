<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use LimeSurvey\DI;
use LimeSurvey\Models\Services\QuestionAggregateService;
use ls\tests\TestBaseClass;
use Question;

/**
 * A new question can be put at a wanted position inside its group.
 *
 * @group services
 */
class NewQuestionPositionTest extends TestBaseClass
{
    /** @var int Group used for the new questions */
    private static $gid;

    /**
     * Imports a survey and picks the group with the most questions.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::app()->session['loginID'] = 1;
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_rankingFilterHideShow.lss');
        self::$gid = (int) \Yii::app()->db->createCommand()
            ->select('gid')
            ->from('{{questions}}')
            ->where('sid = :sid AND parent_qid = 0', [':sid' => self::$surveyId])
            ->group('gid')
            ->order('COUNT(*) DESC')
            ->limit(1)
            ->queryScalar();
    }

    /**
     * Uses a new DI container: Other tests replace models like Question or QuestionL10n by mocks in the shared one.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $container = new \ReflectionProperty(DI::class, 'container');
        $container->setAccessible(true);
        $container->setValue(null, null);
    }

    /**
     * Wanted positions: Empty for at the end, 0 for at the beginning, else after the question at index - 1
     *
     * @return array
     */
    public function positionProvider()
    {
        return [
            'at end' => ['', 'end'],
            'at beginning' => ['0', 0],
            'after first question' => ['after:0', 1],
            'out of range' => ['999', 'end'],
        ];
    }

    /**
     * @testdox A new question is saved at the wanted position, the other questions keep their order
     * @dataProvider positionProvider
     *
     * @param string $position Value of the position selector, "after:<index>" for "After" the question at this index
     * @param int|string $expectedIndex Expected index of the new question in the group, "end" for the last one
     * @return void
     */
    public function testNewQuestionAtPosition($position, $expectedIndex)
    {
        $before = $this->getGroupQuestionCodes();
        $this->assertGreaterThanOrEqual(2, count($before));
        $subquestionOrdersBefore = $this->getSubquestionOrders();

        if (strpos($position, 'after:') === 0) {
            $previous = Question::model()->findByAttributes([
                'sid' => self::$surveyId,
                'title' => $before[(int) substr($position, 6)],
                'parent_qid' => 0,
            ]);
            // Same value as the position selector
            $position = (string) ($previous->question_order + 1);
        }

        $code = 'NEWPOS' . count($before);
        $questionAggregateService = DI::getContainer()->get(QuestionAggregateService::class);
        $questionAggregateService->save(self::$surveyId, [
            'question' => [
                'gid' => self::$gid,
                'type' => Question::QT_T_LONG_FREE_TEXT,
                'title' => $code,
            ],
            'questionposition' => $position,
        ]);

        $after = $this->getGroupQuestionCodes();
        $expected = $before;
        array_splice($expected, $expectedIndex === 'end' ? count($before) : $expectedIndex, 0, [$code]);
        $this->assertSame($expected, $after);
        $this->assertSame($subquestionOrdersBefore, $this->getSubquestionOrders());
    }

    /**
     * Returns the codes of the questions of the group, sorted like in the survey
     *
     * @return string[]
     */
    private function getGroupQuestionCodes()
    {
        $questions = Question::model()->findAll([
            'condition' => 'gid = :gid AND parent_qid = 0',
            'params' => [':gid' => self::$gid],
            'order' => 'question_order ASC, title ASC',
        ]);
        return array_map(function ($question) {
            return $question->title;
        }, $questions);
    }

    /**
     * Returns the question_order of all subquestions of the survey by qid
     *
     * @return array
     */
    private function getSubquestionOrders()
    {
        $orders = [];
        foreach (Question::model()->findAll('sid = :sid AND parent_qid > 0', [':sid' => self::$surveyId]) as $subquestion) {
            $orders[$subquestion->qid] = (int) $subquestion->question_order;
        }
        ksort($orders);
        return $orders;
    }
}
