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
    <div class="panel panel-primary" id="pannel-1">
        <div class="panel-heading">
            <h4 class="panel-title"><?php eT("Data selection"); ?></h4>
        </div>
        <div class="panel-body">
            <div class='form-group'>
                <input type="hidden" id="completionstateSimpleStat" data-grid-display-url="<?php echo App()->createUrl('/admin/statistics/sa/setIncompleteanswers/');?>"  />
                <label for='completionstate' class="col-sm-4 control-label"><?php eT("Include:"); ?> </label>
                <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                    'name' => 'completionstate',
                    'value'=> incompleteAnsFilterState(),
                    'selectOptions'=>array(
                        "all"=>gT("All responses",'unescaped'),
                        "complete"=>gT("Complete only",'unescaped'),
                        "incomplete"=>gT("Incomplete only",'unescaped'),
                    ),
                ));?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 content-right">
            <input type="hidden" id="showGraphOnPageLoad" />
            <div id='statisticsoutput' class='statisticsfilters'>
                <?php echo $output; ?>
            </div>
        </div>
    </div>
</div>
