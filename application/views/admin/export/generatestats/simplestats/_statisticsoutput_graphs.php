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
?>
    <?php if(count($labels) < 70): ?>
        <!-- Charts -->
        <div class="row">
            <div class="col-sm-9 vcenter chartjs-container" id="chartjs-container-<?php echo $qqid; ?>"
                data-chartname="<?php echo $sChartname; // The name of the jschart object ?>"
                data-qid="<?php echo $qqid; // the question id ?>"
                data-type="<?php echo $charttype; // the chart start type (bar, donut, etc.) ?>"
                data-color="<?php echo $color; // the background color for bar, etc. ?>"
            >
                <canvas class="canvas-chart " id="chartjs-<?php echo $qqid; ?>" width="400" height="300<?php // echo $iCanvaHeight;?>"
                    data-color="<?php echo $color; // the background color for bar, etc. ?>"></canvas>
            </div>

            <!-- legends -->
            <?php if($charttype=='Pie' || $charttype=='Doughnut'): ?>
            <div class="legend col-sm-2 vcenter">
                <?php foreach($labels as $i=>$label): ?>
                    <?php $colorindex = $color+$i; $colorindex=($colorindex < 71)?$colorindex:0;?>
                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-sm-1">
                            <span style="background-color:rgba(<?php echo $COLORS_FOR_SURVEY[$colorindex];?>,0.6); display: block;    width: 20px;    height: 20px;    border-radius: 5px; margin: 0px; padding: 0px;">
                            </span>
                        </div>
                        <div class="col-sm-10">
                            <?php echo $label; ?>
                        </div>
                    </div>
                <?php endforeach;?>
            </div>
            <?php endif; ?>
        </div>

<!-- Buttons to change graph type -->
<?php /*
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
        */?>
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

<script>
    var labels_<?php echo $qqid; ?>=<?php echo json_encode($labels); // the array of labels ?>;
    var grawdata_<?php echo $qqid;?>=<?php echo json_encode($grawdata); // the datas to generate the graph ?>;
</script>
