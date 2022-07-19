<?php
/**
 * @var $id int The FailedEmail ID
 * @var $permissions array
 */
?>
<div class="icon-btn-row">
    <button data-toggle="tooltip" title="<?= gT("Resend E-mail") ?>"
            class="btn btn-default btn-sm failedemail-action-modal-open"
            data-href="<?= App()->createUrl('/failedemail/modalcontent', ['id' => $id]) ?>"
            data-contentFile="resend_form"
            <?= !$permissions['update'] ? "disabled='disabled'" : "" ?>>
        <i class="fa fa-envelope-square"></i>
    </button>
    <button data-toggle="tooltip" title="<?= gT("E-mail content") ?>"
            class="btn btn-default btn-sm failedemail-action-modal-open"
            data-href="<?= App()->createUrl('/failedemail/modalcontent', ['id' => $id]) ?>"
            data-contentFile="email_content">
        <i class="fa fa-search"></i>
    </button>
    <button data-toggle="tooltip" title="<?= gT("Error message") ?>"
            class="btn btn-default btn-sm failedemail-action-modal-open"
            data-href="<?= App()->createUrl('/failedemail/modalcontent', ['id' => $id]) ?>"
            data-contentFile="email_error">
        <i class="fa fa-exclamation"></i>
    </button>
    <button data-toggle="tooltip" title="<?= gT("Delete") ?>"
            class="btn btn-default btn-sm failedemail-action-modal-open"
            data-href="<?= App()->createUrl('/failedemail/modalcontent', ['id' => $id]) ?>"
            data-contentFile="delete_form"
            <?= !$permissions['delete'] ? "disabled='disabled'" : "" ?>>
        <i class="fa fa-trash text-danger"></i>
    </button>
</div>
