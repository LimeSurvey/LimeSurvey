<?php
/**
 * @var FailedEmailController $this
 * @var int $successfullEmailCount how many emails succeeded
 * @var int $failedEmailCount how many emails failed
 * @var int $surveyId
 * @var int $id how many emails failed
 **/ ?>
<div class="modal-header">
    <div class="modal-title h4">
        <?= gT('Email send result') ?>
    </div>
</div>
<div class="modal-body">
    <?= $this->renderPartial('./partials/modal/resend_result_body', [
        'successfullEmailCount' => $successfullEmailCount,
        'failedEmailCount'      => $failedEmailCount
    ]) ?>
</div>
<div class="modal-footer modal-footer-buttons">
    <button id="exitForm" class="btn btn-outline-secondary"><?= gT('Close') ?></button>
</div>
