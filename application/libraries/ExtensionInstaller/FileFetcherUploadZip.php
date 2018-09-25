<?php

namespace LimeSurvey\ExtensionInstaller;

/**
 * Extension file fetcher for upload ZIP file.
 * Must work for all extension types: plugins, theme, question theme, etc.
 *
 * @since 2018-09-25
 * @author Olle Haerstedt
 */
class FileFetcherUploadZip extends FileFetcher
{
    /**
     * Filter to apply to unzipping.
     * @var string
     */
    protected $filterName;

    /**
     * @param string $source
     * @return void
     */
    public function setSource($source)
    {
        // Not used.
    }

    /**
     * Fetch files, meaning grab uploaded ZIP file and
     * unzip it in system tmp folder.
     * @return void
     */
    public function fetch()
    {
        $this->checkFileSizeError();
        $this->checkZipBom();
        $this->extractZipFile($this->getDestdir());
    }

    /**
     * @return SimpleXMLElement
     * @throws Exception
     */
    public function getConfig()
    {
        $destdir = $this->getDestdir();
        if (empty($destdir)) {
            throw new \Exception(gT('No destination folder, cannot read configuration file.'));
        }

        $configFile = $destdir . '/config.xml';

        if (!file_exists($configFile)) {
            throw new \Exception(gT('Configuration file config.xml does not exist.'));
        }

        $config = \PluginConfiguration::loadConfigFromFile($configFile);
        return $config;
    }

    /**
     * @param string $filterName
     * @return void
     */
    public function setUnzipFilter($filterName)
    {
        $this->filterName = $filterName;
    }

    /**
     * Abort unzip, clear files and session.
     * @return void
     */
    public function abort()
    {
        // Remove any files.
        $destdir = $this->getDestdir();
        if ($destdir) {
            rmdirr($destdir);
        }

        // Reset user state.
        $this->clearDestdir();
    }

    /**
     * Get tmp destdir for extension to unzip in.
     * @return string
     */
    protected function getDestdir()
    {
        // NB: Since the installation procedure can span several page reloads,
        // we save the destdir in the user session.
        $destdir = App()->user->getState('filefetcheruploadzip_destdir');
        if (empty($destdir)) {
            $tempdir = \Yii::app()->getConfig("tempdir");
            $destdir = createRandomTempDir($tempdir, 'install_');
            App()->user->setState('filefetcheruploadzip_destdir', $destdir);
        }
        return $destdir;
    }

    /**
     * Set user session destdir to null.
     * @return void
     */
    protected function clearDestdir()
    {
        App()->user->setState('filefetcheruploadzip_destdir', null);
    }

    /**
     * @todo Duplicate from themes.php.
     * @return void
     * @throws Exception
     */
    protected function checkFileSizeError()
    {
        if (!isset($_FILES['the_file'])) {
            throw new \Exception(gT('Found no file'));
        }

        if ($_FILES['the_file']['error'] == 1 || $_FILES['the_file']['error'] == 2) {
            throw new \Exception(
                sprintf(
                    gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."),
                    getMaximumFileUploadSize() / 1024 / 1024
                )
            );
        }
    }

    /**
     * Check if uploaded zip file is a zip bomb.
     * @return void
     */
    protected function checkZipBom()
    {
        // Check zip bomb.
        \Yii::import('application.helpers.common_helper', true);
        if (isZipBomb($_FILES['the_file']['name'])) {
            throw new \Exception(gT('Unzipped file is superior to upload_max_filesize or to post_max_size'));
        }
    }

    /**
     * @param string $destdir
     * @return void
     */
    protected function extractZipFile($destdir)
    {
        \Yii::import('application.helpers.common_helper', true);
        \Yii::app()->loadLibrary('admin.pclzip');

        if (!is_file($_FILES['the_file']['tmp_name'])) {
            throw new \Exception(
                gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder.")
            );
        }

        if (empty($this->filterName)) {
            throw new \Exception(gT("No filter name is set, can't unzip."));
        }

        $zip = new \PclZip($_FILES['the_file']['tmp_name']);
        $aExtractResult = $zip->extract(
            PCLZIP_OPT_PATH,
            $destdir,
            PCLZIP_CB_PRE_EXTRACT,
            $this->filterName
        );

        if ($aExtractResult === 0) {
            throw new \Exception(
                gT("This file is not a valid ZIP file archive. Import failed.")
                . ' ' . $zip->error_string
            );
        } else {
            // All good?
        }
    }
}
