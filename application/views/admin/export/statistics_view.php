<?php
    /**
    * Show the statistics filters
    */
?>

<!-- Javascript variables  -->
<?php $this->renderPartial('/admin/export/statistics_subviews/_statistics_view_scripts', array('sStatisticsLanguage'=>$sStatisticsLanguage, 'surveyid'=>$surveyid, 'showtextinline'=>$showtextinline)) ; ?>

<div class="side-body">
    <h3>
        <span class="glyphicon glyphicon-stats"></span> &nbsp;&nbsp;&nbsp;
        <?php eT("Statistics"); ?>
    </h3>

    <div class="row">
            <div class="col-lg-12 content-right">
                <?php echo CHtml::form(array("admin/statistics/sa/index/surveyid/{$surveyid}/"), 'post', array('name'=>'formbuilder','#'=>'start', 'class'=>'form-horizontal'));?>

                    <!-- Header -->
                    <?php $this->renderPartial('/admin/export/statistics_subviews/_header', array()) ; ?>

                    <!-- AUTOSCROLLING DIV CONTAINING GENERAL FILTERS -->
                    <div id='statisticsgeneralfilters' class='statisticsfilters jumbotron message-box box col-lg-12' <?php if ($filterchoice_state!='' || !empty($summary)) { echo " style='display:none' "; } ?>>

                        <div id='statistics_general_filter'>

                            <!-- Data Selection -->
                            <?php $this->renderPartial('/admin/export/statistics_subviews/_dataselection', array('selectshow'=>$selectshow, 'selecthide'=>$selecthide, 'selectinc'=>$selectinc, 'survlangs'=>$survlangs, 'sStatisticsLanguage'=>$sStatisticsLanguage)) ; ?>

                            <!-- Response ID -->
                            <?php $this->renderPartial('/admin/export/statistics_subviews/_responseid', array()) ; ?>

                            <!-- Submission date -->
                            <?php $this->renderPartial('/admin/export/statistics_subviews/_submissiondate', array('datestamp'=>$datestamp)) ; ?>

                            <!-- Output options -->
                            <?php $this->renderPartial('/admin/export/statistics_subviews/_outputoptions', array('error'=>$error, 'showtextinline'=>$showtextinline, 'usegraph'=>$usegraph, 'showtextinline'=>$showtextinline)) ; ?>

                            <!-- Output format -->
                            <?php $this->renderPartial('/admin/export/statistics_subviews/_outputformat', array()) ; ?>
                        </div>


                        <p>
                            <input type='hidden' name='summary[]' value='idG' />
                            <input type='hidden' name='summary[]' value='idL' />
                            <input class="hidden" type='submit' value='<?php eT("View statistics"); ?>' />
                            <input class="hidden" type='button' value='<?php eT("Clear"); ?>' onclick="window.open('<?php echo Yii::app()->getController()->createUrl("admin/statistics/sa/index/surveyid/$surveyid"); ?>', '_top')" />
                        </p>
                    </div>

                    <div style='clear: both'></div>

                    <!-- Response filter header -->
                    <?php $this->renderPartial('/admin/export/statistics_subviews/_response_filter_header', array()) ; ?>

                    <!-- AUTOSCROLLING DIV CONTAINING QUESTION FILTERS -->
                    <div id='statisticsresponsefilters' class='statisticsfilters scrollheight_400' <?php if ($filterchoice_state!='' || !empty($summary)) { echo " style='display:none' "; } ?>>
                        <input type='hidden' id='filterchoice_state' name='filterchoice_state' value='<?php echo $filterchoice_state; ?>' />

                        <!-- Filter choice -->
                        <?php $this->renderPartial(
                                                    '/admin/export/statistics_subviews/_response_filter_choice',
                                                    array(
                                                        'filterchoice_state'=>$filterchoice_state,
                                                        'filters'=>$filters,
                                                        'surveyid'=>$surveyid,
                                                        'result'=>$result,
                                                        'fresults'=>$fresults,
                                                        'summary'=>$summary,
                                                        'oStatisticsHelper'=>$oStatisticsHelper
                                                    )) ;
                        ?>

                    </div>

                    <p id='vertical_slide2'>
                        <input type='submit' class="hidden" value='<?php eT("View statistics"); ?>' />
                        <input type='button'class="hidden"  value='<?php eT("Clear"); ?>' onclick="window.open('<?php echo Yii::app()->getController()->createUrl("admin/statistics/sa/index/surveyid/$surveyid"); ?>', '_top')" />
                        <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
                        <input type='hidden' name='display' value='stats' />
                    </p>
                </form>
            </div><!-- END OF AUTOSCROLLING DIV CONTAINING QUESTION FILTERS -->


            <?php
                // TODO : show the css loader
                flush(); //Let's give the user something to look at while they wait for the pretty pictures
            ?>

            <!-- Statistics header -->
            <?php $this->renderPartial('/admin/export/statistics_subviews/_statistics_header', array()) ; ?>

            <div id='statisticsoutput' class='statisticsfilters'>
                <?php echo $output; ?>
            </div>
    </div>
</div>
