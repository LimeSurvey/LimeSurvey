<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\Sorting;

use Question;

/**
 * Sorts questions whose single value is shown as it is stored: 5 point choice
 * and numerical input by number, date/time oldest first, yes/no by its value.
 * "No answer" and the responses that didn't get the question come last.
 */
class SingleValueSorter implements QuestionSorterInterface
{
    /** Question type => SortSql value type */
    private const TYPES = [
        Question::QT_5_POINT_CHOICE => SortSql::TYPE_DIGITS_AS_TEXT,
        Question::QT_Y_YES_NO_RADIO => SortSql::TYPE_TEXT,
        Question::QT_N_NUMERICAL => SortSql::TYPE_NUMBER_OR_DATE,
        Question::QT_D_DATE => SortSql::TYPE_NUMBER_OR_DATE,
    ];

    /**
     * Whether the question is one of the single value types.
     *
     * @param array[] $fields Field map entries of the question, in column order
     * @return bool True for 5 point choice, yes/no, numerical input and date/time questions
     */
    public function canSort(array $fields): bool
    {
        return count($fields) === 1 && isset(self::TYPES[$fields[0]['type'] ?? '']);
    }

    /**
     * ORDER BY parts sorting by the stored value.
     *
     * @param array[] $fields Field map entries of the question, in column order
     * @param bool $descending True for descending order, which flips the whole order
     * @return string[] ORDER BY parts
     */
    public function getOrder(array $fields, bool $descending): array
    {
        return SortSql::forType(
            SortSql::column($fields[0]['fieldname']),
            self::TYPES[$fields[0]['type']],
            $descending
        );
    }
}
