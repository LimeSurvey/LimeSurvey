<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT('Import role')]
);
?>

<?= TbHtml::formTb(
    null,
    App()->createUrl('userRole/importXML'),
    'post',
    ["id" => "RoleControl--modalform-import", 'enctype' => 'multipart/form-data']
) ?>

<div class="modal-body">
    <div class="mb-3" id="RoleControl--errors">
    </div>
    <div class="mb-3">
        <label class="form-label" for="the_file"><?= gT('Select role-XML file') ?></label>
        <input type="file" name="the_file" id="the_file" class="form-control"/>
    </div>
</div>

<div class="modal-footer modal-footer-buttons" style="margin-top: 15px; ">
    <button class="btn btn-cancel" id="exitForm" data-bs-dismiss="modal">
        <?= gT('Cancel') ?>
    </button>
    <button class="btn btn-primary" id="submitForm">
        <?= gT('Import') ?>
    </button>
</div>
</form>
