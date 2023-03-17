<?php
/**
 * @var $this FailedEmailController
 * @var $surveyId int
 * @var $id int
 */
?>

<?= App()->getController()->renderPartial('/layouts/partial_modals/modal_header', ['modalTitle' => gT('Resend email')]) ?>
<?= CHtml::form(['/failedEmail/resend/', 'surveyid' => $surveyId, 'item' => $id], 'post', ['id' => 'failedemail-action-modal--form']) ?>
<div class="modal-body">
    <?= $this->renderPartial('./partials/modal/resend_body', [], false) ?>
</div>
<div class="modal-footer modal-footer-buttons">
    <button id="exitForm" class="btn btn-cancel  "><?= gT('Cancel') ?></button>
    <button class="btn btn-primary" id="submitForm"><?= gT('Resend') ?></button>
</div>
<?= CHtml::endForm() ?>

