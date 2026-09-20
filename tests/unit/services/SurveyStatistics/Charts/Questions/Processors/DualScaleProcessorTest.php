<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\DualScaleProcessor;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use PHPUnit\Framework\TestCase;
use Question;

class DualScaleProcessorTest extends TestCase
{
    use ResponseAggregateBatchTestHelper;

    /**
     * @testdox process() builds one segment set per scale, with a NoAnswer bucket, and a deduplicated legend
     */
    public function testProcessBuildsSegmentsPerScale()
    {
        $processor = new DualScaleProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 4,
            'type' => Question::QT_1_ARRAY_DUAL,
            'question' => 'Rate both scales',
            'attributes' => [
                'dualscale_headerA' => 'Header A',
                'dualscale_headerB' => 'Header B',
            ],
            'subQuestions' => [
                ['qid' => 1, 'title' => 'SQ1', 'question' => 'Sub 1'],
            ],
        ]);
        $processor->setAnswers([
            ['scale_id' => 0, 'code' => '1', 'answer' => 'Low'],
            ['scale_id' => 0, 'code' => '2', 'answer' => 'High'],
            ['scale_id' => 1, 'code' => 'A', 'answer' => 'AgreeA'],
        ]);
        $batch = new ResponseAggregateBatch(1);
        $processor->setBatch($batch);

        $plan = $processor->process();

        $this->assertSame(['Low', 'High', 'AgreeA', 'No answer'], $plan['legend']);
        $this->assertCount(2, $plan['data']);

        $scale0 = $plan['data'][0];
        $this->assertSame('SQ1#0', $scale0['key']);
        $this->assertSame('Sub 1', $scale0['title']);
        $this->assertSame(flattenText('Header A', false, true), $scale0['scaleTitle']);
        $this->assertSame(['1', '2', 'NoAnswer'], array_column($scale0['segments'], 'key'));

        $scale1 = $plan['data'][1];
        $this->assertSame('SQ1#1', $scale1['key']);
        $this->assertSame(flattenText('Header B', false, true), $scale1['scaleTitle']);
        $this->assertSame(['A', 'NoAnswer'], array_column($scale1['segments'], 'key'));

        $field0 = 'Q4_S1#0';
        $field1 = 'Q4_S1#1';
        $this->injectBatchResults($batch, [
            $batch->countValue($field0, '1') => 2,
            $batch->countValue($field0, '2') => 5,
            $batch->countBlank($field0) => 1,
            $batch->countValue($field1, 'A') => 3,
            $batch->countBlank($field1) => 4,
        ]);

        $this->assertSame([2, 5, 1], $this->resolveAll(array_column($scale0['segments'], 'value')));
        $this->assertSame([3, 4], $this->resolveAll(array_column($scale1['segments'], 'value')));
    }
}
