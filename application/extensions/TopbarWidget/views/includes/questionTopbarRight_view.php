<!-- Save -->
<?php if(!empty($showSaveButton)): ?>
    <a id="save-button" class="btn btn-success" role="button">
        <i class="fa fa-floppy-o"></i>
        <?php eT("Save");?>
    </a>
<?php endif; ?>

<!-- Save and new group -->
<?php if(!empty($showSaveAndNewGroupButton)): ?>
    <a class="btn btn-default" id='save-and-new-button' role="button">
        <span class="fa fa-plus-square"></span>
        <?php eT("Save & add new group"); ?>
    </a>
<?php endif; ?>

<!-- Save and add question -->
<?php if(!empty($showSaveAndNewQuestionButton)): ?>
    <a class="btn btn-default" id='save-and-new-question-button' role="button">
        <span class="fa fa-plus"></span>
        <?php eT("Save & add new question"); ?>
    </a>
<?php endif; ?>

<!-- Save and close -->
<?php if(!empty($showSaveAndCloseButton)): ?>
    <a id="save-and-close-button" class="btn btn-default" role="button">
        <i class="fa fa-check-square"></i>
        <?php eT("Save and close");?>
    </a>
<?php endif; ?>

<!-- Close -->
<?php if(!empty($showCloseButton)): ?>
    <a class="btn btn-danger" href="<?php echo $closeUrl; ?>" role="button">
        <span class="fa fa-close"></span>
        <?php eT("Close");?>
    </a>
<?php endif;?>