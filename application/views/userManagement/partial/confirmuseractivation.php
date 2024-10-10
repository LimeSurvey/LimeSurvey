<?php

if ($action == 'activate') {
    Yii::app()->getController()->renderPartial(
        '/layouts/partial_modals/modal_header',
        ['modalTitle' => gT('Activate user')]
    );
} else {
    Yii::app()->getController()->renderPartial(
        '/layouts/partial_modals/modal_header',
        ['modalTitle' => gT('Deactivate user')]
    );
}

?>

<div class="modal-body">
    <?php if ($action == 'activate') : ?>
        <p class='modal-body-text'><?= gT('Do you want to activate this user?'); ?></p>
    <?php else : ?>
        <p class='modal-body-text'><?= gT('Do you want to deactivate this user?'); ?></p>
    <?php endif; ?>
</div>
<div class="modal-footer">
    <?=TbHtml::formTb(null, App()->createUrl('userManagement/userActivateDeactivate'), 'post', ["id"=>"UserManagement--modalform"])?>
        <input type="hidden" name="userid" value="<?= $userId ?>" />
        <input type="hidden" name="action" value="<?= $action ?>" />
        <button type="button"  class="btn btn-cancel" data-bs-dismiss="modal">&nbsp;<?php eT("Cancel"); ?></button>
        <button class="btn btn-primary">&nbsp;<?php eT("Save"); ?></button>
    <?php echo TbHtml::endForm() ?>
</div>
