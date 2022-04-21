<!-- Quick CSV report -->
<button class="btn btn-outline-secondary" type="button" role="button" onClick="window.open('<?php echo Yii::App()->createUrl("admin/quotas/sa/index/surveyid/$surveyid/quickreport/y") ?>', '_top')">
  <?php eT("Quick CSV report"); ?>
</button>

<!-- Add new quota -->
<button class="btn btn-outline-secondary quota_new" type="submit" role="button" href="<?php echo Yii::App()->createUrl("admin/quotas/sa/newquota/surveyid/$surveyid") ?>">
    <?php eT("Add new quota"); ?>
</button>