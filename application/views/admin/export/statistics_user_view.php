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
        <div class="col-md-12">
            <div class="col-md-3 text-left">
                <h4>
                    <span class="fa fa-bar-chart"></span> &nbsp;&nbsp;&nbsp;
                    <?php eT("Statistics"); ?>
                </h4>
            </div>
            <div class="col-md-9 text-right">
                <div class="col-md-9 text-right">
                    <a href='<?php echo Yii::app()->getController()->createUrl("admin/statistics/sa/index/surveyid/".$surveyid); ?>' class="btn btn-primary">
                        <span class="fa fa-bar-chart"></span>
                        <?php eT("Expert mode"); ?>

                    </a>
                </div>
                <div class="form-group">
                    <div >
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
        <h3></h3>
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
