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
<ul class='list-unstyled subquestion-list questions-list text-list <?php echo $prefixclass?>-list'>

        <?php
            // rows/answer_row.php
            echo $sRows;
        ?>

        <?php if($sumRemainingEqn):?>
            <li>
                <div class="control-label col-xs-12 col-sm-<?php echo $sLabelWidth; ?>">
                    <?php eT('Remaining: ');?>
                </div>
                <div class="input-group col-xs-12 col-sm-<?php echo $sInputContainerWidth; ?>">
                    <?php echo $prefix; ?>
                    <div id="remainingvalue_<?php echo $id; ?>" class="label label-info dynamic_remaining">
                        {<?php echo $sumRemainingEqn;?>}
                    </div>
                    <?php echo $suffix; ?>
                </div>
            </li>
        <?php endif; ?>

        <?php if($sumEqn):?>
            <li>
                <div class="control-label col-xs-12 col-sm-<?php echo $sLabelWidth; ?>">
                    <?php eT('Total: ');?>
                </div>
               <div class="input-group col-xs-12 col-sm-<?php echo $sInputContainerWidth; ?>">
                    <?php echo $prefix; ?>
                    <div id="remainingvalue_<?php echo $id; ?>" class="label label-info dynamic_total">
                        {<?php echo $sumEqn; ?>}
                    </div>
                    <?php echo $suffix; ?>
                </div>
            </li>
        <?php endif; ?>
</ul>
<!-- endof answer -->
<!-- Add some data for slider javascript -->
<div class="hidden">

</div>
