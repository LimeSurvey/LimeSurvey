<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

use InvalidArgumentException;

/**
 * One entry of the `filterSet` request param: a single row of the condition
 * designer, validated into a typed object.
 *
 * The client sends what the user picked (a question and its selections, a
 * response-data field, or a participant attribute). It never sends how to
 * filter: the question kind that decides which columns a selection maps to is
 * derived server-side from the question type, so a stale or forged value can
 * not change which columns are searched.
 *
 * Validation here is structural only — that the shape is well formed and the
 * enums are known. Whether a qid actually belongs to the survey is checked by
 * the resolver, which has the field map.
 */
class ResponseFilter
{
    public const SOURCE_QUESTION = 'question';
    public const SOURCE_SURVEY_DATA = 'surveyData';
    public const SOURCE_PARTICIPANT = 'participant';

    public const JOIN_AND = 'and';
    public const JOIN_OR = 'or';

    /** Response-data fields, mapping to real response table columns. */
    public const FIELD_RESPONSE_ID = 'id';
    public const FIELD_SEED = 'seed';
    public const FIELD_SUBMIT_DATE = 'submitdate';
    public const FIELD_LAST_ACTION = 'datestamp';
    public const FIELD_COMPLETED = 'completed';
    public const FIELD_LANGUAGE = 'startlanguage';

    public const INCLUDED_ALL = 'all';
    public const INCLUDED_COMPLETE = 'complete';
    public const INCLUDED_INCOMPLETE = 'incomplete';

    public const FILE_UPLOADED_YES = 'Y';
    public const FILE_UPLOADED_NO = 'N';

    private const SOURCES = [
        self::SOURCE_QUESTION,
        self::SOURCE_SURVEY_DATA,
        self::SOURCE_PARTICIPANT,
    ];

    private const JOINS = [self::JOIN_AND, self::JOIN_OR];

    private const FIELDS = [
        self::FIELD_RESPONSE_ID,
        self::FIELD_SEED,
        self::FIELD_SUBMIT_DATE,
        self::FIELD_LAST_ACTION,
        self::FIELD_COMPLETED,
        self::FIELD_LANGUAGE,
    ];

    private const INCLUDED_VALUES = [
        self::INCLUDED_ALL,
        self::INCLUDED_COMPLETE,
        self::INCLUDED_INCOMPLETE,
    ];

    private const FILE_UPLOADED_VALUES = [
        self::FILE_UPLOADED_YES,
        self::FILE_UPLOADED_NO,
    ];

    /**
     * Every key the contract accepts. Anything else is rejected rather than
     * ignored, so a client typo ("textValue" for "text") fails loudly instead
     * of silently widening the result set.
     */
    private const ALLOWED_KEYS = [
        'join', 'source',
        // question
        'qid', 'answerCodes', 'text', 'numberMin', 'numberMax', 'dateFrom',
        'dateTo', 'subquestion', 'row', 'column', 'column2', 'fileUploaded',
        // surveyData
        'field', 'included', 'languages',
        // participant
        'attribute', 'value',
    ];

    private string $join;
    private string $source;

    /** @var array<string,mixed> Validated payload, keyed by contract name. */
    private array $payload;

    /**
     * @param array<string,mixed> $payload
     */
    private function __construct(string $join, string $source, array $payload)
    {
        $this->join = $join;
        $this->source = $source;
        $this->payload = $payload;
    }

    /**
     * @param mixed $raw
     * @param int $index Position in the filterSet, used in error messages.
     * @throws InvalidArgumentException
     */
    public static function fromArray($raw, int $index): self
    {
        $at = "filterSet[$index]";

        if (!is_array($raw)) {
            throw new InvalidArgumentException("$at must be an object.");
        }

        $unknown = array_diff(array_keys($raw), self::ALLOWED_KEYS);
        if ($unknown !== []) {
            throw new InvalidArgumentException(
                "$at has unknown propert" . (count($unknown) > 1 ? 'ies' : 'y')
                . ': ' . implode(', ', $unknown) . '.'
            );
        }

        $join = $raw['join'] ?? self::JOIN_AND;
        if (!in_array($join, self::JOINS, true)) {
            throw new InvalidArgumentException(
                "$at has an unknown join. Expected one of: " . implode(', ', self::JOINS) . '.'
            );
        }

        $source = $raw['source'] ?? null;
        if (!in_array($source, self::SOURCES, true)) {
            throw new InvalidArgumentException(
                "$at has an unknown source. Expected one of: " . implode(', ', self::SOURCES) . '.'
            );
        }

        $payload = $raw;
        unset($payload['join'], $payload['source']);

        switch ($source) {
            case self::SOURCE_QUESTION:
                self::validateQuestion($payload, $at);
                break;
            case self::SOURCE_SURVEY_DATA:
                self::validateSurveyData($payload, $at);
                break;
            case self::SOURCE_PARTICIPANT:
                self::validateParticipant($payload, $at);
                break;
        }

        return new self($join, $source, $payload);
    }

    /**
     * @param array<string,mixed> $payload
     */
    private static function validateQuestion(array $payload, string $at): void
    {
        $qid = $payload['qid'] ?? null;
        if (!is_int($qid) && !(is_string($qid) && ctype_digit($qid))) {
            throw new InvalidArgumentException("$at requires a numeric qid.");
        }
        if ((int) $qid <= 0) {
            throw new InvalidArgumentException("$at requires a positive qid.");
        }

        if (isset($payload['answerCodes']) && !is_array($payload['answerCodes'])) {
            throw new InvalidArgumentException("$at answerCodes must be an array.");
        }

        if (
            isset($payload['fileUploaded'])
            && !in_array($payload['fileUploaded'], self::FILE_UPLOADED_VALUES, true)
        ) {
            throw new InvalidArgumentException(
                "$at has an unknown fileUploaded value. Expected one of: "
                . implode(', ', self::FILE_UPLOADED_VALUES) . '.'
            );
        }
    }

    /**
     * @param array<string,mixed> $payload
     */
    private static function validateSurveyData(array $payload, string $at): void
    {
        $field = $payload['field'] ?? null;
        if (!in_array($field, self::FIELDS, true)) {
            throw new InvalidArgumentException(
                "$at has an unknown field. Expected one of: " . implode(', ', self::FIELDS) . '.'
            );
        }

        if (
            isset($payload['included'])
            && !in_array($payload['included'], self::INCLUDED_VALUES, true)
        ) {
            throw new InvalidArgumentException(
                "$at has an unknown included value. Expected one of: "
                . implode(', ', self::INCLUDED_VALUES) . '.'
            );
        }

        if (isset($payload['languages']) && !is_array($payload['languages'])) {
            throw new InvalidArgumentException("$at languages must be an array.");
        }
    }

    /**
     * @param array<string,mixed> $payload
     */
    private static function validateParticipant(array $payload, string $at): void
    {
        $attribute = $payload['attribute'] ?? null;
        if (!is_string($attribute) || trim($attribute) === '') {
            throw new InvalidArgumentException("$at requires an attribute.");
        }
    }

    public function getJoin(): string
    {
        return $this->join;
    }

    /** Whether this entry ORs onto the previous one (ignored on the first). */
    public function isOr(): bool
    {
        return $this->join === self::JOIN_OR;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getQid(): ?int
    {
        return isset($this->payload['qid']) ? (int) $this->payload['qid'] : null;
    }

    /** @return array<int,string> */
    public function getAnswerCodes(): array
    {
        return array_values(array_map('strval', $this->payload['answerCodes'] ?? []));
    }

    public function getText(): ?string
    {
        return isset($this->payload['text']) ? (string) $this->payload['text'] : null;
    }

    /** @return int|float|null */
    public function getNumberMin()
    {
        return self::toNumber($this->payload['numberMin'] ?? null);
    }

    /** @return int|float|null */
    public function getNumberMax()
    {
        return self::toNumber($this->payload['numberMax'] ?? null);
    }

    public function getDateFrom(): ?string
    {
        return isset($this->payload['dateFrom']) ? (string) $this->payload['dateFrom'] : null;
    }

    public function getDateTo(): ?string
    {
        return isset($this->payload['dateTo']) ? (string) $this->payload['dateTo'] : null;
    }

    public function getSubquestion(): ?int
    {
        return isset($this->payload['subquestion']) ? (int) $this->payload['subquestion'] : null;
    }

    public function getRow(): ?int
    {
        return isset($this->payload['row']) ? (int) $this->payload['row'] : null;
    }

    /**
     * What "column" holds depends on the question: an answer code for the
     * array kinds that pick from a scale, a subquestion id for the grid kinds
     * whose columns are subquestions. It stays a raw string here, and the
     * resolver — which knows the kind — reads it as whichever it is.
     */
    public function getColumn(): ?string
    {
        return isset($this->payload['column']) ? (string) $this->payload['column'] : null;
    }

    /** The second scale of a dual-scale question; an answer code. */
    public function getColumn2(): ?string
    {
        return isset($this->payload['column2']) ? (string) $this->payload['column2'] : null;
    }

    public function getFileUploaded(): ?string
    {
        return $this->payload['fileUploaded'] ?? null;
    }

    public function getField(): ?string
    {
        return $this->payload['field'] ?? null;
    }

    public function getIncluded(): string
    {
        return $this->payload['included'] ?? self::INCLUDED_ALL;
    }

    /** @return array<int,string> */
    public function getLanguages(): array
    {
        return array_values(array_map('strval', $this->payload['languages'] ?? []));
    }

    public function getAttribute(): ?string
    {
        return $this->payload['attribute'] ?? null;
    }

    public function getValue(): ?string
    {
        return isset($this->payload['value']) ? (string) $this->payload['value'] : null;
    }

    /**
     * @param mixed $value
     * @return int|float|null
     */
    private static function toNumber($value)
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }
        return $value + 0;
    }
}
