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
 */
?>
<br />
<?php // Name/value pairs: a description list instead of a table ?>
<dl class="row mx-0 mb-3 global-settings-overview">
    <dt class="col-8 col-md-6 py-2 border-bottom"><?php eT("Users"); ?>:</dt>
    <dd class="col-4 col-md-6 py-2 mb-0 border-bottom"><?php echo $usercount; ?></dd>
    <dt class="col-8 col-md-6 py-2 border-bottom"><?php eT("Surveys"); ?>:</dt>
    <dd class="col-4 col-md-6 py-2 mb-0 border-bottom"><?php echo $surveycount; ?></dd>
    <dt class="col-8 col-md-6 py-2 border-bottom"><?php eT("Active surveys"); ?>:</dt>
    <dd class="col-4 col-md-6 py-2 mb-0 border-bottom"><?php echo $activesurveycount; ?></dd>
    <dt class="col-8 col-md-6 py-2 border-bottom"><?php eT("Deactivated result tables"); ?>:</dt>
    <dd class="col-4 col-md-6 py-2 mb-0 border-bottom"><?php echo $deactivatedsurveys; ?></dd>
    <dt class="col-8 col-md-6 py-2 border-bottom"><?php eT("Active survey participant lists"); ?>:</dt>
    <dd class="col-4 col-md-6 py-2 mb-0 border-bottom"><?php echo $activetokens; ?></dd>
    <dt class="col-8 col-md-6 py-2 border-bottom"><?php eT("Deactivated survey participant lists"); ?>:</dt>
    <dd class="col-4 col-md-6 py-2 mb-0 border-bottom"><?php echo $deactivatedtokens; ?></dd>

    <?php
    if (Yii::app()->getConfig('iFileUploadTotalSpaceMB') > 0) {
        $fUsed = calculateTotalFileUploadUsage();
        ?>
        <dt class="col-8 col-md-6 py-2 border-bottom"><?php eT("Used/free space for file uploads"); ?>:</dt>
        <dd class="col-4 col-md-6 py-2 mb-0 border-bottom"><?php echo sprintf('%01.2F', $fUsed); ?> MB / <?php echo sprintf('%01.2F', Yii::app()->getConfig('iFileUploadTotalSpaceMB') - $fUsed); ?> MB</dd>
        <?php
    }
    ?>
</dl>
<?php
if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
    ?>
        <p><a href="<?php echo $this->createUrl('admin/globalsettings', array('sa' => 'showphpinfo')) ?>" target="blank" class="btn btn-primary"><?php eT("Show PHPInfo"); ?></a></p>
    <?php
}
?>
