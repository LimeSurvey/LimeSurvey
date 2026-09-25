<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

use InvalidArgumentException;

/**
 * Question id -> the response columns that store its answers.
 *
 * A question is one thing to the user and one row in the filter modal, but in
 * storage it can be one column, or one per option, or one per row-and-column
 * pair. This turns the survey's field map into the lookups the resolvers need,
 * so they can ask "which column holds this" instead of re-deriving column
 * naming rules.
 *
 * Built from `ResponseMappingTrait::getQuestionFieldMap()`, which already
 * carries the per-column `qid`, `sqid`, `aid`, `scaleid` and `type`. Taking a
 * plain array rather than a survey keeps this testable without a database.
 */
class QuestionColumnMap
{
    /** @var array<int,array{type:string,columns:array<int,array>}> */
    private array $questions;

    /**
     * @param array<int,array{type:string,columns:array<int,array>}> $questions
     */
    private function __construct(array $questions)
    {
        $this->questions = $questions;
    }

    /**
     * @param array<string,array> $questionFieldMap Keyed by column name.
     */
    public static function fromQuestionFieldMap(array $questionFieldMap): self
    {
        $questions = [];

        foreach ($questionFieldMap as $fieldname => $column) {
            $qid = isset($column['qid']) ? (int) $column['qid'] : 0;
            if ($qid <= 0) {
                continue;
            }

            // The field map keys by column name; entries also carry it, but
            // fall back to the key so a trimmed-down map still works.
            $column['fieldname'] = $column['fieldname'] ?? $fieldname;

            // createFieldMap() writes scale_id; the responses transformer
            // renames it to scaleid on the way out. Accept either, so the
            // statistics side can pass the raw map straight in.
            if (!isset($column['scaleid']) && isset($column['scale_id'])) {
                $column['scaleid'] = $column['scale_id'];
            }

            if (!isset($questions[$qid])) {
                $questions[$qid] = [
                    'type' => (string) ($column['type'] ?? ''),
                    'columns' => [],
                ];
            }

            $questions[$qid]['columns'][] = $column;
        }

        return new self($questions);
    }

    public function has(int $qid): bool
    {
        return isset($this->questions[$qid]);
    }

    /**
     * @throws InvalidArgumentException when the qid is not part of this survey.
     */
    public function getType(int $qid): string
    {
        if (!$this->has($qid)) {
            throw new InvalidArgumentException("Question $qid is not part of this survey.");
        }

        return $this->questions[$qid]['type'];
    }

    /**
     * Every column of the question, in field-map order.
     *
     * @return array<int,array>
     */
    public function getColumns(int $qid): array
    {
        return $this->questions[$qid]['columns'] ?? [];
    }

    /**
     * The column that holds the answer itself, for questions that have just
     * one. Skips the extras a question can carry alongside it — the "other"
     * text, a comment, a file count — which are identified by their `aid`.
     */
    public function getMainColumn(int $qid): ?string
    {
        foreach ($this->getColumns($qid) as $column) {
            if (self::isBlank($column['aid'] ?? null) && self::isBlank($column['sqid'] ?? null)) {
                return (string) $column['fieldname'];
            }
        }

        return null;
    }

    /**
     * The column belonging to one subquestion — a multiple-choice option, or a
     * row of a subquestion-based question.
     *
     * Comment columns of a "multiple choice with comments" question repeat the
     * subquestion's `aid` but carry no `sqid`, so they are never returned here.
     */
    public function getColumnBySqid(int $qid, int $sqid): ?string
    {
        foreach ($this->getColumns($qid) as $column) {
            if (isset($column['sqid']) && (int) $column['sqid'] === $sqid) {
                return (string) $column['fieldname'];
            }
        }

        return null;
    }

    /**
     * A column identified by its `aid` — the marker the field map puts on the
     * extras a question carries beside its answer: the free-text "other", a
     * file upload's count, or a ranking's rank position.
     */
    public function getColumnByAid(int $qid, string $aid): ?string
    {
        foreach ($this->getColumns($qid) as $column) {
            if (isset($column['aid']) && (string) $column['aid'] === $aid) {
                return (string) $column['fieldname'];
            }
        }

        return null;
    }

    /**
     * The column for one row of a dual-scale question.
     *
     * Both scales of a row share a subquestion and are told apart by their
     * scale id, which is also why their column names differ only by a '#0' or
     * '#1' suffix.
     */
    public function getColumnBySqidAndScale(int $qid, int $sqid, int $scale): ?string
    {
        foreach ($this->getColumns($qid) as $column) {
            if (
                isset($column['sqid'], $column['scaleid'])
                && (int) $column['sqid'] === $sqid
                && (int) $column['scaleid'] === $scale
            ) {
                return (string) $column['fieldname'];
            }
        }

        return null;
    }

    /**
     * The column for one cell of an array grid — the questions built from two
     * sets of subquestions, where every row/column pair gets a column of its
     * own.
     */
    public function getGridColumn(int $qid, int $rowSqid, int $columnSqid): ?string
    {
        foreach ($this->getColumns($qid) as $column) {
            if (!isset($column['sqid']) || (int) $column['sqid'] !== $rowSqid) {
                continue;
            }

            $fieldname = (string) $column['fieldname'];
            if (str_ends_with($fieldname, '_S' . $columnSqid)) {
                return $fieldname;
            }
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    private static function isBlank($value): bool
    {
        return $value === null || $value === '';
    }
}
