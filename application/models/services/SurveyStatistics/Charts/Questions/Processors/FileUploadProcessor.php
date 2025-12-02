<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use Yii;

class FileUploadProcessor extends AbstractQuestionProcessor
{
    protected string $rt;

    public function rt(): void
    {
        $this->rt = 'Q' . $this->question['qid'];
    }

    public function process(): StatisticsChartDTO
    {
        $this->rt();

        $counts = $this->getFileCounts();
        $fileStats = $this->getFileStats();

        $legend = [
            'Total files',
            'Average files/respondent',
            'Total size (bytes)',
            'Average size/respondent',
            'Largest file',
            'Smallest file'
        ];

        $dataItems = [
            ['key' => 'total_files', 'title' => 'Total files', 'value' => $counts['sum']],
            ['key' => 'avg_files', 'title' => 'Average files/respondent', 'value' => $counts['avg']],
            ['key' => 'total_size', 'title' => 'Total size (bytes)', 'value' => $fileStats['total_size']],
            ['key' => 'avg_size', 'title' => 'Average size/respondent', 'value' => $fileStats['avg_size']],
            ['key' => 'max_size', 'title' => 'Largest file (bytes)', 'value' => $fileStats['max_size']],
            ['key' => 'min_size', 'title' => 'Smallest file (bytes)', 'value' => $fileStats['min_size']],
        ];

        // Add extension breakdowns as extra items
        foreach ($fileStats['extensions'] as $ext => $count) {
            $legend[] = strtoupper($ext);
            $dataItems[] = ['key' => $ext, 'title' => $ext, 'value' => $count];
        }

        return new StatisticsChartDTO(
            $this->question['question'],
            $legend,
            $dataItems,
            $counts['sum']
        );
    }

    protected function getFileCounts(): array
    {
        $sql = "SELECT SUM(" . Yii::app()->db->quoteColumnName($this->rt . '_Cfilecount') . ") as sum,
                AVG(" . Yii::app()->db->quoteColumnName($this->rt . '_Cfilecount') . ") as avg 
             FROM {{responses_{$this->surveyId}}}";

        $row = Yii::app()->db->createCommand($sql)->queryRow();

        return [
            'sum' => (int)$row['sum'],
            'avg' => (float)$row['avg'],
        ];
    }

    protected function getFileStats(): array
    {
        $sql = "SELECT " . Yii::app()->db->quoteColumnName($this->rt) . " as json FROM {{responses_{$this->surveyId}}}";

        $rows = Yii::app()->db->createCommand($sql)->queryAll();

        $totalSize = 0;
        $maxSize = 0;
        $minSize = PHP_INT_MAX;
        $fileCount = 0;
        $extensions = [];

        foreach ($rows as $row) {
            $json = $row['json'];
            $files = json_decode((string)$json);

            if (!$files) {
                continue;
            }

            foreach ($files as $metadata) {
                $size = (int)($metadata->size ?? 0);
                $ext = strtolower(pathinfo($metadata->name ?? '', PATHINFO_EXTENSION));

                $totalSize += $size;
                $fileCount++;

                $maxSize = max($maxSize, $size);
                $minSize = min($minSize, $size);

                if ($ext) {
                    $extensions[$ext] = ($extensions[$ext] ?? 0) + 1;
                }
            }
        }

        return [
            'total_size' => $totalSize,
            'avg_size' => $fileCount > 0 ? round($totalSize / $fileCount, 2) : 0,
            'max_size' => $maxSize,
            'min_size' => $minSize === PHP_INT_MAX ? 0 : $minSize,
            'extensions' => $extensions,
        ];
    }
}
