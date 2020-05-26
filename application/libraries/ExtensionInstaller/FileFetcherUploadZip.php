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
        $this->extractZipFile($this->getTempdir());
    }

    /**
     * Move files from tempdir to final destdir.
     * @param string $destdir
     * @return boolean
     */
    public function move($destdir)
    {
        if (empty($destdir)) {
            throw new \InvalidArgumentException('Missing destdir argument');
        }

        $tempdir = $this->getTempdir();
        if (empty($tempdir)) {
            throw new \Exception(gT('Temporary folder cannot be determined.'));
        }

        if (!file_exists($tempdir)) {
            throw new \Exception(gT('Temporary folder does not exist.'));
        }

        if (!is_writable(dirname($destdir))) {
            throw new \Exception(gT('Cannot move files due to permission problem.'));
        }

        if (file_exists($destdir) && !rmdirr($destdir)) {
            throw new \Exception('Could not remove old files.');
        }

        return $this->recurseCopy($tempdir, $destdir);
    }

    /**
     * @return SimpleXMLElement
     * @throws Exception
     */
    public function getConfig()
    {
        $tempdir = $this->getTempdir();
        if (empty($tempdir)) {
            throw new \Exception(gT('No temporary folder, cannot read configuration file.'));
        }

        $configFile = $tempdir . DIRECTORY_SEPARATOR . 'config.xml';

        if (!file_exists($configFile)) {
            //Check if zip file was unzipped in subfolder
            $subdirs = preg_grep('/^([^.])/', scandir($tempdir));
            if (count($subdirs) == 1) {
                $configXml = '';
                foreach ($subdirs as $dir) {
                    $tempdir = $tempdir . DIRECTORY_SEPARATOR . $dir;
                    $configXml = $tempdir . DIRECTORY_SEPARATOR . 'config.xml';
                }
                if (file_exists($configXml)) {
                    //save new tempDir in the user session
                    App()->user->setState('filefetcheruploadzip_tmpdir', $tempdir);
                    //set new config file
                    $configFile = $configXml;
                } else {
                    throw new \Exception(gT('Configuration file config.xml does not exist.'));
                }
            } else {
                throw new \Exception(gT('Configuration file config.xml does not exist.'));
            }
        }

        $config = \ExtensionConfig::loadConfigFromFile($configFile);

        if (empty($config)) {
            throw new \Exception(gT('Could not parse config.xml file.'));
        }

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
        $tempdir = $this->getTempdir();
        if ($tempdir) {
            rmdirr($tempdir);
        }

        // Reset user state.
        $this->clearTmpdir();
    }

    /**
     * Get tmp tempdir for extension to unzip in.
     * @return string
     */
    protected function getTempdir()
    {
        // NB: Since the installation procedure can span several page reloads,
        // we save the tempdir in the user session.
        $tempdir = App()->user->getState('filefetcheruploadzip_tmpdir');
        if (empty($tempdir)) {
            $tempdir = \Yii::app()->getConfig("tempdir");
            $tempdir = createRandomTempDir($tempdir, 'install_');
            App()->user->setState('filefetcheruploadzip_tmpdir', $tempdir);
        }
        return $tempdir;
    }

    /**
     * Set user session tempdir to null.
     * @return void
     */
    protected function clearTmpdir()
    {
        App()->user->setState('filefetcheruploadzip_tmpdir', null);
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
            throw new \Exception(gT('Unzipped file is too big.'));
        }
    }

    /**
     * @param string $tempdir
     * @return void
     */
    protected function extractZipFile($tempdir)
    {
        \Yii::import('application.helpers.common_helper', true);
        \Yii::app()->loadLibrary('admin.pclzip');

        $this->checkZipBom();

        if (!is_file($_FILES['the_file']['tmp_name'])) {
            throw new \Exception(
                gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder.")
            );
        }

        if (empty($this->filterName)) {
            throw new \Exception("No filter name is set, can't unzip.");
        }

        $zip = new \PclZip($_FILES['the_file']['tmp_name']);
        $aExtractResult = $zip->extract(
            PCLZIP_OPT_PATH,
            $tempdir,
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

    /**
     * Recursively copy source folder $src to destination $dest.
     *
     * @param string $src
     * @param string $dest
     * @return boolean
     * @see https://stackoverflow.com/questions/2050859/copy-entire-contents-of-a-directory-to-another-using-php
     */
    protected function recurseCopy($src, $dest)
    {
        $dir = opendir($src);
        mkdir($dest);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . '/' . $file)) {
                    $this->recurseCopy($src . '/' . $file, $dest . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dest . '/' . $file);
                }
            }
        }
        closedir($dir);

        // TODO: When should this return false?
        return true;
    }
}
