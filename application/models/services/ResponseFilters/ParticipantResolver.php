<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

use InvalidArgumentException;

/**
 * Resolves `source: "participant"` rows — filters on who answered rather than
 * on what they answered.
 *
 * Participant details live in the survey's own participant table, not in the
 * responses, so these conditions carry the relation that links the two and are
 * joined in when the query is built.
 *
 * The modal sends the attribute as an expression placeholder, `{TOKEN:EMAIL}`,
 * because that is the spelling the rest of LimeSurvey uses for participant
 * fields. Lowercasing the name inside the braces gives the column, for the
 * built-in fields and the custom `attribute_1`, `attribute_2`, ... alike.
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

    /** @var array<string,true> Attribute columns this survey has, lowercased. */
    private array $allowed;

    /**
     * @param array<int,string> $attributes Participant columns of this survey.
     *     Empty when the survey has no participant table at all.
     */
    public function __construct(array $attributes)
    {
        $this->allowed = [];
        foreach ($attributes as $attribute) {
            $this->allowed[strtolower((string) $attribute)] = true;
        }
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

        // A text box means "contains", the same as filtering a free-text
        // question, so a surname search need not be spelled in full.
        return new ResolvedFilter($filter->getJoin(), [
            new ResolvedCondition(
                [$column],
                ResolvedCondition::OPERATOR_CONTAIN,
                $value,
                self::RELATION
            ),
        ]);
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
