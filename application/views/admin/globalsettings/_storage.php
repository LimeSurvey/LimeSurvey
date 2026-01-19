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
                <button id='global-settings-calculate-storage' class='btn btn-outline-secondary ' type="button" >
                    <i class="ri-settings-5-fill"></i>&nbsp;
                    <?php eT('Calculate storage');?>
</button>
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
        <!-- Max size for db dump setting -->
        <div id="global-settings-max-size-for-db-dump-settings" class="mb-3">
            <label class="form-label" for='global-settings-max-size-for-db-dump'><?php eT("Set limit in megabytes for direct download of the database.");?></label>
            <div>
                <input class="form-control" type="number" id="global-settings-max-size-for-db-dump" min="0" step="1" name="global-settings-max-size-for-db-dump" value="<?php echo App()->getConfig('maxDatabaseSizeForDump') ?? 256; ?>" />
                <span class='hint'>
                    <?php eT('The recommended value is 256 MB. Depending on the system, a higher value could lead to slower performance.');?>
                </span>
            </div>
        </div>
    </div>
</div>
