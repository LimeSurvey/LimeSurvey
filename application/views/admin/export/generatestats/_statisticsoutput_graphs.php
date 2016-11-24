<?php
/**
 * This view render the graphs
 *
 * @var $rt
 * @var $qqid
 * @var $labels
 * @var $COLORS_FOR_SURVEY
 * @var $charttype
 * @var $sChartname
 * @var $grawdata
 * @var $color
 *
 */
 // TODO: move to controller
 $qqid = str_replace ( '-', '__' , $qqid );
?>
<tr>
    <td colspan='4' style=\"text-align:center\" id='statzone_<?php echo $rt;?>'>
    <?php if(count($labels) < 70): ?>
        <!-- Charts -->
        <div class="row">
            <div class="col-lg-8 col-md-12 chartjs-container" id="chartjs-container-<?php echo $qqid; ?>"
                data-chartname="<?php echo $sChartname; // The name of the jschart object ?>"
                data-qid="<?php echo $qqid; // the question id ?>"
                data-type="<?php echo $charttype; // the chart start type (bar, donut, etc.) ?>"
                data-color="<?php echo $color; // the background color for bar, etc. ?>"
            >

            <?php if (array_sum($grawdata_percent)<1):?>
                <div class="stat-no-answer text-center" id="stat-no-answer-<?php echo $qqid; ?>" style="position: relative; top: 300px; display: none;" >
                    <?php eT('Not enough response data');?>
                </div>
            <?php endif;?>

                <!-- a default width/height is provided from the server side. But it's overwritten by javascript-->
                <canvas class="canvas-chart " id="chartjs-<?php echo $qqid; ?>" width="<?php echo $canvaWidth?>" height="<?php echo $canvaHeight?>"
                    data-color="<?php echo $color; // the background color for bar, etc. ?>"></canvas>


            </div>
            <!-- legends -->
            <div class="legend legend-no-percent col-lg-4 col-md-12" id="legend-no-percent-<?php echo $qqid;?>">
                <?php foreach($labels as $i=>$label): ?>
                    <?php $colorindex = $color+$i; $colorindex = ($colorindex < 72)?$colorindex:0;?>
                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-sm-1">
                            <span style="background-color:rgba(<?php echo $COLORS_FOR_SURVEY[$colorindex];?>,0.6) !important; display: block;    width: 20px;    height: 20px;    border-radius: 5px; margin: 0px; padding: 0px;">
                            </span>
                        </div>
                        <div class="col-sm-10">
                            <?php echo $label;?>
                        </div>
                    </div>
                <?php endforeach;?>
            </div>

            <!-- legends in percents -->
            <?php // var_dump($labels); var_dump($graph_labels_percent);?>
            <div class="legend legend-percent col-lg-4  col-md-12" id="legend-percent-<?php echo $qqid;?>">
                <?php if (count($graph_labels_percent)>0):?>
                    <?php foreach($graph_labels_percent as $i=>$label): ?>
                        <?php $colorindex = $color+$i; $colorindex = ($colorindex < 72)?$colorindex:0;?>
                        <div class="row" style="margin-bottom: 10px;">
                            <div class="col-sm-1">
                                <span style="background-color:rgba(<?php echo $COLORS_FOR_SURVEY[$colorindex];?>,0.6); display: block;    width: 20px;    height: 20px;    border-radius: 5px; margin: 0px; padding: 0px;">
                                </span>
                            </div>
                            <div class="col-sm-10">
                                <?php echo $label;?>
                            </div>
                        </div>
                    <?php endforeach;?>
                <?php endif; ?>
            </div>
        </div>
    </td>
</tr>

<!-- Buttons to change graph type -->
<tr>
    <td colspan='4'>
        <div class="chartjs-buttons" style="text-align:center">

            <!-- Bar chart -->
            <button type="button" data-qid="<?php echo $qqid; ?>" id="button-chartjs-Bar-<?php echo $qqid; ?>" class="btn btn-default chart-type-control" data-type="Bar" data-canva-id="chartjs-Bar-<?php echo $qqid; ?>" aria-label="Left Align">
                <i class="fa fa-bar-chart"></i>
                    <?php eT('Bar chart'); ?>
            </button>


            <!-- Pie chart -->
            <button type="button" data-qid="<?php echo $qqid; ?>" id="button-chartjs-Pie-<?php echo $qqid; ?>" class="btn btn-default chart-type-control" data-type="Pie" data-canva-id="chartjs-Pie-<?php echo $qqid; ?>" aria-label="Left Align">
                <i class="fa fa-pie-chart"></i>
                <?php eT('Pie chart'); ?>
            </button>

            <!-- Radar chart -->
            <button type="button" data-qid="<?php echo $qqid; ?>" id="button-chartjs-Radar-<?php echo $qqid; ?>" class="btn btn-default chart-type-control" data-type="Radar" data-canva-id="chartjs-Radar-<?php echo $qqid; ?>" aria-label="Left Align">
                <i class="fa fa-crosshairs"></i>
                    <?php eT('Radar chart'); ?>
            </button>


            <!-- Line chart -->
            <button type="button" data-qid="<?php echo $qqid; ?>" id="button-chartjs-Line-<?php echo $qqid; ?>" class="btn btn-default chart-type-control" data-type="Line" data-canva-id="chartjs-Line-<?php echo $qqid; ?>" aria-label="Left Align">
                <i class="fa fa-line-chart"></i>
                <?php eT('Line chart'); ?>
            </button>

            <!-- Polar chart -->
            <button type="button" data-qid="<?php echo $qqid; ?>" id="button-chartjs-PolarArea-<?php echo $qqid; ?>" class="btn btn-default chart-type-control" data-type="PolarArea" data-canva-id="chartjs-PolarArea-<?php echo $qqid; ?>" aria-label="Left Align">
                <i class="fa fa-sun-o"></i>
                <?php eT('Polar chart'); ?>
            </button>

            <!-- Doughnut chart -->
            <button type="button" data-qid="<?php echo $qqid; ?>" id="button-chartjs-Doughnut-<?php echo $qqid; ?>" class="btn btn-default chart-type-control" data-type="Doughnut" data-canva-id="chartjs-Doughnut-<?php echo $qqid; ?>" aria-label="Left Align">
                <i class="fa fa fa-circle-o"></i>
                <?php eT('Doughnut chart'); ?>
            </button>
        </div>
        <div id='stats_<?php echo $rt;?>' class='graphdisplay' style="text-align:center">
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-warning" role="alert">
                    <?php eT("Too many labels, can't generate chart");?>
                </div>
            </div>
        </div>
    <?php endif;?>
    </td>
</tr>
<?php
/*<script>
    var labels_<?php echo $qqid; ?>=<?php echo json_encode($graph_labels); // the array of labels ?>;
    var grawdata_<?php echo $qqid;?>=<?php echo json_encode($grawdata); // the datas to generate the graph ?>;
    var labels_percent_<?php echo $qqid; ?>=<?php echo json_encode($graph_labels_percent); // the array of labels ?>;
    var grawdata_percent_<?php echo $qqid;?>=<?php echo json_encode($grawdata_percent); // the datas to generate the graph using percentages (pie, Doughnut, polar ) ?>;
</script>*/
?>