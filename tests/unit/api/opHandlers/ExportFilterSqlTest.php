<?php

namespace ls\tests\unit\api\opHandlers;

use CDbCriteria;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\ExportFilterSql;
use LimeSurvey\Models\Services\ExportSurveyResultsService;
use ls\tests\TestCondition;

class ExportFilterSqlTest extends TestCondition
{
    private function quote(string $value): string
    {
        return \Yii::app()->db->quoteValue($value);
    }

    private function filterSql(?CDbCriteria $columnCriteria = null): ExportFilterSql
    {
        $exportSurvey = $this->createMock(ExportSurveyResultsService::class);
        $exportSurvey->method('setFilters')->willReturnSelf();
        $exportSurvey->method('buildFilterCriteria')->willReturn($columnCriteria);

        return new ExportFilterSql($exportSurvey);
    }

    public function testPositionalParamsAreQuotedInOrder(): void
    {
        $sql = $this->filterSql()->inlineParams('a = ? AND b = ?', ['x', "it's"]);

        $this->assertSame('a = ' . $this->quote('x') . ' AND b = ' . $this->quote("it's"), $sql);
    }

    public function testNamedParamsAreQuoted(): void
    {
        $sql = $this->filterSql()->inlineParams('a = :p0 OR b = :p1', [':p0' => '1', ':p1' => '2']);

        $this->assertSame('a = ' . $this->quote('1') . ' OR b = ' . $this->quote('2'), $sql);
    }

    /** A placeholder without a value is left for the database to refuse, not guessed. */
    public function testAnUnknownPlaceholderIsLeftAlone(): void
    {
        $this->assertSame('a = :missing', $this->filterSql()->inlineParams('a = :missing', []));
    }

    public function testEmptyConditionsAreLeftOutWhenCombining(): void
    {
        $filterSql = $this->filterSql();

        $this->assertSame('', $filterSql->combineSqlFilters('', ''));
        $this->assertSame('a = 1', $filterSql->combineSqlFilters('', 'a = 1'));
        $this->assertSame('(a = 1) AND (b = 2 OR c = 3)', $filterSql->combineSqlFilters('a = 1', '', 'b = 2 OR c = 3'));
    }

    public function testNoFiltersGiveNoCondition(): void
    {
        $exportSurvey = $this->createMock(ExportSurveyResultsService::class);
        $exportSurvey->expects($this->never())->method('setFilters');

        $sql = (new ExportFilterSql($exportSurvey))->buildLegacyCondition(123456, '{{responses_123456}}', [], []);

        $this->assertSame('', $sql);
    }

    /** Column filters name the responses table, so a joined table cannot make them ambiguous. */
    public function testColumnFiltersArePrefixedWithTheResponsesTable(): void
    {
        $criteria = new CDbCriteria();
        $criteria->condition = "`Q1` = :p0 AND `Q2` <> 'a`b'";
        $criteria->params = [':p0' => 'A1'];

        $sql = $this->filterSql($criteria)->buildLegacyCondition(
            123456,
            '{{responses_123456}}',
            [['key' => 'Q1', 'filterMethod' => 'equal', 'value' => 'A1']],
            []
        );

        $this->assertSame(
            "{{responses_123456}}.`Q1` = " . $this->quote('A1') . " AND {{responses_123456}}.`Q2` <> 'a`b'",
            $sql
        );
    }
}
