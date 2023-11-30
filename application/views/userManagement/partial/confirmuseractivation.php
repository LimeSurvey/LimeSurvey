<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => sprintf(gT('%s user'), ucfirst($action))]
);
?>

<div class="modal-body">
    <p class='modal-body-text'><?= sprintf(gT('Do you want to %s this user?'), $action); ?></p>
</div>
<div class="modal-footer">
    <?=TbHtml::formTb(null, App()->createUrl('userManagement/userActivateDeactivate'), 'post', ["id"=>"UserManagement--modalform"])?>
        <input type="hidden" name="userid" value="<?= $userId ?>" />
        <input type="hidden" name="action" value="<?= $action ?>" />
        <button type="button"  class="btn btn-cancel" data-bs-dismiss="modal">&nbsp;<?php eT("Cancel"); ?></button>
        <button class="btn btn-primary">&nbsp;<?php eT("Save"); ?></button>
    <?php echo TbHtml::endForm() ?>
</div>
