<!-- Delete -->
<?php if (!empty($showDeleteButton)) : ?>
    <?php if ($oSurvey->active != 'Y') : ?>
        <button class="btn btn-danger"
                data-toggle="modal"
                data-target="#confirmation-modal"
                data-btnclass="btn-danger"
                data-title="<?= gt('Delete this question') ?>"
                data-btntext="<?= gt('Delete') ?>"
                data-onclick='(function() { <?php echo convertGETtoPOST(Yii::app()->createUrl("questionAdministration/delete/", ["qid" => $qid, "redirectTo" => "groupoverview"])); ?>})'
                data-message="<?php eT("Deleting this question will also delete any answer options and subquestions it includes. Are you sure you want to continue?", "js"); ?>"
        >
            <span class="fa fa-trash"></span>
            <?php eT("Delete"); ?>
        </button>
    <?php else : ?>
        <button class="btn btn-danger btntooltip" disabled data-toggle="tooltip" data-placement="bottom" title="<?php eT("You can't delete a question if the survey is active."); ?>">
            <span class="fa fa-trash"></span>
            <?php eT("Delete"); ?>
        </button>
    <?php endif; ?>
<?php endif; ?>
