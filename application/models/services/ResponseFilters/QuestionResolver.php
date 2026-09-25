<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

use InvalidArgumentException;

/**
 * Resolves `source: "question"` rows into conditions on response columns.
 *
 * The filter model is question-shaped ("question 42, answers Y and N"); storage
 * is column-shaped ("column 132241X130X2110 is Y"). Which columns a selection
 * means depends on the question's kind, so this is a switch over
 * {@see QuestionKind} — the same split the modal uses to decide which controls
 * to show, applied here to decide which columns to search.
 *
 * Kinds without a case fail loudly. A filter the user built and that quietly
 * matched everything would be worse than an error: it reads as "the filter is
 * broken" and it shows rows they asked to exclude.
 */
class QuestionResolver
{
    /**
     * The pseudo-answer behind an "Other" option. Single-choice questions store
     * it as the answer itself; multiple choice does not — see resolveAnswers().
     */
    private const OTHER_CODE = '-oth-';

    /** What a ticked multiple-choice option stores in its own column. */
    private const CHECKED = 'Y';

    /** The `aid` marking a question's free-text "Other" column. */
    private const OTHER_AID = 'other';

    /** Array (Numbers); its sibling ';' is Array (Texts). */
    private const ARRAY_NUMBERS_TYPE = ':';

    private QuestionColumnMap $map;

    public function __construct(QuestionColumnMap $map)
    {
        $this->map = $map;
    }

    public function resolve(ResponseFilter $filter): ResolvedFilter
    {
        if ($filter->getSource() !== ResponseFilter::SOURCE_QUESTION) {
            throw new InvalidArgumentException('QuestionResolver only resolves question filters.');
        }

        $qid = (int) $filter->getQid();
        // Throws when the qid is not in this survey, so a filter can never
        // reach for a column belonging to somebody else's data.
        $type = $this->map->getType($qid);

        if (QuestionKind::isExcluded($type)) {
            throw new InvalidArgumentException("Question $qid holds no answers to filter on.");
        }

        $kind = QuestionKind::fromType($type);
        $innerJoin = ResponseFilter::JOIN_AND;

        switch ($kind) {
            case QuestionKind::ANSWERS:
                $conditions = $this->resolveAnswers($qid, $type, $filter);
                // Options picked from one list mean "any of these".
                $innerJoin = ResponseFilter::JOIN_OR;
                break;
            case QuestionKind::TEXT:
                $conditions = $this->resolveText($qid, $filter);
                break;
            case QuestionKind::NUMBER:
                $conditions = $this->resolveNumber($qid, $filter);
                break;
            case QuestionKind::DATE:
                $conditions = $this->resolveDate($qid, $filter);
                break;
            case QuestionKind::SUB_TEXT:
                $conditions = $this->resolveSubText($qid, $filter);
                break;
            case QuestionKind::SUB_NUMBER:
                $conditions = $this->resolveSubNumber($qid, $filter);
                break;
            case QuestionKind::ARRAY_SCALE:
                $conditions = $this->resolveArrayScale($qid, $filter);
                break;
            case QuestionKind::ARRAY_DUAL:
                $conditions = $this->resolveArrayDual($qid, $filter);
                break;
            case QuestionKind::ARRAY_GRID:
                $conditions = $this->resolveArrayGrid($qid, $type, $filter);
                break;
            default:
                throw new InvalidArgumentException("No resolver for question kind: $kind.");
        }

        return new ResolvedFilter($filter->getJoin(), $conditions, $innerJoin);
    }

    /**
     * Answer-based questions. Two storage shapes hide behind one modal control:
     *
     * - One column holding the chosen code (radio lists, dropdowns, yes/no,
     *   gender, 5 point choice). Several picks are an IN list, and "Other" is
     *   just another stored code.
     * - One column per option, holding 'Y' when ticked (multiple choice). The
     *   picks are subquestion ids, each resolving to its own column, and
     *   "Other" is not among them: ticking it stores free text in a separate
     *   column, so the only trace of the tick is that the text is there.
     *
     * @return ResolvedCondition[]
     */
    private function resolveAnswers(int $qid, string $type, ResponseFilter $filter): array
    {
        $codes = $filter->getAnswerCodes();
        if ($codes === []) {
            return [];
        }

        if (!QuestionKind::isMultipleChoice($type)) {
            return [
                new ResolvedCondition(
                    [$this->requireMainColumn($qid)],
                    ResolvedCondition::OPERATOR_MULTI_SELECT,
                    $codes
                ),
            ];
        }

        $conditions = [];
        $optionColumns = [];

        foreach ($codes as $code) {
            if ($code === self::OTHER_CODE) {
                $conditions[] = new ResolvedCondition(
                    [$this->requireOtherColumn($qid)],
                    ResolvedCondition::OPERATOR_NOT_EMPTY,
                    null
                );
                continue;
            }

            $optionColumns[] = $this->requireSubquestionColumn($qid, $code);
        }

        if ($optionColumns !== []) {
            // One condition over several keys: the equal handler already ORs
            // them, so ticked-any-of is a single comparison.
            array_unshift(
                $conditions,
                new ResolvedCondition($optionColumns, ResolvedCondition::OPERATOR_EQUAL, self::CHECKED)
            );
        }

        return $conditions;
    }

    /**
     * @return ResolvedCondition[]
     */
    private function resolveText(int $qid, ResponseFilter $filter): array
    {
        $text = $this->readText($filter);

        return $text === null ? [] : $this->contains($this->requireMainColumn($qid), $text);
    }

    /**
     * Multiple short text: the user picks one of the question's boxes, then
     * types what it should contain. Same comparison as a plain text question,
     * against the box's own column.
     *
     * @return ResolvedCondition[]
     */
    private function resolveSubText(int $qid, ResponseFilter $filter): array
    {
        $text = $this->readText($filter);

        return $text === null ? [] : $this->contains($this->requireSubquestion($qid, $filter), $text);
    }

    /**
     * @return ResolvedCondition[]
     */
    private function resolveNumber(int $qid, ResponseFilter $filter): array
    {
        $bounds = $this->readNumberBounds($filter);

        return $bounds === null ? [] : $this->range($this->requireMainColumn($qid), $bounds);
    }

    /**
     * Multiple numerical input: as above, with a min/max instead of text.
     *
     * @return ResolvedCondition[]
     */
    private function resolveSubNumber(int $qid, ResponseFilter $filter): array
    {
        $bounds = $this->readNumberBounds($filter);

        return $bounds === null ? [] : $this->range($this->requireSubquestion($qid, $filter), $bounds);
    }

    /**
     * @return ResolvedCondition[]
     */
    private function resolveDate(int $qid, ResponseFilter $filter): array
    {
        $from = $filter->getDateFrom();
        $to = $filter->getDateTo();

        if ($from === null && $to === null) {
            return [];
        }

        return [
            new ResolvedCondition(
                [$this->requireMainColumn($qid)],
                ResolvedCondition::OPERATOR_DATE_RANGE,
                [$from ?? '', $to ?? '']
            ),
        ];
    }

    /**
     * Array questions: each row is a column holding the answer picked on the
     * scale. The user names a row and a scale answer, so this is one equality
     * on the row's column.
     *
     * @return ResolvedCondition[]
     */
    private function resolveArrayScale(int $qid, ResponseFilter $filter): array
    {
        $answerCode = $filter->getColumn();
        if ($answerCode === null || $answerCode === '') {
            return [];
        }

        $column = $this->map->getColumnBySqid($qid, $this->requireRow($qid, $filter));
        if ($column === null) {
            throw new InvalidArgumentException("Question $qid has no row {$filter->getRow()}.");
        }

        return [new ResolvedCondition([$column], ResolvedCondition::OPERATOR_EQUAL, $answerCode)];
    }

    /**
     * Dual-scale arrays: one row, answered twice, stored in two columns.
     *
     * The modal lets either scale be left blank, so a row can produce one
     * condition or two. Two mean the respondent must have given both answers on
     * that row, which is why the row's conditions AND.
     *
     * @return ResolvedCondition[]
     */
    private function resolveArrayDual(int $qid, ResponseFilter $filter): array
    {
        $scales = [0 => $filter->getColumn(), 1 => $filter->getColumn2()];
        $scales = array_filter($scales, static function ($code): bool {
            return $code !== null && $code !== '';
        });

        if ($scales === []) {
            return [];
        }

        $rowSqid = $this->requireRow($qid, $filter);
        $conditions = [];

        foreach ($scales as $scale => $answerCode) {
            $column = $this->map->getColumnBySqidAndScale($qid, $rowSqid, $scale);
            if ($column === null) {
                throw new InvalidArgumentException(
                    "Question $qid has no row $rowSqid on scale $scale."
                );
            }

            $conditions[] = new ResolvedCondition(
                [$column],
                ResolvedCondition::OPERATOR_EQUAL,
                $answerCode
            );
        }

        return $conditions;
    }

    /**
     * Array grids: rows and columns are both subquestions, and every cell has a
     * column of its own holding what was typed into it. The type decides what
     * that is — numbers for ':' and text for ';' — so the same cell is filtered
     * by range or by contains.
     *
     * @return ResolvedCondition[]
     */
    private function resolveArrayGrid(int $qid, string $type, ResponseFilter $filter): array
    {
        $isNumeric = $type === self::ARRAY_NUMBERS_TYPE;

        $text = $isNumeric ? null : $this->readText($filter);
        $bounds = $isNumeric ? $this->readNumberBounds($filter) : null;

        if ($text === null && $bounds === null) {
            return [];
        }

        $columnSqid = $filter->getColumn();
        if ($columnSqid === null || !ctype_digit($columnSqid)) {
            throw new InvalidArgumentException("Question $qid needs a column to filter on.");
        }

        $rowSqid = $this->requireRow($qid, $filter);
        $column = $this->map->getGridColumn($qid, $rowSqid, (int) $columnSqid);
        if ($column === null) {
            throw new InvalidArgumentException("Question $qid has no cell $rowSqid/$columnSqid.");
        }

        return $isNumeric ? $this->range($column, $bounds) : $this->contains($column, $text);
    }

    /**
     * The row an array filter applies to. Rejected rather than dropped when
     * missing, for the same reason as a missing subquestion: the value has
     * nowhere to go.
     */
    private function requireRow(int $qid, ResponseFilter $filter): int
    {
        $row = $filter->getRow();
        if ($row === null) {
            throw new InvalidArgumentException("Question $qid needs a row to filter on.");
        }

        return $row;
    }

    /** The typed-in text, or null when the row carries none. */
    private function readText(ResponseFilter $filter): ?string
    {
        $text = $filter->getText();

        return ($text === null || trim($text) === '') ? null : $text;
    }

    /**
     * The min/max pair in the shape the range handler reads — position 0 is the
     * min, 1 the max, '' meaning no bound. Null when neither end was given.
     *
     * @return array{0:int|float|string,1:int|float|string}|null
     */
    private function readNumberBounds(ResponseFilter $filter): ?array
    {
        $min = $filter->getNumberMin();
        $max = $filter->getNumberMax();

        if ($min === null && $max === null) {
            return null;
        }

        return [$min ?? '', $max ?? ''];
    }

    /**
     * @return ResolvedCondition[]
     */
    private function contains(string $column, string $text): array
    {
        return [new ResolvedCondition([$column], ResolvedCondition::OPERATOR_CONTAIN, $text)];
    }

    /**
     * @param array{0:int|float|string,1:int|float|string} $bounds
     * @return ResolvedCondition[]
     */
    private function range(string $column, array $bounds): array
    {
        return [new ResolvedCondition([$column], ResolvedCondition::OPERATOR_RANGE, $bounds)];
    }

    private function requireMainColumn(int $qid): string
    {
        $column = $this->map->getMainColumn($qid);
        if ($column === null) {
            throw new InvalidArgumentException("Question $qid has no answer column to filter on.");
        }

        return $column;
    }

    /**
     * The column of the subquestion this row targets.
     *
     * A row with a value but no subquestion is rejected rather than dropped: we
     * know what the user wants to match but not where, and quietly ignoring it
     * would return rows they asked to exclude.
     */
    private function requireSubquestion(int $qid, ResponseFilter $filter): string
    {
        $sqid = $filter->getSubquestion();
        if ($sqid === null) {
            throw new InvalidArgumentException("Question $qid needs a subquestion to filter on.");
        }

        $column = $this->map->getColumnBySqid($qid, $sqid);
        if ($column === null) {
            throw new InvalidArgumentException("Question $qid has no subquestion $sqid.");
        }

        return $column;
    }

    private function requireSubquestionColumn(int $qid, string $code): string
    {
        // Multiple-choice options are sent as subquestion ids: that is what the
        // modal's option list is built from.
        $column = ctype_digit($code)
            ? $this->map->getColumnBySqid($qid, (int) $code)
            : null;

        if ($column === null) {
            throw new InvalidArgumentException("Question $qid has no option '$code'.");
        }

        return $column;
    }

    private function requireOtherColumn(int $qid): string
    {
        foreach ($this->map->getColumns($qid) as $column) {
            if (($column['aid'] ?? null) === self::OTHER_AID) {
                return (string) $column['fieldname'];
            }
        }

        throw new InvalidArgumentException("Question $qid has no 'Other' option.");
    }
}
