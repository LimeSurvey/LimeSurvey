<?php

namespace LimeSurvey\Models\Services\FileAggregateService\FileValidation;

use Yii;
class FileExtensionValidationService
{
    /**
     * @param $data
     * @return bool
     */
    public function validate($data): bool
    {
        $file = $data['file'];
        $extension = strtolower($file->getExtensionName());
        return in_array($extension, $this->getAllowedExtensions(), true);
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return "File extension not allowed.";
    }

    private function getAllowedExtensions(): array
    {
        return array_map('trim', explode(',', (string) Yii::app()->getConfig('allowedresourcesuploads')));
    }
}
