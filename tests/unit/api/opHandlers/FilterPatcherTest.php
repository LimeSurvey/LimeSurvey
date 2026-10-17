<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
use ls\tests\TestCondition;

/**
 * The legacy `filters` param and the `filterSet` param end up in the same
 * query. A participant filter joins the token table, which carries a `token`
 * column of its own, so once that join is there the response-side keys have to
 * say which table they mean or the database refuses the whole query.
 */
class FilterPatcherTest extends TestCondition
{
    private const DATA_MAP = [
        'token' => ['key' => 'token'],
        'submitdate' => ['key' => 'submitDate'],
    ];

    private const VALID_COLUMNS = ['token', 'submitdate', 'Q1_S1', 'Q1_S2'];

    private function apply(array $filters, string $alias = ''): \LSDbCriteria
    {
        $criteria = new \LSDbCriteria();
        $sort = new \CSort();

        (new FilterPatcher())->apply(
            ['filters' => $filters],
            $criteria,
            $sort,
            self::DATA_MAP,
            self::VALID_COLUMNS,
            $alias
        );

        return $criteria;
    }

    /**
     * @param string|array $key
     */
    private function contains($key, string $value = 'abc'): array
    {
        return [['key' => $key, 'filterMethod' => 'contain', 'value' => $value]];
    }

    /** The other commands sharing the patcher pass no alias and are unaffected. */
    public function testKeysStayBareWithoutAnAlias(): void
    {
        $criteria = $this->apply($this->contains('token'));

        $paramName = array_key_first($criteria->params);
        $this->assertFieldConditions($criteria->condition, "[0] LIKE $paramName", ['token']);
    }

    /**
     * Without this the condition reads `token LIKE :p` next to the joined
     * tokens table, which MySQL rejects as an ambiguous column rather than
     * returning rows.
     */
    public function testAnAliasNamesTheTableTheKeyBelongsTo(): void
    {
        $criteria = $this->apply($this->contains('token'), 't');

        $paramName = array_key_first($criteria->params);
        $this->assertFieldConditions($criteria->condition, "[0].[1] LIKE $paramName", ['t', 'token']);
    }

    /** A filter targeting several columns qualifies every one of them. */
    public function testEveryKeyOfAMultiColumnFilterIsQualified(): void
    {
        $criteria = $this->apply($this->contains(['Q1_S1', 'Q1_S2']), 't');

        $this->assertFieldConditions($criteria->condition, '[0].[1]', ['t', 'Q1_S1']);
        $this->assertFieldConditions($criteria->condition, '[0].[1]', ['t', 'Q1_S2']);
    }

    /**
     * The allowed set holds bare column names, so qualifying has to happen
     * after validation or every filter would be dropped as unknown.
     */
    public function testQualifyingDoesNotDropTheFilter(): void
    {
        $criteria = $this->apply($this->contains('token'), 't');

        $this->assertNotSame('', $criteria->condition);
        $this->assertSame(['%abc%'], array_values($criteria->params));
    }

    /** An unknown key is still refused, alias or not. */
    public function testAnUnknownKeyIsStillDropped(): void
    {
        $criteria = $this->apply($this->contains('nosuchcolumn'), 't');

        $this->assertSame('', (string) $criteria->condition);
    }
}
