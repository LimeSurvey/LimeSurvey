<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT('User details')]
);
?>

<div class="modal-body">
    <table class="table table-striped">
        <tr>
            <td><?=gT('User groups:')?></td>
            <td><?=CHTml::encode(join(', ',$usergroups))?></td>
        </tr>
        <?php if ($oUser->parentUser): ?>
            <tr>
                <td><?=gT('Owner:')?></td>
                <td><?=CHTml::encode($oUser->parentUser['displayName'])?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <td><?=gT('Surveys owned:')?></td>
            <td><?=$oUser->surveysCreated?></td>
        </tr>
        <tr>
            <td><?=gT('Last login:')?></td>
            <td><?=$oUser->lastloginFormatted?></td>
        </tr>
    </table>
</div>

<div class="modal-footer modal-footer-buttons">
    <button id="exitForm" class="btn btn-cancel" data-bs-dismiss="modal">
        <?=gT('Close')?>
    </button>
</div>
