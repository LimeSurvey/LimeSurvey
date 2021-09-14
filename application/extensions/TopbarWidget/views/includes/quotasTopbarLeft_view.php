<!-- Quick CSV report -->
<a class="btn btn-default" role="button" onClick="window.open('<?php echo Yii::App()->createUrl("admin/quotas/sa/index/surveyid/$surveyid/quickreport/y") ?>', '_top')">
  <?php eT("Quick CSV report"); ?>
</a>

<!-- Add new quota -->
<a class="btn btn-default quota_new" type="submit" role="button" href="<?php echo Yii::App()->createUrl("admin/quotas/sa/newquota/surveyid/$surveyid") ?>">
    <?php eT("Add new quota"); ?>
</a>