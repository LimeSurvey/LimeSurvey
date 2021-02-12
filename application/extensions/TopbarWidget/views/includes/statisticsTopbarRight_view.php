<?php if (isset($expertstats) && $expertstats =  true):?>
    <a class="btn btn-info" href="<?php echo Yii::app()->createUrl('/admin/statistics/sa/index/surveyid/'.$surveyid); ?>" id="expert-mode">
        <span class="fa fa-bar-chart"></span>
        <?php eT("Expert mode"); ?>
    </a>
<?php else: ?>
    <a class="btn btn-info" href="<?php echo Yii::app()->createUrl('/admin/statistics/sa/simpleStatistics/surveyid/'.$surveyid); ?>" id="simple-mode">
        <span class="fa fa-bar-chart"></span>
        <?php eT("Simple mode"); ?>
    </a>
    <button class="btn btn-success" name="view-button" id="view-button" data-submit-form=1>
        <span class="fa"></span>
        <?php eT("View statistics"); ?>
    </button>

    <a class="btn btn-default" href="<?php echo Yii::app()->createUrl("admin/statistics/sa/index/",array('surveyid'=>$surveyid)) ?>" id="clear-button">
        <span class="fa fa-refresh text-success"></span>
        <?php eT("Clear"); ?>
    </a>
<?php endif; ?>

<a class="btn btn-danger" href="<?php echo Yii::app()->createUrl("surveyAdministration/view", ["surveyid" => $surveyid]); ?>" role="button">
    <span class="fa fa-close"></span>
    <?php eT("Close");?>
</a>
