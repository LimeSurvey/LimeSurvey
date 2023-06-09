<?php 
/**
 * @var $this FailedEmailController
 * @var $failedEmail FailedEmail
 **/ ?>
<?= App()->getController()->renderPartial('/layouts/partial_modals/modal_header', ['modalTitle' => gT('Email content')]) ?>
<div id="failedemail-action-modal--emailcontent" class="modal-body">
    <?= $failedEmail->getRawMailBody() ?>
</div>
<div class="modal-footer modal-footer-buttons">
    <button id="exitForm" class="btn btn-cancel  "><?= gT('Close') ?></button>
</div>