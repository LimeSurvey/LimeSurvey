<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * 带评论的多选题（P 类型）也要有「最多可选 N 项」的前端拦截。
 *
 * P 类型走的是另一个渲染器（RenderMultipleChoiceWithComments），
 * M 类型加过的 data 属性不会自动出现在这里，必须单独保证。
 * 两种题型对用户是同一个语义，行为不能不一致。
 *
 * @group em
 * @group num_answers
 * @group render
 */
class NumberOfAnswersWithCommentsTest extends TestBaseClass
{
    private static $qid;
    private static $sgqa;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_NumberOfAnswers.lss');

        // 把夹具里的 M 题改成 P 题，复用同一份测试数据
        $question = \Question::model()->findByAttributes(
            ['sid' => self::$testSurvey->sid, 'title' => 'MAXQ']
        );
        $question->type = \Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS;
        $question->save();

        self::$qid = (int) $question->qid;
        self::$sgqa = self::$testSurvey->sid . 'X' . $question->gid . 'X' . $question->qid;
    }

    private function renderQuestion(): string
    {
        \Yii::app()->loadHelper('qanda');
        \LoadQuestionTypes::load(\Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS);
        $renderer = new \RenderMultipleChoiceWithComments(
            [
                self::$qid,
                self::$sgqa,
                'MAXQ',
                'Please select at most two answers',
                'P',
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

    public function testCommentedQuestionExposesMaxAnswers(): void
    {
        $html = $this->renderQuestion();

        $this->assertStringContainsString(
            'data-max-answers="2"',
            $html,
            'P 类型多选题也要输出 data-max-answers，行为必须与 M 类型一致'
        );
    }

    public function testCommentedQuestionExposesQuestionId(): void
    {
        $html = $this->renderQuestion();

        $this->assertStringContainsString(
            'data-question-id="' . self::$qid . '"',
            $html,
            'P 类型容器也要带题目 ID，超限提示才能定位'
        );
    }

    public function testCommentedQuestionRegistersLimitScript(): void
    {
        // 渲染后再取当前页面已注册的脚本，确认拦截脚本确实被挂上
        $this->renderQuestion();

        $clientScript = \Yii::app()->getClientScript();
        $reflection = new \ReflectionObject($clientScript);
        $property = $reflection->hasProperty('scripts')
            ? $reflection->getProperty('scripts')
            : null;

        $this->assertNotNull($property, '应能读取 LSYii_ClientScript 的 scripts 属性');
        $property->setAccessible(true);
        $scripts = json_encode($property->getValue($clientScript));

        $this->assertStringContainsString(
            'LSMultiAnswerLimit',
            $scripts,
            'P 类型应同样注册前端拦截脚本'
        );
    }
}
