<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\Sorting;

use LimeSurvey\Helpers\SortHelper;

/**
 * Alphabetical order of the answer options of a question, as the response
 * table shows them: The answer text in the table language without HTML, or
 * the answer code when the text is missing. Image select questions show
 * images, so their options are ordered by the image file name.
 */
class AnswerLabelOrder
{
    /** Prefix of the question themes whose answer texts are image URLs */
    private const IMAGE_SELECT_THEME_PREFIX = 'image_select';

    private string $language;

    /**
     * @param string $language Language of the response table, which the answer texts are read in
     */
    public function __construct(string $language)
    {
        $this->language = $language;
    }

    /**
     * Rank of each answer code in alphabetical order. Answers with the same
     * text share a rank, so their responses are ordered like equal values.
     *
     * @param int $qid Question ID
     * @param int $scaleId Answer scale
     * @return array<string|int, int> Answer code => rank, starting at 1; empty if the question has no answers
     */
    public function getRanks(int $qid, int $scaleId = 0): array
    {
        $labels = $this->getLabels($qid, $scaleId);
        // Equal labels keep the order of their codes
        ksort($labels, SORT_STRING);
        SortHelper::getInstance($this->language)->asort($labels, SortHelper::SORT_STRING);

        $ranks = [];
        $rank = 0;
        $previous = null;
        foreach ($labels as $code => $label) {
            $comparable = mb_strtolower($label);
            if ($comparable !== $previous) {
                $rank++;
                $previous = $comparable;
            }
            $ranks[$code] = $rank;
        }
        return $ranks;
    }

    /**
     * Answer labels of a question as the response table shows them.
     *
     * @param int $qid Question ID
     * @param int $scaleId Answer scale
     * @return array<string|int, string> Answer code => label
     */
    private function getLabels(int $qid, int $scaleId): array
    {
        $db = \Yii::app()->db;
        $rows = $db->createCommand()
            ->select(['a.code', 'l.answer'])
            ->from('{{answers}} a')
            ->leftJoin('{{answer_l10ns}} l', 'l.aid = a.aid AND l.language = :language', [':language' => $this->language])
            ->where('a.qid = :qid AND a.scale_id = :scale', [':qid' => $qid, ':scale' => $scaleId])
            ->queryAll();
        $themeName = (string) $db->createCommand()
            ->select('question_theme_name')
            ->from('{{questions}}')
            ->where('qid = :qid', [':qid' => $qid])
            ->queryScalar();
        $isImageSelect = str_starts_with($themeName, self::IMAGE_SELECT_THEME_PREFIX);

        $labels = [];
        foreach ($rows as $row) {
            $text = (string) ($row['answer'] ?? '');
            $label = $isImageSelect ? self::imageName($text) : self::plainText($text);
            // The table shows the code when an answer has no text
            $labels[$row['code']] = $label !== '' ? $label : (string) $row['code'];
        }
        return $labels;
    }

    /**
     * Text without HTML tags and entities, like the response table shows it.
     *
     * @param string $html Answer text, which may contain HTML
     * @return string Plain, trimmed text
     */
    private static function plainText(string $html): string
    {
        return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * File name of the image of an image select option. The answer text is the
     * image URL; the name is taken like the question theme does it.
     *
     * @param string $text Answer text: The image URL, or HTML containing an image
     * @return string File name without query and fragment, empty if there is none
     */
    private static function imageName(string $text): string
    {
        $url = $text;
        if (preg_match('/<img\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/i', $text, $matches)) {
            $url = $matches[2];
        }
        $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $path = explode('#', explode('?', $url)[0])[0];
        $segments = explode('/', $path);
        return trim((string) end($segments));
    }
}
