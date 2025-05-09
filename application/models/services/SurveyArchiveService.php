<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use Permission;
use ArchivedTableSettings;

class SurveyArchiveService
{
    private Permission $permission;

    public function __construct(
        Permission $permission
    ) {
        $this->permission = $permission;
    }

    /**
     * Get the alias for an archive
     *
     * @param int $iSurveyID
     * @param int $iTimestamp archive timestamp
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @return string
     */
    public function getArhiveAlias($iSurveyID, $iTimestamp): string
    {
        $tbl_name = "old_survey_{$iSurveyID}_{$iTimestamp}";
        $archives = ArchivedTableSettings::getArchivesForTimestamp($iSurveyID, $iTimestamp);

        foreach ($archives as $archive) {
            if ($archive->tbl_name === $tbl_name) {
                return $archive->archive_alias;
            }
        }

        throw new NotFoundException(
            'No Alias found'
        );
    }

    /**
     * Update the alias for a specific archive
     *
     * @param int $iSurveyID
     * @param int $iTimestamp archive timestamp
     * @param string $newAlias The new alias to set
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @return bool True if updated successfully, false otherwise
     */
    public function updateArchiveAlias($iSurveyID, $iTimestamp, $newAlias): bool
    {

        if (!$this->hasPermission($surveyID)) {
            throw new PermissionDeniedException(
                'Access denied'
            );
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
     * Checks if user has the necessary permission
     * @param int $iSurveyID the id of the survey
     * @return bool whether the permission necessary is present
     */
    protected function hasPermission(int $iSurveyID)
    {
        return $this->permission->hasSurveyPermission($iSurveyID, 'surveysettings', 'update');
    }
}
