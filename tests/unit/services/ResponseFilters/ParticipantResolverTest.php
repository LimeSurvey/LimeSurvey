<?php

namespace ls\tests\unit\services\ResponseFilters;

use InvalidArgumentException;
use LimeSurvey\Models\Services\ResponseFilters\ParticipantResolver;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedCondition;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedFilter;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSet;
use PHPUnit\Framework\TestCase;

class ParticipantResolverTest extends TestCase
{
    private ParticipantResolver $resolver;

    /** A survey's participant columns: the built-in ones plus two custom. */
    protected function setUp(): void
    {
        $this->resolver = new ParticipantResolver([
            'firstname',
            'lastname',
            'email',
            'emailstatus',
            'token',
            'language',
            'sent',
            'remindersent',
            'remindercount',
            'usesleft',
            'completed',
            'attribute_1',
            'attribute_2',
        ]);
    }

    private function resolveOne(array $entry): ResolvedFilter
    {
        $set = ResponseFilterSet::fromRequestValue([['source' => 'participant'] + $entry]);
        return $this->resolver->resolve($set->all()[0]);
    }

    private function singleCondition(array $entry): ResolvedCondition
    {
        $resolved = $this->resolveOne($entry);
        $this->assertCount(1, $resolved->getConditions());
        return $resolved->getConditions()[0];
    }

    /**
     * The modal sends attributes the way the rest of LimeSurvey spells them,
     * as expression placeholders.
     */
    public function testUnwrapsThePlaceholderTheModalSends(): void
    {
        $condition = $this->singleCondition([
            'attribute' => '{TOKEN:EMAIL}',
            'value' => 'example.org',
        ]);

        $this->assertSame(['email'], $condition->getKeys());
        $this->assertSame(ResolvedCondition::OPERATOR_CONTAIN, $condition->getOperator());
        $this->assertSame('example.org', $condition->getValue());
    }

    /** Custom attributes are numbered, and unwrap the same way. */
    public function testUnwrapsACustomAttribute(): void
    {
        $condition = $this->singleCondition([
            'attribute' => '{TOKEN:ATTRIBUTE_2}',
            'value' => 'Berlin',
        ]);

        $this->assertSame(['attribute_2'], $condition->getKeys());
    }

    /** A plain column name is accepted too, for callers that are not the modal. */
    public function testAcceptsABareAttributeName(): void
    {
        $condition = $this->singleCondition(['attribute' => 'lastname', 'value' => 'Smith']);

        $this->assertSame(['lastname'], $condition->getKeys());
    }

    /**
     * Participant details are not in the responses, so the condition says which
     * table it belongs to and the query builder joins it in.
     */
    public function testTheConditionCarriesTheParticipantRelation(): void
    {
        $condition = $this->singleCondition(['attribute' => '{TOKEN:LASTNAME}', 'value' => 'Smith']);

        $this->assertSame(ParticipantResolver::RELATION, $condition->getRelation());
    }

    /** Conditions on response columns carry no relation, so nothing is joined. */
    public function testResponseColumnConditionsHaveNoRelation(): void
    {
        $condition = new ResolvedCondition(['id'], ResolvedCondition::OPERATOR_EQUAL, '1');

        $this->assertNull($condition->getRelation());
    }

    public function testAnAttributeThisSurveyDoesNotHaveThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Participants have no attribute '{TOKEN:ATTRIBUTE_9}'.");
        $this->resolveOne(['attribute' => '{TOKEN:ATTRIBUTE_9}', 'value' => 'x']);
    }

    /**
     * The attribute name reaches SQL as a column, so anything that is not a
     * known attribute has to be refused rather than cleaned up and used.
     */
    public function testAnAttributeThatIsNotAColumnNameThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->resolveOne(['attribute' => 'email; DROP TABLE users', 'value' => 'x']);
    }

    /**
     * Surveys without participants have no table to join, so the filter cannot
     * be honoured and must say so — silently ignoring it would return everyone.
     */
    public function testASurveyWithoutParticipantsThrows(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            ['source' => 'participant', 'attribute' => '{TOKEN:EMAIL}', 'value' => 'x'],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no participant table');
        (new ParticipantResolver([]))->resolve($set->all()[0]);
    }

    /**
     * @dataProvider valuelessRows
     */
    public function testARowWithoutAValueResolvesToNoConditions(array $entry): void
    {
        $this->assertTrue($this->resolveOne($entry)->isEmpty());
    }

    public function valuelessRows(): array
    {
        return [
            'no value' => [['attribute' => '{TOKEN:EMAIL}']],
            'empty value' => [['attribute' => '{TOKEN:EMAIL}', 'value' => '']],
            'whitespace only' => [['attribute' => '{TOKEN:EMAIL}', 'value' => '   ']],
        ];
    }

    /**
     * An unknown attribute is only reported once the row actually asks for
     * something; an untouched row is not a filter yet.
     */
    public function testAValuelessRowIsDroppedBeforeTheAttributeIsChecked(): void
    {
        $this->assertTrue($this->resolveOne(['attribute' => '{TOKEN:NONSENSE}'])->isEmpty());
    }

    public function testTheRowsJoinIsCarriedThrough(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            ['source' => 'participant', 'attribute' => 'email', 'value' => 'a'],
            ['source' => 'participant', 'attribute' => 'email', 'value' => 'b', 'join' => 'or'],
        ]);

        $this->assertFalse($this->resolver->resolve($set->all()[0])->isOr());
        $this->assertTrue($this->resolver->resolve($set->all()[1])->isOr());
    }

    public function testRejectsAFilterFromAnotherSource(): void
    {
        $set = ResponseFilterSet::fromRequestValue([
            ['source' => 'surveyData', 'field' => 'id', 'numberMin' => 1],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('only resolves participant filters');
        $this->resolver->resolve($set->all()[0]);
    }
}
