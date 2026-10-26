<?php
/**
 * @var array $plugin Plugin model attributes (database values)
 * @var PluginBase $pluginObject
 * @var xml $config Config XML
 * @var xml $metadata Metadata config
 */
?>

<!-- Name -->
<div class="row mb-2">
    <div class="col-md-4 text-end fw-bold"><?php eT("Name:"); ?></div>
    <div class="col-md-8"><?php echo $metadata->name; ?></div>
</div>

<!-- Description -->
<div class="row mb-2">
    <div class="col-md-4 text-end fw-bold"><?php eT("Description:"); ?></div>
    <div class="col-md-8"><?php echo $metadata->description; ?></div>
</div>

<!-- Author -->
<div class="row mb-2">
    <div class="col-md-4 text-end fw-bold"><?php eT("Author:"); ?></div>
    <div class="col-md-8"><?php echo $metadata->author; ?></div>
</div>

<!-- Email -->
<?php if (trim((string) $metadata->authorEmail) !== '') : ?>
    <div class="row mb-2">
        <div class="col-md-4 text-end fw-bold"><?php eT("Email:"); ?></div>
        <div class="col-md-8"><a href="mailto:<?php echo $metadata->authorEmail; ?>"><?php echo $metadata->authorEmail; ?></a></div>
    </div>
<?php endif; ?>

<!-- Url -->
<div class="row mb-2">
    <div class="col-md-4 text-end fw-bold"><?php eT("Web page:"); ?></div>
    <div class="col-md-8"><a href="<?php echo $metadata->authorUrl; ?>" target="_blank"><?php echo $metadata->authorUrl; ?></a></div>
</div>

<!-- Version -->
<div class="row mb-2">
    <div class="col-md-4 text-end fw-bold"><?php eT("Version:"); ?></div>
    <div class="col-md-8"><?php echo $plugin['version']; ?></div>
</div>

<!-- Last updated -->
<div class="row mb-2">
    <div class="col-md-4 text-end fw-bold"><?php eT("Last updated:"); ?></div>
    <div class="col-md-8"><?php echo $metadata->lastUpdate; ?></div>
</div>

<!-- License -->
<div class="row mb-2">
    <div class="col-md-4 text-end fw-bold"><?php eT("License:"); ?></div>
    <div class="col-md-8"><?php echo $metadata->license; ?></div>
</div>

<!-- Compatible -->
<div class="row mb-2">
    <div class="col-md-4 text-end fw-bold"><?php eT("Compatible:"); ?></div>
    <?php if ($plugin->isCompatible()) : ?>
        <div class="col-md-8"><span role="img" aria-label="<?php eT('Yes'); ?>" class="ri-check-fill text-success align-middle"></span></div>
    <?php else : ?>
        <div class="col-md-8"><span role="img" aria-label="<?php eT('No'); ?>" class="ri-close-fill text-danger align-middle"></span></div>
    <?php endif; ?>
</div>

<!-- Active -->
<?php if ($showactive) : ?>
    <div class="row mb-2">
        <div class="col-md-4 text-end fw-bold"><?php eT("Status:"); ?></div>
        <div class="col-md-8"><?= $plugin->getStatus(true) ?></div>
    </div>
<?php endif; ?>

<?php if ($plugin['active']) : ?>
    <?php
        $pluginStatus = $pluginObject->getHealthStatusText();
    ?>
    <?php if (!empty($pluginStatus)) : ?>
        <!-- Status -->
        <div class="row mb-2">
            <div class="col-md-4 text-end fw-bold"><?php eT("Status:"); ?></div>
            <div class="col-md-8"><?= $pluginStatus ?></div>
        </div>
    <?php endif; ?>
<?php endif; ?>