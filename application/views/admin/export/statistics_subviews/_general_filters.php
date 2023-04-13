<div id='statistics_general_filter'>
    <div class="col-12">
        <!-- Data Selection -->
        <?php $this->renderPartial('/admin/export/statistics_subviews/_mainoptions', array('error' => $error, 'surveyid' => $surveyid, 'selectshow' => $selectshow, 'selecthide' => $selecthide, 'selectinc' => $selectinc, 'survlangs' => $survlangs, 'sStatisticsLanguage' => $sStatisticsLanguage)); ?>
        <!-- Output options -->
        <?php $this->renderPartial('/admin/export/statistics_subviews/_outputoptions', array()); ?>
        <!-- Filter -->
        <?php $this->renderPartial('/admin/export/statistics_subviews/_filter', array('datestamp' => $datestamp, 'dateformatdetails' => $dateformatdetails)); ?>
    </div>
</div>
<p>
    <input type='hidden' name='summary[]' value='idG' />
    <input type='hidden' name='summary[]' value='idL' />
    <input class="d-none" type='submit' value='<?php eT("View statistics"); ?>' />
    <input class="d-none" type='button' value='<?php eT("Clear"); ?>' onclick="window.open('<?php echo Yii::app()->getController()->createUrl("admin/statistics/sa/index/surveyid/$surveyid"); ?>', '_top')" />
</p>
