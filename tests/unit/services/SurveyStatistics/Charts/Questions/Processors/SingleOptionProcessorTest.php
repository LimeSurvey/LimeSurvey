<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\SingleOptionProcessor;
use PHPUnit\Framework\TestCase;
use Question;

class SingleOptionProcessorTest extends TestCase
{
    use ResponseAggregateBatchTestHelper;

    private function makeProcessor(string $type, string $other = 'N'): SingleOptionProcessor
    {
        $processor = new SingleOptionProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 2,
            'title' => 'Q2',
            'type' => $type,
            'question' => 'A question',
            'other' => $other,
        ]);
        $processor->setBatch(new ResponseAggregateBatch(1));

        return $processor;
    }

    /**
     * @testdox process() builds Female/Male/NoAnswer items for gender questions
     */
    public function testHandleGender()
    {
        $plan = $this->makeProcessor(Question::QT_G_GENDER)->process();

        $this->assertSame(['Female', 'Male', 'NoAnswer'], $plan['legend']);
        $this->assertSame(['F', 'M', 'NoAnswer'], array_column($plan['data'], 'key'));
    }

    /**
     * @testdox process() builds Yes/No/NoAnswer items for yes-no questions
     */
    public function testHandleYesNo()
    {
        $plan = $this->makeProcessor(Question::QT_Y_YES_NO_RADIO)->process();

        $this->assertSame(['Yes', 'No', 'NoAnswer'], $plan['legend']);
        $this->assertSame(['Y', 'N', 'NoAnswer'], array_column($plan['data'], 'key'));
    }

    /**
     * @testdox process() builds one item per point (1-5) plus NoAnswer for 5-point-choice questions
     */
    public function testHandle5PointChoice()
    {
        $plan = $this->makeProcessor(Question::QT_5_POINT_CHOICE)->process();

        $this->assertSame(['1', '2', '3', '4', '5', 'NoAnswer'], $plan['legend']);
        $this->assertSame(['1', '2', '3', '4', '5', 'NoAnswer'], array_column($plan['data'], 'key'));
    }

    /**
     * @testdox process() builds items from the question's own answer list for list questions, plus an "other" item when enabled
     */
    public function testHandleDefaultWithOtherOption()
    {
        $processor = $this->makeProcessor(Question::QT_L_LIST, Question::QT_Y_YES_NO_RADIO);
        $processor->setAnswers([
            ['code' => 'A1', 'answer' => 'Answer 1'],
            ['code' => 'A2', 'answer' => 'Answer 2'],
        ]);

        $plan = $processor->process();

        // list questions are in the "always has a NoAnswer bucket" set, plus
        // the explicit "other" item this test enables.
        $this->assertSame(['Answer 1', 'Answer 2', 'NoAnswer', 'other'], $plan['legend']);
        $this->assertSame(['A1', 'A2', 'NoAnswer', 'other'], array_column($plan['data'], 'key'));
        // gT('Other') rather than the literal string: the active language is
        // shared mutable state across the whole PHPUnit process, so another
        // test can leave it on something other than English.
        $this->assertSame(gT('Other'), $plan['data'][3]['title']);
    }

    /**
     * @testdox process() omits the "other" item for list questions where it is disabled
     */
    public function testHandleDefaultWithoutOtherOption()
    {
        $processor = $this->makeProcessor(Question::QT_L_LIST, 'N');
        $processor->setAnswers([
            ['code' => 'A1', 'answer' => 'Answer 1'],
        ]);

        $plan = $processor->process();

        $this->assertSame(['Answer 1', 'NoAnswer'], $plan['legend']);
    }
}
