<?php

/**
 * Subview: Userimport form
 *
 * @package UserManagement
 * @author LimeSurvey GmbH <info@limesurvey.org>
 * @license GPL3.0
 */
?>

<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT('Import users')]
);
?>

<?= TbHtml::formTb(
    null,
    App()->createUrl('userManagement/importUsers', ['importFormat' => $importFormat]),
    'post',
    ["id" => "UserManagement--modalform--import", 'enctype' => 'multipart/form-data']
) ?>

<div class="modal-body">
    <?php
    $this->widget('ext.AlertWidget.AlertWidget', [
        'text' => $note,
        'type' => 'info',
    ]);
    ?>
    <div class="md-3" id="UserManagement--errors">
    </div>
    <div class="mb-3 ">
        <input type="checkbox" name="overwrite" value="overwrite" id="overwrite">
        <label class="form-check-label" for="overwrite">
            <?= eT("Overwrite existing users"); ?>
        </label>
    </div>

    <div class="mb-3">
        <label class="form-label" for="the_file"><?= sprintf(gT('Select %s file:', 'js'), $importFormat); ?></label>
        <input class="form-control" id="the_file" type="file" accept="<?= $allowFile ?>" name="the_file" id="the_file"
               class="form control" required/>
    </div>
</div>

<div class="modal-footer modal-footer-buttons">
    <button class="btn btn-cancel" id="exitForm" data-bs-dismiss="modal">
        <?= gT('Cancel') ?>
    </button>
    <button class="btn btn-primary" id="submitForm">
        <?= gT('Import') ?>
    </button>
</div>
<?= CHtml::endForm() ?>
