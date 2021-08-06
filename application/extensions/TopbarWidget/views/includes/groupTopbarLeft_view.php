<?php
/**
 * Include the Survey Preview and Group Preview buttons
 */
$this->render('includes/previewSurveyAndGroupButtons_view', get_defined_vars());
?>

<!-- Edit button -->
<?php if($hasSurveyContentUpdatePermission): ?>
    <a class="btn btn-success pjax" href="<?php echo Yii::App()->createUrl("questionGroupsAdministration/edit/surveyid/{$surveyid}/gid/{$gid}/"); ?>" role="button">
        <span class="icon-edit"></span>
        <?php eT("Edit");?>
    </a>
<?php endif; ?>

<?php
    // Tools dropdown button
    $toolsDropdownItems = $this->render('includes/groupToolsDropdownItems', get_defined_vars(), true);
    if (!empty(trim($toolsDropdownItems))):
?>
<!-- Tools  -->
<div class="btn-group hidden-xs">

    <!-- Main button dropdown -->
    <button id="ls-question-group-tools-button" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="icon-tools" ></span>
        <?php eT('Tools'); ?>&nbsp;<span class="caret"></span>
    </button>

    <!-- dropdown -->
    <ul class="dropdown-menu">
        <?= $toolsDropdownItems ?>
    </ul>
</div>
<?php endif; ?>

<?php if($hasSurveyContentDeletePermission):?>
    <!-- Delete -->
    <?php if( $oSurvey->active != "Y" ):?>
        <?php if(is_null($condarray)):?>
            <!-- can delete group and question -->
            <button
                class="btn btn-danger"
                data-toggle="modal"
                data-target="#confirmation-modal"
                data-onclick='(function() { <?php echo convertGETtoPOST(Yii::app()->createUrl("questionGroupsAdministration/delete/", ["asJson" => true, "surveyid" => $surveyid, "gid"=>$gid])); ?> })'
                data-message="<?php eT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js"); ?>"
                >
                <span class="fa fa-trash"></span>
                <?php eT("Delete"); ?>
            </button>
        <?php else: ?>
            <!-- there is at least one question having a condition on its content -->
            <button type="button" class="btn btn-danger btntooltip" disabled data-toggle="tooltip" data-placement="bottom" title="<?php eT("Impossible to delete this group because there is at least one question having a condition on its content"); ?>" >
                <span class="fa fa-trash"></span>
                <?php eT("Delete"); ?>
            </a>
        <?php endif; ?>
    <?php else:?>
        <!-- Activated -->
        <button type="button" class="btn btn-danger btntooltip" disabled data-toggle="tooltip" data-placement="bottom" title="<?php eT("You can't delete this question group because the survey is currently active."); ?>" >
            <span class="fa fa-trash"></span>
            <?php eT("Delete"); ?>
        </button>
    <?php endif; ?>
<?php endif; ?>