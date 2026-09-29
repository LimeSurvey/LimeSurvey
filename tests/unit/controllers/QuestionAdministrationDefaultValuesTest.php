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
     * Imports a survey in English and German with a List (radio) question and
     * a Multiple choice question with six subquestions.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.controllers.QuestionAdministrationController', true);
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_373616_copySurvey.lss');
    }

    /**
     * @testdox getDefaultValues() returns stored defaults under the question type selected in the editor
     * @return void
     */
    public function testGetDefaultValuesUsesSelectedQuestionType()
    {
        $question = $this->getQuestion(Question::QT_L_LIST);
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

    /**
     * @testdox getDefaultValues() returns subquestion defaults per language without a query per subquestion
     * @return void
     */
    public function testGetDefaultValuesForSubquestions()
    {
        $question = $this->getQuestion(Question::QT_M_MULTIPLE_CHOICE);
        DI::getContainer()->get(DefaultValuesService::class)->save($question, [
            'defaultvalues' => [
                'en' => ['SQ001' => ['Y']],
                'de' => ['SQ003' => ['Y']],
            ],
        ]);

        // Warm up the schema cache, so only the queries of getDefaultValues() itself are counted
        QuestionAdministrationController::getDefaultValues(self::$surveyId, $question->gid, $question->qid);
        $db = \Yii::app()->db;
        $enableProfiling = $db->enableProfiling;
        $db->enableProfiling = true;
        try {
            $queriesBefore = $this->countQueries();
            $defaultValues = QuestionAdministrationController::getDefaultValues(
                self::$surveyId,
                $question->gid,
                $question->qid
            );
            $queriesAfter = $this->countQueries();
        } finally {
            $db->enableProfiling = $enableProfiling;
        }

        foreach (['en' => 'SQ001', 'de' => 'SQ003'] as $language => $checkedTitle) {
            $rows = $defaultValues[$language][Question::QT_M_MULTIPLE_CHOICE][0]['sqresult'];
            $this->assertSame(
                ['SQ001', 'SQ002', 'SQ003', 'SQ004', 'SQ005', 'SQ006'],
                array_column($rows, 'title'),
                "Subquestions in $language, in question order"
            );
            $checked = array_column(array_filter($rows, function ($row) {
                return $row['defaultvalue'] === 'Y';
            }), 'title');
            $this->assertSame([$checkedTitle], $checked, "Checked subquestions in $language");
            $this->assertNotEmpty($rows[0]['question'], "Subquestion text in $language");
        }

        // 2 languages x 6 subquestions: the former implementation ran more than 14 queries here
        $this->assertLessThanOrEqual(5, $queriesAfter - $queriesBefore, 'Queries run by getDefaultValues()');
    }

    /**
     * Every question type supported by the "Default answers" tab.
     *
     * @return array<string, array{string}>
     */
    public static function supportedQuestionTypeProvider()
    {
        $types = [];
        foreach (DefaultValuesService::SUPPORTED_QUESTION_TYPES as $type) {
            $types["type $type"] = [$type];
        }
        return $types;
    }

    /**
     * @testdox The "Default answers" tab renders in the Twig sandbox for question type $type
     * @dataProvider supportedQuestionTypeProvider
     * @param string $type Question type
     * @return void
     */
    public function testDefaultAnswersTabRenders($type)
    {
        $questionType = \QuestionType::modelsAttributes()[$type];
        // Answer options and subquestions come from the stored question, the type is switched in memory only
        $question = $this->getQuestion(
            $questionType['subquestions'] > 0 ? Question::QT_M_MULTIPLE_CHOICE : Question::QT_L_LIST
        );
        $question->type = $type;
        $survey = $question->survey;

        // The Yes/No widget is created through the current controller, which a unit test does not have
        $controller = \Yii::app()->getController();
        if ($controller === null) {
            \Yii::app()->setController(new \CController('test'));
        }

        // Same as application/views/questionAdministration/extraOptions.php
        $twigRenderer = \Yii::app()->twigRenderer;
        $twigRenderer->getLoader()->addPath(\Yii::app()->getBasePath() . '/views/questionAdministration', '__main__');
        try {
            $html = $twigRenderer->renderViewFromFile(
                '/application/views/questionAdministration/defaultValues.twig',
                [
                    'subquestions' => $questionType['subquestions'],
                    'answerScales' => $questionType['answerscales'],
                    'answers' => $question->getScaledAnswerOptions(),
                    'question' => $question,
                    'allLanguages' => $survey->allLanguages,
                    'language' => $survey->language,
                    'defaultValues' => QuestionAdministrationController::getDefaultValues(
                        self::$surveyId,
                        $question->gid,
                        $question->qid,
                        $type
                    ),
                    'sameDefault' => (bool)$question->same_default,
                    'hasUpdatePermission' => true,
                ],
                true
            );
        } finally {
            \Yii::app()->setController($controller);
        }

        $this->assertStringContainsString('lang-de', $html);
        $this->assertStringContainsString("name='samedefault'", $html);
    }

    /**
     * Returns the number of SQL statements profiled so far.
     * Like CDbConnection::getStats(), but refreshes the logger's cached profiling results.
     *
     * @return int
     */
    private function countQueries()
    {
        $logger = \Yii::getLogger();
        return count($logger->getProfilingResults(null, 'system.db.CDbCommand.query', true))
            + count($logger->getProfilingResults(null, 'system.db.CDbCommand.execute', true));
    }

    /**
     * Returns the first question of the given type in the test survey.
     *
     * @param string $type Question type
     * @return Question
     */
    private function getQuestion($type)
    {
        $question = Question::model()->findByAttributes([
            'sid' => self::$surveyId,
            'type' => $type,
            'parent_qid' => 0,
        ]);
        $this->assertNotNull($question);
        return $question;
    }
}
