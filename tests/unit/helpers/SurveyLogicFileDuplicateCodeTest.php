<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;
use LimeExpressionManager;
use Question;
use QuestionGroup;
use Yii;

/**
 * Tests that the survey logic file reports question codes used more than once.
 */
class SurveyLogicFileDuplicateCodeTest extends TestBaseClass
{
    /** @var string[] Question codes used a second time in the last group */
    private static $duplicatedCodes = ['FixedQ03', 'R2Q03', 'R2Q04'];

    /**
     * Imports the survey and restores the duplicated question codes the import renamed.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Yii::import('application.helpers.common_helper', true);
        Yii::import('application.helpers.expressions.em_manager_helper', true);
        Yii::app()->setController(new DummyController('dummyid'));

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_17200_duplicate_codes.lss');

        // Import renames duplicated codes, so restore them directly in the database like in legacy surveys
        $lastGroup = QuestionGroup::model()->find([
            'condition' => 'sid = :sid',
            'params'    => [':sid' => self::$surveyId],
            'order'     => 'group_order DESC',
        ]);
        $lastGroupQuestions = Question::model()->findAll([
            'condition' => 'parent_qid = 0 AND gid = :gid',
            'params'    => [':gid' => $lastGroup->gid],
            'order'     => 'question_order',
        ]);
        foreach ($lastGroupQuestions as $index => $question) {
            Question::model()->updateByPk($question->qid, ['title' => self::$duplicatedCodes[$index]]);
        }
    }

    /**
     * Each duplicated question code must be counted as an error in the logic file.
     */
    public function testDuplicateQuestionCodesAreReportedAsErrors()
    {
        SetSurveyLanguage(self::$surveyId, '');
        killSurveySession(self::$surveyId);

        $result = LimeExpressionManager::ShowSurveyLogicFile(
            self::$surveyId,
            null,
            null,
            LEM_DEBUG_VALIDATION_SUMMARY + LEM_DEBUG_VALIDATION_DETAIL + LEM_PRETTY_PRINT_ALL_SYNTAX
        );

        $this->assertCount(3, $result['errors']);
        $this->assertStringNotContainsString('No syntax errors detected in this survey.', $result['html']);
        $this->assertStringContainsString('3 questions contain errors that need to be corrected.', $result['html']);
        foreach (self::$duplicatedCodes as $code) {
            $this->assertMatchesRegularExpression(
                "/<td class='danger'>Q-\d+<\/td><td><b><span class='highlighterror' title='This variable name has already been used.' [^>]*>{$code}<\/span>/",
                $result['html']
            );
        }
    }
}
