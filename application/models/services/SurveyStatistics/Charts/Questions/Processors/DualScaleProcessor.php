<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use Yii;

class DualScaleProcessor extends AbstractQuestionProcessor
{
    public function rt(): void
    {
        $this->rt = $this->surveyId . 'X' . $this->question['gid'] . 'X' . $this->question['qid'];
    }

    public function process()
    {
        $this->rt();
        $db = Yii::app()->db;

        $scaleName = [
            0 => $this->question['attributes']['dualscale_headerA'] ?? 'Scale A',
            1 => $this->question['attributes']['dualscale_headerB'] ?? 'Scale B',
        ];

        $answersByScale = [0 => [], 1 => []];
        foreach ($this->answers ?? [] as $ans) {
            $answersByScale[(int)$ans['scale_id']][] = $ans;
        }

        [$sql, $aliasMap] = $this->buildSinglePassSql($this->question['subQuestions'], $answersByScale);

        $row = $db->createCommand($sql)->queryRow();
        if (!$row) {
            return [];
        }

        $qTitleBase = flattenText($this->question['question']);
        $charts = [];

        foreach ($this->question['subQuestions'] as $subQuestion) {
            $subCode = $subQuestion['title'];
            $subLabel = $subQuestion['question'] ?? $subCode;

            foreach ([0, 1] as $scaleId) {
                $legend = [];
                $dataItems = [];

                foreach ($answersByScale[$scaleId] as $ans) {
                    $code  = (string)$ans['code'];
                    $label = (string)$ans['answer'];

                    $alias = $aliasMap[$subCode][$scaleId]['answers'][$code];
                    $cnt   = (int)($row[$alias] ?? 0);

                    $legend[]    = $label;
                    $dataItems[] = ['key' => $code, 'value' => $cnt, 'title' => $label];
                }

                $blankAlias = $aliasMap[$subCode][$scaleId]['blank'];
                $blankCnt   = (int)($row[$blankAlias] ?? 0);

                $legend[]    = 'NoAnswer';
                $dataItems[] = ['key' => 'NoAnswer', 'value' => $blankCnt, 'title' => 'No answer'];

                $title = "{$qTitleBase} [{$scaleName[$scaleId]}] [{$subLabel}]";
                $charts[] = new StatisticsChartDTO($title, $legend, $dataItems, null, ['question' => $this->question]);
            }
        }

        return $charts;
    }

    /**
     * Build one SELECT that returns:
     * - COUNT(*) AS __total
     * - SUM(CASE WHEN `field` = 'CODE' THEN 1 END) AS `alias` for each answer
     * - SUM(CASE WHEN `field` IS NULL OR `field`='' THEN 1 END) AS `alias_blank` per field
     *
     * @return array{0:string,1:array} [$sql, $aliasMap]
     */
    protected function buildSinglePassSql(array $subQuestions, array $answersByScale): array
    {
        $db = Yii::app()->db;
        $selects = ['COUNT(*) AS __total'];
        $aliasMap = [];

        foreach ($subQuestions as $sq) {
            $subCode = $sq['title'];

            foreach ([0, 1] as $scaleId) {
                $field = $this->rt . $subCode . '#' . $scaleId;
                $col   = $db->quoteColumnName($field);

                // answers
                foreach ($answersByScale[$scaleId] as $ans) {
                    $code  = (string)$ans['code'];
                    $value = $db->quoteValue($code);
                    $alias = $this->aliasFor($subCode, $scaleId, $code);

                    $selects[] = "SUM(CASE WHEN {$col} = {$value} THEN 1 ELSE 0 END) AS `{$alias}`";
                    $aliasMap[$subCode][$scaleId]['answers'][$code] = $alias;
                }

                // blank per field
                $blankAlias = $this->aliasForBlank($subCode, $scaleId);
                $selects[]  = "SUM(CASE WHEN {$col} IS NULL OR {$col} = '' THEN 1 ELSE 0 END) AS `{$blankAlias}`";
                $aliasMap[$subCode][$scaleId]['blank'] = $blankAlias;
            }
        }

        $where = '';
        $sql = 'SELECT ' . implode(",\n  ", $selects) . "\nFROM {{responses_{$this->surveyId}}}{$where}";

        return [$sql, $aliasMap];
    }

    protected function aliasFor(string $subCode, int $scaleId, string $code): string
    {
        return 'f_' . $this->slug($subCode) . '_s' . $scaleId . '_' . $this->slug($code);
    }

    protected function aliasForBlank(string $subCode, int $scaleId): string
    {
        return 'f_' . $this->slug($subCode) . '_s' . $scaleId . '_blank';
    }

    protected function slug(string $s): string
    {
        $s = preg_replace('/[^A-Za-z0-9_]+/', '_', $s);
        $s = trim($s, '_');
        return substr($s, 0, 40) ?: 'x';
    }
}
