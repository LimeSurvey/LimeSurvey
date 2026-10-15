<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

use LSActiveRecord;
use LSDbCriteria;
use SurveyDynamic;

/**
 * Lists the uploaded files of one file upload column as one row per file.
 *
 * The search runs on each file's name, title and comment in PHP rather than as
 * SQL LIKE on the stored JSON, so JSON keys and internal values never match,
 * encrypted columns are searchable, and pagination counts files, not responses.
 */
class SurveyResponseFiles
{
    /**
     * @param SurveyDynamic $model
     * @param LSDbCriteria $criteria Response filters and order; select is replaced
     * @param string $column File upload JSON column
     * @param bool $encrypted
     * @param string[] $terms Each must occur in the response's files; a file is listed when it holds any of them
     * @param int $page Zero-based
     * @param int $pageSize
     * @return array{files: array, totalItems: int}
     */
    public function list(
        SurveyDynamic $model,
        LSDbCriteria $criteria,
        string $column,
        bool $encrypted,
        array $terms,
        int $page,
        int $pageSize
    ): array {
        $db = \Yii::app()->db;
        $criteria = clone $criteria;
        $criteria->select = ['id', $db->quoteColumnName($column)];
        $fileCountColumn = $column . '_Cfilecount';
        if ($model->getTableSchema()->getColumn($fileCountColumn) !== null) {
            $criteria->addCondition($db->quoteColumnName($fileCountColumn) . ' > 0');
        }

        $rows = $model->getCommandBuilder()
            ->createFindCommand($model->tableName(), $criteria)
            ->query();

        $offset = $page * $pageSize;
        $total = 0;
        $files = [];
        foreach ($rows as $row) {
            foreach ($this->matchingFiles((int) $row['id'], (string) ($row[$column] ?? ''), $encrypted, $terms) as $file) {
                if ($total >= $offset && count($files) < $pageSize) {
                    $files[] = $file;
                }
                $total++;
            }
        }

        return ['files' => $files, 'totalItems' => $total];
    }

    /**
     * @param int $responseId
     * @param string $value
     * @param bool $encrypted
     * @param string[] $terms
     * @return array[]
     */
    private function matchingFiles(int $responseId, string $value, bool $encrypted, array $terms): array
    {
        if ($value === '') {
            return [];
        }
        $decoded = json_decode($encrypted ? (string) LSActiveRecord::decryptSingle($value) : $value, true);
        if (!is_array($decoded)) {
            return [];
        }

        $files = [];
        foreach ($decoded as $index => $file) {
            if (!is_array($file) || $this->isDeleted($file)) {
                continue;
            }
            $name = rawurldecode((string) ($file['name'] ?? ''));
            $files[] = [
                'responseId' => $responseId,
                'index' => $index,
                'name' => $name,
                'title' => (string) ($file['title'] ?? ''),
                'comment' => (string) ($file['comment'] ?? ''),
                'size' => (float) ($file['size'] ?? 0),
                'ext' => strtolower((string) ($file['ext'] ?? pathinfo($name, PATHINFO_EXTENSION))),
                'searchText' => [$name, $this->plainText($file['title'] ?? ''), $this->plainText($file['comment'] ?? '')],
            ];
        }
        if ($terms === []) {
            return array_map([$this, 'withoutSearchText'], $files);
        }

        $responseText = array_merge(...array_column($files, 'searchText'));
        foreach ($terms as $term) {
            if (!$this->containsTerm($responseText, $term)) {
                return [];
            }
        }

        $matches = [];
        foreach ($files as $file) {
            foreach ($terms as $term) {
                if ($this->containsTerm($file['searchText'], $term)) {
                    $matches[] = $this->withoutSearchText($file);
                    break;
                }
            }
        }
        return $matches;
    }

    /**
     * Response::deleteFilesAndFilename() keeps the entry but removes the file
     * and appends a translated " (deleted)" note after the extension.
     *
     * @param array $file
     * @return bool
     */
    private function isDeleted(array $file): bool
    {
        $ext = (string) ($file['ext'] ?? '');
        return !empty($file['isDeleted'])
            || ($ext !== '' && preg_match(
                '/\.' . preg_quote($ext, '/') . ' \([^()]+\)$/iu',
                rawurldecode((string) ($file['name'] ?? ''))
            ) === 1);
    }

    /**
     * @param string[] $haystacks
     * @param string $term
     * @return bool
     */
    private function containsTerm(array $haystacks, string $term): bool
    {
        foreach ($haystacks as $haystack) {
            if (mb_stripos($haystack, $term) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param mixed $text
     * @return string
     */
    private function plainText($text): string
    {
        return html_entity_decode(strip_tags((string) $text), ENT_QUOTES);
    }

    /**
     * @param array $file
     * @return array
     */
    private function withoutSearchText(array $file): array
    {
        unset($file['searchText']);
        return $file;
    }
}
