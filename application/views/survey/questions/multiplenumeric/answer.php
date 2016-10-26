<?php
/**
 * Multiple Numerical question Html for slider.
 *
 * @var $sRows      : the rows, generated with the views rows/answer_row.php
 *
 * @var $prefixclass
 * @var $equals_num_value
 * @var $id
 * @var $prefix
 * @var $sumRemainingEqn
 * @var $displaytotal
 * @var $sumEqn
 * @var $prefix
 */
?>
<!-- Multiple Numerical with slider -->
<!-- answer -->
<ul class='<?php echo $coreClass?> list-unstyled form-horizontal'>
    <?php
        // rows/answer_row.php
        echo $sRows;
    ?>
<?php
    doRender("/survey/questions/multiplenumeric/rows/dynamic",array(
        'id'=>$id,
        'sumRemainingEqn'=>$sumRemainingEqn,
        'sumEqn'=>$sumEqn,
        'sLabelWidth'=>$sLabelWidth,
        'sInputContainerWidth'=>$sInputContainerWidth,
        'prefix'=>$prefix,
        'suffix'=>$suffix,
    ),false);
?>

</ul>
<!-- endof answer -->
<!-- Add some data for slider javascript -->
<div class="hidden">

</div>
