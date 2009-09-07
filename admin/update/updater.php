<?php


if ($action=='update'){
   if ($subaction=='step3')
   {
    $adminoutput=UpdateStep3();          
   }
   elseif ($subaction=='step2')
   {
    $adminoutput=UpdateStep2();          
   }
   else  $adminoutput=UpdateStep1();
}


function UpdateStep1()
{
  global $clang, $scriptname, $updatekey, $subaction;
  
    
  if ($subaction=='keyupdate')
  {
      setGlobalSetting('updatekey',sanitize_paranoid_string($_POST['updatekey']));
  }  
    

  
  $output='<div class="settingcaption">'.$clang->gT('Welcome to the ComfortUpdate').'</div><div class="background"><br />'; 
  $output.=$clang->gT('The LimeSurvey ComfortUpdate is an easy procedure to quickly update to the latest version of LimeSurvey.').'<br />'; 
  $output.=$clang->gT('The following steps will be done by this update:').'<br /><ul>'; 
  $output.='<li>'.$clang->gT('Your LimeSurvey installation is checked if the update can be run successfully.').'</li>'; 
  $output.='<li>'.$clang->gT('Your DB and and any changed files will be backed up.').'</li>'; 
  $output.='<li>'.$clang->gT('New files will be downloaded and installed.').'</li>'; 
  $output.='<li>'.$clang->gT('If necessary the database will be updated.').'</li></ul>'; 
  $output.='<br />'.$clang->gT('Checking basic requirements...'); 
  if ($updatekey==''){
    $output.='<br />'.$clang->gT('You need an update key to run the comfort update. During the beta test of this update feature the key "LIMESURVEYUPDATE" can be used.'); 
    $output.="<br /><form id='keyupdate' method='post' action='$scriptname?action=update&amp;subaction=keyupdate'><label for='updatekey'>".$clang->gT('Please enter a valid update-key:').'</label>';
    $output.='<input id="updateley" name="updatekey" type="text" value="LIMESURVEYUPDATE" /> <input type="submit" value="'.$clang->gT('Save update key').'" /></form>';
  }
  else {
    $output.='<br />'.$clang->gT('Update key: Valid'); 
      
  }
  $output.='<br />'.$clang->gT('Everything looks alright. Please proceed to the next step to start the update.').'<br />'; 
  $output.="<button onclick=\"window.open('$scriptname?action=update&amp;subaction=step2', '_top')\"";
  if ($updatekey==''){    $output.="disabled='disabled'"; }
  $output.=">Update Step 2</button>";
  $output.='</div><table><tr>';
  return $output;
}


function UpdateStep2()
{
    global $clang, $scriptname, $homedir, $buildnumber, $updatebuild, $debug, $rootdir;
  
    // Request the list with changed files from the server
  
    require_once($homedir."/classes/http/http.php");     

    $http=new http_class;    
    /* Connection timeout */
    $http->timeout=0;
    /* Data transfer timeout */
    $http->data_timeout=0;
    $http->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";       
    $http->GetRequestArguments("http://update.limesurvey.org//updates/update/$buildnumber/$updatebuild/mykey",$arguments);

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
        $updateinfo=json_php5decode($full_body);
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
    foreach ($updateinfo['files'] as $afile)
    {
        if ($afile['type']=='A' && file_exists($rootdir.$afile['file']))
        {
            //A new file, check if this already exists
            $existingfiles[]=$afile;  
        }
        elseif (($afile['type']=='D' || $afile['type']=='M') && sha1_file($rootdir.$afile['file'])!=$afile['checksum'])  // A deleted or modified file - check if it is unmodified
        {
            $modifiedfiles[]=$afile;
        }
    }
 
  $output='<div class="settingcaption">'.$clang->gT('ComfortUpdate Step 2').'</div><div class="background"><br />'; 
 
  $output.=$clang->gT('Checking your existing LimeSurvey files').'<br />'; 
  if (count($existingfiles)>0)
  {
      $output.=$clang->gT('The following files would be added by the update but already exist. This is very unusual and may be co-incidental.').'<br />';  
      $output.=$clang->gT('We recommend that these files should be replaced by the update procedure.').'<br />';  
      $output.='<ul>';  
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
  
  $output.=$clang->gT('Please check any problems above and then proceed to the next step to start the update.').'<br />'; 
  $output.="<button onclick=\"window.open('$scriptname?action=update&amp;subaction=step3', '_top')\" >Continue with update step 3</button>";
  $output.='</div><table><tr>';
  $_SESSION['updateinfo']=$updateinfo;
  return $output;
}


function UpdateStep3()
{
    global $clang, $scriptname, $homedir, $buildnumber, $updatebuild, $debug, $rootdir, $publicdir, $tempdir, $database_exists, $databasetype, $action, $demoModeOnly;
  
    $output='<div class="settingcaption">'.$clang->gT('ComfortUpdate Step 2').'</div><div class="background"><br />'; 
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
        if (file_exists($publicdir.$file['file']))
        {
            $filestozip[]=$publicdir.$file['file']; 
        }
    }
      
    require_once("classes/pclzip/pclzip.lib.php");

    $archive = new PclZip($tempdir.'/files-'.$basefilename.'.zip');
    $v_list = $archive->create(implode(',',$filestozip),PCLZIP_OPT_REMOVE_PATH, $publicdir);
    if ($v_list == 0) {
        die("Error : ".$archive->errorInfo(true));
    }
    else
    {
        $output.=$clang->gT('Old files were successfuly backed up to ').htmlspecialchars($tempdir.'/files-'.$basefilename.'.zip').'<br />'; 
        
    }

    require_once("dumpdb.php");

    $byteswritten=file_put_contents($tempdir.'/db-'.$basefilename.'.sql',completedump());
    if ($byteswritten>5000)
    {
        $output.=$clang->gT('Database was successfuly backed up to ').htmlspecialchars($tempdir.'/db-'.$basefilename.'.sql').'<br />'; 
    }
  
  $output.=$clang->gT('Please check any problems above and then proceed to the next step to start the update.').'<br />'; 
  $output.="<button onclick=\"window.open('$scriptname?action=update&amp;subaction=step4', '_top')\" >Continue with update step 3</button>";
  $output.='</div><table><tr>';
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

    if (intval($dbversionnumber)<GetGlobalSetting('DBVersion'))
    {
        $upgradedbtype=$databasetype;
        if ($upgradedbtype=='mssql_n' || $upgradedbtype=='odbc_mssql' || $upgradedbtype=='odbtp') $upgradedbtype='mssql';         
        if ($upgradedbtype=='mysqli') $upgradedbtype='mysql';         
        include ('upgrade-'.$upgradedbtype.'.php');
        $tables = $connect->MetaTables();
        db_upgrade(intval($usrow['stg_value']));
        $adminoutput="<br />".$clang->gT("Database has been successfully upgraded to version ".$dbversionnumber);
    }
    return $adminoutput;
}


?>