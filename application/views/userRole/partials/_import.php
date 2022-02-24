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
    <div class="container-center">
        <div class="row ls-space margin top-5 bottom-5 hidden" id="RoleControl--errors">
        </div>
        <div class="row ls-space margin top-5 bottom-5 hidden" id="RoleControl--errors">
        </div>
        <div class="row ls-space margin top-5 bottom-15">
            <label for="the_file"><?=gT('Select role-XML file')?></label>
            <input type="file" name="the_file" id="the_file" class="form control"/>
        </div>
        <div class="row ls-space margin top-5">
            <hr class="ls-space margin top-5 bottom-10"/>
        </div>
    </div>
</div>

<div class="modal-footer modal-footer-buttons" style="margin-top: 15px; ">
    <button class="btn btn-cancel" id="exitForm"><?=gT('Cancel')?></button>
    <button class="btn btn-success" id="submitForm"><?=gT('Import')?></button>
</div>
</form>
