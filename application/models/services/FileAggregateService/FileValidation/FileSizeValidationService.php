<?php

namespace LimeSurvey\Models\Services\FileAggregateService\FileValidation;

class FileSizeValidationService
{
    /**
     * @param $data
     * @return bool
     */
    public function validate($data): bool
    {
        $file = $data['file'];
        return $file->size <= $this->getMaxSize();
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return "File size exceeds the maximum limit.";
    }

    /**
     * @return int
     */
    private function getMaxSize(): int
    {
        return getMaximumFileUploadSize();
    }
}
