<?php
/**
 * @var array $result
 * @var string $installUrl
 */

?>
<div class="row">
    <div class='col-12'>
        <?php foreach ($result as $name => $scannedPlugin) : ?>
            <div class='mb-3 col-12'>
                <label class='form-label col-md-4'>
                    <?php echo $name; ?>
                </label>
                <?php if ($scannedPlugin['load_error'] == 0 && $scannedPlugin['extensionConfig'] == null) : ?>
                    <i class='ri-forbid-2-line'></i>&nbsp;
                    <span class='text-danger'><?php eT('Missing configuration file.'); ?></span>
                <?php elseif ($scannedPlugin['isCompatible']) : ?>
                    <?php echo CHtml::beginForm($installUrl, 'post', ['style' => 'display: inline-block;']); ?>
                    <input type='hidden' name='pluginName' value='<?php echo $name; ?>'/>
                    <button href='' class='btn btn-primary' data-bs-toggle='tooltip' title='<?php eT('Install this plugin'); ?>'>
                        <i class='ri-download-fill'></i>
                        &nbsp;
                        <?php eT('Install'); ?>
                    </button>
                    <?= CHtml::endForm() ?>
                <?php elseif (
                    $scannedPlugin['load_error'] == 0
                    && $scannedPlugin['extensionConfig'] != null
                    && !$scannedPlugin['isCompatible']
                ) : ?>
                    <i class='ri-forbid-2-line'></i>&nbsp;
                    <span class='text-danger'><?php eT('Plugin is not compatible with your GititSurvey version.'); ?></span>
                <?php else : ?>
                    <i class='fri-error-warning-fill'></i>&nbsp;
                    <span class='text-danger'><?php eT('Load error. Please contact the plugin author.'); ?></span>
                <?php endif; ?>

                <?php if (isset($scannedPlugin['deleteUrl'])) : ?>
                <a href='#'
                   class='btn btn-outline-secondary'
                   data-bs-target='#confirmation-modal'
                   data-bs-toggle='modal'
                   data-post-url='<?= $scannedPlugin['deleteUrl'] ?>'
                   data-message='<?php eT('Are you sure you want to delete this plugin from the file system?'); ?>'
                   type='submit'>
                    <i class='ri-delete-bin-fill text-danger'></i>&nbsp;
                    <span data-bs-toggle='tooltip'
                          title='<?php eT('Delete this plugin from the file system'); ?>'>Delete files</span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
