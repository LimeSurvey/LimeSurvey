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
}
