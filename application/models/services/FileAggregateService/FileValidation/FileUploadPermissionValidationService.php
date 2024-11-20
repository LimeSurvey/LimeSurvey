<?php

namespace LimeSurvey\Models\Services\FileAggregateService\FileValidation;

use Permission;
use Survey;
class FileUploadPermissionValidationService
{
    private Permission $permission;

    public function __construct(Permission $permission)
    {
        $this->permission = $permission;
    }

    /**
     * @param $data
     * @return bool
     */
    public function validate($data): bool
    {
        $survey_id = (int)$data['survey_id'];

        return $this->permission->hasSurveyPermission(
            $survey_id,
            'survey',
            'update'
        );
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return "You do not have permission to upload files to this survey.";
    }
}
