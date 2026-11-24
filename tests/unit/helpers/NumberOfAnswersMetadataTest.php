<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * 多选题 min_answers / max_answers 的校验元数据是否随题目下发到前端。
 *
 * 背景：浏览器端在用户勾选选项时需要知道"最多能选几个"，
 * 才能在超限时阻止继续勾选，而不是等到提交才报错。
 * 这些用例锁定「元数据必须随页面下发」这个契约。
 *
 * @group em
 * @group num_answers
 */
class NumberOfAnswersMetadataTest extends TestBaseClass
{
    /** @var int 多选题 qid */
    private static $qid;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_NumberOfAnswers.lss');

        $question = \Question::model()->findByAttributes(
            ['sid' => self::$testSurvey->sid, 'title' => 'MAXQ']
        );
        self::$qid = (int) $question->qid;
    }

    public function testQuestionHasMaxAnswersAttribute(): void
    {
        $attributes = \QuestionAttribute::model()->getQuestionAttributes(self::$qid);

        $this->assertSame('2', (string) $attributes['max_answers'], '夹具题目的 max_answers 应为 2');
    }

    public function testQuestionHasMinAnswersAttribute(): void
    {
        $attributes = \QuestionAttribute::model()->getQuestionAttributes(self::$qid);

        $this->assertSame('1', (string) $attributes['min_answers'], '夹具题目的 min_answers 应为 1');
    }
}
