<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

use CDbCriteria;
use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\ConditionHandlerFactory;
use LimeSurvey\Models\Services\ResponseFilters\ParticipantResolver;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedCondition;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedFilter;

/**
 * Turns resolved filters into one set of criteria for the responses query.
 *
 * The resolvers say *what* to compare; this says how those comparisons combine.
 * Two levels of combining:
 *
 * - Inside a row, by its own inner join.
 * - Between rows, by each row's join, folded left to right. So `A OR B AND C`
 *   means `(A OR B) AND C` — the modal is a list read top to bottom, and that
 *   is how it reads.
 */
class ResponseFilterCriteriaBuilder
{
    private ConditionHandlerFactory $handlers;

    /** For the participant subquery, and for handlers that read how a column is stored. */
    private ?int $surveyId;

    public function __construct(?int $surveyId = null)
    {
        $this->surveyId = $surveyId;
        $this->handlers = new ConditionHandlerFactory();
    }

    /**
     * @param ResolvedFilter[] $filters
     * @return CDbCriteria Empty criteria when nothing was asked for, which
     *     merges into the query without changing it.
     */
    public function build(array $filters): CDbCriteria
    {
        $combined = null;

        foreach ($filters as $filter) {
            // A row the user started but left without a value filters nothing.
            // It is dropped along with its join, so an empty OR row cannot
            // widen the results back to everything.
            if ($filter->isEmpty()) {
                continue;
            }

            $criteria = $this->buildRow($filter);

            if ($combined === null) {
                $combined = $criteria;
                continue;
            }

            $combined->mergeWith($criteria, !$filter->isOr());
        }

        return $combined ?? new CDbCriteria();
    }

    /**
     * One row: its conditions combined by the row's own inner join.
     */
    private function buildRow(ResolvedFilter $filter): CDbCriteria
    {
        $combined = null;

        foreach ($filter->getConditions() as $condition) {
            $criteria = $this->buildCondition($condition);

            if ($combined === null) {
                $combined = $criteria;
                continue;
            }

            $combined->mergeWith($criteria, !$filter->isInnerOr());
        }

        // isEmpty() is checked before a row gets here, so there is always one.
        return $combined ?? new CDbCriteria();
    }

    private function buildCondition(ResolvedCondition $condition): CDbCriteria
    {
        $keys = $this->qualify($condition);
        $handler = $this->handlers->make($condition->getOperator(), $this->surveyId);

        // Handlers that take one key are given one; the rest read an array and
        // OR across it themselves.
        $key = count($keys) === 1 ? $keys[0] : $keys;

        $criteria = $handler->execute($key, $condition->getValue());

        if ($condition->getRelation() !== null) {
            $criteria->condition = $this->throughRelation(
                $condition->getRelation(),
                (string) $criteria->condition
            );
        }

        return $criteria;
    }

    /**
     * Ask the related table by subquery rather than by join.
     */
    private function throughRelation(string $relation, string $fragment): string
    {
        if ($relation !== ParticipantResolver::RELATION) {
            throw new InvalidArgumentException("No criteria for filter relation: $relation.");
        }

        if ($this->surveyId === null) {
            throw new InvalidArgumentException(
                'A participant filter needs the survey it belongs to.'
            );
        }

        return ParticipantResolver::throughSubquery($this->surveyId, $fragment);
    }

    /**
     * Name the table a condition's columns belong to, when it is not the
     * responses table. The relation name doubles as the subquery's alias.
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
}
