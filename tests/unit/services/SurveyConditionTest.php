<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyCondition;

/**
 * Tests for the SurveyCondition service.
 */
class SurveyConditionTest extends TestBaseClass
{
    /**
     * Import a survey with a multiple choice question that has subquestions.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $filename = self::$surveysFolder . '/limesurvey_survey_425647_subquestion_relevance_edit.lss';
        self::importSurvey($filename);
    }

    /**
     * The question labels in the condition designer don't contain the code of script blocks in question or subquestion texts.
     *
     * @return void
     */
    public function testQuestionLabelsDoNotContainScriptCode()
    {
        $questions = $this->getAllSurveyQuestions();
        $question = $questions['Q01'];
        $subquestions = \Question::model()->findAllByAttributes(['parent_qid' => $question->qid]);
        $this->assertNotEmpty($subquestions);

        \QuestionL10n::model()->updateAll(
            ['question' => "<p>Visible question text</p>\n<script type=\"text/javascript\">var conditionsLabelMarker = 1;</script>"],
            'qid = :qid',
            [':qid' => $question->qid]
        );
        \QuestionL10n::model()->updateAll(
            ['question' => 'Visible subquestion text<script>var conditionsLabelMarker = 2;</script>'],
            'qid = :qid',
            [':qid' => $subquestions[0]->qid]
        );

        $surveyCondition = \LimeSurvey\DI::getContainer()->get(SurveyCondition::class);
        $surveyCondition->setISurveyID(self::$surveyId);
        $language = new \ReflectionProperty($surveyCondition, 'language');
        $language->setAccessible(true);
        $language->setValue($surveyCondition, self::$testSurvey->language);

        $getTheseRows = new \ReflectionMethod($surveyCondition, 'getTheseRows');
        $getTheseRows->setAccessible(true);
        $theseRows = $getTheseRows->invoke($surveyCondition, [$question->qid]);

        $getCAnswersAndCQuestions = new \ReflectionMethod($surveyCondition, 'getCAnswersAndCQuestions');
        $getCAnswersAndCQuestions->setAccessible(true);
        list($cquestions) = $getCAnswersAndCQuestions->invoke($surveyCondition, $theseRows);

        $this->assertNotEmpty($cquestions);
        $labels = implode("\n", array_column($cquestions, 0));
        $this->assertStringContainsString('Visible question text', $labels);
        $this->assertStringContainsString('Visible subquestion text', $labels);
        $this->assertStringNotContainsString('conditionsLabelMarker', $labels);
    }
}
