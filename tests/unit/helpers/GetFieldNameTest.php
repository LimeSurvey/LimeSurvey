<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use LimeSurvey\Helpers\Update\Update_700;

/**
 * Tests for getFieldName(), which maps an old SGQA field name to the new
 * Q{qid}_S{sqid} field name. Both the common_helper function and the copy
 * used by the database update to version 7 are checked.
 */
class GetFieldNameTest extends TestBaseClass
{
    /**
     * Builds an array question with the given subquestion codes.
     *
     * @param string $type
     * @param string[] $subquestionCodes subquestion qid => code
     * @return array
     */
    private function buildQuestions(string $type, array $subquestionCodes): array
    {
        $questions = [['qid' => 100, 'gid' => 10, 'parent_qid' => 0, 'type' => $type, 'title' => 'Q1']];
        foreach ($subquestionCodes as $sqid => $code) {
            $questions[] = ['qid' => $sqid, 'gid' => 10, 'parent_qid' => 100, 'type' => $type, 'title' => $code];
        }
        return $questions;
    }

    /**
     * Asserts that both implementations map the old field name to the expected new one.
     *
     * @param string $expected
     * @param string $oldFieldName
     * @param array $questions
     * @return void
     */
    private function assertFieldName(string $expected, string $oldFieldName, array $questions): void
    {
        $objects = array_map(function ($question) {
            return (object) $question;
        }, $questions);
        $this->assertSame(
            $expected,
            getFieldName('{{responses_1}}', $oldFieldName, $objects, 1, 10),
            "common_helper getFieldName() for {$oldFieldName}"
        );
        $update = new Update_700(\Yii::app()->db, '');
        $method = new \ReflectionMethod($update, 'getFieldName');
        $method->setAccessible(true);
        $this->assertSame(
            $expected,
            $method->invoke($update, 'lime_survey_1', $oldFieldName, $questions, 1, 10),
            "Update_700::getFieldName() for {$oldFieldName}"
        );
    }

    /**
     * A subquestion coded "other" in an array question is a regular subquestion,
     * not the "other" field of the question.
     *
     * @return void
     */
    public function testArraySubquestionCodedOther()
    {
        $questions = $this->buildQuestions(\Question::QT_F_ARRAY, [101 => 'SQ001', 102 => 'other']);
        $this->assertFieldName('Q100_S102', '1X10X100other', $questions);
        $this->assertFieldName('Q100_S101', '1X10X100SQ001', $questions);

        // Order of the subquestions must not matter
        $questions = $this->buildQuestions(\Question::QT_F_ARRAY, [102 => 'other', 101 => 'SQ001']);
        $this->assertFieldName('Q100_S102', '1X10X100other', $questions);
    }

    /**
     * Subquestions coded "comment" and "othercomment" are regular subquestions too.
     *
     * @return void
     */
    public function testArraySubquestionCodedComment()
    {
        $questions = $this->buildQuestions(\Question::QT_A_ARRAY_5_POINT, [101 => 'comment', 102 => 'othercomment']);
        $this->assertFieldName('Q100_S101', '1X10X100comment', $questions);
        $this->assertFieldName('Q100_S102', '1X10X100othercomment', $questions);
    }

    /**
     * The "other" and comment fields of multiple choice with comments are still mapped.
     *
     * @return void
     */
    public function testMultipleChoiceWithCommentsOtherAndComments()
    {
        $questions = $this->buildQuestions(\Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS, [101 => 'SQ001', 102 => 'SQ002']);
        $this->assertFieldName('Q100_S101', '1X10X100SQ001', $questions);
        $this->assertFieldName('Q100_S102_Ccomment', '1X10X100SQ002comment', $questions);
        $this->assertFieldName('Q100_Cother', '1X10X100other', $questions);
        $this->assertFieldName('Q100_Cothercomment', '1X10X100othercomment', $questions);
    }

    /**
     * In multiple choice with comments a subquestion coded "other" has its own
     * comment field "othercomment", which is not the comment of the question's "other" field.
     *
     * @return void
     */
    public function testMultipleChoiceWithCommentsSubquestionCodedOther()
    {
        foreach ([[101 => 'SQ001', 102 => 'other'], [102 => 'other', 101 => 'SQ001']] as $codes) {
            $questions = $this->buildQuestions(\Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS, $codes);
            $this->assertFieldName('Q100_S102', '1X10X100other', $questions);
            $this->assertFieldName('Q100_S102_Ccomment', '1X10X100othercomment', $questions);
            $this->assertFieldName('Q100_S101_Ccomment', '1X10X100SQ001comment', $questions);
        }
    }

    /**
     * A subquestion code that is another subquestion's code plus "other" or "comment"
     * is mapped to its own subquestion, whatever the order of the subquestions.
     *
     * @return void
     */
    public function testSubquestionCodeIsAnotherCodePlusSuffix()
    {
        $questions = $this->buildQuestions(\Question::QT_F_ARRAY, [101 => 'A', 102 => 'Aother', 103 => 'Acomment', 104 => 'Aothercomment']);
        $this->assertFieldName('Q100_S101', '1X10X100A', $questions);
        $this->assertFieldName('Q100_S102', '1X10X100Aother', $questions);
        $this->assertFieldName('Q100_S103', '1X10X100Acomment', $questions);
        $this->assertFieldName('Q100_S104', '1X10X100Aothercomment', $questions);

        // "Aothercomment" is the comment of "Aother", not an "othercomment" field of "A"
        foreach ([[101 => 'Aother', 102 => 'A'], [102 => 'A', 101 => 'Aother']] as $codes) {
            $questions = $this->buildQuestions(\Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS, $codes);
            $this->assertFieldName('Q100_S101', '1X10X100Aother', $questions);
            $this->assertFieldName('Q100_S101_Ccomment', '1X10X100Aothercomment', $questions);
            $this->assertFieldName('Q100_S102', '1X10X100A', $questions);
            $this->assertFieldName('Q100_S102_Ccomment', '1X10X100Acomment', $questions);
        }
    }
}
