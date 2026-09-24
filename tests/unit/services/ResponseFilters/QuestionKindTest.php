<?php

namespace ls\tests\unit\services\ResponseFilters;

use LimeSurvey\Models\Services\ResponseFilters\QuestionKind;
use PHPUnit\Framework\TestCase;

/**
 * The kind mapping is duplicated on the client, which needs it to pick the
 * controls to show (buildQuestionOptions.js). These cases pin the server half
 * so the two can be compared when either changes.
 */
class QuestionKindTest extends TestCase
{
    /** @dataProvider types */
    public function testKindIsDerivedFromTheTypeCode(string $type, string $expected): void
    {
        $this->assertSame($expected, QuestionKind::fromType($type));
    }

    public function types(): array
    {
        return [
            'numerical input' => ['N', QuestionKind::NUMBER],
            'date' => ['D', QuestionKind::DATE],
            'short text' => ['S', QuestionKind::TEXT],
            'long text' => ['T', QuestionKind::TEXT],
            'huge text' => ['U', QuestionKind::TEXT],
            'multiple short text' => ['Q', QuestionKind::SUB_TEXT],
            'multiple numerical' => ['K', QuestionKind::SUB_NUMBER],
            'array' => ['F', QuestionKind::ARRAY_SCALE],
            'array by column' => ['H', QuestionKind::ARRAY_SCALE],
            'array dual scale' => ['1', QuestionKind::ARRAY_DUAL],
            'array numbers' => [':', QuestionKind::ARRAY_GRID],
            'array texts' => [';', QuestionKind::ARRAY_GRID],
            'ranking' => ['R', QuestionKind::RANKING],
            'file upload' => ['|', QuestionKind::FILE_UPLOAD],
            'list radio' => ['L', QuestionKind::ANSWERS],
            'list dropdown' => ['!', QuestionKind::ANSWERS],
            'multiple choice' => ['M', QuestionKind::ANSWERS],
            'multiple choice with comments' => ['P', QuestionKind::ANSWERS],
            'yes/no' => ['Y', QuestionKind::ANSWERS],
            'gender' => ['G', QuestionKind::ANSWERS],
            '5 point choice' => ['5', QuestionKind::ANSWERS],
        ];
    }

    /** An unrecognised type stores a picked code like any other answer question. */
    public function testAnUnknownTypeFallsBackToAnswers(): void
    {
        $this->assertSame(QuestionKind::ANSWERS, QuestionKind::fromType('somethingNew'));
    }

    public function testOnlyMultipleChoiceTypesAreMultipleChoice(): void
    {
        $this->assertTrue(QuestionKind::isMultipleChoice('M'));
        $this->assertTrue(QuestionKind::isMultipleChoice('P'));
        $this->assertFalse(QuestionKind::isMultipleChoice('L'));
        $this->assertFalse(QuestionKind::isMultipleChoice('F'));
    }

    /** Display text and equations carry no answer, so they can't be filtered. */
    public function testDisplayOnlyTypesAreExcluded(): void
    {
        $this->assertTrue(QuestionKind::isExcluded('X'));
        $this->assertTrue(QuestionKind::isExcluded('*'));
        $this->assertFalse(QuestionKind::isExcluded('S'));
    }
}
