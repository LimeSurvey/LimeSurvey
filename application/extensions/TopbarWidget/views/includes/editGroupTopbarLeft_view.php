<?php
/**
 * Include the Survey Preview and Group Preview buttons
 */
$this->render('includes/previewSurveyAndGroupButtons_view', get_defined_vars());
?>

<!-- Check survey logic -->
<?php if($hasSurveyContentReadPermission): ?>
    <a class="btn btn-default pjax" href="<?php echo Yii::App()->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/"); ?>" role="button">
        <span class="icon-expressionmanagercheck"></span>
        <?php eT("Check logic"); ?>
    </a>
<?php endif; ?>

<?php if($hasSurveyContentExportPermission):?>
    <!-- Export -->
    <a class="btn btn-default " href="<?php echo Yii::App()->createUrl("admin/export/sa/group/surveyid/$surveyid/gid/$gid");?>" role="button">
        <span class="icon-export"></span>
        <?php eT("Export"); ?>
    </a>
<?php endif; ?>