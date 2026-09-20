<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;

/**
 * The checkintegrity console command is the CLI, no-confirmation-needed equivalent of
 * the "Check data integrity" admin page: it must apply the same automatic fixes
 * (CheckIntegrity::applyAutomaticFixes()) unattended, and report what it did.
 */
class CheckIntegrityCommandTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_143933.lss';
        self::importSurvey($surveyFile);
    }

    public function setUp(): void
    {
        parent::setUp();
        \Yii::import('application.commands.CheckIntegrityCommand', true);
        \Yii::app()->session['loginID'] = 1;
    }

    public function testRunDeletesOrphanQuestionUnattendedAndReportsSuccess()
    {
        $group = self::$testSurvey->groups[0];

        $question = new \Question();
        $question->sid = self::$surveyId;
        $question->gid = $group->gid;
        $question->parent_qid = 0;
        $question->type = \QuestionType::QT_L_LIST;
        $question->title = 'CLICOMMAND';
        $question->question_order = 1;
        $this->assertTrue($question->save(), 'Could not save question fixture: ' . json_encode($question->errors));

        $qid = $question->qid;

        // Orphan the question directly at the DB level (no parent survey), same as a
        // real orphan would look like; the command must find and fix this on its own,
        // with nobody available to click a confirmation button.
        \Yii::app()->db->createCommand()->update(
            '{{questions}}',
            array('sid' => 999999904),
            'qid = :qid',
            array(':qid' => $qid)
        );

        $command = new \CheckIntegrityCommand('checkintegrity', null);

        ob_start();
        $exitCode = $command->run(array());
        $output = ob_get_clean();

        $this->assertSame(0, $exitCode, 'Command should exit cleanly. Output was: ' . $output);
        $this->assertStringContainsString('Deleted question ' . $qid, $output, 'Output should log why the question was deleted.');
        $this->assertNull(\Question::model()->findByPk($qid), 'Orphan question was not deleted by the console command.');
    }

    public function testRunReportsCleanStateWithoutAnyOrphans()
    {
        $command = new \CheckIntegrityCommand('checkintegrity', null);

        ob_start();
        $exitCode = $command->run(array());
        $output = ob_get_clean();

        $this->assertSame(0, $exitCode, 'Command should exit cleanly. Output was: ' . $output);
        $this->assertStringContainsString('No database action required.', $output);
    }
}
