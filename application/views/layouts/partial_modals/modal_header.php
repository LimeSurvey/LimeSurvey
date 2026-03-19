<?php
/**
 * Modal header fragment (title + close).
 *
 * For screen readers: the parent .modal should have role="dialog", aria-modal="true",
 * and aria-labelledby listing first the title id, then the dialog role hint id (in that order),
 * e.g. aria-labelledby="myModal-title myModal-title-dialogsr"
 * so the accessible name is announced as the heading text, then "Dialog".
 *
 * @var string $modalTitle Dialog title text (plain; will be HTML-encoded).
 * @var string|null $modalTitleId Id for the title element; when set, a hidden "Dialog" span is output with id "{$modalTitleId}-dialogsr".
 */
/** @var string $modalTitle */
/** @var string|null $modalTitleId */
$modalTitleId = isset($modalTitleId) ? $modalTitleId : null;
$modalDialogSrId = !empty($modalTitleId) ? $modalTitleId . '-dialogsr' : null;
?>

<div class="modal-header">
    <h2 class="modal-title h5"<?= !empty($modalTitleId) ? ' id="' . CHtml::encode($modalTitleId) . '"' : '' ?>><?php echo CHtml::encode($modalTitle); ?></h2>
    <?php if (!empty($modalTitleId) && !empty($modalDialogSrId)) : ?>
        <span class="visually-hidden" id="<?= CHtml::encode($modalDialogSrId) ?>"><?= gT('Dialog') ?></span>
    <?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= gT('Close') ?>"></button>
</div>
