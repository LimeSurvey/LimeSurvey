<?php /**
 * @var int $deletedCount how many emails have been deleted
 **/ ?>
<div class="modal-header">
    <div class="modal-title h4">
        <?= gT('Deleted email notifications') ?>
    </div>
</div>
<div class="modal-body">
    <?= $this->renderPartial('./partials/modal/delete_result_body', [
        'deletedCount' => $deletedCount
    ]) ?>
</div>
<div class="modal-footer modal-footer-buttons">
    <button id="exitForm" class="btn btn-outline-secondary"><?= gT('Close') ?></button>
</div>
