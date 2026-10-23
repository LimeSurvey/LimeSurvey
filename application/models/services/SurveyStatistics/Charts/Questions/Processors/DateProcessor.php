<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

class DateProcessor extends AbstractQuestionProcessor
{
    #[\Override]
    public function process()
    {
        $this->rt();
        $field = $this->rt;
        $encrypted = self::isEncrypted($this->question);

        // Datetime columns must be counted in numeric-column mode: MySQL 8
        // rejects comparing them to ''. Encrypted answers are plain text.
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
     */
    public static function hasTime(array $question): bool
    {
        $format = (string)($question['attributes']['date_format'] ?? '');

        return preg_match('/[HM]/', $format) === 1;
    }

    public static function isEncrypted(array $question): bool
    {
        return ($question['encrypted'] ?? 'N') === 'Y';
    }

    /**
     * Deferred calendar data; null when no day was answered.
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
