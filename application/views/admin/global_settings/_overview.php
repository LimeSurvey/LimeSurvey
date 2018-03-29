<?php
/**
 * This view generate the 'overview' tab inside global settings.
 *
 * @var int $usercount
 * @var int $surveycount
 * @var int $activesurveycount
 * @var int $deactivatedsurveys
 * @var int $activetokens
 * @var int $deactivatedtokens
 *
 */
?>

<br /><table class='table table-striped table-bordered'>
    <tr>
        <th ><?php eT("Users"); ?>:</th><td><?php echo $usercount; ?></td>
    </tr>
    <tr>
        <th ><?php eT("Surveys"); ?>:</th><td><?php echo $surveycount; ?></td>
    </tr>
    <tr>
        <th ><?php eT("Active surveys"); ?>:</th><td><?php echo $activesurveycount; ?></td>
    </tr>
    <tr>
        <th ><?php eT("Deactivated result tables"); ?>:</th><td><?php echo $deactivatedsurveys; ?></td>
    </tr>
    <tr>
        <th ><?php eT("Active survey participants tables"); ?>:</th><td><?php echo $activetokens; ?></td>
    </tr>
    <tr>
        <th ><?php eT("Deactivated survey participants tables"); ?>:</th><td><?php echo $deactivatedtokens; ?></td>
    </tr>
    <?php
        if (Yii::app()->getConfig('iFileUploadTotalSpaceMB')>0)
        {
            $fUsed=calculateTotalFileUploadUsage();
        ?>
        <tr>
            <th ><?php eT("Used/free space for file uploads"); ?>:</th><td><?php echo sprintf('%01.2F',$fUsed); ?> MB / <?php echo sprintf('%01.2F',Yii::app()->getConfig('iFileUploadTotalSpaceMB')-$fUsed); ?> MB</td>
        </tr>
        <?php
        }
    ?>
</table>
<?php
    if (Permission::model()->hasGlobalPermission('superadmin','read'))
    {
    ?>
        <p><a href="<?php echo $this->createUrl('admin/globalsettings',array('sa'=>'showphpinfo')) ?>" target="blank" class="button"><?php eT("Show PHPInfo"); ?></a></p>
    <?php
    }
    ?>


