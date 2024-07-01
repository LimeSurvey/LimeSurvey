<div id="exportcsv" title="exportcsv" role="dialog" tabindex="-1" class="modal fade">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php eT("Export participants"); ?> </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form">
                    <div class='mb-3 row'>
                        <div class="col-4">
                            <label class='form-label' for='attributes'><?php eT('Attributes to export:');?></label>
                            <input type="checkbox" value="" id="select-all">
                            <label class="form-check-label" for="select-all">
                                <?= gT('Select all'); ?>
                            </label>
                        </div>
                        <div class='col-8'>
                            <?php $this->widget('yiiwheels.widgets.select2.WhSelect2',
                                array(
                                    'asDropDownList' => true,
                                    'htmlOptions' => ['multiple' => 'multiple', 'id' => 'attributes'],
                                    'data' => array_combine(array_column($aAttributes, 'attribute_id'), array_column($aAttributes, 'defaultname')),
                                    'value' => null,
                                    'name' => 'attributes',
                                    'pluginOptions' => []
                                )
                            ); ?>
                        </div>
                    </div>
                <?php if (Yii::app()->getConfig('hideblacklisted') != 'N'): ?>
                    <?php
                    $this->widget('ext.AlertWidget.AlertWidget', [
                        'text' => gT('If you want to export blocklisted participants, set "Hide blocklisted participants" to "No" in CPDB settings.'),
                        'type' => 'info',
                    ]);
                    ?>
                <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT('Cancel'); ?></button>
                <button type="button" class="btn btn-primary exportButton">
                    <?php eT('Export'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div id='exportcsvallprocessing' title='exportcsvall' style='display:none'>
    <p><?php eT('Please wait, loading data...');?></p>
    <div class="preloader loading">
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
    </div>
</div>

<div id='exportcsvallnorow' title='exportcsvallnorow' role="dialog" tabindex="-1" class="modal fade">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php eT("Export participants"); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php eT("There are no participants to be exported."); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php eT('Close'); ?></button>
            </div>
        </div>
    </div>
</div>
