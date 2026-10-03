<?php

namespace ls\tests\unit\services\ResponseFilters;

use InvalidArgumentException;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilter;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSet;
use PHPUnit\Framework\TestCase;

class ResponseFilterSetTest extends TestCase
{
    public function testMissingOrEmptyParamYieldsAnEmptySet(): void
    {
        foreach ([null, '', []] as $raw) {
            $set = ResponseFilterSet::fromRequestValue($raw);
            $this->assertTrue($set->isEmpty());
            $this->assertSame(0, $set->count());
        }
    }

    public function testParsesAQuestionFilter(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            ['source' => 'question', 'qid' => 42, 'answerCodes' => ['Y', 'N']],
        ]);

        $this->assertSame(1, $set->count());
        $filter = $set->all()[0];
        $this->assertSame(ResponseFilter::SOURCE_QUESTION, $filter->getSource());
        $this->assertSame(42, $filter->getQid());
        $this->assertSame(['Y', 'N'], $filter->getAnswerCodes());
    }

    public function testParsesASurveyDataFilter(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            ['source' => 'surveyData', 'field' => 'id', 'numberMin' => '5', 'numberMax' => 10],
        ]);

        $filter = $set->all()[0];
        $this->assertSame(ResponseFilter::FIELD_RESPONSE_ID, $filter->getField());
        $this->assertSame(5, $filter->getNumberMin());
        $this->assertSame(10, $filter->getNumberMax());
    }

    public function testParsesAParticipantFilter(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            ['source' => 'participant', 'attribute' => 'attribute_1', 'value' => 'Smith'],
        ]);

        $filter = $set->all()[0];
        $this->assertSame('attribute_1', $filter->getAttribute());
        $this->assertSame('Smith', $filter->getValue());
    }

    public function testJoinDefaultsToAndAndIsReadPerEntry(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            ['source' => 'surveyData', 'field' => 'id'],
            ['source' => 'surveyData', 'field' => 'seed', 'join' => 'or'],
            ['source' => 'surveyData', 'field' => 'submitdate', 'join' => 'and'],
        ]);

        [$first, $second, $third] = $set->all();
        $this->assertSame(ResponseFilter::JOIN_AND, $first->getJoin());
        $this->assertFalse($first->isOr());
        $this->assertTrue($second->isOr());
        $this->assertFalse($third->isOr());
    }

    public function testIncludedDefaultsToAll(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            ['source' => 'surveyData', 'field' => 'completed'],
        ]);

        $this->assertSame(ResponseFilter::INCLUDED_ALL, $set->all()[0]->getIncluded());
    }

    public function testRejectsANonListSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('filterSet must be a list of filters.');
        ResponseFilterSet::fromRequestValue(['source' => 'question', 'qid' => 1]);
    }

    public function testRejectsAnUnknownSource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('filterSet[0] has an unknown source.');
        ResponseFilterSet::fromRequestValue([['source' => 'nope']]);
    }

    public function testRejectsAnUnknownJoin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('filterSet[0] has an unknown join.');
        ResponseFilterSet::fromRequestValue([
            ['source' => 'surveyData', 'field' => 'id', 'join' => 'xor'],
        ]);
    }

    /**
     * A typo like "textValue" (the UI-side name) must fail loudly. Ignoring it
     * would drop the filter and silently return more rows than asked for.
     */
    public function testRejectsUnknownProperties(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('filterSet[0] has unknown property: textValue.');
        ResponseFilterSet::fromRequestValue([
            ['source' => 'question', 'qid' => 1, 'textValue' => 'oops'],
        ]);
    }

    /**
     * questionKind is derived server-side from the question type, so a client
     * must not be able to send one.
     */
    public function testRejectsAClientSuppliedQuestionKind(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('questionKind');
        ResponseFilterSet::fromRequestValue([
            ['source' => 'question', 'qid' => 1, 'questionKind' => 'answers'],
        ]);
    }

    public function testRejectsAQuestionFilterWithoutAUsableQid(): void
    {
        foreach ([[], ['qid' => 'abc'], ['qid' => 0], ['qid' => -3]] as $payload) {
            try {
                ResponseFilterSet::fromRequestValue([['source' => 'question'] + $payload]);
                $this->fail('Expected an exception for qid: ' . json_encode($payload));
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('qid', $e->getMessage());
            }
        }
    }

    public function testAcceptsANumericStringQid(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            ['source' => 'question', 'qid' => '42'],
        ]);

        $this->assertSame(42, $set->all()[0]->getQid());
    }

    public function testRejectsAnUnknownSurveyDataField(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('filterSet[0] has an unknown field.');
        ResponseFilterSet::fromRequestValue([
            ['source' => 'surveyData', 'field' => 'nope'],
        ]);
    }

    public function testRejectsAParticipantFilterWithoutAnAttribute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('filterSet[0] requires an attribute.');
        ResponseFilterSet::fromRequestValue([
            ['source' => 'participant', 'value' => 'Smith'],
        ]);
    }

    public function testRejectsAnUnknownFileUploadedValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unknown fileUploaded value');
        ResponseFilterSet::fromRequestValue([
            ['source' => 'question', 'qid' => 1, 'fileUploaded' => 'maybe'],
        ]);
    }

    /** The error names the offending entry, not just "something was wrong". */
    public function testErrorIdentifiesWhichEntryFailed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('filterSet[2]');
        ResponseFilterSet::fromRequestValue([
            ['source' => 'surveyData', 'field' => 'id'],
            ['source' => 'surveyData', 'field' => 'seed'],
            ['source' => 'surveyData', 'field' => 'bogus'],
        ]);
    }

    public function testBlankNumbersAreTreatedAsAbsent(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            ['source' => 'surveyData', 'field' => 'id', 'numberMin' => '', 'numberMax' => null],
        ]);

        $filter = $set->all()[0];
        $this->assertNull($filter->getNumberMin());
        $this->assertNull($filter->getNumberMax());
    }

    /**
     * The command reads this to decide whether the caller needs permission to
     * read the survey's participants, so it has to see a participant row
     * wherever it sits in the set.
     */
    public function testReportsWhetherTheSetReadsParticipantData(): void
    {
        $without = ResponseFilterSet::fromRequestValue([
            ['source' => 'question', 'qid' => 42, 'answerCodes' => ['Y']],
            ['source' => 'surveyData', 'field' => 'id', 'numberMin' => 1],
        ]);
        $this->assertFalse($without->hasParticipantFilter());

        $with = ResponseFilterSet::fromRequestValue([
            ['source' => 'question', 'qid' => 42, 'answerCodes' => ['Y']],
            ['source' => 'participant', 'attribute' => 'email', 'value' => 'a@b.c'],
        ]);
        $this->assertTrue($with->hasParticipantFilter());
    }

    public function testAnEmptySetReadsNoParticipantData(): void
    {
        $this->assertFalse(ResponseFilterSet::fromRequestValue([])->hasParticipantFilter());
    }

    /**
     * A value that cannot be read as what it claims to be has to fail here.
     * Left alone it does not fail anywhere: an unreadable bound counts as no
     * bound, and a row with no usable bound at all is dropped — showing every
     * response to someone who thinks they filtered.
     *
     * @dataProvider malformedValues
     */
    public function testAMalformedValueIsRejected(array $entry, string $expected): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expected);
        ResponseFilterSet::fromRequestValue([$entry]);
    }

    public function malformedValues(): array
    {
        $question = ['source' => 'question', 'qid' => 42];

        return [
            'text where a number belongs' => [
                $question + ['numberMin' => 'abc'],
                'numberMin must be a number',
            ],
            'unreadable upper bound' => [
                $question + ['numberMax' => 'ten'],
                'numberMax must be a number',
            ],
            'date in the wrong format' => [
                $question + ['dateFrom' => '31/01/2024'],
                'dateFrom must be a date formatted as Y-m-d',
            ],
            // createFromFormat() alone rolls this over into 2025 instead of
            // refusing it, which is why the round trip matters.
            'date that does not exist' => [
                $question + ['dateTo' => '2024-13-45'],
                'dateTo must be a date formatted as Y-m-d',
            ],
            'row that is not a number' => [
                $question + ['row' => 'abc'],
                'row must be a whole number',
            ],
            'subquestion that is not a number' => [
                $question + ['subquestion' => '12a'],
                'subquestion must be a whole number',
            ],
            'text sent as a list' => [
                $question + ['text' => ['a', 'b']],
                'text must be a single value',
            ],
            'participant value sent as a list' => [
                ['source' => 'participant', 'attribute' => 'email', 'value' => ['a']],
                'value must be a single value',
            ],
            'survey data bound that is not a number' => [
                ['source' => 'surveyData', 'field' => 'id', 'numberMin' => 'first'],
                'numberMin must be a number',
            ],
            // The list is a list, so the is_array check lets it past; what it
            // holds reaches strval() and comes out as the literal 'Array'.
            'answer code nested in another list' => [
                $question + ['answerCodes' => [['A1']]],
                'answerCodes must be a list of single values',
            ],
            'language nested in another list' => [
                ['source' => 'surveyData', 'field' => 'startlanguage', 'languages' => [['en']]],
                'languages must be a list of single values',
            ],
        ];
    }

    /** The shapes a well-formed value is allowed to take still pass. */
    public function testWellFormedValuesAreAccepted(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            [
                'source' => 'question',
                'qid' => 42,
                'numberMin' => '2.5',
                'numberMax' => 10,
                'row' => '1002',
                'subquestion' => 901,
            ],
            [
                'source' => 'question',
                'qid' => 43,
                'dateFrom' => '2024-01-31',
                'dateTo' => '2024-12-01',
            ],
        ]);

        $this->assertSame(2, $set->count());
        $this->assertSame(2.5, $set->all()[0]->getNumberMin());
        $this->assertSame(1002, $set->all()[0]->getRow());
        $this->assertSame('2024-01-31', $set->all()[1]->getDateFrom());
    }
}
