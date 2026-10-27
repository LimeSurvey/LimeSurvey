<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

class QuestionKind
{
    public const ANSWERS = 'answers';
    public const TEXT = 'text';
    public const NUMBER = 'number';
    public const DATE = 'date';
    public const SUB_TEXT = 'subText';
    public const SUB_NUMBER = 'subNumber';
    public const ARRAY_SCALE = 'arrayScale';
    public const ARRAY_DUAL = 'arrayDual';
    public const ARRAY_GRID = 'arrayGrid';
    public const RANKING = 'ranking';
    public const FILE_UPLOAD = 'fileUpload';

    /** Short, long and huge free text. */
    private const TEXT_TYPES = ['S', 'T', 'U'];

    /** Multiple short text (contains) and multiple numerical (min/max). */
    private const SUBQUESTION_TYPES = [
        'Q' => self::SUB_TEXT,
        'K' => self::SUB_NUMBER,
    ];

    /** Array types: row x column, sometimes with a value in the cell. */
    private const ARRAY_TYPES = [
        'A' => self::ARRAY_SCALE,
        'B' => self::ARRAY_SCALE,
        'C' => self::ARRAY_SCALE,
        'E' => self::ARRAY_SCALE,
        'F' => self::ARRAY_SCALE,
        'H' => self::ARRAY_SCALE,
        '1' => self::ARRAY_DUAL,
        ':' => self::ARRAY_GRID,
        ';' => self::ARRAY_GRID,
    ];

    /**
     * Multiple choice, plain and with comments. Filtered like an answer
     * question, but each option is its own column rather than one column
     * holding the chosen code.
     */
    private const MULTIPLE_CHOICE_TYPES = ['M', 'P'];

    /**
     * Types that hold no answer to filter on: X is display-only text and *
     * is a computed equation. The modal leaves both out of the question list.
     */
    private const EXCLUDED_TYPES = ['X', '*'];

    public static function fromType(string $type): string
    {
        if ($type === 'N') {
            return self::NUMBER;
        }
        if ($type === 'D') {
            return self::DATE;
        }
        if (in_array($type, self::TEXT_TYPES, true)) {
            return self::TEXT;
        }
        if (isset(self::SUBQUESTION_TYPES[$type])) {
            return self::SUBQUESTION_TYPES[$type];
        }
        if (isset(self::ARRAY_TYPES[$type])) {
            return self::ARRAY_TYPES[$type];
        }
        if ($type === 'R') {
            return self::RANKING;
        }
        if ($type === '|') {
            return self::FILE_UPLOAD;
        }

        // Everything else is answer-based: radio lists, dropdowns, yes/no,
        // gender, 5 point choice, multiple choice, and any future type that
        // stores a picked code.
        return self::ANSWERS;
    }

    public static function isMultipleChoice(string $type): bool
    {
        return in_array($type, self::MULTIPLE_CHOICE_TYPES, true);
    }

    public static function isExcluded(string $type): bool
    {
        return in_array($type, self::EXCLUDED_TYPES, true);
    }
}
