<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Tests for getFieldName(), which maps an old SGQA field name to the new
 * Q{qid}_S{sqid} field name.
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
        $questions = [(object) ['qid' => 100, 'gid' => 10, 'parent_qid' => 0, 'type' => $type, 'title' => 'Q1']];
        foreach ($subquestionCodes as $sqid => $code) {
            $questions[] = (object) ['qid' => $sqid, 'gid' => 10, 'parent_qid' => 100, 'type' => $type, 'title' => $code];
        }
        return $questions;
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
        $this->assertSame('Q100_S102', getFieldName('{{responses_1}}', '1X10X100other', $questions, 1, 10));
        $this->assertSame('Q100_S101', getFieldName('{{responses_1}}', '1X10X100SQ001', $questions, 1, 10));

        // Order of the subquestions must not matter
        $questions = $this->buildQuestions(\Question::QT_F_ARRAY, [102 => 'other', 101 => 'SQ001']);
        $this->assertSame('Q100_S102', getFieldName('{{responses_1}}', '1X10X100other', $questions, 1, 10));
    }

    /**
     * Subquestions coded "comment" and "othercomment" are regular subquestions too.
     *
     * @return void
     */
    public function testArraySubquestionCodedComment()
    {
        $questions = $this->buildQuestions(\Question::QT_A_ARRAY_5_POINT, [101 => 'comment', 102 => 'othercomment']);
        $this->assertSame('Q100_S101', getFieldName('{{responses_1}}', '1X10X100comment', $questions, 1, 10));
        $this->assertSame('Q100_S102', getFieldName('{{responses_1}}', '1X10X100othercomment', $questions, 1, 10));
    }

    /**
     * The "other" and comment fields of multiple choice with comments are still mapped.
     *
     * @return void
     */
    public function testMultipleChoiceWithCommentsOtherAndComments()
    {
        $questions = $this->buildQuestions(\Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS, [101 => 'SQ001', 102 => 'SQ002']);
        $this->assertSame('Q100_S101', getFieldName('{{responses_1}}', '1X10X100SQ001', $questions, 1, 10));
        $this->assertSame('Q100_S102_Ccomment', getFieldName('{{responses_1}}', '1X10X100SQ002comment', $questions, 1, 10));
        $this->assertSame('Q100_Cother', getFieldName('{{responses_1}}', '1X10X100other', $questions, 1, 10));
        $this->assertSame('Q100_Cothercomment', getFieldName('{{responses_1}}', '1X10X100othercomment', $questions, 1, 10));
    }
}
