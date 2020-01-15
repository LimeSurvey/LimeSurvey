<div class='col-lg-12'>
    <div class='pagetitle h3'><?php eT('Plugin manager - scanned files'); ?></div>
    <?php foreach($result as $name => $scannedPlugin): ?>
        <div class='form-group col-lg-12'>
            <label class='control-label col-sm-4'>
                <?php echo $name; ?>
            </label>
            <?php if ($scannedPlugin['isCompatible']): ?>
                <?php echo CHtml::beginForm($installUrl, 'post', ['style' => 'display: inline-block;']); ?>
                    <input type='hidden' name='pluginName' value='<?php echo $name; ?>' />
                    <button href='' class='btn btn-success' data-toggle='tooltip' title='<?php eT('Install this plugin'); ?>'>
                        <i class='fa fa-download'></i>&nbsp;
                        <?php eT('Install'); ?>
                    </button>
                </form>
            <?php elseif ($scannedPlugin['load_error'] == 0
                          && $scannedPlugin['extensionConfig'] != null
                          && !$scannedPlugin['isCompatible']): ?>
                <i class='fa fa-ban text-warning'></i>&nbsp;
                <span class='text-warning'><?php eT('Plugin is not compatible with your LimeSurvey version.'); ?></span>
            <?php elseif ($scannedPlugin['load_error'] == 0 && $scannedPlugin['extensionConfig'] == null): ?>
                <i class='fa fa-ban text-warning'></i>&nbsp;
                <span class='text-warning'><?php eT('Missing configuration file.'); ?></span>
            <?php else: ?>
                <i class='fa fa-exclamation-triangle text-warning'></i>&nbsp;
                <span class='text-warning'><?php eT('Load error. Please contact the plugin author.'); ?></span>
            <?php endif; ?>

            <a href='' class='btn btn-danger' data-toggle='tooltip' title='<?php eT('Delete this plugin from the file system'); ?>'>
                <i class='fa fa-trash'></i>&nbsp;
                Delete files
            </a>
        </div>
    <?php endforeach; ?>
</div>
