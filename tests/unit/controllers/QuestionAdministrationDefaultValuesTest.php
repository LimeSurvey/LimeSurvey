<?php

namespace ls\tests\controllers;

use LimeSurvey\DI;
use LimeSurvey\Models\Services\QuestionAggregateService\DefaultValuesService;
use ls\tests\TestBaseClass;
use Question;
use QuestionAdministrationController;

/**
 * Tests QuestionAdministrationController::getDefaultValues(), which feeds the
 * "Default answers" tab of the question editor.
 */
class QuestionAdministrationDefaultValuesTest extends TestBaseClass
{
    /**
     * Imports a survey with List (radio) questions.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.controllers.QuestionAdministrationController', true);
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_767665_ListRadioOtherPositionTest.lss');
    }

    /**
     * @testdox getDefaultValues() returns stored defaults under the question type selected in the editor
     * @return void
     */
    public function testGetDefaultValuesUsesSelectedQuestionType()
    {
        $question = Question::model()->findByAttributes([
            'sid' => self::$surveyId,
            'type' => Question::QT_L_LIST,
            'parent_qid' => 0,
        ]);
        $this->assertNotNull($question);
        DI::getContainer()->get(DefaultValuesService::class)
            ->save($question, ['defaultvalues' => ['en' => ['A2']]]);

        $stored = QuestionAdministrationController::getDefaultValues(self::$surveyId, $question->gid, $question->qid);
        $this->assertSame('A2', $stored['en'][Question::QT_L_LIST][0]['defaultvalue']);

        // List (radio) switched to List (dropdown) in the editor, not saved yet
        $dropdown = QuestionAdministrationController::getDefaultValues(
            self::$surveyId,
            $question->gid,
            $question->qid,
            Question::QT_EXCLAMATION_LIST_DROPDOWN
        );
        $this->assertArrayNotHasKey(Question::QT_L_LIST, $dropdown['en']);
        $this->assertSame('A2', $dropdown['en'][Question::QT_EXCLAMATION_LIST_DROPDOWN][0]['defaultvalue']);

        // Switched to a type without answer options, e.g. Short text
        $shortText = QuestionAdministrationController::getDefaultValues(
            self::$surveyId,
            $question->gid,
            $question->qid,
            Question::QT_S_SHORT_FREE_TEXT
        );
        $this->assertSame('A2', $shortText['en'][Question::QT_S_SHORT_FREE_TEXT][0]);
    }
}
