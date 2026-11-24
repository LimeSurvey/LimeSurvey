<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * 多选题的「最多可选几个」必须随页面下发到浏览器端。
 *
 * 背景：现有实现只在提交时校验 min_answers / max_answers，
 * 用户在长问卷里勾过头后要走到页尾提交才知道错了。
 * 要让浏览器端能在勾选时拦截，就必须先让页面知道上限。
 *
 * 这些用例锁定「渲染出的多选题容器带有 data-max-answers / data-min-answers」这个契约。
 *
 * @group em
 * @group num_answers
 * @group render
 */
class NumberOfAnswersRenderTest extends TestBaseClass
{
    /** @var int 多选题 qid */
    private static $qid;

    /** @var string 多选题 sgqa 前缀（形如 123456X789X7002） */
    private static $sgqa;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_NumberOfAnswers.lss');

        $question = \Question::model()->findByAttributes(
            ['sid' => self::$testSurvey->sid, 'title' => 'MAXQ']
        );
        self::$qid = (int) $question->qid;
        self::$sgqa = self::$testSurvey->sid . 'X' . $question->gid . 'X' . $question->qid;
    }

    /**
     * 渲染多选题，返回 HTML。
     *
     * @return string
     */
    private function renderQuestion(): string
    {
        \Yii::app()->loadHelper('qanda');
        // 用官方入口加载题型渲染类（内部会 import QuestionBaseRenderer 等依赖）
        \LoadQuestionTypes::load(\Question::QT_M_MULTIPLE_CHOICE);
        $renderer = new \RenderMultipleChoice(
            [
                self::$qid,
                self::$sgqa,
                'MAXQ',
                'Please select at most two answers',
                'M',
                null,
                'N',
                '0',
                '0',
                '1',
            ]
        );
        [$html] = $renderer->render();
        return $html;
    }

    public function testRenderedQuestionExposesMaxAnswers(): void
    {
        $html = $this->renderQuestion();

        $this->assertStringContainsString(
            'data-max-answers="2"',
            $html,
            '多选题容器应带 data-max-answers="2"，浏览器端才能据此阻止超选'
        );
    }

    public function testRenderedQuestionExposesMinAnswers(): void
    {
        $html = $this->renderQuestion();

        $this->assertStringContainsString(
            'data-min-answers="1"',
            $html,
            '多选题容器应带 data-min-answers="1"'
        );
    }

    public function testLimitIsPutOnTheListWrappingAllOptions(): void
    {
        $html = $this->renderQuestion();

        // 上限必须挂在包住所有选项的那个 <ul> 上，浏览器端才能按容器统计已选数量
        $this->assertSame(
            1,
            preg_match('/<ul[^>]*data-max-answers="2"[^>]*>/', $html),
            '带上限的容器应是包住所有选项的 <ul>'
        );
        $this->assertMatchesRegularExpression(
            '/<ul[^>]*data-max-answers="2"[^>]*>[\s\S]*<\/ul>/',
            $html,
            '容器应当闭合，选项行渲染在其内部'
        );
    }
}
