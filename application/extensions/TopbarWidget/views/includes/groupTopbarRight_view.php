<!-- Edit button -->
<?php if($hasSurveyContentUpdatePermission): ?>
    <a class="btn btn-success pjax" href="<?php echo Yii::App()->createUrl("questionGroupsAdministration/edit/surveyid/{$surveyid}/gid/{$gid}/"); ?>" role="button">
        <span class="icon-edit"></span>
        <?php eT("Edit");?>
    </a>
<?php endif; ?>
