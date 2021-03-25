<!-- Save -->
<?php if(!empty($showSaveButton)): ?>
    <a id="save-button" class="btn btn-success" role="button">
        <i class="fa fa-floppy-o"></i>
        <?php eT("Save");?>
    </a>
<?php endif; ?>

<!-- Export -->
<?php if(!empty($showExportButton)): ?>
    <button class="btn btn-success" name="export-button" id="export-button" data-submit-form=1>
        <span class="fa fa-download-alt"></span>
        <?php eT("Export");?>
    </button>
<?php endif;?>

<!-- Import -->
<?php if(!empty($showImportButton)): ?>
    <button class="btn btn-success" name="import-button" id="import-button" data-submit-form=1>
        <span class="fa fa-upload"></span>
        <?php eT("Import");?>
    </button>
<?php endif;?>

<!-- Close -->
<?php if(!empty($showCloseButton)): ?>
    <a class="btn btn-danger" href="<?php echo $closeUrl; ?>" role="button">
        <span class="fa fa-close"></span>
        <?php eT("Close");?>
    </a>
<?php endif;?>