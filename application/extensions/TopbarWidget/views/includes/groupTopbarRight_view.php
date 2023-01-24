<!-- Edit button -->
<?php if($hasSurveyContentUpdatePermission): ?>
    <a class="btn btn-primary pjax" href="<?php echo Yii::App()->createUrl("questionGroupsAdministration/edit/surveyid/{$surveyid}/gid/{$gid}/"); ?>" role="button">
        <?php eT("Edit");?>
    </a>
<?php endif; ?>
