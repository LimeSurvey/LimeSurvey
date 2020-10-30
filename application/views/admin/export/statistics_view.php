<?php
/**
 * Statistic main view
 * @var AdminController $this
 * @var Survey $oSurvey
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('statisticsIndex');

?>
<!-- Javascript variables  -->
<?php $this->renderPartial('/admin/export/statistics_subviews/_statistics_view_scripts', array('sStatisticsLanguage'=>$sStatisticsLanguage, 'surveyid'=>$surveyid, 'showtextinline'=>$showtextinline)) ; ?>

<div id='statisticsview' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3>
        <span class="fa fa-bar-chart"></span> &nbsp;&nbsp;&nbsp;
        <?php eT("Statistics"); ?>
    </h3>

    <?php echo CHtml::form(array("admin/statistics/sa/index/surveyid/{$surveyid}/"), 'post', array('name'=>'formbuilder', 'class'=>'', 'id'=>'generate-statistics'));?>
        <!-- General filters -->
        <div class="row">
            <div class="col-sm-12 content-right">

                <!-- Header -->
                <?php $this->renderPartial('/admin/export/statistics_subviews/_header', array()) ; ?>

                    <!-- AUTOSCROLLING DIV CONTAINING GENERAL FILTERS -->
                    <div id='statisticsgeneralfilters' class='statisticsfilters' <?php if ($filterchoice_state!='' || !empty($summary)) { echo " style='display:none' "; } ?>>
                        <div id='statistics_general_filter'>

                            <div class="col-sm-6">
                                <!-- Data Selection -->
                                <?php $this->renderPartial('/admin/export/statistics_subviews/_dataselection', array('surveyid'=>$surveyid, 'selectshow'=>$selectshow, 'selecthide'=>$selecthide, 'selectinc'=>$selectinc, 'survlangs'=>$survlangs, 'sStatisticsLanguage'=>$sStatisticsLanguage)) ; ?>
                                <!-- Response ID -->
                                <?php $this->renderPartial('/admin/export/statistics_subviews/_responseid', array()) ; ?>
                                <!-- Output format -->
                                <?php $this->renderPartial('/admin/export/statistics_subviews/_outputformat', array()) ; ?>
                            </div>

                            <div class="col-sm-6">
                                <!-- Output options -->
                                <?php $this->renderPartial('/admin/export/statistics_subviews/_outputoptions', array('error'=>$error, 'showtextinline'=>$showtextinline, 'usegraph'=>$usegraph, 'showtextinline'=>$showtextinline)) ; ?>
                                <!-- Submission date -->
                                <?php $this->renderPartial('/admin/export/statistics_subviews/_submissiondate', array('datestamp'=>$datestamp, 'dateformatdetails' => $dateformatdetails)) ; ?>
                            </div>
                        </div>

                        <p>
                            <input type='hidden' name='summary[]' value='idG' />
                            <input type='hidden' name='summary[]' value='idL' />
                            <input class="hidden" type='submit' value='<?php eT("View statistics"); ?>' />
                            <input class="hidden" type='button' value='<?php eT("Clear"); ?>' onclick="window.open('<?php echo Yii::app()->getController()->createUrl("admin/statistics/sa/index/surveyid/$surveyid"); ?>', '_top')" />
                        </p>
                    </div>
                </div>
            </div>

            <!-- Response filter -->
            <div class="row">
                <div class="col-lg-12 content-right">
                    <div style='clear: both'></div>

                    <!-- Response filter header -->
                    <?php $this->renderPartial('/admin/export/statistics_subviews/_response_filter_header', array()) ; ?>

                    <!-- AUTOSCROLLING DIV CONTAINING QUESTION FILTERS -->
                    <div id='statisticsresponsefilters' class='statisticsfilters scrollheight_400'>
                        <input type='hidden' id='filterchoice_state' name='filterchoice_state' value='<?php echo $filterchoice_state; ?>' />

                        <?php
                            $dshresults = (isset($dshresults))?$dshresults:'';
                            $dshresults2 = (isset($dshresults2))?$dshresults2:'';
                        ?>
                        <!-- Filter choice -->
                        <?php $this->renderPartial(
                                                    '/admin/export/statistics_subviews/_response_filter_choice',
                                                    array(
                                                        'filterchoice_state'=>$filterchoice_state,
                                                        'dateformatdetails'=>$dateformatdetails,
                                                        'filters'=>$filters,
                                                        'aGroups'=>$aGroups,
                                                        'surveyid'=>$surveyid,
                                                        'result'=>$result,
                                                        'fresults'=>$fresults,
                                                        'summary'=>$summary,
                                                        'oStatisticsHelper'=>$oStatisticsHelper,
                                                        'language'=>$language,
                                                        'dshresults'=>$dshresults,
                                                        'dshresults2'=>$dshresults2,
                                                    )) ;
                        ?>

                    </div>

                    <p id='vertical_slide2'>
                        <input type='submit' class="hidden" value='<?php eT("View statistics"); ?>' />
                        <input type='button'class="hidden"  value='<?php eT("Clear"); ?>' onclick="window.open('<?php echo Yii::app()->getController()->createUrl("admin/statistics/sa/index/surveyid/$surveyid"); ?>', '_top')" />
                        <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
                        <input type='hidden' name='display' value='stats' />
                    </p>
            </div><!-- END OF AUTOSCROLLING DIV CONTAINING QUESTION FILTERS -->
        </div>
    </form>

    <div class="row">
        <div class="col-lg-12 content-right">

            <?php
                // TODO : show the css loader
                flush(); //Let's give the user something to look at while they wait for the pretty pictures
            ?>

            <!-- Statistics header -->
            <?php $this->renderPartial('/admin/export/statistics_subviews/_statistics_header', array()) ; ?>

            <div id='statisticsoutput' class='statisticsfilters'>
                <?php if ($output==""):?>
                    <div class="alert alert-info" role="alert" id="view-stats-alert-info">
                        <?php eT('Please select filters and click on the "View statistics" button to generate the statistics.');?>
                    </div>
                <?php else:?>
                    <?php echo $output; ?>
                <?php endif;?>
                    <div id="statsContainerLoading" >
                        <p><?php eT('Please wait, loading data...');?></p>
                        <div class="preloader loading">
                            <span class="slice"></span>
                            <span class="slice"></span>
                            <span class="slice"></span>
                            <span class="slice"></span>
                            <span class="slice"></span>
                            <span class="slice"></span>
                        </div>
                    </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 content-left">
            <button id="statisticsExportImages" class="btn btn-info" style="margin: auto;"><?=gT('Export images')?></button>
            <p><?php eT('Make sure all images on this screen are loaded before clicking on the button.');?></p>
        </div>
    </div>
</div>
<?php
App()->getClientScript()->registerScript('StatisticsViewBSSwitcher', "
LS.renderBootstrapSwitch();
", LSYii_ClientScript::POS_POSTSCRIPT);
?>