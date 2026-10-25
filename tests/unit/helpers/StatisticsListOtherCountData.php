<?php

namespace ls\tests\unit\helpers;

use Yii;

/**
 * Responses and output parsing shared by the admin and public statistics count tests.
 */
class StatisticsListOtherCountData
{
    /**
     * Inserts completed responses with selected options, "Other" with and without other text,
     * empty answers and not displayed (NULL) answers.
     *
     * List question: A1 2x, "Other" 2x (with and without other text), no answer 3x, not displayed 2x.
     * Text question: answered 1x, no answer 2x, not displayed 6x.
     *
     * @param int $surveyId
     * @param \Question $listQuestion List question with "Other" option
     * @param \Question $textQuestion Short free text question
     * @return void
     */
    public static function insertResponses($surveyId, $listQuestion, $textQuestion)
    {
        $list = 'Q' . $listQuestion->qid;
        $text = 'Q' . $textQuestion->qid;
        $rows = [
            [$list => 'A1', $list . '_Cother' => null, $text => 'abc'],
            [$list => 'A1', $list . '_Cother' => null, $text => ''],
            [$list => '-oth-', $list . '_Cother' => 'other text', $text => ''],
            [$list => '-oth-', $list . '_Cother' => '', $text => null],
            [$list => '', $list . '_Cother' => null, $text => null],
            [$list => '', $list . '_Cother' => null, $text => null],
            [$list => '', $list . '_Cother' => null, $text => null],
            [$list => null, $list . '_Cother' => null, $text => null],
            [$list => null, $list . '_Cother' => null, $text => null],
        ];
        $dateStamp = date('Y-m-d H:i:s');
        foreach ($rows as $key => $row) {
            $rows[$key] = $row + ['submitdate' => $dateStamp, 'startdate' => $dateStamp, 'datestamp' => $dateStamp, 'startlanguage' => 'en', 'lastpage' => 1];
        }
        Yii::app()->db->schema->commandBuilder
            ->createMultipleInsertCommand("{{responses_$surveyId}}", $rows)
            ->execute();
    }

    /**
     * Returns the counts of a statistics summary table, keyed by the label of each row.
     *
     * @param string $html Statistics HTML output
     * @return int[]
     */
    public static function countsFromHtml($html)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        $counts = [];
        foreach ($dom->getElementsByTagName('tr') as $tableRow) {
            $cells = $tableRow->getElementsByTagName('td');
            if ($cells->length < 2) {
                continue;
            }
            $label = trim(preg_replace('/\s+/', ' ', $cells->item(0)->textContent));
            $count = trim($cells->item(1)->textContent);
            if (ctype_digit($count)) {
                $counts[$label] = (int) $count;
            }
        }
        return $counts;
    }
}
