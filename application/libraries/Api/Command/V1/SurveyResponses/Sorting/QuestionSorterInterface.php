<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\Sorting;

/**
 * Sorts the responses table by the column of one question.
 *
 * The response table shows one column per question, so a sorter gets all
 * field map entries of the question (subquestions, Other, comments) and
 * decides itself which of them take part in the order.
 */
interface QuestionSorterInterface
{
    /**
     * Whether this sorter handles the question.
     *
     * @param array[] $fields Field map entries of the question, in column order
     * @return bool True if the question can be sorted by this sorter
     */
    public function canSort(array $fields): bool;

    /**
     * ORDER BY parts for the question, without the tie-breaker on the response ID.
     * Use {@see SortSql} so that missing values are grouped like everywhere else.
     *
     * @param array[] $fields Field map entries of the question, in column order
     * @param bool $descending True for descending order, which flips the whole order
     * @return string[] ORDER BY parts
     */
    public function getOrder(array $fields, bool $descending): array;
}
