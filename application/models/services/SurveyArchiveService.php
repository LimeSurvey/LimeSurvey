<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use Survey;
use Permission;
use LSYii_Application;
use ArchivedTableSettings;
use TokenDynamicArchive;
use SurveyDynamicArchive;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class SurveyArchiveService
{
    private Survey $survey;

    private Permission $permission;

    protected LSYii_Application $app;

    public static $Response_archive = 'response';

    public static $Tokens_archive = 'token';

    public static $Timings_archive = 'timings';

    public static $Questions_archive = 'questions';

    private static $tableNameMap = [
        'response'  => 'old_responses_%d_%d',
        'token'     => 'old_tokens_%d_%d',
        'timings'   => 'old_timings_%d_%d',
        'questions' => 'old_questions_%d_%d',
    ];

    public function __construct(
        Survey $survey,
        Permission $permission,
        LSYii_Application $app
    ) {
        $this->survey = $survey;
        $this->permission = $permission;
        $this->app = $app;
    }

    /**
     * Builds the archive table name for the given type.
     *
     * @param string $archiveType
     * @param int $sid
     * @param int $timestamp
     * @throws \InvalidArgumentException if the archive type is unknown
     *
     * @return string
     */
    public static function buildArchiveTableName(string $archiveType, int $sid, int $timestamp): string
    {
        if (!isset(self::$tableNameMap[$archiveType])) {
            throw new \InvalidArgumentException("Unknown archive type: $archiveType");
        }
        return sprintf(self::$tableNameMap[$archiveType], $sid, $timestamp);
    }

    /**
     * Get the alias for an archive
     *
     * @param int $iSurveyID
     * @param int $iTimestamp
     * @return string
     */
    public function getArchiveAlias(int $iSurveyID, int $iTimestamp): string
    {
        $responseArchive = ArchivedTableSettings::getArchiveForTimestamp($iSurveyID, $iTimestamp);
        $tokenArchive = ArchivedTableSettings::getArchiveForTimestamp($iSurveyID, $iTimestamp, self::$Tokens_archive);

        if (!$responseArchive && !$tokenArchive) {
            return 'Unknown archive';
        }

        $responseAlias = $responseArchive->archive_alias ?? null;
        $tokenAlias = $tokenArchive->archive_alias ?? null;
        foreach ([$responseAlias, $tokenAlias] as $alias) {
            if (!is_null($alias) && $alias !== '') {
                return $alias;
            }
        }

        return '';
    }

    /**
     * Update the alias for a specific archive
     *
     * @param int $iSurveyID
     * @param int $iTimestamp
     * @param string $newAlias
     * @throws PermissionDeniedException
     * @return bool
     */
    public function updateArchiveAlias(int $iSurveyID, int $iTimestamp, string $newAlias): bool
    {
        if (!$this->hasPermission($iSurveyID)) {
            throw new PermissionDeniedException('Access denied');
        }

        $responseArchive = ArchivedTableSettings::getArchiveForTimestamp($iSurveyID, $iTimestamp);
        $tokenArchive = ArchivedTableSettings::getArchiveForTimestamp($iSurveyID, $iTimestamp, self::$Tokens_archive);

        $sanitizedAlias = sanitize_ldap_string($newAlias);
        $success = false;

        if ($responseArchive) {
            $responseArchive->archive_alias = $sanitizedAlias;
            $success = $responseArchive->save();
        }

        if ($tokenArchive) {
            $tokenArchive->archive_alias = $sanitizedAlias;
            $success = $tokenArchive->save() || $success;
        }

        return $success;
    }

    /**
     * Get token archive data (participants)
     *
     * @param int $iSurveyID
     * @param int $iTimestamp
     * @param array $searchParams
     * @return array
     */
    public function getTokenArchiveData(int $iSurveyID, int $iTimestamp, array $searchParams = []): array
    {
        if (!$this->doesArchiveExists($iSurveyID, $iTimestamp, self::$Tokens_archive)) {
            return [];
        }

        return $this->getArchiveDataInternal(TokenDynamicArchive::class, $iSurveyID, $iTimestamp, $searchParams);
    }

    /**
     * Get responses archive data
     *
     * @param int $iSurveyID
     * @param int $iTimestamp
     * @param array $searchParams
     * @return array
     */
    public function getResponseArchiveData(int $iSurveyID, int $iTimestamp, array $searchParams = []): array
    {
        if (!$this->doesArchiveExists($iSurveyID, $iTimestamp, self::$Response_archive)) {
            return [];
        }

        $archivedResponsesData = $this->getArchiveDataInternal(SurveyDynamicArchive::class, $iSurveyID, $iTimestamp, $searchParams);

        if (!empty($archivedResponsesData['data'])) {
            $this->attachTimingsToResponses($archivedResponsesData, $iSurveyID, $iTimestamp);
            $this->attachQuestionTitlesToResponses($archivedResponsesData, $iSurveyID);
        }

        return $archivedResponsesData;
    }

    /**
     * Delete archive data
     *
     * @param int $iSurveyID
     * @param int $iTimestamp
     * @param array $ArchivesToDelete archive table types
     * @throws PermissionDeniedException
     * @return void
     */
    public function deleteArchiveData(int $iSurveyID, int $iTimestamp, array $ArchivesToDelete = []): void
    {
        if (empty($ArchivesToDelete)) {
            $ArchivesToDelete = [self::$Response_archive, self::$Tokens_archive, self::$Timings_archive];
        }

        $permissionMap = [
            self::$Response_archive => 'responses',
            self::$Tokens_archive   => 'tokens',
            self::$Timings_archive => 'timings',
        ];
        $requiredPermissions = [];

        foreach ($ArchivesToDelete as $archiveType) {
            if (!isset($permissionMap[$archiveType])) {
                throw new \InvalidArgumentException("Unknown archive type: $archiveType");
            }
            $requiredPermissions[$permissionMap[$archiveType]] = 'delete';
        }

        foreach ($requiredPermissions as $permName => $permType) {
            if (!$this->permission->hasSurveyPermission($iSurveyID, $permName, $permType)) {
                throw new PermissionDeniedException('Permission denied for deleting archive data');
            }
        }

        foreach ($ArchivesToDelete as $archiveType) {
            $archive = ArchivedTableSettings::getArchiveForTimestamp($iSurveyID, $iTimestamp, $archiveType);
            if ($archive) {
                $this->dropArchiveTable($archiveType, $iSurveyID, $iTimestamp);
                if ($archiveType === self::$Response_archive) { // delete question types table when deleting responses
                    $this->dropArchiveTable(self::$Questions_archive, $iSurveyID, $iTimestamp);
                }
                $archive->delete();
            }
        }
    }

    /**
     * Drops an archive table if it exists
     *
     * @param string $archiveType
     * @param int $iSurveyID
     * @param int $iTimestamp
     * @return void
     */
    private function dropArchiveTable(string $archiveType, int $iSurveyID, int $iTimestamp): void
    {
        $tableName = "{{" . self::buildArchiveTableName($archiveType, $iSurveyID, $iTimestamp) . "}}";
        if (tableExists($tableName)) {
            $this->app->db->createCommand()->dropTable($tableName);
        }
    }

    /**
     * verifies if an archive exists
     *
     * @param int $iSurveyID
     * @param int $iTimestamp
     * @param string $archiveType
     * @return bool
     */
    public function doesArchiveExists(int $iSurveyID, int $iTimestamp, string $archiveType): bool
    {
        $tableName = self::buildArchiveTableName($archiveType, $iSurveyID, $iTimestamp);
        if (!tableExists("{{{$tableName}}}")) {
            return false;
        }

        return true;
    }

    /**
     * Shared internal method for archive data
     *
     * @param class-string $modelClass
     * @param int $iSurveyID
     * @param int $iTimestamp
     * @param array $searchParams
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    private function getArchiveDataInternal(string $modelClass, int $iSurveyID, int $iTimestamp, array $searchParams): array
    {
        if (!method_exists($modelClass, 'setTimestamp')) {
            throw new \InvalidArgumentException("Model class {$modelClass} doesn't support timestamp");
        }

        $modelClass::setTimestamp($iTimestamp);
        $model = $modelClass::model($iSurveyID);

        $criteria = new \LSDbCriteria();
        $sort     = new \CSort();
        $filters = $searchParams['filters'] ?? [];
        $sortBy = $searchParams['sort'] ?? null;
        $page = max(1, (int)($searchParams['page'] ?? 1));
        $pageSize = max(1, (int)($searchParams['pageSize'] ?? 10));

        $columnNames = $model->getTableSchema()->getColumnNames();
        foreach ($filters as $field => $value) {
            if (!in_array($field, $columnNames, true)) {
                throw new \InvalidArgumentException("Unknown filter field: $field");
            }
            $criteria->addSearchCondition($this->app->db->quoteColumnName($field), (string) $value, true, 'AND');
        }

        if ($sortBy && isset($sortBy['attribute'], $sortBy['direction'])) {
            if (!in_array($sortBy['attribute'], $columnNames, true)) {
                throw new \InvalidArgumentException("Unknown sort field: {$sortBy['attribute']}");
            }
            $direction = strtolower((string) $sortBy['direction']) === 'desc' ? \CSort::SORT_DESC : \CSort::SORT_ASC;
            $sort->defaultOrder = [$sortBy['attribute'] => $direction];
        }

        $dataProvider = new \LSCActiveDataProvider($model, [
            'sort' => $sort,
            'criteria' => $criteria,
            'pagination' => [
                'pageSize' => $pageSize,
                'currentPage' => max(0, $page - 1),
            ],
        ]);
        $data = $dataProvider->getData();
        $pagination = $dataProvider->getPagination();

        $dataArray = [];
        foreach ($data as $record) {
            if (method_exists($modelClass, 'decrypt')) {
                $record->decrypt();
            }
            $dataArray[] = $record->attributes;
        }

        return [
            'data' => $dataArray,
            'meta' => [
                'currentPage' => $pagination->getCurrentPage() + 1,
                'pageSize' => $pagination->getPageSize(),
                'totalItems' => $dataProvider->getTotalItemCount(),
                'totalPages' => $pagination->getPageCount(),
            ],
        ];
    }

    /**
     * Attach timing data to the archived responses by reference
     *
     * @param array &$archivedResponsesData
     * @param int $iSurveyID
     * @param int $iTimestamp
     * @return void
     */
    private function attachTimingsToResponses(array &$archivedResponsesData, int $iSurveyID, int $iTimestamp): void
    {
        $timingsTableName = self::buildArchiveTableName(self::$Timings_archive, $iSurveyID, $iTimestamp);
        if (!tableExists("{{{$timingsTableName}}}")) {
            return;
        }

        $responseIds = array_column($archivedResponsesData['data'], 'id');
        if (empty($responseIds)) {
            return;
        }

        $timingsData = $this->app->db->createCommand()
            ->select('*')
            ->from("{{{$timingsTableName}}}")
            ->where(['in', 'id', array_map('intval', $responseIds)])
            ->queryAll();

        $timings = [];
        foreach ($timingsData as $timingRecord) {
            $timings[$timingRecord['id']] = $timingRecord;
        }

        $dataWithTimings = [];
        foreach ($archivedResponsesData['data'] as $response) {
            $responseId = $response['id'];
            $response['timings'] = $timings[$responseId] ?? null;
            $dataWithTimings[] = $response;
        }

        $archivedResponsesData['data'] = $dataWithTimings;
    }

    /**
     * Attach question titles and group titles to the archived responses.
     *
     * @param array $archivedResponsesData Array of archived survey responses.
     * @param int $iSurveyID.
     *
     * @return void
     */
    private function attachQuestionTitlesToResponses(array &$archivedResponsesData, int $iSurveyID): void
    {
        $survey = $this->survey->findByPk($iSurveyID);
        $fieldMap = createFieldMap($survey, 'full', false, false);
        $dataWithTitles = [];

        foreach ($archivedResponsesData['data'] as $response) {
            $fieldDetails = [];

            foreach ($response as $fieldName => $value) {
                if (!isset($fieldMap[$fieldName])) {
                    continue;
                }

                $fieldMeta = $fieldMap[$fieldName];

                if (empty($fieldMeta['sid']) || empty($fieldMeta['gid']) || empty($fieldMeta['qid'])) {
                    continue;
                }

                $subQuestionTitle = '';
                if (!empty($fieldMeta['sqid'])) {
                    $sub1 = $fieldMeta['subquestion1'] ?? '';
                    $sub2 = $fieldMeta['subquestion2'] ?? '';
                    if (!empty($sub1) && !empty($sub2)) {
                        $subQuestionTitle =  "{$sub1} - {$sub2}";
                    }
                }

                $fieldDetails[$fieldName] = [
                    'groupTitle' => $fieldMeta['group_name'] ?? '',
                    'questionTitle' => $fieldMeta['question'] ?? '',
                    'subQuestionTitle' => $subQuestionTitle,
                    'questionCode' => $fieldMeta['title'] ?? '',
                ];
            }
            $response['fieldDetails'] = $fieldDetails;
            $dataWithTitles[] = $response;
        }

        $archivedResponsesData['data'] = $dataWithTitles;
    }

    /**
     * Exports tokens archive as a stream
     *
     * @param int $iSurveyID
     * @param int $iTimestamp
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return void
     */
    public function exportTokensAsStream(int $iSurveyID, int $iTimestamp)
    {

        echo chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF'));

        $tableName = self::buildArchiveTableName(self::$Tokens_archive, $iSurveyID, $iTimestamp);

        $oRecordSet = $this->app->db->createCommand()->from("{{" . $tableName . "}}");
        $schema = $this->app->db->getSchema();
        $table = $schema->getTable("{{" . $tableName . "}}");
        $headerColumns = array_keys($table->columns);
        $oRecordSet->select('*');
        $oRecordSet->order('tid');

        echo $this->buildCsvRow($headerColumns) . "\n";
        flush();

        $countQuery = clone $oRecordSet;
        $countQuery->select('COUNT(tid)');
        $totalRows = $countQuery->queryScalar();

        $maxRows = 1000;
        $maxPages = ceil($totalRows / $maxRows);

        TokenDynamicArchive::setTimestamp($iTimestamp);
        $token = TokenDynamicArchive::model($iSurveyID);
        $tokenAttributes = array_keys($token->getAttributes());

        for ($i = 0; $i < $maxPages; $i++) {
            $offset = $i * $maxRows;
            $batchQuery = clone $oRecordSet;
            $batchQuery->limit($maxRows, $offset);
            $results = $batchQuery->queryAll();

            foreach ($results as $tokenValue) {
                foreach ($tokenValue as $key => $value) {
                    if (in_array($key, $tokenAttributes)) {
                        $token->$key = $value;
                    }
                }

                $token->decrypt();
                $decryptedRow = $token->attributes;

                if (!empty($decryptedRow['validfrom'])) {
                    $datetimeobj = new \Date_Time_Converter($decryptedRow['validfrom'], "Y-m-d H:i:s");
                    $decryptedRow['validfrom'] = $datetimeobj->convert('Y-m-d H:i');
                }
                if (!empty($decryptedRow['validuntil'])) {
                    $datetimeobj = new \Date_Time_Converter($decryptedRow['validuntil'], "Y-m-d H:i:s");
                    $decryptedRow['validuntil'] = $datetimeobj->convert('Y-m-d H:i');
                }

                $csvRow = [];
                foreach ($headerColumns as $column) {
                    $csvRow[] = isset($decryptedRow[$column]) ? trim((string) $decryptedRow[$column]) : '';
                }

                echo $this->buildCsvRow($csvRow) . "\n";
            }
            flush();
        }
    }

    /**
     * Exports responses archive as a stream
     *
     * @param int $iSurveyID
     * @param int $iTimestamp
     * @param int $maxRows number of rows fetched per batch
     *
     * @return void
     */
    public function exportResponsesAsStream(int $iSurveyID, int $iTimestamp = 0, int $maxRows = 1000)
    {
        echo chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF'));

        $tableName = self::buildArchiveTableName(self::$Response_archive, $iSurveyID, $iTimestamp);
        $oRecordSet = $this->app->db->createCommand()->from("{{" . $tableName . "}}");

        $schema = $this->app->db->getSchema();
        $table = $schema->getTable("{{" . $tableName . "}}");
        $headerColumns = array_keys($table->columns);
        $oRecordSet->select('*');

        $countQuery = clone $oRecordSet;
        $countQuery->select('COUNT(id)');
        $totalRows = $countQuery->queryScalar();

        $oRecordSet->order('id');
        echo $this->buildCsvRow($headerColumns) . "\n";
        flush();

        $maxPages = ceil($totalRows / $maxRows);

        SurveyDynamicArchive::setTimestamp($iTimestamp);
        $response = SurveyDynamicArchive::model($iSurveyID);

        for ($i = 0; $i < $maxPages; $i++) {
            $offset = $i * $maxRows;
            $batchQuery = clone $oRecordSet;
            $batchQuery->limit($maxRows, $offset);
            $results = $batchQuery->queryAll();

            foreach ($results as $record) {
                $decryptedRow = $response->populateRecord($record, false)->decrypt()->attributes;
                $csvRow = [];
                foreach ($headerColumns as $headerColumn) {
                    $csvRow[] = $decryptedRow[$headerColumn] ?? '';
                }
                echo $this->buildCsvRow($csvRow) . "\n";
            }

            flush();
        }
    }

    /**
     * Builds one CSV line, quoting every value and masking spreadsheet formulas
     *
     * Numeric values are not formula-masked so negative numbers stay intact.
     *
     * @param array $values
     * @return string
     */
    private function buildCsvRow(array $values): string
    {
        $escaped = [];
        foreach ($values as $value) {
            $value = (string) $value;
            if ($value === '') {
                $escaped[] = '""';
            } elseif (is_numeric($value)) {
                $escaped[] = '"' . $value . '"';
            } else {
                $escaped[] = csvEscape($value);
            }
        }
        return implode(',', $escaped);
    }

    /**
     * Check if user has permission to update archive
     *
     * @param int $iSurveyID
     * @return bool
     */
    private function hasPermission(int $iSurveyID): bool
    {
        return $this->permission->hasSurveyPermission($iSurveyID, 'surveysettings', 'update');
    }
}
