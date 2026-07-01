<?php

namespace LimeSurvey\Models\Services;

class ZipExtractor
{
    /** @var \ZipArchive */
    private $zip;

    /** @var callable */
    private $filterCallback;

    /** @var array */
    private $skippedFiles = [];

    /** @var array */
    private $extractResult = [];

    /** @var mixed */
    private $extractStatus;

    public function __construct($filename = null)
    {
        if (!empty($filename)) {
            $this->openFile($filename);
        }
    }

    public function openFile($filename)
    {
        $this->zip = new \ZipArchive();
        $this->zip->open($filename);
    }

    public function setFilterCallback($filterCallback)
    {
        $this->filterCallback = $filterCallback;
    }

    public function extractTo($folder)
    {
        /** @todo: Should we just export everything if there is no filter? We wouldn't know which files were extracted. */
        $files = $this->getFilesList($folder);
        $result = $this->zip->extractTo($folder, array_keys($files));
        $this->extractStatus = $this->zip->getStatusString();
        if ($result !== false) {
            $extractResult = [];
            foreach ($files as $file) {
                $extractResult[] = array_merge($file, ['status' => 'extracted']);
            }
            foreach ($this->skippedFiles as $file) {
                $extractResult[] = array_merge($file, ['status' => 'skipped']);
            }
            $this->extractResult = $extractResult;
        }
        $this->zip->close();
        return $result;
    }

    public function getFilesList($targetFolder = null)
    {
        $files = [];
        $skippedFiles = [];
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $filename = $this->zip->getNameIndex($i);
            /**
             * @todo: Should we consider the possibility of a ZIP using backslashes as folder separators?
             *        According to item 4.4.17.1 of the ZIP specification, the separator MUST be a forward slash,
             *        but some tools may still use backslashes.
             */
            $isFolder = (substr($filename, -1) === '/');
            $fileInfo = $this->zip->statIndex($i);
            $fileInfo['target_filename'] = empty($targetFolder)
                ? $filename
                : $targetFolder . DIRECTORY_SEPARATOR . $filename;
            $fileInfo['is_folder'] = $isFolder;
            if (!empty($this->filterCallback) && is_callable($this->filterCallback)) {
                if (!call_user_func($this->filterCallback, $fileInfo)) {
                    $skippedFiles[] = $fileInfo;
                    continue;
                }
            }
            $files[$filename] = $fileInfo;
        }
        $this->skippedFiles = $skippedFiles;
        return $files;
    }

    /**
     * @return mixed
     */
    public function getExtractStatus()
    {
        return $this->extractStatus;
    }

    /**
     * @return array
     */
    public function getExtractResult()
    {
        return $this->extractResult;
    }
}
