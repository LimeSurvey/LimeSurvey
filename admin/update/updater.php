<?php


if ($action=='update'){
    if ($subaction=='step4')
    {
        $adminoutput=UpdateStep4();          
    }
    elseif ($subaction=='step3')
    {
        $adminoutput=UpdateStep3();          
    }
    elseif ($subaction=='step2')
    {
        $adminoutput=UpdateStep2();          
    }
    else $adminoutput=UpdateStep1();
}


function UpdateStep1()
{
  global $clang, $scriptname, $updatekey, $subaction, $updatebuild, $homedir, $buildnumber, $tempdir, $rootdir;
  
    
  if ($subaction=='keyupdate')
  {
      setGlobalSetting('updatekey',sanitize_paranoid_string($_POST['updatekey']));
  }  
  $error=false;
  $output='<div class="background"><div class="header">'.$clang->gT('Welcome to the ComfortUpdate').'</div><br />'; 
  $output.=$clang->gT('The LimeSurvey ComfortUpdate is an easy procedure to quickly update to the latest version of LimeSurvey.').'<br />'; 
  $output.=$clang->gT('The following steps will be done by this update:').'<br /><ul>'; 
  $output.='<li>'.$clang->gT('Your LimeSurvey installation is checked if the update can be run successfully.').'</li>'; 
  $output.='<li>'.$clang->gT('Your DB and any changed files will be backed up.').'</li>'; 
  $output.='<li>'.$clang->gT('New files will be downloaded and installed.').'</li>'; 
  $output.='<li>'.$clang->gT('If necessary the database will be updated.').'</li></ul>'; 
  $output.='<h3>'.$clang->gT('Checking basic requirements...').'</h3>'; 
  if ($updatekey==''){
    $output.=$clang->gT('You need an update key to run the comfort update. During the beta test of this update feature the key "LIMESURVEYUPDATE" can be used.'); 
    $output.="<br /><form id='keyupdate' method='post' action='$scriptname?action=update&amp;subaction=keyupdate'><label for='updatekey'>".$clang->gT('Please enter a valid update-key:').'</label>';
    $output.='<input id="updatekey" name="updatekey" type="text" value="LIMESURVEYUPDATE" /> <input type="submit" value="'.$clang->gT('Save update key').'" /></form>';
  }
  else {
    $output.="<ul><li class='successtitle'>".$clang->gT('Update key: Valid')."</li>"; 
     
    if (!is_writable($tempdir))
	{
		$output.= "<li>".sprintf($clang->gT("Tempdir %s is not writable"),$tempdir)."<li>";
        $error=true;
	}
    if (!is_writable($rootdir.DIRECTORY_SEPARATOR.'version.php'))
	{
		$output.= "<li class='errortitle'>".sprintf($clang->gT("Version file is not writable (%s). Please set according file permissions."),$rootdir.DIRECTORY_SEPARATOR.'version.php')."</li>";
        $error=true; 
	}
    $output.='</ul><h3>'.$clang->gT('Change log').'</h3>'; 
    require_once($homedir."/classes/http/http.php");     
    $updatekey=getGlobalSetting('updatekey');

    $http=new http_class;    
    /* Connection timeout */
    $http->timeout=0;
    /* Data transfer timeout */
    $http->data_timeout=0;
    $http->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";       
    $http->GetRequestArguments("http://update.limesurvey.org/updates/changelog/$buildnumber/$updatebuild/$updatekey",$arguments);

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
        $output.='<textarea style="width:800px; height:300px; background-color:#fff; font-family:Monospace; font-size:11px;" readonly="readonly">'.htmlspecialchars($changelog['changelog']).'</textarea>';
    }
    else
    {
        print( $httperror );
    }
  }
  

  if ($error)
  {
        $output.='<br />'.$clang->gT('When checking your installation we found one or more problems. Please check for any error messages above and fix these before you can proceed.'); 
        $output.="<p><button onclick=\"window.open('$scriptname?action=update&amp;subaction=step1', '_top')\"";
        $output.=">".$clang->gT('Check again')."</button>";
  }
  else
  {
        $output.='<br />'.$clang->gT('Everything looks alright. Please proceed to the next step.'); 
        $output.="<p><button onclick=\"window.open('$scriptname?action=update&amp;subaction=step2', '_top')\"";
        if ($updatekey==''){    $output.="disabled='disabled'"; }
        $output.=">".sprintf($clang->gT('Proceed to step %s'),'2')."</button>";
  }
  $output.='</div><table><tr>';
  return $output;
}


function UpdateStep2()
{
    global $clang, $scriptname, $homedir, $buildnumber, $updatebuild, $debug, $rootdir;
  
    // Request the list with changed files from the server
  
    require_once($homedir."/classes/http/http.php");     
    $updatekey=getGlobalSetting('updatekey');

    $output='<div class="background"><div class="settingcaption">'.$clang->gT('ComfortUpdate Step 2').'</div><br />'; 
    
    $http=new http_class;    
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
        $output='<div class="settingcaption">'.$clang->gT('ComfortUpdate Step 2').'</div><div class="background"><br />'; 
        $output.=$clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />'; 
        
        if ($updateinfo['error']==1)
        {
            setGlobalSetting('updatekey','');
            $output.=$clang->gT('Your update key is invalid and was removed. ').'<br />'; 
        }
        else
        $output.=$clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />'; 
    }
    // okay, updateinfo now contains all necessary updateinformation
    // Now check if the existing files have the mentioned checksum
    $existingfiles=array();
    $modifiedfiles=array();
    $readonlyfiles=array();
    if (!isset($updateinfo['files']))
    {
        $output.="<div class='messagebox'>
            <div class='warningheader'>".$clang->gT('Update server busy')."</div>
            <p>".$clang->gT('The update server seems to be currently busy . This happens most likely if the necessary update files for a new version are prepared.')."<br />
               ".$clang->gT('Please be patient and try again in about 10 minutes.')."</div>
            <p><button onclick=\"window.open('$scriptname?action=globalsettings', '_top')\">".sprintf($clang->gT('Back to global settings'),'4')."</button>";
               
    }
    else
    {
        
        foreach ($updateinfo['files'] as $afile)
        {
            if (($afile['type']=='A' || !file_exists($rootdir.$afile['file'])) && !is_writable(dirname($rootdir.$afile['file'])))
		    {
			    $readonlyfiles[]=dirname($rootdir.$afile['file']);
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
     
      $output.='<h3>'.$clang->gT('Checking existing LimeSurvey files...').'</h3>'; 
      if (count($readonlyfiles)>0)
      {
          $output.='<span class="warningtitle">'.$clang->gT('Warning: The following files/directories need to be updated but their permissions are set to read-only.').'<br />';  
          $output.=$clang->gT('You must set according write permissions on these filese before you can proceed. If you are unsure what to do please contact your system administrator for advice.').'<br />';  
          $output.='</span><ul>'; 
          $readonlyfiles=array_unique($readonlyfiles);  
          sort($readonlyfiles);
          foreach ($readonlyfiles as $readonlyfile)
          {
              $output.='<li>'.htmlspecialchars($readonlyfile).'</li>';  
          }
          $output.='</ul>';  
      }
      if (count($existingfiles)>0)
      {
          $output.=$clang->gT('The following files would be added by the update but already exist. This is very unusual and may be co-incidental.').'<br />';  
          $output.=$clang->gT('We recommend that these files should be replaced by the update procedure.').'<br />';  
          $output.='<ul>';
          sort($existingfiles);  
          foreach ($existingfiles as $existingfile)
          {
              $output.='<li>'.htmlspecialchars($existingfile['file']).'</li>';  
          }
          $output.='</ul>';  
      }
                                                           
      if (count($modifiedfiles)>0)
      {
          $output.=$clang->gT('The following files will be modified or deleted but were already modified by someone else.').'<br />';  
          $output.=$clang->gT('We recommend that these files should be replaced by the update procedure.').'<br />';  
          $output.='<ul>';  
          sort($modifiedfiles);
          foreach ($modifiedfiles as $modifiedfile)
          {
              $output.='<li>'.htmlspecialchars($modifiedfile['file']).'</li>';  
          }
          $output.='</ul>';  
      }
      
      if (count($readonlyfiles)>0) 
      {
            $output.='<br />'.$clang->gT('When checking your file permissions we found one or more problems. Please check for any error messages above and fix these before you can proceed.'); 
            $output.="<p><button onclick=\"window.open('$scriptname?action=update&amp;subaction=step2', '_top')\"";
            $output.=">".$clang->gT('Check again')."</button>";
      }
      else
      {
            $output.=$clang->gT('Please check any problems above and then proceed to the next step.').'<br />'; 
            $output.="<p><button onclick=\"window.open('$scriptname?action=update&amp;subaction=step3', '_top')\" ";
            $output.=">".sprintf($clang->gT('Proceed to step %s'),'4')."</button>";
          
      }
  }
  $_SESSION['updateinfo']=$updateinfo;
  $_SESSION['updatesession']=$site_cookies;
  return $output;
}


function UpdateStep3()
{
    global $clang, $scriptname, $homedir, $buildnumber, $updatebuild, $debug, $rootdir, $publicdir, $tempdir, $database_exists, $databasetype, $action, $demoModeOnly;
  
    $output='<div class="background"><div class="settingcaption">'.$clang->gT('ComfortUpdate Step 3').'</div>'; 
    $output.='<h3>'.$clang->gT('Creating DB & file backup').'</h3>';
    if (!isset( $_SESSION['updateinfo']))
    {
        $output.=$clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />'; 
        
        if ($updateinfo['error']==1)
        {
            setGlobalSetting('updatekey','');
            $output.=$clang->gT('Your update key is invalid and was removed. ').'<br />'; 
        }
        else
        $output.=$clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />'; 
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
      
    require_once("classes/pclzip/pclzip.lib.php");
  //  require_once('classes/pclzip/pcltrace.lib.php');
  //  require_once('classes/pclzip/pclzip-trace.lib.php');
  
  //PclTraceOn(1);        

    $archive = new PclZip($tempdir.DIRECTORY_SEPARATOR.'files-'.$basefilename.'.zip');
    
    
    $v_list = $archive->add($filestozip, PCLZIP_OPT_REMOVE_PATH, $publicdir);

    $output.=$clang->gT('Creating file backup... ').'<br />'; 

    if ($v_list == 0) {
        die("Error : ".$archive->errorInfo(true));
    }
    else
    {
        $output.="<span class='successtitle'>".$clang->gT('File backup created:').' '.htmlspecialchars($tempdir.DIRECTORY_SEPARATOR.'files-'.$basefilename.'.zip').'</span><br /><br />'; 
        
    }

    require_once("dumpdb.php");

    $output.=$clang->gT('Creating database backup... ').'<br />'; 
    $byteswritten=file_put_contents($tempdir.DIRECTORY_SEPARATOR.'db-'.$basefilename.'.sql',completedump());
    if ($byteswritten>5000)
    {
        $output.="<span class='successtitle'>".$clang->gT('DB backup created:')." ".htmlspecialchars($tempdir.DIRECTORY_SEPARATOR.'db-'.$basefilename.'.sql').'</span><br /><br />'; 
    }
  
  $output.=$clang->gT('Please check any problems above and then proceed to the final step.'); 
  $output.="<p><button onclick=\"window.open('$scriptname?action=update&amp;subaction=step4', '_top')\" >Continue with the last step 4</button>";
  $output.='</div><table><tr>';
  return $output;
}


function UpdateStep4()
{
    global $clang, $scriptname, $homedir, $buildnumber, $updatebuild, $debug, $rootdir, $publicdir, $tempdir, $database_exists, $databasetype, $action, $demoModeOnly;
  
    $output='<div class="background"><div class="settingcaption">'.$clang->gT('ComfortUpdate Step 3').'</div><br />'; 
    if (!isset( $_SESSION['updateinfo']))
    {
        $output.=$clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />'; 
        
        if ($updateinfo['error']==1)
        {
            setGlobalSetting('updatekey','');
            $output.=$clang->gT('Your update key is invalid and was removed. ').'<br />'; 
        }
        else
        $output.=$clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />'; 
    }
    else
    {
      $updateinfo=$_SESSION['updateinfo'];
    }
    // this is the last step - Download the zip file, unpack it and replace files accordingly
    // Create DB and file backups now
    require_once("classes/pclzip/pclzip.lib.php");

 //   require_once('classes/pclzip/pcltrace.lib.php');
 //   require_once('classes/pclzip/pclzip-trace.lib.php');
  
 // PclTraceOn(2);    
    require_once($homedir."/classes/http/http.php");     

    $http=new http_class;    
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
        unlink($tempdir.'/update.zip');
    }
    elseif($error=='') {
        $body=''; $full_body=''; 
        for(;;){
            $error = $http->ReadReplyBody($body,100000);
            if($error != "" || strlen($body)==0) break;
            $full_body .= $body;
        }        
      file_put_contents($tempdir.'/update.zip',$full_body);
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
            $output.=sprintf($clang->gT('File deleted: %s'),$afile['file']).'<br />'; 
        }
    }    
    
    //Now unzip the new files over the existing ones.
  if (file_exists($tempdir.'/update.zip')){
      $archive = new PclZip($tempdir.'/update.zip');
      if ($archive->extract(PCLZIP_OPT_PATH, $rootdir.'/', PCLZIP_OPT_REPLACE_NEWER)== 0) {
        die("Error : ".$archive->errorInfo(true));
        } 
      else
      {
        $output.=$clang->gT('New files were successfully installed.').'<br />'; 
        unlink($tempdir.'/update.zip');
      }   
  }
  else
  {
    $output.=$clang->gT('There was a problem downloading the update file. Please try to restart the update process.').'<br />'; 
  }
  //  PclTraceDisplay();                                                              
  
  // Now we have to update version.php
  
  @ini_set('auto_detect_line_endings', true);      
  $versionlines=file($rootdir.'/version.php');
  $handle = fopen($rootdir.'/version.php', "w");
  foreach ($versionlines as $line)
  {
      if(strpos($line,'$buildnumber')!==false)
      {
          $line='$buildnumber'." = '{$_SESSION['updateinfo']['toversion']}';\r\n";   
      }
      fwrite($handle,$line);
  }
  fclose($handle);
  $output.=sprintf($clang->gT('Buildnumber was successfully updated to %s.'),$_SESSION['updateinfo']['toversion']).'<br />'; 

  $output.=$clang->gT('Please check any problems above - update was done.').'<br />'; 
  $output.="<p><button onclick=\"window.open('$scriptname?action=globalsettings&amp;subaction=updatecheck', '_top')\" >Back to main menu</button>";
  $output.='</div>';
  return $output;
}

/**
* This functions checks if the databaseversion in the settings table is the same one as required        
* If not then the necessary upgrade procedures are run
*/
function CheckForDBUpgrades()
{
    global $connect, $databasetype, $dbprefix, $dbversionnumber, $clang;
    $adminoutput='';
    $currentDBVersion=GetGlobalSetting('DBVersion');
    if (intval($dbversionnumber)>intval($currentDBVersion))
    {
        $upgradedbtype=$databasetype;
        if ($upgradedbtype=='mssql_n' || $upgradedbtype=='odbc_mssql' || $upgradedbtype=='odbtp') $upgradedbtype='mssql';         
        if ($upgradedbtype=='mysqli') $upgradedbtype='mysql';         
        include ('upgrade-'.$upgradedbtype.'.php');
        $tables = $connect->MetaTables();
        db_upgrade(intval($currentDBVersion));
        $adminoutput="<br />".sprintf($clang->gT("Database has been successfully upgraded to version %s"),$dbversionnumber);
    }
    return $adminoutput;
}


?>
