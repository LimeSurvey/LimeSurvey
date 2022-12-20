<?php
/** @var int $userGroupId */
/** @var UserGroup $userGroup */
/** @var int $currentUserId */

$canDelete = Permission::model()->hasGlobalPermission('usergroups', 'update')
    && $userGroup && $userGroup->owner_id == Yii::app()->session['loginID'];
?>
<div class='icon-btn-row'>
    <?php if ($userGroup && ($canDelete || Permission::model()->hasGlobalPermission('superadmin')) && $currentUserId != '1') : ?>
        <?= CHtml::form(["userGroup/DeleteUserFromGroup/ugid/{$userGroupId}/"], 'post') ?>
        <button
            class="btn btn-outline-secondary btn-sm"
            data-bs-toggle="tooltip"
            data-bs-placement="bottom"
            title="<?php eT('Delete') ?>"
            type="submit"
            onclick='return confirm("<?php eT("Are you sure you want to delete this entry?", "js"); ?>")'>
        <span class="fa fa-trash text-danger"></span>
        </button>
        <input name="uid" type="hidden" value="<?= $currentUserId  ?>"/>
        <?= CHtml::endForm() ?>
    <?php endif; ?>
</div>
