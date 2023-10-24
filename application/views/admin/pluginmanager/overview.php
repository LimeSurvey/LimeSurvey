<?php
/**
 * @var array $plugin Plugin model attributes (database values)
 * @var PluginBase $pluginObject
 * @var xml $config Config XML
 * @var xml $metadata Metadata config
 */
?>

<!-- Name -->
<div class="row">
    <label class="col-md-4 form-label text-end"><?php eT("Name:"); ?></label>
    <div class="col-md-8"><?php echo $metadata->name; ?></div>
</div>

<!-- Description -->
<div class="row">
    <label class="col-md-4 form-label text-end"><?php eT("Description:"); ?></label>
    <div class="col-md-8"><?php echo $metadata->description; ?></div>
</div>

<!-- Author -->
<div class="row">
    <label class="col-md-4 form-label text-end"><?php eT("Author:"); ?></label>
    <div class="col-md-8"><?php echo $metadata->author; ?></div>
</div>

<!-- Email -->
<div class="row">
    <label class="col-md-4 form-label text-end"><?php eT("Email:"); ?></label>
    <div class="col-md-8"><a href="mailto:<?php echo $metadata->authorEmail; ?>"><?php echo $metadata->authorEmail; ?></a></div>
</div>

<!-- Url -->
<div class="row">
    <label class="col-md-4 form-label text-end"><?php eT("Web page:"); ?></label>
    <div class="col-md-8"><a href="<?php echo $metadata->authorUrl; ?>" target="_blank"><?php echo $metadata->authorUrl; ?></a></div>
</div>

<!-- Version -->
<div class="row">
    <label class="col-md-4 form-label text-end"><?php eT("Version:"); ?></label>
    <div class="col-md-8"><?php echo $plugin['version']; ?></div>
</div>

<!-- Last updated -->
<div class="row">
    <label class="col-md-4 form-label text-end"><?php eT("Last updated:"); ?></label>
    <div class="col-md-8"><?php echo $metadata->lastUpdate; ?></div>
</div>

<!-- License -->
<div class="row">
    <label class="col-md-4 form-label text-end"><?php eT("License:"); ?></label>
    <div class="col-md-8"><?php echo $metadata->license; ?></div>
</div>

<!-- Compatible -->
<div class="row">
    <label class="col-md-4 form-label text-end"><?php eT("Compatible"); ?></label>
    <?php if ($plugin->isCompatible()): ?>
        <div class="col-md-4"><span class="ri-check-fill text-success"></span></div>
    <?php else: ?>
        <div class="col-md-4"><span class="ri-close-fill text-danger"></span></div>
    <?php endif; ?>
</div>

<!-- Active -->
<?php if ($showactive): ?>
    <div class="row">
        <label class="col-md-4 form-label text-end"><?php eT("Active:"); ?></label>
        <?php if ($plugin['active']): ?>
            <div class="col-md-2"><span class="ri-check-fill text-success"></span></div>
            <div class="col-md-2">
                <?= $plugin->getDeactivateButton() ?>
            </div>
        <?php else: ?>
            <div class="col-md-2"><span class="ri-close-fill text-danger"></span></div>
            <div class="col-md-2">
                <?= $plugin->getActivateButton() ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($plugin['active']): ?>
    <?php
        $pluginStatus = $pluginObject->getHealthStatusText();
    ?>
    <?php if (!empty($pluginStatus)): ?>
        <!-- Status -->
        <div class="row">
            <label class="col-md-4 form-label text-end"><?php eT("Status:"); ?></label>
            <div class="col-md-8"><?= $pluginStatus ?></div>
        </div>
    <?php endif; ?>
<?php endif; ?>