<?php

namespace LimeSurvey\Models\Services\FileAggregateService\FileValidation;

class FileValidationService
{
    private array $validators;

    /**
     * FileValidationService constructor.
     * @param FileSizeValidationService $fileSizeValidationService
     * @param FileExtensionValidationService $fileExtensionValidationService
     * @param FileUploadPermissionValidationService $fileUploadPermissionValidationService
     */
    public function __construct(
        FileSizeValidationService $fileSizeValidationService,
        FileExtensionValidationService $fileExtensionValidationService,
        FileUploadPermissionValidationService $fileUploadPermissionValidationService
    )
    {
        $this->validators = [
            $fileSizeValidationService,
            $fileExtensionValidationService,
            $fileUploadPermissionValidationService
        ];
    }

    /**
     * @param $data
     * @return array
     */
    public function validate($data): array
    {
        $errors = [];

        foreach ($this->validators as $validator) {
            if (!$validator->validate($data)) {
                $errors[] = $validator->getErrorMessage();
            }
        }

        return $errors;
    }
}
