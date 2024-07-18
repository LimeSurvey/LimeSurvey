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

<?php if (YII_DEBUG) : ?>
    <?php
    $this->widget('ext.AlertWidget.AlertWidget', [
        'tag' => 'p',
        'text' => gT(
            'this subview is rendered from global setting module. This message is shown only when debug mode is on'
        ),
        'type' => 'info',
        'showCloseButton' => false,
    ]);
    ?>
<?php endif;?>

<br />
<div class="table-responsive">
    <table class='table table-hover' role="list">
        <tr role="listitem">
            <th role="none"><?php eT("Users"); ?>:</th>
            <td role="none"><?php echo $usercount; ?></td>
        </tr>
        <tr role="listitem">
            <th role="none"><?php eT("Surveys"); ?>:</th>
            <td role="none"><?php echo $surveycount; ?></td>
        </tr>
        <tr role="listitem">
            <th role="none"><?php eT("Active surveys"); ?>:</th>
            <td role="none"><?php echo $activesurveycount; ?></td>
        </tr>
        <tr role="listitem">
            <th role="none"><?php eT("Deactivated result tables"); ?>:</th>
            <td role="none"><?php echo $deactivatedsurveys; ?></td>
        </tr>
        <tr role="listitem">
            <th role="none"><?php eT("Active survey participants tables"); ?>:</th>
            <td role="none"><?php echo $activetokens; ?></td>
        </tr>
        <tr role="listitem">
            <th role="none"><?php eT("Deactivated survey participants tables"); ?>:</th>
            <td role="none"><?php echo $deactivatedtokens; ?></td>
        </tr>

        <?php if (YII_DEBUG) : ?>
            <!-- If debug mode is on, we show the new parameter -->
            <tr role="listitem">
                <th role="none">Value of myNewParam :</th>
                <td role="none"><?php echo $myNewParam; ?></td>
            </tr>
        <?php endif; ?>
        <?php
        if (Yii::app()->getConfig('iFileUploadTotalSpaceMB') > 0) {
            $fUsed = calculateTotalFileUploadUsage();
            ?>
            <tr>
                <th><?php eT("Used/free space for file uploads"); ?>:</th>
                <td><?php echo sprintf('%01.2F', $fUsed); ?> MB / <?php echo sprintf('%01.2F', Yii::app()->getConfig('iFileUploadTotalSpaceMB') - $fUsed); ?> MB</td>
            </tr>
            <?php
        }
        ?>
    </table>
</div>
<?php
if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
    ?>
        <p><a href="<?php echo $this->createUrl('admin/globalsettings', array('sa' => 'showphpinfo')) ?>" target="blank" class="button"><?php eT("Show PHPInfo"); ?></a></p>
    <?php
}
?>
