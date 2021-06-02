<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT('Delete user')]
);
?>

<div class="modal-body">
    <p class='modal-body-text'><?php eT('Do you want to delete this user?'); ?></p>
</div>
<div class="modal-footer modal-footer-yes-no">
    <?=TbHtml::formTb(null, App()->createUrl('userManagement/deleteUser'), 'post', ["id"=>"UserManagement--modalform"])?>
        <input type="hidden" name="userid" value="<?= $userId ?>" />
        <input type="hidden" name="user" value="<?= $sUserName ?>" />
        <button class="btn btn-cancel" data-dismiss="modal">&nbsp;<?php eT("Cancel"); ?></button>
        <button class="btn btn-danger btn-ok" id="submitForm">&nbsp;<?php eT("Delete"); ?></button>
    </form>
</div>

