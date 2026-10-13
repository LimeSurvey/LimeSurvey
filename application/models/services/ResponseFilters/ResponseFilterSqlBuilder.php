<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\ConditionHandlerFactory;

/**
 * Turns resolved filters into one WHERE clause for the statistics aggregates.
 */
class ResponseFilterSqlBuilder
{
    private ?int $surveyId;

    private ConditionHandlerFactory $handlers;

    /**
     * @param int|null $surveyId Needed for the participant subquery and for
     *     handlers that read how a column is stored.
     */
    public function __construct(?int $surveyId = null)
    {
        $this->surveyId = $surveyId;
        $this->handlers = new ConditionHandlerFactory();
    }

    /**
     * @param ResolvedFilter[] $filters
     * @return array{condition: string, params: array<string,mixed>} An empty
     *     condition when nothing was asked for, which filters nothing.
     */
    public function build(array $filters): array
    {
        $condition = '';
        $params = [];

        foreach ($filters as $filter) {
            if ($filter->isEmpty()) {
                continue;
            }

            [$condition, $params] = $this->merge(
                $condition,
                $params,
                $this->buildRow($filter),
                !$filter->isOr()
            );
        }

        return ['condition' => $condition, 'params' => $params];
    }

    /**
     * One row: its conditions combined by the row's own inner join.
     *
     * @return array{0: string, 1: array<string,mixed>}
     */
    private function buildRow(ResolvedFilter $filter): array
    {
        $condition = '';
        $params = [];

        foreach ($filter->getConditions() as $resolved) {
            [$condition, $params] = $this->merge(
                $condition,
                $params,
                $this->buildCondition($resolved),
                !$filter->isInnerOr()
            );
        }

        return [$condition, $params];
    }

    /**
     * @return array{0: string, 1: array<string,mixed>}
     */
    private function buildCondition(ResolvedCondition $condition): array
    {
        $keys = $this->qualify($condition);
        $handler = $this->handlers->make($condition->getOperator(), $this->surveyId);

        $key = count($keys) === 1 ? $keys[0] : $keys;

        $criteria = $handler->execute($key, $condition->getValue());
        $sql = (string) $criteria->condition;

        if ($condition->getRelation() !== null) {
            $sql = $this->throughRelation($condition->getRelation(), $sql);
        }

        return [$sql, (array) $criteria->params];
    }

    /**
     * Name the table a condition's columns belong to, as the responses side
     * does, so the comparison itself is the same SQL on both.
     *
     * @return array<int,string>
     */
    private function qualify(ResolvedCondition $condition): array
    {
        $relation = $condition->getRelation();
        if ($relation === null) {
            return $condition->getKeys();
        }

        return array_map(
            static function (string $key) use ($relation): string {
                return "$relation.$key";
            },
            $condition->getKeys()
        );
    }

    /**
     * Ask the related table by subquery rather than by join.
     */
    private function throughRelation(string $relation, string $fragment): string
    {
        if ($relation !== ParticipantResolver::RELATION) {
            throw new InvalidArgumentException("No SQL for filter relation: $relation.");
        }

        if ($this->surveyId === null) {
            throw new InvalidArgumentException(
                'A participant filter needs the survey it belongs to.'
            );
        }

        $db = \App()->db;
        $token = $db->quoteColumnName('token');
        $relationToken = $db->quoteColumnName("$relation.token");

        return "$token IN (SELECT $relationToken FROM {{tokens_{$this->surveyId}}} $relation"
            . " WHERE $fragment)";
    }

    /**
     * Combine two fragments the way CDbCriteria::mergeWith() does: parenthesise
     * both sides, and keep one of two identical conditions — which is how the
     * responses side reads two rows that resolve to the same parameterless SQL.
     *
     * @param array<string,mixed> $params
     * @param array{0: string, 1: array<string,mixed>} $addition
     * @return array{0: string, 1: array<string,mixed>}
     */
    private function merge(string $condition, array $params, array $addition, bool $and): array
    {
        [$right, $rightParams] = $addition;

        if ($condition !== $right) {
            if ($condition === '') {
                $condition = $right;
            } elseif ($right !== '') {
                $condition = '(' . $condition . ')' . ($and ? ' AND ' : ' OR ') . '(' . $right . ')';
            }
        }

        if ($params !== $rightParams) {
            $params = array_merge($params, $rightParams);
        }

        return [$condition, $params];
    }
}
