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

class SurveyArchiveService
{
    private Survey $survey;

    private Permission $permission;

    protected LSYii_Application $app;

    public static $Response_archive = 'RP';

    public static $Tokens_archive = 'TK';

    public static $Timings_archive = 'TM';

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
     * Get the alias for an archive
     *
     * @param int $iSurveyID
     * @param int $iTimestamp
     * @return string
     */
    public function getArchiveAlias(int $iSurveyID, int $iTimestamp): string
    {
        $responseArchive = ArchivedTableSettings::getArchiveForTimestamp($iSurveyID, $iTimestamp);
        $tokenArchive = ArchivedTableSettings::getArchiveForTimestamp($iSurveyID, $iTimestamp, 'token');

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
        $tokenArchive = ArchivedTableSettings::getArchiveForTimestamp($iSurveyID, $iTimestamp, 'token');

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
        $tableName = '{{old_tokens_' . $iSurveyID . '_' . $iTimestamp . '}}';
        if (!tableExists($tableName)) {
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
        $tableName = '{{old_survey_' . $iSurveyID . '_' . $iTimestamp . '}}';
        if (!tableExists($tableName)) {
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
        $requiredPermissions = [];
        if (empty($ArchivesToDelete)) {
            $ArchivesToDelete = [
                self::$Response_archive,
                self::$Tokens_archive,
                self::$Timings_archive,
            ];
        }
        foreach ($ArchivesToDelete as $archiveType) {
            switch ($archiveType) {
                case self::$Response_archive:
                    $requiredPermissions['responses'] = 'delete';
                    break;
                case self::$Tokens_archive:
                    $requiredPermissions['tokens'] = 'delete';
                    break;
                case self::$Timings_archive:
                    $requiredPermissions['timings'] = 'delete';
                    break;
            }
        }
        foreach ($requiredPermissions as $permName => $permType) {
            if (!$this->permission->hasSurveyPermission($iSurveyID, $permName, $permType)) {
                throw new PermissionDeniedException('Permission denied for deleting archive data');
            }
        }
        foreach ($ArchivesToDelete as $archiveType) {
            switch ($archiveType) {
                case self::$Response_archive:
                    $sTableType = "response";
                    break;
                case self::$Tokens_archive:
                    $sTableType = "token";
                    break;
                case self::$Timings_archive:
                    $sTableType = "timings";
                    break;
                default:
                    continue 2;
            }
            $archive = ArchivedTableSettings::getArchiveForTimestamp($iSurveyID, $iTimestamp, $sTableType);
            if ($archive) {
                $archiveTableName = $archive->tbl_name;
                $this->app->db->createCommand()->dropTable("{{" . $archiveTableName . "}}");
                if ($archiveType === self::$Response_archive) { // delete question types table when deleting responses
                    $questionTypesTableName = str_replace('survey', 'questions', $archiveTableName);
                    $this->app->db->createCommand()->dropTable("{{" . $questionTypesTableName . "}}");
                }
                $archive->delete();
            }
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

        $archiveTypeString = $archiveType === self::$Tokens_archive ? 'token' : 'response';
        $archive = ArchivedTableSettings::getArchiveForTimestamp($iSurveyID, $iTimestamp, $archiveTypeString);
        if (!$archive) {
            return false;
        }

        $tableName = $archive->tbl_name;
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
        $page = (int)($searchParams['page'] ?? 1);
        $pageSize = (int)($searchParams['pageSize'] ?? 10);

        foreach ($filters as $field => $value) {
            $criteria->addSearchCondition($field, $value, true, 'AND');
        }

        if ($sortBy && isset($sortBy['attribute'], $sortBy['direction'])) {
            $direction = strtolower($sortBy['direction']) === 'desc' ? false : true;
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
        $timingsTableName = "old_survey_{$iSurveyID}_timings_{$iTimestamp}";
        if (!tableExists("{{{$timingsTableName}}}")) {
            return;
        }

        $responseIds = array_column($archivedResponsesData['data'], 'id');
        if (empty($responseIds)) {
            return;
        }

        $idList = implode(',', $responseIds);
        $query = "SELECT * FROM {{{$timingsTableName}}} WHERE id IN ($idList)";
        $timingsData = $this->app->db->createCommand($query)->queryAll();

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
    protected function attachQuestionTitlesToResponses(array &$archivedResponsesData, int $iSurveyID): void
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
     * @return void
     */
    function exportTokensAsStream(int $iSurveyID, int $iTimestamp)
    {

        echo chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF'));

        $archive = ArchivedTableSettings::getArchiveForTimestamp($iSurveyID, $iTimestamp, 'token');
        $tableName = $archive->tbl_name;
       
        $oRecordSet = $this->app->db->createCommand()->from("{{" . $tableName . "}}");
        $schema = $this->app->db->getSchema();
        $table = $schema->getTable("{{" . $tableName . "}}");
        $headerColumns = array_keys($table->columns);
        $oRecordSet->select('*');
        $oRecordSet->order('tid');

        echo implode(',', $headerColumns) . "\n";
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
                    $datetimeobj = new Date_Time_Converter($decryptedRow['validfrom'], "Y-m-d H:i:s");
                    $decryptedRow['validfrom'] = $datetimeobj->convert('Y-m-d H:i');
                }
                if (!empty($decryptedRow['validuntil'])) {
                    $datetimeobj = new Date_Time_Converter($decryptedRow['validuntil'], "Y-m-d H:i:s");
                    $decryptedRow['validuntil'] = $datetimeobj->convert('Y-m-d H:i');
                }

                $csvRow = [];
                foreach ($headerColumns as $column) {
                    $value = isset($decryptedRow[$column]) ? $decryptedRow[$column] : '';
                    $escapedValue = str_replace('"', '""', trim((string) $value));
                    $csvRow[] = '"' . $escapedValue . '"';
                }

                echo implode(',', $csvRow) . "\n";
            }
            flush();
        }
    }

    public function exportResponsesAsStream(int $iSurveyID, int $iTimestamp = 0, int $maxRows = 1000)
    {
        echo chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF'));

        $tableName = 'survey_' . $iSurveyID;
        if ($iTimestamp) {
            $tableName = 'old_' . $tableName . '_' . $iTimestamp;
        }

        $oRecordSet = $this->app->db->createCommand()->from("{{" . $tableName . "}}");

        $schema = $this->app->db->getSchema();
        $table = $schema->getTable("{{" . $tableName . "}}");
        $headerColumns = array_keys($table->columns);
        $oRecordSet->select('*');

        $countQuery = clone $oRecordSet;
        $countQuery->select('COUNT(id)');
        $totalRows = $countQuery->queryScalar();

        $oRecordSet->order('id');
        echo implode(',', $headerColumns) . "\n";
        flush();

        $maxPages = ceil($totalRows / $maxRows);

        $csvRow = [];
        for ($i = 0; $i < $maxPages; $i++) {
            $offset = $i * $maxRows;
            $batchQuery = clone $oRecordSet;
            $batchQuery->limit($maxRows, $offset);
            $results = $batchQuery->queryAll();

            foreach ($results as $record) {
                foreach ($headerColumns as $headerColumn) {
                    $csvRow []= '"' . $record[$headerColumn] . '"';
                }
            }

            echo implode(',', $csvRow) . "\n";
        }
        flush();
    }

    /**
     * Check if user has permission to update archive
     *
     * @param int $iSurveyID
     * @return bool
     */
    protected function hasPermission(int $iSurveyID): bool
    {
        return $this->permission->hasSurveyPermission($iSurveyID, 'surveysettings', 'update');
    }
}
