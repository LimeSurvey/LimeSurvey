<h3 class='pagetitle h3'><?php eT('Confirm uploaded plugin'); ?></h3>

<?php // Only show config summary if config could be found. ?>
<?php if (isset($config)) : ?>
    <?php echo CHtml::form(
        Yii::app()->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'installUploadedPlugin'
            ]
        ),
        'post',
        ['class' => 'row']
    ); ?>

    <input type="hidden" name="isUpdate" value="<?php echo json_encode($isUpdate); ?>"/>

    <div class="mb-3">
        <?php if ($isUpdate) : ?>
            <?php
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => gT('The following plugin will be updated. Please click "Update" to update the plugin, or "Abort" to abort.'),
                'type' => 'info',
            ]);
            ?>
        <?php else : ?>
            <?php
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => gT('The following plugin will be installed. Please click "Install" to install the plugin, or "Abort" to abort. Aborting will remove the files from the file system.'),
                'type' => 'info',
            ]);
            ?>
        <?php endif; ?>
    </div>

    <!-- Name -->
    <div class="mb-3 col-12">
        <label class="col-2 form-label"><?php eT("Name:"); ?></label>
        <?= htmlentities((string) $config->getName()); ?>
    </div>

    <!-- Description -->
    <div class="mb-3 col-12">
        <label class="col-2 form-label"><?php eT("Description:"); ?></label>
        <?= htmlentities((string) $config->getDescription()); ?>
    </div>

    <!-- Version -->
    <div class="mb-3 col-12">
        <label class="col-2 form-label"><?php eT("Version:"); ?></label>
        <?= htmlentities((string) $config->getVersion()); ?>
    </div>

    <!-- Author -->
    <div class="mb-3 col-12">
        <label class="col-2 form-label"><?php eT("Author:"); ?></label>
        <?= htmlentities((string) $config->getAuthor()); ?>
    </div>

    <!-- Compatible -->
    <div class="mb-3 col-12">
        <label class="col-2 form-label"><?php eT("Compatible"); ?></label>
        <?php if ($config->isCompatible()) : ?>
            <span class="ri-check-fill text-success"></span>
        <?php else : ?>
            <span class="ri-close-fill"></span>
        <?php endif; ?>
    </div>

    <!-- Buttons -->
        <div class="col-2">&nbsp;</div>
        <div class="col-4">
            <a href="<?php echo $abortUrl; ?>" class="btn btn-warning"><?php eT("Abort"); ?></a>
            <?php if ($isUpdate) : ?>
                <input type="submit" class="btn btn-primary" value="<?php eT("Update"); ?>"/>
            <?php else : ?>
                <input type="submit" class="btn btn-primary" value="<?php eT("Install"); ?>"/>
            <?php endif; ?>
        </div>

    </form>

<?php else : ?>
    <?php
    $this->widget('ext.AlertWidget.AlertWidget', [
        'text' => gT('Error: Found no configuration for plugin. Please contact the plugin author.'),
        'type' => 'warning',
    ]);
    ?>
<?php endif; ?>
