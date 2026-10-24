<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedCondition;

/**
 * The handler for a resolved operator.
 *
 * Shared by both emitters — responses build CDbCriteria, statistics build a SQL
 * string — so an operator cannot be given a handler on one side only.
 */
class ConditionHandlerFactory
{
    private const HANDLERS = [
        ResolvedCondition::OPERATOR_EQUAL => EqualConditionHandler::class,
        ResolvedCondition::OPERATOR_CONTAIN => ContainConditionHandler::class,
        ResolvedCondition::OPERATOR_RANGE => RangeConditionHandler::class,
        ResolvedCondition::OPERATOR_DATE_RANGE => DateRangeConditionHandler::class,
        ResolvedCondition::OPERATOR_MULTI_SELECT => MultiSelectConditionHandler::class,
        ResolvedCondition::OPERATOR_NOT_EMPTY => NotEmptyConditionHandler::class,
        ResolvedCondition::OPERATOR_EMPTY => EmptyConditionHandler::class,
        ResolvedCondition::OPERATOR_JSON_ELEMENT => JsonElementConditionHandler::class,
        ResolvedCondition::OPERATOR_NULL => NullConditionHandler::class,
    ];

    /**
     * @param int|null $surveyId Passed on to handlers that need to know how a
     *     column is stored.
     * @throws InvalidArgumentException on an operator no handler covers.
     */
    public function make(string $operator, ?int $surveyId = null): HandlerInterface
    {
        if (!isset(self::HANDLERS[$operator])) {
            throw new InvalidArgumentException("No handler for filter operator: $operator.");
        }

        $handlerClass = self::HANDLERS[$operator];
        $handler = new $handlerClass();

        if ($handler instanceof SurveyContextAwareInterface) {
            $handler->setSurveyId($surveyId);
        }

        return $handler;
    }
}
