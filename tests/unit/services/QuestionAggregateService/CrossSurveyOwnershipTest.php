<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use Answer;
use LimeSurvey\DI;
use LimeSurvey\Api\Command\V1\SurveyPatch\{
    OpHandlerAnswerDelete,
    OpHandlerQuestionL10nUpdate
};
use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\QuestionAggregateService\{
    AnswersService,
    L10nService
};
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;
use Question;
use QuestionL10n;
use Survey;

/**
 * Question and answer IDs from another survey must never be accepted when
 * permission was checked against the current (context) survey.
 *
 * @group services
 */
class CrossSurveyOwnershipTest extends TestBaseClass
{
    /** @var int Survey the operations are authorized against */
    private static $ownSurveyId;

    /** @var int Survey whose objects must stay untouched */
    private static $foreignSurveyId;

    /**
     * Imports the same survey twice: once as "own" and once as "foreign" survey.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_188432_answerSetsForEmTest.lss';
        self::importSurvey($surveyFile);
        self::$foreignSurveyId = self::$surveyId;
        self::importSurvey($surveyFile);
        self::$ownSurveyId = self::$surveyId;
    }

    /**
     * Deletes the foreign survey; the own survey is deleted by the parent.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        \Yii::app()->session['loginID'] = 1;
        $foreignSurvey = Survey::model()->findByPk(self::$foreignSurveyId);
        if ($foreignSurvey) {
            $foreignSurvey->delete();
        }
        parent::tearDownAfterClass();
    }

    /**
     * @testdox questionL10n update rejects a question of another survey
     * @return void
     */
    public function testQuestionL10nUpdateRejectsForeignQuestion()
    {
        $foreignQuestion = $this->getFirstQuestionWithAnswers(self::$foreignSurveyId);
        $originalText = $this->getQuestionText($foreignQuestion->qid);

        $op = OpStandard::factory(
            'questionL10n',
            'update',
            $foreignQuestion->qid,
            ['en' => ['question' => 'Changed <b>&"', 'help' => 'Changed']],
            ['id' => self::$ownSurveyId]
        );
        $handler = DI::getContainer()->get(OpHandlerQuestionL10nUpdate::class);

        try {
            $handler->handle($op);
            $this->fail('Expected NotFoundException for a question of another survey');
        } catch (NotFoundException $e) {
            // expected
        }
        $this->assertSame($originalText, $this->getQuestionText($foreignQuestion->qid));
    }

    /**
     * @testdox L10nService ignores a qid inside the data blocks
     * @return void
     */
    public function testL10nServiceIgnoresNestedQid()
    {
        $ownQuestion = $this->getFirstQuestionWithAnswers(self::$ownSurveyId);
        $foreignQuestion = $this->getFirstQuestionWithAnswers(self::$foreignSurveyId);
        $originalForeignText = $this->getQuestionText($foreignQuestion->qid);

        DI::getContainer()->get(L10nService::class)->save(
            $ownQuestion->qid,
            ['en' => ['qid' => $foreignQuestion->qid, 'question' => 'Own text <b>&"']]
        );

        $this->assertSame($originalForeignText, $this->getQuestionText($foreignQuestion->qid));
        $this->assertSame('Own text <b>&"', $this->getQuestionText($ownQuestion->qid));
    }

    /**
     * @testdox answer delete rejects an answer of another survey
     * @return void
     */
    public function testAnswerDeleteRejectsForeignAnswer()
    {
        $foreignQuestion = $this->getFirstQuestionWithAnswers(self::$foreignSurveyId);
        $foreignAnswer = $foreignQuestion->answers[0];

        $op = OpStandard::factory(
            'answer',
            'delete',
            $foreignAnswer->aid,
            [],
            ['id' => self::$ownSurveyId]
        );
        $handler = DI::getContainer()->get(OpHandlerAnswerDelete::class);

        try {
            $handler->handle($op);
            $this->fail('Expected NotFoundException for an answer of another survey');
        } catch (NotFoundException $e) {
            // expected
        }
        $this->assertNotNull(Answer::model()->findByPk($foreignAnswer->aid));
    }

    /**
     * @testdox saving answer options does not take over an answer of another question
     * @return void
     */
    public function testAnswersServiceDoesNotReuseForeignAnswer()
    {
        $ownQuestion = $this->getFirstQuestionWithAnswers(self::$ownSurveyId);
        $foreignQuestion = $this->getFirstQuestionWithAnswers(self::$foreignSurveyId);
        $foreignAnswer = $foreignQuestion->answers[0];

        DI::getContainer()->get(AnswersService::class)->save(
            $ownQuestion,
            [
                $foreignAnswer->aid => [
                    0 => [
                        'code' => 'X1',
                        'answeroptionl10n' => ['en' => 'Own answer']
                    ]
                ]
            ]
        );

        $reloadedForeignAnswer = Answer::model()->findByPk($foreignAnswer->aid);
        $this->assertNotNull($reloadedForeignAnswer);
        $this->assertSame((int)$foreignQuestion->qid, (int)$reloadedForeignAnswer->qid);
        $this->assertSame($foreignAnswer->code, $reloadedForeignAnswer->code);
    }

    /**
     * Returns the first question of a survey that has answer options.
     *
     * @param int $surveyId
     * @return Question
     */
    private function getFirstQuestionWithAnswers(int $surveyId): Question
    {
        $answer = Answer::model()->with('question')->find(
            'question.sid = :sid',
            [':sid' => $surveyId]
        );
        $this->assertNotNull($answer, 'Test survey has no answer options');
        return Question::model()->findByPk($answer->qid);
    }

    /**
     * Returns the English question text of a question.
     *
     * @param int $questionId
     * @return string|null
     */
    private function getQuestionText(int $questionId): ?string
    {
        $l10n = QuestionL10n::model()->findByAttributes([
            'qid' => $questionId,
            'language' => 'en'
        ]);
        return $l10n ? $l10n->question : null;
    }
}
