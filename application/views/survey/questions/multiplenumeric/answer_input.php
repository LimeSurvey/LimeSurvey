<?php
/**
 * Multiple Numerical question Html for input boxes.
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
<!-- Multiple Numerical -->

<!-- answer -->
<div class='subquestion-list questions-list text-list <?php echo $prefixclass?>-list'>

    <table class='table no-more-tables table-multi-num'>
        <?php
            // rows/answer_row.php
            echo $sRows;
        ?>

        <?php if($equals_num_value):?>
            <tr>
                <td class='hide-on-small-screen'></td>
                <?php if (!empty($prefix)): ?>
                    <td class='hide-on-small-screen'></td>
                <?php endif; ?>
                <td>
                    <div class='multiplenumerichelp help-block pull-right'>
                        <div class='label label-default'>
                            <label>
                                <?php eT('Remaining: ');?>
                            </label>
                            <span id="remainingvalue_<?php echo $id; ?>" class="dynamic_remaining">
                                <?php echo $prefix; ?>
                                {<?php echo $sumRemainingEqn;?>}
                                <?php echo $suffix; ?>
                            </span>
                        </div>
                    </div>
                </td>
            </tr>
        <?php endif; ?>

        <?php if($displaytotal):?>
            <tr>
                <td class='hide-on-small-screen'></td>
                <?php if (!empty($prefix)): ?>
                    <td class='hide-on-small-screen'></td>
                <?php endif; ?>
                <td>

                    <div class='multiplenumerichelp help-block pull-right'>
                        <div class='label label-default'>
                            <label class=""><?php eT('Total: '); ?></label>
                            <span id="totalvalue_<?php echo $id; ?>" class="">
                                <?php echo $prefix; ?>
                                <?php // NO SPACE AFTER BRACKET !!!! ?>
                                {<?php echo $sumEqn; ?>}
                                <?php echo $suffix; ?>
                            </span>
                        </div>
                    </div>

                </td>
            </tr>
        <?php endif; ?>
    </table>
</div>
<!-- endof answer -->
