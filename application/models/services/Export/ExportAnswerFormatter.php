<?php

namespace LimeSurvey\Models\Services\Export;

use Question;

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

    /**
     * Format a raw answer value to its display text,
     * matching the old export's "full answer" format.
     *
     * @param mixed $value Raw answer value from the database
     * @param string|null $type Question type character
     * @param string $fieldKey Full field key (e.g. "123X456X789SQ001")
     * @return mixed Formatted display value
     */
    public function formatFullAnswer($value, $type, $fieldKey)
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
}
