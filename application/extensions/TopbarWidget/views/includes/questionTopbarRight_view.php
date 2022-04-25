<?php
/**
 * @var Survey $oSurvey
 * @var string $qid
 */

?>

<!-- Edit button -->
<?php if($hasSurveyContentUpdatePermission): ?>
    <button id="questionEditorButton" class="btn btn-success pjax" href="#" role="button" type="button" onclick="LS.questionEditor.showEditor(); return false;">
        <span class="icon-edit"></span>
        <?php eT("Edit");?>
    </button>
<?php endif; ?>
