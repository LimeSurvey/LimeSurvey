<?php

namespace LimeSurvey\Models\Services\Export;

/**
 * Shared heading formatting logic for export writers.
 *
 * Replicates the legacy Writer::getFullHeading() format which produces
 * headings like "Question text [Subquestion]" for questions with subquestions.
 */
trait ExportHeadingTrait
{
    /**
     * Build a question heading matching the legacy export "full" heading format.
     *
     * @param array $question Question field map entry
     * @return string
     */
    private function buildQuestionHeading(array $question): string
    {
        $questionText = $this->cleanHeadingText($question['question'] ?? '');

        $hasSubQuestion = isset($question['aid']) && $question['aid'] !== '';
        if ($hasSubQuestion) {
            $subHeadingParts = [];
            if (!empty($question['subquestion'])) {
                $subHeadingParts[] = '[' . $this->cleanHeadingText($question['subquestion']) . ']';
            }
            if (!empty($question['subquestion1'])) {
                $subHeadingParts[] = '[' . $this->cleanHeadingText($question['subquestion1']) . ']';
            }
            if (!empty($question['subquestion2'])) {
                $subHeadingParts[] = '[' . $this->cleanHeadingText($question['subquestion2']) . ']';
            }
            if (!empty($subHeadingParts)) {
                $questionText .= ' ' . implode('', $subHeadingParts);
            }
        }
        if (!empty($question['scale'])) {
            $questionText .= ' [' . $this->cleanHeadingText($question['scale']) . ']';
        }

        $questionText = trim($questionText);
        if ($questionText === '') {
            $questionText = $question['title'] ?? "Q{$question['qid']}";
        }

        return $questionText;
    }

    /**
     * Strip HTML tags and decode entities from text.
     *
     * @param string $text
     * @return string
     */
    private function cleanHeadingText(string $text): string
    {
        return strip_tags(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
    }
}
