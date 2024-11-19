<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2015 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
* Update Form Model
*
* This model retrieves all the data Comfort Updates needs. Most of them come from request to the Update Server.
*
* @package        LimeSurvey
* @subpackage    Backend
* @inheritdoc
*/
class UpdateForm extends CFormModel
{
    /** @var string $build The build id */
    public $build;

    /** @var  string $view The view to display : used only for welcome type views to let the server choose which view will be displayed */
    public $view;

    // Proxy infos
    /** @var string $proxy_host_name */
    private $proxy_host_name;
    /** @var integer $proxy_host_port */
    private $proxy_host_port;


    // File system infos
    /** @var  string $tempdir */
    private $tempdir;
    /** @var  string $rootdir */
    private $rootdir;
    /** @var string $publicdir  */
    private $publicdir;

    // Session
    /** @var string $path_cookie  */
    private $path_cookie;

    /** @inheritdoc */
    public function init()
    {
        $this->build = Yii::app()->getConfig("buildnumber");
        $this->proxy_host_name = Yii::app()->getConfig("proxy_host_name", "");
        $this->proxy_host_port = Yii::app()->getConfig("proxy_host_port", 80);
        $this->tempdir = Yii::app()->getConfig("tempdir");
        $this->rootdir = Yii::app()->getConfig("rootdir");
        $this->publicdir = Yii::app()->getConfig("publicdir");
        $this->path_cookie = $this->tempdir . DIRECTORY_SEPARATOR . 'comfort_updater_cookie.txt';
    }

    /**
     * First call to the server : This function requests the latest update information from the update server necessary to build the update buttons.
     * If any error occurred (server not answering, no curl, server returns error, etc.), the view check_updates/update_buttons/_updatesavailable_error will be rendered by the controller.
     *
     * @param string|boolean $crosscheck if it checks for info for both stable and unstable branches
     * @return stdClass Contains update information or error object
     */
    public function getUpdateInfo($crosscheck = "1")
    {
        if (Yii::app()->getConfig("updatable")) {
            if ($this->build != '') {
                $crosscheck = (int) $crosscheck;
                $getters = '/index.php?r=updates/updateinfo&currentbuild=' . $this->build . '&id=' . md5((string) getGlobalSetting('SessionName')) . '&crosscheck=' . $crosscheck;
                $content = $this->performRequest($getters);
            } else {
                $content = new stdClass();
                $content->result = false;
                $content->error = "no_build";
            }
        } else {
            $content = new stdClass();
            $content->result = false;
            $content->error = "update_disable";
        }
        return $content;
    }

    /**
     * The server will do some checks and will ask for the correct view to be diplayed.
     *
     * @param string $updateKey the update key -
     * @param string $destinationBuild
     * @return string html
     */
    public function getWelcomeMessage($updateKey, $destinationBuild)
    {
        // First, we destroy any previous cookie :
        if (file_exists(realpath($this->path_cookie))) {
            unlink($this->path_cookie);
        }

        $updater_version = Yii::app()->getConfig("updaterversion");
        touch($this->path_cookie);
        $getters = '/index.php?r=updates/getwelcome&currentbuild=' . $this->build . '&keyid=' . $updateKey . '&destinationbuild=' . $destinationBuild . '&updater_version=' . $updater_version;
        $content = $this->performRequest($getters, true);
        return $content;
    }

    /**
     * check if a submitted update key exist on the update server and if it's valid
     * @param string $submittedUpdateKey the submitted update key
     * @return boolean true if it exists, false if it doesn't
     */
    public function checkUpdateKeyonServer($submittedUpdateKey)
    {
        $getters = '/index.php?r=updates/checkupdatekey&keyid=' . $submittedUpdateKey;
        $content = $this->performRequest($getters);
        return $content;
    }

    /**
     * create or update the updateKey to the submitted value
     * @param string $submittedUpdateKey the new key id
     * @return SettingGlobal|array<string,false|string> the new update key if success, CActiveRecord result if error
     *
     * TODO : should return same status than server to use the same view render
     */
    public function setUpdateKey($submittedUpdateKey)
    {
        // The update keys never contains special characters, so, it should not affect the key
        // If it affects the key : then the key was wrong... and the database is safe
        $submittedUpdateKey = trim(htmlspecialchars(addslashes($submittedUpdateKey)));

        $updateKey = SettingGlobal::model()->findByPk('update_key');
        if (!$updateKey) {
            // Create
            $updateKey = new SettingGlobal();
            $updateKey->stg_name = 'update_key';
            $updateKey->stg_value = $submittedUpdateKey;
            $result = $updateKey->save();
        } else {
            //Update
            $result = SettingGlobal::model()->updateByPk('update_key', array('stg_value' => $submittedUpdateKey));
        }

        if ($result) {
            // If success we return the updateKey row
            /** @var SettingGlobal $updateKey */
            $updateKey = SettingGlobal::model()->findByPk('update_key');
            return $updateKey;
        } else {
            // Else we return the errors
            return array('result' => false, 'error' => 'db_error');
        }
    }


    /**
     * This function check for local errors such as readonly files, available space, php ini config etc.
     * It calls the server to get the list of files/directories to check
     *
     * @param int  $destinationBuild : the id of the destination build
     * @return stdClass $checks ; an object indexing local checks
     */
    public function getLocalChecks($destinationBuild)
    {
        $checks = new stdClass();

        $checks->files = $this->getFileSystemCheckList();
        $checks->php = $this->phpVerCheck($destinationBuild);
        $checks->php_modules = $this->getModuleChecks($destinationBuild);
        $checks->mysql = $this->getMysqlChecks($destinationBuild);

        return $checks;
    }



    /**
     * This function check for local arrors such as readonly files/directory to update the updater itself
     *
     * @return object $checks
     */
    public function getLocalChecksForUpdater()
    {

        $getters = '/index.php?r=updates/filesystemchecklistforupdater';
        $content = $this->performRequest($getters);
        $toCheck = $content->list;
        $readOnly = array();

        // We check the write permission of files
        $lsRootPath = dirname((string) Yii::app()->request->scriptFile) . '/';
        foreach ($toCheck as $check) {
            if (file_exists($lsRootPath . $check)) {
                if (!is_writable($lsRootPath . $check)) {
                    $readOnly[] = $lsRootPath . $check;
                }
            }
        }

        if (count($readOnly) <= 0) {
            return (object) array('result' => true);
        }

        return  (object) array('result' => false, 'readOnly' => $readOnly);
    }


    /**
     * This function requests the change log between the curent build and the destination build
     *
     * @param int $destinationBuild
     * @return mixed|stdClass
     */
    public function getChangeLog($destinationBuild)
    {
        $getters = '/index.php?r=updates/changelog&frombuild=' . $this->build . '&tobuild=' . $destinationBuild;
        $content = $this->performRequest($getters);
        return $content;
    }

    /**
     * This function requests the list of changed file between two build
     * @param int $destinationBuild
     * @return mixed|stdClass
     */
    public function getChangedFiles($destinationBuild)
    {
        $getters = '/index.php?r=updates/changed-files&frombuild=' . $this->build . '&tobuild=' . $destinationBuild;
        $content = $this->performRequest($getters);
        return $content;
    }

    /**
     * This function requests a download to the server
     * @param int $downloadid the id of the download on the server
     * @param string $tobuild
     * @return object
     * //TODO $downloadid not used locally
     */
    public function downloadUpdateFile($downloadid, $tobuild)
    {
        $getters = '/index.php?r=updates/download&frombuild=' . $this->build . '&tobuild=' . $tobuild;
        $getters .= "&access_token=" . $_REQUEST['access_token'];
        $file = $this->performDownload($getters);
        return $file;
    }


    /**
     * This function download the file to update the updater to the last version
     * @param string $tobuild
     * @return object
     */
    public function downloadUpdateUpdaterFile($tobuild)
    {
        $getters = '/index.php?r=updates/download-updater&tobuild=' . $tobuild . '&frombuild=' . $this->build;
        $file = $this->performDownload($getters, 'update_updater');
        return $file;
    }


    /**
     * Unzip the update file.
     * @param string $file_to_unzip
     * @return object NULL if success or message error void (TODO : return status)
     */
    public function unzipUpdateFile($file_to_unzip = 'update.zip')
    {
        if (file_exists($this->tempdir . DIRECTORY_SEPARATOR . $file_to_unzip)) {
            // To debug pcl_zip, uncomment the following line :    require_once('/var/www/limesurvey/LimeSurvey/application/libraries/admin/pclzip/pcltrace.lib.php'); require_once('/var/www/limesurvey/LimeSurvey/application/libraries/admin/pclzip/pclzip-trace.lib.php'); PclTraceOn(2);
            // To debug pcl_zip, comment the following line:

            $archive = new PclZip($this->tempdir . DIRECTORY_SEPARATOR . $file_to_unzip, false);

            // TODO : RESTORE REPLACE NEWER !!
            // To debug pcl_zip, uncomment the following line :
            //if ($archive->extract(PCLZIP_OPT_PATH, $this->rootdir.'/', PCLZIP_OPT_REPLACE_NEWER)== 0)
            if ($archive->extract(PCLZIP_OPT_PATH, $this->rootdir . DIRECTORY_SEPARATOR, PCLZIP_OPT_REPLACE_NEWER) == 0) {
                // To debug pcl_zip, uncomment the following line :
                //PclTraceDisplay(); die();
                $return = array('result' => false, 'error' => 'unzip_error', 'message' => $archive->errorInfo(true));
                return (object) $return;
            }
            $return = array('result' => true);
            return (object) $return;
        } else {
            $return = array('result' => false, 'error' => 'zip_update_not_found');
            return (object) $return;
        }
    }

    /**
     * Unzip the update file.
     * @return object NULL if success or message error void (TODO : return status)
     */
    public function unzipUpdateUpdaterFile()
    {
        Yii::app()->loadLibrary("admin/pclzip");
        $file_to_unzip = 'update_updater.zip';
        return $this->unzipUpdateFile($file_to_unzip);
    }

    /**
     * Delete the files tagged as deleted in the update
     *
     * @param array $updateinfos
     * @return object
     */
    public function removeDeletedFiles($updateinfos)
    {
        foreach ($updateinfos as $file) {
            $sFileToDelete = str_replace("..", "", (string) $file['file']);
            if ($file['type'] == 'D' && file_exists($this->rootdir . $sFileToDelete)) {
                if (is_file($this->rootdir . $sFileToDelete)) {
                    // TODO: Deal with error here
                    if (!@unlink($this->rootdir . $sFileToDelete)) {
                        $return = array('result' => false, 'error' => 'cant_remove_deleted_files', 'message' => 'file : ' . $sFileToDelete);
                        return (object) $return;
                    }
                } else {
                    if (!rmdir($this->rootdir . $sFileToDelete)) {
                        $return = array('result' => false, 'error' => 'cant_remove_deleted_directory', 'message' => 'dir : ' . $sFileToDelete);
                        return (object) $return;
                    }
                }
            }
        }
        $return = array('result' => true);
        return (object) $return;
    }

    /**
     * Delete a tmp file
     * @param string $sTmpFile The name of the file to delete in tmp/ directory
     * @return object
     */
    public function removeTmpFile($sTmpFile = 'update.zip')
    {
        $sTmpFilePath = $this->tempdir . DIRECTORY_SEPARATOR . $sTmpFile;
        if (file_exists($sTmpFilePath)) {
            if (!@unlink($sTmpFilePath)) {
                $return = array('result' => false, 'error' => 'cant_remove_update_file', 'message' => 'file : ' . $sTmpFilePath);
                return (object) $return;
            }
        }

        $return = array('result' => true);
        return (object) $return;
    }

    /**
     * Republish all the assets
     * For now, only for frontEnd templates
     * (backend theme are still based on file assets, not directory assets )
     */
    public function republishAssets()
    {
        // Don't touch symlinked assets because it won't work
        if (App()->getAssetManager()->linkAssets) {
            return;
        }

        // Delete all the content in the asset directory, but not the directory itself nor the index.html file at its root ^^
        $sAssetsDir = Yii::app()->getConfig('tempdir') . '/assets/';
        $dir = dir($sAssetsDir);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..' || $entry == 'index.html') {
                continue;
            }
            rmdirr($sAssetsDir . DIRECTORY_SEPARATOR . $entry);
        }
    }


    /**
     * Update the version file to the destination build version
     * @param string $destinationBuild the id of the new version
     * @return null : will never fail (file access tested before), or only if user changed it manually
     */
    public function updateVersion($destinationBuild)
    {
        $destinationBuild = (int) $destinationBuild;
        @ini_set('auto_detect_line_endings', '1');
        $versionlines = file($this->rootdir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'version.php');
        $handle = fopen($this->rootdir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'version.php', "w");
        foreach ($versionlines as $line) {
            if (strpos((string) $line, 'buildnumber') !== false) {
                $line = '$config[\'buildnumber\'] = ' . $destinationBuild . ';' . "\r\n";
            }
            fwrite($handle, (string) $line);
        }
        fclose($handle);
        Yii::app()->setConfig("buildnumber", $destinationBuild);
        return;
    }

    /**
     * This function provide status information about files presents on the system that will be afected by the update : do they exist ? are they writable ? modified ?
     *
     * @param array $updateinfo Array of updated files
     * @return array
     */
    public function getFileStatus($updateinfo)
    {
        $existingfiles = array();
        $modifiedfiles = array();
        $readonlyfiles = array();

        foreach ($updateinfo as $file) {
            $file = (array) $file;
            $readonly_checked_file = $this->getReadOnlyCheckedFile($file);

            if ($readonly_checked_file->type == 'readonlyfile') {
                $readonlyfiles[] = $readonly_checked_file->file;
            }

            $checkedfile = $this->getCheckedFile($file);
            switch ($checkedfile->type) {
                case 'modifiedfile':
                    $modifiedfiles[] = $checkedfile->file;
                    break;

                case 'existingfile':
                    $existingfiles[] = $checkedfile->file;
            }
        }

        // Format the array for presentation in the view
        if (count($readonlyfiles)) {
            foreach (array_unique($readonlyfiles) as $sFile) {
                // If substr return wrong, the root directory is not writable
                $sCleanFile = substr((string) $sFile, strlen((string) Yii::app()->getConfig("rootdir")));
                $aReadOnlyFiles[] = ($sCleanFile) ? $sCleanFile : $sFile;
            }
            sort($aReadOnlyFiles);
            $readonlyfiles = $aReadOnlyFiles;
        }

        return array(
                'readonlyfiles' => $readonlyfiles,
                'modifiedfiles' => $modifiedfiles,
                'existingfiles' => $existingfiles
            );
    }

    /**
     * Create a backup of the files that will be updated
     * @param array[] $updateinfos array of files to updated (needs file field)
     * @return stdClass error/success and text message
     */
    public function backupFiles($updateinfos)
    {
        $filestozip = array();

        foreach ($updateinfos as $file) {
            // To block the access to subdirectories
            $sFileToZip = str_replace("..", "", (string) $file['file']);

            if (is_file($this->publicdir . $sFileToZip) === true && basename($sFileToZip) != 'config.php' && filesize($this->publicdir . $sFileToZip) > 0) {
                $filestozip[] = $this->publicdir . $sFileToZip;
            }
        }

        Yii::app()->loadLibrary("admin/pclzip");
        $basefilename = dateShift(date("Y-m-d H:i:s"), "Y-m-d", Yii::app()->getConfig('timeadjust')) . '_' . md5(uniqid(rand(), true));
        $archive = new PclZip($this->tempdir . DIRECTORY_SEPARATOR . 'LimeSurvey_files_backup_' . $basefilename . '.zip');
        $v_list = $archive->add($filestozip, PCLZIP_OPT_REMOVE_PATH, $this->publicdir);
        $backup = new stdClass();

        if (!$v_list == 0) {
            $backup->result = true;
            $backup->basefilename = $basefilename;
            $backup->tempdir = $this->tempdir;
        } else {
            $backup->result = false;
            $backup->error = 'cant_zip_backup';
            $backup->message = $archive->errorInfo(true);
        }
        return $backup;
    }

    /**
     * Create a backup of the DataBase
     * @param string $destinationBuild
     * @return stdClass error/success and text message
     */
    public function backupDb($destinationBuild)
    {
        $backupDb = new stdClass();
        $dbType = Yii::app()->db->getDriverName();

        // We backup only mysql/mysqli database
        // TODO : add postgresql
        if (in_array($dbType, array('mysql', 'mysqli')) && Yii::app()->getConfig('demoMode') != true) {
            // This function will call the server to get the requirement about DB, such as max size
            $dbChecks = $this->getDbChecks($destinationBuild);

            // Test if user defined by himself a max size for dbBackup
            if (Yii::app()->getConfig("maxdbsizeforbackup") && Yii::app()->getConfig("maxdbsizeforbackup") > 0) {
                 $dbChecks->dbSize = Yii::app()->getConfig("maxdbsizeforbackup");
            }

            if ($dbChecks->result) {
                $currentDbVersion = Yii::app()->getConfig("dbversionnumber");
                if ($currentDbVersion < $dbChecks->dbVersion) {
                    $dbSize = $this->getDbTotalSize();
                    $backupDb->message = 'db_change';

                    if ($dbSize <= $dbChecks->dbSize) {
                        return $this->createDbBackup();
                    } else {
                        $backupDb->result = false;
                        $backupDb->message = 'db_too_big';
                    }
                } else {
                    $backupDb->result = false;
                    $backupDb->message = 'no_db_changes';
                }
            }
        } else {
            $backupDb->result = false;
            $backupDb->message = 'not_mysql';
        }

        return $backupDb;
    }

    /**
     * Check if assets needs to be updated
     * @return boolean
     */
    private function checkAssets()
    {
        $iAssetVersionNumber  = Yii::app()->getConfig('assetsversionnumber'); // From version.php
        $iCurrentAssetVersion = GetGlobalSetting('AssetsVersion'); // From setting_global table

        if ($iAssetVersionNumber != $iCurrentAssetVersion) {
            self::republishAssets();
            SettingGlobal::setSetting('AssetsVersion', $iAssetVersionNumber);
        }
        return false;
    }

    /**
     * Check if an update is available, and prints the update notification
     * It also check if the assets need to be republished
     *
     * @return object
     */
    public function getUpdateNotification()
    {
        $this->checkAssets();
        if (Yii::app()->getConfig('updatable') && Permission::model()->hasGlobalPermission('superadmin')) {
            $today = new DateTime("now");
            $next_update_check = Yii::app()->session['next_update_check'];

            if (is_null($next_update_check) || ($next_update_check < $today) || is_null(Yii::app()->session['update_result'])) {
                // Turn on the alert notification
                Yii::app()->session['notificationstate'] = 1;

                $updates = $this->getUpdateInfo('1');
                $update_available = false;

                if ($updates->result) {
                    unset($updates->result);

                    $security_update_available = false;
                    $unstable_update_available = false;

                    if (!is_array($updates) && !($updates instanceof Countable)) {
                        $updates = (array) $updates;
                    }

                    if (count($updates) > 0) {
                        $update_available = true;
                        foreach ($updates as $update) {
                            if ($update->security_update) {
                                $security_update_available = true;
                            }

                            if (!in_array($update->branch, ['master','5.x','3.x-LTS'])) {
                                $unstable_update_available = true;
                            }
                        }
                    }

                    Yii::app()->session['update_result'] = $update_available;
                    Yii::app()->session['security_update'] = $security_update_available;

                    // If only one update is available and it's an unstable one, then it will be displayed in a different color, and will be removed, not minified when clicked
                    if (count((array) $updates) == 1 && $unstable_update_available) {
                        Yii::app()->session['unstable_update'] = $unstable_update_available;
                    } else {
                        Yii::app()->session['unstable_update'] = false;
                    }

                    $next_update_check = $today->add(new DateInterval('P1D'));
                    Yii::app()->session['next_update_check'] = $next_update_check;
                    $updates = array('result' => $update_available, 'security_update' => $security_update_available, 'unstable_update' => $unstable_update_available);
                } else {
                    $next_update_check = $today->add(new DateInterval('P1D'));
                    Yii::app()->session['next_update_check'] = $next_update_check;
                    Yii::app()->session['update_result'] = false;
                    Yii::app()->session['unstable_update'] = false;
                }
            } else {
                $update_available = Yii::app()->session['update_result'];
                $unstable_update_available = Yii::app()->session['unstable_update'];
                $security_update_available = Yii::app()->session['security_update'];
                $updates = array('result' => $update_available, 'security_update' => $security_update_available, 'unstable_update' => $unstable_update_available);
            }
        } else {
            Yii::app()->session['notificationstate'] = 0;
            Yii::app()->session['update_result'] = false;
            $updates = array('result' => false);
        }
        return (object) $updates;
    }

    //// END OF INTERFACE ////

    /**
     * Call the server to get the necessary datas to check the database
     * @param string $destinationBuild
     * @return mixed|stdClass
     */
    private function getDbChecks($destinationBuild)
    {
        $getters = '/index.php?r=updates/get-db-checks&build=' . $destinationBuild;
        $content = $this->performRequest($getters);
        return $content;
    }


    /**
     * Return the total size of the current database in MB
     * @return string
     */
    private function getDbTotalSize()
    {
        $command = Yii::app()->db->createCommand("SHOW TABLE STATUS");
        $results = $command->query();

        $size = 0;
        foreach ($results as $row) {
            $size += $row["Data_length"] + $row["Index_length"];
        }

        $dbSize = number_format($size / (1024 * 1024), 2);

        return $dbSize;
    }

    /**
     * Create a backup of the DataBase
     * @return stdClass result of backup
     */
    private function createDbBackup()
    {
        Yii::app()->loadHelper("admin/backupdb");
        $backupDb = new stdClass();
        $basefilename = dateShift(date("Y-m-d H:i:s"), "Y-m-d", Yii::app()->getConfig('timeadjust')) . '_' . md5(uniqid(rand(), true));
        $sfilename = $this->tempdir . DIRECTORY_SEPARATOR . "backup_db_" . randomChars(20) . "_" . dateShift(date("Y-m-d H:i:s"), "Y-m-d", Yii::app()->getConfig('timeadjust')) . ".sql";
        $dfilename = $this->tempdir . DIRECTORY_SEPARATOR . "LimeSurvey_database_backup_" . $basefilename . ".zip";
        outputDatabase('', false, $sfilename);

        if (is_file($sfilename) && filesize($sfilename)) {
            $archive = new PclZip($dfilename);
            $v_list = $archive->add(array($sfilename), PCLZIP_OPT_REMOVE_PATH, $this->tempdir, PCLZIP_OPT_ADD_TEMP_FILE_ON);
            unlink($sfilename);
            if ($v_list == 0) {
                $backupDb->result = false;
                $backupDb->message = 'db_backup_zip_failed';
            } else {
                $backupDb->result = true;
                $backupDb->message = htmlspecialchars($dfilename);
                $backupDb->fileurl = Yii::app()->getBaseUrl(true) . '/tmp/LimeSurvey_database_backup_' . $basefilename . '.zip';
            }
        } else {
            $backupDb->result = false;
            $backupDb->message = htmlspecialchars('db_backup_failed');
        }
        return $backupDb;
    }


    /**
     * Check if a file (added/deleted/modified) from the update exists yet on the server and if it is readonly
     * @param array $file a file to update (must contain file and type indexes)
     * @return stdClass containing a list of read only files
     */
    private function getReadOnlyCheckedFile($file)
    {
        $checkedfile = new stdClass();
        $checkedfile->type = '';
        $checkedfile->file = '';

        // We check if the file read only
        if ($file['type'] == 'A' && !file_exists($this->rootdir . $file['file']) || ($file['type'] == 'D' && file_exists($this->rootdir . $file['file']))) {
            $searchpath = $this->rootdir . $file['file'];
            $is_writable = is_writable(dirname($searchpath));

            // LOUIS : snippet from the original code.
            while (!$is_writable && strlen($searchpath) > strlen($this->rootdir)) {
                $searchpath = dirname($searchpath);
                if (file_exists($searchpath)) {
                    $is_writable = is_writable($searchpath);
                    break;
                }
            }

            if (!$is_writable) {
                $checkedfile->type = 'readonlyfile';
                $checkedfile->file = $searchpath;
            }
        } elseif (
            file_exists($this->rootdir . $file['file'])
            && !is_writable($this->rootdir . $file['file'])
        ) {
            $checkedfile->type = 'readonlyfile';
            $checkedfile->file = $this->rootdir . $file['file'];
        }

        return $checkedfile;
    }



    /**
     * Check if a file (added/deleted/) on the update yet exists on the server, or has been modified
     *
     * @param array $file  array of files to update (must contain file, type and chekcsum indexes)
     * @return stdClass containing a list of read only files
     */
    private function getCheckedFile($file)
    {
        $checkedfile = new stdClass();
        $checkedfile->type = '';
        $checkedfile->file = '';

        if ($file['file'] != '/application/config/version.php') {
            // We check if the file exist
            if ($file['type'] == 'A' && file_exists($this->rootdir . $file['file'])) {
                //A new file, check if this already exists
                $checkedfile->type = 'existingfile';
                $checkedfile->file = $file;
            } elseif (
                // We check if the file has been modified
                ($file['type'] == 'D' || $file['type'] == 'M') && is_file($this->rootdir . $file['file'])
                && sha1_file($this->rootdir . $file['file']) != $file['checksum']
            ) {
                $checkedfile->type = 'modifiedfile';
                $checkedfile->file = $file;
            }
        }

        return $checkedfile;
    }


    /**
     * Call the server to get the list of files and directory to check (and check type : writable / free space)
     * @return array containing the list
     */
    private function getFileSystemCheckList()
    {
        $getters = '/index.php?r=updates/filesystemchecklist';
        $content = $this->performRequest($getters);
        $fileSystemCheck = $content->list;

        // Strategy Pattern : different way to buil the path of the file
        // Right now, calling fileSystemCheckAppath() or fileSystemCheckConfig()
        // Could also use params in the futur : YAGNI !!!!!
        $files = array();
        foreach ($fileSystemCheck as $key => $obj) {
            $method = 'fileSystemCheck' . $obj->type;
            $files[$key] = $this->$method($obj);
        }

        return $files;
    }

    /**
     * Check if a file / dir is writable AND/OR if it has enough freespace
     * @param object $obj an object containing the name of the file/directory to check, and what must be checked
     * @return stdClass the result of the test
     */
    private function fileSystemCheck($obj)
    {
        $check = new stdClass();
        $check->name = $obj->name;

        if ($obj->writableCheck) {
            $check->writable = is_writable($obj->name);
        } else {
            $check->writable = 'pass';
        }
        if ($obj->freespaceCheck && function_exists('disk_free_space')) {
            $check->freespace = (@disk_free_space($obj->name) > $obj->minfreespace);
        } else {
            $check->freespace = 'pass';
        }

        $check->name = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, (string) $check->name);

        return $check;
    }

    /**
     * build the file / Directory path using APPATH, and then call the check method
     * NB: This method is called by building the method name string in getFileSystemCheckList.
     * @param object $obj an object containing the name of the file/directory to check, and what must be checked
     * @return stdClass the result of the test
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function fileSystemCheckAppath($obj)
    {
        $obj->name = APPPATH . $obj->name;
        return  $this->fileSystemCheck($obj);
    }


    /**
     * build the file / Directory path using getConfig(), and then call the check method
     * NB: This method is called by building the method name string in getFileSystemCheckList.
     * @param object $obj an object containing the name of the file/directory to check, and what must be checked
     * @return stdClass the result of the test
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function fileSystemCheckConfig($obj)
    {
        $obj->name = Yii::app()->getConfig($obj->name);
        return  $this->fileSystemCheck($obj);
    }

    /**
     * Get the required minimal php version for destination build from server, and compare it to local php version.
     *
     * @param int $build the buildId to test
     * @return stdClass the success/error result
     */
    private function phpVerCheck($build)
    {
        $getters = '/index.php?r=updates/get-php-ver&build=' . $build;
        $php_ver = $this->performRequest($getters);

        $return = new stdClass();
        $return->php_ver = $php_ver->php_version;

        if (version_compare(PHP_VERSION, $return->php_ver) >= 0) {
            $return->result = true;
        } else {
            $return->result = false;
            $return->local_php_ver = PHP_VERSION;
        }
        return ($return);
    }

    /**
     * Get the list of required PHP modules for this update
     *
     * @param int $build the buildId to test
     * @return stdClass the success/error message
     */
    private function getModuleChecks($build)
    {
        $getters = '/index.php?r=updates/get-php-modules&build=' . $build;
        $php_module_list = $this->performRequest($getters);

        $return = new stdClass();
        if ($php_module_list->result) {
            foreach ($php_module_list->php_modules as $module => $state) {
                $return->$module = new stdClass();
                // Required or Optional
                $return->$module->$state = true;
                // Installed or not
                $return->$module->installed = (extension_loaded($module)) ? true : false;
            }
        }

        return($return);
    }

    // Get the minimum required mysql version from the server

    /**
     * @param integer $build
     * @return stdClass
     */
    private function getMysqlChecks($build)
    {
        $checks = new stdClass();
        $dbType = Yii::app()->db->getDriverName();
        if (in_array($dbType, array('mysql', 'mysqli'))) {
            $checks->docheck = 'do';
            $getters = '/index.php?r=updates/get-mysql-ver&build=' . $build;
            $mysql_requirements = $this->performRequest($getters);
            $checks->mysql_ver = $mysql_requirements->version;
            $checks->local_mysql_ver = Yii::app()->db->getServerVersion();
            $checks->result = (version_compare($checks->local_mysql_ver, $checks->mysql_ver, '<')) ? false : true;
        } else {
            $checks->docheck = 'pass';
        }
        return($checks);
    }

    /**
     * Returns the supported protocol extension (https/http)
     *
     * @return string
     */
    private function getProtocol()
    {
        $server_ssl = Yii::app()->getConfig("comfort_update_server_ssl");
        if ($server_ssl === 1 && extension_loaded("openssl")) {
                return 'https://';
        }
        return 'http://';
    }

    /**
     * This function download a file from the ComfortUpdate and accept redirection
     * @param string $getters request parameters
     * @param string $fileName
     * @return object containing success = TRUE or error message
     */
    private function performDownload($getters, $fileName = 'update')
    {
        // TODO : Could test if curl is loaded, and if not, use httprequest2
        $ch = curl_init();
        $pFile = fopen($this->tempdir . DIRECTORY_SEPARATOR . $fileName . '.zip', 'w');
        curl_setopt($ch, CURLOPT_URL, $this->getProtocol() . Yii::app()->getConfig("comfort_update_server_url") . $getters);
        if ($this->proxy_host_name != '') {
            $proxy = $this->proxy_host_name . ':' . $this->proxy_host_port;
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->path_cookie);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false); // We don't want the header to be written in the zip file !
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FILE, $pFile);
        curl_exec($ch);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE); // But we want the header to be returned to the controller so we can check if a file has been returned
        curl_close($ch);

        $result = ($content_type == "application/zip") ? array("result" => true) : array('result' => false, 'error' => 'error_while_processing_download');

        return (object) $result;
    }


    /**
     * This function perform the request
     * @param string $getters request parameters
     * @param boolean $create_new_cookie_file
     * @return mixed|stdClass the server page answer (json most of the time)
     */
    private function performRequest($getters, $create_new_cookie_file = false)
    {
        if ((extension_loaded("curl"))) {
            if (isset($_REQUEST['access_token'])) {
                $getters .= "&access_token=" . urlencode((string) $_REQUEST['access_token']);
            }
            if (Yii::app()->getConfig("allow_non_public_release")) {
                $getters .= "&debug=1";
            }
            $ch = curl_init($this->getProtocol() . Yii::app()->getConfig("comfort_update_server_url") . $getters);

            if ($this->proxy_host_name != '') {
                $proxy = $this->proxy_host_name . ':' . $this->proxy_host_port;
                curl_setopt($ch, CURLOPT_PROXY, $proxy);
            }

            if ($create_new_cookie_file) {
                curl_setopt($ch, CURLOPT_COOKIEJAR, $this->path_cookie);
            } else {
                curl_setopt($ch, CURLOPT_COOKIEFILE, $this->path_cookie);
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $content = curl_exec($ch);
            curl_close($ch);

            $content_decoded = json_decode(base64_decode($content));
            if (!is_object($content_decoded)) {
                $content_decoded = new stdClass();
                $content_decoded->result = false;
                $content_decoded->error = "no_server_answer";
                $content_decoded->message = $content;
            }
            return $content_decoded;
        } else {
            // Should happen only on first step (get buttons), diplayed in check_updates/update_buttons/_updatesavailable_error.php
            // Could rather define a call to httprequest2 functions.
            return (object) array('result' => false, 'error' => "php_curl_not_loaded");
        }
    }
}
