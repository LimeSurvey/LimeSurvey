<div class='col-lg-12'>
    <div class='pagetitle h3'><?php eT('Confirm uploaded plugin'); ?></div>

    <?php // Only show config summary if config could be found. ?>
    <?php if (isset($config)): ?>

        <?php echo CHtml::form(
            Yii::app()->getController()->createUrl(
                '/admin/pluginmanager',
                [
                    'sa'       => 'installUploadedPlugin'
                ]
            ),
            'post'
        ); ?>

            <input type="hidden" name="isUpdate" value="<?php echo json_encode($isUpdate); ?>" />

            <?php if ($isUpdate): ?>
                <div class='alert alert-info'>
                    <p>
                        <i class='fa fa-info'></i>&nbsp;
                        <?php eT('The following plugin will be updated. Please click "Update" to update the plugin, or "Abort" to abort.'); ?>
                    </p>
                </div>
            <?php else: ?>
                <div class='alert alert-info'>
                    <p>
                        <i class='fa fa-info'></i>&nbsp;
                        <?php eT('The following plugin will be installed. Please click "Install" to install the plugin, or "Abort" to abort. Aborting will remove the files from the file system.'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Name -->
            <div class="form-group col-sm-12">
                <label class="col-sm-4 control-label"><?php eT("Name:"); ?></label>
                <div class="col-sm-4"><?php echo $config->getName(); ?></div>
            </div>

            <!-- Description -->
            <div class="form-group col-sm-12">
                <label class="col-sm-4 control-label"><?php eT("Description:"); ?></label>
                <div class="col-sm-8"><?php echo $config->getDescription(); ?></div>
            </div>

            <!-- Version -->
            <div class="form-group col-sm-12">
                <label class="col-sm-4 control-label"><?php eT("Version:"); ?></label>
                <div class="col-sm-4"><?php echo $config->getVersion(); ?></div>
            </div>

            <!-- Author -->
            <div class="form-group col-sm-12">
                <label class="col-sm-4 control-label"><?php eT("Author:"); ?></label>
                <div class="col-sm-4"><?php echo $config->getAuthor(); ?></div>
            </div>

            <!-- Compatible -->
            <div class="form-group col-sm-12">
                <label class="col-sm-4 control-label"><?php eT("Compatible"); ?></label>
                <?php if ($config->isCompatible()): ?>
                    <div class="col-sm-4"><span class="fa fa-check text-success"></span></div>
                <?php else: ?>
                    <div class="col-sm-4"><span class="fa fa-times text-warning"></span></div>
                <?php endif; ?>
            </div>

            <!-- Buttons -->
            <div class="form-group col-sm-12">
                <label class="col-sm-4 control-label"></label>
                <div class="col-sm-4">
                    <?php if ($isUpdate): ?>
                        <input type="submit" class="btn btn-success" value="<?php eT("Update");?>" />
                    <?php else: ?>
                        <input type="submit" class="btn btn-success" value="<?php eT("Install");?>" />
                    <?php endif; ?>
                    <a href="<?php echo $abortUrl; ?>" class="btn btn-warning" data-dismiss="modal"><?php eT("Abort");?></a>
                </div>
            </div>

        </form>

    <?php else: ?>

        <div class='alert alert-warning'>
            <p>
            <i class='fa fa-warning'></i>&nbsp;
            <?php eT('Error: Found no configuration for plugin. Please contact the plugin author.'); ?>
            </p>
        </div>

    <?php endif; ?>
</div>
