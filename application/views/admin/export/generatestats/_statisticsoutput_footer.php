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

statisticsData['quid'+'<?php echo $qqid; ?>'] = {
    labels : <?php echo json_encode($graph_labels); ?>,
    grawdata : <?php echo json_encode($grawdata); ?>, // the datas to generate the graph  
    labels_percent : <?php echo json_encode($graph_labels_percent); ?>, // the array of labels  
    grawdata_percent : <?php echo json_encode($grawdata_percent);?> // the datas to generate the graph using percentages (pie, Doughnut, polar ) 
};