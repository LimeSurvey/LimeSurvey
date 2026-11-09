<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

/**
 * Chart data of a Date/time question: how many answered it and, unless it is
 * encrypted, its answers per day for the calendar.
 */
class DateProcessor extends AbstractQuestionProcessor
{
    /**
     * Build the chart data of the question.
     *
     * @return array Title, the answered count and, for a plain question, the
     *     per-day calendar data
     */
    #[\Override]
    public function process()
    {
        $this->rt();
        $field = $this->rt;
        $encrypted = self::isEncrypted($this->question);

        $answered = $this->read($this->batch->countNonEmpty($field, !$encrypted));

        $item = [
            'key' => $this->question['title'],
            'title' => $this->question['question'],
            'value' => $answered,
        ];
        if (!$encrypted) {
            $item['stats'] = $this->daysOf($field);
        }

        return [
            'title' => $this->question['question'],
            'legend' => [],
            'data' => [$item],
            'total' => $answered,
        ];
    }

    /**
     * Whether the question's date format records a time.
     *
     * @param array $question Question data with its `attributes`
     * @return bool
     */
    public static function hasTime(array $question): bool
    {
        $format = (string)($question['attributes']['date_format'] ?? '');

        return preg_match('/[HM]/', $format) === 1;
    }

    /**
     * Whether the question's answers are stored encrypted.
     *
     * @param array $question Question data with its `encrypted` flag
     * @return bool
     */
    public static function isEncrypted(array $question): bool
    {
        return ($question['encrypted'] ?? 'N') === 'Y';
    }

    /**
     * Deferred calendar data; null when no day was answered.
     *
     * @param string $field The question's response column
     * @return callable(): ?array Earliest and latest day, and the count per day
     */
    private function daysOf(string $field): callable
    {
        $daysAlias = $this->batch->countByDay($field);

        return function () use ($daysAlias): ?array {
            $days = $this->batch->value($daysAlias);
            if (empty($days) || !is_array($days)) {
                return null;
            }

            return [
                'earliest' => array_key_first($days),
                'latest' => array_key_last($days),
                'days' => $days,
            ];
        };
    }
}
