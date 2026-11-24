<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * 多选题超选后的「提交前拦截」。
 *
 * 场景：页面已加载时用户选了 N 项（N > max_answers），例如：
 *   - 用键盘/脚本绕过点击拦截
 *   - 页面加载时回填了旧答案
 *   - 同时打开了多个标签页
 * 这时点「下一题」必须被拦下并给出可见提示，
 * 而不是像原来那样一路走到问卷末尾才报错。
 *
 * @group em
 * @group num_answers
 * @group render
 */
class NumberOfAnswersSubmitGuardTest extends TestBaseClass
{
    private static $qid;
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

    private function renderQuestion(): string
    {
        \Yii::app()->loadHelper('qanda');
        \LoadQuestionTypes::load(\Question::QT_M_MULTIPLE_CHOICE);
        $renderer = new \RenderMultipleChoice(
            [self::$qid, self::$sgqa, 'MAXQ', 'Please select at most two answers', 'M', null, 'N', '0', '0', '1']
        );
        [$html] = $renderer->render();
        return $html;
    }

    public function testOverLimitQuestionIsMarkedForSubmitGuard(): void
    {
        $html = $this->renderQuestion();

        // 容器带上限还不够，提交时也要能识别"这一题超选了"，
        // 因此容器要能通过 data 属性与自己所属题目关联。
        $this->assertMatchesRegularExpression(
            '/<ul[^>]*data-max-answers="2"[^>]*>/',
            $html,
            '容器应带上限'
        );
    }

    public function testQuestionIdIsAvailableToTheGuard(): void
    {
        $html = $this->renderQuestion();

        $this->assertStringContainsString(
            'data-question-id="' . self::$qid . '"',
            $html,
            '容器应带题目 ID，提交拦截时才能定位到具体题目'
        );
    }
}
