<?php
/**
 * Multiple numerci : dynamic row (remaining + total)
 * @var $id
 * @var $sumRemainingEqn
 * @var $sumEqn
 * @var $sLabelWidth
 * @var $sInputContainerWidth
 * @var $prefix
 * @var $suffix
 */
?>
<?php if($sumRemainingEqn):?>
    <li class="form-group ls-group-remaining ls-group-dynamic">
        <div class="col-xs-12 col-sm-<?php echo $sInputContainerWidth; ?> col-md-offset-<?php echo $sLabelWidth; ?>">
            <div class="control-label">
                <?php eT('Remaining: ');?>
            </div>
            <?php if ($prefix != ''): ?>
                <div class="prefix-text prefix hidden"><!-- Suffix prefix are not shown inline, but only in the slider: completely diofferent concept : set hidden -->
                    <?php echo $prefix; ?>
                </div>
            <?php endif; ?>
            <div id="remainingvalue_<?php echo $id; ?>" class="form-control-static numeric dynamic-remaining" data-number="1"><!-- alternative class : form-control : display like an input:text -->
                {<?php echo $sumRemainingEqn;?>}
            </div>
            <?php if ($suffix != ''): ?>
                <div class="suffix-text suffix hidden">
                    <?php echo $suffix; ?>
                </div>
            <?php endif; ?>
        </div>
    </li>
<?php endif; ?>

<?php if($sumEqn):?>
    <li class="form-group ls-group-total ls-group-dynamic">
       <div class="col-xs-12 col-sm-<?php echo $sInputContainerWidth; ?> col-md-offset-<?php echo $sLabelWidth; ?>">
            <div class="control-label">
                <?php eT('Total: ');?>
            </div>
            <div id="totalvalue_<?php echo $id; ?>" class="form-control-static numeric dynamic-total" data-number="1">
                {<?php echo $sumEqn; ?>}
            </div>
        </div>
    </li>
<?php endif; ?>
