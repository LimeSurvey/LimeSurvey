<?php

namespace LimeSurvey\ExtensionInstaller;

use Exception;
use InvalidArgumentException;
use ExtensionConfig;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Extension file fetcher for LimeStore ZIP file
 * Same logic as for upload, except where the file is fetched from (curl, in this case)
 *
 * @since 2022-05-013
 * @author LimeSurvey GmbH
 */
class FileFetcherLimestore extends FileFetcherUploadZip
{
    /** @var string */
    private $url;

    public function fetch()
    {
        // TODO: Check file size?
        // TODO: Check zip bomb
        $this->extractZipFile($this->getTempdir());
    }

    /**
     * Set download URL to limestore
     *
     * @param string $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Curl to limestore and unzip the file.
     *
     * @param string $tempdir
     * @return void
     * @throws Exception
     */
    protected function extractZipFile($tempdir)
    {
        \Yii::import('application.helpers.common_helper', true);
        \Yii::app()->loadLibrary('admin.pclzip');

        $zipFile = $tempdir . '/limestore.zip';

        //This is the file where we save the    information
        $fp = fopen($zipFile, 'w+');
        //Here is the file we are downloading, replace spaces with %20
        $ch = curl_init(str_replace(" ", "%20", $this->url));
        // make sure to set timeout to a high enough value
        // if this is too low the download will be interrupted
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        // write curl response to file
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // get curl response
        curl_exec($ch);
        $error = curl_error($ch);
        if (!empty($error)) {
            throw new Exception($error);
        }

        curl_close($ch);
        fclose($fp);

        $zip = new \PclZip($zipFile);
        $aExtractResult = $zip->extract(
            PCLZIP_OPT_PATH,
            $tempdir,
            PCLZIP_CB_PRE_EXTRACT,
            $this->filterName
        );

        if ($aExtractResult === 0) {
            // NB: Can fail with text "No permission" if extension is disabled in limestore.
            throw new Exception(
                gT("This file is not a valid ZIP file archive. Import failed.")
                . ' ' . $zip->error_string
            );
        } else {
            // All good?
        }
    }
}
