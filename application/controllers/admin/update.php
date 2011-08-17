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
list(,$updaterversion)=explode(' ','$Rev: 588887976 $');  // this is updated by subversion so don't change this string

/**
 * Update Controller
 *
 * This controller performs updates
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class update extends Admin_Controller {

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
        session_start();
	}

	/**
	 * Default Controller Action
	 */
	function index($subaction = null)
	{
		global $updaterversion;
		self::_RunUpdaterUpdate();
		
	    $clang = $this->limesurvey_lang;
		$buildnumber = $this->config->item("buildnumber");
		$tempdir = $this->config->item("tempdir");
		$updatekey = $this->config->item("updatekey");
		$_POST=$this->input->post();
		$this->load->library('admin/http/http');

	    if ($subaction=='keyupdate')
	    {
	        setGlobalSetting('updatekey',sanitize_paranoid_string($_POST['updatekey']));
			$updatekey = getGlobalSetting('updatekey');
	    }
	    $error=false;
		echo self::_getAdminHeader(false, true);
	    echo '<div class="header ui-widget-header">'.$clang->gT('Welcome to the ComfortUpdate').'</div><div class="updater-background"><br />';
	    echo $clang->gT('The LimeSurvey ComfortUpdate is an easy procedure to quickly update to the latest version of LimeSurvey.').'<br />';
	    echo $clang->gT('The following steps will be done by this update:').'<br /><ul>';
	    echo '<li>'.$clang->gT('Your LimeSurvey installation is checked if the update can be run successfully.').'</li>';
	    echo '<li>'.$clang->gT('Your DB and any changed files will be backed up.').'</li>';
	    echo '<li>'.$clang->gT('New files will be downloaded and installed.').'</li>';
	    echo '<li>'.$clang->gT('If necessary the database will be updated.').'</li></ul>';
	    echo '<h3>'.$clang->gT('Checking basic requirements...').'</h3>';
	    if ($updatekey==''){
	        echo $clang->gT('You need an update key to run the comfort update. During the beta test of this update feature the key "LIMESURVEYUPDATE" can be used.');
	        echo "<br /><form id='keyupdate' method='post' action='".site_url("admin/update/index/keyupdate")."'><label for='updatekey'>".$clang->gT('Please enter a valid update-key:').'</label>';
	        echo '<input id="updatekey" name="updatekey" type="text" value="LIMESURVEYUPDATE" /> <input type="submit" value="'.$clang->gT('Save update key').'" /></form>';
	    }
	    else
	    {
	        echo "<ul><li class='successtitle'>".$clang->gT('Update key: Valid')."</li>";
	         
	        if (!is_writable($tempdir))
	        {
	            echo  "<li class='errortitle'>".sprintf($clang->gT("Tempdir %s is not writable"),$tempdir)."<li>";
	            $error=true;
	        }
	        if (!is_writable(APPPATH.'config/version.php'))
	        {
	            echo  "<li class='errortitle'>".sprintf($clang->gT("Version file is not writable (%s). Please set according file permissions."),APPPATH.'config/version.php')."</li>";
	            $error=true;
	        }
	        echo '</ul><h3>'.$clang->gT('Change log').'</h3>';
	        $updatekey=getGlobalSetting('updatekey');
	
	        $http=new http;
	        /* Connection timeout */
	        $http->timeout=0;
	        /* Data transfer timeout */
	        $http->data_timeout=0;
	        $http->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
	        $http->GetRequestArguments("http://update.limesurvey.org/updates/changelog/$buildnumber/$updaterversion/$updatekey",$arguments);
	
	        $updateinfo=false;
	        $httperror=$http->Open($arguments);
	        $httperror=$http->SendRequest($arguments);
	
	        if($httperror=="") {
	            $body=''; $full_body='';
	            for(;;){
	                $httperror = $http->ReadReplyBody($body,10000);
	                if($httperror != "" || strlen($body)==0) break;
	                $full_body .= $body;
	            }
	            $changelog=json_decode($full_body,true);
	            echo '<textarea class="updater-changelog" readonly="readonly">'.htmlspecialchars($changelog['changelog']).'</textarea>';
	        }
	        else
	        {
	            print( $httperror );
	        }
	    }
	
	
	    if ($error)
	    {
	        echo '<br /><br />'.$clang->gT('When checking your installation we found one or more problems. Please check for any error messages above and fix these before you can proceed.');
	        echo "<p><button onclick=\"window.open('".site_url("admin/update/index/")."', '_top')\"";
	        echo ">".$clang->gT('Check again')."</button></p>";
	    }
	    else
	    {
	        echo '<br /><br />'.$clang->gT('Everything looks alright. Please proceed to the next step.');
	        echo "<p><button onclick=\"window.open('".site_url("admin/update/step2/")."', '_top')\"";
	        if ($updatekey==''){    echo "disabled='disabled'"; }
	        echo ">".sprintf($clang->gT('Proceed to step %s'),'2')."</button></p>";
	    }
	    echo '</div>';
		echo self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"), true);
			
			
			
			
		
		
		
		
	}

	
	function step2()
	{
			global $updaterversion;
			
		    $clang = $this->limesurvey_lang;
			$this->load->config("version");
			$buildnumber = $this->config->item("buildnumber");
			$tempdir = $this->config->item("tempdir");
			$updatekey = $this->config->item("updatekey");
			$updatebuild = $this->config->item("updatebuild");
			$_POST=$this->input->post();
			$this->load->library('admin/http/http');
			$rootdir = $this->config->item("rootdir");
			
	    // Request the list with changed files from the server
	    $updatekey=getGlobalSetting('updatekey');
		
		echo self::_getAdminHeader(false, true);
	    echo '<div class="header ui-widget-header">'.sprintf($clang->gT('ComfortUpdate step %s'),'2').'</div><div class="updater-background"><br />';
	
	    $http=new http;
	    /* Connection timeout */
	    $http->timeout=0;
	    /* Data transfer timeout */
	    $http->data_timeout=0;
	    $http->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
	    $http->GetRequestArguments("http://update.limesurvey.org/updates/update/$buildnumber/$updatebuild/$updatekey",$arguments);
	
	    $updateinfo=false;
	    $error=$http->Open($arguments);
	    $error=$http->SendRequest($arguments);
	
	    if($error=="") {
	        $body=''; $full_body='';
	        for(;;){
	            $error = $http->ReadReplyBody($body,10000);
	            if($error != "" || strlen($body)==0) break;
	            $full_body .= $body;
	        }
	        $updateinfo=json_decode($full_body,true);
	        $http->SaveCookies($site_cookies);
	    }
	    else
	    {
	        print( $error );
	    }
	
	    if (isset($updateinfo['error']))
	    {
	        echo $clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
	
	        if ($updateinfo['error']==1)
	        {
	            setGlobalSetting('updatekey','');
	            echo $clang->gT('Your update key is invalid and was removed. ').'<br />';
	        }
	        else
	        echo $clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
	    }
	    // okay, updateinfo now contains all necessary updateinformation
	    // Now check if the existing files have the mentioned checksum
	    $existingfiles=array();
	    $modifiedfiles=array();
	    $readonlyfiles=array();
	    if (!isset($updateinfo['files']))
	    {
	        echo "<div class='messagebox ui-corner-all'>
	            <div class='warningheader'>".$clang->gT('Update server busy')."</div>
	            <p>".$clang->gT('The update server is currently busy. This usually happens when the update files for a new version are being prepared.')."<br /><br />
	               ".$clang->gT('Please be patient and try again in about 10 minutes.')."</p></div>
	            <p><button onclick=\"window.open('".site_url("admin/globalsettings")."', '_top')\">".sprintf($clang->gT('Back to global settings'),'4')."</button></p>";
	         
	    }
	    else
	    {
	
	        foreach ($updateinfo['files'] as $afile)
	        {
	            if ($afile['type']=='A' && !file_exists($rootdir.$afile['file']))
	            {
	                $searchpath=$rootdir.$afile['file'];
	                $is_writable=is_writable(dirname($searchpath));
	                while (!$is_writable && strlen($searchpath)>strlen($rootdir))
	                {
	                    $searchpath=dirname($searchpath);
	                    if (file_exists($searchpath))
	                    {
	                        $is_writable=is_writable($searchpath);
	                        break;
	
	                    }
	                }
	                if (!$is_writable)
	                {
	                    $readonlyfiles[]=$searchpath;
	                }
	            }
	            elseif (file_exists($rootdir.$afile['file']) && !is_writable($rootdir.$afile['file'])) {
	                $readonlyfiles[]=$rootdir.$afile['file'];
	            }
	
	
	            if ($afile['type']=='A' && file_exists($rootdir.$afile['file']))
	            {
	                //A new file, check if this already exists
	                $existingfiles[]=$afile;
	            }
	            elseif (($afile['type']=='D' || $afile['type']=='M')  && is_file($rootdir.$afile['file']) && sha1_file($rootdir.$afile['file'])!=$afile['checksum'])  // A deleted or modified file - check if it is unmodified
	            {
	                $modifiedfiles[]=$afile;
	            }
	        }
	         
	        echo '<h3>'.$clang->gT('Checking existing LimeSurvey files...').'</h3>';
	        if (count($readonlyfiles)>0)
	        {
	            echo '<span class="warningtitle">'.$clang->gT('Warning: The following files/directories need to be updated but their permissions are set to read-only.').'<br />';
	            echo $clang->gT('You must set according write permissions on these filese before you can proceed. If you are unsure what to do please contact your system administrator for advice.').'<br />';
	            echo '</span><ul>';
	            $readonlyfiles=array_unique($readonlyfiles);
	            sort($readonlyfiles);
	            foreach ($readonlyfiles as $readonlyfile)
	            {
	                echo '<li>'.htmlspecialchars($readonlyfile).'</li>';
	            }
	            echo '</ul>';
	        }
	        if (count($existingfiles)>0)
	        {
	            echo $clang->gT('The following files would be added by the update but already exist. This is very unusual and may be co-incidental.').'<br />';
	            echo $clang->gT('We recommend that these files should be replaced by the update procedure.').'<br />';
	            echo '<ul>';
	            sort($existingfiles);
	            foreach ($existingfiles as $existingfile)
	            {
	                echo '<li>'.htmlspecialchars($existingfile['file']).'</li>';
	            }
	            echo '</ul>';
	        }
	         
	        if (count($modifiedfiles)>0)
	        {
	            echo $clang->gT('The following files will be modified or deleted but were already modified by someone else.').'<br />';
	            echo $clang->gT('We recommend that these files should be replaced by the update procedure.').'<br />';
	            echo '<ul>';
	            sort($modifiedfiles);
	            foreach ($modifiedfiles as $modifiedfile)
	            {
	                echo '<li>'.htmlspecialchars($modifiedfile['file']).'</li>';
	            }
	            echo '</ul>';
	        }
	
	        if (count($readonlyfiles)>0)
	        {
	            echo '<br />'.$clang->gT('When checking your file permissions we found one or more problems. Please check for any error messages above and fix these before you can proceed.');
	            echo "<p><button onclick=\"window.open('".site_url("admin/update/step2/")."', '_top')\"";
	            echo ">".$clang->gT('Check again')."</button></p>";
	        }
	        else
	        {
	            echo $clang->gT('Please check any problems above and then proceed to the next step.').'<br />';
	            echo "<p><button onclick=\"window.open('".site_url("admin/update/step3/")."', '_top')\" ";
	            echo ">".sprintf($clang->gT('Proceed to step %s'),'3')."</button></p>";
	
	        }
	    }
	    $_SESSION['updateinfo']=$updateinfo;
	    $_SESSION['updatesession']=$site_cookies;
		echo "</div>";
		echo self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"), true);
	}
		
	
	function step3()
	{
	    $clang = $this->limesurvey_lang;
		$this->load->config("version");
		$buildnumber = $this->config->item("buildnumber");
		$tempdir = $this->config->item("tempdir");
		$updatekey = $this->config->item("updatekey");
		$updatebuild = $this->config->item("updatebuild");
		$_POST=$this->input->post();
		$this->load->library('admin/http/http');
		$rootdir = $this->config->item("rootdir");
		$publicdir = $this->config->item("publicdir");
		$tempdir = $this->config->item("tempdir");
		$databasetype = $this->db->dbdriver;
			
	    // Request the list with changed files from the server
	    $updatekey=getGlobalSetting('updatekey');
		
		echo self::_getAdminHeader(false, true);
		
	    echo '<div class="header ui-widget-header">'.sprintf($clang->gT('ComfortUpdate step %s'),'3').'</div><div class="updater-background">';
	    echo '<h3>'.$clang->gT('Creating DB & file backup').'</h3>';
	    if (!isset( $_SESSION['updateinfo']))
	    {
	        echo $clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
	
	        if ($updateinfo['error']==1)
	        {
	            setGlobalSetting('updatekey','');
	            echo $clang->gT('Your update key is invalid and was removed. ').'<br />';
	        }
	        else
	        echo $clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
	    }
	    else
	    {
	        $updateinfo=$_SESSION['updateinfo'];
	    }
	
	    // okay, updateinfo now contains all necessary updateinformation
	    // Create DB and file backups now
	
	    $basefilename = date("YmdHis-").md5(uniqid(rand(), true));
	    //Now create a backup of the files to be delete or modified
	
	    Foreach ($updateinfo['files'] as $file)
	    {
	        if (is_file($publicdir.$file['file'])===true) // Sort out directories
	        {
	            $filestozip[]=$publicdir.$file['file'];
	        }
	    }
	
	    //  require_once("classes/pclzip/pclzip.lib.php");
	    //  require_once('classes/pclzip/pcltrace.lib.php');
	    //  require_once('classes/pclzip/pclzip-trace.lib.php');
	
	    //PclTraceOn(1);
	
		$this->load->library("admin/pclzip/pclzip",array('p_zipname' => $tempdir.DIRECTORY_SEPARATOR.'files-'.$basefilename.'.zip'));
	    $archive = $this->pclzip;
	
	    $v_list = $archive->add($filestozip, PCLZIP_OPT_REMOVE_PATH, $publicdir);
	
	    echo $clang->gT('Creating file backup... ').'<br />';
	
	    if ($v_list == 0) {
	        die("Error : ".$archive->errorInfo(true));
	    }
	    else
	    {
	        echo "<span class='successtitle'>".$clang->gT('File backup created:').' '.htmlspecialchars($tempdir.DIRECTORY_SEPARATOR.'files-'.$basefilename.'.zip').'</span><br /><br />';
	
	    }
	
	
	    if ($databasetype=='mysql' || $databasetype=='mysqli')
	    {
	        echo $clang->gT('Creating database backup... ').'<br />';
	        //$byteswritten=file_put_contents($tempdir.DIRECTORY_SEPARATOR.'db-'.$basefilename.'.sql',completedump());
			$this->load->dbutil();
	        $this->load->helper("string");
			if ($this->dbutil->database_exists($this->db->database) && ($this->db->dbdriver=='mysql' || $this->db->dbdriver=='mysqli') && $this->config->item('demoModeOnly') != true) {  
	            $tables = $this->db->list_tables();
	            foreach ($tables as $table)
	            {
	               if ($this->db->dbprefix==substr($table, 0, strlen($this->db->dbprefix)))
	               {              
	                    $lstables[] = $table;
	               }
	            }
	            $sfilename = "backup_db_".random_string('unique')."_".date_shift(date("Y-m-d H:i:s"), "Y-m-d", $this->config->item('timeadjust')).".sql";
	            $dfilename = "LimeSurvey_".$this->db->database."_dump_".date_shift(date("Y-m-d H:i:s"), "Y-m-d", $this->config->item('timeadjust')).".sql.gz";
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
	           echo "<span class='successtitle'>".$clang->gT('DB backup created:')." ".htmlspecialchars($tempdir.DIRECTORY_SEPARATOR.'db-'.$basefilename.'.sql').'</span><br /><br />';
	    }
	    else
	    {
	        echo "<span class='warningtitle'>".$clang->gT('No DB backup created:').'<br />'.$clang->gT('Database backup functionality is currently not available for your database type. Before proceeding please backup your database using a backup tool!').'</span><br /><br />';
	    }
	
	    echo $clang->gT('Please check any problems above and then proceed to the final step.');
	    echo "<p><button onclick=\"window.open('".site_url("admin/update/step4/")."', '_top')\" ";
	    echo ">".sprintf($clang->gT('Proceed to step %s'),'4')."</button></p>";
	    echo '</div>';
		echo self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"), true);
	}
	
	
	function step4()
	{
	    $clang = $this->limesurvey_lang;
		$this->load->config("version");
		$buildnumber = $this->config->item("buildnumber");
		$tempdir = $this->config->item("tempdir");
		$updatekey = $this->config->item("updatekey");
		$updatebuild = $this->config->item("updatebuild");
		$_POST=$this->input->post();
		$this->load->library('admin/http/http');
		$rootdir = $this->config->item("rootdir");
		$publicdir = $this->config->item("publicdir");
		$tempdir = $this->config->item("tempdir");
		$databasetype = $this->db->dbdriver;
	    // Request the list with changed files from the server
	    $updatekey=getGlobalSetting('updatekey');
		
		echo self::_getAdminHeader(false, true);
	    echo '<div class="header ui-widget-header">'.sprintf($clang->gT('ComfortUpdate step %s'),'4').'</div><div class="updater-background"><br />';
	    if (!isset( $_SESSION['updateinfo']))
	    {
	        echo $clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
	
	        if ($updateinfo['error']==1)
	        {
	            setGlobalSetting('updatekey','');
	            echo $clang->gT('Your update key is invalid and was removed. ').'<br />';
	        }
	        else
	        echo $clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
	    }
	    else
	    {
	        $updateinfo=$_SESSION['updateinfo'];
	    }
	    // this is the last step - Download the zip file, unpack it and replace files accordingly
	    // Create DB and file backups now
	
	    //   require_once('classes/pclzip/pcltrace.lib.php');
	    //   require_once('classes/pclzip/pclzip-trace.lib.php');
	
	    // PclTraceOn(2);
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
	    elseif($error=='') {
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
	    if (file_exists($tempdir.'/update.zip')){
			$this->load->library("admin/pclzip/pclzip",array('p_zipname' => $tempdir.'/update.zip'));
	    	$archive = $this->pclzip;
	        if ($archive->extract(PCLZIP_OPT_PATH, $rootdir.'/', PCLZIP_OPT_REPLACE_NEWER)== 0) {
	            die("Error : ".$archive->errorInfo(true));
	        }
	        else
	        {
	            echo $clang->gT('New files were successfully installed.').'<br />';
	            unlink($tempdir.'/update.zip');
	        }
	    }
	    else
	    {
	        echo $clang->gT('There was a problem downloading the update file. Please try to restart the update process.').'<br />';
	        $downloaderror=true;
	    }
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
	        echo sprintf($clang->gT('Buildnumber was successfully updated to %s.'),$_SESSION['updateinfo']['toversion']).'<br />';
	        echo $clang->gT('Please check any problems above - update was done.').'<br />';
	    }
	
	
	    echo "<p><button onclick=\"window.open('".site_url("admin/globalsettings")."', '_top')\" >".$clang->gT('Back to main menu')."</button></p>";
	    echo '</div>';
	    setGlobalSetting('updatelastcheck','1980-01-01 00:00');
	    setGlobalSetting('updateavailable','0');
		setGlobalSetting('updatebuild','');
		setGlobalSetting('updateversion','');
		echo self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"), true);
	}
		
	function _RunUpdaterUpdate()
	{
	    //global $homedir, $debug, $updaterversion;
	    global $updaterversion;
	    $clang = $this->limesurvey_lang;
		$versionnumber = $this->config->item("versionnumber");
		$tempdir = $this->config->item("tempdir");
	    $this->load->library('admin/http/http');
	
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
	        echo  "<li class='errortitle'>".sprintf($clang->gT("Tempdir %s is not writable"),$tempdir)."<li>";
	        $error=true;
	    }
	    if (!is_writable(APPPATH.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'update.php'))
	    {
	        echo  "<li class='errortitle'>".sprintf($clang->gT("Updater file is not writable (%s). Please set according file permissions."),DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'update.php')."</li>";
	        $error=true;
	    }
	 
	    //  Download the zip file, unpack it and replace the updater file accordingly
	    // Create DB and file backups now
	
	    //   require_once('classes/pclzip/pcltrace.lib.php');
	    //   require_once('classes/pclzip/pclzip-trace.lib.php');
	    // PclTraceOn(2);
	
	    //require_once($homedir."/classes/http/http.php");
	
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
	    else
	    {
	        print( $httperror );
	    }
	
	    //Now unzip the new updater over the existing ones.
	    if (file_exists($tempdir.'/updater.zip')){
	    	$this->load->library("admin/pclzip/pclzip",array('p_zipname' => $tempdir.'/updater.zip'));
	        $archive = new PclZip(array('p_zipname' => $tempdir.'/updater.zip'));
	        if ($archive->extract(PCLZIP_OPT_PATH, APPPATH.'/controllers/admin/', PCLZIP_OPT_REPLACE_NEWER)== 0) {
	            die("Error : ".$archive->errorInfo(true));
	        }
	        else
	        {
	            unlink($tempdir.'/updater.zip');
	        }
	    }
	    else
	    {
	        echo $clang->gT('There was a problem downloading the updater file. Please try to restart the update process.').'<br />';
	        $error=true;
	    }
	 
	 
	}

	/**
	 * Update database
	 */
	function db($subaction = null) 
	{
		$clang = $this->limesurvey_lang;
	    $this->load->helper("update/update");
		echo self::_getAdminHeader(false, true);
	    if(isset($subaction) && $subaction=="continue") 
	    {   
	        echo CheckForDBUpgrades($subaction);
	        updatecheck();                     
	    }        
	    else
	    {   
	        echo  CheckForDBUpgrades();
	    }        
		echo self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"), true);
	}

}