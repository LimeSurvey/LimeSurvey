<?php

namespace LimeSurvey\ExtensionInstaller;

/**
 * Extension file fetcher for upload ZIP file.
 */
class FileFetcherUploadZip
{
    /**
     * 
     */
    public function setSource($source)
    {
    }

    /**
     * 
     */
    public function fetch()
    {
        // Redirect back at file size error.
        $this->checkFileSizeError();

        // Redirect back at zip bomb.
        $this->checkZipBom();

    }

    /**
     * 
     */
    public function getExtensionConfig()
    {
        
    }
}
