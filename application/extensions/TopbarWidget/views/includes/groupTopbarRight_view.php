<!-- Edit button -->
<?php if($hasSurveyContentUpdatePermission): ?>
    <button class="btn btn-success pjax" href="<?php echo Yii::App()->createUrl("questionGroupsAdministration/edit/surveyid/{$surveyid}/gid/{$gid}/"); ?>" type="button" role="button">
        <span class="icon-edit"></span>
        <?php eT("Edit");?>
    </button>
<?php endif; ?>
