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
        $tbl_name = "old_survey_{$iSurveyID}_{$iTimestamp}";
        $archives = ArchivedTableSettings::getArchivesForTimestamp($iSurveyID, $iTimestamp);

        foreach ($archives as $archive) {
            if ($archive->tbl_name === $tbl_name) {
                return $archive->archive_alias;
            }
        }

        return 'Unknown archive';
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
     * Get response archive data (responses)
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

        return $this->getArchiveDataInternal(SurveyDynamicArchive::class, $iSurveyID, $iTimestamp, $searchParams);
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
                    $archiveTable = "old_survey_{$iSurveyID}_{$iTimestamp}";
                    break;
                case self::$Tokens_archive:
                    $archiveTable = "old_tokens_{$iSurveyID}_{$iTimestamp}";
                    break;
                case self::$Timings_archive:
                    $archiveTable = "old_survey_{$iSurveyID}_timings_{$iTimestamp}";
                    break;
                default:
                    continue 2;
            }

            $this->app->db->createCommand()->dropTable("{{" . $archiveTable . "}}");
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

        $tbl_name = "old_survey_{$iSurveyID}_{$iTimestamp}";
        $archives = ArchivedTableSettings::getArchivesForTimestamp($iSurveyID, $iTimestamp);

        foreach ($archives as $archive) {
            if ($archive->tbl_name === $tbl_name) {
                $archive->archive_alias = $newAlias;
                return $archive->save();
            }
        }

        return false;
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
