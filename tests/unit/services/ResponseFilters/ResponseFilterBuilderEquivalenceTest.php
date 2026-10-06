<?php

namespace ls\tests\unit\services\ResponseFilters;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\ResponseFilterCriteriaBuilder;
use LimeSurvey\Models\Services\ResponseFilters\ParticipantResolver;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedCondition;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedFilter;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilter;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSqlBuilder;
use ls\tests\TestCondition;

class ResponseFilterBuilderEquivalenceTest extends TestCondition
{
    /** Only reaches the token table name and the JSON column check. */
    private const SURVEY_ID = 563242;

    private ResponseFilterCriteriaBuilder $criteriaBuilder;
    private ResponseFilterSqlBuilder $sqlBuilder;

    protected function setUp(): void
    {
        $this->criteriaBuilder = new ResponseFilterCriteriaBuilder(self::SURVEY_ID);
        $this->sqlBuilder = new ResponseFilterSqlBuilder(self::SURVEY_ID);
    }

    /** @return array<string, array{0: ResolvedFilter[]}> */
    public function filterSets(): array
    {
        return [
            'nothing asked for' => [[]],
            'one row' => [[self::row([self::equals('gender', 'F')])]],
            'rows joined by and' => [[
                self::row([self::equals('gender', 'F')]),
                self::row([self::equals('country', 'DE')]),
            ]],
            'rows joined by or' => [[
                self::row([self::equals('gender', 'F')]),
                self::row([self::equals('country', 'DE')], ResponseFilter::JOIN_OR),
            ]],
            // The fold that matters: (a OR b) AND c, not a OR (b AND c).
            'or then and' => [[
                self::row([self::equals('a', '1')]),
                self::row([self::equals('b', '2')], ResponseFilter::JOIN_OR),
                self::row([self::equals('c', '3')]),
            ]],
            'and then or' => [[
                self::row([self::equals('a', '1')]),
                self::row([self::equals('b', '2')]),
                self::row([self::equals('c', '3')], ResponseFilter::JOIN_OR),
            ]],
            'conditions inside a row and' => [[
                self::row([self::equals('Q1_S1#0', 'A1'), self::equals('Q1_S1#1', 'B1')]),
            ]],
            'conditions inside a row or' => [[
                self::row(
                    [self::equals('Q1_S1', 'Y'), self::equals('Q1_Cother', 'x')],
                    ResponseFilter::JOIN_AND,
                    ResponseFilter::JOIN_OR
                ),
            ]],
            'a condition over several columns' => [[
                self::row([
                    new ResolvedCondition(['Q1_S1', 'Q1_S2'], ResolvedCondition::OPERATOR_EQUAL, 'Y'),
                ]),
            ]],
            // An unfilled row is dropped with its join, or an empty OR row would
            // widen the results back to everything.
            'an empty row between two filled ones' => [[
                self::row([self::equals('a', '1')]),
                self::row([], ResponseFilter::JOIN_OR),
                self::row([self::equals('c', '3')]),
            ]],
            'only empty rows' => [[
                self::row([]),
                self::row([], ResponseFilter::JOIN_OR),
            ]],
            // CDbCriteria keeps one of two identical parameterless conditions.
            'the same parameterless condition twice' => [[
                self::row([self::notEmpty('Q1')]),
                self::row([self::notEmpty('Q1')], ResponseFilter::JOIN_OR),
            ]],
            'mixed operators across rows' => [[
                self::row([new ResolvedCondition(['id'], ResolvedCondition::OPERATOR_RANGE, [1, 10])]),
                self::row(
                    [new ResolvedCondition(['submitdate'], ResolvedCondition::OPERATOR_NULL, 'true')],
                    ResponseFilter::JOIN_OR
                ),
                self::row([
                    new ResolvedCondition(['Q2'], ResolvedCondition::OPERATOR_CONTAIN, 'volley'),
                ]),
            ]],
        ];
    }

    /**
     * @dataProvider filterSets
     * @param ResolvedFilter[] $filters
     */
    public function testBothBuildersProduceTheSameSql(array $filters): void
    {
        $criteria = $this->criteriaBuilder->build($filters);
        $sql = $this->sqlBuilder->build($filters);

        $this->assertSame(
            $this->renumberPlaceholders($criteria->condition),
            $this->renumberPlaceholders($sql['condition']),
            'The two builders folded the same filter differently.'
        );
        $this->assertSame(
            array_values($criteria->params),
            array_values($sql['params']),
            'The same filter bound different values on the two sides.'
        );
    }

    /**
     * @dataProvider operators
     * @param mixed $value
     */
    public function testEveryOperatorIsBuiltTheSameWay(string $operator, $value): void
    {
        $filters = [self::row([new ResolvedCondition(['col'], $operator, $value)])];

        $criteria = $this->criteriaBuilder->build($filters);
        $sql = $this->sqlBuilder->build($filters);

        $this->assertNotSame('', $sql['condition'], "No SQL built for operator: $operator");
        $this->assertSame(
            $this->renumberPlaceholders($criteria->condition),
            $this->renumberPlaceholders($sql['condition']),
            "The two builders disagree about operator: $operator"
        );
    }

    /** @return array<string, array{0: string, 1: mixed}> */
    public function operators(): array
    {
        $cases = [];
        foreach (self::operatorSamples() as $operator => $value) {
            $cases[$operator] = [$operator, $value];
        }

        return $cases;
    }

    /** A new operator stays uncovered until it gets a sample, so fail here. */
    public function testTheOperatorListCoversEveryDeclaredOperator(): void
    {
        $declared = [];
        foreach ((new \ReflectionClass(ResolvedCondition::class))->getConstants() as $name => $value) {
            if (strpos($name, 'OPERATOR_') === 0) {
                $declared[] = $value;
            }
        }

        $this->assertSame([], array_diff($declared, array_keys(self::operatorSamples())));
    }

    /**
     * Responses compare `tokens.email` through a loaded relation. The aggregate
     * scan has no relations, and joining the token table into it would multiply
     * rows and inflate every count, so the same fragment goes in a subquery.
     */
    public function testParticipantConditionsWrapTheSameFragment(): void
    {
        $filters = [self::row([self::participant('email', 'example.org')])];

        $criteria = $this->criteriaBuilder->build($filters);
        $sql = $this->sqlBuilder->build($filters);

        $this->assertStringContainsString(
            $this->renumberPlaceholders($criteria->condition),
            $this->renumberPlaceholders($sql['condition']),
            'The comparison itself should be the responses fragment, unchanged.'
        );
        $this->assertStringContainsString('IN (SELECT', $sql['condition']);
        $this->assertStringContainsString('{{tokens_' . self::SURVEY_ID . '}}', $sql['condition']);
        $this->assertSame(array_values($criteria->params), array_values($sql['params']));
    }

    /** The subquery must not swallow the rows around it. */
    public function testAParticipantRowFoldsWithTheRowsAroundIt(): void
    {
        $sql = $this->sqlBuilder->build([
            self::row([self::equals('gender', 'F')]),
            self::row([self::participant('email', 'example.org')], ResponseFilter::JOIN_OR),
            self::row([self::equals('country', 'DE')]),
        ]);

        $orPosition = strpos($sql['condition'], ') OR (');
        $andPosition = strpos($sql['condition'], ') AND (');
        $this->assertNotFalse($orPosition);
        $this->assertNotFalse($andPosition);
        $this->assertLessThan($andPosition, $orPosition);
    }

    /** Nothing asked for must filter nothing, not match nothing. */
    public function testAnEmptySetFiltersNothing(): void
    {
        $sql = $this->sqlBuilder->build([]);

        $this->assertSame('', $sql['condition']);
        $this->assertSame([], $sql['params']);
    }

    /**
     * Placeholder names come from a request-wide counter, so renumber them in
     * order of appearance: two builds of one filter then compare equal, while a
     * different shape still does not.
     */
    private function renumberPlaceholders(string $condition): string
    {
        $seen = [];

        return (string) preg_replace_callback(
            '/:[A-Za-z_][A-Za-z0-9_]*/',
            static function (array $match) use (&$seen): string {
                if (!isset($seen[$match[0]])) {
                    $seen[$match[0]] = ':p' . count($seen);
                }
                return $seen[$match[0]];
            },
            $condition
        );
    }

    /** @return array<string, mixed> */
    private static function operatorSamples(): array
    {
        return [
            ResolvedCondition::OPERATOR_EQUAL => 'Y',
            ResolvedCondition::OPERATOR_CONTAIN => 'text',
            ResolvedCondition::OPERATOR_RANGE => [1, 10],
            ResolvedCondition::OPERATOR_DATE_RANGE => ['2024-01-01', '2024-12-31'],
            ResolvedCondition::OPERATOR_MULTI_SELECT => ['A1', 'A2'],
            ResolvedCondition::OPERATOR_NOT_EMPTY => null,
            ResolvedCondition::OPERATOR_EMPTY => null,
            ResolvedCondition::OPERATOR_JSON_ELEMENT => ['position' => 0, 'value' => 'SQ001'],
            ResolvedCondition::OPERATOR_NULL => 'true',
        ];
    }

    private static function equals(string $key, string $value): ResolvedCondition
    {
        return new ResolvedCondition([$key], ResolvedCondition::OPERATOR_EQUAL, $value);
    }

    private static function notEmpty(string $key): ResolvedCondition
    {
        return new ResolvedCondition([$key], ResolvedCondition::OPERATOR_NOT_EMPTY, null);
    }

    private static function participant(string $attribute, string $value): ResolvedCondition
    {
        return new ResolvedCondition(
            [$attribute],
            ResolvedCondition::OPERATOR_CONTAIN,
            $value,
            ParticipantResolver::RELATION
        );
    }

    /** @param ResolvedCondition[] $conditions */
    private static function row(
        array $conditions,
        string $join = ResponseFilter::JOIN_AND,
        string $innerJoin = ResponseFilter::JOIN_AND
    ): ResolvedFilter {
        return new ResolvedFilter($join, $conditions, $innerJoin);
    }
}
