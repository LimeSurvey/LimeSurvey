<!-- Edit button -->
<?php if($hasSurveyContentUpdatePermission): ?>
    <a class="btn btn-success pjax" href="<?php echo Yii::App()->createUrl("questionGroupsAdministration/edit/surveyid/{$surveyid}/gid/{$gid}/"); ?>" role="button">
        <span class="ri-pencil-fill"></span>
        <?php eT("Edit");?>
    </a>
<?php endif; ?>
