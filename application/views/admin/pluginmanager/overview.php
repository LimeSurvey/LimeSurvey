<?php
/**
 * @var array $plugin Plugin model attributes (database values)
 * @var PluginBase $pluginObject
 * @var xml $config Config XML
 * @var xml $metadata Metadata config
 */
?>

<div class="container-fluid">

    <!-- Name -->
    <div class="row">
        <label class="col-sm-4 control-label text-right"><?php eT("Name:"); ?></label>
        <div class="col-sm-8"><?php echo $metadata->name; ?></div>
    </div>

    <!-- Description -->
    <div class="row">
        <label class="col-sm-4 control-label text-right"><?php eT("Description:"); ?></label>
        <div class="col-sm-8"><?php echo $metadata->description; ?></div>
    </div>

    <!-- Author -->
    <div class="row">
        <label class="col-sm-4 control-label text-right"><?php eT("Author:"); ?></label>
        <div class="col-sm-8"><?php echo $metadata->author; ?></div>
    </div>

    <!-- Email -->
    <div class="row">
        <label class="col-sm-4 control-label text-right"><?php eT("Email:"); ?></label>
        <div class="col-sm-8"><a href="mailto:<?php echo $metadata->authorEmail; ?>"><?php echo $metadata->authorEmail; ?></a></div>
    </div>

    <!-- Url -->
    <div class="row">
        <label class="col-sm-4 control-label text-right"><?php eT("Web page:"); ?></label>
        <div class="col-sm-8"><a href="<?php echo $metadata->authorUrl; ?>" target="_blank"><?php echo $metadata->authorUrl; ?></a></div>
    </div>

    <!-- Version -->
    <div class="row">
        <label class="col-sm-4 control-label text-right"><?php eT("Version:"); ?></label>
        <div class="col-sm-8"><?php echo $plugin['version']; ?></div>
    </div>

    <!-- Last updated -->
    <div class="row">
        <label class="col-sm-4 control-label text-right"><?php eT("Last updated:"); ?></label>
        <div class="col-sm-8"><?php echo $metadata->lastUpdate; ?></div>
    </div>

    <!-- License -->
    <div class="row">
        <label class="col-sm-4 control-label text-right"><?php eT("License:"); ?></label>
        <div class="col-sm-8"><?php echo $metadata->license; ?></div>
    </div>

    <!-- Compatible -->
    <div class="row">
        <label class="col-sm-4 control-label text-right"><?php eT("Compatible"); ?></label>
        <?php if ($plugin->isCompatible()): ?>
            <div class="col-sm-4"><span class="fa fa-check text-success"></span></div>
        <?php else: ?>
            <div class="col-sm-4"><span class="fa fa-times text-warning"></span></div>
        <?php endif; ?>
    </div>

    <!-- Active -->
    <?php if ($showactive): ?>
        <div class="row">
            <label class="col-sm-4 control-label text-right"><?php eT("Active:"); ?></label>
            <?php if ($plugin['active']): ?>
                <div class="col-sm-2"><span class="fa fa-check text-success"></span></div>
                <div class="col-sm-2">
                    <a data-toggle="tooltip" title="<?php eT('Deactivate'); ?>" href='#activate' data-action='activate' data-id='<?php echo $plugin['id']; ?>' class='ls_action_changestate btn btn-warning btn-xs btntooltip'>
                       <span class='fa fa-power-off'></span>
                    </a>
                </div>
            <?php else: ?>
                <div class="col-sm-2"><span class="fa fa-times text-warning"></span></div>
                <div class="col-sm-2">
                    <a data-toggle="tooltip" title="<?php eT('Activate'); ?>" href='#activate' data-action='activate' data-id='<?php echo $plugin['id']; ?>' class='ls_action_changestate btn btn-default btn-xs btntooltip'>
                       <span class='fa fa-power-off'></span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
