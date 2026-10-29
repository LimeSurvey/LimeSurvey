<?php

namespace LimeSurvey\Models\Services\SurveyStatistics;

use InvalidArgumentException;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\DateProcessor;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use LSActiveRecord;
use Question;
use QuestionAttribute;

/**
 * Every entered value of a Date/time question with its count, grouped by day
 * and then by time, under the Statistics filters.
 */
class DateTimeValues
{
    private const VALUE_PATTERN = '/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?/';

    /**
     * @param \LimeSurvey\Models\Services\ResponseFilters\ResolvedFilter[] $resolvedFilters
     *     The filterSet the user built in the filter modal, resolved.
     * @param bool $decrypt Asked for explicitly, and only for an encrypted
     *     question, so its answers are never decrypted by accident.
     * @throws InvalidArgumentException When the question is no date question
     *                                  of the survey, or $decrypt doesn't match
     *                                  whether it is encrypted.
     */
    public function get(
        int $surveyId,
        int $qid,
        ?StatisticsResponseFilters $filters = null,
        array $resolvedFilters = [],
        bool $decrypt = false
    ): array {
        $question = $this->findDateQuestion($surveyId, $qid);
        $encrypted = DateProcessor::isEncrypted($question);
        if ($encrypted && !$decrypt) {
            throw new InvalidArgumentException('Encrypted date answers have to be decrypted first.');
        }
        if (!$encrypted && $decrypt) {
            throw new InvalidArgumentException("Question {$qid} is not encrypted.");
        }

        $batch = new ResponseAggregateBatch($surveyId, $filters, $resolvedFilters);
        $field = 'Q' . $qid;

        return self::groupByDay(
            DateProcessor::hasTime($question),
            $encrypted
                ? self::countValues($batch->eachValue($field), [LSActiveRecord::class, 'decryptSingle'])
                : $batch->groupedValues($field)
        );
    }

    /**
     * Count values after decoding each one; empty results are skipped.
     *
     * @param iterable<string> $values
     * @param callable(string): string $decode
     * @return array<string, int> decoded value => count
     */
    public static function countValues(iterable $values, callable $decode): array
    {
        $counts = [];
        foreach ($values as $value) {
            $decoded = trim((string)$decode($value));
            if ($decoded !== '') {
                $counts[$decoded] = ($counts[$decoded] ?? 0) + 1;
            }
        }

        return $counts;
    }

    /**
     * Shape [stored value => count] into the day => time => count response.
     * Values that are no real date are skipped.
     *
     * @param array<string, int> $valueCounts
     */
    public static function groupByDay(bool $hasTime, array $valueCounts): array
    {
        $days = [];
        $total = 0;
        foreach ($valueCounts as $value => $count) {
            $parts = self::split((string)$value);
            if ($parts === null) {
                continue;
            }
            [$day, $time] = $parts;
            $days[$day][$time] = ($days[$day][$time] ?? 0) + (int)$count;
            $total += (int)$count;
        }

        ksort($days);
        foreach ($days as &$times) {
            ksort($times);
        }
        unset($times);

        return ['hasTime' => $hasTime, 'total' => $total, 'days' => $days];
    }

    /**
     * Day and time of a stored value: datetime columns give 'Y-m-d H:i:s'
     * (MSSQL adds fractions), decrypted answers 'Y-m-d H:i'.
     *
     * @return array{0: string, 1: string}|null ['Y-m-d', 'H:i:s' or '']
     */
    private static function split(string $value): ?array
    {
        if (preg_match(self::VALUE_PATTERN, trim($value), $parts) !== 1) {
            return null;
        }
        if (!checkdate((int)$parts[2], (int)$parts[3], (int)$parts[1])) {
            return null;
        }

        $day = "$parts[1]-$parts[2]-$parts[3]";
        $time = isset($parts[4]) && $parts[4] !== ''
            ? $parts[4] . ':' . $parts[5] . ':' . ($parts[6] ?? '00')
            : '';

        return [$day, $time];
    }

    /**
     * @return array Question data in the shape DateProcessor expects.
     * @throws InvalidArgumentException
     */
    private function findDateQuestion(int $surveyId, int $qid): array
    {
        $question = Question::model()->findByAttributes([
            'qid' => $qid,
            'sid' => $surveyId,
            'parent_qid' => 0,
        ]);
        if ($question === null || $question->type !== Question::QT_D_DATE) {
            throw new InvalidArgumentException("Question {$qid} is no date question of survey {$surveyId}.");
        }

        $dateFormat = QuestionAttribute::model()->findByAttributes([
            'qid' => $qid,
            'attribute' => 'date_format',
        ]);

        return [
            'encrypted' => $question->encrypted,
            'attributes' => ['date_format' => $dateFormat->value ?? ''],
        ];
    }
}
