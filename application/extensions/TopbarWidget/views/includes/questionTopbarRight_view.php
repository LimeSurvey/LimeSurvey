<!-- Edit button -->
<?php if(!empty($showEditButton) && $hasSurveyContentUpdatePermission): ?>
    <a id="questionEditorButton" class="btn btn-primary pjax" href="#" role="button" onclick="LS.questionEditor.showEditor(); return false;">
        <span class="icon-edit"></span>
        <?php eT("Edit");?>
    </a>
<?php endif; ?>

<!-- Delete -->
<?php if(!empty($showDeleteButton) && $hasSurveyContentDeletePermission):?>
    <?php if($oSurvey->active!='Y'): ?>
        <button class="btn btn-danger"
                data-toggle="modal"
                data-target="#confirmation-modal"
                data-onclick='(function() { <?php echo convertGETtoPOST(Yii::app()->createUrl("questionAdministration/delete/", ["qid" => $qid, "redirectTo" => "groupoverview"])); ?>})'
                data-message="<?php eT("Deleting this question will also delete any answer options and subquestions it includes. Are you sure you want to continue?","js"); ?>"
        >
            <span class="fa fa-trash text-danger"></span>
            <?php eT("Delete"); ?>
        </button>
    <?php else: ?>
        <button class="btn btn-danger btntooltip" disabled data-toggle="tooltip" data-placement="bottom" title="<?php eT("You can't delete a question if the survey is active."); ?>">
            <span class="fa fa-trash text-danger"></span>
            <?php eT("Delete"); ?>
        </button>
    <?php endif; ?>
<?php endif; ?>

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