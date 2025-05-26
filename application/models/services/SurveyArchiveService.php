<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use Permission;
use LSYii_Application;
use ArchivedTableSettings;
use TokenDynamicArchive;
use SurveyDynamicArchive;

class SurveyArchiveService
{
    private Permission $permission;

    protected LSYii_Application $app;

    public static $Response_archive = 'RP';

    public static $Tokens_archive = 'TK';

    public static $Timings_archive = 'TM';

    public function __construct(
        Permission $permission,
        LSYii_Application $app
    ) {
        $this->permission = $permission;
        $this->app = $app;
    }

    /**
     * Get the alias for an archive
     *
     * @param int $iSurveyID
     * @param int $iTimestamp
     * @throws NotFoundException
     * @return string
     */
    public function getArchiveAlias(int $iSurveyID, int $iTimestamp): string
    {
        $archive = ArchivedTableSettings::getArchiveForTimestamp($iSurveyID, $iTimestamp);
        if (!$archive) {
            return 'Unknown archive';
        }

        $alias = $archive->archive_alias;
        return $alias;
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

        $archive = ArchivedTableSettings::getArchiveForTimestamp($iSurveyID, $iTimestamp);

        if ($archive) {
            $archive->archive_alias = $newAlias;
            return $archive->save();
        }

        return false;
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

        if (isEmpty($ArchivesToDelete)) {
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
            $archiveTableName = $archive->tbl_name;

            if ($archive) {
                $this->app->db->createCommand()->dropTable("{{" . $archiveTableName . "}}");
                $archive->delete();
            }
        }
    }

    /**
     * Shared internal method for archive data
     *
     * @param class-string $modelClass
     * @param int $iSurveyID
     * @param int $iTimestamp
     * @param array $searchParams
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

        return [
            'data' => $data,
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
        foreach ($archivedResponsesData['data'] as $responseModel) {
            $responseId = $responseModel->id;
            $responseAttributes = $responseModel->attributes;
            $responseAttributes['timings'] = $timings[$responseId] ?? null;
            $dataWithTimings[] = $responseAttributes;
        }

        $archivedResponsesData['data'] = $dataWithTimings;
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
