<?php if (isset($expertstats) && $expertstats =  true):?>
    <button 
        class="btn btn-info" 
        href="<?php echo Yii::app()->createUrl('/admin/statistics/sa/index/surveyid/'.$surveyid); ?>" 
        id="expert-mode"
        type="button">
        <span class="fa fa-bar-chart"></span>
        <?php eT("Expert mode"); ?>
    </button>
<?php else: ?>
    <button 
        class="btn btn-info" 
        href="<?php echo Yii::app()->createUrl('/admin/statistics/sa/simpleStatistics/surveyid/'.$surveyid); ?>" 
        id="simple-mode"
        type="button">
        <span class="fa fa-bar-chart"></span>
        <?php eT("Simple mode"); ?>
    </button>
    
    <button 
        class="btn btn-success" 
        type="button" 
        name="view-button" 
        id="view-button" 
        data-submit-form=1>
        <span class="fa"></span>
        <?php eT("View statistics"); ?>
    </button>

    <button 
        class="btn btn-outline-secondary" 
        href="<?php echo Yii::app()->createUrl("admin/statistics/sa/index/",array('surveyid'=>$surveyid)) ?>" 
        id="clear-button"
        type="button">
        <span class="fa fa-refresh text-success"></span>
        <?php eT("Clear"); ?>
    </button>
<?php endif; ?>

<button 
    class="btn btn-danger"
    href="<?php echo Yii::app()->createUrl("surveyAdministration/view", ["surveyid" => $surveyid]); ?>" 
    type="button">
    <span class="fa fa-close"></span>
    <?php eT("Close");?>
</button>
