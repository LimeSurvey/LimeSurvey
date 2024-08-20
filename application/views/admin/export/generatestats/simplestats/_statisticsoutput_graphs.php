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
<!-- _statisticsoutput_graphs -->
    <?php if(count($labels) < 70): ?>
        <!-- Charts -->
        <div class="row custom custom-padding bottom-20">
            <div class="col-md-12 vcenter chartjs-container" id="chartjs-container-<?php echo $qqid; ?>"
                data-chartname="<?php echo $sChartname; // The name of the jschart object ?>"
                data-qid="<?php echo $qqid; // the question id ?>"
                data-type="<?php echo $charttype; // the chart start type (bar, donut, etc.) ?>"
                data-color="<?php echo $color; // the background color for bar, etc. ?>"
            >

            <?php
            //var_dump($labels);
            ?>
                <canvas class="canvas-chart " id="chartjs-<?php echo $qqid; ?>" width="400" height="300<?php // echo $iCanvaHeight;?>"
                    data-color="<?php echo $color; // the background color for bar, etc. ?>"></canvas>
            </div>
        </div>

        <div class="row">
            <!-- legends -->
            <div class="legend col-md-12 vcenter">
                <?php foreach($fullLabels as $i=>$label): ?>
                    <?php $colorindex = $color+$i; ?>
                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-md-1">
                            <span style="background-color:rgba(<?php echo $COLORS_FOR_SURVEY[$colorindex];?>,0.6); display: block;    width: 20px;    height: 20px;    border-radius: 5px; margin: 0px; padding: 0px;">
                            </span>
                        </div>
                        <div class="col-md-10">
                            <?php echo $label; ?>
                        </div>
                    </div>
                <?php endforeach;?>
            </div>
        </div>

<!-- Buttons to change graph type -->
        <div id='stats_<?php echo $rt;?>' class='graphdisplay' style="text-align:center">
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-12">
                <?php
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT("Too many labels, can't generate chart"),
                    'type' => 'warning',
                ]);
                ?>
            </div>
        </div>
    <?php endif;?>

<?php //Simpler js-aggregation of values through global object. Approx 30% faster than parsing through eval ?>
<script>
    statisticsData['quid'+'<?php echo $qqid; ?>'] = {
        labels : <?php echo json_encode($labels); ?>,
        grawdata : <?php echo json_encode($grawdata); ?>, // the datas to generate the graph  
    };
</script>
<!-- endof  _statisticsoutput_graphs -->
