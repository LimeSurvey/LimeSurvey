<?php
/**
 * @var $this FailedEmailController
 * @var $failedEmail FailedEmail
 **/ ?>
<?= App()->getController()->renderPartial('/layouts/partial_modals/modal_header', ['modalTitle' => gT('Error message')]) ?>
<div id="failedemail-action-modal--emailerror" class="modal-body">
    <?= $failedEmail->error_message ?>
</div>
<div class="modal-footer modal-footer-buttons">
    <button id="exitForm" class="btn btn-cancel  "><?= gT('Close') ?></button>
</div>