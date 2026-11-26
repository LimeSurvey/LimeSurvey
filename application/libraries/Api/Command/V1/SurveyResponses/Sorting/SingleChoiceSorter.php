<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\Sorting;

use Question;

/**
 * Sorts single choice questions with answer options (list, dropdown, buttons,
 * image select, list with comment) alphabetically by the chosen answer.
 *
 * Order: The answers A–Z, then answer codes the question doesn't have anymore,
 * then "Other" (its free text doesn't take part), then "No answer", then the
 * responses that didn't get the question. The comment of a list with comment
 * doesn't take part either.
 */
class SingleChoiceSorter implements QuestionSorterInterface
{
    /** Question types sorted by this sorter */
    private const TYPES = [
        Question::QT_L_LIST,
        Question::QT_EXCLAMATION_LIST_DROPDOWN,
        Question::QT_O_LIST_WITH_COMMENT,
    ];

    /** Stored value of the "Other" option */
    private const OTHER_CODE = '-oth-';

    private AnswerLabelOrder $answerLabelOrder;

    /**
     * Create the sorter with the answer order it ranks the chosen answers by.
     *
     * @param AnswerLabelOrder $answerLabelOrder Order of the answer options in the table language
     * @return void
     */
    public function __construct(AnswerLabelOrder $answerLabelOrder)
    {
        $this->answerLabelOrder = $answerLabelOrder;
    }

    /**
     * Whether the question is a single choice question with answer options.
     *
     * @param array[] $fields Field map entries of the question, in column order
     * @return bool True for list, dropdown and list with comment questions
     */
    public function canSort(array $fields): bool
    {
        $field = $fields[0] ?? [];
        return in_array($field['type'] ?? null, self::TYPES, true);
    }

    /**
     * ORDER BY parts sorting by the rank of the chosen answer's text.
     *
     * @param array[] $fields Field map entries of the question, in column order: The answer comes first
     * @param bool $descending True for descending order, which flips the whole order
     * @return string[] ORDER BY parts
     */
    public function getOrder(array $fields, bool $descending): array
    {
        $field = $fields[0];
        $column = SortSql::column($field['fieldname']);
        $direction = SortSql::direction($descending);

        $ranks = $this->answerLabelOrder->getRanks((int) $field['qid']);
        $unknownRank = empty($ranks) ? 1 : max($ranks) + 1;
        $ranks[self::OTHER_CODE] = $unknownRank + 1;

        return [
            SortSql::missingLast($column, $descending),
            SortSql::rank($column, $ranks, $unknownRank) . " $direction",
            self::unknownCodes($column, array_keys($ranks)) . " $direction",
        ];
    }

    /**
     * Expression keeping the responses of each unknown answer code together.
     * Known codes all map to the same value, so answers with the same text
     * stay equal and are ordered by the newest-first tie-breaker.
     *
     * @param string $column Quoted answer column
     * @param array<int, string|int> $knownCodes Codes that have a rank, at least one
     * @return string SQL expression: Empty text for known codes, the stored code otherwise
     */
    private static function unknownCodes(string $column, array $knownCodes): string
    {
        $db = \Yii::app()->db;
        $cases = '';
        foreach ($knownCodes as $code) {
            // Numeric codes become integer keys: Compare them as the strings they are stored as.
            // No IN list: Yii's limit rewrite for old MSSQL versions splits the ORDER BY at commas.
            $cases .= ' WHEN ' . $db->quoteValue((string) $code) . " THEN ''";
        }
        return "CASE $column$cases ELSE $column END";
    }
}
