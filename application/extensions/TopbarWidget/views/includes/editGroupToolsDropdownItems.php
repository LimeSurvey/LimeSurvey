<?php if($hasSurveyContentReadPermission): ?>
    <!-- Check survey logic -->
    <li>
        <a class="pjax" href="<?php echo Yii::App()->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/"); ?>">
            <span class="icon-expressionmanagercheck"></span>
            <?php eT("Check logic"); ?>
        </a>
    </li>
<?php endif; ?>

<?php if($hasSurveyContentExportPermission):?>
    <!-- Export -->
    <li>
        <a href="<?php echo Yii::App()->createUrl("admin/export/sa/group/surveyid/$surveyid/gid/$gid");?>">
            <span class="icon-export"></span>
            <?php eT("Export"); ?>
        </a>
    </li>
<?php endif; ?>
