<?php

namespace ls\tests;

use Question;
use QuestionGroup;
use Survey;

/**
 * Tests that deleting a question group also deletes the texts of its questions, subquestions,
 * answers and default values.
 */
class QuestionGroupDeleteTextsTest extends TestBaseClass
{
    /**
     * Import a fresh copy of the test survey, so the deleted group doesn't affect other tests.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_594264_getGroupDescription.lss');
    }

    /**
     * Delete the survey imported in setUp().
     *
     * @return void
     */
    public function tearDown(): void
    {
        \Yii::app()->session['loginID'] = 1;
        Survey::model()->deleteSurvey(self::$surveyId);
        self::$testSurvey = null;
        parent::tearDown();
    }

    /**
     * No question_l10ns, answer_l10ns or defaultvalue_l10ns rows of the deleted group's questions are left behind.
     *
     * @return void
     */
    public function testDeleteWithDependencyDeletesTexts(): void
    {
        $db = \Yii::app()->db;
        $question = Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0]);
        $this->assertNotEmpty($question, 'The test survey has no question.');

        $db->createCommand()->insert('{{questions}}', [
            'parent_qid' => $question->qid,
            'sid' => self::$surveyId,
            'gid' => $question->gid,
            'title' => 'SQ001',
            'question_order' => 1,
        ]);
        $subquestionId = (int) $db->getLastInsertID();
        $db->createCommand()->insert('{{question_l10ns}}', ['qid' => $subquestionId, 'question' => 'Subquestion', 'language' => 'en']);
        $db->createCommand()->insert('{{answers}}', ['qid' => $question->qid, 'code' => 'A1', 'sortorder' => 1]);
        $answerId = (int) $db->getLastInsertID();
        $db->createCommand()->insert('{{answer_l10ns}}', ['aid' => $answerId, 'answer' => 'Answer', 'language' => 'en']);
        $db->createCommand()->insert('{{defaultvalues}}', ['qid' => $question->qid]);
        $defaultValueId = (int) $db->getLastInsertID();
        $db->createCommand()->insert('{{defaultvalue_l10ns}}', ['dvid' => $defaultValueId, 'defaultvalue' => 'Default', 'language' => 'en']);

        $this->assertSame(1, QuestionGroup::deleteWithDependency($question->gid));

        $this->assertSame(0, $this->countRows('{{question_l10ns}}', 'qid', [$question->qid, $subquestionId]));
        $this->assertSame(0, $this->countRows('{{answer_l10ns}}', 'aid', [$answerId]));
        $this->assertSame(0, $this->countRows('{{defaultvalue_l10ns}}', 'dvid', [$defaultValueId]));
    }

    /**
     * Count the rows of a table with one of the given ids.
     *
     * @param string $table Table name
     * @param string $column Id column
     * @param int[] $ids Ids
     * @return int
     */
    private function countRows(string $table, string $column, array $ids): int
    {
        return (int) \Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from($table)
            ->where(['in', $column, $ids])
            ->queryScalar();
    }
}
