<!-- Save -->
<?php if(!empty($showSaveButton)): ?>
    <a id="save-button" class="btn btn-success pull-right" style="margin-left: 5px;" role="button">
        <i class="fa fa-check"></i>
        <?php eT("Save");?>
    </a>
<?php endif; ?>

<!-- Save and Close -->
<?php if(!empty($showsSaveAndCloseButton)): ?>
    <a class="btn btn-default" 
       href="<?php echo $closeUrl; ?>"
       role="button" 
       id="save-and-close-form-button"
       onclick="$(this).addClass('disabled').attr('onclick', 'return false;');"
       style="margin-top: 10px;">
            <span class="fa fa-saved"></span>
            <?php eT("Save and close");?>
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