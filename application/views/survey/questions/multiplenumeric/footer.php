<?php
/**
 * Multiple numeric question, footer Html
 *
 * @var $equals_num_value
 * @var $id                 $ia[0]
 * @var $prefix
 * @var $sumRemainingEqn      $qinfo['sumRemainingEqn']
 * @var $displaytotal
 * @var $sumEqn             $qinfo['sumEqn']
 */
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
</div>
    <?php endif; ?>
</div> <!-- Footer -->
