<?php

/**
 * @since 2017-09-20
 * @author Olle Härstedt
 */

?>

<div class="container-fluid">
    <div class="ls-flex-column ls-space padding left-5 right-35 col-md-12">
        <div id='global-settings-storage' class="form-group">
            <label class="control-label"  for='global-settings-calculate-storage'><?=gT("Recalculates the storage used by all your files in the upload folders")?></label>
            <div class="">
                <input type='hidden' name='global-settings-storage-url' value='<?php echo Yii::app()->createUrl('admin/globalsettings', array('sa' => 'getStorageData')); ?>' />
                <a id='global-settings-calculate-storage' class='btn btn-default '>
                    <span class='fa fa-cogs'></span>&nbsp;
                    <?php eT('Calculate storage');?>
                </a>
                <br/>
                <span class='hint'>
                    <?php eT('Depending on the number of uploaded files, this might take some time.');?>
                </span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label"  for='overwritefiles'><?php eT("Overwrite files with the same name when uploaded, moved or copied through the editor/file-manager");?></label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'overwritefiles',
                    'id' => 'overwritefiles',
                    'value' => getGlobalSetting('overwritefiles') == 'Y' ? '1' : 0,
                    'onLabel' => gT('On'),
                    'offLabel' => gT('Off')));
                ?>
            </div>
        </div>
    </div>
</div>
