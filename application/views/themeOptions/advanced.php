<div role="tabpanel" class="tab-pane <?php echo Yii::app()->getConfig('debug') > 1 ? '' : 'd-none'; ?>" id="advanced">
    <?php
    $this->widget('ext.AlertWidget.AlertWidget', [
    'text' => gT('All fields below (except CSS framework name) must be either a valid JSON array or the string "inherit".'),
    'type' => 'info',
    ]);
    $this->widget('ext.AlertWidget.AlertWidget', [
        'text' => '<strong>' . gT('Warning') . '</strong> ' . gT("Don't touch the values below unless you know what you're doing."),
        'type' => 'warning',
    ]);
    ?>

    <div class="row ls-space margin bottom-15">
        <div class="row mb-3">
            <div class="col-4">
                <label>
                    <?php printf(gT("Upload an image (maximum size: %d MB):"), getMaximumFileUploadSize() / 1024 / 1024); ?>
                </label>
            </div>
            <div class="col-8">
                <?php // loaded through upload_frontend form in themeOptions/update.php ?>
                <span id="fileselector">
                    <label class="btn btn-outline-secondary" for="upload_image">
                        <input class="d-none" id="upload_image" name="upload_image" type="file">
                        <i class="ri-upload-fill ls-space margin right-10"></i><?php eT("Upload"); ?>
                    </label>
                </span>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="progress">
                    <div id="upload_progress" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                        <span class="visually-hidden">0%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <hr/>
    </div>
    <div class="row">
        <p class="note"><?php echo sprintf(gT('Fields with %s are required.'), '<span class="required">*</span>'); ?></p>
        <?php $this->widget('ext.AlertWidget.AlertWidget', ['errorSummaryModel' => $model]); ?>
        <div class="row">
            <div class="mb-3">
                <?php echo $form->labelEx($model, 'files_css'); ?>
                <?php echo $form->textArea($model, 'files_css', ['rows' => 6, 'cols' => 50]); ?>
                <?php echo $form->error($model, 'files_css'); ?>
            </div>
        </div>
        <div class="row">
            <div class="mb-3">
                <?php echo $form->labelEx($model, 'files_js'); ?>
                <?php echo $form->textArea($model, 'files_js', ['rows' => 6, 'cols' => 50]); ?>
                <?php echo $form->error($model, 'files_js'); ?>
            </div>
        </div>
        <div class="row">
            <div class="mb-3">
                <?php echo $form->labelEx($model, 'files_print_css'); ?>
                <?php echo $form->textArea($model, 'files_print_css', ['rows' => 6, 'cols' => 50]); ?>
                <?php echo $form->error($model, 'files_print_css'); ?>
            </div>
        </div>
        <div class="row">
            <div class="mb-3">
                <?php echo $form->labelEx($model, 'options'); ?>
                <?php echo $form->textArea($model, 'options', ['rows' => 6, 'cols' => 50]); ?>
                <?php echo $form->error($model, 'options'); ?>
            </div>
        </div>
        <div class="row">
            <div class="mb-3">
                <?php echo $form->labelEx($model, 'cssframework_name'); ?>
                <?php echo $form->textField($model, 'cssframework_name', ['size' => 45, 'maxlength' => 45]); ?>
                <?php echo $form->error($model, 'cssframework_name'); ?>
            </div>
        </div>
        <div class="row">
            <div class="mb-3">
                <?php echo $form->labelEx($model, 'cssframework_css'); ?>
                <?php echo $form->textArea($model, 'cssframework_css', ['rows' => 6, 'cols' => 50]); ?>
                <?php echo $form->error($model, 'cssframework_css'); ?>
            </div>
        </div>
        <div class="row">
            <div class="mb-3">
                <?php echo $form->labelEx($model, 'cssframework_js'); ?>
                <?php echo $form->textArea($model, 'cssframework_js', ['rows' => 6, 'cols' => 50]); ?>
                <?php echo $form->error($model, 'cssframework_js'); ?>
            </div>
        </div>
        <div class="row">
            <div class="mb-3">
                <?php echo $form->labelEx($model, 'packages_to_load'); ?>
                <?php echo $form->textArea($model, 'packages_to_load', ['rows' => 6, 'cols' => 50]); ?>
                <?php echo $form->error($model, 'packages_to_load'); ?>
            </div>
        </div>
        <div class="row buttons d-none">
            <?php echo TbHtml::submitButton($model->isNewRecord ? gT('Create') : gT('Save'), ['class' => 'btn-primary']); ?>
        </div>

    </div>
</div>
