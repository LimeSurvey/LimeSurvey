<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <div class="h3 modal-title"><?php eT("Confirm"); ?></div>
</div>
<div class="modal-body">
    <p class='modal-body-text'><?php eT('Do you want to delete this user?'); ?></p>
</div>
<div class="modal-footer modal-footer-yes-no">
    <?=TbHtml::formTb(null, App()->createUrl('userManagement/deleteUser'), 'post', ["id"=>"UserManagement--modalform"])?>
        <input type="hidden" name="userid" value="<?= $userId ?>" />
        <input type="hidden" name="user" value="<?= $sUserName ?>" />
        <button class="btn btn-primary btn-ok" id="submitForm"><span class='fa fa-check'></span>&nbsp;<?php eT("Yes"); ?></button>
        <button class="btn btn-danger" data-dismiss="modal"><span class='fa fa-ban'></span>&nbsp;<?php eT("No"); ?></button>
    </form>
</div>
<div class="modal-footer-close modal-footer" style="display: none;">
    <button type="button" class="btn btn-danger" data-dismiss="modal">
        <?php eT("Close"); ?>
    </button>
</div>
