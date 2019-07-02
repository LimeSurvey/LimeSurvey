<?php
    /**
    * Statistic simple view
    *
    */
?>

<!-- Javascript variables  -->
<?php $this->renderPartial('/admin/export/statistics_subviews/_statistics_view_scripts', array('sStatisticsLanguage'=>$sStatisticsLanguage, 'surveyid'=>$surveyid, 'showtextinline'=>$showtextinline)) ; ?>

<div id='statisticsview' class='side-body <?php echo getSideBodyClass(false); ?>'>

    <div class="row">
        <div class="col-sm-12">
            <h3>
                <span class="fa fa-bar-chart"></span> &nbsp;&nbsp;&nbsp;
                <?php eT("Statistics"); ?>
            </h3>
        </div>


        <div class="text-right">
            <div class="form-group">
                <div style="display:inline-block;position:relative;top:-65px;">
                    <label for='completionstate' class="control-label"><?php eT("Include:"); ?> </label>
                    <?php
                    echo CHtml::dropDownList(
                        'completionstate',
                        incompleteAnsFilterState(),
                        array(
                            "all"=>gT("All responses",'unescaped'),
                            "complete"=>gT("Complete only",'unescaped'),
                            "incomplete"=>gT("Incomplete only",'unescaped'),
                        ),
                        array(
                            'class'=>'form-control',
                            'style'=>'display: inline;width: auto;',
                            'data-url'=>App()->createUrl('/admin/statistics/sa/setIncompleteanswers/')
                        ))
                    ;
                    ?>
                </div>
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
        <div class="col-lg-12 content-left">
            <button id="statisticsExportImages" class="btn btn-info" style="margin: auto;"><?=gT('Export images')?></button>
            <p><?php eT('Make sure all images on this screen are loaded before clicking on the button.');?></p>
        </div>
    </div>
</div>

<input type="hidden" id="completionstateSimpleStat"  />
