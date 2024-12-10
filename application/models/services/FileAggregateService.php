<?php

namespace LimeSurvey\Models\Services;
use LimeSurvey\Models\Services\FileAggregateService\FileService;

class FileAggregateService
{
    private FileService $fileService;

    public function __construct(

        FileService $fileService
    ) {

        $this->fileService = $fileService;
    }

    /**
     * Upload a file aggregate service
     *
     * @param $file
     * @return bool
     */
    public function upload($file): bool
    {
        return $this->fileService->upload(
           $file
        );
    }
}
