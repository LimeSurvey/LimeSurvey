<?php
/**
 * @var string $closeUrl
 * @var string $returnUrl
 */

?>

<!-- White Close button -->
<?php if (!empty($showWhiteCloseButton)) : ?>
    <a class="btn btn-default" href="<?php echo $closeUrl ?>" role="button">
        <span class="fa fa-close"></span>
        <?php eT("Close"); ?>
    </a>
<?php endif; ?>

<!-- Save and Close -->
<?php if (!empty($showSaveAndCloseButton)): ?>
    <a class="btn btn-default"
       href="<?php echo $closeUrl; ?>"
       role="button"
       id="save-and-close-button"
       onclick="$(this).addClass('disabled').attr('onclick', 'return false;');">
        <span class="fa fa-saved"></span>
        <?php eT("Save and close"); ?>
    </a>
<?php endif; ?>

<!-- Return -->
<?php if (!empty($showBackButton)): ?>
    <a class="btn btn-default" href="<?php echo $returnUrl; ?>" role="button">
        <span class="fa fa-backward"></span>
        &nbsp;&nbsp;
        <?php eT('Back') ?>
    </a>
<?php endif; ?>

<!-- Green Save and Close -->
<?php if (!empty($showGreenSaveAndCloseButton)): ?>
    <a class="btn btn-success"
       href="<?php echo $closeUrl; ?>"
       role="button"
       id="save-and-close-button"
       onclick="$(this).addClass('disabled').attr('onclick', 'return false;');">
        <span class="fa fa-saved"></span>
        <?php eT("Save and close"); ?>
    </a>
<?php endif; ?>

<!-- Save -->
<?php if (!empty($showSaveButton)): ?>
    <a id="save-button" class="btn btn-success pull-right" role="button">
        <i class="fa fa-check"></i>
        <?php eT("Save"); ?>
    </a>
<?php endif; ?>

<!-- Export -->
<?php if (!empty($showExportButton)): ?>
    <button class="btn btn-success" name="export-button" id="export-button" data-submit-form=1>
        <span class="fa fa-download-alt"></span>
        <?php eT("Export"); ?>
    </button>
<?php endif; ?>

<!-- Import -->
<?php if (!empty($showImportButton)): ?>
    <button class="btn btn-success" name="import-button" id="import-button" data-submit-form=1>
        <span class="fa fa-upload"></span>
        <?php eT("Import"); ?>
    </button>
<?php endif; ?>

<!-- Close -->
<?php if (!empty($showCloseButton)): ?>
    <a class="btn btn-danger" href="<?php echo $closeUrl; ?>" role="button">
        <span class="fa fa-close"></span>
        <?php eT("Close"); ?>
    </a>
<?php endif; ?>
