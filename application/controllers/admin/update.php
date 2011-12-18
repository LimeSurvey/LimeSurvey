<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* $Id: update.php 10323 2011-06-22 15:33:54Z dionet $
*
*/
list(,$updaterversion)=explode(' ','$Rev: 11155 $');  // this is updated by subversion so don't change this string

/**
 * Update Controller
 *
 * This controller performs updates
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class update extends CAction
{
    /**
     * Executes the action based on given input
     *
     * @access public
     * @return void
     */
    public function run()
    {
        $actions = array_keys($_GET);
        $_GET['method'] = $action = (!empty($actions[0])) ? $actions[0] : '';

        echo $this->getController()->_getAdminHeader(false, true);

        if (!empty($action)) {
            $this->$action($_GET[$action]);
        }
        else
        {
            $this->index();
        }
        echo $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"), true);
    }

    /**
     * Default Controller Action
     */
    function index($subaction = null)
    {
        global $updaterversion;
        $this->_RunUpdaterUpdate();

        $clang = $this->getController()->lang;
        $buildnumber = Yii::app()->getConfig("buildnumber");
        $tempdir = Yii::app()->getConfig("tempdir");
        $updatekey = $this->_getUpdateKey($subaction);
        $error = false;

        if ( $updatekey != '' ) {
            if (!is_writable($tempdir)) {
                $error = true;
            }
            if (!is_writable(APPPATH . 'config/version.php')) {
                $error = true;
            }

            list($httperror, $changelog, $cookies) = $this->_getChangelog($buildnumber, $updaterversion, $updatekey);
        }

        $data = array();
        $data['clang'] = $clang;
        $data['error'] = $error;
        $data['tempdir'] = $tempdir;
        $data['updatekey'] = $updatekey;
        $data['changelog'] = isset($changelog) ? $changelog : '';
        $data['httperror'] = isset($httperror) ? $httperror : '';

        $this->getController()->render('/admin/update/update', $data);
    }

    private function _getChangelog($buildnumber, $updaterversion, $updatekey)
    {
        Yii::app()->loadLibrary('admin/http/http');
        $http = new http;
        $httperror = $this->_requestChangelog($http, $buildnumber, $updaterversion, $updatekey);

        if ($httperror != '') {
            return array($httperror, null);
        }
        return $this->_readChangelog($http);
    }

    private function _readChangelog(http $http)
    {
        $szLines = '';
        $szResponse = '';
        for (; ;) {
            $httperror = $http->ReadReplyBody($szLines, 10000);
            if ($httperror != "" || strlen($szLines) == 0) {
                $changelog = json_decode($szResponse, true);
                $http->SaveCookies($cookies);
                return array($httperror, $changelog, $cookies);
            }
            $szResponse .= $szLines;
        }
    }

    private function _requestChangelog(http $http, $buildnumber, $updaterversion, $updatekey)
    {
        $http->timeout = 0;
        $http->data_timeout = 0;
        $http->user_agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)';
        $http->GetRequestArguments('http://update.limesurvey.org/updates/changelog/' . $buildnumber . '/' . $updaterversion . '/' . $updatekey, $arguments);

        $http->Open($arguments);

        return $http->SendRequest($arguments);
    }

    private function _getUpdateKey($subaction)
    {
        $updatekey = Yii::app()->getConfig("updatekey");
        if ($subaction == 'keyupdate') {
            $updatekey = sanitize_paranoid_string($_POST['updatekey']);
            setGlobalSetting('updatekey', $updatekey);
            return $updatekey;
        }
        return $updatekey;
    }


    function step2()
    {
        $clang = $this->getController()->lang;
        $buildnumber = Yii::app()->getConfig("buildnumber");
        $updatebuild = Yii::app()->getConfig("updatebuild");
        $updatekey = Yii::app()->getConfig("updatekey");

        list($error, $updateinfo, $cookies) = $this->_getChangelog($buildnumber, $updatebuild, $updatekey);
        $readonlyfiles = $this->_getReadOnlyFiles($updateinfo);


        $_SESSION['updateinfo'] = $updateinfo;
        $_SESSION['updatesession'] = $cookies;

        $data = array();
        $data['clang'] = $clang;
        $data['error'] = $error;
        $data['updateinfo'] = $updateinfo;
        $data['readonlyfiles'] = $readonlyfiles;
        $this->getController()->render('/admin/update/step2', $data);
    }

    private function _getReadOnlyFiles($updateinfo)
    {
        // okay, updateinfo now contains all necessary updateinformation
        // Now check if the existing files have the mentioned checksum

        if (!isset($updateinfo['files'])) {
            return array();
        }

        $rootdir = Yii::app()->getConfig("rootdir");
        $existingfiles = array();
        $modifiedfiles = array();
        $readonlyfiles = array();

        foreach ($updateinfo['files'] as $afile)
        {
            $this->_checkFile($afile, $rootdir, $readonlyfiles, $existingfiles, $modifiedfiles);
        }
        return $readonlyfiles;
    }

    private function _checkFile($file, $rootdir, &$readonlyfiles, &$existingfiles, &$modifiedfiles)
    {
        $this->_checkReadOnlyFile($file, $rootdir, $readonlyfiles);


        if ($file['type'] == 'A' && file_exists($rootdir . $file['file'])) {
            //A new file, check if this already exists
            $existingfiles[] = $file;
        }
        elseif (($file['type'] == 'D' || $file['type'] == 'M') && is_file($rootdir . $file['file']) && sha1_file($rootdir . $file['file']) != $file['checksum']) {
            // A deleted or modified file - check if it is unmodified
            $modifiedfiles[] = $file;
        }
    }

    private function _checkReadOnlyFile($file, $rootdir, &$readonlyfiles)
    {
        if ($file['type'] == 'A' && !file_exists($rootdir . $file['file'])) {
            $searchpath = $rootdir . $file['file'];
            $is_writable = is_writable(dirname($searchpath));
            while (!$is_writable && strlen($searchpath) > strlen($rootdir))
            {
                $searchpath = dirname($searchpath);
                if (file_exists($searchpath)) {
                    $is_writable = is_writable($searchpath);
                    break;

                }
            }

            if (!$is_writable) {
                $readonlyfiles[] = $searchpath;
            }
        }
        elseif (file_exists($rootdir . $file['file']) && !is_writable($rootdir . $file['file'])) {
            $readonlyfiles[] = $rootdir . $file['file'];
        }
    }

    function step3()
    {
        $clang = $this->getController()->lang;
        $buildnumber = Yii::app()->getConfig("buildnumber");
        $tempdir = Yii::app()->getConfig("tempdir");
        $updatekey = Yii::app()->getConfig("updatekey");
        $updatebuild = Yii::app()->getConfig("updatebuild");
        //$_POST=$this->input->post();
        Yii::app()->loadLibrary('admin/http/http');
        $rootdir = Yii::app()->getConfig("rootdir");
        $publicdir = Yii::app()->getConfig("publicdir");
        $tempdir = Yii::app()->getConfig("tempdir");
        $databasetype = Yii::app()->db->getDriverName();
        $data = array('clang' => $clang);
        // Request the list with changed files from the server
        $updatekey=getGlobalSetting('updatekey');

        if (!isset( $_SESSION['updateinfo']))
        {
            if ($updateinfo['error']==1)
            {
                setGlobalSetting('updatekey','');
            }
        }
        else
        {
            $updateinfo=$_SESSION['updateinfo'];
        }

        $data['updateinfo'] = $updateinfo;

        // okay, updateinfo now contains all necessary updateinformation
        // Create DB and file backups now

        $basefilename = date("YmdHis-").md5(uniqid(rand(), true));
        //Now create a backup of the files to be delete or modified

        foreach ($updateinfo['files'] as $file)
        {
            if (is_file($publicdir.$file['file'])===true) // Sort out directories
            {
                $filestozip[]=$publicdir.$file['file'];
            }
        }

        Yii::app()->loadLibrary("admin/pclzip/pclzip",array('p_zipname' => $tempdir.DIRECTORY_SEPARATOR.'files-'.$basefilename.'.zip'));
        $archive = $this->pclzip;

        $v_list = $archive->add($filestozip, PCLZIP_OPT_REMOVE_PATH, $publicdir);

        if ($v_list == 0) {
            die("Error : ".$archive->errorInfo(true));
        }

        $data['databasetype'] = $databasetype;

        //TODO: Yii provides no function to backup the database. To be done after dumpdb is ported
        if (in_array($databasetype, array('mysql', 'mysqli')))
        {
            $this->load->dbutil();
            $this->load->helper("string");
            if ((in_array($databasetype, array('mysql', 'mysqli'))) && Yii::app()->getConfig('demoMode') != true) {
                $tables = $this->db->list_tables();
                foreach ($tables as $table)
                {
                    if ($this->db->dbprefix==substr($table, 0, strlen($this->db->dbprefix)))
                    {
                        $lstables[] = $table;
                    }
                }
                $sfilename = "backup_db_".random_string('unique')."_".date_shift(date("Y-m-d H:i:s"), "Y-m-d", Yii::app()->getConfig('timeadjust')).".sql";
                $dfilename = "LimeSurvey_".$this->db->database."_dump_".date_shift(date("Y-m-d H:i:s"), "Y-m-d", Yii::app()->getConfig('timeadjust')).".sql.gz";
                $prefs = array(
	                'format'      => 'zip',             // gzip, zip, txt
                // File name - NEEDED ONLY WITH ZIP FILES
	                'filename'    => $sfilename,
	                'tables'      => $lstables,
	                'add_drop'    => TRUE,              // Whether to add DROP TABLE statements to backup file
	                'add_insert'  => TRUE,              // Whether to add INSERT data to backup file
	                'newline'     => "\n"               // Newline character used in backup file
                );
                $this->dbutil->backup($prefs);
                $backup =& $this->dbutil->backup();
                $this->load->helper('file');
                write_file($tempdir.'/'.$sfilename.".gz", $backup);
            }
        }
        $this->getController()->render('/admin/update/step3', $data);
    }


    function step4()
    {
        $clang = $this->getController()->lang;
        $buildnumber = Yii::app()->getConfig("buildnumber");
        $tempdir = Yii::app()->getConfig("tempdir");
        $updatekey = Yii::app()->getConfig("updatekey");
        $updatebuild = Yii::app()->getConfig("updatebuild");
        Yii::app()->loadLibrary('admin/http/http');
        $rootdir = Yii::app()->getConfig("rootdir");
        $publicdir = Yii::app()->getConfig("publicdir");
        $tempdir = Yii::app()->getConfig("tempdir");
        $databasetype = Yii::app()->db->getDriverName();
        // Request the list with changed files from the server
        $updatekey=getGlobalSetting('updatekey');
        $data = array('clang' => $clang);

        if (!isset( $_SESSION['updateinfo']))
        {
            if ($updateinfo['error']==1)
            {
                setGlobalSetting('updatekey','');
            }
        }
        else
        {
            $updateinfo=$_SESSION['updateinfo'];
        }
        // this is the last step - Download the zip file, unpack it and replace files accordingly
        // Create DB and file backups now

        $downloaderror=false;
        $http=new http;

        // Allow redirects
        $http->follow_redirect=1;
        /* Connection timeout */
        $http->timeout=0;
        /* Data transfer timeout */
        $http->data_timeout=0;
        $http->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
        $http->GetRequestArguments("http://update.limesurvey.org/updates/download/{$updateinfo['downloadid']}",$arguments);
        $http->RestoreCookies($_SESSION['updatesession']);

        $error=$http->Open($arguments);
        $error=$http->SendRequest($arguments);
        $http->ReadReplyHeaders($headers);
        if ($headers['content-type']=='text/html')
        {
            @unlink($tempdir.'/update.zip');
        }
        else if($error=='') {
            $body='';
            $pFile = fopen($tempdir.'/update.zip', 'w');
            for(;;){
                $error = $http->ReadReplyBody($body,100000);
                if($error != "" || strlen($body)==0) break;
                fwrite($pFile, $body);
            }
            fclose($pFile);
        }
        else
        {
            print( $error );
        }

        // Now remove all files that are to be deleted according to update process
        foreach ($updateinfo['files'] as $afile)
        {
            if ($afile['type']=='D' && file_exists($rootdir.$afile['file']))
            {
                if (is_file($rootdir.$afile['file']))
                {
                    unlink($rootdir.$afile['file']);
                }
                else{
                    rmdirr($rootdir.$afile['file']);
                }
                echo sprintf($clang->gT('File deleted: %s'),$afile['file']).'<br />';
            }
        }

        //Now unzip the new files over the existing ones.
        $new_files = false;
        if (file_exists($tempdir.'/update.zip')){
            Yii::app()->loadLibrary("admin/pclzip/pclzip",array('p_zipname' => $tempdir.'/update.zip'));
            $archive = $this->pclzip;
            if ($archive->extract(PCLZIP_OPT_PATH, $rootdir.'/', PCLZIP_OPT_REPLACE_NEWER)== 0) {
                die("Error : ".$archive->errorInfo(true));
            }
            else
            {
                $new_files = true;
                unlink($tempdir.'/update.zip');
            }
        }
        else
        {
            $downloaderror=true;
        }

        $data['new_files'] = $new_files;
        $data['downloaderror'] = $downloaderror;

        //  PclTraceDisplay();

        // Now we have to update version.php
        if (!$downloaderror)
        {
            @ini_set('auto_detect_line_endings', true);
            $versionlines=file($rootdir.'/application/config/version.php');
            $handle = fopen($rootdir.'/application/config/version.php', "w");
            foreach ($versionlines as $line)
            {
                if(strpos($line,'buildnumber')!==false)
                {
                    $line='$config[\'buildnumber\']'." = '{$_SESSION['updateinfo']['toversion']}';\r\n";
                }
                fwrite($handle,$line);
            }
            fclose($handle);
        }
        setGlobalSetting('updatelastcheck','1980-01-01 00:00');
        setGlobalSetting('updateavailable','0');
        setGlobalSetting('updatebuild','');
        setGlobalSetting('updateversion','');
        $this->getController()->render('/admin/update/step4', $data);
    }

    private function _RunUpdaterUpdate()
    {
        //global $homedir, $debug, $updaterversion;
        global $updaterversion;
        $clang = $this->getController()->lang;
        $versionnumber = Yii::app()->getConfig("versionnumber");
        $tempdir = Yii::app()->getConfig("tempdir");
        Yii::app()->loadLibrary('admin/http/http');

        $http=new http;

        /* Connection timeout */
        $http->timeout=0;
        /* Data transfer timeout */
        $http->data_timeout=0;
        $http->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
        $http->GetRequestArguments("http://update.limesurvey.org?updaterbuild=$updaterversion",$arguments);

        $updateinfo=false;
        $error=$http->Open($arguments);
        $error=$http->SendRequest($arguments);

        $http->ReadReplyHeaders($headers);


        if($error=="") {
            $body=''; $full_body='';
            for(;;){
                $error = $http->ReadReplyBody($body,10000);
                if($error != "" || strlen($body)==0) break;
                $full_body .= $body;
            }
            $updateinfo=json_decode($full_body,true);
            if ($http->response_status!='200')
            {
                $updateinfo['errorcode']=$http->response_status;
                $updateinfo['errorhtml']=$full_body;
            }
        }
        else
        {
            $updateinfo['errorcode']=$error;
            $updateinfo['errorhtml']=$error;
        }
        unset( $http );
        if ((int)$updateinfo['UpdaterRevision']<=$updaterversion)
        {
            return true;
        }

        if (!is_writable($tempdir))
        {
            $error=true;
        }
        if (!is_writable(APPPATH.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'update.php'))
        {
            $error=true;
        }

        //  Download the zip file, unpack it and replace the updater file accordingly
        // Create DB and file backups now

        $downloaderror=false;
        $http=new http;

        // Allow redirects
        $http->follow_redirect=1;
        /* Connection timeout */
        $http->timeout=0;
        /* Data transfer timeout */
        $http->data_timeout=0;
        $http->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
        $http->GetRequestArguments("http://update.limesurvey.org/updates/downloadupdater/$updaterversion",$arguments);

        $httperror=$http->Open($arguments);
        $httperror=$http->SendRequest($arguments);
        $http->ReadReplyHeaders($headers);
        if ($headers['content-type']=='text/html')
        {
            @unlink($tempdir.'/updater.zip');
        }
        elseif($httperror=='') {
            $body=''; $full_body='';
            for(;;){
                $httperror = $http->ReadReplyBody($body,100000);
                if($httperror != "" || strlen($body)==0) break;
                $full_body .= $body;
            }
            file_put_contents($tempdir.'/updater.zip',$full_body);
        }
        $data['httperror'] = $httperror;

        //Now unzip the new updater over the existing ones.
        if (file_exists($tempdir.'/updater.zip')){
            Yii::app()->loadLibrary("admin/pclzip/pclzip",array('p_zipname' => $tempdir.'/updater.zip'));
            $archive = new PclZip(array('p_zipname' => $tempdir.'/updater.zip'));
            if ($archive->extract(PCLZIP_OPT_PATH, APPPATH.'/controllers/admin/', PCLZIP_OPT_REPLACE_NEWER)== 0) {
                die("Error : ".$archive->errorInfo(true));
            }
            else
            {
                unlink($tempdir.'/updater.zip');
            }
            $updater_exists = true;
        }
        else
        {
            $updater_exists = false;
            $error=true;
        }
        $data['updater_exists'] = $updater_exists;
    }

    /**
     * Update database
     */
    function db($subaction = null)
    {
        $clang = $this->getController()->lang;
        Yii::app()->loadHelper("update/update");
        echo $this->getController()->_getAdminHeader(false, true);
        if(isset($subaction) && $subaction=="continue")
        {
            echo CheckForDBUpgrades($subaction);
            updatecheck();
        }
        else
        {
            echo  CheckForDBUpgrades();
        }
        echo self::_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"), true);
    }

}
