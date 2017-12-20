<?php
/**
 * This view display the result of backup process, and warn about database
 *
 * @var int $destinationBuild the destination build
 * @var string $basefilename the base file name of the backup file
 * @var string $tempdir the temp dir where the backup file is saved
 */

?>

<h2 class="maintitle"><?php eT('Creating file backup')?></h2>

<?php
    if(isset($dbBackupInfos->html))
        echo $dbBackupInfos->html;
?>

<div class="updater-background">
    <p class="success" style="text-align: left;">
        <strong><?php echo sprintf(gT("File backup created: %s"),''); ?></strong>
        <br/>
        <?php echo $tempdir.DIRECTORY_SEPARATOR.'LimeSurvey_files_backup_'.$basefilename.'.zip'; ?><br/>
        <a class="btn btn-success" href="<?php echo Yii::app()->getBaseUrl(true);?>/tmp/LimeSurvey_files_backup_<?php echo $basefilename;?>.zip" title="<?php eT('Download this file');?>"><?php eT('Download this file');?></a>
    </p>


    <?php if($dbBackupInfos->result):?>
        <p class="success" style="text-align: left;">
            <strong><?php eT('DB backup created:'); ?></strong>
            <br/>
            <?php echo $dbBackupInfos->message; ?>
            <br/>
            <a class="btn btn-success" href="<?php echo $dbBackupInfos->fileurl;?>" title="<?php eT('Download this file');?>"><?php eT('Download this file');?></a>
        </p>
    <?php else:?>
        <?php
            switch ($dbBackupInfos->message)
            {
                case 'db_changes':
                    $db_message = gT('At the end of the process the database will be updated.');
                case 'db_too_big':
                    $db_message = gT('Your database is too big to be saved!').' '.gT('Before proceeding please back up your database using a backup tool!');
                    break;
                case 'no_db_changes':
                    $db_message = gT('This update will not change the database. No database backup is required.');
                    break;
                case 'not_mysql':
                    $db_message = gT('Your database type is not MySQL!').' '.gT('Before proceeding please back up your database using a backup tool!');
                    break;
                case 'db_backup_zip_failed':
                    $db_message = gT('We could not zip your database!').' '.gT('Before proceeding please back up your database using a backup tool!');
                    break;
                default :
                    $db_message = gT('Unable to backup your database for unknown reason.').' '.gT('Before proceeding please back up your database using a backup tool!');
                    break;
            }
        ?>

    <p class="warning" style="text-align: left;">
        <strong><?php eT($db_message); ?></strong>
    </p>

    <?php endif;?>

    </p>
    <p class="information"  style="text-align: left"><?php eT('Please check any problems above and then proceed to the final step.'); ?>

    <?php $formUrl = Yii::app()->getController()->createUrl("admin/update/sa/step4/");?>
    <?php echo CHtml::beginForm($formUrl, 'post', array('id'=>'launchStep4Form')); ?>
        <!-- The destination build  -->
        <?php echo CHtml::hiddenField('destinationBuild' ,  $destinationBuild); ?>
        <?php echo CHtml::hiddenField('datasupdateinfo' , $datasupdateinfo);?>
        <?php  echo CHtml::hiddenField('access_token' , $access_token); ?>

        <a class="btn btn-default" href="<?php echo Yii::app()->createUrl("admin/update"); ?>" role="button" aria-disabled="false">
            <?php eT("Cancel"); ?>
        </a>

        <?php echo CHtml::submitButton(gT('Continue','unescaped'), array("class"=>"btn btn-default")); ?>
    <?php echo CHtml::endForm(); ?>
</div>

<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/assets/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
    $('#launchStep4Form').comfortUpdateNextStep({'step': 4});
</script>
