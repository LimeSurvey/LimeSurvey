<?php

/**
 * @since 2017-09-20
 * @author Olle Härstedt
 */

?>

<div class="container">
    <div class="ls-flex-column ls-space padding left-5 right-35 col-12">
        <div id='global-settings-storage' class="mb-3">
            <label class="form-label"  for='global-settings-calculate-storage'><?=gT("Recalculates the storage used by all your files in the upload folders")?></label>
            <div class="">
                <input type='hidden' name='global-settings-storage-url' value='<?php echo Yii::app()->createUrl('admin/globalsettings', array('sa' => 'getStorageData')); ?>' />
                <a id='global-settings-calculate-storage' class='btn btn-outline-secondary '>
                    <i class="ri-settings-5-fill"></i>&nbsp;
                    <?php eT('Calculate storage');?>
                </a>
                <br/>
                <span class='hint'>
                    <?php eT('Depending on the number of uploaded files, this might take some time.');?>
                </span>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label"  for='overwritefiles'><?php eT("Overwrite files with the same name when uploaded, moved or copied through the editor/file-manager?");?></label>
            <div>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name'          => 'overwritefiles',
                    'checkedOption' => App()->getConfig('overwritefiles') === 'Y' ? '1' : 0,
                    'selectOptions' => [
                        '1' => gT('On'),
                        '0' => gT('Off'),
                    ]
                ]); ?>
            </div>
        </div>
    </div>
</div>
