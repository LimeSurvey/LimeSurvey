<?php
/**
 * @var $id int The FailedEmail ID
 * @var $permissions array
 */
?>
<div class="icon-btn-row">
    <button data-bs-toggle="tooltip" title="<?= gT("Resend email") ?>"
            class="btn btn-outline-secondary btn-sm <?= !$permissions['update'] ? "" : "failedemail-action-modal-open" ?>"
            data-href="<?= App()->createUrl('/failedEmail/modalcontent', ['id' => $id]) ?>"
            data-contentFile="resend_form"
            <?= !$permissions['update'] ? "disabled='disabled'" : "" ?>>
        <i class="ri-mail-line"></i>
    </button>
    <button data-bs-toggle="tooltip" title="<?= gT("Email content") ?>"
            class="btn btn-outline-secondary btn-sm failedemail-action-modal-open"
            data-href="<?= App()->createUrl('/failedEmail/modalcontent', ['id' => $id]) ?>"
            data-contentFile="email_content">
        <i class="ri-search-line"></i>
    </button>
    <button data-bs-toggle="tooltip" title="<?= gT("Error message") ?>"
            class="btn btn-outline-secondary btn-sm failedemail-action-modal-open"
            data-href="<?= App()->createUrl('/failedEmail/modalcontent', ['id' => $id]) ?>"
            data-contentFile="email_error">
        <i class="fa fa-exclamation"></i>
    </button>
    <button data-bs-toggle="tooltip" title="<?= gT("Delete") ?>"
            class="btn btn-outline-secondary btn-sm <?= !$permissions['delete'] ? "" : "failedemail-action-modal-open" ?>"
            data-href="<?= App()->createUrl('/failedEmail/modalcontent', ['id' => $id]) ?>"
            data-contentFile="delete_form"
            <?= !$permissions['delete'] ? "disabled='disabled'" : "" ?>>
        <i class="ri-delete-bin-fill text-danger"></i>
    </button>
</div>
