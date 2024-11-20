<?php

namespace LimeSurvey\Models\Services\FileAggregateService;

class FileUploadService
{
    /**
     * Upload a file
     *
     * @param $file
     * @return bool
     */
    public function upload($file): bool
    {
        if ($file !== null) {

            $uploadPath = APPPATH . '..'. DIRECTORY_SEPARATOR .'upload' . DIRECTORY_SEPARATOR . 'surveys' .DIRECTORY_SEPARATOR;

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $filePath = $uploadPath . time() .'_' .$file->getName();

            if ($file->saveAs($filePath)) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }
}
