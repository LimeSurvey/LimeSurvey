<?php
/**
 * This view display any error encoutered while getting the welcome message. Most of those errors are returned by the update server, and concern the update key.
 * @var obj $errorObject the object error
 */
// TODO : move to the controler
$urlNew = Yii::app()->createUrl("admin/update", array("update" => 'newKey', 'destinationBuild' => $_REQUEST['destinationBuild']));
// We first build the error message.
// View is right place to do this, so it's easy for further integrators to change messages.
$buttons = 0;
switch ($errorObject->error) {
    case 'no_server_answer':
        $title = gT('No server answer!');
        $message = gT("We couldn't reach the server or the server didn't provide any answer. Please try again in few minutes.");
        break;

    case 'db_error':
        $title = gT('Database error!');
        $message = gT("ComfortUpdate encountered an error while trying to get data from your database.");
        break;

    case 'unzip_error':
        $title = gT('Unzip error!');
        $message = gT("ComfortUpdate couldn't unzip the update file (or the updater update file)");
        break;

    case 'zip_update_not_found':
        $title = gT('Zip file not found!');
        $message = gT("ComfortUpdate couldn't find the update file on your local system (or the updater update file)");
        break;

    case 'cant_zip_backup':
        $title = gT('Zip error!');
        $message = gT("ComfortUpdate could not zip the files for your backup");
        break;

    case 'error_while_processing_download':
        $title = gT('Download error!');
        $message = gT("ComfortUpdate could not download the update!");
        break;

    case 'out_of_updates':
        $title = gT("Your update key has exceeded the maximum number of updates!");
        $message = gT("Please buy/enter a new one!");
        $buttons = 1;
        break;

    case 'expired':
        $title = gT("Your update key has expired!");
        $message = gT("Please buy/enter a new one!");
        $buttons = 1;
        break;

    case 'not_found':
        $title = gT("Unknown update key!");
        $message = gT("Your key is unknown to the update server.");
        $buttons = 3;
        break;

    case 'key_null':
        $title = gT("Key can't be empty!");
        $message = "";
        $buttons = 3;
        break;

    case 'unknown_view':
        $title = gT("The server tried to call an unknown view!");
        $message = gT('Is your ComfortUpdate up to date?') . ' ' . gT('Please contact the LimeSurvey team.');
        $buttons = 3;
        break;

    case 'unknown_destination_build':
        $title = gT("Unknown destination build!");
        $message = gT("It seems that ComfortUpdate doesn't know the version you're trying to update to. Please restart the process.");
        $buttons = 0;
        break;

    case 'file_locked':
        $title = gT('Update server busy');
        $message = gT('The update server is currently busy.') . ' ' . gT('Most likely this is a temporary problem, so please try again in 5 minutes. If the issue persists, please contact LimeSurvey support.');
        $buttons = 0;
        break;

    case 'server_error_creating_zip_update':
        $title = gT('Server error!');
        $message = gT('An error occurred while creating your update package file.') . ' ' . gT('Most likely this is a temporary problem, so please try again in 5 minutes. If the issue persists, please contact LimeSurvey support.');
        $buttons = 0;
        break;

    case 'server_error_getting_checksums':
        $title = gT('Server error!');
        $message = gT('An error occurred while getting checksums.') . ' ' . gT('Most likely this is a temporary problem, so please try again in 5 minutes. If the issue persists, please contact LimeSurvey support.');
        $buttons = 0;
        break;

    case 'cant_get_changeset':
        $title = gT('Server error!');
        $message = gT('An error occurred while getting the changeset.') . ' ' . gT('Most likely this is a temporary problem, so please try again in 5 minutes. If the issue persists, please contact LimeSurvey support.');
        $buttons = 0;
        break;

    case 'wrong_token':
        $title = gT('Unknown session');
        $message = gT('Your session with the ComfortUpdate server is not valid or expired. Please restart the process.');
        $buttons = 0;
        break;

    case 'zip_error':
        $title = gT('Error while creating zip file');
        $message = gT("An error occurred while creating a backup of your files. Check your local system (permission, available space, etc.)");
        break;


    case 'no_updates_infos':
        $title = gT("Could not retrieve update info data");
        $message = gT("ComfortUpdate could not find the update info data.");
        break;

    case 'cant_remove_deleted_files':
        $title = gT("Could not remove deleted files");
        $message =  gT("ComfortUpdate couldn't remove one or more files that were deleted with the update.");
        $message .=  $errorObject->message;
        break;

    case 'cant_remove_deleted_directory':
        $title = gT("Could not remove deleted directories");
        $message =  gT("ComfortUpdate couldn't remove one or more directories that were deleted with the update.");
        break;


    default:
        $title = $errorObject->error;
        $message = gT('Unknown error.') . ' ' . gT('Please contact the LimeSurvey team.');
        $buttons = 0;
        break;
}
?>
<div class="row">
    <div class="col-12">
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', [
                'header' => $title,
            'text' => $message,
            'type' => 'danger',
        ]);
        ?>
    </div>
</div>

<div class="row">
    <div class="col-12 pt-2">
    <?php if ($buttons == 1) : ?>
            <a class="btn btn-outline-secondary" href="https://community.limesurvey.org/comfort-update-extension/"
               role="button" aria-disabled="false" target="_blank">
                <?php eT("Buy a new key"); ?>
            </a>

            <a class="btn btn-outline-secondary" href="<?php echo Yii::app()->createUrl("admin/update/sa/managekey"); ?>"
               role="button" aria-disabled="false">
                <?php eT("Enter a new key"); ?>
            </a>
    <?php endif; ?>
    <?php if ($buttons == 3) : ?>
            <a class="btn btn-outline-secondary" href="<?php echo Yii::app()->createUrl("admin/update/sa/managekey"); ?>"
               role="button" aria-disabled="false">
                <?php eT("Enter a new key"); ?>
            </a>
        <?php endif; ?>
        <a class="btn btn-outline-secondary" href="<?php echo Yii::app()->createUrl("admin/update"); ?>" role="button"
           aria-disabled="false">
            <?php eT("Cancel"); ?>
        </a>
    </div>
</div>