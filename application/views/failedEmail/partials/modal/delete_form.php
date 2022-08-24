<?php
/**
 * @var $this FailedEmailController
 * @var $surveyId int
 * @var $id int
 */
?>

<?= App()->getController()->renderPartial('/layouts/partial_modals/modal_header', ['modalTitle' => gT('Delete failed email notifications')]) ?>
<?= CHtml::form(['/failedEmail/delete/', 'surveyid' => $surveyId, 'item' => $id], 'post', ['id' => 'failedemail-action-modal--form']) ?>
<div class="modal-body">
    <p><?= gT('Are you sure you want to delete the selected notifications?') ?></p>
</div>
<div class="modal-footer modal-footer-buttons">
    <button id="exitForm" class="btn btn-cancel  "><?= gT('Cancel') ?></button>
    <button class="btn btn-danger" id="submitForm"><?= gT('Delete') ?></button>
</div>
<?= CHtml::endForm() ?>

