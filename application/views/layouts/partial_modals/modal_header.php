<?php
/** @var string $modalTitle */
/** @var string|null $modalTitleId Optional id for the title element (for aria-labelledby on the dialog) */
$modalTitleId = isset($modalTitleId) ? $modalTitleId : null;
?>

<div class="modal-header">
    <h2 class="h1 modal-title"<?= !empty($modalTitleId) ? ' id="' . CHtml::encode($modalTitleId) . '"' : '' ?>><?php echo $modalTitle; ?></h2>
    <button type="button" class="btn-close" data-bs-dismiss="modal"
            aria-label="<?= gT('Close module'); ?>"
    ></button>
</div>
