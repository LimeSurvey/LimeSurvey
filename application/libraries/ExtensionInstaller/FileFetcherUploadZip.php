<?php

namespace LimeSurvey\ExtensionInstaller;

use Exception;
use InvalidArgumentException;
use ExtensionConfig;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Extension file fetcher for upload ZIP file.
 * Must work for all extension types: plugins, theme, question theme, etc.
 *
 * @since 2018-09-25
 * @author LimeSurvey GmbH
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
     *
     * @return void
     */
    public function fetch()
    {
        $this->checkFileSizeError();
        $this->clearTmpdir();
        $this->extractZipFile($this->getTempdir());
    }

    /**
     * Move files from tempdir to final destdir.
     *
     * @param string $destdir
     * @return boolean
     */
    public function move($destdir)
    {
        if (empty($destdir)) {
            throw new InvalidArgumentException('Missing destdir argument');
        }

        $tempdir = $this->getTempdir();
        if (empty($tempdir)) {
            throw new Exception(gT('Temporary folder cannot be determined.'));
        }

        if (!file_exists($tempdir)) {
            throw new Exception(gT('Temporary folder does not exist.'));
        }

        if (!file_exists($destdir)) {
            // NB: mkdir() always applies the set umask to 0777. See https://www.php.net/manual/en/function.mkdir
            mkdir($destdir, 0777, true);
        }

        if (!is_writable(dirname($destdir))) {
            throw new Exception(gT('Cannot move files due to permission problem. ' . $destdir));
        }

        if (file_exists($destdir) && !rmdirr($destdir)) {
            throw new Exception('Could not remove old files.');
        }

        return $this->recurseCopy($tempdir, $destdir);
    }

    /**
     * Get config from unzipped zip file, but in temp dir. fetch() must be called before this.
     *
     * @return ExtensionConfig
     * @throws Exception
     */
    public function getConfig()
    {
        $tempdir = $this->getTempdir();
        if (empty($tempdir)) {
            throw new Exception(gT('No temporary folder, cannot read configuration file.'));
        }

        $config = $this->getConfigFromDir($tempdir);

        if (empty($config)) {
            throw new Exception(gT('Could not parse config.xml file.'));
        }

        return $config;
    }

    /**
     * Look for config.xml in $tempdir
     * Recursively searches the folders if config.xml is not in root folder.
     *
     * @param string $tempdir
     * @return ExtensionConfig|null
     */
    public function getConfigFromDir(string $tempdir)
    {
        $configFile = $tempdir . DIRECTORY_SEPARATOR . 'config.xml';

        if (file_exists($configFile)) {
             return ExtensionConfig::loadFromFile($configFile);
        } else {
            $it = new RecursiveDirectoryIterator($tempdir);
            // @see https://stackoverflow.com/questions/1860393/recursive-file-search-php
            foreach (new RecursiveIteratorIterator($it) as $file) {
                // @see https://stackoverflow.com/questions/619610/whats-the-most-efficient-test-of-whether-a-php-string-ends-with-another-string?lq=1
                if (stripos(strrev((string) $file), strrev('config.xml')) === 0) {
                    return ExtensionConfig::loadFromFile($file);
                }
            }
        }
        throw new Exception(gT('Configuration file config.xml does not exist.'));
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
            throw new Exception(gT('Found no file'));
        }

        if ($_FILES['the_file']['error'] == 1 || $_FILES['the_file']['error'] == 2) {
            throw new Exception(
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
    protected function checkZipBomb()
    {
        // Check zip bomb.
        \Yii::import('application.helpers.common_helper', true);
        if (isZipBomb($_FILES['the_file']['name'])) {
            throw new Exception(gT('Unzipped file is too big.'));
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

        $this->checkZipBomb();

        if (!is_file($_FILES['the_file']['tmp_name'])) {
            throw new Exception(
                gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder.")
            );
        }

        if (empty($this->filterName)) {
            throw new Exception("No filter name is set, can't unzip.");
        }

        $zip = new \ZipArchive();
        $zip->open($_FILES['the_file']['tmp_name']);

        $files = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (empty($filename)) {
                continue;
            }
            $isFolder = (substr($filename, -1) === '/');
            // Filter files
            if (!empty($this->filterName) && function_exists($this->filterName)) {
                $fileInfo = [
                    'filename' => $tempdir . DIRECTORY_SEPARATOR . $filename,
                    'store_filename' => $filename,
                    'folder' => $isFolder,
                ];
                $fileInfo = array_merge($fileInfo, $zip->statIndex($i));
                if (!call_user_func($this->filterName, $fileInfo)) {
                    continue;
                }
            }
            $files[] = $filename;
        }

        if ($zip->extractTo($tempdir, $files) === false) {
            throw new Exception(
                gT("This file is not a valid ZIP file archive. Import failed.")
                . ' ' . $zip->getStatusString()
            );
        }

        $zip->close();
    }

    /**
     * Recursively copy source folder $src to destination $dest.
     *
     * @param string $src
     * @param string $dest
     * @return boolean
     * @see https://stackoverflow.com/questions/2050859/copy-entire-contents-of-a-directory-to-another-using-php
     * @todo Inject FileIO wrapper and add unit-test
     */
    public function recurseCopy($src, $dest)
    {
        $dir = opendir($src);
        if (!file_exists($dest)) {
            if (!mkdir($dest)) {
                throw new Exception('Could not create folder ' . $dest);
            }
        }
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . '/' . $file)) {
                    // If folder name === extension name, skip one folder level to avoid duplicates.
                    if ($file === $this->getConfig()->getName()) {
                        $this->recurseCopy($src . '/' . $file, $dest . '/../' . $file);
                    } else {
                        $this->recurseCopy($src . '/' . $file, $dest . '/' . $file);
                    }
                } else {
                    if (!copy($src . '/' . $file, $dest . '/' . $file)) {
                        throw new Exception('Could not copy to ' . $file);
                    }
                }
            }
        }
        closedir($dir);

        // TODO: When should this return false?
        return true;
    }
}
