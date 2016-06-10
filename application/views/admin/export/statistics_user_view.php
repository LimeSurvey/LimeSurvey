<?php
    /**
    * Statistic main view
    *
    */
?>

<!-- Javascript variables  -->
<?php $this->renderPartial('/admin/export/statistics_subviews/_statistics_view_scripts', array('sStatisticsLanguage'=>$sStatisticsLanguage, 'surveyid'=>$surveyid, 'showtextinline'=>$showtextinline)) ; ?>

<div id='statisticsview' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3>
        <span class="glyphicon glyphicon-stats"></span> &nbsp;&nbsp;&nbsp;
        <?php eT("Statistics"); ?>
    </h3>

    <div class="row">
        <div class="col-lg-12 content-right">
            <input type="hidden" id="showGraphOnPageLoad" />
            <div id='statisticsoutput' class='statisticsfilters'>
                <?php echo $output; ?>
            </div>
        </div>
    </div>
</div>
