<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

use InvalidArgumentException;

/**
 * Resolves `source: "participant"` rows — filters on who answered rather than
 * on what they answered.
 *
 * Which attributes exist depends on the survey, so the caller passes them in —
 * the same arrangement as {@see QuestionColumnMap}, and for the same reason: a
 * filter must never reach a column the survey does not have.
 */
class ParticipantResolver
{
    /**
     * The relation on SurveyDynamic that links a response to its participant.
     * Also the alias its table gets in the query.
     */
    public const RELATION = 'tokens';

    /** Column kinds, which decide how a value is compared. */
    public const KIND_TEXT = 'text';
    public const KIND_INTEGER = 'integer';
    public const KIND_OTHER = 'other';
    /** Stored as ciphertext, so only the whole value can be matched. */
    public const KIND_ENCRYPTED = 'encrypted';

    /** @var array<string,true> Attribute columns this survey has, lowercased. */
    private array $allowed;

    /** @var array<string,string> Column kind per lowercased column; text when absent. */
    private array $kinds;

    /** @var bool Whether the survey is anonymized, which refuses every participant filter. */
    private bool $anonymized;

    /**
     * @param array<int,string> $attributes Participant columns of this survey.
     *     Empty when the survey has no participant table at all.
     * @param bool $anonymized Anonymized responses are not linked to
     *     participants (no token column), and must not be filtered by them.
     * @param array<string,string> $kinds KIND_* per column; columns left out
     *     are treated as text.
     */
    public function __construct(array $attributes, bool $anonymized = false, array $kinds = [])
    {
        $this->anonymized = $anonymized;
        $this->allowed = [];
        foreach ($attributes as $attribute) {
            $this->allowed[strtolower((string) $attribute)] = true;
        }
        $this->kinds = array_change_key_case($kinds, CASE_LOWER);
    }

    /**
     * The participant columns of a survey, or none when it has no participant
     * table; in which case a participant filter cannot be honoured and is
     * refused rather than ignored.
     *
     * Read from the table itself so custom attributes need no separate lookup.
     */
    public static function fromSurvey(?\Survey $survey): self
    {
        if (empty($survey) || !$survey->hasTokensTable) {
            return new self([]);
        }

        if ($survey->isAnonymized) {
            return new self([], true);
        }

        $table = \App()->db->schema->getTable($survey->tokensTableName);
        if ($table === null) {
            return new self([]);
        }

        $kinds = [];
        foreach ($table->columns as $name => $column) {
            $kinds[$name] = self::kindOf($column);
        }
        foreach ($survey->getAllEncryptedAttributes($survey->sid, 'TokenDynamic') as $name) {
            $kinds[$name] = self::KIND_ENCRYPTED;
        }

        return new self(array_keys($table->columns), false, $kinds);
    }

    /**
     * Text columns are matched with LIKE; PostgreSQL has no LIKE for integers
     * or dates, so those need a different comparison or none at all.
     */
    private static function kindOf(\CDbColumnSchema $column): string
    {
        if ($column->type === 'integer') {
            return self::KIND_INTEGER;
        }

        return preg_match('/char|text/i', (string) $column->dbType) === 1
            ? self::KIND_TEXT
            : self::KIND_OTHER;
    }

    public function resolve(ResponseFilter $filter): ResolvedFilter
    {
        if ($filter->getSource() !== ResponseFilter::SOURCE_PARTICIPANT) {
            throw new InvalidArgumentException('ParticipantResolver only resolves participant filters.');
        }

        $value = $filter->getValue();
        if ($value === null || trim($value) === '') {
            return new ResolvedFilter($filter->getJoin(), []);
        }

        $column = $this->requireAttribute((string) $filter->getAttribute());
        $value = trim($value);

        switch ($this->kinds[$column] ?? self::KIND_TEXT) {
            case self::KIND_INTEGER:
                if (preg_match('/^-?\d+$/', $value) !== 1) {
                    throw new InvalidArgumentException("Participant attribute '$column' needs a whole number.");
                }
                $operator = ResolvedCondition::OPERATOR_EQUAL;
                $value = (int) $value;
                break;
            case self::KIND_TEXT:
                $operator = ResolvedCondition::OPERATOR_CONTAIN;
                break;
            case self::KIND_ENCRYPTED:
                $operator = ResolvedCondition::OPERATOR_EQUAL;
                $value = $this->encryptValue($value);
                break;
            default:
                throw new InvalidArgumentException("Participant attribute '$column' cannot be filtered.");
        }

        return new ResolvedFilter($filter->getJoin(), [
            new ResolvedCondition(
                [$column],
                $operator,
                $value,
                self::RELATION
            ),
        ]);
    }

    protected function encryptValue(string $value): string
    {
        return (string) \LSActiveRecord::encryptSingle($value);
    }

    /**
     * Wrap a condition on participant columns so it filters responses.
     *
     * @param string $tokenColumn The responses' token column, qualified when
     *     the query joins other tables that carry one.
     */
    public static function throughSubquery(int $surveyId, string $fragment, string $tokenColumn = 'token'): string
    {
        $db = \App()->db;
        $token = $db->quoteColumnName($tokenColumn);
        $relationToken = $db->quoteColumnName(self::RELATION . '.token');

        return "$token IN (SELECT $relationToken FROM {{tokens_$surveyId}} " . self::RELATION
            . " WHERE $fragment)";
    }

    /**
     * Turn the attribute the modal sent into a column of this survey's
     * participant table.
     */
    private function requireAttribute(string $attribute): string
    {
        $column = strtolower(trim($attribute));

        // {TOKEN:EMAIL} -> email
        if (preg_match('/^\{token:([a-z0-9_]+)\}$/', $column, $matches) === 1) {
            $column = $matches[1];
        }

        if ($this->anonymized) {
            throw new InvalidArgumentException(
                'This survey is anonymized, so it cannot be filtered by participant.'
            );
        }

        if ($this->allowed === []) {
            throw new InvalidArgumentException(
                'This survey has no participant table, so it cannot be filtered by participant.'
            );
        }

        if (!isset($this->allowed[$column])) {
            throw new InvalidArgumentException("Participants have no attribute '$attribute'.");
        }

        return $column;
    }
}
