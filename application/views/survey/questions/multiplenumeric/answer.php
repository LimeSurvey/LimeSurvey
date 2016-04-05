<?php
/**
 * Multiple Numerical question Html
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
 */
?>
<!-- Multiple Numerical -->

<!-- answer -->
<div class='subquestions-list questions-list text-list <?php echo $prefixclass?>-list'>

        <?php
            // rows/answer_row.php
            echo $sRows;
        ?>

        <?php if($equals_num_value):?>
            <p class='multiplenumerichelp help-item text-info'>
                <span class="label">
                    <?php eT('Remaining: ');?>
                </span>
                <span id="remainingvalue_<?php echo $id; ?>" class="dynamic_remaining">
                    <?php echo $prefix; ?>
                    {<?php echo $sumRemainingEqn;?>}
                </span>
            </p>
        <?php endif; ?>

        <?php if($displaytotal):?>

            <li class='multiplenumerichelp  help-item'>
                <span class="label"><?php eT('Total: '); ?></span>
                <span id="totalvalue_<?php echo $id; ?>" class="dynamic_sum">
                    <?php echo $prefix; ?>
                    <?php // NO SPACE AFTER BRACKET !!!! ?>
                    {<?php echo $sumEqn; ?>}
                    <?php echo $suffix; ?>
                </span>
            </li>

        <?php endif; ?>
</div>
<!-- endof answer -->
