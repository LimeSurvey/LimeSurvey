<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\Sorting;

/**
 * Builds the ORDER BY of the response list from the column the user sorts by.
 *
 * Columns are addressed by the keys the response table uses: The API keys of
 * the metadata columns (id, submitDate, …) and the question ID for question
 * columns. Without a valid sort, the newest responses come first. Every sort
 * ends with the newest response first, so responses with the same value keep
 * a stable order.
 */
class ResponseSort
{
    /** Metadata columns: API key => [response table column, SortSql value type] */
    private const METADATA_COLUMNS = [
        'id' => ['id', SortSql::TYPE_ID],
        'quotaExit' => ['quota_exit', SortSql::TYPE_NUMBER_OR_DATE],
        'token' => ['token', SortSql::TYPE_TEXT],
        'dateLastAction' => ['datestamp', SortSql::TYPE_NUMBER_OR_DATE],
        'seed' => ['seed', SortSql::TYPE_DIGITS_AS_TEXT],
        'language' => ['startlanguage', SortSql::TYPE_TEXT],
        'submitDate' => ['submitdate', SortSql::TYPE_NUMBER_OR_DATE],
    ];

    /** Participant columns: API key => participants table column */
    public const PARTICIPANT_COLUMNS = [
        'firstName' => 'firstname',
        'lastName' => 'lastname',
        'email' => 'email',
    ];

    /** @var array<string, array{0: string, 1: string}> Sortable metadata: API key => [column, value type] */
    private array $metadataColumns = [];

    /** @var array<string, array[]> Question ID => field map entries of the question */
    private array $questionFields = [];

    /** @var QuestionSorterInterface[] */
    private array $questionSorters;

    /** @var array<string, string> Sortable participant columns: API key => participants table column */
    private array $participantColumns;

    private ?string $participantsTable;

    /**
     * @param array $fieldMap Field map of the survey, as created by createFieldMap()
     * @param QuestionSorterInterface[] $questionSorters Sorters for question columns, the first that can sort a question wins
     * @param string|null $participantsTable Participants table name, null when participant columns can't be sorted.
     *   Ignored when the field map has no token (anonymized survey).
     * @param string[] $participantColumns API keys of the participant columns that can be sorted (not encrypted, readable)
     */
    public function __construct(
        array $fieldMap,
        array $questionSorters = [],
        ?string $participantsTable = null,
        array $participantColumns = []
    ) {
        foreach (self::METADATA_COLUMNS as $key => $definition) {
            if (isset($fieldMap[$definition[0]])) {
                $this->metadataColumns[$key] = $definition;
            }
        }
        foreach ($fieldMap as $field) {
            if (!empty($field['qid']) && is_numeric($field['qid'])) {
                $this->questionFields[(string) $field['qid']][] = $field;
            }
        }
        $this->questionSorters = $questionSorters;
        $this->participantsTable = $participantsTable;
        // Anonymized surveys have no token column to find the participant with
        $this->participantColumns = $participantsTable === null || !isset($fieldMap['token'])
            ? []
            : array_intersect_key(self::PARTICIPANT_COLUMNS, array_flip($participantColumns));
    }

    /**
     * Keys of all columns the response list can be sorted by.
     *
     * @return string[] Column keys as used by the response table
     */
    public function getSortableKeys(): array
    {
        $keys = array_merge(array_keys($this->metadataColumns), array_keys($this->participantColumns));
        foreach (array_keys($this->questionFields) as $qid) {
            if ($this->getQuestionSorter((string) $qid) !== null) {
                $keys[] = (string) $qid;
            }
        }
        return $keys;
    }

    /**
     * ORDER BY for the requested sort.
     *
     * @param mixed $sort Sort param of the request: [column key => "asc"|"desc"], only the first entry is used
     * @return string ORDER BY expression, newest responses first when the sort is missing or invalid
     */
    public function getOrder($sort): string
    {
        $default = SortSql::newestFirst();
        if (!is_array($sort) || empty($sort)) {
            return $default;
        }
        $key = (string) array_key_first($sort);
        $direction = strtolower((string) reset($sort));
        if (!in_array($direction, ['asc', 'desc'], true)) {
            return $default;
        }
        $parts = $this->getOrderParts($key, $direction === 'desc');
        if ($parts === null) {
            return $default;
        }
        if ($key !== 'id') {
            $parts[] = $default;
        }
        return implode(', ', $parts);
    }

    /**
     * ORDER BY parts of one column, without the tie-breaker.
     *
     * @param string $key Column key as used by the response table
     * @param bool $descending True for descending order
     * @return string[]|null ORDER BY parts, null if the column can't be sorted
     */
    private function getOrderParts(string $key, bool $descending): ?array
    {
        if (isset($this->metadataColumns[$key])) {
            [$column, $type] = $this->metadataColumns[$key];
            return SortSql::forType(SortSql::column($column), $type, $descending);
        }
        if (isset($this->participantColumns[$key])) {
            return SortSql::forType($this->participantValue($this->participantColumns[$key]), SortSql::TYPE_TEXT, $descending);
        }
        $sorter = $this->getQuestionSorter($key);
        if ($sorter !== null) {
            return $sorter->getOrder($this->questionFields[$key], $descending);
        }
        return null;
    }

    /**
     * Sorter for a question column, if the question can be sorted.
     *
     * @param string $qid Question ID
     * @return QuestionSorterInterface|null The first sorter that handles the question, null if none does or a field is encrypted
     */
    private function getQuestionSorter(string $qid): ?QuestionSorterInterface
    {
        $fields = $this->questionFields[$qid] ?? [];
        if (empty($fields)) {
            return null;
        }
        foreach ($fields as $field) {
            // Encrypted values can't be ordered in SQL
            if (($field['encrypted'] ?? 'N') === 'Y') {
                return null;
            }
        }
        foreach ($this->questionSorters as $sorter) {
            if ($sorter->canSort($fields)) {
                return $sorter;
            }
        }
        return null;
    }

    /**
     * Subquery reading a participant attribute of the response's participant.
     * A subquery rather than a join, so it can't clash with joins of filters.
     *
     * @param string $column Column in the participants table
     * @return string SQL expression, NULL when the response has no participant
     */
    private function participantValue(string $column): string
    {
        $db = \Yii::app()->db;
        $alias = 'sortparticipant';
        $subquery = $db->createCommand()
            ->select('MIN(' . $db->quoteColumnName("$alias.$column") . ')')
            ->from([$this->participantsTable . ' ' . $alias])
            ->where($db->quoteColumnName("$alias.token") . ' = ' . SortSql::column('token'))
            ->getText();
        return "($subquery)";
    }
}
