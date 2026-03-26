<?php
/** @var string $modalTitle */
/** @var string|null $modalTitleId Optional id for the title element (for aria-labelledby on the dialog) */
$modalTitleId = isset($modalTitleId) ? $modalTitleId : "model_id8765_title";
?>

<div class="modal-header" role="dialog" aria-labelledby="<?= !empty($modalTitleId) ? CHtml::encode($modalTitleId) : '' ?>">
    <h2 id="<?= !empty($modalTitleId) ? CHtml::encode($modalTitleId) : '' ?>" class="h1 modal-title"><?php echo $modalTitle; ?></h2>
    <button type="button" class="btn-close" data-bs-dismiss="modal" role="button" aria-label="<?= gT('Close') ?>"></button>
</div>