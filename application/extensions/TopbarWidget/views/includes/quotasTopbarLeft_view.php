<!-- Quick CSV report -->
<a class="btn btn-outline-secondary" role="button" onClick="window.open('<?php echo Yii::App()->createUrl("admin/quotas/sa/index/surveyid/$surveyid/quickreport/y") ?>', '_top')">
  <?php eT("Quick CSV report"); ?>
</a>

<!-- Add new quota -->
<a class="btn btn-outline-secondary quota_new" role="button" href="<?php echo Yii::App()->createUrl("admin/quotas/sa/newquota/surveyid/$surveyid") ?>">
    <?php eT("Add new quota"); ?>
</a>
