<?php
/**
 * This view display any error encoutered while getting the welcome message. Most of those errors are returned by the update server, and concern the update key. 
 * @var obj $errorObject the object error   
 */
// TODO : move to the controler
//$urlNew = Yii::app()->createUrl("admin/globalsettings", array("update"=>'newKey'));
$urlNew = Yii::app()->createUrl("admin/globalsettings", array("update"=>'newKey', 'destinationBuild' => $_REQUEST['destinationBuild']));
// We first build the error message.
// View is right place to do this, so it's easy for further integrators to change messages.
$buttons = 0;
switch ($errorObject->error) 
{
    case 'no_server_answer':
        $title = gT('No server answer !');
        $message = gt("We couldn't reach the server, or the server didn't provide any answer. Please, try again in few minutes.");
        break; 
    
    case 'db_error':            
        $title = gT('Database error !');
        $message = gt("Comfort Update encounter an error while trying to get datas from your database.");
        break;  
    
    case 'unzip_error':
        $title = gT('Unzip error');
        $message = gt("ComfortUpdate could not unzip the update file (or the updater update file)");
        break;
    
    case 'zip_update_not_found':
        $title = gT('Zip file not found !');
        $message = gt("ComfortUpdate could not find the update file on your local system (or the updater update file)");
        break;      
    
    case 'cant_zip_backup':
        $title = gT('Zip error !');
        $message = gt("ComfortUpdate could not zip the files for your backup");
        break;              
    
    case 'error_while_processing_download':
        $title = gT('Download error !');
        $message = gt("ComfortUpdate could not download the update !");
        break;                  
    
    case 'out_of_updates':
        $title = gt("Your update key is out of update !");
        $message = gt("you should first renew this key before using it, or try to enter a new one !");
        $buttons = 1;
        break;
    
    case 'expired':
        $title = gt("Your update key is expired !");
        $message = gt("you should first renew this key before using it, or try to enter a new one !");
        $buttons = 1;       
        break;

    case 'not_found':
        $title = gt("Unknown update key !");
        $message = gt("Your key is unkown by the update server.");
        $buttons = 3;
        break;      
    
    case 'key_null':
        $title = gt("key can't be null !");
        $message = "";
        $buttons = 3;
        break;
    
    case 'unknown_view': 
        $title = gt("The server try to call an unkown view !");
        $message = gt("Is your ComfortUpdater up to date ? Please, contact LimeSurvey team.");
        $buttons = 3;
        break;
            
    case 'unkown_destination_build':
        $title = gt("Unkown destination build !");
        $message = gt("It seems that the ComfortUpdater don't know to which version you're trying to update. Please, restart the process.");
        $buttons = 0;
        break;
    
    case 'file_locked':
        $title = gT('Update server busy');
        $message = gT('The update server is currently busy. This usually happens when the update files for a new version are being prepared.').'<br/>'.gT('Please be patient and try again in about 10 minutes.');
        $buttons = 0;
        break;
    
    case 'server_error_creating_zip_update':
        $title = gT('Server error !');
        $message = gT('An error occure while creating your update zip. Please, contact LimeSurvey team.');
        $buttons = 0;
        break;       
    
    case 'server_error_getting_checksums':
        $title = gT('Server error !');
        $message = gT('An error occure while getting checksums. Please, contact LimeSurvey team.');
        $buttons = 0;
        break;       
    
    case 'cant_get_changeset':
        $title = gT('Server error !');
        $message = gT('An error occure while getting the changeset. Please, contact LimeSurvey team.');
        $buttons = 0;
        break;          
        
    case 'wrong_token': 
        $title = gT('Unkown sessions');
        $message = gT('Your session with the ComfortUpdate server is not valid. Please, restart the process.');
        $buttons = 0;
        break;
        
    case 'zip_error':
        $title = gT('Error while creating zip file');
        $message = gt("An error occured while creating the backup of your files. Check your local system (permission, available space, etc.)");
        break; 
        
    
    case 'no_updates_infos':
        $title = gT("Could not retrieve update infos datas");
        $message = gt("ComfortUpdate could not find the update infos datas.");
        break;
        
    case 'cant_remove_deleted_files':
        $title = gT("Could not remove the deleted files");
        $message =  gT("ComfortUpdate could not delete from your system the removed file in the update.");
        break;
    
    case 'cant_remove_deleted_directory':
        $title = gT("Could not remove the deleted directories");
        $message =  gT("ComfortUpdate could not delete from your system the removed directories in the update.");
        break;
            
            
    default:
        $title = $errorObject->error;
        $message = gt("Unknown error. Please, contact LimeSurvey team.");
        $buttons = 0;
        break;
}
?>

<h2 class="maintitle" style="color: red;"><?php echo $title;?></h2>
<div style="padding: 10px">
    <?php echo $message; ?>
</div>

<div>

<?php if( $buttons == 1 ): ?>
        <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="https://www.limesurvey.org/en/" role="button" aria-disabled="false" target="_blank">
            <span class="ui-button-text"><?php eT("Renew this key"); ?></span>
        </a>
    
        <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo $urlNew;?>" role="button" aria-disabled="false">
            <span class="ui-button-text"><?php eT("Enter a new key"); ?></span>
        </a>
<?php endif; ?> 
<?php if( $buttons == 3 ): ?>   
        <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo $urlNew;?>" role="button" aria-disabled="false">
            <span class="ui-button-text"><?php eT("Enter a new key"); ?></span>
        </a>
<?php endif;?>
<a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo Yii::app()->createUrl("admin/globalsettings"); ?>" role="button" aria-disabled="false">
    <span class="ui-button-text"><?php eT("Cancel"); ?></span>
</a>
</div>
