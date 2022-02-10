
<?php /* Ported from previous versions: Pending to adapt to screen own JS for saving (and validations) 
<!-- Save and new group -->
<?php if(!empty($showSaveAndNewGroupButton)): ?>
    <a class="btn btn-default" id='save-and-new-button' role="button">
        <span class="fa fa-plus-square"></span>
        <?php eT("Save and new group"); ?>
    </a>
<?php endif; ?>

<!-- Save and add question -->
<?php if(!empty($showSaveAndNewQuestionButton)): ?>
    <a class="btn btn-default" id='save-and-new-question-button' role="button">
        <span class="fa fa-plus"></span>
        <?php eT("Save and add question"); ?>
    </a>
<?php endif; ?>
*/ ?>

<!-- Close -->
<?php if(!empty($showCloseButton)): ?>
    <a class="btn btn-default" href="#" role="button" onclick="LS.questionEditor.showOverview(); return false;">
        <span class="fa fa-close"></span>
        <?php eT("Close");?>
    </a>
<?php endif;?>

<!-- Save and close -->
<?php if(!empty($showSaveAndCloseButton)): ?>
    <button
        id="save-and-close-button-create-question"
        class="btn btn-default"
        type="button"
        onclick="return LS.questionEditor.checkIfSaveIsValid(event, 'overview');"
    >
        <i class="fa fa-check-square"></i>
        <?php eT("Save and close");?>
    </button>
<?php endif; ?>

<!-- Save -->
<?php if(!empty($showSaveButton)): ?>
    <button
        id="save-button-create-question"
        class="btn btn-success"
        type="button"
        <?php if ($oQuestion->qid !== 0): // Only enable Ajax save for edit question, not create question. ?>
            data-save-with-ajax="true"
        <?php endif; ?>
        onclick="return LS.questionEditor.checkIfSaveIsValid(event, 'editor');"
    >
        <i class="fa fa-check"></i>
        <?php eT("Save");?>
    </button>
<?php endif; ?>
