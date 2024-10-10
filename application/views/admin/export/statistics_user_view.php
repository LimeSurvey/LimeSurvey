<?php
    /**
    * Statistic simple view
    *
    */
?>

<!-- Javascript variables  -->
<?php $this->renderPartial('/admin/export/statistics_subviews/_statistics_view_scripts', array('sStatisticsLanguage'=>$sStatisticsLanguage, 'surveyid'=>$surveyid, 'showtextinline'=>$showtextinline)) ; ?>

<div id='statisticsview' class='side-body'>

    <div class="row">
        <div class="col-12">
            <div class="col-lg-3 text-start">
                <h4>
                    <span class="ri-bar-chart-fill"></span> &nbsp;&nbsp;&nbsp;
                    <?php eT("Statistics"); ?>
                </h4>
            </div>
            <div class="col-lg-9 text-end">
                <div class="mb-3">
                    <div >
                        <label for='completionstate' class="form-label"><?php eT("Include:"); ?> </label>
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
        <h3></h3>
    </div>


    <div class="row">
        <div class="col-12 content-right">
            <input type="hidden" id="showGraphOnPageLoad" />
            <div id='statisticsoutput' class='statisticsfilters'>
                <?php echo $output; ?>
            </div>
        </div>
        <div class="col-12 content-left">
            <button 
                type="button"
                id="statisticsExportImages" 
                class="btn btn-info" 
                style="margin: auto;">
                <?=gT('Export images')?>
            </button>
            <p><?php eT('Make sure all images on this screen are loaded before clicking on the button.');?></p>
        </div>
    </div>
</div>

<input type="hidden" id="completionstateSimpleStat"  />
