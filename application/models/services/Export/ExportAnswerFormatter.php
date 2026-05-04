<?php

namespace LimeSurvey\Models\Services\Export;

use LimeSurvey\Models\Services\SurveyAnswerCache;
use Question;
use QuestionAttribute;

class ExportAnswerFormatter
{
    /**
     * Maps question types to their answer code => translation key mappings.
     */
    private const ANSWER_CODE_MAPS = [
        Question::QT_Y_YES_NO_RADIO => ['Y' => 'Yes', 'N' => 'No'],
        Question::QT_G_GENDER => ['M' => 'Male', 'F' => 'Female'],
        Question::QT_C_ARRAY_YES_UNCERTAIN_NO => ['Y' => 'Yes', 'N' => 'No', 'U' => 'Uncertain'],
        Question::QT_E_ARRAY_INC_SAME_DEC => ['I' => 'Increase', 'S' => 'Same', 'D' => 'Decrease'],
    ];

    /**
     * Types where unmatched codes return raw value instead of N/A.
     */
    private const RAW_FALLBACK_TYPES = [
        Question::QT_C_ARRAY_YES_UNCERTAIN_NO,
        Question::QT_E_ARRAY_INC_SAME_DEC,
    ];

    /** @var SurveyAnswerCache */
    private $answerCache;

    public function __construct(SurveyAnswerCache $answerCache)
    {
        $this->answerCache = $answerCache;
    }

    /**
     * Load answer data for a survey into the shared cache.
     *
     * @param int $surveyId
     * @param string $language
     */
    public function loadAnswers($surveyId, $language)
    {
        $this->answerCache->load($surveyId, $language);
    }

    /**
     * Format a raw answer value to its display text,
     * matching the old export's "full answer" format.
     *
     * @param mixed $value Raw answer value from the database
     * @param string|null $type Question type character
     * @param string $fieldKey Full field key (e.g. "123X456X789SQ001")
     * @param int|string|null $qid Question ID for answer label lookup
     * @return mixed Formatted display value
     */
    public function formatFullAnswer($value, $type, $fieldKey, $qid = null)
    {
        if ($type === null) {
            return $value;
        }

        if (
            $type === Question::QT_M_MULTIPLE_CHOICE
            || $type === Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS
        ) {
            return $this->formatMultipleChoiceAnswer($value, $fieldKey);
        }

        if (isset(self::ANSWER_CODE_MAPS[$type])) {
            return $this->mapCodeToLabel($value, $type);
        }

        if (
            $type === Question::QT_L_LIST
            || $type === Question::QT_EXCLAMATION_LIST_DROPDOWN
        ) {
            return $this->formatListAnswer($value, $fieldKey, $qid);
        }

        if ($type === Question::QT_O_LIST_WITH_COMMENT) {
            return $this->formatListWithCommentAnswer($value, $fieldKey, $qid);
        }

        if (
            $type === Question::QT_F_ARRAY
            || $type === Question::QT_H_ARRAY_COLUMN
        ) {
            return $this->lookupAnswerLabel($qid, 0, $value) ?? '';
        }

        if ($type === Question::QT_1_ARRAY_DUAL) {
            return $this->formatArrayDualAnswer($value, $fieldKey, $qid);
        }

        if ($type === Question::QT_R_RANKING) {
            return $this->lookupAnswerLabel($qid, 0, $value) ?? $value;
        }

        if (
            $type === Question::QT_N_NUMERICAL
            || $type === Question::QT_K_MULTIPLE_NUMERICAL
        ) {
            return $this->formatNumericAnswer($value, $qid);
        }

        return $value;
    }

    /**
     * Format multiple choice (M/P) answer values.
     *
     * @param mixed $value
     * @param string $fieldKey
     * @return mixed
     */
    private function formatMultipleChoiceAnswer($value, $fieldKey)
    {
        if (str_ends_with($fieldKey, 'other') || str_ends_with($fieldKey, 'comment')) {
            return $value;
        }
        if ($value === 'Y') {
            return gT('Yes');
        }
        if ($value === 'N' || $value === '') {
            return gT('No');
        }
        return gT('N/A');
    }

    /**
     * Map an answer code to its translated label using the type's code map.
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    private function mapCodeToLabel($value, $type)
    {
        $map = self::ANSWER_CODE_MAPS[$type];
        if (isset($map[$value])) {
            return gT($map[$value]);
        }
        return in_array($type, self::RAW_FALLBACK_TYPES) ? $value : gT('N/A');
    }

    /**
     * Format list-type (L, !) answer values.
     *
     * @param mixed $value
     * @param string $fieldKey
     * @param int|string|null $qid
     * @return mixed
     */
    private function formatListAnswer($value, $fieldKey, $qid)
    {
        if (str_ends_with($fieldKey, 'other')) {
            return $value;
        }
        if ($value === '-oth-') {
            return gT('Other');
        }
        return $this->lookupAnswerLabel($qid, 0, $value) ?? $value;
    }

    /**
     * Format list with comment (O) answer values.
     *
     * @param mixed $value
     * @param string $fieldKey
     * @param int|string|null $qid
     * @return mixed
     */
    private function formatListWithCommentAnswer($value, $fieldKey, $qid)
    {
        if (str_ends_with($fieldKey, 'comment')) {
            return $value;
        }
        $label = $this->lookupAnswerLabel($qid, 0, $value);
        return ($label !== null && $label !== '') ? $label : $value;
    }

    /**
     * Format array dual scale (1) answer values.
     *
     * @param mixed $value
     * @param string $fieldKey
     * @param int|string|null $qid
     * @return mixed
     */
    private function formatArrayDualAnswer($value, $fieldKey, $qid)
    {
        $scaleId = (mb_substr($fieldKey, -1) === '0') ? 0 : 1;
        return $this->lookupAnswerLabel($qid, $scaleId, $value) ?? '';
    }

    /**
     * Format numeric (N, K) answer values — trim trailing zeros.
     *
     * @param mixed $value
     * @param int|string|null $qid
     * @return mixed
     */
    private function formatNumericAnswer($value, $qid)
    {
        if (is_null($value) || trim((string)$value) === '') {
            return $value;
        }
        $formatted = (string)$value;
        if ($formatted !== '' && $formatted[0] === '.') {
            $formatted = '0' . $formatted;
        }
        if (strpos($formatted, '.') !== false) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }
        if ($qid !== null) {
            try {
                $qidAttributes = QuestionAttribute::model()->getQuestionAttributes((int)$qid);
                if (!empty($qidAttributes['num_value_int_only'])) {
                    $formatted = number_format((float)$formatted, 0, '', '');
                }
            } catch (\Throwable $e) {
            }
        }
        return $formatted;
    }

    /**
     * Look up an answer label from the cached answers.
     *
     * @param int|string|null $qid
     * @param int $scaleId
     * @param mixed $code
     * @return string|null
     */
    private function lookupAnswerLabel($qid, $scaleId, $code)
    {
        return $this->answerCache->getLabel($qid, $scaleId, $code);
    }
}
