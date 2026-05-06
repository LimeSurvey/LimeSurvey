<?php
/** @var string $modalTitle */
/** @var string|null $modalTitleId Optional id for the title element (for aria-labelledby on the dialog) */
$modalTitleId = isset($modalTitleId) ? $modalTitleId : null;
?>

<div class="modal-header">
    <h2 class="modal-title h1"<?= !empty($modalTitleId) ? ' id="' . $modalTitleId . '"' : '' ?>>
        <?= $modalTitle ?>
    </h2>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= gT('Close modal') ?>"></button>
</div>
