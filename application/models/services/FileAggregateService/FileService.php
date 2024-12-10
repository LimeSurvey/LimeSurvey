<?php

namespace LimeSurvey\Models\Services\FileAggregateService;

class FileService
{
    private FileUploadService $fileUploadService;

    public function __construct(
        FileUploadService $fileUploadService
    ) {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Upload a file
     *
     * @param $file
     * @return bool
     */
    public function upload($file): bool
    {
        return $this->fileUploadService->upload($file);
    }
}
